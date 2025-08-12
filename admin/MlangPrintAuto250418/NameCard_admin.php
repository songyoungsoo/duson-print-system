<?php
include "../../db.php";
include "../config.php";


$T_DirUrl="../../MlangPrintAuto";
$T_TABLE="NameCard";

$mode = $_GET['mode'] ?? $_POST['mode'] ?? null;
$code = $_GET['code'] ?? $_POST['code'] ?? null;
$Ttable = $_GET['Ttable'] ?? $_POST['Ttable'] ?? null;
$RadOne = $_POST['RadOne'] ?? '';
$myList = $_POST['myList'] ?? '';
$quantity = $_POST['quantity'] ?? '';
$money = $_POST['money'] ?? '';
$myListTreeSelect = $_POST['myListTreeSelect'] ?? '';
$TDesignMoney = $_POST['TDesignMoney'] ?? '';
$DesignMoney = $_POST['DesignMoney'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$quantityTwo = $_POST['quantityTwo'] ?? '';
$no = $_POST['no'] ?? $_GET['no'] ?? null;
$PHP_SELF = $_SERVER['PHP_SELF'];
$MlangPrintAutoFildView_POtype = $_POST['POtype'] ?? '';
$MlangPrintAutoFildView_quantity = $_POST['quantity'] ?? '';
$MlangPrintAutoFildView_quantityTwo = $_POST['quantityTwo'] ?? '';
$MlangPrintAutoFildView_money = $_POST['money'] ?? '';
$MlangPrintAutoFildView_DesignMoney = $_POST['TDesignMoney'] ?? '';
$MlangPrintAutoFildView_style = $_POST['RadOne'] ?? '';
$MlangPrintAutoFildView_TreeSelect = $_POST['myListTreeSelect'] ?? '';
$MlangPrintAutoFildView_Section = $_POST['myList'] ?? '';


include "$T_DirUrl/ConDb.php";
$T_DirFole="$T_DirUrl/$T_TABLE/inc.php";
$TABLE="MlangPrintAuto_{$T_TABLE}";

/**
 * 다수의 전역 변수를 안전하게 초기화합니다.
 *
 * @param array $vars [ 'varName' => defaultValue ]
 */
function initVars(array $vars): void {
    foreach ($vars as $var => $default) {
        if (!isset($GLOBALS[$var])) {
            $GLOBALS[$var] = $default;
        }
    }
}
initVars([
    'SectionOne'  => '',
    'SectionTwo'  => '',
    'SectionTree' => '',
    'SectionFour' => '',
    'SectionFive' => '',
    'ImgOne'      => '',
    'ImgTwo'      => '',
    'ImgTree'     => '',
    'ImgFour'     => '',
    'ImgFive'     => ''
]);
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form"){

include "../title.php";
include "int/info.php";
$Bgcolor1="408080";

if($code=="Modify"){include "./{$T_TABLE}_NoFild.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
var NUM = "0123456789."; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////

function MemberXCheckField()
{
var f=document.myForm;

if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
alert("<?php echo $View_TtableC?> [명함종류] 을 선택하여주세요!!");
f.RadOne.focus();
return false;
}

if (f.myList.value == "#" || f.myList.value == "==================") {
alert("<?php echo $View_TtableC?>[명함재질] 을 선택하여주세요!!");
f.myList.focus();
return false;
}

if (f.quantity.value == "") {
alert("수량을 입력하여주세요!!");
f.quantity.focus();
return false;
}
if (!TypeCheck(f.quantity.value, NUM)) {
alert("수량은 숫자로만 입력해 주셔야 합니다.");
f.quantity.focus();
return false;
}

if (f.money.value == "") {
alert("가격을 입력하여주세요!!");
f.money.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("가격은 숫자로만 입력해 주셔야 합니다.");
f.money.focus();
return false;
}

if (f.TDesignMoney.value == "") {
alert("디자인비 을 입력하여주세요!!");
f.TDesignMoney.focus();
return false;
}
if (!TypeCheck(f.TDesignMoney.value, NUM)) {
alert("디자인비 은 숫자로만 입력해 주셔야 합니다.");
f.TDesignMoney.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<?php if ($code=="Modify"){?>
<b>&nbsp;&nbsp;▒ <?php echo $View_TtableC?> 자료 수정 ▒▒▒▒▒</b><BR>
<?}else{?>
<b>&nbsp;&nbsp;▒ <?php echo $View_TtableC?> 신 자료 입력 ▒▒▒▒▒</b><BR>
<?php } ?>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>

<?php include "{$T_TABLE}_Script.php";?>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>인쇄면&nbsp;&nbsp;</td>
<td>
<select name="POtype">
<option value='1' <?php if ($MlangPrintAutoFildView_POtype=="1"){echo("selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'");}?>>단면</option>
<option value='2' <?php if ($MlangPrintAutoFildView_POtype=="2"){echo("selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'");}?>>양면</option>
</select>
</td>
</tr>		

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>수량&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="quantity" size=20 maxLength='20' <?php if ($code=="Modify"){echo("value='$MlangPrintAutoFildView_quantity'");}?>>매</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>가격&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="money" size=20 maxLength='20' <?php if ($code=="Modify"){echo("value='$MlangPrintAutoFildView_money'");}?>></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>디자인비&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="TDesignMoney" size=20 maxLength='20' <?php if ($code=="Modify"){echo("value='$MlangPrintAutoFildView_DesignMoney'");}else{echo("value='$DesignMoney'");}?>></td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<?php if ($code=="Modify"){?>
<input type='submit' value=' 수정 합니다.'>
<?}else{?>
<input type='submit' value=' 저장 합니다.'>
<?php } ?>
</td>
</tr>
</FORM>
</table>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


include "../../db.php";
$db = new mysqli($host, $user, $password, $dataname);

if ($db->connect_error) {
	die("Connection failed: " . $db->connect_error);
}
if ($mode == "form_ok") {
    $stmt = $db->prepare("INSERT INTO $TABLE (style, Section, quantity, money, DesignMoney, POtype) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $RadOne, $myList, $quantity, $money, $TDesignMoney, $POtype);
    $stmt->execute();

    echo ("
        <script language='javascript'>
        alert('\\n자료를 정상적으로 저장 하였습니다.\\n');
        opener.parent.location.reload();
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&Ttable=$Ttable'>
    ");
    exit;
}

if ($mode == "Modify_ok") {
    $stmt = $db->prepare("UPDATE $TABLE SET style = ?, Section = ?, quantity = ?, money = ?, DesignMoney = ?, POtype = ? WHERE no = ?");
    $stmt->bind_param("ssssssi", $RadOne, $myList, $quantity, $money, $TDesignMoney, $POtype, $no);
    $result = $stmt->execute();

    if (!$result) {
        echo "
            <script language='javascript'>
            window.alert(\"DB 접속 에러입니다!\")
            history.go(-1);
            </script>
        ";
        exit;
    } else {
        echo ("
            <script language='javascript'>
            alert('\\n정보를 정상적으로 수정하였습니다.\\n');
            opener.parent.location.reload();
            </script>
            <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=Modify&no=$no&Ttable=$Ttable'>
        ");
        exit;
    }
}

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM $TABLE WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();

    echo ("
        <html>
        <script language='javascript'>
        window.alert('$no 번 자료를 삭제 처리 하였습니다.');
        opener.parent.location.reload();
        window.self.close();
        </script>
        </html>
    ");
    exit;
}
?>

?>


<?php
if($mode=="IncForm"){ // inc 파일을 수정하는폼
include "int/info.php";

include "../title.php";
?>

<head>

<script language=javascript>

var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;
////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}
/////////////////////////////////////////////////////////////////////////////////

function AdminPassKleCheckField()
{
var f=document.AdminPassKleInfo;

if (f.moeny.value == "") {
alert("디자인 가격을 입력하여주세요?");
f.moeny.focus();
return false;
}
if (!TypeCheck(f.moeny.value, NUM)) {
alert("디자인 가격은 숫자로만 입력해 주셔야 합니다.");
f.moeny.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<BR>
<p align=center>
<form name='AdminPassKleInfo' method='post' OnSubmit='javascript:return AdminPassKleCheckField()' action='<?php echo $PHP_SELF?>' enctype='multipart/form-data'>
<INPUT TYPE="hidden" name='mode' value='IncFormOk'>

<table border=1 width=100% align=center cellpadding='5' cellspacing='0'>

<tr><td bgcolor='#6699CC' class='td11' colspan=2>
아래의 가격을 숫자로 변경 가능합니다.
</td></tr>
<tr>
<td align=center>디자인 가격</td>
<td><input type='text' name='moeny' maxLength='10' size='15' value='<?php echo $DesignMoney?>'> 원</td>
</tr>

<tr><td bgcolor='#6699CC' class='td11' colspan=2>
<font style='color:#FFFFFF; line-height:130%;'>
아래의 내용은 마우스를 대면 나오는 설명글 입니다, 사진/내용 을 입력하지 않으면 자동으로 호출되지 않습니다,
<BR>
기존 사진자료가 있을경우 자료을 지우려면 사진 미입력후 체크버튼에 체크만 하시면 자료가 지워집니다.
<BR>
<font color=red>*</font>
문구 입력시 HTML을 인식, 엔터을 치면 자동 br 로 처리, # 입력시 공백하나 ##(두개)입력시 공백2칸식으로 처리됨
</font>
</td></tr>
<tr>
<td align=center>명함종류</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section1" ROWS="4" COLS="50"><?php echo $SectionOne?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File1' size='20'>
					 <?php if ($ImgOne){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeOneChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File1_Y' value='<?php echo $ImgOne?>'>
                     <?php } ?>
					 </td>
                     <?php if ($ImgOne){?>
					 <td align=center>
                       <img src='<?php echo $upload_dir?>/<?php echo $ImgOne?>' width=80 height=95 border=0>
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
<td align=center>명함재질</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section2" ROWS="4" COLS="50"><?php echo $SectionTwo?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File2' size='20'>
					 <?php if ($ImgTwo){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeTwoChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File2_Y' value='<?php echo $ImgTwo?>'>
                     <?php } ?>
					 </td>
                     <?php if ($ImgTwo){?>
					 <td align=center>
                       <img src='<?php echo $upload_dir?>/<?php echo $ImgTwo?>' width=80 height=95 border=0>
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
<td align=center>인쇄면</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section3" ROWS="4" COLS="50"><?php echo $SectionTree?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File3' size='20'>
					 <?php if ($ImgTree){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeTreeChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File3_Y' value='<?php echo $ImgTree?>'>
                     <?php } ?>
					 </td>
                     <?php if ($ImgTree){?>
					 <td align=center>
                       <img src='<?php echo $upload_dir?>/<?php echo $ImgTree?>' width=80 height=95 border=0>
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
<td align=center>수량</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section4" ROWS="4" COLS="50"><?php echo $SectionFour?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File4' size='20'>
					 <?php if ($ImgFour){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeFourChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File4_Y' value='<?php echo $ImgFour?>'>
                     <?php } ?>
					 </td>
                     <?php if ($ImgFour){?>
					 <td align=center>
                       <img src='<?php echo $upload_dir?>/<?php echo $ImgFour?>' width=80 height=95 border=0>
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
<td align=center>디자인</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section5" ROWS="4" COLS="50"><?php echo $SectionFive?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File5' size='20'>
					 <?php if ($ImgFive){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeFiveChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File5_Y' value='<?php echo $ImgFive?>'>
                     <?php } ?>
					 </td>
                     <?php if ($ImgFive){?>
					 <td align=center>
                       <img src='<?php echo $upload_dir?>/<?php echo $ImgFive?>' width=80 height=95 border=0>
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

<BR>
<input type='submit' value='수정합니다'>
<input type='button' value='창 닫기' onClick='javascript:window.self.close();'>
</p>
</form>

<?php
exit;
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "IncFormOk") {
    // 파일 업로드 및 삭제 처리
    $files = [
        1 => 'ImeOneChick',
        2 => 'ImeTwoChick',
        3 => 'ImeTreeChick',
        4 => 'ImeFourChick',
        5 => 'ImeFiveChick'
    ];

    foreach ($files as $num => $checkName) {
        $fileKey = "File{$num}";
        $fileKey_Y = "File{$num}_Y";
        $imgVar = "File{$num}NAME";
        $uploadFile = $_FILES[$fileKey]['tmp_name'] ?? null;
        $$checkName = $_POST[$checkName] ?? null;
        $$fileKey_Y = $_POST[$fileKey_Y] ?? null;

        if ($$checkName == "on") {
            if (!empty($uploadFile)) {
                if (!empty($$fileKey_Y)) unlink("$upload_dir/" . $$fileKey_Y);
                include "$T_DirUrl/Upload_{$num}.php";
            } else {
                if (!empty($$fileKey_Y)) unlink("$upload_dir/" . $$fileKey_Y);
            }
        } else {
            if (!empty($$fileKey_Y)) {
                $$imgVar = $$fileKey_Y;
            } elseif (!empty($uploadFile)) {
                include "$T_DirUrl/Upload_{$num}.php";
            }
        }
    }

    // 설정값 저장
    $moeny = $_POST['moeny'] ?? '';
    $Section1 = $_POST['Section1'] ?? '';
    $Section2 = $_POST['Section2'] ?? '';
    $Section3 = $_POST['Section3'] ?? '';
    $Section4 = $_POST['Section4'] ?? '';
    $Section5 = $_POST['Section5'] ?? '';

    $fp = fopen("$T_DirFole", "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$DesignMoney=\"$moeny\";\n");
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

    echo ("<script language=javascript>
    window.alert('수정 완료....*^^*\\n\\n$WebSoftCopyright');
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=IncForm'>");
    exit;
}
?>