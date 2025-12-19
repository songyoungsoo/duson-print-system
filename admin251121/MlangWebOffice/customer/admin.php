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

////////////////// 관리자 로그인 ////////////////////
include"../../../db.php";
include"../../config.php";
////////////////////////////////////////////////////
?>

<?php 
if($mode=="staffForm"){ ///////////////////////////////////////////////////////////////////////////////////////
include"../../title.php";
$Bgcolor1="408080";
if($code=="modify"){include"view_staff.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.name.value == "") {
alert("이름을 입력하여주세요!!");
f.name.focus();
return false;
}

if ((f.tel_1.value.length < 2) || (f.tel_1.value.length > 4)) {
alert("휴대폰의 앞자리는 2자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_1.focus();
return false;
}
if (!TypeCheck(f.tel_1.value, NUM)) {
alert("휴대폰의 앞자리는 숫자로만 사용할 수 있습니다.");
f.tel_1.focus();
return false;
}
if ((f.tel_2.value.length < 3) || (f.tel_2.value.length > 4)) {
alert("휴대폰의 중간자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_2.focus();
return false;
}
if (!TypeCheck(f.tel_2.value, NUM)) {
alert("휴대폰의 중간자리는 숫자로만 사용할 수 있습니다.");
f.tel_2.focus();
return false;
}
if ((f.tel_3.value.length < 3) || (f.tel_3.value.length > 4)) {
alert("휴대폰의 뒷자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_3.focus();
return false;
}
if (!TypeCheck(f.tel_3.value, NUM)) {
alert("휴대폰의 뒷자리는 숫자로만 사용할 수 있습니다.");
f.tel_3.focus();
return false;
}

if (f.work.value == "") {
alert("직책을 입력하여주세요!!");
f.work.focus();
return false;
}

if(f.photofile.value){
<?if($code=="modify"){}else{?>
if((f.photofile.value.lastIndexOf(".jpg")==-1) && (f.photofile.value.lastIndexOf(".gif")==-1))
{
alert("사진 자료등록은 JPG 와 GIF 파일만 하실수 있습니다.");
f.photofile.focus();
return false
}
<?}?>
}

}
//////////////// 이미지 미리보기 //////////////////////////////////
/* 소스제작: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>이미지사진 미리보기</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><a href=\"#\" onClick=\"javascript:window.close();\"><img src=\"" + image + "\" border=\"0\"></a></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='창 닫기' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
</script>
<script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=1>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?if($code=="modify"){?>staffModify_ok<?}else{?>staffForm_ok<?}?>'>
<INPUT TYPE="hidden" name='customer_no' value='<?=$customer_no?>'>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' class='Left1' align=left colspan=2>&nbsp;&nbsp;채용직원정보입력란</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>이름&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="name" size=12 maxLength='5' value='<?if($code=="modify"){echo("$staffViewname");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>휴대폰&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="tel_1" size=9 maxLength='5' value='<?if($code=="modify"){echo("$staffViewtel_1");}?>'>
-
<INPUT TYPE="text" NAME="tel_2" size=9 maxLength='5' value='<?if($code=="modify"){echo("$staffViewtel_2");}?>'>
-
<INPUT TYPE="text" NAME="tel_3" size=9 maxLength='5' value='<?if($code=="modify"){echo("$staffViewtel_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>직책&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="work" size=18 maxLength='20' value='<?if($code=="modify"){echo("$staffViewwork");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>사진&nbsp;&nbsp;</td>
<td>
<?if($code=="modify"){?>
<img src='./upload/staff/<?=$staffViewcustomer_no?>/<?=$staffViewphoto?>' width=30>
<INPUT TYPE="hidden" name='TTFileName' value='<?=$staffViewphoto?>'>
<INPUT TYPE="hidden" name='PHONO' value='<?=$staffViewcustomer_no?>'>
<INPUT TYPE="checkbox" name='PhotoFileModify'> 사진을 변경하려면 체크해주세요!!<BR>
<?}?>
<INPUT TYPE="file" NAME="photofile" size=30 onChange="Mlamg_image(this.value)"><BR>
</td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?if($code=="modify"){?>수정<?}else{?>저장<?}?> 합니다.'>
</td>
</tr>

</table>

<?php 
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="staffForm_ok"){

	$result = mysqli_query($db, "SELECT max(no) FROM WebOffice_customer_staff");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################

// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "./upload/staff/$customer_no";
$dir_handle = is_dir("$dir"); 
if(!$dir_handle){mkdir("$dir", 0755); exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$upload_dir="$dir";
include"upload.php";

$dbinsert ="insert into WebOffice_customer_staff values('$new_no',
'$customer_no',
'$name',  
'$tel_1',   
'$tel_2',
'$tel_3',
'$work',
'$PhotofileName'
)";
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n\\n자료를 새로 등록하시려면 창을 다시 여세요\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysqli_query($db, "SELECT max(no) FROM WebOffice_customer");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################

$upload_dir="./upload";
include"upload.php";

$dbinsert ="insert into WebOffice_customer values('$new_no',
'$bizname',  
'$ceoname',
'$tel_1',   
'$tel_2',
'$tel_3',
'$fax_1',
'$fax_2',  
'$fax_3',
'$zip',   
'$offtel_1',
'$offtel_2', 
'$offtel_3',   
'$offfax_1',    
'$offfax_2', 
'$offfax_3', 
'$PhotofileName',
'$cont '
)";
$result_insert= mysqli_query($db, $dbinsert);

// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "./upload/staff/$new_no"; mkdir("$dir", 0755);  exec("chmod 777 $dir"); 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n\\n자료를 새로 등록하시려면 창을 다시 여세요\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="view"){ ///////////////////////////////////////////////////////////////////////////////////////
include"../../title.php";
$Bgcolor1="408080";
include"view.php";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
.Left2 {font-size:9pt; color:#FFFFFF;}
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000;}
</style>

<script src="../../js/coolbar.js" type="text/javascript"></script>

<script>
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function WebOffice_customer_staff_Del(no){
	if (confirm(+no+'번 직원자료를 삭제처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='<?=$PHP_SELF?>?customer_staff_no='+no+'&mode=customer_staff_delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=100,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=1 align=center width=100% cellpadding=5 cellspacing=1>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>상호&nbsp;&nbsp;</td>
<td><?=$Viewbizname?></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>대표&nbsp;&nbsp;</td>
<td><?=$Viewceoname?></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>본사TEL&nbsp;&nbsp;</td>
<td>
<?=$Viewtel_1?>
-
<?=$Viewtel_2?>
-
<?=$Viewtel_3?>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>본사FAX&nbsp;&nbsp;</td>
<td>
<?=$Viewfax_1?>
-
<?=$Viewfax_2?>
-
<?=$Viewfax_3?>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>주소(본사)&nbsp;&nbsp;</td>
<td><?=$Viewzip?></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장전화&nbsp;&nbsp;</td>
<td>
<?=$Viewofftel_1?>
-
<?=$Viewofftel_2?>
-
<?=$Viewofftel_3?>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장FAX&nbsp;&nbsp;</td>
<td>
<?=$Viewofffax_1?>
-
<?=$Viewofffax_2?>
-
<?=$Viewofffax_3?>
</td>
</tr>

<tr>
<td align=right>
<font style='font:bold; font-size:10pt;'>직원채용현황</font>&nbsp;&nbsp;
</td>
<td>
<input type='button' onClick="javascript:popup=window.open('<?=$PHP_SELF?>?mode=staffForm&customer_no=<?=$row[no]?>', 'WebOffice_customerstaffForm','width=450,height=200,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='채용직원 정보 입력하기'>
</td>
</tr>

<tr><td colspan=2 align=center>
   <table border=1 align=center width=100% cellpadding='5' cellspacing='0'>
     <tr>
	 <td align=center bgcolor='#<?=$Bgcolor1?>' class='Left2'>이름</td>
	 <td align=center bgcolor='#<?=$Bgcolor1?>' class='Left2'>휴대폰</td>
	 <td align=center bgcolor='#<?=$Bgcolor1?>' class='Left2'>직책</td>
	 <td align=center bgcolor='#<?=$Bgcolor1?>' class='Left2'>사진</td>
	 <td align=center bgcolor='#<?=$Bgcolor1?>' class='Left2'>관리</td>
	 </tr>
<?php 
include"../../../db.php";
$result_customer_staff= mysqli_query($db, "select * from WebOffice_customer_staff where customer_no=$no");
$rows=mysqli_num_rows($result_customer_staff);
if($rows){
while($row= mysqli_fetch_array($result_customer_staff)) 
{ 
?>
     <tr>
	 <td align=center><?=$row[name]?></td>
	 <td align=center><?=$row[tel_1]?> - <?=$row[tel_2]?> - <?=$row[tel_3]?></td>
	 <td align=center><?=$row[work]?></td>
	 <td align=center><?if(!$row[photo]){?>사진NO<?}else{?><a href='#' onClick="javascript:window.open('./upload/staff/<?=$no?>/<?=$row[photo]?>', 'dasd12d1nt3wa','top=0,left=0,menubar=yes,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='./upload/staff/<?=$no?>/<?=$row[photo]?>' onload="if(this.width>50){this.width=50}" border=0></a><?}?>
	 </td>
     <td align=center>
	 <input type='button' onClick="javascript:popup=window.open('<?=$PHP_SELF?>?mode=staffForm&customer_no=<?=$row[no]?>&code=modify', 'WebOffice_customerstaffModify','width=450,height=200,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='수정' style='height:18; width:30;'>
     <input type='button' onClick="javascript:WebOffice_customer_staff_Del('<?=$row[no]?>');" value='삭제' style='height:18; width:30;'>
	 </td>
	 </tr>
<?php 
}
}else{echo("<tr><td colspan=4><p align=center><b>등록 자료가 없음.</b></p></td></tr>");}
mysqli_close($db); 
?>
   </table>
</td></tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장약도&nbsp;&nbsp;</td>
<td><a href='#' onClick="javascript:window.open('./upload/<?=$Viewoffmap?>', 'dasd12d1nt3wa','top=0,left=0,menubar=yes,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='./upload/<?=$Viewoffmap?>' onload="if(this.width>500){this.width=500}" border=0></a></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>메모란&nbsp;&nbsp;</td>
<td>
<TEXTAREA NAME="cont" ROWS="5" COLS="60"><?=$Viewcont?></TEXTAREA>
</td>
</tr>

</table>

</body>
</html>

<?php exit; }?>


<?php 
if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../../title.php";
$Bgcolor1="408080";

if($code=="modify"){include"view.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.bizname.value == "") {
alert("상호을 입력하여주세요!!");
f.bizname.focus();
return false;
}

if (f.ceoname.value == "") {
alert("대표을 입력하여주세요!!");
f.ceoname.focus();
return false;
}

if ((f.tel_1.value.length < 2) || (f.tel_1.value.length > 4)) {
alert("본사TEL의 앞자리는 2자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_1.focus();
return false;
}
if (!TypeCheck(f.tel_1.value, NUM)) {
alert("본사TEL의 앞자리는 숫자로만 사용할 수 있습니다.");
f.tel_1.focus();
return false;
}
if ((f.tel_2.value.length < 3) || (f.tel_2.value.length > 4)) {
alert("본사TEL의 중간자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_2.focus();
return false;
}
if (!TypeCheck(f.tel_2.value, NUM)) {
alert("본사TEL의 중간자리는 숫자로만 사용할 수 있습니다.");
f.tel_2.focus();
return false;
}
if ((f.tel_3.value.length < 3) || (f.tel_3.value.length > 4)) {
alert("본사TEL의 뒷자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_3.focus();
return false;
}
if (!TypeCheck(f.tel_3.value, NUM)) {
alert("본사TEL의 뒷자리는 숫자로만 사용할 수 있습니다.");
f.tel_3.focus();
return false;
}

if ((f.fax_1.value.length < 2) || (f.fax_1.value.length > 4)) {
alert("본사FAX의 앞자리는 2자리 이상 4자리 이하를 입력하셔야 합니다.");
f.fax_1.focus();
return false;
}
if (!TypeCheck(f.fax_1.value, NUM)) {
alert("본사FAX의 앞자리는 숫자로만 사용할 수 있습니다.");
f.fax_1.focus();
return false;
}
if ((f.fax_2.value.length < 3) || (f.fax_2.value.length > 4)) {
alert("본사FAX의 중간자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.fax_2.focus();
return false;
}
if (!TypeCheck(f.fax_2.value, NUM)) {
alert("본사FAX의 중간자리는 숫자로만 사용할 수 있습니다.");
f.fax_2.focus();
return false;
}
if ((f.fax_3.value.length < 3) || (f.fax_3.value.length > 4)) {
alert("본사FAX의 뒷자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.fax_3.focus();
return false;
}
if (!TypeCheck(f.fax_3.value, NUM)) {
alert("본사FAX의 뒷자리는 숫자로만 사용할 수 있습니다.");
f.fax_3.focus();
return false;
}

if (f.zip.value == "") {
alert("주소[본사]를 입력하여주세요!!");
f.zip.focus();
return false;
}

if ((f.offtel_1.value.length < 2) || (f.offtel_1.value.length > 4)) {
alert("현장전화의 앞자리는 2자리 이상 4자리 이하를 입력하셔야 합니다.");
f.offtel_1.focus();
return false;
}
if (!TypeCheck(f.offtel_1.value, NUM)) {
alert("현장전화의 앞자리는 숫자로만 사용할 수 있습니다.");
f.offtel_1.focus();
return false;
}
if ((f.offtel_2.value.length < 3) || (f.offtel_2.value.length > 4)) {
alert("현장전화의 중간자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.offtel_2.focus();
return false;
}
if (!TypeCheck(f.offtel_2.value, NUM)) {
alert("현장전화의 중간자리는 숫자로만 사용할 수 있습니다.");
f.offtel_2.focus();
return false;
}
if ((f.offtel_3.value.length < 3) || (f.offtel_3.value.length > 4)) {
alert("현장전화의 뒷자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.offtel_3.focus();
return false;
}
if (!TypeCheck(f.offtel_3.value, NUM)) {
alert("현장전화의 뒷자리는 숫자로만 사용할 수 있습니다.");
f.offtel_3.focus();
return false;
}

if ((f.offfax_1.value.length < 2) || (f.offfax_1.value.length > 4)) {
alert("현장FAX의 앞자리는 2자리 이상 4자리 이하를 입력하셔야 합니다.");
f.offfax_1.focus();
return false;
}
if (!TypeCheck(f.offfax_1.value, NUM)) {
alert("현장FAX의 앞자리는 숫자로만 사용할 수 있습니다.");
f.offfax_1.focus();
return false;
}
if ((f.offfax_2.value.length < 3) || (f.offfax_2.value.length > 4)) {
alert("현장FAX의 중간자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.offfax_2.focus();
return false;
}
if (!TypeCheck(f.offfax_2.value, NUM)) {
alert("현장FAX의 중간자리는 숫자로만 사용할 수 있습니다.");
f.offfax_2.focus();
return false;
}
if ((f.offfax_3.value.length < 3) || (f.offfax_3.value.length > 4)) {
alert("현장FAX의 뒷자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.offfax_3.focus();
return false;
}
if (!TypeCheck(f.offfax_3.value, NUM)) {
alert("현장FAX의 뒷자리는 숫자로만 사용할 수 있습니다.");
f.offfax_3.focus();
return false;
}

if(f.photofile.value){
<?if($code=="modify"){}else{?>
if((f.photofile.value.lastIndexOf(".jpg")==-1) && (f.photofile.value.lastIndexOf(".gif")==-1))
{
alert("사진 자료등록은 JPG 와 GIF 파일만 하실수 있습니다.");
f.photofile.focus();
return false
}
<?}?>
}

}
//////////////// 이미지 미리보기 //////////////////////////////////
/* 소스제작: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {

Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
Mlangwindow.document.open();
Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
Mlangwindow.document.write("<body>");
Mlangwindow.document.write("<p align=center><a href=\"#\" onClick=\"javascript:window.close();\"><img src=\"" + image + "\" border=\"0\"></a></p>");
Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='윈도우 닫기' " + "onClick='window.close()'></p>");
Mlangwindow.document.write("</body></html>");
Mlangwindow.document.close();
  
}
</script>
<script src="../../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?if($code=="modify"){?>modify_ok<?}else{?>form_ok<?}?>'>
<?if($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?=$no?>'><?}?>

<tr>
<td class='coolBar' colspan=2 height=25>
<b>&nbsp;&nbsp;현장 거래처정보 <?if($code=="modify"){?>수정<?}else{?>입력<?}?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>상호&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="bizname" size=50 maxLength='80' value='<?if($code=="modify"){echo("$Viewbizname");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>대표&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="ceoname" size=30 maxLength='20' value='<?if($code=="modify"){echo("$Viewceoname");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>본사TEL&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="tel_1" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewtel_1");}?>'>
-
<INPUT TYPE="text" NAME="tel_2" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewtel_2");}?>'>
-
<INPUT TYPE="text" NAME="tel_3" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewtel_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>본사FAX&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="fax_1" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewfax_1");}?>'>
-
<INPUT TYPE="text" NAME="fax_2" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewfax_2");}?>'>
-
<INPUT TYPE="text" NAME="fax_3" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewfax_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>주소(본사)&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="zip" size=70 maxLength='200' value='<?if($code=="modify"){echo("$Viewzip");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장전화&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="offtel_1" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewofftel_1");}?>'>
-
<INPUT TYPE="text" NAME="offtel_2" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewofftel_2");}?>'>
-
<INPUT TYPE="text" NAME="offtel_3" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewofftel_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장FAX&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="offfax_1" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewofffax_1");}?>'>
-
<INPUT TYPE="text" NAME="offfax_2" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewofffax_2");}?>'>
-
<INPUT TYPE="text" NAME="offfax_3" size=12 maxLength='5' value='<?if($code=="modify"){echo("$Viewofffax_3");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장약도&nbsp;&nbsp;</td>
<td>
<?if($code=="modify"){?>
<img src='./upload/<?=$Viewoffmap?>' width=50><BR>
<INPUT TYPE="hidden" name='TTFileName' value='<?=$Viewoffmap?>'>
<INPUT TYPE="checkbox" name='PhotoFileModify'> 현장약도 사진을 변경하려면 체크해주세요!!<BR>
<?}?>
<INPUT TYPE="file" NAME="photofile" size=30 onChange="Mlamg_image(this.value)"><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>메모란&nbsp;&nbsp;</td>
<td>
<TEXTAREA NAME="cont" ROWS="5" COLS="60"><?if($code=="modify"){echo("$Viewcont");}?></TEXTAREA>
</td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?if($code=="modify"){?>수정<?}else{?>저장<?}?> 합니다.'>
</td>
</tr>

</table>

<?php 
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

// 한 폴더에 대한 전체 파일 삭제후 그 폴더 삭제 //////////////////////////////////
	$Mlang_DIR = opendir("./upload/staff/$no"); // upload 폴더 OPEN
	while($ufiles = readdir($Mlang_DIR)) {
		if(($ufiles != ".") && ($ufiles != "..")) {
			unlink("./upload/staff/$no/$ufiles"); // 파일들 삭제
		}
	}
	closedir($Mlang_DIR);

	rmdir("./upload/staff/$no");  // upload 폴더 삭제

////////////////////////////////////////////////////////////////////////////////////
$resultPHpto= mysqli_query($db, "select * from WebOffice_customer where no='$no'");
$row= mysqli_fetch_array($resultPHpto);
$PHOToDir="./upload/$row[offmap]";
$PHOToFile = join ('', file ("$PHOToDir"));
if($PHOToFile){unlink("$PHOToDir");}
mysqli_query($db, "DELETE FROM WebOffice_customer WHERE no='$no'");
mysqli_query($db, "DELETE FROM WebOffice_customer_staff WHERE customer_no='$no'");
mysqli_close($db);

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 거래처현황 $no번 자료을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="customer_staff_delete"){
//사진자료삭제
$resultPHpto= mysqli_query($db, "select * from WebOffice_customer_staff where no='$customer_staff_no'");
$row= mysqli_fetch_array($resultPHpto);
$PHOToDir="./upload/staff/$row[customer_no]/$row[photo]";
$PHOToFile = join ('', file ("$PHOToDir"));
if($PHOToFile){unlink("$PHOToDir");}

mysqli_query($db, "DELETE FROM WebOffice_customer_staff WHERE no='$customer_staff_no'");
mysqli_close($db);

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 직원채용현황 $no번 자료을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="staffModify_ok"){

if($PhotoFileModify){
$upload_dir="./upload/staff/$PHONO";
include"upload.php";
$YYPjFile="$PhotofileName";
if($TTFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}

$query ="UPDATE WebOffice_customer_staff SET 
 name='$name',
 tel_1='$tel_1',
 tel_2='$tel_2',
 tel_3='$tel_3',
 work='$work',
 photo='$YYPjFile'
WHERE no='$customer_no'";
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
	");
		exit;

}
mysqli_close($db);


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify_ok"){

if($PhotoFileModify){
$upload_dir="./upload";
include"upload.php";
$YYPjFile="$PhotofileName";
if($TTFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}

$query ="UPDATE WebOffice_customer SET 
bizname='$bizname',  
ceoname='$ceoname',
tel_1='$tel_1',   
tel_2='$tel_2',
tel_3='$tel_3',
fax_1='$fax_1',
fax_2='$fax_2',  
fax_3='$fax_3',
zip='$zip',   
offtel_1='$offtel_1',
offtel_2='$offtel_2', 
offtel_3='$offtel_3',   
offfax_1='$offfax_1',    
offfax_2='$offfax_2', 
offfax_3='$offfax_3', 
offmap='$YYPjFile',
cont='$cont'
WHERE no='$no'";
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
	");
		exit;

}
mysqli_close($db);


}
?>