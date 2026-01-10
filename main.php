<?php
/**
 * 두손기획인쇄 메인 페이지
 * 기존 슬라이드 이미지를 활용한 현대적 디자인
 */

session_start(); 

// 페이지 설정
$page_title = '▒ 두손기획인쇄 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다.';
$current_page = 'main';

// 데이터베이스 연결 (세션 정보를 위해)
include "db.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="js/jquery-1.4.4.min.js"></script>
    <script src="js/slides.jquery.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        /* 상단 네비게이션 */
        .top-nav {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 0.8rem 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .top-nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .top-nav a:hover {
            color: #3498db;
        }

        .checkboard-btn {
            background: #e67e22;
            color: white !important;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }

        .checkboard-btn:hover {
            background: #d35400;
            color: white !important;
        }

        /* 헤더 섹션 */
        .header-section {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-img {
            max-height: 80px;
        }

        .banner-img {
            max-height: 80px;
        }

        /* 메인 컨테이너 */
        .main-container {
            max-width: 1040px;
            margin: 2rem auto;
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 2rem;
            padding: 0 2rem;
            margin-right: 180px;
        }

        /* 왼쪽 사이드바 */
        .left-sidebar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            height: fit-content;
        }

        /* 중앙 컨텐츠 */
        .main-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* 제품 네비게이션 */
        .product-nav {
            display: flex;
            justify-content: center;
            background: #ecf0f1;
            padding: 1rem;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .product-nav-item {
            transition: all 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .product-nav-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .product-nav-item img {
            display: block;
            transition: filter 0.3s ease;
        }

        .product-nav-item:hover img {
            filter: brightness(1.1);
        }

        /* 슬라이드 영역 */
        .slide-container {
            background: #2c3e50;
            padding: 2rem;
            position: relative;
        }

        #slides {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }

        .slides_container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        .slides_container img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
        }

        /* 슬라이드 네비게이션 */
        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            padding: 15px;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .prev {
            left: 20px;
        }

        .next {
            right: 20px;
        }

        .prev:hover, .next:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
        }

        /* 하단 섹션 */
        .bottom-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }

        .checkboard-banner {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .checkboard-banner:hover {
            transform: translateY(-5px);
        }

        .right-banners {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .right-banner {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .right-banner:hover {
            transform: translateY(-3px);
        }

        /* 오른쪽 사이드바 - right.php에서 position:fixed로 처리 */

        /* 푸터 */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .footer-info {
            display: flex;
            gap: 3rem;
            align-items: center;
        }

        .footer-section h4 {
            margin-bottom: 0.5rem;
            color: #3498db;
        }

        .footer-section p {
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .footer-banks {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .footer-banks img {
            height: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        /* 반응형 디자인 */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-right: auto;
            }

            .left-sidebar {
                display: none;
            }

            .product-nav {
                padding: 0.5rem;
            }

            .slides_container {
                height: 250px;
            }

            .slides_container img {
                height: 250px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .product-nav {
                justify-content: center;
            }

            .bottom-section {
                grid-template-columns: 1fr;
            }

            .footer-content {
                flex-direction: column;
                gap: 2rem;
                text-align: center;
            }

            .footer-info {
                flex-direction: column;
                gap: 1rem;
            }

            .top-nav a {
                margin: 0 8px;
                font-size: 0.9rem;
            }

            .slides_container {
                height: 200px;
            }

            .slides_container img {
                height: 200px;
            }
        }

        /* 애니메이션 */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-container > * {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .main-container > *:nth-child(2) {
            animation-delay: 0.2s;
        }

        .main-container > *:nth-child(3) {
            animation-delay: 0.4s;
        }

        .hero-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateX(0px) translateY(0px); }
            50% { transform: translateX(-10px) translateY(-10px); }
            100% { transform: translateX(0px) translateY(0px); }
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .company-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .company-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .company-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            min-width: 120px;
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* 연락처 섹션 */
        .contact-bar {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            text-align: center;
        }

        .contact-items {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.05);
        }

        .contact-item i {
            font-size: 1.2rem;
        }

        /* 서비스 메뉴 */
        .services-section {
            padding: 4rem 0;
            background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .service-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
            transition: all 0.6s ease;
        }

        .service-card:hover::before {
            left: 100%;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .service-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .service-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .service-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .service-price {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e74c3c;
            margin-bottom: 1rem;
        }

        .service-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .service-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* 특가 서비스 하이라이트 */
        .featured-service {
            border: 3px solid #f39c12;
            position: relative;
        }

        .featured-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: #f39c12;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* 프로세스 섹션 */
        .process-section {
            padding: 4rem 0;
            background: white;
        }

        .process-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 3rem 0;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .process-step {
            flex: 1;
            text-align: center;
            min-width: 200px;
            position: relative;
        }

        .process-step::after {
            content: '→';
            position: absolute;
            right: -1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: #667eea;
        }

        .process-step:last-child::after {
            display: none;
        }

        .process-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 50%;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .process-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .process-desc {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* CTA 섹션 */
        .cta-section {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .cta-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .cta-btn:hover {
            background: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.3);
            color: white;
        }

        .cta-btn.secondary {
            background: transparent;
            border: 2px solid white;
        }

        .cta-btn.secondary:hover {
            background: white;
            color: #2c3e50;
        }

        /* 푸터 */
        .footer {
            background: #1a252f;
            color: white;
            padding: 3rem 0 2rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #667eea;
        }

        .footer-section p, .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            line-height: 1.8;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #34495e;
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
            color: #7f8c8d;
        }

        /* 반응형 */
        @media (max-width: 768px) {
            .company-title {
                font-size: 2rem;
            }

            .hero-stats {
                gap: 1rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .process-steps {
                flex-direction: column;
            }

            .process-step::after {
                display: none;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .contact-items {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* 애니메이션 */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        /* 스크롤 애니메이션 */
        .scroll-animate {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .scroll-animate.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- 상단 네비게이션 -->
    <div class="top-nav">
        <?php include $_SERVER['DOCUMENT_ROOT']."/session/index.php"; ?>
        <a href="/">HOME</a>|
        <a href="/sub/info.php">회사소개</a>|
        <a href="/sub/leaflet.php">포트폴리오</a>|
        <a href="/sub/estimate_auto.php">견적안내</a>|
        <a href="/sub/checkboard.php" class="checkboard-btn">교정보기</a>|
        <a href="/bbs/qna.php">고객문의</a>
    </div>

    <!-- 헤더 섹션 -->
    <div class="header-section">
        <div class="header-content">
            <div class="logo-section">
                <img src="/img/11.jpg" alt="두손기획인쇄 로고" class="logo-img">
            </div>
            <div class="banner-section">
                <img src="/WEBSILDESIGN/swf/WEBSILDESIGN.gif" alt="웹실디자인 배너" class="banner-img">
            </div>
        </div>
    </div>

    <!-- 메인 컨테이너 -->
    <div class="main-container">
        <!-- 왼쪽 사이드바 -->
        <div class="left-sidebar">
            <?php include "left.htm"; ?>
        </div>

        <!-- 중앙 컨텐츠 -->
        <div class="main-content">
            <!-- 제품 네비게이션 -->
            <div class="product-nav">
                <a href="/sub/leaflet.php" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m10a.jpg'" onmouseout="this.querySelector('img').src='img/main_m10.jpg'">
                    <img src="img/main_m10.jpg" alt="전단지" width="77" height="32">
                </a>
                <a href="/mlangprintauto/shop/view_modern.php" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m7a.jpg'" onmouseout="this.querySelector('img').src='img/main_m7.jpg'">
                    <img src="img/main_m7.jpg" alt="스티커" width="77" height="32">
                </a>
                <a href="/sub/catalog.php" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m2a.jpg'" onmouseout="this.querySelector('img').src='img/main_m2.jpg'">
                    <img src="img/main_m2.jpg" alt="카탈로그" width="77" height="32">
                </a>
                <a href="/sub/brochure.php" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m3a.jpg'" onmouseout="this.querySelector('img').src='img/main_m3.jpg'">
                    <img src="img/main_m3.jpg" alt="브로슈어" width="77" height="32">
                </a>
                <a href="/sub/bookdesign.php" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m8a.jpg'" onmouseout="this.querySelector('img').src='img/main_m8.jpg'">
                    <img src="img/main_m8.jpg" alt="도서디자인" width="77" height="32">
                </a>
                <a href="/mlangprintauto/LittlePrint/" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m11a.jpg'" onmouseout="this.querySelector('img').src='img/main_m11.jpg'">
                    <img src="img/main_m11.jpg" alt="포스터" width="76" height="32">
                </a>
                <a href="/mlangprintauto/NameCard/" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m5a.jpg'" onmouseout="this.querySelector('img').src='img/main_m5.jpg'">
                    <img src="img/main_m5.jpg" alt="명함" width="77" height="32">
                </a>
                <a href="/mlangprintauto/envelope/" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m6a.jpg'" onmouseout="this.querySelector('img').src='img/main_m6.jpg'">
                    <img src="img/main_m6.jpg" alt="봉투" width="77" height="32">
                </a>
                <a href="/sub/seosig.php" class="product-nav-item" onmouseover="this.querySelector('img').src='img/main_m1a.jpg'" onmouseout="this.querySelector('img').src='img/main_m1.jpg'">
                    <img src="img/main_m1.jpg" alt="서식" width="77" height="32">
                </a>
            </div>

            <!-- 슬라이드 영역 -->
            <div class="slide-container">
                <div id="slides">
                    <div class="slides_container">
                        <a href="/mlangprintauto/shop/view_modern.php" title="프리미엄 스티커">
                            <img src="img/slide-1.jpg" alt="스티커 슬라이드 1" width="692" height="300">
                        </a>
                        <a href="/sub/leaflet.php" title="전단지/리플릿">
                            <img src="img/slide-2.jpg" alt="전단지 슬라이드 2" width="692" height="300">
                        </a>
                        <a href="/sub/brochure.php" title="브로슈어">
                            <img src="img/slide-3.jpg" alt="브로슈어 슬라이드 3" width="692" height="300">
                        </a>
                        <a href="/sub/catalog.php" title="카탈로그">
                            <img src="img/slide-4.jpg" alt="카탈로그 슬라이드 4" width="692" height="300">
                        </a>
                        <a href="/mlangprintauto/shop/view_modern.php" title="스티커 특가">
                            <img src="img/slide-5.jpg" alt="스티커 슬라이드 5" width="692" height="300">
                        </a>
                        <a href="/mlangprintauto/LittlePrint/" title="포스터">
                            <img src="img/slide-6.jpg" alt="포스터 슬라이드 6" width="692" height="300">
                        </a>
                        <a href="/mlangprintauto/shop/view_modern.php" title="고품질 스티커">
                            <img src="img/slide-7.jpg" alt="스티커 슬라이드 7" width="692" height="300">
                        </a>
                    </div>
                    <a href="#" class="prev">
                        <img src="img/arrow-prev.png" alt="이전" width="24" height="43">
                    </a>
                    <a href="#" class="next">
                        <img src="img/arrow-next.png" alt="다음" width="24" height="43">
                    </a>
                </div>
            </div>

            <!-- 하단 섹션 -->
            <div class="bottom-section">
                <div class="checkboard-banner">
                    <a href="/sub/checkboard.php">
                        <img src="/WEBSILDESIGN/images/main_25.gif" alt="교정보기" width="517" height="52">
                    </a>
                    <div style="margin-top: 1rem;">
                        <?php include "sign.php"; ?>
                    </div>
                </div>
                
                <div class="right-banners">
                    <div class="right-banner">
                        <a href="http://www.ilogen.com/d2d/delivery/invoice_search_popup.jsp?viewType=type1&invoiceNum=" target="_blank">
                            <img src="/WEBSILDESIGN/images/main_27.gif" alt="배송조회" width="170" height="120">
                        </a>
                    </div>
                    <div class="right-banner">
                        <a href="#" onclick="javascript:window.open('https://talk.naver.com/wcbvey?ref='+encodeURIComponent(location.href), 'talktalk', 'width=471, height=640');return false;">
                            <img src="https://ssl.pstatic.net/static.talk/bizmember/banner/btn1_p_v2.png" alt="두손상담창" width="170" height="120">
                        </a>
                    </div>
                </div>
            </div>

            <!-- 프로세스 이미지 -->
            <div style="text-align: center; margin: 2rem 0;">
                <img src="/WEBSILDESIGN/images/step.jpg" alt="주문 프로세스" style="max-width: 100%; height: auto; border-radius: 8px;">
            </div>
        </div>

    </div>

    <!-- 오른쪽 사이드바 (position:fixed) -->
    <?php include "right.php"; ?>

    <!-- 푸터 -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-info">
                <div class="footer-section">
                    <h4>🏢 두손기획인쇄</h4>
                    <p>서울 영등포구 영등포로36길 9, 송호빌딩 1F</p>
                    <p>전화: 02-2632-1830 | 고객센터: 1688-2384</p>
                    <p>이메일: dsp1830@naver.com</p>
                </div>
                
                <div class="footer-section">
                    <h4>💳 계좌 정보</h4>
                    <p>국민은행: 999-1688-2384</p>
                    <p>신한은행: 110-342-543507</p>
                    <p>농협: 301-2632-1830-11</p>
                    <p>예금주: 두손기획인쇄 차경선</p>
                </div>
            </div>
            
            <div class="footer-banks">
                <img src="/img/bank_1.gif" alt="은행1">
                <img src="/img/bank_2.gif" alt="은행2">
                <img src="/img/bank_3.gif" alt="은행3">
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #34495e; color: #95a5a6; font-size: 0.9rem;">
            <p>&copy; 2025 두손기획인쇄. All rights reserved. | 기획에서 인쇄까지 원스톱으로 해결해 드립니다.</p>
        </div>
    </footer>

    <script>
        $(function(){
            $('#slides').slides({
                preload: true,
                preloadImage: 'img/loading.gif',
                play: 3000,
                pause: 4000,
                hoverPause: true,
                animationStart: function(current){
                    $('.caption').animate({
                        bottom:-35
                    },100);
                },
                animationComplete: function(current){
                    $('.caption').animate({
                        bottom:0
                    },200);
                },
                slidesLoaded: function() {
                    $('.caption').animate({
                        bottom:0
                    },200);
                }
            });
        });

        // 부드러운 스크롤 효과
        document.addEventListener('DOMContentLoaded', function() {
            // 모든 링크에 부드러운 스크롤 적용
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // 이미지 지연 로딩
            const images = document.querySelectorAll('img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.style.opacity = '1';
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                img.style.transition = 'opacity 0.3s ease';
                imageObserver.observe(img);
            });
        });

        // 상단으로 스크롤 버튼
        window.onscroll = function() {
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                document.querySelector('.scroll-to-top')?.style.display = 'block';
            } else {
                document.querySelector('.scroll-to-top')?.style.display = 'none';
            }
        };
    </script>

    <!-- 상단으로 스크롤 버튼 -->
    <div class="scroll-to-top" onclick="scrollToTop()" style="
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        text-align: center;
        line-height: 50px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        font-size: 18px;
        transition: all 0.3s ease;
        z-index: 1000;
    ">
        ↑
    </div>

    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
<?php
// 채팅 위젯 포함
include_once __DIR__ . "/includes/chat_widget.php";
?>
</body>
</html>
