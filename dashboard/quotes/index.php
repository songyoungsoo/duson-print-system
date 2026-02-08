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

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-[18px]">
        <!-- 헤더 -->
        <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold text-gray-900">견적 관리</h1>
                <div class="flex items-center gap-1.5">
                    <?php
                    $stat_items = [
                        ['key' => 'total',     'label' => '전체',     'color' => 'text-gray-700', 'filter' => ''],
                        ['key' => 'draft',     'label' => '임시',     'color' => 'text-gray-500', 'filter' => 'draft'],
                        ['key' => 'sent',      'label' => '발송',     'color' => 'text-blue-600', 'filter' => 'sent'],
                        ['key' => 'viewed',    'label' => '열람',     'color' => 'text-yellow-600', 'filter' => 'viewed'],
                        ['key' => 'accepted',  'label' => '수락',     'color' => 'text-green-600', 'filter' => 'accepted'],
                        ['key' => 'rejected',  'label' => '거절',     'color' => 'text-red-500', 'filter' => 'rejected'],
                        ['key' => 'converted', 'label' => '전환',     'color' => 'text-purple-600', 'filter' => 'converted'],
                    ];
                    foreach ($stat_items as $si):
                        $active = ($status_filter === $si['filter']);
                        $cls = $active ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200 ' . $si['color'];
                    ?>
                    <a href="?status=<?php echo $si['filter']; ?>" class="px-2.5 py-1 text-xs rounded-md <?php echo $cls; ?>">
                        <?php echo $si['label']; ?> <span class="font-bold"><?php echo $stats[$si['key']]; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center gap-1.5">
                    <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>"><?php endif; ?>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="고객명, 견적번호 검색..."
                           class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <?php if ($search !== '' || $status_filter): ?>
                    <a href="?" class="text-xs text-gray-400 hover:text-gray-600">초기화</a>
                    <?php endif; ?>
                </form>
                <a href="/admin/mlangprintauto/quote/" target="_blank" class="px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg hover:bg-gray-50">관리자</a>
                <a href="/admin/mlangprintauto/quote/create.php" target="_blank" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ 새 견적</a>
            </div>
        </div>

        <?php if (!$has_table): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center text-sm text-yellow-800">견적 시스템 준비 중 (admin_quotes 테이블 없음)</div>
        <?php else: ?>

        <!-- 견적 목록 -->
        <?php if (empty($quotes)): ?>
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-400 text-sm"><?php echo ($search !== '' || $status_filter) ? '검색 조건에 맞는 견적서가 없습니다.' : '아직 견적서가 없습니다.'; ?></p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden divide-y divide-gray-100">
            <?php foreach ($quotes as $q):
                $sc = $status_colors[$q['status']] ?? 'bg-gray-100 text-gray-700';
                $sl = $status_labels[$q['status']] ?? $q['status'];
                $is_expired = !empty($q['valid_until']) && $q['valid_until'] < date('Y-m-d') && !in_array($q['status'], ['accepted', 'converted']);
            ?>
            <div class="px-5 py-[11px] flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors">
                <!-- 왼쪽: 견적정보 -->
                <div class="flex-1 min-w-0 flex items-center gap-3">
                    <span class="text-sm font-bold text-gray-900 whitespace-nowrap"><?php echo htmlspecialchars($q['quote_no']); ?></span>
                    <span class="px-2 py-0.5 text-[11px] font-medium rounded-full whitespace-nowrap <?php echo $sc; ?>"><?php echo $sl; ?></span>
                    <?php if ($is_expired): ?><span class="px-2 py-0.5 text-[11px] rounded-full bg-red-50 text-red-500">만료</span><?php endif; ?>
                    <span class="text-xs text-gray-400 whitespace-nowrap"><?php echo date('m/d', strtotime($q['created_at'])); ?></span>
                    <span class="text-sm font-medium text-gray-800 whitespace-nowrap"><?php echo htmlspecialchars($q['customer_name']); ?></span>
                    <?php if (!empty($q['customer_company'])): ?>
                    <span class="text-xs text-gray-400 whitespace-nowrap"><?php echo htmlspecialchars($q['customer_company']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($q['item_summary'])): ?>
                    <span class="text-xs text-gray-400 truncate"><?php echo htmlspecialchars($q['item_summary']); ?><?php if ($q['item_count'] > 1) echo " ({$q['item_count']}건)"; ?></span>
                    <?php endif; ?>
                </div>
                <!-- 오른쪽: 금액 + 버튼 -->
                <div class="flex items-center gap-5 flex-shrink-0">
                    <span class="text-sm font-bold text-gray-900 whitespace-nowrap"><?php echo number_format(intval($q['grand_total'])); ?><span class="text-xs font-normal text-gray-400">원</span></span>
                    <div class="flex items-center gap-2">
                        <a href="/admin/mlangprintauto/quote/detail.php?id=<?php echo $q['id']; ?>" target="_blank" class="px-3 py-[5px] text-xs text-gray-500 border border-gray-200 rounded hover:bg-gray-100">상세</a>
                        <a href="/admin/mlangprintauto/quote/edit.php?id=<?php echo $q['id']; ?>" target="_blank" class="px-3 py-[5px] text-xs text-gray-500 border border-gray-200 rounded hover:bg-gray-100">수정</a>
                        <a href="/admin/mlangprintauto/quote/preview.php?id=<?php echo $q['id']; ?>" target="_blank" class="px-3 py-[5px] text-xs text-blue-600 border border-blue-200 rounded hover:bg-blue-50">미리보기</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1):
            $qs = http_build_query(array_filter(['status' => $status_filter, 'q' => $search]));
            $qs_prefix = $qs ? "&{$qs}" : '';
        ?>
        <div class="mt-3.5 flex items-center justify-between">
            <span class="text-xs text-gray-500">총 <?php echo number_format($total); ?>건 (<?php echo $page; ?>/<?php echo $total_pages; ?>페이지)</span>
            <nav class="flex items-center gap-0.5">
                <?php if ($page > 1): ?>
                <a href="?page=1<?php echo $qs_prefix; ?>" class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100">&laquo;</a>
                <a href="?page=<?php echo $page - 1; ?><?php echo $qs_prefix; ?>" class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100">&lsaquo;</a>
                <?php else: ?>
                <span class="px-2 py-1 text-xs border border-gray-200 rounded text-gray-300">&laquo;</span>
                <span class="px-2 py-1 text-xs border border-gray-200 rounded text-gray-300">&lsaquo;</span>
                <?php endif; ?>

                <?php
                $start_p = max(1, $page - 2);
                $end_p = min($total_pages, $page + 2);
                if ($start_p > 1): ?>
                <a href="?page=1<?php echo $qs_prefix; ?>" class="px-2.5 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100">1</a>
                <?php if ($start_p > 2): ?><span class="px-1 text-xs text-gray-400">...</span><?php endif; ?>
                <?php endif;

                for ($i = $start_p; $i <= $end_p; $i++):
                    if ($i === $page): ?>
                <span class="px-2.5 py-1 text-xs border border-blue-500 rounded bg-blue-600 text-white font-bold"><?php echo $i; ?></span>
                    <?php else: ?>
                <a href="?page=<?php echo $i; ?><?php echo $qs_prefix; ?>" class="px-2.5 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100"><?php echo $i; ?></a>
                    <?php endif;
                endfor;

                if ($end_p < $total_pages): ?>
                <?php if ($end_p < $total_pages - 1): ?><span class="px-1 text-xs text-gray-400">...</span><?php endif; ?>
                <a href="?page=<?php echo $total_pages; ?><?php echo $qs_prefix; ?>" class="px-2.5 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100"><?php echo $total_pages; ?></a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $qs_prefix; ?>" class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100">&rsaquo;</a>
                <a href="?page=<?php echo $total_pages; ?><?php echo $qs_prefix; ?>" class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100">&raquo;</a>
                <?php else: ?>
                <span class="px-2 py-1 text-xs border border-gray-200 rounded text-gray-300">&rsaquo;</span>
                <span class="px-2 py-1 text-xs border border-gray-200 rounded text-gray-300">&raquo;</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
