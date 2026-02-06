<?php
/**
 * 관리자 견적서 작성 - Excel Style
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
$adminSessionId = session_id();
$newQuoteNo = $quoteManager->generateQuoteNo();
$tempItems = $quoteManager->getTempItems($adminSessionId);
$unitOptions = ['매', '연', '부', '권', '개', '장', '식'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>새 견적서 작성</title>
    <link rel="stylesheet" href="assets/excel-style.css">
</head>
<body>
<div class="container">
    <div class="page-header">
        <div>
            <h1>새 견적서 작성 <span class="quote-no"><?php echo htmlspecialchars($newQuoteNo); ?></span></h1>
        </div>
        <div class="action-bar">
            <a href="index.php" class="back-link">← 취소</a>
            <button onclick="saveQuote(true)" class="btn">임시저장</button>
            <button onclick="saveQuote(false)" class="btn btn-primary">저장</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">고객 정보</div>
        <div class="card-body">
            <div class="form-inline-grid">
                <label class="form-label">회사명</label>
                <input type="text" id="customer_company" class="form-input" placeholder="회사명">
                <label class="form-label">담당자명 <span class="required">*</span></label>
                <input type="text" id="customer_name" class="form-input" placeholder="담당자명" required>
                <label class="form-label">연락처</label>
                <input type="tel" id="customer_phone" class="form-input" placeholder="010-0000-0000">
                <label class="form-label">이메일</label>
                <input type="email" id="customer_email" class="form-input" placeholder="email@example.com">
                <div class="full-row">
                    <label class="form-label">주소</label>
                    <input type="text" id="customer_address" class="form-input" placeholder="배송 주소">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>품목 목록</span>
            <div class="action-bar">
                <button onclick="openCalculatorSelect()" class="btn btn-primary btn-sm">계산기</button>
                <button onclick="openManualModal()" class="btn btn-sm">+ 수동</button>
            </div>
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
            <div class="totals-row">
                <span class="totals-label">공급가액</span>
                <span class="totals-value" id="supplyTotal">0</span>
            </div>
            <div class="totals-row">
                <span class="totals-label">부가세</span>
                <span class="totals-value" id="vatTotal">0</span>
            </div>
            <div class="totals-row grand">
                <span class="totals-label">총액</span>
                <span class="totals-value" id="grandTotal">0</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">메모</div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">고객 요청사항</label>
                    <textarea id="customer_memo" class="form-input" placeholder="고객이 요청한 내용"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">관리자 메모</label>
                    <textarea id="admin_memo" class="form-input" placeholder="내부 메모"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 수동 입력 모달 -->
<div class="modal" id="manualModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>수동 품목 추가</h3>
            <button class="modal-close" onclick="closeManualModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">품목명 <span class="required">*</span></label>
                <input type="text" id="manual_product_name" class="form-input" placeholder="예: 스티커, 전단지, 택배비">
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label class="form-label">규격/설명</label>
                <textarea id="manual_specification" class="form-input" placeholder="예: 아트지유광 / 60x50mm / 사각"></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 60px; gap:8px; margin-top:10px;">
                <div class="form-group">
                    <label class="form-label">수량 <span class="required">*</span></label>
                    <input type="number" id="manual_quantity" class="form-input" value="1" min="0.1" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">단위</label>
                    <select id="manual_unit" class="form-input">
                        <?php foreach ($unitOptions as $unit): ?>
                        <option value="<?php echo $unit; ?>"><?php echo $unit; ?></option>
                        <?php endforeach; ?>
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

<!-- 계산기 선택 모달 -->
<div class="modal" id="calcSelectModal">
    <div class="modal-content" style="max-width:380px;">
        <div class="modal-header">
            <h3>품목 계산기 선택</h3>
            <button class="modal-close" onclick="closeCalculatorSelect()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="calc-grid">
                <button class="calc-btn" onclick="openCalculator('sticker')"><div class="icon">🏷️</div><div class="name">스티커</div></button>
                <button class="calc-btn" onclick="openCalculator('inserted')"><div class="icon">📄</div><div class="name">전단지</div></button>
                <button class="calc-btn" onclick="openCalculator('namecard')"><div class="icon">💼</div><div class="name">명함</div></button>
                <button class="calc-btn" onclick="openCalculator('envelope')"><div class="icon">✉️</div><div class="name">봉투</div></button>
                <button class="calc-btn" onclick="openCalculator('ncrflambeau')"><div class="icon">📋</div><div class="name">NCR양식</div></button>
                <button class="calc-btn" onclick="openCalculator('cadarok')"><div class="icon">📚</div><div class="name">카다록</div></button>
                <button class="calc-btn" onclick="openCalculator('littleprint')"><div class="icon">🖼️</div><div class="name">포스터</div></button>
                <button class="calc-btn" onclick="openCalculator('msticker')"><div class="icon">🧲</div><div class="name">자석스티커</div></button>
                <button class="calc-btn" onclick="openCalculator('merchandisebond')"><div class="icon">🎫</div><div class="name">상품권</div></button>
            </div>
        </div>
    </div>
</div>

<!-- 계산기 iframe 모달 -->
<div class="calc-modal" id="calcIframeModal">
    <div class="calc-modal-content">
        <div class="calc-modal-header">
            <h3 id="calcModalTitle">계산기</h3>
            <button class="calc-modal-close" onclick="closeCalculatorIframe()">&times;</button>
        </div>
        <div class="calc-modal-body">
            <iframe id="calcIframe" src="about:blank"></iframe>
        </div>
        <div class="calc-modal-footer">계산기에서 옵션 선택 후 <strong>견적서에 적용</strong> 버튼 클릭</div>
    </div>
</div>

<script>
let items = <?php echo json_encode(array_map(function($item) {
    $isManual = !empty($item['is_manual']);
    return [
        'no' => $item['no'],
        'is_manual' => $isManual ? 1 : 0,
        'product_name' => $isManual ? ($item['manual_product_name'] ?? '') : PriceHelper::getProductTypeName($item['product_type'] ?? ''),
        'specification' => $isManual ? ($item['manual_specification'] ?? '') : ($item['specification'] ?? ''),
        'quantity' => $isManual ? floatval($item['manual_quantity'] ?? 1) : floatval($item['mesu'] ?? $item['MY_amount'] ?? 1),
        'unit' => $isManual ? ($item['manual_unit'] ?? '개') : PriceHelper::getDefaultUnit($item['product_type'] ?? ''),
        'quantity_display' => $item['quantity_display'] ?? '',
        'unit_price' => floatval($item['unit_price'] ?? 0),
        'supply_price' => $isManual ? intval($item['manual_supply_price'] ?? 0) : intval($item['st_price'] ?? 0),
        'product_type' => $item['product_type'] ?? '',
        'source_data' => $item
    ];
}, $tempItems), JSON_UNESCAPED_UNICODE); ?>;

const quoteNo = '<?php echo addslashes($newQuoteNo); ?>';

document.addEventListener('DOMContentLoaded', renderItems);

function renderItems() {
    const tbody = document.getElementById('itemsBody');
    tbody.innerHTML = '';

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center" style="padding:30px;color:#888;">품목을 추가해주세요.</td></tr>';
        updateTotals();
        return;
    }

    items.forEach((item, i) => {
        // 저장된 unit_price 사용, 없으면 계산
        const unitPrice = item.unit_price > 0 ? Math.round(item.unit_price) : (item.quantity > 0 ? Math.round(item.supply_price / item.quantity) : 0);
        const qtyDisplay = item.quantity_display || (formatNumber(item.quantity) + item.unit);
        // 줄바꿈(\n)을 <br>로 변환하여 2줄 표시
        const specHtml = (item.specification || '').replace(/\n/g, '<br>');
        tbody.innerHTML += `<tr>
            <td class="text-center">${i+1}</td>
            <td>${item.product_name}</td>
            <td>${specHtml}</td>
            <td class="text-center">${qtyDisplay}</td>
            <td class="text-right">${formatNumber(unitPrice)}</td>
            <td class="text-right">${formatNumber(item.supply_price)}</td>
            <td class="text-center"><button class="delete-btn" onclick="deleteItem(${item.no})">×</button></td>
        </tr>`;
    });
    updateTotals();
}

function updateTotals() {
    let supply = 0;
    items.forEach(item => supply += parseInt(item.supply_price) || 0);
    const vat = Math.round(supply * 0.1);
    document.getElementById('supplyTotal').textContent = formatNumber(supply);
    document.getElementById('vatTotal').textContent = formatNumber(vat);
    document.getElementById('grandTotal').textContent = formatNumber(supply + vat);
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

    fetch('api/add_manual_item.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({is_manual:true, product_name:name, specification:spec, quantity:qty, unit:unit, supply_price:price})
    }).then(r=>r.json()).then(d=>{
        if(d.success) {
            items.push({no:d.item_no, is_manual:1, product_name:name, specification:spec, quantity:qty, unit:unit, quantity_display:formatNumber(qty)+unit, unit_price:Math.round(price/qty), supply_price:price, product_type:'', source_data:null});
            renderItems(); closeManualModal();
        } else alert('실패: '+d.message);
    }).catch(e=>alert('오류: '+e.message));
}

function deleteItem(itemNo) {
    if(!confirm('삭제하시겠습니까?')) return;
    fetch('api/delete_temp_item.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({item_no:itemNo})})
    .then(r=>r.json()).then(d=>{
        if(d.success) { items = items.filter(x=>x.no!==itemNo); renderItems(); }
        else alert('삭제 실패: '+d.message);
    }).catch(e=>alert('오류: '+e.message));
}

function saveQuote(isDraft) {
    const name = document.getElementById('customer_name').value.trim();
    if(!name) { alert('담당자명을 입력해주세요.'); document.getElementById('customer_name').focus(); return; }
    if(items.length===0) { alert('품목을 추가해주세요.'); return; }

    const data = {
        quote_no: quoteNo,
        customer_company: document.getElementById('customer_company').value.trim(),
        customer_name: name,
        customer_phone: document.getElementById('customer_phone').value.trim(),
        customer_email: document.getElementById('customer_email').value.trim(),
        customer_address: document.getElementById('customer_address').value.trim(),
        customer_memo: document.getElementById('customer_memo').value.trim(),
        admin_memo: document.getElementById('admin_memo').value.trim(),
        is_draft: isDraft,
        items: items.map(x=>({source_type:x.is_manual?'manual':'calculator', product_type:x.product_type||'', product_name:x.product_name, specification:x.specification, quantity:x.quantity, unit:x.unit, quantity_display:x.quantity_display, unit_price:x.unit_price, supply_price:x.supply_price, source_data:x.source_data}))
    };

    fetch('api/save.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)})
    .then(r=>r.json()).then(d=>{
        if(d.success) { alert(isDraft?'임시저장됨':'저장됨'); location.href='detail.php?id='+d.quote_id; }
        else alert('저장 실패: '+d.message);
    }).catch(e=>alert('오류: '+e.message));
}

function formatNumber(n) { const v=parseFloat(n); return isNaN(v)?'0':v.toLocaleString('ko-KR',{maximumFractionDigits:1}); }

// 계산기 연동
const CALC_CFG = {
    'sticker':{name:'스티커',url:'/admin/mlangprintauto/quote/widgets/sticker.php'},
    'inserted':{name:'전단지',url:'/admin/mlangprintauto/quote/widgets/inserted.php'},
    'namecard':{name:'명함',url:'/admin/mlangprintauto/quote/widgets/namecard.php'},
    'envelope':{name:'봉투',url:'/admin/mlangprintauto/quote/widgets/envelope.php'},
    'ncrflambeau':{name:'NCR양식',url:'/admin/mlangprintauto/quote/widgets/ncrflambeau.php'},
    'cadarok':{name:'카다록',url:'/admin/mlangprintauto/quote/widgets/cadarok.php'},
    'littleprint':{name:'포스터',url:'/admin/mlangprintauto/quote/widgets/littleprint.php'},
    'msticker':{name:'자석스티커',url:'/admin/mlangprintauto/quote/widgets/msticker.php'},
    'merchandisebond':{name:'상품권',url:'/admin/mlangprintauto/quote/widgets/merchandisebond.php'}
};

function openCalculatorSelect() { document.getElementById('calcSelectModal').classList.add('active'); }
function closeCalculatorSelect() { document.getElementById('calcSelectModal').classList.remove('active'); }
function openCalculator(type) {
    const c = CALC_CFG[type]; if(!c){alert('알 수 없는 품목');return;}
    closeCalculatorSelect();
    document.getElementById('calcModalTitle').textContent = c.name+' 계산기';
    document.getElementById('calcIframe').src = c.url;
    document.getElementById('calcIframeModal').classList.add('active');
    document.body.style.overflow='hidden';
}
function closeCalculatorIframe() {
    document.getElementById('calcIframeModal').classList.remove('active');
    document.getElementById('calcIframe').src='about:blank';
    document.body.style.overflow='';
}

window.addEventListener('message', function(e) {
    if(e.origin!==window.location.origin||!e.data||!e.data.type) return;
    if(e.data.type==='ADMIN_QUOTE_ITEM_ADDED' || e.data.type==='CALCULATOR_PRICE_DATA') {
        const payload = e.data.payload || {};
        if (payload.product_code && !payload.product_type) payload.product_type = payload.product_code;
        if (payload.quantity_unit && !payload.unit) payload.unit = payload.quantity_unit;
        if (payload.options && typeof payload.options === 'object') {
            Object.keys(payload.options).forEach(k => { if (!(k in payload)) payload[k] = payload.options[k]; });
        }
        const addBtn = document.querySelector('#calcIframeModal .btn-close, #calcIframeModal button');
        if(addBtn) addBtn.disabled = true;
        fetch('api/add_calculator_item.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)})
        .then(r=>r.json()).then(d=>{
            if(d.success) {
                items.push({no:d.item_no, is_manual:0, product_name:d.item.product_name, specification:d.item.specification, quantity:d.item.quantity, unit:d.item.unit, quantity_display:d.item.quantity_display, unit_price:d.item.unit_price, supply_price:d.item.supply_price, product_type:d.item.product_type, source_data:payload});
                renderItems();
                closeCalculatorIframe();
            } else {
                alert('품목 추가 실패: '+d.message);
            }
        }).catch(err=>{
            alert('서버 오류: '+err.message+'\n다시 시도해주세요.');
        }).finally(()=>{
            if(addBtn) addBtn.disabled = false;
        });
    }
    if(e.data.type==='ADMIN_QUOTE_CLOSE_MODAL') closeCalculatorIframe();
});
</script>
</body>
</html>
