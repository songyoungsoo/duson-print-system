<?php
/**
 * 배송비 안내
 * 배송비 및 배송 정책 안내
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
    <title>배송비 안내 - 두손기획인쇄 고객센터</title>

    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">
    <style>
        .shipping-calculator {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }

        .calc-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }

        .calc-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }

        .calc-btn {
            padding: 12px 30px;
            background: #2196F3;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
        }

        .calc-result {
            margin-top: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            border: 2px solid #2196F3;
            display: none;
        }

        .calc-result.show {
            display: block;
        }

        .result-amount {
            font-size: 28px;
            font-weight: 700;
            color: #2196F3;
            text-align: center;
            margin: 10px 0;
        }

        .price-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .price-table th,
        .price-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .price-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .price-table tbody tr:hover {
            background: #f8f9fa;
        }

        .highlight-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .region-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .region-card {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .region-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .region-price {
            font-size: 24px;
            font-weight: 700;
            color: #2196F3;
        }

        .region-desc {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="customer-center-container">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>배송비 안내</span>
            </div>

            <div class="content-header">
                <h1>🚚 배송비 안내</h1>
                <p class="subtitle">지역별 배송비 및 무료배송 조건 안내</p>
            </div>

            <div class="content-body">
                <!-- 배송비 계산기 -->
                <section class="shipping-calculator">
                    <h2 class="section-title">💰 배송비 계산기</h2>
                    <p style="margin-bottom: 20px; color: #666;">주문금액과 배송지를 입력하시면 예상 배송비를 확인하실 수 있습니다.</p>

                    <div class="calc-row">
                        <select id="region" class="calc-input">
                            <option value="서울/경기/인천">서울/경기/인천</option>
                            <option value="지방">지방 (충청/전라/경상/강원)</option>
                            <option value="제주">제주도</option>
                            <option value="도서산간">도서산간 지역</option>
                        </select>

                        <input
                            type="number"
                            id="orderAmount"
                            class="calc-input"
                            placeholder="주문금액 (원)"
                            min="0"
                        >

                        <button class="calc-btn" onclick="calculateShipping()">계산하기</button>
                    </div>

                    <div id="calcResult" class="calc-result">
                        <h3 style="margin: 0 0 15px 0; text-align: center;">예상 배송비</h3>
                        <div class="result-amount" id="resultAmount">0원</div>
                        <p id="resultDesc" style="text-align: center; color: #666; margin: 10px 0 0 0;"></p>
                    </div>
                </section>

                <!-- 기본 배송비 -->
                <section class="guide-section">
                    <h2 class="section-title">📦 기본 배송비</h2>
                    <div class="section-content">
                        <div class="region-grid">
                            <div class="region-card">
                                <div class="region-name">🏙️ 서울/경기/인천</div>
                                <div class="region-price">3,000원</div>
                                <div class="region-desc">일반 택배 (1~2일)</div>
                            </div>

                            <div class="region-card">
                                <div class="region-name">🏞️ 지방</div>
                                <div class="region-price">4,000원</div>
                                <div class="region-desc">충청/전라/경상/강원 (2~3일)</div>
                            </div>

                            <div class="region-card">
                                <div class="region-name">🏝️ 제주도</div>
                                <div class="region-price">6,000원</div>
                                <div class="region-desc">제주 전역 (3~4일)</div>
                            </div>

                            <div class="region-card">
                                <div class="region-name">⛰️ 도서산간</div>
                                <div class="region-price">별도 문의</div>
                                <div class="region-desc">우편번호 확인 필요</div>
                            </div>
                        </div>

                        <div class="highlight-box">
                            <h3 style="margin: 0 0 15px 0;">✨ 무료배송 조건</h3>
                            <ul style="margin: 0; padding-left: 20px;">
                                <li><strong>50,000원 이상</strong> 주문 시 무료배송 (제주/도서산간 제외)</li>
                                <li><strong>100,000원 이상</strong> 주문 시 전국 무료배송 (제주 포함)</li>
                                <li>도서산간 지역은 추가 배송비 발생 가능</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 상세 배송비 표 -->
                <section class="guide-section">
                    <h2 class="section-title">📊 주문금액별 배송비</h2>
                    <div class="section-content">
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>주문금액</th>
                                    <th>서울/경기/인천</th>
                                    <th>지방</th>
                                    <th>제주</th>
                                    <th>도서산간</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>0원 ~ 49,999원</td>
                                    <td>3,000원</td>
                                    <td>4,000원</td>
                                    <td>6,000원</td>
                                    <td>별도 문의</td>
                                </tr>
                                <tr>
                                    <td>50,000원 ~ 99,999원</td>
                                    <td><strong class="text-success">무료</strong></td>
                                    <td><strong class="text-success">무료</strong></td>
                                    <td>6,000원</td>
                                    <td>별도 문의</td>
                                </tr>
                                <tr>
                                    <td>100,000원 이상</td>
                                    <td><strong class="text-success">무료</strong></td>
                                    <td><strong class="text-success">무료</strong></td>
                                    <td><strong class="text-success">무료</strong></td>
                                    <td>별도 문의</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 배송 정책 -->
                <section class="guide-section">
                    <h2 class="section-title">📮 배송 정책</h2>
                    <div class="section-content">
                        <h3>배송 방법</h3>
                        <ul class="step-list">
                            <li><strong>택배사:</strong> 로젠택배</li>
                            <li><strong>배송 시간:</strong> 평일 오후 2시 이전 출고분은 당일 발송</li>
                            <li><strong>주말/공휴일:</strong> 배송 없음 (익일 배송)</li>
                            <li><strong>송장번호:</strong> 발송 후 문자 및 이메일로 전송</li>
                        </ul>

                        <h3>배송 기간</h3>
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>지역</th>
                                    <th>배송 기간</th>
                                    <th>비고</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>서울/경기/인천</td>
                                    <td>1~2일</td>
                                    <td>수도권 익일 배송</td>
                                </tr>
                                <tr>
                                    <td>지방 (충청/전라/경상/강원)</td>
                                    <td>2~3일</td>
                                    <td>일반 택배</td>
                                </tr>
                                <tr>
                                    <td>제주도</td>
                                    <td>3~4일</td>
                                    <td>항공 운송</td>
                                </tr>
                                <tr>
                                    <td>도서산간</td>
                                    <td>3~5일</td>
                                    <td>추가 배송비 발생</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>직접 방문 수령</h3>
                        <div class="info-box">
                            <p><strong>📍 수령 장소:</strong> 서울시 강남구 테헤란로 123 (가상 주소)</p>
                            <p><strong>🕐 수령 시간:</strong> 평일 09:00~18:00 (점심시간 12:00~13:00 제외)</p>
                            <p><strong>📞 사전 연락:</strong> 1688-2384 / 02-2632-1830 (필수)</p>
                            <p><strong>💡 TIP:</strong> 방문 수령 시 배송비 무료</p>
                        </div>
                    </div>
                </section>

                <!-- 도서산간 지역 -->
                <section class="guide-section">
                    <h2 class="section-title">⛰️ 도서산간 지역 안내</h2>
                    <div class="section-content">
                        <p>다음 지역은 도서산간 지역으로 추가 배송비가 발생합니다.</p>

                        <div class="warning-box">
                            <h4>⚠️ 도서산간 추가 배송비</h4>
                            <ul>
                                <li><strong>일반 도서:</strong> 3,000원 ~ 5,000원 추가</li>
                                <li><strong>산간 지역:</strong> 2,000원 ~ 3,000원 추가</li>
                                <li><strong>섬 지역:</strong> 5,000원 ~ 10,000원 추가</li>
                                <li><strong>특수 지역:</strong> 별도 문의 (배송 불가 지역 있음)</li>
                            </ul>
                        </div>

                        <h3>주요 도서산간 지역</h3>
                        <ul class="step-list">
                            <li><strong>경기:</strong> 연천, 가평, 양평 일부</li>
                            <li><strong>강원:</strong> 평창, 정선, 영월, 인제, 양구 등 산간 지역</li>
                            <li><strong>충청:</strong> 옹진군, 태안군 일부 섬 지역</li>
                            <li><strong>전라:</strong> 신안군, 진도군, 완도군, 영광군 일부</li>
                            <li><strong>경상:</strong> 울릉군, 거제시, 통영시 일부 섬 지역</li>
                            <li><strong>제주:</strong> 우도, 마라도 등 부속 섬</li>
                        </ul>

                        <div class="info-box">
                            <p><strong>💡 TIP:</strong> 우편번호 조회를 통해 도서산간 여부를 확인하실 수 있습니다.</p>
                            <p>주문 시 주소 입력 후 자동으로 추가 배송비가 계산됩니다.</p>
                        </div>
                    </div>
                </section>

                <!-- 배송 관련 FAQ -->
                <section class="guide-section">
                    <h2 class="section-title">❓ 자주 묻는 질문</h2>
                    <div class="section-content">
                        <div class="faq-list">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 배송비는 언제 결제하나요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>주문 시 상품 금액과 함께 배송비가 자동으로 계산되어 결제됩니다.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 여러 주문을 합배송할 수 있나요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>같은 날 주문한 건에 한해 합배송 가능합니다. 주문 시 요청사항에 '합배송 요청'을 남겨주세요. 단, 제작 일정이 다른 경우 합배송이 불가능할 수 있습니다.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 배송 추적은 어떻게 하나요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>발송 후 문자로 전송된 송장번호로 로젠택배 홈페이지 또는 앱에서 조회 가능합니다. 마이페이지에서도 확인하실 수 있습니다.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <h3>Q. 배송지 변경이 가능한가요?</h3>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="faq-answer">
                                    <p>발송 전에는 고객센터(1688-2384 / 02-2632-1830)로 연락주시면 변경 가능합니다. 발송 후에는 택배사에 직접 연락하셔야 하며, 추가 비용이 발생할 수 있습니다.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 관련 링크 -->
                <div class="related-links">
                    <h3>더 궁금하신 사항이 있으신가요?</h3>
                    <div class="link-buttons">
                        <a href="/sub/customer/same_day.php" class="btn-secondary">당일판 안내</a>
                        <a href="/sub/customer/faq.php" class="btn-secondary">자주하는 질문</a>
                        <a href="/sub/customer/inquiry.php" class="btn-primary">1:1 문의하기</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
    <script>
        // 배송비 계산 함수
        function calculateShipping() {
            const region = document.getElementById('region').value;
            const amount = parseInt(document.getElementById('orderAmount').value) || 0;
            let shippingFee = 0;
            let description = '';

            if (amount === 0) {
                alert('주문금액을 입력해주세요.');
                return;
            }

            // 지역별 배송비 계산
            if (amount >= 100000) {
                // 10만원 이상: 전국 무료 (도서산간 제외)
                if (region === '도서산간') {
                    shippingFee = 0; // 별도 문의
                    description = '도서산간 지역은 별도 문의가 필요합니다. (고객센터: 1688-2384 / 02-2632-1830)';
                } else {
                    shippingFee = 0;
                    description = '🎉 100,000원 이상 주문으로 무료배송 혜택을 받으실 수 있습니다!';
                }
            } else if (amount >= 50000) {
                // 5만원 이상: 수도권/지방 무료, 제주/도서산간 유료
                if (region === '서울/경기/인천' || region === '지방') {
                    shippingFee = 0;
                    description = '✨ 50,000원 이상 주문으로 무료배송 혜택을 받으실 수 있습니다!';
                } else if (region === '제주') {
                    shippingFee = 6000;
                    description = '제주 지역 배송비입니다. 100,000원 이상 주문 시 무료배송 혜택이 있습니다.';
                } else {
                    shippingFee = 0; // 별도 문의
                    description = '도서산간 지역은 별도 문의가 필요합니다. (고객센터: 1688-2384 / 02-2632-1830)';
                }
            } else {
                // 5만원 미만: 지역별 기본 배송비
                switch (region) {
                    case '서울/경기/인천':
                        shippingFee = 3000;
                        description = '서울/경기/인천 지역 기본 배송비입니다. 50,000원 이상 주문 시 무료배송!';
                        break;
                    case '지방':
                        shippingFee = 4000;
                        description = '지방 지역 기본 배송비입니다. 50,000원 이상 주문 시 무료배송!';
                        break;
                    case '제주':
                        shippingFee = 6000;
                        description = '제주 지역 기본 배송비입니다. 100,000원 이상 주문 시 무료배송!';
                        break;
                    case '도서산간':
                        shippingFee = 0; // 별도 문의
                        description = '도서산간 지역은 별도 문의가 필요합니다. (고객센터: 1688-2384 / 02-2632-1830)';
                        break;
                }
            }

            // 결과 표시
            const resultDiv = document.getElementById('calcResult');
            const amountDiv = document.getElementById('resultAmount');
            const descDiv = document.getElementById('resultDesc');

            amountDiv.textContent = shippingFee === 0 && region !== '도서산간' ?
                '무료배송' :
                (region === '도서산간' ? '별도 문의' : shippingFee.toLocaleString() + '원');

            descDiv.textContent = description;
            resultDiv.classList.add('show');
        }

        // Enter 키로도 계산 가능
        document.getElementById('orderAmount').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                calculateShipping();
            }
        });
    </script>
</body>
</html>
