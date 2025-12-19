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
if($mode=="SoForm"){ ///////////////////////////////////////////////////////////////////////////////////////
include"../../title.php";
$Bgcolor1="408080";
if($no){include"view_admin.php";}else{
	echo ("
		<script language=javascript>
		alert('거래내역서를 입력하려면 제출처, 현장명등의 등록NO 정보가 필요한데\\n\\n그 자료가 없습니다. 창을 다시 여세요');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;
}
?>

<?include"SoList.php";?>

<?php 
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SoForm_ok"){

	$result = mysqli_query($db, "SELECT max(no) FROM MlangWebOffice_Biz_particulars");
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

$dbinsert ="insert into MlangWebOffice_Biz_particulars values('$new_no',
'$admin_no',
'$biz_date',
'$kinds',
'$fitting_no',
'$engineer_name',
'$money',
'$remark'
)";
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('자료 저장 OK');
		</script>
	<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$admin_no'>
	");
		exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php 
if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../../title.php";
$Bgcolor1="408080";

if($code=="modify"){include"view_admin.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
self.moveTo(0,0)
<?if($code=="modify"){?>
self.resizeTo(availWidth=780,screen.availHeight)	
<?}else{?>
self.resizeTo(availWidth=630,availHeight=190)
<?}?> 

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

if (f.biz_name.value == "") {
alert("제출처 을 입력하여주세요!!");
f.biz_name.focus();
return false;
}

if (f.a_name.value == "") {
alert("담당자 을 입력하여주세요!!");
f.a_name.focus();
return false;
}

if (f.b_name.value == "") {
alert("현장명 을 입력하여주세요!!");
f.b_name.focus();
return false;
}

if ((f.tel_1.value.length < 2) || (f.tel_1.value.length > 4)) {
alert("연락처 의 앞자리는 2자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_1.focus();
return false;
}
if (!TypeCheck(f.tel_1.value, NUM)) {
alert("연락처 의 앞자리는 숫자로만 사용할 수 있습니다.");
f.tel_1.focus();
return false;
}
if ((f.tel_2.value.length < 3) || (f.tel_2.value.length > 4)) {
alert("연락처 의 중간자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_2.focus();
return false;
}
if (!TypeCheck(f.tel_2.value, NUM)) {
alert("연락처 의 중간자리는 숫자로만 사용할 수 있습니다.");
f.tel_2.focus();
return false;
}
if ((f.tel_3.value.length < 3) || (f.tel_3.value.length > 4)) {
alert("연락처 의 뒷자리는 3자리 이상 4자리 이하를 입력하셔야 합니다.");
f.tel_3.focus();
return false;
}
if (!TypeCheck(f.tel_3.value, NUM)) {
alert("연락처 의 뒷자리는 숫자로만 사용할 수 있습니다.");
f.tel_3.focus();
return false;
}

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
<td class='coolBar' colspan=3 height=25>
<b>&nbsp;&nbsp;거래내역서 <?if($code=="modify"){?>수정<?}else{?>입력<?}?></b><BR>
</td>
<td align=right><font color='#336666'>등록NO: <b><big><?=$no?></big></b>&nbsp;&nbsp;</font></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>제출처&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="biz_name" size=25 maxLength='80' value='<?if($code=="modify"){echo("$Viewbiz_name");}?>'></td>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>담당자&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="a_name" size=25 maxLength='20' value='<?if($code=="modify"){echo("$Viewa_name");}?>'></td>
</tr>


<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>현장명&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="b_name" size=25 maxLength='20' value='<?if($code=="modify"){echo("$Viewb_name");}?>'></td>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>본사TEL&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="tel_1" size=7 maxLength='5' value='<?if($code=="modify"){echo("$Viewtel_1");}?>'>
-
<INPUT TYPE="text" NAME="tel_2" size=7 maxLength='5' value='<?if($code=="modify"){echo("$Viewtel_2");}?>'>
-
<INPUT TYPE="text" NAME="tel_3" size=7 maxLength='5' value='<?if($code=="modify"){echo("$Viewtel_3");}?>'>
</td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?if($code=="modify"){?>수정<?}else{?>입력<?}?> 합니다.'>
<BR><BR>
</td>
</tr>
</form>
</table>

<?if($code=="modify"){ include"SoList.php"; }?> 

<?php 
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysqli_query($db, "SELECT max(no) FROM MlangWebOffice_Biz_particulars_admin");
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


$dbinsert ="insert into MlangWebOffice_Biz_particulars_admin values('$new_no',
'$id',
'$biz_name',  
'$a_name',
'$b_name',
'$tel_1',   
'$tel_2',
'$tel_3'
)";
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n\\n거래내역 정보를 입력할 페이지로 바로 이동하겠습니다.\\n');
        opener.parent.location.reload();
		</script>
	<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$new_no'>
	");
		exit;


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SoDelete"){

mysqli_query($db, "DELETE FROM MlangWebOffice_Biz_particulars WHERE no='$no'");
mysqli_close($db);

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 자료을 삭제 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="BigDelete"){

mysqli_query($db, "DELETE FROM MlangWebOffice_Biz_particulars_admin WHERE no='$no'");
/////////////////////////////////////
$result_SO= mysqli_query($db, "select * from MlangWebOffice_Biz_particulars where admin_no='$no'");
$rows_SO=mysqli_num_rows($result_SO);
if($rows_SO){
while($row_SO= mysqli_fetch_array($result_SO)) 
{ mysqli_query($db, "DELETE FROM MlangWebOffice_Biz_particulars WHERE no='$row_SO[no]'"); }
}
/////////////////////////////////////
mysqli_close($db);

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 제출처 정보와 거래내역의 자료 $no번 을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="SoModifypart99"){

$query ="UPDATE MlangWebOffice_Biz_particulars SET 
biz_date='$biz_date',
kinds='$kinds',
fitting_no='$fitting_no',
engineer_name='$engineer_name',
money='$money',
remark='$remark'
WHERE no='$SoTyuno'";
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
		</script>");

if($offset){$INH="&offset=$offset";}

if($TDsearch){
	echo("<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$Big_no&TDsearch=$TDsearch&TDsearchValue=$TDsearchValue$INH'>");
}else{
	echo("<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=SoForm&no=$Big_no$INH'>");
}

		exit;

}
mysqli_close($db);


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify_ok"){

$query ="UPDATE MlangWebOffice_Biz_particulars_admin SET 
id='$id',
biz_name='$biz_name',
a_name='$a_name',
b_name='$b_name',
tel_1='$tel_1',
tel_2='$tel_2',
tel_3='$tel_3'
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