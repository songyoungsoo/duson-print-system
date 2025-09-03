<?php
// DB 연결 (보안 상수 임시 비활성화)
include 'db.php';
$connect = $db; // auth.php expects $connect variable

// Debug: Check if database connection is valid
if (!$connect) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Include the auth system which handles login/logout processing
include 'includes/auth.php';

$page_title = '두손기획인쇄 - 명함 스티커 전단지 봉투 카다록 포스터 라벨 인쇄 전문';
$current_page = 'home';

// 공통 헤더 포함
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획인쇄 - 전문 인쇄 서비스</title>
    <link rel="stylesheet" href="css/style250801.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .slider-slide {
            transition: opacity 1000ms ease-in-out;
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
    </style>
</head>
<body>
    <!-- Hero Slider Section -->
    <section class="relative overflow-hidden" style="max-width: 1200px; margin: 0 auto;">
        <div id="hero-slider" class="relative" style="height: 400px;">
            <!-- Slider Content -->
            <div class="slider-container relative w-full h-full">
                <!-- Slide 1: 전단지 -->
                <div class="slider-slide absolute inset-0 opacity-100 transition-opacity duration-1000" data-slide="0">
                    <img src="/slide/slide_inserted.gif" alt="전단지 인쇄 서비스" class="w-full h-full object-cover">
                </div>
                
                <!-- Slide 2: 스티커 -->
                <div class="slider-slide absolute inset-0 opacity-0 transition-opacity duration-1000" data-slide="1">
                    <img src="/slide/slide__Sticker.gif" alt="스티커 인쇄 서비스" class="w-full h-full object-cover">
                </div>
                
                <!-- Slide 3: 카다록 -->
                <div class="slider-slide absolute inset-0 opacity-0 transition-opacity duration-1000" data-slide="2">
                    <img src="/slide/slide_cadarok.gif" alt="카다록 인쇄 서비스" class="w-full h-full object-cover">
                </div>
                
                <!-- Slide 4: NCR 양식지 -->
                <div class="slider-slide absolute inset-0 opacity-0 transition-opacity duration-1000" data-slide="3">
                    <img src="/slide/slide_Ncr.gif" alt="NCR 양식지 인쇄 서비스" class="w-full h-full object-cover">
                </div>
                
                <!-- Slide 5: 포스터 -->
                <div class="slider-slide absolute inset-0 opacity-0 transition-opacity duration-1000" data-slide="4">
                    <img src="/slide/slide__poster.gif" alt="포스터 인쇄 서비스" class="w-full h-full object-cover">
                </div>
                
                <!-- Slide 6: 스티커 2 -->
                <div class="slider-slide absolute inset-0 opacity-0 transition-opacity duration-1000" data-slide="5">
                    <img src="/slide/slide__Sticker_2.gif" alt="스티커 제작 서비스 2" class="w-full h-full object-cover">
                </div>
                
                <!-- Slide 7: 스티커 3 -->
                <div class="slider-slide absolute inset-0 opacity-0 transition-opacity duration-1000" data-slide="6">
                    <img src="/slide/slide__Sticker_3.gif" alt="스티커 제작 서비스 3" class="w-full h-full object-cover">
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
                        <li>투명/홀로그램</li>
                        <li>대량 할인</li>
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
                        <li>다양한 용지 선택</li>
                        <li>고해상도 인쇄</li>
                        <li>빠른 제작</li>
                        <li>합리적 가격</li>
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
                        <li>고급 용지 선택</li>
                        <li>UV 코팅 가능</li>
                        <li>특수 효과</li>
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
                        <li>표준/맞춤 사이즈</li>
                        <li>창봉투 가능</li>
                        <li>로고 인쇄</li>
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
                        <li>다양한 제본</li>
                        <li>고급 용지</li>
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
                        <li>내구성 소재</li>
                        <li>실내외 사용</li>
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
                        <li>각종 양식</li>
                        <li>번호 인쇄</li>
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
                        <li>다양한 디자인</li>
                        <li>위조 방지</li>
                        <li>번호 인쇄</li>
                        <li>특수 용지</li>
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
                        <li>실내외 가능</li>
                        <li>차량용 최적</li>
                        <li>재사용 가능</li>
                    </ul>
                    <div class="product-action">
                        <a href="mlangprintauto/msticker/" class="btn-product btn-primary">주문하기</a>
                        <a href="mlangprintauto/msticker/" class="btn-product btn-secondary">상세보기</a>
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
        const slides = document.querySelectorAll('.slider-slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;
        
        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => slide.style.opacity = '0');
            dots.forEach(dot => dot.classList.remove('active'));
            
            // Show current slide
            slides[index].style.opacity = '1';
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

<?php
// 공통 푸터 포함
include 'includes/footer.php';
?>