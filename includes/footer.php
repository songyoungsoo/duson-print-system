<?php
/**
 * 공통 푸터 파일
 * 경로: includes/footer.php
 */
?>
        </div> <!-- main-content-wrapper 끝 -->

        <!-- 푸터 -->
        <footer class="modern-footer">
            <div class="footer-container">
                <!-- 메인 푸터 콘텐츠 -->
                <div class="footer-main">
                    <div class="footer-grid">
                        
                        <!-- 회사 정보 카드 -->
                        <div class="footer-card">
                            <div class="footer-card-header">
                                <span class="footer-icon">🏢</span>
                                <h3 class="footer-card-title">두손기획인쇄</h3>
                            </div>
                            <div class="footer-card-content">
                                <div class="info-item">
                                    <span class="info-label">주소</span>
                                    <span class="info-value">서울 영등포구 영등포로 36길 9<br>송호빌딩 1F</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">전화</span>
                                    <span class="info-value">02-2632-1830</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">이메일</span>
                                    <span class="info-value">dsp1830@naver.com</span>
                                </div>
                            </div>
                        </div>

                        <!-- 계좌 정보 카드 -->
                        <div class="footer-card">
                            <div class="footer-card-header">
                                <span class="footer-icon">💳</span>
                                <h3 class="footer-card-title">입금계좌</h3>
                            </div>
                            <div class="footer-card-content">
                                <div class="account-info">
                                    <p class="account-holder">예금주: 두손기획인쇄 차경선</p>
                                    <div class="account-list">
                                        <div class="account-item">국민은행: 999-1688-2384</div>
                                        <div class="account-item">신한은행: 110-342-543507</div>
                                        <div class="account-item">농협: 301-2632-1829</div>
                                        <div class="account-item highlight">💳 카드결제: 1688-2384</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 주문 및 결제 카드 -->
                        <div class="footer-card">
                            <div class="footer-card-header">
                                <span class="footer-icon">📋</span>
                                <h3 class="footer-card-title">주문 및 결제</h3>
                            </div>
                            <div class="footer-card-content">
                                <div class="service-list">
                                    <div class="service-item">
                                        <span class="service-icon">📞</span>
                                        <span>전화주문: 1688-2384</span>
                                    </div>
                                    <div class="service-item">
                                        <span class="service-icon">🌐</span>
                                        <span>온라인 주문 24시간 접수</span>
                                    </div>
                                    <div class="service-item">
                                        <span class="service-icon">🚚</span>
                                        <span>당일 주문 시 익일 출고</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 서비스 카드 -->
                        <div class="footer-card">
                            <div class="footer-card-header">
                                <span class="footer-icon">🎨</span>
                                <h3 class="footer-card-title">서비스</h3>
                            </div>
                            <div class="footer-card-content">
                                <div class="service-links-simple">
                                    <a href="/mlangprintauto/inserted/" class="service-link-simple">
                                        <span class="service-icon-simple">📄</span>
                                        <span>전단지/리플릿</span>
                                    </a>
                                    <a href="/mlangprintauto/namecard/" class="service-link-simple">
                                        <span class="service-icon-simple">💼</span>
                                        <span>명함</span>
                                    </a>
                                    <a href="/mlangprintauto/sticker_new/" class="service-link-simple">
                                        <span class="service-icon-simple">🏷️</span>
                                        <span>일반스티커</span>
                                    </a>
                                    <a href="/mlangprintauto/msticker/" class="service-link-simple">
                                        <span class="service-icon-simple">🧲</span>
                                        <span>자석스티커</span>
                                    </a>
                                    <a href="/bbs/" class="service-link-simple">
                                        <span class="service-icon-simple">💬</span>
                                        <span>문의게시판</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 하단 정보 -->
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        <p>© 2024 두손기획인쇄. All rights reserved.</p>
                        <p>고품질 인쇄물을 합리적인 가격으로 제작해드립니다. | 사업자등록번호: 123-45-67890</p>
                    </div>
                </div>
            </div>
        </footer>

        <style>
        .modern-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            margin-top: 40px;
            font-family: 'Noto Sans KR', sans-serif;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px 10px; /* 기존 40px 20px 20px에서 1/3로 축소 */
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px; /* 기존 20px에서 축소 */
            margin-bottom: 10px; /* 기존 30px에서 1/3로 축소 */
        }

        .footer-card {
            background: transparent; /* 배경 장식 제거 */
            border-radius: 0; /* 장식 제거 */
            padding: 8px; /* 기존 20px에서 크게 축소 */
            backdrop-filter: none; /* 장식 제거 */
            border: none; /* 테두리 제거 */
            transition: none; /* 애니메이션 제거 */
        }

        .footer-card:hover {
            background: transparent; /* 호버 효과 제거 */
            transform: none; /* 변환 효과 제거 */
            box-shadow: none; /* 그림자 제거 */
        }

        .footer-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 5px; /* 기존 15px에서 1/3로 축소 */
            padding-bottom: 3px; /* 기존 10px에서 1/3로 축소 */
            border-bottom: 1px solid rgba(255, 255, 255, 0.3); /* 얇은 선으로 변경 */
        }

        .footer-icon {
            display: none; /* 이모지 아이콘 제거 */
        }

        .footer-card-title {
            color: #ffffff; /* 더 선명한 흰색으로 가독성 향상 */
            font-size: 14px; /* 기존 16px에서 축소 */
            font-weight: 700; /* 더 굵게 해서 가독성 향상 */
            margin: 0;
        }

        .footer-card-content {
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
            line-height: 1.3; /* 기존 1.6에서 축소 */
        }

        .info-item {
            display: flex;
            margin-bottom: 3px; /* 기존 8px에서 1/3로 축소 */
            align-items: flex-start;
        }

        .info-label {
            font-weight: 700; /* 더 굵게 해서 가독성 향상 */
            min-width: 40px; /* 기존 50px에서 축소 */
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
            margin-right: 8px; /* 기존 10px에서 축소 */
            font-size: 12px; /* 크기 축소 */
        }

        .info-value {
            flex: 1;
            font-size: 12px; /* 기존 14px에서 축소 */
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
        }

        .account-holder {
            font-weight: 700; /* 가독성을 위해 더 굵게 */
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
            margin-bottom: 3px; /* 기존 10px에서 1/3로 축소 */
            font-size: 12px; /* 크기 축소 */
        }

        .account-list {
            display: flex;
            flex-direction: column;
            gap: 2px; /* 기존 6px에서 1/3로 축소 */
        }

        .account-item {
            padding: 3px 6px; /* 기존 8px 12px에서 1/3로 축소 */
            background: transparent; /* 배경 제거 */
            border-radius: 0; /* 장식 제거 */
            font-size: 11px; /* 기존 13px에서 축소 */
            border-left: none; /* 장식 제거 */
            transition: none; /* 애니메이션 제거 */
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
        }

        .account-item:hover {
            background: transparent; /* 호버 효과 제거 */
        }

        .account-item.highlight {
            background: transparent; /* 배경 제거 */
            color: #ffeb3b; /* 더 선명한 노란색으로 강조 */
            font-weight: 700; /* 더 굵게 */
            border-left: none; /* 장식 제거 */
            box-shadow: none; /* 그림자 제거 */
        }

        .service-list {
            display: flex;
            flex-direction: column;
            gap: 2px; /* 기존 8px에서 2px로 축소 - 입금계좌와 동일 */
        }

        .service-item {
            display: flex;
            align-items: center;
            padding: 3px 0; /* 기존 8px에서 3px로 축소 - 입금계좌와 동일 */
            font-size: 12px; /* 기존 14px에서 축소 */
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
        }

        .service-item.highlight {
            color: #ffeb3b; /* 더 선명한 노란색으로 강조 - 입금계좌와 동일 */
            font-weight: 700; /* 더 굵게 */
        }

        .service-icon {
            display: none; /* 이모지 제거 */
        }

        .service-links-simple {
            display: flex;
            flex-direction: column;
            gap: 2px; /* 기존 8px에서 2px로 축소 - 입금계좌와 동일 */
        }

        .service-link-simple {
            display: flex;
            align-items: center;
            padding: 3px 0; /* 기존 6px에서 3px로 축소 - 입금계좌와 동일 */
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
            text-decoration: none;
            transition: none; /* 애니메이션 제거 */
            font-size: 12px; /* 기존 14px에서 축소 */
        }

        .service-link-simple:hover {
            color: #ffeb3b; /* 더 선명한 노란색으로 강조 */
            transform: none; /* 변환 효과 제거 */
        }

        .service-icon-simple {
            display: none; /* 이모지 제거 */
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 8px; /* 기존 20px에서 1/3로 축소 */
            text-align: center;
        }

        .footer-copyright {
            color: #ffffff; /* 가독성을 위해 더 선명한 흰색 */
            font-size: 10px; /* 기존 12px에서 축소 */
            line-height: 1.3; /* 기존 1.6에서 축소 */
        }

        .footer-copyright p {
            margin: 4px 0;
        }

        /* 반응형 디자인 */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .footer-container {
                padding: 30px 15px 15px;
            }
            
            .footer-card {
                padding: 15px;
            }
        }
        </style>
    </div> <!-- page-wrapper 끝 -->

    <?php 
    // 로그인 모달 포함 (로그인하지 않은 사용자에게만 표시)
    if (!$is_logged_in) {
        include_once __DIR__ . '/login_modal.php';
    }
    ?>

    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
<!-- 카카오톡 인쇄상담 플로팅 버튼 -->
<div id="kakao-talk-floating" style="
    position: fixed;
    top: 180px;
    right: 30px;
    width: 90px;
    height: 50px;
    background: #FEE500;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    cursor: pointer;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
">
    <a href="https://center-pf.kakao.com/_pEGhj/chats" target="_blank" style="
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        gap: 5px;
    ">
        <div style="
            background: #3C1E1E;
            border-radius: 8px;
            width: 32px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        ">
            <span style="
                color: #FEE500;
                font-weight: bold;
                font-size: 11px;
                font-family: 'Noto Sans KR', sans-serif;
            ">TALK</span>
            <!-- 말풍선 꼬리 -->
            <div style="
                position: absolute;
                bottom: -4px;
                left: 8px;
                width: 0;
                height: 0;
                border-left: 5px solid transparent;
                border-right: 5px solid transparent;
                border-top: 5px solid #3C1E1E;
                transform: rotate(-30deg);
            "></div>
        </div>
        <span style="
            color: #3C1E1E;
            font-size: 11px;
            font-weight: bold;
            font-family: 'Noto Sans KR', sans-serif;
            line-height: 1.3;
        ">카톡<br>상담</span>
    </a>
</div>

<style>
#kakao-talk-floating:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    #kakao-talk-floating {
        top: 140px;
        right: 20px;
        width: 80px;
        height: 45px;
    }
}
</style>

</body>
</html>