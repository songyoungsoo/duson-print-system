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
    <!-- Favicon -->
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
                            <a href="/mypage/" class="contact-text">주문확인</a>
                        </div>
                        <div class="contact-card">
                            <form action="/auth/logout.php" method="post" style="margin: 0;" onsubmit="return confirm('로그아웃 하시겠습니까?');">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <button type="submit" class="contact-text">로그아웃</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="contact-card">
                            <a href="javascript:void(0);" onclick="showLoginModal()" class="contact-text"><span style="letter-spacing: 0.35em;">로그인</span></a>
                        </div>
                        <div class="contact-card">
                            <a href="javascript:void(0);" onclick="showLoginModal(); setTimeout(function(){ document.querySelector('.login-tab:last-child').click(); }, 100);" class="contact-text">회원가입</a>
                        </div>
                        <div class="contact-card">
                            <a href="/mypage/" class="contact-text">주문확인</a>
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
            <!-- 사용자 메뉴 JavaScript는 /js/common-auth.js에서 처리 -->

<?php
// 로그인 모달 포함 (비로그인 상태에서만)
if (!$is_logged_in) {
    include __DIR__ . '/login_modal.php';
}
?>