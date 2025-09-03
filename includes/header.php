<?php
/**
 * 공통 헤더 파일
 * 경로: includes/header.php
 */

// 세션이 시작되지 않았다면 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 통합 로그인 상태 확인 (세션 + 쿠키 호환)
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

if (isset($_SESSION['user_id'])) {
    // 신규 시스템
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    // 기존 시스템 세션
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
} elseif (isset($_COOKIE['id_login_ok'])) {
    // 기존 시스템 쿠키 (fallback)
    $user_name = $_COOKIE['id_login_ok'];
    $is_logged_in = true;
} else {
    $user_name = '';
    $is_logged_in = false;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : '두손기획인쇄'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style250801.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <!-- 공통 인증 JavaScript -->
    <script src="/js/common-auth.js"></script>
    <style>
    /* 헤더 컴팩트 스타일 - 위아래 여백 1/3로 축소 */
    .top-header {
        padding: 0.5rem 0 !important; /* 기존 1.5rem에서 0.5rem로 축소 */
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); /* 푸터와 동일한 배경 */
        border-bottom: 1px solid rgba(255, 255, 255, 0.2); /* 어두운 배경에 맞는 테두리 */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* 살짝 그림자 추가 */
    }
    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1rem;
        min-height: 50px; /* 기존 80px에서 50px로 축소 */
    }
    .logo-section {
        display: flex;
        align-items: center;
        gap: 0.5rem; /* 기존 1rem에서 0.5rem로 축소 */
    }
    .logo-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease;
        border-radius: 4px;
        padding: 0.2rem;
    }
    .logo-link:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-1px);
    }
    .logo-icon {
        width: 35px; /* 기존 50px에서 35px로 축소 */
        height: 35px;
        background: linear-gradient(135deg, #ff9100 0%, #8bc34a 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #1a365d;
        font-size: 0.6rem;
        position: relative;
        overflow: hidden;
    }
    .logo-icon::before {
        content: "두손\A기획\A인쇄";
        white-space: pre;
        text-align: center;
        line-height: 0.7;
        font-size: 0.45rem;
        font-weight: 800;
        color: #1a365d;
    }
    .company-info h1 {
        font-size: 1.2rem !important; /* 기존 1.8rem에서 1.2rem로 축소 */
        font-weight: 700;
        color: #ffffff !important; /* 푸터와 동일한 선명한 흰색 */
        margin: 0;
        line-height: 1.2;
    }
    .company-info p {
        font-size: 0.75rem !important; /* 기존 1rem에서 0.75rem로 축소 */
        color: #ffffff !important; /* 푸터와 동일한 선명한 흰색 */
        opacity: 0.9; /* 살짝 투명도 추가 */
        margin: 0;
        line-height: 1.3;
    }
    .contact-info {
        display: flex;
        gap: 0.5rem; /* 기존 1rem에서 0.5rem로 축소 */
        align-items: center;
    }
    .contact-card {
        padding: 0.3rem 0.6rem; /* 기존 0.5rem 1rem에서 축소 */
        background: rgba(255, 255, 255, 0.1); /* 반투명 흰색 배경 */
        border: none; /* 테두리 제거 */
        border-radius: 4px;
        transition: all 0.2s;
    }
    .contact-card:hover {
        background: rgba(255, 255, 255, 0.2); /* 호버시 더 밝게 */
    }
    .contact-text, .user-menu-toggle {
        color: #ffffff !important; /* 푸터와 동일한 선명한 흰색 */
        text-decoration: none;
        font-size: 0.8rem !important; /* 기존 0.9rem에서 0.8rem로 축소 */
        font-weight: 600; /* 더 굵게 해서 가독성 향상 */
        background: none;
        border: none;
        cursor: pointer;
    }
    .user-info-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.1rem;
    }
    .user-info-header .label {
        font-size: 0.65rem; /* 기존 0.8rem에서 축소 */
        color: #ffffff; /* 푸터와 동일한 선명한 흰색 */
        opacity: 0.8; /* 살짝 투명도 추가 */
    }
    .user-info-header .value {
        font-size: 0.75rem; /* 기존 0.9rem에서 축소 */
        font-weight: 700; /* 더 굵게 해서 가독성 향상 */
        color: #ffffff; /* 푸터와 동일한 선명한 흰색 */
    }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="main-content-wrapper">
            <!-- 상단 헤더 -->
            <div class="top-header">
                <div class="header-content">
                    <div class="logo-section">
                        <a href="/" class="logo-link">
                            <div class="logo-icon"></div>
                            <div class="company-info">
                                <h1>두손기획인쇄</h1>
                                <p>기획에서 인쇄까지 원스톱으로 해결해 드립니다</p>
                            </div>
                        </a>
                    </div>
                    <div class="contact-info">
                        <div class="contact-card proofread-card">
                            <a href="/sub/checkboard.php" class="contact-text">교정보기</a>
                        </div>
                        <?php if ($is_logged_in): ?>
                        <div class="contact-card">
                            <div class="user-info-header">
                                <div class="value"><?php echo htmlspecialchars($user_name); ?>님</div>
                            </div>
                        </div>
                        <div class="contact-card">
                            <a href="/account/orders.php" class="contact-text">내주문내역</a>
                        </div>
                        <div class="contact-card">
                            <form action="/auth/logout.php" method="post" style="margin: 0;" onsubmit="return confirm('로그아웃 하시겠습니까?');">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <button type="submit" class="contact-text">로그아웃</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="contact-card">
                            <button onclick="showLoginModal()" class="contact-text">로그인</button>
                        </div>
                        <div class="contact-card">
                            <button onclick="showRegisterModal()" class="contact-text">회원가입</button>
                        </div>
                        <div class="contact-card">
                            <a href="/account/orders.php" class="contact-text">내주문내역</a>
                        </div>
                        <?php endif; ?>
                        <div class="contact-card">
                            <a href="tel:1688-2384" class="contact-text">고객센터</a>
                        </div>
                        <div class="contact-card">
                            <a href="/MlangPrintAuto/shop/cart.php" class="contact-text">장바구니</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 사용자 메뉴 JavaScript는 /js/common-auth.js에서 처리 -->