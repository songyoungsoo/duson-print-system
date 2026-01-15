<?php
/**
 * 입금계좌안내
 * 무통장입금 계좌 정보 및 결제 방법 안내
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
    <title>입금계좌안내 - 두손기획인쇄 고객센터</title>

    <link rel="stylesheet" href="/css/common-styles.css">
    <link rel="stylesheet" href="/css/customer-center.css">
</head>
<body>
    <div class="customer-center-container">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/customer_sidebar.php'; ?>

        <main class="customer-content">
            <div class="breadcrumb">
                <a href="/">홈</a> &gt; <a href="/sub/customer/">고객센터</a> &gt; <span>입금계좌안내</span>
            </div>

            <div class="content-header">
                <h1>💳 입금계좌안내</h1>
                <p class="subtitle">무통장입금 계좌 정보 및 결제 방법 안내</p>
            </div>

            <div class="content-body">
                <!-- 주요 입금 계좌 -->
                <section class="account-section main-account">
                    <h2 class="section-title">주요 입금 계좌</h2>
                    <div class="account-cards">
                        <div class="account-card primary">
                            <div class="bank-logo">🏦</div>
                            <div class="account-info">
                                <h3>국민은행</h3>
                                <div class="account-number">999-1688-2384</div>
                                <div class="account-holder">예금주: 두손기획인쇄 차경선</div>
                            </div>
                            <button class="btn-copy" data-account="999-1688-2384">계좌번호 복사</button>
                        </div>

                        <div class="account-card">
                            <div class="bank-logo">🏦</div>
                            <div class="account-info">
                                <h3>신한은행</h3>
                                <div class="account-number">110-342-543507</div>
                                <div class="account-holder">예금주: 두손기획인쇄 차경선</div>
                            </div>
                            <button class="btn-copy" data-account="110-342-543507">계좌번호 복사</button>
                        </div>

                        <div class="account-card">
                            <div class="bank-logo">🏦</div>
                            <div class="account-info">
                                <h3>농협</h3>
                                <div class="account-number">301-2632-1830-11</div>
                                <div class="account-holder">예금주: 두손기획인쇄 차경선</div>
                            </div>
                            <button class="btn-copy" data-account="301-2632-1830-11">계좌번호 복사</button>
                        </div>
                    </div>
                </section>

                <!-- 결제 방법 안내 -->
                <section class="payment-methods-section">
                    <h2 class="section-title">결제 방법 안내</h2>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <div class="method-icon">🏧</div>
                            <h3>무통장입금</h3>
                            <ul>
                                <li>위 계좌로 주문금액 입금</li>
                                <li>입금자명: 주문자명과 동일하게</li>
                                <li>입금 확인 후 제작 시작</li>
                                <li>영업일 기준 1~2시간 내 확인</li>
                            </ul>
                        </div>

                        <div class="payment-method">
                            <div class="method-icon">💳</div>
                            <h3>카드결제</h3>
                            <ul>
                                <li>주문 완료 시 카드 결제 선택</li>
                                <li>모든 신용카드 사용 가능</li>
                                <li>즉시 결제 확인</li>
                                <li>할부 가능 (카드사별 상이)</li>
                            </ul>
                        </div>

                        <div class="payment-method">
                            <div class="method-icon">🔄</div>
                            <h3>실시간 계좌이체</h3>
                            <ul>
                                <li>주문 시 계좌이체 선택</li>
                                <li>본인 계좌에서 즉시 이체</li>
                                <li>실시간 결제 확인</li>
                                <li>공인인증서 필요</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 입금 시 주의사항 -->
                <section class="notice-section">
                    <h2 class="section-title">⚠️ 입금 시 주의사항</h2>
                    <div class="notice-box">
                        <ul class="notice-list">
                            <li>
                                <strong>입금자명 확인</strong>
                                <p>주문자명과 입금자명이 다를 경우 고객센터(1688-2384 / 02-2632-1830)로 연락주세요.</p>
                            </li>
                            <li>
                                <strong>입금 기한</strong>
                                <p>주문 후 3일 이내 미입금 시 자동 취소될 수 있습니다.</p>
                            </li>
                            <li>
                                <strong>입금 확인 시간</strong>
                                <p>평일 09:00~18:00, 토요일 09:00~13:00 (일요일/공휴일 제외)</p>
                            </li>
                            <li>
                                <strong>부분 입금</strong>
                                <p>주문금액과 입금액이 다를 경우 제작이 지연될 수 있습니다.</p>
                            </li>
                            <li>
                                <strong>현금영수증</strong>
                                <p>마이페이지에서 현금영수증 신청 가능합니다.</p>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- 세금계산서 안내 -->
                <section class="tax-invoice-section">
                    <h2 class="section-title">📋 세금계산서 발행 안내</h2>
                    <div class="tax-invoice-info">
                        <div class="info-row">
                            <div class="info-label">발행 대상</div>
                            <div class="info-value">사업자 회원 (사업자등록증 등록 필요)</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">발행 시점</div>
                            <div class="info-value">주문 시 세금계산서 발행 선택 → 입금 확인 후 발행</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">발행 방법</div>
                            <div class="info-value">전자세금계산서 (이메일 전송)</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">필요 정보</div>
                            <div class="info-value">사업자등록번호, 상호명, 대표자명, 업태/종목, 이메일</div>
                        </div>
                    </div>
                    <div class="tax-help">
                        <p>💡 세금계산서 관련 문의: 1688-2384 / 02-2632-1830 또는 <a href="/sub/customer/inquiry.php">1:1 문의하기</a></p>
                    </div>
                </section>

                <!-- FAQ 링크 -->
                <div class="related-links">
                    <h3>더 궁금하신 사항이 있으신가요?</h3>
                    <div class="link-buttons">
                        <a href="/sub/customer/faq.php" class="btn-secondary">자주하는 질문</a>
                        <a href="/sub/customer/inquiry.php" class="btn-secondary">1:1 문의하기</a>
                        <a href="tel:1688-2384 / 02-2632-1830" class="btn-primary">📞 1688-2384 / 02-2632-1830</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/customer-center.js"></script>
</body>
</html>
