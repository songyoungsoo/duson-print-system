<?php
include "../../db.php";
$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    die("DB 연결 실패: " . mysqli_connect_error());
}
// 변수 초기화 (방지용)
$cate       = $_GET['cate'] ?? $_POST['cate'] ?? '';
$title_search = $_GET['title_search'] ?? $_POST['title_search'] ?? '';
$PHP_SELF   = $_SERVER['PHP_SELF'];
$TIO_CODE="inserted";
$table="MlangPrintAuto_{$TIO_CODE}";
$mode       = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no         = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$search     = $_GET['search'] ?? $_POST['search'] ?? '';
$RadOne     = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myList     = $_GET['myList'] ?? $_POST['myList'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$offset     = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$listcut = 20; // 기본값 지정

if($mode=="delete"){

$result = mysqli_query("DELETE FROM $table WHERE no='$no'");
mysqli_close();

echo ("<script language=javascript>
window.alert('테이블명: $table - $no 번 자료 삭제 완료');
opener.parent.location.reload();
window.// TODO: replace with window.close()
window.close();
</script>
");
exit;

} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$M123="..";
include"../top.php"; 

$T_DirUrl="../../MlangPrintAuto";
include"$T_DirUrl/ConDb.php";
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
		str='<?php echo $PHP_SELF?>?no='+no+'&mode=delete';
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
<?php include "ListSearchBox.php";?>
</td>

<?php
include "../../db.php";

if($search=="yes"){ //검색모드일때
 $Mlang_query="select * from $table where style='$RadOne' and TreeSelect='$myListTreeSelect' and Section='$myList'";
}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}
$db = mysqli_connect($host, $user, $password, $dataname);
$query = "SELECT * FROM $table ORDER BY no DESC LIMIT $offset, $listcut";
$result = mysqli_query($db, $query);
if (!$result) {
    die("쿼리 실패: " . mysqli_error($db));
}
$rows = mysqli_num_rows($result);

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$total = mysqli_num_rows($query);

$listcut= 15;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 
?>


<td align=right>
<input type='button' onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo $TIO_CODE?>&TreeSelect=ok', '<?php echo $table?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 구분 관리 '>
<input type='button' onClick="javascript:window.open('<?php echo $TIO_CODE?>_admin.php?mode=IncForm', '<?php echo $table?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' 가격/설명 관리 '>
<input type='button' onClick="javascript:popup=window.open('<?php echo $TIO_CODE?>_admin.php?mode=form&Ttable=<?php echo $TIO_CODE?>', '<?php echo $table?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 신 자료 입력 '>
<BR><BR>
전체자료수-<font style='color:blue;'><b><?php echo $total?></b></font>&nbsp;개&nbsp;&nbsp;
</td>
</tr>
</table>
<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록번호</td>
<td align=center>인쇄색상</td>
<td align=center>종이종류</td>
<td align=center>종이규격</td>
<td align=center>인쇄면</td>
<td align=center>수량(옆)</td>
<td align=center>가격</td>
<td align=center>디자인비</td>
<td align=center>관리기능</td>
</tr>

<?php
$result = mysqli_query($db, $Mlang_query . " order by NO desc limit $offset, $listcut");
$rows = mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo $row['no']?></font></td>
<td align=center><font color=white>
<? 
$result_FGTwo=mysqli_query($db, "select * from $GGTABLE where no='$row[style]'");
$row_FGTwo= mysqli_fetch_array($result_FGTwo);
if($row_FGTwo){ echo("$row_FGTwo[title]"); }
?>
</font></td>
<td align=center><font color=white>
<? 
$result_FGFree=mysqli_query($db, "select * from $GGTABLE where no='$row[TreeSelect]'");
$row_FGFree= mysqli_fetch_array($result_FGFree);
if($row_FGFree){ echo("$row_FGFree[title]"); }
?>
</font></td> 
<td align=center><font color=white>
<? 
$result_FGOne=mysqli_query($db, "select * from $GGTABLE where no='$row[Section]'");
$row_FGOne= mysqli_fetch_array($result_FGOne);
if($row_FGOne){ echo("$row_FGOne[title]"); }
?>
</font></td> 
<td align=center><font color=white>
<?php if ($row['POtype']=="1"){echo("단면");}?>
<?php if ($row['POtype']=="2"){echo("양면");}?>
</font></td>
<td align=center><font color=white><?php echo $row['quantity']?>연(<?php echo $row['quantityTwo']?>장)</font></td>
<td align=center><font color=white>
<?$sum = "$row[money]"; $sum = number_format($sum);  echo("$sum"); $sum = str_replace(",","",$sum); ?>원
</font></td>
<td align=center><font color=white>
<?$sumr = "$row[DesignMoney]"; $sumr = number_format($sumr);  echo("$sumr"); $sumr = str_replace(",","",$sumr); ?>원
</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo $TIO_CODE?>_admin.php?mode=form&code=Modify&no=<?php echo $row['no']?>&Ttable=<?php echo $TIO_CODE?>', '<?php echo $table?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo $row['no']?>');" value=' 삭제 '>
</td>
<tr>

<?php
$i=0;
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

if($search=="yes"){ $mlang_pagego="search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList";
}else{
  $mlang_pagego="cate=$cate&title_search=$title_search"; // 필드속성들 전달값
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

<?php
include"../down.php";
?>