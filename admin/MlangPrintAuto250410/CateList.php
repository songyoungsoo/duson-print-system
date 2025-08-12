<?php
$Ttable     = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
include "../title.php";
include "../../MlangPrintAuto/ConDb.php";
	
$mode       = $_GET['mode'] ?? $_POST['mode'] ?? '';
$code       = $_GET['code'] ?? $_POST['code'] ?? '';
$no         = $_GET['no'] ?? $_POST['no'] ?? 0;
$offset     = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$TreeSelect = $_GET['TreeSelect'] ?? $_POST['TreeSelect'] ?? '';
$ACate      = $_GET['ACate'] ?? $_POST['ACate'] ?? '';
$ATreeNo    = $_GET['ATreeNo'] ?? $_POST['ATreeNo'] ?? '';
$search     = $_GET['search'] ?? $_POST['search'] ?? '';
$PHP_SELF   = $_SERVER['PHP_SELF'];
include "CateAdmin_title.php";

$DF_Tatle_2 = $DF_Tatle_2 ?? 'Default Title 2';
$DF_Tatle_3 = $DF_Tatle_3 ?? 'Default Title 3';
echo "<pre>";
echo "Ttable = " . $Ttable . "\\n";
echo "ToTitle = " . $ToTitle . "\\n";
echo "ListXTtable = " . $ListXTtable . "\\n";
echo "View_TtableC = " . $View_TtableC . "\\n";
echo "</pre>";
?>

<head>
<script>
self.moveTo(0,0)
self.resizeTo(availWidth=650,screen.availHeight)

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
		str='./CateAdmin.php?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

<script src="../js/coolbar.js" type="text/javascript"></script>

</head>

<?php
include "../../db.php";

if($ACate){  // $DF_Tatle_2 종이 규격 검색 
$Mlang_query="select * from $GGTABLE where Ttable='$View_TtableB' and BigNo='$ACate'";
}else if($ATreeNo){  // $DF_Tatle_3 종이 종류 검색
$Mlang_query="select * from $GGTABLE where Ttable='$View_TtableB' and TreeNo='$ATreeNo'";
}else{  // 일반모드 일때
$Mlang_query="select * from $GGTABLE where Ttable='$View_TtableB'";
}

$query= mysqli_query($db, "$Mlang_query");
$recordsu= mysqli_num_rows($query);
$total = $recordsu;

$listcut= 30;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 
?>



<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
(<b>
<?php
// View_TtableC가 이미 있으면 그대로 사용, 없으면 DB에서 가져오기
if (empty($View_TtableC)) {
    $query = "SELECT title FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' LIMIT 1";
    $result = mysqli_query($db, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $View_TtableC = $row['title'];
    }
}
echo $View_TtableC ?: 'Default Category';
?>
</b>) CATEGORY LIST<BR>
* 상위 란 CATEGORY  의 최상 분야, 목록 을 말합니다.( 예; <?php echo $View_TtableC ?: 'Default Category'?> >> 수입명함 >> TITLE )
<?php if ($TreeSelect=="ok"){?><BR>
* 3단 이란 CATEGORY  의 최상 분야 선택시 TITLE 과 동시에 호출( 예; 전단지 일경우 종이종류을 말함 )
<?php } ?>
</td>
</tr>
<tr>
<td>
   <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
     <tr>
	    <td align=left>
<script type="text/javascript">
function MM_jumpMenu(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>

<SELECT onChange="MM_jumpMenu('parent',this,0)">
<option value='<?php echo $PHP_SELF?>?Ttable=<?php echo $Ttable?>'>→ 전체자료</option>
<?php
$Cate_result= mysqli_query($db,"select * from $GGTABLE where Ttable='$Ttable' and BigNo='0'");
$Cate_rows=mysqli_num_rows($Cate_result);
if($Cate_rows){

while($Cate_row= mysqli_fetch_array($Cate_result)) 
{
?>

<option value='<?php echo $PHP_SELF?>?ACate=<?php echo $Cate_row['no']?>&Ttable=<?php echo $Ttable?>' <?php if ($ACate == $Cate_row['no']){echo("style='background-color:#429EB2; color:#FFFFFF;' selected");}?>><?php echo $Cate_row['title']?>-(<?php echo $DF_Tatle_2 ?? 'Default Title 2'?>)</option>

<?php
$Sub_result= mysqli_query($db,"select * from $GGTABLE where TreeNo='{$Cate_row['no']}'");
$Sub_row= mysqli_fetch_array($Sub_result);
?>

<?php if ($Sub_row['TreeNo']){?>
<option value='<?php echo $PHP_SELF?>?ATreeNo=<?php echo $Sub_row['TreeNo']?>&Ttable=<?php echo $Ttable?>' <?php if ($ATreeNo=="{$Cate_row['no']}"){echo("style='background-color:#429EB2; color:#FFFFFF;' selected");}?>><?php echo $Cate_row['title']?>-(<?php echo $DF_Tatle_3 ?? 'Default Title 3'?>)</option>
<?php } ?>


<?php
}
}else{}
?>
</SELECT>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
<?include "CateList_Title.php"?>
</td>
</tr>
</table>


<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록NO</td>
<td align=center>상위CATEGORY(번호)</td>
<td align=center>TITLE</td>
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
<td align=center><font color=white><?php echo $row[no]?></font></td>
<td>&nbsp;&nbsp;<font color=white>
<?php
if($row['TreeNo']){
 $BigNo_result= mysqli_query($db, "select * from $GGTABLE where Ttable='$View_TtableB' and no='{$row['TreeNo']}'");
   }else{   
    $BigNo_result= mysqli_query($db, "select * from $GGTABLE where Ttable='$View_TtableB' and no='{$row['BigNo']}'");
    }
   $BigNo_row= mysqli_fetch_array($BigNo_result);
     if($BigNo_row){
            echo("{$BigNo_row['title']}");
         }
?></font>
<font color=#A2A2A2>
(<?php
if($row['BigNo']=="0"){echo("$DF_Tatle_1");}
if($row['TreeNo']){echo("$DF_Tatle_3");}
if($row['BigNo']){echo("$DF_Tatle_2");}
?>)
</font>&nbsp;&nbsp;
</td>
<td>&nbsp;&nbsp;<font color=white><?php echo $row['title']?></font>&nbsp;&nbsp;</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&code=modify&no=<?php echo $row['no']?>&Ttable=<?php echo $Ttable?><?php if ($row['TreeNo']){?>&TreeSelect=2<?}else if($row['BigNo']=="0"){}else{?>&TreeSelect=1<?php } ?><?php if ($Cate){echo("&Cate=$Cate");}?><?php if ($ATreeNo){echo("&ATreeNo=$ATreeNo");}?><?php if ($ACate){echo("&ACate=$ACate");}?>', 'WebOffice_<?php echo $PageCode?>Modify','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?php echo $row['no']?>');" value=' 삭제 '>
</td>
</tr>

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

$mlang_pagego="ACate=$ACate&Ttable=$Ttable&ATreeNo=$ATreeNo"; // 필드속성들 전달값

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