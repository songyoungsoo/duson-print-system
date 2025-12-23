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
$ACate = $_GET['ACate'] ?? $_POST['ACate'] ?? '';
$ATreeNo = $_GET['ATreeNo'] ?? $_POST['ATreeNo'] ?? '';
$Cate = $_GET['Cate'] ?? $_POST['Cate'] ?? '';
$Ttable = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
$i = 0; // 루프 카운터 초기화

include"../title.php";
include"../../mlangprintauto/ConDb.php";
include"CateAdmin_title.php";

// ✅ GGTABLE 및 기타 변수 설정
$GGTABLE = 'mlangprintauto_transactioncate';
$View_TtableB = $Ttable ?? 'inserted';
$View_TtableC = $View_TtableB ?? 'inserted';
// ⚠️ $DF_Tatle_1/2/3 are set by CateAdmin_title.php based on $Ttable - DO NOT override here
$PageCode = 'CateList';	
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
    }
}

// 수정 버튼 함수 - JavaScript 코드 숨김 처리
function openModifyPopup(no, ttable, treeNo, bigNo, cate, aTreeNo, aCate, pageCode) {
    var url = './CateAdmin.php?mode=form&code=modify&no=' + no + '&Ttable=' + ttable;

    // TreeSelect 파라미터 설정
    if (treeNo) {
        url += '&TreeSelect=2';
    } else if (bigNo == "0") {
        // 상위 카테고리
    } else {
        url += '&TreeSelect=1';
    }

    // 추가 파라미터들
    if (cate) url += '&Cate=' + cate;
    if (aTreeNo) url += '&ATreeNo=' + aTreeNo;
    if (aCate) url += '&ACate=' + aCate;

    // 팝업 창 열기
    var windowName = 'WebOffice_' + pageCode + 'Modify';
    var windowFeatures = 'width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no';

    var popup = window.open(url, windowName, windowFeatures);
    popup.focus();
}

// 입력 버튼 함수들 - JavaScript 코드 숨김 처리
function openAddPopup(ttable, pageCode, cate) {
    var url = './CateAdmin.php?mode=form&Ttable=' + ttable;
    if (cate) url += '&Cate=' + cate;

    var windowName = 'WebOffice_' + pageCode + 'Form';
    var windowFeatures = 'width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no';

    var popup = window.open(url, windowName, windowFeatures);
    popup.focus();
}

function openTreeAddPopup(ttable, pageCode, treeSelect, cate) {
    var url = './CateAdmin.php?mode=form&Ttable=' + ttable + '&TreeSelect=' + treeSelect;
    if (cate) url += '&Cate=' + cate;

    var windowName = 'WebOffice_Tree' + pageCode + 'Form';
    var windowFeatures = 'width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no';

    var popup = window.open(url, windowName, windowFeatures);
    popup.focus();
}

function openSpecialAddPopup(ttable, pageCode, treeSelect, categoryType, cate) {
    var url = './CateAdmin.php?mode=form&Ttable=' + ttable + '&TreeSelect=' + treeSelect + '&category_type=' + categoryType;
    if (cate) url += '&Cate=' + cate;

    var windowName = 'WebOffice_' + ttable + '_' + categoryType + 'Form';
    var windowFeatures = 'width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no';

    var popup = window.open(url, windowName, windowFeatures);
    popup.focus();
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

<script src="../js/coolbar.js" type="text/javascript"></script>

</head>

<?php include"../../db.php";

if($ACate){  // $DF_Tatle_2 종이 규격 검색 
$Mlang_query="select * from $GGTABLE where Ttable='$View_TtableB' and BigNo='$ACate'";
}else if($ATreeNo){  // $DF_Tatle_3 종이 종류 검색
$Mlang_query="select * from $GGTABLE where Ttable='$View_TtableB' and TreeNo='$ATreeNo'";
}else{  // 일반모드 일때
$Mlang_query="select * from $GGTABLE where Ttable='$View_TtableB'";
}

$query= mysqli_query($db, $Mlang_query);
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 30;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 
?>



<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
(<b><?=$View_TtableC?></b>) CATEGORY LIST<BR>
* 상위 란 CATEGORY  의 최상 분야, 목록 을 말합니다.( 예; <?=$View_TtableC?> >> 수입명함 >> TITLE )
<?if($TreeSelect=="ok"){?><BR>
* 3단 이란 CATEGORY  의 최상 분야 선택시 TITLE 과 동시에 호출( 예; 전단지 일경우 종이종류을 말함 )
<?}?>
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
<option value='<?=$PHP_SELF?>?Ttable=<?=$Ttable?>'>→ 전체자료</option>
<?php $Cate_result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0'");
$Cate_rows=mysqli_num_rows($Cate_result);
if($Cate_rows){

while($Cate_row= mysqli_fetch_array($Cate_result)) 
{
?>

<option value='<?=$PHP_SELF?>?ACate=<?=$Cate_row['no']?>&Ttable=<?=$Ttable?>' <?if($ACate==$Cate_row['no']){echo("style='background-color:#429EB2; color:#FFFFFF;' selected");}?>><?=htmlspecialchars($Cate_row['title'])?>-(<?=$DF_Tatle_2?>)</option>

<?php $Sub_result= mysqli_query($db, "select * from $GGTABLE where TreeNo='{$Cate_row['no']}'");
if ($Sub_result) {
    $Sub_row= mysqli_fetch_array($Sub_result);
} else {
    $Sub_row = false;
}
?>

<?php if($Sub_row && isset($Sub_row['TreeNo']) && $Sub_row['TreeNo'] && isset($Cate_row['title'])){ ?>
<option value='<?=htmlspecialchars($PHP_SELF)?>?ATreeNo=<?=htmlspecialchars($Sub_row['TreeNo'])?>&Ttable=<?=htmlspecialchars($Ttable)?>' <?php if($ATreeNo && isset($Cate_row['no']) && $ATreeNo==$Cate_row['no']){echo("style='background-color:#429EB2; color:#FFFFFF;' selected");}?>><?=htmlspecialchars($Cate_row['title'])?>-(<?=htmlspecialchars($DF_Tatle_3)?>)</option>
<?php } ?>


<?php }
}else{}
?>
</SELECT>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
<?php include "CateList_Title.php"; ?>
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

<?php $result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
$rows=mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row['no']?></font></td>
<td>&nbsp;&nbsp;<font color=white>
<?php if($row['TreeNo']){
 $BigNo_result= mysqli_query($db, "select * from $GGTABLE where Ttable='$View_TtableB' and no='{$row['TreeNo']}'");
   }else{   
    $BigNo_result= mysqli_query($db, "select * from $GGTABLE where Ttable='$View_TtableB' and no='{$row['BigNo']}'");
    }
   $BigNo_row= mysqli_fetch_array($BigNo_result);
     if($BigNo_row){
            echo(htmlspecialchars($BigNo_row['title']));
         }
?></font>
<font color=#A2A2A2>
(<?php if($row['BigNo']=="0"){echo($DF_Tatle_1);}
if($row['TreeNo']){echo($DF_Tatle_3);}
if($row['BigNo']){echo($DF_Tatle_2);}
?>)
</font>&nbsp;&nbsp;
</td>
<td>&nbsp;&nbsp;<font color=white><?=htmlspecialchars($row['title'])?></font>&nbsp;&nbsp;</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&code=modify&no=<?=$row['no']?>&Ttable=<?=$Ttable?><?php if($row['TreeNo']){echo('&TreeSelect=2');}elseif($row['BigNo']!='0'){echo('&TreeSelect=1');}?><?php if($Cate){echo('&Cate='.$Cate);}?><?php if($ATreeNo){echo('&ATreeNo='.$ATreeNo);}?><?php if($ACate){echo('&ACate='.$ACate);}?>', 'WebOffice_Modify','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?=$row['no']?>');" value=' 삭제 '>
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