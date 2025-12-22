<?
$M123="..";
include"../top.php"; 

$PageCode="Customer";
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
function WebOffice_customer_Del(no){
	if (confirm(+no+'번 자료를 삭제 하시겠습니까..?\n\n최상위 일경우 하위항목까지 삭제가 됩니다.\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='./<?=$PageCode?>/CateAdmin.php?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

</head>

<?
include"../../db.php";
$table="MlangHomePage_Customer";

if($HomePage_YearCate){ // 검색
$Mlang_query="select * from $table where BigNo='$HomePage_YearCate'";
}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

$query= mysql_query("$Mlang_query",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 30;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 
?>


<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
<font color=red>*</font> 자료 입력/수정 시 <b><u>YES</u></b>를 선택하면 메인 페이지에만 호출 됩니다.
</td>
</tr>
<tr>
<td>
   <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
     <tr>
	    <td align=left>
		<b>연도별로 따로보기 :&nbsp;</b>
		<?$YMode="Location"; include"./Customer/Year.php";?>
	    </td>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
<input type='button' onClick="javascript:popup=window.open('./<?=$PageCode?>/CateAdmin.php?mode=form', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 정보 입력하기 '>
</td>
</tr>
</table>


<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록NO</td>
<td align=center>연도</td>
<td align=center>주요고객회사</td>
<td align=center>추천여부</td>
<td align=center>관리기능</td>
</tr>

<?
$result= mysql_query("$Mlang_query order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){


while($row= mysql_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row[no]?></font></td>
<td align=center><font color=white><?=$row[BigNo]?></font></td>
<td>&nbsp;&nbsp;<font color=white><?=$row[title]?></font>&nbsp;&nbsp;</td>
<td align=center><font color=white>
<?if($row[newy]=="no"){echo("NO");}else{echo("<font style='color:#66CCFF; font:bold;'>YES</font>");}?>
</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./<?=$PageCode?>/CateAdmin.php?mode=form&code=modify&no=<?=$row[no]?>', 'WebOffice_<?=$PageCode?>Modify','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?=$row[no]?>');" value=' 삭제 '>
</td>
<tr>

<?
		$i=$i+1;
} 


}else{

if($HomePage_YearCate){
echo"<tr><td colspan=10><p align=center><b>$HomePage_YearCate</b> 연도<BR><BR> 검색 자료없음</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
}

}

?>


</table>



<p align='center'>

<?
if($rows){

if($HomePage_YearCate){
       $mlang_pagego="HomePage_YearCate=$HomePage_YearCate&offset=$offset"; // 필드속성들 전달값
}else{
     $mlang_pagego="offset=$offset"; // 필드속성들 전달값
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