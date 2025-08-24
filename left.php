<?php
// 현재 페이지 경로를 기반으로 활성 메뉴 결정
$current_page = $_SERVER['REQUEST_URI'];
$current_script = basename($_SERVER['SCRIPT_NAME']);

// 각 메뉴 항목의 활성 상태 확인 함수
function isActive($page_path, $current_page) {
    return strpos($current_page, $page_path) !== false ? 'active' : '';
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

.vertical-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 160px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.vertical-menu li {
    margin-bottom: 4px;
    border-radius: 8px;
    overflow: hidden;
}

.vertical-menu a {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    text-decoration: none;
    color: #2c3e50;
    background: white;
    border: 1px solid rgba(44, 62, 80, 0.15);
    border-radius: 8px;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif !important;
    font-weight: 600;
    font-size: 13px;
    line-height: 1.3;
    text-align: center;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    min-height: 25px;
    width: fit-content;
    min-width: 120px;
    max-width: 140px;
}

.vertical-menu a::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.1) 100%);
    border-radius: 15px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.vertical-menu a:hover::before {
    opacity: 1;
}

.vertical-menu a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: rgba(44, 62, 80, 0.3);
}

.vertical-menu a.order-btn {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 8px;
    box-shadow: 0 3px 10px rgba(44, 62, 80, 0.3);
    min-width: 130px;
    border: 1px solid #2c3e50;
}

.vertical-menu a.order-btn:hover {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
    border-color: #e74c3c;
}

/* 각 메뉴별 호버 색상 */
.vertical-menu a.sticker:hover {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
    border-color: #4CAF50;
}

.vertical-menu a.leaflet:hover {
    background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
    border-color: #FF9800;
}

.vertical-menu a.business:hover {
    background: linear-gradient(135deg, #9C27B0 0%, #7b1fa2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(156, 39, 176, 0.4);
    border-color: #9C27B0;
}

.vertical-menu a.coupon:hover {
    background: linear-gradient(135deg, #E91E63 0%, #c2185b 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.4);
    border-color: #E91E63;
}

.vertical-menu a.envelope:hover {
    background: linear-gradient(135deg, #795548 0%, #5d4037 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(121, 85, 72, 0.4);
    border-color: #795548;
}

.vertical-menu a.form:hover {
    background: linear-gradient(135deg, #607D8B 0%, #455a64 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(96, 125, 139, 0.4);
    border-color: #607D8B;
}

.vertical-menu a.catalog:hover {
    background: linear-gradient(135deg, #8BC34A 0%, #689f38 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(139, 195, 74, 0.4);
    border-color: #8BC34A;
}

.vertical-menu a.poster:hover {
    background: linear-gradient(135deg, #FF5722 0%, #d84315 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(255, 87, 34, 0.4);
    border-color: #FF5722;
}

/* Active 상태 */
.vertical-menu a.active {
    color: white;
    transform: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.vertical-menu a.active.sticker {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.5);
    border-color: #4CAF50;
}

.vertical-menu a.active.leaflet {
    background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.5);
    border-color: #FF9800;
}

.vertical-menu a.active.business {
    background: linear-gradient(135deg, #9C27B0 0%, #7b1fa2 100%);
    box-shadow: 0 4px 12px rgba(156, 39, 176, 0.5);
    border-color: #9C27B0;
}

.vertical-menu a.active.coupon {
    background: linear-gradient(135deg, #E91E63 0%, #c2185b 100%);
    box-shadow: 0 4px 12px rgba(233, 30, 99, 0.5);
    border-color: #E91E63;
}

.vertical-menu a.active.envelope {
    background: linear-gradient(135deg, #795548 0%, #5d4037 100%);
    box-shadow: 0 4px 12px rgba(121, 85, 72, 0.5);
    border-color: #795548;
}

.vertical-menu a.active.form {
    background: linear-gradient(135deg, #607D8B 0%, #455a64 100%);
    box-shadow: 0 4px 12px rgba(96, 125, 139, 0.5);
    border-color: #607D8B;
}

.vertical-menu a.active.catalog {
    background: linear-gradient(135deg, #8BC34A 0%, #689f38 100%);
    box-shadow: 0 4px 12px rgba(139, 195, 74, 0.5);
    border-color: #8BC34A;
}

.vertical-menu a.active.poster {
    background: linear-gradient(135deg, #FF5722 0%, #d84315 100%);
    box-shadow: 0 4px 12px rgba(255, 87, 34, 0.5);
    border-color: #FF5722;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .vertical-menu {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .vertical-menu li {
        flex: 1;
        min-width: calc(50% - 2px);
        margin-bottom: 0;
    }
    
    .vertical-menu li.dropdown {
        flex: 1;
        min-width: calc(100% - 2px); /* 드롭다운은 전체 너비 */
    }
    
    .vertical-menu a {
        font-size: 11px;
        padding: 6px 8px;
        min-height: 20px;
        min-width: auto;
    }
    
    .vertical-menu a.order-btn {
        flex: 100%;
        font-size: 12px;
        margin-bottom: 4px;
    }
    
    .dropdown-content {
        position: static;
        display: none;
        box-shadow: none;
        border: 1px solid #e0e0e0;
        border-top: none;
        border-radius: 0 0 8px 8px;
        margin-top: 0;
    }
    
    .dropdown:hover .dropdown-content {
        display: block;
    }
}

/* 메뉴 제목 제거 */
.menu-title {
    display: none;
}

/* 드롭다운 메뉴 스타일 */
.dropdown {
    position: relative;
    display: inline-block;
    width: 100%;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 150px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    border-radius: 8px;
    z-index: 1000;
    top: 100%;
    left: 0;
    border: 1px solid rgba(44, 62, 80, 0.15);
    overflow: hidden;
}

.dropdown-content a {
    color: #2c3e50 !important;
    padding: 8px 12px !important;
    text-decoration: none;
    display: block !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    border-radius: 0 !important;
    border: none !important;
    box-shadow: none !important;
    margin: 0 !important;
    min-width: auto !important;
    max-width: none !important;
    width: 100% !important;
    text-align: left !important;
    background: white !important;
    transition: background-color 0.2s ease !important;
}

.dropdown-content a:hover {
    background-color: #f8f9fa !important;
    transform: none !important;
    box-shadow: none !important;
    border-color: transparent !important;
}

.dropdown-content a.sticker-general:hover {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%) !important;
    color: white !important;
}

.dropdown-content a.sticker-magnetic:hover {
    background: linear-gradient(135deg, #2196F3 0%, #1976d2 100%) !important;
    color: white !important;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown-main {
    position: relative;
}

.dropdown-arrow {
    font-size: 10px;
    margin-left: 4px;
    transition: transform 0.2s ease;
}

.dropdown:hover .dropdown-arrow {
    transform: rotate(180deg);
}
</style>

<!-- 컴팩트 네비게이션 메뉴 -->
<ul class="vertical-menu">
  <li>
    <a href="/MlangPrintAuto/inserted/index.php" 
       class="order-btn <?php echo isActive('/MlangPrintAuto/inserted/', $current_page); ?> leaflet">
       🚀 견적주문
    </a>
  </li>
  
  <li>
    <a href="/shop/cart.php" 
       class="order-btn" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); margin-bottom: 8px; box-shadow: 0 3px 10px rgba(39, 174, 96, 0.3); border-color: #27ae60;">
       🛒 장바구니
    </a>
  </li>
  
  <li>
    <a href="/MlangPrintAuto/inserted/index.php?page=inserted" 
       class="leaflet <?php echo isActive('/MlangPrintAuto/inserted/', $current_page); ?>">
       📄 전단지·리플렛
    </a>
  </li>
  
  <li class="dropdown">
    <a href="javascript:void(0)" 
       class="sticker dropdown-main <?php echo (isActive('/shop/view', $current_page) || isActive('/MlangPrintAuto/msticker/', $current_page)) ? 'active' : ''; ?>">
       🏷️ 스티커 <span class="dropdown-arrow">▼</span>
    </a>
    <div class="dropdown-content">
      <a href="/shop/view_modern.php" class="sticker-general">
        🏷️ 일반스티커
      </a>
      <a href="/MlangPrintAuto/msticker/index.php" class="sticker-magnetic">
        🧲 자석스티커
      </a>
    </div>
  </li>
  
  <li>
    <a href="/MlangPrintAuto/NameCard/index.php" 
       class="business <?php echo isActive('/MlangPrintAuto/NameCard/', $current_page); ?>">
       📇 명함
    </a>
  </li>
  
  <li>
    <a href="/MlangPrintAuto/MerchandiseBond/index.php?page=MerchandiseBond" 
       class="coupon <?php echo isActive('/MlangPrintAuto/MerchandiseBond/', $current_page); ?>">
       🎫 상품권
    </a>
  </li>
  
  <li>
    <a href="/MlangPrintAuto/envelope/index.php?page=envelope" 
       class="envelope <?php echo isActive('/MlangPrintAuto/envelope/', $current_page); ?>">
       ✉️ 봉투
    </a>
  </li>
  
  <li>
    <a href="/MlangPrintAuto/NcrFlambeau/index.php?page=NcrFlambeau" 
       class="form <?php echo isActive('/MlangPrintAuto/NcrFlambeau/', $current_page); ?>">
       📋 양식지
    </a>
  </li>
  
  <li>
    <a href="/MlangPrintAuto/cadarok/index.php" 
       class="catalog <?php echo isActive('/MlangPrintAuto/cadarok/', $current_page); ?>">
       📖 카다록
    </a>
  </li>
  
  <li>
    <a href="/MlangPrintAuto/LittlePrint/index_compact.php?page=LittlePrint" 
       class="poster <?php echo isActive('/MlangPrintAuto/LittlePrint/', $current_page); ?>">
       🎨 포스터
    </a>
  </li>
</ul>
<p>&nbsp;</p>