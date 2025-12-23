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

// ✅ 추가 변수 초기화 (PHP 7.4 호환)
$cate = $_GET['cate'] ?? $_POST['cate'] ?? '';
$title_search = $_GET['title_search'] ?? $_POST['title_search'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
$i = 0; // 루프 카운터 초기화

// ✅ db.php는 top.php에서 include되므로 제거
// include"../../db.php";
$TIO_CODE="sticker";
$table="mlangprintauto_sticker"; // ✅ 소문자 테이블명

if($mode=="delete"){

$result = mysqli_query($db, "DELETE FROM $table WHERE no='$no'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

mysqli_close($db);

echo ("<script language=javascript>
window.alert('테이블명: $table - $no 번 자료 삭제 완료');
opener.parent.location.reload();
window.self.close();
</script>
");
exit;

} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$M123="..";
include"../top.php"; 

$T_DirUrl=".";
include"$T_DirUrl/condb.php";

// ✅ GGTABLE 변수 올바르게 설정
$GGTABLE = "mlangprintauto_transactioncate";
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
function WomanMember_Admin_Del(no){
	if (confirm(+no+'번 자료을 삭제 처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='<?=$PHP_SELF?>?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php
// ✅ 검색 박스 include with error handling
if(file_exists("listsearchbox.php")) {
    include"listsearchbox.php";
} else {
    echo "<p style='color: red;'>검색 박스 파일이 없습니다: listsearchbox.php</p>";
}
?>
</td>

<?php
// ✅ db.php는 이미 top.php에서 include되었으므로 제거
// include"../../db.php";

if($search=="yes"){ //검색모드일때
 $where_parts = [];
 if($RadOne && $RadOne != '#') {
     $where_parts[] = "style='$RadOne'";
 }
 if($myList && $myList != '#') {
     $where_parts[] = "Section='$myList'";
 }
 $Mlang_query = "select * from $table" . (count($where_parts) > 0 ? " where " . implode(' and ', $where_parts) : "");
}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

$query= mysqli_query($db, $Mlang_query);
if (!$query) {
    die("쿼리 실패: " . mysqli_error($db) . "<br>쿼리: " . $Mlang_query);
}

$recordsu= mysqli_num_rows($query);
$total = $recordsu; // affected_rows 대신 num_rows 사용

$listcut= 15;  //한 페이지당 보여줄 목록 게시물수.
if(!$offset) $offset=0; 
?>


<td align=right>
<input type='button' onClick="javascript:popup=window.open('catelist.php?Ttable=<?=$TIO_CODE?>&TreeSelect=ok', '<?=$table?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 구분 관리 '>
<input type='button' onClick="javascript:window.open('<?=$TIO_CODE?>_admin.php?mode=IncForm', '<?=$table?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' 가격/설명 관리 '>
<input type='button' onClick="javascript:popup=window.open('<?=$TIO_CODE?>_admin.php?mode=form&Ttable=<?=$TIO_CODE?>', '<?=$table?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 신 자료 입력 '>
<BR><BR>
전체자료수-<font style='color:blue;'><b><?=$total?></b></font>&nbsp;개&nbsp;&nbsp;
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록번호</td>
<td align=center>스티카종류</td>
<td align=center>규격</td>
<td align=center>수량</td>
<td align=center>가격</td>
<td align=center>디자인비</td>
<td align=center>관리기능</td>
</tr>

<?php $result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$rows=mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row['no']?></font></td>
<td align=center><font color=white>
<?php 
$result_FGTwo=mysqli_query($db, "select * from $GGTABLE where Ttable='sticker' AND no='{$row['style']}'");
$row_FGTwo= mysqli_fetch_array($result_FGTwo);
if($row_FGTwo){ echo(htmlspecialchars($row_FGTwo['title'])); }
?>
</font></td>
<td align=center><font color=white>
<?php 
$result_FGOne=mysqli_query($db, "select * from $GGTABLE where Ttable='sticker' AND no='{$row['Section']}'");
$row_FGOne= mysqli_fetch_array($result_FGOne);
if($row_FGOne){ echo(htmlspecialchars($row_FGOne['title'])); }
?>
</font></td> 
<td align=center><font color=white><?=$row['quantity']?>매</font></td>
<td align=center><font color=white>
<?php $sum = (float)$row['money']; $sum = number_format($sum);  echo($sum); ?>원
</font></td>
<td align=center><font color=white>
<?php $sumr = (float)$row['DesignMoney']; $sumr = number_format($sumr);  echo($sumr); ?>원
</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?=$TIO_CODE?>_admin.php?mode=form&code=Modify&no=<?=$row['no']?>&Ttable=<?=$TIO_CODE?>', '<?=$table?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WomanMember_Admin_Del('<?=$row['no']?>');" value=' 삭제 '>
</td>
<tr>

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

if($search=="yes"){
    $mlang_pagego="search=$search&RadOne=" . urlencode($RadOne) . "&myList=" . urlencode($myList);
}else{
  $mlang_pagego=""; // 일반 모드일 때는 파라미터 없음
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

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php include"../down.php";
?>