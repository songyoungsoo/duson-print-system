<?php
/**
 * 관리자 견적서 목록 - Excel Style
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/mlangprintauto/login.php");
    exit;
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/includes/AdminQuoteManager.php';

if (!$db) { die('DB 연결 실패'); }
mysqli_set_charset($db, 'utf8mb4');

$quoteManager = new AdminQuoteManager($db);

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$filters = ['status' => $status, 'search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo];
$result = $quoteManager->getQuoteList($filters, $page, 20);
$quotes = $result['quotes'];
$totalPages = $result['total_pages'];
$stats = $quoteManager->getStatistics();

$statusText = [
    'draft' => '임시저장', 'sent' => '발송', 'viewed' => '열람',
    'accepted' => '승인', 'rejected' => '거절', 'expired' => '만료', 'converted' => '주문전환'
];
$statusClass = [
    'draft' => 'status-draft', 'sent' => 'status-sent', 'viewed' => 'status-viewed',
    'accepted' => 'status-accepted', 'rejected' => 'status-rejected',
    'expired' => 'status-expired', 'converted' => 'status-converted'
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 관리</title>
    <link rel="stylesheet" href="assets/excel-style.css">
</head>
<body>
<div class="container">
    <div class="page-header">
        <div>
            <h1>견적서 관리</h1>
        </div>
        <div class="action-bar">
            <a href="/admin/mlangprintauto/" class="back-link">← 관리자홈</a>
            <a href="create.php" class="btn btn-primary">+ 새 견적서</a>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-cell highlight">
            <h4>전체</h4>
            <div class="number"><?php echo number_format($stats['total'] ?? 0); ?></div>
        </div>
        <div class="stat-cell">
            <h4>임시저장</h4>
            <div class="number"><?php echo number_format($stats['draft'] ?? 0); ?></div>
        </div>
        <div class="stat-cell">
            <h4>발송</h4>
            <div class="number"><?php echo number_format($stats['sent'] ?? 0); ?></div>
        </div>
        <div class="stat-cell">
            <h4>승인</h4>
            <div class="number"><?php echo number_format($stats['accepted'] ?? 0); ?></div>
        </div>
        <div class="stat-cell">
            <h4>주문전환</h4>
            <div class="number"><?php echo number_format($stats['converted'] ?? 0); ?></div>
        </div>
    </div>

    <form method="GET" class="filter-bar">
        <div class="filter-group">
            <label>상태</label>
            <select name="status">
                <option value="">전체</option>
                <?php foreach ($statusText as $key => $text): ?>
                <option value="<?php echo $key; ?>" <?php echo $status === $key ? 'selected' : ''; ?>><?php echo $text; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>기간</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
            <span>~</span>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
        </div>
        <div class="filter-group">
            <label>검색</label>
            <input type="text" name="search" placeholder="견적번호, 고객명" value="<?php echo htmlspecialchars($search); ?>" style="width:150px;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">검색</button>
        <a href="?" class="btn btn-sm">초기화</a>
    </form>

    <div class="card">
        <?php if (empty($quotes)): ?>
        <div class="empty-state">
            견적서가 없습니다.<br><br>
            <a href="create.php" class="btn btn-primary">+ 새 견적서 작성</a>
        </div>
        <?php else: ?>
        <table class="excel-table">
            <thead>
                <tr>
                    <th style="width:120px;">견적번호</th>
                    <th>고객정보</th>
                    <th style="width:60px;">품목</th>
                    <th style="width:100px;">공급가</th>
                    <th style="width:100px;">총액</th>
                    <th style="width:80px;">상태</th>
                    <th style="width:85px;">작성일</th>
                    <th style="width:85px;">유효기간</th>
                    <th style="width:100px;">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($quotes as $q): ?>
                <tr>
                    <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($q['quote_no']); ?></td>
                    <td>
                        <?php if (!empty($q['customer_company'])): ?>
                        <strong><?php echo htmlspecialchars($q['customer_company']); ?></strong><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($q['customer_name']); ?>
                    </td>
                    <td class="text-center"><?php echo $q['item_count']; ?>개</td>
                    <td class="text-right"><?php echo number_format($q['supply_total']); ?></td>
                    <td class="text-right fw-bold"><?php echo number_format($q['grand_total']); ?></td>
                    <td class="text-center">
                        <span class="status-badge <?php echo $statusClass[$q['status']] ?? ''; ?>">
                            <?php echo $statusText[$q['status']] ?? $q['status']; ?>
                        </span>
                    </td>
                    <td class="text-center"><?php echo date('Y-m-d', strtotime($q['created_at'])); ?></td>
                    <td class="text-center">
                        <?php if ($q['valid_until']): ?>
                            <?php $exp = strtotime($q['valid_until']) < time(); ?>
                            <span style="color:<?php echo $exp ? '#a94442' : '#333'; ?>"><?php echo date('Y-m-d', strtotime($q['valid_until'])); ?></span>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="actions">
                            <a href="detail.php?id=<?php echo $q['id']; ?>" class="btn btn-sm">상세</a>
                            <a href="edit.php?id=<?php echo $q['id']; ?>" class="btn btn-primary btn-sm">수정</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            $qp = http_build_query(array_filter(['status'=>$status,'search'=>$search,'date_from'=>$dateFrom,'date_to'=>$dateTo]));
            $base = '?' . ($qp ? $qp . '&' : '');
            if ($page > 1): ?><a href="<?php echo $base; ?>page=<?php echo $page-1; ?>">이전</a><?php endif;
            for ($i = max(1,$page-3); $i <= min($totalPages,$page+3); $i++):
                if ($i === $page): ?><span class="current"><?php echo $i; ?></span>
                <?php else: ?><a href="<?php echo $base; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a><?php endif;
            endfor;
            if ($page < $totalPages): ?><a href="<?php echo $base; ?>page=<?php echo $page+1; ?>">다음</a><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
