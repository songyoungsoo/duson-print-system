<!--------------------------------------------------------------------------------
     디자인 편집툴-포토샵8.0, 플래쉬MX
     프로그램 제작툴-에디터플러스2
     프로그램언어: PHP, javascript, DHTML, html
     제작자: Mlang - 메일: webmaster@script.ne.kr
     URL: http://www.websil.net , http://www.script.ne.kr

* 현 사이트는 MYSQLDB(MySql데이터베이스) 화 작업되어져 있는 홈페이지 입니다.
* 홈페이지의 해킹, 사고등으로 자료가 없어질시 5분안에 복구가 가능합니다.
* 현사이트는 PHP프로그램화 되어져 있음으로 웹초보자가 자료를 수정/삭제 가능합니다.
* 페이지 수정시 의뢰자가 HTML에디터 추가를 원하면 프로그램을 지원합니다.
* 모든 페이지는 웹상에서 관리할수 있습니다.

   홈페이지 제작/상담: ☏ 010-8946-7038, 임태희 (전화안받을시 문자를주셔염*^^*)
   전화를 안받으면 다른 전화번호로 변경된 경우일수 있습니다...
   그럴경우는 http://www.websil.net 홈페이지에 방문하시면 메인 페이지에 전화번호가 공개 되어있음으로
   언제든지 부담없이 전화 하여 주시기 바랍니다.... 감사합니다.*^^*
----------------------------------------------------------------------------------->

<?php
$SoftUrl="/MlangPrintAuto";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🖨️ 두손기획인쇄 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
        }
        
        /* 상단 헤더 */
        .top-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .company-info h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .company-info p {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .contact-info {
            display: flex;
            gap: 30px;
        }
        
        .contact-card {
            text-align: right;
            padding: 15px 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .contact-card .label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .contact-card .value {
            font-weight: 700;
            font-size: 1.2rem;
            color: #3498db;
        }
        
        /* 네비게이션 메뉴 */
        .nav-menu {
            background: white;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .nav-links {
            display: flex;
            justify-content: center;
            gap: 0;
            overflow-x: auto;
        }
        
        .nav-link {
            padding: 18px 25px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-link:hover {
            color: #3498db;
            border-bottom-color: #3498db;
            background: rgba(52, 152, 219, 0.05);
        }
        
        .nav-link.active {
            color: #3498db;
            border-bottom-color: #3498db;
            background: rgba(52, 152, 219, 0.1);
            font-weight: 700;
        }
        
        /* 메인 컨테이너 */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* 사이드바 */
        .sidebar {
            width: 160px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        /* 메인 콘텐츠 영역 */
        .main-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            min-height: 600px;
        }
        
        .content-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }
        
        .content-body {
            padding: 2rem;
        }
        
        /* 박스 메뉴 스타일 */
        .box-menu {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .box-menu ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        
        .box-menu li {
            margin: 0;
        }
        
        .box-menu a {
            display: inline-block;
            padding: 12px 20px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .box-menu a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
        }
        
        /* 레이아웃 조정 */
        .layout-wrapper {
            display: flex;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .main-wrapper {
            flex: 1;
        }
        
        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .layout-wrapper {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .box-menu ul {
                flex-direction: column;
                align-items: center;
            }
            
            .box-menu a {
                width: 200px;
                text-align: center;
            }
        }
    </style>
</head>

<body>
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
                <div class="contact-card">
                    <div class="label">⏰ 운영시간</div>
                    <div class="value">평일 09:00-18:00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 네비게이션 메뉴 -->
    <div class="nav-menu">
        <div class="nav-content">
            <div class="nav-links">
                <a href="/mlangprintauto/inserted/index.php" class="nav-link">📄 전단지</a>
                <a href="/shop/view_modern.php" class="nav-link">🏷️ 스티커</a>
                <a href="/mlangprintauto/cadarok/index.php" class="nav-link">📖 카다록</a>
                <a href="/mlangprintauto/namecard/index.php" class="nav-link">📇 명함</a>
                <a href="/mlangprintauto/merchandisebond/index.php" class="nav-link">🎫 상품권</a>
                <a href="/mlangprintauto/envelope/index.php" class="nav-link">✉️ 봉투</a>
                <a href="/mlangprintauto/ncrflambeau/index.php" class="nav-link">📄 양식/서식</a>
                <a href="/mlangprintauto/littleprint/index.php" class="nav-link">🎨 포스터</a>
                <a href="/mlangprintauto/shop/cart.php" class="nav-link">🛒 장바구니</a>
            </div>
        </div>
    </div>

    <!-- 메인 레이아웃 -->
    <div class="layout-wrapper">
        <!-- 사이드바 -->
        <aside class="sidebar">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/left.php"; ?>
        </aside>
        
        <!-- 메인 콘텐츠 -->
        <div class="main-wrapper">
            <div class="main-content">
                <div class="content-header">
                    <h2>🖨️ 자동 견적 시스템</h2>
                    <p>원하시는 인쇄물을 선택하여 실시간으로 견적을 확인해보세요</p>
                </div>
                
                <div class="content-body">
                    <!-- 서비스 메뉴 -->
                    <nav class="box-menu">
                        <ul>
                            <li><a href="/mlangprintauto/inserted/index.php">📄 전단지</a></li>
                            <li><a href="/shop/view_modern.php">🏷️ 스티커</a></li>
                            <li><a href="/mlangprintauto/cadarok/index.php">📖 카다록</a></li>
                            <li><a href="/mlangprintauto/namecard/index.php">📇 명함</a></li>
                            <li><a href="/mlangprintauto/merchandisebond/index.php">🎫 상품권</a></li>
                            <li><a href="/mlangprintauto/envelope/index.php">✉️ 봉투</a></li>
                            <li><a href="/mlangprintauto/ncrflambeau/index.php" class="nav-link">📄 양식/서식</a>
                            <li><a href="/mlangprintauto/littleprint/index.php">🎨 포스터</a></li>
                            <li><a href="/mlangprintauto/shop/cart.php">🛒 장바구니</a></li>
                        </ul>
                    </nav>
                    
                    <!-- 콘텐츠 영역 시작 -->
                    <!-- 여기에 각 페이지의 콘텐츠가 들어갑니다 -->
                </div>
            </div>
        </div>
    </div>

    <!-- 푸터 -->
    <footer style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; margin-top: 4rem; border-top: 4px solid #3498db;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 3rem 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
            <div>
                <h3 style="color: #3498db; font-size: 1.3rem; margin-bottom: 1.5rem; font-weight: 700;">🖨️ 두손기획인쇄</h3>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📍 주소: 서울시 영등포구 영등포로 36길 9 송호빌딩 1층</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📞 전화: 1688-2384</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📠 팩스: 02-2632-1829</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">✉️ 이메일: dsp1830@naver.com</p>
            </div>

            <div>
                <h4 style="color: #3498db; font-size: 1.3rem; margin-bottom: 1.5rem; font-weight: 700;">🎯 주요 서비스</h4>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🏷️ 스티커 제작</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📇 명함 인쇄</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📖 카다록 제작</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🎨 포스터 인쇄</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📄 각종 인쇄물</p>
            </div>

            <div>
                <h4 style="color: #3498db; font-size: 1.3rem; margin-bottom: 1.5rem; font-weight: 700;">⏰ 운영 안내</h4>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">평일: 09:00 - 18:00</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">토요일: 09:00 - 15:00</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">일요일/공휴일: 휴무</p>
                <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">점심시간: 12:00 - 13:00</p>
            </div>
        </div>
        
        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding: 2rem 20px; text-align: center; background: rgba(0,0,0,0.2);">
            <p style="color: #bdc3c7; font-size: 0.95rem;">© 2024 두손기획인쇄. All rights reserved. | 제작: Mlang (010-8946-7038)</p>
        </div>
    </footer>

    <script>
    // 페이지 상태 저장 및 복원 기능
    function savePageState() {
        const pageState = {
            scrollPosition: window.pageYOffset || document.documentElement.scrollTop,
            currentPage: window.location.pathname,
            timestamp: Date.now()
        };
        
        // localStorage에 저장 (24시간 유효)
        localStorage.setItem('printAutoPageState', JSON.stringify(pageState));
    }
    
    function restorePageState() {
        try {
            const savedState = localStorage.getItem('printAutoPageState');
            if (!savedState) return;
            
            const pageState = JSON.parse(savedState);
            
            // 24시간이 지났으면 삭제
            if (Date.now() - pageState.timestamp > 24 * 60 * 60 * 1000) {
                localStorage.removeItem('printAutoPageState');
                return;
            }
            
            // 같은 페이지인 경우에만 스크롤 위치 복원
            if (pageState.currentPage === window.location.pathname) {
                // 스크롤 위치 복원 (약간의 지연을 두어 페이지 로딩 완료 후 실행)
                setTimeout(() => {
                    if (pageState.scrollPosition > 0) {
                        window.scrollTo({
                            top: pageState.scrollPosition,
                            behavior: 'smooth'
                        });
                        
                        // 복원 알림 표시
                        showRestoreNotification();
                    }
                }, 200);
            }
            
        } catch (error) {
            console.error('페이지 상태 복원 중 오류:', error);
            localStorage.removeItem('printAutoPageState');
        }
    }
    
    function showRestoreNotification() {
        const notification = document.createElement('div');
        notification.innerHTML = '📍 이전 위치로 복원되었습니다';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            z-index: 10000;
            font-weight: 600;
            font-size: 14px;
            animation: slideIn 0.5s ease-out;
        `;
        
        // 애니메이션 CSS 추가
        if (!document.getElementById('restoreAnimationStyle')) {
            const style = document.createElement('style');
            style.id = 'restoreAnimationStyle';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        // 3초 후 알림 제거
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.5s ease-in';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }
    
    // 페이지 로드 시 상태 복원
    document.addEventListener('DOMContentLoaded', restorePageState);
    
    // 페이지 언로드 시 상태 저장
    window.addEventListener('beforeunload', savePageState);
    
    // 스크롤 시 주기적으로 위치 저장 (성능을 위해 throttling 적용)
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(savePageState, 200);
    });
    
    // 메뉴 클릭 시 상태 저장
    document.querySelectorAll('.nav-link, .box-menu a').forEach(link => {
        link.addEventListener('click', function() {
            savePageState();
        });
    });
    
    // 페이지 새로고침 감지
    if (performance.navigation.type === performance.navigation.TYPE_RELOAD) {
        // 새로고침 시에도 복원 기능 작동
        setTimeout(restorePageState, 100);
    }
    
    // 브라우저 뒤로가기/앞으로가기 시 상태 복원
    window.addEventListener('popstate', function() {
        setTimeout(restorePageState, 100);
    });
    
    // 활성 메뉴 표시 기능
    function setActiveMenu() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    }
    
    // 페이지 로드 시 활성 메뉴 설정
    document.addEventListener('DOMContentLoaded', setActiveMenu);
    </script>

</body>
</html>