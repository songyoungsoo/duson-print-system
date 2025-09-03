<?php
// 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 파일 업로드 컴포넌트
include "../../includes/FileUploadComponent.php";

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("명함 견적안내 - 컴팩트");

// 기본값 설정 (데이터베이스에서 가져오기)
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 명함 종류 가져오기 (일반명함(쿠폰) 우선)
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='namecard' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 명함 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='namecard' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (500매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_namecard 
                          WHERE style='" . $type_row['no'] . "' AND Section='" . $section_row['no'] . "' 
                          ORDER BY CASE WHEN quantity='500' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
                          LIMIT 1";
        $quantity_result = mysqli_query($db, $quantity_query);
        if ($quantity_result && ($quantity_row = mysqli_fetch_assoc($quantity_result))) {
            $default_values['MY_amount'] = $quantity_row['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 공통 헤더 포함 -->
    <?php include "../../includes/header.php"; ?>
    
    <!-- 명함 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    
    <!-- 통합 갤러리 JavaScript -->
    <script src="../../includes/js/UnifiedGallery.js"></script>
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
        <div class="page-title">
            <h1>💳 명함 견적안내</h1>
            <p>컴팩트 버전 - 한화면에서 간편하게</p>
        </div>

        <div class="main-content">
            <!-- 좌측: 포스터 기술 통합 갤러리 -->
            <div class="gallery-section">
                <div id="gallery-section">
                    <!-- UnifiedGallery 컴포넌트가 여기에 렌더링됩니다 -->
                </div>
            </div>

            <!-- 우측: 동적 계산기 -->
            <div class="calculator-section">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="namecardForm">
                    <!-- 옵션 선택 그리드 -->
                    <div class="options-grid">
                        <div class="option-group">
                            <label class="option-label" for="MY_type">명함 종류</label>
                            <select class="option-select" name="MY_type" id="MY_type" required>
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'namecard');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="Section">명함 재질</label>
                            <select class="option-select" name="Section" id="Section" required>
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="POtype">인쇄면</label>
                            <select class="option-select" name="POtype" id="POtype" required>
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="MY_amount">수량</label>
                            <select class="option-select" name="MY_amount" id="MY_amount" required>
                                <option value="">먼저 재질을 선택해주세요</option>
                            </select>
                        </div>

                        <div class="option-group full-width">
                            <label class="option-label" for="ordertype">편집디자인</label>
                            <select class="option-select" name="ordertype" id="ordertype" required>
                                <option value="">선택해주세요</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                            </select>
                        </div>
                    </div>

                    <!-- 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-label">견적 금액</div>
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton" style="display: none;">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            📎 파일 업로드 및 주문하기
                        </button>
                    </div>

                    <!-- 숨겨진 필드들 -->
                    <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
                    <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
                    <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
                    <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
                    <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
                    <input type="hidden" name="page" value="namecard">
                </form>
            </div>
        </div>
    </div>

    <!-- 파일 업로드 모달 -->
    <div id="uploadModal" class="upload-modal" style="display: none;">
        <div class="modal-overlay" onclick="closeUploadModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📎 파일첨부방법 선택</h3>
                <button type="button" class="modal-close" onclick="closeUploadModal()">✕</button>
            </div>
            
            <div class="modal-body">
                <div class="upload-container">
                    <div class="upload-left">
                        <label class="upload-label" for="modalFileInput">파일첨부</label>
                        <div class="upload-buttons">
                            <button type="button" class="btn-upload-method active" onclick="selectUploadMethod('upload')">
                                파일업로드
                            </button>
                            <button type="button" class="btn-upload-method" onclick="selectUploadMethod('manual')" disabled>
                                10분만에 작품완료 자기는 방법!
                            </button>
                        </div>
                        <div class="upload-area" id="modalUploadArea">
                            <div class="upload-dropzone" id="modalUploadDropzone">
                                <span class="upload-icon">📁</span>
                                <span class="upload-text">파일을 여기에 드래그하거나 클릭하세요</span>
                                <input type="file" id="modalFileInput" accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.psd" multiple hidden>
                            </div>
                            <div class="upload-info">
                                파일첨부 독수리파일(#,&,'&',*,%, 등) 사용은 불가능하며 파일명이 길면 예전가 불성
                                하니 되도록 짧고 간단하게 작성해 주세요!
                            </div>
                        </div>
                    </div>
                    
                    <div class="upload-right">
                        <label class="upload-label">작업메모</label>
                        <textarea id="modalWorkMemo" class="memo-textarea" placeholder="작업 관련 요청사항이나 특별한 지시사항을 입력해주세요.&#10;&#10;예시:&#10;- 색상을 더 진하게 해주세요&#10;- 로고 크기를 조금 더 크게&#10;- 배경색을 파란색으로 변경"></textarea>
                        
                        <div class="upload-notice">
                            <div class="notice-item">📋 택배 무료배송은 결제금액 총 3만원 명부시에 한함</div>
                            <div class="notice-item">📋 온전판(당일)주 전날 주문 제품과 목업 불가</div>
                        </div>
                    </div>
                </div>
                
                <div class="uploaded-files" id="modalUploadedFiles" style="display: none;">
                    <h5>📂 업로드된 파일</h5>
                    <div class="file-list" id="modalFileList"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cart" onclick="addToBasketFromModal()" style="max-width: none;">
                    🛒 장바구니에 저장
                </button>
            </div>
        </div>
    </div>

    <?php include "../../includes/login_modal.php"; ?>
    <?php include "../../includes/footer.php"; ?>

    <!-- 명함 갤러리 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    <style>
    /* =================================================================== */
    /* 1단계: Page-title 컴팩트화 (1/2 높이 축소) */
    /* =================================================================== */
    .page-title {
        padding: 12px 0 !important;          /* 1/2 축소 */
        margin-bottom: 15px !important;      /* 1/2 축소 */
        border-radius: 10px !important;      /* 2/3 축소 */
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
    }

    .page-title h1 {
        font-size: 1.6rem !important;        /* 27% 축소 */
        line-height: 1.2 !important;         /* 타이트 */
        margin: 0 !important;
        color: white !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
    }

    .page-title p {
        margin: 4px 0 0 0 !important;        /* 1/2 축소 */
        font-size: 0.85rem !important;       /* 15% 축소 */
        line-height: 1.3 !important;
        color: white !important;
        opacity: 0.9 !important;
    }

    /* =================================================================== */
    /* 2단계: Calculator-header 컴팩트화 (gallery-title과 완전히 동일한 디자인) */
    /* =================================================================== */
    .calculator-header {
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%) !important;
        color: white !important;
        padding: 15px 20px !important;       /* gallery-title과 동일 */
        margin: 0px -25px 20px -25px !important; /* 좌우 -25px로 섹션 너비에 맞춤 */
        border-radius: 15px 15px 0 0 !important;  /* gallery-title과 동일한 라운딩 */
        font-size: 1.1rem !important;        /* gallery-title과 동일 */
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 2px 10px rgba(233, 30, 99, 0.3) !important;
        line-height: 1.2 !important;
    }

    /* calculator-section에 갤러리와 동일한 배경 적용 */
    .calculator-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 15px !important;
        padding: 25px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        position: relative !important; /* 헤더 오버플로우를 위한 설정 */
    }

    .calculator-header h3 {
        font-size: 1.2rem !important;        /* 14% 축소 */
        line-height: 1.2 !important;
        margin: 0 !important;
        color: white !important;
        font-weight: 600 !important;
    }

    .calculator-subtitle {
        font-size: 0.85rem !important;
        margin: 0 !important;
        opacity: 0.9 !important;
    }

    /* =================================================================== */
    /* 3단계: Price-display 컴팩트화 (2/3 높이 축소) */
    /* =================================================================== */
    .price-display {
        padding: 8px 5px !important;         /* 상하 패딩 최적화 */
        border-radius: 8px !important;       /* 2/3 축소 */
        margin-bottom: 5px !important;
    }

    .price-display .price-label {
        font-size: 0.85rem !important;       /* 15% 축소 */
        margin-bottom: 4px !important;       /* 1/2 축소 */
        line-height: 1.2 !important;
    }

    .price-display .price-amount {
        font-size: 1.4rem !important;        /* 22% 축소 */
        margin-bottom: 6px !important;       /* 40% 축소 */
        line-height: 1.1 !important;
    }

    .price-display .price-details {
        font-size: 0.75rem !important;       /* 12% 축소 */
        line-height: 1.3 !important;
        margin: 0 !important;
    }

    .price-display.calculated {
        transform: scale(1.01) !important;   /* 애니메이션 절제 */
        box-shadow: 0 4px 12px rgba(233, 30, 99, 0.15) !important;
    }

    /* =================================================================== */
    /* 4단계: Form 요소 컴팩트화 (패딩 1/2 축소) */
    /* =================================================================== */
    .option-select {
        padding: 6px 15px !important;        /* 상하 패딩 1/2 */
    }

    /* =================================================================== */
    /* 5단계: 기타 요소들 컴팩트화 */
    /* =================================================================== */
    .calculator-section {
        padding: 0px 25px !important;        /* 더 타이트하게 */
        min-height: 400px !important;
    }

    .options-grid {
        gap: 12px !important;                /* 25% 축소 */
    }

    .option-group {
        margin-bottom: 8px !important;       /* 33% 축소 */
    }

    .upload-order-button {
        margin-top: 8px !important;          /* 20% 축소 */
    }

    /* =================================================================== */
    /* 6단계: 갤러리 섹션 스타일 (명함 브랜드 컸러 - 핀크-마젠타) */
    /* =================================================================== */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9);
    }
    
    /* 통합 갤러리 제목 색상 조정 (명함 브랜드 컸러) */
    .gallery-section .gallery-title {
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%) !important;
        color: white !important;
    }

    /* =================================================================== */
    /* 7단계: 반응형 최적화 */
    /* =================================================================== */
    @media (max-width: 768px) {
        /* 모바일에서는 축소 정도 완화 */
        .page-title { 
            padding: 15px 0 !important;       /* 데스크톱보다 약간 여유 */
        }
        
        .page-title h1 {
            font-size: 1.4rem !important;     /* 가독성 고려 */
        }
        
        .calculator-header { 
            padding: 15px 20px !important;    /* 터치 친화적 */
        }
        
        .price-display .price-amount {
            font-size: 1.5rem !important;     /* 모바일 가독성 */
        }
        
        .option-select {
            padding: 10px 15px !important;    /* 터치 영역 확보 */
        }

        .gallery-section {
            padding: 20px;
            margin: 0 -10px;
            border-radius: 10px;
        }
        
        .gallery-title {
            margin: -20px -20px 15px -20px;
            padding: 12px 15px;
            font-size: 1rem;
        }
    }
    </style>

    <script>
        // PHP 변수를 JavaScript로 전달
        var phpVars = {
            MultyUploadDir: "../../PHPClass/MultyUpload",
            log_url: "<?php echo safe_html($log_info['url']); ?>",
            log_y: "<?php echo safe_html($log_info['y']); ?>",
            log_md: "<?php echo safe_html($log_info['md']); ?>",
            log_ip: "<?php echo safe_html($log_info['ip']); ?>",
            log_time: "<?php echo safe_html($log_info['time']); ?>",
            page: "namecard"
        };

        // 전역 변수들
        let namecardGallery = null;
        let currentPriceData = null;
        let uploadedFiles = [];
        let selectedUploadMethod = 'upload';

        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            console.log('명함 갤러리 초기화 시작');
            
            // 포스터 갤러리 기술이 통합된 UnifiedGallery 초기화
            if (typeof UnifiedGallery !== 'undefined') {
                const gallery = new UnifiedGallery({
                    container: '#gallery-section',
                    category: 'namecard',
                    categoryLabel: '명함',
                    apiUrl: '/api/get_real_orders_portfolio.php'
                });
                
                console.log('명함 갤러리 초기화 완료');
            } else {
                console.error('UnifiedGallery 클래스를 찾을 수 없습니다.');
            }
            
            // 명함 계산기 초기화
            initializeCalculator();
            initializeFileUpload();
            
            // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
            const typeSelect = document.getElementById('MY_type');
            if (typeSelect.value) {
                loadPaperTypes(typeSelect.value);
            }
        });

        // === 갤러리 관련 함수들은 GalleryLightbox 컴포넌트로 이동됨 ===

        // === 계산기 관련 함수들 ===
        
        function initializeCalculator() {
            const typeSelect = document.getElementById('MY_type');
            const paperSelect = document.getElementById('Section');
            const sideSelect = document.getElementById('POtype');
            const quantitySelect = document.getElementById('MY_amount');
            const ordertypeSelect = document.getElementById('ordertype');

            // 드롭다운 변경 이벤트 리스너
            typeSelect.addEventListener('change', function() {
                const style = this.value;
                resetSelectWithText(paperSelect, '명함 재질을 선택해주세요');
                resetSelectWithText(quantitySelect, '수량을 선택해주세요');
                resetPrice();

                if (style) {
                    loadPaperTypes(style);
                }
            });

            paperSelect.addEventListener('change', loadQuantities);
            sideSelect.addEventListener('change', loadQuantities);
            
            // 모든 옵션 변경 시 자동 계산 (실시간)
            [typeSelect, paperSelect, sideSelect, quantitySelect, ordertypeSelect].forEach(select => {
                select.addEventListener('change', autoCalculatePrice);
            });
        }
        
        function resetSelect(selectElement, defaultText) {
            selectElement.innerHTML = `<option value="">${defaultText}</option>`;
        }
        
        function resetSelectWithText(selectElement, defaultText) {
            selectElement.innerHTML = `<option value="">${defaultText}</option>`;
        }
        
        function resetPrice() {
            document.getElementById('priceAmount').textContent = '견적 계산 필요';
            document.getElementById('priceDetails').textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
            document.getElementById('priceDisplay').classList.remove('calculated');
            document.getElementById('uploadOrderButton').style.display = 'none';
            currentPriceData = null;
        }
        
        function loadPaperTypes(style) {
            if (!style) return;

            fetch(`get_paper_types.php?style=${style}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const paperSelect = document.getElementById('Section');
                        updateSelectWithOptions(paperSelect, data.data, '명함 재질을 선택해주세요');
                        
                        <?php if (!empty($default_values['Section'])): ?>
                        paperSelect.value = '<?php echo $default_values['Section']; ?>';
                        if (paperSelect.value) {
                            loadQuantities();
                        }
                        <?php endif; ?>
                    } else {
                        alert('재질 로드 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('재질 로드 오류:', error);
                    alert('재질 로드 중 오류가 발생했습니다.');
                });
        }
        
        function loadQuantities() {
            const typeSelect = document.getElementById('MY_type');
            const paperSelect = document.getElementById('Section');
            const sideSelect = document.getElementById('POtype');
            const quantitySelect = document.getElementById('MY_amount');

            const style = typeSelect.value;
            const section = paperSelect.value;
            const potype = sideSelect.value;

            resetSelectWithText(quantitySelect, '수량을 선택해주세요');
            resetPrice();

            if (!style || !section || !potype) return;

            fetch(`get_quantities.php?style=${style}&section=${section}&potype=${potype}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateSelectWithOptions(quantitySelect, data.data, '수량을 선택해주세요');
                        
                        <?php if (!empty($default_values['MY_amount'])): ?>
                        quantitySelect.value = '<?php echo $default_values['MY_amount']; ?>';
                        if (quantitySelect.value) {
                            autoCalculatePrice();
                        }
                        <?php endif; ?>
                    } else {
                        alert('수량 로드 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('수량 로드 오료:', error);
                    alert('수량 로드 중 오류가 발생했습니다.');
                });
        }
        
        function updateSelectWithOptions(selectElement, options, defaultOptionText) {
            selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
            if (options) {
                options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value || option.no;
                    optionElement.textContent = option.text || option.title;
                    selectElement.appendChild(optionElement);
                });
            }
        }
        
        // 자동 계산 (실시간)
        function autoCalculatePrice() {
            const form = document.getElementById('namecardForm');
            const formData = new FormData(form);
            
            // 모든 필수 옵션이 선택되었는지 확인
            if (!formData.get('MY_type') || !formData.get('Section') || 
                !formData.get('POtype') || !formData.get('MY_amount') || 
                !formData.get('ordertype')) {
                return; // 아직 모든 옵션이 선택되지 않음
            }
            
            // 실시간 계산 실행
            calculatePrice(true);
        }
        
        // 가격 계산 함수
        function calculatePrice(isAuto = true) {
            const form = document.getElementById('namecardForm');
            const formData = new FormData(form);
            
            if (!formData.get('MY_type') || !formData.get('Section') || 
                !formData.get('POtype') || !formData.get('MY_amount') || 
                !formData.get('ordertype')) {
                return;
            }
            
            const params = new URLSearchParams(new FormData(form));
            
            fetch('calculate_price_ajax.php?' + params.toString())
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    const priceData = response.data;
                    currentPriceData = priceData;
                    
                    // 가격 표시 업데이트
                    const priceDisplay = document.getElementById('priceDisplay');
                    const priceAmount = document.getElementById('priceAmount');
                    const priceDetails = document.getElementById('priceDetails');
                    const actionButtons = document.getElementById('actionButtons');
                    
                    priceAmount.textContent = format_number(Math.round(priceData.total_with_vat)) + '원';
                    priceDetails.innerHTML = `
                        인쇄비: ${format_number(priceData.base_price)}원<br>
                        디자인비: ${format_number(priceData.design_price)}원<br>
                        합계(VAT포함): ${format_number(Math.round(priceData.total_with_vat))}원
                    `;
                    
                    priceDisplay.classList.add('calculated');
                    document.getElementById('uploadOrderButton').style.display = 'block';
                    
                } else {
                    resetPrice();
                }
            })
            .catch(error => {
                console.error('가격 계산 오류:', error);
            });
        }
        
        // === 파일 업로드 관련 함수들 ===
        
        function openUploadModal() {
            if (!currentPriceData) {
                alert('먼저 가격을 계산해주세요.');
                return;
            }
            
            const modal = document.getElementById('uploadModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // 모달 내 파일 업로드 초기화
            initializeModalFileUpload();
        }
        
        function closeUploadModal() {
            const modal = document.getElementById('uploadModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // 업로드된 파일 초기화
            uploadedFiles = [];
            updateModalFileList();
            document.getElementById('modalWorkMemo').value = '';
        }
        
        function initializeFileUpload() {
            // 기본 초기화는 유지하되 모달용으로 변경
            initializeModalFileUpload();
        }
        
        function initializeModalFileUpload() {
            const dropzone = document.getElementById('modalUploadDropzone');
            const fileInput = document.getElementById('modalFileInput');
            
            // 기존 이벤트 리스너 제거
            if (dropzone.onclick) dropzone.onclick = null;
            if (fileInput.onchange) fileInput.onchange = null;
            
            // 드롭존 클릭 시 파일 선택
            dropzone.addEventListener('click', () => {
                fileInput.click();
            });
            
            // 파일 선택 시
            fileInput.addEventListener('change', handleFileSelect);
            
            // 드래그 앤 드롭
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
            
            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('dragover');
            });
            
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                const files = Array.from(e.dataTransfer.files);
                handleFiles(files);
            });
        }
        
        function selectUploadMethod(method) {
            selectedUploadMethod = method;
            const buttons = document.querySelectorAll('.btn-upload-method');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }
        
        function handleFileSelect(e) {
            const files = Array.from(e.target.files);
            handleFiles(files);
        }
        
        function handleFiles(files) {
            const validTypes = ['.jpg', '.jpeg', '.png', '.pdf', '.ai', '.eps', '.psd'];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            files.forEach(file => {
                const extension = '.' + file.name.split('.').pop().toLowerCase();
                
                if (!validTypes.includes(extension)) {
                    alert(`지원하지 않는 파일 형식입니다: ${file.name}\n지원 형식: JPG, PNG, PDF, AI, EPS, PSD`);
                    return;
                }
                
                if (file.size > maxSize) {
                    alert(`파일 크기가 너무 큽니다: ${file.name}\n최대 10MB까지 업로드 가능합니다.`);
                    return;
                }
                
                // 업로드된 파일 목록에 추가
                const fileObj = {
                    id: Date.now() + Math.random(),
                    file: file,
                    name: file.name,
                    size: formatFileSize(file.size),
                    type: extension
                };
                
                uploadedFiles.push(fileObj);
                updateFileList();
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function updateFileList() {
            updateModalFileList();
        }
        
        function updateModalFileList() {
            const uploadedFilesDiv = document.getElementById('modalUploadedFiles');
            const fileList = document.getElementById('modalFileList');
            
            if (uploadedFiles.length === 0) {
                uploadedFilesDiv.style.display = 'none';
                return;
            }
            
            uploadedFilesDiv.style.display = 'block';
            fileList.innerHTML = '';
            
            uploadedFiles.forEach(fileObj => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div class="file-info">
                        <span class="file-icon">${getFileIcon(fileObj.type)}</span>
                        <div class="file-details">
                            <div class="file-name">${fileObj.name}</div>
                            <div class="file-size">${fileObj.size}</div>
                        </div>
                    </div>
                    <button class="file-remove" onclick="removeFile('${fileObj.id}')">삭제</button>
                `;
                fileList.appendChild(fileItem);
            });
        }
        
        function getFileIcon(extension) {
            switch(extension.toLowerCase()) {
                case '.jpg':
                case '.jpeg':
                case '.png': return '🖼️';
                case '.pdf': return '📄';
                case '.ai': return '🎨';
                case '.eps': return '🎨';
                case '.psd': return '🎨';
                default: return '📁';
            }
        }
        
        function removeFile(fileId) {
            uploadedFiles = uploadedFiles.filter(f => f.id != fileId);
            updateModalFileList();
        }
        
        // 모달에서 장바구니에 추가
        function addToBasketFromModal() {
            if (!currentPriceData) {
                alert('먼저 가격을 계산해주세요.');
                return;
            }
            
            // 로딩 상태 표시
            const cartButton = document.querySelector('.btn-cart');
            const originalText = cartButton.innerHTML;
            cartButton.innerHTML = '🔄 저장 중...';
            cartButton.disabled = true;
            cartButton.style.opacity = '0.7';
            
            const form = document.getElementById('namecardForm');
            const workMemo = document.getElementById('modalWorkMemo').value;
            
            const formData = new FormData(form);
            
            // 기본 주문 정보
            formData.set('action', 'add_to_basket');
            formData.set('price', Math.round(currentPriceData.total_price));
            formData.set('vat_price', Math.round(currentPriceData.total_with_vat));
            formData.set('product_type', 'namecard');
            
            // 추가 정보
            formData.set('work_memo', workMemo);
            formData.set('upload_method', selectedUploadMethod);
            
            // 업로드된 파일들 추가
            uploadedFiles.forEach((fileObj, index) => {
                formData.append(`uploaded_files[${index}]`, fileObj.file);
            });
            
            // 파일 정보 JSON
            const fileInfoArray = uploadedFiles.map(fileObj => ({
                name: fileObj.name,
                size: fileObj.size,
                type: fileObj.type
            }));
            formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
            
            fetch('add_to_basket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text(); // 먼저 text로 받아서 확인
            })
            .then(text => {
                console.log('Raw response:', text);
                
                try {
                    const response = JSON.parse(text);
                    console.log('Parsed response:', response);
                    
                    if (response.success) {
                        // 모달 닫기
                        closeUploadModal();
                        
                        // 성공 메시지 표시
                        alert('장바구니에 저장되었습니다! 🛒');
                        
                        // 장바구니 페이지로 이동
                        window.location.href = '/mlangprintauto/shop/cart.php';
                        
                    } else {
                        // 버튼 복원
                        cartButton.innerHTML = originalText;
                        cartButton.disabled = false;
                        cartButton.style.opacity = '1';
                        
                        alert('장바구니 저장 중 오류가 발생했습니다: ' + response.message);
                    }
                } catch (parseError) {
                    // 버튼 복원
                    cartButton.innerHTML = originalText;
                    cartButton.disabled = false;
                    cartButton.style.opacity = '1';
                    
                    console.error('JSON Parse Error:', parseError);
                    alert('서버 응답 처리 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                // 버튼 복원
                cartButton.innerHTML = originalText;
                cartButton.disabled = false;
                cartButton.style.opacity = '1';
                
                console.error('Fetch Error:', error);
                alert('장바구니 저장 중 네트워크 오류가 발생했습니다: ' + error.message);
            });
        }
        
        // 호환성을 위한 기본 장바구니 함수 (사용하지 않음)
        function addToBasket() {
            openUploadModal();
        }
        
        
        // 바로 주문하기 (호환성용 - 사용하지 않음)
        function directOrder() {
            openUploadModal();
        }
        
        
        // 유틸리티 함수
        function format_number(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        
        // === 구식 갤러리 시스템 제거됨 ===
        // UnifiedGallery 컴포넌트로 모든 기능이 통합되었습니다.
        // - 4개 썸네일 표시
        // - "더 많은 샘플 보기" 팝업
        // - 라이트박스 확대 보기
        // - 포스터 갤러리의 고급 줌 기술
        // - 페이지네이션 지원
        // 모든 기능이 하나의 컴포넌트에서 제공됩니다.
        
        // 초기화 다음에 계산기 설정
        
    </script>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>