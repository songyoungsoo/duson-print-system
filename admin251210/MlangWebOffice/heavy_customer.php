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

$PageCode="heavy_customer";
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
	if (confirm(+no+'번 자료를 삭제 하시겠습니까..?\n\n채용직원정보의 모든 자료까지 동시에 다 같이 삭제가 됩니다.\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='./<?=$PageCode?>/admin.php?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

</head>

<?php 
include"../../db.php";
$table="MlangWebOffice_$PageCode";

if($TDsearchValue){ // 검색
$Mlang_query="select * from $table where $TDsearch like '%$TDsearchValue%'";
}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

$query= mysqli_query($db, "$Mlang_query");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 15;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 
?>


<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
<font color=red>*</font> 직원자료를 등록/수정 하시려면 <b><u>정보자세히보기</u></b>를 이용하세요
</td>
</tr>
<tr>
<td>
   <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?=$PHP_SELF?>'>
	    <td align=left>
		<b>검색 :&nbsp;</b>
		<select name='TDsearch'>
		<option value='bizname'>상호</option>
		<option value='ceoname'>대표</option>
		<option value='a_name'>영업담당</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' 검 색 '>
	    </td>
		</form>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
거래처 수-<font style='color:blue;'><b><?=$total?></b></font>&nbsp;업체
<input type='button' onClick="javascript:popup=window.open('./<?=$PageCode?>/admin.php?mode=form', 'WebOffice_<?=$PageCode?>Form','width=600,height=480,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 정보 입력하기 '>
</td>
</tr>
</table>


<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록NO</td>
<td align=center>상호</td>
<td align=center>대표(휴대폰)</td>
<td align=center>영업담당(휴대폰)</td>
<td align=center>관리기능</td>
</tr>

<?php 
$result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
$rows=mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row[no]?></font></td>
<td align=center><font color=white><?=$row[bizname]?></font></td>
<td align=center><font color=white><?=$row[ceoname]?></font></td>
<td align=center><font color=white>명</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./<?=$PageCode?>/admin.php?mode=view&no=<?=$row[no]?>', 'WebOffice_<?=$PageCode?>View','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='정보자세히보기'>
<input type='button' onClick="javascript:popup=window.open('./<?=$PageCode?>/admin.php?mode=form&code=modify&no=<?=$row[no]?>', 'WebOffice_<?=$PageCode?>Modify','width=600,height=480,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?=$row[no]?>');" value=' 삭제 '>
</td>
<tr>

<?php 
		$i=$i+1;
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

<?php 
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

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php 
include"../down.php";
?>