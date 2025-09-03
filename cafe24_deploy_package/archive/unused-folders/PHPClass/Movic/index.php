<!------------------------------------------- 리스트 시작----------------------------------------->
<?php
include "$HomePageMovicDir/db.php";

$Mlang_query="select * from MlangHomePage_Movic";

$query= mysqli_query("$Mlang_query",$db);
$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 30;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

echo(" <table border=0 align=center width=80% cellpadding=0 cellspacing=0>
<tr bgcolor='#F2F2F2'>
   <td height=30>&nbsp;no&nbsp;</td>
   <td>&nbsp;동영상 제목&nbsp;</td>
   <td align=right>자료수($total)&nbsp;</td>
</tr>
<tr>
<td colspan=3 height=1 bgcolor='#B6B6B6'></td>
</tr>
");

$result= mysqli_query("$Mlang_query order by new='yes' desc, no desc limit $offset,$listcut",$db);
$rows=mysqli_num_rows($result);
if($rows){

$i=1+$offset;
while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr>
   <td height=30>&nbsp;<?php echo $i?>&nbsp;</td>
   <td colspan=2>&nbsp;<a href='#' onClick="javascript:window.open('/PHPClass/Movic/Window.php?PCode=<?php echo $row['no']?>', 'HomePageMovicWindow3','width=607,height=451,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><?php echo $row['title']?></a>&nbsp;</td>
</tr>
<tr>
<td background='/img/line_24-2.gif' colspan=3 height=1 bgcolor='#FFFFFF'></td>
</tr>

<?php
		$i=$i+1;
} 

echo("</table>");

}else{
echo"<p align=center><b>자료없음</b></p>";
}

?>

<p align='center'>

<?php
if($rows){

$mlang_pagego="page=$page"; // 필드속성들 전달값

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
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>[$i]</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>[$i]</font>&nbsp;"); } 

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

<p align=center>
<?php echo $WebSoftCopyright?>
</p>
?>