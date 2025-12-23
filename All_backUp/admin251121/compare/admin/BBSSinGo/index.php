<?
if($mode=="delete"){

include"../../db.php";
$result = mysql_query("DELETE FROM BBS_Singo WHERE no='$no'");
mysql_close();
	echo ("
		<script language=javascript>
		alert('\\n신고자료 - $no 번 삭제처리 완료\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
exit;
}
?>



<?
if($mode=="view"){
include"../../db.php";

$result= mysql_query("select * from BBS_Singo where no='$no'",$db);
$row= mysql_fetch_array($result);
if($row[AdminSelect]=="1"){
$query ="UPDATE BBS_Singo SET AdminSelect='2' WHERE no='$no'";
$result= mysql_query($query,$db);
	echo ("
		<script language=javascript>
        opener.parent.location.reload();
		</script>
	");
}
?>

<?= htmlspecialchars($row[Cont]);?>

<p align=center>
<input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style='background-color:#FFFFFF; color:#539D26; border-width:1; border-style:solid; height:21px; border:1 solid #539D26;'>
</p>

<?
mysql_close($db); 
exit;
}
?>


<?
$M123="..";
include"../top.php"; 
?>

<head>
<script>
function clearField(field)
{
	if (field.value == field.defaultValue) {
		field.value = "";
	}
}
function checkField(field)
{
	if (!field.value) {
		field.value = field.defaultValue;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function Member_Admin_Del(no){
	if (confirm(+no+'번 자료를 삭제 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='<?=$PHP_SELF?>?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function TDsearchCheckField()
{
var f=document.TDsearch;

if (f.TDsearchValue.value == "") {
alert("검색할 검색어 값을 입력해주세요");
f.TDsearchValue.focus();
return false;
}

}
</script>

</head>


<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=right>

   <table border=0 align=center width=100% cellpadding=2 cellspacing=0>
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?=$PHP_SELF?>'>
	    <td align=left>
		<b>간단 검색 :&nbsp;</b>
		<select name='TDsearch'>
		<option value='id'>회원아이디</option>
		<option value='name'>회원이름</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' 검 색 '>
	    </td>
		</form>
	 </tr>
  </table>

</td>
</tr>
</table>
<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>신고회원</td>
<td align=center>신고날짜</td>
<td align=center>테이블, 번호</td>
<td align=center>확인여부</td>
<td align=center>관리기능</td>
</tr>

<?
include"../../db.php";
$table="BBS_Singo";

if($search=="yes"){ //검색모드일때

function ERROR($msg)
{
echo ("<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
");
exit;
}

}else if($TDsearchValue){ // 회원 간단검색 TDsearch //  TDsearchValue

$Mlang_query="select * from $table where $TDsearch like '%$TDsearchValue%'";

}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 12;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row[no]?></font></td>
<td><font color=white><?= htmlspecialchars($row[Member_id]);?></font></td>
<td align=center><font color=white><?= htmlspecialchars($row[date]);?></font></td>
<td align=center><a href='<?=$Homedir?>/bbs/bbs.php?table=<?=$row[BBS_table]?>&mode=list' target='_blank'><font color=white><?=$row[BBS_table]?> - <?=$row[BBS_no]?></font></a></td>
<td align=center><font color=white><?if($row[AdminSelect]=="1"){echo("<font color=red>미확인</font>");}else{echo("<font color=#FFFFFF>확인</font>");}?></font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?=$PHP_SELF?>?mode=view&no=<?=$row[no]?>', 'afcas12s','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='신고 내용보기'>
<input type='button' onClick="javascript:Member_Admin_Del('<?=$row[no]?>');" value=' 삭제 '>
</td>
<tr>

<?
		$i=$i+1;
} 


}else{

if($search){
echo"<tr><td colspan=10><p align=center><BR><BR>관련 검색 자료없음</p></td></tr>";
}else if($TDsearchValue){ // 회원 간단검색 TDsearch //  TDsearchValue
echo"<tr><td colspan=10><p align=center><BR><BR>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료없음</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
}

}

?>


</table>



<p align='center'>

<?
if($rows){

$mlang_pagego="cate=$cate$title_search=$title_search"; // 필드속성들 전달값

$pagecut= 7;  //한 장당 보여줄 페이지수 
$one_bbs= $listcut*$pagecut;  //한 장당 실을 수 있는 목록(게시물)수 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  //각 장에 처음 페이지의 $offset값. 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //마지막 장의 첫페이지의 $offset값. 
$start_page= intval($start_offset/$listcut)+1; //각 장에 처음 페이지의 값. 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
//마지막 장의 끝 페이지. 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset){
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); } 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
} 
echo "총목록갯수: $end_page 개"; 


}

mysql_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?
include"../down.php";
?>