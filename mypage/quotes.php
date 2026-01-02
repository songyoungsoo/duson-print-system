<?php
require_once __DIR__ . '/auth_required.php';

$user_id = $current_user['id'];

// 페이지네이션
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// 상태 필터
$status_filter = $_GET['status'] ?? '';

// 견적 목록 조회
$where = ["customer_id = ?"];
$params = [$user_id];
$types = "i";

if (!empty($status_filter)) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_clause = implode(" AND ", $where);

$query = "
    SELECT 
        q.id,
        q.quote_no,
        q.customer_name,
        q.customer_company,
        q.supply_total,
        q.vat_total,
        q.grand_total,
        q.status,
        q.created_at,
        q.valid_until,
        q.converted_order_no,
        q.pdf_path,
        COUNT(qi.id) as item_count
    FROM quotes q
    LEFT JOIN quote_items qi ON q.id = qi.quote_id
    WHERE {$where_clause}
    GROUP BY q.id
    ORDER BY q.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($db, $query);
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$quotes = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// 전체 개수
$count_query = "SELECT COUNT(*) as total FROM quotes WHERE {$where_clause}";
$count_stmt = mysqli_prepare($db, $count_query);
$count_types = substr($types, 0, -2); // 마지막 limit, offset 타입 제거
$count_params = array_slice($params, 0, -2);
if (!empty($count_params)) {
    mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));
mysqli_stmt_close($count_stmt);

$total_items = $count_result['total'];
$total_pages = ceil($total_items / $limit);

// 견적 통계
$stats_query = "
    SELECT 
        COUNT(*) as total_quotes,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
        SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_count
    FROM quotes
    WHERE customer_id = ?
";

$stats_stmt = mysqli_prepare($db, $stats_query);
mysqli_stmt_bind_param($stats_stmt, "i", $user_id);
mysqli_stmt_execute($stats_stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stats_stmt));
mysqli_stmt_close($stats_stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>견적 내역 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/mlangprintauto/css/common-styles.css">
    <style>
        body { background: #f5f5f5; padding: 20px; font-family: 'Malgun Gothic', sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 { color: #333; margin-bottom: 10px; }
        .header p { color: #666; margin-top: 5px; }
        
        .nav-link { margin: 20px 0; }
        .nav-link a { color: #667eea; text-decoration: none; }
        
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
        .stat-card .number { font-size: 28px; font-weight: bold; color: #667eea; }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .filters select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
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
            grid-template-columns: 1fr 1fr;
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
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover { background: #5a6268; }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover { background: #218838; }
        
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
            <h1>💰 견적 내역</h1>
            <p>요청하신 견적을 확인하고 관리할 수 있습니다</p>
        </div>
        
        <div class="nav-link">
            <a href="index.php">← 마이페이지로 돌아가기</a> | 
            <a href="/mlangprintauto/quote/" target="_blank">새 견적 요청 →</a>
        </div>
        
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>전체 견적</h3>
                <div class="number"><?php echo number_format($stats['total_quotes'] ?? 0); ?>건</div>
            </div>
            <div class="stat-card">
                <h3>임시 저장</h3>
                <div class="number"><?php echo number_format($stats['draft_count'] ?? 0); ?>건</div>
            </div>
            <div class="stat-card">
                <h3>발송됨</h3>
                <div class="number"><?php echo number_format($stats['sent_count'] ?? 0); ?>건</div>
            </div>
            <div class="stat-card">
                <h3>승인됨</h3>
                <div class="number"><?php echo number_format($stats['accepted_count'] ?? 0); ?>건</div>
            </div>
            <div class="stat-card">
                <h3>주문 전환</h3>
                <div class="number"><?php echo number_format($stats['converted_count'] ?? 0); ?>건</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <label>상태 필터:</label>
            <select id="statusFilter" onchange="filterByStatus(this.value)">
                <option value="">전체</option>
                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>임시 저장</option>
                <option value="sent" <?php echo $status_filter === 'sent' ? 'selected' : ''; ?>>발송됨</option>
                <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>승인됨</option>
                <option value="converted" <?php echo $status_filter === 'converted' ? 'selected' : ''; ?>>주문 전환</option>
                <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>만료됨</option>
            </select>
        </div>
        
        <!-- Quote List -->
        <div class="quote-list">
            <?php if (mysqli_num_rows($quotes) === 0): ?>
                <div class="no-quotes">
                    <p>견적 내역이 없습니다.</p>
                    <a href="/mlangprintauto/quote/" class="btn btn-primary" style="margin-top: 20px;">견적 요청하기</a>
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
                                <strong>공급가:</strong> ₩<?php echo number_format($quote['supply_total']); ?>
                            </div>
                            <div class="detail-item">
                                <strong>부가세:</strong> ₩<?php echo number_format($quote['vat_total']); ?>
                            </div>
                            <div class="detail-item">
                                <strong>총액:</strong> <span style="color: #667eea; font-weight: bold;">₩<?php echo number_format($quote['grand_total']); ?></span>
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
                                    <a href="/mypage/orders.php?search=<?php echo urlencode($quote['converted_order_no']); ?>" style="color: #667eea;">
                                        <?php echo htmlspecialchars($quote['converted_order_no']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="actions">
                            <a href="/mlangprintauto/quote/detail.php?id=<?php echo $quote['id']; ?>" class="btn btn-primary" target="_blank">
                                상세보기
                            </a>
                            
                            <?php if ($quote['pdf_path']): ?>
                                <a href="<?php echo htmlspecialchars($quote['pdf_path']); ?>" class="btn btn-secondary" target="_blank">
                                    PDF 다운로드
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($quote['status'] === 'sent' || $quote['status'] === 'viewed' || $quote['status'] === 'accepted'): ?>
                                <button onclick="confirmConvertToOrder(<?php echo $quote['id']; ?>, '<?php echo htmlspecialchars($quote['quote_no'], ENT_QUOTES); ?>')" class="btn btn-success">
                                    🛒 주문하기
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?status=<?php echo urlencode($status_filter); ?>&page=<?php echo $page - 1; ?>">← 이전</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 5); $i <= min($total_pages, $page + 5); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?status=<?php echo urlencode($status_filter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?status=<?php echo urlencode($status_filter); ?>&page=<?php echo $page + 1; ?>">다음 →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 주문 전환 확인 모달 -->
    <div id="convertModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
            <h2 style="margin: 0 0 15px 0; color: #333;">주문 전환 확인</h2>
            <p style="color: #666; line-height: 1.6;">
                견적서를 주문으로 전환하시겠습니까?<br>
                <strong id="modalQuoteNo" style="color: #667eea;"></strong><br><br>
                전환 후에는 주문 내역에서 확인하실 수 있으며,<br>
                주문 확인 이메일이 발송됩니다.
            </p>
            <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end;">
                <button onclick="closeConvertModal()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    취소
                </button>
                <button id="confirmConvertBtn" onclick="executeConvertToOrder()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    주문 전환
                </button>
            </div>
            <div id="convertProgress" style="display: none; text-align: center; margin-top: 15px;">
                <div style="display: inline-block; width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 10px; color: #666;">주문 처리 중...</p>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        let currentQuoteId = null;

        function confirmConvertToOrder(quoteId, quoteNo) {
            currentQuoteId = quoteId;
            document.getElementById('modalQuoteNo').textContent = '견적번호: ' + quoteNo;
            document.getElementById('convertModal').style.display = 'flex';
        }

        function closeConvertModal() {
            document.getElementById('convertModal').style.display = 'none';
            document.getElementById('convertProgress').style.display = 'none';
            document.getElementById('confirmConvertBtn').disabled = false;
            currentQuoteId = null;
        }

        function executeConvertToOrder() {
            if (!currentQuoteId) {
                alert('견적서 ID가 없습니다.');
                return;
            }

            // 버튼 비활성화 및 진행 표시
            document.getElementById('confirmConvertBtn').disabled = true;
            document.getElementById('convertProgress').style.display = 'block';

            // AJAX 요청
            fetch('/mlangprintauto/quote/api/convert_to_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'quote_id=' + currentQuoteId + '&confirm=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ 주문 전환이 완료되었습니다!\n\n' +
                          '주문 번호: ' + data.first_order_no + '\n' +
                          '생성된 주문: ' + data.order_count + '건\n\n' +
                          '주문 확인 이메일이 발송되었습니다.');
                    closeConvertModal();
                    // 페이지 새로고침
                    location.reload();
                } else {
                    alert('❌ 오류: ' + (data.message || '주문 전환에 실패했습니다.'));
                    document.getElementById('convertProgress').style.display = 'none';
                    document.getElementById('confirmConvertBtn').disabled = false;
                }
            })
            .catch(error => {
                console.error('네트워크 오류:', error);
                alert('❌ 네트워크 오류가 발생했습니다.');
                document.getElementById('convertProgress').style.display = 'none';
                document.getElementById('confirmConvertBtn').disabled = false;
            });
        }

        function filterByStatus(status) {
            window.location.href = '?status=' + encodeURIComponent(status);
        }
    </script>
</body>
</html>
