<?php
include "../../db.php";
include "../../includes/auth.php";
include "../../includes/upload_path_manager.php"; // 레거시 경로 관리

// 추가 옵션 표시 시스템 포함
if (file_exists('../../includes/AdditionalOptionsDisplay.php')) {
    include_once '../../includes/AdditionalOptionsDisplay.php';
    // AdditionalOptionsDisplay 인스턴스 생성
    $optionsDisplay = new AdditionalOptionsDisplay($db);
}

// 디버깅: $db 변수 확인
if (!isset($db) || !$db) {
    die("ERROR: Database connection not established from db.php");
}

include "../config.php";

$T_DirUrl = "../../mlangprintauto";
include "$T_DirUrl/ConDb.php";

$T_DirFole = "./int/info.php";
$mode = isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : ""); // 초기화
$ModifyCode = isset($_POST['ModifyCode']) ? $_POST['ModifyCode'] : (isset($_GET['ModifyCode']) ? $_GET['ModifyCode'] : "");
$no = isset($_POST['no']) ? intval($_POST['no']) : (isset($_GET['no']) ? intval($_GET['no']) : 0);
$Type = isset($_POST['Type']) ? $_POST['Type'] : "기본값";
$ImgFolder = isset($_POST['ImgFolder']) ? $_POST['ImgFolder'] : "default_folder";
$Type_1 = isset($_POST['Type_1']) ? $_POST['Type_1'] : "default_type";
$money_1 = isset($_POST['money_1']) ? $_POST['money_1'] : 0;
$money_2 = isset($_POST['money_2']) ? $_POST['money_2'] : 0;
$money_3 = isset($_POST['money_3']) ? $_POST['money_3'] : 0;
$money_4 = isset($_POST['money_4']) ? $_POST['money_4'] : 0;
$money_5 = isset($_POST['money_5']) ? $_POST['money_5'] : 0;
$OrderName = isset($_POST['name']) ? $_POST['name'] : "미입력";
$email = isset($_POST['email']) ? $_POST['email'] : "noemail@example.com";
$zip = isset($_POST['zip']) ? $_POST['zip'] : "";
$zip1 = isset($_POST['zip1']) ? $_POST['zip1'] : "";
$zip2 = isset($_POST['zip2']) ? $_POST['zip2'] : "";
$phone = isset($_POST['phone']) ? $_POST['phone'] : "";
$Hendphone = isset($_POST['Hendphone']) ? $_POST['Hendphone'] : "";
$bizname = isset($_POST['bizname']) ? $_POST['bizname'] : "기본 회사명";
$bank = isset($_POST['bank']) ? $_POST['bank'] : "기본 은행";
$bankname = isset($_POST['bankname']) ? $_POST['bankname'] : "";
$cont = isset($_POST['cont']) ? $_POST['cont'] : "내용 없음";
$date = isset($_POST['date']) ? $_POST['date'] : date("Y-m-d H:i:s");
$OrderStyle = isset($_POST['OrderStyle']) ? $_POST['OrderStyle'] : "기본 스타일";
$ThingCate = isset($_POST['ThingCate']) ? $_POST['ThingCate'] : "";
$pass = isset($_POST['pass']) ? $_POST['pass'] : "";
$Designer = isset($_POST['Designer']) ? $_POST['Designer'] : "미정";
$Gensu = isset($_POST['Gensu']) ? $_POST['Gensu'] : 0;
$ThingNo= isset($_POST['ThingNo']) ? $_POST['ThingNo'] : 0;

// BankForm 관련 POST 데이터 받기
$SignMMk = isset($_POST['SignMMk']) ? $_POST['SignMMk'] : "";
$BankName = isset($_POST['BankName']) ? $_POST['BankName'] : "";
$TName = isset($_POST['TName']) ? $_POST['TName'] : "";
$BankNo = isset($_POST['BankNo']) ? $_POST['BankNo'] : "";
///////////////////////////////////////////////////////////////////////////////////////////////

// ✅ 재주문 처리
if ($mode == "ReOrder") {
    $db->set_charset("utf8");

    // 원본 주문 조회
    $source_no = isset($_GET['source_no']) ? intval($_GET['source_no']) : 0;
    if ($source_no <= 0) {
        echo "<script>alert('원본 주문번호가 없습니다.'); history.back();</script>";
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM mlangorder_printauto WHERE no = ?");
    $stmt->bind_param("i", $source_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $source_order = $result->fetch_assoc();
    $stmt->close();

    if (!$source_order) {
        echo "<script>alert('원본 주문을 찾을 수 없습니다.'); history.back();</script>";
        exit;
    }

    // 최신 주문번호 조회 후 +1
    $max_result = $db->query("SELECT MAX(no) as max_no FROM mlangorder_printauto");
    $max_row = $max_result->fetch_assoc();
    $new_no = intval($max_row['max_no']) + 1;

    // 새 주문 생성 (날짜와 상태만 변경)
    $new_date = date("Y-m-d H:i:s");
    $new_order_style = "2"; // 입금대기 상태

    // 실제 테이블 컬럼에 맞춰 INSERT
    $insert_stmt = $db->prepare("INSERT INTO mlangorder_printauto
        (no, Type, ImgFolder, Type_1, mesu, money_1, money_2, money_3, money_4, money_5,
         name, email, zip, zip1, zip2, phone, Hendphone, bizname, bank, bankname,
         cont, date, OrderStyle, ThingCate, Designer, pass, Gensu, delivery,
         price_supply, price_vat, quantity_display, unit, uploaded_files, product_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$insert_stmt) {
        echo "<script>alert('SQL 준비 실패: " . $db->error . "'); history.back();</script>";
        exit;
    }

    // 타입: i(no) + 25s(Type~pass) + i(Gensu) + s(delivery) + ii(price_supply,price_vat) + 4s(quantity_display~product_type)
    $insert_stmt->bind_param("isssssssssssssssssssssssssisiissss",
        $new_no,
        $source_order['Type'],
        $source_order['ImgFolder'],
        $source_order['Type_1'],
        $source_order['mesu'],
        $source_order['money_1'],
        $source_order['money_2'],
        $source_order['money_3'],
        $source_order['money_4'],
        $source_order['money_5'],
        $source_order['name'],
        $source_order['email'],
        $source_order['zip'],
        $source_order['zip1'],
        $source_order['zip2'],
        $source_order['phone'],
        $source_order['Hendphone'],
        $source_order['bizname'],
        $source_order['bank'],
        $source_order['bankname'],
        $source_order['cont'],
        $new_date,
        $new_order_style,
        $source_order['ThingCate'],
        $source_order['Designer'],
        $source_order['pass'],
        $source_order['Gensu'],
        $source_order['delivery'],
        $source_order['price_supply'],
        $source_order['price_vat'],
        $source_order['quantity_display'],
        $source_order['unit'],
        $source_order['uploaded_files'],
        $source_order['product_type']
    );

    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        echo "<script>
            alert('재주문이 생성되었습니다.\\n새 주문번호: $new_no');
            window.location.href = 'admin.php?mode=OrderView&no=$new_no';
        </script>";
    } else {
        $error = $insert_stmt->error;
        $insert_stmt->close();
        echo "<script>alert('재주문 생성 실패: $error'); history.back();</script>";
    }
    exit;
}

if ($mode == "ModifyOk") { ////////////////////////////////////////////////////////////////////////////
    // 데이터베이스 연결
    // $db는 이미 ../../db.php에서 생성됨
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // POST 데이터 받기
    $TypeOne = isset($_POST['TypeOne']) ? $_POST['TypeOne'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $zip = isset($_POST['zip']) ? $_POST['zip'] : '';
    $zip1 = isset($_POST['zip1']) ? $_POST['zip1'] : '';
    $zip2 = isset($_POST['zip2']) ? $_POST['zip2'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $Hendphone = isset($_POST['Hendphone']) ? $_POST['Hendphone'] : '';
    $bizname = isset($_POST['bizname']) ? $_POST['bizname'] : '';
    $bank = isset($_POST['bank']) ? $_POST['bank'] : '';
    $bankname = isset($_POST['bankname']) ? $_POST['bankname'] : '';
    $cont = isset($_POST['cont']) ? $_POST['cont'] : '';
    $Gensu = isset($_POST['Gensu']) ? $_POST['Gensu'] : 0;
    $delivery = isset($_POST['delivery']) ? $_POST['delivery'] : '';

    // SQL UPDATE 문 준비
    $stmt = $db->prepare("UPDATE mlangorder_printauto 
        SET name = ?, email = ?, zip = ?, zip1 = ?, zip2 = ?, phone = ?, Hendphone = ?, bizname = ?, 
            bank = ?, bankname = ?, cont = ?, Gensu = ?, delivery = ?
        WHERE no = ?");

    $stmt->bind_param(
        "sssssssssssssi", 
        $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, 
        $bank, $bankname, $cont, $Gensu, $delivery, $no
    );

    if (!$stmt->execute()) {
        $stmt->close();
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    $stmt->close();

    // JavaScript로 알림 후 페이지 새로고침 (header() 대신 사용)
    $redirect_url = htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=" . intval($no);
    echo "<script>
            alert('정보를 정상적으로 수정하였습니다.');
            if (window.opener) {
                // 팝업 창인 경우: 부모 창 새로고침 후 닫기
                window.opener.location.reload();
                window.close();
            } else {
                // 일반 페이지인 경우: 리디렉션
                window.location.href = '{$redirect_url}';
            }
          </script>";
    exit;
}
?>

<?php
if ($mode == "SubmitOk") { ////////////////////////////////////////////////////////////////////////////
    // 데이터베이스 연결
    // $db는 이미 ../../db.php에서 생성됨
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // 새로운 주문번호 생성
    $Table_result = $db->query("SELECT MAX(no) FROM mlangorder_printauto");
    if (!$Table_result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    $row = $Table_result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 업로드 폴더 생성
    $dir = "../../mlangorder_printauto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        chmod($dir, 0777);
    }

    // 현재 날짜 가져오기
    $date = date("Y-m-d H:i:s");

    // 데이터 삽입
    $stmt = $db->prepare("INSERT INTO mlangorder_printauto 
        (no, Type, ImgFolder, TypeOne, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, bizname, bank, bankname, cont, date, orderStyle, ThingCate, Designer, pass, Gensu) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $status = 3; // 기본 상태값 설정
    $ThingCate = ""; // 첨부파일 기본값 (추후 파일 업로드 기능이 추가될 경우 업데이트 가능)

    $stmt->bind_param(
        "issssssssssssssssssssssssi", 
        $new_no, $Type, $ImgFolder, $TypeOne, $money_1, $money_2, $money_3, $money_4, $money_5, 
        $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname, 
        $cont, $date, $OrderStyle, $ThingCate, $Designer, $pass, $Gensu
    );

    if (!$stmt->execute()) {
        echo "<script>alert('DB 저장 실패! 오류: " . $stmt->error . "'); history.go(-1);</script>";
        exit;
    }

    echo "<script>
            alert('정보를 정상적으로 [저장] 하였습니다.');
            opener.parent.location.reload();
            window.location.href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=$new_no';
          </script>";

    $stmt->close();
    // // $db->close(); // 스크립트 끝에서 자동으로 닫힘 // 연결 유지
    exit;
}
?>


<?php
if ($mode == "BankForm") { //////////////////////////////////////////////////////////////////////////
    include "../title.php";
    include "int/info.php";
    $Bgcolor1 = "408080";
?>
<head>
    <style>
/* BankForm 전용 스타일 - CSS specificity로 우선순위 확보 */

/* coolbar.js의 동적 스타일보다 먼저 로드되어야 하므로
   더 구체적인 선택자로 우선순위를 높임 */

/* 1. 라벨 셀 스타일 - 청록색 배경에 흰색 글씨 */
body.coolBar table td.Left1 {
    font-size: 10pt;
    color: #FFFFFF;
    font-weight: bold;
    background-color: #408080;
}

/* 2. 전체 페이지 배경 - 밝은 회색 */
body.coolBar {
    background: #E8E8E8;
    /* coolbar.js의 background: buttonface를 덮어씀 */
}

/* 3. 테이블 배경 - 흰색 */
body.coolBar table {
    background-color: #ffffff;
    border: 0;
}

/* 4. 일반 td 셀 배경 - 흰색 */
body.coolBar table td {
    background-color: #ffffff;
    padding: 8px;
}

/* 5. 입력 필드와 textarea 배경 - 흰색, 스크린샷 기준 폭 조정 */
body.coolBar table td input[type="text"] {
    background-color: #ffffff;
    border: 1px solid #cccccc;
    padding: 5px;
    width: 200px; /* 은행명, 예금주, 계좌번호 입력 필드 폭 */
}

body.coolBar table td textarea {
    font-family: 굴림;
    font-size: 9pt;
    background-color: #ffffff;
    border: 1px solid #cccccc;
    padding: 5px;
    width: 350px; /* 견적안내 TEXT 입력 필드 폭 */
    height: 70px; /* 높이 조정 */
}

/* 6. 라디오 버튼은 투명 배경 유지 */
body.coolBar table td input[type="radio"] {
    background-color: transparent;
}

/* 7. bgcolor 속성이 있는 td는 인라인 스타일 우선 (청록색 라벨) */
body.coolBar table td[bgcolor='#408080'] {
    background-color: #408080;
}

body.coolBar table td[bgcolor='#484848'] {
    background-color: #484848;
}

    </style>
    <script>
        self.moveTo(0, 0);
        self.resizeTo(680, 500);

        function validateForm() {
            var f = document.myForm;

            if (f.BankName.value.trim() == "") {
                alert("은행명을 입력하여 주세요!!");
                f.BankName.focus();
                return false;
            }

            if (f.TName.value.trim() == "") {
                alert("예금주를 입력하여 주세요!!");
                f.TName.focus();
                return false;
            }

            if (f.BankNo.value.trim() == "") {
                alert("계좌번호를 입력하여 주세요!!");
                f.BankNo.focus();
                return false;
            }
            return true;
        }
    </script>
</head>

<body class='coolBar'>
    <table border=0 align=center width=100% cellpadding=5 cellspacing=5>
        <form name='myForm' method='post' onsubmit='return validateForm()' action='<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>'>
            <input type="hidden" name='mode' value='BankModifyOk'>

            <tr>
                <td colspan=2 bgcolor='#484848'>
                    <font color=white><b>&nbsp;&nbsp;▒ 교정시안 비밀번호 기능 수정 ▒▒▒▒▒</b></font>
                </td>
            </tr>

            <tr>
                <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>사용여부&nbsp;&nbsp;</td>
                <td>
                    <input type="radio" name="SignMMk" value='yes' <?php echo  ($View_SignMMk == "yes") ? "checked" : "" ?>>YES
                    <input type="radio" name="SignMMk" value='no' <?php echo  ($View_SignMMk == "no") ? "checked" : "" ?>>NO
                </td>
            </tr>

            <tr>
                <td colspan=2 bgcolor='#484848'>
                    <font color=white><b>&nbsp;&nbsp;▒ 입금은행 수정 ▒▒▒▒▒</b></font>
                </td>
            </tr>

            <tr>
                <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>은행명&nbsp;&nbsp;</td>
                <td><input type="text" name="BankName" size=20 maxlength='200' value='<?php echo  htmlspecialchars($View_BankName) ?>'></td>
            </tr>

            <tr>
                <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>예금주&nbsp;&nbsp;</td>
                <td><input type="text" name="TName" size=20 maxlength='200' value='<?php echo  htmlspecialchars($View_TName) ?>'></td>
            </tr>

            <tr>
                <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>계좌번호&nbsp;&nbsp;</td>
                <td><input type="text" name="BankNo" size=40 maxlength='200' value='<?php echo  htmlspecialchars($View_BankNo) ?>'></td>
            </tr>

            <tr>
                <td colspan=2 bgcolor='#484848'>
                    <font color=white><b>&nbsp;&nbsp;▒ 견적안내 하단 TEXT 내용 수정 ▒▒▒▒▒</b><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;*주의사항: <b>'</b> 외 따옴표 및 <b>"</b> 쌍 따옴표 입력 불가</font>
                </td>
            </tr>

            <?php
            if (!empty($ConDb_A)) {
                $Si_LIST_script = explode(":", $ConDb_A);
                foreach ($Si_LIST_script as $index => $label) {
                    $tempVar = "View_ContText_" . $index;
                    $get_tempTwo = isset($$tempVar) ? htmlspecialchars($$tempVar) : '';
            ?>
                    <tr>
                        <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right><?php echo  htmlspecialchars($label) ?>&nbsp;&nbsp;</td>
                        <td><textarea name="ContText_<?php echo  $index ?>" rows="4" cols="58"><?php echo  $get_tempTwo ?></textarea></td>
                    </tr>
            <?php
                }
            }
            ?>

            <tr>
                <td>&nbsp;&nbsp;</td>
                <td>
                    <input type='submit' value=' 수정 합니다.'>
                </td>
            </tr>
        </form>
    </table>
    <br>
</body>
<?php
}
?>

<?php
if ($mode == "BankModifyOk") { ////////////////////////////////////////////////////////////////////
    // 디버깅: POST 데이터 확인 (개발 환경에서만 사용)
    // error_log("POST Data: " . print_r($_POST, true));

    // ContText 데이터 배열로 수집
    $contTextData = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'ContText_') === 0) {
            $contTextData[$key] = $value;
        }
    }

    // POST 데이터 검증
    if (empty($SignMMk) && empty($BankName) && empty($TName) && empty($BankNo)) {
        echo "<script>
                alert('입력된 데이터가 없습니다. 폼을 다시 작성해주세요.');
                history.go(-1);
              </script>";
        exit;
    }

    // 파일 쓰기 준비
    $content = "<?php\n";
    $content .= "\$View_SignMMk=\"" . addslashes($SignMMk) . "\";\n";
    $content .= "\$View_BankName=\"" . addslashes($BankName) . "\";\n";
    $content .= "\$View_TName=\"" . addslashes($TName) . "\";\n";
    $content .= "\$View_BankNo=\"" . addslashes($BankNo) . "\";\n";

    // ContText 필드들 처리
    if (!empty($ConDb_A)) {
        $Si_LIST_script = explode(":", $ConDb_A);
        foreach ($Si_LIST_script as $index => $value) {
            $tempVar = "ContText_" . $index;
            // POST 데이터에서 직접 가져오기
            $get_tempTwo = isset($contTextData[$tempVar]) ? addslashes($contTextData[$tempVar]) : '';
            $content .= "\$View_ContText_{$index}=\"" . $get_tempTwo . "\";\n";
        }
    }

    $content .= "?>";

    // 파일 쓰기 실행 및 오류 체크
    $write_result = file_put_contents($T_DirFole, $content);

    if ($write_result === false) {
        echo "<script>
                alert('파일 저장 실패! 경로: $T_DirFole\\n권한을 확인해주세요.');
                history.go(-1);
              </script>";
        exit;
    }

    // 리디렉션 및 알림 메시지 출력
    echo "<script>
            alert('수정 완료....*^^*');
            window.location.href = '" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=BankForm';
          </script>";
    exit;
}
?>

 
<?php
if ($mode == "OrderView") {
    include "../title.php";

    // 데이터베이스 연결은 이미 파일 상단에서 완료됨

    if (!empty($no)) {
        // ✅ Step 1: 기준 주문 정보 조회
        $stmt = $db->prepare("SELECT * FROM mlangorder_printauto WHERE no = ?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            echo "❌ 주문 정보를 찾을 수 없습니다.";
            exit;
        }

        // ✅ Step 2: 같은 장바구니(같은 초 + 연속 주문번호)의 주문을 모두 조회
        $base_date = $row['date'];
        $base_no = intval($row['no']);

        // 같은 초 + 주문번호 ±50 범위 조회 (장바구니 그룹핑)
        $group_stmt = $db->prepare("
            SELECT * FROM mlangorder_printauto
            WHERE date = ?
            AND no BETWEEN ? AND ?
            ORDER BY no ASC
        ");
        $no_min = $base_no - 50;
        $no_max = $base_no + 50;
        $group_stmt->bind_param("sii", $base_date, $no_min, $no_max);
        $group_stmt->execute();
        $group_result = $group_stmt->get_result();

        // 배열로 저장
        $order_rows = [];
        while ($group_row = $group_result->fetch_assoc()) {
            $order_rows[] = $group_row;
        }
        $group_stmt->close();

        // ✅ Step 3: 그룹 내 모든 주문의 상태를 업데이트 (OrderStyle이 "2"일 경우만)
        foreach ($order_rows as $order_row) {
            if ($order_row['OrderStyle'] == "2") {
                $update_stmt = $db->prepare("UPDATE mlangorder_printauto SET OrderStyle = '3' WHERE no = ?");
                $update_no = $order_row['no'];
                $update_stmt->bind_param("i", $update_no);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }

        // 페이지 새로고침 (한 번만)
        if (count($order_rows) > 0 && $order_rows[0]['OrderStyle'] == "2") {
            echo "<script>if(opener && opener.parent) { opener.parent.location.href = opener.parent.location.href.split('?')[0]; }</script>";
        }
    } else {
        echo "❌ 주문 번호가 제공되지 않았습니다.";
    }

?>

    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap');
        
        a.file:link, a.file:visited {
            font-family: '굴림'; font-size: 10pt; color: #336699; line-height: 130%; text-decoration: underline;
        }
        a.file:hover, a.file:active {
            font-family: '굴림'; font-size: 10pt; color: #333333; line-height: 130%; text-decoration: underline;
        }
        
        /* Admin OrderView 모던 스타일 */
        body {
            font-family: 'Noto Sans KR', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            margin: 0;
            padding: 15px;
            min-height: 100vh;
            font-size: 14px;
        }

        .admin-container {
            max-width: 700px;
            width: calc(100vw - 30px);
            min-height: 780px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            overflow: visible;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .admin-header {
            width: 100%;
            box-sizing: border-box;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ffffff;
            padding: 15px 25px;
            border-bottom: 2px solid #3498db;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            border-radius: 12px 12px 0 0;
            align-self: stretch;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-header .order-info {
            margin-top: 8px;
            opacity: 1;
            font-size: 0.85rem;
            color: #ffffff;
            font-weight: 500;
        }

        .admin-content {
            width: 100%;
            box-sizing: border-box;
            padding: 15px 25px;
            background: #f8f9fa;
            min-height: 680px;
            overflow-y: visible;
        }

        .info-grid {
            width: 100%;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
        }

        .info-card h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .form-section {
            width: 100%;
            box-sizing: border-box;
            background: white;
            border-radius: 8px;
            padding: 12px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
            margin-top: 8px;
        }

        .form-section h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 20px;
            margin-bottom: 18px;
            align-items: center;
        }

        .form-label {
            font-family: 'Noto Sans KR', sans-serif;
            font-weight: 600;
            color: #495057;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .form-input {
            font-family: 'Noto Sans KR', sans-serif;
            padding: 12px 18px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            min-width: 200px;
        }

        .form-input:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        .btn-group {
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
        }

        .btn {
            padding: 12px 25px;
            margin: 0 10px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108,117,125,0.3);
        }

        /* 파일 섹션 스타일 개선 */
        .file-section {
            width: 100%;
            box-sizing: border-box;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
            margin-top: 20px;
        }

        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .form-label {
                text-align: left;
            }
            
            .admin-content {
                padding: 20px;
            }
        }

        /* 기존 테이블 스타일 개선 */
        /* ⚠️ 주문 상품 테이블은 제외 (OrderFormOrderTree.php의 인라인 스타일 우선) */
        table:not(#order-products-table) {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        td:not(#order-products-table td):not(#order-products-table th) {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }

        /* 🎯 주문 상품 테이블 전용 스타일 - 최고 우선순위로 강제 적용 */
        #order-products-table {
            table-layout: fixed !important;
        }

        #order-products-table th,
        #order-products-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 텍스트 영역 스타일 개선 */
        textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-family: 'Noto Sans KR', sans-serif;
            font-size: 0.95rem;
            line-height: 1.5;
            resize: vertical;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        textarea:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>

    <?php
    // ✅ 첨부 파일 섹션 HTML을 미리 생성 (OrderFormOrderTree.php 내부에서 버튼 전에 출력됨)
    if (!empty($no) && $row) {
        ob_start();
?>
            <!-- 첨부 파일 섹션 -->
            <div class="file-section" style="padding: 12px; margin: 15px 0; background: white; border-radius: 8px; border: 1px solid #e9ecef;">
            <h3 style="color: #2c3e50; margin-bottom: 8px; font-size: 0.95rem;">📎 첨부 파일</h3>
            <p style="color: #6c757d; margin-bottom: 10px; font-size: 0.8rem;">파일명을 클릭하시면 다운로드됩니다.</p>
            <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #e9ecef;">

                    
                    <?php
                    if ($row) {
                        echo "<strong>📎 업로드된 파일:</strong><br>";

                        $total_file_count = 0;
                        $displayed_files = []; // 중복 방지용

                        // ✅ Step 1: uploaded_files JSON 파싱 (한 번만)
                        $uploaded_files = [];
                        if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
                            $decoded = json_decode($row['uploaded_files'], true);
                            if (is_array($decoded)) {
                                $uploaded_files = $decoded;
                            }
                        }

                        // ✅ Step 2: JSON에서 파일 표시 (StandardUploadHandler 표준화된 주문)
                        if (count($uploaded_files) > 0) {
                            echo "<div style='margin-top: 10px; color: #28a745; font-weight: bold;'>✅ 표준화된 파일 정보:</div>";

                            foreach ($uploaded_files as $file_info) {
                                if (isset($file_info['original_name']) && isset($file_info['saved_name'])) {
                                    $total_file_count++;

                                    // 파일 크기 계산 (path가 있으면 실제 파일에서, 없으면 JSON size 사용)
                                    $file_size_mb = 0;
                                    if (isset($file_info['path']) && file_exists($file_info['path'])) {
                                        $file_size_mb = round(filesize($file_info['path']) / 1024 / 1024, 2);
                                    } elseif (isset($file_info['size'])) {
                                        $file_size_mb = round($file_info['size'] / 1024 / 1024, 2);
                                    }

                                    // 대표 파일 아이콘
                                    $icon = ($file_info['saved_name'] == $row['ThingCate']) ? "📌" : "📄";

                                    // 다운로드 링크 (download.php가 no와 downfile로 자동 경로 탐지)
                                    echo "$icon <a href='download.php?no=$no&downfile=" . urlencode($file_info['saved_name']) . "' class='file'>";
                                    echo htmlspecialchars($file_info['original_name']) . "</a> ({$file_size_mb}MB)";

                                    if ($file_info['saved_name'] == $row['ThingCate']) {
                                        echo " <span style='color: #28a745; font-weight: bold;'>(대표 파일)</span>";
                                    }
                                    echo "<br>";

                                    $displayed_files[] = $file_info['saved_name'];
                                }
                            }
                        }

                        // ✅ Step 3: 폴백 - ImgFolder 디렉토리 스캔 (레거시 주문 또는 JSON 없는 경우)
                        if ($total_file_count == 0 && !empty($row['ImgFolder'])) {
                            // ImgFolder 경로 결정
                            $dir_path = '';
                            if (strpos($row['ImgFolder'], '_MlangPrintAuto_') === 0) {
                                // 새 표준 경로: _MlangPrintAuto_*_index.php/YYYY/MMDD/...
                                $dir_path = "../../ImgFolder/" . $row['ImgFolder'];
                            } elseif (strpos($row['ImgFolder'], '/') === 0) {
                                // 절대 경로
                                $dir_path = $row['ImgFolder'];
                            } else {
                                // 상대 경로
                                $dir_path = "../../" . $row['ImgFolder'];
                            }

                            if (is_dir($dir_path)) {
                                echo "<div style='margin-top: 10px; color: #ff9800; font-weight: bold;'>📁 레거시 ImgFolder:</div>";

                                $files = scandir($dir_path);
                                foreach ($files as $file) {
                                    if ($file != "." && $file != ".." && is_file("$dir_path/$file")) {
                                        $total_file_count++;
                                        $file_size = filesize("$dir_path/$file");
                                        $file_size_mb = round($file_size / 1024 / 1024, 2);

                                        $icon = ($file == $row['ThingCate']) ? "📌" : "📄";

                                        echo "$icon <a href='download.php?no=$no&downfile=" . urlencode($file) . "' class='file'>";
                                        echo htmlspecialchars($file) . "</a> ({$file_size_mb}MB)";

                                        if ($file == $row['ThingCate']) {
                                            echo " <span style='color: #28a745; font-weight: bold;'>(대표 파일)</span>";
                                        }
                                        echo "<br>";

                                        $displayed_files[] = $file;
                                    }
                                }
                            }
                        }

                        // ✅ Step 4: 추가 폴백 - mlangorder_printauto/upload/{no} (초기 레거시 경로)
                        if ($total_file_count == 0) {
                            $legacy_dir = "../../mlangorder_printauto/upload/$no";
                            if (is_dir($legacy_dir)) {
                                echo "<div style='margin-top: 10px; color: #9e9e9e; font-weight: bold;'>🗂️ 초기 업로드 폴더:</div>";

                                $files = scandir($legacy_dir);
                                foreach ($files as $file) {
                                    if ($file != "." && $file != ".." && is_file("$legacy_dir/$file")) {
                                        $total_file_count++;
                                        $file_size = filesize("$legacy_dir/$file");
                                        $file_size_mb = round($file_size / 1024 / 1024, 2);

                                        $icon = ($file == $row['ThingCate']) ? "📌" : "📄";

                                        echo "$icon <a href='download.php?no=$no&downfile=" . urlencode($file) . "' class='file'>";
                                        echo htmlspecialchars($file) . "</a> ({$file_size_mb}MB)";

                                        if ($file == $row['ThingCate']) {
                                            echo " <span style='color: #28a745; font-weight: bold;'>(대표 파일)</span>";
                                        }
                                        echo "<br>";
                                    }
                                }
                            }
                        }

                        // ✅ 결과 표시
                        if ($total_file_count == 0) {
                            // ThingCate가 기본 패턴(제품명_타임스탬프.jpg)인지 확인
                            $is_default_pattern = !empty($row['ThingCate']) &&
                                                 preg_match('/^[^_]+_\d{14}\.(jpg|jpeg|png)$/i', $row['ThingCate']);

                            if ($is_default_pattern) {
                                // 파일 미업로드 주문
                                echo "<div style='margin-top: 10px; padding: 8px; background: #e8f5e9; border-left: 3px solid #4caf50;'>";
                                echo "📭 <strong>파일이 업로드되지 않은 주문입니다.</strong><br>";
                                echo "<small style='color: #2e7d32;'>고객이 파일 업로드 없이 주문을 완료했습니다. 필요 시 고객에게 파일 전송을 요청하세요.</small>";
                                echo "</div>";
                            } else {
                                // 파일이 있어야 하는데 찾을 수 없는 경우
                                echo "<div style='margin-top: 10px; padding: 8px; background: #fff3cd; border-left: 3px solid #ffc107;'>";
                                echo "⚠️ 업로드된 파일을 찾을 수 없습니다.<br>";
                                if (!empty($row['ThingCate'])) {
                                    echo "<small style='color: #856404;'>대표 파일명: " . htmlspecialchars($row['ThingCate']) . "</small>";
                                }
                                echo "</div>";
                            }
                        } else {
                            echo "<div style='margin-top: 10px; padding: 8px; background: #e3f2fd; border-left: 3px solid #2196f3; font-size: 0.9em;'>";
                            echo "💡 <strong>총 {$total_file_count}개 파일</strong> | 파일명을 클릭하면 다운로드됩니다.";
                            echo "</div>";
                        }
                    } else {
                        echo "❌ 주문 정보를 찾을 수 없습니다.";
                    }
                    ?>
            </div>
        </div> <!-- file-section 종료 -->
<?php
        $GLOBALS['file_section_html'] = ob_get_clean();
    } // end if (!empty($no) && $row)

    // OrderFormOrderTree.php가 $no를 덮어쓰므로 백업
    $original_no = $no;
    include "../../mlangorder_printauto/OrderFormOrderTree.php";
    // $no 복원
    $no = $original_no;
} // End of OrderView mode
?>


<?php
if ($mode == "SinForm") { /////////////////////////////////////////////////////////////////////////
    include "../title.php";

    // 전화번호 조회 (교정확인용)
    $db->set_charset("utf8");
    $stmt = $db->prepare("SELECT Hendphone, pass FROM mlangorder_printauto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->bind_result($ViewSignTy_Hendphone, $ViewSignTy_pass);
    $stmt->fetch();
    $stmt->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>교정/시안 등록</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Noto Sans KR', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        body.popup-mode {
            padding: 20px;
            display: block;
        }
        .form-container {
            width: 100%;
            background: #fff;
            overflow: hidden;
            animation: slideIn 0.3s ease-out;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        body.popup-mode .form-container {
            width: 350px;
            min-width: 350px;
            max-width: 350px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            display: block;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .form-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            padding: 16px;
            text-align: center;
        }
        .form-header h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .form-header .subtitle {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 4px;
        }
        .form-body {
            padding: 18px 18px 25px 18px;
            flex: 1;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Noto Sans KR', sans-serif;
            transition: all 0.2s ease;
            background: #f9fafb;
        }
        .form-group input[type="text"]:focus,
        .form-group select:focus {
            border-color: #667eea;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.15);
        }
        .form-group input[type="file"] {
            padding: 6px;
            cursor: pointer;
            font-size: 11px;
        }
        .form-group .hint {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        .btn-submit {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Noto Sans KR', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        }
        .btn-close {
            flex: 1;
            padding: 12px;
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Noto Sans KR', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-close:hover {
            background: #e5e7eb;
        }
        .form-footer {
            padding: 0 18px 18px 18px;
        }
    </style>
    <script>
        function MlangFriendSiteCheckField() {
            var f = document.MlangFriendSiteInfo;
            if (f.photofile.value.trim() === "") {
                alert("업로드할 이미지를 올려주시기 바랍니다.");
                f.photofile.focus();
                return false;
            }
            <?php
            include "$T_DirFole";
            if ($View_SignMMk == "yes") {
            ?>
                if (f.pass.value == "") {
                    alert("사용할 비밀번호를 입력해 주시기 바랍니다.");
                    f.pass.focus();
                    return false;
                }
            <?php } ?>
            return true;
        }

        // 창 크기 자동 조정 (팝업 모드에서만)
        window.onload = function() {
            var isModal = window.location.search.indexOf('modal=1') > -1;
            if (!isModal) {
                document.body.classList.add('popup-mode');
                try {
                    window.resizeTo(420, 520);
                    window.moveTo(100, 100);
                } catch(e) {
                    console.log('창 크기 조정 불가: ', e);
                }
            }
        };
        
        function closeModal() {
            if (window.parent !== window) {
                window.parent.postMessage('closeSinModal', '*');
            } else {
                window.close();
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>📋 교정/시안 <?php echo $ModifyCode ? '수정' : '등록'; ?></h2>
            <div class="subtitle">주문번호: <?php echo htmlspecialchars($no ?? ''); ?></div>
        </div>

        <div class="form-body">
            <form name="MlangFriendSiteInfo" method="post" enctype="multipart/form-data"
                  onsubmit="return MlangFriendSiteCheckField()"
                  action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <input type="hidden" name="mode" value="SinFormModifyOk">
                <input type="hidden" name="no" value="<?php echo htmlspecialchars($_GET['no'] ?? '') ?>">
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_GET['return_url'] ?? $_SERVER['HTTP_REFERER'] ?? 'admin.php') ?>">
                <?php if(isset($_GET['modal']) && $_GET['modal'] == '1'){ ?>
                <input type="hidden" name="modal" value="1">
                <?php } ?>
                <?php if(isset($ModifyCode) && !empty($ModifyCode)){ ?>
                <input type="hidden" name="ModifyCode" value="ok">
                <?php } ?>
                <input type="hidden" name="photofileModify" value="ok">

                <div class="form-group">
                    <label>📁 이미지 파일 <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="photofile" accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="hint">JPG, PNG, GIF, PDF (최대 10MB)</div>
                </div>

                <div class="form-group">
                    <label>📱 휴대폰 번호</label>
                    <input type="text" name="Hendphone" value="<?php echo htmlspecialchars($ViewSignTy_Hendphone ?? '') ?>" placeholder="010-1234-5678">
                    <div class="hint">고객 교정확인용 (뒷자리 4자리)</div>
                </div>

                <?php if ($View_SignMMk == "yes") { ?>
                <div class="form-group">
                    <label>🔒 비밀번호</label>
                    <input type="text" name="pass" value="<?php echo htmlspecialchars($ViewSignTy_pass ?? '') ?>">
                </div>
                <?php } ?>

                <div class="btn-group">
                    <button type="submit" class="btn-submit">
                        <?php echo $ModifyCode ? '✏️ 수정하기' : '📤 등록하기'; ?>
                    </button>
                    <?php if(isset($_GET['modal']) && $_GET['modal'] == '1'){ ?>
                    <button type="button" class="btn-close" onclick="closeModal()">
                        ✕ 닫기
                    </button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
}
?>

<?php
// 업로드 처리 (SinFormModifyOk)
if ($mode == "SinFormModifyOk") { /////////////////////////////////////////////////////////////////
    if ($ModifyCode == "ok") {
        $TOrderStyle = "7";
    } else {
        $TOrderStyle = "6";
    }
    $ModifyCode = intval($no); // 보안 강화를 위해 정수형 변환

    // 데이터베이스 연결 (mysqli)
    // $db는 이미 ../../db.php에서 생성됨
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // `mlangorder_printauto` 테이블에서 기존 파일명 가져오기
    $stmt = $db->prepare("SELECT ThingCate FROM mlangorder_printauto WHERE no = ?");
    $stmt->bind_param("i", $ModifyCode);
    $stmt->execute();
    $stmt->bind_result($GF_upfile);
    $stmt->fetch();
    $stmt->close();

    if (empty($GF_upfile)) {
        echo "<p align=center><b>DB에 $ModifyCode 의 등록 자료가 없음.</b></p>";
        exit;
    }

    // 자료를 업로드할 폴더를 생성 (절대 경로 사용)
    $dir = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload/$no";
    
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // 새로운 파일 업로드 처리
    $photofileNAME = $GF_upfile; // 기존 파일 유지
    if (!empty($_FILES['photofile']['name'])) {
        $upload_dir = $dir . "/";
        $file_name = basename($_FILES['photofile']['name']);
        $file_tmp_path = $_FILES['photofile']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "pdf"];
        $max_file_size = 2 * 1024 * 1024; // 2MB 제한

        // 파일 크기 및 확장자 검사
        if ($_FILES['photofile']['size'] > $max_file_size) {
            die("<script>alert('파일 크기가 너무 큽니다. (최대: 2MB)'); history.go(-1);</script>");
        }
        if (!in_array($file_ext, $allowed_extensions)) {
            die("<script>alert('허용되지 않은 파일 형식입니다. (jpg, jpeg, png, gif, pdf 만 가능)'); history.go(-1);</script>");
        }

        // 새로운 파일명 생성 (중복 방지)
        $new_file_name = date("YmdHis") . "_" . uniqid() . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;

        // 기존 파일 삭제 후 새로운 파일 저장
        if (!empty($GF_upfile) && file_exists($upload_dir . $GF_upfile)) {
            unlink($upload_dir . $GF_upfile);
        }
        if (!move_uploaded_file($file_tmp_path, $target_file)) {
            die("<script>alert('파일 이동 실패! 경로: $target_file'); history.go(-1);</script>");
        }

        $photofileNAME = $new_file_name; // 업로드한 파일명을 DB에 저장할 변수로 설정
    }

    // DB 업데이트 (전화번호 포함)
    $Hendphone = isset($_POST['Hendphone']) ? $_POST['Hendphone'] : '';
    $stmt = $db->prepare("UPDATE mlangorder_printauto SET OrderStyle=?, ThingCate=?, pass=?, Hendphone=? WHERE no=?");
    $stmt->bind_param("ssssi", $TOrderStyle, $photofileNAME, $pass, $Hendphone, $no);
    
    if (!$stmt->execute()) {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    $return_url = isset($_POST['return_url']) && !empty($_POST['return_url']) ? $_POST['return_url'] : 'admin.php';
    $isModal = isset($_GET['modal']) || isset($_POST['modal']) || (strpos($return_url, 'modal=1') !== false);
    
    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>완료</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .alert-box {
            background: #fff;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 350px;
            width: 100%;
        }
        .alert-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .alert-msg {
            font-size: 15px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .alert-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-family: 'Noto Sans KR', sans-serif;
        }
        .alert-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class='alert-box'>
        <div class='alert-icon'>✅</div>
        <div class='alert-msg'>정보를 정상적으로<br>수정하였습니다.</div>
        <button class='alert-btn' onclick=\"closeWindow()\">확인</button>
    </div>
    <script>
        var isModal = " . ($isModal ? 'true' : 'false') . ";
        
        function closeWindow() {
            if (isModal && window.parent !== window) {
                window.parent.postMessage('closeSinModal', '*');
            } else {
                window.location.href = '" . htmlspecialchars($return_url, ENT_QUOTES) . "';
            }
        }
        
        if (!isModal) {
            window.resizeTo(390, 450);
        }
        
        setTimeout(function() {
            closeWindow();
        }, 1500);
    </script>
</body>
</html>";

    $stmt->close();
    // // $db->close(); // 스크립트 끝에서 자동으로 닫힘 // 연결 유지
    exit;
}

?>



<?php
if ($mode == "AdminMlangOrdert") { /////////////////////////////////////////////////////////////////
    include "../title.php";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>교정/시안 등록</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Noto Sans KR', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .form-container {
            width: 350px;
            min-width: 350px;
            max-width: 350px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .form-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            padding: 16px;
            text-align: center;
        }
        .form-header h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .form-header .subtitle {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 4px;
        }
        .form-body {
            padding: 18px;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-group label .required {
            color: #ef4444;
            margin-left: 2px;
        }
        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Noto Sans KR', sans-serif;
            transition: all 0.2s ease;
            background: #f9fafb;
        }
        .form-group input[type="text"]:focus,
        .form-group select:focus {
            border-color: #667eea;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.15);
        }
        .form-group input[type="file"] {
            padding: 6px;
            cursor: pointer;
            font-size: 11px;
        }
        .form-group input[type="file"]:hover {
            border-color: #667eea;
            background: #fff;
        }
        .form-group .hint {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .form-group .hint::before {
            content: "💡";
            font-size: 10px;
        }
        .radio-group {
            display: flex;
            gap: 14px;
            margin-bottom: 8px;
        }
        .radio-item {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        .radio-item input[type="radio"] {
            width: 14px;
            height: 14px;
            accent-color: #667eea;
            cursor: pointer;
        }
        .radio-item span {
            font-size: 12px;
            color: #374151;
        }
        .dynamic-select {
            margin-top: 8px;
            padding: 8px;
            background: #f3f4f6;
            border-radius: 6px;
            min-height: 36px;
        }
        .dynamic-select select,
        .dynamic-select input[type="text"] {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Noto Sans KR', sans-serif;
            background: #fff;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Noto Sans KR', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
            letter-spacing: 0.5px;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .icon-label {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        @media (max-width: 400px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-body {
                padding: 14px;
            }
        }
    </style>
    <script>
        function MlangFriendSiteCheckField() {
            var f = document.MlangFriendSiteInfo;

            if ((!f.MlangFriendSiteInfoS[0].checked) && (!f.MlangFriendSiteInfoS[1].checked)) {
                alert('종류를 선택해주세요');
                return false;
            }
            if (f.name.value == "") {
                alert("주문자 성함을 입력해주세요");
                f.name.focus();
                return false;
            }
            if (f.Designer.value == "") {
                alert("담당 디자이너를 입력해주세요");
                f.Designer.focus();
                return false;
            }
            if (f.OrderStyle.value == "0") {
                alert("결과 처리를 선택해주세요");
                f.OrderStyle.focus();
                return false;
            }
            if (f.date.value == "") {
                alert("주문날짜을 입력해주세요\n\n마우스로 콕 찍으면 자동입력창이 나옵니다.");
                f.date.focus();
                return false;
            }
            if (f.photofile.value == "") {
                alert("업로드할 이미지를 올려주시기 바랍니다.");
                f.photofile.focus();
                return false;
            }
            // 디자이너 이름 저장 (다음 입력 시 기본값으로 사용)
            localStorage.setItem('lastDesigner', f.Designer.value);
            return true;
        }

        function MlangFriendSiteInfocheck() {
            let f = document.MlangFriendSiteInfo;
            let thingInputArea = document.getElementById('Mlang_go');

            if (f.MlangFriendSiteInfoS[0].checked) {
                fetch("fetch_categories.php")
                .then(response => response.json())
                .then(data => {
                    let selectHTML = "<select name='Thing' onchange='inThing(this.value)'>";
                    selectHTML += "<option value=''>-- 선택하세요 --</option>";
                    data.forEach(category => {
                        selectHTML += `<option value='${category}'>${category}</option>`;
                    });
                    selectHTML += "</select>";
                    thingInputArea.innerHTML = selectHTML;
                })
                .catch(error => console.error("Error:", error));
            } else if (f.MlangFriendSiteInfoS[1].checked) {
                thingInputArea.innerHTML = "<input type='text' name='Thing' placeholder='품목명을 직접 입력하세요' onblur='inThing(this.value)'>";
            }
        }

        function inThing(value) {
            document.MlangFriendSiteInfo.ThingNo.value = value;
        }

        // 창 크기 자동 조정 - resizeTo 제거 (left01.php에서 설정한 크기 사용)
        window.onload = function() {
            try {
                // window.resizeTo(390, 630); // 제거: left01.php의 390x700 설정 우선
                window.moveTo(100, 100);
            } catch(e) {
                console.log('창 위치 조정 불가: ', e);
            }
        };
    </script>
    <script src="../js/exchange.js"></script>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>📋 교정/시안 등록</h2>
            <div class="subtitle">Proof & Design Upload</div>
        </div>

        <div class="form-body">
            <form name="MlangFriendSiteInfo" method="post" enctype="multipart/form-data"
                  onsubmit="return MlangFriendSiteCheckField()"
                  action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>">

                <input type="hidden" name="mode" value="AdminMlangOrdertOk">
                <input type="hidden" name="no" value="<?php echo htmlspecialchars($no ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'admin.php') ?>">
                <input type="hidden" name="ThingNo" value="">
                <?php if (!empty($ModifyCode)) { ?>
                    <input type="hidden" name="ModifyCode" value="ok">
                <?php } ?>

                <!-- 종류 선택 -->
                <div class="form-group">
                    <label><span class="icon-label">📁 종류 <span class="required">*</span></span></label>
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="MlangFriendSiteInfoS" value="select" onclick="MlangFriendSiteInfocheck()">
                            <span>선택박스</span>
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="MlangFriendSiteInfoS" value="input" onclick="MlangFriendSiteInfocheck()">
                            <span>직접입력</span>
                        </label>
                    </div>
                    <div class="dynamic-select" id="Mlang_go">
                        <span style="color:#9ca3af; font-size:13px;">↑ 위에서 종류를 선택하세요</span>
                    </div>
                </div>

                <!-- 주문인/디자이너 -->
                <div class="form-row">
                    <div class="form-group">
                        <label><span class="icon-label">👤 주문인 성함 <span class="required">*</span></span></label>
                        <input type="text" name="name" placeholder="홍길동" required>
                    </div>
                    <div class="form-group">
                        <label><span class="icon-label">🎨 담당 디자이너 <span class="required">*</span></span></label>
                        <input type="text" name="Designer" id="designerInput" placeholder="디자이너명" required>
                        <script>
                            // 이전에 입력한 디자이너 불러오기
                            (function() {
                                var saved = localStorage.getItem('lastDesigner');
                                if (saved) {
                                    document.getElementById('designerInput').value = saved;
                                }
                            })();
                        </script>
                    </div>
                </div>

                <!-- 휴대폰 번호 -->
                <div class="form-group">
                    <label><span class="icon-label">📱 휴대폰 번호</span></label>
                    <input type="text" name="Hendphone" placeholder="010-1234-5678">
                    <div class="hint">고객 교정확인용 - 뒷자리 4자리로 인증</div>
                </div>

                <!-- 결과처리/주문날짜 -->
                <div class="form-row">
                    <div class="form-group">
                        <label><span class="icon-label">⚙️ 결과 처리 <span class="required">*</span></span></label>
                        <select name="OrderStyle" required>
                            <option value="0">-- 선택 --</option>
                            <option value="6" selected>📝 시안</option>
                            <option value="7">✅ 교정</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><span class="icon-label">📅 주문 날짜 <span class="required">*</span></span></label>
                        <input type="text" name="date" value="<?php echo date('Y-m-d'); ?>" onclick="Calendar(this);" readonly>
                    </div>
                </div>

                <!-- 파일 업로드 -->
                <div class="form-group">
                    <label><span class="icon-label">📎 이미지 자료 <span class="required">*</span></span></label>
                    <input type="file" name="photofile" accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="hint">jpg, jpeg, png, gif, pdf 파일만 업로드 가능</div>
                </div>

                <!-- 제출 버튼 -->
                <?php if (!empty($ModifyCode)) { ?>
                    <button type="submit" class="btn-submit">✏️ 수정하기</button>
                <?php } else { ?>
                    <button type="submit" class="btn-submit">📤 등록하기</button>
                <?php } ?>
            </form>
        </div>
    </div>
</body>
</html>
<?php
}
?>

<?php
if ($mode == "AdminMlangOrdertOk") { ////////////////////////////////////////////////////////////////
    // echo "<pre>";
    // print_r($_POST);  // 입력된 값 확인
    // echo "</pre>";
    // exit();
    // 데이터베이스 연결
    // $db는 이미 ../../db.php에서 생성됨
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    $ToTitle = $_POST['ThingNo'] ?? '';
    include "../../mlangprintauto/ConDb.php";

    $ThingNoOkp = empty($_POST['ThingNoOkp']) ? $ToTitle : $_POST['View_TtableB'];
    // if(!$ThingNoOkp){$ThingNoOkp="$ThingNo";}else{$ThingNoOkp="$View_TtableB";}

    // 새로운 주문번호 생성
    $Table_result = $db->query("SELECT MAX(no) FROM mlangorder_printauto");
    if (!$Table_result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    $row = $Table_result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 업로드 폴더 생성 (절대 경로 사용)
    $upload_base = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload';
    $dir = $upload_base . '/' . $new_no;

    // 베이스 폴더 확인 및 생성
    if (!is_dir($upload_base)) {
        if (!mkdir($upload_base, 0777, true)) {
            die("<script>alert('업로드 베이스 폴더 생성 실패!'); history.go(-1);</script>");
        }
    }

    // 주문별 폴더 생성
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true)) {
            die("<script>alert('업로드 폴더 생성 실패! 폴더: " . $dir . "'); history.go(-1);</script>");
        }
    }

    //파일 업로드 처리
    $photofileNAME = "";
    if (!empty($_FILES['photofile']['name'])) {
        $file_name = basename($_FILES['photofile']['name']);
        $file_tmp_path = $_FILES['photofile']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "pdf"];
        $max_file_size = 10 * 1024 * 1024; // 10MB로 증가

        if ($_FILES['photofile']['size'] > $max_file_size) {
            die("<script>alert('파일 크기가 너무 큽니다. (최대: 10MB)'); history.go(-1);</script>");
        }
        if (!in_array($file_ext, $allowed_extensions)) {
            die("<script>alert('허용되지 않은 파일 형식입니다. (jpg, jpeg, png, gif, pdf 만 가능)'); history.go(-1);</script>");
        }

        $new_file_name = date("YmdHis") . "_" . uniqid() . "." . $file_ext;
        $target_file = $dir . "/" . $new_file_name;

        // 임시 파일 확인
        if (!is_uploaded_file($file_tmp_path)) {
            die("<script>alert('업로드된 파일이 없습니다!'); history.go(-1);</script>");
        }

        if (!move_uploaded_file($file_tmp_path, $target_file)) {
            $error_msg = '파일 이동 실패! ';
            $error_msg .= '폴더 쓰기 가능: ' . (is_writable($dir) ? 'Y' : 'N') . ', ';
            $error_msg .= '폴더 존재: ' . (is_dir($dir) ? 'Y' : 'N');
            die("<script>alert('" . $error_msg . "'); history.go(-1);</script>");
        }

        $photofileNAME = $new_file_name;
    }

    // INSERT 데이터 준비
    $Type_1 = isset($_POST['Type_1']) ? $_POST['Type_1'] : "";
    $Type_2 = isset($_POST['Type_2']) ? $_POST['Type_2'] : "";
    $Type_3 = isset($_POST['Type_3']) ? $_POST['Type_3'] : "";
    $Type_4 = isset($_POST['Type_4']) ? $_POST['Type_4'] : "";
    $Type_5 = isset($_POST['Type_5']) ? $_POST['Type_5'] : "";
    $Type_6 = isset($_POST['Type_6']) ? $_POST['Type_6'] : "";

    $TypeOne = trim("$Type_1 $Type_2 $Type_3 $Type_4 $Type_5 $Type_6"); // 합쳐서 사용

    $posted_date = isset($_POST['date']) ? $_POST['date'] : '';
    if (!empty($posted_date) && strlen($posted_date) == 10) {
        $date = $posted_date . ' ' . date('H:i:s');
    } else {
        $date = date("Y-m-d H:i:s");
    }   
// `INSERT INTO` SQL 실행
$stmt = $db->prepare("INSERT INTO mlangorder_printauto 
    (no, Type, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, 
    name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, 
    cont, date, OrderStyle, ThingCate, pass, Gensu, Designer) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

if (!$stmt) {
    die("❌ SQL Prepare Error: " . $db->error);
}

// `bind_param()`에서 변수 개수 & 데이터 타입 맞추기
$stmt->bind_param(
    "isssdddddssssssssssssssssss",
$new_no,
$ThingNo, 
$ImgFolder, 
$TypeOne,
$money_1,
$money_2,	
$money_3,	
$money_4,	
$money_5,	
$OrderName,   
$email,
$zip, 
$zip1,
$zip2,
$phone,   
$Hendphone,
$delivery, 
$bizname,
$bank,
$bankname,
$cont, 
$date,
$OrderStyle,
$photofileNAME,
$pass,
$Gensu,
$Designer
);

if (!$stmt->execute()) {
    die("❌ SQL Execution Error: " . $stmt->error);
}

// 성공 메시지 및 주문 목록 페이지로 이동
$next_url = "orderlist.php";
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>완료</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .alert-box {
            background: #fff;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 350px;
            width: 100%;
        }
        .alert-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .alert-msg {
            font-size: 15px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .alert-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-family: 'Noto Sans KR', sans-serif;
        }
        .alert-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class='alert-box'>
        <div class='alert-icon'>✅</div>
        <div class='alert-msg'>주문이 정상적으로 등록되었습니다.<br>주문 목록으로 이동합니다.</div>
        <button class='alert-btn' onclick=\"window.location.href='" . $next_url . "'\">확인</button>
    </div>
    <script>
        window.resizeTo(390, 630);
        setTimeout(function() {
            window.location.href = '" . $next_url . "';
        }, 1500);
    </script>
</body>
</html>";

$stmt->close();
// $db->close(); // 스크립트 끝에서 자동으로 닫힘
exit;
}
?>

