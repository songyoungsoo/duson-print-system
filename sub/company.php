<?php
/**
 * 회사소개 페이지
 * 경로: sub/company.php
 */

// 세션 시작
session_start();
$session_id = session_id();

// 보안 상수 정의 후 데이터베이스 연결
include "../db.php";
$connect = $db;

// 페이지 설정
$page_title = '회사소개 - 두손기획인쇄';
$current_page = 'company';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

// 공통 인증 시스템 사용
include "../includes/auth.php";
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
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- SEO 메타 태그 -->
    <meta name="description" content="두손기획인쇄 회사소개 - 1998년 창립 이래 25년 이상 축적된 인쇄 전문성으로 고객에게 최고 품질의 인쇄 서비스를 제공합니다">
    <meta name="keywords" content="두손기획인쇄, 회사소개, 인쇄전문, 온라인견적, 공장직영">

    <!-- 브랜드 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/brand-design-system.css">
    <link rel="stylesheet" href="../css/product-layout.css">
    <link rel="stylesheet" href="../css/style250801.css">
    <link rel="stylesheet" href="../css/common-styles.css">

    <style>
        /* 회사소개 전용 스타일 */
        .company-hero {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .company-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
        }

        .company-hero p {
            font-size: 1.2rem;
            color: #e0e0e0;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .company-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        .company-section {
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3b82f6;
        }

        .company-intro {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
            margin-bottom: 60px;
        }

        .intro-image {
            width: 100%;
            height: 550px;
            background: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .intro-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 20%;
        }

        .intro-content {
            padding: 20px;
        }

        .intro-content h3 {
            font-size: 2.2rem;
            color: #ffffff;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .intro-content p {
            font-size: 1.3rem;
            line-height: 2.2;
            color: #c0c0c0;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin: 40px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin: 40px 0;
        }

        .feature-box {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature-box:hover {
            border-color: #3b82f6;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }

        .feature-box h4 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .feature-box p {
            font-size: 1.2rem;
            color: #1a252f;
            line-height: 1.9;
            font-weight: 500;
        }

        .company-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            background: #f8f9fa;
            padding: 40px;
            border-radius: 12px;
            margin: 40px 0;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            background: white;
            border-radius: 8px;
        }

        .info-label {
            font-weight: 700;
            color: #2c3e50;
            min-width: 120px;
            font-size: 1.1rem;
        }

        .info-value {
            color: #1a252f;
            font-size: 1.2rem;
            line-height: 1.9;
            font-weight: 500;
        }

        .cta-box {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 40px;
            border-radius: 12px;
            text-align: center;
            margin-top: 60px;
        }

        .cta-box h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: white;
        }

        .cta-box p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #e0e0e0;
        }

        .btn-cta {
            display: inline-block;
            padding: 18px 40px;
            background: #ffc107;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.3rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-cta:hover {
            background: #ffeb3b;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .company-hero h1 {
                font-size: 1.8rem;
            }

            .company-intro {
                grid-template-columns: 1fr;
            }

            .intro-image {
                height: 450px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .company-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include "../includes/header-ui.php"; ?>
    <?php if (file_exists('../includes/nav.php')) include "../includes/nav.php"; ?>

    <!-- Hero 섹션 -->
    <section class="company-hero">
        <h1>두손기획인쇄를 소개합니다</h1>
        <p>1998년 창립 이래 25년 이상 축적된 인쇄 전문성으로<br>고객에게 최고 품질의 인쇄 서비스를 제공합니다</p>
    </section>

    <div class="company-container">
        <!-- 회사 소개 -->
        <section class="company-section">
            <div class="company-intro">
                <div class="intro-image">
                    <img src="/ImgFolder/sample/ceoface.png" alt="두손기획인쇄 대표">
                </div>
                <div class="intro-content">
                    <h3>신뢰할 수 있는 인쇄 파트너</h3>
                    <p>
                        두손기획인쇄는 1998년부터 시작하여 25년 이상의 경험과 노하우로
                        기업과 개인 고객에게 최상의 인쇄 서비스를 제공하고 있습니다.
                    </p>
                    <p>
                        공장 직영 시스템을 통해 합리적인 가격과 빠른 제작 속도를 자랑하며,
                        온라인 견적 시스템으로 언제 어디서나 편리하게 주문하실 수 있습니다.
                    </p>
                    <p>
                        스티커, 전단지, 명함, 봉투, 카다록, 포스터, 상품권, 양식지, 자석스티커 등
                        다양한 인쇄물을 전문적으로 제작하며, 기획에서 인쇄까지 원스톱 서비스를 제공합니다.
                    </p>
                </div>
            </div>
        </section>

        <!-- 주요 통계 -->
        <section class="company-section">
            <h2 class="section-title">두손기획인쇄의 성과</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">25+</div>
                    <div class="stat-label">년간 경험</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">10,000+</div>
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

        <!-- 핵심 강점 -->
        <section class="company-section">
            <h2 class="section-title">두손기획인쇄의 강점</h2>
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon">⚡</div>
                    <h4>실시간 견적</h4>
                    <p>복잡한 계산 없이 즉시 확인하는 정확한 가격으로 시간을 절약하세요</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">🏭</div>
                    <h4>공장 직영</h4>
                    <p>중간 유통 과정 없이 공장에서 직접 제작하여 합리적인 가격을 제공합니다</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">🎨</div>
                    <h4>전문 디자인</h4>
                    <p>20년 경험의 디자이너가 제공하는 완성도 높은 전문 디자인 서비스</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">🏆</div>
                    <h4>품질 보증</h4>
                    <p>까다로운 품질 검사를 통과한 최고급 소재와 정밀한 인쇄 기술</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">🚚</div>
                    <h4>신속 배송</h4>
                    <p>전국 익일 배송으로 급한 일정도 여유롭게 해결 (도서지방제외)</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">💻</div>
                    <h4>온라인 시스템</h4>
                    <p>24시간 온라인 주문 및 실시간 견적으로 언제든지 편리하게 이용</p>
                </div>
            </div>
        </section>

        <!-- 회사 정보 -->
        <section class="company-section">
            <h2 class="section-title">회사 정보</h2>
            <div class="company-info-grid">
                <div class="info-item">
                    <div class="info-label">회사명</div>
                    <div class="info-value">두손기획인쇄</div>
                </div>
                <div class="info-item">
                    <div class="info-label">대표</div>
                    <div class="info-value">차경선</div>
                </div>
                <div class="info-item">
                    <div class="info-label">사업자등록번호</div>
                    <div class="info-value">107-06-45106</div>
                </div>
                <div class="info-item">
                    <div class="info-label">전화번호</div>
                    <div class="info-value">1688-2384</div>
                </div>
                <div class="info-item">
                    <div class="info-label">주소</div>
                    <div class="info-value">서울시 영등포구 영등포로 36길 9 송호빌딩 1층</div>
                </div>
                <div class="info-item">
                    <div class="info-label">창립년도</div>
                    <div class="info-value">1998년</div>
                </div>
                <div class="info-item">
                    <div class="info-label">주요 서비스</div>
                    <div class="info-value">스티커, 전단지, 명함, 봉투, 카다록, 포스터, 상품권, 양식지, 자석스티커 인쇄</div>
                </div>
                <div class="info-item">
                    <div class="info-label">운영 시간</div>
                    <div class="info-value">평일 09:00 ~ 18:00<br>(온라인 주문 24시간 가능)</div>
                </div>
            </div>
        </section>

        <!-- CTA 섹션 -->
        <section class="cta-box">
            <h3>지금 바로 상담받으세요</h3>
            <p>전문 상담원이 최적의 인쇄 솔루션을 제안해드립니다</p>
            <a href="tel:1688-2384" class="btn-cta">
                📞 1688-2384
            </a>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
