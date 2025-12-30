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
    <style>
        /* 콘텐츠 영역 폭 제한 */
        .customer-content {
            max-width: 900px;
        }
        /* 계좌 카드 - 결제 방법과 동일한 스타일 */
        .account-cards-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 15px 0;
        }
        .account-card-compact {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
        }
        .account-card-compact .bank-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        .account-card-compact .account-num {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 15px;
            color: #1466BA;
            margin-bottom: 15px;
        }
        .btn-copy-sm {
            padding: 8px 16px;
            font-size: 13px;
            background: #1466BA;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-copy-sm:hover {
            background: #0d4d8a;
        }
        .account-holder-note {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
    </style>
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
                    <div class="account-cards-row">
                        <div class="account-card-compact">
                            <div class="bank-name">국민은행</div>
                            <div class="account-num">999-1688-2384</div>
                            <button class="btn-copy-sm" data-account="999-1688-2384">복사</button>
                        </div>
                        <div class="account-card-compact">
                            <div class="bank-name">신한은행</div>
                            <div class="account-num">110-342-543507</div>
                            <button class="btn-copy-sm" data-account="110-342-543507">복사</button>
                        </div>
                        <div class="account-card-compact">
                            <div class="bank-name">농협</div>
                            <div class="account-num">301-2632-1829</div>
                            <button class="btn-copy-sm" data-account="301-2632-1829">복사</button>
                        </div>
                    </div>
                    <p class="account-holder-note">예금주: 두손기획인쇄 차경선</p>
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
    <script>
    // 계좌번호 복사 기능 (인라인)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-copy-sm').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var accountNumber = this.getAttribute('data-account');
                var button = this;

                // fallback 방식 사용 (HTTP에서도 작동)
                var textArea = document.createElement('textarea');
                textArea.value = accountNumber;
                textArea.style.position = 'fixed';
                textArea.style.left = '-9999px';
                textArea.style.top = '0';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    var successful = document.execCommand('copy');
                    if (successful) {
                        var originalText = button.textContent;
                        button.textContent = '✓ 복사완료!';
                        button.style.background = '#4CAF50';
                        setTimeout(function() {
                            button.textContent = originalText;
                            button.style.background = '#1466BA';
                        }, 2000);
                    } else {
                        alert('계좌번호: ' + accountNumber);
                    }
                } catch (err) {
                    alert('계좌번호: ' + accountNumber);
                }

                document.body.removeChild(textArea);
            });
        });
    });
    </script>
</body>
</html>
