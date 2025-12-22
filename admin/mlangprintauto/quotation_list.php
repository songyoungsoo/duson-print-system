<?php
/**
 * 견적서 관리 - 목록 페이지
 * 두손기획인쇄 관리자용
 */
session_start();
require_once __DIR__ . '/../../db.php';
$conn = $db;

mysqli_set_charset($db, 'utf8mb4');

// 페이지네이션
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 검색 및 필터
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// WHERE 조건 구성
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(quotation_no LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 총 개수 조회
$count_sql = "SELECT COUNT(*) as total FROM quotations $where_clause";
if (!empty($params)) {
    $stmt = mysqli_prepare($db, $count_sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $count_result = mysqli_stmt_get_result($stmt);
} else {
    $count_result = mysqli_query($db, $count_sql);
}
$total_count = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_count / $per_page);

// 목록 조회
$list_sql = "SELECT * FROM quotations $where_clause ORDER BY created_at DESC LIMIT ?, ?";
$list_params = array_merge($params, [$offset, $per_page]);
$list_types = $types . 'ii';

$stmt = mysqli_prepare($db, $list_sql);
if (!empty($list_params)) {
    mysqli_stmt_bind_param($stmt, $list_types, ...$list_params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quotations[] = $row;
}

// 상태 라벨
$status_labels = [
    'draft' => ['label' => '작성중', 'color' => '#6c757d'],
    'sent' => ['label' => '발송완료', 'color' => '#0d6efd'],
    'accepted' => ['label' => '승인', 'color' => '#198754'],
    'rejected' => ['label' => '거절', 'color' => '#dc3545'],
    'expired' => ['label' => '만료', 'color' => '#6c757d']
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 관리 - 두손기획인쇄</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', sans-serif; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; color: #333; margin-bottom: 10px; }
        .header p { color: #666; font-size: 14px; }
        .toolbar { background: #fff; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .search-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .search-form input, .search-form select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .search-form input[type="text"] { width: 250px; }
        .search-form button { padding: 8px 16px; background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .search-form button:hover { background: #0b5ed7; }
        .stats { display: flex; gap: 15px; }
        .stat-item { padding: 8px 15px; background: #f8f9fa; border-radius: 4px; font-size: 13px; }
        .stat-item strong { color: #0d6efd; }
        .table-wrapper { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; font-size: 13px; }
        td { font-size: 14px; color: #555; }
        tr:hover { background: #f8f9fa; }
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; color: #fff; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #0d6efd; color: #fff; }
        .btn-primary:hover { background: #0b5ed7; }
        .btn-outline { background: #fff; border: 1px solid #ddd; color: #333; }
        .btn-outline:hover { background: #f8f9fa; }
        .pagination { display: flex; justify-content: center; gap: 5px; padding: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; font-size: 14px; }
        .pagination a:hover { background: #f8f9fa; }
        .pagination .active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state h3 { margin-bottom: 10px; color: #333; }
        .price { text-align: right; font-weight: 500; }
        .date { color: #888; font-size: 13px; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #0d6efd; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">← 관리자 메뉴로 돌아가기</a>

        <div class="header">
            <h1>📋 견적서 관리</h1>
            <p>고객에게 발송한 견적서를 관리합니다.</p>
        </div>

        <div class="toolbar">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="견적번호, 고객명, 이메일 검색" value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">전체 상태</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>작성중</option>
                    <option value="sent" <?php echo $status_filter == 'sent' ? 'selected' : ''; ?>>발송완료</option>
                    <option value="accepted" <?php echo $status_filter == 'accepted' ? 'selected' : ''; ?>>승인</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>거절</option>
                    <option value="expired" <?php echo $status_filter == 'expired' ? 'selected' : ''; ?>>만료</option>
                </select>
                <button type="submit">검색</button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="quotation_list.php" class="btn btn-outline">초기화</a>
                <?php endif; ?>
            </form>
            <div class="stats">
                <div class="stat-item">전체 <strong><?php echo number_format($total_count); ?></strong>건</div>
            </div>
        </div>

        <div class="table-wrapper">
            <?php if (empty($quotations)): ?>
                <div class="empty-state">
                    <h3>견적서가 없습니다</h3>
                    <p>검색 조건을 변경하거나 새로운 견적서를 생성해주세요.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>견적번호</th>
                            <th>고객명</th>
                            <th>이메일</th>
                            <th>연락처</th>
                            <th class="price">금액 (VAT포함)</th>
                            <th>상태</th>
                            <th>유효기간</th>
                            <th>작성일</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotations as $q): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($q['quotation_no']); ?></strong></td>
                                <td><?php echo htmlspecialchars($q['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($q['customer_email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($q['customer_phone'] ?? '-'); ?></td>
                                <td class="price"><?php echo number_format($q['total_price']); ?>원</td>
                                <td>
                                    <?php
                                    $status = $q['status'];
                                    $label = $status_labels[$status]['label'] ?? $status;
                                    $color = $status_labels[$status]['color'] ?? '#6c757d';
                                    ?>
                                    <span class="status-badge" style="background: <?php echo $color; ?>"><?php echo $label; ?></span>
                                </td>
                                <td class="date"><?php echo $q['expires_at'] ? date('Y-m-d', strtotime($q['expires_at'])) : '-'; ?></td>
                                <td class="date"><?php echo date('Y-m-d H:i', strtotime($q['created_at'])); ?></td>
                                <td>
                                    <a href="quotation_detail.php?id=<?php echo $q['id']; ?>" class="btn btn-primary">상세</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">이전</a>
                        <?php endif; ?>
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">다음</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
