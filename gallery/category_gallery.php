<?php
/**
 * 품목별 갤러리 시스템
 * 실제 교정 시안 업로드 이미지를 품목별로 분류하여 표시
 * Based on portfolio_migration_progress.md
 */

session_start();
require_once "../db.php";

// 데이터베이스 연결 확인
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

// 파라미터 받기
$category = $_GET['category'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20; // 페이지당 20개
$search = $_GET['search'] ?? '';

// 품목 카테고리 정의 (한글/영문 매핑)
$categories = [
    'all' => '전체',
    'leaflet' => '전단지',
    'sticker' => '스티커', 
    'namecard' => '명함',
    'coupon' => '상품권',
    'envelope' => '봉투',
    'form' => '양식지',
    'catalog' => '카다로그',
    'poster' => '소량인쇄(포스터)',
    'magnet' => '종이자석'
];

// 카테고리 매핑 (DB 저장값 -> 카테고리키)
$category_mapping = [
    '전단지' => 'leaflet',
    '전단지A5' => 'leaflet',
    'inserted' => 'leaflet',
    '스티커' => 'sticker',
    '명함' => 'namecard',
    '상품권' => 'coupon',
    '봉투' => 'envelope',
    'envelope' => 'envelope',
    '양식지' => 'form',
    'NcrFlambeau' => 'form',
    '카다로그' => 'catalog',
    'cadarok' => 'catalog',
    '포스터' => 'poster',
    'LittlePrint' => 'poster',
    '소량인쇄' => 'poster',
    '종이자석' => 'magnet',
    'msticker' => 'magnet'
];

// WHERE 절 생성
$where_conditions = ["OrderStyle = '8'", "ThingCate != ''", "ThingCate IS NOT NULL"];

if ($category !== 'all') {
    $type_conditions = [];
    foreach ($category_mapping as $db_value => $cat_key) {
        if ($cat_key === $category) {
            $type_conditions[] = "Type = '" . mysqli_real_escape_string($connect, $db_value) . "'";
            $type_conditions[] = "Item = '" . mysqli_real_escape_string($connect, $db_value) . "'";
        }
    }
    if (!empty($type_conditions)) {
        $where_conditions[] = "(" . implode(" OR ", $type_conditions) . ")";
    }
}

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($connect, $search);
    $where_conditions[] = "(name LIKE '%{$search_safe}%' OR OrderName LIKE '%{$search_safe}%')";
}

$where_clause = implode(" AND ", $where_conditions);

// 전체 개수 구하기
$count_sql = "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto WHERE {$where_clause}";
$count_result = mysqli_query($connect, $count_sql);
$total = 0;
if ($count_result && $row = mysqli_fetch_assoc($count_result)) {
    $total = intval($row['total']);
}

// 페이지네이션 계산
$total_pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;

// 실제 데이터 가져오기
$sql = "SELECT No, ThingCate, Type, Item, name, standard, OrderName, Date 
        FROM MlangOrder_PrintAuto 
        WHERE {$where_clause}
        ORDER BY No DESC
        LIMIT {$offset}, {$per_page}";

$result = mysqli_query($connect, $sql);
$items = [];

if ($result) {
    $upload_base = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload";
    
    while ($row = mysqli_fetch_assoc($result)) {
        // 이미지 파일 찾기
        $image_path = '';
        $order_no = $row['No'];
        $thing_cate = $row['ThingCate'];
        
        if (!empty($thing_cate)) {
            // 새로운 구조 확인 (upload/주문번호/파일명)
            $new_path = "{$upload_base}/{$order_no}/{$thing_cate}";
            if (file_exists($new_path)) {
                $image_path = "/MlangOrder_PrintAuto/upload/{$order_no}/{$thing_cate}";
            } else {
                // 이전 구조 확인 (upload/날짜코드/주문번호/파일명)
                $date_dirs = glob($upload_base . "/0*", GLOB_ONLYDIR);
                foreach ($date_dirs as $date_dir) {
                    $dir_name = basename($date_dir);
                    $old_path = "{$upload_base}/{$dir_name}/{$order_no}/{$thing_cate}";
                    if (file_exists($old_path)) {
                        $image_path = "/MlangOrder_PrintAuto/upload/{$dir_name}/{$order_no}/{$thing_cate}";
                        break;
                    }
                }
            }
        }
        
        // 고객명 마스킹
        $masked_name = '';
        if (!empty($row['name'])) {
            $name_length = mb_strlen($row['name'], 'UTF-8');
            if ($name_length > 1) {
                $masked_name = mb_substr($row['name'], 0, 1, 'UTF-8') . str_repeat('*', $name_length - 1);
            } else {
                $masked_name = $row['name'];
            }
        }
        
        $items[] = [
            'no' => $order_no,
            'image' => $image_path,
            'type' => $row['Type'] ?? $row['Item'] ?? '',
            'name' => $masked_name,
            'standard' => $row['standard'] ?? '',
            'order_name' => $row['OrderName'] ?? '',
            'date' => $row['Date'] ?? ''
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($categories[$category] ?? '전체') ?> 갤러리 - 두손기획인쇄</title>
    <link rel="stylesheet" href="gallery-style.css">
</head>
<body>
    <div class="gallery-container">
        <!-- 헤더 -->
        <div class="gallery-header">
            <h1>📸 품목별 갤러리</h1>
            <p>실제 완성된 작업물을 확인하실 수 있습니다</p>
        </div>

        <!-- 카테고리 필터 -->
        <div class="category-filter">
            <?php foreach ($categories as $key => $label): ?>
                <a href="?category=<?= $key ?>&page=1" 
                   class="category-btn <?= $category === $key ? 'active' : '' ?>">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($key === $category && $key !== 'all'): ?>
                        <span class="count">(<?= $total ?>)</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- 검색 -->
        <div class="search-box">
            <form method="get" action="">
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <input type="text" name="search" placeholder="고객명 또는 주문명 검색" 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit">🔍 검색</button>
            </form>
        </div>

        <!-- 갤러리 그리드 -->
        <div class="gallery-grid">
            <?php if (empty($items)): ?>
                <div class="no-items">
                    <p>😔 해당 조건에 맞는 작업물이 없습니다.</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="gallery-item" data-no="<?= $item['no'] ?>">
                        <div class="item-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" 
                                     alt="주문번호 <?= $item['no'] ?>"
                                     loading="lazy"
                                     onclick="openDetail(<?= $item['no'] ?>)">
                            <?php else: ?>
                                <div class="no-image">
                                    <span>이미지 준비중</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="item-info">
                            <div class="item-type"><?= htmlspecialchars($item['type']) ?></div>
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="item-date"><?= htmlspecialchars($item['date']) ?></div>
                            <div class="item-actions">
                                <button onclick="openDetail(<?= $item['no'] ?>)" class="btn-detail">
                                    상세보기
                                </button>
                                <button onclick="openPopup(<?= $item['no'] ?>)" class="btn-popup">
                                    크게보기
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?category=<?= $category ?>&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" 
                       class="page-btn">◀ 이전</a>
                <?php endif; ?>

                <?php
                $window = 5;
                $start = max(1, $page - floor($window / 2));
                $end = min($total_pages, $start + $window - 1);
                if ($end - $start + 1 < $window) {
                    $start = max(1, $end - $window + 1);
                }

                for ($p = $start; $p <= $end; $p++):
                ?>
                    <a href="?category=<?= $category ?>&page=<?= $p ?>&search=<?= urlencode($search) ?>" 
                       class="page-num <?= $p === $page ? 'active' : '' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?category=<?= $category ?>&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" 
                       class="page-btn">다음 ▶</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- 요약 정보 -->
        <div class="gallery-footer">
            <p>전체 <?= number_format($total) ?>개 중 
               <?= number_format($offset + 1) ?>-<?= number_format(min($offset + $per_page, $total)) ?>번째</p>
        </div>
    </div>

    <script>
    function openDetail(orderNo) {
        // WindowSian.php로 상세보기
        window.open('/MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=' + orderNo, 
                    'detail_' + orderNo,
                    'width=1000,height=800,scrollbars=yes');
    }

    function openPopup(orderNo) {
        // 팝업 갤러리로 크게보기
        window.open('category_gallery_popup.php?no=' + orderNo,
                    'popup_' + orderNo,
                    'width=1200,height=900,scrollbars=yes');
    }
    </script>
</body>
</html>