<?php
// envelope_List.php (Updated for PHP 7.4+ with mysqli)

$TIO_CODE = "envelope";
$table = "mlangprintauto_{$TIO_CODE}";
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : (isset($_POST['offset']) ? (int)$_POST['offset'] : 0);
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';

// top.php를 먼저 include (이 안에서 db.php가 include됨)
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
if ($mode == "delete") {
    $stmt = mysqli_prepare($db, "DELETE FROM {$table} WHERE no=?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // DB 연결은 페이지 끝에서 닫음
    echo ("<script>
        alert('테이블명: {$table} - {$no} 번 자료 삭제 완료');
        opener.parent.location.reload();
        window.self.close();
    </script>");
    exit;
}
$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

// Define GGTABLE from ConDb.php's $TABLE variable
$GGTABLE = $TABLE; // This is "mlangprintauto_transactioncate"

// DB 연결 상태 확인 및 재연결 필요시 처리
if (!$db || mysqli_connect_errno()) {
    // DB 접근 허용 상수 정의 (이미 top.php에서 정의되었지만 안전을 위해)
    if (!defined('DB_ACCESS_ALLOWED')) {
        define('DB_ACCESS_ALLOWED', true);
    }
    include_once "$M123/../db.php";
}
?>

<head>
<script>
function clearField(field)
{
	if (field.value == field.defaultValue) {
		field.value = "";
	}
}
function checkField(field)
{
	if (!field.value) {
		field.value = field.defaultValue;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function WomanMember_Admin_Del(no){
	if (confirm(+no+'번 자료을 삭제 처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='<?php echo $PHP_SELF?>?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<?php include "ListSearchBox.php";?>
</td>
<?php
include "../../db.php";
// $db 연결 재설정 (top.php 이후)
$Mlang_query = $search === "yes"
    ? "SELECT * FROM {$table} WHERE style='" . mysqli_real_escape_string($db, $RadOne) . "' AND Section='" . mysqli_real_escape_string($db, $myList) . "'"
    : "SELECT * FROM {$table}";

$db = ensure_db_connection(); 
$query = mysqli_query($db, $Mlang_query);
$recordsu = $query ? mysqli_num_rows($query) : 0;
$listcut = 15;
$total = $recordsu;

// 페이지네이션 변수 설정
$pagecut = 7;  // 한 장당 보여줄 페이지수 
$one_bbs = $listcut * $pagecut;  // 한 장당 실을 수 있는 목록(게시물)수 
$start_offset = intval($offset / $one_bbs) * $one_bbs;  // 각 장에 처음 페이지의 $offset값
$end_offset = intval($recordsu / $one_bbs) * $one_bbs;  // 마지막 장의 첫페이지의 $offset값
$start_page = intval($start_offset / $listcut) + 1; // 각 장에 처음 페이지의 값
$end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); // 마지막 장의 끝 페이지

?>

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
// $db 연결은 이미 상단에서 db.php로 완료됨
$db = ensure_db_connection(); 
$result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $style_title = '';
        $section_title = '';
        ensure_db_connection(); $FGTwo = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['style']}'");
        if ($FGTwo && $r = mysqli_fetch_assoc($FGTwo)) $style_title = $r['title'];
        ensure_db_connection(); $FGOne = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['Section']}'");
        if ($FGOne && $r = mysqli_fetch_assoc($FGOne)) $section_title = $r['title'];

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
// DB 연결은 페이지 끝에서 자동으로 닫힘 (down.php 또는 스크립트 종료시)
?>
</table>

<p align="center">
<?php
if ($recordsu > 0) {
    // 검색 파라미터 유지
    $mlang_pagego = ($search === "yes")
        ? "search=$search&RadOne=$RadOne&myList=$myList"
        : "";

    // 이전 페이지 링크
    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    // 페이지 번호 링크
    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;
        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font-weight:bold; color:green;'>($i)</font>&nbsp;";
        }
        if ($i == $end_page) break;
    }

    // 다음 페이지 링크
    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }

    echo "&nbsp;총목록갯수: $end_page 개";
}
?>
</p>

<?php include "../down.php"; ?>
