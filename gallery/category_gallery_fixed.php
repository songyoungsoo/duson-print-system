<?php
/**
 * 수정된 품목별 갤러리 - 실제 파일 시스템 기반
 * 실제 업로드 디렉토리의 이미지를 스캔하여 표시
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

// 카테고리 매핑
$category_mapping = [
    'all' => '전체',
    'leaflet' => '전단지',
    'sticker' => '스티커', 
    'namecard' => '명함',
    'coupon' => '상품권',
    'envelope' => '봉투',
    'form' => '양식지',
    'catalog' => '카다록',
    'poster' => '포스터',
    'magnet' => '종이자석',
    'etc' => '기타'
];

// 검색 조건 만들기
$where_conditions = ["OrderStyle IN ('2', '3', '7', '8')"];

if ($category !== 'all') {
    $category_name = $category_mapping[$category] ?? '';
    if ($category_name) {
        $where_conditions[] = "Type = '" . mysqli_real_escape_string($connect, $category_name) . "'";
    }
}

if ($search) {
    $search_escaped = mysqli_real_escape_string($connect, $search);
    $where_conditions[] = "(name LIKE '%{$search_escaped}%' OR Type LIKE '%{$search_escaped}%')";
}

$where_clause = implode(' AND ', $where_conditions);

// 전체 개수 조회
$count_sql = "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto WHERE {$where_clause}";
$count_result = mysqli_query($connect, $count_sql);
$total_items = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_items / $items_per_page);

// 갤러리 아이템 조회 - 실제 파일이 있는 것만
$items_sql = "SELECT No, Type, ThingCate, name, date 
              FROM MlangOrder_PrintAuto 
              WHERE {$where_clause}
              ORDER BY No DESC 
              LIMIT {$items_per_page} OFFSET {$offset}";

$items_result = mysqli_query($connect, $items_sql);
$gallery_items = [];

if ($items_result) {
    $upload_base = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload";
    
    while ($row = mysqli_fetch_assoc($items_result)) {
        $order_no = $row['No'];
        
        // 실제 이미지 파일 찾기
        $image_info = findActualImage($upload_base, $order_no);
        
        if ($image_info) {
            $row['image_path'] = $image_info['web_path'];
            $row['image_filename'] = $image_info['filename'];
            $gallery_items[] = $row;
        }
    }
}

/**
 * 실제 이미지 파일 찾기 함수 - 개선된 버전
 */
function findActualImage($upload_base, $order_no) {
    // 이미지 파일 확장자
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // 새 구조 확인: /upload/{order_no}/
    $order_dir = $upload_base . "/" . $order_no;
    if (is_dir($order_dir)) {
        $files = glob($order_dir . "/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $image_extensions)) {
                    $filename = basename($file);
                    return [
                        'web_path' => "/MlangOrder_PrintAuto/upload/{$order_no}/{$filename}",
                        'filename' => $filename
                    ];
                }
            }
        }
    }
    
    // 구 구조 확인: /upload/{date_dir}/{order_no}/
    $date_dirs = glob($upload_base . "/0*", GLOB_ONLYDIR);
    foreach ($date_dirs as $date_dir) {
        $dir_name = basename($date_dir);
        $old_order_dir = $upload_base . "/" . $dir_name . "/" . $order_no;
        
        if (is_dir($old_order_dir)) {
            $files = glob($old_order_dir . "/*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($extension, $image_extensions)) {
                        $filename = basename($file);
                        return [
                            'web_path' => "/MlangOrder_PrintAuto/upload/{$dir_name}/{$order_no}/{$filename}",
                            'filename' => $filename
                        ];
                    }
                }
            }
        }
    }
    
    return null;
}

// 카테고리별 통계
$stats_sql = "SELECT Type, COUNT(*) as count 
              FROM MlangOrder_PrintAuto 
              WHERE OrderStyle IN ('2', '3', '7', '8')
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
    <title>실제 작업물 갤러리 - 두손기획인쇄</title>
    <link rel="stylesheet" href="gallery-style.css">
    <style>
    .debug-info {
        background: #f8f9fa;
        padding: 15px;
        margin: 20px 0;
        border-radius: 8px;
        border-left: 4px solid #667eea;
        font-size: 14px;
    }
    .debug-info strong {
        color: #667eea;
    }
    </style>
</head>
<body>
    <div class="gallery-container">
        <!-- 헤더 -->
        <div class="gallery-header">
            <h1>🎨 실제 작업물 갤러리</h1>
            <p>완성된 인쇄물 시안을 확인하세요</p>
            
            <!-- 디버그 정보 -->
            <div class="debug-info">
                <strong>시스템 상태:</strong> 
                전체 <?= number_format($total_items) ?>건 | 
                현재 페이지 <?= $page ?>/<?= $total_pages ?> |
                실제 이미지 <?= count($gallery_items) ?>개 표시 중
            </div>
        </div>

        <!-- 카테고리 필터 -->
        <div class="category-filter">
            <?php foreach ($category_mapping as $key => $name): ?>
                <?php 
                $count = 0;
                if ($key === 'all') {
                    $count = array_sum($category_stats);
                } else {
                    $count = $category_stats[$name] ?? 0;
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
                    <div class="gallery-item">
                        <div class="item-image">
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                 alt="주문번호 <?= $item['No'] ?>"
                                 loading="lazy"
                                 onerror="this.parentElement.innerHTML='<div class=\'no-image\'>이미지 로드 실패</div>'">
                        </div>
                        
                        <div class="item-info">
                            <div class="item-type"><?= htmlspecialchars($item['Type']) ?></div>
                            <div class="item-name">
                                고객: <?= htmlspecialchars(maskName($item['name'])) ?>
                            </div>
                            <div class="item-date">
                                주문번호: <?= $item['No'] ?> | 
                                <?= date('Y-m-d', strtotime($item['date'])) ?>
                            </div>
                            
                            <div class="item-actions">
                                <button class="btn-detail" onclick="openDetail(<?= $item['No'] ?>)">
                                    📄 상세보기
                                </button>
                                <button class="btn-popup" onclick="openPopup(<?= $item['No'] ?>)">
                                    🔍 크게보기
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
            <p>두손기획인쇄 © 2024 | 총 <?= number_format($total_items) ?>개 작업물</p>
        </div>
    </div>

    <script src="gallery-script.js"></script>
    <script>
    function openDetail(orderNo) {
        window.open('/MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=' + orderNo, 
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