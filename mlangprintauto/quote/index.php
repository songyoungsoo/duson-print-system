<?php
/**
 * 견적서 관리 - 목록 페이지 (엑셀 스타일)
 */

session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';

if (!$db) {
    die('데이터베이스 연결 실패');
}

$manager = new QuoteManager($db);

// 필터링 파라미터
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

// 검색 조건
$where = [];
$params = [];
$types = '';

if ($status && in_array($status, ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted'])) {
    $where[] = "q.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($search) {
    $where[] = "(q.quote_no LIKE ? OR q.customer_name LIKE ? OR q.customer_company LIKE ? OR q.customer_email LIKE ?)";
    $searchParam = "%{$search}%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    $types .= 'ssss';
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// 전체 개수
$countQuery = "SELECT COUNT(*) as total FROM quotes q $whereClause";
if (!empty($params)) {
    $stmt = mysqli_prepare($db, $countQuery);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $totalResult = mysqli_stmt_get_result($stmt);
} else {
    $totalResult = mysqli_query($db, $countQuery);
}
$totalRow = mysqli_fetch_assoc($totalResult);
$total = $totalRow['total'];
$totalPages = ceil($total / $perPage);

// 목록 조회
$offset = ($page - 1) * $perPage;
$query = "SELECT q.*,
    (SELECT COUNT(*) FROM quote_items WHERE quote_id = q.id) as item_count
FROM quotes q
$whereClause
ORDER BY q.created_at DESC
LIMIT $perPage OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($db, $query);
}

$quotes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quotes[] = $row;
}

// 상태별 개수
$statusCounts = [];
$statusQuery = "SELECT status, COUNT(*) as cnt FROM quotes GROUP BY status";
$statusResult = mysqli_query($db, $statusQuery);
while ($row = mysqli_fetch_assoc($statusResult)) {
    $statusCounts[$row['status']] = $row['cnt'];
}

// 상태 라벨
$statusLabels = [
    'draft' => ['label' => '작성중', 'color' => '#6c757d'],
    'sent' => ['label' => '발송', 'color' => '#0d6efd'],
    'viewed' => ['label' => '확인', 'color' => '#17a2b8'],
    'accepted' => ['label' => '승인', 'color' => '#28a745'],
    'rejected' => ['label' => '거절', 'color' => '#dc3545'],
    'expired' => ['label' => '만료', 'color' => '#6c757d'],
    'converted' => ['label' => '주문', 'color' => '#198754']
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 관리</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; background: #f0f0f0; font-size: 13px; }

        .container { max-width: 1600px; margin: 0 auto; padding: 12px; }

        /* 헤더 */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            background: #fff;
            padding: 10px 18px;
            border: 1px solid #ccc;
        }
        .header h1 { font-size: 18px; font-weight: bold; }
        .btn-new {
            padding: 6px 14px;
            background: #217346;
            color: #fff;
            border: none;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-new:hover { background: #1a5c38; }

        /* 툴바 */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            background: #fff;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-bottom: none;
        }
        .status-filters {
            display: flex;
            gap: 4px;
        }
        .status-btn {
            padding: 5px 10px;
            border: 1px solid #ccc;
            background: #f8f8f8;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-size: 13px;
        }
        .status-btn:hover { background: #e8e8e8; }
        .status-btn.active { background: #217346; color: #fff; border-color: #217346; }
        .status-btn .count {
            background: rgba(0,0,0,0.1);
            padding: 2px 5px;
            font-size: 11px;
            margin-left: 3px;
        }
        .status-btn.active .count { background: rgba(255,255,255,0.3); }

        .search-box {
            display: flex;
            gap: 4px;
        }
        .search-box input {
            width: 220px;
            padding: 5px 10px;
            border: 1px solid #ccc;
            font-size: 13px;
        }
        .search-box button {
            padding: 5px 12px;
            background: #5a5a5a;
            color: #fff;
            border: 1px solid #444;
            cursor: pointer;
            font-size: 13px;
        }

        /* 엑셀 스타일 테이블 */
        .excel-table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            border: 1px solid #8c8c8c;
        }
        .excel-table th {
            background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 100%);
            border: 1px solid #8c8c8c;
            padding: 8px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            color: #333;
            white-space: nowrap;
        }
        .excel-table td {
            border: 1px solid #c0c0c0;
            padding: 6px 10px;
            font-size: 13px;
            vertical-align: middle;
        }
        .excel-table tbody tr:hover { background: #e8f4fc; }
        .excel-table tbody tr:nth-child(even) { background: #fafafa; }
        .excel-table tbody tr:nth-child(even):hover { background: #e8f4fc; }

        /* 컬럼별 스타일 */
        .col-no { width: 40px; text-align: center; color: #666; }
        .col-quote { width: 140px; }
        .col-customer { width: 130px; }
        .col-company { width: 160px; }
        .col-items { width: 55px; text-align: center; }
        .col-amount { width: 110px; text-align: right; font-family: 'Consolas', monospace; }
        .col-status { width: 70px; text-align: center; }
        .col-valid { width: 95px; text-align: center; }
        .col-created { width: 140px; text-align: center; }
        .col-actions { width: 180px; text-align: center; }

        .quote-link {
            color: #0066cc;
            text-decoration: none;
            font-weight: 500;
        }
        .quote-link:hover { text-decoration: underline; }

        .status-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            color: #fff;
        }
        .expired-tag {
            background: #dc3545;
            margin-left: 3px;
        }

        .action-btn {
            padding: 3px 8px;
            font-size: 12px;
            border: 1px solid #ccc;
            background: #f8f8f8;
            color: #333;
            text-decoration: none;
            margin: 0 2px;
            cursor: pointer;
        }
        .action-btn:hover { background: #e0e0e0; }
        .btn-delete { border-color: #dc3545; color: #dc3545; }
        .btn-delete:hover { background: #dc3545; color: #fff; }

        /* 페이지네이션 */
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-top: none;
        }
        .page-info { font-size: 13px; color: #666; }
        .page-nav {
            display: flex;
            gap: 3px;
        }
        .page-nav a, .page-nav span {
            padding: 4px 10px;
            border: 1px solid #ccc;
            background: #f8f8f8;
            text-decoration: none;
            color: #333;
            font-size: 13px;
        }
        .page-nav a:hover { background: #e0e0e0; }
        .page-nav .current {
            background: #217346;
            color: #fff;
            border-color: #217346;
        }
        .page-nav .disabled {
            color: #aaa;
            background: #f0f0f0;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
            background: #fff;
            border: 1px solid #ccc;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>견적서 관리</h1>
            <a href="create.php" class="btn-new">+ 새 견적서</a>
        </div>

        <div class="toolbar">
            <div class="status-filters">
                <a href="index.php" class="status-btn <?php echo !$status ? 'active' : ''; ?>">
                    전체<span class="count"><?php echo $total; ?></span>
                </a>
                <?php foreach ($statusLabels as $key => $info): ?>
                <a href="?status=<?php echo $key; ?>" class="status-btn <?php echo $status === $key ? 'active' : ''; ?>">
                    <?php echo $info['label']; ?><span class="count"><?php echo $statusCounts[$key] ?? 0; ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <form class="search-box" method="get">
                <?php if ($status): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="검색..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">검색</button>
            </form>
        </div>

        <?php if (count($quotes) > 0): ?>
        <table class="excel-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-quote">견적번호</th>
                    <th class="col-customer">고객명</th>
                    <th class="col-company">회사명</th>
                    <th class="col-items">품목</th>
                    <th class="col-amount">합계금액</th>
                    <th class="col-status">상태</th>
                    <th class="col-valid">유효기간</th>
                    <th class="col-created">작성일시</th>
                    <th class="col-actions">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rowNum = $offset;
                foreach ($quotes as $quote):
                    $rowNum++;
                    $statusInfo = $statusLabels[$quote['status']] ?? ['label' => $quote['status'], 'color' => '#6c757d'];
                    $isExpired = strtotime($quote['valid_until']) < time() && !in_array($quote['status'], ['accepted', 'rejected', 'converted']);
                ?>
                <tr>
                    <td class="col-no"><?php echo $rowNum; ?></td>
                    <td class="col-quote">
                        <a href="detail.php?id=<?php echo $quote['id']; ?>" class="quote-link"><?php echo htmlspecialchars($quote['quote_no']); ?></a>
                    </td>
                    <td class="col-customer"><?php echo htmlspecialchars($quote['customer_name']); ?></td>
                    <td class="col-company"><?php echo htmlspecialchars($quote['customer_company'] ?? '-'); ?></td>
                    <td class="col-items"><?php echo $quote['item_count']; ?></td>
                    <td class="col-amount"><?php echo number_format($quote['grand_total']); ?></td>
                    <td class="col-status">
                        <span class="status-tag" style="background:<?php echo $statusInfo['color']; ?>;"><?php echo $statusInfo['label']; ?></span>
                        <?php if ($isExpired): ?><span class="status-tag expired-tag">만료</span><?php endif; ?>
                        <?php if ($quote['status'] === 'converted' && !empty($quote['converted_order_no'])): ?>
                        <a href="/admin/mlangprintauto/admin.php?mode=OrderView&no=<?php echo htmlspecialchars($quote['converted_order_no']); ?>" target="_blank" class="order-link" style="display: inline-block; margin-left: 4px; font-size: 11px; color: #198754; text-decoration: underline;">#<?php echo htmlspecialchars($quote['converted_order_no']); ?></a>
                        <?php endif; ?>
                    </td>
                    <td class="col-valid"><?php echo date('Y-m-d', strtotime($quote['valid_until'])); ?></td>
                    <td class="col-created"><?php echo date('Y-m-d H:i', strtotime($quote['created_at'])); ?></td>
                    <td class="col-actions">
                        <a href="detail.php?id=<?php echo $quote['id']; ?>" class="action-btn">상세</a>
                        <a href="public/view.php?token=<?php echo $quote['public_token']; ?>" target="_blank" class="action-btn">보기</a>
                        <a href="api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $quote['public_token']; ?>" target="_blank" class="action-btn">PDF</a>
                        <?php if ($quote['status'] !== 'converted'): ?>
                        <button type="button" class="action-btn btn-delete" onclick="deleteQuote(<?php echo $quote['id']; ?>, '<?php echo htmlspecialchars($quote['quote_no'], ENT_QUOTES); ?>')">삭제</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination-bar">
            <span class="page-info">총 <?php echo number_format($total); ?>건 | <?php echo $page; ?>/<?php echo max(1, $totalPages); ?> 페이지</span>
            <div class="page-nav">
                <?php if ($totalPages > 1): ?>
                    <?php if ($page > 1): ?>
                    <a href="?page=1&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">처음</a>
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">이전</a>
                    <?php else: ?>
                    <span class="disabled">처음</span>
                    <span class="disabled">이전</span>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 3);
                    $end = min($totalPages, $page + 3);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">다음</a>
                    <a href="?page=<?php echo $totalPages; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">끝</a>
                    <?php else: ?>
                    <span class="disabled">다음</span>
                    <span class="disabled">끝</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p>견적서가 없습니다.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function deleteQuote(id, quoteNo) {
        if (!confirm('견적서 [' + quoteNo + ']을(를) 삭제하시겠습니까?\n\n삭제된 견적서는 복구할 수 없습니다.')) {
            return;
        }

        const formData = new FormData();
        formData.append('id', id);

        fetch('api/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('견적서가 삭제되었습니다.');
                location.reload();
            } else {
                alert('삭제 실패: ' + data.message);
            }
        })
        .catch(error => {
            alert('오류가 발생했습니다: ' + error);
        });
    }
    </script>
</body>
</html>
