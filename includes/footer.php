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
                                    <div class="service-item highlight">
                                        <span class="service-icon">🎁</span>
                                        <span>3만원 이상 배송비 무료</span>
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
                                    <a href="/MlangPrintAuto/inserted/" class="service-link-simple">
                                        <span class="service-icon-simple">📄</span>
                                        <span>전단지/리플릿</span>
                                    </a>
                                    <a href="/MlangPrintAuto/NameCard/" class="service-link-simple">
                                        <span class="service-icon-simple">💼</span>
                                        <span>명함</span>
                                    </a>
                                    <a href="/MlangPrintAuto/shop/view_modern.php" class="service-link-simple">
                                        <span class="service-icon-simple">🏷️</span>
                                        <span>일반스티커</span>
                                    </a>
                                    <a href="/MlangPrintAuto/msticker/" class="service-link-simple">
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
            padding: 40px 20px 20px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .footer-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .footer-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .footer-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .footer-icon {
            font-size: 20px;
            margin-right: 10px;
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-card-title {
            color: white;
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .footer-card-content {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .info-item {
            display: flex;
            margin-bottom: 8px;
            align-items: flex-start;
        }

        .info-label {
            font-weight: 600;
            min-width: 50px;
            color: rgba(255, 255, 255, 0.8);
            margin-right: 10px;
        }

        .info-value {
            flex: 1;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }

        .account-holder {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 10px;
        }

        .account-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .account-item {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 13px;
            border-left: 3px solid rgba(255, 255, 255, 0.3);
            transition: all 0.2s ease;
        }

        .account-item:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .account-item.highlight {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            font-weight: 600;
            border-left-color: #f39c12;
            box-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
        }

        .service-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .service-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }

        .service-item.highlight {
            color: #3498db;
            font-weight: 600;
        }

        .service-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .service-links-simple {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .service-link-simple {
            display: flex;
            align-items: center;
            padding: 6px 0;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .service-link-simple:hover {
            color: #3498db;
            transform: translateX(5px);
        }

        .service-icon-simple {
            margin-right: 10px;
            font-size: 16px;
            min-width: 20px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            text-align: center;
        }

        .footer-copyright {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            line-height: 1.6;
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

    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>