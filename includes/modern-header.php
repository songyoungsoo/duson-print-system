<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '🖨️ 프리미엄 인쇄 주문 시스템'; ?></title>
    <link rel="stylesheet" href="../css/modern-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* 전체 레이아웃 스타일 */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .main-layout {
            display: flex;
            min-height: 100vh;
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .sidebar {
            flex: 0 0 220px;
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .main-content {
            flex: 1;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        
        /* 공통 헤더 스타일 */
        .modern-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .modern-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .modern-header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .modern-header p {
            font-size: 1.3rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            font-weight: 400;
        }
        
        /* 공통 네비게이션 */
        .modern-nav {
            background: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .nav-link {
            padding: 10px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #495057;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .nav-link:hover {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }
        
        .nav-actions {
            display: flex;
            gap: 1rem;
        }
        
        .nav-action-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .nav-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
        }
        
        .nav-action-btn.cart {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .nav-action-btn.cart:hover {
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
        }
        
        /* 반응형 디자인 */
        @media (max-width: 1024px) {
            .main-layout {
                flex-direction: column;
                padding: 10px;
            }
            
            .sidebar {
                flex: none;
                position: static;
            }
            
            .modern-header h1 {
                font-size: 2.2rem;
            }
            
            .modern-header p {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 768px) {
            .modern-nav {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-links {
                justify-content: center;
            }
            
            .modern-header {
                padding: 2rem 1rem;
            }
            
            .modern-header h1 {
                font-size: 1.8rem;
            }
            
            .modern-header p {
                font-size: 1rem;
            }
        }
        
        /* 페이지별 추가 스타일 */
        <?php if (isset($additional_css)) echo $additional_css; ?>
    </style>
</head>
<body>
    <div class="main-layout">
        <!-- 사이드바 네비게이션 -->
        <div class="sidebar">
            <?php include '../left.php'; ?>
        </div>
        
        <!-- 메인 콘텐츠 -->
        <div class="main-content">
            <!-- 공통 헤더 -->
            <div class="modern-header">
                <h1><?php echo $header_title ?? '🖨️ 프리미엄 인쇄 서비스'; ?></h1>
                <p><?php echo $header_subtitle ?? '전문적이고 고품질의 인쇄물을 합리적인 가격으로 제작해드립니다'; ?></p>
            </div>
            
            <!-- 공통 네비게이션 -->
            <div class="modern-nav">
                <div class="nav-links">
                    <a href="../shop/view_modern.php" class="nav-link <?php echo ($current_page == 'sticker') ? 'active' : ''; ?>">🏷️ 스티커</a>
                    <a href="../MlangPrintAuto/NameCard/index_modern.php" class="nav-link <?php echo ($current_page == 'namecard') ? 'active' : ''; ?>">📇 명함</a>
                    <a href="../MlangPrintAuto/cadarok/index_modern.php" class="nav-link <?php echo ($current_page == 'catalog') ? 'active' : ''; ?>">📖 카다록</a>
                    <a href="../MlangPrintAuto/LittlePrint/index.php" class="nav-link <?php echo ($current_page == 'poster') ? 'active' : ''; ?>">🎨 포스터</a>
                </div>
                
                <div class="nav-actions">
                    <a href="../shop/cart.php" class="nav-action-btn cart">🛒 장바구니</a>
                    <a href="../shop/order.php" class="nav-action-btn">📋 주문현황</a>
                </div>
            </div>
            
            <!-- 페이지 콘텐츠 시작 -->