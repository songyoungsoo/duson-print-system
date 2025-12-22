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

if($code=="br"){ ///////////////////////////////////////////////////////////////////////////////////////////////  ?>

<head>
<script language=javascript>

function EditCheckField()
{
var f=document.EditInfo;

if (f.cate.value == "0") {
alert("위치를 선택하여주세요!!");
return false;
}
if (f.SUBJECT.value == "") {
alert("페이지 제목을 입력하여주세요!!");
return false;
}
if (f.connent.value == "") {
alert("페이지 내용을 입력하여주세요!!");
return false;
}

var winopts = "width=780,height=590,toolbar=no,location=no,directories=no, status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
var popup = window.open('','POPWIN', winopts);
popup.focus();

}

</script>

</head>

<table border="0" cellpadding="0" cellspacing="0" width="100%" class='coolBar'>

<form name='EditInfo' method='post' OnSubmit='javascript:return EditCheckField()'  target=POPWIN  action='./editor/submit_ok.php'>

<tr>
<td align=center>&nbsp;형식 :&nbsp;</td>
<td> 
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=br&no=<?echo("$no");?>';" name='style' value='br' <?if($code=="br"){echo("checked");}?>>BR자동입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=html&no=<?echo("$no");?>';"  name='style' value='html' <?if($code=="html"){echo("checked");}?>>HTML직접입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=edit&no=<?echo("$no");?>';"  name='style' value='edit' <?if($code=="edit"){echo("checked");}?>>HTML 에디터사용
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=file&no=<?echo("$no");?>';"  name='style' value='file' <?if($code=="file"){echo("checked");}?>>파일(업로드)로 입력
</td>
</tr>

<tr>
<td align=center>&nbsp;위치 :&nbsp;</td>
<td>
<?php include"../../db.php";
$result= mysqli_query($db, "select * from $page_big_table");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$rows=mysqli_num_rows($result);
if($rows){
echo("<select name='cate'><option value='0'>-선택하세요-</option>");
while($row= mysqli_fetch_array($result)) 
{ 
if($row[no]==$TT_cate){echo("<option value='$row[no]' selected>$row[title]</option>");}else{echo("<option value='$row[no]'>$row[title]</option>");}
}
echo("</select>");
}else{echo("&nbsp;&nbsp;&nbsp;<b>등록 자료가 없음.(주메뉴를등록해주세요!!)</b>");}

mysqli_close($db); 
?>

&nbsp;&nbsp;&nbsp;&nbsp;제 목 :&nbsp;
<input type=text name=SUBJECT size=40 Maxlength=20>
</td></tr>

<tr>
<td colspan=2 align=center>
<textarea cols=72 name=connent rows=30></textarea>
</td>
</tr>

<tr><td colspan=2><center><br>
<input type=submit name='mode' value='저장하기'>
<input type=submit name='mode' value='BR형식미리보기'>
<input type='reset' value=' 다시 작성 '>
<input type=button value='이전화면' onClick='javascript:history.back()'>
<br><br>
</td></tr>

</table>

</form>


<?php }else if($code=="html"){ //////////////////////////////////////////////////////////////////////////////////// ?>


<head>
<script language=javascript>

function EditCheckField()
{
var f=document.EditInfo;

if (f.cate.value == "0") {
alert("위치를 선택하여주세요!!");
return false;
}
if (f.SUBJECT.value == "") {
alert("페이지 제목을 입력하여주세요!!");
return false;
}
if (f.connent.value == "") {
alert("페이지 내용을 입력하여주세요!!");
return false;
}

var winopts = "width=780,height=590,toolbar=no,location=no,directories=no, status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
var popup = window.open('','POPWIN', winopts);
popup.focus();

}

</script>

</head>

<table border="0" cellpadding="0" cellspacing="0" width="100%" class='coolBar'>

<form name='EditInfo' method='post' OnSubmit='javascript:return EditCheckField()'  target=POPWIN  action='./editor/submit_ok.php'>

<tr>
<td align=center>&nbsp;형식 :&nbsp;</td>
<td> 
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=br&no=<?echo("$no");?>';" name='style' value='br' <?if($code=="br"){echo("checked");}?>>BR자동입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=html&no=<?echo("$no");?>';"  name='style' value='html' <?if($code=="html"){echo("checked");}?>>HTML직접입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=edit&no=<?echo("$no");?>';"  name='style' value='edit' <?if($code=="edit"){echo("checked");}?>>HTML 에디터사용
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=file&no=<?echo("$no");?>';"  name='style' value='file' <?if($code=="file"){echo("checked");}?>>파일(업로드)로 입력
</td>
</tr>

<tr>
<td align=center>&nbsp;위치 :&nbsp;</td>
<td>
<?php include"../../db.php";
$result= mysqli_query($db, "select * from $page_big_table");
$rows=mysqli_num_rows($result);
if($rows){
echo("<select name='cate'><option value='0'>-선택하세요-</option>");
while($row= mysqli_fetch_array($result)) 
{ 
echo("<option value='$row[no]'>$row[title]</option>");
}
echo("</select>");
}else{echo("&nbsp;&nbsp;&nbsp;<b>등록 자료가 없음.(주메뉴를등록해주세요!!)</b>");}

mysqli_close($db); 
?>

&nbsp;&nbsp;&nbsp;&nbsp;제 목 :&nbsp;
<input type=text name=SUBJECT size=40 Maxlength=20>
</td></tr>

<tr>
<td colspan=2 align=center>
<textarea cols=72 name=connent rows=30></textarea>
</td>
</tr>

<tr><td colspan=2><center><br>
<input type=submit name='mode' value='저장하기'>
<input type=submit name='mode' value='HTML형식미리보기'>
<input type='reset' value=' 다시 작성 '>
<input type=button value='이전화면' onClick='javascript:history.back()'>
<br><br>
</td></tr>

</table>

</form>

<?php }else if($code=="file"){ ////////////////////////////////////////////////////////////////////////////////////// ?>

<head>
<script language=javascript>

function EditCheckField()
{
var f=document.EditInfo;

if (f.cate.value == "0") {
alert("위치를 선택하여주세요!!");
return false;
}
if (f.SUBJECT.value == "") {
alert("페이지 제목을 입력하여주세요!!");
return false;
}
if (f.FILELINK.value == "") {
alert("업로드할 파일을 불러 주십시요!!");
return false;
}
if((f.FILELINK.value.lastIndexOf(".php")==-1) && (f.FILELINK.value.lastIndexOf(".php3")==-1) && (f.FILELINK.value.lastIndexOf(".htm")==-1) && (f.FILELINK.value.lastIndexOf(".html")==-1))
{
alert("업로드할 파일의 확장자가 php, php3, htm, html이 아닙니다.\n\n다시 불러와 주시기 바랍니다........")
return false
}

var winopts = "width=780,height=590,toolbar=no,location=no,directories=no, status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
var popup = window.open('','POPWIN', winopts);
popup.focus();

}

</script>

</head>

<table border="0" cellpadding="0" cellspacing="0" width="100%" class='coolBar'>

<form name='EditInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return EditCheckField()'  target=POPWIN  action='./editor/submit_ok.php'>

<tr>
<td align=center>&nbsp;형식 :&nbsp;</td>
<td> 
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=br&no=<?echo("$no");?>';" name='style' value='br' <?if($code=="br"){echo("checked");}?>>BR자동입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=html&no=<?echo("$no");?>';"  name='style' value='html' <?if($code=="html"){echo("checked");}?>>HTML직접입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=edit&no=<?echo("$no");?>';"  name='style' value='edit' <?if($code=="edit"){echo("checked");}?>>HTML 에디터사용
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=file&no=<?echo("$no");?>';"  name='style' value='file' <?if($code=="file"){echo("checked");}?>>파일(업로드)로 입력
</td>
</tr>

<tr>
<td align=center>&nbsp;위치 :&nbsp;</td>
<td>
<?php include"../../db.php";
$result= mysqli_query($db, "select * from $page_big_table");
$rows=mysqli_num_rows($result);
if($rows){
echo("<select name='cate'><option value='0'>-선택하세요-</option>");
while($row= mysqli_fetch_array($result)) 
{ 
echo("<option value='$row[no]'>$row[title]</option>");
}
echo("</select>");
}else{echo("&nbsp;&nbsp;&nbsp;<b>등록 자료가 없음.(주메뉴를등록해주세요!!)</b>");}

mysqli_close($db); 
?>

&nbsp;&nbsp;&nbsp;&nbsp;제 목 :&nbsp;
<input type=text name=SUBJECT size=40 Maxlength=20>
</td></tr>


<tr>
<td align=center colspan=2>
<BR>
* 업로드할 파일은 확장자 php, php3, html, htm 만 업로드 가능합니다.
</td>
</tr>

<tr>
<td align=center>&nbsp;업로드파일 :&nbsp;</td>
<td>
<input type='file' name='FILELINK' size=50>
</td>
</tr>

<tr><td colspan=2><center><br>
<input type=submit name='mode' value='저장하기'>
<input type=submit name='mode' value='업로드형식미리보기'>
<input type='reset' value=' 다시 작성 '>
<input type=button value='이전화면' onClick='javascript:history.back()'>
<br><br>
</td></tr>

</table>

</form>

<?php }else if($code=="edit"){  ///////////////////////////////////////////////////////////////////////////////////// ?>

<script language=javascript src='./editor/editor.js'></script>

<Form Name=mailsendform Method=POST enctype='multipart/form-data'>
<input type=hidden name=return_url value='/admin/page_submit.php?mode=form&code=edit'>
<input type=hidden name=CONTENT value=''>


<table border="0" cellpadding="0" cellspacing="0" width="100%" class='coolBar'>

<tr>
<td align=center>&nbsp;형식 :&nbsp;</td>
<td> 
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=br&no=<?echo("$no");?>';" name='style' value='br' <?if($code=="br"){echo("checked");}?>>BR자동입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=html&no=<?echo("$no");?>';"  name='style' value='html' <?if($code=="html"){echo("checked");}?>>HTML직접입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=edit&no=<?echo("$no");?>';"  name='style' value='edit' <?if($code=="edit"){echo("checked");}?>>HTML 에디터사용
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=file&no=<?echo("$no");?>';"  name='style' value='file' <?if($code=="file"){echo("checked");}?>>파일(업로드)로 입력
</td>
</tr>

<tr>
<td align=center>&nbsp;위치 :&nbsp;</td>
<td>
<?php include"../../db.php";
$result= mysqli_query($db, "select * from $page_big_table");
$rows=mysqli_num_rows($result);
if($rows){
echo("<select name='cate'><option value='0'>-선택하세요-</option>");
while($row= mysqli_fetch_array($result)) 
{ 
echo("<option value='$row[no]'>$row[title]</option>");
}
echo("</select>");
}else{echo("&nbsp;&nbsp;&nbsp;<b>등록 자료가 없음.(주메뉴를등록해주세요!!)</b>");}

mysqli_close($db); 
?>

&nbsp;&nbsp;&nbsp;&nbsp;제 목 :&nbsp;
<input type=text name=SUBJECT size=40 Maxlength=20>
</td></tr>

<tr>
<td colspan=2 align=center>
<iframe name="editor" src="editor/editor.html" marginheight="0" marginwidth="0" frameborder="0" width="100%" height="450" scrolling="yes"></iframe>
</td>
</tr>

<tr><td colspan=2><center><br>
<input type=hidden name=mode value='저장하기'>
<input type=button value='저장하기' onClick="javascript:return jsSubmit('');">
<input type=button value='Edit사용 미리보기' onClick="javascript:return jsPreview()">
<input type='reset' value=' 다시 작성 '>
<input type=button value='이전화면' onClick='javascript:history.back()'>
<br><br>
</td></tr>

</table>

</form>

<?php }else{

echo ("<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=$mode&code=br'>");

}

?>