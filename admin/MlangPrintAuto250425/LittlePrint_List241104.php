<?php
include "../../db.php";
$TIO_CODE = "LittlePrint";
$table = "MlangPrintAuto_{$TIO_CODE}";

if (isset($_GET['mode']) && $_GET['mode'] == "delete") {
    $no = intval($_GET['no']);
    $result = mysqli_query($db, "DELETE FROM {$table} WHERE no='{$no}'");
    mysqli_close($db);

    echo ("<script language='javascript'>
    window.alert('테이블: {$table} - {$no} 번 데이터 삭제 완료');
    opener.parent.location.reload();
    window.self.close();
    </script>");
    exit;
} 

$M123 = "..";
include "$M123/top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "{$T_DirUrl}/ConDb.php";
?>

<head>
<script>
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

function WomanMember_Admin_Del(no) {
    if (confirm(no + '번 데이터를 삭제 처리 하시겠습니까?\n\n삭제된 데이터는 복구되지 않습니다.')) {
        str = '<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?no=' + no + '&mode=delete';
        popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' >
<tr>
<td align=left>
<?php include "ListSearchBox.php"; ?>
</td>

<?php
include "../../db.php";

$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

if ($search == "yes") { // 검색모드
    $RadOne = $_GET['RadOne'];
    $myListTreeSelect = $_GET['myListTreeSelect'];
    $myList = $_GET['myList'];
    $Mlang_query = "SELECT * FROM {$table} WHERE style='{$RadOne}' AND TreeSelect='{$myListTreeSelect}' AND Section='{$myList}'";
} else { // 일반모드
    $Mlang_query = "SELECT * FROM {$table}";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut = 15;  // 한 페이지에 보여줄 글의 수
?>

<td align=right>
<input type='button' onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>&TreeSelect=ok', '<?php echo  htmlspecialchars($table) ?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 카테고리 관리 '>
<input type='button' onClick="javascript:window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=IncForm', '<?php echo  htmlspecialchars($table) ?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' 신규/수정 등록 '>
<input type='button' onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 데이터 입력 '>
<BR><BR>
전체데이터 - <font style='color:blue;'><b><?php echo  htmlspecialchars($total) ?></b></font> 건
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작 ----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>스타일</td>
<td align=center>종이재질</td>
<td align=center>종이규격</td>
<td align=center>포맷</td>
<td align=center>수량</td>
<td align=center>금액</td>
<td align=center>편집비용</td>
<td align=center>관리</td>
</tr>

<?php
$result = mysqli_query($db, "{$Mlang_query} ORDER BY no DESC LIMIT {$offset}, {$listcut}");
$rows = mysqli_num_rows($result);
if ($rows) {
    while ($row = mysqli_fetch_array($result)) {
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo  htmlspecialchars($row['no']) ?></font></td>
<td align=center><font color=white>
<?php 
$result_FGTwo = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE no='{$row['style']}'");
$row_FGTwo = mysqli_fetch_array($result_FGTwo);
if ($row_FGTwo) { echo htmlspecialchars($row_FGTwo['title']); }
?>
</font></td>
<td align=center><font color=white>
<?php 
$result_FGFree = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE no='{$row['TreeSelect']}'");
$row_FGFree = mysqli_fetch_array($result_FGFree);
if ($row_FGFree) { echo htmlspecialchars($row_FGFree['title']); }
?>
</font></td> 
<td align=center><font color=white>
<?php 
$result_FGOne = mysqli_query($db, "SELECT * FROM {$GGTABLE} WHERE no='{$row['Section']}'");
$row_FGOne = mysqli_fetch_array($result_FGOne);
if ($row_FGOne) { echo htmlspecialchars($row_FGOne['title']); }
?>
</font></td> 
<td align=center><font color=white>
<?php if ($row['POtype'] == "1") { echo "특별"; } ?>
<?php if ($row['POtype'] == "2") { echo "일반"; } ?>
</font></td>
<td align=center><font color=white><?php echo  htmlspecialchars($row['quantity']) ?></font></td>
<td align=center><font color=white>
<?php $sum = number_format($row['money']); echo "{$sum}원"; ?>
</font></td>
<td align=center><font color=white>
<?php $sumr = number_format($row['DesignMoney']); echo "{$sumr}원"; ?>
</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($TIO_CODE) ?>_admin.php?mode=form&code=Modify&no=<?php echo  htmlspecialchars($row['no']) ?>&Ttable=<?php echo  htmlspecialchars($TIO_CODE) ?>', '<?php echo  htmlspecialchars($table) ?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo  htmlspecialchars($row['no']) ?>');" value=' 삭제 '>
</td>
</tr>

<?php
    }
} else {
    if ($search) {
        echo "<tr><td colspan=10><p align=center><BR><BR>검색된 데이터가 없습니다.</p></td></tr>";
    } else {
        echo "<tr><td colspan=10><p align=center><BR><BR>데이터가 없습니다.</p></td></tr>";
    }
}
?>

</table>

<p align='center'>
<?php
if ($rows) {
    if ($search == "yes") { 
        $mlang_pagego = "search={$search}&cate={$cate}&title_search={$title_search}&RadOne={$RadOne}&myListTreeSelect={$myListTreeSelect}&myList={$myList}";
    } else {
        $mlang_pagego = "cate={$cate}&title_search={$title_search}"; // 일반 모드
    }

    $pagecut = 7;  // 한 번에 보여줄 페이지 수
    $one_bbs = $listcut * $pagecut;  // 한 번에 보여줄 글 수
    $start_offset = intval($offset / $one_bbs) * $one_bbs;  // 현재 페이지의 시작 오프셋
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 마지막 페이지의 오프셋
    $start_page = intval($start_offset / $listcut) + 1; // 현재 페이지의 시작 번호
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); 

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='".htmlspecialchars($_SERVER['PHP_SELF'])."?offset={$apoffset}&{$mlang_pagego}'>...[처음]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;

        if ($offset != $newoffset) {
            echo "&nbsp;<a href='".htmlspecialchars($_SERVER['PHP_SELF'])."?offset={$newoffset}&{$mlang_pagego}'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }

        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='".htmlspecialchars($_SERVER['PHP_SELF'])."?offset={$nextoffset}&{$mlang_pagego}'>[다음]...</a>";
    }
    echo "전체 페이지: {$end_page}";
}

mysqli_close($db);
?>
</p>

<?php
include "../down.php";
?>
