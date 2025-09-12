<?php
// PHP 7.4+ Updated - cadarok_List_updated.php (with pagination)
$TIO_CODE = "cadarok";
$table = "mlangprintauto_{$TIO_CODE}";
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
// $db 연결은 이미 상단에서 db.php로 완료됨
$Mlang_query = $search === "yes"
    ? "SELECT * FROM {$table} WHERE style='" . mysqli_real_escape_string($db, $RadOne) . "' AND Section='" . mysqli_real_escape_string($db, $myListTreeSelect) . "' AND TreeSelect='" . mysqli_real_escape_string($db, $myList) . "'"
    : "SELECT * FROM {$table}";

$db = ensure_db_connection(); 
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
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $TIO_CODE ?> 관리 - MlangPrintAuto</title>
    <link rel="stylesheet" href="../css/corporate-design-system.css">
    <style>
        .management-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-lg);
        }
        .page-header {
            background: var(--bg-primary);
            border: 1px solid var(--bg-tertiary);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .data-table {
            background: var(--bg-primary);
            border: 1px solid var(--bg-tertiary);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-base);
        }
        .table-header {
            background: var(--bg-secondary);
            padding: var(--space-md);
            border-bottom: 1px solid var(--bg-tertiary);
            display: grid;
            grid-template-columns: 80px 120px 120px 120px 100px 100px 140px;
            gap: var(--space-sm);
            font-weight: 600;
            font-size: var(--text-sm);
            color: var(--text-primary);
        }
        .table-row {
            display: grid;
            grid-template-columns: 80px 120px 120px 120px 100px 100px 140px;
            gap: var(--space-sm);
            padding: var(--space-md);
            border-bottom: 1px solid var(--bg-tertiary);
            transition: background-color 0.2s ease;
            align-items: center;
        }
        .table-row:hover {
            background: var(--bg-secondary);
        }
        .table-row:last-child {
            border-bottom: none;
        }
        .table-cell {
            font-size: var(--text-sm);
            color: var(--text-secondary);
            text-align: center;
        }
        .action-buttons {
            display: flex;
            gap: var(--space-xs);
        }
        .btn-sm {
            padding: var(--space-xs) var(--space-sm);
            font-size: var(--text-xs);
            border-radius: var(--radius-sm);
        }
        .empty-state {
            text-align: center;
            padding: var(--space-3xl);
            color: var(--text-secondary);
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-lg);
            font-size: var(--text-sm);
        }
        .pagination a {
            padding: var(--space-xs) var(--space-sm);
            border: 1px solid var(--bg-tertiary);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-secondary);
            transition: all 0.2s ease;
        }
        .pagination a:hover {
            background: var(--primary-color);
            color: var(--text-inverse);
            border-color: var(--primary-color);
        }
        .current-page {
            font-weight: 600;
            color: var(--primary-color);
        }
        .stats-badge {
            background: var(--info-color);
            color: var(--text-inverse);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-size: var(--text-xs);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="management-container">
        <div class="page-header">
            <div>
                <?php include "ListSearchBox.php";?>
            </div>
            <div style="display: flex; align-items: center; gap: var(--space-sm);">
                <button type="button" class="btn btn-outline btn-sm" onClick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok','<?php echo  $table ?>_FormCate','width=600,height=650');">구분 관리</button>
                <button type="button" class="btn btn-outline btn-sm" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm','<?php echo  $table ?>_Form1','width=820,height=600');">가격/설명 관리</button>
                <button type="button" class="btn btn-primary btn-sm" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>','<?php echo  $table ?>_Form2','width=300,height=250');">신 자료 입력</button>
                <span class="stats-badge"><?php echo  $total ?>개</span>
            </div>
        </div>

        <div class="data-table">

            <div class="table-header">
                <div>등록번호</div>
                <div>구분</div>
                <div>규격</div>
                <div>종이종류</div>
                <div>수량</div>
                <div>기타</div>
                <div>관리기능</div>
            </div>

<?php
$db = ensure_db_connection(); 
$result = mysqli_query($db, "$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $style_title = '';
        ensure_db_connection(); $style_query = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['style']}'");
        if ($style_query && $style_row = mysqli_fetch_assoc($style_query)) {
            $style_title = $style_row['title'];
        }
        
        $section_title = '';
        ensure_db_connection(); $section_query = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['Section']}'");
        if ($section_query && $section_row = mysqli_fetch_assoc($section_query)) {
            $section_title = $section_row['title'];
        }
        
        $tree_title = '';
        ensure_db_connection(); $tree_query = mysqli_query($db, "SELECT title FROM $GGTABLE WHERE no='{$row['TreeSelect']}'");
        if ($tree_query && $tree_row = mysqli_fetch_assoc($tree_query)) {
            $tree_title = $tree_row['title'];
        }
        $quantity_display = ($row['quantity'] === "9999") ? "기타" : $row['quantity'] . "부";
        $money_display = number_format((int)$row['money']) . "원";
        ?>
            <div class="table-row">
                <div class="table-cell"><?php echo  $row['no'] ?></div>
                <div class="table-cell"><?php echo  $style_title ?></div>
                <div class="table-cell"><?php echo  $section_title ?></div>
                <div class="table-cell"><?php echo  $tree_title ?></div>
                <div class="table-cell"><?php echo  $quantity_display ?></div>
                <div class="table-cell"><?php echo  $money_display ?></div>
                <div class="table-cell">
                    <div class="action-buttons">
                        <button type="button" class="btn btn-outline btn-sm" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250');">수정</button>
                        <button type="button" class="btn btn-danger btn-sm" onClick="if(confirm('삭제하시겠습니까?')){location.href='<?php echo  $PHP_SELF ?>?no=<?php echo  $row['no'] ?>&mode=delete';}">삭제</button>
                    </div>
                </div>
            </div>
<?php    }
} else { 
    echo "<div class='empty-state'>등록 자료없음</div>";
} ?>
        </div>

        <?php if ($recordsu > 0): ?>
        <div class="pagination">
            <?php
            $mlang_pagego = ($search === "yes")
                ? "search=$search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList"
                : "";

            if ($start_offset != 0) {
                $apoffset = $start_offset - $one_bbs;
                echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>← 이전</a>";
            }

            for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
                $newoffset = ($i - 1) * $listcut;
                if ($offset != $newoffset) {
                    echo "<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>$i</a>";
                } else {
                    echo "<span class='current-page'>$i</span>";
                }
                if ($i == $end_page) break;
            }

            if ($start_offset != $end_offset) {
                $nextoffset = $start_offset + $one_bbs;
                echo "<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>다음 →</a>";
            }
            ?>
            <span style="margin-left: var(--space-lg); color: var(--text-secondary); font-size: var(--text-xs);">
                총 <?php echo $end_page ?>페이지
            </span>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>

<?php
// DB 연결은 페이지 끝에서 자동으로 닫힘 (down.php 또는 스크립트 종료시)
?>
<?php include "../down.php"; ?>
