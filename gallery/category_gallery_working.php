<?php
/**
 * 작동하는 품목별 갤러리 - ThingCate 기반
 * checkboard.php와 동일한 방식으로 이미지 표시
 */

session_start();
require_once dirname(__DIR__) . "/db.php";

// 데이터베이스 연결 확인
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

// 파라미터 받기
$category = $_GET['category'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

// 카테고리 매핑 (실제 데이터베이스 Type 값 기반)
$category_mapping = [
    'all' => '전체',
    'leaflet' => '전단지',
    'sticker' => '스티커', 
    'namecard' => '명함',
    'coupon' => '금액쿠폰',
    'envelope' => 'envelope',
    'form' => 'NCR 양식지',
    'catalog' => 'cadarok',
    'poster' => 'LittlePrint'
];

$category_names = [
    'all' => '전체',
    'leaflet' => '전단지',
    'sticker' => '스티커', 
    'namecard' => '명함',
    'coupon' => '상품권',
    'envelope' => '봉투',
    'form' => '양식지',
    'catalog' => '카탈로그',
    'poster' => '포스터'
];

// 검색 조건 만들기 - 시안완료, 교정중, 작업완료 포함
$where_conditions = ["OrderStyle IN ('6', '7', '8')"]; // 시안완료, 교정중, 작업완료
$where_conditions[] = "ThingCate IS NOT NULL AND ThingCate != ''"; // 이미지가 있는 것만

if ($category !== 'all' && isset($category_mapping[$category])) {
    $db_type = $category_mapping[$category];
    $where_conditions[] = "Type = '" . mysqli_real_escape_string($connect, $db_type) . "'";
}

if ($search) {
    $search_escaped = mysqli_real_escape_string($connect, $search);
    $where_conditions[] = "(name LIKE '%{$search_escaped}%' OR Type LIKE '%{$search_escaped}%')";
}

$where_clause = implode(' AND ', $where_conditions);

// 전체 개수 조회
$count_sql = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE {$where_clause}";
$count_result = mysqli_query($connect, $count_sql);
$total_items = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_items / $items_per_page);

// 실제 파일이 있는 주문만 조회하는 최적화된 쿼리
// 오래된 주문부터 우선적으로 표시 (파일 존재 가능성이 높음)
$items_sql = "SELECT No, Type, ThingCate, name, date 
              FROM mlangorder_printauto 
              WHERE {$where_clause}
              ORDER BY 
                CASE 
                    WHEN No < 70000 THEN 1  -- 매우 오래된 주문 (높은 우선순위)
                    WHEN No < 80000 THEN 2  -- 오래된 주문
                    WHEN No < 82000 THEN 3  -- 중간 주문
                    ELSE 4                  -- 최신 주문 (낮은 우선순위)
                END,
                No DESC 
              LIMIT " . ($items_per_page * 3) . " OFFSET {$offset}"; // 더 많이 조회해서 파일 있는 것만 필터링

$items_result = mysqli_query($connect, $items_sql);
$gallery_items = [];
$found_items = 0;

if ($items_result) {
    while (($row = mysqli_fetch_assoc($items_result)) && $found_items < $items_per_page) {
        // 행 데이터가 유효한지 확인
        if (!$row || !is_array($row)) {
            continue;
        }
        
        $order_no = $row['No'] ?? '';
        $thing_cate = $row['ThingCate'] ?? '';
        
        // 필수 데이터가 있는지 확인
        if (empty($order_no) || empty($thing_cate)) {
            continue;
        }
        
        // 이미지 파일 경로 확인 (WindowSian.php와 동일한 방식)
        $image_path = "/mlangorder_printauto/upload/{$order_no}/{$thing_cate}";
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
        
        if (file_exists($file_path)) {
            $row['image_path'] = $image_path;
            $row['image_filename'] = $thing_cate;
            $gallery_items[] = $row;
            $found_items++;
        }
    }
}

// 카테고리별 통계
$stats_sql = "SELECT Type, COUNT(*) as count 
              FROM mlangorder_printauto 
              WHERE OrderStyle IN ('6', '7', '8') AND ThingCate IS NOT NULL AND ThingCate != ''
              GROUP BY Type 
              ORDER BY count DESC";
$stats_result = mysqli_query($connect, $stats_sql);
$category_stats = [];
if ($stats_result) {
    while ($row = mysqli_fetch_assoc($stats_result)) {
        $category_stats[$row['Type']] = $row['count'];
    }
}

// 고객명 마스킹 함수
function maskName($name) {
    if (empty($name)) return '';
    $length = mb_strlen($name, 'UTF-8');
    if ($length <= 1) return $name;
    return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $length - 1);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>완성된 작업물 갤러리 - 두손기획인쇄</title>
    <link rel="stylesheet" href="gallery-style.css">
    <style>
    .success-info {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        text-align: center;
    }
    .success-info strong {
        color: #0c5460;
    }
    </style>
</head>
<body>
    <div class="gallery-container">
        <!-- 헤더 -->
        <div class="gallery-header">
            <h1>🎨 완성된 작업물 갤러리</h1>
            <p>시안완료, 교정중, 작업완료 인쇄물을 확인하세요</p>
            
            <!-- 성공 메시지 -->
            <div class="success-info">
                <p><strong>✅ 갤러리 최적화 완료!</strong></p>
                <p>실제 파일이 있는 주문을 우선적으로 표시합니다 (오래된 주문 우선)</p>
                <p>전체 주문 <?= number_format($total_items) ?>건 | 실제 이미지 <?= count($gallery_items) ?>개 표시 중</p>
                <?php if (count($gallery_items) > 0): ?>
                    <p style="color: #16a34a;">🎉 실제 이미지가 있는 주문들을 성공적으로 찾았습니다!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 카테고리 필터 -->
        <div class="category-filter">
            <?php foreach ($category_names as $key => $name): ?>
                <?php 
                $count = 0;
                if ($key === 'all') {
                    $count = array_sum($category_stats);
                } else {
                    $db_type = $category_mapping[$key];
                    $count = $category_stats[$db_type] ?? 0;
                }
                $is_active = ($category === $key) ? 'active' : '';
                ?>
                <a href="?category=<?= $key ?>&search=<?= urlencode($search) ?>" 
                   class="category-btn <?= $is_active ?>">
                    <?= htmlspecialchars($name) ?>
                    <span class="count"><?= number_format($count) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- 검색 박스 -->
        <div class="search-box">
            <form method="GET">
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="고객명이나 품목으로 검색...">
                <button type="submit">🔍 검색</button>
            </form>
        </div>

        <!-- 갤러리 그리드 -->
        <div class="gallery-grid">
            <?php if (empty($gallery_items)): ?>
                <div class="no-items">
                    <p>😅 표시할 이미지가 없습니다</p>
                    <p>다른 카테고리를 선택하거나 검색어를 변경해보세요.</p>
                </div>
            <?php else: ?>
                <?php foreach ($gallery_items as $item): ?>
                    <?php if (is_array($item) && isset($item['No']) && isset($item['image_path'])): ?>
                    <div class="gallery-item">
                        <div class="item-image">
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                 alt="주문번호 <?= htmlspecialchars($item['No']) ?>"
                                 loading="lazy"
                                 onerror="this.parentElement.innerHTML='<div class=\'no-image\'>이미지 로드 실패</div>'">
                        </div>
                        
                        <div class="item-info">
                            <div class="item-type"><?= htmlspecialchars($item['Type'] ?? '') ?></div>
                            <div class="item-name">
                                고객: <?= htmlspecialchars(maskName($item['name'] ?? '')) ?>
                            </div>
                            <div class="item-date">
                                주문번호: <?= htmlspecialchars($item['No']) ?> | 
                                <?= !empty($item['date']) ? date('Y-m-d', strtotime($item['date'])) : '날짜없음' ?>
                            </div>
                            
                            <div class="item-actions">
                                <button class="btn-detail" onclick="openDetail(<?= htmlspecialchars($item['No']) ?>)">
                                    📄 상세보기
                                </button>
                                <button class="btn-popup" onclick="openPopup(<?= htmlspecialchars($item['No']) ?>)">
                                    🔍 크게보기
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?category=<?= $category ?>&page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" class="page-btn">◀ 이전</a>
                <?php endif; ?>
                
                <?php 
                $start_page = max(1, $page - 5);
                $end_page = min($total_pages, $page + 5);
                ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?category=<?= $category ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                       class="page-num <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?category=<?= $category ?>&page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" class="page-btn">다음 ▶</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- 푸터 -->
        <div class="gallery-footer">
            <p>두손기획인쇄 © 2024 | 시안완료+교정중+작업완료 <?= number_format($total_items) ?>개</p>
        </div>
    </div>

    <script src="gallery-script.js"></script>
    <script>
    function openDetail(orderNo) {
        window.open('/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' + orderNo, 
                    'detail_' + orderNo,
                    'width=1000,height=800,scrollbars=yes,resizable=yes');
    }

    function openPopup(orderNo) {
        window.open('category_gallery_popup.php?no=' + orderNo,
                    'popup_' + orderNo,
                    'width=1200,height=900,scrollbars=yes,resizable=yes');
    }
    </script>
</body>
</html>