<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획인쇄 - 주문 조회 시스템</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .main-container {
            background: white;
            padding: 3rem;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 600px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradient 4s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .company-header {
            margin-bottom: 3rem;
        }
        
        .company-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .company-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .main-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .main-description {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 3rem;
        }
        
        .access-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .access-card {
            padding: 2rem 1.5rem;
            border-radius: 20px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .access-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .access-card:hover::before {
            left: 100%;
        }
        
        .customer-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 3px solid transparent;
        }
        
        .customer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }
        
        .admin-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #333;
            border: 3px solid #e2e8f0;
        }
        
        .admin-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .card-description {
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.4;
        }
        
        .features-section {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .features-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .features-title::before {
            content: '✨';
            margin-right: 0.5rem;
        }
        
        .features-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            list-style: none;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            color: #555;
            font-size: 0.9rem;
        }
        
        .feature-item::before {
            content: '🔸';
            margin-right: 0.5rem;
        }
        
        .contact-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
            color: #666;
        }
        
        .contact-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .contact-details {
            color: #667eea;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .access-options {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .features-list {
                grid-template-columns: 1fr;
            }
            
            .main-container {
                padding: 2rem 1.5rem;
                margin: 10px;
            }
            
            .company-logo {
                font-size: 2rem;
            }
            
            .main-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="company-header">
            <div class="company-logo">두손기획인쇄</div>
            <div class="company-subtitle">기획에서 인쇄까지 원스톱 서비스</div>
            <div class="main-title">주문 조회 시스템</div>
            <div class="main-description">
                고객님의 주문 상태와 시안을 안전하게 확인하세요.<br>
                개인정보 보호를 위해 본인 인증 후 이용 가능합니다.
            </div>
        </div>
        
        <div class="access-options">
            <a href="customer_login.php" class="access-card customer-card">
                <span class="card-icon">👤</span>
                <div class="card-title">고객 조회</div>
                <div class="card-description">
                    주문자명과 전화번호로<br>
                    나의 주문만 안전하게 조회
                </div>
            </a>
            
            <a href="checkboard_auth.php" class="access-card admin-card">
                <span class="card-icon">🔧</span>
                <div class="card-title">관리자 모드</div>
                <div class="card-description">
                    전체 주문 관리 및<br>
                    시스템 관리자 전용
                </div>
            </a>
        </div>
        
        <div class="features-section">
            <div class="features-title">시스템 특징</div>
            <ul class="features-list">
                <li class="feature-item">개인정보 완벽 보호</li>
                <li class="feature-item">본인 주문만 조회</li>
                <li class="feature-item">실시간 진행상황</li>
                <li class="feature-item">시안/교정 즉시 확인</li>
                <li class="feature-item">자동 보안 로그아웃</li>
                <li class="feature-item">모바일 완벽 지원</li>
            </ul>
        </div>
        
        <div class="contact-info">
            <div class="contact-title">문의사항이 있으시면</div>
            <div class="contact-details">
                📞 02-2632-1830 | 1688-2384<br>
                📍 서울 영등포구 영등포로36길 9, 송호빌딩 1F
            </div>
        </div>
    </div>
</body>
</html>