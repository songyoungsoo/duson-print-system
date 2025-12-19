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
if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../../title.php";
$Bgcolor1="408080";

if($code=="modify"){include"CateView.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
window.moveTo(screen.width/5, screen.height/5); 

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.title.value == "") {
alert("회사명 을 입력하여주세요!!");
f.title.focus();
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
<td class='coolBar' colspan=4 height=25>
<b>&nbsp;&nbsp;주요고객회사 <?if($code=="modify"){?>수정<?}else{?>입력<?}?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>연도&nbsp;&nbsp;</td>
<td colspan=3>
<?$YMode="input"; include"Year.php";?>
<?if($View_newy=="yes"){?>
<INPUT TYPE="radio" NAME="newy" value='no'>NO
<INPUT TYPE="radio" NAME="newy" value='yes' checked>YES
<?}else{?>
<INPUT TYPE="radio" NAME="newy" value='no' checked>NO
<INPUT TYPE="radio" NAME="newy" value='yes'>YES
<?}?>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>회사명&nbsp;&nbsp;</td>
<td colspan=3><INPUT TYPE="text" NAME="title" size=50 maxLength='80' value='<?if($code=="modify"){echo("$View_title");}?>'></td>
</tr>

<tr>
<td colspan=4 align=center>
<input type='submit' value=' <?if($code=="modify"){?>수정<?}else{?>저장<?}?> 합니다.'>
</td>
</tr>

</table>

<?php } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

mysqli_query($db, "DELETE FROM MlangHomePage_Customer WHERE no='$no'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

mysqli_close($db);

echo ("
<html>
<script language=javascript>
window.alert('$no번 자료을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="form_ok"){

$dbinsert ="insert into MlangHomePage_Customer values('',
'$Y8y_year',
'$title',
'$newy'
)";
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n');
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form'>
	");
		exit;


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php 
if($mode=="modify_ok"){

$query ="UPDATE MlangHomePage_Customer SET 
BigNo='$Y8y_year',  
title='$title',
newy='$newy'
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
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no'>
	");
		exit;

}
mysqli_close($db);


}
?>