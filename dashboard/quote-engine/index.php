<?php
/**
 * 견적엔진 — 견적서 목록
 * 경로: /dashboard/quote-engine/index.php
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../includes/quote-engine/QuoteEngine.php';

// Override sidebar active
$_SERVER['REQUEST_URI'] = '/dashboard/quote-engine/';

// ─── 필터 파라미터 ──────────────────────────────────────────
$filters = [];
$status    = trim($_GET['status'] ?? '');
$docType   = trim($_GET['doc_type'] ?? '');
$search    = trim($_GET['search'] ?? '');
$dateFrom  = trim($_GET['date_from'] ?? '');
$dateTo    = trim($_GET['date_to'] ?? '');
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 20;

if ($status !== '')   $filters['status']    = $status;
if ($docType !== '')  $filters['doc_type']  = $docType;
if ($search !== '')   $filters['search']    = $search;
if ($dateFrom !== '') $filters['date_from'] = $dateFrom;
if ($dateTo !== '')   $filters['date_to']   = $dateTo;

$engine = new QE_QuoteEngine($db);
$result = $engine->listQuotes($filters, $page, $perPage);

$quotes     = $result['items'];
$totalItems = $result['total'];
$totalPages = $result['pages'];
$currentPage = $result['page'];

// ─── 상태 뱃지 맵 ──────────────────────────────────────────
$statusMap = [
    'draft'     => ['label' => '임시저장', 'cls' => 'bg-gray-100 text-gray-700'],
    'completed' => ['label' => '완료',     'cls' => 'bg-blue-100 text-blue-700'],
    'sent'      => ['label' => '발송',     'cls' => 'bg-green-100 text-green-700'],
    'expired'   => ['label' => '만료',     'cls' => 'bg-red-100 text-red-700'],
];

$docTypeMap = [
    'quotation'   => ['label' => '견적서',     'cls' => 'bg-blue-50 text-blue-600 border border-blue-200'],
    'transaction' => ['label' => '거래명세서', 'cls' => 'bg-purple-50 text-purple-600 border border-purple-200'],
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 min-h-0 bg-gray-50 overflow-y-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">

        <!-- 헤더 + 필터 한 줄 -->
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <h1 class="text-lg font-bold text-gray-900 mr-1">견적서 관리</h1>
            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700"><?php echo number_format($totalItems); ?>건</span>

            <select id="statusFilter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" onchange="applyFilters()">
                <option value="">상태: 전체</option>
                <option value="draft"<?php echo $status === 'draft' ? ' selected' : ''; ?>>임시저장</option>
                <option value="completed"<?php echo $status === 'completed' ? ' selected' : ''; ?>>완료</option>
                <option value="sent"<?php echo $status === 'sent' ? ' selected' : ''; ?>>발송</option>
                <option value="expired"<?php echo $status === 'expired' ? ' selected' : ''; ?>>만료</option>
            </select>

            <select id="docTypeFilter" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" onchange="applyFilters()">
                <option value="">유형: 전체</option>
                <option value="quotation"<?php echo $docType === 'quotation' ? ' selected' : ''; ?>>견적서</option>
                <option value="transaction"<?php echo $docType === 'transaction' ? ' selected' : ''; ?>>거래명세서</option>
            </select>

            <input type="date" id="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>"
                   class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" onchange="applyFilters()">
            <span class="text-xs text-gray-400">~</span>
            <input type="date" id="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>"
                   class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" onchange="applyFilters()">

            <div class="flex-1 min-w-[160px]">
                <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="문서번호, 고객명, 회사명"
                       class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <button onclick="applyFilters()" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">검색</button>

            <a href="create.php" class="ml-auto px-3 py-1 text-xs font-semibold text-white rounded hover:opacity-90 transition-colors flex items-center gap-1"
               style="background:#1E4E79;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                새 견적서
            </a>
        </div>

        <!-- 테이블 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 w-10">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">문서번호</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 w-20">유형</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">고객명</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">회사명</th>
                            <th class="px-2 py-1.5 text-right text-xs font-medium text-gray-500">총액</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 w-16">상태</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">작성일</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 w-24">작업</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($quotes)): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-sm text-gray-500">견적서가 없습니다.</p>
                                    <a href="create.php" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">+ 새 견적서 작성하기</a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($quotes as $idx => $q):
                                $rowNum = $totalItems - (($currentPage - 1) * $perPage) - $idx;
                                $st = $statusMap[$q['status']] ?? ['label' => $q['status'], 'cls' => 'bg-gray-100 text-gray-600'];
                                $dt = $docTypeMap[$q['doc_type']] ?? ['label' => $q['doc_type'], 'cls' => 'bg-gray-50 text-gray-500'];
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-1 text-center text-xs text-gray-400"><?php echo $rowNum; ?></td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <span class="text-xs font-mono font-semibold text-gray-800"><?php echo htmlspecialchars($q['quote_no']); ?></span>
                                </td>
                                <td class="px-2 py-1 text-center">
                                    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded <?php echo $dt['cls']; ?>"><?php echo $dt['label']; ?></span>
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-700"><?php echo htmlspecialchars($q['customer_name'] ?? '-'); ?></td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500"><?php echo htmlspecialchars($q['customer_company'] ?? '-'); ?></td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-right font-semibold text-gray-800">
                                    <?php echo number_format($q['grand_total']); ?><span class="text-gray-400 font-normal">원</span>
                                </td>
                                <td class="px-2 py-1 text-center">
                                    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full <?php echo $st['cls']; ?>"><?php echo $st['label']; ?></span>
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500">
                                    <?php echo date('Y-m-d H:i', strtotime($q['created_at'])); ?>
                                </td>
                                <td class="px-2 py-1 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="detail.php?id=<?php echo $q['id']; ?>" title="상세보기"
                                           class="p-1 rounded hover:bg-gray-100 text-gray-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="create.php?id=<?php echo $q['id']; ?>" title="수정"
                                           class="p-1 rounded hover:bg-blue-50 text-blue-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button onclick="deleteQuote(<?php echo $q['id']; ?>, '<?php echo htmlspecialchars($q['quote_no']); ?>')" title="삭제"
                                                class="p-1 rounded hover:bg-red-50 text-red-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <!-- 페이지네이션 -->
            <div class="px-3 py-1.5 border-t border-gray-200 flex items-center justify-between text-xs">
                <span class="text-gray-500">총 <strong><?php echo number_format($totalItems); ?></strong>건</span>
                <div class="flex items-center gap-1">
                    <?php
                    $baseUrl = '?' . http_build_query(array_filter([
                        'status' => $status, 'doc_type' => $docType, 'search' => $search,
                        'date_from' => $dateFrom, 'date_to' => $dateTo,
                    ]));
                    $btnNav = 'text-xs rounded border px-2 py-1 transition-colors border-gray-300 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed';
                    $btnActive = 'text-xs rounded border px-2.5 py-1 border-blue-600 bg-blue-600 text-white font-medium';
                    $btnNormal = 'text-xs rounded border px-2.5 py-1 border-gray-300 text-gray-700 hover:bg-gray-50';
                    ?>
                    <a href="<?php echo $baseUrl . '&page=1'; ?>" class="<?php echo $btnNav; ?> <?php echo $currentPage <= 1 ? 'pointer-events-none opacity-40' : ''; ?>">«</a>
                    <a href="<?php echo $baseUrl . '&page=' . max(1, $currentPage - 1); ?>" class="<?php echo $btnNav; ?> <?php echo $currentPage <= 1 ? 'pointer-events-none opacity-40' : ''; ?>">‹</a>

                    <?php
                    $delta = 2;
                    $startP = max(1, $currentPage - $delta);
                    $endP = min($totalPages, $currentPage + $delta);
                    if ($startP > 1) { echo '<a href="' . $baseUrl . '&page=1" class="' . $btnNormal . '">1</a>'; if ($startP > 2) echo '<span class="px-1 text-gray-400">…</span>'; }
                    for ($p = $startP; $p <= $endP; $p++):
                    ?>
                        <a href="<?php echo $baseUrl . '&page=' . $p; ?>" class="<?php echo $p === $currentPage ? $btnActive : $btnNormal; ?>"><?php echo $p; ?></a>
                    <?php endfor;
                    if ($endP < $totalPages) { if ($endP < $totalPages - 1) echo '<span class="px-1 text-gray-400">…</span>'; echo '<a href="' . $baseUrl . '&page=' . $totalPages . '" class="' . $btnNormal . '">' . $totalPages . '</a>'; }
                    ?>

                    <a href="<?php echo $baseUrl . '&page=' . min($totalPages, $currentPage + 1); ?>" class="<?php echo $btnNav; ?> <?php echo $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : ''; ?>">›</a>
                    <a href="<?php echo $baseUrl . '&page=' . $totalPages; ?>" class="<?php echo $btnNav; ?> <?php echo $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : ''; ?>">»</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<script>
function applyFilters() {
    var params = new URLSearchParams();
    var s = document.getElementById('statusFilter').value;
    var d = document.getElementById('docTypeFilter').value;
    var q = document.getElementById('searchInput').value.trim();
    var df = document.getElementById('dateFrom').value;
    var dt = document.getElementById('dateTo').value;
    if (s) params.set('status', s);
    if (d) params.set('doc_type', d);
    if (q) params.set('search', q);
    if (df) params.set('date_from', df);
    if (dt) params.set('date_to', dt);
    location.href = '?' + params.toString();
}

document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') applyFilters();
});

function deleteQuote(id, quoteNo) {
    if (!confirm('견적서 [' + quoteNo + ']을(를) 삭제하시겠습니까?')) return;
    fetch('/api/quote-engine/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('삭제 완료', 'success');
            location.reload();
        } else {
            showToast(data.error || '삭제 실패', 'error');
        }
    })
    .catch(function() { showToast('서버 오류', 'error'); });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
