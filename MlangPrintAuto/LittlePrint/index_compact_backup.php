<?php
/**
 * 포스터/리플렛 견적안내 컴팩트 시스템 - PROJECT_SUCCESS_REPORT.md 스펙 구현  
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
$page_title = generate_page_title("포스터/리플렛 견적안내 컴팩트 - 프리미엄");

// 기본값 설정 (데이터베이스에서 완전히 동적으로 가져오기)
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'PN_type' => '',
    'POtype' => '',
    'MY_amount' => '',
    'ordertype' => ''
];

// mlangprintauto_transactioncate에서 첫 번째 포스터 종류 가져오기
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate 
               WHERE Ttable='LittlePrint' AND BigNo='0' 
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);

if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // mlangprintauto_littleprint에서 해당 스타일의 첫 번째 재질 가져오기
    $material_query = "SELECT DISTINCT TreeSelect FROM mlangprintauto_littleprint 
                       WHERE style='" . mysqli_real_escape_string($db, $type_row['no']) . "' 
                       AND TreeSelect IS NOT NULL 
                       ORDER BY TreeSelect ASC LIMIT 1";
    $material_result = mysqli_query($db, $material_query);
    
    if ($material_result && ($material_row = mysqli_fetch_assoc($material_result))) {
        $default_values['Section'] = $material_row['TreeSelect'];
        
        // 해당 재질의 첫 번째 규격 가져오기
        $size_query = "SELECT DISTINCT Section FROM mlangprintauto_littleprint 
                       WHERE TreeSelect='" . mysqli_real_escape_string($db, $material_row['TreeSelect']) . "' 
                       AND Section IS NOT NULL 
                       ORDER BY Section ASC LIMIT 1";
        $size_result = mysqli_query($db, $size_query);
        
        if ($size_result && ($size_row = mysqli_fetch_assoc($size_result))) {
            $default_values['PN_type'] = $size_row['Section'];
            
            // 첫 번째 인쇄면 가져오기
            $potype_query = "SELECT DISTINCT POtype FROM mlangprintauto_littleprint 
                            WHERE TreeSelect='" . mysqli_real_escape_string($db, $material_row['TreeSelect']) . "' 
                            AND Section='" . mysqli_real_escape_string($db, $size_row['Section']) . "'
                            ORDER BY POtype ASC LIMIT 1";
            $potype_result = mysqli_query($db, $potype_query);
            
            if ($potype_result && ($potype_row = mysqli_fetch_assoc($potype_result))) {
                $default_values['POtype'] = $potype_row['POtype'];
                
                // 첫 번째 수량 가져오기
                $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_littleprint 
                                  WHERE style='" . mysqli_real_escape_string($db, $type_row['no']) . "' 
                                  AND TreeSelect='" . mysqli_real_escape_string($db, $material_row['TreeSelect']) . "'
                                  AND Section='" . mysqli_real_escape_string($db, $size_row['Section']) . "'
                                  AND POtype='" . mysqli_real_escape_string($db, $potype_row['POtype']) . "'
                                  ORDER BY CAST(quantity AS UNSIGNED) ASC LIMIT 1";
                $quantity_result = mysqli_query($db, $quantity_query);
                
                if ($quantity_result && ($quantity_row = mysqli_fetch_assoc($quantity_result))) {
                    $default_values['MY_amount'] = $quantity_row['quantity'];
                }
            }
        }
    }
}

// ordertype 기본값 (디자인만 하드코딩)
$default_values['ordertype'] = 'print'; // 인쇄만
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 공통 헤더 포함 -->
    <?php include "../../includes/header.php"; ?>
    
    <!-- 포스터 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <link rel="stylesheet" href="../../css/gallery-common.css">
    
    <!-- 고급 JavaScript 라이브러리 (적응형 이미지 분석 및 실시간 계산) -->
    <script src="../../includes/js/GalleryLightbox.js"></script>
    <script src="../../js/poster.js" defer></script>
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
        <div class="page-title">
            <h1>📄 포스터/리플렛 견적안내</h1>
            <p>컴팩트 프리미엄 - PROJECT_SUCCESS_REPORT.md 스펙 구현</p>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="main-content">
            <!-- 좌측: 고급 이미지 갤러리 (적응형 이미지 분석 및 스마트 확대) -->
            <div class="gallery-section">
                <div class="gallery-title">🖼️ 포스터 샘플 갤러리</div>
                
                <!-- 고급 갤러리 시스템 (PROJECT_SUCCESS_REPORT.md 스펙) -->
                <div id="posterGallery">
                    <div class="loading">🖼️ 갤러리 로딩 중...</div>
                </div>
            </div>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="calculator-section">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="posterForm">
                    <!-- 옵션 선택 그리드 - 개선된 2열 레이아웃 -->
                    <div class="options-grid">
                        <div class="option-group">
                            <label class="option-label" for="MY_type">포스터 종류</label>
                            <select class="option-select" name="MY_type" id="MY_type" required>
                                <option value="">선택해주세요</option>
                                <?php
                                // mlangprintauto_transactioncate에서 동적으로 포스터 종류 가져오기
                                $category_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                                                  WHERE Ttable='LittlePrint' AND BigNo='0' 
                                                  ORDER BY no ASC";
                                $category_result = mysqli_query($db, $category_query);
                                if ($category_result) {
                                    while ($category = mysqli_fetch_assoc($category_result)) {
                                        $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                        echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="Section">용지 재질</label>
                            <select class="option-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="PN_type">규격</label>
                            <select class="option-select" name="PN_type" id="PN_type" required>
                                <option value="">먼저 재질을 선택해주세요</option>
                            </select>
                        </div>

                        <div class="option-group">
                            <label class="option-label" for="POtype">인쇄면</label>
                            <select class="option-select" name="POtype" id="POtype" required>
                                <option value="">선택해주세요</option>
                                <?php
                                // mlangprintauto_littleprint에서 사용 가능한 인쇄면 옵션 가져오기
                                $potype_query = "SELECT DISTINCT POtype FROM mlangprintauto_littleprint 
                                               WHERE POtype IS NOT NULL 
                                               ORDER BY POtype ASC";
                                $potype_result = mysqli_query($db, $potype_query);
                                if ($potype_result) {
                                    while ($potype = mysqli_fetch_assoc($potype_result)) {
                                        $selected = ($potype['POtype'] == $default_values['POtype']) ? 'selected' : '';
                                        $potype_text = ($potype['POtype'] == '1') ? '단면' : '양면';
                                        echo "<option value='" . safe_html($potype['POtype']) . "' $selected>" . safe_html($potype_text) . "</option>";
                                    }
                                }
                                ?>
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
                                <?php
                                // 편집디자인 옵션 (이 부분은 비즈니스 로직이므로 간단한 배열 사용)
                                $ordertype_options = [
                                    ['value' => 'print', 'text' => '인쇄만 의뢰'],
                                    ['value' => 'total', 'text' => '디자인+인쇄']
                                ];
                                foreach ($ordertype_options as $option) {
                                    $selected = ($option['value'] == $default_values['ordertype']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($option['value']) . "' $selected>" . safe_html($option['text']) . "</option>";
                                }
                                ?>
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

    <?php include "../../includes/login_modal.php"; ?>
    <?php include "../../includes/footer.php"; ?>

    <script>
        // PHP 변수를 JavaScript로 전달 (PROJECT_SUCCESS_REPORT.md 스펙)
        window.phpVars = {
            MultyUploadDir: "../../PHPClass/MultyUpload",
            log_url: "<?php echo safe_html($log_info['url']); ?>",
            log_y: "<?php echo safe_html($log_info['y']); ?>",
            log_md: "<?php echo safe_html($log_info['md']); ?>",
            log_ip: "<?php echo safe_html($log_info['ip']); ?>",
            log_time: "<?php echo safe_html($log_info['time']); ?>",
            page: "LittlePrint",
            defaultValues: {
                MY_type: "<?php echo safe_html($default_values['MY_type']); ?>",
                Section: "<?php echo safe_html($default_values['Section']); ?>",
                POtype: "<?php echo safe_html($default_values['POtype']); ?>",
                MY_amount: "<?php echo safe_html($default_values['MY_amount']); ?>",
                ordertype: "<?php echo safe_html($default_values['ordertype']); ?>"
            }
        };

        // namecard.js에서 전역 변수와 초기화 함수들을 처리
        // PROJECT_SUCCESS_REPORT.md 스펙에 따른 고급 갤러리 시스템 자동 로드
    </script>

    <!-- 포스터/리플렛 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
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
        background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%) !important;
        color: white !important;
        padding: 15px 20px !important;       /* gallery-title과 동일 */
        margin: 0px -25px 20px -25px !important; /* 좌우 -25px로 섹션 너비에 맞춤 */
        border-radius: 15px 15px 0 0 !important;  /* gallery-title과 동일한 라운딩 */
        font-size: 1.1rem !important;        /* gallery-title과 동일 */
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 2px 10px rgba(156, 39, 176, 0.3) !important;
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
        box-shadow: 0 4px 12px rgba(156, 39, 176, 0.15) !important;
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
    /* 6단계: 갤러리 섹션 스타일 (포스터 브랜드 컬러 - 퍼플-바이올렛) */
    /* =================================================================== */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9);
    }
    
    .gallery-title {
        background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
        color: white;
        padding: 15px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 10px rgba(156, 39, 176, 0.3);
    }

    /* 라이트박스 뷰어 스타일 */
    .lightbox-viewer {
        width: 100%;
        height: 400px;
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
        border-color: #9c27b0;
        box-shadow: 0 8px 30px rgba(156, 39, 176, 0.15);
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
        border-color: #9c27b0;
    }
    
    .thumbnail-strip img.active {
        opacity: 1;
        border-color: #9c27b0;
        box-shadow: 0 4px 15px rgba(156, 39, 176, 0.3);
    }
    
    /* 갤러리 로딩 상태 */
    .posterGallery .loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        background: white;
        border-radius: 12px;
        animation: pulse 2s infinite;
    }
    
    /* 갤러리 에러 상태 */
    .posterGallery .error {
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

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>