<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
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
///////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "ModifyOk") { ////////////////////////////////////////////////////////////////////////////
    // 데이터베이스 연결
    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // SQL UPDATE 문 준비
    $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto 
        SET Type_1 = ?, name = ?, email = ?, zip = ?, zip1 = ?, zip2 = ?, phone = ?, Hendphone = ?, bizname = ?, 
            bank = ?, bankname = ?, cont = ?, Gensu = ? 
        WHERE no = ?");

    $stmt->bind_param(
        "sssssssssssssi", 
        $TypeOne, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, 
        $bank, $bankname, $cont, $Gensu, $no
    );

    if (!$stmt->execute()) {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    echo "<script>
            alert('정보를 정상적으로 수정하였습니다.');
            opener.parent.location.reload();
          </script>";

    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=$no");
    exit;

    $stmt->close();
    $db->close();
}
?>

<?php
if ($mode == "SubmitOk") { ////////////////////////////////////////////////////////////////////////////
    // 데이터베이스 연결
    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // 새로운 주문번호 생성
    $Table_result = $db->query("SELECT MAX(no) FROM MlangOrder_PrintAuto");
    if (!$Table_result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    $row = $Table_result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 업로드 폴더 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        chmod($dir, 0777);
    }

    // 현재 날짜 가져오기
    $date = date("Y-m-d H:i:s");

    // 데이터 삽입
    $stmt = $db->prepare("INSERT INTO MlangOrder_PrintAuto 
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
    $db->close();
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
.Left1 {
    font-size: 10pt;
    color: #000000; /* 글씨를 검은색으로 */
    font-weight: bold;
}

body {
    background-color: #f0f0f0; /* 전체 배경을 밝게 */
}

table {
    background-color: #ffffff; /* 테이블 배경을 흰색으로 */
    border: 1px solid #ccc;
}

td {
    background-color: #e6e6e6; /* 셀 배경을 더 밝게 */
    padding: 8px;
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
    // 파일 쓰기 준비
    $content = "<?php\n";
    $content .= "\$View_SignMMk=\"" . addslashes($SignMMk) . "\";\n";
    $content .= "\$View_BankName=\"" . addslashes($BankName) . "\";\n";
    $content .= "\$View_TName=\"" . addslashes($TName) . "\";\n";
    $content .= "\$View_BankNo=\"" . addslashes($BankNo) . "\";\n";

    // PHP 7 이상에서는 `split()`이 제거되었으므로 `explode()`로 변경
    if (!empty($ConDb_A)) {
        $Si_LIST_script = explode(":", $ConDb_A);
        foreach ($Si_LIST_script as $index => $value) {
            $tempVar = "ContText_" . $index;
            $get_tempTwo = isset($$tempVar) ? addslashes($$tempVar) : '';
            $content .= "\$View_ContText_${index}=\"" . $get_tempTwo . "\";\n";
        }
    }

    $content .= "?>";

    // 파일 쓰기 실행
    file_put_contents($T_DirFole, $content);

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
    
    // ✅ 데이터베이스 연결
    include "../../db.php";
    
    if (!empty($no)) {
        // ✅ 주문 정보 조회
        $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();  // ✅ 쿼리 완료 후 닫기
        
        // ✅ 주문 상태 업데이트 (OrderStyle이 "2"일 경우만)
        if ($row && $row['OrderStyle'] == "2") {
            $update_stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle = '3' WHERE no = ?");
            $update_stmt->bind_param("i", $no);
            if ($update_stmt->execute()) {
                echo "<script>opener.parent.location.reload();</script>";
            }
            $update_stmt->close();
        }
    } else {
        echo "❌ 주문 번호가 제공되지 않았습니다.";
    }

?>

    
    <style>
        a.file:link, a.file:visited {
            font-family: '굴림'; font-size: 10pt; color: #336699; line-height: 130%; text-decoration: underline;
        }
        a.file:hover, a.file:active {
            font-family: '굴림'; font-size: 10pt; color: #333333; line-height: 130%; text-decoration: underline;
        }
    </style>
    
    <?php include "../../MlangOrder_PrintAuto/OrderFormOrderTree.php"; ?>
    <br><br>
    
    <?php if (!empty($no)) { ?>
        <font style='font:bold; color:#336699;'>* 첨부 파일 *</font> 파일명을 클릭하시면 저장/보기를 하실 수 있습니다.<br>
        <table border=0 align=center width=100% cellpadding=5 cellspacing=0>
            <tr>
                <td height="20">

                    
                    <?php
                        include "../../db.php";
                     if ($row) {
                        // ✅ 파일명(ThingCate) 조회
                        $stmt_file = $db->prepare("SELECT ThingCate FROM MlangOrder_PrintAuto WHERE no = ?");
                        $stmt_file->bind_param("i", $no);
                        $stmt_file->execute();
                        $stmt_file->bind_result($ThingCate);
            
                        if ($stmt_file->fetch()) {
                            // ✅ ThingCate(파일명)이 있으면 다운로드 링크 추가
                            echo "<a href='download.php?no=$no&downfile=" . urlencode($ThingCate) . "'>$ThingCate 파일 다운로드</a>";
                        } else {
                            echo "⚠️ 다운로드할 파일이 없습니다.";
                        }
                        $stmt_file->close();  // ✅ 파일명 쿼리 종료
                    }
            
                    $dir_path = "../../ImgFolder/$View_ImgFolder";
                    if (!empty($View_ImgFolder) && is_dir($dir_path)) {
                        $dir_handle = opendir($dir_path);
                        $i = 1;
                        while ($tmp = readdir($dir_handle)) {
                            if ($tmp != "." && $tmp != "..") {
                                echo "[$i] 파일: <a href='$dir_path/$tmp' target='_blank' class='file'>$tmp</a><br>";
                                $i++;
                            }
                        }
                        closedir($dir_handle);
                    }
                    ?>
                </td>
            </tr>
        </table>
        ========<br>
    <?php } ?>
    
    <form>
        <?php if (!empty($no)) { ?>
            <input type='submit' value=' 정 보 수 정 '>
        <?php } else { ?>
            <input type='submit' value=' 자 료 저 장 '>
        <?php } ?>
        <input type='button' onClick='window.close();' value=' 창닫기-CLOSE '>
    </form>
    
    <?php
} // End of OrderView mode
?>


<?php
if ($mode == "SinForm") { /////////////////////////////////////////////////////////////////////////
    include "../title.php";
?>
<head>
    <style>
        .Left1 {
            font-size: 10pt;
            color: #000000; /* 글씨 검은색 */
            font-weight: bold;
        }

    </style>
</head>


    <script>
        self.moveTo(0,0);
        self.resizeTo(600, 200);

        function MlangFriendSiteCheckField() {
            var f = document.MlangFriendSiteInfo;

            if (f.photofile.value.trim() === "") {
                alert("업로드할 이미지를 올려주시기 바랍니다.");
                f.photofile.focus();
                return false;
            }
            console.log("폼 제출 진행 중...");
            return true; // `return false;`를 잘못 사용하면 폼이 전송되지 않음!
            <?php
            include "$T_DirFole";
            if ($View_SignMMk == "yes") {  // 추가된 교정시안 비번 입력 기능
            ?>
                if (f.pass.value == "") {
                    alert("사용할 비밀번호를 입력해 주시기 바랍니다.");
                    f.pass.focus();
                    return false;
                }
            <?php
            }
            ?>
            return true;
        }

        // 이미지 미리보기
        function Mlamg_image(image) {
            let Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
            Mlangwindow.document.open();
            Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
            Mlangwindow.document.write("<body>");
            Mlangwindow.document.write("<p align=center><img src='" + image + "'></p>");
            Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='윈도우 닫기' onClick='window.close()'></p>");
            Mlangwindow.document.write("</body></html>");
            Mlangwindow.document.close();
        }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body class='coolBar'>
    <table border=0 align=center width=100% cellpadding='5' cellspacing='1' >
    <form name="MlangFriendSiteInfo" method="post" enctype="multipart/form-data" 
    onsubmit="return MlangFriendSiteCheckField()" 
    action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name='mode' value='SinFormModifyOk'>
            <input type="hidden" name='no' value="<?php echo  isset($_GET['no']) ? htmlspecialchars($_GET['no']) : '' ?>">
            <?php if(isset($ModifyCode) && !empty($ModifyCode)){ ?>
    <input type="hidden" name="ModifyCode" value="ok">
<?php } ?>


            <tr>
                <td bgcolor='#6699CC' colspan=2 align=center>
                    <font color='#FFFFFF'><b>교정/시안 - 등록/수정</b></font>
                </td>
            </tr>

            <tr>
                <td align=right>이미지 자료:&nbsp;</td>
                <td>
                    <input type="hidden" name="photofileModify" value='ok'>
                    <input type="file" size=45 name="photofile" accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="Mlamg_image(this.value)">
                </td>
            </tr>

            <?php
            if ($View_SignMMk == "yes") {  // 추가된 교정시안 비번 입력 기능
                $db = new mysqli($host, $user, $password, $dataname);
                if ($db->connect_error) {
                    die("Database connection failed: " . $db->connect_error);
                }
                $db->set_charset("utf8");

                $stmt = $db->prepare("SELECT pass FROM MlangOrder_PrintAuto WHERE no = ?");
                $stmt->bind_param("i", $no);
                $stmt->execute();
                $stmt->bind_result($ViewSignTy_pass);
                $stmt->fetch();
                $stmt->close();
                $db->close();
            ?>
                <tr>
                    <td align=right>사용 비밀번호:&nbsp;</td>
                    <td>
                        <input type="text" name="pass" size=20 value='<?php echo  htmlspecialchars($ViewSignTy_pass) ?>'>
                    </td>
                </tr>
            <?php } ?>

            <tr>
                <td>&nbsp;</td>
                <td>
                    <?php if ($ModifyCode) { ?>
                        <input type='submit' value='수정 합니다.'>
                    <?php } else { ?>
                        <input type='submit' value='등록 합니다.'>
                    <?php } ?>
                </td>
            </tr>
        </form>
    </table>
</body>
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
    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // `MlangOrder_PrintAuto` 테이블에서 기존 파일명 가져오기
    $stmt = $db->prepare("SELECT ThingCate FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("i", $ModifyCode);
    $stmt->execute();
    $stmt->bind_result($GF_upfile);
    $stmt->fetch();
    $stmt->close();

    if (empty($GF_upfile)) {
        echo "<p align=center><b>DB에 $ModifyCode 의 등록 자료가 없음.</b></p>";
        exit;
    }

    // 자료를 업로드할 폴더를 생성 시켜준다.. ///////////////////////////////
    $dir = "../../MlangOrder_PrintAuto/upload/$no";
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        chmod($dir, 0777);
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

    // DB 업데이트
    $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle=?, ThingCate=?, pass=? WHERE no=?");
    $stmt->bind_param("sssi", $TOrderStyle, $photofileNAME, $pass, $no);
    
    if (!$stmt->execute()) {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    echo "<script>
            alert('정보를 정상적으로 수정하였습니다.');
            opener.parent.location.reload();
            window.self.close();
          </script>";

    $stmt->close();
    $db->close();
    exit;
}

?>



<?php
if ($mode == "AdminMlangOrdert") { /////////////////////////////////////////////////////////////////
    include "../title.php";
?>
<head>
    <script>
        self.moveTo(0, 0);
        self.resizeTo(680, 400);

        function MlangFriendSiteCheckField() {
            var f = document.MlangFriendSiteInfo;

            if ((!f.MlangFriendSiteInfo[0].checked) && (!f.MlangFriendSiteInfo[1].checked)) {
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
            return true;
        }

    // HONG : 스크립트 값을 표준화시키고 선택하경우 히든으로 값을 넣는 inThing()함수를 하나더 사용.

    function MlangFriendSiteInfocheck() {
    let f = document.MlangFriendSiteInfo;
    let thingInputArea = document.getElementById('Mlang_go');
    
    if (f.MlangFriendSiteInfoS[0].checked) {
        let selectHTML = "<select name='Thing' onchange='inThing(this.value)'>";
        
        fetch("fetch_categories.php") // Fetch categories dynamically
        .then(response => response.json())
        .then(data => {
            data.forEach(category => {
                selectHTML += `<option value='${category}'>${category}</option>`;
            });
            selectHTML += "</select>";
            thingInputArea.innerHTML = selectHTML;
        })
        .catch(error => console.error("Error fetching categories:", error));
    } else if (f.MlangFriendSiteInfoS[1].checked) {
        thingInputArea.innerHTML = "<input type='text' name='Thing' size='30' onblur='inThing(this.value)'>";
    }
}

function inThing(value) {
    document.MlangFriendSiteInfo.ThingNo.value = value;
}


</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
<SCRIPT LANGUAGE=JAVASCRIPT src='../js/exchange.js'></SCRIPT>
</head>

<body class='coolBar'>
    <table border=0 align=center width=100% cellpadding='8' cellspacing='1' >
    <form name="MlangFriendSiteInfo" method="post" enctype="multipart/form-data" 
    onsubmit="return MlangFriendSiteCheckField()" 
    action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>">

    <input type="hidden" name='mode' value='AdminMlangOrdertOk'>
    <input type="hidden" name='no' value='<?php echo  htmlspecialchars($no, ENT_QUOTES, 'UTF-8') ?>'>

    <?php if (!empty($ModifyCode)) { ?>
        <input type="hidden" name='ModifyCode' value='ok'>
    <?php } ?>
    <tr>
                <td bgcolor='#6699CC' colspan=2 align=center>
                    <font color='#FFFFFF'><b>교정/시안 - 등록/수정</b></font>
                </td>
            </tr>
    <tr>
        <td bgcolor='#6699CC' align=right>종류&nbsp;</td>
        <td>
            <input type="radio" name="MlangFriendSiteInfoS" value="select" onclick='MlangFriendSiteInfocheck()'> 선택박스
            <input type="radio" name="MlangFriendSiteInfoS" value="input" onclick='MlangFriendSiteInfocheck()'> 직접입력
            <input type='hidden' name='ThingNo'>
            <BR>
            <table border=0 align=center width=100% cellpadding=5 cellspacing=0>
                <tr>
                    <td id='Mlang_go'></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>주문인 성함&nbsp;</td>
        <td><input type="text" name="name" size=20 required></td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>담당 디자이너&nbsp;</td>
        <td><input type="text" name="Designer" size=20 required></td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>결과 처리&nbsp;</td>
        <td>
            <select name='OrderStyle' required>
                <option value='0'>:::선택:::</option>
                <option value='6'>시안</option>
                <option value='7'>교정</option>
            </select>
        </td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>주문 날짜&nbsp;</td>
        <td><input type="text" name="date" size=20 onclick="Calendar(this);">
        <font style='color:#363636; font-size:8pt;'>(입력예:2005-08-10 * 마우스로 선택 가능)</font></td>
    </tr>

    <tr>
        <td bgcolor='#6699CC' align=right>이미지 자료&nbsp;</td>
        <td>
            <input type="file" name="photofile" accept=".jpg,.jpeg,.png,.gif,.pdf">
        </td>
    </tr>

    <tr>
        <td align=center colspan=2>
            <?php if (!empty($ModifyCode)) { ?>
                <input type='submit' value='수정 합니다.'>
            <?php } else { ?>
                <input type='submit' value='등록 합니다.'>
            <?php } ?>
        </td>
    </tr>
</form>
    </table>
</body>

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
    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    $ToTitle = $_POST['ThingNo'] ?? '';
    include "../../MlangPrintAuto/ConDb.php";

    $ThingNoOkp = empty($_POST['ThingNoOkp']) ? $ToTitle : $_POST['View_TtableB'];
    // if(!$ThingNoOkp){$ThingNoOkp="$ThingNo";}else{$ThingNoOkp="$View_TtableB";}

    // 새로운 주문번호 생성
    $Table_result = $db->query("SELECT MAX(no) FROM MlangOrder_PrintAuto");
    if (!$Table_result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    $row = $Table_result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 업로드 폴더 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        chmod($dir, 0777);
    }
    //파일 업로드 처리
    $photofileNAME = "";
    if (!empty($_FILES['photofile']['name'])) {
        $file_name = basename($_FILES['photofile']['name']);
        $file_tmp_path = $_FILES['photofile']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "pdf"];
        $max_file_size = 2 * 1024 * 1024;

        if ($_FILES['photofile']['size'] > $max_file_size) {
            die("<script>alert('파일 크기가 너무 큽니다. (최대: 2MB)'); history.go(-1);</script>");
        }
        if (!in_array($file_ext, $allowed_extensions)) {
            die("<script>alert('허용되지 않은 파일 형식입니다. (jpg, jpeg, png, gif, pdf 만 가능)'); history.go(-1);</script>");
        }

        $new_file_name = date("YmdHis") . "_" . uniqid() . "." . $file_ext;
        $target_file = $dir . "/" . $new_file_name;

        if (!move_uploaded_file($file_tmp_path, $target_file)) {
            die("<script>alert('파일 이동 실패!'); history.go(-1);</script>");
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

    $date = !empty($date) ? $date : date("Y-m-d H:i:s");   
// `INSERT INTO` SQL 실행
$stmt = $db->prepare("INSERT INTO MlangOrder_PrintAuto 
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

// 성공 메시지 및 리디렉션
echo "<script>
        alert('정보를 정상적으로 저장하였습니다.');
        opener.parent.location.reload();
        window.self.close();
      </script>";
// <script>
//     alert('정보를 정상적으로 저장하였습니다.');
//     if (window.opener && !window.opener.closed) {
//         window.opener.location.href = '/admin/MlangPrintAuto/OrderList.php'; // 부모 창 이동
//         window.opener.focus(); // 부모 창 활성화
//     }
//     window.close(); // 현재 창 닫기
// </script>

$stmt->close();
$db->close();
exit;
}
?>

