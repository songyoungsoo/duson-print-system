<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — Duson Print | Premium Printing from Seoul</title>
    <meta name="description" content="Browse our complete product range: stickers, flyers, business cards, envelopes, catalogs, posters, NCR forms, gift vouchers, and magnetic stickers. Factory-direct pricing.">
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
            transition: all 0.25s; border: none; cursor: pointer;
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

        /* ===== HERO ===== */
        .hero {
            padding: 120px 24px 64px;
            background: linear-gradient(165deg, var(--navy-dark) 0%, var(--navy) 40%, var(--navy-light) 100%);
            position: relative; overflow: hidden; text-align: center;
        }
        .hero::before {
            content: ''; position: absolute; top: -40%; right: -15%;
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,213,0,0.07) 0%, transparent 65%);
        }
        .hero::after {
            content: ''; position: absolute; bottom: -25%; left: -10%;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 60%);
        }
        .hero-inner {
            max-width: 720px; margin: 0 auto; position: relative; z-index: 1;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 16px; border-radius: 30px;
            background: rgba(255,213,0,0.12); border: 1px solid rgba(255,213,0,0.25);
            color: var(--gold); font-size: 13px; font-weight: 600; margin-bottom: 20px;
        }
        .hero-badge::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: var(--gold); animation: pulse 2.5s infinite;
        }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }
        .hero h1 {
            font-family: var(--font-heading); font-size: 48px; font-weight: 800;
            color: var(--white); line-height: 1.15; letter-spacing: -1.5px; margin-bottom: 16px;
        }
        .hero h1 em {
            font-style: normal;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            font-size: 18px; line-height: 1.7; color: rgba(255,255,255,0.65);
            max-width: 540px; margin: 0 auto 28px;
        }
        .hero-count {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 14px; color: rgba(255,255,255,0.45); font-weight: 500;
        }
        .hero-count strong {
            font-family: var(--font-heading); font-size: 20px; font-weight: 800;
            color: var(--gold);
        }

        /* ===== PRODUCTS GRID ===== */
        .products-section { padding: 72px 24px 100px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .products-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px;
        }
        .p-card {
            background: var(--white); border-radius: var(--radius-lg); overflow: hidden;
            transition: all 0.35s ease; border: 1px solid var(--border);
            display: flex; flex-direction: column; text-decoration: none; color: inherit;
        }
        .p-card:hover {
            transform: translateY(-8px); box-shadow: var(--shadow-lg);
            border-color: transparent;
        }
        .p-card-img {
            height: 200px; display: flex; align-items: center; justify-content: center;
            padding: 28px; background: var(--bg);
            position: relative; overflow: hidden;
        }
        .p-card-img::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(30,78,121,0.03), transparent);
            transition: opacity 0.3s; opacity: 0;
        }
        .p-card:hover .p-card-img::after { opacity: 1; }
        .p-card-img img {
            max-height: 100%; max-width: 100%; object-fit: contain;
            transition: transform 0.4s ease;
        }
        .p-card:hover .p-card-img img { transform: scale(1.06); }
        .p-card-body {
            padding: 22px 26px 26px; flex: 1;
            display: flex; flex-direction: column;
        }
        .p-card-name {
            font-family: var(--font-heading); font-size: 19px; font-weight: 700;
            color: var(--text); margin-bottom: 8px; letter-spacing: -0.3px;
        }
        .p-card-desc {
            font-size: 14px; color: var(--text-muted); line-height: 1.65;
            margin-bottom: 18px; flex: 1;
        }
        .p-card-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 14px; font-weight: 600; color: var(--navy);
            transition: gap 0.25s;
        }
        .p-card:hover .p-card-btn { gap: 10px; }
        .p-card-btn svg { transition: transform 0.25s; }
        .p-card:hover .p-card-btn svg { transform: translateX(2px); }

        /* ===== BACK LINK ===== */
        .back-link-wrap {
            text-align: center; padding: 0 24px 48px;
        }
        .back-link {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 15px; font-weight: 600; color: var(--navy);
            text-decoration: none; transition: gap 0.2s;
        }
        .back-link:hover { gap: 12px; }

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

        /* ===== ANIMATIONS ===== */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-d1 { transition-delay: 0.05s; }
        .reveal-d2 { transition-delay: 0.1s; }
        .reveal-d3 { transition-delay: 0.15s; }
        .reveal-d4 { transition-delay: 0.2s; }
        .reveal-d5 { transition-delay: 0.25s; }
        .reveal-d6 { transition-delay: 0.3s; }
        .reveal-d7 { transition-delay: 0.35s; }
        .reveal-d8 { transition-delay: 0.4s; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .hero h1 { font-size: 38px; }
            .products-grid { grid-template-columns: repeat(2, 1fr); gap: 22px; }
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
            .hero { padding: 100px 20px 48px; }
            .hero h1 { font-size: 30px; letter-spacing: -1px; }
            .hero p { font-size: 16px; }
            .products-section { padding: 48px 20px 72px; }
            .products-grid { grid-template-columns: 1fr; gap: 18px; }
            .p-card-img { height: 160px; }
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

<!-- HERO -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-badge">Factory-Direct Printing &mdash; Seoul, Korea</div>
        <h1>Our <em>Products</em></h1>
        <p>From everyday business essentials to specialty items &mdash; all produced in our own facility with premium materials and fast turnaround.</p>
        <div class="hero-count"><strong>9</strong> product categories &middot; Worldwide shipping</div>
    </div>
</section>

<!-- PRODUCTS GRID -->
<section class="products-section">
    <div class="container">
        <div class="products-grid">

            <?php
            $products = [
                ['key' => 'sticker', 'name' => 'Stickers &amp; Labels', 'img' => 'sticker_new_s.png', 'desc' => 'Custom die-cut stickers in any shape. Waterproof, transparent, and metallic options available.'],
                ['key' => 'inserted', 'name' => 'Flyers &amp; Leaflets', 'img' => 'inserted_s.png', 'desc' => 'High-resolution offset printed flyers for mass distribution. Single or double-sided, with coating options.'],
                ['key' => 'namecard', 'name' => 'Business Cards', 'img' => 'namecard_s.png', 'desc' => 'Professional business cards with premium finishes. UV coating, embossing, and same-day available.'],
                ['key' => 'envelope', 'name' => 'Envelopes', 'img' => 'envelop_s.png', 'desc' => 'Standard and window envelopes for professional correspondence. Full-color custom printing.'],
                ['key' => 'cadarok', 'name' => 'Catalogs &amp; Booklets', 'img' => 'catalogue_s.png', 'desc' => 'Full-color product catalogs with professional binding. Saddle-stitch or perfect binding.'],
                ['key' => 'littleprint', 'name' => 'Posters', 'img' => 'poster_s.png', 'desc' => 'Large format posters with vivid color reproduction. Perfect for events, retail, and promotions.'],
                ['key' => 'ncrflambeau', 'name' => 'NCR Forms', 'img' => 'ncr_s.png', 'desc' => 'Carbonless copy paper forms. 2 to 4 ply options for invoices, receipts, and order forms.'],
                ['key' => 'merchandisebond', 'name' => 'Gift Vouchers', 'img' => 'merchandise_s.png', 'desc' => 'Custom gift certificates and coupons with anti-counterfeit features and serial numbering.'],
                ['key' => 'msticker', 'name' => 'Magnetic Stickers', 'img' => 'm_sticker_s.png', 'desc' => 'Strong magnetic stickers for vehicles, refrigerators, and promotional displays.'],
            ];

            foreach ($products as $i => $p):
                $delay = 'd' . ($i % 9);
            ?>
            <a href="<?php echo $p['key'] === 'sticker' ? '/en/products/order_sticker.php' : '/en/products/order.php?type=' . $p['key']; ?>" class="p-card reveal reveal-<?php echo $delay; ?>">
                <div class="p-card-img">
                    <img src="/ImgFolder/gate_picto/<?php echo $p['img']; ?>" alt="<?php echo strip_tags($p['name']); ?>">
                </div>
                <div class="p-card-body">
                    <div class="p-card-name"><?php echo $p['name']; ?></div>
                    <div class="p-card-desc"><?php echo $p['desc']; ?></div>
                    <div class="p-card-btn">
                        Order Now
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- BACK TO LANDING -->
<div class="back-link-wrap">
    <a href="/en/" class="back-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Home
    </a>
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
// Nav scroll
var nav = document.getElementById('nav');
window.addEventListener('scroll', function() {
    nav.classList.toggle('scrolled', window.scrollY > 20);
});

// Mobile nav
document.getElementById('navToggle').addEventListener('click', function() {
    document.getElementById('navLinks').classList.toggle('open');
});
document.querySelectorAll('.nav-links a').forEach(function(a) {
    a.addEventListener('click', function() {
        document.getElementById('navLinks').classList.remove('open');
    });
});

// Scroll reveal
var reveals = document.querySelectorAll('.reveal');
var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) entry.target.classList.add('visible');
    });
}, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
reveals.forEach(function(el) { observer.observe(el); });
</script>

</body>
</html>
