<?php
session_start();

// CSRF 검증
include_once __DIR__ . '/../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_die();
}

// 에러 로그만 기록 (화면 표시 안함)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
$HomeDir = "..";
$PageCode = "PrintAuto";
include "../db.php";

// 데이터베이스 연결 확인
if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// 장바구니에서 주문 처리
if (isset($_GET['SubmitMode']) && $_GET['SubmitMode'] === 'OrderOne') {
    $session_id = session_id();
    
    // 장바구니 아이템 가져오기
    $cart_query = "SELECT * FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($db, $cart_query);
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $cart_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($cart_result) > 0) {
        // 새 주문 번호 생성
        $Table_result = mysqli_query($db, "SELECT MAX(no) FROM mlangorder_printauto");
        if (!$Table_result) {
            echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
            exit;
        }
        $row = mysqli_fetch_row($Table_result);
        $new_no = $row[0] ? $row[0] + 1 : 1;
        
        // 주문 폴더 생성
        $dir = "upload/$new_no";
        if (!is_dir($dir)) {
            mkdir($dir, 0755);
            chmod($dir, 0777);
        }
        
        // 각 장바구니 아이템을 주문으로 변환
        while ($item = mysqli_fetch_assoc($cart_result)) {
            // 용지 정보 가져오기
            $fsd_query = "SELECT name FROM product_fsd WHERE no = ?";
            $fsd_stmt = mysqli_prepare($db, $fsd_query);
            mysqli_stmt_bind_param($fsd_stmt, 'i', $item['MY_Fsd']);
            mysqli_stmt_execute($fsd_stmt);
            $fsd_result = mysqli_stmt_get_result($fsd_stmt);
            $fsd_name = ($fsd_row = mysqli_fetch_assoc($fsd_result)) ? $fsd_row['name'] : $item['MY_Fsd'];
            
            // 인쇄색상 정보 가져오기
            $type_query = "SELECT name FROM product_type WHERE no = ?";
            $type_stmt = mysqli_prepare($db, $type_query);
            mysqli_stmt_bind_param($type_stmt, 'i', $item['MY_type']);
            mysqli_stmt_execute($type_stmt);
            $type_result = mysqli_stmt_get_result($type_stmt);
            $type_name = ($type_row = mysqli_fetch_assoc($type_result)) ? $type_row['name'] : $item['MY_type'];
            
            // 규격 정보 가져오기
            $pntype_query = "SELECT name FROM pn_type WHERE no = ?";
            $pntype_stmt = mysqli_prepare($db, $pntype_query);
            mysqli_stmt_bind_param($pntype_stmt, 'i', $item['PN_type']);
            mysqli_stmt_execute($pntype_stmt);
            $pntype_result = mysqli_stmt_get_result($pntype_stmt);
            $pntype_name = ($pntype_row = mysqli_fetch_assoc($pntype_result)) ? $pntype_row['name'] : $item['PN_type'];
            
            $insert_query = "INSERT INTO mlangorder_printauto 
                           (no, Type, Type_1, money_1, money_2, money_3, money_4,
                            OrderStyle, ThingCate, date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                            
            $stmt = mysqli_prepare($db, $insert_query);
            
            // 제품 타입에 따른 정보 구성
            switch ($item['product_type']) {
                case 'inserted':
                    $type = '전단지';
                    $type_1 = "용지: {$item['MY_Fsd']}\n" .
                             "인쇄색상: {$item['MY_type']}\n" .
                             "규격: {$item['PN_type']}\n" .
                             "수량: {$item['MY_amount']}\n" .
                             ($item['POtype'] == '1' ? '단면' : '양면');
                    break;
                    
                case 'cadarok':
                    $type = '카달로그';
                    $type_1 = "종류: {$item['MY_Fsd']}\n" .
                             "옵션: {$item['MY_type']}\n" .
                             "수량: {$item['MY_amount']}";
                    break;
                    
                default:
                    $type = '스티커';
                    $type_1 = "용지: {$item['MY_Fsd']}\n" .
                             "인쇄색상: {$item['MY_type']}\n" .
                             "규격: {$item['PN_type']}\n" .
                             "수량: {$item['MY_amount']}";
            }
            
            // VAT 차액 계산
            $vat_difference = $item['st_price_vat'] - $item['st_price'];
            $design_money = 0;
            
            mysqli_stmt_bind_param($stmt, 'issddddsss',
                $new_no,
                $type,
                $type_1,
                $item['st_price'],
                $item['st_price_vat'],
                $design_money,
                $vat_difference,
                $item['product_type'],
                $item['MY_type'] // ThingCate
            );
            
            mysqli_stmt_execute($stmt);
        }
        
        // 장바구니 비우기
        mysqli_query($db, "DELETE FROM shop_temp WHERE session_id = '$session_id'");
        
        // 주문 완료 페이지로 이동
        echo "<script>
                alert('주문이 완료되었습니다.');
                location.href='order_complete.php?order_no=$new_no';
              </script>";
        exit;
    } else {
        echo "<script>alert('장바구니가 비어있습니다.'); history.go(-1);</script>";
        exit;
    }
}

if (isset($_POST['mode']) && $_POST['mode'] == "SubmitOk") {

    // 데이터베이스 연결 및 최대 no 조회
    $Table_result = mysqli_query($db, "SELECT MAX(no) FROM mlangorder_printauto");
    if (!$Table_result) {
        // 보안: DB 에러 상세 정보는 로그에만 기록
        error_log("[OnlineOrder] DB 조회 오류: " . mysqli_error($db));
        echo "<script>
                window.alert(\"주문 처리 중 일시적인 오류가 발생했습니다. 잠시 후 다시 시도해주세요.\");
                history.go(-1);
              </script>";
        exit;
    }
    $row = mysqli_fetch_row($Table_result);
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 자료를 업로드할 폴더를 생성
    $dir = "upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777);
    }

    // 데이터 보호
    $zip = $db->real_escape_string($_POST['sample6_postcode'] ?? '');
    $zip1 = $db->real_escape_string($_POST['sample6_address'] ?? '');
    $zip2 = $db->real_escape_string($_POST['sample6_detailAddress'] ?? '');
    $address3 = $db->real_escape_string($_POST['sample6_extraAddress'] ?? '');
    $name = $db->real_escape_string($_POST['username'] ?? '');
    $email = $db->real_escape_string($_POST['email'] ?? '');
    $phone = $db->real_escape_string($_POST['phone'] ?? '');
    $Hendphone = $db->real_escape_string($_POST['Hendphone'] ?? '');
    $delivery = $db->real_escape_string($_POST['delivery'] ?? '');
    $bizname = $db->real_escape_string($_POST['bizname'] ?? '');
    $bank = $db->real_escape_string($_POST['bank'] ?? '');
    $bankname = $db->real_escape_string($_POST['bankname'] ?? '');
    $cont = $db->real_escape_string($_POST['cont'] ?? '');
    $pass = $db->real_escape_string($_POST['pass'] ?? '');
    $Gensu = $db->real_escape_string($_POST['Gensu'] ?? '');
    $Type = $db->real_escape_string($_POST['Type'] ?? '');
    $ImgFolder = $db->real_escape_string($_POST['ImgFolder'] ?? ''); // ImgFolder 변수가 정의되지 않았을 때 기본값 설정
    $money_1 = $db->real_escape_string($_POST['money_1'] ?? '');
    $money_2 = $db->real_escape_string($_POST['money_2'] ?? '');
    $money_3 = $db->real_escape_string($_POST['money_3'] ?? '');
    $money_4 = $db->real_escape_string($_POST['money_4'] ?? '');
    $money_5 = $db->real_escape_string($_POST['money_5'] ?? '');
    $PageSS = $db->real_escape_string($_POST['PageSS'] ?? '');
    $Type_1 = $db->real_escape_string($_POST['Type_1'] ?? '');
    $Type_2 = $db->real_escape_string($_POST['Type_2'] ?? '');
    $Type_3 = $db->real_escape_string($_POST['Type_3'] ?? '');
    $Type_4 = $db->real_escape_string($_POST['Type_4'] ?? '');
    $Type_5 = $db->real_escape_string($_POST['Type_5'] ?? '');
    $Type_6 = $db->real_escape_string($_POST['Type_6'] ?? '');
    $OrderSytle = $db->real_escape_string($_POST['OrderSytle'] ?? '');
    $standard = $db->real_escape_string($_POST['standard'] ?? '');
    $page = $db->real_escape_string($_POST['page'] ?? '');
    $Designer = $db->real_escape_string($_POST['Designer'] ?? '');
    $ThingCate = $db->real_escape_string($_POST['ThingCate'] ?? '');

    if ($PageSS == "OrderOne") {
        $PageSSOk = "2";
    } elseif ($PageSS == "OrderTwo") {
        $PageSSOk = "1";
    } else {
        $PageSSOk = "0";
    }

    // Type_1 필드에 Type_1~Type_6 데이터를 합쳐서 저장
    $Type_combined = implode('|', [$Type_1, $Type_2, $Type_3, $Type_4, $Type_5, $Type_6]);

    $date = date("Y-m-d H:i:s");
    $dbinsert = "INSERT INTO mlangorder_printauto (
        no, Type, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, 
        name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, 
        date, OrderStyle, ThingCate, pass, Gensu, Designer
    ) VALUES (
        '$new_no', '$Type', '$ImgFolder', '$Type_combined', '$money_1', '$money_2', '$money_3', '$money_4', '$money_5', 
        '$name', '$email', '$zip', '$zip1', '$zip2', '$phone', '$Hendphone', '$delivery', '$bizname', '$bank', 
        '$bankname', '$cont', '$date', '$OrderSytle', '$ThingCate', '$pass', '$Gensu', '$Designer'
    )";

    // 쿼리를 출력하여 확인
    // echo "<pre>$dbinsert</pre>";

    $result_insert = mysqli_query($db, $dbinsert);

    // 데이터 삽입 결과 확인 및 리디렉션
    if ($result_insert) {
        $redirect_url = "OrderResult.php?OrderSytle=$OrderSytle&no=$new_no&username=" . urlencode($_POST['username']) . 
            "&Type_1=" . urlencode($Type_combined) . "&money4=$money_4&money5=$money_5&phone=$phone&Hendphone=$Hendphone&zip1=$zip1&zip2=$zip2&email=$email&date=$date&cont=$cont" .
            "&standard=$standard&page=$page&PageSS=$PageSS";
        echo "<html><meta http-equiv='Refresh' content='0; URL=$redirect_url'></html>";
    } else {
        // 보안: SQL 에러는 로그에만 기록하고 사용자에게는 일반 메시지 표시
        error_log("[OnlineOrder] 주문 삽입 오류: " . mysqli_error($db));
        echo "<script>
                window.alert(\"주문 처리 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.\");
                history.go(-1);
              </script>";
    }
    exit;
}
?>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<!---------------- 전체를 감싼다. ------------------------>
<table border=0 align=center width=100% cellpadding=0 cellspacing=0>
<tr>
<td align=center> 
<?php
$submitMode = isset($_GET['SubmitMode']) ? $_GET['SubmitMode'] : 'OrderOne';

// 허용된 파일 목록 정의
$allowedFiles = ['OrderOne', 'OrderTwo', 'OrderTree'];

// submitMode 값이 허용된 목록에 있는지 확인
if (in_array($submitMode, $allowedFiles)) {
    // 파일 이름을 생성하고 포함
    $fileName = "OrderForm" . $submitMode . ".php";
    include $fileName;
} else {
    // 허용되지 않은 파일 요청에 대한 처리
    echo "잘못된 요청입니다. 유효하지 않은 SubmitMode 값입니다.";
}
?>   
</td>
</tr>
</table>

</body>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/mlangprintauto/MlangPrintAutoDown.php";
?>
