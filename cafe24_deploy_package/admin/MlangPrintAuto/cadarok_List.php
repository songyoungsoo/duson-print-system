<?php
// PHP 7.4+ Updated - cadarok_List_updated.php (with pagination)
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

// top.php를 먼저 include (이 안에서 db.php가 include됨)
$M123 = "..";
include "$M123/top.php";

if ($mode === "delete" && $no) {
    $stmt = mysqli_prepare($db, "DELETE FROM {$table} WHERE no=?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // DB 연결은 페이지 끝에서 닫음
    echo "<script>
        alert('테이블명: {$table} - {$no} 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>";
    exit;
}
$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

// DB 연결 상태 확인 및 재연결 필요시 처리
if (!$db || mysqli_connect_errno()) {
    // DB 접근 허용 상수 정의 (이미 top.php에서 정의되었지만 안전을 위해)
    if (!defined('DB_ACCESS_ALLOWED')) {
        define('DB_ACCESS_ALLOWED', true);
    }
    include_once "$M123/../db.php";
}
// $db 연결은 이미 상단에서 db.php로 완료됨
$Mlang_query = $search === "yes"
    ? "SELECT * FROM {$table} WHERE style='" . mysqli_real_escape_string($db, $RadOne) . "' AND Section='" . mysqli_real_escape_string($db, $myListTreeSelect) . "' AND TreeSelect='" . mysqli_real_escape_string($db, $myList) . "'"
    : "SELECT * FROM {$table}";

$query = mysqli_query($db, $Mlang_query);
$recordsu = $query ? mysqli_num_rows($query) : 0;
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
$result = mysqli_query($db, "$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $style_title = '';
        $style_query = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['style']}'");
        if ($style_query && $style_row = mysqli_fetch_assoc($style_query)) {
            $style_title = $style_row['title'];
        }
        
        $section_title = '';
        $section_query = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['Section']}'");
        if ($section_query && $section_row = mysqli_fetch_assoc($section_query)) {
            $section_title = $section_row['title'];
        }
        
        $tree_title = '';
        $tree_query = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['TreeSelect']}'");
        if ($tree_query && $tree_row = mysqli_fetch_assoc($tree_query)) {
            $tree_title = $tree_row['title'];
        }
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

<?php
// DB 연결은 페이지 끝에서 자동으로 닫힘 (down.php 또는 스크립트 종료시)
?>
<?php include "../down.php"; ?>
