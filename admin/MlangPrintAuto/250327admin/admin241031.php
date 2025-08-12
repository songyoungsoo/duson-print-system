<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

$T_DirFole = "./int/info.php";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "ModifyOk") {

    // MySQLi 준비된 명령문으로 데이터 업데이트
    $query = "UPDATE MlangOrder_PrintAuto 
              SET Type_1 = ?, name = ?, email = ?, zip = ?, zip1 = ?, zip2 = ?, phone = ?, Hendphone = ?, 
                  delivery = ?, bizname = ?, bank = ?, bankname = ?, cont = ?, Gensu = ? 
              WHERE no = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ssssssssssssssi", 
        $TypeOne, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, 
        $delivery, $bizname, $bank, $bankname, $cont, $Gensu, $no);

    if ($stmt->execute()) {
        echo "<script>
                alert('정보를 정상적으로 수정하였습니다.');
                opener.parent.location.reload();
              </script>";
        echo "<meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=" . htmlspecialchars($no) . "'>";
    } else {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    $stmt->close();
    $db->close();
    exit;
}

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "SubmitOk") {

    // 최대 `no` 값 조회 및 오류 처리
    $Table_result = $db->query("SELECT MAX(no) FROM MlangOrder_PrintAuto");
    if (!$Table_result) {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    $row = $Table_result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 자료를 업로드할 폴더를 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777); // 0777 권한 설정
    }

    // 현재 날짜와 시간 저장
    $date = date("Y-m-d H:i:s");

    // 데이터 삽입 (준비된 명령문 사용)
    $stmt = $db->prepare("INSERT INTO MlangOrder_PrintAuto 
        (no, Type, ImgFolder, TypeOne, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, date, OrderStyle, pass, phone2, Gensu) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '3', '', ?, ?)");
    $stmt->bind_param("issssssssssssssssssssss", 
        $new_no, $Type, $ImgFolder, $TypeOne, 
        $money_1, $money_2, $money_3, $money_4, $money_5, 
        $name, $email, $zip, $zip1, $zip2, 
        $phone, $Hendphone, $delivery, $bizname, 
        $bank, $bankname, $cont, $date, $phone, $Gensu);

    if ($stmt->execute()) {
        echo "<script>
                alert('정보를 정상적으로 저장하였습니다.');
                opener.parent.location.reload();
              </script>";
        echo "<meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=" . htmlspecialchars($new_no) . "'>";
    } else {
        echo "<script>
                alert('정보 저장에 실패했습니다.');
                history.go(-1);
              </script>";
    }

    $stmt->close();
    $db->close();
    exit;
}

if($mode=="BankForm"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

include "../title.php";
include "int/info.php";
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

<form name='myForm' method='post' <?php if ($code=="Text"){}else{?>OnSubmit='javascript:return MemberXCheckField()'<?php } ?> action='<?php echo $_SERVER['PHP_SELF']?>'>
<INPUT TYPE="hidden" name='mode' value='BankModifyOk'>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;▒ 교정시안 비밀번호 기능 수정 ▒▒▒▒▒</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>사용여부&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="radio" NAME="SignMMk" <?php if ($View_SignMMk=="yes"){?>checked<?php } ?> value='yes'>YES
<INPUT TYPE="radio" NAME="SignMMk" <?php if ($View_SignMMk=="no"){?>checked<?php } ?> value='no'>NO
</td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;▒ 입금은행 수정 ▒▒▒▒▒</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>은행명&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankName" size=20 maxLength='200' value='<?php echo $View_BankName?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>예금주&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="TName" size=20 maxLength='200' value='<?php echo $View_TName?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>계좌번호&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankNo" size=40 maxLength='200' value='<?php echo $View_BankNo?>'></td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;▒ 자동견적 하단 TEXT 내용 수정 ▒▒▒▒▒</b><BR>
&nbsp;&nbsp;&nbsp;&nbsp;*주의사항 <big><b>'</b></big> 외 따옴표 와  <big><b>"</b></big> 쌍 따옴표 입력 불가</font>
</td>
</tr>

<?php
if ($ConDb_A) {
	$Si_LIST_script = explode(":", $ConDb_A);
	$k = 0; $kt = 0;
	while($k < sizeof($Si_LIST_script)) {
?>
 <tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right><?echo("$Si_LIST_script[$k]");?>&nbsp;&nbsp;</td>
<td><TEXTAREA NAME="ContText_<?php echo $kt?>" ROWS="4" COLS="58"><?$temp = "View_ContText_".$kt; $get_temp=$$temp; echo("$get_temp");?></TEXTAREA></td>
</tr>
 <?php
		$k=$k+1; $kt=$kt+1;
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
<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="BankModifyOk"){

	$fp = fopen("$T_DirFole", "w");
	fwrite($fp, "<?\n");
	fwrite($fp, "\$View_SignMMk=\"$SignMMk\";\n");
	fwrite($fp, "\$View_BankName=\"$BankName\";\n");
	fwrite($fp, "\$View_TName=\"$TName\";\n");
	fwrite($fp, "\$View_BankNo=\"$BankNo\";\n");

if ($ConDb_A) {
	$Si_LIST_script = split(":", $ConDb_A);
	$k = 0; $kt = 0;
	while($k < sizeof($Si_LIST_script)) {
		$tempTwo = "ContText_".$kt; $get_tempTwo=$$tempTwo;
     fwrite($fp, "\$View_ContText_{$kt}=\"$get_tempTwo\";\n");
		$k=$k+1; $kt=$kt+1;
	} 
} 

	fwrite($fp, "?>");
	fclose($fp);


	echo "<script>
	alert('수정 완료....*^^*');
  </script>";

echo "<meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=BankForm'>";

exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php
if($mode=="OrderView"){

 include "../title.php";
 
 if($no){
   $result= mysqli_query($db,"select * from MlangOrder_PrintAuto where no='$no'");
   $row= mysqli_fetch_array($result);
     if($row){

if($row['OrderStyle']=="2"){
$query ="UPDATE MlangOrder_PrintAuto SET OrderStyle='3' WHERE no='$no'";
$result= mysqli_query($db,$query);

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
	 $ViewDiwr="../../MlangOrder_PrintAuto";
     include "$ViewDiwr/OrderFormOrderTree.php";
?>
----------------------------------------------------------------------------------------<BR>
<!-- <?php
	//  $ViewDiwr="../../MlangOrder_PrintAuto";
    //  include "$ViewDiwr/OrderFormOrderTree.php";
?> -->
<BR>
<?php if ($no){?>
 
 <font style='font:bold; color:#336699;'>* 첨부 파일 *</font> 파일명을 클릭하시면 저장/보기를 하실수 있습니다.  =============================<BR>
<table border=0 align=center width=100% cellpadding=5 cellspacing=0>
       <tr>
         <td height="20">
         
<a href='download.php?downfile=<?php echo $row[ThingCate]?>'><?php echo $row[ThingCate]?></a>
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
<?php } ?>

 <?php if ($no){?>
 <input type='submit' value=' 정 보 수 정 '>
<?}else{?>
 <input type='submit' value=' 자 료 저 장 '>
<?php } ?>
<input type='button' onClick='javascript:window.close();' value=' 창닫기-CLOSE '>


        </td>
       </tr>
     </table>
</form>
 <BR>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php
if($mode=="SinForm"){
 include "../title.php";
?>

<head>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,availHeight=300)

function MlangFriendSiteCheckField()
{
var f=document.MlangFriendSiteInfo;

if (f.photofile.value == "") {
alert("업로드할 이미지를 올려주시기 바랍니다.");
f.photofile.focus();
return false;
}

<?php
include "$T_DirFole";?>
if($View_SignMMk=="yes"){  // 추가된 교정시안 비번 입력 기능


if (f.pass.value == "") {
alert("사용할 비밀번호을 입력해 주시기 바랍니다.");
f.pass.focus();
return false;
}


}
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

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="coolBar">

<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" bgcolor="<?php echo htmlspecialchars($Bgcolor_1); ?>">

<form name="MlangFriendSiteInfo" method="post" enctype="multipart/form-data" onsubmit="return MlangFriendSiteCheckField()" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

    <input type="hidden" name="mode" value="SinFormModifyOk">
    <input type="hidden" name="no" value="<?php echo htmlspecialchars($no); ?>">
    <?php if (!empty($ModifyCode)) { ?>
        <input type="hidden" name="ModifyCode" value="ok">
    <?php } ?>

    <tr>
        <td bgcolor="#6699CC" colspan="2">
            <font style="color:#FFFFFF; font-weight:bold;">교정/시안 - 등록/수정</font>
        </td>
    </tr>

    <tr>
        <td align="right">이미지 자료:&nbsp;</td>
        <td>
            <input type="hidden" name="photofileModify" value="ok">
            <input type="file" size="45" name="photofile" onchange="Mlamg_image(this.value)">
        </td>
    </tr>
<?php } ?>
<?php
if ($View_SignMMk == "yes") { // 추가된 교정시안 비번 입력 기능

    // MySQLi 객체 사용 및 준비된 명령문으로 데이터 조회
    $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("s", $no);
    $stmt->execute();
    $result_SignTy = $stmt->get_result();
    $row_SignTy = $result_SignTy->fetch_assoc();

    $ViewSignTy_pass = $row_SignTy ? htmlspecialchars($row_SignTy['pass']) : ''; // XSS 방지를 위해 htmlspecialchars() 사용
?>
    <tr>
        <td align="right">사용 비밀번호:&nbsp;</td>
        <td>
            <input type="text" name="pass" size="20" value="<?php echo $ViewSignTy_pass; ?>">
        </td>
    </tr>
<?php 
}
?>

<tr>
    <td>&nbsp;</td>
    <td>
        <?php if ($ModifyCode) { ?>
            <input type="submit" value="수정 합니다.">
        <?php } else { ?>
            <input type="submit" value="등록 합니다.">
        <?php } ?>
    </td>
</tr>

</table>
</form>

<?php
// PHP 종료
?>


<?php
if ($mode == "SinFormModifyOk") {

    $TOrderStyle = ($ModifyCode == "ok") ? "7" : "6";
    $ModifyCode = $no;

    // MySQLi 객체 사용 및 준비된 명령문으로 데이터 조회
    $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("s", $ModifyCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $GF_upfile = $row['ThingCate'];
        }
    } else {
        echo "<p align='center'><b>DB에 $ModifyCode 의 등록 자료가 없습니다.</b></p>";
        exit;
    }

    // 자료를 업로드할 폴더 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777); // 0777 권한 설정
    }

    // 파일 업로드 처리
    if ($GF_upfile) {
        if (!empty($photofileModify) && !empty($photofile)) {
            $upload_dir = "../../MlangOrder_PrintAuto/upload/$no";
            include "upload.php";
            unlink("$upload_dir/$GF_upfile");
        } else {
            $photofileNAME = $GF_upfile;
        }
    } else {
        if (!empty($photofile)) {
            $upload_dir = "../../MlangOrder_PrintAuto/upload/$no";
            include "upload.php";
        }
    }

    // UPDATE 쿼리 실행 (준비된 명령문 사용)
    $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle = ?, ThingCate = ?, pass = ? WHERE no = ?");
    $stmt->bind_param("ssss", $TOrderStyle, $photofileNAME, $pass, $no);

    if ($stmt->execute()) {
        echo "<script>
                alert('\\n정보를 정상적으로 수정하였습니다.\\n');
                opener.parent.location.reload();
                window.close();
              </script>
              <meta charset='utf-8'>";
    } else {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    $stmt->close();
    $db->close();
    exit;
}
?>



<?php
if($mode=="AdminMlangOrdert"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
 include "../title.php";
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

<?php
include"$T_DirFole";
if($View_SignMMk=="yes"){  // 추가된 교정시안 비번 입력 기능
?>

if (f.pass.value == "") {
alert("사용할 비밀번호을 입력해 주시기 바랍니다.");
f.pass.focus();
return false;
}

<?php
}
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
		ThingNoVal="<select name='Thing' OnChange='inThing(this.value)'><?php
		include"../../MlangPrintAuto/ConDb.php";
		if ( $ConDb_A) {
			$OrderCate_LIST_script = explode(":", $ConDb_A);
			$k = 0;
			while($k < sizeof($OrderCate_LIST_script)) {

							  if($OrderCate=="$OrderCate_LIST_script[$k]"){
									echo "<OPTION VALUE='$OrderCate_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$OrderCate_LIST_script[$k]</OPTION>";
								   }else{
									   echo "<OPTION VALUE='$OrderCate_LIST_script[$k]'>$OrderCate_LIST_script[$k]</OPTION>";
												   }

				$k++;
			} 
		} 
		?></select>"
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

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>

<INPUT TYPE="hidden" name='mode' value='AdminMlangOrdertOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
<?php if ($ModifyCode){?><INPUT TYPE="hidden" name='ModifyCode' value='ok'><?php } ?>

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

<?php
if($View_SignMMk=="yes"){  // 추가된 교정시안 비번 입력 기능
?>
<tr>
<td bgcolor='#6699CC' align=right>비밀번호&nbsp;</td>
<td>
<INPUT type="text" Size=25 name="pass">
</td>
</tr>
<?php } ?>

<tr>
<td align=center colspan=2>
<?php if ($ModifyCode){?>
<input type='submit' value='수정 합니다.'>
<?}else{?>
<input type='submit' value='등록 합니다.'>
<?php } ?>
</td>

</table>


</form>


<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if ($mode == "AdminMlangOrdertOk") {

    $ToTitle = "$ThingNo";
    include "../../MlangPrintAuto/ConDb.php";

    // 변수 초기화
    $ThingNoOkp = isset($ThingNoOkp) ? $ThingNoOkp : "$ThingNo";
    $ThingNoOkp = !empty($ThingNoOkp) ? $ThingNoOkp : "$View_TtableB";

    // mysqli 사용 및 오류 처리 추가
    $Table_result = $db->query("SELECT MAX(no) FROM MlangOrder_PrintAuto");
    if (!$Table_result) {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    $row = $Table_result->fetch_row();
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 자료를 업로드할 폴더 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777); // 0777 권한 설정
    }

    if (!empty($photofile)) {
        $upload_dir = $dir;
        include "upload.php";
    }

    // Prepared Statement를 이용한 안전한 쿼리 실행
    $stmt = $db->prepare("INSERT INTO MlangOrder_PrintAuto 
        (no, ThingNoOkp, ImgFolder, Type, money_1, money_2, money_3, money_4, money_5, OrderName, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, date, OrderStyle, photofileNAME, pass, Designer) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?)");
    
    // 타입 매개변수 추가 및 바인딩
    $types = 'isssssssssssssssssssssss';
    $stmt->bind_param($types, 
        $new_no, $ThingNoOkp, $ImgFolder, 
        "$Type_1 $Type_2 $Type_3 $Type_4 $Type_5 $Type_6", 
        $money_1, $money_2, $money_3, $money_4, $money_5, 
        $OrderName, $email, $zip, $zip1, $zip2, 
        $phone, $Hendphone, $delivery, $bizname, 
        $bank, $bankname, $cont, $date, 
        $OrderStyle, $photofileNAME, $pass, $Designer);

    if ($stmt->execute()) {
        echo "<script>
                alert('정보를 정상적으로 저장하였습니다.');
                opener.parent.location.reload();
                window.close();
              </script>";
    } else {
        echo "<script>
                alert('저장에 실패했습니다.');
              </script>";
    }

    $stmt->close();
    $db->close();
    exit;
}
?>
