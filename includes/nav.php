<?php
/**
 * 공통 네비게이션 파일
 * 경로: includes/nav.php
 */

// 현재 페이지 확인을 위한 변수 (각 페이지에서 설정)
$current_page = isset($current_page) ? $current_page : '';
?>
<!-- 네비게이션 메뉴 -->
<div class="nav-menu">
    <div class="nav-content">
        <div class="nav-links">
            <a href="/mlangprintauto/inserted/index.php" class="nav-link <?php echo ($current_page == 'leaflet' || $current_page == 'inserted') ? 'active' : ''; ?>">
                <span class="nav-icon">📄</span><span class="nav-text">전단지</span>
            </a>
            <a href="/mlangprintauto/sticker_new/index.php" class="nav-link <?php echo ($current_page == 'sticker') ? 'active' : ''; ?>">
                <span class="nav-icon">🏷️</span><span class="nav-text">스티커</span>
            </a>
            <a href="/mlangprintauto/cadarok/index.php" class="nav-link <?php echo ($current_page == 'cadarok') ? 'active' : ''; ?>">
                <span class="nav-icon">📖</span><span class="nav-text">카다록</span>
            </a>
            <a href="/mlangprintauto/namecard/index.php" class="nav-link <?php echo ($current_page == 'namecard') ? 'active' : ''; ?>">
                <span class="nav-icon">📇</span><span class="nav-text">명&nbsp;함</span>
            </a>
            <a href="/mlangprintauto/merchandisebond/index.php" class="nav-link <?php echo ($current_page == 'merchandisebond') ? 'active' : ''; ?>">
                <span class="nav-icon">🎫</span><span class="nav-text">상품권</span>
            </a>
            <a href="/mlangprintauto/envelope/index.php" class="nav-link <?php echo ($current_page == 'envelope') ? 'active' : ''; ?>">
                <span class="nav-icon">✉️</span><span class="nav-text">봉&nbsp;투</span>
            </a>
            <a href="/mlangprintauto/ncrflambeau/index.php" class="nav-link <?php echo ($current_page == 'ncrflambeau') ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span><span class="nav-text">양식지</span>
            </a>
            <a href="/mlangprintauto/littleprint/index.php" class="nav-link <?php echo ($current_page == 'littleprint' || $current_page == 'poster') ? 'active' : ''; ?>">
                <span class="nav-icon">🎨</span><span class="nav-text">포스터</span>
            </a>
            <a href="/mlangprintauto/shop/cart.php" class="nav-link <?php echo ($current_page == 'cart') ? 'active' : ''; ?>">
                <span class="nav-icon">🛒</span><span class="nav-text">카&nbsp;트</span>
            </a>
        </div>
    </div>
</div>
