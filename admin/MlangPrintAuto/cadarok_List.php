<?php
// PHP 7.4+ Updated - cadarok_List_updated.php (with pagination)
require_once "../../db.php";
$TIO_CODE = "cadarok";
$table = "MlangPrintAuto_{$TIO_CODE}";
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : (isset($_POST['offset']) ? (int)$_POST['offset'] : 0);
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';

$db = new mysqli($host, $user, $password, $dataname);
if ($db->connect_error) {
    die("DB 연결 오류: " . $db->connect_error);
}

if ($mode === "delete" && $no) {
    $stmt = $db->prepare("DELETE FROM {$table} WHERE no=?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();
    $db->close();
    echo "<script>
        alert('테이블명: {$table} - {$no} 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>";
    exit;
}

$M123 = "..";
include "$M123/top.php";
$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

$db = mysqli_connect($host, $user, $password, $dataname);
$Mlang_query = $search === "yes"
    ? "SELECT * FROM {$table} WHERE style='{$db->real_escape_string($RadOne)}' AND Section='{$db->real_escape_string($myListTreeSelect)}' AND TreeSelect='{$db->real_escape_string($myList)}'"
    : "SELECT * FROM {$table}";

$query = $db->query($Mlang_query);
$recordsu = $query ? $query->num_rows : 0;
$total = $recordsu;
$listcut = 15;
$pagecut = 7;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

?>

<table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
<tr>
<td align=left>
<?php include "ListSearchBox.php";?>
</td>
<td align="right">
    <input type="button" onClick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok','<?php echo  $table ?>_FormCate','width=600,height=650');" value="구분 관리">
    <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm','<?php echo  $table ?>_Form1','width=820,height=600');" value="가격/설명 관리">
    <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>','<?php echo  $table ?>_Form2','width=300,height=250');" value="신 자료 입력"><br><br>
    전체자료수-<font color="blue"><b><?php echo  $total ?></b></font>개
</td></tr>
</table>

<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
    <td align="center">등록번호</td>
    <td align="center">구분</td>
    <td align="center">규격</td>
    <td align="center">종이종류</td>
    <td align="center">수량</td>
    <td align="center">기타</td>
    <td align="center">관리기능</td>
</tr>

<?php
$result = $db->query("$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $style_title = $db->query("SELECT title FROM $GGTABLE WHERE no='{$row['style']}'")->fetch_assoc()['title'] ?? '';
        $section_title = $db->query("SELECT title FROM $GGTABLE WHERE no='{$row['Section']}'")->fetch_assoc()['title'] ?? '';
        $tree_title = $db->query("SELECT title FROM $GGTABLE WHERE no='{$row['TreeSelect']}'")->fetch_assoc()['title'] ?? '';
        $quantity_display = ($row['quantity'] === "9999") ? "기타" : $row['quantity'] . "부";
        $money_display = number_format((int)$row['money']) . "원";
        ?>
        <tr bgcolor="#575757">
            <td align="center"><font color="white"><?php echo  $row['no'] ?></font></td>
            <td align="center"><font color="white"><?php echo  $style_title ?></font></td>
            <td align="center"><font color="white"><?php echo  $section_title ?></font></td>
            <td align="center"><font color="white"><?php echo  $tree_title ?></font></td>
            <td align="center"><font color="white"><?php echo  $quantity_display ?></font></td>
            <td align="center"><font color="white"><?php echo  $money_display ?></font></td>
            <td align="center">
                <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250');" value="수정">
                <input type="button" onClick="if(confirm('삭제하시겠습니까?')){location.href='<?php echo  $PHP_SELF ?>?no=<?php echo  $row['no'] ?>&mode=delete';}" value="삭제">
            </td>
        </tr>
<?php    }
} else { 
    echo "<tr><td colspan='10' align='center'><br><br>등록 자료없음</td></tr>";
} ?>
</table>

<p align="center">
<?php
if ($recordsu > 0) {
    $mlang_pagego = ($search === "yes")
        ? "search=$search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList"
        : "";

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;
        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font-weight:bold; color:green;'>($i)</font>&nbsp;";
        }
        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }

    echo "&nbsp;총목록갯수: $end_page 개";
}
?>
</p>

<?php $db->close(); ?>
<?php include "../down.php"; ?>
