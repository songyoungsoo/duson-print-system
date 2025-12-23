            <!-- 페이지 콘텐츠 끝 -->
            
            <!-- 공통 푸터 -->
            <div class="modern-footer">
                <div class="footer-content">
                    <div class="footer-section">
                        <h4>🖨️ 인쇄 서비스</h4>
                        <ul>
                            <li><a href="../shop/view_modern.php">스티커 제작</a></li>
                            <li><a href="../mlangprintauto/NameCard/index_modern.php">명함 제작</a></li>
                            <li><a href="../mlangprintauto/cadarok/index_modern.php">카다록 제작</a></li>
                            <li><a href="../mlangprintauto/LittlePrint/index.php">포스터 제작</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-section">
                        <h4>📋 주문 관리</h4>
                        <ul>
                            <li><a href="../shop/cart.php">장바구니</a></li>
                            <li><a href="../shop/order.php">주문 현황</a></li>
                            <li><a href="../shop/estimate.php">견적 문의</a></li>
                            <li><a href="../shop/support.php">고객 지원</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-section">
                        <h4>📞 고객 센터</h4>
                        <div class="contact-info">
                            <p><strong>전화:</strong> 1588-0000</p>
                            <p><strong>이메일:</strong> info@print.co.kr</p>
                            <p><strong>운영시간:</strong> 평일 09:00-18:00</p>
                            <p><strong>주말:</strong> 토요일 09:00-15:00</p>
                        </div>
                    </div>
                    
                    <div class="footer-section">
                        <h4>💡 주문 안내</h4>
                        <div class="notice-info">
                            <p>• 모든 작업은 입금 확인 후 진행</p>
                            <p>• 택배비는 착불로 진행</p>
                            <p>• 주문 후 파일 업로드 필수</p>
                            <p>• 디자인 수정은 3회까지 무료</p>
                        </div>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; 2024 프리미엄 인쇄 서비스. All rights reserved.</p>
                    <p>고품질 인쇄물을 합리적인 가격으로 제작해드립니다.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
    .modern-footer {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        margin-top: 3rem;
    }
    
    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        padding: 3rem 2rem 2rem;
    }
    
    .footer-section h4 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #ecf0f1;
    }
    
    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-section ul li {
        margin-bottom: 0.5rem;
    }
    
    .footer-section ul li a {
        color: #bdc3c7;
        text-decoration: none;
        transition: color 0.3s ease;
        font-size: 0.95rem;
    }
    
    .footer-section ul li a:hover {
        color: #3498db;
    }
    
    .contact-info p,
    .notice-info p {
        margin: 0.5rem 0;
        color: #bdc3c7;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem 2rem;
        text-align: center;
        background: rgba(0, 0, 0, 0.2);
    }
    
    .footer-bottom p {
        margin: 0.3rem 0;
        color: #95a5a6;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            padding: 2rem 1rem 1rem;
            gap: 1.5rem;
        }
        
        .footer-bottom {
            padding: 1rem;
        }
    }
    </style>

    <!-- 공통 JavaScript -->
    <script>
    // 네비게이션 활성화 상태 관리
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active');
            }
        });
    });
    
    // 부드러운 스크롤 효과
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // 장바구니 아이템 수 표시 (선택사항)
    function updateCartCount() {
        fetch('../shop/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartBtn = document.querySelector('.nav-action-btn.cart');
            if (data.count > 0) {
                cartBtn.innerHTML = `🛒 장바구니 (${data.count})`;
            }
        })
        .catch(error => console.log('Cart count update failed:', error));
    }
    
    // 페이지 로드 시 장바구니 수 업데이트
    updateCartCount();
    </script>
</body>
</html>