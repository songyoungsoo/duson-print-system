<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
mysqli_set_charset($db, 'utf8');

$types = [];
$r = mysqli_query($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='Envelope' AND BigNo='0' ORDER BY TreeNo, no");
if ($r) { while ($row = mysqli_fetch_assoc($r)) { $types[] = $row; } }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>봉투 계산기</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; color: #333; background: #f8f9fa; padding: 16px; }
.form-grid { display: grid; grid-template-columns: 80px 1fr; gap: 8px 12px; align-items: center; }
.form-grid label { font-weight: 600; color: #555; white-space: nowrap; }
select { width: 100%; padding: 6px 8px; border: 1px solid #d0d5dd; border-radius: 4px; font-size: 13px; background: #fff; }
select:focus { outline: none; border-color: #4f7cff; }
.price-box { margin-top: 16px; background: #fff; border: 1px solid #e0e3e8; border-radius: 6px; padding: 12px; }
.price-row { display: flex; justify-content: space-between; padding: 4px 0; }
.price-row.total { font-weight: 700; font-size: 16px; color: #1a56db; border-top: 1px solid #e0e3e8; margin-top: 6px; padding-top: 8px; }
.apply-btn { width: 100%; margin-top: 12px; padding: 10px; border: none; border-radius: 6px; background: #1a56db; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
.apply-btn:hover { background: #1642b0; }
.apply-btn:disabled { background: #ccc; cursor: not-allowed; }
.error-msg { color: #dc3545; font-size: 12px; margin-top: 8px; display: none; }
.loading { display: none; text-align: center; padding: 8px; color: #666; }
.section-label { font-weight: 600; color: #555; margin-top: 10px; margin-bottom: 4px; font-size: 12px; grid-column: 1 / -1; border-top: 1px solid #eee; padding-top: 8px; }
.opt-row { display: flex; align-items: center; gap: 6px; }
.opt-row input[type="checkbox"] { width: auto; }
</style>
</head>
<body>

<div class="form-grid">
    <label>종류</label>
    <select id="style" onchange="loadChildren(this.value,'Section')">
        <option value="">선택</option>
        <?php foreach ($types as $t): ?>
        <option value="<?php echo $t['no']; ?>"><?php echo htmlspecialchars($t['title']); ?></option>
        <?php endforeach; ?>
    </select>

    <label>재질</label>
    <select id="Section" onchange="loadChildren(this.value,'quantity')">
        <option value="">종류를 먼저 선택</option>
    </select>

    <label>수량</label>
    <select id="quantity" onchange="calculatePrice()">
        <option value="">재질을 먼저 선택</option>
    </select>

    <label>인쇄색상</label>
    <select id="POtype" onchange="calculatePrice()">
        <option value="1">마스터1도</option>
        <option value="2">마스터2도</option>
        <option value="3">칼라4도</option>
    </select>

    <label>편집</label>
    <select id="ordertype" onchange="calculatePrice()">
        <option value="print">인쇄만 의뢰</option>
        <option value="design">디자인+인쇄</option>
    </select>

    <div class="section-label">추가옵션</div>

    <label>풀띠</label>
    <div class="opt-row"><input type="checkbox" id="envelope_tape_enabled" onchange="calculatePrice()"></div>
</div>

<div class="price-box">
    <div class="loading" id="loading">계산 중...</div>
    <div id="priceDisplay">
        <div class="price-row"><span>공급가액</span><span id="supplyPrice">-</span></div>
        <div class="price-row"><span>부가세</span><span id="vatPrice">-</span></div>
        <div class="price-row total"><span>합계</span><span id="totalPrice">-</span></div>
    </div>
    <div class="error-msg" id="errorMsg"></div>
</div>

<button class="apply-btn" id="applyBtn" onclick="applyToQuote()" disabled>견적서에 적용</button>

<script>
var API_URL = '/api/quote/calculate_price.php';
var OPT_URL = '/admin/mlangprintauto/quote/widgets/api/get_options.php';
var currentPayload = null;

function loadChildren(parentVal, childId) {
    var child = document.getElementById(childId);
    child.innerHTML = '<option value="">로딩중...</option>';

    if (childId === 'Section') {
        document.getElementById('quantity').innerHTML = '<option value="">상위 항목을 선택</option>';
    }

    if (!parentVal) {
        child.innerHTML = '<option value="">상위 항목을 선택</option>';
        resetPrice();
        return;
    }

    fetch(OPT_URL + '?table=envelope&parent=' + parentVal, {credentials: 'same-origin'})
        .then(function(r) { return r.json(); })
        .then(function(data) {
            child.innerHTML = '<option value="">선택</option>';
            for (var i = 0; i < data.length; i++) {
                var o = document.createElement('option');
                o.value = data[i].no;
                o.textContent = data[i].title;
                child.appendChild(o);
            }
            if (data.length === 1) {
                child.value = data[0].no;
                child.dispatchEvent(new Event('change'));
            }
        })
        .catch(function() {
            child.innerHTML = '<option value="">로딩 실패</option>';
        });

    resetPrice();
}

function calculatePrice() {
    var style = document.getElementById('style').value;
    var section = document.getElementById('Section').value;
    var quantity = document.getElementById('quantity').value;

    if (!style || !section || !quantity) {
        resetPrice();
        return;
    }

    var params = {
        MY_type: style,
        Section: section,
        MY_amount: quantity,
        POtype: document.getElementById('POtype').value,
        ordertype: document.getElementById('ordertype').value
    };

    if (document.getElementById('envelope_tape_enabled').checked) {
        params.envelope_tape_enabled = 1;
    }

    document.getElementById('loading').style.display = 'block';
    document.getElementById('priceDisplay').style.opacity = '0.4';
    document.getElementById('errorMsg').style.display = 'none';
    document.getElementById('applyBtn').disabled = true;

    fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({product: 'envelope', params: params})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('priceDisplay').style.opacity = '1';

        if (data.success && data.payload) {
            var p = data.payload;
            document.getElementById('supplyPrice').textContent = fmt(p.supply_price);
            document.getElementById('vatPrice').textContent = fmt(p.vat_price);
            document.getElementById('totalPrice').textContent = fmt(p.total_price);
            document.getElementById('applyBtn').disabled = false;
            currentPayload = p;
        } else {
            showError(data.message || '가격 계산 실패');
        }
    })
    .catch(function(err) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('priceDisplay').style.opacity = '1';
        showError('서버 연결 오류: ' + err.message);
    });
}

function applyToQuote() {
    if (!currentPayload) return;
    var btn = document.getElementById('applyBtn');
    btn.disabled = true;
    btn.textContent = '적용 중...';
    window.parent.postMessage({type: 'ADMIN_QUOTE_ITEM_ADDED', payload: currentPayload}, window.location.origin);
    setTimeout(function() { btn.disabled = false; btn.textContent = '견적서에 적용'; }, 1000);
}

function resetPrice() {
    document.getElementById('supplyPrice').textContent = '-';
    document.getElementById('vatPrice').textContent = '-';
    document.getElementById('totalPrice').textContent = '-';
    document.getElementById('applyBtn').disabled = true;
    currentPayload = null;
}

function showError(msg) {
    var el = document.getElementById('errorMsg');
    el.textContent = msg;
    el.style.display = 'block';
    resetPrice();
}

function fmt(n) {
    return new Intl.NumberFormat('ko-KR').format(n) + '원';
}
</script>
</body>
</html>
