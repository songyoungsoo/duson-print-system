<?php 
session_start(); 
$session_id = session_id();
$HomeDir="../../";
include "../lib/func.php";
$connect = dbconn(); 
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏷️ 프리미엄 스티커 주문 시스템</title>
    <link rel="stylesheet" href="../css/modern-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 0.5rem;
        }
        
        .order-wizard {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: -3rem auto 3rem;
            max-width: 900px;
            position: relative;
        }
        
        .wizard-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .wizard-steps {
            display: flex;
            justify-content: center;
            padding: 2rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .step.active {
            color: #667eea;
            font-weight: 600;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .step.active .step-number {
            background: var(--primary-gradient);
            color: white;
        }
        
        .form-section {
            padding: 3rem;
        }
        
        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .option-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .option-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.1);
        }
        
        .option-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .option-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        
        .form-control-modern {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control-modern:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }
        
        .size-input-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .size-input {
            flex: 1;
            position: relative;
        }
        
        .size-input input {
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .size-label {
            position: absolute;
            top: -10px;
            left: 15px;
            background: white;
            padding: 0 8px;
            font-size: 0.8rem;
            color: #667eea;
            font-weight: 600;
        }
        
        .multiply-icon {
            font-size: 1.5rem;
            color: #6c757d;
        }
        
        .price-calculator {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .price-calculator::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }
        
        .price-display {
            position: relative;
            z-index: 1;
        }
        
        .price-amount {
            font-size: 3rem;
            font-weight: 700;
            margin: 1rem 0;
        }
        
        .price-vat {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn-modern {
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-modern:hover::before {
            left: 100%;
        }
        
        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        }
        
        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }
        
        .floating-cart {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--success-gradient);
            color: white;
            border-radius: 50px;
            padding: 1rem 1.5rem;
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .floating-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(79, 172, 254, 0.4);
        }
        
        .recent-orders {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .order-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .order-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        
        .order-details {
            flex: 1;
        }
        
        .order-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .order-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .order-price {
            font-weight: 700;
            color: #667eea;
            font-size: 1.1rem;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .notification {
            position: fixed;
            top: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 1001;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: var(--success-gradient);
        }
        
        .notification.error {
            background: var(--danger-gradient);
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .option-grid {
                grid-template-columns: 1fr;
            }
            
            .size-input-group {
                flex-direction: column;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .floating-cart {
                bottom: 1rem;
                right: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- 로딩 오버레이 -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- 알림 -->
    <div class="notification" id="notification"></div>
    
    <!-- 히어로 섹션 -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">🏷️ 프리미엄 스티커</h1>
                <p class="hero-subtitle">최고 품질의 스티커를 합리적인 가격으로 제작해드립니다</p>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number">99.9%</span>
                        <span class="stat-label">고객 만족도</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">24H</span>
                        <span class="stat-label">빠른 제작</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">완성된 주문</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container">
        <!-- 주문 마법사 -->
        <div class="order-wizard">
            <div class="wizard-header">
                <h2>스티커 주문 시작하기</h2>
                <p>간단한 몇 단계로 완벽한 스티커를 주문하세요</p>
            </div>
            
            <div class="wizard-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <span>옵션 선택</span>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <span>가격 확인</span>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span>장바구니</span>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <span>주문 완료</span>
                </div>
            </div>
            
            <div class="form-section">
                <form id="orderForm" method="post">
                    <input type="hidden" name="no" value="<?php echo htmlspecialchars($no ?? '', ENT_QUOTES, 'UTF-8')?>">
                    <input type="hidden" name="action" value="calculate">
                    
                    <div class="option-grid">
                        <!-- 재질 선택 -->
                        <div class="option-card">
                            <div class="option-icon">🎨</div>
                            <h3 class="option-title">재질 선택</h3>
                            <select name="jong" class="form-control-modern">
                                <option value="jil 아트유광코팅">✨ 아트지유광코팅 (90g)</option>
                                <option value="jil 아트무광코팅">🌟 아트지무광코팅 (90g)</option>
                                <option value="jil 아트비코팅">💫 아트지비코팅 (90g)</option>
                                <option value="jka 강접아트유광코팅">💪 강접아트유광코팅 (90g)</option>
                                <option value="cka 초강접아트코팅">🔥 초강접아트유광코팅 (90g)</option>
                                <option value="cka 초강접아트비코팅">⚡ 초강접아트비코팅 (90g)</option>
                                <option value="jsp 유포지">📄 유포지 (80g)</option>
                                <option value="jsp 은데드롱">🌙 은데드롱 (25g)</option>
                                <option value="jsp 투명스티커">💎 투명스티커 (25g)</option>
                                <option value="jil 모조비코팅">📋 모조지비코팅 (80g)</option>
                                <option value="jsp 크라프트지">🌿 크라프트스티커 (57g)</option>
                            </select>
                            <small style="color: #6c757d; margin-top: 0.5rem; display: block;">
                                프리미엄 재질로 최고의 품질을 보장합니다
                            </small>
                        </div>
                        
                        <!-- 사이즈 선택 -->
                        <div class="option-card">
                            <div class="option-icon">📏</div>
                            <h3 class="option-title">사이즈 설정</h3>
                            <div class="size-input-group">
                                <div class="size-input">
                                    <div class="size-label">가로 (mm)</div>
                                    <input type="number" name="garo" class="form-control-modern" min="1" max="590" placeholder="100">
                                </div>
                                <div class="multiply-icon">×</div>
                                <div class="size-input">
                                    <div class="size-label">세로 (mm)</div>
                                    <input type="number" name="sero" class="form-control-modern" min="1" max="590" placeholder="150">
                                </div>
                            </div>
                            <small style="color: #6c757d; margin-top: 0.5rem; display: block;">
                                최대 590mm까지 제작 가능합니다
                            </small>
                        </div>
                        
                        <!-- 수량 선택 -->
                        <div class="option-card">
                            <div class="option-icon">📦</div>
                            <h3 class="option-title">수량 선택</h3>
                            <select name="mesu" class="form-control-modern">
                                <option value="500">500매</option>
                                <option value="1000" selected>1,000매 (추천)</option>
                                <option value="2000">2,000매</option>
                                <option value="3000">3,000매</option>
                                <option value="4000">4,000매</option>
                                <option value="5000">5,000매</option>
                                <option value="10000">10,000매 (대량할인)</option>
                            </select>
                            <small style="color: #6c757d; margin-top: 0.5rem; display: block;">
                                수량이 많을수록 단가가 저렴해집니다
                            </small>
                        </div>
                        
                        <!-- 편집 옵션 -->
                        <div class="option-card">
                            <div class="option-icon">✏️</div>
                            <h3 class="option-title">편집 서비스</h3>
                            <select name="uhyung" class="form-control-modern">
                                <option value="0">인쇄만 (파일 준비완료)</option>
                                <option value="10000">디자인 + 인쇄 (+10,000원)</option>
                            </select>
                            <small style="color: #6c757d; margin-top: 0.5rem; display: block;">
                                전문 디자이너가 직접 작업해드립니다
                            </small>
                        </div>
                        
                        <!-- 모양 선택 -->
                        <div class="option-card">
                            <div class="option-icon">✂️</div>
                            <h3 class="option-title">모양 선택</h3>
                            <select name="domusong" class="form-control-modern">
                                <option value="00000 사각">⬜ 기본 사각형</option>
                                <option value="08000 사각도무송">📐 사각 도무송</option>
                                <option value="08000 귀돌">🔄 귀돌이 (라운드)</option>
                                <option value="08000 원형">⭕ 원형</option>
                                <option value="08000 타원">🥚 타원형</option>
                                <option value="19000 복잡">🎨 특수 모양 (별도견적)</option>
                            </select>
                            <small style="color: #6c757d; margin-top: 0.5rem; display: block;">
                                다양한 모양으로 개성있는 스티커를 만들어보세요
                            </small>
                        </div>
                    </div>
                    
                    <div class="text-center" style="margin: 3rem 0;">
                        <button type="button" onclick="calculatePrice()" class="btn-modern btn-primary-modern">
                            💰 실시간 가격 계산하기
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 가격 계산 결과 -->
        <div id="priceSection" class="price-calculator" style="display: none;">
            <div class="price-display">
                <h3>💎 견적 결과</h3>
                <div class="price-amount" id="priceAmount">0원</div>
                <div class="price-vat">부가세 포함: <span id="priceVat">0원</span></div>
                
                <div class="action-buttons">
                    <button onclick="addToBasket()" class="btn-modern btn-success-modern">
                        🛒 장바구니에 담기
                    </button>
                    <button onclick="goToCart()" class="btn-modern" style="background: rgba(255,255,255,0.2); color: white;">
                        👀 장바구니 보기
                    </button>
                </div>
            </div>
        </div>
        
        <!-- 최근 주문 내역 -->
        <div class="recent-orders">
            <h3 style="margin-bottom: 2rem; color: #2c3e50;">📋 최근 주문 내역</h3>
            <div id="recentOrdersList">
                <?php
                $query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC LIMIT 5";  
                $result = mysqli_query($connect, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($data = mysqli_fetch_array($result)) {
                        $domusong_parts = explode(' ', $data['domusong'], 2);
                        $domusong_name = isset($domusong_parts[1]) ? $domusong_parts[1] : $data['domusong'];
                        ?>
                        <div class="order-item">
                            <div class="order-icon">🏷️</div>
                            <div class="order-details">
                                <div class="order-title">
                                    <?php echo substr($data['jong'], 4, 12); ?> 
                                    (<?php echo $data['garo']; ?>×<?php echo $data['sero']; ?>mm)
                                </div>
                                <div class="order-meta">
                                    <?php echo number_format($data['mesu']); ?>매 · <?php echo htmlspecialchars($domusong_name); ?> · 
                                    <?php echo date('Y-m-d H:i', $data['regdate']); ?>
                                </div>
                            </div>
                            <div class="order-price">
                                <?php echo number_format($data['st_price_vat']); ?>원
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div style="text-align: center; padding: 3rem; color: #6c757d;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                        <h4>아직 주문 내역이 없습니다</h4>
                        <p>첫 번째 스티커 주문을 시작해보세요!</p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- 플로팅 장바구니 -->
    <div class="floating-cart" onclick="goToCart()">
        🛒 장바구니
    </div>
    
    <script>
    // 전역 변수
    let currentStep = 1;
    
    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 애니메이션 효과
        const cards = document.querySelectorAll('.option-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }, index * 100);
        });
    });
    
    // 알림 표시 함수
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }
    
    // 로딩 표시/숨김
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
    
    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }
    
    // 단계 업데이트
    function updateStep(step) {
        const steps = document.querySelectorAll('.step');
        steps.forEach((stepEl, index) => {
            if (index < step) {
                stepEl.classList.add('active');
            } else {
                stepEl.classList.remove('active');
            }
        });
        currentStep = step;
    }
    
    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // 필수 입력값 체크
        if (!formData.get('garo') || !formData.get('sero')) {
            showNotification('가로, 세로 크기를 입력해주세요.', 'error');
            return;
        }
        
        // action 파라미터 추가
        formData.set('action', 'calculate');
        
        showLoading();
        updateStep(2);
        
        // AJAX로 가격 계산 요청
        fetch('calculate_price.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // 계산 결과 표시
                document.getElementById('priceAmount').textContent = data.price + '원';
                document.getElementById('priceVat').textContent = data.price_vat + '원';
                
                // 가격 섹션 표시
                const priceSection = document.getElementById('priceSection');
                priceSection.style.display = 'block';
                priceSection.scrollIntoView({ behavior: 'smooth' });
                
                showNotification('가격이 계산되었습니다! 🎉');
            } else {
                showNotification('가격 계산 중 오류가 발생했습니다: ' + data.message, 'error');
                updateStep(1);
            }
        })
        .catch(error => {
            hideLoading();
            updateStep(1);
            console.error('Error:', error);
            showNotification('가격 계산 중 오류가 발생했습니다.', 'error');
        });
    }
    
    // 장바구니에 추가하는 함수
    function addToBasket() {
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // 필수 입력값 체크
        if (!formData.get('garo') || !formData.get('sero')) {
            showNotification('가로, 세로 크기를 입력해주세요.', 'error');
            return;
        }
        
        // action 파라미터 추가
        formData.set('action', 'add_to_basket');
        
        showLoading();
        updateStep(3);
        
        // AJAX로 장바구니에 추가
        fetch('add_to_basket_safe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showNotification('장바구니에 추가되었습니다! 🛒');
                
                // 장바구니 확인 여부 묻기
                setTimeout(() => {
                    if (confirm('장바구니를 확인하시겠습니까?')) {
                        updateStep(4);
                        window.location.href = 'cart.php';
                    } else {
                        // 폼 초기화하고 계속 쇼핑
                        document.getElementById('orderForm').reset();
                        document.getElementById('priceSection').style.display = 'none';
                        updateStep(1);
                        showNotification('계속 쇼핑하세요! 😊');
                    }
                }, 1000);
            } else {
                showNotification('장바구니 추가 중 오류가 발생했습니다: ' + data.message, 'error');
                updateStep(2);
            }
        })
        .catch(error => {
            hideLoading();
            updateStep(2);
            console.error('Error:', error);
            showNotification('장바구니 추가 중 오류가 발생했습니다.', 'error');
        });
    }
    
    // 장바구니 페이지로 이동
    function goToCart() {
        updateStep(4);
        window.location.href = 'cart.php';
    }
    
    // 입력값 변경 시 실시간 유효성 검사
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('change', function() {
            if (this.checkValidity()) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        });
    });
    
    // 키보드 단축키
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'Enter':
                    e.preventDefault();
                    calculatePrice();
                    break;
                case 'b':
                    e.preventDefault();
                    if (document.getElementById('priceSection').style.display !== 'none') {
                        addToBasket();
                    }
                    break;
            }
        }
    });
    </script>
</body>
</html>

<?php
if ($connect) {
    mysqli_close($connect);
}
?>