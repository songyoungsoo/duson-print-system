<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
mysqli_set_charset($db, 'utf8');

$types = [];
$r = mysqli_query($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='LittlePrint' AND BigNo='0' ORDER BY TreeNo, no");
if ($r) { while ($row = mysqli_fetch_assoc($r)) { $types[] = $row; } }

// 추가옵션 가격을 DB에서 조회 (additional_options_config)
$addOpts = ['coating' => [], 'folding' => [], 'creasing' => []];
$r = mysqli_query($db, "SELECT option_category, option_type, option_name, base_price FROM additional_options_config WHERE is_active = 1 AND option_category IN ('coating','folding','creasing') ORDER BY option_category, sort_order");
if ($r) { while ($row = mysqli_fetch_assoc($r)) { $addOpts[$row['option_category']][] = $row; } }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>포스터 계산기</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; color: #333; background: #f8f9fa; padding: 16px; }
.form-grid { display: grid; grid-template-columns: 80px 1fr; gap: 8px 12px; align-items: center; }
.form-grid label { font-weight: 600; color: #555; white-space: nowrap; }
select { width: 100%; padding: 6px 8px; border: 1px solid #d0d5dd; border-radius: 4px; font-size: 13px; background: #fff; }
select:focus { outline: none; border-color: #1E4E79; }
.price-box { margin-top: 16px; background: #fff; border: 1px solid #e0e3e8; border-radius: 6px; padding: 12px; }
.price-row { display: flex; justify-content: space-between; padding: 4px 0; }
.price-row.total { font-weight: 700; font-size: 16px; color: #1E4E79; border-top: 1px solid #e0e3e8; margin-top: 6px; padding-top: 8px; }
.apply-btn { width: 100%; margin-top: 12px; padding: 10px; border: none; border-radius: 6px; background: #1E4E79; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
.apply-btn:hover { background: #163D5C; }
.apply-btn:disabled { background: #ccc; cursor: not-allowed; }
.error-msg { color: #dc3545; font-size: 12px; margin-top: 8px; display: none; }
.loading { display: none; text-align: center; padding: 8px; color: #666; }
.section-label { font-weight: 600; color: #555; margin-top: 10px; margin-bottom: 4px; font-size: 12px; grid-column: 1 / -1; border-top: 1px solid #eee; padding-top: 8px; }
.opt-row { display: flex; align-items: center; gap: 6px; }
.opt-row input[type="checkbox"] { width: auto; }
.opt-row select { flex: 1; }
</style>
</head>
<body>

<div class="form-grid">
    <label>종류</label>
    <select id="style" onchange="loadCascade(this.value,'Section',['PN_type','quantity'])">
        <option value="">선택</option>
        <?php foreach ($types as $t): ?>
        <option value="<?php echo $t['no']; ?>"><?php echo htmlspecialchars($t['title']); ?></option>
        <?php endforeach; ?>
    </select>

    <label>지류</label>
    <select id="Section" onchange="loadCascade(this.value,'PN_type',['quantity'])">
        <option value="">종류를 먼저 선택</option>
    </select>

    <label>규격</label>
    <select id="PN_type" onchange="loadQuantities()">
        <option value="">지류를 먼저 선택</option>
    </select>

    <label>수량</label>
    <select id="quantity" onchange="calculatePrice()">
        <option value="">규격을 먼저 선택</option>
    </select>

    <label>인쇄면</label>
    <select id="POtype" onchange="calculatePrice()">
        <option value="1">단면</option>
        <option value="2">양면</option>
    </select>

    <label>편집</label>
    <select id="ordertype" onchange="calculatePrice()">
        <option value="print">인쇄만 의뢰</option>
        <option value="total">디자인+인쇄</option>
    </select>

    <div class="section-label">추가옵션</div>

    <label>코팅</label>
    <div class="opt-row">
        <input type="checkbox" id="coating_enabled" onchange="calculatePrice()">
        <select id="coating_type" onchange="calculatePrice()">
            <?php foreach ($addOpts['coating'] as $opt): ?>
            <option value="<?php echo htmlspecialchars($opt['option_type']); ?>"><?php echo htmlspecialchars($opt['option_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label>접지</label>
    <div class="opt-row">
        <input type="checkbox" id="folding_enabled" onchange="calculatePrice()">
        <select id="folding_type" onchange="calculatePrice()">
            <?php foreach ($addOpts['folding'] as $opt): ?>
            <option value="<?php echo htmlspecialchars($opt['option_type']); ?>"><?php echo htmlspecialchars($opt['option_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label>오시</label>
    <div class="opt-row">
        <input type="checkbox" id="creasing_enabled" onchange="calculatePrice()">
        <select id="creasing_lines" onchange="calculatePrice()">
            <?php foreach ($addOpts['creasing'] as $opt): ?>
            <option value="<?php echo htmlspecialchars($opt['option_type']); ?>"><?php echo htmlspecialchars($opt['option_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
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

// 추가옵션 base_price (DB: additional_options_config 자동 로드)
var OPTION_PRICES = <?php
    $optPrices = [];
    foreach ($addOpts as $cat => $items) {
        $optPrices[$cat] = [];
        foreach ($items as $item) {
            $optPrices[$cat][$item['option_type']] = (int)$item['base_price'];
        }
    }
    echo json_encode($optPrices, JSON_UNESCAPED_UNICODE);
?>;

function getSelectedText(id) {
    var el = document.getElementById(id);
    return (el && el.selectedIndex >= 0) ? el.options[el.selectedIndex].text : '';
}

function loadCascade(parentVal, childId, resetIds) {
    var child = document.getElementById(childId);
    child.innerHTML = '<option value="">로딩중...</option>';

    for (var i = 0; i < resetIds.length; i++) {
        document.getElementById(resetIds[i]).innerHTML = '<option value="">상위 항목을 선택</option>';
    }

    if (!parentVal) {
        child.innerHTML = '<option value="">상위 항목을 선택</option>';
        resetPrice();
        return;
    }

    var url;
    if (childId === 'PN_type') {
        // L2 (규격/용지): uses TreeNo=root_no (style value), not BigNo
        var rootVal = document.getElementById('style').value;
        url = OPT_URL + '?table=littleprint&parent=' + rootVal + '&lookup=TreeNo';
    } else {
        url = OPT_URL + '?table=littleprint&parent=' + parentVal;
    }

    fetch(url, {credentials: 'same-origin'})
        .then(function(r) { return r.json(); })
        .then(function(data) {
            child.innerHTML = '<option value="">선택</option>';
            for (var i = 0; i < data.length; i++) {
                var o = document.createElement('option');
                o.value = data[i].no;
                o.textContent = data[i].title;
                child.appendChild(o);
            }
            // Auto-select first option and cascade
            if (data.length >= 1) {
                child.value = data[0].no;
                child.dispatchEvent(new Event('change'));
            }
        })
        .catch(function() {
            child.innerHTML = '<option value="">로딩 실패</option>';
        });

    resetPrice();
}

function loadQuantities() {
    var style = document.getElementById('style').value;
    var section = document.getElementById('Section').value;
    var pnType = document.getElementById('PN_type').value;
    var qty = document.getElementById('quantity');
    qty.innerHTML = '<option value="">로딩중...</option>';
    if (!style || !section || !pnType) { qty.innerHTML = '<option value="">상위 항목을 선택</option>'; resetPrice(); return; }
    var url = OPT_URL + '?table=littleprint&source=price&field=quantity&filter_style=' + style + '&filter_Section=' + pnType + '&filter_TreeSelect=' + section;
    fetch(url, {credentials: 'same-origin'}).then(function(r) { return r.json(); }).then(function(data) {
        qty.innerHTML = '<option value="">선택</option>';
        for (var i = 0; i < data.length; i++) { var o = document.createElement('option'); o.value = data[i].no; var n = parseInt(data[i].title); o.textContent = n ? n.toLocaleString() + '매' : data[i].title; qty.appendChild(o); }
        if (data.length >= 1) { qty.value = data[0].no; qty.dispatchEvent(new Event('change')); }
    }).catch(function() { qty.innerHTML = '<option value="">로딩 실패</option>'; });
    resetPrice();
}

// Auto-init: select first style on page load to cascade all dropdowns
document.addEventListener('DOMContentLoaded', function() {
    var style = document.getElementById('style');
    if (style.options.length > 1) {
        style.value = style.options[1].value;
        loadCascade(style.value, 'Section', ['PN_type', 'quantity']);
    }
});

function calculatePrice() {
    var style = document.getElementById('style').value;
    var section = document.getElementById('Section').value;
    var pnType = document.getElementById('PN_type').value;
    var quantity = document.getElementById('quantity').value;

    if (!style || !section || !pnType || !quantity) {
        resetPrice();
        return;
    }

    // Calculate additional options price (OPTION_PRICES: DB additional_options_config 자동 로드)
    var additionalTotal = 0;
    var optionDetails = {};
    var qtyMultiplier = Math.max(parseFloat(quantity), 1);
    if (document.getElementById('coating_enabled').checked) {
        var coatingType = document.getElementById('coating_type').value;
        var coatingBase = (OPTION_PRICES.coating && OPTION_PRICES.coating[coatingType]) || 0;
        var coatingPrice = Math.round(coatingBase * qtyMultiplier);
        additionalTotal += coatingPrice;
        optionDetails.coating = {type: getSelectedText('coating_type'), price: coatingPrice};
    }
    if (document.getElementById('folding_enabled').checked) {
        var foldingType = document.getElementById('folding_type').value;
        var foldingBase = (OPTION_PRICES.folding && OPTION_PRICES.folding[foldingType]) || 0;
        var foldingPrice = Math.round(foldingBase * qtyMultiplier);
        additionalTotal += foldingPrice;
        optionDetails.folding = {type: getSelectedText('folding_type'), price: foldingPrice};
    }
    if (document.getElementById('creasing_enabled').checked) {
        var creasingLines = document.getElementById('creasing_lines').value;
        var creasingBase = (OPTION_PRICES.creasing && OPTION_PRICES.creasing[creasingLines]) || 0;
        var creasingPrice = Math.round(creasingBase * qtyMultiplier);
        additionalTotal += creasingPrice;
        optionDetails.creasing = {lines: parseInt(creasingLines), price: creasingPrice};
    }

    var params = {
        style: style,
        Section: pnType,
        TreeSelect: section,
        quantity: quantity,
        POtype: document.getElementById('POtype').value,
        ordertype: document.getElementById('ordertype').value,
        premium_options_total: additionalTotal
    };

    if (document.getElementById('coating_enabled').checked) {
        params.coating_enabled = 1;
        params.coating_type = document.getElementById('coating_type').value;
    }
    if (document.getElementById('folding_enabled').checked) {
        params.folding_enabled = 1;
        params.folding_type = document.getElementById('folding_type').value;
    }
    if (document.getElementById('creasing_enabled').checked) {
        params.creasing_enabled = 1;
        params.creasing_lines = parseInt(document.getElementById('creasing_lines').value);
    }

    document.getElementById('loading').style.display = 'block';
    document.getElementById('priceDisplay').style.opacity = '0.4';
    document.getElementById('errorMsg').style.display = 'none';
    document.getElementById('applyBtn').disabled = true;

    fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({product: 'littleprint', params: params})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('priceDisplay').style.opacity = '1';

        if (data.success && data.payload) {
             var p = data.payload;

             // Override specification with human-readable dropdown text (3-level)
             p.spec_type = getSelectedText('style');
             p.spec_material = getSelectedText('Section');
             p.spec_paper = getSelectedText('PN_type');
             p.spec_sides = (function() {
                 var val = document.getElementById('POtype').value;
                 var map = {'1': '단면', '2': '양면'};
                 return map[val] || '';
             })();
             p.spec_design = (function() {
                 var val = document.getElementById('ordertype').value;
                 var map = {'print': '인쇄만', 'total': '디자인+인쇄'};
                 return map[val] || '';
             })();

             var line1 = [p.spec_type, p.spec_material, p.spec_paper].filter(Boolean).join(' / ');
             var line2Parts = [];
             if (p.spec_sides) line2Parts.push(p.spec_sides);
             if (p.quantity_display) line2Parts.push(p.quantity_display);
             if (p.spec_design) line2Parts.push(p.spec_design);
             var line2 = line2Parts.filter(Boolean).join(' / ');
             p.specification = line1 + '\n' + line2;

             // Enrich with additional options info
             if (Object.keys(optionDetails).length > 0) {
                 p.options = p.options || {};
                 if (optionDetails.coating) {
                     p.options.coating_enabled = 1;
                     p.options.coating_type = optionDetails.coating.type;
                     p.options.coating_price = optionDetails.coating.price;
                 }
                 if (optionDetails.folding) {
                     p.options.folding_enabled = 1;
                     p.options.folding_type = optionDetails.folding.type;
                     p.options.folding_price = optionDetails.folding.price;
                 }
                 if (optionDetails.creasing) {
                     p.options.creasing_enabled = 1;
                     p.options.creasing_lines = optionDetails.creasing.lines;
                     p.options.creasing_price = optionDetails.creasing.price;
                 }
                 p.options.additional_options_total = additionalTotal;
             }

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
