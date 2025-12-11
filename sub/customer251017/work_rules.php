<?php
/**
 * 인쇄작업규약
 * 인쇄작업 시 알아야 할 규약 및 주의사항
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
    <title>인쇄작업규약 - 두손기획인쇄 고객센터</title>

    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">
</head>
<body>
    <div class="customer-center-container">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>인쇄작업규약</span>
            </div>

            <div class="content-header">
                <h1>📜 인쇄작업규약</h1>
                <p class="subtitle">원활한 인쇄 작업을 위한 필수 규약 및 주의사항</p>
            </div>

            <div class="content-body">
                <!-- 주문 및 결제 -->
                <section class="guide-section">
                    <h2 class="section-title"><span class="step-number">1</span> 주문 및 결제</h2>
                    <div class="section-content">
                        <h3>1.1 주문 확정</h3>
                        <ul class="step-list">
                            <li>주문은 결제 완료 후 확정됩니다.</li>
                            <li>무통장입금의 경우 입금 확인 후 제작이 시작됩니다.</li>
                            <li>주문 확정 후에는 수량, 사양 변경이 불가능합니다.</li>
                            <li>착오 주문에 대한 책임은 주문자에게 있습니다.</li>
                        </ul>

                        <h3>1.2 결제 방법</h3>
                        <ul class="step-list">
                            <li>무통장입금, 신용카드, 실시간 계좌이체 가능</li>
                            <li>법인 사업자는 세금계산서 발행 가능 (별도 계약 필요)</li>
                            <li>현금영수증은 마이페이지에서 신청 가능</li>
                        </ul>

                        <div class="info-box">
                            <p><strong>💡 TIP:</strong> 급한 주문의 경우 카드결제 또는 실시간 계좌이체를 이용하시면 즉시 제작이 시작됩니다.</p>
                        </div>
                    </div>
                </section>

                <!-- 파일 업로드 및 검수 -->
                <section class="guide-section">
                    <h2 class="section-title"><span class="step-number">2</span> 파일 업로드 및 검수</h2>
                    <div class="section-content">
                        <h3>2.1 파일 형식</h3>
                        <ul class="step-list">
                            <li><strong>권장 형식:</strong> AI, PDF (Adobe Illustrator, Photoshop 작업 파일)</li>
                            <li><strong>가능 형식:</strong> JPG, PNG, PSD (해상도 300dpi 이상)</li>
                            <li><strong>불가능 형식:</strong> 한글(HWP), 워드(DOC), 파워포인트(PPT)</li>
                        </ul>

                        <h3>2.2 파일 제작 규격</h3>
                        <div class="warning-box">
                            <h4>⚠️ 필수 준수사항</h4>
                            <ul>
                                <li><strong>재단선(Trim):</strong> 제작 사이즈 정확히 설정</li>
                                <li><strong>도련(Bleed):</strong> 상하좌우 각 품목별 mm씩 여유 (칼선 표시)</li>
                                <li><strong>안전영역(Safe Area):</strong> 재단선에서 안쪽 3mm 이내 중요 내용 배치 금지</li>
                                <li><strong>색상 모드:</strong> CMYK (RGB는 색상 변환 시 차이 발생 가능)</li>
                                <li><strong>해상도:</strong> 300dpi 이상 (저해상도 시 흐릿하게 인쇄)</li>
                                <li><strong>폰트:</strong> 모든 텍스트 외곽선(Outline) 처리 필수</li>
                            </ul>
                        </div>

                        <h3>2.3 파일 검수</h3>
                        <ul class="step-list">
                            <li>업로드 후 <strong>30분 이내</strong> 자동 검수 진행</li>
                            <li>파일 오류 발견 시 이메일 또는 문자로 알림</li>
                            <li>파일 수정 및 재업로드 시 제작 시간 지연 가능</li>
                            <li>검수 완료 후 시안 확인 요청 시 별도 요금 발생</li>
                        </ul>

                        <div class="info-box">
                            <p><strong>💡 TIP:</strong> 파일 업로드 전 <a href="/sub/customer/work_guide.php">작업가이드</a>를 꼭 확인하시면 재작업을 방지할 수 있습니다.</p>
                        </div>
                    </div>
                </section>

                <!-- 인쇄 작업 -->
                <section class="guide-section">
                    <h2 class="section-title"><span class="step-number">3</span> 인쇄 작업</h2>
                    <div class="section-content">
                        <h3>3.1 색상</h3>
                        <ul class="step-list">
                            <li>모니터 색상과 인쇄 색상은 차이가 있을 수 있습니다.</li>
                            <li>같은 파일도 제작 시기, 용지에 따라 색상 편차가 발생할 수 있습니다.</li>
                            <li>동일 주문의 재주문 시에도 완전히 같은 색상을 보장하지 않습니다.</li>
                            <li>색상 재현율: <strong>±5% 허용 오차</strong></li>
                        </ul>

                        <h3>3.2 재단 및 오차</h3>
                        <ul class="step-list">
                            <li>재단 오차: <strong>±1~2mm</strong> (업계 표준 허용 오차)</li>
                            <li>양면 인쇄 시 앞뒤 이미지 오차: <strong>±2mm</strong></li>
                            <li>책자/리플렛 제본 시 접지 오차: <strong>±1mm</strong></li>
                            <li>오차 범위 내의 작업은 정상 제품으로 간주</li>
                        </ul>

                        <h3>3.3 수량 오차</h3>
                        <ul class="step-list">
                            <li>인쇄물 특성상 수량 오차가 발생할 수 있습니다.</li>
                            <li>허용 오차: 주문 수량의 <strong>±5%</strong></li>
                            <li>500매 미만 소량 주문: <strong>±10매</strong></li>
                            <li>오차 범위 내 수량 부족/초과는 재작업 사유가 아닙니다.</li>
                        </ul>

                        <div class="warning-box">
                            <h4>⚠️ 중요</h4>
                            <ul>
                                <li>정확한 수량이 필요한 경우 여유분을 포함하여 주문해주세요.</li>
                                <li>예: 1000매 필요 시 → 1050~1100매 주문 권장</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 납기 및 배송 -->
                <section class="guide-section">
                    <h2 class="section-title"><span class="step-number">4</span> 납기 및 배송</h2>
                    <div class="section-content">
                        <h3>4.1 제작 기간</h3>
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>제작 일정</th>
                                    <th>설명</th>
                                    <th>주의사항</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>당일 출고</strong></td>
                                    <td>오전 10시 이전 주문 확정 건</td>
                                    <td>제한된 품목만 가능</td>
                                </tr>
                                <tr>
                                    <td><strong>1영업일</strong></td>
                                    <td>명함, 스티커 등 일반 인쇄</td>
                                    <td>주문 확정 시점 기준</td>
                                </tr>
                                <tr>
                                    <td><strong>2~3영업일</strong></td>
                                    <td>책자, 리플렛 등 후가공 포함</td>
                                    <td>후가공 종류에 따라 변동</td>
                                </tr>
                                <tr>
                                    <td><strong>3~5영업일</strong></td>
                                    <td>대량 주문, 특수 인쇄</td>
                                    <td>별도 협의 필요</td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>4.2 배송</h3>
                        <ul class="step-list">
                            <li>배송은 <strong>로젠택배</strong> 택배로 발송됩니다.</li>
                            <li>평균 배송 기간: <strong>1~2일</strong> (도서산간 지역 2~3일)</li>
                            <li>배송비: <a href="/sub/customer/shipping_info.php">배송비 안내</a> 참조</li>
                            <li>직접 방문 수령 가능 (사전 연락 필수)</li>
                        </ul>

                        <div class="warning-box">
                            <h4>⚠️ 납기 지연 사유</h4>
                            <ul>
                                <li>파일 오류로 인한 재업로드</li>
                                <li>시안 확인 요청 및 수정</li>
                                <li>입금 지연</li>
                                <li>천재지변, 물류 대란 등 불가항력적 사유</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 교환 및 환불 -->
                <section class="guide-section">
                    <h2 class="section-title"><span class="step-number">5</span> 교환 및 환불</h2>
                    <div class="section-content">
                        <h3>5.1 교환/재작업 가능 사유</h3>
                        <ul class="step-list">
                            <li>당사의 제작 실수로 인한 오류 (색상 오류 ±5% 초과, 재단 오류 ±2mm 초과)</li>
                            <li>파일과 다르게 인쇄된 경우</li>
                            <li>인쇄 품질 불량 (얼룩, 긁힘, 찢어짐 등)</li>
                            <li>배송 중 파손 (택배사 책임, 증빙 사진 필요)</li>
                        </ul>

                        <h3>5.2 교환/재작업 불가 사유</h3>
                        <div class="warning-box">
                            <h4>⚠️ 다음의 경우 교환/환불이 불가능합니다</h4>
                            <ul>
                                <li>주문자의 파일 오류 (오탈자, 디자인 오류, 색상 설정 오류)</li>
                                <li>파일 검수 후 작업 진행된 건</li>
                                <li>모니터 색상과 인쇄 색상 차이 (CMYK 특성)</li>
                                <li>허용 오차 범위 내의 색상/재단/수량 차이</li>
                                <li>단순 변심 또는 주문 착오</li>
                                <li>인쇄 완료 후 5일 경과한 건</li>
                                <li>맞춤 제작 상품 (명함, 전단지 등 모든 인쇄물)</li>
                            </ul>
                        </div>

                        <h3>5.3 환불 규정</h3>
                        <ul class="step-list">
                            <li><strong>제작 전:</strong> 전액 환불 (결제 수수료 제외)</li>
                            <li><strong>제작 중:</strong> 진행률에 따라 부분 환불</li>
                            <li><strong>제작 완료:</strong> 환불 불가 (당사 귀책 사유 제외)</li>
                            <li><strong>배송 시작:</strong> 왕복 배송비 차감 후 환불</li>
                        </ul>

                        <h3>5.4 클레임 접수</h3>
                        <ul class="step-list">
                            <li>제품 수령 후 <strong>5일 이내</strong> 고객센터 연락</li>
                            <li>불량 내용 사진 촬영 및 전송 (필수)</li>
                            <li>검토 후 귀책 사유 판단 (1~2 영업일)</li>
                            <li>당사 귀책 시 전액 재작업 또는 환불</li>
                        </ul>

                        <div class="info-box">
                            <p><strong>📞 클레임 접수:</strong> 1688-2384 / 02-2632-1830 또는 <a href="/sub/customer/inquiry.php">1:1 문의</a></p>
                        </div>
                    </div>
                </section>

                <!-- 기타 규정 -->
                <section class="guide-section">
                    <h2 class="section-title"><span class="step-number">6</span> 기타 규정</h2>
                    <div class="section-content">
                        <h3>6.1 저작권 및 책임</h3>
                        <ul class="step-list">
                            <li>업로드된 파일의 저작권 문제는 주문자에게 있습니다.</li>
                            <li>저작권 침해, 불법 복제물 제작 요청 시 작업 거부됩니다.</li>
                            <li>초상권, 상표권 침해에 대한 책임은 주문자에게 있습니다.</li>
                        </ul>

                        <h3>6.2 개인정보 보호</h3>
                        <ul class="step-list">
                            <li>주문 시 수집된 개인정보는 제작/배송 용도로만 사용됩니다.</li>
                            <li>파일은 제작 완료 후 <strong>30일 후 자동 삭제</strong>됩니다.</li>
                            <li>재주문 시 파일을 다시 업로드해주셔야 합니다.</li>
                        </ul>

                        <h3>6.3 면책 조항</h3>
                        <ul class="step-list">
                            <li>천재지변, 전쟁, 파업 등 불가항력적 사유로 인한 납기 지연은 책임지지 않습니다.</li>
                            <li>택배사의 배송 지연/분실은 택배사 책임입니다.</li>
                            <li>주문자의 잘못된 정보 입력으로 인한 오배송은 재배송비가 청구됩니다.</li>
                        </ul>
                    </div>
                </section>

                <!-- 규약 동의 -->
                <div class="notice-box" style="margin-top: 50px;">
                    <h3>📋 인쇄작업규약 동의</h3>
                    <p>
                        본 규약은 주문 시 자동으로 동의한 것으로 간주됩니다.<br>
                        규약을 숙지하지 않아 발생하는 불이익은 주문자 책임입니다.<br>
                        추가 문의사항은 고객센터(1688-2384 / 02-2632-1830)로 연락주시기 바랍니다.
                    </p>
                </div>

                <!-- 관련 링크 -->
                <div class="related-links">
                    <h3>함께 보면 좋은 정보</h3>
                    <div class="link-cards">
                        <a href="/sub/customer/work_guide.php" class="link-card">
                            <span class="card-icon">📐</span>
                            <h3>작업가이드</h3>
                            <p>파일 제작 가이드</p>
                        </a>
                        <a href="/sub/customer/faq.php" class="link-card">
                            <span class="card-icon">❓</span>
                            <h3>자주하는 질문</h3>
                            <p>FAQ 바로가기</p>
                        </a>
                        <a href="/sub/customer/inquiry.php" class="link-card">
                            <span class="card-icon">💬</span>
                            <h3>1:1 문의</h3>
                            <p>궁금한 점 문의하기</p>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
</body>
</html>
