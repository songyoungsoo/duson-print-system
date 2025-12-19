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
    <meta property="og:image" content="https://www.dsp1830.shop/ImgFolder/dusonlogo1.png">
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
        .slider-container {
            position: relative;
            overflow: hidden;
        }

        .slider-track {
            display: flex;
            width: 700%; /* 7 slides * 100% */
            transition: transform 1000ms ease-in-out;
        }

        .slider-slide {
            width: 14.28571%; /* 100% / 7 slides */
            flex-shrink: 0;
            position: relative;
        }

        .slider-slide img {
            transform: translateY(-25%);
            object-fit: cover;
        }

        .slider-dot.active {
            background: white !important;
            transform: scale(1.2);
        }

        @media (max-width: 768px) {
            .slider-prev, .slider-next {
                display: none;
            }
        }
/* ✅ 이 코드로 교체해서 붙여넣으세요 */

/* 1. 그리드 전체 레이아웃 */
.products-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important; /* PC: 3열 */
    gap: 24px !important; /* 간격을 시원하게 */
    max-width: 1200px !important;
    margin: 0 auto !important;
    padding: 40px 20px !important;
}

/* 2. 제품 카드 (흰색 배경 + 얇은 테두리) */
.product-card {
    background: #ffffff !important; 
    border: 1px solid #e5e7eb !important; /* 연한 회색 테두리 */
    border-radius: 12px !important;
    box-shadow: none !important; /* 평소엔 그림자 없음 */
    transition: all 0.3s ease !important;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 280px !important; /* 카드 높이 확보 */
}

/* 3. 마우스 올렸을 때 (살짝 뜨면서 파란 테두리) */
.product-card:hover {
    transform: translateY(-5px);
    border-color: #3b82f6 !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
}

/* 4. 헤더 영역 (아이콘/제목) */
.product-header {
    background: #f9fafb !important; /* 아주 연한 회색 배경 */
    padding: 20px !important;
    border-bottom: 1px solid #f3f4f6;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 8px !important;
}

/* 5. 텍스트 스타일 */
.product-title, .product-title a {
    color: #111827 !important; /* 진한 검정 */
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
}

.product-subtitle {
    color: #6b7280 !important; /* 회색 설명 */
    font-size: 0.9rem !important;
    font-weight: 400 !important;
    margin: 0 !important;
}

/* 6. 본문 및 버튼 영역 */
.product-body {
    padding: 20px !important;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-features {
    margin-bottom: 20px !important;
    padding-left: 0 !important;
    list-style: none !important;
}

.product-features li {
    font-size: 0.9rem !important;
    color: #4b5563 !important;
    margin-bottom: 6px !important;
    display: flex;
    align-items: center;
}

.product-features li::before {
    content: "•";
    color: #d1d5db;
    margin-right: 8px;
}

/* 7. 버튼 디자인 */
.product-action {
    display: flex !important;
    gap: 10px !important;
    margin-top: auto !important; /* 버튼을 항상 하단으로 */
}

.btn-product {
    flex: 1;
    text-align: center;
    padding: 10px 0 !important;
    font-size: 0.9rem !important;
    border-radius: 6px !important;
    cursor: pointer;
    font-weight: 600 !important;
}

.btn-primary {
    background-color: #3b82f6 !important;
    color: white !important;
    border: none !important;
}

.btn-secondary {
    background-color: white !important;
    color: #4b5563 !important;
    border: 1px solid #d1d5db !important;
}

.btn-secondary:hover {
    background-color: #f3f4f6 !important;
}

/* 8. 모바일 대응 */
@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 15px !important;
        padding: 20px 15px !important;
    }
}
        /* .products-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            grid-template-rows: repeat(4, 1fr) !important;
            gap: 20px !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
            padding: 20px !important;
        }

        .product-card {
            height: auto !important;
            min-height: 180px !important;
        }

        .product-header {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin-bottom: 10px !important;
        }

        .product-title {
            font-size: 1rem !important;
            margin: 0 !important;
            white-space: nowrap !important;
        }

        .product-subtitle {
            font-size: 0.75rem !important;
            color: #6b7280 !important;
            margin: 0 !important;
            white-space: nowrap !important;
        }

        .product-features {
            margin-bottom: 10px !important;
        }

        .product-features li {
            font-size: 0.8rem !important;
            padding: 2px 0 !important;
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                grid-template-rows: auto !important;
                gap: 15px !important;
            }
        }

        .products-section {
            margin-top: -20px !important;
            padding-top: 20px !important;
        }

        .section-header {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        } */
    </style>

    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="css/common-styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include "includes/header-ui.php"; ?>
    <?php if (file_exists('includes/nav.php')) include "includes/nav.php"; ?>

    <!-- Hero Slider Section -->
    <section class="relative overflow-hidden" style="max-width: 1200px; margin: 0 auto;">
        <div id="hero-slider" class="relative" style="height: 250px;">
            <!-- Slider Content -->
            <div class="slider-container relative w-full h-full">
                <div class="slider-track" id="sliderTrack">
                    <!-- Slide 1: 전단지 -->
                    <div class="slider-slide" data-slide="0">
                        <img src="/slide/slide_inserted.gif" alt="전단지 인쇄 서비스" class="w-full h-full object-cover">
                    </div>

                    <!-- Slide 2: 스티커 -->
                    <div class="slider-slide" data-slide="1">
                        <img src="/slide/slide__Sticker.gif" alt="스티커 인쇄 서비스" class="w-full h-full object-cover">
                    </div>

                    <!-- Slide 3: 카다록 -->
                    <div class="slider-slide" data-slide="2">
                        <img src="/slide/slide_cadarok.gif" alt="카다록 인쇄 서비스" class="w-full h-full object-cover">
                    </div>

                    <!-- Slide 4: NCR 양식지 -->
                    <div class="slider-slide" data-slide="3">
                        <img src="/slide/slide_Ncr.gif" alt="NCR 양식지 인쇄 서비스" class="w-full h-full object-cover">
                    </div>

                    <!-- Slide 5: 포스터 -->
                    <div class="slider-slide" data-slide="4">
                        <img src="/slide/slide__poster.gif" alt="포스터 인쇄 서비스" class="w-full h-full object-cover">
                    </div>

                    <!-- Slide 6: 스티커 2 -->
                    <div class="slider-slide" data-slide="5">
                        <img src="/slide/slide__Sticker_2.gif" alt="스티커 제작 서비스 2" class="w-full h-full object-cover">
                    </div>

                    <!-- Slide 7: 스티커 3 -->
                    <div class="slider-slide" data-slide="6">
                        <img src="/slide/slide__Sticker_3.gif" alt="스티커 제작 서비스 3" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
            
            <!-- Slider Controls -->
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex gap-3 z-10">
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
                    <ul class="product-features">
                        <li>방수 소재 가능</li>
                        <li>자유로운 형태</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/sticker_new/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/sticker_new/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>고해상도 인쇄</li>
                        <li>빠른 제작</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/inserted/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/inserted/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>UV 코팅 가능</li>
                        <li>당일 제작 가능</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/namecard/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/namecard/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>창봉투 가능</li>
                        <li>대량 주문</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/envelope/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/envelope/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>풀컬러 인쇄</li>
                        <li>전문 편집</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/cadarok/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/cadarok/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>대형 사이즈</li>
                        <li>고화질 출력</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/littleprint/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/littleprint/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>2~4연 제작</li>
                        <li>무탄소 용지</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/ncrflambeau/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/ncrflambeau/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>위조 방지</li>
                        <li>번호 인쇄</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/merchandisebond/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/merchandisebond/" class="btn-product btn-secondary">상세보기</a>
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
                    <ul class="product-features">
                        <li>강력한 자석</li>
                        <li>차량용 최적</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/msticker/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/msticker/" class="btn-product btn-secondary">상세보기</a>
                    </div>
                </div>
            </div>

            <!-- 10. 배너 - 실내외게시대 -->
            <div class="product-card" style="--card-gradient: #059669;">
                <div class="product-header">
                    <h3 class="product-title">🎪 배너</h3>
                    <p class="product-subtitle">실내외게시대</p>
                </div>
                <div class="product-body">
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

            <!-- 11. 옥외스티커 - 탈색방지용스티커 -->
            <div class="product-card" style="--card-gradient: #7c3aed;">
                <div class="product-header">
                    <h3 class="product-title">🌞 옥외스티커</h3>
                    <p class="product-subtitle">탈색방지용스티커</p>
                </div>
                <div class="product-body">
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

            <!-- 12. 책자인쇄 - 무선제본/양장제본 -->
            <div class="product-card" style="--card-gradient: #dc2626;">
                <div class="product-header">
                    <h3 class="product-title">📚 책자인쇄</h3>
                    <p class="product-subtitle">무선제본/양장제본</p>
                </div>
                <div class="product-body">
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
        // Hero Slider functionality
        let currentSlide = 0;
        const sliderTrack = document.getElementById('sliderTrack');
        const slides = document.querySelectorAll('.slider-slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            // 우측에서 좌측으로 슬라이드 (음수 translate로 좌측 이동)
            const translateX = -index * (100 / totalSlides);
            sliderTrack.style.transform = `translateX(${translateX}%)`;

            // 도트 상태 업데이트
            dots.forEach(dot => dot.classList.remove('active'));
            dots[index].classList.add('active');

            currentSlide = index;
        }

        function nextSlide() {
            const next = (currentSlide + 1) % totalSlides;
            showSlide(next);
        }

        function prevSlide() {
            const prev = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(prev);
        }

        // Event listeners for slider controls
        document.querySelector('.slider-next').addEventListener('click', nextSlide);
        document.querySelector('.slider-prev').addEventListener('click', prevSlide);

        // Event listeners for dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });

        // Auto-play slider (우측에서 좌측으로 자동 슬라이딩)
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