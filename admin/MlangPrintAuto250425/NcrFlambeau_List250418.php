<?php
include "../../db.php";
$TIO_CODE = "NcrFlambeau";
$table = "MlangPrintAuto_{$TIO_CODE}";
$mode = $_GET['mode'] ?? $_POST['mode'] ?? null;
$search = $_GET['search'] ?? $_POST['search'] ?? null;
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$Cate            = $_GET['Cate']         ?? $_POST['Cate']         ?? '';
$title_search     = $_GET['title_search'] ?? $_POST['title_search'] ?? '';
$PageCode        = $_GET['PageCode']     ?? $_POST['PageCode']     ?? '';
$Ttable          = $_GET['Ttable']       ?? $_POST['Ttable']       ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
$no = isset($_GET['no']) ? (int)$_GET['no'] : (isset($_POST['no']) ? (int)$_POST['no'] : 0);

if ($mode === "delete") {
    $stmt = mysqli_prepare($db, "DELETE FROM $table WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "s", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($db);

    echo ("<script>
        alert('테이블명: $table - $no 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.close();
    </script>");
    exit;
}

$M123 = "..";
include "../top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";
?>

<head>
<script>
function clearField(field) {
    if (field.value === field.defaultValue) {
        field.value = "";
    }
}
function checkField(field) {
    if (!field.value) {
        field.value = field.defaultValue;
    }
}
function WomanMember_Admin_Del(no) {
    if (confirm(no + '번 자료을 삭제 처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
        const str = '<?php echo $_SERVER['PHP_SELF']; ?>?no=' + no + '&mode=delete';
        const popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
<tr>
<td align="left">
<?php include "ListSearchBox.php"; ?>
</td>

<?php
include "../../db.php";

if ($search == "yes") { //검색모드일때
    $stmt = mysqli_prepare($db, "SELECT * FROM $table WHERE style = ? AND TreeSelect = ? AND Section = ?");
    mysqli_stmt_bind_param($stmt, "sss", $RadOne, $myListTreeSelect, $myList);
} else { // 일반모드 일때
    $query = "SELECT * FROM $table";
    $stmt = mysqli_prepare($db, $query);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recordsu = mysqli_num_rows($result);
$total = $recordsu;

$listcut = 15;
if (!isset($offset)) $offset = 0;
?>

<td align="right">
    <input type="button" onclick="window.open('CateList.php?Ttable=<?php echo $TIO_CODE; ?>&TreeSelect=ok', '<?php echo $table; ?>_FormCate', 'width=600,height=650');" value=" 구분 관리 ">
    <input type="button" onclick="window.open('<?php echo $TIO_CODE; ?>_admin.php?mode=IncForm', '<?php echo $table; ?>_Form1', 'width=820,height=600');" value=" 가격/설명 관리 ">
    <input type="button" onclick="window.open('<?php echo $TIO_CODE; ?>_admin.php?mode=form&Ttable=<?php echo $TIO_CODE; ?>', '<?php echo $table; ?>_Form2', 'width=300,height=250');" value=" 신 자료 입력 ">
    <br><br>
    전체자료수 - <span style="color:blue;"><b><?php echo $total; ?></b></span> 개&nbsp;&nbsp;
</td>
</tr>
</table>

<!-- 리스트 시작 -->
<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
    <td align="center">등록번호</td>
    <td align="center">구분</td>
    <td align="center">규격</td>
    <td align="center">색상 및 재질</td>
    <td align="center">수량(옆)</td>
    <td align="center">가격</td>
    <td align="center">관리기능</td>
</tr>

<?php
$queryList = mysqli_query($db, "$query ORDER BY no DESC LIMIT $offset, $listcut");
$rows = mysqli_num_rows($queryList);

if ($rows) {
    while ($row = mysqli_fetch_assoc($queryList)) {
        echo "<tr bgcolor='#575757'>";
        echo "<td align='center'><font color='white'>{$row['no']}</font></td>";

        // 구분
        $res1 = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no = '{$row['style']}'");
        $style = mysqli_fetch_assoc($res1)['title'] ?? "";
        echo "<td align='center'><font color='white'>{$style}</font></td>";

        // 규격
        $res2 = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no = '{$row['Section']}'");
        $section = mysqli_fetch_assoc($res2)['title'] ?? "";
        echo "<td align='center'><font color='white'>{$section}</font></td>";

        // 색상 및 재질
        $res3 = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no = '{$row['TreeSelect']}'");
        $material = mysqli_fetch_assoc($res3)['title'] ?? "";
        echo "<td align='center'><font color='white'>{$material}</font></td>";

        // 수량 및 가격
        echo "<td align='center'><font color='white'>{$row['quantity']}({$row['quantityTwo']})</font></td>";
        echo "<td align='center'><font color='white'>" . number_format($row['money']) . "원</font></td>";

        // 수정/삭제 버튼
        echo "<td align='center'>
            <input type='button' onclick=\"window.open('{$TIO_CODE}_admin.php?mode=form&code=Modify&no={$row['no']}&Ttable={$TIO_CODE}', '{$table}_Form2Modify', 'width=300,height=250');\" value=' 수정 '>
            <input type='button' onclick=\"WomanMember_Admin_Del('{$row['no']}');\" value=' 삭제 '>
        </td></tr>";
    }
} else {
    $msg = ($search === "yes") ? "관련 검색 자료없음" : "등록 자료없음";
    echo "<tr><td colspan='10' align='center'><br><br>$msg</td></tr>";
}
?>
</table>

<p align="center">
<?php
if ($rows) {
    $mlang_pagego = ($search == "yes") 
        ? "search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList" 
        : "cate=$cate&title_search=$title_search";

    $pagecut = 7;
    $one_bbs = $listcut * $pagecut;
    $start_offset = intval($offset / $one_bbs) * $one_bbs;
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
    $start_page = intval($start_offset / $listcut) + 1;
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$_SERVER[PHP_SELF]?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;
        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$_SERVER[PHP_SELF]?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }
        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$_SERVER[PHP_SELF]?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }

    echo " 총목록갯수: $end_page 개";
}

mysqli_close($db);
?>
</p>

<?php include "../down.php"; ?>

