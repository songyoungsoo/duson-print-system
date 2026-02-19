<?php
session_start();

include __DIR__ . '/../includes/exchange_rate.php';
$exchangeRate = getExchangeRate();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stickers &amp; Labels — Order | Duson Print</title>
    <meta name="description" content="Order custom stickers and labels online from Duson Print. Formula-based pricing, die-cut shapes, 11 material options. Factory-direct from Seoul, Korea.">
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
        .form-select, .form-input {
            width: 100%; padding: 12px 16px; border-radius: 10px;
            border: 1.5px solid var(--border); font-family: var(--font-body);
            font-size: 14px; color: var(--text); background: var(--bg);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-select {
            cursor: pointer; appearance: none;
            padding-right: 40px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        .form-select:focus, .form-input:focus {
            border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,78,121,0.1);
        }
        .form-select:disabled, .form-input:disabled {
            opacity: 0.5; cursor: not-allowed; background-color: #f1f5f9;
        }

        /* Size inputs row */
        .size-row {
            display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px;
            align-items: end;
        }
        .size-field { position: relative; }
        .size-field .form-input {
            padding-right: 44px;
        }
        .size-suffix {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            font-size: 13px; font-weight: 600; color: var(--text-light);
            pointer-events: none;
        }
        .size-x {
            font-size: 18px; font-weight: 700; color: var(--text-light);
            padding-bottom: 12px; text-align: center;
        }
        .size-helper {
            font-size: 12px; color: var(--text-light); margin-top: 8px;
            line-height: 1.5; padding: 8px 12px;
            background: #F8FAFC; border-radius: 8px; border: 1px solid var(--border);
        }
        .size-helper strong { color: var(--text-muted); font-weight: 600; }

        /* Validation messages */
        .field-error {
            font-size: 12px; color: var(--red); margin-top: 4px;
            display: none; align-items: center; gap: 4px;
        }
        .field-error.visible { display: flex; }
        .field-error svg { flex-shrink: 0; }

        .kr-note {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: var(--text-light); margin-top: 6px;
            padding: 8px 12px; background: #FFF8E1; border-radius: 8px;
            border: 1px solid #FFF3C4;
        }
        .kr-note svg { flex-shrink: 0; }

        .info-note {
            display: flex; align-items: flex-start; gap: 8px;
            font-size: 12px; color: var(--text-muted); margin-top: 20px;
            padding: 12px 14px; background: #F0F9FF; border-radius: 10px;
            border: 1px solid #BAE6FD; line-height: 1.5;
        }
        .info-note svg { flex-shrink: 0; margin-top: 1px; }

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

        .price-specs {
            padding: 12px 14px; border-radius: 10px;
            background: #F8FAFC; border: 1px solid var(--border);
            margin-bottom: 16px;
        }
        .price-spec-row {
            display: flex; justify-content: space-between;
            font-size: 12px; padding: 3px 0;
        }
        .price-spec-label { color: var(--text-light); }
        .price-spec-value { color: var(--text); font-weight: 500; }

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

        .price-unit {
            text-align: right; font-size: 12px; color: var(--text-light);
            padding: 6px 0 0;
        }

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

        .price-warn {
            display: none; padding: 10px 14px; border-radius: 8px;
            font-size: 13px; margin-top: 12px;
            background: #FFFBEB; color: #92400E;
            border: 1px solid #FDE68A;
        }
        .price-warn.visible { display: block; }

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
            .order-wrap { grid-template-columns: 1fr; gap: 24px; }
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
            .size-row { grid-template-columns: 1fr; gap: 4px; }
            .size-x { display: none; }
            .footer-inner { flex-direction: column; text-align: center; }
        }

        @keyframes spin { to { transform: rotate(360deg); } }
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

<!-- BREADCRUMB -->
<div class="breadcrumb-bar">
    <div class="breadcrumb">
        <a href="/en/">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <a href="/en/products/">Products</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <span>Stickers &amp; Labels</span>
    </div>
</div>

<!-- ORDER FORM -->
<div class="order-wrap">
    <div>
        <!-- Product Header -->
        <div class="product-header">
            <div class="product-header-img">
                <img src="/ImgFolder/gate_picto/sticker_new_s.png" alt="Stickers &amp; Labels">
            </div>
            <div class="product-header-info">
                <h1>Stickers &amp; Labels</h1>
                <div class="kr-name">스티커</div>
                <p>Custom stickers and labels with formula-based pricing. Enter your exact dimensions and get instant pricing. Available in 11 materials including waterproof, clear, and kraft paper.</p>
            </div>
        </div>

        <!-- Configuration Form -->
        <div class="order-form-card">
            <div class="form-section-title">
                <span class="num">1</span> Material
            </div>

            <div class="form-row">
                <label class="form-label" for="jong">Material Type</label>
                <select id="jong" name="jong" class="form-select">
                    <option value="">— Select material —</option>
                    <option value="jil 아트유광코팅">Art Paper - Gloss Coated (아트유광코팅)</option>
                    <option value="jil 아트무광코팅">Art Paper - Matte Coated (아트무광코팅)</option>
                    <option value="jil 아트비코팅">Art Paper - Uncoated (아트비코팅)</option>
                    <option value="jka 강접아트유광코팅">Strong Adhesive - Gloss (강접아트유광코팅)</option>
                    <option value="cka 초강접아트코팅">Super Strong Adhesive - Gloss (초강접아트코팅)</option>
                    <option value="cka 초강접아트비코팅">Super Strong Adhesive - Uncoated (초강접아트비코팅)</option>
                    <option value="jsp 유포지">Yupo / Waterproof (유포지)</option>
                    <option value="jsp 은데드롱">Silver Deadlong (은데드롱)</option>
                    <option value="jsp 투명스티커">Clear / Transparent (투명스티커)</option>
                    <option value="jil 모조비코팅">Bond Paper - Uncoated (모조비코팅)</option>
                    <option value="jsp 크라프트지">Kraft Paper (크라프트지)</option>
                </select>
                <div class="kr-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B8860B" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    Korean material names shown in parentheses — these are industry-standard names used in Korean printing.
                </div>
            </div>

            <div class="form-section-title" style="margin-top:28px;">
                <span class="num">2</span> Size
            </div>

            <div class="form-row">
                <label class="form-label">Dimensions</label>
                <div class="size-row">
                    <div class="size-field">
                        <label class="form-label" for="garo" style="font-size:12px; color:var(--text-light); font-weight:500;">Width</label>
                        <input type="number" id="garo" name="garo" class="form-input" min="10" max="560" value="100" step="1" placeholder="Width">
                        <span class="size-suffix">mm</span>
                    </div>
                    <div class="size-x">×</div>
                    <div class="size-field">
                        <label class="form-label" for="sero" style="font-size:12px; color:var(--text-light); font-weight:500;">Height</label>
                        <input type="number" id="sero" name="sero" class="form-input" min="10" max="560" value="100" step="1" placeholder="Height">
                        <span class="size-suffix">mm</span>
                    </div>
                </div>
                <div class="field-error" id="sizeError">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                    <span></span>
                </div>
                <div class="size-helper">
                    <strong>Common sizes:</strong> Business card 90×50mm &middot; A7 105×74mm &middot; Circle 50×50mm &middot; Max 560×560mm
                </div>
            </div>

            <div class="form-section-title" style="margin-top:28px;">
                <span class="num">3</span> Options
            </div>

            <div class="form-row">
                <label class="form-label" for="mesu">Quantity</label>
                <select id="mesu" name="mesu" class="form-select">
                    <option value="500">500 sheets</option>
                    <option value="1000" selected>1,000 sheets</option>
                    <option value="2000">2,000 sheets</option>
                    <option value="3000">3,000 sheets</option>
                    <option value="4000">4,000 sheets</option>
                    <option value="5000">5,000 sheets</option>
                    <option value="6000">6,000 sheets</option>
                    <option value="7000">7,000 sheets</option>
                    <option value="8000">8,000 sheets</option>
                    <option value="9000">9,000 sheets</option>
                    <option value="10000">10,000 sheets</option>
                </select>
            </div>

            <div class="form-row">
                <label class="form-label" for="domusong">Shape / Die-cut</label>
                <select id="domusong" name="domusong" class="form-select">
                    <option value="00000 사각">Rectangle — no die-cut</option>
                    <option value="08000 사각도무송">Rectangle Die-cut (+₩8,000)</option>
                    <option value="08000 귀돌">Rounded Corners (+₩8,000)</option>
                    <option value="08000 원형">Circle (+₩8,000)</option>
                    <option value="08000 타원">Oval (+₩8,000)</option>
                    <option value="19000 복잡">Custom Shape (+₩19,000)</option>
                </select>
                <div class="field-error" id="shapeError">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                    <span></span>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label" for="uhyung">Design Service</label>
                <select id="uhyung" name="uhyung" class="form-select">
                    <option value="0">Print Only (I have files)</option>
                    <option value="10000">Basic Editing (+₩10,000)</option>
                    <option value="30000">Advanced Editing (+₩30,000)</option>
                </select>
            </div>

            <div class="info-note">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                <span>Option labels shown in Korean — these are material/finish names used in the Korean printing industry. Your order will be processed correctly regardless of language.</span>
            </div>
        </div>
    </div>

    <!-- Price Sidebar -->
    <div>
        <div class="price-card">
            <div class="price-card-title">Order Summary</div>

            <div class="price-empty" id="priceEmpty">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <div>Select a material to see pricing</div>
            </div>

            <div class="price-loading" id="priceLoading">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite; display:inline-block;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                Calculating price...
            </div>

            <div class="price-breakdown" id="priceBreakdown">
                <div class="price-specs" id="priceSpecs">
                    <div class="price-spec-row">
                        <span class="price-spec-label">Material</span>
                        <span class="price-spec-value" id="specMaterial">—</span>
                    </div>
                    <div class="price-spec-row">
                        <span class="price-spec-label">Size</span>
                        <span class="price-spec-value" id="specSize">—</span>
                    </div>
                    <div class="price-spec-row">
                        <span class="price-spec-label">Quantity</span>
                        <span class="price-spec-value" id="specQty">—</span>
                    </div>
                    <div class="price-spec-row">
                        <span class="price-spec-label">Shape</span>
                        <span class="price-spec-value" id="specShape">—</span>
                    </div>
                </div>

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
                <div class="price-unit" id="priceUnit"></div>
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
            <div class="price-warn" id="priceWarn"></div>

            <button type="button" class="btn-cart" id="btnCart" disabled onclick="addToCart()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                Add to Cart
            </button>

            <a href="/en/#quote" class="btn-quote-link">Or request a quote instead →</a>
        </div>
    </div>
</div>

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

<script>
(function() {
    'use strict';

    // ── Exchange rate from PHP ──
    var EXCHANGE_RATE = <?php echo $exchangeRate ? json_encode($exchangeRate['rate']) : 'null'; ?>;
    var currentPriceData = null;
    var priceTimer = null;
    var isCalculating = false;

    // ── Material label map (for spec summary) ──
    var MATERIAL_LABELS = {
        'jil 아트유광코팅': 'Art Paper - Gloss',
        'jil 아트무광코팅': 'Art Paper - Matte',
        'jil 아트비코팅': 'Art Paper - Uncoated',
        'jka 강접아트유광코팅': 'Strong Adhesive - Gloss',
        'cka 초강접아트코팅': 'Super Strong - Gloss',
        'cka 초강접아트비코팅': 'Super Strong - Uncoated',
        'jsp 유포지': 'Yupo (Waterproof)',
        'jsp 은데드롱': 'Silver Deadlong',
        'jsp 투명스티커': 'Clear / Transparent',
        'jil 모조비코팅': 'Bond Paper - Uncoated',
        'jsp 크라프트지': 'Kraft Paper'
    };

    var SHAPE_LABELS = {
        '00000 사각': 'Rectangle',
        '08000 사각도무송': 'Rect. Die-cut',
        '08000 귀돌': 'Rounded Corners',
        '08000 원형': 'Circle',
        '08000 타원': 'Oval',
        '19000 복잡': 'Custom Shape'
    };

    // ── Utility ──
    function fmtNum(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function getVal(id) {
        var el = document.getElementById(id);
        return el ? el.value : '';
    }

    function getNumVal(id) {
        return parseInt(getVal(id)) || 0;
    }

    // ── Nav scroll & mobile toggle ──
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

    // ── Shape validation helpers ──
    function updateShapeOptions() {
        var w = getNumVal('garo');
        var h = getNumVal('sero');
        var circleOpt = document.querySelector('#domusong option[value="08000 원형"]');
        var ovalOpt = document.querySelector('#domusong option[value="08000 타원"]');

        if (circleOpt) circleOpt.disabled = (w !== h);
        if (ovalOpt) ovalOpt.disabled = (w === h);

        // If currently selected option got disabled, reset to rectangle
        var currentVal = getVal('domusong');
        if (currentVal === '08000 원형' && w !== h) {
            document.getElementById('domusong').value = '00000 사각';
        }
        if (currentVal === '08000 타원' && w === h) {
            document.getElementById('domusong').value = '00000 사각';
        }
    }

    // ── Validation ──
    function validate() {
        var w = getNumVal('garo');
        var h = getNumVal('sero');
        var qty = getNumVal('mesu');
        var jong = getVal('jong');
        var domusong = getVal('domusong');

        var sizeErr = document.getElementById('sizeError');
        var shapeErr = document.getElementById('shapeError');
        var warnEl = document.getElementById('priceWarn');
        var valid = true;

        // Reset errors
        sizeErr.classList.remove('visible');
        shapeErr.classList.remove('visible');
        warnEl.classList.remove('visible');

        // Size range
        if (w < 10 || w > 560) {
            sizeErr.querySelector('span').textContent = 'Width must be between 10mm and 560mm.';
            sizeErr.classList.add('visible');
            valid = false;
        }
        if (h < 10 || h > 560) {
            sizeErr.querySelector('span').textContent = 'Height must be between 10mm and 560mm.';
            sizeErr.classList.add('visible');
            valid = false;
        }

        // Small size die-cut requirement
        if (valid && (w < 50 || h < 60) && (w < 60 || h < 50) && domusong === '00000 사각') {
            shapeErr.querySelector('span').textContent = 'Sizes under 50×60mm require a die-cut shape (not Rectangle).';
            shapeErr.classList.add('visible');
            valid = false;
        }

        // Large area + high qty
        if (valid && (w * h) > 250000 && qty > 5000) {
            warnEl.textContent = '⚠ Large format (over 250,000mm²) with 5,000+ sheets requires a phone quote. Please call +82-2-2632-1830 or email dsp1830@naver.com.';
            warnEl.classList.add('visible');
            valid = false;
        }

        // Circle/Oval constraints
        if (valid && domusong === '08000 원형' && w !== h) {
            shapeErr.querySelector('span').textContent = 'Circle shape requires equal width and height.';
            shapeErr.classList.add('visible');
            valid = false;
        }
        if (valid && domusong === '08000 타원' && w === h) {
            shapeErr.querySelector('span').textContent = 'Oval shape requires different width and height. Use Circle for equal dimensions.';
            shapeErr.classList.add('visible');
            valid = false;
        }

        // Material required
        if (!jong) {
            valid = false;
        }

        return valid;
    }

    // ── Price display states ──
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
        document.getElementById('priceWarn').classList.remove('visible');
        document.getElementById('btnCart').disabled = true;
    }

    function showPriceResult(supply, vat, total) {
        document.getElementById('priceEmpty').style.display = 'none';
        document.getElementById('priceLoading').classList.remove('visible');
        document.getElementById('priceBreakdown').classList.add('visible');
        document.getElementById('priceError').classList.remove('visible');

        // Update spec summary
        var jong = getVal('jong');
        var materialLabel = MATERIAL_LABELS[jong] || jong;
        var w = getNumVal('garo');
        var h = getNumVal('sero');
        var qty = getNumVal('mesu');
        var domusong = getVal('domusong');
        var shapeLabel = SHAPE_LABELS[domusong] || domusong;

        document.getElementById('specMaterial').textContent = materialLabel;
        document.getElementById('specSize').textContent = w + ' × ' + h + ' mm';
        document.getElementById('specQty').textContent = fmtNum(qty) + ' sheets';
        document.getElementById('specShape').textContent = shapeLabel;

        // Prices
        document.getElementById('priceSupply').textContent = '₩' + fmtNum(supply);
        document.getElementById('priceVat').textContent = '₩' + fmtNum(vat);
        document.getElementById('priceTotal').innerHTML = '₩' + fmtNum(total) + ' <small>KRW</small>';
        document.getElementById('btnCart').disabled = false;

        // Unit price
        if (qty > 0 && total > 0) {
            var unitPrice = total / qty;
            document.getElementById('priceUnit').textContent = '≈ ₩' + unitPrice.toFixed(1) + ' per sheet (incl. VAT)';
        } else {
            document.getElementById('priceUnit').textContent = '';
        }

        // USD conversion
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

    // ── Price calculation via API ──
    function tryCalculatePrice() {
        if (priceTimer) clearTimeout(priceTimer);
        if (!validate()) {
            if (!getVal('jong')) showPriceEmpty();
            return;
        }

        priceTimer = setTimeout(function() { calculatePrice(); }, 300);
    }

    function calculatePrice() {
        if (isCalculating) return;
        isCalculating = true;
        showPriceLoading();

        var formData = new FormData();
        formData.append('action', 'calculate');
        formData.append('jong', getVal('jong'));
        formData.append('garo', getVal('garo'));
        formData.append('sero', getVal('sero'));
        formData.append('mesu', getVal('mesu'));
        formData.append('uhyung', getVal('uhyung'));
        formData.append('domusong', getVal('domusong'));

        fetch('/mlangprintauto/sticker_new/calculate_price_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            isCalculating = false;

            if (!resp.success) {
                // Translate common Korean error messages
                var msg = translateError(resp.message || 'Unknown error');
                showPriceError(msg);
                return;
            }

            var supply = resp.raw_price || 0;
            var totalVat = resp.raw_price_vat || 0;
            var vat = totalVat - supply;

            if (supply <= 0) {
                showPriceError('No price available for this combination. Please adjust your options.');
                return;
            }

            currentPriceData = {
                supply: supply,
                vat: vat,
                total: totalVat,
                raw_price: supply,
                raw_price_vat: totalVat
            };
            showPriceResult(supply, vat, totalVat);
        })
        .catch(function(err) {
            isCalculating = false;
            console.error('Price calc error:', err);
            showPriceError('Network error. Please check your connection and try again.');
        });
    }

    // ── Translate Korean API errors to English ──
    function translateError(msg) {
        var translations = {
            '재질을 선택하세요': 'Please select a material.',
            '가로사이즈를 입력하세요': 'Please enter the width.',
            '세로사이즈를 입력하세요': 'Please enter the height.',
            '수량을 입력하세요': 'Please select a quantity.',
            '가로사이즈를 590mm이하만 입력할 수 있습니다': 'Width must be 590mm or less.',
            '세로사이즈를 590mm이하만 입력할 수 있습니다': 'Height must be 590mm or less.',
            '금지스티커는 전화 또는 메일로 견적 문의하세요': 'This material requires a phone quote. Call +82-2-2632-1830.',
            '금박스티커는 전화 또는 메일로 견적 문의하세요': 'Gold foil stickers require a phone quote. Call +82-2-2632-1830.',
            '롤스티커는 전화 또는 메일로 견적 문의하세요': 'Roll stickers require a phone quote. Call +82-2-2632-1830.',
            '1만매 이상은 할인가 적용-전화주시기바랍니다': 'Orders over 10,000 sheets get a bulk discount — please call +82-2-2632-1830.'
        };

        for (var kr in translations) {
            if (msg.indexOf(kr) !== -1) return translations[kr];
        }

        // Partial matches
        if (msg.indexOf('50mm') !== -1 && msg.indexOf('60mm') !== -1) {
            return 'Sizes under 50×60mm require a die-cut shape (not Rectangle).';
        }
        if (msg.indexOf('5000매이상') !== -1 || msg.indexOf('대형사이즈') !== -1) {
            return 'Large format with 5,000+ sheets requires a phone quote. Call +82-2-2632-1830.';
        }
        if (msg.indexOf('전화') !== -1) {
            return msg + ' — Please call +82-2-2632-1830 for assistance.';
        }

        return msg;
    }

    // ── Add to Cart ──
    window.addToCart = function() {
        if (!currentPriceData || !validate()) return;

        var btn = document.getElementById('btnCart');
        btn.disabled = true;
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Adding...';

        var formData = new FormData();
        formData.append('action', 'add_to_basket');
        formData.append('product_type', 'sticker');
        formData.append('jong', getVal('jong'));
        formData.append('garo', getVal('garo'));
        formData.append('sero', getVal('sero'));
        formData.append('mesu', getVal('mesu'));
        formData.append('uhyung', getVal('uhyung'));
        formData.append('domusong', getVal('domusong'));
        formData.append('price', currentPriceData.raw_price);
        formData.append('st_price', currentPriceData.raw_price);
        formData.append('st_price_vat', currentPriceData.raw_price_vat);

        // Build quantity display text
        var qty = getNumVal('mesu');
        formData.append('quantity_display', fmtNum(qty) + '매');

        fetch('/mlangprintauto/sticker_new/add_to_basket.php', {
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
                    window.location.href = '/en/cart.php?added=sticker';
                }, 900);
            } else {
                var msg = resp.message || resp.error || 'Failed to add to cart.';
                alert('Error: ' + msg + '\n\nPlease try again or contact us at dsp1830@naver.com');
                resetCartBtn();
            }
        })
        .catch(function(err) {
            console.error('Cart error:', err);
            alert('Network error adding to cart.\n\nPlease try again or email us at dsp1830@naver.com');
            resetCartBtn();
        });
    };

    function resetCartBtn() {
        var btn = document.getElementById('btnCart');
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg> Add to Cart';
        btn.disabled = false;
        btn.style.background = '';
    }

    // ── Bind events ──
    var fields = ['jong', 'garo', 'sero', 'mesu', 'domusong', 'uhyung'];
    fields.forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function() {
            if (id === 'garo' || id === 'sero') updateShapeOptions();
            tryCalculatePrice();
        });
        // Also listen to input for number fields (live typing)
        if (el.type === 'number') {
            el.addEventListener('input', function() {
                updateShapeOptions();
                tryCalculatePrice();
            });
        }
    });

    // ── Initialize: pre-select first material and calculate price ──
    updateShapeOptions();
    var jongEl = document.getElementById('jong');
    if (jongEl && jongEl.options.length > 1 && !jongEl.value) {
        jongEl.selectedIndex = 1; // Select first real option
        tryCalculatePrice();
    }

})();
</script>

</body>
</html>
