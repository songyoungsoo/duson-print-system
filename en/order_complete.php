<?php
session_start();
include __DIR__ . '/../db.php';

$order_numbers_raw = $_GET['orders'] ?? '';
$email = $_GET['email'] ?? '';
$name = $_GET['name'] ?? '';

$orders = [];
if (!empty($order_numbers_raw)) {
    $order_nos = array_filter(array_map('intval', explode(',', $order_numbers_raw)));

    if (!empty($order_nos) && $db) {
        $placeholders = implode(',', array_fill(0, count($order_nos), '?'));
        $types = str_repeat('i', count($order_nos));
        $query = "SELECT no, Type, product_type, money_4, money_5, name, date FROM mlangorder_printauto WHERE no IN ($placeholders) ORDER BY no ASC";
        $stmt = mysqli_prepare($db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$order_nos);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $orders[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$PRODUCT_NAMES = [
    'sticker' => 'Stickers & Labels', 'inserted' => 'Flyers & Leaflets',
    'namecard' => 'Business Cards', 'envelope' => 'Envelopes',
    'littleprint' => 'Posters', 'merchandisebond' => 'Gift Vouchers',
    'msticker' => 'Magnetic Stickers', 'cadarok' => 'Catalogs & Booklets',
    'ncrflambeau' => 'NCR Forms', 'leaflet' => 'Flyers & Leaflets'
];

if (isset($db) && $db) { mysqli_close($db); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed — Duson Print</title>
    <link rel="icon" type="image/png" href="/ImgFolder/dusonlogo1.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy: #1E4E79; --navy-dark: #132F4C; --navy-light: #2D6FA8;
            --gold: #FFD500; --bg: #FAFBFC;
            --text: #1A1A2E; --text-muted: #64748B; --text-light: #94A3B8;
            --white: #FFFFFF; --border: #E2E8F0;
            --radius: 14px; --radius-lg: 20px;
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --font-body: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-heading: 'Sora', 'DM Sans', sans-serif;
            --green: #059669; --green-light: #ECFDF5;
        }
        body { font-family: var(--font-body); color: var(--text); background: var(--bg); line-height: 1.6; -webkit-font-smoothing: antialiased; }

        .page { max-width: 720px; margin: 0 auto; padding: 140px 24px 60px; text-align: center; }

        .success-icon {
            width: 80px; height: 80px; border-radius: 50%;
            background: var(--green-light); display: flex; align-items: center;
            justify-content: center; margin: 0 auto 24px;
        }
        .page h1 { font-family: var(--font-heading); font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .page .sub { font-size: 16px; color: var(--text-muted); margin-bottom: 36px; }

        .card { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 28px; margin-bottom: 20px; text-align: left; }
        .card-title { font-family: var(--font-heading); font-size: 16px; font-weight: 600; margin-bottom: 16px; }
        .order-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #F1F5F9; font-size: 14px; }
        .order-row:last-child { border-bottom: none; }
        .order-no { font-family: monospace; font-size: 13px; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; color: var(--text-muted); }
        .order-product { font-weight: 600; }
        .order-price { font-weight: 600; color: var(--navy); }

        .info-box { padding: 16px 20px; border-radius: 12px; font-size: 14px; line-height: 1.7; margin-bottom: 20px; text-align: left; }
        .info-box.blue { background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; }
        .info-box.amber { background: #FFF8E1; border: 1px solid #FFF3C4; color: #92400E; }
        .info-box strong { display: block; margin-bottom: 4px; }

        .actions { display: flex; gap: 12px; justify-content: center; margin-top: 32px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.2s; cursor: pointer; border: none; }
        .btn-primary { background: var(--navy); color: var(--white); }
        .btn-primary:hover { background: var(--navy-dark); }
        .btn-outline { background: var(--white); color: var(--navy); border: 1.5px solid var(--navy); }
        .btn-outline:hover { background: rgba(30,78,121,0.04); }

        .footer { background: var(--navy-dark); color: rgba(255,255,255,0.6); padding: 40px 24px; margin-top: 60px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
        .footer-brand { font-family: var(--font-heading); font-weight: 600; color: rgba(255,255,255,0.9); font-size: 15px; margin-bottom: 4px; }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; }

        @media (max-width: 640px) {
            .page { padding: 120px 16px 40px; }
            .page h1 { font-size: 24px; }
            .card { padding: 20px; }
            .actions { flex-direction: column; }
            .btn { justify-content: center; }
            .footer-inner { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<?php $_en_current_page = ''; include __DIR__ . '/includes/nav.php'; ?>

<div class="page">
    <div class="success-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
    </div>

    <h1>Order Confirmed!</h1>
    <p class="sub">Thank you<?= $name ? ', ' . htmlspecialchars($name) : '' ?>. Your order has been received and is being processed.</p>

    <?php if (!empty($orders)): ?>
    <div class="card">
        <div class="card-title">Order Details</div>
        <?php foreach ($orders as $o):
            $pname = $PRODUCT_NAMES[$o['product_type']] ?? $o['Type'];
        ?>
        <div class="order-row">
            <div>
                <span class="order-no">#<?= $o['no'] ?></span>
                <span class="order-product"><?= htmlspecialchars($pname) ?></span>
            </div>
            <span class="order-price"><?= '₩' . number_format($o['money_5']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="info-box amber">
        <strong>Payment: Bank Transfer</strong>
        Please transfer the total amount to:<br>
        <strong>IBK (기업은행) 066-066881-04-011</strong><br>
        Account holder: 두손기획인쇄 (Duson Planning Print)<br>
        Include your name or order number as reference.
    </div>

    <div class="info-box blue">
        <strong>What happens next?</strong>
        1. We verify your payment (1-2 business days)<br>
        2. Your files are reviewed / design begins<br>
        3. Printing &amp; quality check (3-5 business days)<br>
        4. Shipped to your Korean address via courier<br><br>
        Questions? Email <a href="mailto:dsp1830@naver.com" style="color:#1E40AF;font-weight:600;">dsp1830@naver.com</a> or call <a href="tel:+82-2-2632-1830" style="color:#1E40AF;font-weight:600;">+82-2-2632-1830</a>
    </div>

    <?php if ($email): ?>
    <p style="font-size:13px;color:var(--text-light);margin-bottom:24px;">
        A confirmation email will be sent to <strong><?= htmlspecialchars($email) ?></strong>.
    </p>
    <?php endif; ?>

    <div class="actions">
        <a href="/en/products/" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4"/><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg>
            Continue Shopping
        </a>
        <a href="/en/" class="btn btn-outline">Back to Home</a>
    </div>
</div>

<footer class="footer">
    <div class="footer-inner">
        <div><div class="footer-brand">DUSON PRINT</div><div>Seoul, South Korea &middot; Factory-direct printing since 1995</div></div>
        <div><a href="mailto:dsp1830@naver.com">dsp1830@naver.com</a> &middot; <a href="tel:+82-2-2632-1830">+82-2-2632-1830</a></div>
    </div>
</footer>



</body>
</html>
