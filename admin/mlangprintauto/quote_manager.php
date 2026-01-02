<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Admin authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin_login.php");
    exit;
}

// 페이지네이션
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// 필터
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// WHERE 절 구성
$where = [];
$params = [];
$types = "";

if (!empty($status_filter)) {
    $where[] = "q.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $where[] = "(q.quote_no LIKE ? OR q.customer_name LIKE ? OR q.customer_company LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// 견적 목록 조회
$query = "
    SELECT 
        q.id,
        q.quote_no,
        q.customer_name,
        q.customer_company,
        q.customer_email,
        q.customer_phone,
        q.supply_total,
        q.vat_total,
        q.grand_total,
        q.status,
        q.created_at,
        q.valid_until,
        q.converted_order_no,
        COUNT(qi.id) as item_count
    FROM quotes q
    LEFT JOIN quote_items qi ON q.id = qi.quote_id
    {$where_clause}
    GROUP BY q.id
    ORDER BY q.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($db, $query);
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$quotes = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// 전체 개수
$count_query = "SELECT COUNT(*) as total FROM quotes q {$where_clause}";
$count_stmt = mysqli_prepare($db, $count_query);
$count_types = substr($types, 0, -2);
$count_params = array_slice($params, 0, -2);
if (!empty($count_params)) {
    mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));
mysqli_stmt_close($count_stmt);

$total_items = $count_result['total'];
$total_pages = ceil($total_items / $limit);

// 통계
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
    FROM quotes
";

$stats_result = mysqli_query($db, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>견적 관리 - 두손기획인쇄 관리자</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', sans-serif; background: #f5f5f5; padding: 20px; }
        
        .container { max-width: 1400px; margin: 0 auto; }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 { color: #333; margin-bottom: 10px; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #667eea; }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-row select, .filter-row input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-row button {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .filter-row button:hover { background: #5568d3; }
        
        .quote-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .quote-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 20px;
        }
        
        .quote-item:last-child { border-bottom: none; }
        
        .quote-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .quote-info h3 { color: #333; margin-bottom: 5px; }
        .quote-info p { color: #666; font-size: 14px; }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-draft { background: #e9ecef; color: #495057; }
        .status-sent { background: #d1ecf1; color: #0c5460; }
        .status-viewed { background: #cfe2ff; color: #084298; }
        .status-accepted { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-expired { background: #f8f9fa; color: #6c757d; }
        .status-converted { background: #d4edda; color: #155724; font-weight: bold; }
        
        .quote-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item { color: #666; font-size: 14px; }
        .detail-item strong { color: #333; }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover { background: #5568d3; }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover { background: #f0f0f0; }
        .pagination .current { background: #667eea; color: white; border-color: #667eea; }
        
        .no-quotes {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 견적 관리</h1>
            <p>고객 견적 요청 및 관리</p>
        </div>
        
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>전체 견적</h3>
                <div class="number"><?php echo number_format($stats['total'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>임시 저장</h3>
                <div class="number"><?php echo number_format($stats['draft'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>발송됨</h3>
                <div class="number"><?php echo number_format($stats['sent'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>승인됨</h3>
                <div class="number"><?php echo number_format($stats['accepted'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>주문 전환</h3>
                <div class="number"><?php echo number_format($stats['converted'] ?? 0); ?></div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="filter-row">
                <label>상태:</label>
                <select name="status">
                    <option value="">전체</option>
                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>임시 저장</option>
                    <option value="sent" <?php echo $status_filter === 'sent' ? 'selected' : ''; ?>>발송됨</option>
                    <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>승인됨</option>
                    <option value="converted" <?php echo $status_filter === 'converted' ? 'selected' : ''; ?>>주문 전환</option>
                    <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>만료됨</option>
                </select>
                
                <label>검색:</label>
                <input type="text" name="search" placeholder="견적번호, 고객명, 회사명" value="<?php echo htmlspecialchars($search); ?>">
                
                <button type="submit">검색</button>
                <a href="?" style="margin-left: 10px; color: #667eea; text-decoration: none;">초기화</a>
                <a href="index.php" style="margin-left: auto; color: #667eea; text-decoration: none;">← 대시보드로</a>
            </form>
        </div>
        
        <!-- Quote List -->
        <div class="quote-list">
            <?php if (mysqli_num_rows($quotes) === 0): ?>
                <div class="no-quotes">
                    <p>견적이 없습니다.</p>
                </div>
            <?php else: ?>
                <?php while ($quote = mysqli_fetch_assoc($quotes)): ?>
                    <div class="quote-item">
                        <div class="quote-header">
                            <div class="quote-info">
                                <h3>견적번호: <?php echo htmlspecialchars($quote['quote_no']); ?></h3>
                                <p>
                                    <?php if ($quote['customer_company']): ?>
                                        <?php echo htmlspecialchars($quote['customer_company']); ?> | 
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($quote['customer_name']); ?> | 
                                    <?php echo htmlspecialchars($quote['customer_phone'] ?? '-'); ?> | 
                                    <?php echo $quote['item_count']; ?>개 품목
                                </p>
                            </div>
                            <span class="status-badge status-<?php echo $quote['status']; ?>">
                                <?php
                                $status_text = [
                                    'draft' => '임시 저장',
                                    'sent' => '발송됨',
                                    'viewed' => '열람됨',
                                    'accepted' => '승인됨',
                                    'rejected' => '거절됨',
                                    'expired' => '만료됨',
                                    'converted' => '주문 완료'
                                ];
                                echo $status_text[$quote['status']] ?? $quote['status'];
                                ?>
                            </span>
                        </div>
                        
                        <div class="quote-details">
                            <div class="detail-item">
                                <strong>총액:</strong> ₩<?php echo number_format($quote['grand_total']); ?>
                            </div>
                            <div class="detail-item">
                                <strong>견적일:</strong> <?php echo date('Y-m-d', strtotime($quote['created_at'])); ?>
                            </div>
                            <?php if ($quote['valid_until']): ?>
                                <div class="detail-item">
                                    <strong>유효기간:</strong> <?php echo date('Y-m-d', strtotime($quote['valid_until'])); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($quote['converted_order_no']): ?>
                                <div class="detail-item">
                                    <strong>주문번호:</strong> 
                                    <a href="/admin/mlangprintauto/order_manager.php?search=<?php echo urlencode($quote['converted_order_no']); ?>" style="color: #667eea;">
                                        <?php echo htmlspecialchars($quote['converted_order_no']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actions">
                            <a href="/mlangprintauto/quote/detail.php?id=<?php echo $quote['id']; ?>" class="btn btn-primary" target="_blank">
                                상세보기
                            </a>
                            <a href="/mlangprintauto/quote/edit.php?id=<?php echo $quote['id']; ?>" class="btn btn-primary" target="_blank">
                                수정
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">← 이전</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 5); $i <= min($total_pages, $page + 5); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">다음 →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
