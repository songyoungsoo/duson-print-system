<?php 
// 공통 함수 포함
include "../../includes/functions.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 세션 및 기본 설정
$session_id = check_session();

// 데이터베이스 연결
include "../../db.php";
check_db_connection($db);
$connect = $db;

// UTF-8 설정
mysqli_set_charset($connect, "utf8");

// 페이지 설정
$page_title = generate_page_title('포스터');
$current_page = 'poster';
$log_info = generateLogInfo();

// 포스터 관련 설정
$page = "LittlePrint";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 공통함수를 사용하여 초기 데이터 로드

$categoryOptions = getCategoryOptions($connect, $GGTABLE, $page);
$firstCategoryNo = !empty($categoryOptions) ? $categoryOptions[0]['no'] : '590';
$paperTypeOptions = getPaperTypes($connect, $GGTABLE, $firstCategoryNo);
$paperSizeOptions = getPaperSizes($connect, $GGTABLE, $firstCategoryNo);
$quantityOptions = getQuantityOptions($connect);

// 공통 인증 처리 포함
include "../../includes/auth.php";
// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 공통 CSS -->
    <link rel="stylesheet" href="../../css/common_style.css">
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .main-content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .page-header p {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
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
            border-bottom: 2px solid #667eea;
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group small {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .gallery-panel {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        
        .gallery-panel h3 {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .calculate-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn-calculate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-calculate:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* 이미지 갤러리 스타일 */
        .image-gallery-section {
            margin-top: 0;
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
            background-size: contain;
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
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .thumbnails img:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
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
        
        /* 가격 섹션 */
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
        
        .selected-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .option-item {
            text-align: center;
        }
        
        .option-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
            display: block;
        }
        
        .option-value {
            font-weight: 600;
            color: #495057 !important;
            font-size: 1rem;
        }
        
        .price-amount {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
            margin: 20px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .btn-action {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
        }
        
        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .zoom-box {
                height: 300px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-content-wrapper">
        <!-- 페이지 헤더 -->
        <div class="page-header">
            <h1>📄 포스터 견적안내</h1>
            <p>고품질 포스터를 합리적인 가격으로 제작해드립니다</p>
        </div>
        
        <!-- 메인 컨테이너 -->
        <div class="form-container">
            <!-- 선택 패널 -->
            <div class="selection-panel">
                <h3>📝 포스터 주문 옵션 선택</h3>
                
                <form id="littleprintForm" method="post">
                    <input type="hidden" name="action" value="calculate">
                    
                    <div class="form-group">
                        <label for="MY_type">🏷️ 1. 구분</label>
                        <select name="MY_type" id="MY_type" onchange="resetSelectedOptions(); changeCategoryType(this.value)">
                            <?php foreach ($categoryOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                <?php echo htmlspecialchars($option['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small>포스터 종류를 선택해주세요</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="TreeSelect">📄 2. 종이종류</label>
                        <select name="TreeSelect" id="TreeSelect" onchange="resetSelectedOptions(); updateQuantities()">
                            <?php foreach ($paperTypeOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                <?php echo htmlspecialchars($option['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small>용도에 맞는 종이를 선택해주세요</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="PN_type">📏 3. 종이규격</label>
                        <select name="PN_type" id="PN_type" onchange="resetSelectedOptions(); updateQuantities()">
                            <?php foreach ($paperSizeOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option['no']); ?>">
                                <?php echo htmlspecialchars($option['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small>배포 목적에 맞는 크기를 선택해주세요</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="POtype">🔄 4. 인쇄면</label>
                        <select name="POtype" id="POtype" onchange="resetSelectedOptions()">
                            <option value="1" selected>단면 (앞면만)</option>
                            <option value="2">양면 (앞뒤 모두)</option>
                        </select>
                        <small>양면 인쇄 시 더 많은 정보를 담을 수 있습니다</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="MY_amount">📦 5. 수량</label>
                        <select name="MY_amount" id="MY_amount" onchange="resetSelectedOptions()">
                            <option value="">수량을 선택해주세요</option>
                        </select>
                        <small>수량이 많을수록 단가가 저렴해집니다</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="ordertype">✏️ 6. 디자인(편집)</label>
                        <select name="ordertype" id="ordertype" onchange="resetSelectedOptions()">
                            <option value="total">디자인+인쇄 (전체 의뢰)</option>
                            <option value="print">인쇄만 의뢰 (파일 준비완료)</option>
                        </select>
                        <small>디자인 파일이 없으시면 디자인+인쇄를 선택해주세요</small>
                    </div>
                    
                    <div class="calculate-section">
                        <button type="button" onclick="calculatePrice()" class="btn-calculate">
                            💰 실시간 가격 계산하기
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- 갤러리 패널 -->
            <div class="gallery-panel">
                <h3>🖼️ 포스터 샘플</h3>
                
                <!-- 부드러운 확대 갤러리 -->
                <div class="image-gallery-section">
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
        
        <!-- 가격 계산 결과 -->
        <div id="priceSection" class="price-section">
            <h3>💎 견적 결과</h3>
            
            <!-- 선택한 옵션 요약 -->
            <div class="selected-options">
                <div class="option-item">
                    <span class="option-label">🏷️ 구분</span>
                    <span id="selectedCategory" class="option-value">-</span>
                </div>
                <div class="option-item">
                    <span class="option-label">📄 종이종류</span>
                    <span id="selectedPaperType" class="option-value">-</span>
                </div>
                <div class="option-item">
                    <span class="option-label">📏 종이규격</span>
                    <span id="selectedPaperSize" class="option-value">-</span>
                </div>
                <div class="option-item">
                    <span class="option-label">🔄 인쇄면</span>
                    <span id="selectedSides" class="option-value">-</span>
                </div>
                <div class="option-item">
                    <span class="option-label">📦 수량</span>
                    <span id="selectedQuantity" class="option-value">-</span>
                </div>
                <div class="option-item">
                    <span class="option-label">✏️ 디자인</span>
                    <span id="selectedDesign" class="option-value">-</span>
                </div>
            </div>
            
            <div class="price-amount" id="priceAmount">0원</div>
            <div style="text-align: center; margin: 15px 0; color: #6c757d;">
                부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700; color: #28a745;">0원</span>
            </div>
                    
                    <?php
                    // 포스터용 업로드 컴포넌트 설정
                    $uploadComponent = new FileUploadComponent([
                        'product_type' => 'littleprint',
                        'max_file_size' => 20 * 1024 * 1024, // 20MB
                        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
                        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip'],
                        'multiple' => true,
                        'drag_drop' => true,
                        'show_progress' => true,
                        'auto_upload' => true,
                        'delete_enabled' => true,
                        'custom_messages' => [
                            'title' => '포스터 디자인 파일 업로드',
                            'drop_text' => '포스터 디자인 파일을 여기로 드래그하거나 클릭하여 선택하세요',
                            'format_text' => '지원 형식: JPG, PNG, PDF, ZIP (최대 20MB)'
                        ]
                    ]);
                    
                    // 컴포넌트 렌더링
                    echo $uploadComponent->render();
                    ?>
            
            <div class="action-buttons">
                <button onclick="addToBasket()" class="btn-action btn-primary">
                    🛒 장바구니에 담기
                </button>
                <button onclick="directOrder()" class="btn-action btn-secondary">
                    📋 바로 주문하기
                </button>
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
        page: "LittlePrint"
    };

    // 파일첨부 관련 함수들
    function small_window(url) {
        window.open(url, 'FileUpload', 'width=500,height=400,scrollbars=yes,resizable=yes');
    }

    function deleteSelectedItemsFromList(selectObj) {
        var i;
        for (i = selectObj.options.length - 1; i >= 0; i--) {
            if (selectObj.options[i].selected) {
                selectObj.options[i] = null;
            }
        }
    }

    function addToParentList(srcList) {
        var parentList = document.littleprintForm.parentList;
        for (var i = 0; i < srcList.options.length; i++) {
            if (srcList.options[i] != null) {
                parentList.options[parentList.options.length] = new Option(srcList.options[i].text, srcList.options[i].value);
            }
        }
    }
    
    // === 갤러리 시스템 관련 변수들 ===
    let galleryImages = [];
    let currentImageIndex = 0;

    // 갤러리 줌 기능 초기화 - 적응형 이미지 표시 및 확대
    let targetX = 50, targetY = 50;
    let currentX = 50, currentY = 50;
    let targetSize = 100, currentSize = 100;
    let currentImageDimensions = { width: 0, height: 0 };
    let currentImageType = 'large'; // 'small' 또는 'large'
    let originalBackgroundSize = 'contain'; // 원래 배경 크기 저장

    // 페이지 로드 시 갤러리 초기화
    document.addEventListener('DOMContentLoaded', function() {
        // 이미지 갤러리 초기화
        loadImageGallery();
        initGalleryZoom();
        animate();
        
        // 로그인 메시지가 있으면 모달 자동 표시
        <?php if (!empty($login_message)): ?>
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
        <?php endif; ?>
    
    // 선택한 옵션 요약을 초기화하는 함수
    function resetSelectedOptions() {
        document.getElementById('selectedCategory').textContent = '-';
        document.getElementById('selectedPaperType').textContent = '-';
        document.getElementById('selectedPaperSize').textContent = '-';
        document.getElementById('selectedSides').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.getElementById('littleprintForm');
        
        // 각 select 요소에서 선택된 옵션의 텍스트 가져오기
        const categorySelect = form.querySelector('select[name="MY_type"]');
        const paperTypeSelect = form.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = form.querySelector('select[name="PN_type"]');
        const sidesSelect = form.querySelector('select[name="POtype"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const designSelect = form.querySelector('select[name="ordertype"]');
        
        // 선택된 옵션의 텍스트 업데이트
        document.getElementById('selectedCategory').textContent = 
            categorySelect.options[categorySelect.selectedIndex].text;
        document.getElementById('selectedPaperType').textContent = 
            paperTypeSelect.options[paperTypeSelect.selectedIndex].text;
        document.getElementById('selectedPaperSize').textContent = 
            paperSizeSelect.options[paperSizeSelect.selectedIndex].text;
        document.getElementById('selectedSides').textContent = 
            sidesSelect.options[sidesSelect.selectedIndex].text;
        document.getElementById('selectedQuantity').textContent = 
            quantitySelect.options[quantitySelect.selectedIndex].text;
        document.getElementById('selectedDesign').textContent = 
            designSelect.options[designSelect.selectedIndex].text;
    }    

    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('littleprintForm');
        const formData = new FormData(form);
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        // AJAX로 실제 가격 계산
        const params = new URLSearchParams({
            MY_type: formData.get('MY_type'),
            PN_type: formData.get('PN_type'),
            TreeSelect: formData.get('TreeSelect'),
            MY_amount: formData.get('MY_amount'),
            ordertype: formData.get('ordertype'),
            POtype: formData.get('POtype')
        });
        
        fetch('calculate_price_ajax.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (data.success) {
                const priceData = data.data;
                
                // 선택한 옵션들 업데이트
                updateSelectedOptions(formData);
                
                // 가격 정보 표시
                document.getElementById('priceAmount').textContent = priceData.Order_Price + '원';
                document.getElementById('priceVat').textContent = Math.round(priceData.Total_PriceForm).toLocaleString() + '원';
                
                // 가격 섹션 표시
                document.getElementById('priceSection').style.display = 'block';
                
                // 부드럽게 스크롤
                document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
                
                // 전역 변수에 가격 정보 저장 (장바구니 추가용)
                window.currentPriceData = priceData;
                
            } else {
                alert(data.error.message);
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
        
        const form = document.getElementById('littleprintForm');
        const formData = new FormData(form);
        
        // 가격 정보 추가
        formData.set('action', 'add_to_basket');
        formData.set('price', Math.round(window.currentPriceData.Order_PriceForm));
        formData.set('vat_price', Math.round(window.currentPriceData.Total_PriceForm));
        formData.set('product_type', 'poster');
        
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
        .then(data => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (data.success) {
                alert('장바구니에 추가되었습니다! 🛒');
                
                // 장바구니 확인 여부 묻기
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = '/MlangPrintAuto/shop/cart.php';
                } else {
                    // 폼 초기화하고 계속 쇼핑
                    document.getElementById('littleprintForm').reset();
                    document.getElementById('priceSection').style.display = 'none';
                    window.currentPriceData = null;
                }
            } else {
                alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
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
        
        const form = document.getElementById('littleprintForm');
        const formData = new FormData(form);
        
        // 주문 정보를 URL 파라미터로 구성
        const params = new URLSearchParams();
        params.set('direct_order', '1');
        params.set('product_type', 'poster');
        params.set('MY_type', formData.get('MY_type'));
        params.set('TreeSelect', formData.get('TreeSelect'));
        params.set('PN_type', formData.get('PN_type'));
        params.set('POtype', formData.get('POtype'));
        params.set('MY_amount', formData.get('MY_amount'));
        params.set('ordertype', formData.get('ordertype'));
        params.set('price', Math.round(window.currentPriceData.Order_PriceForm));
        params.set('vat_price', Math.round(window.currentPriceData.Total_PriceForm));
        
        // 선택된 옵션 텍스트도 전달
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        const sidesSelect = document.querySelector('select[name="POtype"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        const designSelect = document.querySelector('select[name="ordertype"]');
        
        params.set('category_text', categorySelect.options[categorySelect.selectedIndex].text);
        params.set('paper_type_text', paperTypeSelect.options[paperTypeSelect.selectedIndex].text);
        params.set('paper_size_text', paperSizeSelect.options[paperSizeSelect.selectedIndex].text);
        params.set('sides_text', sidesSelect.options[sidesSelect.selectedIndex].text);
        params.set('quantity_text', quantitySelect.options[quantitySelect.selectedIndex].text);
        params.set('design_text', designSelect.options[designSelect.selectedIndex].text);
        
        // 주문 페이지로 이동
        window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
    }
    
    // 구분 변경 시 종이종류와 종이규격 동적 업데이트
    function changeCategoryType(categoryNo) {
        console.log('구분 변경:', categoryNo);
        
        // 종이종류 업데이트
        updatePaperTypes(categoryNo);
        
        // 종이규격 업데이트
        updatePaperSizes(categoryNo);
        
        // 수량 초기화
        clearQuantities();
    }
    
    function updatePaperTypes(categoryNo) {
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        
        fetch(`get_paper_types.php?CV_no=${categoryNo}&page=LittlePrint`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            paperTypeSelect.innerHTML = '';
            
            if (!response.success || !response.data) {
                console.error('종이종류 로드 실패:', response.message);
                return;
            }
            
            // 새 옵션 추가
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                paperTypeSelect.appendChild(optionElement);
            });
            
            console.log('종이종류 업데이트 완료:', response.data.length, '개');
        })
        .catch(error => {
            console.error('종이종류 업데이트 오류:', error);
        });
    }
    
    function updatePaperSizes(categoryNo) {
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        
        fetch(`get_paper_sizes.php?CV_no=${categoryNo}&page=LittlePrint`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            paperSizeSelect.innerHTML = '';
            
            if (!response.success || !response.data) {
                console.error('종이규격 로드 실패:', response.message);
                return;
            }
            
            // 새 옵션 추가
            response.data.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.no;
                optionElement.textContent = option.title;
                paperSizeSelect.appendChild(optionElement);
            });
            
            console.log('종이규격 업데이트 완료:', response.data.length, '개');
            
            // 종이규격이 변경되면 수량도 업데이트
            updateQuantities();
        })
        .catch(error => {
            console.error('종이규격 업데이트 오류:', error);
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
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        const quantitySelect = document.querySelector('select[name="MY_amount"]');
        
        const MY_type = categorySelect.value;
        const TreeSelect = paperTypeSelect.value;
        const PN_type = paperSizeSelect.value;
        
        if (!MY_type || !TreeSelect || !PN_type) {
            clearQuantities();
            return;
        }
        
        fetch(`get_quantities.php?style=${MY_type}&Section=${PN_type}&TreeSelect=${TreeSelect}`)
        .then(response => response.json())
        .then(response => {
            // 기존 옵션 제거
            quantitySelect.innerHTML = '';
            
            if (!response.success || !response.data || response.data.length === 0) {
                quantitySelect.innerHTML = '<option value="">수량 정보가 없습니다</option>';
                console.log('수량 정보 없음:', response.message || '데이터 없음');
                return;
            }
            
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
    
    // 페이지 로드 시 초기 수량 로드
    document.addEventListener('DOMContentLoaded', function() {
        // 드롭다운 변경 이벤트 리스너 추가
        const categorySelect = document.querySelector('select[name="MY_type"]');
        const paperTypeSelect = document.querySelector('select[name="TreeSelect"]');
        const paperSizeSelect = document.querySelector('select[name="PN_type"]');
        
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                changeCategoryType(this.value);
            });
        }
        
        if (paperTypeSelect) {
            paperTypeSelect.addEventListener('change', function() {
                updateQuantities();
            });
        }
        
        if (paperSizeSelect) {
            paperSizeSelect.addEventListener('change', function() {
                updateQuantities();
            });
        }
        
        // 약간의 지연 후 초기 수량 업데이트 (다른 드롭다운이 로드된 후)
        setTimeout(function() {
            updateQuantities();
        }, 1000);
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
        
        fetch('get_poster_images.php')
        .then(response => response.json())
        .then(response => {
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            if (response.success && response.data.length > 0) {
                galleryImages = response.data;
                createThumbnails();
                console.log('포스터 갤러리 로드 완료:', response.count + '개 이미지');
            } else {
                showGalleryError('포스터 샘플 이미지가 없습니다.');
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