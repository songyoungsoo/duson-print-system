<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 페이지 설정
$page_title = '🧲 두손기획인쇄 - 자석스티커 자동견적';
$current_page = 'msticker';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 공통 함수 및 설정
include "../../includes/functions.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);

// 로그 정보 생성
$log_info = generateLogInfo();

// 공통 인증 처리 포함
include "../../includes/auth.php";

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 파일 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';
?>

<style>
/* 자석스티커 페이지 전용 스타일 */
        
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .selection-panel {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        
        .selection-panel h3 {
            margin: 0 0 25px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 1rem;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        .info-panel {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        
        .info-panel h3 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .info-text {
            line-height: 1.6;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .calculate-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn-calculate {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        
        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }
        
        .btn-calculate:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .price-section {
            display: none;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .price-section h3 {
            margin: 0 0 25px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
            text-align: center;
        }
        
        /* 기존 카드 형식 스타일들 (표 형식으로 교체됨) */
        /* 
        .selected-options, .price-details, .total-price 등의 스타일들은 
        quote-table.css의 표 형식으로 대체되었습니다.
        */
        
        /* 표 형식에서 선택된 옵션 값들의 색상 설정 */
        #selectedCategory,
        #selectedSize,
        #selectedQuantity,
        #selectedDesign {
            color: #495057 !important;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-action {
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #17a2b8;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        

        
        .comment-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        
        .comment-section h4 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
        }
        
        .comment-section textarea {
            width: 100%;
            min-height: 100px;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .comment-section textarea:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .zoom-box {
                height: 300px;
            }
        }
        
        /* 이미지 갤러리 스타일 - gallery3.php 방식 */
        .image-gallery-section {
            margin-top: 30px;
        }
        
        .image-gallery-section h4 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
        }
        
        .gallery-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* 확대 박스: 420px 높이 - 적응형 이미지 표시 */
        .zoom-box {
            width: 100%;
            height: 420px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: contain; /* 이미지 전체가 보이도록 */
            background-color: #fff;
            will-change: background-position, background-size;
            cursor: crosshair;
            margin-bottom: 16px;
        }
        
        /* 썸네일 */
        .thumbnails {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .thumbnails img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .thumbnails img.active {
            border-color: #e74c3c;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        }
        
        .thumbnails img:hover {
            border-color: #e74c3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
        }
        
        .gallery-loading, .gallery-error {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        .gallery-error {
            color: #dc3545;
}

@media (max-width: 768px) {
    .form-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .price-details {
        grid-template-columns: 1fr;
    }
    
    .selected-options {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

            <div class="container">
                <!-- 주문 폼 -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🧲 자석스티커 자동견적</h2>
                        <p class="card-subtitle">종이자석과 전체자석 스티커의 정확한 견적을 확인하세요</p>
                    </div>
                    
                    <form id="mstickerForm" method="post">
            <div class="form-container">
                <!-- 선택 옵션 패널 -->
                <div class="selection-panel">
                    <h3>📋 옵션 선택</h3>
                    
                    <div class="form-group">
                        <label for="MY_type">종류</label>
                        <select name="MY_type" id="MY_type" required>
                            <option value="">종류를 선택해주세요</option>
                            <?php
                            $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'msticker');
                            foreach ($categories as $category) {
                                echo "<option value='" . safe_html($category['no']) . "'>" . safe_html($category['title']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="PN_type">규격</label>
                        <select name="PN_type" id="PN_type" required>
                            <option value="">먼저 종류를 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="MY_amount">수량</label>
                        <select name="MY_amount" id="MY_amount" required>
                            <option value="">수량을 선택해주세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ordertype">편집디자인</label>
                        <select name="ordertype" id="ordertype" required>
                            <option value="">편집 방식을 선택해주세요</option>
                            <option value="total">디자인+인쇄</option>
                            <option value="print">인쇄만 의뢰</option>
                        </select>
                    </div>
                </div>

                <!-- 정보 패널 -->
                <div class="info-panel">
                    <!-- <h3>ℹ️ 자석스티커 안내</h3> -->
                    <!-- 자석스티커 안내 텍스트 (주석 처리)
                    <div class="info-text">
                        <p><strong>자석스티커 특징:</strong></p>
                        <ul>
                            <li>종이자석: 후면에 작은 자석이 부착된 형태</li>
                            <li>전체자석: 전면이 모두 자석으로 된 형태</li>
                            <li>냉장고, 화이트보드 등에 부착 가능</li>
                            <li>다양한 규격으로 제작 가능</li>
                        </ul>
                        
                        <p><strong>제작 기간:</strong></p>
                        <ul>
                            <li>일반 자석스티커: 2-3일</li>
                            <li>특수 규격: 3-4일</li>
                            <li>대량 주문: 별도 문의</li>
                        </ul>
                        
                        <p><strong>고객센터:</strong> 02-2632-1830</p>
                    </div>
                    -->
                    
                    <!-- 이미지 갤러리 섹션 추가 -->
                    <div class="image-gallery-section">
                        <h4>🖼️ 자석스티커 샘플</h4>
                        
                        <!-- 부드러운 확대 갤러리 (gallery3.php 방식) -->
                        <div class="gallery-container">
                            <div class="zoom-box" id="zoomBox">
                                <!-- 배경 이미지로 표시됩니다 -->
                            </div>
                            
                            <!-- 썸네일 이미지들 -->
                            <div class="thumbnails" id="thumbnailGrid">
                                <!-- 썸네일들이 여기에 동적으로 로드됩니다 -->
                            </div>
                        </div>
                        
                        <!-- 로딩 상태 -->
                        <div id="galleryLoading" class="gallery-loading">
                            <p>이미지를 불러오는 중...</p>
                        </div>
                        
                        <!-- 에러 상태 -->
                        <div id="galleryError" class="gallery-error" style="display: none;">
                            <p>이미지를 불러올 수 없습니다.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="calculate-section">
                <button type="button" onclick="calculatePrice()" class="btn-calculate">
                    💰 견적 계산하기
                </button>
            </div>

            <!-- 가격 표시 섹션 -->
            <div id="priceSection" class="price-section">
                <h3>💰 견적 결과</h3>
                
                <!-- 견적 결과 표 -->
                <table class="quote-table">
                    <thead>
                        <tr>
                            <th>항목</th>
                            <th>내용</th>
                            <th>금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 옵션 정보 행들 -->
                        <tr>
                            <td>자석스티커 종류</td>
                            <td id="selectedCategory">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>규격</td>
                            <td id="selectedSize">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>수량</td>
                            <td id="selectedQuantity">-</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>편집디자인</td>
                            <td id="selectedDesign">-</td>
                            <td>-</td>
                        </tr>
                        
                        <!-- 가격 정보 행들 -->
                        <tr class="price-row">
                            <td>인쇄비</td>
                            <td>-</td>
                            <td id="printPrice">0원</td>
                        </tr>
                        <tr class="price-row">
                            <td>디자인비</td>
                            <td>-</td>
                            <td id="designPrice">0원</td>
                        </tr>
                        
                        <!-- 합계 행들 -->
                        <tr class="total-row">
                            <td><strong>합계 (부가세 별도)</strong></td>
                            <td>-</td>
                            <td><strong id="priceAmount">0원</strong></td>
                        </tr>
                        <tr class="vat-row">
                            <td><strong>총 금액 (부가세 포함)</strong></td>
                            <td>-</td>
                            <td><strong id="priceVat">0원</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <?php
                // 자석스티커용 업로드 컴포넌트 설정
                $uploadComponent = new FileUploadComponent([
                    'product_type' => 'msticker',
                    'max_file_size' => 12 * 1024 * 1024, // 12MB
                    'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                    'multiple' => true,
                    'drag_drop' => true,
                    'show_progress' => true,
                    'auto_upload' => true,
                    'delete_enabled' => true,
                    'custom_messages' => [
                        'title' => '자석스티커 디자인 파일 업로드',
                        'drop_text' => '자석스티커 디자인 파일을 여기로 드래그하거나 클릭하여 선택하세요',
                        'format_text' => '지원 형식: JPG, PNG, PDF (최대 12MB)'
                    ]
                ]);
                
                // 컴포넌트 렌더링
                echo $uploadComponent->render();
                ?>
                
                <div class="action-buttons">
                    <button type="button" onclick="addToBasket()" class="btn-action btn-primary">
                        🛒 장바구니에 담기
                    </button>
                    <button type="button" onclick="directOrder()" class="btn-action btn-secondary">
                        📋 바로 주문하기
                    </button>
                </div>
            </div>

            <!-- 새로운 업로드 컴포넌트는 이미 위에 적용되어 있음 -->

            <!-- 기타사항 섹션 -->
            <div class="comment-section">
                <h4>📝 기타사항</h4>
                <textarea name="comment" placeholder="추가 요청사항이나 문의사항을 입력해주세요..."></textarea>
            </div>

            <!-- 숨겨진 필드들 -->
            <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
            <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
            <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
            <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
            <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
            <input type="hidden" name="page" value="msticker">
                    </form>
                </div>
            </div>
        </div> <!-- main-content-wrapper 끝 -->   
     
<?php
// 공통 로그인 모달 포함
include "../../includes/login_modal.php";
?>

<?php
// 공통 푸터 포함
include "../../includes/footer.php";
?>    

    <script>
    // PHP 변수를 JavaScript로 전달 (공통함수 활용)
    var phpVars = {
        MultyUploadDir: "../../PHPClass/MultyUpload",
        log_url: "<?php echo safe_html($log_info['url']); ?>",
        log_y: "<?php echo safe_html($log_info['y']); ?>",
        log_md: "<?php echo safe_html($log_info['md']); ?>",
        log_ip: "<?php echo safe_html($log_info['ip']); ?>",
        log_time: "<?php echo safe_html($log_info['time']); ?>",
        page: "msticker"
    };

    // 이미지 갤러리 관련 변수들
    let galleryImages = [];
    let currentImageIndex = 0;
    
    // 갤러리 줌 기능 초기화 - 적응형 이미지 표시 및 확대
    let targetX = 50, targetY = 50;
    let currentX = 50, currentY = 50;
    let targetSize = 100, currentSize = 100;
    let currentImageDimensions = { width: 0, height: 0 };
    let currentImageType = 'large'; // 'small' 또는 'large'
    let originalBackgroundSize = 'contain'; // 원래 배경 크기 저장

    // 파일첨부 관련 함수들은 공통 컴포넌트에서 처리
    
    // 숫자 포맷팅 함수
    function format_number(number) {
        return new Intl.NumberFormat('ko-KR').format(number);
    }
    
    // 로그인 메시지가 있으면 모달 자동 표시
    <?php if (!empty($login_message)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
    });
    <?php endif; ?>
    
    // 페이지 로드 시 갤러리 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 이미지 갤러리 초기화
        loadImageGallery();
        initGalleryZoom();
        animate();
        
        // 드롭다운 이벤트 리스너 추가
        const categorySelect = document.querySelector('select[name="MY_type"]');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                if (this.value) {
                    changeCategoryType(this.value);
                }
            });
        }
    });
    
    // 선택한 옵션 요약을 초기화하는 함수
    function resetSelectedOptions() {
        document.getElementById('selectedCategory').textContent = '-';
        document.getElementById('selectedSize').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('mstickerForm');
        
        // 각 select 요소에서 선택된 옵션의 텍스트 가져오기
        const categorySelect = form.querySelector('select[name="MY_type"]');
        const sizeSelect = form.querySelector('select[name="PN_type"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        // 선택된 옵션의 텍스트 업데이트
        if (categorySelect.selectedIndex > 0) {
            document.getElementById('selectedCategory').textContent = 
                categorySelect.options[categorySelect.selectedIndex].text;
        }
        if (sizeSelect.selectedIndex > 0) {
            document.getElementById('selectedSize').textContent = 
                sizeSelect.options[sizeSelect.selectedIndex].text;
        }
        if (quantitySelect.selectedIndex > 0) {
            document.getElementById('selectedQuantity').textContent = 
                quantitySelect.options[quantitySelect.selectedIndex].text;
        }
        if (designSelect.selectedIndex > 0) {
            document.getElementById('selectedDesign').textContent = 
                designSelect.options[designSelect.selectedIndex].text;
        }
    }    

    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('mstickerForm');
        const formData = new FormData(form);
        
        // 필수 필드 검증
        if (!formData.get('MY_type') || !formData.get('PN_type') || 
            !formData.get('MY_amount') || !formData.get('ordertype')) {
            alert('모든 옵션을 선택해주세요.');
            return;
        }
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        // AJAX로 실제 가격 계산
        const params = new URLSearchParams({
            MY_type: formData.get('MY_type'),
            PN_type: formData.get('PN_type'),
            MY_amount: formData.get('MY_amount'),
            ordertype: formData.get('ordertype')
        });
        
        fetch('calculate_price_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                const priceData = response.data;
                
                // 선택한 옵션들 업데이트
                updateSelectedOptions(formData);
                
                // 가격 정보 표시
                document.getElementById('printPrice').textContent = format_number(priceData.base_price) + '원';
                document.getElementById('designPrice').textContent = format_number(priceData.design_price) + '원';
                document.getElementById('priceAmount').textContent = format_number(priceData.total_price) + '원';
                document.getElementById('priceVat').textContent = format_number(Math.round(priceData.total_with_vat)) + '원';
                
                // 가격 섹션 표시
                document.getElementById('priceSection').style.display = 'block';
                
                // 부드럽게 스크롤
                document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
                
                // 전역 변수에 가격 정보 저장 (장바구니 추가용)
                window.currentPriceData = priceData;
                
            } else {
                alert(response.message || '가격 계산 중 오류가 발생했습니다.');
                document.getElementById('priceSection').style.display = 'none';
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('가격 계산 오류:', error);
            alert('가격 계산 중 오류가 발생했습니다.');
        });
    }
    
    // 장바구니에 추가하는 함수
    function addToBasket() {
        // 가격 계산이 먼저 되었는지 확인
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('mstickerForm');
        const formData = new FormData(form);
        
        // 가격 정보 추가
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.total_price));
        formData.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        formData.set('product_type', 'msticker');
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 추가중...';
        button.disabled = true;
        
        // AJAX로 장바구니에 추가
        fetch('add_to_basket.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (response.success) {
                alert('장바구니에 추가되었습니다! 🛒');
                
                // 장바구니 확인 여부 묻기
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                } else {
                    // 폼 초기화하고 계속 쇼핑
                    document.getElementById('mstickerForm').reset();
                    document.getElementById('priceSection').style.display = 'none';
                    window.currentPriceData = null;
                }
            } else {
                alert('장바구니 추가 중 오류가 발생했습니다: ' + response.message);
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('Error:', error);
            alert('장바구니 추가 중 오류가 발생했습니다.');
        });
    }
    
    // 바로 주문하기 함수
    function directOrder() {
        // 가격 계산이 먼저 되었는지 확인
        if (!window.currentPriceData) {
            alert('먼저 가격을 계산해주세요.');
            return;
        }
        
        const form = document.getElementById('mstickerForm');
        const formData = new FormData(form);
        
        // 주문 정보를 URL 파라미터로 구성
        const params = new URLSearchParams();
        params.set('direct_order', '1');
        params.set('product_type', 'msticker');
        params.set('MY_type', formData.get('MY_type'));
        params.set('PN_type', formData.get('PN_type'));
        params.set('MY_amount', formData.get('MY_amount'));
        params.set('ordertype', formData.get('ordertype'));
        params.set('price', Math.round(window.currentPriceData.total_price));
        params.set('vat_price', Math.round(window.currentPriceData.total_with_vat));
        
        // 선택된 옵션 텍스트도 전달
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const designSelect = document.querySelector('select[name="ordertype"]');
        
        params.set('category_text', categorySelect.options[categorySelect.selectedIndex].text);
        params.set('size_text', sizeSelect.options[sizeSelect.selectedIndex].text);
        params.set('quantity_text', quantitySelect.options[quantitySelect.selectedIndex].text);
        params.set('design_text', designSelect.options[designSelect.selectedIndex].text);
        
        // 주문 페이지로 이동
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 종류 변경 시 규격 동적 업데이트
    function changeCategoryType(categoryNo) {
        console.log('종류 변경:', categoryNo);
        
        // 규격 업데이트
        updateSizes(categoryNo);
        
        // 수량 초기화
        clearQuantities();
    }
    
    function updateSizes(categoryNo) {
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        
        fetch(`get_sizes.php?CV_no=${categoryNo}&page=msticker`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            sizeSelect.innerHTML = '';
            
            if (!response.success || !response.data) {
                sizeSelect.innerHTML = '<option value="">규격 정보가 없습니다</option>';
                console.error('규격 로드 실패:', response.message);
                return;
            }
            
            // 기본 옵션 추가
            sizeSelect.innerHTML = '<option value="">규격을 선택해주세요</option>';
            
            // 새 옵션 추가
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                sizeSelect.appendChild(optionElement);
            });
            
            console.log('규격 업데이트 완료:', response.data.length, '개');
        })
        .catch(error => {
            console.error('규격 업데이트 오류:', error);
            sizeSelect.innerHTML = '<option value="">규격 로드 오류</option>';
        });
    }
    
    // 수량 초기화
    function clearQuantities() {
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        quantitySelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
    }
    
    // 수량 업데이트
    function updateQuantities() {
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        
        const MY_type = categorySelect.value;
        const PN_type = sizeSelect.value;
        
        if (!MY_type || !PN_type) {
            clearQuantities();
            return;
        }
        
        fetch(`get_quantities.php?style=${MY_type}&Section=${PN_type}`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            quantitySelect.innerHTML = '';
            
            if (!response.success || !response.data || response.data.length === 0) {
                quantitySelect.innerHTML = '<option value="">수량 정보가 없습니다</option>';
                console.log('수량 정보 없음:', response.message || '데이터 없음');
                return;
            }
            
            // 기본 옵션 추가
            quantitySelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
            
            // 새 옵션 추가
            response.data.forEach((option, index) => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                if (index === 0) optionElement.selected = true; // 첫 번째 옵션 선택
                quantitySelect.appendChild(optionElement);
            });
            
            console.log('수량 업데이트 완료:', response.data.length, '개');
        })
        .catch(error => {
            console.error('수량 업데이트 오류:', error);
            quantitySelect.innerHTML = '<option value="">수량 로드 오류</option>';
        });
    }
    
    // 숫자 포맷팅 함수
    function format_number(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // 입력값 변경 시 실시간 유효성 검사
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('change', function() {
            if (this.checkValidity()) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e74c3c';
            }
        });
    });
    
    // 페이지 로드 시 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 드롭다운 변경 이벤트 리스너 추가
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const sizeSelect = document.querySelector('select[name="PN_type"]');
        
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                changeCategoryType(this.value);
            });
        }
        
        if (sizeSelect) {
            sizeSelect.addEventListener('change', function() {
                updateQuantities();
            });
        }
        
        // 초기 옵션 선택 시 수량 업데이트
        setTimeout(function() {
            if (categorySelect.value) {
                changeCategoryType(categorySelect.value);
            }
        }, 500);
    });
    
    // === 이미지 갤러리 함수들 ===
    
    // 이미지 갤러리 로드
    function loadImageGallery() {
        const loadingElement = document.getElementById('galleryLoading');
        const errorElement = document.getElementById('galleryError');
        
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        fetch('get_msticker_images.php')
        .then(response => response.json())
        .then(response => {
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            if (response.success && response.data.length > 0) {
                galleryImages = response.data;
                createThumbnails();
                console.log('자석스티커 갤러리 로드 완료:', response.count + '개 이미지');
            } else {
                showGalleryError('자석스티커 샘플 이미지가 없습니다.');
            }
        })
        .catch(error => {
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            showGalleryError('이미지를 불러오는 중 오류가 발생했습니다: ' + error.message);
        });
    }
    
    // 갤러리 오류 표시
    function showGalleryError(message) {
        const errorElement = document.getElementById('galleryError');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    // 갤러리 줌 기능 초기화
    function initGalleryZoom() {
        const zoomBox = document.getElementById('zoomBox');
        
        if (!zoomBox) return;
        
        // 마우스 이동 → 목표 포지션 & 사이즈 설정
        zoomBox.addEventListener('mousemove', e => {
            const { width, height, left, top } = zoomBox.getBoundingClientRect();
            const xPct = (e.clientX - left) / width * 100;
            const yPct = (e.clientY - top) / height * 100;
            targetX = xPct;
            targetY = yPct;
            
            // 이미지 타입에 따른 확대 비율 설정
            if (currentImageType === 'small') {
                targetSize = 130; // 작은 이미지: 1.3배 확대
            } else {
                targetSize = 150; // 큰 이미지: 1.5배 확대
            }
        });
        
        // 마우스 이탈 → 원상태로 복원
        zoomBox.addEventListener('mouseleave', () => {
            targetX = 50;
            targetY = 50;
            targetSize = 100;
        });
        
        console.log('갤러리 줌 기능 초기화 완료');
    }
    
    // 이미지 크기 분석 및 적응형 표시 설정
    function analyzeImageSize(imagePath, callback) {
        const img = new Image();
        img.onload = function() {
            const containerHeight = 420; // 컨테이너 높이
            const containerWidth = document.getElementById('zoomBox').getBoundingClientRect().width;
            
            currentImageDimensions.width = this.naturalWidth;
            currentImageDimensions.height = this.naturalHeight;
            
            let backgroundSize;
            
            // 이미지가 420px 높이보다 작고 비율이 적절하면 1:1 표시
            if (this.naturalHeight <= containerHeight && this.naturalWidth <= containerWidth) {
                backgroundSize = `${this.naturalWidth}px ${this.naturalHeight}px`;
                currentImageType = 'small'; // 작은 이미지로 분류
                console.log('1:1 크기로 표시 (1.3배 확대):', backgroundSize);
            } else {
                // 이미지가 크면 contain으로 전체 모양 보이게
                backgroundSize = 'contain';
                currentImageType = 'large'; // 큰 이미지로 분류
                console.log('전체 비율 맞춤으로 표시 (1.5배 확대): contain');
            }
            
            callback(backgroundSize);
        };
        img.onerror = function() {
            console.log('이미지 로드 실패, 기본 contain 사용');
            currentImageType = 'large';
            callback('contain');
        };
        img.src = imagePath;
    }
    
    // 부드러운 애니메이션 루프
    function animate() {
        const zoomBox = document.getElementById('zoomBox');
        if (!zoomBox) return;
        
        // lerp 계수: 0.15 → 부드러운 추적
        currentX += (targetX - currentX) * 0.15;
        currentY += (targetY - currentY) * 0.15;
        currentSize += (targetSize - currentSize) * 0.15;
        
        zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
        
        // 확대 시에는 항상 퍼센트 방식으로 처리
        if (currentSize > 100.1) { // 확대 중
            // 확대 시에는 이미지가 잘리도록 cover 방식 사용
            zoomBox.style.backgroundSize = `${currentSize}%`;
        } else { // 원래 크기로 복원 중
            // 원래 크기로 복원
            zoomBox.style.backgroundSize = originalBackgroundSize;
        }
        
        requestAnimationFrame(animate);
    }
    
    // 메인 이미지 업데이트 함수
    function updateMainImage(index) {
        if (galleryImages.length === 0) return;
        
        const zoomBox = document.getElementById('zoomBox');
        const image = galleryImages[index];
        
        console.log('메인 이미지 업데이트:', image);
        
        // 이미지 크기 분석 후 적응형 표시
        analyzeImageSize(image.path, function(backgroundSize) {
            // 배경 이미지 및 크기 설정
            zoomBox.style.backgroundImage = `url('${image.path}')`;
            zoomBox.style.backgroundSize = backgroundSize;
            
            // 원래 배경 크기 저장 (애니메이션에서 사용)
            originalBackgroundSize = backgroundSize;
            
            console.log('이미지 적용 완료:', {
                path: image.path,
                size: backgroundSize,
                dimensions: currentImageDimensions
            });
        });
        
        currentImageIndex = index;
        
        // 타겟 상태 초기화
        targetSize = 100;
        targetX = 50;
        targetY = 50;
        
        // 썸네일 active 상태 업데이트
        updateThumbnailActive(index);
    }
    
    // 썸네일 생성 함수
    function createThumbnails() {
        const thumbnailGrid = document.getElementById('thumbnailGrid');
        thumbnailGrid.innerHTML = '';
        
        galleryImages.forEach((image, index) => {
            const thumbnail = document.createElement('img');
            thumbnail.src = image.thumbnail;
            thumbnail.alt = image.title;
            thumbnail.className = index === 0 ? 'active' : '';
            thumbnail.title = image.title;
            thumbnail.dataset.src = image.path;
            
            // 썸네일 클릭 이벤트
            thumbnail.addEventListener('click', () => {
                // 모든 썸네일에서 active 클래스 제거
                const allThumbs = thumbnailGrid.querySelectorAll('img');
                allThumbs.forEach(t => t.classList.remove('active'));
                
                // 클릭된 썸네일에 active 클래스 추가
                thumbnail.classList.add('active');
                
                // 메인 이미지 업데이트
                updateMainImage(index);
            });
            
            thumbnailGrid.appendChild(thumbnail);
        });
        
        // 첫 번째 이미지로 초기화
        if (galleryImages.length > 0) {
            updateMainImage(0);
        }
        
        console.log('썸네일 생성 완료:', galleryImages.length + '개');
    }
    
    // 썸네일 active 상태 업데이트
    function updateThumbnailActive(activeIndex) {
        const thumbnails = document.querySelectorAll('#thumbnailGrid img');
        thumbnails.forEach((thumb, index) => {
            if (index === activeIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }
    </script>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>