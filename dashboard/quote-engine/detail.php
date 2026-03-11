<?php
/**
 * 견적엔진 — 견적서/거래명세서 상세 보기
 * /dashboard/quote-engine/detail.php
 * ?id=N 으로 조회
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../includes/quote-engine/QuoteEngine.php';

$_SERVER['REQUEST_URI'] = '/dashboard/quote-engine/';

$quoteId = intval($_GET['id'] ?? 0);
if (!$quoteId) { header('Location: index.php'); exit; }

$engine = new QE_QuoteEngine($db);
$quote = $engine->getQuote($quoteId);
if (!$quote) { header('Location: index.php'); exit; }

$items = $quote['items'] ?? [];
$productItems = array_filter($items, function($i) { return ($i['item_type'] ?? '') === 'product'; });
$manualItems  = array_filter($items, function($i) { return ($i['item_type'] ?? '') === 'manual'; });
$extraItems   = array_filter($items, function($i) { return ($i['item_type'] ?? '') === 'extra'; });

// ─── 상태/유형 맵 ──────────────────────────────────────────
$statusMap = [
    'draft'     => ['label' => '임시저장', 'cls' => 'bg-gray-100 text-gray-700'],
    'completed' => ['label' => '완료',     'cls' => 'bg-blue-100 text-blue-700'],
    'sent'      => ['label' => '발송',     'cls' => 'bg-green-100 text-green-700'],
    'expired'   => ['label' => '만료',     'cls' => 'bg-red-100 text-red-700'],
];
$docTypeMap = [
    'quotation'   => ['label' => '견적서',     'title' => '견적서 상세'],
    'transaction' => ['label' => '거래명세서', 'title' => '거래명세서 상세'],
];

$st = $statusMap[$quote['status']] ?? ['label' => $quote['status'], 'cls' => 'bg-gray-100 text-gray-600'];
$dt = $docTypeMap[$quote['doc_type']] ?? ['label' => $quote['doc_type'], 'title' => '문서 상세'];

$itemTypeBadges = [
    'product' => '<span class="px-1.5 py-0.5 text-[9px] font-semibold rounded bg-blue-50 text-blue-600">품목계산</span>',
    'manual'  => '<span class="px-1.5 py-0.5 text-[9px] font-semibold rounded bg-green-50 text-green-600">수동입력</span>',
    'extra'   => '<span class="px-1.5 py-0.5 text-[9px] font-semibold rounded bg-orange-50 text-orange-600">부가항목</span>',
];

$extraLabels = [
    'shipping' => '택배비', 'design' => '디자인비', 'rush' => '급행료',
    'processing' => '가공비', 'packing' => '포장비', 'other' => '기타',
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<style>
@media print {
    nav, aside, .no-print, .sidebar, .mini-chat-widget { display: none !important; }
    main { margin: 0 !important; padding: 0 !important; }
    body { background: white !important; }
    .print-only { display: block; }
    .shadow { box-shadow: none; }
}
</style>

<main class="flex-1 min-h-0 bg-gray-50 overflow-y-auto">
<div class="max-w-5xl mx-auto px-4 py-2">

<!-- 헤더 -->
<div class="flex items-center gap-2 mb-2 no-print">
    <a href="index.php" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($dt['title']); ?></h1>
    <span class="px-2 py-0.5 text-xs font-mono font-bold rounded bg-gray-100 text-gray-600"><?php echo htmlspecialchars($quote['quote_no']); ?></span>
    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full <?php echo $st['cls']; ?>"><?php echo $st['label']; ?></span>
    <div class="ml-auto flex gap-1.5">
        <a href="create.php?id=<?php echo $quoteId; ?>" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            수정
        </a>
        <button onclick="window.print()" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            인쇄
        </button>
        <a href="/api/quote-engine/pdf.php?id=<?php echo $quoteId; ?>" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1" target="_blank">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            PDF
        </a>
        <button onclick="sendEmail()" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            이메일
        </button>
        <button onclick="deleteQuote()" class="px-3 py-1 text-xs border border-red-200 rounded text-red-500 hover:bg-red-50 transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            삭제
        </button>
    </div>
</div>

<!-- 문서 정보 + 고객 정보 (2열) -->
<div class="grid grid-cols-2 gap-2 mb-2">
    <!-- 문서 정보 -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg text-white" style="background:#1E4E79;">문서 정보</div>
        <div class="px-3 py-2 space-y-1.5">
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">문서번호</span>
                <span class="text-xs font-mono font-semibold text-gray-800"><?php echo htmlspecialchars($quote['quote_no']); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">문서유형</span>
                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded <?php
                    $dtBadge = $docTypeMap[$quote['doc_type']] ?? ['label' => $quote['doc_type']];
                    echo ($quote['doc_type'] === 'transaction') ? 'bg-purple-50 text-purple-600 border border-purple-200' : 'bg-blue-50 text-blue-600 border border-blue-200';
                ?>"><?php echo htmlspecialchars($dtBadge['label']); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">작성일</span>
                <span class="text-xs text-gray-800"><?php echo date('Y-m-d', strtotime($quote['created_at'])); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">유효기간</span>
                <span class="text-xs text-gray-800"><?php
                    $validDays = intval($quote['valid_days'] ?? 0);
                    $validUntil = $quote['valid_until'] ?? '';
                    echo $validDays . '일';
                    if ($validUntil) echo ' (' . date('Y-m-d', strtotime($validUntil)) . '까지)';
                ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">결제조건</span>
                <span class="text-xs text-gray-800"><?php echo htmlspecialchars($quote['payment_terms'] ?? '-'); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">상태</span>
                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full <?php echo $st['cls']; ?>"><?php echo $st['label']; ?></span>
            </div>
        </div>
    </div>

    <!-- 고객 정보 -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg text-white" style="background:#1E4E79;">고객 정보</div>
        <div class="px-3 py-2 space-y-1.5">
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">회사명</span>
                <span class="text-xs text-gray-800 font-medium"><?php echo htmlspecialchars($quote['customer_company'] ?? '-'); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">담당자</span>
                <span class="text-xs text-gray-800"><?php echo htmlspecialchars($quote['customer_name'] ?? '-'); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">전화</span>
                <span class="text-xs text-gray-800"><?php echo htmlspecialchars($quote['customer_phone'] ?? '-'); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">이메일</span>
                <span class="text-xs text-gray-800"><?php echo htmlspecialchars($quote['customer_email'] ?? '-'); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">주소</span>
                <span class="text-xs text-gray-800"><?php echo htmlspecialchars($quote['customer_address'] ?? '-'); ?></span>
            </div>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 w-20 flex-shrink-0">사업자번호</span>
                <span class="text-xs text-gray-800"><?php echo htmlspecialchars($quote['customer_biz_no'] ?? '-'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- 품목 목록 -->
<div class="bg-white rounded-lg shadow mb-2">
    <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg text-white" style="background:#1E4E79;">
        <span>품목 목록</span>
        <span class="ml-2 px-1.5 py-0.5 text-[10px] bg-white/20 rounded"><?php echo count($items); ?>건</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-1.5 text-center text-[10px] font-medium text-gray-500 w-8">NO</th>
                    <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500 w-16">구분</th>
                    <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500">품목명</th>
                    <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500">사양</th>
                    <th class="px-2 py-1.5 text-right text-[10px] font-medium text-gray-500 w-16">수량</th>
                    <th class="px-2 py-1.5 text-center text-[10px] font-medium text-gray-500 w-10">단위</th>
                    <th class="px-2 py-1.5 text-right text-[10px] font-medium text-gray-500 w-20">단가</th>
                    <th class="px-2 py-1.5 text-right text-[10px] font-medium text-gray-500 w-24">공급가액</th>
                    <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500 w-24">비고</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="9" class="px-4 py-6 text-center text-xs text-gray-400">등록된 품목이 없습니다.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($items as $idx => $item):
                        $badge = $itemTypeBadges[$item['item_type']] ?? '';
                        $pName = htmlspecialchars($item['product_name'] ?? '-');
                        if ($item['item_type'] === 'extra' && !empty($item['extra_category'])) {
                            $pName = htmlspecialchars($extraLabels[$item['extra_category']] ?? $item['product_name'] ?? '-');
                        }
                        $spec = htmlspecialchars($item['specification'] ?? '-');
                        $qty = floatval($item['quantity'] ?? 0);
                        $qtyDisplay = ($qty == intval($qty)) ? number_format($qty) : rtrim(rtrim(number_format($qty, 2), '0'), '.');
                        $unitPrice = intval($item['unit_price'] ?? 0);
                        $supplyPrice = intval($item['supply_price'] ?? 0);
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-2 py-1 text-center text-xs text-gray-400"><?php echo $idx + 1; ?></td>
                        <td class="px-2 py-1"><?php echo $badge; ?></td>
                        <td class="px-2 py-1 text-xs font-medium text-gray-800"><?php echo $pName; ?></td>
                        <td class="px-2 py-1 text-xs text-gray-500" title="<?php echo $spec; ?>"><?php echo mb_strlen($spec) > 40 ? mb_substr($spec, 0, 40) . '…' : $spec; ?></td>
                        <td class="px-2 py-1 text-xs text-right text-gray-700"><?php echo $qtyDisplay; ?></td>
                        <td class="px-2 py-1 text-xs text-center text-gray-500"><?php echo htmlspecialchars($item['unit'] ?? '-'); ?></td>
                        <td class="px-2 py-1 text-xs text-right text-gray-600"><?php echo $unitPrice > 0 ? number_format($unitPrice) : '-'; ?></td>
                        <td class="px-2 py-1 text-xs text-right font-semibold text-gray-800"><?php echo number_format($supplyPrice); ?><span class="text-gray-400 font-normal">원</span></td>
                        <td class="px-2 py-1 text-xs text-gray-500"><?php echo htmlspecialchars($item['note'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 합계 -->
    <div class="px-3 py-2 border-t bg-gray-50 rounded-b-lg">
        <div class="flex justify-end">
            <div class="w-64 space-y-0.5">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>공급가액</span>
                    <span class="font-semibold text-gray-800"><?php echo number_format(intval($quote['supply_total'] ?? 0)); ?>원</span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>부가세 (10%)</span>
                    <span class="font-semibold text-gray-800"><?php echo number_format(intval($quote['vat_total'] ?? 0)); ?>원</span>
                </div>
                <?php if (intval($quote['discount_amount'] ?? 0) > 0): ?>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>할인<?php if (!empty($quote['discount_reason'])) echo ' (' . htmlspecialchars($quote['discount_reason']) . ')'; ?></span>
                    <span class="font-semibold text-red-500">-<?php echo number_format(intval($quote['discount_amount'])); ?>원</span>
                </div>
                <?php endif; ?>
                <div class="border-t border-gray-200 pt-1 mt-1 flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-900">합계</span>
                    <span class="text-sm font-bold" style="color:#1E4E79;"><?php echo number_format(intval($quote['grand_total'] ?? 0)); ?>원</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 메모 -->
<?php if (!empty($quote['customer_memo']) || !empty($quote['admin_memo'])): ?>
<div class="bg-white rounded-lg shadow mb-2">
    <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg text-white" style="background:#1E4E79;">메모</div>
    <div class="px-3 py-2 space-y-2">
        <?php if (!empty($quote['customer_memo'])): ?>
        <div>
            <div class="text-[10px] font-semibold text-gray-500 mb-0.5">고객 전달사항</div>
            <div class="text-xs text-gray-700 bg-gray-50 rounded px-2 py-1.5 whitespace-pre-wrap"><?php echo htmlspecialchars($quote['customer_memo']); ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['admin_memo'])): ?>
        <div class="no-print">
            <div class="text-[10px] font-semibold text-gray-500 mb-0.5">관리자 메모 <span class="text-orange-500">(내부용)</span></div>
            <div class="text-xs text-gray-700 bg-yellow-50 border border-yellow-200 rounded px-2 py-1.5 whitespace-pre-wrap"><?php echo htmlspecialchars($quote['admin_memo']); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- 상태 변경 / 변환 버튼 -->
<div class="flex items-center justify-between mb-4 no-print">
    <div class="flex gap-2">
        <?php if ($quote['status'] === 'draft'): ?>
        <button onclick="changeStatus('completed')" class="px-4 py-1.5 text-xs font-semibold text-white rounded hover:opacity-90 transition-colors" style="background:#1E4E79;">
            ✓ 완료 처리
        </button>
        <?php endif; ?>
        <?php if ($quote['status'] === 'completed'): ?>
        <button onclick="changeStatus('sent')" class="px-4 py-1.5 text-xs font-semibold text-white rounded hover:opacity-90 transition-colors bg-green-600">
            📧 발송 완료
        </button>
        <?php endif; ?>
        <?php if ($quote['doc_type'] === 'quotation'): ?>
        <button onclick="convertToTransaction()" class="px-4 py-1.5 text-xs font-semibold text-white rounded hover:opacity-90 transition-colors bg-purple-600">
            📋 거래명세서로 변환
        </button>
        <?php endif; ?>
    </div>
    <div class="text-[10px] text-gray-400">
        <?php if (!empty($quote['sent_at'])): ?>
        발송일: <?php echo date('Y-m-d H:i', strtotime($quote['sent_at'])); ?> |
        <?php endif; ?>
        수정일: <?php echo date('Y-m-d H:i', strtotime($quote['updated_at'] ?? $quote['created_at'])); ?>
    </div>
</div>

</div>
</main>

<script>
var QUOTE_ID = <?php echo intval($quoteId); ?>;

function changeStatus(newStatus) {
    if (!confirm('상태를 변경하시겠습니까?')) return;
    fetch('/api/quote-engine/status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: QUOTE_ID, action: 'status', status: newStatus })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.reload();
        else alert(data.error || '상태 변경 실패');
    })
    .catch(function() { alert('서버 오류'); });
}

function convertToTransaction() {
    if (!confirm('이 견적서를 거래명세서로 변환하시겠습니까?\n원본 견적서는 유지됩니다.')) return;
    fetch('/api/quote-engine/status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: QUOTE_ID, action: 'convert' })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert('거래명세서 ' + (data.new_quote_no || '') + ' 생성 완료');
            location.href = 'detail.php?id=' + data.new_id;
        } else {
            alert(data.error || '변환 실패');
        }
    })
    .catch(function() { alert('서버 오류'); });
}

function deleteQuote() {
    if (!confirm('정말 삭제하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;
    fetch('/api/quote-engine/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: QUOTE_ID })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.href = 'index.php';
        else alert(data.error || '삭제 실패');
    })
    .catch(function() { alert('서버 오류'); });
}

function sendEmail() {
    var defaultTo = '<?php echo htmlspecialchars($quote["customer_email"] ?? ""); ?>';
    var to = prompt('발송할 이메일 주소를 입력하세요:', defaultTo);
    if (to === null) return;
    to = to.trim();
    if (!to) { alert('이메일 주소를 입력해주세요.'); return; }
    fetch('/api/quote-engine/email.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: QUOTE_ID, to: to })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert('이메일 발송 완료: ' + to);
            location.reload();
        } else {
            alert(data.error || '발송 실패');
        }
    })
    .catch(function() { alert('서버 오류'); });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
