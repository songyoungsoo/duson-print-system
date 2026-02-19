<?php
session_start();
include __DIR__ . '/includes/exchange_rate.php';
include __DIR__ . '/../includes/csrf.php';
include __DIR__ . '/../db.php';
include __DIR__ . '/../mlangprintauto/shop_temp_helper.php';

$exchangeRate = getExchangeRate();
$session_id = session_id();

$cart_result = getCartItems($db, $session_id);
$items = [];
$total_supply = 0;
$total_vat_amount = 0;
if ($cart_result) {
    while ($row = mysqli_fetch_assoc($cart_result)) {
        $formatted = formatCartItemForDisplay($db, $row);
        $items[] = [
            'no' => $row['no'],
            'product_type' => $row['product_type'],
            'name' => $formatted['name'],
            'details' => $formatted['details'],
            'st_price' => $row['st_price'],
            'st_price_vat' => $row['st_price_vat']
        ];
        $total_supply += $row['st_price'];
        $total_vat_amount += $row['st_price_vat'];
    }
}
$vat_only = $total_vat_amount - $total_supply;
$item_count = count($items);

if (isset($db) && $db) { mysqli_close($db); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Duson Print</title>
    <link rel="icon" type="image/png" href="/ImgFolder/dusonlogo1.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy: #1E4E79; --navy-dark: #132F4C; --navy-light: #2D6FA8;
            --gold: #FFD500; --gold-dark: #D4B000; --bg: #FAFBFC;
            --text: #1A1A2E; --text-muted: #64748B; --text-light: #94A3B8;
            --white: #FFFFFF; --border: #E2E8F0;
            --radius: 14px; --radius-lg: 20px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --font-body: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-heading: 'Sora', 'DM Sans', sans-serif;
            --green: #059669; --green-light: #ECFDF5;
            --red: #DC2626; --red-light: #FEF2F2;
        }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font-body); color: var(--text); background: var(--bg); line-height: 1.6; -webkit-font-smoothing: antialiased; }

        /* LAYOUT */
        .page { max-width: 1200px; margin: 0 auto; padding: 130px 24px 60px; }
        .page h1 { font-family: var(--font-heading); font-size: 32px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .page-sub { font-size: 15px; color: var(--text-muted); margin-bottom: 32px; }
        .layout { display: grid; grid-template-columns: 1fr 380px; gap: 32px; align-items: start; }

        /* EMPTY */
        .empty-box { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 60px 32px; text-align: center; }
        .empty-box h2 { font-family: var(--font-heading); font-size: 22px; margin-bottom: 8px; }
        .empty-box p { color: var(--text-muted); margin-bottom: 20px; }
        .empty-box a { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 12px; background: var(--navy); color: var(--white); text-decoration: none; font-weight: 600; font-size: 14px; }

        /* FORM */
        .card { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 28px; margin-bottom: 20px; }
        .card-title { font-family: var(--font-heading); font-size: 17px; font-weight: 600; color: var(--text); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .card-title .num { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: var(--navy); color: var(--white); font-size: 13px; font-weight: 700; }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
        .field label .req { color: var(--red); margin-left: 2px; }
        .field input, .field textarea, .field select {
            width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px;
            font-family: var(--font-body); font-size: 14px; color: var(--text); transition: border-color 0.2s;
        }
        .field input:focus, .field textarea:focus, .field select:focus { outline: none; border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,78,121,0.1); }
        .field textarea { resize: vertical; min-height: 80px; }
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .field .hint { font-size: 12px; color: var(--text-light); margin-top: 4px; }
        .radio-group { display: flex; flex-wrap: wrap; gap: 8px; }
        .radio-chip { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: 1.5px solid var(--border); background: var(--white); font-size: 13px; font-weight: 500; color: var(--text-muted); cursor: pointer; transition: all 0.2s; }
        .radio-chip:has(input:checked) { border-color: var(--navy); background: rgba(30,78,121,0.06); color: var(--navy); font-weight: 600; }
        .radio-chip input { display: none; }

        /* INFO BOX */
        .info-box { padding: 14px 16px; border-radius: 10px; font-size: 13px; line-height: 1.6; margin-bottom: 16px; }
        .info-box.blue { background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; }
        .info-box.amber { background: #FFF8E1; border: 1px solid #FFF3C4; color: #92400E; }

        /* SIDEBAR */
        .sidebar-card { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 24px; position: sticky; top: 128px; }
        .sidebar-title { font-family: var(--font-heading); font-size: 16px; font-weight: 600; margin-bottom: 16px; }
        .order-item { display: flex; justify-content: space-between; align-items: start; padding: 10px 0; border-bottom: 1px solid #F1F5F9; font-size: 13px; }
        .order-item:last-child { border-bottom: none; }
        .order-item-name { font-weight: 600; color: var(--text); max-width: 200px; }
        .order-item-price { font-weight: 600; color: var(--text); text-align: right; white-space: nowrap; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
        .summary-row .lbl { color: var(--text-muted); }
        .summary-row .val { font-weight: 600; }
        .summary-divider { border: none; border-top: 1px solid var(--border); margin: 8px 0; }
        .summary-total { display: flex; justify-content: space-between; padding: 12px 0 4px; }
        .summary-total .lbl { font-family: var(--font-heading); font-size: 16px; font-weight: 600; }
        .summary-total .val { font-family: var(--font-heading); font-size: 20px; font-weight: 700; color: var(--navy); }
        .summary-usd { text-align: right; font-size: 13px; color: var(--text-light); margin-bottom: 4px; }
        .summary-rate { text-align: right; font-size: 11px; color: var(--text-light); margin-bottom: 20px; }
        .btn-submit { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px; border-radius: 12px; border: none; background: var(--navy); color: var(--white); font-family: var(--font-heading); font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-submit:hover { background: var(--navy-dark); transform: translateY(-1px); }
        .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .back-link { display: block; text-align: center; margin-top: 12px; font-size: 13px; color: var(--text-muted); text-decoration: none; }
        .back-link:hover { color: var(--navy); }

        /* FOOTER */
        .footer { background: var(--navy-dark); color: rgba(255,255,255,0.6); padding: 40px 24px; margin-top: 60px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
        .footer-brand { font-family: var(--font-heading); font-weight: 600; color: rgba(255,255,255,0.9); font-size: 15px; margin-bottom: 4px; }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; }

        /* RESPONSIVE */
        @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } .sidebar-card { position: static; } }
        @media (max-width: 640px) {
            .page { padding: 120px 16px 40px; }
            .page h1 { font-size: 24px; }
            .card { padding: 20px; }
            .field-row { grid-template-columns: 1fr; }
            .footer-inner { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<?php $_en_current_page = ''; include __DIR__ . '/includes/nav.php'; ?>

<div class="page">
    <h1>Checkout</h1>
    <p class="page-sub">Complete your order details below. All items ship within South Korea.</p>

<?php if ($item_count === 0): ?>
    <div class="empty-box">
        <h2>Your cart is empty</h2>
        <p>Add some products before checking out.</p>
        <a href="/en/products/">Browse Products</a>
    </div>
<?php else: ?>
    <form id="orderForm" method="POST" action="/mlangorder_printauto/ProcessOrder_unified.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="session_id" value="<?= htmlspecialchars($session_id) ?>">
        <input type="hidden" name="is_direct_order" value="0">
        <input type="hidden" name="total_price" value="<?= $total_supply ?>">
        <input type="hidden" name="total_price_vat" value="<?= $total_vat_amount ?>">
        <input type="hidden" name="items_count" value="<?= $item_count ?>">
        <input type="hidden" name="lang" value="en">

        <div class="layout">
            <div>
                <!-- Shipping Notice -->
                <div class="info-box blue" style="margin-bottom: 20px;">
                    <strong>Shipping within Korea only</strong><br>
                    All printed items are delivered to a Korean address. If you are overseas, please provide the address of someone in Korea who can receive on your behalf. For international forwarding options, contact us at <a href="mailto:dsp1830@naver.com" style="color:#1E40AF;font-weight:600;">dsp1830@naver.com</a>.
                </div>

                <!-- Contact Info -->
                <div class="card">
                    <div class="card-title"><span class="num">1</span> Contact Information</div>
                    <div class="field-row">
                        <div class="field">
                            <label>Full Name / Company <span class="req">*</span></label>
                            <input type="text" name="username" required placeholder="e.g. John Kim">
                        </div>
                        <div class="field">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" name="email" required placeholder="your@email.com">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label>Phone <span class="req">*</span></label>
                            <input type="tel" name="phone" required placeholder="e.g. 010-1234-5678">
                            <div class="hint">Korean phone number preferred for delivery coordination</div>
                        </div>
                        <div class="field">
                            <label>Mobile (optional)</label>
                            <input type="tel" name="Hendphone" placeholder="e.g. 010-9876-5432">
                        </div>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="card">
                    <div class="card-title"><span class="num">2</span> Delivery Address (Korea)</div>
                    <input type="hidden" name="address_option" value="different">
                    <div class="field">
                        <label>Postal Code</label>
                        <input type="text" name="sample6_postcode" placeholder="e.g. 07301">
                    </div>
                    <div class="field">
                        <label>Address <span class="req">*</span></label>
                        <input type="text" name="sample6_address" required placeholder="Street address in Korea">
                        <div class="hint">Korean address where items will be delivered</div>
                    </div>
                    <div class="field">
                        <label>Detail Address</label>
                        <input type="text" name="sample6_detailAddress" placeholder="Building, floor, unit number">
                    </div>
                    <input type="hidden" name="sample6_extraAddress" value="">
                </div>

                <!-- Delivery & Payment -->
                <div class="card">
                    <div class="card-title"><span class="num">3</span> Delivery & Payment</div>

                    <div class="field">
                        <label>Delivery Method</label>
                        <div class="radio-group">
                            <label class="radio-chip"><input type="radio" name="delivery_method" value="택배" checked> Courier (Parcel)</label>
                            <label class="radio-chip"><input type="radio" name="delivery_method" value="방문(방문시 전화)"> Pickup (Seoul office)</label>
                        </div>
                    </div>

                    <div class="field">
                        <label>Payment Method</label>
                        <div class="radio-group">
                            <label class="radio-chip"><input type="radio" name="payment_method" value="계좌이체" checked> Bank Transfer</label>
                            <label class="radio-chip"><input type="radio" name="payment_method" value="카드결제"> Card Payment</label>
                        </div>
                    </div>

                    <div class="info-box amber" id="bankInfo">
                        <strong>Bank Transfer Details</strong><br>
                        After placing your order, transfer to:<br>
                        <strong>IBK (기업은행) 066-066881-04-011</strong><br>
                        Account holder: 두손기획인쇄 (Duson Planning Print)<br>
                        Please include your name as transfer reference.
                    </div>

                    <div class="field" id="banknameField">
                        <label>Depositor Name</label>
                        <input type="text" name="bankname" placeholder="Name used for bank transfer">
                        <div class="hint">Enter the name that will appear on the bank transfer</div>
                    </div>

                    <div class="field">
                        <label>Special Requests (optional)</label>
                        <textarea name="cont" placeholder="Any special instructions for your print order..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div>
                <div class="sidebar-card">
                    <div class="sidebar-title">Order Summary</div>

                    <?php
                    $PRODUCT_NAMES = [
                        'sticker' => 'Stickers', 'inserted' => 'Flyers', 'namecard' => 'Business Cards',
                        'envelope' => 'Envelopes', 'littleprint' => 'Posters', 'merchandisebond' => 'Gift Vouchers',
                        'msticker' => 'Magnetic Stickers', 'cadarok' => 'Catalogs', 'ncrflambeau' => 'NCR Forms',
                        'leaflet' => 'Flyers'
                    ];
                    foreach ($items as $item):
                        $pname = $PRODUCT_NAMES[$item['product_type']] ?? $item['name'];
                    ?>
                    <div class="order-item">
                        <div class="order-item-name"><?= htmlspecialchars($pname) ?></div>
                        <div class="order-item-price"><?= '₩' . number_format($item['st_price_vat']) ?></div>
                    </div>
                    <?php endforeach; ?>

                    <hr class="summary-divider" style="margin-top: 12px;">
                    <div class="summary-row">
                        <span class="lbl"><?= $item_count ?> item<?= $item_count > 1 ? 's' : '' ?></span>
                        <span class="val"><?= '₩' . number_format($total_supply) ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="lbl">VAT (10%)</span>
                        <span class="val"><?= '₩' . number_format($vat_only) ?></span>
                    </div>
                    <hr class="summary-divider">
                    <div class="summary-total">
                        <span class="lbl">Total</span>
                        <span class="val"><?= '₩' . number_format($total_vat_amount) ?></span>
                    </div>
                    <?php
                    $rate = $exchangeRate['rate'] ?? 1450;
                    $date = $exchangeRate['date'] ?? '';
                    $usd = $total_vat_amount / $rate;
                    $datePart = explode(' ', $date)[0] ?? $date;
                    ?>
                    <div class="summary-usd">&asymp; $<?= number_format($usd, 2) ?> USD</div>
                    <div class="summary-rate">Rate: ₩<?= number_format($rate, 2) ?>/USD (<?= htmlspecialchars($datePart) ?>)</div>

                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        Place Order
                    </button>
                    <a href="/en/cart.php" class="back-link">Back to Cart</a>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>
</div>

<footer class="footer">
    <div class="footer-inner">
        <div><div class="footer-brand">DUSON PRINT</div><div>Seoul, South Korea &middot; Factory-direct printing since 1995</div></div>
        <div><a href="mailto:dsp1830@naver.com">dsp1830@naver.com</a> &middot; <a href="tel:+82-2-2632-1830">+82-2-2632-1830</a></div>
    </div>
</footer>

<script>
(function() {
    'use strict';
    // Payment method toggle
    var radios = document.querySelectorAll('input[name="payment_method"]');
    var bankInfo = document.getElementById('bankInfo');
    var banknameField = document.getElementById('banknameField');
    radios.forEach(function(r) {
        r.addEventListener('change', function() {
            var isBank = this.value === '계좌이체';
            if (bankInfo) bankInfo.style.display = isBank ? '' : 'none';
            if (banknameField) banknameField.style.display = isBank ? '' : 'none';
        });
    });

    // Form submission guard
    var form = document.getElementById('orderForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></path></svg> Processing...';
        });
    }
})();
</script>

</body>
</html>
