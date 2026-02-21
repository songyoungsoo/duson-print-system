<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$page = max(1, intval($_GET['page'] ?? 1));
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$per_page = 15;
$offset = ($page - 1) * $per_page;

$has_table = false;
$quotes = [];
$total = 0;
$stats = ['total' => 0, 'draft' => 0, 'sent' => 0, 'viewed' => 0, 'accepted' => 0, 'rejected' => 0, 'expired' => 0, 'converted' => 0];

try {
    $check = mysqli_query($db, "SHOW TABLES LIKE 'admin_quotes'");
    $has_table = $check && mysqli_num_rows($check) > 0;
} catch (Throwable $e) {}

if ($has_table) {
    $where = "1=1";
    $params = [];
    $types = '';

    if ($status_filter) {
        $where .= " AND q.status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    if ($search !== '') {
        $where .= " AND (q.customer_name LIKE ? OR q.quote_no LIKE ? OR q.customer_company LIKE ?)";
        $sp = "%{$search}%";
        $params[] = $sp;
        $params[] = $sp;
        $params[] = $sp;
        $types .= 'sss';
    }

    try {
        $count_q = "SELECT COUNT(*) as cnt FROM admin_quotes q WHERE {$where}";
        if (!empty($params)) {
            $stmt = mysqli_prepare($db, $count_q);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $total = intval(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['cnt']);
        } else {
            $total = intval(mysqli_fetch_assoc(mysqli_query($db, $count_q))['cnt']);
        }

        $main_q = "SELECT q.*,
                    (SELECT GROUP_CONCAT(qi.product_name ORDER BY qi.item_no SEPARATOR ', ')
                     FROM admin_quote_items qi WHERE qi.quote_id = q.id) as item_summary,
                    (SELECT COUNT(*) FROM admin_quote_items qi2 WHERE qi2.quote_id = q.id) as item_count
                   FROM admin_quotes q
                   WHERE {$where}
                   ORDER BY q.id DESC
                   LIMIT {$per_page} OFFSET {$offset}";
        if (!empty($params)) {
            $stmt = mysqli_prepare($db, $main_q);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
        } else {
            $r = mysqli_query($db, $main_q);
        }
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $quotes[] = $row;
            }
        }

        $sr = mysqli_query($db, "SELECT status, COUNT(*) as cnt FROM admin_quotes GROUP BY status");
        if ($sr) {
            while ($row = mysqli_fetch_assoc($sr)) {
                $stats[$row['status']] = intval($row['cnt']);
                $stats['total'] += intval($row['cnt']);
            }
        }
    } catch (Throwable $e) {}
}

$total_pages = max(1, ceil($total / $per_page));

$status_labels = [
    'draft' => '임시저장', 'sent' => '발송', 'viewed' => '열람',
    'accepted' => '수락', 'rejected' => '거절', 'expired' => '만료', 'converted' => '주문전환'
];
$status_colors = [
    'draft' => 'bg-gray-100 text-gray-700',
    'sent' => 'bg-blue-100 text-blue-700',
    'viewed' => 'bg-yellow-100 text-yellow-700',
    'accepted' => 'bg-green-100 text-green-700',
    'rejected' => 'bg-red-100 text-red-700',
    'expired' => 'bg-gray-100 text-gray-400',
    'converted' => 'bg-purple-100 text-purple-700'
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<style>
.qt-main { font-family: 'Noto Sans KR', -apple-system, sans-serif; font-size: 13px; max-width: 980px; margin: 0 auto; padding: 16px 20px; }
.qt-page-header { background: #1E4E79; color: #fff; padding: 10px 16px; border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; }
.qt-page-header h1 { font-size: 15px; font-weight: 700; margin: 0; color: #fff; }
.qt-filters { display: flex; align-items: center; gap: 4px; margin-bottom: 12px; flex-wrap: wrap; }
.qt-filters .filter-btn { padding: 4px 10px; font-size: 12px; border-radius: 4px; text-decoration: none; background: #f3f4f6; color: #374151; transition: all 0.15s; }
.qt-filters .filter-btn:hover { background: #e5e7eb; }
.qt-filters .filter-btn.active { background: #1E4E79; color: #fff; }
.qt-filters .filter-btn .cnt { font-weight: 700; }
.qt-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.qt-toolbar .search-input { width: 200px; padding: 4px 10px; font-size: 12px; border: 1px solid #d1d5db; border-radius: 4px; outline: none; }
.qt-toolbar .search-input:focus { border-color: #1E4E79; }
.qt-toolbar .toolbar-btn { padding: 4px 12px; font-size: 12px; border-radius: 4px; text-decoration: none; border: 1px solid #d1d5db; background: #fff; color: #374151; cursor: pointer; }
.qt-toolbar .toolbar-btn:hover { background: #f9fafb; }
.qt-toolbar .toolbar-btn-primary { background: #1E4E79; color: #fff; border-color: #1E4E79; }
.qt-toolbar .toolbar-btn-primary:hover { background: #163d5e; }
.qt-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
.qt-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.qt-table thead th { background: #f9fafb; font-size: 12px; font-weight: 600; padding: 6px 8px; text-align: center; color: #374151; border-bottom: 1px solid #e5e7eb; }
.qt-table thead th:first-child { text-align: center; padding-left: 0; width: 32px; }
.qt-table thead th:nth-child(2) { text-align: left; padding-left: 12px; }
.qt-table thead th:last-child { text-align: center; }
.qt-table tbody tr { height: 33px; transition: background 0.1s; }
.qt-table tbody tr:nth-child(odd) { background: #fff; }
.qt-table tbody tr:nth-child(even) { background: #e6f7ff; }
.qt-table tbody tr:hover { background: #dbeafe; }
.qt-table tbody td { padding: 2px 8px; text-align: center; font-size: 13px; vertical-align: middle; white-space: nowrap; }
.qt-table tbody td:first-child { text-align: center; padding-left: 0; width: 32px; }
.qt-table tbody td:nth-child(2) { text-align: left; padding-left: 12px; }
.qt-table .td-left { text-align: left; }
.qt-table .td-right { text-align: right; }
.qt-table .badge { display: inline-block; padding: 1px 8px; font-size: 11px; border-radius: 10px; font-weight: 500; }
.qt-table .badge-draft { background: #f3f4f6; color: #6b7280; }
.qt-table .badge-sent { background: #dbeafe; color: #1d4ed8; }
.qt-table .badge-viewed { background: #fef3c7; color: #b45309; }
.qt-table .badge-accepted { background: #dcfce7; color: #15803d; }
.qt-table .badge-rejected { background: #fee2e2; color: #dc2626; }
.qt-table .badge-expired { background: #f3f4f6; color: #9ca3af; }
.qt-table .badge-converted { background: #f3e8ff; color: #7c3aed; }
.qt-table .action-link { font-size: 11px; color: #1E4E79; text-decoration: none; margin: 0 3px; }
.qt-table .action-link:hover { text-decoration: underline; }
.qt-table .action-link-preview { color: #2563eb; }
.qt-table .action-link-delete { color: #dc2626; }
.qt-table .items-text { font-size: 11px; color: #6b7280; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; vertical-align: middle; }
.qt-pagination { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #6b7280; }
.qt-pagination nav { display: flex; gap: 3px; }
.qt-pagination .pg-btn { padding: 3px 8px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 3px; text-decoration: none; color: #374151; background: #fff; }
.qt-pagination .pg-btn:hover { background: #f3f4f6; }
.qt-pagination .pg-btn.active { background: #1E4E79; color: #fff; border-color: #1E4E79; font-weight: 700; }
.qt-pagination .pg-btn.disabled { color: #d1d5db; pointer-events: none; }
.qt-empty { padding: 40px; text-align: center; color: #9ca3af; font-size: 13px; }
.qt-bulk-bar { display: none; align-items: center; gap: 10px; margin-top: 8px; padding: 8px 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; font-size: 12px; color: #991b1b; }
.qt-bulk-bar.active { display: flex; }
.qt-bulk-bar button { padding: 4px 14px; font-size: 12px; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; }
.qt-bulk-bar .btn-delete { background: #dc2626; color: #fff; }
.qt-bulk-bar .btn-delete:hover { background: #b91c1c; }
.qt-bulk-bar .btn-cancel { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.qt-bulk-bar .btn-cancel:hover { background: #f3f4f6; }
</style>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="qt-main">
        <!-- 헤더 -->
        <div class="qt-page-header">
            <h1>견적 관리</h1>
            <div style="display:flex;gap:6px;">
                <a href="#" onclick="openQuotePopup('/admin/mlangprintauto/quote/create.php', 'quote_create'); return false;" class="qt-toolbar toolbar-btn" style="color:#fff;border-color:rgba(255,255,255,0.5);background:rgba(255,255,255,0.15);font-size:12px;">+ 새 견적</a>
            </div>
        </div>

        <!-- 필터 + 검색 -->
        <div class="qt-toolbar">
            <div class="qt-filters" style="margin-bottom:0;">
                <?php
                $stat_items = [
                    ['key' => 'total',     'label' => '전체',     'filter' => ''],
                    ['key' => 'draft',     'label' => '임시',     'filter' => 'draft'],
                    ['key' => 'sent',      'label' => '발송',     'filter' => 'sent'],
                    ['key' => 'viewed',    'label' => '열람',     'filter' => 'viewed'],
                    ['key' => 'accepted',  'label' => '수락',     'filter' => 'accepted'],
                    ['key' => 'rejected',  'label' => '거절',     'filter' => 'rejected'],
                    ['key' => 'converted', 'label' => '전환',     'filter' => 'converted'],
                ];
                foreach ($stat_items as $si):
                    $active = ($status_filter === $si['filter']);
                ?>
                <a href="?status=<?php echo $si['filter']; ?>" class="filter-btn <?php echo $active ? 'active' : ''; ?>">
                    <?php echo $si['label']; ?> <span class="cnt"><?php echo $stats[$si['key']]; ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <form method="GET" style="display:flex;align-items:center;gap:6px;">
                    <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>"><?php endif; ?>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="고객명, 견적번호 검색..." class="search-input">
                    <?php if ($search !== '' || $status_filter): ?>
                    <a href="?" style="font-size:11px;color:#9ca3af;text-decoration:none;">초기화</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (!$has_table): ?>
        <div class="qt-card qt-empty">견적 시스템 준비 중 (admin_quotes 테이블 없음)</div>
        <?php else: ?>

        <!-- 견적 목록 -->
        <?php if (empty($quotes)): ?>
        <div class="qt-card qt-empty"><?php echo ($search !== '' || $status_filter) ? '검색 조건에 맞는 견적서가 없습니다.' : '아직 견적서가 없습니다.'; ?></div>
        <?php else: ?>
        <div class="qt-card">
            <table class="qt-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="qt-check-all" onchange="toggleAllChecks(this)"></th>
                        <th>견적번호</th>
                        <th>상태</th>
                        <th>날짜</th>
                        <th>고객명</th>
                        <th>업체</th>
                        <th>품목</th>
                        <th>금액</th>
                        <th>액션</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotes as $q):
                        $badge_cls = 'badge-' . ($q['status'] ?? 'draft');
                        $sl = $status_labels[$q['status']] ?? $q['status'];
                        $is_expired = !empty($q['valid_until']) && $q['valid_until'] < date('Y-m-d') && !in_array($q['status'], ['accepted', 'converted']);
                    ?>
                    <tr>
                        <td><input type="checkbox" class="qt-row-check" value="<?php echo $q['id']; ?>" onchange="updateBulkBar()"></td>
                        <td class="td-left" style="font-weight:600;"><?php echo htmlspecialchars($q['quote_no']); ?></td>
                        <td>
                            <span class="badge <?php echo $badge_cls; ?>"><?php echo $sl; ?></span>
                            <?php if ($is_expired): ?><span class="badge badge-expired">만료</span><?php endif; ?>
                        </td>
                        <td><?php echo date('m/d', strtotime($q['created_at'])); ?></td>
                        <td style="font-weight:500;"><?php echo htmlspecialchars($q['customer_name']); ?></td>
                        <td style="color:#6b7280;font-size:12px;"><?php echo htmlspecialchars($q['customer_company'] ?? ''); ?></td>
                        <td><?php if (!empty($q['item_summary'])): ?><span class="items-text"><?php echo htmlspecialchars($q['item_summary']); ?><?php if ($q['item_count'] > 1) echo " ({$q['item_count']}건)"; ?></span><?php endif; ?></td>
                        <td class="td-right" style="font-weight:600;"><?php echo number_format(intval($q['grand_total'])); ?>원</td>
                        <td>
                            <a href="#" onclick="openQuotePopup('/admin/mlangprintauto/quote/detail.php?id=<?php echo $q['id']; ?>', 'quote_detail_<?php echo $q['id']; ?>'); return false;" class="action-link">상세</a>
                            <a href="#" onclick="openQuotePopup('/admin/mlangprintauto/quote/edit.php?id=<?php echo $q['id']; ?>', 'quote_edit_<?php echo $q['id']; ?>'); return false;" class="action-link">수정</a>
                            <a href="#" onclick="openQuotePopup('/admin/mlangprintauto/quote/preview.php?id=<?php echo $q['id']; ?>', 'quote_preview_<?php echo $q['id']; ?>'); return false;" class="action-link action-link-preview">미리보기</a>
                            <a href="#" onclick="deleteQuote(<?php echo $q['id']; ?>, '<?php echo htmlspecialchars($q['quote_no'], ENT_QUOTES); ?>'); return false;" class="action-link action-link-delete">삭제</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="qt-bulk-bar" id="qt-bulk-bar">
            <span id="qt-bulk-count">0</span>건 선택됨
            <button class="btn-delete" onclick="bulkDeleteQuotes()">선택 삭제</button>
            <button class="btn-cancel" onclick="clearAllChecks()">선택 해제</button>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1):
            $qs = http_build_query(array_filter(['status' => $status_filter, 'q' => $search]));
            $qs_prefix = $qs ? "&{$qs}" : '';
        ?>
        <div class="qt-pagination">
            <span>총 <?php echo number_format($total); ?>건 (<?php echo $page; ?>/<?php echo $total_pages; ?>페이지)</span>
            <nav>
                <?php if ($page > 1): ?>
                <a href="?page=1<?php echo $qs_prefix; ?>" class="pg-btn">&laquo;</a>
                <a href="?page=<?php echo $page - 1; ?><?php echo $qs_prefix; ?>" class="pg-btn">&lsaquo;</a>
                <?php else: ?>
                <span class="pg-btn disabled">&laquo;</span>
                <span class="pg-btn disabled">&lsaquo;</span>
                <?php endif; ?>

                <?php
                $start_p = max(1, $page - 2);
                $end_p = min($total_pages, $page + 2);
                if ($start_p > 1): ?>
                <a href="?page=1<?php echo $qs_prefix; ?>" class="pg-btn">1</a>
                <?php if ($start_p > 2): ?><span style="padding:0 2px;color:#9ca3af;">...</span><?php endif; ?>
                <?php endif;

                for ($i = $start_p; $i <= $end_p; $i++):
                    if ($i === $page): ?>
                <span class="pg-btn active"><?php echo $i; ?></span>
                    <?php else: ?>
                <a href="?page=<?php echo $i; ?><?php echo $qs_prefix; ?>" class="pg-btn"><?php echo $i; ?></a>
                    <?php endif;
                endfor;

                if ($end_p < $total_pages): ?>
                <?php if ($end_p < $total_pages - 1): ?><span style="padding:0 2px;color:#9ca3af;">...</span><?php endif; ?>
                <a href="?page=<?php echo $total_pages; ?><?php echo $qs_prefix; ?>" class="pg-btn"><?php echo $total_pages; ?></a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $qs_prefix; ?>" class="pg-btn">&rsaquo;</a>
                <a href="?page=<?php echo $total_pages; ?><?php echo $qs_prefix; ?>" class="pg-btn">&raquo;</a>
                <?php else: ?>
                <span class="pg-btn disabled">&rsaquo;</span>
                <span class="pg-btn disabled">&raquo;</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function openQuotePopup(url, name) {
    var w = Math.min(960, screen.availWidth - 40);
    var h = Math.min(screen.availHeight - 40, screen.availHeight * 0.92);
    var left = Math.round((screen.availWidth - w) / 2);
    var top = Math.round((screen.availHeight - h) / 2);
    window.open(url, name, 'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',scrollbars=yes,resizable=yes');
}

function deleteQuote(id, quoteNo) {
    if (!confirm('견적 [' + quoteNo + '] 을(를) 삭제하시겠습니까?\n삭제 후 복구할 수 없습니다.')) return;
    fetch('/dashboard/api/quotes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) location.reload();
        else alert('삭제 실패: ' + res.message);
    })
    .catch(function() { alert('서버 오류가 발생했습니다.'); });
}

function getCheckedIds() {
    return Array.from(document.querySelectorAll('.qt-row-check:checked')).map(function(el) { return parseInt(el.value); });
}

function toggleAllChecks(master) {
    document.querySelectorAll('.qt-row-check').forEach(function(cb) { cb.checked = master.checked; });
    updateBulkBar();
}

function clearAllChecks() {
    document.querySelectorAll('.qt-row-check').forEach(function(cb) { cb.checked = false; });
    var master = document.getElementById('qt-check-all');
    if (master) master.checked = false;
    updateBulkBar();
}

function updateBulkBar() {
    var ids = getCheckedIds();
    var bar = document.getElementById('qt-bulk-bar');
    var cnt = document.getElementById('qt-bulk-count');
    var checks = document.querySelectorAll('.qt-row-check');
    var master = document.getElementById('qt-check-all');

    cnt.textContent = ids.length;
    bar.classList.toggle('active', ids.length > 0);

    if (master) master.checked = checks.length > 0 && ids.length === checks.length;
}

function bulkDeleteQuotes() {
    var ids = getCheckedIds();
    if (ids.length === 0) return;
    if (!confirm(ids.length + '건의 견적을 삭제하시겠습니까?\n삭제 후 복구할 수 없습니다.')) return;

    fetch('/dashboard/api/quotes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'bulk_delete', ids: ids })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) location.reload();
        else alert('삭제 실패: ' + res.message);
    })
    .catch(function() { alert('서버 오류가 발생했습니다.'); });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
