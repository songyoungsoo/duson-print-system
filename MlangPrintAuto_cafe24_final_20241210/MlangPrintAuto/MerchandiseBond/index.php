<?php
/**
 * 상품권/쿠폰 견적안내 컴팩트 시스템 - NameCard 시스템 구조 적용
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

// 보안 상수 정의 후 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("상품권/쿠폰 견적안내 컴팩트 - 프리미엄");

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 상품권/쿠폰 종류 가져오기
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='MerchandiseBond' AND BigNo='0' 
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 상품권/쿠폰 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='MerchandiseBond' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (100매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_MerchandiseBond 
                          WHERE style='" . $type_row['no'] . "' AND Section='" . $section_row['no'] . "' 
                          ORDER BY CASE WHEN quantity='100' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
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
    
    <!-- 상품권/쿠폰 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <link rel="stylesheet" href="../../css/gallery-common.css">
    <link rel="stylesheet" href="../../css/btn-primary.css">
    
    <!-- 고급 JavaScript 라이브러리 (적응형 이미지 분석 및 실시간 계산) -->
    <script src="../../includes/js/GalleryLightbox.js"></script>
    <script src="../../js/merchandisebond.js" defer></script>
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
        <div class="page-title">
            <h1>🎁 상품권/쿠폰 견적안내</h1>
            <p>컴팩트 프리미엄 - NameCard 시스템 구조 적용</p>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="main-content">
            <!-- 좌측: 통합 갤러리 시스템 -->
            <section class="merchandisebond-gallery" aria-label="상품권/쿠폰 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 사용 (3줄로 완전 간소화)
                include_once "../../includes/gallery_helper.php";
                include_product_gallery('merchandisebond', ['mainSize' => [500, 400]]);
                ?>
            </section>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="calculator-section">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="merchandisebondForm">
                    <!-- 옵션 선택 그리드 - 개선된 2열 레이아웃 -->
                    <div class="options-grid">
                        <div class="option-group">
                            <label class="option-label" for="MY_type">상품권 종류</label>
                            <select class="option-select" name="MY_type" id="MY_type" required>
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'MerchandiseBond');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="Section">상품권 재질</label>
                            <select class="option-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>">
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
                            <select class="option-select" name="MY_amount" id="MY_amount" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
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

                    <!-- 실시간 가격 표시 - 개선된 애니메이션 -->
                    <div class="price-display" id="priceDisplay">
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
                    <input type="hidden" name="page" value="MerchandiseBond">
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

    <!-- 상품권 통합 갤러리 모달 색상 설정 -->
    <style>
    /* 통합 갤러리 모달 헤더 색상 (상품권 브랜드 색상 - 핑크색) */
    .gallery-modal-header {
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%) !important;
        color: white !important;
    }
    
    .gallery-modal-close {
        color: white !important;
    }
    
    .gallery-modal-close:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }
    </style>

    <?php
    // 갤러리 에셋 자동 포함
    if (defined('GALLERY_ASSETS_NEEDED')) {
        include_gallery_assets();
    }
    ?>
    
    <!-- 상품권 브랜드 컬러 적용 (핑크 계열) -->
    <style>
        /* 상품권 갤러리 모달 브랜드 컬러 */
        [data-product="merchandisebond"] .gallery-modal-header {
            background: linear-gradient(135deg, #ff6b9d, #ffc1e3);
            color: white;
        }
        
        [data-product="merchandisebond"] .gallery-modal-header h2 {
            color: white;
        }
        
        [data-product="merchandisebond"] .gallery-more-btn {
            background: linear-gradient(135deg, #ff6b9d, #ffc1e3);
            border: none;
        }
        
        [data-product="merchandisebond"] .gallery-more-btn:hover {
            background: linear-gradient(135deg, #ff4585, #ff9ad6);
            transform: translateY(-1px);
        }
        
        /* 상품권 썸네일 활성 상태 */
        [data-product="merchandisebond"] .gallery-thumb.active {
            border-color: #ff6b9d;
            box-shadow: 0 0 0 1px rgba(255, 107, 157, 0.25);
        }
        
        [data-product="merchandisebond"] .gallery-thumb:hover {
            border-color: #ff4585;
        }
    </style>

    <?php
    // 갤러리 모달과 JavaScript는 include_product_gallery()에서 자동 포함됨
    ?>

    <?php include "../../includes/login_modal.php"; ?>
    <?php 
    include "../../includes/footer.php"; 
    ?>

    <!-- 상품권/쿠폰 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
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
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%) !important;
        color: white !important;
        border-radius: 12px !important;
        text-align: center !important;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3) !important;
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
    /* 6단계: 갤러리 섹션 스타일 (상품권 브랜드 컬러) */
    /* =================================================================== */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }
    
    .gallery-title {
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
        color: white;
        padding: 15px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 10px rgba(233, 30, 99, 0.3);
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
        border-color: #e91e63;
        box-shadow: 0 8px 30px rgba(233, 30, 99, 0.15);
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
        border-color: #e91e63;
    }
    
    .thumbnail-strip img.active {
        opacity: 1;
        border-color: #e91e63;
        box-shadow: 0 4px 15px rgba(233, 30, 99, 0.3);
    }
    
    /* 갤러리 로딩 상태 */
    #merchandisebondGallery .loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        background: white;
        border-radius: 12px;
        animation: pulse 2s infinite;
    }
    
    /* 갤러리 에러 상태 */
    #merchandisebondGallery .error {
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
    /* 갤러리 더보기 버튼 스타일 */
    /* =================================================================== */
    .gallery-more-button {
        text-align: center;
        margin-top: 20px;
    }
    
    .btn-more-gallery {
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 3px 12px rgba(233, 30, 99, 0.3);
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-more-gallery:hover {
        background: linear-gradient(135deg, #ad1457 0%, #8e0038 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(233, 30, 99, 0.4);
    }
    
    .btn-more-gallery:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(233, 30, 99, 0.3);
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
        background: rgba(0, 0, 0, 0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .gallery-modal[style*="flex"] {
        opacity: 1;
        visibility: visible;
    }
    
    .gallery-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .gallery-modal-content {
        background: white;
        border-radius: 15px;
        max-width: 90vw;
        max-height: 90vh;
        width: 1000px;
        position: relative;
        z-index: 1;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }
    
    .gallery-modal[style*="flex"] .gallery-modal-content {
        transform: scale(1);
    }
    
    .gallery-modal-header {
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
        color: white;
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .gallery-modal-header h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .gallery-modal-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.2rem;
        font-weight: bold;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .gallery-modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }
    
    .gallery-modal-body {
        padding: 25px;
        max-height: calc(90vh - 140px);
        overflow-y: auto;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        padding: 10px 0;
    }
    
    /* 페이지네이션 스타일 */
    .gallery-pagination {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-top: 1px solid #dee2e6;
    }

    .pagination-info {
        text-align: center;
        margin-bottom: 15px;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pagination-btn {
        background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 80px;
    }

    .pagination-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, #d81b60 0%, #a0124e 100%);
        transform: translateY(-2px);
    }

    .pagination-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
    }

    .pagination-numbers {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .pagination-number {
        background: white;
        color: #e91e63;
        border: 2px solid #e91e63;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 40px;
    }

    .pagination-number:hover {
        background: #e91e63;
        color: white;
        transform: translateY(-2px);
    }

    .pagination-number.active {
        background: #e91e63;
        color: white;
        font-weight: bold;
    }
    
    .gallery-item {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-color: #e91e63;
    }
    
    .gallery-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .gallery-item:hover img {
        transform: scale(1.05);
    }
    
    .gallery-item-title {
        padding: 15px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #333;
        text-align: center;
        line-height: 1.3;
        border-top: 1px solid #f0f0f0;
    }
    
    .gallery-loading,
    .gallery-empty,
    .gallery-error {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        grid-column: 1 / -1;
    }
    
    .gallery-error {
        color: #dc3545;
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
            page: "MerchandiseBond",
            defaultValues: {
                MY_type: "<?php echo safe_html($default_values['MY_type']); ?>",
                Section: "<?php echo safe_html($default_values['Section']); ?>",
                POtype: "<?php echo safe_html($default_values['POtype']); ?>",
                MY_amount: "<?php echo safe_html($default_values['MY_amount']); ?>",
                ordertype: "<?php echo safe_html($default_values['ordertype']); ?>"
            }
        };

        // merchandisebond.js에서 전역 변수와 초기화 함수들을 처리
        // 고급 갤러리 시스템 자동 로드
        
        // 갤러리 모달 제어 함수들 (페이지네이션 지원)
        let merchandiseBondCurrentPage = 1;
        let merchandiseBondTotalPages = 1;
        
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
        
        // 독립 모달 함수들 제거됨 - 통합 갤러리 시스템 사용
        
        // 독립 갤러리 함수들 제거됨 - 통합 갤러리 시스템에서 모든 기능 처리
    </script>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>