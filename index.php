<?php
session_start();
$session_id = session_id();

// 출력 버퍼 관리 및 에러 설정
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 보안 상수 정의 후 데이터베이스 연결
include "db.php";
$connect = $db;

// 방문자 추적 시스템
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

// 페이지 설정
$page_title = '두손기획인쇄 - 스티커 라벨 인쇄 전문 | 전단지 리플렛 명함 쿠폰 봉투 카다록 포스터 상품권 양식지';
$current_page = 'home';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

// 공통 함수 및 설정
if (file_exists('includes/functions.php')) {
    include "includes/functions.php";
}

// 공통 인증 시스템 사용
include "includes/auth.php";
$is_logged_in = isLoggedIn() || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

// 사용자 정보 설정
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
} elseif (isset($_COOKIE['id_login_ok'])) {
    $user_name = $_COOKIE['id_login_ok'];
} else {
    $user_name = '';
}

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- SEO 메타 태그 -->
    <meta name="description" content="스티커 인쇄 전문 두손기획인쇄. 투명스티커, 유포지스티커, 자석스티커 등 다양한 스티커 제작. 전단지, 명함, 봉투, 카다록, 포스터, 상품권, 양식지까지. 공장직영 신속제작, 온라인 견적">
    <meta name="keywords" content="스티커인쇄, 라벨인쇄, 투명스티커, 유포지스티커, 자석스티커, 스티커제작, 전단지인쇄, 명함인쇄, 쿠폰인쇄, 봉투인쇄, 카다록인쇄, 포스터인쇄, 상품권제작, 양식지인쇄, 온라인견적, 인쇄전문, 두손기획, 두손기획인쇄">
    <meta name="author" content="두손기획인쇄">
    <meta name="naver-site-verification" content="33529ae09a9a019b325c1c07cffc6f3b8c85c9a0" />
    <link rel="canonical" href="https://dsp114.com/">

    <!-- Open Graph (카카오톡, 페이스북 공유용) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="두손기획인쇄 - 스티커 인쇄 전문 | 전단지 명함 봉투 카다록 포스터">
    <meta property="og:description" content="스티커 인쇄 전문. 투명스티커, 유포지스티커, 자석스티커 등 다양한 스티커 제작. 전단지, 명함 등 모든 인쇄물 온라인 견적">
    <meta property="og:image" content="https://dsp114.com/ImgFolder/og-image.png">
    <meta property="og:url" content="https://dsp114.com/">
    <meta property="og:site_name" content="두손기획인쇄">
    <meta property="og:locale" content="ko_KR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="두손기획인쇄 - 스티커 전단지 명함 인쇄 전문">
    <meta name="twitter:description" content="기획에서 인쇄까지 원스톱 서비스. 10가지 인쇄물 온라인 견적">
    <meta name="twitter:image" content="https://dsp114.com/ImgFolder/og-image.png">

    <!-- 세션 ID 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars($session_id); ?>">

    <!-- 브랜드 디자인 시스템 (최우선 로드) -->
    <link rel="stylesheet" href="css/brand-design-system.css?v=<?php echo time(); ?>">

    <!-- 홈페이지 전용 CSS -->
    <link rel="stylesheet" href="css/product-layout.css">
    <link rel="stylesheet" href="assets/css/layout.css?v=<?php echo time(); ?>">

    <!-- 브랜드 폰트 - Pretendard & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- JSON-LD 구조화 데이터 (Google 검색 결과 향상) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "두손기획인쇄",
        "alternateName": "Duson Planning Print",
        "description": "스티커 인쇄 전문. 투명스티커, 유포지스티커, 자석스티커, 전단지, 명함, 봉투, 카다록, 포스터, 상품권, NCR양식지 제작. 공장직영 신속제작.",
        "url": "https://dsp114.com",
        "logo": "https://dsp114.com/ImgFolder/dusonlogo1.png",
        "image": "https://dsp114.com/ImgFolder/dusonlogo1.png",
        "telephone": "+82-2-2632-1830",
        "faxNumber": "+82-2-2632-1831",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "서울특별시",
            "addressRegion": "영등포구",
            "addressCountry": "KR"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 37.5165,
            "longitude": 126.9074
        },
        "openingHoursSpecification": {
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
            "opens": "09:00",
            "closes": "18:00"
        },
        "priceRange": "₩₩",
        "areaServed": "KR",
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "인쇄 서비스",
            "itemListElement": [
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "스티커 인쇄"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "전단지 인쇄"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "명함 인쇄"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "봉투 인쇄"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "포스터 인쇄"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "카다록 인쇄"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "상품권 제작"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "NCR양식지 인쇄"}},
                {"@type": "Offer", "itemOffered": {"@type": "Service", "name": "자석스티커 인쇄"}}
            ]
        },
        "sameAs": []
    }
    </script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .slider-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 100%;
        }

        .slider-track {
            display: flex;
            flex-wrap: nowrap;
            width: 1300%; /* 13 slides (11 + 2 clones) * 100% */
            height: 100%;
            transition: transform 1000ms ease-in-out;
        }

        .slider-track.no-transition {
            transition: none;
        }

        .slider-slide {
            width: calc(100% / 13); /* 100% / 13 slides */
            height: 100%;
            flex-shrink: 0;
            position: relative;
        }

        .slider-slide img,
        .slider-slide .slider-img {
            width: 100%;
            height: 260px;
            object-fit: cover;
            object-position: center center;
        }

        .slider-dot.active {
            background: white !important;
            transform: scale(1.2);
        }

        .video-slide-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .video-slide-wrapper video {
            width: 100%;
            height: 260px;
            object-fit: cover;
            object-position: center;
            display: none;
        }
        .video-play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 64px;
            height: 64px;
            background: rgba(0,0,0,0.55);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, transform 0.2s;
            pointer-events: none;
        }
        .video-slide-wrapper:hover .video-play-btn {
            background: rgba(0,0,0,0.75);
            transform: translate(-50%, -50%) scale(1.1);
        }
        .video-play-btn.hidden { display: none; }

        @media (max-width: 768px) {
            .video-slide-wrapper video {
                height: 180px;
            }
            .video-play-btn {
                width: 48px;
                height: 48px;
            }
        }

        /* 데스크톱 슬라이더 기본 높이 */
        #hero-slider {
            height: 260px;
        }

        @media (max-width: 768px) {
            .slider-prev, .slider-next {
                display: none;
            }

            /* 모바일 슬라이더 - vw 단위 사용 */
            #slider-section {
                width: 100vw;
                max-width: 100vw;
                overflow: hidden;
                margin-left: calc(-50vw + 50%);
            }

            #hero-slider {
                width: 100vw;
                height: 180px;
                overflow: hidden;
            }

            .slider-container {
                width: 100vw;
                height: 180px;
                overflow: hidden;
            }

            .slider-track {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                width: 1300vw;
                height: 180px;
            }

            .slider-slide {
                width: 100vw;
                min-width: 100vw;
                max-width: 100vw;
                height: 180px;
                flex: 0 0 100vw;
            }

            .slider-slide img,
            .slider-slide .slider-img {
                display: block;
                width: 100vw;
                height: 180px;
                object-fit: cover;
                object-position: left center;
                transform: none;
            }
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(4, 1fr);
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .products-grid .product-card {
            height: 220px;
            overflow: visible;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .products-grid .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .product-card .product-header {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
            padding: 12px 20px;
            min-height: 44px;
        }

        .product-card .product-body {
            padding: 0 20px 20px 20px;
            display: grid;
            grid-template-columns: 1fr 140px;
            gap: 16px;
            flex: 1;
        }

        /* 이미지 없는 카드 (별도견적 제품) */
        .product-card-no-image .product-body {
            display: block;
            grid-template-columns: none;
        }

        .product-content-single {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .product-action {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        .product-action .btn-product {
            flex: 1;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .product-action .btn-secondary {
            background: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .product-action .btn-secondary:hover {
            background: #f3f4f6;
        }

        .product-card .product-content-left {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card .product-content-left .btn-product.btn-primary {
            width: 177px;
            height: 40px;
            min-width: 177px;
            max-width: 177px;
            min-height: 40px;
            max-height: 40px;
            margin-top: auto;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .product-card .product-content-right {
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .product-card .product-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
            color: #ffffff;
            display: flex;
            align-items: center;
        }

        .product-card .product-title a {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #ffffff;
            text-decoration: none;
            line-height: 1;
        }

        .product-card .product-subtitle {
            font-size: 0.8125rem;
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
            font-weight: 500;
            letter-spacing: -0.01em;
        }

        .product-card .product-features {
            margin: 0;
            padding: 0;
            list-style: none;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .product-card .product-features li {
            font-size: 0.8125rem;
            padding: 0;
            margin: 0 0 6px 0;
            line-height: 1.5;
            color: #4b5563;
            text-align: center;
        }

        .product-card .product-features li:before {
            content: "✓ ";
            color: #10b981;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .product-card .product-features li:last-child {
            margin-bottom: 0;
        }

        /* 태블릿 (1024px 이하) */
        @media (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: auto;
                gap: 20px;
                padding: 30px 16px;
            }
        }

        /* 모바일 (768px 이하) */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: auto;
                gap: 16px;
                padding: 24px 12px;
            }

            .products-grid .product-card {
                height: 200px;
            }

            .product-card .product-header {
                padding: 10px 16px;
                min-height: 40px;
            }

            .product-card .product-title {
                font-size: 1.125rem;
            }

            .product-card .product-subtitle {
                font-size: 0.75rem;
            }

            .product-card .product-body {
                padding: 0 16px 16px 16px;
                gap: 12px;
            }

            .product-card .product-features {
                margin: 0;
            }

            .product-card .product-features li {
                font-size: 0.75rem;
                margin: 0 0 4px 0;
                text-align: center;
            }

            .product-card .product-image {
                width: 100px;
                height: 100px;
            }

            .product-card .product-content-left .btn-product.btn-primary {
                width: 140px;
                height: 36px;
                min-width: 140px;
                max-width: 140px;
                min-height: 36px;
                max-height: 36px;
                font-size: 0.8125rem;
            }

            .product-action .btn-product {
                height: 36px;
                font-size: 0.8125rem;
            }
        }

        /* 초소형 모바일 (480px 이하) */
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 20px 8px;
            }

            .products-grid .product-card {
                height: auto;
                min-height: 180px;
            }

            .product-card .product-body {
                grid-template-columns: 1fr 90px;
                gap: 10px;
            }

            .product-card .product-image {
                width: 90px;
                height: 90px;
            }

            .product-action {
                flex-direction: column;
                gap: 6px;
            }

            .product-action .btn-product {
                width: 100%;
                height: 36px;
                font-size: 0.8125rem;
            }
        }

        .products-section {
            margin: 0 auto;
            padding: 20px 0;
            max-width: 1200px;
            width: 100%;
        }

        .section-header {
            margin: 0 auto;
            margin-bottom: 0;
            padding-bottom: 0;
            text-align: center;
            width: 100%;
            max-width: 1200px;
        }


        /* 🖼️ 이미지 고정 크기 */
        .product-card .product-image {
            width: 140px;
            height: 140px;
            flex-shrink: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .product-card .product-image:hover {
            transform: scale(1.05);
        }

        .product-card .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>

    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="css/common-styles.css?v=<?php echo time(); ?>">

    <link rel="icon" type="image/png" href="/ImgFolder/dusonlogo1.png">
    <link rel="apple-touch-icon" href="/ImgFolder/icon-192x192.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="두손기획인쇄">
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
    </script>
</head>

<body>
    <?php include "includes/header-ui.php"; ?>
    <?php if (file_exists('includes/nav.php')) include "includes/nav.php"; ?>

    <!-- Hero Slider Section -->
    <section id="slider-section" style="max-width: 1200px; margin: 0 auto; position: relative; overflow: hidden;">
        <div id="hero-slider">
            <!-- Slider Content -->
            <div class="slider-container">
                <div class="slider-track" id="sliderTrack">
                    <!-- Clone of last slide (for infinite loop) -->
                    <div class="slider-slide clone" data-slide="-1">
                        <div class="video-slide-wrapper" onclick="toggleSliderVideo()">
                            <img src="/media/explainer_poster.jpg" alt="두손기획인쇄 소개 영상" class="slider-img">
                            <video preload="none" playsinline>
                                <source src="/media/explainer_90s.mp4" type="video/mp4">
                            </video>
                            <div class="video-play-btn">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 1: 전단지 -->
                    <div class="slider-slide" data-slide="0">
                        <img src="/slide/slide_inserted.gif" alt="전단지 인쇄 서비스" class="slider-img">
                    </div>

                    <!-- Slide 2: 스티커 -->
                    <div class="slider-slide" data-slide="1">
                        <img src="/slide/slide__Sticker.gif" alt="스티커 인쇄 서비스" class="slider-img">
                    </div>

                    <!-- Slide 3: 카다록 -->
                    <div class="slider-slide" data-slide="2">
                        <img src="/slide/slide_cadarok.gif" alt="카다록 인쇄 서비스" class="slider-img">
                    </div>

                    <!-- Slide 4: NCR 양식지 -->
                    <div class="slider-slide" data-slide="3">
                        <img src="/slide/slide_Ncr.gif" alt="NCR 양식지 인쇄 서비스" class="slider-img">
                    </div>

                    <!-- Slide 5: 포스터 -->
                    <div class="slider-slide" data-slide="4">
                        <img src="/slide/slide__poster.gif" alt="포스터 인쇄 서비스" class="slider-img">
                    </div>

                    <!-- Slide 6: 스티커 2 -->
                    <div class="slider-slide" data-slide="5">
                        <img src="/slide/slide__Sticker_2.gif" alt="스티커 제작 서비스 2" class="slider-img">
                    </div>

                    <!-- Slide 7: 스티커 3 -->
                    <div class="slider-slide" data-slide="6">
                        <img src="/slide/slide__Sticker_3.gif" alt="스티커 제작 서비스 3" class="slider-img">
                    </div>

                    <!-- Slide 8: 회사소개 영상 -->
                    <div class="slider-slide" data-slide="7">
                        <div class="video-slide-wrapper" id="videoSlideWrapper" onclick="toggleSliderVideo()">
                            <img src="/media/explainer_poster.jpg" alt="두손기획인쇄 소개 영상" class="slider-img" id="videoPoster">
                            <video id="sliderVideo" preload="none" playsinline>
                                <source src="/media/explainer_90s.mp4" type="video/mp4">
                            </video>
                            <div class="video-play-btn" id="videoPlayBtn">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Clone of first slide (for infinite loop) -->
                    <div class="slider-slide clone" data-slide="8">
                        <img src="/slide/slide_inserted.gif" alt="전단지 인쇄 서비스" class="slider-img">
                </div>
            </div>
            
            <!-- Slider Controls -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-3 z-10">
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition active" data-slide="0" aria-label="전단지"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="1" aria-label="스티커"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="2" aria-label="카다록"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="3" aria-label="NCR양식지"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="4" aria-label="포스터"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="5" aria-label="스티커2"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="6" aria-label="스티커3"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="7" aria-label="소개영상"></button>
            </div>
            
            <!-- Navigation Arrows -->
            <button class="slider-prev absolute left-2 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white text-lg transition" aria-label="이전 슬라이드">
                ‹
            </button>
            <button class="slider-next absolute right-2 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white text-lg transition" aria-label="다음 슬라이드">
                ›
            </button>
        </div>
    </section>

    <!-- 품목 카드 섹션 -->
    <section class="products-section">
        <div class="section-header">
        </div>
        <div class="products-grid">
            <!-- 1. 스티커 (네비 첫 번째) -->
            <div class="product-card" style="--card-gradient: #3b82f6">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/sticker_new/" style="color: inherit; text-decoration: none;">🏷️ 스티커/라벨</a></h3>
                    <p class="product-subtitle">맞춤형 스티커 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>방수 소재 가능</li>
                            <li>자유로운 형태</li>
                        </ul>
                        <a href="mlangprintauto/sticker_new/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/sticker_new_s.png" alt="스티커 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 2. 전단지 (네비 두 번째) -->
            <div class="product-card" style="--card-gradient: #10b981">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/inserted/" style="color: inherit; text-decoration: none;">📄 전단지/리플릿</a></h3>
                    <p class="product-subtitle">홍보용 전단지 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>고해상도 인쇄</li>
                            <li>빠른 제작</li>
                        </ul>
                        <a href="mlangprintauto/inserted/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/inserted_s.png" alt="전단지 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 3. 명함 (네비 세 번째) -->
            <div class="product-card" style="--card-gradient: #8b5cf6">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/namecard/" style="color: inherit; text-decoration: none;">📇 명함/쿠폰</a></h3>
                    <p class="product-subtitle">전문 명함 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>UV 코팅 가능</li>
                            <li>당일 제작 가능</li>
                        </ul>
                        <a href="mlangprintauto/namecard/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/namecard_s.png" alt="명함 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 4. 봉투 (네비 네 번째) -->
            <div class="product-card" style="--card-gradient: #e11d48">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/envelope/" style="color: inherit; text-decoration: none;">✉️ 봉투</a></h3>
                    <p class="product-subtitle">각종 봉투 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>창봉투 가능</li>
                            <li>대량 주문</li>
                        </ul>
                        <a href="mlangprintauto/envelope/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/envelop_s.png" alt="봉투 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 5. 카다록 (네비 다섯 번째) -->
            <div class="product-card" style="--card-gradient: #06b6d4">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/cadarok/" style="color: inherit; text-decoration: none;">📖 카다록</a></h3>
                    <p class="product-subtitle">제품 카탈로그 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>풀컬러 인쇄</li>
                            <li>전문 편집</li>
                        </ul>
                        <a href="mlangprintauto/cadarok/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/catalogue_s.png" alt="카다록 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 6. 포스터 (네비 여섯 번째) -->
            <div class="product-card" style="--card-gradient: #f97316">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/littleprint/" style="color: inherit; text-decoration: none;">🎨 포스터</a></h3>
                    <p class="product-subtitle">대형 포스터 인쇄</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>대형 사이즈</li>
                            <li>고화질 출력</li>
                        </ul>
                        <a href="mlangprintauto/littleprint/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/poster_s.png" alt="포스터 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 7. 양식지 (네비 일곱 번째) -->
            <div class="product-card" style="--card-gradient: #84cc16">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/ncrflambeau/" style="color: inherit; text-decoration: none;">📋 양식지</a></h3>
                    <p class="product-subtitle">NCR 양식지 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>2~4연 제작</li>
                            <li>무탄소 용지</li>
                        </ul>
                        <a href="mlangprintauto/ncrflambeau/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/ncr_s.png" alt="양식지 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 8. 상품권 (네비 여덟 번째) -->
            <div class="product-card" style="--card-gradient: #d946ef">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/merchandisebond/" style="color: inherit; text-decoration: none;">🎫 상품권</a></h3>
                    <p class="product-subtitle">쿠폰/상품권 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>위조 방지</li>
                            <li>번호 인쇄</li>
                        </ul>
                        <a href="mlangprintauto/merchandisebond/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/merchandise_s.png" alt="상품권 샘플">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 9. 자석스티커 (네비 아홉 번째) -->
            <div class="product-card" style="--card-gradient: #ef4444">
                <div class="product-header">
                    <h3 class="product-title"><a href="mlangprintauto/msticker/" style="color: inherit; text-decoration: none;">🧲 자석스티커</a></h3>
                    <p class="product-subtitle">마그네틱 스티커 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>강력한 자석</li>
                            <li>차량용 최적</li>
                        </ul>
                        <a href="mlangprintauto/msticker/" class="btn-product btn-primary">가격 보기</a>
                    </div>
                    <div class="product-content-right">
                        <div class="product-image">
                            <img src="/ImgFolder/gate_picto/m_sticker_s.png" alt="자석스티커 샘플">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 10. 배너 - 실내외게시대 -->
            <div class="product-card product-card-no-image" style="--card-gradient: #059669;">
                <div class="product-header">
                    <h3 class="product-title">🎪 배너</h3>
                    <p class="product-subtitle">실내외게시대</p>
                </div>
                <div class="product-body">
                    <div class="product-content-single">
                        <ul class="product-features">
                            <li>단면/양면게시대</li>
                            <li>미니게시대</li>
                        </ul>
                        <div class="product-action">
                            <button class="btn-product btn-primary" onclick="alert('별도견적 문의: 1688-2384')">별도견적</button>
                            <button class="btn-product btn-secondary" onclick="alert('문의전화: 1688-2384')">상세보기</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 11. 옥외스티커 - 탈색방지용스티커 -->
            <div class="product-card product-card-no-image" style="--card-gradient: #7c3aed;">
                <div class="product-header">
                    <h3 class="product-title">🌞 옥외스티커</h3>
                    <p class="product-subtitle">탈색방지용스티커</p>
                </div>
                <div class="product-body">
                    <div class="product-content-single">
                        <ul class="product-features">
                            <li>차량용스티커</li>
                            <li>대형스티커(1.4m폭 이하 길이는 자유)</li>
                        </ul>
                        <div class="product-action">
                            <button class="btn-product btn-primary" onclick="alert('별도견적 문의: 1688-2384')">별도견적</button>
                            <button class="btn-product btn-secondary" onclick="alert('문의전화: 1688-2384')">상세보기</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 12. 책자인쇄 - 무선제본/양장제본 -->
            <div class="product-card product-card-no-image" style="--card-gradient: #dc2626;">
                <div class="product-header">
                    <h3 class="product-title">📚 책자인쇄</h3>
                    <p class="product-subtitle">무선제본/양장제본</p>
                </div>
                <div class="product-body">
                    <div class="product-content-single">
                        <ul class="product-features">
                            <li>소량(디지털)인쇄</li>
                            <li>컬러인쇄 2도인쇄</li>
                        </ul>
                        <div class="product-action">
                            <button class="btn-product btn-primary" onclick="alert('별도견적 문의: 1688-2384')">별도견적</button>
                            <button class="btn-product btn-secondary" onclick="alert('문의전화: 1688-2384')">상세보기</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 실시간 견적 라이브 데모 섹션 -->
    <section class="quote-demo-section" style="padding:60px 20px;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1E4E79 100%);overflow:hidden;">
        <div style="max-width:1100px;margin:0 auto;display:flex;align-items:center;gap:60px;flex-wrap:wrap;justify-content:center;">
            
            <!-- 왼쪽: 견적 위젯 목업 -->
            <div class="quote-demo-widget" style="flex-shrink:0;">
                <div style="background:#fff;border-radius:12px;box-shadow:0 8px 40px rgba(0,0,0,.3);width:220px;overflow:hidden;transform:scale(1);transition:transform .3s;">
                    <!-- 헤더 -->
                    <div style="background:#1E4E79;padding:12px 16px;text-align:center;">
                        <div style="font-size:14px;font-weight:600;color:#fff;display:flex;align-items:center;justify-content:center;gap:6px;font-family:'Pretendard Variable',sans-serif;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#86efac;box-shadow:0 0 8px rgba(134,239,172,.6);animation:qd-pulse 2.5s infinite;display:inline-block;"></span>
                            실시간 견적받기
                        </div>
                    </div>
                    <!-- 가격 -->
                    <div style="padding:16px;text-align:center;border-bottom:1px solid #f1f5f9;">
                        <div style="font-family:'JetBrains Mono',monospace;font-size:28px;font-weight:700;color:#1e293b;line-height:1.3;">
                            <span class="quote-demo-price" data-target="165000">0</span><span style="font-size:15px;font-weight:500;color:#64748b;margin-left:2px;">원</span>
                        </div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:3px;">VAT 포함</div>
                    </div>
                    <!-- 스펙 -->
                    <div style="padding:10px 16px 6px;">
                        <div style="font-size:9px;font-weight:600;color:#94a3b8;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;display:flex;align-items:center;gap:4px;">
                            <span style="width:2px;height:8px;background:#3b82f6;border-radius:1px;display:inline-block;"></span>SPEC
                        </div>
                        <div class="quote-demo-spec" style="opacity:0;transform:translateY(5px);transition:all .4s ease .3s;">
                            <div style="display:flex;justify-content:space-between;line-height:2;"><span style="font-size:12px;color:#64748b;">용지</span><span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#1e293b;">아트지 150g</span></div>
                            <div style="display:flex;justify-content:space-between;line-height:2;"><span style="font-size:12px;color:#64748b;">인쇄</span><span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#1e293b;">4도/4도</span></div>
                            <div style="display:flex;justify-content:space-between;line-height:2;"><span style="font-size:12px;color:#64748b;">사이즈</span><span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#1e293b;">A4</span></div>
                            <div style="display:flex;justify-content:space-between;line-height:2;"><span style="font-size:12px;color:#64748b;">수량</span><span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#1e293b;">1,000매</span></div>
                        </div>
                        <div style="height:1px;background:#f1f5f9;margin:6px 0;"></div>
                        <div style="font-size:9px;font-weight:600;color:#94a3b8;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;display:flex;align-items:center;gap:4px;">
                            <span style="width:2px;height:8px;background:#3b82f6;border-radius:1px;display:inline-block;"></span>PRICING
                        </div>
                        <div class="quote-demo-pricing" style="opacity:0;transform:translateY(5px);transition:all .4s ease .6s;">
                            <div style="display:flex;justify-content:space-between;line-height:2;"><span style="font-size:12px;color:#64748b;">인쇄비</span><span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:#334155;">135,000</span></div>
                            <div style="display:flex;justify-content:space-between;line-height:2;"><span style="font-size:12px;color:#64748b;font-weight:600;">합계</span><span style="font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:600;color:#1e293b;">150,000</span></div>
                            <div style="display:flex;justify-content:space-between;line-height:2;"><span style="font-size:11px;color:#94a3b8;">부가세(10%)</span><span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#94a3b8;">15,000</span></div>
                        </div>
                    </div>
                    <!-- 버튼 -->
                    <div style="padding:10px 16px 14px;display:flex;gap:6px;">
                        <div style="flex:1;padding:8px 0;border-radius:6px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:12px;font-weight:600;text-align:center;font-family:'Pretendard Variable',sans-serif;">주문하기</div>
                        <div style="flex:1;padding:8px 0;border-radius:6px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:12px;font-weight:600;text-align:center;font-family:'Pretendard Variable',sans-serif;">견적인쇄</div>
                    </div>
                </div>
            </div>

            <!-- 오른쪽: 설명 텍스트 -->
            <div style="flex:1;min-width:280px;max-width:520px;">
                <div style="display:inline-block;padding:4px 12px;background:rgba(134,239,172,.15);border:1px solid rgba(134,239,172,.3);border-radius:20px;font-size:12px;font-weight:600;color:#86efac;margin-bottom:16px;font-family:'Pretendard Variable',sans-serif;">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#86efac;margin-right:6px;animation:qd-pulse 2.5s infinite;"></span>LIVE PRICING
                </div>
                <h2 style="font-size:32px;font-weight:800;color:#fff;line-height:1.35;margin:0 0 16px;font-family:'Pretendard Variable',sans-serif;">
                    옵션 선택만으로<br><span style="color:#60a5fa;">즉시 가격 확인</span>
                </h2>
                <p style="font-size:16px;color:#94a3b8;line-height:1.7;margin:0 0 28px;font-family:'Pretendard Variable',sans-serif;">
                    용지, 사이즈, 수량을 선택하면 <strong style="color:#cbd5e1;">실시간으로 정확한 견적</strong>이 표시됩니다. 
                    전화 문의 없이도 바로 가격을 비교하고 주문할 수 있습니다.
                </p>
                <div style="display:flex;gap:8px 20px;flex-wrap:wrap;margin-bottom:32px;">
                    <div style="display:flex;align-items:center;gap:8px;font-size:14px;color:#cbd5e1;font-family:'Pretendard Variable',sans-serif;">
                        <span style="color:#60a5fa;">✓</span> 9개 품목 실시간 견적
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:14px;color:#cbd5e1;font-family:'Pretendard Variable',sans-serif;">
                        <span style="color:#60a5fa;">✓</span> VAT 포함 정확한 가격
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:14px;color:#cbd5e1;font-family:'Pretendard Variable',sans-serif;">
                        <span style="color:#60a5fa;">✓</span> 옵션별 추가금 자동 계산
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:14px;color:#cbd5e1;font-family:'Pretendard Variable',sans-serif;">
                        <span style="color:#60a5fa;">✓</span> 견적서 이메일 발송
                    </div>
                </div>
                <a href="mlangprintauto/inserted/" style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:15px;font-weight:700;border-radius:10px;text-decoration:none;font-family:'Pretendard Variable',sans-serif;box-shadow:0 4px 15px rgba(59,130,246,.35);transition:all .2s;">
                    지금 견적 확인하기 <span style="font-size:18px;">→</span>
                </a>
            </div>
        </div>

        <style>
            @keyframes qd-pulse{0%,100%{opacity:1}50%{opacity:.4}}
            .quote-demo-section .quote-demo-widget:hover > div { transform: scale(1.03); }
            @media(max-width:768px){
                .quote-demo-section > div { flex-direction: column; gap: 36px !important; text-align: center; }
                .quote-demo-section h2 { font-size: 26px; }
                .quote-demo-section a { width: 100%; justify-content: center; box-sizing: border-box; }
            }
        </style>
        <script>
        (function(){
            var observed = false;
            var section = document.querySelector('.quote-demo-section');
            if (!section) return;
            
            function animateNumber(el, target, duration) {
                var start = 0, startTime = null;
                function step(ts) {
                    if (!startTime) startTime = ts;
                    var p = Math.min((ts - startTime) / duration, 1);
                    var ease = 1 - Math.pow(2, -10 * p);
                    var val = Math.round(start + (target - start) * ease);
                    el.textContent = val.toLocaleString();
                    if (p < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            }

            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && !observed) {
                        observed = true;
                        var priceEl = section.querySelector('.quote-demo-price');
                        if (priceEl) animateNumber(priceEl, parseInt(priceEl.dataset.target), 1200);
                        var spec = section.querySelector('.quote-demo-spec');
                        if (spec) { spec.style.opacity = '1'; spec.style.transform = 'translateY(0)'; }
                        var pricing = section.querySelector('.quote-demo-pricing');
                        if (pricing) { pricing.style.opacity = '1'; pricing.style.transform = 'translateY(0)'; }
                    }
                });
            }, { threshold: 0.3 });
            obs.observe(section);
        })();
        </script>
    </section>

    <!-- 강화된 특징 섹션 -->
    <section class="features-section">
        <div class="section-header">
            <h2 class="section-title">왜 두손기획인쇄인가요?</h2>
            <p class="section-subtitle">고객이 선택하는 이유가 있습니다</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">실시간 견적</h3>
                <p class="feature-description">복잡한 계산 없이 즉시 확인하는 정확한 가격으로 시간을 절약하세요</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎨</div>
                <h3 class="feature-title">전문 디자인</h3>
                <p class="feature-description">20년 경험의 디자이너가 제공하는 완성도 높은 전문 디자인 서비스</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3 class="feature-title">품질 보증</h3>
                <p class="feature-description">까다로운 품질 검사를 통과한 최고급 소재와 정밀한 인쇄 기술</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3 class="feature-title">신속 배송</h3>
                <p class="feature-description">전국 당일/익일 배송으로 급한 일정도 여유롭게 해결</p>
            </div>
        </div>
    </section>

    <!-- 프로세스 섹션 -->
    <section class="process-section">
        <div class="process-content">
            <div class="section-header">
                <h2 class="section-title" style="color: white;">간단한 주문 프로세스</h2>
                <p class="section-subtitle" style="color: #cbd5e1;">4단계로 완성되는 전문적인 인쇄 서비스</p>
            </div>
            <div class="process-grid">
                <div class="process-step">
                    <div class="process-number">1</div>
                    <h3 class="process-title">제품 선택</h3>
                    <p class="process-description">원하는 제품을 선택하고 옵션을 설정합니다</p>
                </div>
                <div class="process-step">
                    <div class="process-number">2</div>
                    <h3 class="process-title">파일 업로드</h3>
                    <p class="process-description">디자인 파일을 업로드하거나 디자인을 의뢰합니다</p>
                </div>
                <div class="process-step">
                    <div class="process-number">3</div>
                    <h3 class="process-title">검수 & 교정</h3>
                    <p class="process-description">전문 관리자가 검수 후 교정안을 확인합니다</p>
                </div>
                <div class="process-step">
                    <div class="process-number">4</div>
                    <h3 class="process-title">제작 & 배송</h3>
                    <p class="process-description">품질 검사 후 안전하게 포장하여 배송합니다</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 회사 소개 섹션 -->
    <section class="about-section">
        <div class="section-header">
            <h2 class="section-title">신뢰할 수 있는 인쇄 파트너</h2>
            <p class="section-subtitle">두손기획인쇄는 1998년부터 25년 이상 축적된 인쇄 전문성으로 기업과 개인 고객에게 최고 품질의 인쇄 서비스를 제공합니다.</p>
        </div>
        
        <div class="about-stats">
            <div class="stat-card">
                <div class="stat-number">25+</div>
                <div class="stat-label">년간 경험</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">10000+</div>
                <div class="stat-label">년간 주문</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">99%</div>
                <div class="stat-label">고객 만족도</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">11</div>
                <div class="stat-label">전문 제품군</div>
            </div>
        </div>
    </section>

    <!-- 연락 및 상담 섹션 -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>지금 바로 상담받으세요</h2>
            <p>전문 상담원이 최적의 인쇄 솔루션을 제안해드립니다</p>
            <a href="tel:02-2632-1830" class="btn-cta">
                📞 02-2632-1830
            </a>
        </div>
    </section>

    <!-- JavaScript 로드 -->
    <script src="assets/js/layout.js"></script>
    
    <script>
        // Hero Slider functionality - Infinite Loop
        let currentIndex = 1;
        const sliderTrack = document.getElementById('sliderTrack');
        const allSlides = document.querySelectorAll('.slider-slide');
        const slides = document.querySelectorAll('.slider-slide:not(.clone)');
        const dots = document.querySelectorAll('.slider-dot');
        
        const totalSlides = slides.length;
        const totalWithClones = allSlides.length;
        let isTransitioning = false;
        let autoPlayTimer = null;
        let videoPlaying = false;

        // [FIX] 동적으로 트랙과 슬라이드 너비 설정 (CSS 하드코딩 무시)
        sliderTrack.style.width = `${totalWithClones * 100}%`;
        allSlides.forEach(slide => {
            slide.style.width = `${100 / totalWithClones}%`;
        });

        // 슬라이드와 점의 개수가 맞지 않으면 점을 슬라이드 개수에 맞춰 다시 그림
        if (dots.length !== totalSlides) {
            const dotsContainer = document.querySelector('.slider-dots-container'); // 부모 컨테이너 선택
            if (dotsContainer) {
                dotsContainer.innerHTML = ''; // 기존 점들 삭제
                for (let i = 0; i < totalSlides; i++) {
                    const button = document.createElement('button');
                    button.classList.add('slider-dot', 'w-3', 'h-3', 'rounded-full', 'bg-white/60', 'hover:bg-white', 'transition');
                    if (i === 0) button.classList.add('active');
                    button.dataset.slide = i;
                    button.setAttribute('aria-label', `슬라이드 ${i + 1}`);
                    dotsContainer.appendChild(button);
                }
                // 새로운 점들에 이벤트 리스너 다시 할당
                document.querySelectorAll('.slider-dot').forEach((dot, index) => {
                    dot.addEventListener('click', () => {
                        if (isTransitioning) return;
                        isTransitioning = true;
                        moveToSlide(index + 1);
                    });
                });
            }
        }

        function moveToSlide(index, withTransition = true) {
            if (!withTransition) {
                sliderTrack.classList.add('no-transition');
            } else {
                sliderTrack.classList.remove('no-transition');
            }

            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                sliderTrack.style.transform = `translateX(${-index * 100}vw)`;
            } else {
                sliderTrack.style.transform = `translateX(${-index * (100 / totalWithClones)}%)`;
            }

            currentIndex = index;

            const realIndex = getRealIndex(index);
            document.querySelectorAll('.slider-dot').forEach(dot => dot.classList.remove('active'));
            if (realIndex >= 0 && realIndex < totalSlides) {
                const activeDot = document.querySelector(`.slider-dot[data-slide="${realIndex}"]`);
                if(activeDot) activeDot.classList.add('active');
            }

            // 비디오 슬라이드를 벗어나면 영상 정지 + 포스터 복원
            if (realIndex !== (totalSlides - 1) && videoPlaying) { // 비디오가 마지막 슬라이드
                resetSliderVideo();
            }
        }

        function getRealIndex(index) {
            if (index === 0) return totalSlides - 1;
            if (index === totalWithClones - 1) return 0;
            return index - 1;
        }

        function nextSlide() {
            if (isTransitioning || videoPlaying) return;
            isTransitioning = true;
            moveToSlide(currentIndex + 1);
        }

        function prevSlide() {
            if (isTransitioning || videoPlaying) return;
            isTransitioning = true;
            moveToSlide(currentIndex - 1);
        }

        sliderTrack.addEventListener('transitionend', () => {
            isTransitioning = false;
            if (currentIndex === totalWithClones - 1) {
                moveToSlide(1, false);
            }
            if (currentIndex === 0) {
                moveToSlide(totalSlides, false);
            }
        });

        document.querySelector('.slider-next').addEventListener('click', nextSlide);
        document.querySelector('.slider-prev').addEventListener('click', prevSlide);

        document.querySelectorAll('.slider-dot').forEach((dot, index) => {
            dot.addEventListener('click', () => {
                if (isTransitioning) return;
                isTransitioning = true;
                moveToSlide(index + 1);
            });
        });

        moveToSlide(1, false);

        function startAutoPlay() {
            stopAutoPlay();
            autoPlayTimer = setInterval(nextSlide, 4000);
        }
        function stopAutoPlay() {
            if (autoPlayTimer) { clearInterval(autoPlayTimer); autoPlayTimer = null; }
        }
        startAutoPlay();

        // --- Video slide controls ---
        const sliderVideo = document.getElementById('sliderVideo');
        const videoPoster = document.getElementById('videoPoster');
        const videoPlayBtn = document.getElementById('videoPlayBtn');

        function toggleSliderVideo() {
            if (!sliderVideo) return;
            if (sliderVideo.paused) {
                videoPoster.style.display = 'none';
                sliderVideo.style.display = 'block';
                videoPlayBtn.classList.add('hidden');
                sliderVideo.play();
                videoPlaying = true;
                stopAutoPlay();
            } else {
                sliderVideo.pause();
                videoPlayBtn.classList.remove('hidden');
                videoPlaying = false;
                startAutoPlay();
            }
        }

        function resetSliderVideo() {
            if (!sliderVideo) return;
            sliderVideo.pause();
            sliderVideo.currentTime = 0;
            sliderVideo.style.display = 'none';
            videoPoster.style.display = '';
            videoPlayBtn.classList.remove('hidden');
            videoPlaying = false;
            startAutoPlay();
        }

        if (sliderVideo) {
            sliderVideo.addEventListener('ended', function() {
                resetSliderVideo();
                setTimeout(nextSlide, 500);
            });
        }

        // 현재 연도 설정
        document.addEventListener('DOMContentLoaded', function() {
            const yearElement = document.getElementById('currentYear');
            if (yearElement) {
                yearElement.textContent = new Date().getFullYear();
            }
        });
    </script>




    <!-- 구조화된 데이터 (Schema.org) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "두손기획인쇄",
        "image": "https://dsp114.com/ImgFolder/dusonlogo1.png",
        "description": "스티커, 전단지, 명함, 봉투, 카다록, 포스터, 상품권, 양식지, 자석스티커 인쇄 전문. 공장직영 신속제작",
        "@id": "https://dsp114.com",
        "url": "https://dsp114.com",
        "telephone": "",
        "priceRange": "₩₩",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "KR"
        },
        "geo": {
            "@type": "GeoCoordinates"
        },
        "sameAs": [
            "https://dsp114.com"
        ],
        "offers": [
            {
                "@type": "Offer",
                "name": "스티커 인쇄",
                "description": "다양한 크기와 재질의 스티커 인쇄 서비스"
            },
            {
                "@type": "Offer",
                "name": "전단지 인쇄",
                "description": "전단지, 리플렛 인쇄 및 접지 서비스"
            },
            {
                "@type": "Offer",
                "name": "명함 인쇄",
                "description": "고급 명함 인쇄 및 코팅 서비스"
            },
            {
                "@type": "Offer",
                "name": "봉투 인쇄",
                "description": "각종 봉투 인쇄 및 제작"
            },
            {
                "@type": "Offer",
                "name": "카다록 인쇄",
                "description": "카탈로그, 브로슈어 제작"
            },
            {
                "@type": "Offer",
                "name": "포스터 인쇄",
                "description": "대형 포스터 및 배너 인쇄"
            },
            {
                "@type": "Offer",
                "name": "상품권 제작",
                "description": "상품권 디자인 및 제작"
            },
            {
                "@type": "Offer",
                "name": "양식지 인쇄",
                "description": "NCR양식지, 복사용지 인쇄"
            },
            {
                "@type": "Offer",
                "name": "자석스티커 제작",
                "description": "자석 스티커 제작 서비스"
            }
        ]
    }
    </script>

<?php include 'includes/popup_layer.php'; ?>

<?php
// 공통 푸터 포함
include 'includes/footer.php';
?>