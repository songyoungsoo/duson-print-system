<?php
/**
 * 관리자용 견적서 발송 내역 관리 페이지
 * 경로: /admin/quote_management.php
 */

session_start();
include "../db.php";
include "../includes/functions.php";

// 관리자 인증 확인 (필요에 따라 수정)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: ../member/login.php');
//     exit;
// }

// UTF-8 설정
mysqli_set_charset($db, 'utf8');

// 페이지네이션 설정
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// 검색 조건
$search_name = $_GET['search_name'] ?? '';
$search_phone = $_GET['search_phone'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status_filter = $_GET['status'] ?? '';

// WHERE 조건 구성
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search_name)) {
    $where_conditions[] = "customer_name LIKE ?";
    $params[] = "%{$search_name}%";
    $types .= 's';
}

if (!empty($search_phone)) {
    $where_conditions[] = "customer_phone LIKE ?";
    $params[] = "%{$search_phone}%";
    $types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// 전체 개수 조회
$count_query = "SELECT COUNT(*) as total FROM quote_log {$where_clause}";
$count_stmt = mysqli_prepare($db, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_count = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_count / $limit);
mysqli_stmt_close($count_stmt);

// 견적서 목록 조회
$main_params = array_merge($params, [$limit, $offset]);
$main_types = $types . 'ii';

$query = "SELECT * FROM quote_log {$where_clause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($db, $query);
if (!empty($main_params)) {
    mysqli_stmt_bind_param($stmt, $main_types, ...$main_params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 견적서 상태 업데이트 처리
if ($_POST['action'] ?? '' === 'update_status') {
    $quote_number = $_POST['quote_number'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (!empty($quote_number) && !empty($new_status)) {
        $update_query = "UPDATE quote_log SET status = ?, notes = ?, admin_viewed = 1, admin_viewed_at = NOW() WHERE quote_number = ?";
        $update_stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'sss', $new_status, $notes, $quote_number);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
        
        header('Location: quote_management.php');
        exit;
    }
}

// 통계 정보 조회
$stats_query = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN admin_viewed = 0 THEN 1 END) as unviewed,
    COUNT(CASE WHEN status = 'generated' THEN 1 END) as generated,
    COUNT(CASE WHEN status = 'ordered' THEN 1 END) as ordered,
    SUM(total_price_vat) as total_amount
    FROM quote_log 
    WHERE DATE(created_at) = CURDATE()";
$stats_result = mysqli_query($db, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 발송 내역 관리 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Malgun Gothic', sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #2c5aa0 0%, #17a2b8 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header h1 {
            font-size: 1.8rem;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #2c5aa0;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c5aa0;
        }

        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }

        .search-box {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #2c5aa0;
            color: white;
        }

        .btn-primary:hover {
            background: #1e3a6f;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table thead {
            background: #f8f9fa;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .table th {
            font-weight: 600;
            color: #495057;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-generated {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-sent {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .status-viewed {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-ordered {
            background: #e8f5e8;
            color: #388e3c;
        }

        .status-cancelled {
            background: #ffebee;
            color: #d32f2f;
        }

        .unviewed {
            font-weight: bold;
            background: #fff3cd;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin: 2rem 0;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #dee2e6;
            text-decoration: none;
            color: #495057;
            border-radius: 5px;
        }

        .pagination .current {
            background: #2c5aa0;
            color: white;
            border-color: #2c5aa0;
        }

        .quote-details {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 12px;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
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
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>📊 견적서 발송 내역 관리</h1>
        </div>
    </div>

    <div class="container">
        <!-- 통계 카드 -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">오늘 총 견적서</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #e74c3c;"><?php echo number_format($stats['unviewed']); ?></div>
                <div class="stat-label">미확인 견적서</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #f39c12;"><?php echo number_format($stats['generated']); ?></div>
                <div class="stat-label">발송 완료</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #27ae60;"><?php echo number_format($stats['ordered']); ?></div>
                <div class="stat-label">주문 전환</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_amount']); ?>원</div>
                <div class="stat-label">오늘 견적 총액</div>
            </div>
        </div>

        <!-- 검색 박스 -->
        <div class="search-box">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label>고객명</label>
                    <input type="text" name="search_name" class="form-control" 
                           value="<?php echo htmlspecialchars($search_name); ?>" placeholder="고객명 검색">
                </div>
                <div class="form-group">
                    <label>연락처</label>
                    <input type="text" name="search_phone" class="form-control" 
                           value="<?php echo htmlspecialchars($search_phone); ?>" placeholder="연락처 검색">
                </div>
                <div class="form-group">
                    <label>시작일</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                <div class="form-group">
                    <label>종료일</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                <div class="form-group">
                    <label>상태</label>
                    <select name="status" class="form-control">
                        <option value="">전체</option>
                        <option value="generated" <?php echo $status_filter === 'generated' ? 'selected' : ''; ?>>발송완료</option>
                        <option value="viewed" <?php echo $status_filter === 'viewed' ? 'selected' : ''; ?>>확인됨</option>
                        <option value="ordered" <?php echo $status_filter === 'ordered' ? 'selected' : ''; ?>>주문완료</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>취소됨</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">🔍 검색</button>
                </div>
            </form>
        </div>

        <!-- 견적서 목록 테이블 -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>견적번호</th>
                        <th>고객정보</th>
                        <th>상품정보</th>
                        <th>금액</th>
                        <th>상태</th>
                        <th>발송일시</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="<?php echo $row['admin_viewed'] == 0 ? 'unviewed' : ''; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($row['quote_number']); ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($row['customer_phone']); ?></small>
                            <?php if (!empty($row['customer_company'])): ?>
                                <br><small style="color: #666;"><?php echo htmlspecialchars($row['customer_company']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="quote-details">
                            <?php echo $row['total_items']; ?>개 상품
                            <?php if (!empty($row['items_summary'])): ?>
                                <br><small style="color: #666;">
                                    <?php 
                                    $items = json_decode($row['items_summary'], true);
                                    if ($items && is_array($items)) {
                                        $item_names = array_column($items, 'product_name');
                                        echo htmlspecialchars(implode(', ', array_slice($item_names, 0, 2)));
                                        if (count($item_names) > 2) echo ' 외 ' . (count($item_names) - 2) . '개';
                                    }
                                    ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo number_format($row['total_price_vat']); ?>원</strong><br>
                            <small style="color: #666;">(VAT포함)</small>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php 
                                $status_labels = [
                                    'generated' => '발송완료',
                                    'sent' => '메일발송',
                                    'viewed' => '확인됨',
                                    'ordered' => '주문완료',
                                    'cancelled' => '취소됨'
                                ];
                                echo $status_labels[$row['status']] ?? $row['status'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('m-d H:i', strtotime($row['created_at'])); ?>
                            <?php if ($row['admin_viewed'] == 0): ?>
                                <br><span style="color: #e74c3c; font-size: 12px;">⚠️ 미확인</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button onclick="viewQuoteDetails('<?php echo $row['quote_number']; ?>')" 
                                        class="btn btn-info btn-sm">📋 상세</button>
                                <button onclick="updateStatus('<?php echo $row['quote_number']; ?>', '<?php echo $row['status']; ?>')" 
                                        class="btn btn-success btn-sm">✏️ 상태</button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&<?php echo http_build_query($_GET); ?>">&laquo; 이전</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($_GET); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&<?php echo http_build_query($_GET); ?>">다음 &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin: 2rem 0;">
            <a href="../" class="btn btn-primary">🏠 메인으로</a>
            <a href="cart.php" class="btn btn-primary">🛒 장바구니</a>
        </div>
    </div>

    <!-- 상세 정보 모달 -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <h3>견적서 상세 정보</h3>
            <div id="detailContent">로딩중...</div>
            <div style="text-align: center; margin-top: 2rem;">
                <button onclick="closeModal()" class="btn btn-primary">닫기</button>
            </div>
        </div>
    </div>

    <!-- 상태 업데이트 모달 -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <h3>상태 업데이트</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="quote_number" id="statusQuoteNumber">
                
                <div class="form-group" style="margin: 1rem 0;">
                    <label>상태 변경</label>
                    <select name="new_status" class="form-control" required>
                        <option value="generated">발송완료</option>
                        <option value="viewed">확인됨</option>
                        <option value="ordered">주문완료</option>
                        <option value="cancelled">취소됨</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 1rem 0;">
                    <label>메모</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="상태 변경 사유나 메모를 입력하세요"></textarea>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="submit" class="btn btn-success">💾 저장</button>
                    <button type="button" onclick="closeModal()" class="btn">취소</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewQuoteDetails(quoteNumber) {
            document.getElementById('detailModal').style.display = 'block';
            
            // AJAX로 상세 정보 가져오기
            fetch(`quote_detail.php?quote_number=${quoteNumber}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('detailContent').innerHTML = '<p>오류가 발생했습니다.</p>';
                });
        }

        function updateStatus(quoteNumber, currentStatus) {
            document.getElementById('statusQuoteNumber').value = quoteNumber;
            document.querySelector('[name="new_status"]').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
            document.getElementById('statusModal').style.display = 'none';
        }

        // 모달 외부 클릭 시 닫기
        window.onclick = function(event) {
            const detailModal = document.getElementById('detailModal');
            const statusModal = document.getElementById('statusModal');
            if (event.target === detailModal) detailModal.style.display = 'none';
            if (event.target === statusModal) statusModal.style.display = 'none';
        }
    </script>
</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($db);
?>