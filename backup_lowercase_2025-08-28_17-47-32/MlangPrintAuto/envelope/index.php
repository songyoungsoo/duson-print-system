<?php
/**
 * 봉투 견적안내 컴팩트 시스템 - NameCard 시스템 구조 적용
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

// 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
if (function_exists("init_gallery_system")) { init_gallery_system("envelope"); }

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("봉투 견적안내 컴팩트 - 프리미엄");

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 봉투 종류 가져오기
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='Envelope' AND BigNo='0' 
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 봉투 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='Envelope' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (1000매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_envelope 
                          WHERE style='" . $type_row['no'] . "' AND Section='" . $section_row['no'] . "' 
                          ORDER BY CASE WHEN quantity='1000' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
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
    
    <!-- 봉투 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../assets/css/gallery.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    
    <!-- jQuery 라이브러리 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- 통합 갤러리 JavaScript 라이브러리 -->
    <script src="../namecard/js/unified-gallery.js"></script>
    <script src="../../js/unified-gallery-popup.js"></script>
    
    <!-- 봉투 전용 JavaScript -->
    <script src="../../js/envelope.js" defer></script>
    
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    
    <?php
    // 갤러리 에셋 자동 포함
    if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
        if (function_exists("include_gallery_assets")) { include_gallery_assets(); }
    }
    ?>
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
        <div class="page-title">
            <h1>✉️ 봉투 견적안내</h1>
            <p>컴팩트 프리미엄 - NameCard 시스템 구조 적용</p>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="main-content">
            <!-- 좌측: 통합 갤러리 시스템 -->
            <section class="envelope-gallery" aria-label="봉투 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 사용 (3줄로 완전 간소화)
                if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
                if (function_exists("include_product_gallery")) { include_product_gallery('envelope', ['mainSize' => [500, 400]]); }
                ?>
            </section>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="calculator-section">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="envelopeForm">
                    <!-- 옵션 선택 그리드 - 개선된 4열 레이아웃 -->
                    <div class="options-grid form-grid-compact">
                        <div class="option-group form-field">
                            <label class="option-label" for="MY_type">봉투종류</label>
                            <select class="option-select" name="MY_type" id="MY_type" required>
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "MlangPrintAuto_transactionCate", "Envelope");
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="option-group form-field">
                            <label class="option-label" for="Section">봉투재질</label>
                            <select class="option-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                        </div>

                        <div class="option-group form-field">
                            <label class="option-label" for="POtype">인쇄면</label>
                            <select class="option-select" name="POtype" id="POtype" required>
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                            </select>
                        </div>

                        <div class="option-group form-field">
                            <label class="option-label" for="MY_amount">수량</label>
                            <select class="option-select" name="MY_amount" id="MY_amount" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
                                <option value="">먼저 재질을 선택해주세요</option>
                            </select>
                        </div>

                        <div class="option-group form-field full-width">
                            <label class="option-label" for="ordertype">편집디자인</label>
                            <select class="option-select" name="ordertype" id="ordertype" required>
                                <option value="">선택해주세요</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                            </select>
                        </div>
                    </div>

                    <!-- 실시간 가격 표시 - 개선된 애니메이션 -->
                    <div class="price-display price-compact" id="priceDisplay">
                        <div class="price-label">견적 금액</div>
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 - 프리미엄 스타일 -->
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
                    <input type="hidden" name="page" value="Envelope">
                </form>
            </div>
        </div>
    </div>

    <!-- 파일 업로드 모달 (드래그 앤 드롭 및 고급 애니메이션) -->
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
                <button type="button" class="modal-btn btn-cart" onclick="addToBasketFromModal()">
                    🛒 장바구니에 저장
                </button>
            </div>
        </div>
    </div>

    <?php include "../../includes/login_modal.php"; ?>
    <?php include "explane05.php"; ?>
    <?php include "../../includes/footer.php"; ?>

    <!-- 봉투 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    <style>
    /* btn-primary 스타일은 공통 CSS (../../css/btn-primary.css)에서 로드됨 */
    
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
    /* 2단계: Calculator-header 컴팩트화 (gallery-title과 동일한 디자인) */
    /* =================================================================== */
    .calculator-header, .price-section h3, .price-calculator h3 {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%) !important;
        color: white !important;
        padding: 15px 20px !important;       /* gallery-title과 동일 */
        margin: 0px -25px 20px -25px !important;      /* 좌우 -25px로 섹션 너비에 맞춤 */
        border-radius: 15px 15px 0 0 !important;      /* gallery-title과 동일 */
        font-size: 1.1rem !important;        /* gallery-title과 동일 */
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 2px 10px rgba(255, 152, 0, 0.3) !important; /* gallery-title과 동일 */
        line-height: 1.2 !important;
    }

    /* calculator-section 갤러리와 동일한 배경 및 패딩 */
    .calculator-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 15px !important;
        padding: 25px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        position: relative !important; /* 헤더 오버플로우를 위한 설정 */
        margin-top: 0 !important; /* 상단 여백 제거 */
        align-self: start !important; /* 상단 정렬 */
    }

    .calculator-header h3 {
        font-size: 1.1rem !important;        /* gallery-title과 동일 */
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
    /* 3단계: 통일된 가격 표시 - 녹색 큰 글씨 (인쇄비+편집비=공급가) */
    /* =================================================================== */
    .price-display {
        background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border: 2px solid #28a745 !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        text-align: center !important;
        margin: 20px 0 !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.1) !important;
    }

    .price-display.calculated {
        background: linear-gradient(145deg, #d4edda 0%, #c3e6cb 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.2) !important;
        border-color: #20c997 !important;
    }

    .price-display .price-label {
        font-size: 0.9rem !important;
        color: #495057 !important;
        margin-bottom: 8px !important;
        font-weight: 500 !important;
    }

    .price-display .price-amount {
        font-size: 2.2rem !important;
        font-weight: 700 !important;
        color: #28a745 !important;
        margin: 10px 0 !important;
        line-height: 1.2 !important;
        text-shadow: 0 2px 4px rgba(40, 167, 69, 0.3) !important;
        letter-spacing: -0.5px !important;
    }

    .price-display .price-details {
        font-size: 0.8rem !important;
        color: #6c757d !important;
        line-height: 1.4 !important;
        margin-top: 8px !important;
    }

    .price-display:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.15) !important;
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
    .main-content {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 20px !important;
        align-items: start !important; /* 그리드 아이템들을 상단 정렬 */
    }
    
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
    /* 6단계: 갤러리 섹션 스타일 (봉투 브랜드 컬러 - 오렌지) */
    /* =================================================================== */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9);
        margin-top: 0 !important; /* 상단 여백 제거 */
        align-self: start !important; /* 상단 정렬 */
    }
    
    .gallery-title {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        padding: 15px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 10px rgba(255, 152, 0, 0.3);
    }

    /* 라이트박스 뷰어 스타일 */
    .lightbox-viewer {
        width: 100%;
        height: 300px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        cursor: zoom-in;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
        position: relative;
        overflow: hidden;
    }
    
    .lightbox-viewer:hover {
        border-color: #ff9800;
        box-shadow: 0 8px 30px rgba(255, 152, 0, 0.15);
        transform: translateY(-2px);
    }
    
    /* 썸네일 스트립 스타일 */
    .thumbnail-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 10px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .thumbnail-strip img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        opacity: 0.7;
    }
    
    .thumbnail-strip img:hover {
        opacity: 1;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        border-color: #ff9800;
    }
    
    .thumbnail-strip img.active {
        opacity: 1;
        border-color: #ff9800;
        box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
    }
    
    /* 갤러리 로딩 상태 */
    #envelopeGallery .loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        background: white;
        border-radius: 12px;
        animation: pulse 2s infinite;
    }
    
    /* 갤러리 에러 상태 */
    #envelopeGallery .error {
        text-align: center;
        padding: 40px 20px;
        color: #dc3545;
        background: #fff5f5;
        border: 1px solid #ffdddd;
        border-radius: 12px;
        font-size: 0.95rem;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
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
        // PHP 변수를 JavaScript로 전달 (PROJECT_SUCCESS_REPORT.md 스펙)
        window.phpVars = {
            MultyUploadDir: "../../PHPClass/MultyUpload",
            log_url: "<?php echo safe_html($log_info['url']); ?>",
            log_y: "<?php echo safe_html($log_info['y']); ?>",
            log_md: "<?php echo safe_html($log_info['md']); ?>",
            log_ip: "<?php echo safe_html($log_info['ip']); ?>",
            log_time: "<?php echo safe_html($log_info['time']); ?>",
            page: "Envelope",
            defaultValues: {
                MY_type: "<?php echo safe_html($default_values['MY_type']); ?>",
                Section: "<?php echo safe_html($default_values['Section']); ?>",
                POtype: "<?php echo safe_html($default_values['POtype']); ?>",
                MY_amount: "<?php echo safe_html($default_values['MY_amount']); ?>",
                ordertype: "<?php echo safe_html($default_values['ordertype']); ?>"
            }
        };

        // envelope.js에서 전역 변수와 초기화 함수들을 처리
        // 고급 갤러리 시스템 자동 로드
        
        // 통일된 갤러리 팝업 초기화
        let unifiedEnvelopeGallery;
        
        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            // 통일된 갤러리 팝업 초기화
            unifiedEnvelopeGallery = new UnifiedGalleryPopup({
                category: 'envelope',
                apiUrl: '/api/get_real_orders_portfolio.php',
                title: '봉투 전체 갤러리',
                icon: '✉️',
                perPage: 18
            });
            
            // 메인 갤러리 API 방식으로 로딩 (전단지와 동일한 방법)
            loadEnvelopeImagesAPI();
        });
        
        // 🎯 성공했던 API 방식으로 봉투 갤러리 로드 (전단지와 동일)
        async function loadEnvelopeImagesAPI() {
            const galleryContainer = document.getElementById('envelopeGallery');
            if (!galleryContainer) return;
            
            console.log('✉️ 봉투 갤러리 API 로딩 중...');
            galleryContainer.innerHTML = '<div class="loading">✉️ 갤러리 로딩 중...</div>';
            
            try {
                const response = await fetch('/api/get_real_orders_portfolio.php?category=envelope&per_page=4');
                const data = await response.json();
                
                console.log('✉️ 봉투 API 응답:', data);
                
                if (data.success && data.data && data.data.length > 0) {
                    console.log(`✅ 봉투 이미지 ${data.data.length}개 로드 성공`);
                    renderEnvelopeGalleryAPI(data.data, galleryContainer);
                } else {
                    console.log('⚠️ 봉투 이미지 데이터 없음');
                    galleryContainer.innerHTML = '<div class="error">표시할 이미지가 없습니다.</div>';
                }
            } catch (error) {
                console.error('❌ 봉투 갤러리 로딩 오류:', error);
                galleryContainer.innerHTML = '<div class="error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
            }
        }
        
        // API 갤러리 렌더링 (전단지 방식과 동일)
        function renderEnvelopeGalleryAPI(images, container) {
            console.log('🎨 봉투 갤러리 렌더링:', images.length + '개 이미지');
            
            // lightboxViewer div 생성 (봉투용)
            const viewerHtml = `
                <div class="lightbox-viewer" id="envelopeLightboxViewer">
                    <img id="envelopeMainImage" src="${images[0].path}" alt="${images[0].title}" 
                         style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;"
                         onclick="openFullScreenImage('${images[0].path}', '${images[0].title}')">
                </div>
                <div class="thumbnail-strip">
                    ${images.map((img, index) => 
                        `<img src="${img.path}" alt="${img.title}" class="${index === 0 ? 'active' : ''}"
                             onclick="changeEnvelopeMainImage('${img.path}', '${img.title}', this)">` 
                    ).join('')}
                </div>
            `;
            
            container.innerHTML = viewerHtml;
            
            // 봉투 마우스 호버 효과 적용 (전단지와 동일)
            initializeEnvelopeZoomEffect();
        }
        
        // 봉투 메인 이미지 변경 함수
        function changeEnvelopeMainImage(imagePath, title, thumbnail) {
            const mainImage = document.getElementById('envelopeMainImage');
            if (mainImage) {
                mainImage.src = imagePath;
                mainImage.alt = title;
                mainImage.onclick = () => openFullScreenImage(imagePath, title);
            }
            
            // 썸네일 활성 상태 업데이트
            const thumbnails = document.querySelectorAll('.thumbnail-strip img');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
            
            // 줌 효과 재초기화
            initializeEnvelopeZoomEffect();
        }
        
        // 봉투 이미지 줌 효과 초기화 (전단지 방식과 동일)
        function initializeEnvelopeZoomEffect() {
            const viewer = document.getElementById('envelopeLightboxViewer');
            const mainImage = document.getElementById('envelopeMainImage');
            
            if (!viewer || !mainImage) return;
            
            // 기존 이벤트 리스너 제거 후 재등록
            const newViewer = viewer.cloneNode(true);
            viewer.parentNode.replaceChild(newViewer, viewer);
            
            const newMainImage = document.getElementById('envelopeMainImage');
            if (!newMainImage) return;
            
            let isZoomed = false;
            
            newViewer.addEventListener('mousemove', function(e) {
                if (isZoomed) return;
                
                const rect = newViewer.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                
                newMainImage.style.transform = `scale(1.5)`;
                newMainImage.style.transformOrigin = `${x}% ${y}%`;
                newMainImage.style.transition = 'transform 0.3s ease';
            });
            
            newViewer.addEventListener('mouseleave', function() {
                if (isZoomed) return;
                newMainImage.style.transform = 'scale(1)';
                newMainImage.style.transformOrigin = 'center center';
            });
            
            newViewer.addEventListener('click', function(e) {
                if (e.target === newMainImage) {
                    const imagePath = newMainImage.src;
                    const title = newMainImage.alt;
                    openFullScreenImage(imagePath, title);
                }
            });
        }
        
        // 통일된 갤러리 팝업 열기 (전단지와 동일한 시스템)
        function openProofPopup(category) {
            const popup = window.open('/popup/proof_gallery.php?cate=' + encodeURIComponent(category), 
                'proof_popup', 
                'width=1200,height=800,scrollbars=yes,resizable=yes,top=50,left=100');
            
            if (popup) {
                popup.focus();
            } else {
                alert('팝업이 차단되었습니다. 팝업 차단을 해제해주세요.');
            }
        }
        
        // 전체화면 이미지 열기
        function openFullScreenImage(imagePath, title) {
            if (window.lightboxViewer && window.lightboxViewer.showLightbox) {
                window.lightboxViewer.showLightbox(imagePath, title);
            } else {
                window.open(imagePath, '_blank');
            }
        }
    </script>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>