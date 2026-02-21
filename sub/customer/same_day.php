<?php
/**
 * 당일판 안내
 * 당일 출고 서비스 안내
 */

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 공통 헤더 포함
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>당일판 안내 - 두손기획인쇄 고객센터</title>

    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">
    <style>
        /* 콘텐츠 영역 폭 제한 */
        .customer-content {
            max-width: 900px;
        }
        .timer-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }

        .timer-box h2 {
            margin: 0 0 20px 0;
            font-size: 28px;
        }

        .current-time {
            font-size: 48px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            margin: 20px 0;
        }

        .deadline-notice {
            font-size: 18px;
            margin: 15px 0;
        }

        .deadline-notice strong {
            font-size: 24px;
            color: #ffd700;
        }

        .status-message {
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: inline-block;
            margin-top: 20px;
            font-size: 16px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .product-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            transition: all 0.2s;
        }

        .product-card:hover {
            border-color: #2196F3;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
            transform: translateY(-2px);
        }

        .product-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        .product-card h3 {
            font-size: 20px;
            margin: 0 0 15px 0;
            color: #333;
        }

        .product-specs {
            font-size: 14px;
            color: #666;
            margin: 0 0 15px 0;
            line-height: 1.6;
        }

        .product-price {
            font-size: 18px;
            font-weight: 600;
            color: #2196F3;
            margin: 15px 0;
        }

        .order-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #2196F3;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .order-btn:hover {
            background: #1976D2;
        }

        .timeline {
            position: relative;
            padding: 30px 0;
        }

        .timeline-item {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            position: relative;
        }

        .timeline-time {
            flex: 0 0 100px;
            font-weight: 600;
            color: #2196F3;
            font-size: 18px;
        }

        .timeline-content {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }

        .timeline-content h4 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .timeline-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .limitation-list {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
        }

        .limitation-list h4 {
            margin: 0 0 15px 0;
            color: #856404;
        }

        .limitation-list ul {
            margin: 0;
            padding-left: 20px;
        }

        .limitation-list li {
            color: #856404;
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="customer-center-container">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>당일판</span>
            </div>

            <div class="content-header">
                <h1>⚡ 당일판 (당일 출고)</h1>
                <p class="subtitle">오전 10시 이전 주문 시 당일 출고 서비스</p>
            </div>

            <div class="content-body">
                <!-- 실시간 타이머 -->
                <div class="timer-box">
                    <h2>🕐 당일 출고 마감까지</h2>
                    <div class="current-time" id="currentTime">--:--:--</div>
                    <div class="deadline-notice">
                        오전 <strong>11:00</strong> 이전 주문 시 <strong>당일 출고 (오후 6시)</strong>
                    </div>
                    <div class="status-message" id="statusMessage">
                        마감 시간 확인 중...
                    </div>
                </div>

                <!-- 당일판 프로세스 -->
                <section class="guide-section">
                    <h2 class="section-title">🚀 당일판 진행 프로세스</h2>
                    <div class="section-content">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-time">오전 11시</div>
                                <div class="timeline-content">
                                    <h4>1️⃣ 주문 마감</h4>
                                    <p>오전 11시까지 주문 확정 및 파일 업로드 완료 필수</p>
                                    <p>결제 완료 + 파일 검수 통과 상태여야 합니다</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-time">11~14시</div>
                                <div class="timeline-content">
                                    <h4>2️⃣ 인쇄 작업</h4>
                                    <p>주문 순서대로 인쇄 작업 진행</p>
                                    <p>후가공 포함 시 추가 시간 소요</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-time">14~17시</div>
                                <div class="timeline-content">
                                    <h4>3️⃣ 후가공 및 포장</h4>
                                    <p>재단, 코팅, 접지 등 후가공</p>
                                    <p>검품 후 포장 작업</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-time">오후 18시</div>
                                <div class="timeline-content">
                                    <h4>4️⃣ 출고 완료</h4>
                                    <p>택배 픽업 및 발송</p>
                                    <p>송장번호 문자 발송</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-box">
                            <p><strong>💡 TIP:</strong> 오전 9시 이전 주문 시 더욱 안정적으로 당일 출고가 가능합니다.</p>
                        </div>
                    </div>
                </section>

                <!-- 당일판 가능 상품 -->
                <section class="guide-section">
                    <h2 class="section-title">📦 당일판 가능 상품</h2>
                    <div class="section-content">
                        <div class="product-grid">
                            <div class="product-card">
                                <span class="product-icon">📇</span>
                                <h3>명함</h3>
                                <div class="product-specs">
                                    • 사이즈: 90x50mm<br>
                                    • 용지: 스노우지 250g, 수입지일부(문의)<br>
                                    • 후가공: 단면/양면 인쇄<br>
                                    • 수량: 200~500매
                                </div>
                                <a href="/mlangprintauto/namecard/" class="order-btn">주문하기</a>
                            </div>

                            <div class="product-card">
                                <span class="product-icon">📄</span>
                                <h3>전단지 (A4)</h3>
                                <div class="product-specs">
                                    • 사이즈: A4 (210x297mm)<br>
                                    • 용지: 아트지 90g<br>
                                    • 단면/양면 인쇄<br>
                                    • 수량: 2000~4000매
                                </div>
                                <a href="/mlangprintauto/inserted/" class="order-btn">주문하기</a>
                            </div>

                            <div class="product-card">
                                <span class="product-icon">🏷️</span>
                                <h3>스티커</h3>
                                <div class="product-specs">
                                    • 사이즈: 90x50mm<br>
                                    • 용지: 아트지 (무광)<br>
                                    • 후가공: 당일판은 없음<br>
                                    • 수량: 100~500매
                                </div>
                                <a href="/mlangprintauto/sticker_new/" class="order-btn">주문하기</a>
                            </div>
                        </div>

                        <div class="limitation-list">
                            <h4>⚠️ 당일판 제한 사항</h4>
                            <ul>
                                <li>특수 용지 및 특수 사이즈는 당일판 불가</li>
                                <li>대량 주문 (2000매 이상)은 당일판 불가</li>
                                <li>복잡한 후가공 (코팅, 박, 형압 등)은 당일판 불가</li>
                                <li>책자, 리플렛 등 제본 작업은 당일판 불가</li>
                                <li>주말, 공휴일은 당일판 서비스 제공 안 함</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 당일판 조건 -->
                <section class="guide-section">
                    <h2 class="section-title">✅ 당일판 필수 조건</h2>
                    <div class="section-content">
                        <h3>1. 주문 시간</h3>
                        <ul class="step-list">
                            <li><strong>평일 오전 11시 이전</strong> 주문 확정 (결제 완료)</li>
                            <li>주말, 공휴일 주문은 익영업일 처리</li>
                            <li>오전 11시 이후 주문은 익영업일 출고</li>
                        </ul>

                        <h3>2. 파일 업로드</h3>
                        <ul class="step-list">
                            <li>주문 시 즉시 파일 업로드 필수</li>
                            <li>파일 검수 통과 필수 (오류 없어야 함)</li>
                            <li>파일 수정 시 당일 출고 불가</li>
                            <li>권장 형식: AI, PDF (CMYK, 300dpi)</li>
                        </ul>

                        <h3>3. 결제 완료</h3>
                        <ul class="step-list">
                            <li>카드결제 또는 실시간 계좌이체 권장 (즉시 확인)</li>
                            <li>무통장입금 시 오전 10시 30분까지 입금 확인 필수</li>
                            <li>입금자명과 주문자명 일치 필수</li>
                        </ul>

                        <h3>4. 주문 사양</h3>
                        <ul class="step-list">
                            <li>당일판 가능 상품 및 사양만 선택</li>
                            <li>표준 사이즈 및 용지만 가능</li>
                            <li>기본 후가공만 가능 (재단, 접지 등)</li>
                        </ul>

                        <div class="warning-box">
                            <h4>⚠️ 주의사항</h4>
                            <ul>
                                <li>당일판 주문이 폭주하는 경우 일부 주문은 익영업일 출고될 수 있습니다.</li>
                                <li>파일 오류 또는 품질 문제 발견 시 고객 확인 후 진행하므로 당일 출고가 어려울 수 있습니다.</li>
                                <li>천재지변, 기계 고장 등 불가항력적 사유 시 당일 출고가 불가할 수 있습니다.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 배송 정보 -->
                <section class="guide-section">
                    <h2 class="section-title">🚚 배송 정보</h2>
                    <div class="section-content">
                        <h3>출고 및 배송</h3>
                        <ul class="step-list">
                            <li><strong>출고 시간:</strong> 당일 오후 6시</li>
                            <li><strong>택배사:</strong> 로젠택배</li>
                            <li><strong>배송 기간:</strong> 수도권 익일 도착, 지방 2~3일</li>
                            <li><strong>송장번호:</strong> 출고 후 문자 발송</li>
                        </ul>

                        <div class="info-box">
                            <p><strong>💡 TIP:</strong> 직접 방문 수령도 가능합니다. (오후 6시 이후, 사전 연락 필수)</p>
                            <p><strong>📞 연락처:</strong> 1688-2384 / 02-2632-1830</p>
                        </div>
                    </div>
                </section>

                <!-- FAQ -->
                <section class="guide-section">
                    <h2 class="section-title">❓ 자주 묻는 질문</h2>
                    <div class="section-content">
                        <div class="faq-list">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 오전 10시 넘어서 주문하면 어떻게 되나요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>오전 11시 이후 주문은 익영업일 출고로 처리됩니다. 급하신 경우 고객센터(1688-2384 / 02-2632-1830)로 문의해주세요.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 당일 출고했는데 언제 받을 수 있나요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>수도권은 익일 오전~오후, 지방은 익일 오후~2일 후 수령 가능합니다. 정확한 도착 시간은 택배사 사정에 따라 다릅니다.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 파일을 나중에 올려도 되나요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>아니요. 주문과 동시에 파일 업로드가 완료되어야 당일 출고가 가능합니다. 파일 검수 시간도 필요하므로 가급적 빠르게 올려주세요.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 토요일에도 당일판이 가능한가요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>토요일, 일요일, 공휴일은 당일판 서비스를 제공하지 않습니다. 주말 주문은 월요일에 출고됩니다.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 관련 링크 -->
                <div class="related-links">
                    <h3>더 알아보기</h3>
                    <div class="link-buttons">
                        <a href="/sub/customer/shipping_info.php" class="btn-secondary">배송비 안내</a>
                        <a href="/sub/customer/work_guide.php" class="btn-secondary">작업가이드</a>
                        <a href="/sub/customer/inquiry.php" class="btn-primary">문의하기</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
    <script>
        // 실시간 시간 업데이트
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            document.getElementById('currentTime').textContent = hours + ':' + minutes + ':' + seconds;

            // 마감 시간 계산 (오전 10시)
            const deadline = new Date(now);
            deadline.setHours(10, 0, 0, 0);

            const statusMessage = document.getElementById('statusMessage');
            const currentHour = now.getHours();

            if (currentHour < 10) {
                const diff = deadline - now;
                const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
                const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                statusMessage.textContent = `⏰ 당일 출고 가능! (마감까지 ${hoursLeft}시간 ${minutesLeft}분 남음)`;
                statusMessage.style.background = 'rgba(76, 175, 80, 0.3)';
            } else if (currentHour < 14) {
                statusMessage.textContent = '⚠️ 당일판 마감되었습니다. 현재 제작 진행 중...';
                statusMessage.style.background = 'rgba(255, 193, 7, 0.3)';
            } else {
                statusMessage.textContent = '❌ 당일판 마감. 내일 오전 10시까지 주문하시면 익일 출고됩니다.';
                statusMessage.style.background = 'rgba(244, 67, 54, 0.3)';
            }
        }

        // 1초마다 업데이트
        updateTime();
        setInterval(updateTime, 1000);
    </script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
