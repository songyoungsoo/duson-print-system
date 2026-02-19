<?php
session_start();

// ── Product Configuration ──────────────────────────────────────────────
$products = [
    'inserted' => [
        'name' => 'Flyers & Leaflets',
        'name_kr' => '전단지',
        'ttable' => 'inserted',
        'unit' => 'ream',
        'image' => '/ImgFolder/gate_picto/inserted_s.png',
        'description' => 'High-quality offset printed flyers for mass distribution. Gang-run printing for cost-effective production.',
        'dropdowns' => [
            ['id' => 'MY_type', 'label' => 'Print Color', 'placeholder' => 'Select print color'],
            ['id' => 'MY_Fsd', 'label' => 'Paper Type', 'placeholder' => 'Select paper type', 'depends_on' => 'MY_type', 'api' => '/mlangprintauto/inserted/get_paper_types.php?CV_no={MY_type}&page=inserted'],
            ['id' => 'PN_type', 'label' => 'Size', 'placeholder' => 'Select size', 'depends_on' => 'MY_type', 'api' => '/mlangprintauto/inserted/get_paper_sizes.php?CV_no={MY_type}&page=inserted'],
            ['id' => 'POtype', 'label' => 'Print Side', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Single-Sided'], ['value' => '2', 'text' => 'Double-Sided']]],
            ['id' => 'MY_amount', 'label' => 'Quantity', 'placeholder' => 'Select quantity', 'depends_on' => 'PN_type', 'api' => '/mlangprintauto/inserted/get_quantities.php?MY_type={MY_type}&PN_type={PN_type}&MY_Fsd={MY_Fsd}&POtype={POtype}'],
            ['id' => 'ordertype', 'label' => 'Design Service', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Print Only (I have files)'], ['value' => '2', 'text' => 'Design + Print (+fee)']]]
        ],
        'price_api' => '/mlangprintauto/inserted/calculate_price_ajax.php',
        'cart_api' => '/mlangprintauto/inserted/add_to_basket.php',
        'additional_options' => true
    ],
    'littleprint' => [
        'name' => 'Posters',
        'name_kr' => '포스터',
        'ttable' => 'LittlePrint',
        'unit' => 'sheets',
        'image' => '/ImgFolder/gate_picto/poster_s.png',
        'description' => 'Large format posters with vivid color reproduction. Available in various sizes.',
        'dropdowns' => [
            ['id' => 'MY_type', 'label' => 'Type', 'placeholder' => 'Select type'],
            ['id' => 'Section', 'label' => 'Paper Type', 'placeholder' => 'Select paper', 'depends_on' => 'MY_type', 'api' => '/mlangprintauto/littleprint/get_paper_types.php?style={MY_type}'],
            ['id' => 'PN_type', 'label' => 'Size', 'placeholder' => 'Select size', 'depends_on' => 'Section', 'api' => '/mlangprintauto/littleprint/get_paper_sizes.php?section={Section}'],
            ['id' => 'POtype', 'label' => 'Print Side', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Single-Sided'], ['value' => '2', 'text' => 'Double-Sided']]],
            ['id' => 'MY_amount', 'label' => 'Quantity', 'placeholder' => 'Select quantity', 'depends_on' => 'PN_type', 'api' => '/mlangprintauto/littleprint/get_quantities.php?style={MY_type}&section={Section}&size={PN_type}&potype={POtype}'],
            ['id' => 'ordertype', 'label' => 'Design Service', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Print Only'], ['value' => '2', 'text' => 'Design + Print']]]
        ],
        'price_api' => '/mlangprintauto/littleprint/calculate_price_ajax.php',
        'cart_api' => '/mlangprintauto/littleprint/add_to_basket.php',
        'additional_options' => false
    ],
    'merchandisebond' => [
        'name' => 'Gift Vouchers',
        'name_kr' => '상품권',
        'ttable' => 'MerchandiseBond',
        'unit' => 'sheets',
        'image' => '/ImgFolder/gate_picto/merchandise_s.png',
        'description' => 'Custom gift vouchers and coupons with professional printing and security features.',
        'dropdowns' => [
            ['id' => 'MY_type', 'label' => 'Type', 'placeholder' => 'Select type'],
            ['id' => 'Section', 'label' => 'Paper Type', 'placeholder' => 'Select paper', 'depends_on' => 'MY_type', 'api' => '/mlangprintauto/merchandisebond/get_paper_types.php?style={MY_type}'],
            ['id' => 'POtype', 'label' => 'Print Side', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Single-Sided'], ['value' => '2', 'text' => 'Double-Sided']]],
            ['id' => 'MY_amount', 'label' => 'Quantity', 'placeholder' => 'Select quantity', 'depends_on' => 'Section', 'api' => '/mlangprintauto/merchandisebond/get_quantities.php?style={MY_type}&section={Section}&potype={POtype}'],
            ['id' => 'ordertype', 'label' => 'Design Service', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Print Only'], ['value' => '2', 'text' => 'Design + Print']]]
        ],
        'price_api' => '/mlangprintauto/merchandisebond/calculate_price_ajax.php',
        'cart_api' => '/mlangprintauto/merchandisebond/add_to_basket.php',
        'additional_options' => false
    ],
    'msticker' => [
        'name' => 'Magnetic Stickers',
        'name_kr' => '자석스티커',
        'ttable' => 'msticker',
        'unit' => 'sheets',
        'image' => '/ImgFolder/gate_picto/m_sticker_s.png',
        'description' => 'Durable magnetic stickers for promotional use. Perfect for refrigerator magnets and vehicle signs.',
        'dropdowns' => [
            ['id' => 'MY_type', 'label' => 'Type', 'placeholder' => 'Select type'],
            ['id' => 'Section', 'label' => 'Size', 'placeholder' => 'Select size', 'depends_on' => 'MY_type', 'api' => '/mlangprintauto/msticker/get_paper_types.php?style={MY_type}'],
            ['id' => 'MY_amount', 'label' => 'Quantity', 'placeholder' => 'Select quantity', 'depends_on' => 'Section', 'api' => '/mlangprintauto/msticker/get_quantities.php?style={MY_type}&Section={Section}'],
            ['id' => 'ordertype', 'label' => 'Design Service', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Print Only'], ['value' => '2', 'text' => 'Design + Print']]]
        ],
        'price_api' => '/mlangprintauto/msticker/calculate_price_ajax.php',
        'cart_api' => '/mlangprintauto/msticker/add_to_basket.php',
        'additional_options' => false
    ],
    'envelope' => [
        'name' => 'Envelopes',
        'name_kr' => '봉투',
        'ttable' => 'envelope',
        'unit' => 'sheets',
        'image' => '/ImgFolder/gate_picto/envelop_s.png',
        'description' => 'Professional business envelopes in various sizes. Custom printing for branding.',
        'dropdowns' => [
            ['id' => 'MY_type', 'label' => 'Type', 'placeholder' => 'Select type'],
            ['id' => 'Section', 'label' => 'Paper Type', 'placeholder' => 'Select paper', 'depends_on' => 'MY_type', 'api' => '/mlangprintauto/envelope/get_paper_types.php?style={MY_type}'],
            ['id' => 'POtype', 'label' => 'Print Side', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Single-Sided'], ['value' => '2', 'text' => 'Double-Sided']]],
            ['id' => 'MY_amount', 'label' => 'Quantity', 'placeholder' => 'Select quantity', 'depends_on' => 'Section', 'api' => '/mlangprintauto/envelope/get_quantities.php?style={MY_type}&section={Section}&potype={POtype}'],
            ['id' => 'ordertype', 'label' => 'Design Service', 'type' => 'static', 'options' => [['value' => '1', 'text' => 'Print Only'], ['value' => '2', 'text' => 'Design + Print']]]
        ],
        'price_api' => '/mlangprintauto/envelope/calculate_price_ajax.php',
        'cart_api' => '/mlangprintauto/envelope/add_to_basket.php',
        'additional_options' => false
    ]
];

$coming_soon = ['sticker', 'namecard', 'cadarok', 'ncrflambeau'];
$all_known = array_merge(array_keys($products), $coming_soon);
$coming_soon_names = [
    'sticker' => 'Stickers & Labels',
    'namecard' => 'Business Cards',
    'cadarok' => 'Catalogs & Booklets',
    'ncrflambeau' => 'NCR Forms'
];

$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$is_valid = in_array($type, $all_known);
$is_coming_soon = in_array($type, $coming_soon);
$is_supported = isset($products[$type]);

$initial_options = [];
if ($is_supported) {
    include '../../db.php';
    $ttable = $products[$type]['ttable'];
    $stmt = mysqli_prepare($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable=? AND BigNo='0' ORDER BY no ASC");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $ttable);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $initial_options[] = ['no' => $row['no'], 'title' => $row['title']];
        }
        mysqli_stmt_close($stmt);
    }
    // Don't close $db here — page might still need it (PHP 8.2 safety)
}

$product = $is_supported ? $products[$type] : null;
$page_title = $is_supported ? $product['name'] : ($is_coming_soon ? ($coming_soon_names[$type] ?? 'Coming Soon') : 'Product Not Found');

$exchangeRate = null;
if ($is_supported) {
    include __DIR__ . '/../includes/exchange_rate.php';
    $exchangeRate = getExchangeRate();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> — Order | Duson Print</title>
    <meta name="description" content="Order <?php echo htmlspecialchars($page_title); ?> online from Duson Print. Factory-direct pricing from Seoul, Korea.">
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

        /* ===== NAV ===== */
        .nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            transition: box-shadow 0.3s;
        }
        .nav.scrolled { box-shadow: 0 2px 20px rgba(0,0,0,0.06); }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; height: 64px;
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo img { height: 36px; width: auto; }
        .nav-logo span {
            font-family: var(--font-heading); font-weight: 700; font-size: 18px;
            color: var(--navy); letter-spacing: -0.5px;
        }
        .nav-links { display: flex; align-items: center; gap: 28px; }
        .nav-links a {
            font-size: 14px; font-weight: 500; color: var(--text-muted);
            text-decoration: none; transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--navy); }
        .nav-links a.active { color: var(--navy); font-weight: 700; }
        .nav-lang {
            font-size: 13px; color: var(--text-light);
            display: flex; align-items: center; gap: 6px;
        }
        .nav-lang a { color: var(--navy); font-weight: 600; text-decoration: none; }
        .nav-cta {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 22px; border-radius: 10px;
            background: var(--navy); color: var(--white);
            font-size: 14px; font-weight: 600; text-decoration: none;
            transition: all 0.25s;
        }
        .nav-cta:hover { background: var(--navy-dark); transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .nav-mobile-toggle {
            display: none; background: none; border: none; cursor: pointer;
            width: 32px; height: 32px; position: relative;
        }
        .nav-mobile-toggle span {
            display: block; width: 22px; height: 2px; background: var(--text);
            position: absolute; left: 5px; transition: all 0.3s;
        }
        .nav-mobile-toggle span:nth-child(1) { top: 9px; }
        .nav-mobile-toggle span:nth-child(2) { top: 15px; }
        .nav-mobile-toggle span:nth-child(3) { top: 21px; }

        /* ===== BREADCRUMB ===== */
        .breadcrumb-bar {
            padding: 80px 24px 0;
            max-width: 1200px; margin: 0 auto;
        }
        .breadcrumb {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-light); padding: 16px 0;
        }
        .breadcrumb a { color: var(--text-muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--navy); }
        .breadcrumb svg { width: 14px; height: 14px; opacity: 0.4; }

        /* ===== ORDER LAYOUT ===== */
        .order-wrap {
            max-width: 1200px; margin: 0 auto; padding: 8px 24px 80px;
            display: grid; grid-template-columns: 1fr 400px; gap: 36px;
            align-items: start;
        }

        /* ===== PRODUCT HEADER ===== */
        .product-header {
            display: flex; gap: 24px; align-items: center;
            margin-bottom: 32px; padding-bottom: 28px;
            border-bottom: 1px solid var(--border);
        }
        .product-header-img {
            width: 100px; height: 100px; flex-shrink: 0;
            background: var(--white); border-radius: var(--radius);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            padding: 14px;
        }
        .product-header-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .product-header-info h1 {
            font-family: var(--font-heading); font-size: 28px; font-weight: 800;
            letter-spacing: -0.5px; margin-bottom: 4px;
        }
        .product-header-info .kr-name {
            font-size: 14px; color: var(--text-light); margin-bottom: 6px;
        }
        .product-header-info p {
            font-size: 14px; color: var(--text-muted); line-height: 1.6;
        }

        /* ===== FORM ===== */
        .order-form-card {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1px solid var(--border); padding: 32px;
        }
        .form-section-title {
            font-family: var(--font-heading); font-size: 16px; font-weight: 700;
            margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
        }
        .form-section-title .num {
            width: 24px; height: 24px; border-radius: 7px;
            background: var(--navy); color: var(--white);
            font-size: 12px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .form-row { margin-bottom: 16px; }
        .form-label {
            display: block; font-size: 13px; font-weight: 600; color: var(--text);
            margin-bottom: 6px;
        }
        .form-select {
            width: 100%; padding: 12px 40px 12px 16px; border-radius: 10px;
            border: 1.5px solid var(--border); font-family: var(--font-body);
            font-size: 14px; color: var(--text); background: var(--bg);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none; cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        .form-select:focus {
            border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,78,121,0.1);
        }
        .form-select:disabled {
            opacity: 0.5; cursor: not-allowed; background-color: #f1f5f9;
        }
        .form-select.loading {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231E4E79' stroke-width='2'%3E%3Cpath d='M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='1s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
        }
        .kr-note {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: var(--text-light); margin-top: 6px;
            padding: 8px 12px; background: #FFF8E1; border-radius: 8px;
            border: 1px solid #FFF3C4;
        }
        .kr-note svg { flex-shrink: 0; }

        /* ===== ADDITIONAL OPTIONS (inserted only) ===== */
        .addl-opts {
            margin-top: 8px; padding-top: 24px;
            border-top: 1px solid var(--border);
        }
        .addl-opt-group { margin-bottom: 14px; }
        .addl-opt-label {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--text); margin-bottom: 6px;
        }
        .addl-opt-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .addl-chip {
            padding: 8px 14px; border-radius: 8px;
            border: 1.5px solid var(--border); background: var(--white);
            font-size: 13px; font-weight: 500; color: var(--text-muted);
            cursor: pointer; transition: all 0.2s; user-select: none;
        }
        .addl-chip:hover { border-color: var(--navy-light); color: var(--navy); }
        .addl-chip.selected {
            border-color: var(--navy); background: rgba(30,78,121,0.06);
            color: var(--navy); font-weight: 600;
        }
        .addl-chip input { display: none; }

        /* ===== PRICE SIDEBAR ===== */
        .price-card {
            background: var(--white); border-radius: var(--radius-lg);
            border: 1px solid var(--border); padding: 28px;
            position: sticky; top: 84px;
        }
        .price-card-title {
            font-family: var(--font-heading); font-size: 16px; font-weight: 700;
            margin-bottom: 20px;
        }
        .price-empty {
            text-align: center; padding: 32px 16px;
            color: var(--text-light); font-size: 14px;
        }
        .price-empty svg { margin-bottom: 10px; opacity: 0.35; }
        .price-breakdown { display: none; }
        .price-breakdown.visible { display: block; }
        .price-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; font-size: 14px;
        }
        .price-row-label { color: var(--text-muted); }
        .price-row-value { font-weight: 600; font-family: var(--font-heading); }
        .price-divider {
            border: none; border-top: 1px dashed var(--border);
            margin: 4px 0;
        }
        .price-total {
            display: flex; justify-content: space-between; align-items: baseline;
            padding: 14px 0 0; margin-top: 4px;
            border-top: 2px solid var(--navy);
        }
        .price-total-label {
            font-family: var(--font-heading); font-size: 16px; font-weight: 700;
        }
        .price-total-value {
            font-family: var(--font-heading); font-size: 26px; font-weight: 800;
            color: var(--navy); letter-spacing: -0.5px;
        }
        .price-total-value small {
            font-size: 14px; font-weight: 500; color: var(--text-muted);
        }
        .price-loading {
            display: none; text-align: center; padding: 12px;
            font-size: 13px; color: var(--navy);
        }
        .price-loading.visible { display: block; }

        .btn-cart {
            width: 100%; padding: 15px; border-radius: 12px;
            background: var(--navy); color: var(--white);
            font-family: var(--font-heading); font-size: 16px; font-weight: 700;
            border: none; cursor: pointer; transition: all 0.25s;
            margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-cart:hover:not(:disabled) {
            background: var(--navy-dark); transform: translateY(-1px); box-shadow: var(--shadow-md);
        }
        .btn-cart:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

        .btn-quote-link {
            display: block; text-align: center; margin-top: 14px;
            font-size: 14px; font-weight: 600; color: var(--navy);
            text-decoration: none; transition: color 0.2s;
        }
        .btn-quote-link:hover { color: var(--navy-dark); text-decoration: underline; }

        .price-error {
            display: none; padding: 10px 14px; border-radius: 8px;
            font-size: 13px; margin-top: 12px;
            background: var(--red-light); color: var(--red);
            border: 1px solid #FECACA;
        }
        .price-error.visible { display: block; }

        /* ===== COMING SOON / 404 ===== */
        .status-page {
            max-width: 560px; margin: 0 auto; padding: 120px 24px 80px;
            text-align: center;
        }
        .status-icon {
            width: 80px; height: 80px; border-radius: 20px;
            background: linear-gradient(135deg, var(--navy), var(--navy-light));
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px; font-size: 36px;
        }
        .status-page h1 {
            font-family: var(--font-heading); font-size: 32px; font-weight: 800;
            margin-bottom: 12px; letter-spacing: -0.5px;
        }
        .status-page p {
            font-size: 16px; color: var(--text-muted); line-height: 1.7;
            margin-bottom: 28px;
        }
        .status-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 12px 28px; border-radius: 10px;
            background: var(--navy); color: var(--white);
            font-size: 15px; font-weight: 600; text-decoration: none;
            transition: all 0.25s;
        }
        .btn-primary:hover { background: var(--navy-dark); transform: translateY(-1px); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 12px 28px; border-radius: 10px;
            background: transparent; color: var(--navy);
            font-size: 15px; font-weight: 600; text-decoration: none;
            border: 1.5px solid var(--border); transition: all 0.25s;
        }
        .btn-secondary:hover { border-color: var(--navy); background: rgba(30,78,121,0.04); }

        /* ===== FOOTER ===== */
        .footer {
            background: var(--navy-dark); padding: 48px 24px 32px;
        }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 20px;
        }
        .footer-brand { display: flex; align-items: center; gap: 10px; }
        .footer-brand img { height: 28px; filter: brightness(10); }
        .footer-brand span { font-family: var(--font-heading); font-weight: 700; font-size: 16px; color: rgba(255,255,255,0.9); }
        .footer-copy { font-size: 13px; color: rgba(255,255,255,0.4); }
        .footer-links { display: flex; gap: 20px; }
        .footer-links a { font-size: 13px; color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.2s; }
        .footer-links a:hover { color: rgba(255,255,255,0.8); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .order-wrap {
                grid-template-columns: 1fr; gap: 24px;
            }
            .price-card { position: static; }
        }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .nav-mobile-toggle { display: block; }
            .nav-links.open {
                display: flex; flex-direction: column;
                position: absolute; top: 64px; left: 0; right: 0;
                background: var(--white); padding: 20px 24px;
                border-bottom: 1px solid var(--border); box-shadow: var(--shadow-md);
                gap: 16px;
            }
            .product-header { flex-direction: column; text-align: center; }
            .product-header-img { width: 80px; height: 80px; }
            .product-header-info h1 { font-size: 24px; }
            .order-form-card { padding: 22px; }
            .price-card { padding: 22px; }
            .status-page { padding: 100px 20px 60px; }
            .status-page h1 { font-size: 26px; }
            .footer-inner { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="nav" id="nav">
    <div class="nav-inner">
        <a href="/en/" class="nav-logo">
            <img src="/ImgFolder/dusonlogo1.png" alt="Duson Print">
            <span>DUSON PRINT</span>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="/en/products/" class="active">Products</a>
            <a href="/en/#why-us">Why Us</a>
            <a href="/en/#about">About</a>
            <a href="/en/#quote">Contact</a>
            <span class="nav-lang">EN | <a href="/">한국어</a></span>
            <a href="/en/#quote" class="nav-cta">Get Free Quote</a>
        </div>
        <button class="nav-mobile-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<?php if (!$is_valid): ?>
<!-- ===== 404 — Unknown Product ===== -->
<div class="status-page">
    <div class="status-icon">🔍</div>
    <h1>Product Not Found</h1>
    <p>The product you're looking for doesn't exist or the URL may be incorrect. Please browse our product catalog.</p>
    <div class="status-btns">
        <a href="/en/products/" class="btn-primary">View All Products</a>
        <a href="/en/#quote" class="btn-secondary">Request a Quote</a>
    </div>
</div>

<?php elseif ($is_coming_soon): ?>
<!-- ===== Coming Soon ===== -->
<div class="status-page">
    <div class="status-icon">🚀</div>
    <h1><?php echo htmlspecialchars($coming_soon_names[$type] ?? $type); ?></h1>
    <p>Online ordering for this product is coming soon. In the meantime, you can request a free quote and our team will respond within 24 hours.</p>
    <div class="status-btns">
        <a href="/en/#quote" class="btn-primary">
            Request Free Quote
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="/en/products/" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            All Products
        </a>
    </div>
</div>

<?php else: ?>
<!-- ===== BREADCRUMB ===== -->
<div class="breadcrumb-bar">
    <div class="breadcrumb">
        <a href="/en/">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <a href="/en/products/">Products</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
</div>

<!-- ===== ORDER FORM ===== -->
<div class="order-wrap">
    <div>
        <!-- Product Header -->
        <div class="product-header">
            <div class="product-header-img">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-header-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="kr-name"><?php echo htmlspecialchars($product['name_kr']); ?></div>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
            </div>
        </div>

        <!-- Configuration Form -->
        <div class="order-form-card">
            <div class="form-section-title">
                <span class="num">1</span> Configure Your Order
            </div>

            <form id="orderForm" onsubmit="return false;">
                <?php
                $showed_kr_note = false;
                foreach ($product['dropdowns'] as $idx => $dd):
                    $is_static = isset($dd['type']) && $dd['type'] === 'static';
                    $is_first = ($idx === 0);
                    $disabled = !$is_static && !$is_first;
                ?>
                <div class="form-row">
                    <label class="form-label" for="<?php echo $dd['id']; ?>"><?php echo htmlspecialchars($dd['label']); ?></label>
                    <select
                        id="<?php echo $dd['id']; ?>"
                        name="<?php echo $dd['id']; ?>"
                        class="form-select"
                        data-index="<?php echo $idx; ?>"
                        <?php if (isset($dd['depends_on'])): ?>data-depends="<?php echo $dd['depends_on']; ?>"<?php endif; ?>
                        <?php if (isset($dd['api'])): ?>data-api="<?php echo htmlspecialchars($dd['api']); ?>"<?php endif; ?>
                        <?php if ($disabled): ?>disabled<?php endif; ?>
                    >
                        <?php if ($is_static): ?>
                            <option value="">— Select —</option>
                            <?php foreach ($dd['options'] as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt['value']); ?>"><?php echo htmlspecialchars($opt['text']); ?></option>
                            <?php endforeach; ?>
                        <?php elseif ($is_first): ?>
                            <option value=""><?php echo htmlspecialchars($dd['placeholder'] ?? '— Select —'); ?></option>
                            <?php foreach ($initial_options as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt['no']); ?>"><?php echo htmlspecialchars($opt['title']); ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value=""><?php echo htmlspecialchars($dd['placeholder'] ?? '— Select —'); ?></option>
                        <?php endif; ?>
                    </select>
                    <?php if (!$is_static && !$showed_kr_note && !$is_first): $showed_kr_note = true; ?>
                    <div class="kr-note">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B8860B" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                        Option labels are shown in Korean. Contact us for translation assistance.
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if ($product['additional_options']): ?>
                <!-- Additional Options (Inserted/Flyers only) -->
                <div class="addl-opts">
                    <div class="form-section-title">
                        <span class="num">2</span> Finishing Options <span style="font-size:12px;font-weight:400;color:var(--text-light);">(optional)</span>
                    </div>

                    <div class="addl-opt-group">
                        <label class="addl-opt-label">Coating</label>
                        <div class="addl-opt-row" data-group="coating">
                            <label class="addl-chip selected"><input type="radio" name="coating" value="" checked> None</label>
                            <label class="addl-chip"><input type="radio" name="coating" value="single_gloss"> 1-Side Gloss</label>
                            <label class="addl-chip"><input type="radio" name="coating" value="double_gloss"> 2-Side Gloss</label>
                            <label class="addl-chip"><input type="radio" name="coating" value="single_matte"> 1-Side Matte</label>
                            <label class="addl-chip"><input type="radio" name="coating" value="double_matte"> 2-Side Matte</label>
                        </div>
                    </div>

                    <div class="addl-opt-group">
                        <label class="addl-opt-label">Folding</label>
                        <div class="addl-opt-row" data-group="folding">
                            <label class="addl-chip selected"><input type="radio" name="folding" value="" checked> None</label>
                            <label class="addl-chip"><input type="radio" name="folding" value="2panel"> 2-Panel</label>
                            <label class="addl-chip"><input type="radio" name="folding" value="3panel"> 3-Panel</label>
                            <label class="addl-chip"><input type="radio" name="folding" value="accordion"> Accordion</label>
                            <label class="addl-chip"><input type="radio" name="folding" value="gate"> Gate Fold</label>
                        </div>
                    </div>

                    <div class="addl-opt-group">
                        <label class="addl-opt-label">Creasing</label>
                        <div class="addl-opt-row" data-group="creasing">
                            <label class="addl-chip selected"><input type="radio" name="creasing" value="" checked> None</label>
                            <label class="addl-chip"><input type="radio" name="creasing" value="1"> 1 Line</label>
                            <label class="addl-chip"><input type="radio" name="creasing" value="2"> 2 Lines</label>
                            <label class="addl-chip"><input type="radio" name="creasing" value="3"> 3 Lines</label>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Price Sidebar -->
    <div>
        <div class="price-card">
            <div class="price-card-title">Order Summary</div>

            <div class="price-empty" id="priceEmpty">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <div>Select all options above to see pricing</div>
            </div>

            <div class="price-loading" id="priceLoading">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite; display:inline-block;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                Calculating price...
            </div>

            <div class="price-breakdown" id="priceBreakdown">
                <div class="price-row">
                    <span class="price-row-label">Supply Price</span>
                    <span class="price-row-value" id="priceSupply">—</span>
                </div>
                <div class="price-row">
                    <span class="price-row-label">VAT (10%)</span>
                    <span class="price-row-value" id="priceVat">—</span>
                </div>
                <hr class="price-divider">
                <div class="price-total">
                    <span class="price-total-label">Total</span>
                    <span class="price-total-value" id="priceTotal">— <small>KRW</small></span>
                </div>
            </div>

            <div class="price-usd" id="priceUsd" style="display:none; text-align:right; padding:8px 0 4px;">
                <span style="font-size:18px; font-weight:700; color:var(--green); font-family:var(--font-heading);" id="priceUsdValue"></span>
                <span style="font-size:13px; color:var(--text-light);"> USD</span>
            </div>
            <div class="price-rate-note" id="priceRateNote" style="display:none; font-size:11px; color:var(--text-light); text-align:right; padding:2px 0 8px; line-height:1.4;">
                Rate: ₩<?php echo $exchangeRate ? number_format($exchangeRate['rate'], 2) : '—'; ?>/USD
                (<?php echo $exchangeRate ? htmlspecialchars($exchangeRate['date']) : '—'; ?>)
            </div>

            <div class="price-error" id="priceError"></div>

            <button type="button" class="btn-cart" id="btnCart" disabled onclick="addToCart()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                Add to Cart
            </button>

            <a href="/en/#quote" class="btn-quote-link">Or request a quote instead →</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <img src="/ImgFolder/dusonlogo1.png" alt="Duson">
            <span>DUSON PRINT</span>
        </div>
        <div class="footer-copy">&copy; 2004&ndash;2026 Duson Planning Print. All rights reserved.</div>
        <div class="footer-links">
            <a href="/">Korean Site</a>
            <a href="mailto:dsp1830@naver.com">Email Us</a>
            <a href="/en/#quote">Get Quote</a>
        </div>
    </div>
</footer>

<?php if ($is_supported): ?>
<script>
// ── Spin keyframe (for loading) ──
(function() {
    var s = document.createElement('style');
    s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(s);
})();

// ── Product config (from PHP) ──
var PRODUCT_TYPE = <?php echo json_encode($type); ?>;
var PRODUCT_CONFIG = <?php echo json_encode($product); ?>;
var DROPDOWNS = PRODUCT_CONFIG.dropdowns;

var currentPriceData = null;
var EXCHANGE_RATE = <?php echo $exchangeRate ? json_encode($exchangeRate['rate']) : 'null'; ?>;

// ── Nav ──
var nav = document.getElementById('nav');
window.addEventListener('scroll', function() {
    nav.classList.toggle('scrolled', window.scrollY > 20);
});
document.getElementById('navToggle').addEventListener('click', function() {
    document.getElementById('navLinks').classList.toggle('open');
});
document.querySelectorAll('.nav-links a').forEach(function(a) {
    a.addEventListener('click', function() { document.getElementById('navLinks').classList.remove('open'); });
});

// ── Chip selection for additional options ──
document.querySelectorAll('.addl-chip').forEach(function(chip) {
    chip.addEventListener('click', function() {
        var group = this.closest('.addl-opt-row');
        group.querySelectorAll('.addl-chip').forEach(function(c) { c.classList.remove('selected'); });
        this.classList.add('selected');
        // Re-trigger price calculation if all dropdowns filled
        tryCalculatePrice();
    });
});

// ── Format number with commas ──
function fmtNum(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ── Get current value of a dropdown by ID ──
function getVal(id) {
    var el = document.getElementById(id);
    return el ? el.value : '';
}

// ── Build API URL with template substitution ──
function buildApiUrl(template) {
    return template.replace(/\{(\w+)\}/g, function(_, key) {
        return encodeURIComponent(getVal(key));
    });
}

// ── Normalize API response (handle both raw array and {success, data} wrapper) ──
function normalizeList(data) {
    if (Array.isArray(data)) return data;
    if (data && data.success && Array.isArray(data.data)) return data.data;
    if (data && Array.isArray(data.data)) return data.data;
    return [];
}

// ── Normalize option item ({no,title} or {value,text}) ──
function normalizeOpt(item) {
    return {
        value: item.no || item.value || '',
        text: item.title || item.text || ''
    };
}

// ── Populate a select element with options ──
function populateSelect(selectEl, items, placeholder) {
    selectEl.innerHTML = '';
    var ph = document.createElement('option');
    ph.value = '';
    ph.textContent = placeholder || '— Select —';
    selectEl.appendChild(ph);

    items.forEach(function(item) {
        var o = normalizeOpt(item);
        var opt = document.createElement('option');
        opt.value = o.value;
        opt.textContent = o.text;
        selectEl.appendChild(opt);
    });
}

// ── Get dropdown config by ID ──
function getDDConfig(id) {
    for (var i = 0; i < DROPDOWNS.length; i++) {
        if (DROPDOWNS[i].id === id) return DROPDOWNS[i];
    }
    return null;
}

// ── Find all dropdowns that depend on a given ID ──
function getDependents(parentId) {
    var deps = [];
    DROPDOWNS.forEach(function(dd) {
        if (dd.depends_on === parentId) deps.push(dd);
    });
    return deps;
}

// ── Reset a dropdown and all its downstream dependents ──
function resetDownstream(fromId) {
    var deps = getDependents(fromId);
    deps.forEach(function(dd) {
        var el = document.getElementById(dd.id);
        if (!el) return;
        if (dd.type !== 'static') {
            el.innerHTML = '<option value="">' + (dd.placeholder || '— Select —') + '</option>';
            el.disabled = true;
        }
        el.value = '';
        resetDownstream(dd.id);
    });
    // Hide price
    showPriceEmpty();
}

// ── Fetch options for a dependent dropdown ──
function fetchOptions(dd) {
    var el = document.getElementById(dd.id);
    if (!el || !dd.api) return;

    var url = buildApiUrl(dd.api);
    el.disabled = true;
    el.classList.add('loading');

    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var items = normalizeList(data);
            populateSelect(el, items, dd.placeholder);
            el.disabled = false;
            el.classList.remove('loading');
        })
        .catch(function(err) {
            console.error('Fetch error for ' + dd.id + ':', err);
            el.disabled = false;
            el.classList.remove('loading');
        });
}

// ── Check if all API template params are filled for a dropdown ──
function apiParamsFilled(dd) {
    if (!dd.api) return false;
    var params = dd.api.match(/\{(\w+)\}/g);
    if (!params) return true;
    for (var i = 0; i < params.length; i++) {
        var key = params[i].replace(/[{}]/g, '');
        if (!getVal(key)) return false;
    }
    return true;
}

// ── Handle dropdown change ──
function onDropdownChange(changedId) {
    var val = getVal(changedId);

    // Reset all downstream dependents
    resetDownstream(changedId);

    if (!val) return;

    // Fetch options for direct dependents
    var deps = getDependents(changedId);
    deps.forEach(function(dd) {
        if (dd.type === 'static') return;
        fetchOptions(dd);
    });

    // Also check if any other dynamic dropdown now has all its params filled
    // (handles cases like static POtype enabling MY_amount which depends on PN_type)
    DROPDOWNS.forEach(function(dd) {
        if (dd.type === 'static') return;
        var el = document.getElementById(dd.id);
        if (!el || !el.disabled) return; // Already enabled/populated
        if (!dd.api) return;
        if (apiParamsFilled(dd)) {
            fetchOptions(dd);
        }
    });

    // Check if we can calculate price
    tryCalculatePrice();
}

// ── Check if all required dropdowns are filled ──
function allFilled() {
    for (var i = 0; i < DROPDOWNS.length; i++) {
        if (!getVal(DROPDOWNS[i].id)) return false;
    }
    return true;
}

// ── Show/hide price states ──
function showPriceEmpty() {
    document.getElementById('priceEmpty').style.display = '';
    document.getElementById('priceLoading').classList.remove('visible');
    document.getElementById('priceBreakdown').classList.remove('visible');
    document.getElementById('priceError').classList.remove('visible');
    document.getElementById('priceUsd').style.display = 'none';
    document.getElementById('priceRateNote').style.display = 'none';
    document.getElementById('btnCart').disabled = true;
    currentPriceData = null;
}

function showPriceLoading() {
    document.getElementById('priceEmpty').style.display = 'none';
    document.getElementById('priceLoading').classList.add('visible');
    document.getElementById('priceBreakdown').classList.remove('visible');
    document.getElementById('priceError').classList.remove('visible');
    document.getElementById('btnCart').disabled = true;
}

function showPriceResult(supply, vat, total) {
    document.getElementById('priceEmpty').style.display = 'none';
    document.getElementById('priceLoading').classList.remove('visible');
    document.getElementById('priceBreakdown').classList.add('visible');
    document.getElementById('priceError').classList.remove('visible');

    document.getElementById('priceSupply').textContent = '₩' + fmtNum(supply);
    document.getElementById('priceVat').textContent = '₩' + fmtNum(vat);
    document.getElementById('priceTotal').innerHTML = '₩' + fmtNum(total) + ' <small>KRW</small>';
    document.getElementById('btnCart').disabled = false;

    if (EXCHANGE_RATE && total > 0) {
        var usd = (total / EXCHANGE_RATE).toFixed(2);
        document.getElementById('priceUsdValue').textContent = '≈ $' + usd;
        document.getElementById('priceUsd').style.display = '';
        document.getElementById('priceRateNote').style.display = '';
    }
}

function showPriceError(msg) {
    document.getElementById('priceEmpty').style.display = 'none';
    document.getElementById('priceLoading').classList.remove('visible');
    document.getElementById('priceBreakdown').classList.remove('visible');
    var errEl = document.getElementById('priceError');
    errEl.textContent = msg || 'Price calculation failed. Please try again or contact us.';
    errEl.classList.add('visible');
    document.getElementById('btnCart').disabled = true;
}

// ── Price calculation ──
var priceTimer = null;
function tryCalculatePrice() {
    if (priceTimer) clearTimeout(priceTimer);
    if (!allFilled()) return;

    // Debounce 300ms
    priceTimer = setTimeout(function() { calculatePrice(); }, 300);
}

function calculatePrice() {
    showPriceLoading();

    // Build query params from all dropdowns
    var params = new URLSearchParams();
    DROPDOWNS.forEach(function(dd) {
        params.set(dd.id, getVal(dd.id));
    });

    // Additional options total (for inserted)
    if (PRODUCT_CONFIG.additional_options) {
        params.set('additional_options_total', '0'); // We don't calculate additional option costs on the English page yet
    }

    var url = PRODUCT_CONFIG.price_api + '?' + params.toString();

    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            // Normalize: extract price from various response formats
            var d = resp;
            if (resp.data) d = resp.data;

            // Parse number that may contain commas (e.g. "84,000")
            function parsePrice(v) {
                if (typeof v === 'number') return v;
                return parseInt(String(v).replace(/,/g, '')) || 0;
            }

            // Try to get supply price (before VAT)
            var supply = 0;
            if (typeof d.Order_PriceForm !== 'undefined') {
                supply = parsePrice(d.Order_PriceForm);
            } else if (typeof d.Order_Price !== 'undefined') {
                supply = parsePrice(d.Order_Price);
            } else if (typeof d.total_price !== 'undefined') {
                supply = parsePrice(d.total_price);
            } else if (typeof d.order_price !== 'undefined') {
                supply = parsePrice(d.order_price);
            } else if (typeof d.Price !== 'undefined') {
                supply = parsePrice(d.Price);
            }

            if (supply <= 0) {
                showPriceError('No price available for this combination. Please try different options.');
                return;
            }

            var vat = Math.floor(supply * 0.1);
            var total = supply + vat;

            // Override with API values if available
            if (typeof d.VAT_PriceForm !== 'undefined') vat = parsePrice(d.VAT_PriceForm);
            else if (typeof d.vat !== 'undefined') vat = parsePrice(d.vat);
            if (typeof d.Total_PriceForm !== 'undefined') total = parsePrice(d.Total_PriceForm);
            else if (typeof d.total_with_vat !== 'undefined') total = parsePrice(d.total_with_vat);

            currentPriceData = { supply: supply, vat: vat, total: total, raw: d };
            showPriceResult(supply, vat, total);
        })
        .catch(function(err) {
            console.error('Price calc error:', err);
            showPriceError('Network error. Please check your connection and try again.');
        });
}

// ── Add to Cart ──
function addToCart() {
    if (!currentPriceData || !allFilled()) return;

    var btn = document.getElementById('btnCart');
    btn.disabled = true;
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Adding...';

    var formData = new FormData();
    formData.append('product_type', PRODUCT_TYPE);
    DROPDOWNS.forEach(function(dd) {
        formData.append(dd.id, getVal(dd.id));
    });
    formData.append('calculated_price', currentPriceData.supply);
    formData.append('calculated_vat_price', currentPriceData.total);
    formData.append('price', currentPriceData.supply);
    formData.append('vat_price', currentPriceData.total);

    fetch(PRODUCT_CONFIG.cart_api, {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
        var success = resp.success || (resp.data && resp.data.basket_id);
        if (success) {
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Added!';
            btn.style.background = 'var(--green)';
            setTimeout(function() {
                alert('Item added to cart successfully!\n\nNote: Cart page is coming soon. Your item has been saved.');
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg> Add to Cart';
                btn.style.background = '';
                btn.disabled = false;
            }, 1200);
        } else {
            var msg = resp.message || resp.error || 'Failed to add to cart.';
            alert('Error: ' + msg + '\n\nPlease try again or contact us at dsp1830@naver.com');
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg> Add to Cart';
            btn.disabled = false;
        }
    })
    .catch(function(err) {
        console.error('Cart error:', err);
        alert('Network error adding to cart.\n\nPlease try again or email us at dsp1830@naver.com');
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg> Add to Cart';
        btn.disabled = false;
    });
}

// ── Bind dropdown change events ──
DROPDOWNS.forEach(function(dd) {
    var el = document.getElementById(dd.id);
    if (el) {
        el.addEventListener('change', function() {
            onDropdownChange(dd.id);
        });
    }
});
</script>
<?php endif; ?>

</body>
</html>
