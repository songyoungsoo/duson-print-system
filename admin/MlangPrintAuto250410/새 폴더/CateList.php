<?php
include "../title.php";
include "../../MlangPrintAuto/ConDb.php";
include "CateAdmin_title.php";
include "../../db.php"; // mysqli 객체 $mysqli 사용
// $mysqli = mysqli_connect($host, $user, $password, $dataname);
// $db = mysqli_connect($host, $user, $password, $dataname);

// 변수 초기화 (방지용)
$ACate = $_GET['ACate'] ?? null;
$ATreeNo = $_GET['ATreeNo'] ?? null;
$Ttable = $_GET['Ttable'] ?? null;
$offset = $_GET['offset'] ?? 0;
$Cate = $_GET['Cate'] ?? null;
$search = $_GET['search'] ?? null;
$TreeSelect = $_GET['TreeSelect'] ?? null;

// 예시로 설정된 값들 (정확한 값은 기존 코드에 맞게 조정 필요)
$View_TtableB = $Ttable;
$View_TtableC = $Ttable; // 이건 실제 테이블 한글명이라면 따로 정의 필요
$PageCode = "Category";
// $DF_Tatle_1 = $_POST['DF_Tatle_1'] ?? null;
// $DF_Tatle_2 = $_POST['DF_Tatle_2'] ?? null;
// $DF_Tatle_3 = $_POST['DF_Tatle_3'] ?? null;
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';

?>

<head>
<script>
self.moveTo(0,0);
self.resizeTo(650,screen.availHeight);

function clearField(field) {
	if (field.value == field.defaultValue) {
		field.value = "";
	}
}
function checkField(field) {
	if (!field.value) {
		field.value = field.defaultValue;
	}
}

function WebOffice_customer_Del(no){
	if (confirm(no + '번 자료를 삭제 하시겠습니까..?\n\n최상위 일경우 하위항목까지 삭제가 됩니다.\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		let str='./CateAdmin.php?no='+no+'&mode=delete';
        let popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.location.href=str;
        popup.focus();
	}
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<?php
// 조건별 쿼리 설정
$mysqli = mysqli_connect($host, $user, $password, $dataname);
if ($ACate) {  // $DF_Tatle_2 종이 규격 검색 
    $stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND BigNo = ?");
    $stmt->bind_param("si", $View_TtableB, $ACate);
} elseif ($ATreeNo) { // $DF_Tatle_3 종이 종류 검색
    $stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND TreeNo = ?");
    $stmt->bind_param("si", $View_TtableB, $ATreeNo);
} else {  // 일반모드 일때
    $stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ?");
    $stmt->bind_param("s", $View_TtableB);
}
$stmt->execute();
$query = $stmt->get_result();
$recordsu = $query->num_rows;
$total = $recordsu;

$listcut = 30;
if (!$offset) $offset = 0;
?>

<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>
(<b><?php echo $View_TtableC?></b>) CATEGORY LIST<BR>
* 상위 란 CATEGORY  의 최상 분야, 목록 을 말합니다.( 예; <?php echo $View_TtableC?> >> 수입명함 >> TITLE )
<?php if($TreeSelect=="ok"){?><BR>
* 3단 이란 CATEGORY  의 최상 분야 선택시 TITLE 과 동시에 호출( 예; 전단지 일경우 종이종류을 말함 )
<?php } ?>
</td>
</tr>
<tr>
<td>
   <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
     <tr>
	    <td align=left>
<script>
function MM_jumpMenu(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>

<SELECT onChange="MM_jumpMenu('parent',this,0)">
<option value='<?php echo $_SERVER['PHP_SELF']?>?Ttable=<?php echo $Ttable?>'>→ 전체자료</option>
<?php
$stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND BigNo = 0");
$stmt->bind_param("s", $Ttable);
$stmt->execute();
$Cate_result = $stmt->get_result();

while($Cate_row = $Cate_result->fetch_assoc()) {
?>
<option value='<?php echo $_SERVER['PHP_SELF']?>?ACate=<?php echo $Cate_row['no']?>&Ttable=<?php echo $Ttable?>' <?php if($ACate==$Cate_row['no']){echo("style='background-color:#429EB2; color:#FFFFFF;' selected");}?>><?php echo $Cate_row['title']?>-(<?php echo $DF_Tatle_2?>)</option>
<?php
    $stmt_sub = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE TreeNo = ?");
    $stmt_sub->bind_param("i", $Cate_row['no']);
    $stmt_sub->execute();
    $Sub_result = $stmt_sub->get_result();
    if($Sub_row = $Sub_result->fetch_assoc()) {
?>
<option value='<?php echo $_SERVER['PHP_SELF']?>?ATreeNo=<?php echo $Sub_row['TreeNo']?>&Ttable=<?php echo $Ttable?>' <?php if($ATreeNo==$Cate_row['no']){echo("style='background-color:#429EB2; color:#FFFFFF;' selected");}?>><?php echo $Cate_row['title']?>-(<?php echo $DF_Tatle_3?>)</option>
<?php } } ?>
</SELECT>
	 </tr>
	</table>
</td>
<td align=right valign=bottom>
<?php include "CateList_Title.php" ?>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록NO</td>
<td align=center>상위CATEGORY(번호)</td>
<td align=center>TITLE</td>
<td align=center>관리기능</td>
</tr>

<?php
$stmt = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? ORDER BY NO DESC LIMIT ?, ?");
$stmt->bind_param("sii", $View_TtableB, $offset, $listcut);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;

if($rows){
while($row = $result->fetch_assoc()) {
?>
<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo $row['no']?></font></td>
<td>&nbsp;&nbsp;<font color=white>
<?php
    if($row['TreeNo']){
        $stmt_big = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND no = ?");
        $stmt_big->bind_param("si", $View_TtableB, $row['TreeNo']);
    } else {
        $stmt_big = $mysqli->prepare("SELECT * FROM $GGTABLE WHERE Ttable = ? AND no = ?");
        $stmt_big->bind_param("si", $View_TtableB, $row['BigNo']);
    }
    $stmt_big->execute();
    $BigNo_result = $stmt_big->get_result();
    if($BigNo_row = $BigNo_result->fetch_assoc()) {
        echo($BigNo_row['title']);
    }
?>
</font>
<font color=#A2A2A2>(<?php
if($row['BigNo']=="0"){echo($DF_Tatle_1);}
if($row['TreeNo']){echo($DF_Tatle_3);}
if($row['BigNo']){echo($DF_Tatle_2);}
?>)</font>&nbsp;&nbsp;
</td>
<td>&nbsp;&nbsp;<font color=white><?php echo $row['title']?></font>&nbsp;&nbsp;</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&code=modify&no=<?php echo $row['no']?>&Ttable=<?php echo $Ttable?><?php if($row['TreeNo']){?>&TreeSelect=2<?php } else if($row['BigNo']=="0"){} else {?>&TreeSelect=1<?php } ?><?php if($Cate){echo("&Cate=$Cate");}?><?php if($ATreeNo){echo("&ATreeNo=$ATreeNo");}?><?php if($ACate){echo("&ACate=$ACate");}?>', 'WebOffice_<?php echo $PageCode?>Modify','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WebOffice_customer_Del('<?php echo $row['no']?>');" value=' 삭제 '>
</td>
</tr>
<?php 
		$i=$i+1;

} 
} else {
    if($search){
        echo"<tr><td colspan=10><p align=center><BR><BR>관련 검색 자료없음</p></td></tr>";
    } else {
        echo"<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
    }
}
?>
</table>

<p align='center'>
<?php
if($rows){
$mlang_pagego="ACate=$ACate&Ttable=$Ttable&ATreeNo=$ATreeNo";
$pagecut = 7;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

if($start_offset != 0){
    $apoffset = $start_offset - $one_bbs;
    echo "<a href='" . $_SERVER['PHP_SELF'] . "?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
}

for($i = $start_page; $i < $start_page + $pagecut; $i++){
    $newoffset = ($i - 1) * $listcut;
    if($offset != $newoffset){
        echo "&nbsp;<a href='" . $_SERVER['PHP_SELF'] . "?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
    } else {
        echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;");
    }
    if($i == $end_page) break;
}

if($start_offset != $end_offset){
    $nextoffset = $start_offset + $one_bbs;
    echo "&nbsp;<a href='" . $_SERVER['PHP_SELF'] . "?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
}

echo "총목록갯수: $end_page 개";
}
$mysqli->close();
?>
</p>
