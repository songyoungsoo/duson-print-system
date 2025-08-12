<?php 
// 🎨 포스터 자동견적 - 스마트 컴포넌트 적용 버전
// 기존 포스터 시스템에 스마트 컴포넌트 시스템을 적용한 예제

// 스마트 컴포넌트 포함
include "../../includes/ProductFieldMapper.php";
include "../../includes/SmartFieldComponent.php";

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
$page_title = generate_page_title('포스터 (스마트 컴포넌트)');
$current_page = 'poster';
$log_info = generateLogInfo();

// 스마트 컴포넌트 생성
$smartComponent = new SmartFieldComponent($db, 'poster');

// 공통 인증 처리 포함
include "../../includes/auth.php";
// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 스마트 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalPriceHandler.js"></script>';
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
        
        .page-header .subtitle {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .page-header .smart-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-top: 10px;
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
        
        /* 스마트 필드 그룹 스타일 */
        .smart-field-group {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .field-group-title {
            margin: 0 0 20px 0;
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group .field-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 1rem;
        }
        
        .form-group .field-label strong {
            color: #28a745;
        }
        
        .form-group .field-label small {
            color: #6c757d;
            font-weight: 400;
            font-size: 0.85rem;
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
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }
        
        .form-group select.smart-field {
            border-left: 4px solid #28a745;
        }
        
        .form-control.smart-field:hover {
            border-color: #28a745;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.1);
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .btn-calculate:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* 이미지 갤러리 스타일 - 기존과 동일 */
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
            border-color: #28a745;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        
        .thumbnails img:hover {
            border-color: #28a745;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
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
            color: #495057;
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
        
        /* 스마트 컴포넌트 상태 표시 */
        .smart-status {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* 로딩 애니메이션 */
        .loading {
            opacity: 0.6;
            position: relative;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
            animation: loading-shimmer 1.5s infinite;
        }
        
        @keyframes loading-shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
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
            
            .smart-status {
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content-wrapper">
        <!-- 페이지 헤더 -->
        <div class="page-header">
            <h1>📄 포스터 자동견적</h1>
            <p class="subtitle">스마트 컴포넌트로 더욱 편리한 주문 경험을 제공합니다</p>
            <div class="smart-badge">🔧 Smart Component v2.0</div>
        </div>
        
        <!-- 메인 컨테이너 -->
        <div class="form-container">
            <!-- 선택 패널 -->
            <div class="selection-panel">
                <h3>🎨 포스터 주문 옵션 선택</h3>
                
                <!-- 스마트 컴포넌트로 생성된 폼 -->
                <form name="choiceForm" id="posterForm" method="post" data-product="poster">
                    <input type="hidden" name="action" value="calculate">
                    
                    <?php
                    // 스마트 컴포넌트로 모든 필드 렌더링
                    echo $smartComponent->renderAllFields([], [
                        'MY_type' => [
                            'help_text' => '포스터 용도에 맞는 종류를 선택해주세요'
                        ],
                        'MY_Fsd' => [
                            'help_text' => '용도와 예산에 맞는 종이를 선택해주세요'
                        ],
                        'PN_type' => [
                            'help_text' => '배포 목적에 맞는 크기를 선택해주세요'
                        ],
                        'POtype' => [
                            'help_text' => '양면 인쇄 시 더 많은 정보를 담을 수 있습니다'
                        ],
                        'MY_amount' => [
                            'help_text' => '수량이 많을수록 단가가 저렴해집니다'
                        ],
                        'ordertype' => [
                            'help_text' => '디자인 파일이 없으시면 디자인+인쇄를 선택해주세요'
                        ]
                    ]);
                    ?>
                    
                    <div class="calculate-section">
                        <button type="button" onclick="window.universalPriceHandler.calculatePrice()" class="btn-calculate">
                            🧮 스마트 가격 계산하기
                        </button>
                    </div>
                    
                    <!-- 숨겨진 가격 필드들 (JavaScript에서 채움) -->
                    <input type="hidden" name="Price" value="">
                    <input type="hidden" name="DS_Price" value="">
                    <input type="hidden" name="Order_Price" value="">
                    <input type="hidden" name="PriceForm" value="">
                    <input type="hidden" name="DS_PriceForm" value="">
                    <input type="hidden" name="Order_PriceForm" value="">
                    <input type="hidden" name="VAT_PriceForm" value="">
                    <input type="hidden" name="Total_PriceForm" value="">
                    <input type="hidden" name="StyleForm" value="">
                    <input type="hidden" name="SectionForm" value="">
                    <input type="hidden" name="QuantityForm" value="">
                    <input type="hidden" name="DesignForm" value="">
                </form>
            </div>
            
            <!-- 갤러리 패널 -->
            <div class="gallery-panel">
                <h3>🖼️ 포스터 샘플</h3>
                
                <!-- 부드러운 확대 갤러리 (기존과 동일) -->
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
            <div class="selected-options" id="selectedOptionsContainer">
                <!-- JavaScript에서 동적으로 채워짐 -->
            </div>
            
            <div class="price-display" style="text-align: center; font-size: 2rem; font-weight: 700; color: #28a745; margin: 20px 0;">
                0원
            </div>
            <div style="text-align: center; margin: 15px 0; color: #6c757d;">
                부가세 포함: <span class="total-display" style="font-size: 1.5rem; font-weight: 700; color: #28a745;">0원</span>
            </div>
                    
            <?php
            // 포스터용 업로드 컴포넌트 설정 (기존과 동일)
            $uploadComponent = new FileUploadComponent([
                'product_type' => 'poster',
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
     
    <!-- 스마트 컴포넌트 상태 표시 -->
    <div class="smart-status">
        🔧 Smart Component Active
    </div>
    
<?php
// 공통 로그인 모달 포함
include "../../includes/login_modal.php";
?>

<?php
// 공통 푸터 포함
include "../../includes/footer.php";
?>    

<script>
// 🧮 스마트 컴포넌트 통합 JavaScript

// === 갤러리 시스템 관련 변수들 === (기존과 동일)
let galleryImages = [];
let currentImageIndex = 0;

// 갤러리 줌 기능 초기화
let targetX = 50, targetY = 50;
let currentX = 50, currentY = 50;
let targetSize = 100, currentSize = 100;
let currentImageDimensions = { width: 0, height: 0 };
let currentImageType = 'large';
let originalBackgroundSize = 'contain';

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 포스터 스마트 컴포넌트 시스템 초기화 시작');
    
    // 이미지 갤러리 초기화
    loadImageGallery();
    initGalleryZoom();
    animate();
    
    // 스마트 컴포넌트 이벤트 리스너 등록
    initSmartComponents();
    
    // 가격 업데이트 이벤트 리스너
    document.addEventListener('priceUpdated', function(e) {
        console.log('💰 가격 업데이트 이벤트:', e.detail);
        updatePriceDisplay(e.detail.priceData);
        showPriceSection();
    });
    
    console.log('✅ 포스터 스마트 컴포넌트 시스템 초기화 완료');
});

// 스마트 컴포넌트 초기화
function initSmartComponents() {
    console.log('🔧 스마트 컴포넌트 이벤트 리스너 등록');
    
    // 모든 스마트 필드에 변경 이벤트 리스너 추가
    document.querySelectorAll('.smart-field').forEach(field => {
        field.addEventListener('change', function() {
            console.log(`📝 필드 변경: ${this.name} = ${this.value}`);
            
            // 선택된 옵션 요약 업데이트
            updateSelectedOptionsSummary();
            
            // 가격 섹션 숨기기 (재계산 필요)
            document.getElementById('priceSection').style.display = 'none';
        });
    });
}

// 선택된 옵션 요약 업데이트
function updateSelectedOptionsSummary() {
    const form = document.getElementById('posterForm');
    const container = document.getElementById('selectedOptionsContainer');
    
    // 스마트 필드들의 현재 값 수집
    const smartFields = form.querySelectorAll('.smart-field');
    const options = [];
    
    smartFields.forEach(field => {
        if (field.value && field.selectedIndex > 0) {
            const context = getFieldContext(field);
            const selectedText = field.options[field.selectedIndex].text;
            
            options.push({
                icon: context.icon,
                label: context.label,
                value: selectedText
            });
        }
    });
    
    // 옵션 요약 HTML 생성
    container.innerHTML = options.map(option => `
        <div class="option-item">
            <span class="option-label">${option.icon} ${option.label}</span>
            <span class="option-value">${option.value}</span>
        </div>
    `).join('');
}

// 필드의 컨텍스트 정보 가져오기 (ProductFieldMapper에서)
function getFieldContext(fieldElement) {
    // 기본값 (실제로는 PHP에서 전달받아야 함)
    const contexts = {
        'MY_type': { icon: '🎨', label: '구분' },
        'MY_Fsd': { icon: '📄', label: '종이종류' },
        'PN_type': { icon: '📏', label: '종이규격' },
        'POtype': { icon: '🔄', label: '인쇄면' },
        'MY_amount': { icon: '📊', label: '수량' },
        'ordertype': { icon: '✏️', label: '편집비' }
    };
    
    return contexts[fieldElement.name] || { icon: '📝', label: fieldElement.name };
}

// 가격 표시 업데이트 (UniversalPriceHandler와 연동)
function updatePriceDisplay(priceData) {
    console.log('💰 가격 표시 업데이트:', priceData);
    
    // 가격 표시 업데이트
    const priceDisplay = document.querySelector('.price-display');
    const totalDisplay = document.querySelector('.total-display');
    
    if (priceDisplay && priceData.Order_Price) {
        priceDisplay.textContent = priceData.Order_Price;
    }
    
    if (totalDisplay && priceData.Total_PriceForm) {
        const totalPrice = Math.round(priceData.Total_PriceForm);
        totalDisplay.textContent = totalPrice.toLocaleString('ko-KR') + '원';
    }
}

// 가격 섹션 표시
function showPriceSection() {
    const priceSection = document.getElementById('priceSection');
    priceSection.style.display = 'block';
    
    // 부드럽게 스크롤
    priceSection.scrollIntoView({ behavior: 'smooth' });
}

// 장바구니에 추가하는 함수
function addToBasket() {
    const form = document.getElementById('posterForm');
    const formData = new FormData(form);
    
    // 가격 정보 확인
    const priceForm = form.PriceForm ? form.PriceForm.value : '';
    const totalForm = form.Total_PriceForm ? form.Total_PriceForm.value : '';
    
    if (!priceForm || !totalForm) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    // 장바구니 추가 데이터 준비
    formData.set('action', 'add_to_basket');
    formData.set('product_type', 'poster');
    formData.set('price', priceForm);
    formData.set('vat_price', totalForm);
    
    // 로딩 표시
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ 추가중...';
    button.disabled = true;
    
    // AJAX로 장바구니에 추가
    fetch('/shop/add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        if (data.success) {
            alert('✅ 장바구니에 추가되었습니다! 🛒');
            
            if (confirm('장바구니를 확인하시겠습니까?')) {
                window.location.href = '/shop/cart.php';
            } else {
                // 폼 초기화하고 계속 쇼핑
                form.reset();
                document.getElementById('priceSection').style.display = 'none';
            }
        } else {
            alert('❌ 장바구니 추가 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        button.innerHTML = originalText;
        button.disabled = false;
        console.error('Error:', error);
        alert('❌ 장바구니 추가 중 오류가 발생했습니다.');
    });
}

// 바로 주문하기 함수
function directOrder() {
    const form = document.getElementById('posterForm');
    const priceForm = form.PriceForm ? form.PriceForm.value : '';
    const totalForm = form.Total_PriceForm ? form.Total_PriceForm.value : '';
    
    if (!priceForm || !totalForm) {
        alert('먼저 가격을 계산해주세요.');
        return;
    }
    
    // 주문 정보를 URL 파라미터로 구성
    const params = new URLSearchParams();
    params.set('direct_order', '1');
    params.set('product_type', 'poster');
    
    // 폼 데이터 추가
    const formData = new FormData(form);
    for (let [key, value] of formData.entries()) {
        params.set(key, value);
    }
    
    // 주문 페이지로 이동
    window.location.href = '/MlangOrder_PrintAuto/OnlineOrder_unified.php?' + params.toString();
}

// === 갤러리 시스템 함수들 === (기존과 동일하지만 콘솔 로그는 스마트 컴포넌트 스타일로)

function loadImageGallery() {
    const loadingElement = document.getElementById('galleryLoading');
    const errorElement = document.getElementById('galleryError');
    
    if (loadingElement) loadingElement.style.display = 'block';
    if (errorElement) errorElement.style.display = 'none';
    
    fetch('get_poster_images.php')
    .then(response => response.json())
    .then(response => {
        if (loadingElement) loadingElement.style.display = 'none';
        
        if (response.success && response.data.length > 0) {
            galleryImages = response.data;
            createThumbnails();
            console.log('🖼️ 포스터 갤러리 로드 완료:', response.count + '개 이미지');
        } else {
            showGalleryError('포스터 샘플 이미지가 없습니다.');
        }
    })
    .catch(error => {
        if (loadingElement) loadingElement.style.display = 'none';
        showGalleryError('이미지를 불러오는 중 오류가 발생했습니다: ' + error.message);
    });
}

function showGalleryError(message) {
    const errorElement = document.getElementById('galleryError');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function initGalleryZoom() {
    const zoomBox = document.getElementById('zoomBox');
    if (!zoomBox) return;
    
    zoomBox.addEventListener('mousemove', e => {
        const { width, height, left, top } = zoomBox.getBoundingClientRect();
        const xPct = (e.clientX - left) / width * 100;
        const yPct = (e.clientY - top) / height * 100;
        targetX = xPct;
        targetY = yPct;
        targetSize = currentImageType === 'small' ? 130 : 150;
    });
    
    zoomBox.addEventListener('mouseleave', () => {
        targetX = 50;
        targetY = 50;
        targetSize = 100;
    });
}

function animate() {
    const zoomBox = document.getElementById('zoomBox');
    if (!zoomBox) return;
    
    currentX += (targetX - currentX) * 0.15;
    currentY += (targetY - currentY) * 0.15;
    currentSize += (targetSize - currentSize) * 0.15;
    
    zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
    
    if (currentSize > 100.1) {
        zoomBox.style.backgroundSize = `${currentSize}%`;
    } else {
        zoomBox.style.backgroundSize = originalBackgroundSize;
    }
    
    requestAnimationFrame(animate);
}

function analyzeImageSize(imagePath, callback) {
    const img = new Image();
    img.onload = function() {
        const containerHeight = 420;
        const containerWidth = document.getElementById('zoomBox').getBoundingClientRect().width;
        
        currentImageDimensions.width = this.naturalWidth;
        currentImageDimensions.height = this.naturalHeight;
        
        let backgroundSize;
        
        if (this.naturalHeight <= containerHeight && this.naturalWidth <= containerWidth) {
            backgroundSize = `${this.naturalWidth}px ${this.naturalHeight}px`;
            currentImageType = 'small';
        } else {
            backgroundSize = 'contain';
            currentImageType = 'large';
        }
        
        callback(backgroundSize);
    };
    img.onerror = function() {
        currentImageType = 'large';
        callback('contain');
    };
    img.src = imagePath;
}

function updateMainImage(index) {
    if (galleryImages.length === 0) return;
    
    const zoomBox = document.getElementById('zoomBox');
    const image = galleryImages[index];
    
    analyzeImageSize(image.path, function(backgroundSize) {
        zoomBox.style.backgroundImage = `url('${image.path}')`;
        zoomBox.style.backgroundSize = backgroundSize;
        originalBackgroundSize = backgroundSize;
    });
    
    currentImageIndex = index;
    targetSize = 100;
    targetX = 50;
    targetY = 50;
    updateThumbnailActive(index);
}

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
        
        thumbnail.addEventListener('click', () => {
            const allThumbs = thumbnailGrid.querySelectorAll('img');
            allThumbs.forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
            updateMainImage(index);
        });
        
        thumbnailGrid.appendChild(thumbnail);
    });
    
    if (galleryImages.length > 0) {
        updateMainImage(0);
    }
}

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
// 스마트 컴포넌트 디버그 정보 (개발 중에만 표시)
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    echo "<div style='position: fixed; bottom: 60px; right: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; max-width: 300px; font-size: 0.8rem; z-index: 1001;'>";
    echo "<h5>🔧 스마트 컴포넌트 디버그</h5>";
    echo $smartComponent->debugComponent();
    echo ProductFieldMapper::debugProductMapping('poster');
    echo "</div>";
}
?>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>
</body>
</html>