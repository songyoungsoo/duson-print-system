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

include"../title.php";
include"../../mlangprintauto/ConDb.php";
?>

<head>
<script>
self.moveTo(0,0)
self.resizeTo(availWidth=400,screen.availHeight)

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
		str='./SuCateAdmin.php?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

<script src="../js/coolbar.js" type="text/javascript"></script>

</head>

<?php include"../../db.php";

if($Cate){ // 검색
$Mlang_query="select * from $GGTABLESu where Ttable='$View_TtableB' and BigNo='$Cate'";
}else{ // 일반모드 일때
$Mlang_query="select * from $GGTABLESu where Ttable='$View_TtableB'";
}

$query= mysqli_query($db, "$Mlang_query");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 30;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 
?>


<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
(<b><?=$View_TtableC?></b>) 수량 LIST
</td>
</tr>
<tr>
<td>
   <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
     <tr>
	    <td align=left>
<script language="JavaScript">
function MM_jumpMenu(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>

<SELECT onChange="MM_jumpMenu('parent',this,0)">
<?php $Cate_result= mysqli_query($db, "select * from $GGTABLESu  where BigNo='0'");
$Cate_rows=mysqli_num_rows($Cate_result);
if($Cate_rows){

while($Cate_row= mysqli_fetch_array($Cate_result)) 
{
?>

<option value='<?=$PHP_SELF?>?Cate=<?=$Cate_row[no]?>&Ttable=<?=$Ttable?>' <?if($Cate=="$Cate_row[no]"){echo("style='background-color:#429EB2; color:#FFFFFF;' selected");}?>><?=$Cate_row[title]?></option>

<?php }
}else{}
?>
<option value='<?=$PHP_SELF?>?Ttable=<?=$Ttable?>'>→ 전체자료보기</option>
</SELECT>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
<input type='button' onClick="javascript:popup=window.open('./SuCateAdmin.php?mode=form&Ttable=<?=$Ttable?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수량 입력하기 '>
</td>
</tr>
</table>


<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록NO</td>
<td align=center>수량</td>
<td align=center>관리기능</td>
</tr>

<?php $result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
$rows=mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row[no]?></font></td>
<td>&nbsp;&nbsp;<font color=white><?=$row[title]?></font>&nbsp;&nbsp;</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./SuCateAdmin.php?mode=form&code=modify&no=<?=$row[no]?>&Ttable=<?=$Ttable?>', 'WebOffice_<?=$PageCode?>Modify','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?=$row[no]?>');" value=' 삭제 '>
</td>
</tr>

<?php 		$i=$i+1;
} 


}else{

if($search){
echo"<tr><td colspan=10><p align=center><BR><BR>관련 검색 자료없음</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
}

}

?>


</table>



<p align='center'>

<?php if($rows){

$mlang_pagego="table=$table&cate=$cate&$title_search=$title_search"; // 필드속성들 전달값

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

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->