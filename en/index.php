<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duson Print — Premium Printing Services from Seoul, Korea</title>
    <meta name="description" content="Premium offset & digital printing from Seoul. Stickers, flyers, business cards, catalogs, posters & more. Factory-direct pricing with worldwide shipping.">
    <meta name="keywords" content="printing service korea, sticker printing, business card printing, flyer printing, catalog printing, poster printing, Seoul printer, international printing">
    <link rel="canonical" href="https://dsp114.co.kr/en/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Duson Print — Premium Printing from Seoul">
    <meta property="og:description" content="Factory-direct printing services. Stickers, flyers, business cards, catalogs & more. Worldwide shipping.">
    <meta property="og:image" content="https://dsp114.co.kr/ImgFolder/dusonlogo1.png">
    <meta property="og:url" content="https://dsp114.co.kr/en/">
    <link rel="icon" type="image/png" href="/ImgFolder/dusonlogo1.png">

    <!-- Fonts: DM Sans (body) + Sora (headings) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

        /* nav styles moved to /en/includes/nav.php */

        /* ===== HERO ===== */
        .hero {
            padding: 180px 24px 80px;
            background: linear-gradient(165deg, var(--navy-dark) 0%, var(--navy) 40%, var(--navy-light) 100%);
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -50%; right: -20%;
            width: 800px; height: 800px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,213,0,0.08) 0%, transparent 70%);
        }
        .hero::after {
            content: ''; position: absolute; bottom: -30%; left: -10%;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 60%);
        }
        .hero-inner {
            max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;
            display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 60px; align-items: center;
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
            font-family: var(--font-heading); font-size: 52px; font-weight: 800;
            color: var(--white); line-height: 1.15; letter-spacing: -1.5px; margin-bottom: 20px;
        }
        .hero h1 em {
            font-style: normal;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            font-size: 18px; line-height: 1.7; color: rgba(255,255,255,0.7);
            max-width: 520px; margin-bottom: 32px;
        }
        .hero-btns { display: flex; gap: 14px; flex-wrap: wrap; }
        .btn-gold {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; border-radius: 12px;
            background: var(--gold); color: var(--navy-dark);
            font-size: 15px; font-weight: 700; text-decoration: none;
            transition: all 0.25s; border: none; cursor: pointer;
        }
        .btn-gold:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(255,213,0,0.3); }
        .btn-outline {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; border-radius: 12px;
            background: transparent; color: var(--white);
            font-size: 15px; font-weight: 600; text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.25); transition: all 0.25s;
            cursor: pointer;
        }
        .btn-outline:hover { border-color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.06); }

        .hero-stats {
            display: flex; gap: 32px; margin-top: 40px;
        }
        .hero-stat { text-align: left; }
        .hero-stat-num {
            font-family: var(--font-heading); font-size: 32px; font-weight: 800;
            color: var(--gold); line-height: 1;
        }
        .hero-stat-label { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 4px; }

        .hero-visual {
            display: flex; align-items: center; justify-content: center;
        }
        .hero-video-wrap {
            width: 100%; max-width: 480px; border-radius: var(--radius-lg);
            overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            position: relative; cursor: pointer;
            border: 2px solid rgba(255,255,255,0.1);
        }
        .hero-video-wrap video,
        .hero-video-wrap img { width: 100%; display: block; aspect-ratio: 16/9; object-fit: cover; }
        .hero-video-wrap video { display: none; }
        .hero-play-btn {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(255,255,255,0.95); display: flex; align-items: center; justify-content: center;
            transition: all 0.3s; box-shadow: 0 4px 30px rgba(0,0,0,0.2);
        }
        .hero-video-wrap:hover .hero-play-btn { transform: translate(-50%,-50%) scale(1.08); }
        .hero-play-btn svg { width: 28px; height: 28px; margin-left: 3px; }

        /* ===== SECTIONS ===== */
        .section { padding: 100px 24px; }
        .section-header { text-align: center; margin-bottom: 56px; }
        .section-tag {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700; color: var(--navy);
            text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px;
        }
        .section-tag::before {
            content: ''; width: 24px; height: 2px; background: var(--gold); display: inline-block;
        }
        .section-title {
            font-family: var(--font-heading); font-size: 40px; font-weight: 800;
            color: var(--text); line-height: 1.2; letter-spacing: -1px;
        }
        .section-sub {
            font-size: 17px; color: var(--text-muted); max-width: 560px; margin: 12px auto 0; line-height: 1.7;
        }
        .container { max-width: 1200px; margin: 0 auto; }

        /* ===== PRODUCTS ===== */
        .products-bg { background: var(--white); }
        .products-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
        }
        .p-card {
            background: var(--bg); border-radius: var(--radius-lg); overflow: hidden;
            transition: all 0.35s; border: 1px solid var(--border);
            display: flex; flex-direction: column;
        }
        .p-card:hover {
            transform: translateY(-6px); box-shadow: var(--shadow-lg);
            border-color: transparent;
        }
        .p-card-img {
            height: 180px; display: flex; align-items: center; justify-content: center;
            padding: 24px; background: var(--white);
        }
        .p-card-img img { max-height: 100%; max-width: 100%; object-fit: contain; }
        .p-card-body { padding: 20px 24px 24px; flex: 1; display: flex; flex-direction: column; }
        .p-card-name {
            font-family: var(--font-heading); font-size: 18px; font-weight: 700;
            color: var(--text); margin-bottom: 6px;
        }
        .p-card-desc { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 14px; flex: 1; }
        .p-card-features { display: flex; flex-wrap: wrap; gap: 6px; }
        .p-card-feat {
            font-size: 11px; font-weight: 600; padding: 4px 10px;
            border-radius: 6px; background: var(--white); color: var(--navy);
            border: 1px solid rgba(30,78,121,0.12);
        }

        /* ===== USP ===== */
        .usp-bg { background: var(--bg-warm); }
        .usp-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; }
        .usp-card {
            background: var(--white); border-radius: var(--radius-lg); padding: 32px 28px;
            text-align: center; border: 1px solid var(--border); transition: all 0.3s;
        }
        .usp-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .usp-icon {
            width: 56px; height: 56px; border-radius: 14px;
            background: linear-gradient(135deg, var(--navy), var(--navy-light));
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px; font-size: 24px;
        }
        .usp-title { font-family: var(--font-heading); font-size: 17px; font-weight: 700; margin-bottom: 8px; }
        .usp-desc { font-size: 14px; color: var(--text-muted); line-height: 1.6; }

        /* ===== QUOTE FORM ===== */
        .quote-bg {
            background: linear-gradient(165deg, var(--navy-dark) 0%, var(--navy) 100%);
            position: relative; overflow: hidden;
        }
        .quote-bg::before {
            content: ''; position: absolute; top: -40%; right: -15%;
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,213,0,0.06) 0%, transparent 60%);
        }
        .quote-inner {
            max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;
            display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start;
        }
        .quote-text h2 {
            font-family: var(--font-heading); font-size: 38px; font-weight: 800;
            color: var(--white); line-height: 1.2; letter-spacing: -1px; margin-bottom: 16px;
        }
        .quote-text p { font-size: 17px; color: rgba(255,255,255,0.65); line-height: 1.7; margin-bottom: 32px; }
        .quote-benefit {
            display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
        }
        .quote-benefit-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,213,0,0.12); display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .quote-benefit-text { font-size: 15px; color: rgba(255,255,255,0.8); font-weight: 500; }

        .quote-form-card {
            background: var(--white); border-radius: var(--radius-lg); padding: 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .quote-form-title {
            font-family: var(--font-heading); font-size: 22px; font-weight: 700;
            margin-bottom: 24px;
        }
        .form-row { margin-bottom: 16px; }
        .form-row-half { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-label {
            display: block; font-size: 13px; font-weight: 600; color: var(--text);
            margin-bottom: 6px;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 12px 16px; border-radius: 10px;
            border: 1.5px solid var(--border); font-family: var(--font-body);
            font-size: 14px; color: var(--text); background: var(--bg);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,78,121,0.1);
        }
        .form-textarea { resize: vertical; min-height: 80px; }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        .btn-submit {
            width: 100%; padding: 14px; border-radius: 12px;
            background: var(--navy); color: var(--white);
            font-family: var(--font-heading); font-size: 16px; font-weight: 700;
            border: none; cursor: pointer; transition: all 0.25s;
        }
        .btn-submit:hover { background: var(--navy-dark); transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .form-msg { margin-top: 14px; padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 500; display: none; }
        .form-msg.success { display: block; background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
        .form-msg.error { display: block; background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }

        /* ===== ABOUT ===== */
        .about-bg { background: var(--white); }
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; }
        .about-img {
            border-radius: var(--radius-lg); overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        .about-img img { width: 100%; display: block; }
        .about-content h2 {
            font-family: var(--font-heading); font-size: 36px; font-weight: 800;
            line-height: 1.2; letter-spacing: -0.5px; margin-bottom: 16px;
        }
        .about-content p { font-size: 16px; color: var(--text-muted); line-height: 1.7; margin-bottom: 20px; }

        .contact-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 24px; }
        .contact-card {
            padding: 16px; border-radius: 12px; background: var(--bg);
            border: 1px solid var(--border);
        }
        .contact-card-label { font-size: 12px; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .contact-card-value { font-size: 15px; font-weight: 600; color: var(--text); }
        .contact-card-value a { color: var(--navy); text-decoration: none; }
        .contact-card-value a:hover { text-decoration: underline; }

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
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .hero-inner { grid-template-columns: 1fr; text-align: center; }
            .hero h1 { font-size: 40px; }
            .hero p { margin: 0 auto 32px; }
            .hero-btns { justify-content: center; }
            .hero-stats { justify-content: center; }
            .hero-visual { margin-top: 40px; }
            .products-grid { grid-template-columns: repeat(2, 1fr); }
            .usp-grid { grid-template-columns: repeat(2, 1fr); }
            .quote-inner { grid-template-columns: 1fr; }
            .quote-text { text-align: center; }
            .about-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .hero { padding: 150px 20px 60px; }
            .hero h1 { font-size: 32px; }
            .hero p { font-size: 16px; }
            .hero-stats { flex-direction: row; gap: 20px; }
            .hero-stat-num { font-size: 24px; }
            .section { padding: 64px 20px; }
            .section-title { font-size: 30px; }
            .products-grid { grid-template-columns: 1fr; }
            .usp-grid { grid-template-columns: 1fr; }
            .form-row-half { grid-template-columns: 1fr; }
            .contact-cards { grid-template-columns: 1fr; }
            .footer-inner { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<?php $_en_current_page = ''; include __DIR__ . '/includes/nav.php'; ?>

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-inner">
        <div>
            <div class="hero-badge">Seoul, South Korea &mdash; Shipping Worldwide</div>
            <h1>Premium <em>Printing</em><br>Services from Korea</h1>
            <p>From stickers to catalogs &mdash; high-quality offset &amp; digital printing at factory-direct prices. Fast turnaround with international shipping.</p>
            <div class="hero-btns">
                <a href="#quote" class="btn-gold">
                    Request Free Quote
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="#products" class="btn-outline">View Products</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-num">20+</div>
                    <div class="hero-stat-label">Years of Experience</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num">9</div>
                    <div class="hero-stat-label">Product Categories</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num">50K+</div>
                    <div class="hero-stat-label">Orders Completed</div>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-video-wrap" id="heroVideoWrap" onclick="toggleHeroVideo()">
                <img src="/media/explainer_poster.jpg" alt="Duson Print Introduction" id="heroPoster">
                <video id="heroVideo" preload="none" playsinline>
                    <source src="/media/explainer_90s.mp4" type="video/mp4">
                </video>
                <div class="hero-play-btn" id="heroPlayBtn">
                    <svg viewBox="0 0 24 24" fill="var(--navy)"><path d="M8 5v14l11-7z"/></svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== PRODUCTS ===== -->
<section class="section products-bg" id="products">
    <div class="container">
        <div class="section-header reveal">
            <div class="section-tag">Our Products</div>
            <h2 class="section-title">Everything You Need, Printed Perfectly</h2>
            <p class="section-sub">From everyday business essentials to specialty items &mdash; all produced in our own factory with premium materials and precise craftsmanship.</p>
        </div>
        <div class="products-grid">
            <div class="p-card reveal">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/sticker_new_s.png" alt="Stickers"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Stickers &amp; Labels</div>
                    <div class="p-card-desc">Custom die-cut stickers in any shape. Waterproof, transparent, and metallic options available.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">Waterproof</span>
                        <span class="p-card-feat">Die-Cut</span>
                        <span class="p-card-feat">Custom Shape</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal reveal-delay-1">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/inserted_s.png" alt="Flyers"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Flyers &amp; Leaflets</div>
                    <div class="p-card-desc">High-resolution promotional flyers with vivid color reproduction. Single or double-sided.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">High-Res</span>
                        <span class="p-card-feat">Fast Print</span>
                        <span class="p-card-feat">Bulk Ready</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal reveal-delay-2">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/namecard_s.png" alt="Business Cards"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Business Cards</div>
                    <div class="p-card-desc">Professional business cards with premium finishes. UV coating and embossing available.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">UV Coating</span>
                        <span class="p-card-feat">Embossing</span>
                        <span class="p-card-feat">Same-Day</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/envelop_s.png" alt="Envelopes"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Envelopes</div>
                    <div class="p-card-desc">Standard and window envelopes for professional correspondence. Full-color printing.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">Window</span>
                        <span class="p-card-feat">Full Color</span>
                        <span class="p-card-feat">Bulk</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal reveal-delay-1">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/catalogue_s.png" alt="Catalogs"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Catalogs &amp; Booklets</div>
                    <div class="p-card-desc">Full-color product catalogs with professional binding. Saddle-stitch or perfect binding.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">Full Color</span>
                        <span class="p-card-feat">Perfect Bind</span>
                        <span class="p-card-feat">Custom Size</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal reveal-delay-2">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/poster_s.png" alt="Posters"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Posters</div>
                    <div class="p-card-desc">Large format posters with vivid color output. Perfect for events, retail, and promotion.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">Large Format</span>
                        <span class="p-card-feat">High Quality</span>
                        <span class="p-card-feat">Lamination</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/ncr_s.png" alt="NCR Forms"></div>
                <div class="p-card-body">
                    <div class="p-card-name">NCR Forms</div>
                    <div class="p-card-desc">Carbonless copy paper forms. 2 to 4 ply options for invoices, receipts, and order forms.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">2-4 Ply</span>
                        <span class="p-card-feat">Carbonless</span>
                        <span class="p-card-feat">Numbered</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal reveal-delay-1">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/merchandise_s.png" alt="Gift Vouchers"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Gift Vouchers</div>
                    <div class="p-card-desc">Professional gift certificates and coupons with anti-counterfeit features and serial numbering.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">Anti-Counterfeit</span>
                        <span class="p-card-feat">Serial No.</span>
                    </div>
                </div>
            </div>
            <div class="p-card reveal reveal-delay-2">
                <div class="p-card-img"><img src="/ImgFolder/gate_picto/m_sticker_s.png" alt="Magnetic Stickers"></div>
                <div class="p-card-body">
                    <div class="p-card-name">Magnetic Stickers</div>
                    <div class="p-card-desc">Strong magnetic stickers perfect for vehicles, refrigerators, and promotional displays.</div>
                    <div class="p-card-features">
                        <span class="p-card-feat">Vehicle Grade</span>
                        <span class="p-card-feat">Strong Magnet</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== WHY US ===== -->
<section class="section usp-bg" id="why-us">
    <div class="container">
        <div class="section-header reveal">
            <div class="section-tag">Why Duson Print</div>
            <h2 class="section-title">Your Trusted Printing Partner</h2>
            <p class="section-sub">We own our production facility &mdash; giving you better prices, faster turnaround, and direct quality control.</p>
        </div>
        <div class="usp-grid">
            <div class="usp-card reveal">
                <div class="usp-icon">🏭</div>
                <div class="usp-title">Factory Direct</div>
                <div class="usp-desc">We own our printing facility. No middlemen, no markups &mdash; just factory-direct pricing on every order.</div>
            </div>
            <div class="usp-card reveal reveal-delay-1">
                <div class="usp-icon">⚡</div>
                <div class="usp-title">Fast Turnaround</div>
                <div class="usp-desc">Same-day production available for business cards. Most orders ship within 1-3 business days.</div>
            </div>
            <div class="usp-card reveal reveal-delay-2">
                <div class="usp-icon">🌏</div>
                <div class="usp-title">Worldwide Shipping</div>
                <div class="usp-desc">International delivery via EMS, DHL, and FedEx. We ship to over 100 countries from Seoul.</div>
            </div>
            <div class="usp-card reveal reveal-delay-3">
                <div class="usp-icon">💎</div>
                <div class="usp-title">Premium Quality</div>
                <div class="usp-desc">20+ years of printing expertise with state-of-the-art Heidelberg offset presses and precision finishing.</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== QUOTE FORM ===== -->
<section class="section quote-bg" id="quote">
    <div class="quote-inner">
        <div class="quote-text reveal">
            <h2>Get Your Free Quote in Minutes</h2>
            <p>Tell us what you need and we'll respond with a detailed quote within 24 hours. No commitment required.</p>
            <div class="quote-benefit">
                <div class="quote-benefit-icon">📋</div>
                <div class="quote-benefit-text">Detailed itemized quote</div>
            </div>
            <div class="quote-benefit">
                <div class="quote-benefit-icon">💬</div>
                <div class="quote-benefit-text">English support available</div>
            </div>
            <div class="quote-benefit">
                <div class="quote-benefit-icon">🚚</div>
                <div class="quote-benefit-text">Shipping cost included</div>
            </div>
            <div class="quote-benefit">
                <div class="quote-benefit-icon">🔒</div>
                <div class="quote-benefit-text">Secure payment via PayPal or wire transfer</div>
            </div>
        </div>
        <div class="quote-form-card reveal reveal-delay-1">
            <div class="quote-form-title">Request a Quote</div>
            <form id="quoteForm" onsubmit="return submitQuote(event)">
                <div class="form-row form-row-half">
                    <div>
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-input" placeholder="John Smith" required>
                    </div>
                    <div>
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" placeholder="john@company.com" required>
                    </div>
                </div>
                <div class="form-row form-row-half">
                    <div>
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-input" placeholder="+1-555-123-4567" required>
                    </div>
                    <div>
                        <label class="form-label">Product *</label>
                        <select name="product" class="form-select" required>
                            <option value="">Select product...</option>
                            <option value="stickers">Stickers &amp; Labels</option>
                            <option value="flyers">Flyers &amp; Leaflets</option>
                            <option value="business-cards">Business Cards</option>
                            <option value="envelopes">Envelopes</option>
                            <option value="catalogs">Catalogs &amp; Booklets</option>
                            <option value="posters">Posters</option>
                            <option value="ncr-forms">NCR Forms</option>
                            <option value="gift-vouchers">Gift Vouchers</option>
                            <option value="magnetic-stickers">Magnetic Stickers</option>
                            <option value="other">Other / Multiple</option>
                        </select>
                    </div>
                </div>
                <div class="form-row form-row-half">
                    <div>
                        <label class="form-label">Quantity *</label>
                        <input type="text" name="quantity" class="form-input" placeholder="e.g. 1,000 pcs" required>
                    </div>
                    <div>
                        <label class="form-label">Size / Specifications</label>
                        <input type="text" name="specs" class="form-input" placeholder="e.g. A4, 150gsm">
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label">Additional Details</label>
                    <textarea name="notes" class="form-textarea" placeholder="Tell us more about your project — finishing options, colors, design requirements, shipping destination, etc."></textarea>
                </div>
                <button type="submit" class="btn-submit" id="submitBtn">
                    Send Quote Request
                </button>
                <div class="form-msg" id="formMsg"></div>
            </form>
        </div>
    </div>
</section>

<!-- ===== ABOUT ===== -->
<section class="section about-bg" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-img reveal">
                <img src="/media/explainer_poster.jpg" alt="Duson Print Factory">
            </div>
            <div class="about-content reveal reveal-delay-1">
                <div class="section-tag">About Us</div>
                <h2>Printing Excellence Since 2004</h2>
                <p>Duson Planning Print has been delivering premium printing services from our factory in Seoul, South Korea for over 20 years. We specialize in offset and digital printing for businesses of all sizes.</p>
                <p>Our commitment to quality, speed, and customer satisfaction has made us a trusted partner for thousands of companies in Korea &mdash; and now, we're bringing the same excellence to the world.</p>
                <div class="contact-cards">
                    <div class="contact-card">
                        <div class="contact-card-label">Phone</div>
                        <div class="contact-card-value"><a href="tel:+82-2-2632-1830">+82-2-2632-1830</a></div>
                    </div>
                    <div class="contact-card">
                        <div class="contact-card-label">Email</div>
                        <div class="contact-card-value"><a href="mailto:dsp1830@naver.com">dsp1830@naver.com</a></div>
                    </div>
                    <div class="contact-card">
                        <div class="contact-card-label">Hours</div>
                        <div class="contact-card-value">Mon-Fri 09:00-18:00 KST</div>
                    </div>
                    <div class="contact-card">
                        <div class="contact-card-label">Address</div>
                        <div class="contact-card-value" style="font-size:13px;">1F Songho Bldg, 9 Yeongdeungpo-ro 36-gil, Seoul</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
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
            <a href="#quote">Get Quote</a>
        </div>
    </div>
</footer>

<script>
// Hero video
function toggleHeroVideo() {
    var video = document.getElementById('heroVideo');
    var poster = document.getElementById('heroPoster');
    var btn = document.getElementById('heroPlayBtn');
    if (!video) return;
    if (video.paused) {
        poster.style.display = 'none';
        btn.style.display = 'none';
        video.style.display = 'block';
        video.play();
    } else {
        video.pause();
        video.style.display = 'none';
        poster.style.display = 'block';
        btn.style.display = 'flex';
    }
}
var hv = document.getElementById('heroVideo');
if (hv) {
    hv.addEventListener('ended', function() {
        this.style.display = 'none';
        document.getElementById('heroPoster').style.display = 'block';
        document.getElementById('heroPlayBtn').style.display = 'flex';
    });
}

// Scroll reveal
var reveals = document.querySelectorAll('.reveal');
var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
reveals.forEach(function(el) { observer.observe(el); });

// Quote form
function submitQuote(e) {
    e.preventDefault();
    var form = document.getElementById('quoteForm');
    var btn = document.getElementById('submitBtn');
    var msg = document.getElementById('formMsg');
    var data = new FormData(form);
    var payload = {};
    data.forEach(function(v, k) { payload[k] = v; });

    btn.disabled = true;
    btn.textContent = 'Sending...';
    msg.className = 'form-msg';
    msg.style.display = 'none';

    fetch('/en/quote_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            msg.className = 'form-msg success';
            msg.textContent = '✓ ' + res.message;
            msg.style.display = 'block';
            form.reset();
        } else {
            msg.className = 'form-msg error';
            msg.textContent = res.message || 'Something went wrong. Please try again.';
            msg.style.display = 'block';
        }
    })
    .catch(function() {
        msg.className = 'form-msg error';
        msg.textContent = 'Network error. Please email us directly at dsp1830@naver.com';
        msg.style.display = 'block';
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = 'Send Quote Request';
    });

    return false;
}
</script>

</body>
</html>
