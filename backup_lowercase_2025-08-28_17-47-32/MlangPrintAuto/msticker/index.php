<?php
// 보안 상수 정의 후 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 공통 인증 시스템
include "../../includes/auth.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 생성
$log_info = generateLogInfo();

// 페이지 제목 설정
$page_title = generate_page_title("자석스티커 견적안내");

// 기본값 설정 (자석스티커용)
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 자석스티커 종류 가져오기 (종이자석 우선)
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='msticker' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%종이%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 자석스티커 종류의 첫 번째 규격 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='msticker' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_row = mysqli_fetch_assoc($section_result)) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (100매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_msticker 
                          WHERE style='" . $type_row['no'] . "' AND Section='" . $section_row['no'] . "' 
                          ORDER BY CASE WHEN quantity='100' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
                          LIMIT 1";
        $quantity_result = mysqli_query($db, $quantity_query);
        if ($quantity_row = mysqli_fetch_assoc($quantity_result)) {
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
    
    <!-- 자석스티커 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <link rel="stylesheet" href="../../css/gallery-common.css">
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    
    <!-- 통일된 갤러리 팝업 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery-popup.css">
    
    <!-- 고급 JavaScript 라이브러리 -->
    <script src="../../includes/js/GalleryLightbox.js"></script>
    <script src="../../js/msticker.js" defer></script>
    
    <!-- 통일된 갤러리 팝업 JavaScript -->
    <script src="../../js/unified-gallery-popup.js"></script>
    
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    
    <!-- 업로드 컴포넌트 JavaScript 라이브러리 포함 -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
        <div class="page-title">
            <h1>🧲 자석스티커 견적안내</h1>
            <p>강력한 자석으로 어디든 붙이는 자석스티커 - 샘플 갤러리 임시 적용</p>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="main-content">
            <!-- 좌측: 통합 갤러리 시스템 -->
            <section class="msticker-gallery" aria-label="자석스티커 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 사용 (3줄로 완전 간소화)
                if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
                if (function_exists("include_product_gallery")) { include_product_gallery('msticker', ['mainSize' => [500, 400]]); }
                ?>
            </section>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="calculator-section">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="mstickerForm">
                    <div class="options-grid form-grid-compact">
                        <div class="option-group form-field">
                            <label class="option-label" for="MY_type">종류</label>
                            <select class="option-select" name="MY_type" id="MY_type" required>
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "MlangPrintAuto_transactionCate', 'msticker');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="option-group form-field">
                            <label class="option-label" for="Section">규격</label>
                            <select class="option-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>">
                                <option value="">먼저 자석스티커 종류를 선택해주세요</option>
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
                                <option value="">먼저 자석스티커 규격을 선택해주세요</option>
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
                    <input type="hidden" name="page" value="msticker">
                </form>
            </div> <!-- calculator-section 끝 -->
        </div> <!-- main-content 끝 -->
    </div> <!-- compact-container 끝 -->

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

    <!-- 갤러리 더보기 모달 -->
    <div id="mstickerGalleryModal" class="gallery-modal" style="display: none;">
        <div class="gallery-modal-overlay" onclick="closeMStickerGalleryModal()"></div>
        <div class="gallery-modal-content">
            <div class="gallery-modal-header">
                <h3 class="gallery-modal-title">🔰 자석스티커 전체 갤러리</h3>
                <button type="button" class="gallery-modal-close" onclick="closeMStickerGalleryModal()">✕</button>
            </div>
            
            <div class="gallery-modal-body">
                <div class="gallery-grid" id="mstickerGalleryModalGrid">
                    <div class="gallery-loading">갤러리를 불러오는 중...</div>
                </div>
                
                <!-- 페이지네이션 -->
                <div class="gallery-pagination" id="mstickerPagination" style="display: none;">
                    <div class="pagination-info">
                        <span id="mstickerPageInfo">페이지 1 / 1 (총 0개)</span>
                    </div>
                    <div class="pagination-controls">
                        <button id="mstickerPrevBtn" class="pagination-btn" onclick="loadMStickerPage('prev')" disabled>
                            ← 이전
                        </button>
                        <div class="pagination-numbers" id="mstickerPageNumbers">
                        </div>
                        <button id="mstickerNextBtn" class="pagination-btn" onclick="loadMStickerPage('next')" disabled>
                            다음 →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
// 공통 푸터 포함
include "../../includes/footer.php";
?>

    <!-- 자석스티커 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
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
    /* 2단계: Calculator-header 컴팩트화 (2/3 높이 축소) */
    /* =================================================================== */
    .calculator-header {
        padding: 12px 25px !important;       /* 2/3 축소 */
        margin: 0 !important;                /* 마진 제거 */
        background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%) !important;
        color: white !important;
        border-radius: 12px !important;
        text-align: center !important;
        box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3) !important;
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
    /* 6단계: 갤러리 섹션 스타일 (자석스티커 브랜드 컬러 - 시안) */
    /* =================================================================== */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }
    
    .gallery-title {
        background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
        color: white;
        padding: 15px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 188, 212, 0.3);
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
        border-color: #00bcd4;
        box-shadow: 0 8px 30px rgba(0, 188, 212, 0.15);
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
        border-color: #00bcd4;
    }
    
    .thumbnail-strip img.active {
        opacity: 1;
        border-color: #00bcd4;
        box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3);
    }
    
    /* 갤러리 로딩 상태 */
    #mstickerGallery .loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        background: white;
        border-radius: 12px;
        animation: pulse 2s infinite;
    }
    
    /* 갤러리 에러 상태 */
    #mstickerGallery .error {
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
    /* 더보기 버튼 스타일 */
    /* =================================================================== */
    .gallery-more-button {
        text-align: center;
        margin-top: 15px;
    }
    
    .btn-more-gallery {
        background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 188, 212, 0.2);
    }
    
    .btn-more-gallery:hover {
        background: linear-gradient(135deg, #0097a7 0%, #00695c 100%);
        box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3);
        transform: translateY(-2px);
    }

    /* =================================================================== */
    /* 갤러리 모달 스타일 */
    /* =================================================================== */
    .gallery-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .gallery-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(3px);
    }
    
    .gallery-modal-content {
        position: relative;
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 1000px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideUp 0.3s ease-out;
    }
    
    .gallery-modal-header {
        background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .gallery-modal-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .gallery-modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }
    
    .gallery-modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .gallery-modal-body {
        padding: 20px;
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .gallery-grid img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .gallery-grid img:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #00bcd4;
    }
    
    @keyframes modalSlideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* =================================================================== */
    /* 페이지네이션 스타일 */
    /* =================================================================== */
    .gallery-pagination {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }
    
    .pagination-info {
        text-align: center;
        margin-bottom: 15px;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .pagination-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .pagination-btn {
        background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        min-width: 80px;
    }
    
    .pagination-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, #0097a7 0%, #00695c 100%);
        transform: translateY(-1px);
    }
    
    .pagination-btn:disabled {
        background: #dee2e6;
        color: #6c757d;
        cursor: not-allowed;
        transform: none;
    }
    
    .pagination-numbers {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    
    .pagination-number {
        background: white;
        color: #00bcd4;
        border: 1px solid #00bcd4;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        min-width: 35px;
        text-align: center;
    }
    
    .pagination-number:hover {
        background: #00bcd4;
        color: white;
    }
    
    .pagination-number.active {
        background: #00bcd4;
        color: white;
        font-weight: bold;
    }
    
    .pagination-ellipsis {
        color: #6c757d;
        padding: 6px 4px;
        font-size: 0.85rem;
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
        // PHP 변수를 JavaScript로 전달 (자석스티커용)
        window.phpVars = {
            MultyUploadDir: "../../PHPClass/MultyUpload",
            log_url: "<?php echo safe_html($log_info['url']); ?>",
            log_y: "<?php echo safe_html($log_info['y']); ?>",
            log_md: "<?php echo safe_html($log_info['md']); ?>",
            log_ip: "<?php echo safe_html($log_info['ip']); ?>",
            log_time: "<?php echo safe_html($log_info['time']); ?>",
            page: "msticker",
            defaultValues: {
                MY_type: "<?php echo safe_html($default_values['MY_type']); ?>",
                Section: "<?php echo safe_html($default_values['Section']); ?>",
                POtype: "<?php echo safe_html($default_values['POtype']); ?>",
                MY_amount: "<?php echo safe_html($default_values['MY_amount']); ?>",
                ordertype: "<?php echo safe_html($default_values['ordertype']); ?>"
            }
        };

        // 자석스티커 전용 msticker.js가 모든 기능을 처리합니다
        
        // 통일된 갤러리 팝업 초기화
        let unifiedMstickerGallery;
        
        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            // 통일된 갤러리 팝업 초기화
            unifiedMstickerGallery = new UnifiedGalleryPopup({
                category: 'msticker',
                apiUrl: '/api/get_real_orders_portfolio.php',
                title: '자석스티커 전체 갤러리',
                icon: '🧲',
                perPage: 18
            });
            
            // 메인 갤러리 API 방식으로 로딩 (전단지와 동일한 방법)
            loadMstickerImagesAPI();
        });
        
        // 🎯 성공했던 API 방식으로 자석스티커 갤러리 로드 (전단지와 동일)
        async function loadMstickerImagesAPI() {
            const galleryContainer = document.getElementById('mstickerGallery');
            if (!galleryContainer) return;
            
            console.log('🧲 자석스티커 갤러리 API 로딩 중...');
            galleryContainer.innerHTML = '<div class="loading">🧲 갤러리 로딩 중...</div>';
            
            try {
                const response = await fetch('/api/get_real_orders_portfolio.php?category=msticker&per_page=4');
                const data = await response.json();
                
                console.log('🧲 자석스티커 API 응답:', data);
                
                if (data.success && data.data && data.data.length > 0) {
                    console.log(`✅ 자석스티커 이미지 ${data.data.length}개 로드 성공`);
                    renderMstickerGalleryAPI(data.data, galleryContainer);
                } else {
                    console.log('⚠️ 자석스티커 이미지 데이터 없음');
                    galleryContainer.innerHTML = '<div class="error">표시할 이미지가 없습니다.</div>';
                }
            } catch (error) {
                console.error('❌ 자석스티커 갤러리 로딩 오류:', error);
                galleryContainer.innerHTML = '<div class="error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
            }
        }
        
        // API 갤러리 렌더링 (전단지 방식과 동일)
        function renderMstickerGalleryAPI(images, container) {
            console.log('🎨 자석스티커 갤러리 렌더링:', images.length + '개 이미지');
            
            // lightboxViewer div 생성 (자석스티커용)
            const viewerHtml = `
                <div class="lightbox-viewer" id="mstickerLightboxViewer">
                    <img id="mstickerMainImage" src="${images[0].path}" alt="${images[0].title}" 
                         style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;"
                         onclick="openFullScreenImage('${images[0].path}', '${images[0].title}')">
                </div>
                <div class="thumbnail-strip">
                    ${images.map((img, index) => 
                        `<img src="${img.path}" alt="${img.title}" class="${index === 0 ? 'active' : ''}"
                             onclick="changeMstickerMainImage('${img.path}', '${img.title}', this)">` 
                    ).join('')}
                </div>
            `;
            
            container.innerHTML = viewerHtml;
            
            // 자석스티커 마우스 호버 효과 적용 (전단지와 동일)
            initializeMstickerZoomEffect();
        }
        
        // 자석스티커 메인 이미지 변경 함수
        function changeMstickerMainImage(imagePath, title, thumbnail) {
            const mainImage = document.getElementById('mstickerMainImage');
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
            initializeMstickerZoomEffect();
        }
        
        // 자석스티커 이미지 줌 효과 초기화 (전단지 방식과 동일)
        function initializeMstickerZoomEffect() {
            const viewer = document.getElementById('mstickerLightboxViewer');
            const mainImage = document.getElementById('mstickerMainImage');
            
            if (!viewer || !mainImage) return;
            
            // 기존 이벤트 리스너 제거 후 재등록
            const newViewer = viewer.cloneNode(true);
            viewer.parentNode.replaceChild(newViewer, viewer);
            
            const newMainImage = document.getElementById('mstickerMainImage');
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
        
        // 통일된 갤러리 팝업 열기
        // 통일된 팝업 열기 함수 (전단지와 동일한 시스템)
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
        
        function openMstickerGalleryModal() {
            if (unifiedMstickerGallery) {
                unifiedMstickerGallery.show();
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
        
        // 갤러리 모달 제어 함수들 (기존 코드 호환성)
        function openMStickerGalleryModal() {
            openMstickerGalleryModal(); // 새로운 통일된 방식으로 리다이렉트
        }
        
        function closeMStickerGalleryModal() {
            const modal = document.getElementById('mstickerGalleryModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // 페이지네이션 변수
        let mstickerCurrentPage = 1;
        let mstickerPaginationData = null;
        
        function loadMStickerFullGallery(page = 1) {
            const galleryGrid = document.getElementById('mstickerGalleryModalGrid');
            if (!galleryGrid) return;
            
            galleryGrid.innerHTML = '<div class="gallery-loading">갤러리를 불러오는 중...</div>';
            
            fetch(`/api/get_real_orders_portfolio.php?category=msticker&all=true&page=${page}&per_page=12`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        mstickerCurrentPage = page;
                        mstickerPaginationData = data.pagination;
                        
                        if (data.data.length > 0) {
                            renderMStickerFullGallery(data.data, galleryGrid);
                            updateMStickerPagination(data.pagination);
                        } else {
                            galleryGrid.innerHTML = '<div class="gallery-empty">표시할 이미지가 없습니다.</div>';
                            hideMStickerPagination();
                        }
                    } else {
                        galleryGrid.innerHTML = '<div class="gallery-empty">표시할 이미지가 없습니다.</div>';
                        hideMStickerPagination();
                    }
                })
                .catch(error => {
                    console.error('Gallery loading error:', error);
                    galleryGrid.innerHTML = '<div class="gallery-error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
                    hideMStickerPagination();
                });
        }
        
        function updateMStickerPagination(pagination) {
            if (!pagination || pagination.total_pages <= 1) {
                hideMStickerPagination();
                return;
            }
            
            const paginationContainer = document.getElementById('mstickerPagination');
            const pageInfo = document.getElementById('mstickerPageInfo');
            const pageNumbers = document.getElementById('mstickerPageNumbers');
            const prevBtn = document.getElementById('mstickerPrevBtn');
            const nextBtn = document.getElementById('mstickerNextBtn');
            
            if (!paginationContainer || !pageInfo || !pageNumbers || !prevBtn || !nextBtn) return;
            
            // 페이지 정보 업데이트
            pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count}개)`;
            
            // 이전/다음 버튼 상태
            prevBtn.disabled = !pagination.has_prev;
            nextBtn.disabled = !pagination.has_next;
            
            // 페이지 번호 생성
            pageNumbers.innerHTML = generateMStickerPageNumbers(pagination);
            
            // 페이지네이션 표시
            paginationContainer.style.display = 'block';
        }
        
        function generateMStickerPageNumbers(pagination) {
            let html = '';
            const current = pagination.current_page;
            const total = pagination.total_pages;
            
            // 간단한 페이지 번호 생성 (1, 2, 3... 형태)
            const startPage = Math.max(1, current - 2);
            const endPage = Math.min(total, current + 2);
            
            if (startPage > 1) {
                html += `<span class="pagination-number" onclick="loadMStickerPage(1)">1</span>`;
                if (startPage > 2) {
                    html += `<span class="pagination-ellipsis">...</span>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === current ? 'active' : '';
                html += `<span class="pagination-number ${activeClass}" onclick="loadMStickerPage(${i})">${i}</span>`;
            }
            
            if (endPage < total) {
                if (endPage < total - 1) {
                    html += `<span class="pagination-ellipsis">...</span>`;
                }
                html += `<span class="pagination-number" onclick="loadMStickerPage(${total})">${total}</span>`;
            }
            
            return html;
        }
        
        function hideMStickerPagination() {
            const paginationContainer = document.getElementById('mstickerPagination');
            if (paginationContainer) {
                paginationContainer.style.display = 'none';
            }
        }
        
        function loadMStickerPage(pageOrDirection) {
            let targetPage;
            
            if (pageOrDirection === 'prev') {
                targetPage = Math.max(1, mstickerCurrentPage - 1);
            } else if (pageOrDirection === 'next') {
                targetPage = mstickerCurrentPage + 1;
            } else {
                targetPage = parseInt(pageOrDirection);
            }
            
            if (targetPage !== mstickerCurrentPage) {
                loadMStickerFullGallery(targetPage);
            }
        }
        
        function renderMStickerFullGallery(images, container) {
            let html = '';
            images.forEach((image, index) => {
                html += `
                    <div class="gallery-item" onclick="openLightbox('${image.path}', '${image.title}')">
                        <img src="${image.path}" alt="${image.title}" loading="lazy" 
                             onerror="this.parentElement.style.display='none'">
                        <div class="gallery-item-title">${image.title}</div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }
        
        function openLightbox(imagePath, title) {
            // 기존 GalleryLightbox 시스템과 연동
            if (window.lightboxViewer && window.lightboxViewer.showLightbox) {
                window.lightboxViewer.showLightbox(imagePath, title);
            } else {
                // 기본 동작: 새 창으로 이미지 열기
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
