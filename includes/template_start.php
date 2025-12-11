<?php
/**
 * 통합 템플릿 시작 - HTML 헤드와 헤더
 * 모든 품목 페이지에서 동일하게 사용
 */

// 세션이 시작되지 않았다면 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 통합 로그인 상태 확인
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
} elseif (isset($_COOKIE['id_login_ok'])) {
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
    
    <!-- 🏗️ 통합 디자인 시스템 - 최우선 적용 -->
    <link rel="stylesheet" href="../../css/mlang-design-system.css">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- 공통 인증 JavaScript -->
    <script src="/js/common-auth.js"></script>
    
    <style>
    /* 헤더 컴팩트 스타일 */
    .top-header {
        padding: 0.5rem 0 !important;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1rem;
        min-height: 50px;
    }
    .logo-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
        width: 35px;
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
        font-size: 1.2rem !important;
        font-weight: 700;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }
    .company-info p {
        font-size: 0.75rem !important;
        color: #ffffff !important;
        opacity: 0.9;
        margin: 0;
        line-height: 1.3;
    }
    .contact-info {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .contact-card {
        padding: 0.3rem 0.6rem;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .contact-card:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    .contact-text, .user-menu-toggle {
        color: #ffffff !important;
        text-decoration: none;
        font-size: 0.8rem !important;
        font-weight: 600;
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
        font-size: 0.65rem;
        color: #ffffff;
        opacity: 0.8;
    }
    .user-info-header .value {
        font-size: 0.75rem;
        font-weight: 700;
        color: #ffffff;
    }
    </style>
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
    <div class="page-wrapper">
        <div class="main-content-wrapper">
            <!-- 상단 헤더 -->
            <div class="top-header">
                <div class="header-content">
                    <div class="logo-section">
                        <a href="/" class="logo-link">
                            <img src="/ImgFolder/dusonlogo1.png" alt="두손기획인쇄 로고" class="logo-icon">
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
                            <a href="/sub/customer/how_to_use.php" class="contact-text">고객센터</a>
                        </div>
                        <div class="contact-card">
                            <a href="/mlangprintauto/shop/cart.php" class="contact-text">장바구니</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 메인 컨텐츠 영역 시작 (여기서부터 품목별로 다름) -->