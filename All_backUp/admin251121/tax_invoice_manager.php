<?php
/**
 * 관리자 - 전자세금계산서 관리
 * 경로: /admin/tax_invoice_manager.php
 */

session_start();

// 관리자 권한 확인 (개발 중에는 주석 처리)
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: /admin/index.php');
//     exit;
// }

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/TrusbillAPI.php';

$error = '';
$success = '';

// 세금계산서 취소 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_invoice'])) {
    $invoice_id = intval($_POST['invoice_id']);
    $cancel_memo = trim($_POST['cancel_memo']);
    
    // 세금계산서 정보 조회
    $query = "SELECT * FROM tax_invoices WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $invoice_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $invoice = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($invoice && $invoice['status'] == 'issued' && !empty($invoice['nts_confirm_num'])) {
        $trusbill = new TrusbillAPI();
        $cancel_result = $trusbill->cancelInvoice($invoice['nts_confirm_num'], $cancel_memo);
        
        if ($cancel_result['success']) {
            $update_query = "UPDATE tax_invoices SET status = 'cancelled', api_response = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_query);
            $api_response_json = json_encode($cancel_result['data'], JSON_UNESCAPED_UNICODE);
            mysqli_stmt_bind_param($stmt, "si", $api_response_json, $invoice_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            $success = "세금계산서가 취소되었습니다.";
        } else {
            $error = "세금계산서 취소 실패: " . ($cancel_result['message'] ?? '알 수 없는 오류');
        }
    } else {
        $error = "취소할 수 없는 세금계산서입니다.";
    }
}

// 페이지네이션
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// 필터
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 전체 세금계산서 수
$count_query = "SELECT COUNT(*) as total FROM tax_invoices WHERE 1=1";
$where_params = [];
$where_types = '';

if ($status_filter) {
    $count_query .= " AND status = ?";
    $where_params[] = $status_filter;
    $where_types .= 's';
}

if ($search) {
    $count_query .= " AND (invoice_number LIKE ? OR nts_confirm_num LIKE ? OR order_no LIKE ?)";
    $search_param = '%' . $search . '%';
    $where_params[] = $search_param;
    $where_params[] = $search_param;
    $where_params[] = $search_param;
    $where_types .= 'sss';
}

$stmt = mysqli_prepare($db, $count_query);
if (!empty($where_params)) {
    mysqli_stmt_bind_param($stmt, $where_types, ...$where_params);
}
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_count = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_count / $per_page);
mysqli_stmt_close($stmt);

// 세금계산서 목록 조회
$list_query = "SELECT t.*, u.name as user_name, u.email as user_email 
               FROM tax_invoices t
               LEFT JOIN users u ON t.user_id = u.id
               WHERE 1=1";

if ($status_filter) {
    $list_query .= " AND t.status = ?";
}

if ($search) {
    $list_query .= " AND (t.invoice_number LIKE ? OR t.nts_confirm_num LIKE ? OR t.order_no LIKE ?)";
}

$list_query .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($db, $list_query);
$params = $where_params;
$params[] = $per_page;
$params[] = $offset;
$types = $where_types . 'ii';

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$invoices_result = mysqli_stmt_get_result($stmt);
$invoices = [];
while ($row = mysqli_fetch_assoc($invoices_result)) {
    $invoices[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>전자세금계산서 관리 - 관리자</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            font-size: 13px;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group label {
            font-weight: 600;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1466BA;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4a8a;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tbody tr:hover {
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

        .status-failed {
            background: #6c757d;
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .modal-body {
            margin-bottom: 15px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            resize: vertical;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 전자세금계산서 관리</h1>
            <div class="header-actions">
                <a href="/admin/" class="btn btn-secondary">관리자 홈</a>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="filter-section">
            <form method="get" class="filter-group">
                <label>상태:</label>
                <select name="status">
                    <option value="">전체</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>발급대기</option>
                    <option value="issued" <?php echo $status_filter == 'issued' ? 'selected' : ''; ?>>발급완료</option>
                    <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>발급실패</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>취소됨</option>
                </select>

                <label>검색:</label>
                <input type="text" name="search" placeholder="승인번호, 주문번호" value="<?php echo htmlspecialchars($search); ?>">

                <button type="submit" class="btn btn-primary">검색</button>
                <a href="?" class="btn btn-secondary">초기화</a>
            </form>
        </div>

        <div class="content">
            <p style="margin-bottom: 15px;">총 <strong><?php echo number_format($total_count); ?></strong>건</p>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>승인번호</th>
                        <th>국세청번호</th>
                        <th>주문번호</th>
                        <th>고객명</th>
                        <th>발급일자</th>
                        <th>합계금액</th>
                        <th>상태</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                            세금계산서가 없습니다.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo $invoice['id']; ?></td>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['nts_confirm_num'] ?? '-'); ?></td>
                        <td>#<?php echo $invoice['order_no']; ?></td>
                        <td><?php echo htmlspecialchars($invoice['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['issue_date']); ?></td>
                        <td><?php echo number_format($invoice['total_amount']); ?>원</td>
                        <td>
                            <?php
                            $status_class = 'status-' . $invoice['status'];
                            $status_text = [
                                'issued' => '발급완료',
                                'pending' => '발급대기',
                                'cancelled' => '취소됨',
                                'failed' => '발급실패'
                            ][$invoice['status']] ?? '알 수 없음';
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td>
                            <?php if ($invoice['status'] == 'issued'): ?>
                            <button onclick="openCancelModal(<?php echo $invoice['id']; ?>, '<?php echo htmlspecialchars($invoice['invoice_number']); ?>')" 
                                    class="btn btn-danger" style="font-size: 11px; padding: 4px 8px;">
                                취소
                            </button>
                            <?php elseif ($invoice['status'] == 'failed'): ?>
                            <button onclick="viewError(<?php echo $invoice['id']; ?>)" 
                                    class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">
                                오류보기
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

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
        </div>
    </div>

    <!-- 취소 모달 -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">세금계산서 취소</div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="cancel_invoice_id">
                    <p>세금계산서 <strong id="cancel_invoice_number"></strong>를 취소하시겠습니까?</p>
                    <div class="form-group">
                        <label>취소 사유:</label>
                        <textarea name="cancel_memo" rows="3" placeholder="취소 사유를 입력하세요" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeCancelModal()" class="btn btn-secondary">닫기</button>
                    <button type="submit" name="cancel_invoice" class="btn btn-danger">취소하기</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(invoiceId, invoiceNumber) {
            document.getElementById('cancel_invoice_id').value = invoiceId;
            document.getElementById('cancel_invoice_number').textContent = invoiceNumber;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        function viewError(invoiceId) {
            // TODO: 오류 상세 보기 구현
            alert('오류 상세 보기 기능은 추후 구현 예정입니다.');
        }

        // 모달 외부 클릭시 닫기
        window.onclick = function(event) {
            const modal = document.getElementById('cancelModal');
            if (event.target == modal) {
                closeCancelModal();
            }
        }
    </script>
</body>
</html>
<?php
mysqli_close($db);
?>
