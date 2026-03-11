<?php
/**
 * 견적엔진 — 거래처 관리
 * 경로: /dashboard/quote-engine/customers.php
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../includes/quote-engine/CustomerManager.php';

$_SERVER['REQUEST_URI'] = '/dashboard/quote-engine/';

$cm = new QE_CustomerManager($db);

// Simple pagination
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$search  = trim($_GET['search'] ?? '');

$result      = $cm->listAll($page, $perPage, $search);
$customers   = $result['items'];
$totalItems  = $result['total'];
$totalPages  = $result['pages'];
$currentPage = $result['page'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 min-h-0 bg-gray-50 overflow-y-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">

        <!-- 헤더 + 검색 한 줄 -->
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <h1 class="text-lg font-bold text-gray-900 mr-1">거래처 관리</h1>
            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700"><?php echo number_format($totalItems); ?>건</span>

            <div class="flex-1 min-w-[160px]">
                <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="회사명, 담당자, 전화번호, 이메일"
                       class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <button onclick="applySearch()" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">검색</button>

            <button onclick="openModal()" class="ml-auto px-3 py-1 text-xs font-semibold text-white rounded hover:opacity-90 transition-colors flex items-center gap-1"
                    style="background:#1E4E79;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                새 거래처
            </button>
        </div>

        <!-- 테이블 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 w-10">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">회사명</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">담당자</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">전화번호</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">이메일</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">사업자번호</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 w-16">이용횟수</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">최근이용</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500 w-20">작업</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="text-sm text-gray-500"><?php echo $search !== '' ? '검색 결과가 없습니다.' : '등록된 거래처가 없습니다.'; ?></p>
                                    <button onclick="openModal()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">+ 새 거래처 등록하기</button>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $idx => $c):
                                $rowNum = $totalItems - (($currentPage - 1) * $perPage) - $idx;
                                $lastUsed = $c['last_used_at'] ? date('Y-m-d', strtotime($c['last_used_at'])) : '-';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="openModal(<?php echo $c['id']; ?>)">
                                <td class="px-2 py-1 text-center text-xs text-gray-400"><?php echo $rowNum; ?></td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs font-semibold text-gray-800"><?php echo htmlspecialchars($c['company'] ?? '-'); ?></td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-700"><?php echo htmlspecialchars($c['name']); ?></td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500"><?php echo htmlspecialchars($c['phone'] ?? '-'); ?></td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500"><?php echo htmlspecialchars($c['email'] ?? '-'); ?></td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500"><?php echo htmlspecialchars($c['business_number'] ?? '-'); ?></td>
                                <td class="px-2 py-1 text-center">
                                    <?php if ($c['use_count'] > 0): ?>
                                    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-700"><?php echo number_format($c['use_count']); ?>회</span>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500"><?php echo $lastUsed; ?></td>
                                <td class="px-2 py-1 text-center" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="openModal(<?php echo $c['id']; ?>)" title="수정"
                                                class="p-1 rounded hover:bg-blue-50 text-blue-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button onclick="deleteCustomer(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars(addslashes($c['name'])); ?>')" title="삭제"
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
                    $baseUrl = '?' . http_build_query(array_filter(['search' => $search]));
                    $btnNav    = 'text-xs rounded border px-2 py-1 transition-colors border-gray-300 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed';
                    $btnActive = 'text-xs rounded border px-2.5 py-1 border-blue-600 bg-blue-600 text-white font-medium';
                    $btnNormal = 'text-xs rounded border px-2.5 py-1 border-gray-300 text-gray-700 hover:bg-gray-50';
                    ?>
                    <a href="<?php echo $baseUrl . '&page=1'; ?>" class="<?php echo $btnNav; ?> <?php echo $currentPage <= 1 ? 'pointer-events-none opacity-40' : ''; ?>">«</a>
                    <a href="<?php echo $baseUrl . '&page=' . max(1, $currentPage - 1); ?>" class="<?php echo $btnNav; ?> <?php echo $currentPage <= 1 ? 'pointer-events-none opacity-40' : ''; ?>">‹</a>

                    <?php
                    $delta  = 2;
                    $startP = max(1, $currentPage - $delta);
                    $endP   = min($totalPages, $currentPage + $delta);
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

<!-- 거래처 추가/수정 모달 -->
<div id="customerModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200" style="background:#1E4E79; border-radius: 8px 8px 0 0;">
            <h3 id="modalTitle" class="text-sm font-bold text-white">새 거래처</h3>
            <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="customerForm" onsubmit="return saveCustomer(event)">
            <input type="hidden" id="customerId" value="">
            <div class="px-4 py-3 space-y-2.5 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-0.5">회사명</label>
                        <input type="text" id="fldCompany" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="(주)두손기획">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-0.5">담당자명 <span class="text-red-500">*</span></label>
                        <input type="text" id="fldName" required class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="홍길동">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-0.5">전화번호</label>
                        <input type="tel" id="fldPhone" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="010-1234-5678">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-0.5">이메일</label>
                        <input type="email" id="fldEmail" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="email@example.com">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-0.5">사업자번호</label>
                    <input type="text" id="fldBizNo" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="000-00-00000">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-0.5">주소</label>
                    <input type="text" id="fldAddress" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="서울시 영등포구...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-0.5">메모</label>
                    <textarea id="fldMemo" rows="2" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="참고 사항"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 px-4 py-2.5 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                <button type="button" onclick="closeModal()" class="px-3 py-1.5 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-100 transition-colors">취소</button>
                <button type="submit" id="btnSave" class="px-4 py-1.5 text-xs font-semibold text-white rounded hover:opacity-90 transition-colors" style="background:#1E4E79;">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Search ──
function applySearch() {
    var params = new URLSearchParams();
    var q = document.getElementById('searchInput').value.trim();
    if (q) params.set('search', q);
    location.href = '?' + params.toString();
}

document.getElementById('searchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') applySearch();
});

// ── Modal ──
function openModal(id) {
    var modal = document.getElementById('customerModal');
    var title = document.getElementById('modalTitle');
    var form  = document.getElementById('customerForm');

    form.reset();
    document.getElementById('customerId').value = '';

    if (id) {
        title.textContent = '거래처 수정';
        loadCustomer(id);
    } else {
        title.textContent = '새 거래처';
    }

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('customerModal').classList.add('hidden');
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

function loadCustomer(id) {
    fetch('/api/quote-engine/customers.php?action=get&id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success || !data.data) {
            showToast('거래처 정보를 불러올 수 없습니다', 'error');
            closeModal();
            return;
        }
        var c = data.data;
        document.getElementById('customerId').value = c.id;
        document.getElementById('fldCompany').value  = c.company || '';
        document.getElementById('fldName').value     = c.name || '';
        document.getElementById('fldPhone').value    = c.phone || '';
        document.getElementById('fldEmail').value    = c.email || '';
        document.getElementById('fldBizNo').value    = c.business_number || '';
        document.getElementById('fldAddress').value  = c.address || '';
        document.getElementById('fldMemo').value     = c.memo || '';

        // Apply phone formatting if available
        var phoneEl = document.getElementById('fldPhone');
        if (window.applyPhoneFormat) applyPhoneFormat(phoneEl);
    })
    .catch(function() {
        showToast('서버 오류', 'error');
        closeModal();
    });
}

// ── Save / Update ──
function saveCustomer(e) {
    e.preventDefault();

    var id = document.getElementById('customerId').value;
    var formData = {
        company:         document.getElementById('fldCompany').value.trim(),
        name:            document.getElementById('fldName').value.trim(),
        phone:           document.getElementById('fldPhone').value.trim(),
        email:           document.getElementById('fldEmail').value.trim(),
        business_number: document.getElementById('fldBizNo').value.trim(),
        address:         document.getElementById('fldAddress').value.trim(),
        memo:            document.getElementById('fldMemo').value.trim()
    };

    if (!formData.name) {
        showToast('담당자명은 필수입니다', 'warning');
        document.getElementById('fldName').focus();
        return false;
    }

    var action = id ? 'update' : 'save';
    var body = id ? Object.assign({ id: parseInt(id) }, formData) : formData;

    var btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.textContent = '저장 중...';

    fetch('/api/quote-engine/customers.php?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast(id ? '거래처가 수정되었습니다' : '거래처가 등록되었습니다', 'success');
            closeModal();
            location.reload();
        } else {
            showToast(data.error || '저장 실패', 'error');
        }
    })
    .catch(function() {
        showToast('서버 오류', 'error');
    })
    .then(function() {
        btn.disabled = false;
        btn.textContent = '저장';
    });

    return false;
}

// ── Delete ──
function deleteCustomer(id, name) {
    if (!confirm('[' + name + '] 거래처를 삭제하시겠습니까?')) return;

    fetch('/api/quote-engine/customers.php?action=delete', {
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
