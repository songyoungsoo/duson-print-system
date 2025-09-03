<?php
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';  // 절대 경로로 변경
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "cadarokTwo";

include "$T_DirUrl/ConDb.php";
$T_DirFole = "$T_DirUrl/$T_TABLE/inc.php";
$TABLE = "MlangPrintAuto_$T_TABLE";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "form") {

    include "../title.php";
    include "int/info.php";
    $Bgcolor1 = "408080";

    if ($code == "Modify") {
        include "./{$T_TABLE}_NoFild.php";
    }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>Form</title>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font-weight:bold;}
</style>
<script>
var NUM = "0123456789."; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i+1)) < 0) {
            return false;
        }
    }
    return true;
}

function MemberXCheckField() {
    var f = document.myForm;

    if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
        alert("<?php echo  $View_TtableC ?> [분류]를 선택해주세요!!");
        f.RadOne.focus();
        return false;
    }

    if (f.myListTreeSelect.value == "#" || f.myListTreeSelect.value == "==================") {
        alert("<?php echo  $View_TtableC ?>[세부항목]을 선택해주세요!!");
        f.myListTreeSelect.focus();
        return false;
    }

    if (f.myList.value == "#" || f.myList.value == "==================") {
        alert("<?php echo  $View_TtableC ?>[세부항목 2]를 선택해주세요!!");
        f.myList.focus();
        return false;
    }

    if (f.quantity.value == "") {
        alert("수량을 입력해주세요!!");
        f.quantity.focus();
        return false;
    }
    if (!TypeCheck(f.quantity.value, NUM)) {
        alert("수량은 숫자로 입력해주세요.");
        f.quantity.focus();
        return false;
    }

    if (f.money.value == "") {
        alert("금액을 입력해주세요!!");
        f.money.focus();
        return false;
    }
    if (!TypeCheck(f.money.value, NUM)) {
        alert("금액은 숫자로 입력해주세요.");
        f.money.focus();
        return false;
    }
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0" class="coolBar">

<?php if ($code == "Modify") { ?>
    <b>&nbsp;&nbsp;<?php echo  $View_TtableC ?> 데이터 수정</b><br>
<?php } else { ?>
    <b>&nbsp;&nbsp;<?php echo  $View_TtableC ?> 데이터 입력</b><br>
<?php } ?>

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
<?php include "{$T_TABLE}_Script.php"; ?>

<form name="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="hidden" name="mode" value="<?php echo  $code == 'Modify' ? 'Modify_ok' : 'form_ok' ?>">
    <?php if ($code == 'Modify') { ?>
        <input type="hidden" name="no" value="<?php echo  htmlspecialchars($no) ?>">
    <?php } ?>
    <input type="hidden" name="Ttable" value="<?php echo  htmlspecialchars($Ttable) ?>">

    <tr>
    <td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">수량&nbsp;&nbsp;</td>
    <td><input type="text" name="quantity" size="20" maxLength="20" value="<?php echo  $code == 'Modify' ? $MlangPrintAutoFildView_quantity : '' ?>"></td>
    </tr>

    <tr>
    <td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">금액&nbsp;&nbsp;</td>
    <td><input type="text" name="money" size="20" maxLength="20" value="<?php echo  $code == 'Modify' ? $MlangPrintAutoFildView_money : '' ?>"></td>
    </tr>

    <tr>
    <td colspan="2" align="center">* 데이터는 9999 이하로 입력해주세요</td>
    </tr>

    <tr>
    <td>&nbsp;&nbsp;</td>
    <td>
    <input type="submit" value=" <?php echo  $code == 'Modify' ? '수정' : '입력' ?>합니다. ">
    </td>
    </tr>
</form>
</table>

</body>
</html>

<?php
    exit;
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "form_ok") {
    $stmt = $db->prepare("INSERT INTO $TABLE VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssssss', $null, $RadOne, $myListTreeSelect, $quantity, $money, $myList, $TDesignMoney, $POtype, $quantityTwo);
    $stmt->execute();

    echo ("
    <script language='javascript'>
    alert('\\n데이터가 성공적으로 입력되었습니다.\\n');
    opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&Ttable=$Ttable'>
    ");
    exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "Modify_ok") {
    $stmt = $db->prepare("UPDATE $TABLE SET style=?, Section=?, quantity=?, money=?, TreeSelect=?, DesignMoney=?, POtype=?, quantityTwo=? WHERE no=?");
    $stmt->bind_param('sssssssss', $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo, $no);
    $stmt->execute();

    if (!$stmt) {
        echo "
        <script language='javascript'>
            window.alert('DB 업데이트 오류!');
            history.go(-1);
        </script>";
        exit;
    } else {
        echo ("
        <script language='javascript'>
        alert('\\n데이터가 성공적으로 업데이트되었습니다.\\n');
        opener.parent.location.reload();
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=Modify&no=$no&Ttable=$Ttable'>
        ");
        exit;
    }
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM $TABLE WHERE no=?");
    $stmt->bind_param('s', $no);
    $stmt->execute();
    $db->close();

    echo ("
    <html>
    <script language='javascript'>
    window.alert('$no 데이터가 삭제되었습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    </html>
    ");
    exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "IncForm") { // inc 파일을 작성하는 경우
    include "int/info.php";
    include "../title.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>IncForm</title>
<script>
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i+1)) < 0) {
            return false;
        }
    }
    return true;
}

function AdminPassKleCheckField() {
    var f = document.AdminPassKleInfo;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0" class="coolBar">

<br>
<p align="center">
<form name="AdminPassKleInfo" method="post" onsubmit="javascript:return AdminPassKleCheckField()" action="<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>" enctype="multipart/form-data">
<input type="hidden" name="mode" value="IncFormOk">

<table border="1" width="100%" align="center" cellpadding="5" cellspacing="0">
<tr><td bgcolor="#6699CC" class="td11" colspan="2">
<font style="color:#FFFFFF; line-height:130%;">
아래의 필드를 입력하고 제출 버튼을 클릭하세요. 이미지를 선택한 경우 이미지를 업로드 합니다.
<br>
<font color="red">*</font> 필드 입력은 HTML로 작성되며, 자동으로 처리됩니다. # 입력시 줄바꿈, ## 입력시 칸 띄움이 처리됩니다.
</font>
</td></tr>

<tr>
<td align="center">필드 1</td>
<td>
    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
       <tr>
         <td align="center"><textarea name="Section1" rows="4" cols="50"><?php echo  $SectionOne ?></textarea></td>
         <td align="center">
            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <input type="file" name="File1" size="20">
                        <?php if ($ImgOne) { ?><br>
                        <input type="checkbox" name="ImeOneChick">이미지 삭제를 선택하세요
                        <input type="hidden" name="File1_Y" value="<?php echo  $ImgOne ?>">
                        <?php } ?>
                    </td>
                    <?php if ($ImgOne) { ?>
                    <td align="center">
                        <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgOne ?>" width="80" height="95" border="0">
                    </td>
                    <?php } ?>
                </tr>
            </table>
         </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align="center">필드 2</td>
<td>
    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
       <tr>
         <td align="center"><textarea name="Section2" rows="4" cols="50"><?php echo  $SectionTwo ?></textarea></td>
         <td align="center">
            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <input type="file" name="File2" size="20">
                        <?php if ($ImgTwo) { ?><br>
                        <input type="checkbox" name="ImeTwoChick">이미지 삭제를 선택하세요
                        <input type="hidden" name="File2_Y" value="<?php echo  $ImgTwo ?>">
                        <?php } ?>
                    </td>
                    <?php if ($ImgTwo) { ?>
                    <td align="center">
                        <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgTwo ?>" width="80" height="95" border="0">
                    </td>
                    <?php } ?>
                </tr>
            </table>
         </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align="center">필드 3</td>
<td>
    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
       <tr>
         <td align="center"><textarea name="Section3" rows="4" cols="50"><?php echo  $SectionTree ?></textarea></td>
         <td align="center">
            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <input type="file" name="File3" size="20">
                        <?php if ($ImgTree) { ?><br>
                        <input type="checkbox" name="ImeTreeChick">이미지 삭제를 선택하세요
                        <input type="hidden" name="File3_Y" value="<?php echo  $ImgTree ?>">
                        <?php } ?>
                    </td>
                    <?php if ($ImgTree) { ?>
                    <td align="center">
                        <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgTree ?>" width="80" height="95" border="0">
                    </td>
                    <?php } ?>
                </tr>
            </table>
         </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align="center">필드 4</td>
<td>
    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
       <tr>
         <td align="center"><textarea name="Section4" rows="4" cols="50"><?php echo  $SectionFour ?></textarea></td>
         <td align="center">
            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <input type="file" name="File4" size="20">
                        <?php if ($ImgFour) { ?><br>
                        <input type="checkbox" name="ImeFourChick">이미지 삭제를 선택하세요
                        <input type="hidden" name="File4_Y" value="<?php echo  $ImgFour ?>">
                        <?php } ?>
                    </td>
                    <?php if ($ImgFour) { ?>
                    <td align="center">
                        <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgFour ?>" width="80" height="95" border="0">
                    </td>
                    <?php } ?>
                </tr>
            </table>
         </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align="center">필드 5</td>
<td>
    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
       <tr>
         <td align="center"><textarea name="Section5" rows="4" cols="50"><?php echo  $SectionFive ?></textarea></td>
         <td align="center">
            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <input type="file" name="File5" size="20">
                        <?php if ($ImgFive) { ?><br>
                        <input type="checkbox" name="ImeFiveChick">이미지 삭제를 선택하세요
                        <input type="hidden" name="File5_Y" value="<?php echo  $ImgFive ?>">
                        <?php } ?>
                    </td>
                    <?php if ($ImgFive) { ?>
                    <td align="center">
                        <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgFive ?>" width="80" height="95" border="0">
                    </td>
                    <?php } ?>
                </tr>
            </table>
         </td>
       </tr>
     </table>
</td>
</tr>
</table>

<br>
<input type="submit" value="제출합니다">
<input type="button" value="닫기" onclick="javascript:window.self.close();">
</p>
</form>

<?php
exit;
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "IncFormOk") {  // inc 파일 작성 처리

    if ($ImeOneChick == "on") {
        if ($File1) { 
            if ($File1_Y) { unlink("$upload_dir/$File1_Y"); }    
            include "$T_DirUrl/Upload_1.php"; 
        } else {  
            if ($File1_Y) { unlink("$upload_dir/$File1_Y"); }
        }
    } else { 
        if ($File1_Y) { 
            $File1NAME = $File1_Y; 
        } else { 
            if ($File1) { include "$T_DirUrl/Upload_1.php"; } 
        } 
    }

    if ($ImeTwoChick == "on") {
        if ($File2) { 
            if ($File2_Y) { unlink("$upload_dir/$File2_Y"); }    
            include "$T_DirUrl/Upload_2.php"; 
        } else {  
            if ($File2_Y) { unlink("$upload_dir/$File2_Y"); }
        }
    } else { 
        if ($File2_Y) { 
            $File2NAME = $File2_Y; 
        } else { 
            if ($File2) { include "$T_DirUrl/Upload_2.php"; } 
        }  
    }

    if ($ImeTreeChick == "on") {
        if ($File3) { 
            if ($File3_Y) { unlink("$upload_dir/$File3_Y"); }    
            include "$T_DirUrl/Upload_3.php"; 
        } else {  
            if ($File3_Y) { unlink("$upload_dir/$File3_Y"); }
        }
    } else { 
        if ($File3_Y) { 
            $File3NAME = $File3_Y; 
        } else { 
            if ($File3) { include "$T_DirUrl/Upload_3.php"; } 
        }  
    }

    if ($ImeFourChick == "on") {
        if ($File4) { 
            if ($File4_Y) { unlink("$upload_dir/$File4_Y"); }    
            include "$T_DirUrl/Upload_4.php"; 
        } else {  
            if ($File4_Y) { unlink("$upload_dir/$File4_Y"); }
        }
    } else { 
        if ($File4_Y) { 
            $File4NAME = $File4_Y; 
        } else { 
            if ($File4) { include "$T_DirUrl/Upload_4.php"; } 
        }  
    }

    if ($ImeFiveChick == "on") {
        if ($File5) { 
            if ($File5_Y) { unlink("$upload_dir/$File5_Y"); }    
            include "$T_DirUrl/Upload_5.php"; 
        } else {  
            if ($File5_Y) { unlink("$upload_dir/$File5_Y"); }
        }
    } else { 
        if ($File5_Y) { 
            $File5NAME = $File5_Y; 
        } else { 
            if ($File5) { include "$T_DirUrl/Upload_5.php"; } 
        }  
    }

    $fp = fopen("$T_DirFole", "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$DesignMoney=\"$money\";\n");
    fwrite($fp, "\$SectionOne=\"$Section1\";\n");
    fwrite($fp, "\$SectionTwo=\"$Section2\";\n");
    fwrite($fp, "\$SectionTree=\"$Section3\";\n");
    fwrite($fp, "\$SectionFour=\"$Section4\";\n");
    fwrite($fp, "\$SectionFive=\"$Section5\";\n");
    fwrite($fp, "\$ImgOne=\"$File1NAME\";\n");
    fwrite($fp, "\$ImgTwo=\"$File2NAME\";\n");
    fwrite($fp, "\$ImgTree=\"$File3NAME\";\n");
    fwrite($fp, "\$ImgFour=\"$File4NAME\";\n");
    fwrite($fp, "\$ImgFive=\"$File5NAME\";\n");
    fwrite($fp, "?>");
    fclose($fp);

    echo ("<script language='javascript'>
    window.alert('작업이 완료되었습니다.');
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=IncForm'>
    ");
    exit;

}
?>
