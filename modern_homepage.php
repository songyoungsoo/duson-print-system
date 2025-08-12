<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획인쇄 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
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
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .checkboard-btn {
            background-color: #ff6b35 !important;
            color: white !important;
            padding: 0.7rem 1.5rem !important;
            border-radius: 25px !important;
            font-weight: 600;
        }
        
        /* Hero Section with Slider */
        .hero-section {
            position: relative;
            height: 500px;
            overflow: hidden;
        }
        
        .hero-slider {
            width: 100%;
            height: 100%;
        }
        
        .hero-slide {
            position: relative;
            height: 500px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
        }
        
        .slide-content {
            text-align: center;
            color: white;
            z-index: 2;
            position: relative;
        }
        
        .slide-content h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .slide-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .cta-button {
            display: inline-block;
            background: #ff6b35;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,53,0.3);
        }
        
        /* Products Section */
        .products-section {
            padding: 5rem 0;
            background: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 4rem;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .card-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(102,126,234,0.8), rgba(118,75,162,0.8));
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-card:hover .card-overlay {
            opacity: 1;
        }
        
        .order-btn {
            background: white;
            color: #667eea;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .order-btn {
            transform: translateY(0);
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .card-description {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        /* Message Section */
        .message-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }
        
        .message-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .message-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .contact-info {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 3rem;
        }
        
        .contact-item {
            text-align: center;
        }
        
        .contact-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #ffd700;
        }
        
        .contact-item p {
            font-size: 1rem;
            margin-bottom: 0;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 3rem 0 2rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            margin-bottom: 1rem;
            color: #ffd700;
        }
        
        .footer-section p, .footer-section a {
            color: #bdc3c7;
            line-height: 1.8;
            text-decoration: none;
        }
        
        .footer-section a:hover {
            color: #ffd700;
        }
        
        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 2rem;
            text-align: center;
            color: #95a5a6;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-menu {
                flex-direction: column;
                gap: 1rem;
            }
            
            .slide-content h2 {
                font-size: 2rem;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 1rem;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">두손기획인쇄</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="/">홈</a></li>
                    <li><a href="/sub/info.php">회사소개</a></li>
                    <li><a href="/sub/leaflet.php">포트폴리오</a></li>
                    <li><a href="/sub/estimate_auto.php">자동견적</a></li>
                    <li><a href="/sub/checkboard.php" class="checkboard-btn">교정보기</a></li>
                    <li><a href="/bbs/qna.php">고객문의</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Slider -->
    <section class="hero-section">
        <div class="hero-slider swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide hero-slide" style="background-image: url('/img/slide-1.jpg')">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h2>프리미엄 스티커 제작</h2>
                        <p>다양한 재질과 형태로 맞춤 제작해드립니다</p>
                        <a href="/MlangPrintAuto/shop/view_modern.php" class="cta-button">주문하기</a>
                    </div>
                </div>
                <div class="swiper-slide hero-slide" style="background-image: url('/img/slide-2.jpg')">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h2>전단지 · 리플렛 제작</h2>
                        <p>효과적인 홍보를 위한 최고의 품질을 제공합니다</p>
                        <a href="/MlangPrintAuto/inserted/index.php" class="cta-button">주문하기</a>
                    </div>
                </div>
                <div class="swiper-slide hero-slide" style="background-image: url('/img/slide-3.jpg')">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h2>카다록 · 책자 제작</h2>
                        <p>전문적인 디자인과 완벽한 마감으로</p>
                        <a href="/MlangPrintAuto/cadarok/index.php" class="cta-button">주문하기</a>
                    </div>
                </div>
                <div class="swiper-slide hero-slide" style="background-image: url('/img/slide-4.jpg')">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h2>포스터 · 대형 인쇄</h2>
                        <p>선명하고 임팩트 있는 대형 인쇄물</p>
                        <a href="/MlangPrintAuto/LittlePrint/index.php" class="cta-button">주문하기</a>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">인쇄 서비스</h2>
            <p class="section-subtitle">두손기획인쇄의 전문적인 인쇄 서비스를 만나보세요</p>
            
            <div class="products-grid">
                <!-- 전단지/리플렛 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/slide-2.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/inserted/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">전단지 · 리플렛</h3>
                        <p class="card-description">홍보 효과를 극대화하는 전단지와 리플렛을 다양한 용지와 후가공으로 제작해드립니다.</p>
                    </div>
                </div>

                <!-- 스티커 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/slide-1.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/shop/view_modern.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">스티커</h3>
                        <p class="card-description">투명, 홀로그램, 크라프트 등 다양한 재질로 원하는 모양과 사이즈로 제작 가능합니다.</p>
                    </div>
                </div>

                <!-- 카다록 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/slide-4.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/cadarok/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">카다록 · 책자</h3>
                        <p class="card-description">제품 소개서, 회사 브로슈어 등 전문적인 카다록과 책자를 완벽한 품질로 제작합니다.</p>
                    </div>
                </div>

                <!-- 명함 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/portfolio/namecard_001.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/NameCard/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">명함</h3>
                        <p class="card-description">비즈니스의 첫인상을 결정하는 프리미엄 명함을 다양한 용지와 후가공으로 제작합니다.</p>
                    </div>
                </div>

                <!-- 봉투 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/portfolio/envelope_001.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/envelope/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">봉투</h3>
                        <p class="card-description">사업용 봉투부터 특수 봉투까지 용도에 맞는 다양한 봉투를 제작해드립니다.</p>
                    </div>
                </div>

                <!-- 포스터 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/portfolio/poster_001.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/LittlePrint/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">포스터 · 대형인쇄</h3>
                        <p class="card-description">A1, A0 대형 포스터부터 현수막까지 임팩트 있는 대형 인쇄물을 제작합니다.</p>
                    </div>
                </div>

                <!-- 상품권 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/pbt1.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/MerchandiseBond/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">상품권 · 쿠폰</h3>
                        <p class="card-description">상품권, 할인쿠폰, 식권 등 다양한 쿠폰 제작을 전문적으로 진행해드립니다.</p>
                    </div>
                </div>

                <!-- 양식지 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/obt.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/NcrFlambeau/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">양식지 · NCR</h3>
                        <p class="card-description">사업자등록증, 계산서, 영수증 등 업무에 필요한 각종 NCR 양식지를 제작해드립니다.</p>
                    </div>
                </div>

                <!-- 자석스티커 -->
                <div class="product-card">
                    <div class="card-image" style="background-image: url('/img/portfolio/sticker_015.jpg')">
                        <div class="card-overlay">
                            <a href="/MlangPrintAuto/msticker/index.php" class="order-btn">주문하기</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">자석스티커</h3>
                        <p class="card-description">자석 재질로 제작되는 특수 스티커로 냉장고, 차량 등에 부착 가능한 홍보용 스티커입니다.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Message Section -->
    <section class="message-section">
        <div class="container">
            <div class="message-content">
                <h2>기획에서 인쇄까지 원스톱 서비스</h2>
                <p>20년 이상의 노하우로 고객만족을 최우선으로 하는 두손기획인쇄입니다.<br>
                디자인 기획부터 최종 인쇄물까지 모든 과정을 책임지고 완성해드립니다.</p>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <h3>📞 대표전화</h3>
                        <p>02-2632-1830<br>1688-2384</p>
                    </div>
                    <div class="contact-item">
                        <h3>📍 오시는 길</h3>
                        <p>서울 영등포구 영등포로 36길 9<br>송호빌딩 1F</p>
                    </div>
                    <div class="contact-item">
                        <h3>🕒 운영시간</h3>
                        <p>평일 09:00 - 18:00<br>토요일 09:00 - 15:00</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>두손기획인쇄</h3>
                    <p>기획에서 인쇄까지 원스톱으로 해결해 드립니다.</p>
                    <p>주소: 서울 영등포구 영등포로 36길 9, 송호빌딩 1F</p>
                    <p>대표전화: 02-2632-1830, 1688-2384</p>
                    <p>홈페이지: www.dsp114.com</p>
                </div>
                <div class="footer-section">
                    <h3>인쇄 서비스</h3>
                    <p><a href="/MlangPrintAuto/inserted/index.php">전단지 · 리플렛</a></p>
                    <p><a href="/MlangPrintAuto/shop/view_modern.php">스티커</a></p>
                    <p><a href="/MlangPrintAuto/NameCard/index.php">명함</a></p>
                    <p><a href="/MlangPrintAuto/cadarok/index.php">카다록 · 책자</a></p>
                    <p><a href="/MlangPrintAuto/envelope/index.php">봉투</a></p>
                    <p><a href="/MlangPrintAuto/LittlePrint/index.php">포스터</a></p>
                </div>
                <div class="footer-section">
                    <h3>고객 서비스</h3>
                    <p><a href="/sub/estimate_auto.php">자동견적</a></p>
                    <p><a href="/sub/checkboard.php">교정보기</a></p>
                    <p><a href="/bbs/qna.php">고객문의</a></p>
                    <p><a href="/sub/info.php">회사소개</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 두손기획인쇄. All rights reserved. | 사업자등록번호: XXX-XX-XXXXX</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        // Initialize Swiper
        const swiper = new Swiper('.hero-slider', {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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

        // Product cards hover effect
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>