<?php
/**
 * 공통 네비게이션 파일
 * 경로: includes/nav.php
 * 스타일: /css/common-styles.css의 .product-nav 섹션
 */

// 현재 페이지 확인을 위한 변수 (각 페이지에서 설정)
$current_page = isset($current_page) ? $current_page : '';
?>
<!-- 네비게이션 메뉴 -->
<div class="cart-nav-wrapper">
    <div class="product-nav">
        <a href="/mlangprintauto/sticker_new/index.php" class="nav-btn <?php echo ($current_page == 'sticker') ? 'active' : ''; ?>">
           🏷️ 스티커
        </a>

        <a href="/mlangprintauto/inserted/index.php" class="nav-btn <?php echo ($current_page == 'leaflet') ? 'active' : ''; ?>">
           📄 전단지
        </a>

        <a href="/mlangprintauto/namecard/index.php" class="nav-btn <?php echo ($current_page == 'namecard') ? 'active' : ''; ?>">
           📇 명함
        </a>

        <a href="/mlangprintauto/envelope/index.php" class="nav-btn <?php echo ($current_page == 'envelope') ? 'active' : ''; ?>">
           ✉️ 봉투
        </a>

        <a href="/mlangprintauto/cadarok/index.php" class="nav-btn <?php echo ($current_page == 'cadarok') ? 'active' : ''; ?>">
           📖 카다록
        </a>

        <a href="/mlangprintauto/littleprint/index.php" class="nav-btn <?php echo ($current_page == 'littleprint') ? 'active' : ''; ?>">
           🎨 포스터
        </a>

        <a href="/mlangprintauto/ncrflambeau/index.php" class="nav-btn <?php echo ($current_page == 'ncrflambeau') ? 'active' : ''; ?>">
           📋 양식지
        </a>

        <a href="/mlangprintauto/merchandisebond/index.php" class="nav-btn <?php echo ($current_page == 'merchandisebond') ? 'active' : ''; ?>">
           🎫 상품권
        </a>

        <a href="/mlangprintauto/msticker/index.php" class="nav-btn <?php echo ($current_page == 'msticker') ? 'active' : ''; ?>">
           자석스티커
        </a>
    </div>
</div>
