<?php
/**
 * 관리자 견적서 수정 - Excel Style
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/mlangprintauto/login.php");
    exit;
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/includes/AdminQuoteManager.php';
require_once __DIR__ . '/includes/PriceHelper.php';

if (!$db) { die('DB 연결 실패'); }
mysqli_set_charset($db, 'utf8mb4');

$quoteManager = new AdminQuoteManager($db);
$quoteId = intval($_GET['id'] ?? 0);
if ($quoteId <= 0) { header("Location: index.php"); exit; }

$quote = $quoteManager->getQuote($quoteId);
if (!$quote) { header("Location: index.php"); exit; }

$quoteItems = $quoteManager->getQuoteItems($quoteId);
$unitOptions = ['매', '연', '부', '권', '개', '장', '식'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 수정 - <?php echo htmlspecialchars($quote['quote_no']); ?></title>
    <link rel="stylesheet" href="assets/excel-style.css">
</head>
<body>
<div class="container">
    <div class="page-header">
        <div>
            <h1>견적서 수정 <span class="quote-no"><?php echo htmlspecialchars($quote['quote_no']); ?></span></h1>
        </div>
        <div class="action-bar">
            <a href="detail.php?id=<?php echo $quoteId; ?>" class="back-link">← 취소</a>
            <button onclick="saveQuote()" class="btn btn-primary">저장</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">고객 정보</div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">회사명</label>
                    <input type="text" id="customer_company" class="form-input" value="<?php echo htmlspecialchars($quote['customer_company'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">담당자명 <span class="required">*</span></label>
                    <input type="text" id="customer_name" class="form-input" value="<?php echo htmlspecialchars($quote['customer_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">연락처</label>
                    <input type="tel" id="customer_phone" class="form-input" value="<?php echo htmlspecialchars($quote['customer_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">이메일</label>
                    <input type="email" id="customer_email" class="form-input" value="<?php echo htmlspecialchars($quote['customer_email'] ?? ''); ?>">
                </div>
                <div class="form-group full">
                    <label class="form-label">주소</label>
                    <input type="text" id="customer_address" class="form-input" value="<?php echo htmlspecialchars($quote['customer_address'] ?? ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>품목 목록</span>
            <button onclick="openManualModal()" class="btn btn-primary btn-sm">+ 수동</button>
        </div>
        <table class="excel-table" id="itemsTable">
            <thead>
                <tr>
                    <th style="width:40px">NO</th>
                    <th style="width:100px">품목</th>
                    <th>규격/옵션</th>
                    <th style="width:80px">수량</th>
                    <th style="width:80px">단가</th>
                    <th style="width:100px">공급가액</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody id="itemsBody"></tbody>
        </table>
        <div class="totals-section" style="padding:10px;">
            <div class="totals-row"><span class="totals-label">공급가액</span><span class="totals-value" id="supplyTotal">0</span></div>
            <div class="totals-row"><span class="totals-label">부가세</span><span class="totals-value" id="vatTotal">0</span></div>
            <div class="totals-row grand"><span class="totals-label">총액</span><span class="totals-value" id="grandTotal">0</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">메모</div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">고객 요청사항</label>
                    <textarea id="customer_memo" class="form-input"><?php echo htmlspecialchars($quote['customer_memo'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">관리자 메모</label>
                    <textarea id="admin_memo" class="form-input"><?php echo htmlspecialchars($quote['admin_memo'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="manualModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>수동 품목 추가</h3>
            <button class="modal-close" onclick="closeManualModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">품목명 <span class="required">*</span></label>
                <input type="text" id="manual_product_name" class="form-input" placeholder="예: 스티커, 전단지">
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label class="form-label">규격/설명</label>
                <textarea id="manual_specification" class="form-input" placeholder="예: 아트지유광 / 60x50mm"></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 60px; gap:8px; margin-top:10px;">
                <div class="form-group">
                    <label class="form-label">수량 <span class="required">*</span></label>
                    <input type="number" id="manual_quantity" class="form-input" value="1" min="0.1" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">단위</label>
                    <select id="manual_unit" class="form-input">
                        <?php foreach ($unitOptions as $u): ?><option value="<?php echo $u; ?>"><?php echo $u; ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label class="form-label">공급가액 <span class="required">*</span></label>
                <input type="number" id="manual_supply_price" class="form-input" placeholder="0" min="0">
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeManualModal()" class="btn">취소</button>
            <button onclick="addManualItem()" class="btn btn-primary">추가</button>
        </div>
    </div>
</div>

<script>
const quoteId = <?php echo $quoteId; ?>;
let items = <?php echo json_encode(array_map(function($item) {
    return [
        'id' => $item['id'],
        'is_manual' => ($item['source_type'] === 'manual') ? 1 : 0,
        'product_name' => $item['product_name'],
        'specification' => $item['specification'] ?? '',
        'quantity' => floatval($item['quantity']),
        'unit' => $item['unit'] ?? '개',
        'quantity_display' => $item['quantity_display'] ?? '',
        'unit_price' => floatval($item['unit_price']),
        'supply_price' => intval($item['supply_price']),
        'product_type' => $item['product_type'] ?? '',
        'source_data' => $item['source_data']
    ];
}, $quoteItems), JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('DOMContentLoaded', renderItems);

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function renderItems() {
    const tbody = document.getElementById('itemsBody');
    while(tbody.firstChild) tbody.removeChild(tbody.firstChild);

    if (items.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 7;
        cell.className = 'text-center';
        cell.style.cssText = 'padding:30px;color:#888;';
        cell.textContent = '품목을 추가해주세요.';
        row.appendChild(cell);
        tbody.appendChild(row);
        updateTotals();
        return;
    }

    items.forEach((item, i) => {
        const up = item.quantity > 0 ? Math.round(item.supply_price / item.quantity) : 0;
        const qd = item.quantity_display || (formatNumber(item.quantity) + item.unit);

        const row = document.createElement('tr');

        const c1 = document.createElement('td'); c1.className='text-center'; c1.textContent = i+1;
        const c2 = document.createElement('td'); c2.textContent = item.product_name;
        const c3 = document.createElement('td'); c3.textContent = item.specification || '-';
        const c4 = document.createElement('td'); c4.className='text-center'; c4.textContent = qd;
        const c5 = document.createElement('td'); c5.className='text-right'; c5.textContent = formatNumber(up);
        const c6 = document.createElement('td'); c6.className='text-right'; c6.textContent = formatNumber(item.supply_price);
        const c7 = document.createElement('td'); c7.className='text-center';
        const btn = document.createElement('button'); btn.className='delete-btn'; btn.textContent='×';
        btn.onclick = function(){ deleteItem(i); };
        c7.appendChild(btn);

        row.appendChild(c1); row.appendChild(c2); row.appendChild(c3);
        row.appendChild(c4); row.appendChild(c5); row.appendChild(c6); row.appendChild(c7);
        tbody.appendChild(row);
    });
    updateTotals();
}

function updateTotals() {
    const s = items.reduce((sum, x) => sum + (x.supply_price || 0), 0);
    const v = Math.round(s * 0.1);
    document.getElementById('supplyTotal').textContent = formatNumber(s);
    document.getElementById('vatTotal').textContent = formatNumber(v);
    document.getElementById('grandTotal').textContent = formatNumber(s + v);
}

function openManualModal() { document.getElementById('manualModal').classList.add('active'); document.getElementById('manual_product_name').focus(); }
function closeManualModal() {
    document.getElementById('manualModal').classList.remove('active');
    document.getElementById('manual_product_name').value = '';
    document.getElementById('manual_specification').value = '';
    document.getElementById('manual_quantity').value = '1';
    document.getElementById('manual_unit').value = '개';
    document.getElementById('manual_supply_price').value = '';
}

function addManualItem() {
    const name = document.getElementById('manual_product_name').value.trim();
    const spec = document.getElementById('manual_specification').value.trim();
    const qty = parseFloat(document.getElementById('manual_quantity').value) || 1;
    const unit = document.getElementById('manual_unit').value;
    const price = parseInt(document.getElementById('manual_supply_price').value) || 0;
    if (!name) { alert('품목명을 입력해주세요.'); return; }
    if (price <= 0) { alert('공급가액을 입력해주세요.'); return; }
    items.push({id:null, is_manual:1, product_name:name, specification:spec, quantity:qty, unit:unit, quantity_display:formatNumber(qty)+unit, unit_price:Math.round(price/qty), supply_price:price, product_type:'', source_data:null});
    renderItems(); closeManualModal();
}

function deleteItem(i) { if (!confirm('삭제하시겠습니까?')) return; items.splice(i, 1); renderItems(); }

function saveQuote() {
    const name = document.getElementById('customer_name').value.trim();
    if (!name) { alert('담당자명을 입력해주세요.'); document.getElementById('customer_name').focus(); return; }
    if (items.length === 0) { alert('품목을 추가해주세요.'); return; }

    const data = {
        quote_id: quoteId,
        customer_company: document.getElementById('customer_company').value.trim(),
        customer_name: name,
        customer_phone: document.getElementById('customer_phone').value.trim(),
        customer_email: document.getElementById('customer_email').value.trim(),
        customer_address: document.getElementById('customer_address').value.trim(),
        customer_memo: document.getElementById('customer_memo').value.trim(),
        admin_memo: document.getElementById('admin_memo').value.trim(),
        items: items.map(x => ({source_type:x.is_manual?'manual':'calculator', product_type:x.product_type||'', product_name:x.product_name, specification:x.specification, quantity:x.quantity, unit:x.unit, quantity_display:x.quantity_display, unit_price:x.unit_price, supply_price:x.supply_price, source_data:x.source_data}))
    };

    fetch('api/update.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)})
    .then(r=>r.json()).then(d=>{ if(d.success){alert('저장됨');location.href='detail.php?id='+quoteId;}else alert('실패: '+d.message); })
    .catch(e=>alert('오류: '+e.message));
}

function formatNumber(n) { const v = parseFloat(n); return isNaN(v) ? '0' : v.toLocaleString('ko-KR', {maximumFractionDigits:1}); }

(function() {
    if (!window.opener) return;
    function fitWindow() {
        var body = document.body, html = document.documentElement;
        var contentH = Math.max(body.scrollHeight, body.offsetHeight, html.scrollHeight);
        var contentW = Math.max(body.scrollWidth, body.offsetWidth, html.scrollWidth);
        var chromeH = window.outerHeight - window.innerHeight;
        var chromeW = window.outerWidth - window.innerWidth;
        var targetW = Math.min(Math.max(contentW + chromeW + 60, 900), screen.availWidth - 40);
        var targetH = Math.min(contentH + chromeH + 60, screen.availHeight - 40);
        window.resizeTo(targetW, targetH);
        var left = Math.round((screen.availWidth - targetW) / 2);
        var top = Math.round((screen.availHeight - targetH) / 2);
        window.moveTo(Math.max(0, left), Math.max(0, top));
    }
    if (document.readyState === 'complete') fitWindow();
    else window.addEventListener('load', fitWindow);
})();
</script>
</body>
</html>
