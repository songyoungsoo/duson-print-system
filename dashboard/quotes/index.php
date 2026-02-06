<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$page = max(1, intval($_GET['page'] ?? 1));
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$has_quotes_table = false;
$quotes = [];
$total = 0;
$stats = ['total' => 0, 'draft' => 0, 'sent' => 0, 'accepted' => 0];

try {
    $check = mysqli_query($db, "SHOW TABLES LIKE 'quotes'");
    $has_quotes_table = mysqli_num_rows($check) > 0;
} catch (Throwable $e) {}

if ($has_quotes_table) {
    $where = "1=1";
    $params = [];
    $types = '';

    if ($status_filter) {
        $where .= " AND status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    if ($search !== '') {
        $where .= " AND (customer_name LIKE ? OR quote_number LIKE ?)";
        $sp = "%{$search}%";
        $params[] = $sp;
        $params[] = $sp;
        $types .= 'ss';
    }

    try {
        $count_q = "SELECT COUNT(*) as cnt FROM quotes WHERE {$where}";
        if (!empty($params)) {
            $stmt = mysqli_prepare($db, $count_q);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['cnt'];
        } else {
            $total = mysqli_fetch_assoc(mysqli_query($db, $count_q))['cnt'];
        }

        $q = "SELECT * FROM quotes WHERE {$where} ORDER BY id DESC LIMIT {$per_page} OFFSET {$offset}";
        if (!empty($params)) {
            $stmt = mysqli_prepare($db, $q);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $r = mysqli_stmt_get_result($stmt);
        } else {
            $r = mysqli_query($db, $q);
        }
        while ($row = mysqli_fetch_assoc($r)) {
            $quotes[] = $row;
        }

        $sr = mysqli_query($db, "SELECT status, COUNT(*) as cnt FROM quotes GROUP BY status");
        while ($row = mysqli_fetch_assoc($sr)) {
            $stats[$row['status']] = intval($row['cnt']);
            $stats['total'] += intval($row['cnt']);
        }
    } catch (Throwable $e) {}
}

$total_pages = max(1, ceil($total / $per_page));

$status_labels = [
    'draft' => '임시저장', 'sent' => '발송', 'viewed' => '열람',
    'accepted' => '수락', 'rejected' => '거절', 'expired' => '만료', 'converted' => '주문전환'
];
$status_colors = [
    'draft' => 'bg-gray-100 text-gray-700', 'sent' => 'bg-blue-100 text-blue-700',
    'viewed' => 'bg-yellow-100 text-yellow-700', 'accepted' => 'bg-green-100 text-green-700',
    'rejected' => 'bg-red-100 text-red-700', 'expired' => 'bg-gray-100 text-gray-500',
    'converted' => 'bg-purple-100 text-purple-700'
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📋 견적 관리</h1>
                <p class="text-sm text-gray-600">견적서 작성 · 발송 · 상태 관리</p>
            </div>
            <a href="/admin/mlangprintauto/quote/create.php" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">
                + 새 견적서
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>

        <?php if (!$has_quotes_table): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <div class="text-3xl mb-2">📋</div>
            <h3 class="font-semibold text-yellow-800 mb-1">견적 시스템 준비 중</h3>
            <p class="text-sm text-yellow-700 mb-3">quotes 테이블이 아직 생성되지 않았습니다.</p>
            <a href="/admin/mlangprintauto/quote/" target="_blank" class="inline-block px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700">기존 견적 시스템 →</a>
        </div>
        <?php else: ?>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-3 mb-4">
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-gray-900"><?php echo $stats['total']; ?></div>
                <div class="text-xs text-gray-500">전체</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-gray-600"><?php echo $stats['draft'] ?? 0; ?></div>
                <div class="text-xs text-gray-500">임시저장</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-blue-600"><?php echo $stats['sent'] ?? 0; ?></div>
                <div class="text-xs text-gray-500">발송</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-green-600"><?php echo $stats['accepted'] ?? 0; ?></div>
                <div class="text-xs text-gray-500">수락</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-3 mb-4">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <div class="flex gap-1 flex-wrap">
                    <a href="?" class="px-3 py-1.5 text-xs rounded-full <?php echo !$status_filter ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">전체</a>
                    <?php foreach ($status_labels as $sk => $sl): ?>
                    <a href="?status=<?php echo $sk; ?>" class="px-3 py-1.5 text-xs rounded-full <?php echo $status_filter === $sk ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>"><?php echo $sl; ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="고객명, 견적번호 검색..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo $status_filter; ?>"><?php endif; ?>
                <button type="submit" class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">검색</button>
            </form>
        </div>

        <!-- Quote List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">견적번호</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">고객명</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">금액</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">상태</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">생성일</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">작업</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($quotes)): ?>
                        <tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-400">견적서가 없습니다.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($quotes as $q): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($q['quote_number'] ?? '#'.$q['id']); ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($q['customer_name'] ?? ''); ?></td>
                            <td class="px-3 py-2 text-sm text-gray-900 text-right font-medium"><?php echo number_format(floatval($q['total_amount'] ?? 0)); ?>원</td>
                            <td class="px-3 py-2 text-center">
                                <?php $sc = $status_colors[$q['status']] ?? 'bg-gray-100 text-gray-700'; ?>
                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full <?php echo $sc; ?>"><?php echo $status_labels[$q['status']] ?? $q['status']; ?></span>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-400 text-center"><?php echo date('m/d', strtotime($q['created_at'] ?? 'now')); ?></td>
                            <td class="px-3 py-2 text-center">
                                <a href="/admin/mlangprintauto/quote/detail.php?id=<?php echo $q['id']; ?>" target="_blank" class="text-xs text-blue-600 hover:text-blue-800">상세 →</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="px-3 py-2 border-t flex items-center justify-between text-sm">
                <span class="text-gray-600">총 <?php echo number_format($total); ?>건</span>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?><a href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&q=<?php echo urlencode($search); ?>" class="px-3 py-1 border rounded hover:bg-gray-50">이전</a><?php endif; ?>
                    <?php if ($page < $total_pages): ?><a href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&q=<?php echo urlencode($search); ?>" class="px-3 py-1 border rounded hover:bg-gray-50">다음</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
