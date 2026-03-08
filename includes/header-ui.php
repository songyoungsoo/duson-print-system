<?php
/**
 * 제품 페이지용 상단 헤더 UI 컴포넌트
 * 경로: includes/header-ui.php
 *
 * 주의: 이 파일은 <body> 태그 안에서 include 되어야 합니다.
 *       완전한 HTML 구조(<html>, <head>, <body>)를 포함하지 않습니다.
 */

// 세션 수명 8시간 통일 (auth.php, admin_auth.php, login_unified.php 동일)
$session_lifetime = 28800;
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $session_lifetime);
    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 통합 로그인 상태 확인 (세션 + 자동로그인 토큰 + 쿠키 호환)
$is_logged_in = isset($_SESSION['user_id']);
$user_name = '';

if ($is_logged_in) {
    // 신규 시스템 세션
    $user_name = $_SESSION['user_name'] ?? '';
} else {
    // 자동 로그인 토큰 체크 (remember_token 쿠키)
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        if (!isset($db)) {
            @include_once dirname(__DIR__) . '/db.php';
        }
        if (isset($db) && $db) {
            include_once __DIR__ . '/auth.php';
            // auth.php가 자동로그인 처리 후 $is_logged_in, $user_name 설정
            $is_logged_in = isset($_SESSION['user_id']);
            $user_name = $_SESSION['user_name'] ?? '';
        }
    }

    // 기존 시스템 호환 (fallback) — username이 아닌 DB에서 표시이름 조회
    if (!$is_logged_in) {
        $legacy_username = null;
        if (isset($_SESSION['id_login_ok'])) {
            $legacy_username = $_SESSION['id_login_ok']['id'] ?? '';
        } elseif (isset($_COOKIE['id_login_ok'])) {
            $legacy_username = $_COOKIE['id_login_ok'];
        }

        if ($legacy_username) {
            if (!isset($db) || !$db) {
                @include_once dirname(__DIR__) . '/db.php';
            }
            if (isset($db) && $db) {
                $name_stmt = mysqli_prepare($db, "SELECT id, name FROM users WHERE username = ?");
                if ($name_stmt) {
                    mysqli_stmt_bind_param($name_stmt, "s", $legacy_username);
                    mysqli_stmt_execute($name_stmt);
                    $name_result = mysqli_stmt_get_result($name_stmt);
                    $name_row = mysqli_fetch_assoc($name_result);
                    mysqli_stmt_close($name_stmt);

                    if ($name_row) {
                        // 신규 시스템 세션 복구
                        $_SESSION['user_id'] = $name_row['id'];
                        $_SESSION['username'] = $legacy_username;
                        $_SESSION['user_name'] = $name_row['name'];

                        $user_name = $name_row['name'];
                        $is_logged_in = true;
                    }
                }
            }
        }
    }
}
$_en_version_enabled = false;
if (isset($db) && $db) {
    $_en_q = @mysqli_query($db, "SELECT setting_value FROM site_settings WHERE setting_key='en_version_enabled' LIMIT 1");
    if ($_en_q && ($r = mysqli_fetch_assoc($_en_q))) {
        $_en_version_enabled = ($r['setting_value'] === '1');
    }
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
                <a href="/mypage/index.php" class="contact-text">주문확인</a>
                <div class="dropdown-menu">
                    <a href="/mypage/index.php">주문확인 홈</a>
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
                <a href="/member/login.php" class="contact-text"><span style="letter-spacing: 0.35em;">로그인</span></a>
            </div>
            <div class="contact-card">
                <a href="/member/join.php" class="contact-text">회원가입</a>
            </div>
            <div class="contact-card">
                <a href="/account/orders.php" class="contact-text">주문확인</a>
            </div>
            <?php endif; ?>
            <div class="contact-card">
                <a href="/sub/customer/how_to_use.php" class="contact-text">고객센터</a>
            </div>
            <div class="contact-card">
                <a href="/mlangprintauto/shop/cart.php" class="contact-text">장바구니</a>
            </div>
            <?php if ($_en_version_enabled): ?>
            <div class="contact-card lang-switch-card">
                <a href="/en/" class="contact-text lang-switch-btn" title="English version">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 3px;"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>EN
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- 사용자 메뉴 JavaScript는 /js/common-auth.js에서 처리 -->

<style>
/* 주문확인 드롭다운 메뉴 스타일 */
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

/* 주문확인 메뉴 아이콘 추가 */
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
