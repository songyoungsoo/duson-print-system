<?php
// PHP 7.4+ Safe Version of MerchandiseBond List Page

define('DB_ACCESS_ALLOWED', true);
include "../../db.php";

$TIO_CODE = "merchandisebond";
$table = "mlangprintauto_{$TIO_CODE}";

$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = (int)($_GET['no'] ?? $_POST['no'] ?? 0);
$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$offset = (int)($_GET['offset'] ?? $_POST['offset'] ?? 0);

$cate = $_GET['cate'] ?? '';
$title_search = $_GET['title_search'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? '';

if ($mode === "delete" && $no > 0) {
    $stmt = mysqli_prepare($db, "DELETE FROM $table WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($db);
    echo ("<script>alert('테이블명: $table - $no 번 자료 삭제 완료'); opener.parent.location.reload(); window.self.close();</script>");
    exit;
}

$M123 = "..";
include "$M123/top.php";


// 데이터베이스 연결 함수 정의 (전역에서 사용)
function ensure_db_connection() {
    // 공통 db.php 설정 사용
    global $db;
    if (!$db) {
        include "../../db.php";
    }
    return $db;
}

// 안전한 DB 연결 확보
$db = ensure_db_connection();
$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

?>

// Define GGTABLE from ConDb.php's $TABLE variable
$GGTABLE = $TABLE; // This is "mlangprintauto_transactioncate"
<head>
<script>
function clearField(field){ if (field.value == field.defaultValue) field.value = ""; }
function checkField(field){ if (!field.value) field.value = field.defaultValue; }
function WomanMember_Admin_Del(no){
    if (confirm(no + '번 자료를 삭제 처리 하시겠습니까?')) {
        const str = '<?php echo  $PHP_SELF ?>?no=' + no + '&mode=delete';
        const popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.location.href = str;
        popup.focus();
    }
}
</script>
</head>

<table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
<tr>
<td align="left"><?php include "ListSearchBox.php"; ?></td>

<?php
include "../../db.php";
// $db 연결 재설정 (top.php 이후)
$Mlang_query = $search === "yes"
    ? "SELECT * FROM $table WHERE style='" . mysqli_real_escape_string($db, $RadOne) . "' AND Section='" . mysqli_real_escape_string($db, $myList) . "'"
    : "SELECT * FROM $table";

$db = ensure_db_connection(); 
$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$listcut = 15;

?>
<td align="right">
<input type="button" value=" 구분 관리 " onClick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate','width=600,height=650');">
<input type="button" value=" 가격/설명 관리 " onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600');">
<input type="button" value=" 신 자료 입력 " onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250');">
<br><br>
전체자료수-<b style="color:blue;"><?php echo  $recordsu ?></b> 개&nbsp;&nbsp;
</td>
</tr>
</table>

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
$db = ensure_db_connection(); 
$result = mysqli_query($db, "$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
$rows = mysqli_num_rows($result);

if ($rows) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<tr bgcolor="#575757">
<td align="center"><font color="white"><?php echo  $row['no'] ?></font></td>
<td align="center"><font color="white">
<?php
ensure_db_connection(); $res = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='" . $row['style'] . "'");
$titleRow = mysqli_fetch_assoc($res);
echo $titleRow['title'] ?? '';
?>
</font></td>
<td align="center"><font color="white"><?php echo  $row['quantity'] ?></font></td>
<td align="center"><font color="white"><?php echo  $row['POtype'] == "1" ? "단면" : ($row['POtype'] == "2" ? "양면" : '') ?></font></td>
<td align="center"><font color="white">
<?php
ensure_db_connection(); $res2 = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='" . $row['Section'] . "'");
$subTitleRow = mysqli_fetch_assoc($res2);
echo $subTitleRow['title'] ?? '';
?>
</font></td>
<td align="center"><font color="white"><?php echo  number_format((int)$row['money']) ?>원</font></td>
<td align="center"><font color="white"><?php echo  number_format((int)$row['DesignMoney']) ?>원</font></td>
<td align="center">
<input type="button" value=" 수정 " onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250');">
<input type="button" value=" 삭제 " onClick="WomanMember_Admin_Del('<?php echo  $row['no'] ?>');">
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="10" align="center"><br><br>등록 자료없음</td></tr>
<?php } ?>
</table>

<p align="center">
<?php
if ($rows) {
    $mlang_pagego = ($search === "yes") 
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
        echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;
        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }
        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }

    echo " 총목록갯수: $end_page 개";
}

mysqli_close($db);
?>
</p>
<?php include "../down.php"; ?>
