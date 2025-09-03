<?php
/**
 * 상품권/쿠폰 견적안내 컴팩트 시스템 - NameCard 시스템 구조 적용
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 * FIXED: db_constants.php 제거 버전
 */

// 공통 인증 및 설정 (db_constants.php 제거)
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
               WHERE Ttable='merchandisebond' AND BigNo='0' 
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 상품권/쿠폰 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
                      WHERE Ttable='merchandisebond' AND BigNo='" . $type_row['no'] . "' 
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
    
    <!-- 상품권/쿠폰 컴팩트 페이지 전용 CSS (NameCard 시스템 공유) -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
</head>
<body>
    <?php include "../../includes/nav.php"; ?>
    
    <div class="container-main">
        <!-- 좌측 네비게이션 -->
        <?php include "../../left.php"; ?>
        
        <!-- 메인 콘텐츠 영역 -->
        <div class="content-wrap-wide">
            <!-- 페이지 헤더 - 깔끔한 타이포그래피 -->
            <div class="page-header-compact">
                <h1 class="page-title">
                    <span class="title-icon">🎫</span>
                    상품권/쿠폰 견적안내
                </h1>
                <p class="page-subtitle">프리미엄 상품권과 쿠폰 인쇄, 실시간 견적 시스템</p>
            </div>

            <!-- 메인 컨테이너 -->
            <div class="estimate-container">
                <!-- 좌측: 옵션 선택 및 가격 영역 -->
                <div class="options-section">
                    <div class="section-header">
                        <h2 class="section-title">📋 견적 옵션 선택</h2>
                    </div>
                    
                    <form id="merchandisebondForm">
                        <!-- 옵션 선택 그리드 - 개선된 2열 레이아웃 -->
                        <div class="options-grid">
                            <div class="option-group">
                                <label class="option-label" for="MY_type">구분</label>
                                <select class="option-select" name="MY_type" id="MY_type" required>
                                    <option value="">선택해주세요</option>
                                    <?php
                                    $categories = getCategoryOptions($db, "MlangPrintAuto_transactionCate', 'merchandisebond');
                                    foreach ($categories as $category) {
                                        $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                        echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label" for="Section">종류</label>
                                <select class="option-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>">
                                    <option value="">먼저 구분을 선택해주세요</option>
                                </select>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label" for="POtype">인쇄면</label>
                                <select class="option-select" name="POtype" id="POtype" required>
                                    <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                    <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                                </select>
                            </div>
                            
                            <div class="option-group">
                                <label class="option-label" for="MY_amount">수량</label>
                                <select class="option-select" name="MY_amount" id="MY_amount" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
                                    <option value="">먼저 종류를 선택해주세요</option>
                                </select>
                            </div>
                            
                            <div class="option-group full-width">
                                <label class="option-label" for="ordertype">주문 타입</label>
                                <select class="option-select" name="ordertype" id="ordertype" required>
                                    <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만</option>
                                    <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인 + 인쇄</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    
                    <!-- 실시간 가격 표시 영역 - 더 명확한 디자인 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-header">
                            <span class="price-icon">💰</span>
                            <span class="price-title">실시간 견적</span>
                        </div>
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">모든 옵션을 선택하면 자동으로 계산됩니다</div>
                        
                        <!-- 주문 버튼 영역 -->
                        <div class="order-buttons" id="uploadOrderButton" style="display: none;">
                            <button type="button" class="btn-primary" onclick="addToCart()">
                                <i class="fas fa-shopping-cart"></i> 장바구니에 담기
                            </button>
                            <button type="button" class="btn-secondary" onclick="directOrder()">
                                <i class="fas fa-arrow-right"></i> 바로 주문하기
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 우측: 샘플 이미지 갤러리 -->
                <div class="gallery-section">
                    <div class="section-header">
                        <h2 class="section-title">🎨 샘플 갤러리</h2>
                    </div>
                    <div id="merchandisebondGallery" class="gallery-container">
                        <!-- 갤러리 이미지는 JavaScript로 동적 로드 -->
                    </div>
                </div>
            </div>
            
            <!-- 파일 업로드 영역 - 모달 창으로 표시 -->
            <div id="uploadModal" class="upload-modal" style="display: none;">
                <div class="modal-content">
                    <span class="modal-close" onclick="closeUploadModal()">&times;</span>
                    <h3>파일 업로드</h3>
                    <?php
                    if (function_exists('renderFileUploadComponent')) {
                        renderFileUploadComponent();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 공통 푸터 -->
    <?php include "../../includes/footer.php"; ?>
    
    <!-- JavaScript 파일들 -->
    <script src="../../js/merchandisebond.js" defer></script>
</body>
</html>