claude<?php
/**
 * 리뷰 관리 페이지
 * 경로: admin/mlangprintauto/review_manager.php
 *
 * 기능: 고객 리뷰 목록 조회, 승인/반려, 관리자 답변, 삭제
 * 패턴: proof_manager.php + order_manager.php 기반
 */

// === 1. Auth & DB Setup ===
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/review_schema.php';

requireAdminAuth();
ensureReviewTables($db);

// === 2. Constants ===
$productNames = [
    'namecard'        => '명함',
    'inserted'        => '전단지',
    'sticker_new'     => '스티커',
    'msticker'        => '자석스티커',
    'envelope'        => '봉투',
    'littleprint'     => '포스터',
    'merchandisebond' => '상품권',
    'cadarok'         => '카다록',
    'ncrflambeau'     => 'NCR양식지',
];

// === 3. Filter Parameters ===
$statusFilter  = $_GET['status']  ?? 'all';
$productFilter = $_GET['product'] ?? '';
$searchKeyword = trim($_GET['search'] ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 20;
$offset        = ($page - 1) * $perPage;

// === 4. Stats (unconditional counts for tab badges) ===
$statsRow = mysqli_fetch_assoc(mysqli_query($db,
    "SELECT COUNT(*) AS total,
            SUM(is_approved = 0) AS pending,
            SUM(is_approved = 1) AS approved,
            SUM(is_approved = 2) AS rejected
     FROM reviews"
));
$stats = [
    'total'    => (int)($statsRow['total']    ?? 0),
    'pending'  => (int)($statsRow['pending']  ?? 0),
    'approved' => (int)($statsRow['approved'] ?? 0),
    'rejected' => (int)($statsRow['rejected'] ?? 0),
];

// === 5. Build WHERE Clause ===
$conditions = [];
$types      = '';
$params     = [];

if ($statusFilter === 'pending') {
    $conditions[] = 'r.is_approved = 0';
} elseif ($statusFilter === 'approved') {
    $conditions[] = 'r.is_approved = 1';
} elseif ($statusFilter === 'rejected') {
    $conditions[] = 'r.is_approved = 2';
}

if ($productFilter !== '') {
    $conditions[] = 'r.product_type = ?';
    $types  .= 's';
    $params[] = $productFilter;
}

if ($searchKeyword !== '') {
    $conditions[] = '(r.user_name LIKE ? OR r.title LIKE ? OR r.content LIKE ?)';
    $types .= 'sss';
    $searchLike = '%' . $searchKeyword . '%';
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
}

$where = '';
if (!empty($conditions)) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}

// === 6. Total Count ===
$countSql  = "SELECT COUNT(*) AS cnt FROM reviews r {$where}";
$countStmt = mysqli_prepare($db, $countSql);
if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params); // 동적 bind — spread
}
mysqli_stmt_execute($countStmt);
$totalItems = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['cnt'];
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int)ceil($totalItems / $perPage));
if ($page > $totalPages) {
    $page   = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// === 7. Fetch Reviews ===
$listSql = "SELECT r.id, r.product_type, r.order_id, r.user_id, r.user_name,
                   r.rating, r.title, r.content, r.is_verified_purchase,
                   r.is_approved, r.admin_reply, r.admin_reply_at,
                   r.likes_count, r.created_at, r.updated_at
            FROM reviews r
            {$where}
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?";

$listStmt   = mysqli_prepare($db, $listSql);
$listTypes  = $types . 'ii';
$listParams = array_merge($params, [$perPage, $offset]);
mysqli_stmt_bind_param($listStmt, $listTypes, ...$listParams); // 동적 bind — spread
mysqli_stmt_execute($listStmt);
$listResult = mysqli_stmt_get_result($listStmt);

$reviews = [];
while ($row = mysqli_fetch_assoc($listResult)) {
    // 사진 조회
    $photoStmt = mysqli_prepare($db,
        "SELECT id, file_path, file_name, sort_order
         FROM review_photos WHERE review_id = ? ORDER BY sort_order ASC"
    );
    $rid = (int)$row['id'];
    mysqli_stmt_bind_param($photoStmt, "i", $rid); // ? 1개, 타입 1글자, 변수 1개
    mysqli_stmt_execute($photoStmt);
    $photoResult = mysqli_stmt_get_result($photoStmt);
    $photos = [];
    while ($photo = mysqli_fetch_assoc($photoResult)) {
        $photos[] = $photo;
    }
    mysqli_stmt_close($photoStmt);

    $row['photos'] = $photos;
    $reviews[] = $row;
}
mysqli_stmt_close($listStmt);

// === 8. URL Helper ===
function buildFilterUrl($overrides = []) {
    global $statusFilter, $productFilter, $searchKeyword;
    $p = [
        'status'  => isset($overrides['status'])  ? $overrides['status']  : $statusFilter,
        'product' => isset($overrides['product']) ? $overrides['product'] : $productFilter,
        'search'  => isset($overrides['search'])  ? $overrides['search']  : $searchKeyword,
        'page'    => isset($overrides['page'])    ? (int)$overrides['page'] : 1,
    ];
    // 기본값 제거하여 URL 깔끔하게
    if ($p['status'] === 'all') unset($p['status']);
    if ($p['product'] === '')   unset($p['product']);
    if ($p['search'] === '')    unset($p['search']);
    if ($p['page'] <= 1)        unset($p['page']);

    $qs = http_build_query($p);
    return 'review_manager.php' . ($qs ? '?' . $qs : '');
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>리뷰 관리 - 두손기획인쇄</title>
    <style>
        /* === Base (proof_manager.php 패턴) === */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }

        /* === Header === */
        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: #333; margin-bottom: 8px; }
        .header p { color: #666; font-size: 14px; }
        .back-link { color: #667eea; text-decoration: none; font-size: 14px; white-space: nowrap; }
        .back-link:hover { text-decoration: underline; }

        /* === Stat Cards (탭 역할) === */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            display: block;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            border-bottom: 3px solid transparent;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .stat-card.active { border-bottom-color: #667eea; }
        .stat-card h3 { color: #666; font-size: 13px; margin-bottom: 8px; }
        .stat-card .number { font-size: 28px; font-weight: bold; color: #667eea; }

        /* === Filters === */
        .filters {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filters label { font-size: 14px; color: #555; font-weight: 600; }
        .filters select,
        .filters input[type="text"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }
        .search-form { display: flex; gap: 5px; margin-left: auto; }
        .btn-search { background: #667eea; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-search:hover { background: #5568d3; }

        /* === Bulk Actions Bar === */
        .bulk-bar {
            display: none;
            background: #e8f0fe;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            align-items: center;
            gap: 10px;
        }
        .bulk-bar.active { display: flex; }
        .bulk-count { font-weight: 600; color: #1a73e8; margin-right: 10px; }

        /* === Review Table === */
        .review-table-wrap {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .review-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .review-table th {
            background: #f8f9fa;
            padding: 12px 14px;
            text-align: left;
            font-size: 13px;
            color: #555;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }
        .review-table th.col-check { width: 40px; text-align: center; }
        .review-table th.col-num   { width: 50px; }
        .review-table th.col-product { width: 80px; }
        .review-table th.col-rating  { width: 90px; }
        .review-table th.col-author  { width: 100px; }
        .review-table th.col-photo   { width: 70px; text-align: center; }
        .review-table th.col-status  { width: 90px; text-align: center; }
        .review-table th.col-actions { width: 80px; text-align: center; }
        .review-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            vertical-align: top;
        }
        .review-table td.center { text-align: center; }
        .review-table tbody tr:hover { background: #fafbff; }
        .review-table tbody tr:last-child td { border-bottom: none; }

        /* === Stars === */
        .stars { color: #ffc107; font-size: 15px; white-space: nowrap; letter-spacing: 1px; }
        .stars-empty { color: #ddd; }

        /* === Author Cell === */
        .author-name { font-weight: 600; color: #333; }
        .badge-verified {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 6px;
            background: #e3f2fd;
            color: #1565c0;
            border-radius: 4px;
            font-size: 11px;
        }

        /* === Content Preview === */
        .content-preview { max-width: 220px; }
        .content-title { font-weight: 600; color: #333; margin-bottom: 2px; font-size: 13px; }
        .content-text { color: #666; font-size: 13px; word-break: break-word; line-height: 1.4; }
        .content-date { color: #aaa; font-size: 11px; margin-top: 4px; }

        /* === Photo Count === */
        .photo-count {
            background: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
            white-space: nowrap;
        }
        .photo-count:hover { background: #f8f9fa; }
        .text-muted { color: #bbb; }

        /* === Status Badge === */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .status-pending  { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .reply-indicator { display: block; margin-top: 4px; font-size: 11px; color: #28a745; }

        /* === Action Buttons === */
        .action-buttons { display: flex; flex-direction: column; gap: 4px; }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-family: inherit;
            cursor: pointer;
            text-align: center;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-approve { background: #28a745; color: white; }
        .btn-approve:hover { background: #218838; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-reject:hover { background: #c82333; }
        .btn-reply { background: #667eea; color: white; }
        .btn-reply:hover { background: #5568d3; }
        .btn-delete { background: #6c757d; color: white; }
        .btn-delete:hover { background: #545b62; }
        .btn-cancel { background: #e9ecef; color: #333; }
        .btn-cancel:hover { background: #dee2e6; }

        /* === Pagination (proof_manager 패턴) === */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
            padding-bottom: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }
        .pagination a:hover { background: #f0f0f0; }
        .pagination .current { background: #667eea; color: white; border-color: #667eea; }

        /* === No Results === */
        .no-results { text-align: center; padding: 60px 20px; color: #999; }
        .no-results p { font-size: 16px; margin-bottom: 5px; }
        .no-results small { font-size: 13px; }

        /* === Modals === */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .modal-box h2 { color: #333; margin-bottom: 15px; font-size: 18px; }
        .modal-box textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            line-height: 1.5;
        }
        .modal-box textarea:focus { outline: none; border-color: #667eea; }
        .modal-actions { display: flex; gap: 10px; margin-top: 15px; justify-content: flex-end; }

        /* Photo Gallery */
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        .photo-gallery img {
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .photo-gallery img:hover { transform: scale(1.03); }
        .photo-empty { text-align: center; color: #999; padding: 30px; }

        /* === Responsive === */
        @media (max-width: 768px) {
            body { padding: 10px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .filters { flex-direction: column; align-items: stretch; }
            .search-form { margin-left: 0; }
            .stat-card .number { font-size: 22px; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- ═══ Header ═══ -->
    <div class="header">
        <div class="header-top">
            <h1>⭐ 리뷰 관리</h1>
            <a href="index.php" class="back-link">← 대시보드</a>
        </div>
        <p>고객 리뷰를 검토하고 승인 · 반려 · 답변을 관리합니다</p>
    </div>

    <!-- ═══ Stat Cards (탭 역할) ═══ -->
    <div class="stats">
        <a href="<?php echo buildFilterUrl(['status' => 'all', 'search' => '', 'page' => 1]); ?>"
           class="stat-card <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
            <h3>전체</h3>
            <div class="number"><?php echo number_format($stats['total']); ?></div>
        </a>
        <a href="<?php echo buildFilterUrl(['status' => 'pending', 'search' => '', 'page' => 1]); ?>"
           class="stat-card <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
            <h3>⏳ 대기</h3>
            <div class="number" style="color: #e67e22;"><?php echo number_format($stats['pending']); ?></div>
        </a>
        <a href="<?php echo buildFilterUrl(['status' => 'approved', 'search' => '', 'page' => 1]); ?>"
           class="stat-card <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>">
            <h3>✅ 승인</h3>
            <div class="number" style="color: #28a745;"><?php echo number_format($stats['approved']); ?></div>
        </a>
        <a href="<?php echo buildFilterUrl(['status' => 'rejected', 'search' => '', 'page' => 1]); ?>"
           class="stat-card <?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>">
            <h3>❌ 반려</h3>
            <div class="number" style="color: #dc3545;"><?php echo number_format($stats['rejected']); ?></div>
        </a>
    </div>

    <!-- ═══ Filters ═══ -->
    <div class="filters">
        <label>제품:</label>
        <select id="productFilter">
            <option value="">전체</option>
            <?php foreach ($productNames as $code => $name): ?>
                <option value="<?php echo htmlspecialchars($code); ?>"
                    <?php echo $productFilter === $code ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <form class="search-form" method="GET" action="review_manager.php">
            <?php if ($statusFilter !== 'all'): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <?php endif; ?>
            <?php if ($productFilter !== ''): ?>
                <input type="hidden" name="product" value="<?php echo htmlspecialchars($productFilter); ?>">
            <?php endif; ?>
            <input type="text" name="search" placeholder="이름, 제목, 내용 검색…"
                   value="<?php echo htmlspecialchars($searchKeyword); ?>" style="min-width:200px;">
            <button type="submit" class="btn-search">검색</button>
        </form>
    </div>

    <!-- ═══ Bulk Actions ═══ -->
    <div id="bulkBar" class="bulk-bar">
        <span id="bulkCount" class="bulk-count">0개 선택됨</span>
        <button class="btn btn-approve" data-action="bulk-approve">선택 승인</button>
        <button class="btn btn-reject" data-action="bulk-reject">선택 반려</button>
    </div>

    <!-- ═══ Review Table ═══ -->
    <div class="review-table-wrap">
        <?php if (empty($reviews)): ?>
            <div class="no-results">
                <p>📭 리뷰가 없습니다</p>
                <small>조건을 변경해보세요</small>
            </div>
        <?php else: ?>
            <table class="review-table">
                <thead>
                    <tr>
                        <th class="col-check"><input type="checkbox" id="selectAll"></th>
                        <th class="col-num">#</th>
                        <th class="col-product">제품</th>
                        <th class="col-rating">별점</th>
                        <th class="col-author">작성자</th>
                        <th>내용</th>
                        <th class="col-photo">사진</th>
                        <th class="col-status">상태</th>
                        <th class="col-actions">관리</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reviews as $i => $rv):
                    $rvId       = (int)$rv['id'];
                    $isApproved = (int)$rv['is_approved'];
                    $rating     = max(1, min(5, (int)$rv['rating']));
                    $rowNum     = $totalItems - $offset - $i;

                    // 상태 매핑
                    $statusMap = [0 => ['pending','⏳ 대기'], 1 => ['approved','✅ 승인'], 2 => ['rejected','❌ 반려']];
                    $statusClass = $statusMap[$isApproved][0] ?? 'pending';
                    $statusLabel = $statusMap[$isApproved][1] ?? '대기';

                    // 사진 JSON (photo modal 용)
                    $photosJson = htmlspecialchars(json_encode($rv['photos'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                ?>
                <tr data-review-id="<?php echo $rvId; ?>"
                    data-admin-reply="<?php echo htmlspecialchars($rv['admin_reply'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Checkbox -->
                    <td class="center">
                        <input type="checkbox" class="review-checkbox" value="<?php echo $rvId; ?>">
                    </td>

                    <!-- # -->
                    <td><?php echo $rowNum; ?></td>

                    <!-- 제품 -->
                    <td><?php echo htmlspecialchars($productNames[$rv['product_type']] ?? $rv['product_type']); ?></td>

                    <!-- 별점 -->
                    <td>
                        <span class="stars"><?php echo str_repeat('★', $rating); ?></span><?php if ($rating < 5): ?><span class="stars stars-empty"><?php echo str_repeat('☆', 5 - $rating); ?></span><?php endif; ?>
                    </td>

                    <!-- 작성자 -->
                    <td>
                        <span class="author-name"><?php echo htmlspecialchars($rv['user_name']); ?></span>
                        <?php if ((int)$rv['is_verified_purchase']): ?>
                            <br><span class="badge-verified">🏷 구매인증</span>
                        <?php endif; ?>
                    </td>

                    <!-- 내용 -->
                    <td class="content-preview">
                        <?php if (!empty($rv['title'])): ?>
                            <div class="content-title"><?php echo htmlspecialchars(mb_strimwidth($rv['title'], 0, 40, '…')); ?></div>
                        <?php endif; ?>
                        <div class="content-text"><?php echo htmlspecialchars(mb_strimwidth($rv['content'], 0, 80, '…')); ?></div>
                        <div class="content-date"><?php echo date('Y-m-d', strtotime($rv['created_at'])); ?></div>
                    </td>

                    <!-- 사진 -->
                    <td class="center">
                        <?php if (count($rv['photos']) > 0): ?>
                            <button class="photo-count"
                                    data-action="view-photos"
                                    data-photos='<?php echo $photosJson; ?>'>
                                📸 <?php echo count($rv['photos']); ?>장
                            </button>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>

                    <!-- 상태 -->
                    <td class="center">
                        <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                        <?php if (!empty($rv['admin_reply'])): ?>
                            <span class="reply-indicator">💬 답변완료</span>
                        <?php endif; ?>
                    </td>

                    <!-- 관리 -->
                    <td>
                        <div class="action-buttons">
                            <?php if ($isApproved !== 1): ?>
                                <button class="btn btn-approve" data-action="approve" data-review-id="<?php echo $rvId; ?>">승인</button>
                            <?php endif; ?>
                            <?php if ($isApproved !== 2): ?>
                                <button class="btn btn-reject" data-action="reject" data-review-id="<?php echo $rvId; ?>">반려</button>
                            <?php endif; ?>
                            <button class="btn btn-reply" data-action="open-reply" data-review-id="<?php echo $rvId; ?>">답변</button>
                            <button class="btn btn-delete" data-action="delete" data-review-id="<?php echo $rvId; ?>">삭제</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ═══ Pagination ═══ -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo buildFilterUrl(['page' => $page - 1]); ?>">← 이전</a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 5); $i <= min($totalPages, $page + 5); $i++): ?>
            <?php if ($i === $page): ?>
                <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="<?php echo buildFilterUrl(['page' => $i]); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?php echo buildFilterUrl(['page' => $page + 1]); ?>">다음 →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- /.container -->

<!-- ═══ Reply Modal ═══ -->
<div id="replyModal" class="modal-overlay">
    <div class="modal-box">
        <h2>💬 리뷰 답변</h2>
        <textarea id="replyText" placeholder="답변 내용을 입력하세요…"></textarea>
        <div class="modal-actions">
            <button class="btn btn-cancel" data-action="close-modal">취소</button>
            <button class="btn btn-reply" data-action="submit-reply">답변 등록</button>
        </div>
    </div>
</div>

<!-- ═══ Photo Modal ═══ -->
<div id="photoModal" class="modal-overlay">
    <div class="modal-box">
        <h2>📸 첨부 사진</h2>
        <div id="photoGallery" class="photo-gallery"></div>
        <div class="modal-actions">
            <button class="btn btn-cancel" data-action="close-modal">닫기</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var API_URL = 'api/reviews.php';
    var currentReplyId = null;

    /* ─── API 호출 헬퍼 ─── */
    function apiCall(action, data) {
        var body = 'action=' + encodeURIComponent(action);
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                body += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
            }
        }
        return fetch(API_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body
        }).then(function(r) { return r.json(); });
    }

    /* ─── 모달 관리 ─── */
    function closeModals() {
        document.querySelectorAll('.modal-overlay').forEach(function(m) {
            m.classList.remove('active');
        });
        currentReplyId = null;
    }

    function openReplyModal(reviewId) {
        currentReplyId = reviewId;
        var row = document.querySelector('tr[data-review-id="' + reviewId + '"]');
        var existing = row ? (row.dataset.adminReply || '') : '';
        var textarea = document.getElementById('replyText');
        textarea.value = existing;
        document.getElementById('replyModal').classList.add('active');
        textarea.focus();
    }

    function openPhotoModal(photosJson) {
        var photos;
        try { photos = JSON.parse(photosJson); } catch(e) { photos = []; }
        var gallery = document.getElementById('photoGallery');
        gallery.innerHTML = '';

        if (photos.length === 0) {
            gallery.innerHTML = '<div class="photo-empty">사진이 없습니다.</div>';
        } else {
            photos.forEach(function(photo) {
                var img = document.createElement('img');
                img.src = '/' + (photo.file_path || '').replace(/^\//, '');
                img.alt = photo.file_name || 'review photo';
                img.title = photo.file_name || '';
                gallery.appendChild(img);
            });
        }

        document.getElementById('photoModal').classList.add('active');
    }

    /* ─── 단건 액션 ─── */
    function handleApprove(id) {
        if (!confirm('이 리뷰를 승인하시겠습니까?')) return;
        apiCall('approve', {review_id: id}).then(function(data) {
            if (data.success) location.reload();
            else alert('오류: ' + (data.error || '승인 실패'));
        }).catch(function(e) { alert('오류: ' + e.message); });
    }

    function handleReject(id) {
        if (!confirm('이 리뷰를 반려하시겠습니까?')) return;
        apiCall('reject', {review_id: id}).then(function(data) {
            if (data.success) location.reload();
            else alert('오류: ' + (data.error || '반려 실패'));
        }).catch(function(e) { alert('오류: ' + e.message); });
    }

    function handleDelete(id) {
        if (!confirm('이 리뷰를 삭제하시겠습니까?\n삭제하면 복구할 수 없습니다.')) return;
        apiCall('delete', {review_id: id}).then(function(data) {
            if (data.success) location.reload();
            else alert('오류: ' + (data.error || '삭제 실패'));
        }).catch(function(e) { alert('오류: ' + e.message); });
    }

    function submitReply() {
        var text = document.getElementById('replyText').value.trim();
        if (!text) { alert('답변 내용을 입력해주세요.'); return; }

        apiCall('reply', {review_id: currentReplyId, reply_text: text}).then(function(data) {
            if (data.success) {
                alert('답변이 등록되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + (data.error || '답변 등록 실패'));
            }
        }).catch(function(e) { alert('오류: ' + e.message); });
    }

    /* ─── Bulk Actions ─── */
    function updateBulkBar() {
        var checked = document.querySelectorAll('.review-checkbox:checked');
        var bulkBar = document.getElementById('bulkBar');
        var countEl = document.getElementById('bulkCount');

        if (checked.length > 0) {
            bulkBar.classList.add('active');
            countEl.textContent = checked.length + '개 선택됨';
        } else {
            bulkBar.classList.remove('active');
        }

        var all = document.querySelectorAll('.review-checkbox');
        var selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.checked = all.length > 0 && checked.length === all.length;
        }
    }

    function bulkAction(action) {
        var checked = document.querySelectorAll('.review-checkbox:checked');
        if (checked.length === 0) return;

        var msg = action === 'approve'
            ? '선택한 ' + checked.length + '개 리뷰를 승인하시겠습니까?'
            : '선택한 ' + checked.length + '개 리뷰를 반려하시겠습니까?';
        if (!confirm(msg)) return;

        var ids = Array.from(checked).map(function(cb) { return cb.value; });
        var completed = 0;

        function processNext(index) {
            if (index >= ids.length) {
                alert(completed + '/' + ids.length + '개 처리 완료');
                location.reload();
                return;
            }
            apiCall(action, {review_id: ids[index]}).then(function(data) {
                if (data.success) completed++;
                processNext(index + 1);
            }).catch(function() {
                processNext(index + 1);
            });
        }
        processNext(0);
    }

    /* ─── 이벤트 위임: click ─── */
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) return;

        var action   = btn.dataset.action;
        var reviewId = btn.dataset.reviewId;

        switch (action) {
            case 'approve':      handleApprove(reviewId);     break;
            case 'reject':       handleReject(reviewId);      break;
            case 'delete':       handleDelete(reviewId);      break;
            case 'open-reply':   openReplyModal(reviewId);    break;
            case 'submit-reply': submitReply();               break;
            case 'view-photos':  openPhotoModal(btn.dataset.photos); break;
            case 'close-modal':  closeModals();               break;
            case 'bulk-approve': bulkAction('approve');       break;
            case 'bulk-reject':  bulkAction('reject');        break;
        }
    });

    /* ─── 이벤트 위임: change ─── */
    document.addEventListener('change', function(e) {
        if (e.target.id === 'selectAll') {
            var checkboxes = document.querySelectorAll('.review-checkbox');
            checkboxes.forEach(function(cb) { cb.checked = e.target.checked; });
            updateBulkBar();
        } else if (e.target.classList.contains('review-checkbox')) {
            updateBulkBar();
        } else if (e.target.id === 'productFilter') {
            var url = new URL(window.location.href);
            if (e.target.value) {
                url.searchParams.set('product', e.target.value);
            } else {
                url.searchParams.delete('product');
            }
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }
    });

    /* ─── 모달 오버레이 클릭 닫기 ─── */
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModals();
        }
    });

    /* ─── ESC 키 모달 닫기 ─── */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModals();
    });
});
</script>
</body>
</html>
<?php
// DB 연결 종료 — 모든 렌더링 완료 후 (PHP 8.2 호환)
if (isset($db) && $db) {
    mysqli_close($db);
}
?>
