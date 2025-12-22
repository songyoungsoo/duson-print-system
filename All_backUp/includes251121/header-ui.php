<?php
/**
 * 제품 페이지용 상단 헤더 UI 컴포넌트
 * 경로: includes/header-ui.php
 *
 * 주의: 이 파일은 <body> 태그 안에서 include 되어야 합니다.
 *       완전한 HTML 구조(<html>, <head>, <body>)를 포함하지 않습니다.
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
            <div class="contact-card mypage-dropdown">
                <a href="/mypage/index.php" class="contact-text">마이페이지</a>
                <div class="dropdown-menu">
                    <a href="/mypage/index.php">마이페이지 홈</a>
                    <a href="/mypage/orders.php">주문조회&배송조회</a>
                    <a href="/mypage/tax_invoices.php">전자세금계산서</a>
                    <a href="/mypage/transactions.php">거래내역조회</a>
                    <div class="dropdown-divider"></div>
                    <a href="/mypage/profile.php">회원정보수정</a>
                    <a href="/mypage/change_password.php">비밀번호변경</a>
                    <a href="/mypage/business_certificate.php">사업자등록증</a>
                    <div class="dropdown-divider"></div>
                    <a href="/mypage/withdraw.php">회원탈퇴</a>
                </div>
            </div>
            <div class="contact-card">
                <form action="/auth/logout.php" method="post" style="margin: 0;" onsubmit="return confirm('로그아웃 하시겠습니까?');">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <button type="submit" class="contact-text">로그아웃</button>
                </form>
            </div>
            <?php else: ?>
            <div class="contact-card">
                <a href="/member/login.php" class="contact-text">로그인</a>
            </div>
            <div class="contact-card">
                <a href="/member/join.php" class="contact-text">회원가입</a>
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
<!-- 사용자 메뉴 JavaScript는 /js/common-auth.js에서 처리 -->

<style>
/* 마이페이지 드롭다운 메뉴 스타일 */
.mypage-dropdown {
    position: relative;
    cursor: pointer;
}

.mypage-dropdown .contact-text {
    cursor: pointer;
    user-select: none;
}

.mypage-dropdown .dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 5px);
    right: 0;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 200px;
    z-index: 10000;
    padding: 6px 0;
}

/* 드롭다운 메뉴 비활성화 */
.mypage-dropdown:hover .dropdown-menu {
    display: none;
}

.dropdown-menu a {
    display: block;
    padding: 10px 16px;
    color: #333;
    text-decoration: none;
    font-size: 13px;
    transition: background 0.2s;
}

.dropdown-menu a:hover {
    background: #f8f9fa;
    color: #1466BA;
}

.dropdown-divider {
    height: 1px;
    background: #e9ecef;
    margin: 6px 0;
}

/* 마이페이지 메뉴 아이콘 추가 */
.dropdown-menu a:before {
    margin-right: 8px;
}

.dropdown-menu a[href*="index.php"]:before { content: "📊 "; }
.dropdown-menu a[href*="orders.php"]:before { content: "📦 "; }
.dropdown-menu a[href*="tax_invoices.php"]:before { content: "🧾 "; }
.dropdown-menu a[href*="transactions.php"]:before { content: "💳 "; }
.dropdown-menu a[href*="profile.php"]:before { content: "👤 "; }
.dropdown-menu a[href*="change_password.php"]:before { content: "🔒 "; }
.dropdown-menu a[href*="business_certificate.php"]:before { content: "📄 "; }
.dropdown-menu a[href*="withdraw.php"]:before { content: "⚠️ "; }

@media (max-width: 768px) {
    .mypage-dropdown .dropdown-menu {
        right: -50px;
        min-width: 180px;
    }

    .dropdown-menu a {
        padding: 9px 14px;
        font-size: 12px;
    }
}
</style>
