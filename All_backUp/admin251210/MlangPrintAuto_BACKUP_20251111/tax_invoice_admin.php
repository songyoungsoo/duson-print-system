<?php
/**
 * 관리자 - 세금계산서 발급 관리
 * 경로: /admin/MlangPrintAuto/tax_invoice_admin.php
 */

session_start();

// 관리자 권한 확인 (간단한 체크, 실제로는 더 강력한 인증 필요)
if (!isset($_SESSION['admin_logged_in'])) {
    // admin 세션이 없으면 로그인 페이지로
    echo "<script>alert('관리자 로그인이 필요합니다.'); location.href='/admin/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$error = '';
$success = '';

// 세금계산서 발급 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['issue_invoice'])) {
    $invoice_id = intval($_POST['invoice_id']);

    $update_query = "UPDATE tax_invoices SET status = 'issued', updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $invoice_id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "세금계산서가 발급되었습니다.";
    } else {
        $error = "세금계산서 발급 중 오류가 발생했습니다.";
    }
    mysqli_stmt_close($stmt);
}

// 세금계산서 취소 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_invoice'])) {
    $invoice_id = intval($_POST['invoice_id']);

    $update_query = "UPDATE tax_invoices SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $invoice_id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "세금계산서가 취소되었습니다.";
    } else {
        $error = "세금계산서 취소 중 오류가 발생했습니다.";
    }
    mysqli_stmt_close($stmt);
}

// 필터링
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// 페이지네이션
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 30;
$offset = ($page - 1) * $per_page;

// 쿼리 조건 구성
$where_conditions = [];
$bind_types = '';
$bind_values = [];

if ($status_filter != 'all') {
    $where_conditions[] = "ti.status = ?";
    $bind_types .= 's';
    $bind_values[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(ti.invoice_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $bind_types .= 'sss';
    $search_term = '%' . $search . '%';
    $bind_values[] = $search_term;
    $bind_values[] = $search_term;
    $bind_values[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 전체 개수 조회
$count_query = "SELECT COUNT(*) as total
                FROM tax_invoices ti
                LEFT JOIN users u ON ti.user_id = u.id
                $where_clause";
$stmt = mysqli_prepare($db, $count_query);
if (!empty($bind_values)) {
    mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_values);
}
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_count = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_count / $per_page);
mysqli_stmt_close($stmt);

// 세금계산서 목록 조회
$list_query = "SELECT ti.*, u.name as buyer_name, u.email as buyer_email, u.business_name
               FROM tax_invoices ti
               LEFT JOIN users u ON ti.user_id = u.id
               $where_clause
               ORDER BY ti.created_at DESC
               LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($db, $list_query);
$bind_types_with_limit = $bind_types . 'ii';
$bind_values_with_limit = array_merge($bind_values, [$per_page, $offset]);
if (!empty($bind_values_with_limit)) {
    mysqli_stmt_bind_param($stmt, $bind_types_with_limit, ...$bind_values_with_limit);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
}
mysqli_stmt_execute($stmt);
$invoices_result = mysqli_stmt_get_result($stmt);
$invoices = [];
while ($row = mysqli_fetch_assoc($invoices_result)) {
    $invoices[] = $row;
}
mysqli_stmt_close($stmt);

// 통계
$stats_query = "SELECT
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'issued' THEN 1 END) as issued_count,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
                    SUM(CASE WHEN status = 'issued' THEN total_amount ELSE 0 END) as total_issued_amount
                FROM tax_invoices";
$stats_result = mysqli_query($db, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>세금계산서 관리 - 두손기획인쇄 관리자</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Malgun Gothic', sans-serif;
            font-size: 13px;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1466BA;
        }

        .page-title {
            font-size: 26px;
            color: #333;
            margin-bottom: 10px;
        }

        .breadcrumb {
            font-size: 12px;
            color: #666;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #1466BA;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1466BA;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4a8a;
        }

        .btn-success {
            background: #28a745;
            color: white;
            font-size: 12px;
            padding: 6px 14px;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            font-size: 12px;
            padding: 6px 14px;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            font-size: 12px;
            padding: 6px 14px;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 15px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th,
        .data-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }

        .data-table td {
            color: #666;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-issued {
            background: #28a745;
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-cancelled {
            background: #dc3545;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
        }

        .page-link:hover {
            background: #f8f9fa;
        }

        .page-link.active {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">세금계산서 관리</h1>
            <div class="breadcrumb">관리자 > 주문관리 > 세금계산서</div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- 통계 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">발급 대기</div>
                <div class="stat-value"><?php echo number_format($stats['pending_count']); ?>건</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">발급 완료</div>
                <div class="stat-value"><?php echo number_format($stats['issued_count']); ?>건</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">취소됨</div>
                <div class="stat-value"><?php echo number_format($stats['cancelled_count']); ?>건</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">총 발급 금액</div>
                <div class="stat-value"><?php echo number_format($stats['total_issued_amount']); ?>원</div>
            </div>
        </div>

        <!-- 필터 -->
        <div class="filter-section">
            <form method="get" class="filter-form">
                <div class="form-group">
                    <label class="form-label">상태</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>전체</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>발급대기</option>
                        <option value="issued" <?php echo $status_filter == 'issued' ? 'selected' : ''; ?>>발급완료</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>취소됨</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">검색</label>
                    <input type="text" name="search" class="form-input"
                           placeholder="승인번호, 고객명, 이메일"
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group" style="flex: 0;">
                    <button type="submit" class="btn btn-primary">검색</button>
                </div>
            </form>
        </div>

        <!-- 세금계산서 목록 -->
        <div class="table-container">
            <?php if (empty($invoices)): ?>
            <div class="empty-state">
                <p>세금계산서가 없습니다.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>승인번호</th>
                        <th>주문번호</th>
                        <th>고객명</th>
                        <th>이메일</th>
                        <th>사업자명</th>
                        <th>발급일자</th>
                        <th>공급가액</th>
                        <th>합계금액</th>
                        <th>상태</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo $invoice['id']; ?></td>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td>#<?php echo $invoice['order_no']; ?></td>
                        <td><?php echo htmlspecialchars($invoice['buyer_name']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['buyer_email']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['business_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['issue_date']); ?></td>
                        <td><?php echo number_format($invoice['supply_amount']); ?>원</td>
                        <td><?php echo number_format($invoice['total_amount']); ?>원</td>
                        <td>
                            <?php
                            $status_class = 'status-' . $invoice['status'];
                            $status_text = [
                                'issued' => '발급완료',
                                'pending' => '발급대기',
                                'cancelled' => '취소됨'
                            ][$invoice['status']] ?? '알 수 없음';
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($invoice['status'] == 'pending'): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="issue_invoice" class="btn btn-success"
                                            onclick="return confirm('세금계산서를 발급하시겠습니까?');">
                                        발급
                                    </button>
                                </form>
                                <?php endif; ?>

                                <?php if ($invoice['status'] == 'issued'): ?>
                                <a href="/mypage/view_invoice.php?id=<?php echo $invoice['id']; ?>"
                                   target="_blank"
                                   class="btn btn-secondary">
                                    보기
                                </a>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="cancel_invoice" class="btn btn-danger"
                                            onclick="return confirm('세금계산서를 취소하시겠습니까?');">
                                        취소
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- 페이지네이션 -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="page-link">이전</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="page-link">다음</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($db);
?>
