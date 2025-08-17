<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// 페이지 설정
$page_title = '📄 두손기획인쇄 - 프리미엄 전단지 포트폴리오';
$current_page = 'leaflet';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 공통 인증 처리 포함
include "../includes/auth.php";

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";
?>

<div class="container">
    <!-- 히어로 섹션 -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">📄 전단지 포트폴리오</h1>
            <p class="hero-subtitle">두손기획인쇄의 전단지 제작 사례를 확인하고 온라인으로 주문하세요</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">1,000+</div>
                    <div class="stat-label">제작 사례</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">당일</div>
                    <div class="stat-label">출고 가능</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">A4~A3</div>
                    <div class="stat-label">다양한 규격</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 포트폴리오 갤러리 섹션 -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🎨 전단지 제작 사례</h2>
            <p class="card-subtitle">다양한 업종의 전단지 제작 사례를 확인해보세요</p>
        </div>
        
        <div class="portfolio-gallery">
            <?php
            // 게시판 설정
            $CATEGORY = "전단지";
            $BBS_CODE = "portfolio";
            $BbsDir = "../bbs/";
            $DbDir = "..";
            $table = "$BBS_CODE";

            // 게시판 데이터 가져오기
            $query = "SELECT * FROM $table WHERE category='$CATEGORY' OR category='' ORDER BY no DESC LIMIT 12";
            $result = mysqli_query($connect, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $count = 0;
                while ($row = mysqli_fetch_array($result)) {
                    $count++;
                    $image_url = !empty($row['imgfile']) ? "/bbs/data/portfolio/{$row['imgfile']}" : "/img/no-image.jpg";
                    $title = !empty($row['title']) ? htmlspecialchars($row['title']) : "전단지 샘플 #{$count}";
                    $content = !empty($row['content']) ? strip_tags($row['content']) : "두손기획인쇄에서 제작한 고품질 전단지입니다.";
                    
                    // 내용이 너무 길면 자르기
                    if (strlen($content) > 100) {
                        $content = substr($content, 0, 100) . "...";
                    }
            ?>
                <div class="portfolio-item">
                    <div class="portfolio-image" onclick="openLightbox('<?php echo $image_url; ?>', '<?php echo $title; ?>')">
                        <img src="<?php echo $image_url; ?>" alt="<?php echo $title; ?>" loading="lazy">
                        <div class="portfolio-overlay">
                            <span class="zoom-icon">🔍</span>
                            <span class="overlay-text">클릭하여 확대</span>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h4 class="portfolio-title"><?php echo $title; ?></h4>
                        <p class="portfolio-description"><?php echo $content; ?></p>
                        <div class="portfolio-meta">
                            <span class="portfolio-date"><?php echo date('Y.m.d', strtotime($row['wdate'])); ?></span>
                            <span class="portfolio-views">👁️ <?php echo number_format($row['count']); ?></span>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
            ?>
                <div class="no-portfolio">
                    <div class="no-portfolio-icon">📄</div>
                    <h3>포트폴리오를 준비 중입니다</h3>
                    <p>곧 다양한 전단지 제작 사례를 선보일 예정입니다.</p>
                </div>
            <?php
            }
            ?>
        </div>
        
        <div class="portfolio-actions">
            <a href="http://duson.ipdisk.co.kr:5544/piwigo/index.php?/category/1" target="_blank" class="btn-action btn-outline">
                🖼️ 더 많은 샘플 보기
            </a>
        </div>
    </div>

    <!-- 가격 정보 및 주문 섹션 -->
    <div class="pricing-section">
        <div class="pricing-card">
            <div class="pricing-header">
                <h3>💰 전단지 제작 가격</h3>
                <p>투명하고 합리적인 가격으로 제공합니다</p>
            </div>
            
            <div class="pricing-features">
                <div class="feature-grid">
                    <div class="feature-item">
                        <span class="feature-icon">🎨</span>
                        <div class="feature-content">
                            <h4>디자인 편집비</h4>
                            <p>A4, 16절 단면: 30,000~40,000원<br>
                               A4, 16절 양면: 60,000원<br>
                               A3, 8절 단면: 50,000원, 양면: 90,000원</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📄</span>
                        <div class="feature-content">
                            <h4>파일 제공시</h4>
                            <p>디자인 완료된 파일 제공시<br>
                               편집비 무료</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">⏰</span>
                        <div class="feature-content">
                            <h4>제작 기간</h4>
                            <p>당일 오전 접수시<br>
                               익일 오후 3시 출고 (2-3일간)</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🚚</span>
                        <div class="feature-content">
                            <h4>배송 안내</h4>
                            <p>배송비 별도, 부가세 별도<br>
                               전국 택배 배송 가능</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="important-notices">
                <h4>📋 주문시 유의사항</h4>
                <ul class="notice-list">
                    <li>인쇄시 정매에서 약간의 로스분이 발생할 수 있습니다</li>
                    <li>모조견적은 견적안내 시스템을 이용해주세요</li>
                    <li>이메일이나 웹하드 업로드시 연락처를 반드시 남겨주세요</li>
                    <li><a href="/sub/attention.htm" target="_blank" class="notice-link">📝 작업시 유의사항</a>을 꼭 확인해주세요</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 주문 버튼 섹션 -->
    <div class="order-section">
        <div class="order-card">
            <div class="order-header">
                <h3>🚀 지금 바로 주문하세요!</h3>
                <p>실시간 가격 계산과 간편한 온라인 주문</p>
            </div>
            
            <div class="order-buttons">
                <a href="/MlangPrintAuto/inserted/index.php" class="btn-order btn-primary">
                    <span class="btn-icon">💰</span>
                    <span class="btn-text">
                        <strong>실시간 가격계산</strong>
                        <small>옵션 선택 후 즉시 견적 확인</small>
                    </span>
                </a>
                
                <a href="/MlangPrintAuto/inserted/index.php" class="btn-order btn-secondary">
                    <span class="btn-icon">📋</span>
                    <span class="btn-text">
                        <strong>온라인 주문하기</strong>
                        <small>파일 업로드 후 바로 주문</small>
                    </span>
                </a>
            </div>
            
            <div class="contact-info-section">
                <p class="contact-text">📞 전화 주문도 가능합니다</p>
                <div class="contact-numbers">
                    <span class="contact-number">1688-2384</span>
                    <span class="contact-divider">|</span>
                    <span class="contact-number">02-2632-1830</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 라이트박스 -->
<div id="image-lightbox" class="lightbox">
    <div class="lightbox-content">
        <img id="lightbox-image" src="" alt="">
        <div class="lightbox-caption" id="lightbox-caption"></div>
    </div>
    <div class="lightbox-close" onclick="closeLightbox()">×</div>
</div>

<?php
// 공통 로그인 모달 포함
include "../includes/login_modal.php";
?>

<?php
// 공통 푸터 포함
include "../includes/footer.php";
?>

<script>
// 라이트박스 기능
function openLightbox(imageSrc, caption) {
    document.getElementById('lightbox-image').src = imageSrc;
    document.getElementById('lightbox-caption').textContent = caption;
    document.getElementById('image-lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('image-lightbox').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// 이벤트 리스너
document.addEventListener('DOMContentLoaded', function() {
    // 라이트박스 이미지 클릭 시 닫기
    document.getElementById('lightbox-image').addEventListener('click', function() {
        closeLightbox();
    });
    
    // 라이트박스 배경 클릭 시 닫기
    document.getElementById('image-lightbox').addEventListener('click', function(e) {
        if (e.target.id === 'image-lightbox') {
            closeLightbox();
        }
    });
    
    // ESC 키로 라이트박스 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
});

// 로그인 메시지가 있으면 모달 자동 표시
<?php if (!empty($login_message)): ?>
document.addEventListener('DOMContentLoaded', function() {
    showLoginModal();
    <?php if (strpos($login_message, '성공') !== false): ?>
    setTimeout(hideLoginModal, 2000);
    <?php endif; ?>
});
<?php endif; ?>
</script>

<style>
/* 추가 스타일링 */
.portfolio-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin: 2rem 0;
}

.portfolio-item {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.portfolio-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.portfolio-image {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.portfolio-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.portfolio-item:hover .portfolio-image img {
    transform: scale(1.05);
}

.portfolio-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(52, 152, 219, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: white;
}

.portfolio-item:hover .portfolio-overlay {
    opacity: 1;
}

.zoom-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.overlay-text {
    font-size: 0.9rem;
    font-weight: 600;
}

.portfolio-info {
    padding: 20px;
}

.portfolio-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
    line-height: 1.3;
}

.portfolio-description {
    color: #7f8c8d;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 15px;
}

.portfolio-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #95a5a6;
}

.portfolio-actions {
    text-align: center;
    margin-top: 2rem;
}

.no-portfolio {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: #7f8c8d;
}

.no-portfolio-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.pricing-section {
    margin: 3rem 0;
}

.pricing-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.pricing-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.pricing-header h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.pricing-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2.5rem;
}

.feature-item {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.feature-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.feature-content h4 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
}

.feature-content p {
    color: #7f8c8d;
    line-height: 1.6;
    font-size: 0.95rem;
}

.important-notices {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    border-left: 5px solid #3498db;
}

.important-notices h4 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.notice-list {
    list-style: none;
    padding: 0;
}

.notice-list li {
    padding: 8px 0;
    padding-left: 25px;
    position: relative;
    color: #5d6d7e;
    line-height: 1.5;
}

.notice-list li:before {
    content: "▪";
    position: absolute;
    left: 0;
    color: #3498db;
    font-weight: bold;
}

.notice-link {
    color: #e74c3c;
    text-decoration: none;
    font-weight: 600;
}

.notice-link:hover {
    text-decoration: underline;
}

.order-section {
    margin: 3rem 0;
}

.order-card {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border-radius: 20px;
    padding: 2.5rem;
    text-align: center;
}

.order-header h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.order-header p {
    opacity: 0.9;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.order-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 2rem;
}

.btn-order {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px 25px;
    border-radius: 15px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.btn-order.btn-primary {
    background: white;
    color: #3498db;
}

.btn-order.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255,255,255,0.3);
    background: #f8f9fa;
}

.btn-order.btn-secondary {
    background: rgba(255,255,255,0.15);
    color: white;
    border-color: rgba(255,255,255,0.3);
}

.btn-order.btn-secondary:hover {
    transform: translateY(-3px);
    background: rgba(255,255,255,0.25);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.btn-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.btn-text {
    text-align: left;
    flex: 1;
}

.btn-text strong {
    display: block;
    font-size: 1.1rem;
    margin-bottom: 3px;
}

.btn-text small {
    opacity: 0.8;
    font-size: 0.9rem;
}

.contact-info-section {
    background: rgba(255,255,255,0.1);
    padding: 20px;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.contact-text {
    margin-bottom: 10px;
    opacity: 0.9;
}

.contact-numbers {
    font-size: 1.2rem;
    font-weight: 700;
}

.contact-number {
    color: #fff;
}

.contact-divider {
    margin: 0 15px;
    opacity: 0.6;
}

/* 라이트박스 스타일 */
.lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.lightbox.active {
    opacity: 1;
    visibility: visible;
}

.lightbox-content {
    max-width: 90%;
    max-height: 90%;
    position: relative;
}

.lightbox-content img {
    max-width: 100%;
    max-height: 80vh;
    display: block;
    margin: 0 auto;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
}

.lightbox-caption {
    color: white;
    text-align: center;
    padding: 15px;
    font-size: 1.1rem;
    font-weight: 600;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    color: white;
    font-size: 30px;
    cursor: pointer;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    background-color: rgba(0,0,0,0.5);
    border-radius: 50%;
    transition: all 0.3s ease;
}

.lightbox-close:hover {
    background-color: rgba(255,0,0,0.7);
    transform: scale(1.1);
}

/* 반응형 */
@media (max-width: 768px) {
    .portfolio-gallery {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .feature-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .order-buttons {
        grid-template-columns: 1fr;
    }
    
    .contact-numbers {
        font-size: 1rem;
    }
}
</style>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>