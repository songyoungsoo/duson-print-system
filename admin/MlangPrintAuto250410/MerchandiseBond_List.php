<?php
include "../../db.php";
$TIO_CODE = "MerchandiseBond";
$table = "MlangPrintAuto_" . $TIO_CODE;
$no = isset($_GET['no']) ? intval($_GET['no']) : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM $table WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo ("<script>
        alert('테이블명: $table - $no 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
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
function WomanMember_Admin_Del(no) {
    if (confirm(no + '번 자료를 삭제 처리 하시겠습니까?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요!')) {
        let str = '<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>?no=' + no + '&mode=delete';
        let popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50");
        popup.document.location.href = str;
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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$RadOne = isset($_GET['RadOne']) ? $_GET['RadOne'] : '';
$myList = isset($_GET['myList']) ? $_GET['myList'] : '';

if ($search == "yes") { // 검색 모드
    $stmt = $db->prepare("SELECT * FROM $table WHERE style = ? AND Section = ?");
    $stmt->bind_param("ss", $RadOne, $myList);
} else { // 일반 모드
    $stmt = $db->prepare("SELECT * FROM $table");
}

$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
$listcut = 15;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$stmt->close();
?>

<td align="right">
    <input type="button" onClick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate', 'width=600,height=650');" value="구분 관리">
    <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1', 'width=820,height=600');" value="가격/설명 관리">
    <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2', 'width=300,height=250');" value="신 자료 입력">
    <br><br>
    전체자료수-<font style="color:blue;"><b><?php echo  $recordsu ?></b></font>&nbsp;개&nbsp;&nbsp;
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
    <td align="center">등록번호</td>
    <td align="center">종류</td>
    <td align="center">수량</td>
    <td align="center">인쇄면</td>
    <td align="center">후가공</td>
    <td align="center">가격</td>
    <td align="center">디자인비</td>
    <td align="center">관리기능</td>
</tr>

<?php
$Mlang_query = ($search == "yes") ? "SELECT * FROM $table WHERE style = ? AND Section = ? ORDER BY no DESC LIMIT ?, ?" : "SELECT * FROM $table ORDER BY no DESC LIMIT ?, ?";
$stmt = $db->prepare($Mlang_query);

if ($search == "yes") {
    $stmt->bind_param("ssii", $RadOne, $myList, $offset, $listcut);
} else {
    $stmt->bind_param("ii", $offset, $listcut);
}

$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;

if ($rows) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr bgcolor="#575757">
            <td align="center"><font color="white"><?php echo  htmlspecialchars($row['no']) ?></font></td>
            <td align="center"><font color="white">
                <?php
                $stmt_style = $db->prepare("SELECT title FROM $GGTABLE WHERE no = ?");
                $stmt_style->bind_param("s", $row['style']);
                $stmt_style->execute();
                $result_style = $stmt_style->get_result();
                if ($row_style = $result_style->fetch_assoc()) {
                    echo htmlspecialchars($row_style['title']);
                }
                $stmt_style->close();
                ?>
            </font></td>
            <td align="center"><font color="white"><?php echo  htmlspecialchars($row['quantity']) ?></font></td>
            <td align="center"><font color="white"><?php echo  $row['POtype'] == "1" ? "단면" : "양면" ?></font></td>
            <td align="center"><font color="white">
                <?php
                $stmt_section = $db->prepare("SELECT title FROM $GGTABLE WHERE no = ?");
                $stmt_section->bind_param("s", $row['Section']);
                $stmt_section->execute();
                $result_section = $stmt_section->get_result();
                if ($row_section = $result_section->fetch_assoc()) {
                    echo htmlspecialchars($row_section['title']);
                }
                $stmt_section->close();
                ?>
            </font></td>
            <td align="center"><font color="white"><?php echo  number_format($row['money']) ?>원</font></td>
            <td align="center"><font color="white"><?php echo  number_format($row['DesignMoney']) ?>원</font></td>
            <td align="center">
                <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify', 'width=300,height=250');" value="수정">
                <input type="button" onClick="WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value="삭제">
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='10' align='center'>등록된 자료가 없습니다.</td></tr>";
}

$stmt->close();
$db->close();
?>

</table>

<p align="center">
<?php
$pagecut = 7;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

if ($start_offset != 0) {
    echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . ($start_offset - $one_bbs) . "&$mlang_pagego'>...[이전]</a>&nbsp;";
}

for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
    $newoffset = ($i - 1) * $listcut;
    if ($offset != $newoffset) {
        echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
    } else {
        echo "<font style='font:bold; color:green;'>($i)</font>&nbsp;";
    }
    if ($i == $end_page) break;
}

if ($start_offset != $end_offset) {
    echo "<a href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?offset=" . ($start_offset + $one_bbs) . "&$mlang_pagego'>[다음]...</a>";
}
echo "총목록갯수: $end_page 개";

$db->close();
?>
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php include "../down.php"; ?>
