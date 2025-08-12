<?php
// envelope_List.php (Updated for PHP 7.4+ with mysqli)
include "../../db.php";

$TIO_CODE = "envelope";
$table = "MlangPrintAuto_{$TIO_CODE}";
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : (isset($_POST['offset']) ? (int)$_POST['offset'] : 0);
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';

$db = new mysqli($host, $user, $password, $dataname);
if ($db->connect_error) {
    die("DB 연결 오류: " . $db->connect_error);
}

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM {$table} WHERE no=?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();
    $db->close();
    echo ("<script>
        alert('테이블명: {$table} - {$no} 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>");
    exit;
}

$M123 = "..";
include "../top.php";
$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";
$db = mysqli_connect($host, $user, $password, $dataname);
$Mlang_query = $search === "yes"
    ? "SELECT * FROM {$table} WHERE style='" . $db->real_escape_string($RadOne) . "' AND Section='" . $db->real_escape_string($myList) . "'"
    : "SELECT * FROM {$table}";

$query = $db->query($Mlang_query);
$recordsu = $query ? $query->num_rows : 0;
$listcut = 15;
$total = $recordsu;

include "ListSearchBox.php";
?>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=right>
<input type='button' onClick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate', 'width=600,height=650');" value=' 구분 관리 '>
<input type='button' onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600');" value=' 가격/설명 관리 '>
<input type='button' onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250');" value=' 신 자료 입력 '>
<br><br>전체자료수-<font color=blue><b><?php echo  $total ?></b></font>개
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록번호</td>
<td align=center>구분</td>
<td align=center>종류</td>
<td align=center>인쇄색상</td>
<td align=center>수량</td>
<td align=center>가격</td>
<td align=center>디자인비</td>
<td align=center>관리기능</td>
</tr>

<?php
$db = mysqli_connect($host, $user, $password, $dataname);
$result = $db->query("$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $style_title = '';
        $section_title = '';
        $FGTwo = $db->query("SELECT title FROM $GGTABLE WHERE no='{$row['style']}'");
        if ($FGTwo && $r = $FGTwo->fetch_assoc()) $style_title = $r['title'];
        $FGOne = $db->query("SELECT title FROM $GGTABLE WHERE no='{$row['Section']}'");
        if ($FGOne && $r = $FGOne->fetch_assoc()) $section_title = $r['title'];

        echo "<tr bgcolor='#575757'>
            <td align=center><font color=white>{$row['no']}</font></td>
            <td align=center><font color=white>{$style_title}</font></td>
            <td align=center><font color=white>{$section_title}</font></td>
            <td align=center><font color=white>" . 
            ($row['POtype'] == "1" ? "마스터1도" : ($row['POtype'] == "2" ? "마스터2도" : ($row['POtype'] == "3" ? "칼라4도(옵셋)" : ""))) . "</font></td>
            <td align=center><font color=white>{$row['quantity']}매</font></td>
            <td align=center><font color=white>" . number_format($row['money']) . "원</font></td>
            <td align=center><font color=white>" . number_format($row['DesignMoney']) . "원</font></td>
            <td align=center>
                <input type='button' onClick=\"window.open('{$TIO_CODE}_admin.php?mode=form&code=Modify&no={$row['no']}&Ttable={$TIO_CODE}', '{$table}_Form2Modify', 'width=300,height=250');\" value=' 수정 '>
                <input type='button' onClick=\"WomanMember_Admin_Del('{$row['no']}');\" value=' 삭제 '>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan=10 align=center><br><br>등록 자료없음</td></tr>";
}
$db->close();
?>
</table>
<?php include "../down.php"; ?>
