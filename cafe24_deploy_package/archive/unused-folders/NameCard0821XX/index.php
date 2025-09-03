<?php
/**
 * 명함 견적안내 컴팩트 시스템 - PROJECT_SUCCESS_REPORT.md 스펙 구현
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 8월 (AI Assistant - Frontend Persona)
 */

// 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("명함 견적안내 컴팩트 - 프리미엄");

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 명함 종류 가져오기 (일반명함(쿠폰) 우선)
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='NameCard' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 명함 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='NameCard' AND BigNo='" . $type_row['no'] . "' 
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
    
    <!-- 명함 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <link rel="stylesheet" href="../../css/gallery-common.css">
    
    <!-- 통합 갤러리 시스템 JavaScript -->
    <script src="../../includes/js/GalleryLightbox.js" defer></script>
    <script src="js/unified-gallery.js" defer></script>
    
    <!-- 통일된 갤러리 팝업 시스템 -->
    <link rel="stylesheet" href="../../css/unified-gallery-popup.css">
    <script src="../../js/unified-gallery-popup.js"></script>
    <script src="../../js/namecard.js" defer></script>
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
        <div class="page-title">
            <h1>💳 명함 견적안내</h1>
            <p><!--  컴팩트 프리미엄 - PROJECT_SUCCESS_REPORT.md 스펙 구현  --></p>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="main-content">
            <!-- 좌측: 표준화된 갤러리 시스템 -->
            <section class="namecard-gallery" aria-label="명함 샘플 갤러리">
                <div class="gallery-section">
                    <!-- 갤러리 제목 -->
                    <div class="gallery-title">
                        🖼️ 명함 샘플 갤러리
                    </div>
                    
                    <!-- 갤러리 콘텐츠 -->
                    <div id="namecardGallery">
                        <?php 
                        $galleryConfig = [
                            'category' => 'namecard',
                            'title' => '명함',
                            'icon' => '💼',
                            'hover_system' => 'lightbox'
                        ];
                        include $_SERVER['DOCUMENT_ROOT']."/components/ProofGalleryInline.php"; 
                        ?>
                        
                        <!-- 더 많은 샘플 보기 버튼 -->
                        <button 
                            class="btn-primary"
                            onclick="openUnifiedModal('명함', '💼')"
                            aria-label="더 많은 샘플 보기"
                        >
                            <span aria-hidden="true">📂</span> 더 많은 샘플 보기
                        </button>
                    </div>
                </div>
            </section>
            </div>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="calculator-section">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="namecardForm">
                    <!-- 옵션 선택 그리드 - 개선된 2열 레이아웃 -->
                    <div class="options-grid">
                        <div class="option-group">
                            <label class="option-label" for="MY_type">명함 종류</label>
                            <select class="option-select" name="MY_type" id="MY_type" required>
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'NameCard');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="Section">명함 재질</label>
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
                    <input type="hidden" name="page" value="NameCard">
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

    <!-- 통합 갤러리 모달 포함 -->
    <?php include "../../includes/unified_gallery_modal.php"; ?>

    <!-- 라이트박스 시스템 -->
    <div id="lightbox" class="lightbox" style="display: none;">
        <div class="lightbox-overlay" onclick="closeLightbox()"></div>
        <div class="lightbox-content">
            <img id="lightboxImage" src="" alt="확대 이미지">
            <button class="btn-lightbox-close" onclick="closeLightbox()">✕</button>
            <button class="btn-prev" onclick="prevLightboxImage()">‹</button>
            <button class="btn-next" onclick="nextLightboxImage()">›</button>
            <div class="lightbox-info">
                <h4 id="lightboxTitle">이미지 제목</h4>
                <p id="lightboxCategory">카테고리</p>
            </div>
        </div>
    </div>

    <?php include "../../includes/login_modal.php"; ?>
    <?php include "../../includes/footer.php"; ?>

    <!-- 명함 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    <style>
    /* =================================================================== */
    /* 아키텍처 가이드: 명함 브랜드 시스템 */
    /* =================================================================== */
    :root {
        --namecard-primary: #2196f3;     /* 명함 - 블루 */
        --namecard-secondary: #1976d2;
    }
    
    /* 전단지 표준 갤러리 스타일 */
    .namecard-gallery .gallery-title {
        background: linear-gradient(135deg, var(--namecard-primary) 0%, var(--namecard-secondary) 100%);
        color: white;
        padding: 18px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.9);
    }

    .namecard-gallery .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.9);
    }
    
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
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        color: white !important;
        padding: 18px 20px !important;
        margin: -25px -25px 5px -25px !important;
        border-radius: 15px 15px 0 0 !important;
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 2px 10px rgba(23, 162, 184, 0.3) !important;
        line-height: 1.2 !important;
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
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.15) !important;
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
    /* 5단계: 그리드 레이아웃 최적화 (좌우 균등 + 상단 정렬) */
    /* =================================================================== */
    .main-content {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 20px !important;
        align-items: start !important; /* 그리드 아이템들을 상단 정렬 */
    }

    /* =================================================================== */
    /* 6단계: 섹션 그림자 효과 (강화된 시각적 구분) */
    /* =================================================================== */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 15px !important;
        padding: 25px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        position: relative !important;
        margin-top: 0 !important;
        align-self: start !important;
    }

    /* calculator-section에 갤러리와 동일한 배경 적용 */
    .calculator-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 15px !important;
        padding: 25px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        position: relative !important; /* 헤더 오버플로우를 위한 설정 */
        margin-top: 0 !important;
        align-self: start !important;
        min-height: 400px !important;
    }
    
    .gallery-title {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 15px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 10px rgba(23, 162, 184, 0.3);
    }

    /* =================================================================== */
    /* 통합 갤러리 시스템 스타일 */
    /* =================================================================== */
    
    /* 메인 뷰어 스타일 */
    .main-viewer {
        width: 100%;
        height: 300px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
    }
    
    .main-viewer:hover {
        border-color: var(--namecard-primary);
        box-shadow: 0 8px 30px rgba(33, 150, 243, 0.15);
        transform: translateY(-2px);
    }
    
    .main-viewer img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    /* 포스터 방식: zoom-active 클래스로 호버 확대 제어 */
    .main-viewer.zoom-active img {
        transform: scale(1.35);
    }
    
    .viewer-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .main-viewer:hover .viewer-overlay {
        opacity: 1;
    }
    
    .zoom-icon {
        font-size: 2rem;
        color: white;
        background: rgba(33, 150, 243, 0.8);
        padding: 15px;
        border-radius: 50%;
        border: 2px solid white;
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
        margin-bottom: 15px;
    }
    
    .thumbnail-item {
        width: 100%;
        height: 80px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        overflow: hidden;
        position: relative;
    }
    
    .thumbnail-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.7;
        transition: all 0.3s ease;
    }
    
    .thumbnail-item:hover img,
    .thumbnail-item.active img {
        opacity: 1;
    }
    
    .thumbnail-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        border-color: var(--namecard-primary);
    }
    
    .thumbnail-item.active {
        border-color: var(--namecard-primary);
        box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
    }
    
    /* 로딩 상태 */
    .thumbnail-item.loading {
        background: #f8f9fa;
    }
    
    .loading-shimmer {
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    /* 더보기 버튼 - 브랜드 색상 적용 */
    .btn-view-more {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, var(--namecard-primary) 0%, var(--namecard-secondary) 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .btn-view-more:hover {
        background: linear-gradient(135deg, var(--namecard-secondary) 0%, #1565c0 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
    }
    
    /* 갤러리 로딩 상태 */
    #namecardGallery .loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        background: white;
        border-radius: 12px;
        animation: pulse 2s infinite;
    }
    
    /* 갤러리 에러 상태 */
    #namecardGallery .error {
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
    /* 팝업 갤러리 시스템 스타일 */
    /* =================================================================== */
    .gallery-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
    }
    
    .popup-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .popup-content {
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 1200px;
        max-height: 90%;
        overflow: hidden;
        position: relative;
        animation: popupIn 0.3s ease-out;
    }
    
    @keyframes popupIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-50px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    .popup-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .popup-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .btn-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 5px;
        transition: background 0.3s ease;
    }
    
    .btn-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .popup-body {
        padding: 20px;
        max-height: 600px;
        overflow-y: auto;
    }
    
    /* 이미지 그리드 */
    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .grid-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .grid-image:hover {
        transform: scale(1.05);
        border-color: #17a2b8;
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
    }
    
    .grid-loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #17a2b8;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* 페이지네이션 */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }
    
    .pagination button {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        background: white;
        color: #6c757d;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .pagination button:hover:not(:disabled) {
        background: #17a2b8;
        color: white;
        border-color: #17a2b8;
    }
    
    .pagination button.active {
        background: #17a2b8;
        color: white;
        border-color: #17a2b8;
    }
    
    .pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* =================================================================== */
    /* 라이트박스 시스템 스타일 */
    /* =================================================================== */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 20000;
        backdrop-filter: blur(10px);
    }
    
    .lightbox-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        animation: lightboxIn 0.3s ease-out;
    }
    
    @keyframes lightboxIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .lightbox-content img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }
    
    .btn-lightbox-close {
        position: absolute;
        top: -50px;
        right: 0;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        padding: 10px 15px;
        border-radius: 50%;
        transition: background 0.3s ease;
    }
    
    .btn-lightbox-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .btn-prev,
    .btn-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        padding: 15px 20px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    
    .btn-prev {
        left: -80px;
    }
    
    .btn-next {
        right: -80px;
    }
    
    .btn-prev:hover,
    .btn-next:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%) scale(1.1);
    }
    
    .lightbox-info {
        position: absolute;
        bottom: -60px;
        left: 0;
        right: 0;
        text-align: center;
        color: white;
    }
    
    .lightbox-info h4 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
    }
    
    .lightbox-info p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.8;
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
            page: "NameCard",
            defaultValues: {
                MY_type: "<?php echo safe_html($default_values['MY_type']); ?>",
                Section: "<?php echo safe_html($default_values['Section']); ?>",
                POtype: "<?php echo safe_html($default_values['POtype']); ?>",
                MY_amount: "<?php echo safe_html($default_values['MY_amount']); ?>",
                ordertype: "<?php echo safe_html($default_values['ordertype']); ?>"
            }
        };

        // namecard.js에서 가격 계산 및 기타 로직 처리
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('명함 갤러리 시스템 초기화');
            
            // 1. 메인 갤러리 로드
            loadNamecardGallery();
            
            // 2. 통합 팝업 갤러리는 unified_gallery_modal.php에서 자동 처리
        });
        
        async function loadNamecardGallery() {
            try {
                const response = await fetch('/api/get_real_orders_portfolio.php?category=namecard&per_page=4');
                const data = await response.json();
                
                if (data.success && data.data.length > 0) {
                    renderNamecardGallery(data.data);
                } else {
                    renderPlaceholderGallery();
                }
            } catch (error) {
                console.error('명함 갤러리 로드 실패:', error);
                renderPlaceholderGallery();
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