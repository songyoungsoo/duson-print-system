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
<input type='hidden' name='no' value='<?echo("$no");?>'>

<tr>
<td align=center>&nbsp;형식 :&nbsp;</td>
<td> 
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=br&no=<?echo("$no");?>';" name='style' value='br'>BR자동입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=html&no=<?echo("$no");?>';"  name='style' value='html' <?if($code=="html"){echo("checked");}else{if($TT_style=="html"){echo("checked");}}?>>HTML직접입력
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=edit&no=<?echo("$no");?>';"  name='style' value='edit'>HTML 에디터사용
<input type="radio" onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=<?echo("$mode");?>&code=file&no=<?echo("$no");?>';"  name='style' value='file'>파일(업로드)로 입력
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
<input type=text name=SUBJECT size=40 Maxlength=20 value='<?echo("$TT_title");?>'>
</td></tr>

<tr>
<td colspan=2 align=center>
<textarea cols=72 name=connent rows=30><?php if($TT_style=="html"){echo("$TT_connent");}else if($TT_style=="edit"){echo("$TT_connent");}
?></textarea>
</td>
</tr>

<tr><td colspan=2><center><br>
<input type=submit name='mode' value='수정하기'>
<input type=submit name='mode' value='HTML형식미리보기'>
<input type='reset' value=' 다시 작성 '>
<input type=button value='이전화면' onClick='javascript:history.back()'>
<br><br>
</td></tr>

</table>

</form>