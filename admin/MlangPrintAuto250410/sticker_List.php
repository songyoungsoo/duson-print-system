<?php
include "../../db.php";
$TIO_CODE = "sticker";
$table = "MlangPrintAuto_{$TIO_CODE}";  // ${TIO_CODE} 대신 {$TIO_CODE} 사용

$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_POST['search']) ? $_POST['search'] : '');
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$no = isset($_GET['no']) ? intval($_GET['no']) : (isset($_POST['no']) ? intval($_POST['no']) : 0);

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM {$table} WHERE no = ?");
    if ($stmt) {
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $stmt->close();
    } else {
        die("Prepare failed: " . $db->error);
    }
    $db->close();

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
include "{$T_DirUrl}/ConDb.php";  // ${T_DirUrl} 대신 {$T_DirUrl} 사용
?>

<head>
<script>
// JavaScript functions...
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php include "ListSearchBox.php"; ?>
</td>

<?php
$db = mysqli_connect($host, $user, $password, $dataname);

if ($search == "yes") {
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE style = ? AND Section = ?");
    if ($stmt) {
        $RadOne = isset($_GET['RadOne']) ? $_GET['RadOne'] : (isset($_POST['RadOne']) ? $_POST['RadOne'] : '');
        $myList = isset($_GET['myList']) ? $_GET['myList'] : (isset($_POST['myList']) ? $_POST['myList'] : '');
        $stmt->bind_param("ss", $RadOne, $myList);
    } else {
        die("Prepare failed: " . $db->error);
    }
} else {
    $stmt = $db->prepare("SELECT * FROM {$table}");
    if (!$stmt) {
        die("Prepare failed: " . $db->error);
    }
}
$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
$total = $recordsu;

$listcut = 15;  // 한 페이지당 보여줄 목록 게시물수.
?>

<td align=right>
<!-- HTML 및 JavaScript 코드는 그대로 유지 -->
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>스타일</td>
<td align=center>섹션</td>
<td align=center>수량</td>
<td align=center>금액</td>
<td align=center>디자인비용</td>
<td align=center>관리</td>
</tr>

<?php
$Mlang_query = "SELECT * FROM {$table}"; // 기본 쿼리 정의
$stmt = $db->prepare("{$Mlang_query} ORDER BY no DESC LIMIT ?, ?");
if ($stmt) {
    $stmt->bind_param("ii", $offset, $listcut);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;

    if ($rows) {
        while ($row = $result->fetch_assoc()) {
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?php echo  htmlspecialchars($row['no']) ?></font></td>
<td align=center><font color=white>
<?php
    $stmt_FGTwo = $db->prepare("SELECT * FROM {$GGTABLE} WHERE no = ?");
    if ($stmt_FGTwo) {
        $stmt_FGTwo->bind_param("i", $row['style']);
        $stmt_FGTwo->execute();
        $result_FGTwo = $stmt_FGTwo->get_result();
        $row_FGTwo = $result_FGTwo->fetch_assoc();
        if ($row_FGTwo) {
            echo htmlspecialchars($row_FGTwo['title']);
        }
    }
?>
</font></td>
<td align=center><font color=white>
<?php
    $stmt_FGOne = $db->prepare("SELECT * FROM {$GGTABLE} WHERE no = ?");
    if ($stmt_FGOne) {
        $stmt_FGOne->bind_param("i", $row['Section']);
        $stmt_FGOne->execute();
        $result_FGOne = $stmt_FGOne->get_result();
        $row_FGOne = $result_FGOne->fetch_assoc();
        if ($row_FGOne) {
            echo htmlspecialchars($row_FGOne['title']);
        }
    }
?>
</font></td>
<td align=center><font color=white><?php echo  htmlspecialchars($row['quantity']) ?>개</font></td>
<td align=center><font color=white><?php echo  number_format($row['money']) ?>원</font></td>
<td align=center><font color=white><?php echo  number_format($row['DesignMoney']) ?>원</font></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
</td>
</tr>

<?php
        }
    } else {
        if ($search) {
            echo "<tr><td colspan=10><p align=center><BR><BR>관련 검색 데이터 없음</p></td></tr>";
        } else {
            echo "<tr><td colspan=10><p align=center><BR><BR>등록 데이터 없음</p></td></tr>";
        }
    }
} else {
    die("Prepare failed: " . $db->error);
}
?>

</table>

<p align='center'>
<?php
// 페이지네이션 코드 유지
$db->close();
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->
<?php include "../down.php"; ?>
