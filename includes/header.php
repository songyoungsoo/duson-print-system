<?php
/**
 * 공통 헤더 파일
 * 경로: includes/header.php
 */

// 세션이 시작되지 않았다면 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 로그인 상태 확인
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
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
</head>
<body>
    <div class="page-wrapper">
        <div class="main-content-wrapper">
            <!-- 상단 헤더 -->
            <div class="top-header">
                <div class="header-content">
                    <div class="logo-section">
                        <div class="logo-icon">🖨️</div>
                        <div class="company-info">
                            <h1>두손기획인쇄</h1>
                            <p>기획에서 인쇄까지 원스톱으로 해결해 드립니다</p>
                        </div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-card">
                            <div class="label">📞 고객센터</div>
                            <div class="value">1688-2384</div>
                        </div>
                        <div class="contact-card cart-card">
                            <a href="/MlangPrintAuto/shop/cart.php" class="cart-btn">🛒 장바구니</a>
                        </div>
                        <?php if ($is_logged_in): ?>
                        <div class="contact-card user-menu">
                            <div class="user-info-header">
                                <div class="label">👤 환영합니다</div>
                                <div class="value"><?php echo htmlspecialchars($user_name); ?>님</div>
                            </div>
                            <div class="user-menu-dropdown">
                                <button class="user-menu-toggle" onclick="toggleUserMenu()">
                                    ⚙️ 메뉴
                                </button>
                                <div class="user-menu-items" id="userMenuItems">
                                    <a href="/account/orders.php" class="menu-item">
                                        📋 내 주문 내역
                                    </a>
                                    <a href="/member/form.php" class="menu-item">
                                        👤 내 정보 수정
                                    </a>
                                    <hr class="menu-divider">
                                    <form action="/auth/logout.php" method="post" style="margin: 0;" onsubmit="return confirm('로그아웃 하시겠습니까?');">
                                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                        <button type="submit" class="menu-item logout-item">
                                            🚪 로그아웃
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="contact-card login-card">
                            <button onclick="showLoginModal()" class="login-btn">🔐 로그인</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <script>
            // 사용자 메뉴 토글 함수
            function toggleUserMenu() {
                const menuItems = document.getElementById('userMenuItems');
                menuItems.classList.toggle('show');
            }

            // 메뉴 외부 클릭 시 닫기
            document.addEventListener('click', function(event) {
                const userMenu = document.querySelector('.user-menu-dropdown');
                const menuItems = document.getElementById('userMenuItems');
                
                if (userMenu && !userMenu.contains(event.target)) {
                    menuItems.classList.remove('show');
                }
            });
            
            // ESC 키로 메뉴 닫기
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const menuItems = document.getElementById('userMenuItems');
                    menuItems.classList.remove('show');
                }
            });
            </script>