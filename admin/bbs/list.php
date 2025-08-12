<?php
/**
 * bbs_admin.php
 * 관리자용 게시판 생성/관리 화면 (PHP 7.4+, MySQLi)
 *
 * 필드 설명:
 *  Mlang_${table}_bbs:
 *    Mlang_bbs_no      : 게시글 번호 (AUTO_INCREMENT)
 *    Mlang_bbs_member  : 등록인 (VARCHAR)
 *    Mlang_bbs_title   : 제목 (TEXT)
 *    Mlang_bbs_style   : 문서형식 (VARCHAR)
 *    Mlang_bbs_connent : 내용 (TEXT)
 *    ... 기타 필드 ...
 *
 *   Mlang_BBS_Admin:
 *    no                : 게시판 번호
 *    title             : 게시판 제목
 *    id                : 게시판 ID
 *    pass              : 게시판 비밀번호
 *    header            : 윗 html 내용
 *    footer            : 아래 html 내용
 *    header_include    : 윗 INCLUDE 파일
 *    footer_include    : 아래 INCLUDE 파일
 *    file_select       : 파일 첨부 여부
 *    link_select       : 링크 첨부 여부
 *    recnum            : 한페이지당 출력수
 *    lnum              : 페이지이동 메뉴수
 *    cutlen            : 제목 글자수 제한
 *    New_Article       : 새글 표시 유지기간
 *    date_select       : 등록일 출력여부
 *    name_select       : 이름 출력여부
 *    count_select      : 조회수 출력여부
 *    recommendation_select : 추천수 출력여부
 *    secret_select     : 공개/비공개 출력여부
 *    write_select      : 쓰기 권한 (member/guest/admin)
 *    date              : 생성일
 */

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

// session_start();
// // DB 접속 (db.php에서 $db = new mysqli(...)로 설정)
include "../db.php";
$table="Mlang_BBS_Admin";

// --- JavaScript 유효성 검사 (변경 없음) --- ?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<script>
var NUM = "0123456789";
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.charAt(i)) < 0) return false;
    }
    return true;
}

function BbsAdminCheckField() {
    var f = document.BbsAdmin;
    if (f.skin.value === "0") {
        alert("SKIN을 선택해주세요.");
        return false;
    }
    if (f.title.value.trim() === "") {
        alert("게시판 타이틀을 입력해주세요.");
        return false;
    }
    if (!TypeCheck(f.table.value, ALPHA + NUM) || f.table.value.length < 2 || f.table.value.length > 20) {
        alert("테이블명은 영문자 및 숫자 2자 이상 20자 이하로만 가능합니다.");
        return false;
    }
    if (!TypeCheck(f.pass.value, ALPHA + NUM) || f.pass.value.length < 4 || f.pass.value.length > 20) {
        alert("비밀번호는 영문자 및 숫자 4자 이상 20자 이하로만 가능합니다.");
        return false;
    }
    return true;
}

function clearField(field) {
    if (field.value === field.defaultValue) field.value = "";
}
function checkField(field) {
    if (!field.value) field.value = field.defaultValue;
}

function BBS_Admin_Del(id) {
    if (confirm("게시판과 관련된 모든 자료가 삭제됩니다. 계속하시겠습니까?")) {
        window.location.href = './bbs/delete.php?id=' + encodeURIComponent(id);
    }
}

function BbsAdminSearchCheckField() {
    var f = document.BbsAdminSearch;
    if (f.search.value.trim() === "") {
        alert("검색어를 입력해주세요.");
        return false;
    }
    return true;
}
</script>
</head>
<body>

<table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
<tr>
<td colspan="2" style="color:red;">
    * 게시판 비밀번호는 권한 관리용이며, 관리 비밀번호는 모든 게시판 권한을 가집니다.<br>
    * 비밀번호를 자주 변경하시길 권장합니다.
</td>
</tr>
<tr>
    <!-- 새 게시판 생성 폼 -->
    <form name="BbsAdmin" method="post" onsubmit="return BbsAdminCheckField();" action="./bbs/submit.php">
        <input type="hidden" name="mode" value="submit">
        <td>
            <?php $BbsAdminCateUrl = ".."; include "./bbs/BbsAdminCate.php"; ?>
            <input type="text" name="title" value="게시판 타이틀" size="20" maxlength="100" onfocus="clearField(this);" onblur="checkField(this);">
            <input type="text" name="table" value="테이블명" size="20" maxlength="20" onfocus="clearField(this);" onblur="checkField(this);">
            <input type="text" name="pass" value="비밀번호" size="14" maxlength="20" onfocus="clearField(this);" onblur="checkField(this);">
            <input type="submit" value="새게시판 생성">
        </td>
    </form>
    <!-- 게시판 검색 폼 -->
    <form name="BbsAdminSearch" method="post" onsubmit="return BbsAdminSearchCheckField();" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="mode" value="list">
        <td align="right">
            <select name="bbs_cate">
                <option value="title">타이틀</option>
                <option value="id">테이블명</option>
            </select>
            <input type="text" name="search" size="18" onfocus="clearField(this);" onblur="checkField(this);">
            <input type="submit" value="검색">
        </td>
    </form>
</tr>
</table>

<?php
// 게시판 목록/검색 처리
$tableAdmin = 'Mlang_BBS_Admin';
$mode       = $_REQUEST['mode'] ?? 'list';
$bbsCate    = $_POST['bbs_cate'] ?? '';
$searchTerm = trim($_POST['search'] ?? '');

$params = [];
$sqlBase = "SELECT * FROM {$tableAdmin}";
$where   = '';
if ($mode === 'list' && $searchTerm !== '') {
    $where = " WHERE {$bbsCate} LIKE ?";
    $params[] = '%' . $searchTerm . '%';
}

// 전체 건수
$stmtCount = $db->prepare("SELECT COUNT(*) FROM {$tableAdmin}" . $where);
if ($where) {
    $stmtCount->bind_param('s', $params[0]);
}
$stmtCount->execute();
$stmtCount->bind_result($totalRecords);
$stmtCount->fetch();
$stmtCount->close();

// 페이징 설정
$listCut = 15;
$offset   = (int)($_GET['offset'] ?? 0);

// 목록 조회
$sqlList = "{$sqlBase}{$where} ORDER BY no DESC LIMIT ?, ?";
$stmtList = $db->prepare($sqlList);
if ($where) {
    $stmtList->bind_param('sii', $params[0], $offset, $listCut);
} else {
    $stmtList->bind_param('ii', $offset, $listCut);
}
$stmtList->execute();
$result = $stmtList->get_result();

if ($result->num_rows > 0): ?>

<style>
/* 게시판 리스트 스타일 개선 */
.bbs-list-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background-color: #fff;
    border: 1px solid #ddd;
}

.bbs-list-table th {
    background-color: #4a6da7;
    color: white;
    padding: 10px;
    text-align: center;
    font-weight: bold;
    border: 1px solid #3a5a8c;
}

.bbs-list-table td {
    padding: 8px;
    text-align: center;
    border: 1px solid #ddd;
    color: #333;
}

.bbs-list-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.bbs-list-table tr:hover {
    background-color: #f1f1f1;
}

.bbs-btn {
    display: inline-block;
    padding: 5px 10px;
    margin: 2px;
    background-color: #4a6da7;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    font-size: 12px;
    border: none;
    cursor: pointer;
}

.bbs-btn:hover {
    background-color: #3a5a8c;
}

.bbs-btn-danger {
    background-color: #d9534f;
}

.bbs-btn-danger:hover {
    background-color: #c9302c;
}
</style>

<table border="0" align="center" width="100%" cellpadding="5" cellspacing="2" class="bbs-list-table">
<tr>
    <th>게시판 제목</th>
    <th>SKIN</th>
    <th>테이블명</th>
    <th>비밀번호</th>
    <th>생성일</th>
    <th>자료수</th>
    <th>관리기능</th>
</tr>
<?php while ($row = $result->fetch_assoc()):
    // 검색어 강조
    if ($searchTerm) {
        $row['title'] = str_ireplace($searchTerm, '<b style="color:blue;">'.$searchTerm.'</b>', $row['title']);
        $row['id']    = str_ireplace($searchTerm, '<b style="color:red;">'.$searchTerm.'</b>', $row['id']);
    }

    // 게시판별 게시물 수 조회
    $bbsCount = 0; // 기본값 설정
    try {
        // 테이블이 존재하는지 확인
        $checkTable = $db->query("SHOW TABLES LIKE 'Mlang_{$row['id']}_bbs'");
        if ($checkTable && $checkTable->num_rows > 0) {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM Mlang_{$row['id']}_bbs");
            if ($countStmt) {
                $countStmt->execute();
                $countStmt->bind_result($bbsCount);
                $countStmt->fetch();
                $countStmt->close();
            }
        }
    } catch (Exception $e) {
        // 오류 발생 시 기본값 사용
        $bbsCount = 0;
    }
?>
<tr>
    <td><a href="<?php echo  htmlspecialchars("{$Homedir}/bbs/bbs.php?table={$row['id']}&mode=list") ?>" target="_blank" style="color:#0066cc; font-weight:bold;"><?php echo  $row['title'] ?></a></td>
    <td><?php echo  $row['skin'] ?></td>
    <td><?php echo  $row['id'] ?></td>
    <td><?php echo  $row['pass'] ?></td>
    <td align="center"><?php echo  $row['date'] ?></td>
    <td align="center"><?php echo  $bbsCount ?></td>
    <td align="center">
        <button class="bbs-btn" onclick="window.open('./bbs/AdminModify.php?code=start&no=<?php echo  $row['no'] ?>', 'bbs_mod', 'width=650,height=600');">설정</button>
        <button class="bbs-btn bbs-btn-danger" onclick="BBS_Admin_Del('<?php echo  $row['id'] ?>');">삭제</button>
        <button class="bbs-btn" onclick="window.open('./bbs/dump.php?TableName=Mlang_<?php echo  $row['id'] ?>_bbs', 'bbs_dump', 'width=567,height=451');">백업</button>
    </td>
</tr>
<?php endwhile; ?>
</table>
<!-- 페이지네이션 -->
<p align="center">
<?php
// // 이전 링크
// if ($currentPage > 1) {
//     $prevOff = ($currentPage - 2) * $listCut;
//     echo '<a href="?offset=' . $prevOff . '&mode=list&bbs_cate=' . urlencode($bbsCate) . '&search=' . urlencode($searchTerm) . '">...[이전]</a> ';
// }
// // 페이지 번호
// for ($i = 1; $i <= $totalPages; $i++) {
//     $off = ($i - 1) * $listCut;
//     if ($i === $currentPage) {
//         echo "[{$i}] ";
//     } else {
//         echo '<a href="?offset=' . $off . '&mode=list&bbs_cate=' . urlencode($bbsCate) . '&search=' . urlencode($searchTerm) . '">[' . $i . ']</a> ';
//     }
// }
// // 다음 링크
// if ($currentPage < $totalPages) {
//     $nextOff = $currentPage * $listCut;
//     echo '<a href="?offset=' . $nextOff . '&mode=list&bbs_cate=' . urlencode($bbsCate) . '&search=' . urlencode($searchTerm) . '">[다음]...</a>';
// }
// echo '총 페이지 수: ' . $totalPages;


// 전체 개수 구하기
$result_count = $db->query("SELECT COUNT(*) AS cnt FROM {$table}");
$total        = (int)$result_count->fetch_assoc()['cnt'];

// 페이징 기본 변수
$listCut      = 15;       // 한 페이지당 글 개수 (원래 선언된 $listCut)
$pageCut      = 10;       // 페이지블록당 표시할 페이지 수
$oneBlock     = $listCut * $pageCut;
$startOffset  = intval($offset  / $oneBlock) * $oneBlock;
$endOffset    = intval($total   / $oneBlock) * $oneBlock;
$startPage    = intval($startOffset / $listCut) + 1;
$endPage      = ($total % $listCut > 0)
                  ? intval($total / $listCut) + 1
                  : intval($total / $listCut);

// 이전 링크
if ($startOffset > 0) {
    $prevOffset = $startOffset - $oneBlock;
    echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'])
       . '?offset=' . $prevOffset
       . '&mode=list&bbs_cate=' . urlencode($bbsCate)
       . '&search=' . urlencode($searchTerm)
       . '">...[이전]</a> ';
}

// 페이지 번호
for ($i = $startPage; $i < $startPage + $pageCut && $i <= $endPage; $i++) {
    $pageOffset = ($i - 1) * $listCut;
    if ($i === intval($offset / $listCut) + 1) {
        echo " <strong>[{$i}]</strong> ";
    } else {
        echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'])
           . '?offset=' . $pageOffset
           . '&mode=list&bbs_cate=' . urlencode($bbsCate)
           . '&search=' . urlencode($searchTerm)
           . '">[' . $i . ']</a> ';
    }
}

// 다음 링크
if ($startOffset + $oneBlock < $total) {
    $nextOffset = $startOffset + $oneBlock;
    echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'])
       . '?offset=' . $nextOffset
       . '&mode=list&bbs_cate=' . urlencode($bbsCate)
       . '&search=' . urlencode($searchTerm)
       . '">[다음]...</a>';
}

echo ' 총페이지수: ' . $endPage;
?>

</p>

<?php else: ?>
    <p align="center"><?php echo  $searchTerm ? '<b>' . htmlspecialchars($searchTerm) . '</b>에 대한 게시판이 없습니다.' : '생성된 게시판이 없습니다.' ?></p>
<?php endif;
$stmtList->close();
$db->close();
?>

</body>
</html>




