<?php
/**
 * 공통 네비게이션 파일
 * 경로: includes/nav.php
 * 스타일: Excel 스타일 - 각진 모서리, 깔끔한 디자인
 */

// 현재 페이지 확인을 위한 변수 (각 페이지에서 설정)
$current_page = isset($current_page) ? $current_page : '';
?>
<!-- 네비게이션 메뉴 (Excel 스타일) -->
<style>
.nav-menu {
    display: flex;
    flex-wrap: wrap;
    gap: 0;
    margin-bottom: 0.5rem;
    justify-content: center;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0;
}
.nav-item {
    display: inline-block;
    padding: 12px 18px;
    background: transparent;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: background 0.15s ease, color 0.15s ease;
    border-right: 1px solid #dee2e6;
    white-space: nowrap;
}
.nav-item:last-child {
    border-right: none;
}
.nav-item:hover {
    background: #1E4E79;
    color: white;
}
.nav-item.active {
    background: #1E4E79;
    color: white;
    font-weight: 600;
}
@media (max-width: 768px) {
    .nav-menu {
        flex-wrap: wrap;
    }
    .nav-item {
        padding: 10px 14px;
        font-size: 13px;
        border-bottom: 1px solid #dee2e6;
    }
}
</style>
<nav class="nav-menu">
    <a href="/mlangprintauto/sticker_new/index.php" class="nav-item <?php echo ($current_page == 'sticker') ? 'active' : ''; ?>">스티커</a>
    <a href="/mlangprintauto/inserted/index.php" class="nav-item <?php echo ($current_page == 'leaflet') ? 'active' : ''; ?>">전단지</a>
    <a href="/mlangprintauto/namecard/index.php" class="nav-item <?php echo ($current_page == 'namecard') ? 'active' : ''; ?>">명함</a>
    <a href="/mlangprintauto/envelope/index.php" class="nav-item <?php echo ($current_page == 'envelope') ? 'active' : ''; ?>">봉투</a>
    <a href="/mlangprintauto/cadarok/index.php" class="nav-item <?php echo ($current_page == 'cadarok') ? 'active' : ''; ?>">카다록</a>
    <a href="/mlangprintauto/littleprint/index.php" class="nav-item <?php echo ($current_page == 'littleprint') ? 'active' : ''; ?>">포스터</a>
    <a href="/mlangprintauto/ncrflambeau/index.php" class="nav-item <?php echo ($current_page == 'ncrflambeau') ? 'active' : ''; ?>">양식지</a>
    <a href="/mlangprintauto/merchandisebond/index.php" class="nav-item <?php echo ($current_page == 'merchandisebond') ? 'active' : ''; ?>">상품권</a>
    <a href="/mlangprintauto/msticker/index.php" class="nav-item <?php echo ($current_page == 'msticker') ? 'active' : ''; ?>">자석스티커</a>
</nav>
