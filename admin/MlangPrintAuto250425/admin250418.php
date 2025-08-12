<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

$T_DirFole = "./int/info.php";

$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$SignMMk = $_POST['SignMMk'] ?? '';
$BankName = $_POST['BankName'] ?? '';
$TName = $_POST['TName'] ?? '';
$BankNo = $_POST['BankNo'] ?? '';
$ConDb_A = $_POST['ConDb_A'] ?? $ConDb_A;

$mysqli = new mysqli($host, $user, $password, $dataname);
if ($mysqli->connect_error) {
    die("DB 접속 오류: " . $mysqli->connect_error);
}

if ($mode == "ModifyOk") {
    $stmt = $mysqli->prepare("UPDATE MlangOrder_PrintAuto SET Type_1=?, name=?, email=?, zip=?, zip1=?, zip2=?, phone=?, Hendphone=?, delivery=?, bizname=?, bank=?, bankname=?, cont=?, Gensu=? WHERE no=?");
    $stmt->bind_param("ssssssssssssssi", $TypeOne, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $delivery, $bizname, $bank, $bankname, $cont, $Gensu, $no);

    if (!$stmt->execute()) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    } else {
        echo "<script>alert('정보를 정상적으로 수정하였습니다.'); opener.parent.location.reload();</script>";
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>";
        exit;
    }
}

if ($mode == "SubmitOk") {
    $Table_result = $mysqli->query("SELECT MAX(no) FROM MlangOrder_PrintAuto");
    if (!$Table_result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    $row = $Table_result->fetch_row();
    $new_no = ($row[0]) ? $row[0] + 1 : 1;

    // 업로드 폴더 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        @chmod($dir, 0777);
    }

    $date = date("Y-m-d H:i:s");
    $stmt = $mysqli->prepare("INSERT INTO MlangOrder_PrintAuto (no, Type, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, regdate, state, memo, phone2, Gensu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issssssssssssssssssssssss", $new_no, $Type, $ImgFolder, $TypeOne, $money_1, $money_2, $money_3, $money_4, $money_5, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $delivery, $bizname, $bank, $bankname, $cont, $date, $state = '3', $memo = '', $phone, $Gensu);

    if (!$stmt->execute()) {
        echo "<script>alert('DB 저장 중 오류가 발생했습니다.'); history.go(-1);</script>";
        exit;
    }

    echo "<script>alert('정보를 정상적으로 [저장] 하였습니다.'); opener.parent.location.reload();</script>";
    echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$new_no'>";
    exit;
}

$mysqli->close();
?>
<?php
if ($mode === "BankForm") {
    include "../title.php";
    include "$T_DirFole";
    $Bgcolor1 = "408080";
    ?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font-weight:bold;}
</style>
<script>
self.moveTo(0,0);
self.resizeTo(screen.availWidth, screen.availHeight);
function MemberXCheckField() {
    var f = document.myForm;
    if (f.BankName.value == "") {
        alert("은행명을 입력하여주세요!!");
        f.BankName.focus();
        return false;
    }
    if (f.TName.value == "") {
        alert("예금주를 입력하여주세요!!");
        f.TName.focus();
        return false;
    }
    if (f.BankNo.value == "") {
        alert("계좌번호를 입력하여주세요!!");
        f.BankNo.focus();
        return false;
    }
    return true;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body class="coolBar" style="margin:0; padding:0;">
<form name='myForm' method='post' onsubmit='return MemberXCheckField()' action='<?php echo  $_SERVER['PHP_SELF'] ?>'>
<input type="hidden" name="mode" value="BankModifyOk">

<table border=0 align=center width=100% cellpadding=5 cellspacing=5>
<tr><td colspan=2 bgcolor='#484848'><font color=white><b>&nbsp;&nbsp;▒ 교정시안 비밀번호 기능 수정 ▒▒▒▒▒</b></font></td></tr>
<tr>
<td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>사용여부&nbsp;&nbsp;</td>
<td>
<input type="radio" name="SignMMk" value="yes" <?php echo  ($View_SignMMk == "yes") ? "checked" : "" ?>> YES
<input type="radio" name="SignMMk" value="no" <?php echo  ($View_SignMMk == "no") ? "checked" : "" ?>> NO
</td>
</tr>
<tr><td colspan=2 bgcolor='#484848'><font color=white><b>&nbsp;&nbsp;▒ 입금은행 수정 ▒▒▒▒▒</b></font></td></tr>
<tr>
<td bgcolor='#<?php echo  $Bgcolor1 ?>' class='Left1' align=right>은행명&nbsp;&nbsp;</td>
<td><input type="text" name="BankName" size=20 maxlength='200' value='<?php echo  $View_BankName ?>'></td>
</tr>
<tr>
<td bgcolor='#<?php echo  $Bgcolor1 ?>' class='Left1' align=right>예금주&nbsp;&nbsp;</td>
<td><input type="text" name="TName" size=20 maxlength='200' value='<?php echo  $View_TName ?>'></td>
</tr>
<tr>
<td bgcolor='#<?php echo  $Bgcolor1 ?>' class='Left1' align=right>계좌번호&nbsp;&nbsp;</td>
<td><input type="text" name="BankNo" size=40 maxlength='200' value='<?php echo  $View_BankNo ?>'></td>
</tr>
<tr><td colspan=2 bgcolor='#484848'><font color=white><b>&nbsp;&nbsp;▒ 자동견적 하단 TEXT 내용 수정 ▒▒▒▒▒</b><br>&nbsp;&nbsp;&nbsp;&nbsp;*주의사항 ' 와 " 쌍따옴표 입력 불가</font></td></tr>
<?php
if ($ConDb_A) {
    $Si_LIST_script = explode(":", $ConDb_A);
    foreach ($Si_LIST_script as $kt => $label) {
        $temp = "View_ContText_" . $kt;
        $get_temp = $$temp ?? '';
        echo "<tr><td bgcolor='#{$Bgcolor1}' class='Left1' align=right>{$label}&nbsp;&nbsp;</td><td><textarea name='ContText_{$kt}' rows='4' cols='58'>{$get_temp}</textarea></td></tr>";
    }
}
?>
<tr><td>&nbsp;&nbsp;</td><td><input type='submit' value=' 수정 합니다.'></td></tr>
</table>
</form>
</body>
<?php
} elseif ($mode === "BankModifyOk") {
    $fp = fopen($T_DirFole, "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$View_SignMMk=\"$SignMMk\";\n");
    fwrite($fp, "\$View_BankName=\"$BankName\";\n");
    fwrite($fp, "\$View_TName=\"$TName\";\n");
    fwrite($fp, "\$View_BankNo=\"$BankNo\";\n");

    if ($ConDb_A) {
        $Si_LIST_script = explode(":", $ConDb_A);
        foreach ($Si_LIST_script as $kt => $label) {
            $get_tempTwo = $_POST["ContText_$kt"] ?? '';
            fwrite($fp, "\$View_ContText_{$kt}=\"$get_tempTwo\";\n");
        }
    }

    fwrite($fp, "?>");
    fclose($fp);

    echo "<script>alert('수정 완료되었습니다.');</script>";
    echo "<meta http-equiv='Refresh' content='0; URL={$_SERVER['PHP_SELF']}?mode=BankForm'>";
    exit;
} elseif ($mode === "OrderView") {
    include "../title.php";
    $mysqli = new mysqli($host, $user, $password, $dataname);
    $stmt = $mysqli->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['OrderStyle'] == "2") {
        $updateStmt = $mysqli->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle='3' WHERE no=?");
        $updateStmt->bind_param("i", $no);
        $updateStmt->execute();
        echo "<script>opener.parent.location.reload();</script>";
    }

    echo "<form><input type='submit' value='정보 수정'> <input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE'></form>";
}
?>

<?php
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$ModifyCode = $_GET['ModifyCode'] ?? $_POST['ModifyCode'] ?? '';
$pass = $_POST['pass'] ?? '';
$photofileModify = $_POST['photofileModify'] ?? '';

if ($mode == "SinForm") {
    include "../title.php";
    include "$T_DirFole";
    ?>
    <head>
    <script>
    self.moveTo(0,0);
    self.resizeTo(screen.availWidth, 200);
    function MlangFriendSiteCheckField() {
        var f = document.MlangFriendSiteInfo;
        if (f.photofile.value == "") {
            alert("업로드할 이미지를 올려주시기 바랍니다.");
            f.photofile.focus();
            return false;
        }
        <?php if ($View_SignMMk == "yes") { ?>
        if (f.pass.value == "") {
            alert("사용할 비밀번호를 입력해 주세요.");
            f.pass.focus();
            return false;
        }
        <?php } ?>
        return true;
    }
    function Mlamg_image(image) {
        let Mlangwindow = window.open("", "Image_Mlang", "width=600,height=400,top=0,left=0,scrollbars=1,resizable=1");
        Mlangwindow.document.open();
        Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head><body>");
        Mlangwindow.document.write("<p align=center><img src='" + image + "'></p>");
        Mlangwindow.document.write("<p align=center><input type='button' value='윈도우 닫기' onclick='window.close()'></p>");
        Mlangwindow.document.write("</body></html>");
        Mlangwindow.document.close();
    }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
    </head>
    <body class="coolBar">
    <form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' onsubmit='return MlangFriendSiteCheckField()' action='<?php echo  $_SERVER['PHP_SELF'] ?>'>
        <input type="hidden" name="mode" value="SinFormModifyOk">
        <input type="hidden" name="no" value="<?php echo  $no ?>">
        <?php if ($ModifyCode) { ?><input type="hidden" name="ModifyCode" value="ok"><?php } ?>
        <table border=0 align=center width=100% cellpadding='5' cellspacing='1'>
            <tr><td colspan=2 bgcolor='#6699CC'><font color='white'><b>교정/시안 - 등록/수정</b></font></td></tr>
            <tr><td align=right>이미지 자료:&nbsp;</td><td>
                <input type="hidden" name="photofileModify" value="ok">
                <input type="file" size=45 name="photofile" onchange="Mlamg_image(this.value)">
            </td></tr>
            <?php if ($View_SignMMk == "yes") {
                $stmt = $mysqli->prepare("SELECT pass FROM MlangOrder_PrintAuto WHERE no=?");
                $stmt->bind_param("i", $no);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                $ViewSignTy_pass = $res['pass'] ?? '';
                ?>
                <tr><td align=right>사용 비밀번호:&nbsp;</td>
                <td><input type="text" name="pass" size=20 value='<?php echo  $ViewSignTy_pass ?>'></td></tr>
            <?php } ?>
            <tr><td>&nbsp;</td><td>
                <input type='submit' value='<?php echo  $ModifyCode ? "수정" : "등록" ?> 합니다.'>
            </td></tr>
        </table>
    </form>
    </body>
    <?php
} elseif ($mode == "SinFormModifyOk") {
    $TOrderStyle = ($ModifyCode == "ok") ? "7" : "6";
    $ModifyCode = $no;

    $stmt = $mysqli->prepare("SELECT ThingCate FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("i", $ModifyCode);
    $stmt->execute();
    $stmt->bind_result($GF_upfile);
    $stmt->fetch();
    $stmt->close();

    $dir = "../../MlangOrder_PrintAuto/upload/$no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        @chmod($dir, 0777);
    }

    if (!empty($GF_upfile) && $photofileModify && !empty($_FILES['photofile']['name'])) {
        $upload_dir = $dir;
        include "upload.php";
        unlink("$dir/$GF_upfile");
    } elseif (empty($GF_upfile) && !empty($_FILES['photofile']['name'])) {
        $upload_dir = $dir;
        include "upload.php";
    } else {
        $photofileNAME = $GF_upfile;
    }

    $stmt = $mysqli->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle=?, ThingCate=?, pass=? WHERE no=?");
    $stmt->bind_param("sssi", $TOrderStyle, $photofileNAME, $pass, $no);
    if (!$stmt->execute()) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    } else {
        echo "<script>alert('정보를 정상적으로 수정하였습니다.'); opener.parent.location.reload(); window.self.close();</script>";
        echo "<meta charset='utf-8'>";
    }
    $mysqli->close();
    exit;
}
?>


<?php
if ($mode == "AdminMlangOrdert") {
    include "../title.php";
    include "$T_DirFole";
    ?>

<head>
<script>
self.moveTo(0, 0);
self.resizeTo(screen.availWidth, 400);

function MlangFriendSiteCheckField() {
    var f = document.MlangFriendSiteInfo;

    if (!f.MlangFriendSiteInfoS[0].checked && !f.MlangFriendSiteInfoS[1].checked) {
        alert('종류를 선택해주세요');
        return false;
    }
    if (f.OrderName.value == "") {
        alert("주문자 성함을 입력해주세요");
        f.OrderName.focus();
        return false;
    }
    if (f.Designer.value == "") {
        alert("담당 디자이너를 입력해주세요");
        f.Designer.focus();
        return false;
    }
    if (f.OrderStyle.value == "0") {
        alert("결과처리를 선택해주세요");
        f.OrderStyle.focus();
        return false;
    }
    if (f.date.value == "") {
        alert("주문날짜를 입력해주세요\n\n마우스로 클릭하면 자동입력창이 나옵니다.");
        f.date.focus();
        return false;
    }
    if (f.photofile.value == "") {
        alert("업로드할 이미지를 올려주시기 바랍니다.");
        f.photofile.focus();
        return false;
    }
    <?php if ($View_SignMMk == "yes") { ?>
    if (f.pass.value == "") {
        alert("사용할 비밀번호를 입력해 주세요");
        f.pass.focus();
        return false;
    }
    <?php } ?>
    return true;
}

function Mlamg_image(image) {
    let Mlangwindow = window.open("", "Image_Mlang", "width=600,height=400,scrollbars=1,resizable=1");
    Mlangwindow.document.open();
    Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head><body>");
    Mlangwindow.document.write("<p align=center><img src='" + image + "'></p>");
    Mlangwindow.document.write("<p align=center><input type='button' value='윈도우 닫기' onclick='window.close()'></p>");
    Mlangwindow.document.write("</body></html>");
    Mlangwindow.document.close();
}

function MlangFriendSiteInfocheck() {
    let f = document.MlangFriendSiteInfo;
    let html = "";
    if (f.MlangFriendSiteInfoS[0].checked) {
        html = "<select name='Thing' onchange='inThing(this.value)'>";
        <?php
        include "../../MlangPrintAuto/ConDb.php";
        if ($ConDb_A) {
            $OrderCate_LIST_script = explode(":", $ConDb_A);
            foreach ($OrderCate_LIST_script as $cate) {
                $selected = ($OrderCate == $cate) ? "selected style='background-color:#000000; color:#FFFFFF;'" : "";
                echo "html += '<option value=\"$cate\" $selected>$cate</option>';";
            }
        }
        ?>
        html += "</select>";
        document.getElementById('Mlang_go').innerHTML = html;
    }
    if (f.MlangFriendSiteInfoS[1].checked) {
        html = "<input type='text' name='Thing' size='30' onblur='inThing(this.value)'>";
        document.getElementById('Mlang_go').innerHTML = html;
    }
}

function inThing(value) {
    document.MlangFriendSiteInfo.ThingNo.value = value;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
<script src="../js/exchange.js" type="text/javascript"></script>
</head>

<body class="coolBar">
<form name="MlangFriendSiteInfo" method="post" enctype="multipart/form-data" onsubmit="return MlangFriendSiteCheckField()" action="<?php echo  $_SERVER['PHP_SELF'] ?>">
    <input type="hidden" name="mode" value="AdminMlangOrdertOk">
    <input type="hidden" name="no" value="<?php echo  $no ?>">
    <?php if ($ModifyCode) { ?><input type="hidden" name="ModifyCode" value="ok"><?php } ?>

    <table border=0 align=center width=100% cellpadding='8' cellspacing='1'>
        <tr><td colspan=2 bgcolor='#6699CC'><font color='#FFFFFF'><b>교정/시안 - 등록/수정</b></font></td></tr>

        <tr>
            <td bgcolor='#6699CC' align=right>종류&nbsp;</td>
            <td>
                <input type="radio" name="MlangFriendSiteInfoS" onclick="MlangFriendSiteInfocheck()"> 선택박스
                <input type="radio" name="MlangFriendSiteInfoS" onclick="MlangFriendSiteInfocheck()"> 직접입력
                <input type="hidden" name="ThingNo">
                <div id="Mlang_go"></div>
            </td>
        </tr>

        <tr><td bgcolor='#6699CC' align=right>주문인 성함&nbsp;</td>
            <td><input type="text" name="OrderName" size=20>
                <small style='color:#363636;'>(주문자 성함은 사용자가 검색하는 코드임으로 실수 없이 입력하세요)</small></td>
        </tr>

        <tr><td bgcolor='#6699CC' align=right>담당 디자이너&nbsp;</td>
            <td><input type="text" name="Designer" size=20></td>
        </tr>

        <tr><td bgcolor='#6699CC' align=right>결과처리&nbsp;</td>
            <td>
                <select name='OrderStyle'>
                    <option value='0'>:::선택:::</option>
                    <option value='6'>시안</option>
                    <option value='7'>교정</option>
                </select>
            </td>
        </tr>

        <tr><td bgcolor='#6699CC' align=right>주문날짜&nbsp;</td>
            <td><input type="text" name="date" size=20 onclick="Calendar(this);">
                <small style='color:#363636;'>(입력예: 2024-03-30 * 마우스로 클릭하면 자동입력창 나옴)</small></td>
        </tr>

        <tr><td bgcolor='#6699CC' align=right>이미지 자료&nbsp;</td>
            <td>
                <input type="hidden" name="photofileModify" value="ok">
                <input type="file" name="photofile" size=45 onchange="Mlamg_image(this.value)">
            </td>
        </tr>

        <?php if ($View_SignMMk == "yes") { ?>
        <tr><td bgcolor='#6699CC' align=right>비밀번호&nbsp;</td>
            <td><input type="text" name="pass" size=25></td></tr>
        <?php } ?>

        <tr><td colspan=2 align=center>
            <input type='submit' value='<?php echo  $ModifyCode ? "수정" : "등록" ?> 합니다.'>
        </td></tr>
    </table>
</form>
</body>
<?php
} // end AdminMlangOrdert
?>
<?php
if ($mode == "AdminMlangOrdertOk") {
    $ThingNo = $_POST['ThingNo'];   // ← 특정 모드일 때만
    $ToTitle = $ThingNo;
    include "../../MlangPrintAuto/ConDb.php";
    $ThingNoOkp  = $_REQUEST['ThingNoOkp']  ?? '';
    // $ThingNoOkp = $ThingNoOkp ?: $ThingNo;

    $mysqli = new mysqli($host, $user, $password, $dataname);
    if ($mysqli->connect_error) {
        die("<script>alert('DB 접속 오류'); history.go(-1);</script>");
    }

    $result = $mysqli->query("SELECT MAX(no) FROM MlangOrder_PrintAuto");
    if (!$result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }
    $row = $result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 업로드 폴더 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        @chmod($dir, 0777);
    }

    // 파일 업로드 처리
    if (!empty($_FILES['photofile']['name'])) {
        $upload_dir = $dir;
        include "upload.php";
    }
    $Type_1      = $_REQUEST['Type_1']      ?? '';
    $Type_2      = $_REQUEST['Type_2']      ?? '';
    $Type_3      = $_REQUEST['Type_3']      ?? '';
    $Type_4      = $_REQUEST['Type_4']      ?? '';
    $Type_5      = $_REQUEST['Type_5']      ?? '';
    $Type_6      = $_REQUEST['Type_6']      ?? '';

    $mysqli = new mysqli($host, $user, $password, $dataname);
    $TypeAll = trim("$Type_1\n$Type_2\n$Type_3\n$Type_4\n$Type_5\n$Type_6");
    $sql = "
    INSERT INTO `MlangOrder_PrintAuto`
          (`no`, `Type`, `ImgFolder`, `Type_1`,
           `money_1`, `money_2`, `money_3`, `money_4`, `money_5`,
           `name`, `email`, `zip`, `zip1`, `zip2`,
           `phone`, `Hendphone`, `delivery`, `bizname`, `bank`, `bankname`,
           `cont`, `regdate`, `state`, `ThingCate`, `pass`, `memo`, `Designer`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

$stmt->bind_param(
    $types,
    $new_no, $ThingNoOkp, $ImgFolder, $TypeAll,
    $money_1, $money_2, $money_3, $money_4, $money_5,
    $OrderName, $email, $zip, $zip1, $zip2,
    $phone, $Hendphone, $delivery, $bizname, $bank,
    $bankname, $cont, $date, $OrderStyle,
    $photofileNAME, $pass, $memo, $Designer
);

    if (!$stmt->execute()) {
        echo "<script>alert('DB 저장 중 오류가 발생했습니다.'); history.go(-1);</script>";
        exit;
    }

    echo "<script>alert('정보를 정상적으로 저장하였습니다.'); opener.parent.location.reload(); window.self.close();</script>";
    $mysqli->close();
    exit;
}
?>


