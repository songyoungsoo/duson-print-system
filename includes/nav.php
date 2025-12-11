<?php
/**
 * 공통 네비게이션 파일
 * 경로: includes/nav.php
 */

// 현재 페이지 확인을 위한 변수 (각 페이지에서 설정)
$current_page = isset($current_page) ? $current_page : '';
?>
<!-- 네비게이션 메뉴 -->
<div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 0.5rem; justify-content: center; padding: 1rem; max-width: 1200px; margin-left: auto; margin-right: auto; background: transparent !important;">
    <a href="/mlangprintauto/sticker_new/index.php"
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'sticker') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'"
       onmouseout="this.style.background='<?php echo ($current_page == 'sticker') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       🏷️ 스티커
    </a>
    
    <a href="/mlangprintauto/inserted/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'leaflet') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'leaflet') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       📄 전단지
    </a>
    
    <a href="/mlangprintauto/namecard/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'namecard') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'namecard') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       📇 명함
    </a>
    
    <a href="/mlangprintauto/envelope/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'envelope') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'envelope') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       ✉️ 봉투
    </a>
    
    <a href="/mlangprintauto/cadarok/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'cadarok') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'cadarok') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       📖 카다록
    </a>
    
    <a href="/mlangprintauto/littleprint/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'littleprint') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'littleprint') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       🎨 포스터
    </a>
    
    <a href="/mlangprintauto/ncrflambeau/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'ncrflambeau') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'ncrflambeau') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       📋 양식지
    </a>
    
    <a href="/mlangprintauto/merchandisebond/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'merchandisebond') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'merchandisebond') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       🎫 상품권
    </a>
    
    <a href="/mlangprintauto/msticker/index.php" 
       style="display: inline-block; padding: 10px 20px; background: <?php echo ($current_page == 'msticker') ? '#5a6268' : '#6c757d'; ?>; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" 
       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" 
       onmouseout="this.style.background='<?php echo ($current_page == 'msticker') ? '#5a6268' : '#6c757d'; ?>'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">
       🧲 자석스티커
    </a>
</div>