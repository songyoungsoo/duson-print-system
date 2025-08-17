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
            <a href="/MlangPrintAuto/inserted/index.php" 
               class="nav-link <?php echo ($current_page == 'leaflet') ? 'active' : ''; ?>">
               📄 전단지
            </a>
            <a href="/MlangPrintAuto/shop/view_modern.php" 
               class="nav-link <?php echo ($current_page == 'sticker') ? 'active' : ''; ?>">
               🏷️ 스티커
            </a>
            <a href="/MlangPrintAuto/cadarok/index.php" 
               class="nav-link <?php echo ($current_page == 'cadarok') ? 'active' : ''; ?>">
               📖 카다록
            </a>
            <a href="/MlangPrintAuto/NameCard/index.php" 
               class="nav-link <?php echo ($current_page == 'namecard') ? 'active' : ''; ?>">
               📇 명함
            </a>
            <a href="/MlangPrintAuto/MerchandiseBond/index.php" 
               class="nav-link <?php echo ($current_page == 'merchandisebond') ? 'active' : ''; ?>">
               🎫 상품권
            </a>
            <a href="/MlangPrintAuto/envelope/index.php" 
               class="nav-link <?php echo ($current_page == 'envelope') ? 'active' : ''; ?>">
               ✉️ 봉투
            </a>
            <a href="/MlangPrintAuto/LittlePrint/index_compact.php" 
               class="nav-link <?php echo ($current_page == 'littleprint') ? 'active' : ''; ?>">
               🎨 포스터
            </a>
            <a href="/MlangPrintAuto/msticker/index.php" 
               class="nav-link <?php echo ($current_page == 'msticker') ? 'active' : ''; ?>">
               🧲 자석스티커
            </a>
            <a href="/MlangPrintAuto/NcrFlambeau/index_compact.php" 
               class="nav-link <?php echo ($current_page == 'ncrflambeau') ? 'active' : ''; ?>">
               📋 양식지 <span style="font-size: 0.7em; color: #28a745;">✨NEW</span>
            </a>
        </div>
    </div>
</div>