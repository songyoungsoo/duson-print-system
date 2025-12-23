<?php
declare(strict_types=1);


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

include"../../db.php";
include"../config.php";

$T_DirUrl="../../mlangprintauto";
include"$T_DirUrl/ConDb.php";

$T_DirFole="./int/info.php";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="ModifyOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

$query ="UPDATE mlangorder_printauto SET Type_1='$TypeOne', name='$name', email='$email', zip='$zip', zip1='$zip1', zip2='$zip2', phone='$phone', Hendphone='$Hendphone', delivery='$delivery', bizname='$bizname', bank='$bank', bankname='$bankname', cont='$cont', Gensu='$Gensu' WHERE no='$no'";
$result= mysqli_query($db, $query);
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

	if(!$result) {
		echo "
			<script language=javascript>
			<meta charset='euc-kr'>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		<meta charset='euc-kr'>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>
<meta charset='euc-kr'>
	");
		exit;

}

mysqli_close($db);

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SubmitOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

$Table_result = mysqli_query($db, "SELECT max(no) FROM mlangorder_printauto");
	if (!$Table_result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($Table_result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   

// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../mlangorder_printauto/upload/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$date=date("Y-m-d H:i;s");
$dbinsert ="insert into mlangorder_printauto values('$new_no',
'$Type', 
'$ImgFolder', 
'$TypeOne',
'$money_1',
'$money_2',	
'$money_3',	
'$money_4',	
'$money_5',	
'$name',   
'$email',
'$zip', 
'$zip1',
'$zip2',
'$phone',   
'$Hendphone',
'$delivery', 
'$bizname',
'$bank',
'$bankname',
'$cont', 
'$date',
'3',
'',
'$phone',
'$Gensu'
)";
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 [저장] 하였습니다.\\n');
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$new_no'>
<meta charset='euc-kr'>
	");
		exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="BankForm"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

include"../title.php";
include"$T_DirFole";
$Bgcolor1="408080";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=500);
</script>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,screen.availHeight)

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

function MemberXCheckField()
{
var f=document.myForm;

if (f.BankName.value == "") {
alert("은행명을 입력하여주세요!!");
f.BankName.focus();
return false;
}

if (f.TName.value == "") {
alert("예금주을 입력하여주세요!!");
f.TName.focus();
return false;
}

if (f.BankNo.value == "") {
alert("계좌번호 입력하여주세요!!");
f.BankNo.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=5>

<form name='myForm' method='post' <?if($code=="Text"){}else{?>OnSubmit='javascript:return MemberXCheckField()'<?}?> action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='BankModifyOk'>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;▒ 교정시안 비밀번호 기능 수정 ▒▒▒▒▒</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>사용여부&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="radio" NAME="SignMMk" <?if($View_SignMMk=="yes"){?>checked<?}?> value='yes'>YES 
<INPUT TYPE="radio" NAME="SignMMk" <?if($View_SignMMk=="no"){?>checked<?}?> value='no'>NO
<!--	$View_SignMMk="yes" checked="checked"-->
</td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;▒ 입금은행 수정 ▒▒▒▒▒</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>은행명&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankName" size=20 maxLength='200' value='<?=$View_BankName?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>예금주&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="TName" size=20 maxLength='200' value='<?=$View_TName?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>계좌번호&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankNo" size=40 maxLength='200' value='<?=$View_BankNo?>'></td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;▒ 자동견적 하단 TEXT 내용 수정 ▒▒▒▒▒</b><BR>
&nbsp;&nbsp;&nbsp;&nbsp;*주의사항 <big><b>'</b></big> 외 따옴표 와  <big><b>"</b></big> 쌍 따옴표 입력 불가</font>
</td>
</tr>

<?php if ($ConDb_A) {
	$Si_LIST_script = explode(":", $ConDb_A);
	$k = 0; $kt = 0;
	while($k < count($Si_LIST_script)) {
?>
 <tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right><?echo("$Si_LIST_script[$k]");?>&nbsp;&nbsp;</td>
<td><TEXTAREA NAME="ContText_<?=$kt?>" ROWS="4" COLS="58"><?$temp = "View_ContText_".$kt; $get_temp=$$temp; echo("$get_temp");?></TEXTAREA></td>
</tr>
 <?php 		$k=$k+1; $kt=$kt+1;
	} 
} 
?>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' 수정 합니다.'>
</td>
</tr>
</FORM>
</table>
<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="BankModifyOk"){

	$fp = fopen("$T_DirFole", "w");
	fwrite($fp, "<?\n");
	fwrite($fp, "\$View_SignMMk=\"$SignMMk\";\n");
	fwrite($fp, "\$View_BankName=\"$BankName\";\n");
	fwrite($fp, "\$View_TName=\"$TName\";\n");
	fwrite($fp, "\$View_BankNo=\"$BankNo\";\n");

if ($ConDb_A) {
	$Si_LIST_script = explode(":", $ConDb_A);
	$k = 0; $kt = 0;
	while($k < count($Si_LIST_script)) {
		$tempTwo = "ContText_".$kt; $get_tempTwo=$$tempTwo;
     fwrite($fp, "\$View_ContText_${kt}=\"$get_tempTwo\";\n");
		$k=$k+1; $kt=$kt+1;
	} 
} 

	fwrite($fp, "?>");
	fclose($fp);


echo ("<script language=javascript>
window.alert('수정 완료....*^^*');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=BankForm'>
");
exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php if($mode=="OrderView"){

 include"../title.php";
 
 if($no){
   $result= mysqli_query($db, "select * from mlangorder_printauto where no='$no'");
   $row= mysqli_fetch_array($result);
     if($row){

if($row['OrderStyle']=="2"){
$query ="UPDATE mlangorder_printauto SET OrderStyle='3' WHERE no='$no'";
$result= mysqli_query($db, $query);

	echo ("
		<script language=javascript>
        opener.parent.location.reload();
		</script>
	");

} 
	 }}
?>

<style>
a.file:link,  a.file:visited{font-family:굴림; font-size: 10pt; color:#336699; line-height:130%; text-decoration:underline}
a.file:hover, a.file:active{font-family:굴림; font-size: 10pt; color:#333333; line-height:130%; text-decoration:underline}
</style>

<?php 
	 $ViewDiwr="../../mlangorder_printauto";
     include"$ViewDiwr/OrderFormOrderTree.php";
?>
----------------------------------------------------------------------------------------<BR>
<?php 
	 $ViewDiwr="../../mlangorder_printauto";
     include"$ViewDiwr/OrderFormOrderTree.php";
?>
<BR>
<?if($no){?>
 
 <font style='font:bold; color:#336699;'>* 첨부 파일 *</font> 파일명을 클릭하시면 저장/보기를 하실수 있습니다.  =============================<BR>
<table border=0 align=center width=100% cellpadding=5 cellspacing=0>
       <tr>
         <td height="20">
         
<a href='download.php?downfile=<?=$row['ThingCate']?>'><?=$row['ThingCate']?></a>
<?php 
if(is_dir("../../ImgFolder/$View_ImgFolder")){
	
$dir_path = "../../ImgFolder/$View_ImgFolder"; 

if($View_ImgFolder){
$dir_handle = opendir($dir_path);

// 전체 디렉토리 내용을 출력한다.
$i=1;
while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")) {
echo (is_file($dir_path.$tmp) ? "" : "[$i] 파일: <a href='$dir_path/$tmp' target='_blank' class='file'>$tmp</a><br>");

$i++;
}

}

closedir($dir_handle);	
}
}
?>
		 </td>
       </tr>
</table>
========
<?}?>

 <?if($no){?>
 <input type='submit' value=' 정 보 수 정 '>
<?}else{?>
 <input type='submit' value=' 자 료 저 장 '>
<?}?>
<input type='button' onClick='javascript:window.close();' value=' 창닫기-CLOSE '>


        </td>
       </tr>
     </table>
</form>
 <BR>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php if($mode=="SinForm"){
 include"../title.php";
?>

<head>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,availHeight=200)

function MlangFriendSiteCheckField()
{
var f=document.MlangFriendSiteInfo;

if (f.photofile.value == "") {
alert("업로드할 이미지를 올려주시기 바랍니다.");
f.photofile.focus();
return false;
}

<?php include"$T_DirFole";
if($View_SignMMk=="yes"){  // 추가된 교정시안 비번 입력 기능
?>

if (f.pass.value == "") {
alert("사용할 비밀번호을 입력해 주시기 바랍니다.");
f.pass.focus();
return false;
}

<?php }
?>
