<!------------------------------------------- 리스트 시작----------------------------------------->
<?php include"$M123/../db.php";
$table="Mlang_${id}_Results";

if($search){$Mlang_query="select * from $table where $search_cate like '%$search%'";}else{$Mlang_query="select * from $table";}


$query= mysqli_query($db, "$Mlang_query");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);
?>

<table border=0 align=center width=100% cellpadding=0 cellspacing=0  class='coolBar'>
<tr>
<td width=20></td>
<td>
실적물프로그램 테이블명: <b><?=$DataAdminFild_title?></b>&nbsp;(<?=$total?>)&nbsp;&nbsp;
<input type='button' onClick="javascript:popup=window.open('data_submit.php?id=<?=$id?>&mode=submit', 'data_submit','width=600,height=300,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='자료 등록창 열기'>
</td>
<td height=40 align=right>

<table border=0 cellpadding=0 cellspacing=0><tr>
<td width=20></td>
<head>
<script language=javascript>
function SrarchCheckField()
{
var f=document.SrarchInfo;

if (f.search.value == "") {
alert("검색할 내용을 입력하세요!!");
return false;
}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function ResultsDelTT(no){
	var str;
		if (confirm("삭제한 자료는 두번 다시 복구 되지 않습니다.\n\n삭제할 자료가 확실하시면 실행하십시요!!")) {
		// 여기서 style 의 기능이 추가되었다  bbstop 는 보드에만 있기때문에 추후 활용가능하다. 
		str='/admin/int/delete.php?no='+no+'&table=<?=$table?>&bbs=del&file=ok&id=<?=$id?>&style=results';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
</script>
</head>

<form name='SrarchInfo' method='post' OnSubmit='javascript:return SrarchCheckField()' action='<?echo("$PHP_SELF");?>'>
<td>

<select name=search_cate>
<option value='Mlang_bbs_title'>제목</font> 
<option value='Mlang_bbs_connent'>내용</font> 
</select>

<input type='hidden' name='mode' value='<?=$mode?>'>
<input type='hidden' name='id' value='<?=$id?>'>
<input type='text' name='search' size='25'>
<input type='submit' value='검색'>
</td>
</form>
<td>
&nbsp;&nbsp;
<input type='button' onClick="javascript:window.location.href='<?echo("$PHP_SELF");?>?mode=list&id=<?=$id?>';" value='전체목록보기' style='width:80;'>
<input type='button' onClick="javascript:window.location.reload();" value='새로고침' style='width:60;'>
&nbsp;
</tr></table>

</td></tr>
</table>


<?php $listcut= 15;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

$result=mysqli_query($db, "$Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut");
$rows=@mysqli_num_rows($result);
if($rows){

echo("
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#666600'>
<tr>
<td align=center height=30><font color=white>등록번호</font></td>");

if($DataAdminFild_celect){echo("<td align=center><font color=white>항목</font></td>");}

if($DataAdminFild_item=="text"){
echo("<td align=center><font color=white>제작사</font></td>
<td align=center><font color=white>제작물</font></td>
<td align=center><font color=white>관리기능</font></td>
</tr>	
");
}else{
echo("
<td align=center><font color=white>제목</font></td>
<td align=center><font color=white>관리기능</font></td>
</tr>	
");
}


$i=1+$offset;
while($row= mysqli_fetch_array($result)) 
{ 
?>

<?php echo("
<tr bgcolor='#FFFFFF'>
<td>&nbsp;&nbsp;$row[Mlang_bbs_no]&nbsp;</td>");

if($DataAdminFild_celect){echo("<td align=center>$row[Mlang_bbs_link]</td>");}

if ($search) //검색 키워드값
{$row[Mlang_bbs_title] = str_replace($search, "<b><FONT COLOR=RED>$search</FONT></b>", $row[Mlang_bbs_title]);}
if ($search) //검색 키워드값
{$row[Mlang_bbs_connent] = str_replace($search, "<b><FONT COLOR=blue>$search</FONT></b>", $row[Mlang_bbs_connent]);}

if($row[Mlang_bbs_title]){
echo("<td><a href='/results/index.php?table=$id&mode=view&no=$row[Mlang_bbs_no]' target='_blank'>$row[Mlang_bbs_title]</a></td>");
}else{
echo("<td><a href='/results/index.php?table=$id&mode=view&no=$row[Mlang_bbs_no]' target='_blank'>제목없음</a></td>");
}

echo("
<td align=center>
<input type='button' onClick=\"javascript:popup=window.open('data_submit.php?id=$id&mode=modify&no=$row[Mlang_bbs_no]', 'data_submit','width=600,height=300,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value='수정' style='width:50;'>
<input type='button' onClick=\"javascript:ResultsDelTT('$row[Mlang_bbs_no]');\" value='삭제'  style='width:50;'>	
</td>
</tr>
");		
		
		$i=$i+1;
} 

echo("</table>");

}else{
	
if($search){
	echo"<p align=center><b>$search</b> 에 관련된 등록 자료없음</p>";
}else{
	echo"<p align=center><b>등록 자료없음</b></p>";
}	

}

?>

<p align='center'>

<?php if($rows){

if($search){
$mlang_pagego="mode=$mode&id=$id&search_cate=$search_cate&search=$search"; // 필드속성들 전달값
}else{
$mlang_pagego="mode=$mode&id=$id"; // 필드속성들 전달값
}

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

if($offset!= $newoffset) 
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>"; 
echo "[$i]"; 
if($offset!= $newoffset) 
  echo "</a>&nbsp;"; 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
} 
echo "총목록갯수: $end_page 개"; 


}

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->	  




</td></tr>
</table>