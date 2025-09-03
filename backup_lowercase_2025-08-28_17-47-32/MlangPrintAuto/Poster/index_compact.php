<?php
/**
 * 포스터 견적안내 시스템 - 고품질 포스터 전문
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
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
$page_title = generate_page_title("포스터 견적안내 - 고품질 포스터 전문");

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
// 포스터는 LittlePrint와 같은 테이블을 사용하되 대형 사이즈 위주로 필터링
$type_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
               WHERE Ttable='littleprint' AND BigNo='0' 
               AND (title LIKE '%포스터%' OR title LIKE '%대형%' OR title LIKE '%현수막%' OR no IN ('590', '591', '592'))
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);

if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // mlangprintauto_littleprint에서 해당 스타일의 첫 번째 재질 가져오기
    $material_query = "SELECT DISTINCT TreeSelect FROM MlangPrintAuto_LittlePrint 
                       WHERE style='" . mysqli_real_escape_string($db, $type_row['no']) . "' 
                       AND TreeSelect IS NOT NULL 
                       ORDER BY TreeSelect ASC LIMIT 1";
    $material_result = mysqli_query($db, $material_query);
    
    if ($material_result && ($material_row = mysqli_fetch_assoc($material_result))) {
        $default_values['Section'] = $material_row['TreeSelect'];
        
        // 해당 재질의 첫 번째 규격 가져오기
        $size_query = "SELECT DISTINCT Section FROM MlangPrintAuto_LittlePrint 
                       WHERE TreeSelect='" . mysqli_real_escape_string($db, $material_row['TreeSelect']) . "' 
                       AND Section IS NOT NULL 
                       ORDER BY Section ASC LIMIT 1";
        $size_result = mysqli_query($db, $size_query);
        
        if ($size_result && ($size_row = mysqli_fetch_assoc($size_result))) {
            $default_values['PN_type'] = $size_row['Section'];
            
            // 첫 번째 인쇄면 가져오기
            $potype_query = "SELECT DISTINCT POtype FROM MlangPrintAuto_LittlePrint 
                            WHERE TreeSelect='" . mysqli_real_escape_string($db, $material_row['TreeSelect']) . "' 
                            AND Section='" . mysqli_real_escape_string($db, $size_row['Section']) . "'
                            ORDER BY POtype ASC LIMIT 1";
            $potype_result = mysqli_query($db, $potype_query);
            
            if ($potype_result && ($potype_row = mysqli_fetch_assoc($potype_result))) {
                $default_values['POtype'] = $potype_row['POtype'];
                
                // 첫 번째 수량 가져오기
                $quantity_query = "SELECT DISTINCT quantity FROM MlangPrintAuto_LittlePrint 
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
    <script src="../../js/poster_main.js" defer></script>
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
</head>
<body>
    <?php include "../../includes/nav.php"; ?>

    <div class="compact-container">
        <div class="page-title">
            <h1>🎨 포스터 견적안내</h1>
            <p>고품질 포스터 전문 - 대형 포스터부터 소형 포스터까지</p>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="main-content">
            <!-- 좌측: 고급 이미지 갤러리 (적응형 이미지 분석 및 스마트 확대) -->
            <div class="gallery-section">
                <div class="gallery-title">🎨 포스터 샘플 갤러리</div>
                
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
                                // mlangprintauto_transactioncate에서 포스터 종류만 필터링해서 가져오기
                                $category_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                                                  WHERE Ttable='littleprint' AND BigNo='0' 
                                                  AND (title LIKE '%포스터%' OR title LIKE '%대형%' OR title LIKE '%현수막%' OR no IN ('590', '591', '592'))
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
                                $potype_query = "SELECT DISTINCT POtype FROM MlangPrintAuto_LittlePrint 
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
                    <input type="hidden" name="page" value="poster">
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
            page: "poster",
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

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>