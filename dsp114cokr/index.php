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
$page_title = '두손기획인쇄 - 스티커 전단지 명함 봉투 카다록 포스터 상품권 양식지 자석스티커 인쇄 전문';
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
    <meta name="description" content="두손기획인쇄 - 스티커, 전단지, 명함, 봉투, 카다록, 포스터, 상품권, 양식지, 자석스티커 등 모든 인쇄물 온라인 견적 및 주문. 공장직영 신속제작, 합리적인 가격으로 기획에서 인쇄까지 원스톱 서비스">
    <meta name="keywords" content="스티커인쇄, 전단지인쇄, 명함인쇄, 봉투인쇄, 카다록인쇄, 포스터인쇄, 상품권제작, 양식지인쇄, 자석스티커, 온라인견적, 인쇄전문, 두손기획">
    <meta name="author" content="두손기획인쇄">
    <link rel="canonical" href="https://www.dsp1830.shop/">

    <!-- Open Graph (카카오톡, 페이스북 공유용) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="두손기획인쇄 - 스티커 전단지 명함 봉투 카다록 포스터 상품권 양식지 자석스티커 인쇄 전문">
    <meta property="og:description" content="기획에서 인쇄까지 원스톱 서비스. 스티커, 전단지, 명함 등 모든 인쇄물 온라인 견적">
    <meta property="og:image" content="https://dsp114.com/ImgFolder/og-image.png">
    <meta property="og:url" content="https://www.dsp1830.shop/">
    <meta property="og:site_name" content="두손기획인쇄">
    <meta property="og:locale" content="ko_KR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="두손기획인쇄 - 스티커 전단지 명함 인쇄 전문">
    <meta name="twitter:description" content="기획에서 인쇄까지 원스톱 서비스. 10가지 인쇄물 온라인 견적">
    <meta name="twitter:image" content="https://www.dsp1830.shop/ImgFolder/dusonlogo1.png">

    <!-- 세션 ID 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars($session_id); ?>">

    <!-- 브랜드 디자인 시스템 (최우선 로드) -->
    <link rel="stylesheet" href="css/brand-design-system.css?v=<?php echo time(); ?>">

    <!-- 홈페이지 전용 CSS -->
    <link rel="stylesheet" href="css/product-layout.css">
    <link rel="stylesheet" href="css/style250801.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/layout.css?v=<?php echo time(); ?>">

    <!-- 브랜드 폰트 - Pretendard & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

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
            width: 900%; /* 9 slides (7 + 2 clones) * 100% */
            height: 100%;
            transition: transform 1000ms ease-in-out;
        }

        .slider-track.no-transition {
            transition: none;
        }

        .slider-slide {
            width: 11.1111%; /* 100% / 9 slides */
            height: 100%;
            flex-shrink: 0;
            position: relative;
        }

        .slider-slide img,
        .slider-slide .slider-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            object-position: center center;
        }

        .slider-dot.active {
            background: white !important;
            transform: scale(1.2);
        }

        /* 데스크톱 슬라이더 기본 높이 */
        #hero-slider {
            height: 300px;
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
                width: 900vw;
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
                        <img src="/slide/slide__Sticker_3.gif" alt="스티커 제작 서비스 3" class="slider-img">
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

                    <!-- Clone of first slide (for infinite loop) -->
                    <div class="slider-slide clone" data-slide="7">
                        <img src="/slide/slide_inserted.gif" alt="전단지 인쇄 서비스" class="slider-img">
                    </div>
                </div>
            </div>
            
            <!-- Slider Controls -->
            <div class="absolute bottom-16 left-1/2 transform -translate-x-1/2 flex gap-3 z-10">
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition active" data-slide="0" aria-label="첫 번째 슬라이드로 이동"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="1" aria-label="두 번째 슬라이드로 이동"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="2" aria-label="세 번째 슬라이드로 이동"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="3" aria-label="네 번째 슬라이드로 이동"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="4" aria-label="다섯 번째 슬라이드로 이동"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="5" aria-label="여섯 번째 슬라이드로 이동"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/60 hover:bg-white transition" data-slide="6" aria-label="일곱 번째 슬라이드로 이동"></button>
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
                    <h3 class="product-title"><a href="mlangprintauto/sticker_new/" style="color: inherit; text-decoration: none;">🏷️ 스티커</a></h3>
                    <p class="product-subtitle">맞춤형 스티커 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>방수 소재 가능</li>
                            <li>자유로운 형태</li>
                        </ul>
                        <a href="mlangprintauto/sticker_new/" class="btn-product btn-primary">주문하기</a>
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
                        <a href="mlangprintauto/inserted/" class="btn-product btn-primary">주문하기</a>
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
                    <h3 class="product-title"><a href="mlangprintauto/namecard/" style="color: inherit; text-decoration: none;">📇 명함</a></h3>
                    <p class="product-subtitle">전문 명함 제작</p>
                </div>
                <div class="product-body">
                    <div class="product-content-left">
                        <ul class="product-features">
                            <li>UV 코팅 가능</li>
                            <li>당일 제작 가능</li>
                        </ul>
                        <a href="mlangprintauto/namecard/" class="btn-product btn-primary">주문하기</a>
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
                        <a href="mlangprintauto/envelope/" class="btn-product btn-primary">주문하기</a>
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
                        <a href="mlangprintauto/cadarok/" class="btn-product btn-primary">주문하기</a>
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
                        <a href="mlangprintauto/littleprint/" class="btn-product btn-primary">주문하기</a>
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
                        <a href="mlangprintauto/ncrflambeau/" class="btn-product btn-primary">주문하기</a>
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
                        <a href="mlangprintauto/merchandisebond/" class="btn-product btn-primary">주문하기</a>
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
                        <a href="mlangprintauto/msticker/" class="btn-product btn-primary">주문하기</a>
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
        let currentIndex = 1; // Start at first real slide (index 1, after clone)
        const sliderTrack = document.getElementById('sliderTrack');
        const slides = document.querySelectorAll('.slider-slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = 7; // Real slides count
        const totalWithClones = slides.length; // 9 (7 + 2 clones)
        let isTransitioning = false;

        function moveToSlide(index, withTransition = true) {
            if (!withTransition) {
                sliderTrack.classList.add('no-transition');
            } else {
                sliderTrack.classList.remove('no-transition');
            }

            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                const translateX = -index * 100;
                sliderTrack.style.transform = `translateX(${translateX}vw)`;
            } else {
                const translateX = -index * (100 / totalWithClones);
                sliderTrack.style.transform = `translateX(${translateX}%)`;
            }

            currentIndex = index;

            // Update dots (map index to real slide 0-6)
            const realIndex = getRealIndex(index);
            dots.forEach(dot => dot.classList.remove('active'));
            if (realIndex >= 0 && realIndex < totalSlides) {
                dots[realIndex].classList.add('active');
            }
        }

        function getRealIndex(index) {
            // index 0 = clone of last, index 1-7 = real slides, index 8 = clone of first
            if (index === 0) return totalSlides - 1;
            if (index === totalWithClones - 1) return 0;
            return index - 1;
        }

        function nextSlide() {
            if (isTransitioning) return;
            isTransitioning = true;
            moveToSlide(currentIndex + 1);
        }

        function prevSlide() {
            if (isTransitioning) return;
            isTransitioning = true;
            moveToSlide(currentIndex - 1);
        }

        // Handle transition end for infinite loop
        sliderTrack.addEventListener('transitionend', () => {
            isTransitioning = false;

            // If at clone of first slide (index 8), jump to real first (index 1)
            if (currentIndex === totalWithClones - 1) {
                moveToSlide(1, false);
            }
            // If at clone of last slide (index 0), jump to real last (index 7)
            if (currentIndex === 0) {
                moveToSlide(totalSlides, false);
            }
        });

        // Event listeners for slider controls
        document.querySelector('.slider-next').addEventListener('click', nextSlide);
        document.querySelector('.slider-prev').addEventListener('click', prevSlide);

        // Event listeners for dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                if (isTransitioning) return;
                isTransitioning = true;
                moveToSlide(index + 1); // +1 because of leading clone
            });
        });

        // Initialize position (start at first real slide)
        moveToSlide(1, false);

        // Auto-play slider
        setInterval(nextSlide, 4000);

        // 현재 연도 설정
        document.addEventListener('DOMContentLoaded', function() {
            const yearElement = document.getElementById('currentYear');
            if (yearElement) {
                yearElement.textContent = new Date().getFullYear();
            }
        });
    </script>


    <!-- KB 에스크로 스크립트 -->
    <script>
    function onPopKBAuthMark() {
        window.open('','KB_AUTHMARK','height=604, width=648, status=yes, toolbar=no, menubar=no, location=no');
        document.KB_AUTHMARK_FORM.action='http://escrow1.kbstar.com/quics';
        document.KB_AUTHMARK_FORM.target='KB_AUTHMARK';
        document.KB_AUTHMARK_FORM.submit();
    }

    function WEBSILDESIGNWINDOW(url, width, height, scrollbars) {
        window.open(url, 'WEBSILDESIGN', 'width=' + width + ',height=' + height + ',scrollbars=' + scrollbars);
    }
    </script>

    <form name="KB_AUTHMARK_FORM" method="GET" style="display: none;">
        <input type="HIDDEN" name="page" value="B009111">
        <input type="HIDDEN" name="cc" value="b010807:b008491">
        <input type="HIDDEN" name="mHValue" value="eb30fbb0bc1da7fdcaf800c0bceebbff201111241043905">
    </form>

    <!-- 구조화된 데이터 (Schema.org) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "두손기획인쇄",
        "image": "https://www.dsp1830.shop/ImgFolder/dusonlogo1.png",
        "description": "스티커, 전단지, 명함, 봉투, 카다록, 포스터, 상품권, 양식지, 자석스티커 인쇄 전문. 공장직영 신속제작",
        "@id": "https://www.dsp1830.shop",
        "url": "https://www.dsp1830.shop",
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
            "https://www.dsp1830.shop"
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

<?php
// 공통 푸터 포함
include 'includes/footer.php';
?>