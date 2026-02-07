<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit('Unauthorized');
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>스티커 계산기</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; color: #333; background: #f8f9fa; padding: 16px; }
.form-grid { display: grid; grid-template-columns: 80px 1fr; gap: 8px 12px; align-items: center; }
.form-grid label { font-weight: 600; color: #555; white-space: nowrap; }
select, input[type="number"] { width: 100%; padding: 6px 8px; border: 1px solid #d0d5dd; border-radius: 4px; font-size: 13px; background: #fff; }
select:focus, input:focus { outline: none; border-color: #4f7cff; }
.size-row { display: grid; grid-template-columns: 1fr 20px 1fr; gap: 4px; align-items: center; }
.size-row span { text-align: center; color: #999; }
.price-box { margin-top: 16px; background: #fff; border: 1px solid #e0e3e8; border-radius: 6px; padding: 12px; }
.price-row { display: flex; justify-content: space-between; padding: 4px 0; }
.price-row.total { font-weight: 700; font-size: 16px; color: #1a56db; border-top: 1px solid #e0e3e8; margin-top: 6px; padding-top: 8px; }
.apply-btn { width: 100%; margin-top: 12px; padding: 10px; border: none; border-radius: 6px; background: #1a56db; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
.apply-btn:hover { background: #1642b0; }
.apply-btn:disabled { background: #ccc; cursor: not-allowed; }
.error-msg { color: #dc3545; font-size: 12px; margin-top: 8px; display: none; }
.loading { display: none; text-align: center; padding: 8px; color: #666; }
</style>
</head>
<body>

<div class="form-grid">
    <label>재질</label>
    <select id="jong">
        <option value="">선택</option>
        <option value="jil 아트지유광">아트지유광</option>
        <option value="jil 아트지무광">아트지무광</option>
        <option value="jil 일반스티커">일반스티커(모조)</option>
        <option value="jil 유포스티커">유포스티커</option>
        <option value="jil 투명스티커">투명스티커</option>
        <option value="jka 크라프트스티커">크라프트스티커</option>
        <option value="jsp 은지스티커">은지스티커</option>
        <option value="cka 크라프트칼라">크라프트칼라</option>
    </select>

    <label>사이즈</label>
    <div class="size-row">
        <input type="number" id="garo" placeholder="가로(mm)" min="1" max="590">
        <span>×</span>
        <input type="number" id="sero" placeholder="세로(mm)" min="1" max="590">
    </div>

    <label>매수</label>
    <select id="mesu">
        <option value="">선택</option>
        <option value="500">500매</option>
        <option value="1000" selected>1,000매</option>
        <option value="2000">2,000매</option>
        <option value="3000">3,000매</option>
        <option value="4000">4,000매</option>
        <option value="5000">5,000매</option>
        <option value="6000">6,000매</option>
        <option value="7000">7,000매</option>
        <option value="8000">8,000매</option>
        <option value="9000">9,000매</option>
        <option value="10000">10,000매</option>
    </select>

    <label>모양</label>
    <select id="domusong">
        <option value="00000 사각">기본사각</option>
        <option value="08000 사각도무송">사각도무송 (+8,000)</option>
        <option value="08000 귀돌">귀돌이(라운드) (+8,000)</option>
        <option value="08000 원형">원형 (+8,000)</option>
        <option value="08000 타원">타원형 (+8,000)</option>
        <option value="19000 복잡">모양도무송 (+19,000)</option>
    </select>

    <label>편집</label>
    <select id="uhyung">
        <option value="0">기본편집</option>
        <option value="10000">편집+10,000</option>
        <option value="30000">고급편집+30,000</option>
    </select>
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
const API_URL = '/api/quote/calculate_price.php';
let currentPayload = null;

function getSelectedText(id) {
    var el = document.getElementById(id);
    return (el && el.selectedIndex >= 0) ? el.options[el.selectedIndex].text : '';
}

document.querySelectorAll('select, input').forEach(el => {
    el.addEventListener('change', calculatePrice);
    if (el.type === 'number') el.addEventListener('input', debounce(calculatePrice, 500));
});

// Auto-calculate on first load with sensible defaults
(function autoInit() {
    var jong = document.getElementById('jong');
    var garo = document.getElementById('garo');
    var sero = document.getElementById('sero');
    // Select first material if not already selected
    if (!jong.value && jong.options.length > 1) {
        jong.value = jong.options[1].value; // skip "선택" placeholder
    }
    // Set default size if empty (min 50x60mm for non-domusong)
    if (!garo.value) garo.value = 50;
    if (!sero.value) sero.value = 60;
    // mesu already has "1000" selected by default
    calculatePrice();
})();

function debounce(fn, ms) {
    let t;
    return function() { clearTimeout(t); t = setTimeout(fn, ms); };
}

function calculatePrice() {
    const jong = document.getElementById('jong').value;
    const garo = document.getElementById('garo').value;
    const sero = document.getElementById('sero').value;
    const mesu = document.getElementById('mesu').value;

    if (!jong || !garo || !sero || !mesu) {
        resetPrice();
        return;
    }

    const params = {
        jong: jong,
        garo: parseInt(garo),
        sero: parseInt(sero),
        mesu: parseInt(mesu),
        uhyung: parseInt(document.getElementById('uhyung').value) || 0,
        domusong: document.getElementById('domusong').value
    };

    document.getElementById('loading').style.display = 'block';
    document.getElementById('priceDisplay').style.opacity = '0.4';
    document.getElementById('errorMsg').style.display = 'none';
    document.getElementById('applyBtn').disabled = true;

    fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({product: 'sticker', params: params})
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('priceDisplay').style.opacity = '1';

        if (data.success && data.payload) {
            const p = data.payload;
            document.getElementById('supplyPrice').textContent = fmt(p.supply_price);
            document.getElementById('vatPrice').textContent = fmt(p.vat_price);
            document.getElementById('totalPrice').textContent = fmt(p.total_price);
            document.getElementById('applyBtn').disabled = false;

             currentPayload = p;
             currentPayload.spec_type = jong.substring(4);
             currentPayload.spec_material = garo + 'x' + sero + 'mm';
             
             // Rebuild specification with human-readable format
             var line1 = [currentPayload.spec_type, currentPayload.spec_material].filter(Boolean).join(' / ');
             var line2Parts = [];
             if (currentPayload.quantity_display) line2Parts.push(currentPayload.quantity_display);
             var line2 = line2Parts.filter(Boolean).join(' / ');
             currentPayload.specification = line1 + (line2 ? '\n' + line2 : '');
        } else {
            showError(data.message || '가격 계산 실패');
        }
    })
    .catch(err => {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('priceDisplay').style.opacity = '1';
        showError('서버 연결 오류: ' + err.message);
    });
}

function applyToQuote() {
    if (!currentPayload) return;

    const btn = document.getElementById('applyBtn');
    btn.disabled = true;
    btn.textContent = '적용 중...';

    window.parent.postMessage({
        type: 'ADMIN_QUOTE_ITEM_ADDED',
        payload: currentPayload
    }, window.location.origin);

    setTimeout(() => {
        btn.disabled = false;
        btn.textContent = '견적서에 적용';
    }, 1000);
}

function resetPrice() {
    document.getElementById('supplyPrice').textContent = '-';
    document.getElementById('vatPrice').textContent = '-';
    document.getElementById('totalPrice').textContent = '-';
    document.getElementById('applyBtn').disabled = true;
    currentPayload = null;
}

function showError(msg) {
    const el = document.getElementById('errorMsg');
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
