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
function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.title.value == "") {
alert("제목 을 입력하여주세요!!");
f.title.focus();
return false;
}

<?if($code=="modify"){}else{?>

if(f.photofile.value==""){
alert("동영상파일을 업로드해 주세염 *^^*\n\n");
f.photofile.focus();
return false
}

if((f.photofile.value.lastIndexOf(".asf")==-1) && (f.photofile.value.lastIndexOf(".avi")==-1) && (f.photofile.value.lastIndexOf(".wma")==-1)){
alert("동영상파일은 asf , avi , wma 파일만 업로드 하실수 있습니다.\n\n");
f.photofile.focus();
return false
}

if(f.photofile.value.lastIndexOf("\"") > -1){
alert("동영상파일에 \" 쌍따옴표는 입력하실수 없습니다.\n\n");
f.photofile.focus();
return false
}

<?}?>

if (f.cont.value.length < 10 ) {
alert("내용을 입력하지 않았거나 너무 짧습니다.\n\n");
f.cont.focus();
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
<b>&nbsp;&nbsp;동영상보기 <?if($code=="modify"){?>수정<?}else{?>입력<?}?></b><BR>
</td>
</tr>

<!------------ 카테고리 기능 추후 추가 -------------
<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>카테고리&nbsp;&nbsp;</td>
<td colspan=3>
</td>
</tr>----------------->

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>제목&nbsp;&nbsp;</td>
<td colspan=3><INPUT TYPE="text" NAME="title" size=50 maxLength='80' value='<?if($code=="modify"){echo("$View_title");}?>'></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>동영상파일&nbsp;&nbsp;</td>
<td colspan=3>
<?if($code=="modify"){?>
<INPUT TYPE="hidden" name='TTFileName' value='<?=$View_upfile?>'> 
현재파일명: <?=$View_upfile?><BR>
<INPUT TYPE="checkbox" name='PhotoFileModify'> 동영상 파일을 변경하려면 체크해주세요!!<BR>
<?}?>
<INPUT TYPE="file" NAME="photofile" size=50 maxLength='80' value='<?if($code=="modify"){echo("$View_upfile");}?>'>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>내용문서형식&nbsp;&nbsp;</td>
<td colspan=3>
<select name='cont_style'>
<option value='br' <?if($code=="modify"){if($View_ContStyle=="br"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?}}?>>자동 BR</option>
<!---<option value='html' <?if($code=="modify"){if($View_ContStyle=="html"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?}}?>>HTML 직접입력</option>--->
</select>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>내용&nbsp;&nbsp;</td>
<td colspan=3>
<TEXTAREA NAME="cont" ROWS="15" COLS="70"><?if($code=="modify"){echo("$View_cont");}?></TEXTAREA>
</td>
</tr>

<tr>
<td colspan=4 align=center>
<input type='submit' value=' <?if($code=="modify"){?>수정<?}else{?>저장<?}?> 합니다.'>
</td>
</tr>

</table>

<?php } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

include"CateView.php";
$upload_dir="./upload";
if($View_upfile){unlink("$upload_dir/$View_upfile");}

mysqli_query($db, "DELETE FROM MlangHomePage_Movic WHERE no='$no'");
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

if($photofile){
	$upload_dir="./upload";
	include"upload.php";
	}

$dbinsert ="insert into MlangHomePage_Movic values('',
'$cate',
'$title',
'$PhotofileName',
'$cont_style',
'$cont'
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

if($PhotoFileModify){ 
       if(!$photofile){
	           echo ("<script language=javascript>
                             window.alert('파일을 수정한다고 체크하셧는데 업로드할 파일이 빠져 있어요 ㅜㅜ');
                               history.go(-1);
                             </script>");
                                               exit;
	        }
	$upload_dir="./upload";
	include"upload.php";
$YYPjFile="$PhotofileName";
if($TTFileName){unlink("$upload_dir/$TTFileName");}
}else{
$YYPjFile="$TTFileName";
}

$query ="UPDATE MlangHomePage_Movic SET 
cate='$cate',
title='$title',
upfile='$YYPjFile',
ContStyle='$cont_style',
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
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no'>
	");
		exit;

}
mysqli_close($db);


}
?>