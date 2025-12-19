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

$M123="..";
include"../top.php"; 
?>

<script>
function PageMenuDel(no){
	var str;
	if (confirm("(주메뉴를 삭제하시면 PAGE 내용들이 정상적으로 호출되지 않습니다.)\n\n한번 삭제한 자료는 두번다시 복구 되지 않습니다.\n\n실행 하시겠습니까...............*^^*")) {
		str='page_menu_admin.php?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
</script>


<table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
<tr><td align=center>


<!------------------------------------------- 리스트 시작----------------------------------------->
<?php $table="$page_big_table";

$Mlang_query="select * from $table";


$query= mysqli_query($db, "$Mlang_query");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 15;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

$result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
$rows=mysqli_num_rows($result);
if($rows){

echo("
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#66CC99'>
<tr>
<td align=center width=25% height=30><font color=white>등록번호</font></td>
<td align=center width=50%><font color=white>메뉴 내용</font></td>
<td align=center width=25%><font color=white>관리기능</font></td>
</tr>	
");

$i=1+$offset;
while($row= mysqli_fetch_array($result)) 
{ 
?>

<?php echo("
<tr bgcolor='#FFFFFF'>
<td>$row[no]</td>
<td>$row[title]</td>
<td align=center>
<input type='button' onClick=\"javascript:window.open('./page_menu_admin.php?mode=modify&no=$row[no]', 'page_menu_admin','width=650,height=200,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');\" value='수정' style='width:50;'>
<input type='button' onClick=\"javascript:PageMenuDel('$row[no]');\" value='삭제'  style='width:50;'>	
</td>
</tr>
");		
		
		$i=$i+1;
} 

echo("</table>");

}else{
echo"<p align=center><BR><BR><BR><big>주메뉴</big> - <b>등록 자료없음</b></p>";
}

?>

<p align='center'>

<?php if($rows){

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


<?php include"../down.php"; ?>