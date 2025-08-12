<?php
declare(strict_types=1);
header("Content-Type: text/html; charset=utf-8");
include "../../db.php";
$db = mysqli_connect($host, $user, $password, $dataname);
$db->set_charset('utf8');

$mode             = isset($_REQUEST['mode'])             ? trim($_REQUEST['mode']) : '';
$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_POST['search']) ? $_POST['search'] : '');
$RadOne = isset($_GET['RadOne']) ? $_GET['RadOne'] : (isset($_POST['RadOne']) ? $_POST['RadOne'] : '');
$myListTreeSelect = isset($_GET['myListTreeSelect']) ? $_GET['myListTreeSelect'] : (isset($_POST['myListTreeSelect']) ? $_POST['myListTreeSelect'] : '');
$myList = isset($_GET['myList']) ? $_GET['myList'] : (isset($_POST['myList']) ? $_POST['myList'] : '');
$Ttable = isset($_GET['Ttable']) ? $_GET['Ttable'] : (isset($_POST['Ttable']) ? $_POST['Ttable'] : '');

// $GGTABLE = htmlspecialchars($GGTABLE);

$Ttable = htmlspecialchars($Ttable);
$GGTABLE = "MlangPrintAuto_{$Ttable}";
if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

$TIO_CODE = "inserted";
$table = "MlangPrintAuto_{$TIO_CODE}";

if ($mode === "delete" && $no > 0) {
    $stmt = $db->prepare("DELETE FROM {$table} WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();
    $db->close();

    echo "<script>
        alert('테이블명: " . htmlspecialchars($table) . " - {$no} 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>";
    exit;
}

$M123 = "..";
include "../top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "{$T_DirUrl}/ConDb.php";



$db = mysqli_connect($host, $user, $password, $dataname);
if ($search === "yes") {
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE style=? AND TreeSelect=? AND Section=?");
    $stmt->bind_param("sss", $RadOne, $myListTreeSelect, $myList);
} else {
    $stmt = $db->prepare("SELECT * FROM {$table}");
}

$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
$total = $recordsu;

$listcut = 15;

?>
<head>
    <meta charset="UTF-8">
    <script>
        function WomanMember_Admin_Del(no) {
            if (confirm(no + '번 자료를 삭제하시겠습니까?\n복구가 불가능하므로 신중히 결정하세요.')) {
                let str = "<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>?no=" + no + "&mode=delete";
                let popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
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

        <td align="right">
            <input type='button' onclick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate','width=600,height=650');" value=' 구분 관리 '>
            <input type='button' onclick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600');" value=' 가격/설명 관리 '>
            <input type='button' onclick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250');" value=' 신 자료 입력 '>
            <br><br>
            전체자료수 - <span style="color:blue;"><b><?php echo  $total ?></b></span> 개
        </td>
    </tr>
</table>

<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
    <tr>
        <td align="center">등록번호</td>
        <td align="center">인쇄색상</td>
        <td align="center">종이종류</td>
        <td align="center">종이규격</td>
        <td align="center">인쇄면</td>
        <td align="center">수량(옆)</td>
        <td align="center">가격</td>
        <td align="center">디자인비</td>
        <td align="center">관리기능</td>
    </tr>

<?php
$db = mysqli_connect($host, $user, $password, $dataname);
$query = $db->query("SELECT * FROM {$table} ORDER BY no DESC LIMIT {$offset}, {$listcut}");
if ($query && $query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        ?>
        <tr bgcolor='#575757'>
            <td align="center"><font color="white"><?php echo  htmlspecialchars($row['no']) ?></font></td>
            <td align="center"><font color="white"><?php echo  getTitle($db, $GGTABLE, $row['style']) ?></font></td>
            <td align="center"><font color="white"><?php echo  getTitle($db, $GGTABLE, $row['TreeSelect']) ?></font></td>
            <td align="center"><font color="white"><?php echo  getTitle($db, $GGTABLE, $row['Section']) ?></font></td>
            <td align="center"><font color="white"><?php echo  $row['POtype'] == "1" ? "단면" : "양면" ?></font></td>
            <td align="center"><font color="white"><?php echo  htmlspecialchars($row['quantity']) ?>연(<?php echo  htmlspecialchars($row['quantityTwo']) ?>장)</font></td>
            <td align="center"><font color="white"><?php echo  number_format((int)$row['money']) ?>원</font></td>
            <td align="center"><font color="white"><?php echo  number_format((int)$row['DesignMoney']) ?>원</font></td>
            <td align="center">
                <input type='button' onclick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250');" value=' 수정 '>
                <input type='button' onclick="WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='10' align='center'><br><br>" . ($search ? "관련 검색 자료 없음" : "등록 자료 없음") . "</td></tr>";
}

function getTitle(mysqli $db, string $table, string $no): string {
    $stmt = $db->prepare("SELECT title FROM {$table} WHERE no = ?");
    $stmt->bind_param("s", $no);
    $stmt->execute();
    $stmt->bind_result($title);
    $stmt->fetch();
    $stmt->close();
    return $title ?? '';
}
?>
</table>

<p align="center">
<?php
// 페이징 처리
$pagecut = 7;
$one_bbs = $listcut * $pagecut;
$start_offset = intval($offset / $one_bbs) * $one_bbs;
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;
$start_page = intval($start_offset / $listcut) + 1;
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

$mlang_pagego = http_build_query([
    'search' => $search,
    'RadOne' => $RadOne,
    'myListTreeSelect' => $myListTreeSelect,
    'myList' => $myList
]);

if ($start_offset !== 0) {
    $apoffset = $start_offset - $one_bbs;
    echo "<a href='{$_SERVER['PHP_SELF']}?offset={$apoffset}&{$mlang_pagego}'>...[이전]</a>&nbsp;";
}

for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
    $newoffset = ($i - 1) * $listcut;
    if ($offset !== $newoffset) {
        echo "&nbsp;<a href='{$_SERVER['PHP_SELF']}?offset={$newoffset}&{$mlang_pagego}'>($i)</a>&nbsp;";
    } else {
        echo "&nbsp;<strong style='color:green;'>($i)</strong>&nbsp;";
    }

    if ($i === $end_page) break;
}

if ($start_offset !== $end_offset) {
    $nextoffset = $start_offset + $one_bbs;
    echo "&nbsp;<a href='{$_SERVER['PHP_SELF']}?offset={$nextoffset}&{$mlang_pagego}'>[다음]...</a>";
}
echo "<br>총목록갯수: {$end_page} 개";
$db->close();
?>
</p>

<?php include "../down.php"; ?>
