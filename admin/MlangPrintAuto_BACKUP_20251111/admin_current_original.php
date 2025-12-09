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

}

//////////////// 이미지 미리보기 //////////////////////////////////
/* 소스제작: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='윈도우 닫기' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='<?=$Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?=$PHP_SELF?>'>

<INPUT TYPE="hidden" name='mode' value='SinFormModifyOk'>
<INPUT TYPE="hidden" name='no' value='<?=$no?>'>
<?if($ModifyCode){?><INPUT TYPE="hidden" name='ModifyCode' value='ok'><?}?>

<tr>
<td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>교정/시안 - 등록/수정</font></td>
</td>
</tr>

<tr>
<td align=right>이미지 자료:&nbsp;</td>
<td>
<INPUT TYPE="hidden" NAME="photofileModify" value='ok'>
<INPUT type="file" Size=45 name="photofile" onChange="Mlamg_image(this.value)">
</td>
</tr>

<?php if($View_SignMMk=="yes"){  // 추가된 교정시안 비번 입력 기능
        $result_SignTy= mysqli_query($db, "select * from  mlangorder_printauto where no='$no'");
        $row_SignTy= mysqli_fetch_array($result_SignTy);
		$ViewSignTy_pass=$row_SignTy['pass']; 
?>
<tr>
<td align=right>사용 비밀번호:&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="pass" size=20 value='<?=$ViewSignTy_pass?>'>
</td>
</tr>
<?}?>

<tr>
<td>&nbsp;</td>
<td>
<?if($ModifyCode){?>
<input type='submit' value='수정 합니다.'>
<?}else{?>
<input type='submit' value='등록 합니다.'>
<?}?>
</td>
</tr>

</table>


</form>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php if($mode=="SinFormModifyOk"){

if($ModifyCode=="ok"){$TOrderStyle="7";}else{$TOrderStyle="6";}
$ModifyCode="$no";

$result= mysqli_query($db, "select * from mlangorder_printauto where no='$ModifyCode'");
$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{   
$GF_upfile=$row['ThingCate'];  
}

}else{echo("<p align=center><b>DB 에 $ModifyCode 의 등록 자료가 없음.</b></p>"); exit;}

// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../mlangorder_printauto/upload/$no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($GF_upfile){if($photofileModify){if($photofile){
$upload_dir="../../mlangorder_printauto/upload/$no"; include"upload.php";
unlink("../../mlangorder_printauto/upload/$no/$GF_upfile");
}}else{$photofileNAME="$GF_upfile";}
}else{if($photofile){$upload_dir="../../mlangorder_printauto/upload/$no"; include"upload.php";}}

$query ="UPDATE mlangorder_printauto SET OrderStyle='$TOrderStyle', ThingCate='$photofileNAME', pass='$pass' WHERE no='$no'";
$result= mysqli_query($db, $query);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		 opener.parent.location.reload();
		 window.self.close();
		</script>
	<meta charset='euc-kr'>
	");

}
mysqli_close($db);

		exit;
}
?>


<?php if($mode=="AdminMlangOrdert"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
 include"../title.php";
?>

<head>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=400);
</script>

<script language=javascript>
function MlangFriendSiteCheckField()
{
var f=document.MlangFriendSiteInfo;

if((f.MlangFriendSiteInfo[0].checked==false) && (f.MlangFriendSiteInfo[1].checked==false)){
   alert('종류을 선택해주세요');
   return false;
 }

if (f.OrderName.value == "") {
alert("주문자성함을 입력해주세요");
f.OrderName.focus();
return false;
}

if (f.Designer.value == "") {
alert("담당 디자이너를 입력해주세요");
f.Designer.focus();
return false;
}

if (f.OrderStyle.value == "0") {
alert("결과처리을 선택해주세요");
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

}

//////////////// 이미지 미리보기 //////////////////////////////////
/* 소스제작: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='윈도우 닫기' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}

// 소스제작: http://www.script.ne.kr - Mlang
// 현글 삭제하시지마셔염.........*^^*
// HONG : 스크립트 값을 표준화시키고 선택하경우 히든으로 값을 넣는 inThing()함수를 하나더 사용.
function MlangFriendSiteInfocheck()
{
	f=document.MlangFriendSiteInfo;
	if (f.MlangFriendSiteInfoS[0].checked==true){
		ThingNoVal="<select name='Thing' OnChange='inThing(this.value)'><option value=''>선택해주세요</option></select>";
		document.getElementById('Mlang_go').innerHTML = ThingNoVal;

	}
	if (f.MlangFriendSiteInfoS[1].checked==true){
		ThingNoVal="<INPUT TYPE='text' NAME='Thing' size='30' OnBlur='inThing(this.value)'>";
		document.getElementById('Mlang_go').innerHTML = ThingNoVal;
	}
}


function inThing(HYO){
	f=document.MlangFriendSiteInfo;
	f.ThingNo.value=HYO;
}


</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
<SCRIPT LANGUAGE=JAVASCRIPT src='../js/exchange.js'></SCRIPT>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='<?=$Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?=$PHP_SELF?>'>

<INPUT TYPE="hidden" name='mode' value='AdminMlangOrdertOk'>
<INPUT TYPE="hidden" name='no' value='<?=$no?>'>
<?if($ModifyCode){?><INPUT TYPE="hidden" name='ModifyCode' value='ok'><?}?>

<tr>
<td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>교정/시안 - 등록/수정</font></td>
</td>

<tr>
<td bgcolor='#6699CC' align=right>종류&nbsp;</td>
<td>
<input type="radio" name="MlangFriendSiteInfoS" onClick='MlangFriendSiteInfocheck()'>선택박스
<input type="radio" name="MlangFriendSiteInfoS" onClick='MlangFriendSiteInfocheck()'>직접입력
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
<td bgcolor='#6699CC' align=right>주문인성함&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="OrderName" size=20> 
<font style='color:#363636; font-size:8pt;'>(주문자성함은 사용자가 검색하는 코드 임으로 실수 없이 입력하세요)</font>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>담당 디자이너&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="Designer" size=20> 
</td>
</tr>


<tr>
<td bgcolor='#6699CC' align=right>결과처리&nbsp;</td>
<td>
<select name='OrderStyle'>
<option value='0'>:::선택:::</option>
<option value='6'>시안</option>
<option value='7'>교정</option>
</select>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>주문날짜&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="date" size=20 onClick="Calendar(this);">
<font style='color:#363636; font-size:8pt;'>(입력예:2005-08-10 * 마우스로 콕찍으면 자동입력창 나옴 * )</font>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>이미지 자료&nbsp;</td>
<td>
<INPUT TYPE="hidden" NAME="photofileModify" value='ok'>
<INPUT type="file" Size=45 name="photofile" onChange="Mlamg_image(this.value)">
</td>
</tr>

<?php if($View_SignMMk=="yes"){  // 추가된 교정시안 비번 입력 기능
?>
<tr>
<td bgcolor='#6699CC' align=right>비밀번호&nbsp;</td>
<td>
<INPUT type="text" Size=25 name="pass">
</td>
</tr>
<?}?>

<tr>
<td align=center colspan=2>
<?if($ModifyCode){?>
<input type='submit' value='수정 합니다.'>
<?}else{?>
<input type='submit' value='등록 합니다.'>
<?}?>
</td>

</table>


</form>


<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php if($mode=="AdminMlangOrdertOk"){

$ToTitle="$ThingNo";
include"../../mlangprintauto/ConDb.php";

if(!$ThingNoOkp){$ThingNoOkp="$ThingNo";}else{$ThingNoOkp="$View_TtableB";}

$Table_result = mysqli_query($db, "SELECT max(no) FROM mlangorder_printauto");
	if (!$Table_result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
				<meta charset='euc-kr'>
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

if($photofile){$upload_dir="$dir"; include"upload.php";}

// 디비에 관련 자료 저장
$dbinsert ="insert into mlangorder_printauto values('$new_no',
'$ThingNoOkp', 
'$ImgFolder', 
'$Type_1 $Type_2 $Type_3 $Type_4 $Type_5 $Type_6',
'$money_1',
'$money_2',	
'$money_3',	
'$money_4',	
'$money_5',	
'$OrderName',   
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
'$OrderStyle',
'$photofileNAME',
'$pass',
'',
'$Designer'
)";

//echo $dbinsert; exit;
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 저장 하였습니다.\\n');
		 opener.parent.location.reload();
		 window.self.close();
		</script>
		<meta charset='euc-kr'>
	");

mysqli_close($db);
		exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>