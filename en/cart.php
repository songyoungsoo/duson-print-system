<?php
session_start();
include __DIR__ . '/includes/exchange_rate.php';
$exchangeRate = getExchangeRate();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart — Duson Print</title>
    <meta name="description" content="Review your printing order from Duson Print. View cart items, pricing in KRW and USD, and proceed to checkout.">
    <link rel="icon" type="image/png" href="/ImgFolder/dusonlogo1.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy: #1E4E79;
            --navy-dark: #132F4C;
            --navy-light: #2D6FA8;
            --gold: #FFD500;
            --gold-dark: #D4B000;
            --gold-light: #FFE766;
            --bg: #FAFBFC;
            --bg-warm: #F8F6F3;
            --text: #1A1A2E;
            --text-muted: #64748B;
            --text-light: #94A3B8;
            --white: #FFFFFF;
            --border: #E2E8F0;
            --radius: 14px;
            --radius-lg: 20px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --font-body: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-heading: 'Sora', 'DM Sans', sans-serif;
            --green: #059669;
            --green-light: #ECFDF5;
            --red: #DC2626;
            --red-light: #FEF2F2;
        }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-body);
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== MAIN LAYOUT ===== */
        .cart-page {
            max-width: 1200px; margin: 0 auto;
            padding: 130px 24px 60px;
        }
        .cart-header {
            margin-bottom: 32px;
        }
        .cart-header h1 {
            font-family: var(--font-heading); font-size: 32px;
            font-weight: 700; color: var(--text); letter-spacing: -0.5px;
        }
        .cart-header .item-count {
            font-size: 15px; color: var(--text-muted); margin-top: 4px;
        }
        .cart-layout {
            display: grid; grid-template-columns: 1fr 360px; gap: 32px;
            align-items: start;
        }

        /* ===== LOADING / EMPTY / ERROR ===== */
        .state-box {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1px solid var(--border); padding: 60px 32px;
            text-align: center;
        }
        .state-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.7; }
        .state-title {
            font-family: var(--font-heading); font-size: 22px;
            font-weight: 600; color: var(--text); margin-bottom: 8px;
        }
        .state-desc {
            font-size: 15px; color: var(--text-muted);
            max-width: 400px; margin: 0 auto 24px;
        }
        .state-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px; border-radius: 12px;
            font-size: 14px; font-weight: 600; text-decoration: none;
            transition: all 0.2s; cursor: pointer; border: none;
        }
        .state-btn-primary {
            background: var(--navy); color: var(--white);
        }
        .state-btn-primary:hover { background: var(--navy-dark); }
        .state-btn-outline {
            background: var(--white); color: var(--navy);
            border: 1.5px solid var(--navy);
        }
        .state-btn-outline:hover { background: rgba(30,78,121,0.04); }

        .spinner {
            width: 40px; height: 40px; border: 3px solid var(--border);
            border-top-color: var(--navy); border-radius: 50%;
            animation: spin 0.8s linear infinite; margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== CART ITEMS ===== */
        .cart-items { display: flex; flex-direction: column; gap: 16px; }
        .cart-item {
            background: var(--white); border-radius: var(--radius);
            border: 1px solid var(--border); padding: 20px;
            display: grid; grid-template-columns: 56px 1fr auto;
            gap: 16px; align-items: start;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .cart-item:hover { border-color: #CBD5E1; box-shadow: var(--shadow-sm); }
        .cart-item-img {
            width: 56px; height: 56px; border-radius: 10px;
            background: var(--bg); display: flex; align-items: center;
            justify-content: center; overflow: hidden;
        }
        .cart-item-img img { width: 40px; height: 40px; object-fit: contain; }
        .cart-item-body { min-width: 0; }
        .cart-item-name {
            font-family: var(--font-heading); font-size: 15px;
            font-weight: 600; color: var(--text); margin-bottom: 6px;
        }
        .cart-item-details {
            display: flex; flex-wrap: wrap; gap: 4px 12px;
            font-size: 13px; color: var(--text-muted);
        }
        .cart-item-detail {
            display: inline-flex; align-items: center; gap: 4px;
        }
        .cart-item-detail .key { color: var(--text-light); }
        .cart-item-detail .val { color: var(--text); font-weight: 500; }
        .cart-item-comment {
            margin-top: 6px; font-size: 12px; color: var(--text-light);
            font-style: italic;
        }
        .cart-item-actions {
            display: flex; flex-direction: column; align-items: flex-end;
            gap: 8px;
        }
        .cart-item-price {
            text-align: right;
        }
        .cart-item-price .krw {
            font-family: var(--font-heading); font-size: 16px;
            font-weight: 700; color: var(--text);
        }
        .cart-item-price .usd {
            font-size: 12px; color: var(--text-light); margin-top: 2px;
        }
        .btn-remove {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 6px 12px; border-radius: 8px;
            font-size: 12px; font-weight: 500;
            color: var(--red); background: var(--red-light);
            border: none; cursor: pointer; transition: all 0.2s;
        }
        .btn-remove:hover { background: #FEE2E2; }
        .btn-remove:disabled { opacity: 0.5; cursor: not-allowed; }

        /* ===== SUMMARY SIDEBAR ===== */
        .summary-card {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1px solid var(--border); padding: 28px;
            position: sticky; top: 128px;
        }
        .summary-title {
            font-family: var(--font-heading); font-size: 18px;
            font-weight: 600; color: var(--text); margin-bottom: 20px;
        }
        .summary-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; font-size: 14px;
        }
        .summary-row .label { color: var(--text-muted); }
        .summary-row .value { font-weight: 600; color: var(--text); }
        .summary-divider {
            border: none; border-top: 1px solid var(--border); margin: 8px 0;
        }
        .summary-total {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 0 4px; font-size: 18px;
        }
        .summary-total .label {
            font-family: var(--font-heading); font-weight: 600; color: var(--text);
        }
        .summary-total .value {
            font-family: var(--font-heading); font-weight: 700;
            color: var(--navy); font-size: 22px;
        }
        .summary-usd {
            text-align: right; font-size: 13px; color: var(--text-light);
            margin-bottom: 4px;
        }
        .summary-rate {
            font-size: 11px; color: var(--text-light); text-align: right;
            margin-bottom: 20px; line-height: 1.4;
        }
        .btn-checkout {
            display: flex; align-items: center; justify-content: center;
            gap: 8px; width: 100%; padding: 14px;
            border-radius: 12px; border: none;
            background: var(--navy); color: var(--white);
            font-family: var(--font-heading); font-size: 15px; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-checkout:hover { background: var(--navy-dark); transform: translateY(-1px); }
        .btn-checkout:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-continue {
            display: block; text-align: center; margin-top: 12px;
            font-size: 13px; color: var(--text-muted); text-decoration: none;
        }
        .btn-continue:hover { color: var(--navy); }

        /* ===== FOOTER ===== */
        .footer {
            background: var(--navy-dark); color: rgba(255,255,255,0.6);
            padding: 40px 24px; margin-top: 60px;
        }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 13px;
        }
        .footer-brand {
            font-family: var(--font-heading); font-weight: 600;
            color: rgba(255,255,255,0.9); font-size: 15px; margin-bottom: 4px;
        }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; }
        .footer a:hover { color: var(--gold); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .cart-layout {
                grid-template-columns: 1fr; gap: 24px;
            }
            .summary-card { position: static; }
        }
        @media (max-width: 640px) {
            .cart-page { padding: 120px 16px 40px; }
            .cart-header h1 { font-size: 24px; }
            .cart-item {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .cart-item-img { display: none; }
            .cart-item-actions {
                flex-direction: row-reverse; justify-content: space-between;
                align-items: center; width: 100%;
                padding-top: 12px; border-top: 1px solid var(--border);
            }
            .cart-item-price { text-align: left; }
            .summary-card { padding: 20px; }
            .footer-inner { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<?php $_en_current_page = 'cart'; include __DIR__ . '/includes/nav.php'; ?>

<!-- MAIN CART -->
<div class="cart-page">
    <div class="cart-header">
        <h1>Shopping Cart</h1>
        <div class="item-count" id="itemCount"></div>
    </div>

    <!-- Loading State -->
    <div id="stateLoading" class="state-box">
        <div class="spinner"></div>
        <div class="state-title">Loading your cart...</div>
    </div>

    <!-- Empty State -->
    <div id="stateEmpty" class="state-box" style="display:none;">
        <div class="state-icon">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
        </div>
        <div class="state-title">Your cart is empty</div>
        <div class="state-desc">Browse our products and add items to get started with your print order.</div>
        <a href="/en/products/" class="state-btn state-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"/><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
            Browse Products
        </a>
    </div>

    <!-- Error State -->
    <div id="stateError" class="state-box" style="display:none;">
        <div class="state-icon">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        </div>
        <div class="state-title">Unable to load cart</div>
        <div class="state-desc" id="errorMsg">Something went wrong. Please try again.</div>
        <button class="state-btn state-btn-outline" onclick="loadCart()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
            Retry
        </button>
    </div>

    <!-- Cart Content -->
    <div id="stateLoaded" class="cart-layout" style="display:none;">
        <div class="cart-items" id="cartItems"></div>

        <div class="summary-card">
            <div class="summary-title">Order Summary</div>
            <div class="summary-row">
                <span class="label">Items</span>
                <span class="value" id="summaryCount">0</span>
            </div>
            <div class="summary-row">
                <span class="label">Subtotal (excl. VAT)</span>
                <span class="value" id="summarySubtotal">-</span>
            </div>
            <div class="summary-row">
                <span class="label">VAT (10%)</span>
                <span class="value" id="summaryVat">-</span>
            </div>
            <hr class="summary-divider">
            <div class="summary-total">
                <span class="label">Total</span>
                <span class="value" id="summaryTotal">-</span>
            </div>
            <div class="summary-usd" id="summaryUsd"></div>
            <div class="summary-rate" id="summaryRate"></div>
            <button class="btn-checkout" id="btnCheckout" onclick="goCheckout()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                Proceed to Order
            </button>
            <a href="/en/products/" class="btn-continue">Continue Shopping</a>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-inner">
        <div>
            <div class="footer-brand">DUSON PRINT</div>
            <div>Seoul, South Korea &middot; Factory-direct printing since 1995</div>
        </div>
        <div>
            <a href="mailto:dsp1830@naver.com">dsp1830@naver.com</a> &middot;
            <a href="tel:+82-2-2632-1830">+82-2-2632-1830</a>
        </div>
    </div>
</footer>

<script>
(function() {
    'use strict';

    // ── Config ──
    var EXCHANGE_RATE = <?php echo json_encode($exchangeRate['rate'] ?? 1450); ?>;
    var EXCHANGE_DATE = <?php echo json_encode($exchangeRate['date'] ?? ''); ?>;

    var PRODUCT_NAMES = {
        'sticker': 'Stickers & Labels',
        'inserted': 'Flyers & Leaflets',
        'namecard': 'Business Cards',
        'envelope': 'Envelopes',
        'littleprint': 'Posters',
        'merchandisebond': 'Gift Vouchers',
        'msticker': 'Magnetic Stickers',
        'cadarok': 'Catalogs & Booklets',
        'ncrflambeau': 'NCR Forms',
        'leaflet': 'Flyers & Leaflets',
        'poster': 'Posters'
    };

    var PRODUCT_IMAGES = {
        'sticker': 'sticker_new_s.png',
        'inserted': 'inserted_s.png',
        'namecard': 'namecard_s.png',
        'envelope': 'envelope_s.png',
        'littleprint': 'littleprint_s.png',
        'merchandisebond': 'merchandise_s.png',
        'msticker': 'm_sticker_s.png',
        'cadarok': 'cadarok_s.png',
        'ncrflambeau': 'ncrflambeau_s.png',
        'leaflet': 'inserted_s.png',
        'poster': 'littleprint_s.png'
    };

    var DETAIL_KEYS = {
        '종류': 'Type', '크기': 'Size', '수량': 'Quantity', '옵션': 'Shape',
        '색상': 'Color', '용지': 'Paper', '사이즈': 'Size', '면수': 'Sides',
        '주문타입': 'Service', '타입': 'Type', '규격': 'Spec', '도수': 'Colors',
        '명함종류': 'Card Type', '용지종류': 'Paper Type', '인쇄면': 'Print Side',
        '디자인': 'Design', '인쇄': 'Print Side', '추가옵션': 'Add-ons',
        '프리미엄옵션': 'Premium Options', '스타일': 'Style', '섹션': 'Section'
    };

    var DETAIL_VALUES = {
        '디자인+인쇄': 'Design + Print',
        '인쇄만': 'Print Only',
        '디자인만': 'Design Only',
        '단면': 'Single-sided',
        '양면': 'Double-sided'
    };

    var cartData = null;

    // ── Helpers ──
    function fmt(n) { return '₩' + Number(n).toLocaleString('en-US'); }
    function toUsd(krw) { return '$' + (krw / EXCHANGE_RATE).toFixed(2); }
    function translateKey(k) { return DETAIL_KEYS[k] || k; }
    function translateVal(v) {
        if (typeof v !== 'string') return v;
        // Check exact match first
        if (DETAIL_VALUES[v]) return DETAIL_VALUES[v];
        // Check partial matches
        for (var kr in DETAIL_VALUES) {
            if (v.indexOf(kr) !== -1) {
                v = v.replace(kr, DETAIL_VALUES[kr]);
            }
        }
        return v;
    }

    // ── State management ──
    function showState(state) {
        ['stateLoading', 'stateEmpty', 'stateError', 'stateLoaded'].forEach(function(id) {
            document.getElementById(id).style.display = 'none';
        });
        document.getElementById(state).style.display = state === 'stateLoaded' ? 'grid' : '';
    }

    // ── Load cart ──
    function loadCart() {
        showState('stateLoading');
        fetch('/mlangprintauto/shop/get_basket_items.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    document.getElementById('errorMsg').textContent = data.message || 'Failed to load cart.';
                    showState('stateError');
                    return;
                }
                cartData = data;
                if (!data.items || data.items.length === 0) {
                    showState('stateEmpty');
                    document.getElementById('itemCount').textContent = '';
                    return;
                }
                renderCart(data);
                showState('stateLoaded');
            })
            .catch(function(err) {
                console.error('Cart load error:', err);
                document.getElementById('errorMsg').textContent = 'Network error. Please check your connection.';
                showState('stateError');
            });
    }
    window.loadCart = loadCart;

    // ── Render cart items ──
    function renderCart(data) {
        var container = document.getElementById('cartItems');
        container.innerHTML = '';

        document.getElementById('itemCount').textContent = data.count + ' item' + (data.count !== 1 ? 's' : '') + ' in your cart';

        data.items.forEach(function(item) {
            var el = document.createElement('div');
            el.className = 'cart-item';
            el.id = 'cart-item-' + item.no;

            var productName = PRODUCT_NAMES[item.product_type] || item.name || 'Print Product';
            var imgFile = PRODUCT_IMAGES[item.product_type] || 'inserted_s.png';

            // Build details HTML
            var detailsHtml = '';
            if (item.details && typeof item.details === 'object') {
                for (var key in item.details) {
                    if (!item.details.hasOwnProperty(key)) continue;
                    var val = item.details[key];
                    if (!val || val === '0' || val === '') continue;
                    detailsHtml += '<span class="cart-item-detail"><span class="key">' +
                        translateKey(key) + ':</span> <span class="val">' +
                        translateVal(val) + '</span></span>';
                }
            }

            // Comment
            var commentHtml = '';
            if (item.MY_comment && item.MY_comment.trim()) {
                commentHtml = '<div class="cart-item-comment">Note: ' + escHtml(item.MY_comment) + '</div>';
            }

            var priceRaw = item.st_price_vat_raw || 0;

            el.innerHTML =
                '<div class="cart-item-img"><img src="/ImgFolder/gate_picto/' + imgFile + '" alt="' + escHtml(productName) + '"></div>' +
                '<div class="cart-item-body">' +
                    '<div class="cart-item-name">' + escHtml(productName) + '</div>' +
                    '<div class="cart-item-details">' + detailsHtml + '</div>' +
                    commentHtml +
                '</div>' +
                '<div class="cart-item-actions">' +
                    '<div class="cart-item-price">' +
                        '<div class="krw">' + fmt(priceRaw) + '</div>' +
                        '<div class="usd">&asymp; ' + toUsd(priceRaw) + ' USD</div>' +
                    '</div>' +
                    '<button class="btn-remove" data-no="' + item.no + '" onclick="removeItem(this)">' +
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>' +
                        'Remove' +
                    '</button>' +
                '</div>';

            container.appendChild(el);
        });

        updateSummary(data);
    }

    // ── Update summary ──
    function updateSummary(data) {
        var supply = data.total_raw || 0;
        var totalVat = data.total_vat_raw || 0;
        var vat = totalVat - supply;

        document.getElementById('summaryCount').textContent = data.count;
        document.getElementById('summarySubtotal').textContent = fmt(supply);
        document.getElementById('summaryVat').textContent = fmt(vat);
        document.getElementById('summaryTotal').textContent = fmt(totalVat);
        document.getElementById('summaryUsd').textContent = '\u2248 ' + toUsd(totalVat) + ' USD';

        if (EXCHANGE_RATE && EXCHANGE_DATE) {
            var datePart = EXCHANGE_DATE.split(' ')[0] || EXCHANGE_DATE;
            document.getElementById('summaryRate').textContent =
                'Rate: \u20A9' + Number(EXCHANGE_RATE).toLocaleString('en-US', {minimumFractionDigits: 2}) +
                '/USD (' + datePart + ')';
        }

        document.getElementById('btnCheckout').disabled = !data.count;
    }

    // ── Remove item ──
    function removeItem(btn) {
        var no = btn.getAttribute('data-no');
        if (!confirm('Remove this item from your cart?')) return;

        btn.disabled = true;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></path></svg> Removing...';

        var formData = new FormData();
        formData.append('no', no);

        fetch('/mlangprintauto/shop/remove_from_basket.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success) {
                // Animate removal
                var row = document.getElementById('cart-item-' + no);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(function() {
                        row.remove();
                        // Reload to get fresh totals
                        loadCart();
                    }, 300);
                } else {
                    loadCart();
                }
            } else {
                alert('Failed to remove item. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg> Remove';
            }
        })
        .catch(function() {
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg> Remove';
        });
    }
    window.removeItem = removeItem;

    // ── Checkout ──
    function goCheckout() {
        window.location.href = '/en/checkout.php';
    }
    window.goCheckout = goCheckout;

    // ── Escape HTML ──
    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Init ──
    loadCart();

    // ── Handle ?added= param (from product pages) ──
    var params = new URLSearchParams(window.location.search);
    var added = params.get('added');
    if (added) {
        var name = PRODUCT_NAMES[added] || added;
        // Remove param from URL silently
        window.history.replaceState({}, '', '/en/cart.php');
    }

})();
</script>

</body>
</html>
