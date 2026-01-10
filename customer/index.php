<?php
/**
 * 고객센터 메인 페이지
 * Duson Planning Print - Customer Center
 */
session_start();
require_once '../db.php';

$current_page = 'customer_center';
$page_title = '두손기획인쇄 - 고객센터';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="customer-center.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header */
        .site-header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0066cc;
            text-decoration: none;
        }

        .header-nav a {
            margin-left: 2rem;
            color: #666;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .header-nav a:hover {
            color: #0066cc;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 20px;
        }

        /* Tab Navigation */
        .tab-navigation {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .tab-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 1rem 2rem;
            background: #f5f7fa;
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
            flex: 1;
            min-width: 150px;
            text-align: center;
        }

        .tab-btn:hover {
            background: #e8ecf1;
            border-color: #0066cc;
        }

        .tab-btn.active {
            background: #0066cc;
            color: white;
            border-color: #0066cc;
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Content Cards */
        .content-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .content-section h2 {
            color: #0066cc;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #0066cc;
        }

        .content-section h3 {
            color: #333;
            font-size: 1.3rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .step-list {
            counter-reset: step-counter;
            list-style: none;
        }

        .step-list li {
            counter-increment: step-counter;
            position: relative;
            padding: 1.5rem;
            padding-left: 4rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #0066cc;
        }

        .step-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 2rem;
            height: 2rem;
            background: #0066cc;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .info-card h4 {
            color: #0066cc;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .info-card p {
            color: #666;
            line-height: 1.8;
        }

        /* FAQ Accordion */
        .faq-item {
            background: white;
            margin-bottom: 1rem;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .faq-question {
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            transition: background 0.3s;
        }

        .faq-question:hover {
            background: #e8ecf1;
        }

        .faq-question h4 {
            color: #333;
            font-size: 1.1rem;
            margin: 0;
        }

        .faq-icon {
            font-size: 1.5rem;
            color: #0066cc;
            transition: transform 0.3s;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background: white;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
        }

        .faq-answer-content {
            padding: 1.5rem;
            color: #666;
            line-height: 1.8;
        }

        /* Contact Section */
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .contact-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
        }

        .contact-card h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .contact-card p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .contact-card a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        /* Footer */
        .site-footer {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 2rem 0;
            margin-top: 4rem;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 1.8rem;
            }

            .tab-btn {
                min-width: 100%;
            }

            .step-list li {
                padding-left: 3.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-content">
            <a href="/" class="logo">🖨️ 두손기획인쇄</a>
            <nav class="header-nav">
                <a href="/">홈</a>
                <a href="/mlangprintauto/shop/cart.php">장바구니</a>
                <a href="/mlangorder_printauto/OrderList_universal.php">주문조회</a>
                <a href="/customer/" class="active">고객센터</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>🎯 고객센터</h1>
            <p>두손기획인쇄를 이용해주셔서 감사합니다. 무엇을 도와드릴까요?</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="showTab('order-guide')">📝 주문 가이드</button>
                <button class="tab-btn" onclick="showTab('faq')">❓ 자주 묻는 질문</button>
                <button class="tab-btn" onclick="showTab('shipping')">🚚 배송 안내</button>
                <button class="tab-btn" onclick="showTab('payment')">💳 결제 안내</button>
                <button class="tab-btn" onclick="showTab('contact')">📞 문의하기</button>
            </div>
        </div>

        <!-- Tab Contents -->

        <!-- 주문 가이드 -->
        <div id="order-guide" class="tab-content active">
            <div class="content-section">
                <h2>📝 주문 가이드</h2>

                <h3>🎯 주문 프로세스</h3>
                <ol class="step-list">
                    <li>
                        <strong>상품 선택</strong><br>
                        원하시는 인쇄물 종류를 선택하세요 (전단지, 명함, 봉투, 스티커 등)
                    </li>
                    <li>
                        <strong>옵션 설정</strong><br>
                        용지, 사이즈, 수량, 코팅 등 상세 옵션을 설정하세요
                    </li>
                    <li>
                        <strong>파일 업로드</strong><br>
                        인쇄할 디자인 파일을 업로드하세요 (AI, PDF, JPG 등)
                    </li>
                    <li>
                        <strong>장바구니 담기</strong><br>
                        설정한 옵션과 파일을 확인 후 장바구니에 담으세요
                    </li>
                    <li>
                        <strong>주문 완료</strong><br>
                        주문 정보를 입력하고 결제를 진행하세요
                    </li>
                    <li>
                        <strong>제작 및 배송</strong><br>
                        주문 확인 후 제작이 시작되며, 완료 후 배송됩니다
                    </li>
                </ol>

                <h3>📁 파일 업로드 가이드</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <h4>✅ 권장 파일 형식</h4>
                        <p>• AI (Adobe Illustrator)<br>
                           • PDF (고해상도)<br>
                           • PSD (포토샵)<br>
                           • JPG/PNG (300dpi 이상)</p>
                    </div>
                    <div class="info-card">
                        <h4>📏 파일 사이즈</h4>
                        <p>• 최대 파일 크기: 15MB<br>
                           • 재단선 포함 제작<br>
                           • 여백 3mm 이상 권장</p>
                    </div>
                    <div class="info-card">
                        <h4>🎨 디자인 주의사항</h4>
                        <p>• CMYK 컬러 모드 사용<br>
                           • 폰트는 아웃라인 처리<br>
                           • 이미지는 고해상도 권장</p>
                    </div>
                    <div class="info-card">
                        <h4>⚠️ 검수 사항</h4>
                        <p>• 오탈자 확인 필수<br>
                           • 재단선 확인<br>
                           • 색상 모니터 차이 가능</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 자주 묻는 질문 -->
        <div id="faq" class="tab-content">
            <div class="content-section">
                <h2>❓ 자주 묻는 질문 (FAQ)</h2>

                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <h4>Q. 주문 후 제작 기간은 얼마나 걸리나요?</h4>
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p><strong>일반 인쇄물:</strong> 영업일 기준 2-3일<br>
                            <strong>명함:</strong> 영업일 기준 1-2일<br>
                            <strong>대량 주문:</strong> 영업일 기준 3-5일<br>
                            <strong>특수 가공:</strong> 영업일 기준 5-7일</p>
                            <p>※ 주말 및 공휴일은 제작일에서 제외됩니다.<br>
                            ※ 파일 검수 및 확정 후 제작이 시작됩니다.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <h4>Q. 배송비는 얼마인가요?</h4>
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p><strong>일반 배송:</strong> 3,000원<br>
                            <strong>무료 배송:</strong> 5만원 이상 구매 시<br>
                            <strong>퀵서비스:</strong> 지역별 상이 (별도 문의)</p>
                            <p>※ 도서산간 지역은 추가 배송비가 발생할 수 있습니다.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <h4>Q. 디자인 파일이 없는데 제작 가능한가요?</h4>
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>네, 가능합니다! 디자인 서비스를 제공하고 있습니다.</p>
                            <p><strong>디자인 서비스:</strong><br>
                            • 명함 디자인: 10,000원~<br>
                            • 전단지 디자인: 30,000원~<br>
                            • 봉투 디자인: 20,000원~</p>
                            <p>주문 시 "디자인 포함" 옵션을 선택하시고,<br>
                            원하시는 디자인 컨셉을 상세히 알려주시면<br>
                            전문 디자이너가 제작해드립니다.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <h4>Q. 주문 취소나 환불이 가능한가요?</h4>
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p><strong>주문 취소 가능:</strong><br>
                            • 제작 시작 전까지 100% 환불<br>
                            • 고객센터로 연락 주시면 즉시 처리</p>
                            <p><strong>부분 환불/교환:</strong><br>
                            • 인쇄 불량: 100% 재제작 또는 환불<br>
                            • 배송 지연: 배송비 환불<br>
                            • 단순 변심: 제작 후 환불 불가</p>
                            <p>※ 맞춤 제작 상품 특성상 제작 시작 후<br>
                            취소/환불이 어려우니 주문 전 신중히 확인해주세요.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <h4>Q. 견적서 발행이 가능한가요?</h4>
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>네, 견적서 및 세금계산서 발행이 가능합니다.</p>
                            <p><strong>견적서:</strong> 주문 전 고객센터 문의<br>
                            <strong>세금계산서:</strong> 주문 시 사업자 정보 입력<br>
                            <strong>현금영수증:</strong> 결제 시 신청 가능</p>
                            <p>※ 사업자 정보가 필요한 경우 미리 준비해주세요.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <h4>Q. 소량 주문도 가능한가요?</h4>
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <p>네, 소량 주문도 환영합니다!</p>
                            <p><strong>최소 주문 수량:</strong><br>
                            • 명함: 100매~<br>
                            • 전단지: 100매~<br>
                            • 봉투: 100매~<br>
                            • 스티커: 50매~</p>
                            <p>소량이라도 품질은 동일하게 제작됩니다.<br>
                            부담 없이 주문하세요!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 배송 안내 -->
        <div id="shipping" class="tab-content">
            <div class="content-section">
                <h2>🚚 배송 안내</h2>

                <h3>📦 배송 방법</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <h4>일반 택배 (착불)</h4>
                        <p>• 배송비: <strong style="color: #e74c3c;">착불</strong><br>
                           • 택배사가 직접 수령 시 청구<br>
                           • 기간: 2-3일</p>
                    </div>
                    <div class="info-card">
                        <h4>등기 우편</h4>
                        <p>• 배송비: 착불 또는 선불<br>
                           • 소량 주문 추천<br>
                           • 기간: 3-4일</p>
                    </div>
                    <div class="info-card">
                        <h4>퀵 서비스</h4>
                        <p>• 배송비: 지역별 상이<br>
                           • 당일 배송 가능<br>
                           • 사전 문의 필수</p>
                    </div>
                    <div class="info-card">
                        <h4>직접 수령</h4>
                        <p>• 배송비: 무료<br>
                           • 매장 방문 수령<br>
                           • 사전 연락 권장</p>
                    </div>
                </div>

                <h3>📍 배송 지역 및 기간</h3>
                <ol class="step-list">
                    <li>
                        <strong>서울/경기 지역</strong><br>
                        출고 후 1-2일 소요 (영업일 기준)
                    </li>
                    <li>
                        <strong>수도권 외 지역</strong><br>
                        출고 후 2-3일 소요 (영업일 기준)
                    </li>
                    <li>
                        <strong>제주/도서산간</strong><br>
                        출고 후 3-5일 소요, 추가 배송비 발생 가능
                    </li>
                </ol>

                <h3>⚠️ 배송 관련 유의사항</h3>
                <div class="info-card">
                    <p>• <strong style="color: #e74c3c;">택배는 기본이 착불입니다.</strong> 선불을 원하시면 주문 시 요청해주세요.<br>
                       • 주문 폭주 시 배송이 지연될 수 있습니다.<br>
                       • 배송 주소는 정확히 입력해주세요.<br>
                       • 수취인 연락처는 항상 연락 가능한 번호로 입력해주세요.<br>
                       • 배송 완료 후 운송장 번호가 문자로 전송됩니다.<br>
                       • 주말/공휴일은 배송일에서 제외됩니다.</p>
                </div>
            </div>
        </div>

        <!-- 결제 안내 -->
        <div id="payment" class="tab-content">
            <div class="content-section">
                <h2>💳 결제 안내</h2>

                <h3>💰 결제 수단</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <h4>신용카드</h4>
                        <p>• 모든 카드사 가능<br>
                           • 할부 결제 지원<br>
                           • 즉시 결제 확인</p>
                    </div>
                    <div class="info-card">
                        <h4>계좌이체</h4>
                        <p>• 실시간 계좌이체<br>
                           • 수수료 없음<br>
                           • 즉시 입금 확인</p>
                    </div>
                    <div class="info-card">
                        <h4>무통장입금</h4>
                        <p>• 입금 확인 후 제작<br>
                           • 2일 내 입금 필수<br>
                           • 입금자명 일치 확인</p>
                    </div>
                    <div class="info-card">
                        <h4>간편결제</h4>
                        <p>• 카카오페이<br>
                           • 네이버페이<br>
                           • 토스</p>
                    </div>
                </div>

                <h3>🏦 무통장 입금 계좌</h3>
                <div class="info-card">
                    <h4>입금 계좌 정보</h4>
                    <p><strong>국민은행:</strong> 999-1688-2384<br>
                       <strong>신한은행:</strong> 110-342-543507<br>
                       <strong>농협:</strong> 301-2632-1830-11<br>
                       <strong>예금주:</strong> 두손기획인쇄 차경선<br><br>
                       ※ 입금 시 주문자명과 입금자명을 동일하게 해주세요.<br>
                       ※ 주문 후 2일 이내 입금하지 않으면 자동 취소됩니다.</p>
                </div>

                <h3>🧾 세금계산서 / 현금영수증</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <h4>세금계산서</h4>
                        <p>• 사업자 정보 입력<br>
                           • 주문 시 신청<br>
                           • 이메일 발송</p>
                    </div>
                    <div class="info-card">
                        <h4>현금영수증</h4>
                        <p>• 결제 시 신청<br>
                           • 휴대폰/사업자 번호<br>
                           • 국세청 조회 가능</p>
                    </div>
                </div>

                <h3>💡 결제 관련 유의사항</h3>
                <div class="info-card">
                    <p>• 결제 완료 후 주문이 확정됩니다.<br>
                       • 무통장 입금 시 입금자명을 정확히 기재해주세요.<br>
                       • 세금계산서는 결제 후 수정이 어려우니 정보를 정확히 입력해주세요.<br>
                       • 카드 할부는 카드사 정책에 따라 제한될 수 있습니다.<br>
                       • <strong style="color: #e74c3c;">택배는 기본이 착불입니다.</strong></p>
                </div>
            </div>
        </div>

        <!-- 문의하기 -->
        <div id="contact" class="tab-content">
            <div class="content-section">
                <h2>📞 문의하기</h2>

                <div class="contact-info">
                    <div class="contact-card">
                        <h3>📞 전화 문의</h3>
                        <p><a href="tel:02-2632-1830">02-2632-1830</a></p>
                        <p style="font-size: 0.95rem; opacity: 0.9;">평일 09:00 - 18:00<br>점심 12:00 - 13:00</p>
                    </div>
                    <div class="contact-card">
                        <h3>📠 팩스</h3>
                        <p><a href="tel:02-2632-1829">02-2632-1829</a></p>
                        <p style="font-size: 0.95rem; opacity: 0.9;">24시간 접수 가능</p>
                    </div>
                    <div class="contact-card">
                        <h3>✉️ 이메일 문의</h3>
                        <p><a href="mailto:dsp1830@naver.com">dsp1830@naver.com</a></p>
                        <p style="font-size: 0.95rem; opacity: 0.9;">24시간 접수<br>영업일 내 답변</p>
                    </div>
                </div>

                <h3 style="margin-top: 3rem;">🏢 오시는 길</h3>
                <div class="info-card">
                    <h4>📍 본사 위치</h4>
                    <p><strong>주소:</strong> 서울 영등포구 영등포로 36길 9 송호빌딩 1층<br>
                       <strong>지하철:</strong> 1호선 영등포역, 2호선 당산역 도보 가능<br>
                       <strong>버스:</strong> 영등포역, 당산역 하차<br>
                       <strong>주차:</strong> 건물 앞 주차 가능</p>
                </div>

                <h3 style="margin-top: 2rem;">⏰ 영업 시간</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <h4>평일</h4>
                        <p>09:00 - 18:00<br>
                           (점심시간 12:00 - 13:00)</p>
                    </div>
                    <div class="info-card">
                        <h4>토요일/일요일/공휴일</h4>
                        <p>휴무<br>
                           (온라인 주문은 24시간 가능)</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <p><strong>두손기획인쇄</strong> | 사업자등록번호: 107-06-45106</p>
            <p>주소: 서울 영등포구 영등포로 36길 9 송호빌딩 1층 | 대표: 차경선</p>
            <p>전화: 02-2632-1830 | 팩스: 02-2632-1829 | 이메일: dsp1830@naver.com</p>
            <p>업태: 제조·도매 | 종목: 인쇄업·광고물</p>
            <p style="margin-top: 1rem; opacity: 0.8;">© 2025 Duson Planning Print. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Tab Switching
        function showTab(tabId) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabId).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');

            // Scroll to top of content
            window.scrollTo({ top: 300, behavior: 'smooth' });
        }

        // FAQ Toggle
        function toggleFaq(element) {
            element.classList.toggle('active');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Customer Center loaded successfully');
        });
    </script>
</body>
</html>
