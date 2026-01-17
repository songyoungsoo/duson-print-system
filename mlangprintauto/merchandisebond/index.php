<?php
// 테마 시스템 로드
include_once __DIR__ . '/../../includes/theme_loader.php';

/**
 * 상품권/쿠폰 견적안내 컴팩트 시스템 - NameCard 시스템 구조 적용
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

// 보안 상수 정의 후 공통 인증 및 설정
include "../../includes/auth.php";

// 견적서 모달용 간소화 모드 체크
$isQuotationMode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';
$isAdminQuoteMode = isset($_GET['mode']) && $_GET['mode'] === 'admin_quote';

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 방문자 추적 시스템
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

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
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate 
               WHERE Ttable='MerchandiseBond' AND BigNo='0' 
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 상품권/쿠폰 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                      WHERE Ttable='MerchandiseBond' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (100매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_merchandisebond 
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
    <!-- 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    
    
    
    <!-- 상품권/쿠폰 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css?v=<?php echo filemtime(__DIR__ . '/../../css/product-layout.css'); ?>">
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">

    <!-- 통합 가격 표시 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    <!-- 통일 인라인 폼 스타일 시스템 -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css?v=<?php echo filemtime(__DIR__ . '/../../css/unified-inline-form.css'); ?>">
    <!-- 프리미엄 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">
    

    <!-- 고급 JavaScript 라이브러리 (적응형 이미지 분석 및 실시간 계산) -->
    <script src="../../includes/js/GalleryLightbox.js"></script>
    <script src="../../js/merchandisebond.js?v=<?php echo time(); ?>" defer></script>

    <!-- 프리미엄 옵션 시스템 (명함 방식 적용) -->
    <script src="js/merchandisebond-premium-options.js?v=<?php echo time(); ?>"></script>

    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    <!-- 공통 갤러리 팝업 함수 -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/merchandisebond-inline-extracted.css">
    <!-- 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
    <!-- 견적서 모달용 공통 스타일 -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">

<!-- Phase 5: 견적 요청 버튼 스타일 -->
<style>
    .action-buttons { display: flex; gap: 10px; margin-top: 20px; }
    .action-buttons button { flex: 1; padding: 15px 20px; font-size: 16px; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
    /* .btn-upload-order → common-styles.css SSOT 사용 */
    .btn-request-quote { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
    .btn-request-quote:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(240, 147, 251, 0.4); }
</style>
    <!-- 테마 시스템 CSS -->
    <?php ThemeLoader::renderCSS(); ?>


</head>
<body class="merchandisebond-page<?php echo ($isQuotationMode || $isAdminQuoteMode) ? ' quotation-modal-mode' : ''; ?>" <?php ThemeLoader::renderBodyAttributes(); ?>>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/header-ui.php"; ?>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/nav.php"; ?>

    <div class="product-container">
        <div class="page-title">
            <h1>상품권/쿠폰 견적 안내</h1>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
            <!-- 좌측: 통합 갤러리 시스템 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="상품권/쿠폰 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'merchandisebond';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </section>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="product-calculator">
                <div class="calculator-header">
                    <h3>실시간 견적 계산기</h3>
                </div>

                <form id="merchandisebondForm">
                    <!-- 통일 인라인 폼 시스템 - MerchandiseBond 페이지 -->
                    <div class="inline-form-container">
                        <!-- 1. 종류 -->
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">종류</label>
                            <select class="inline-select" name="MY_type" id="MY_type" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "mlangprintauto_transactioncate", 'MerchandiseBond');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="inline-note">상품권 종류를 선택하세요</span>
                        </div>

                        <!-- 2. 수량 -->
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_amount">수량</label>
                            <select class="inline-select" name="MY_amount" id="MY_amount" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하시는 수량을 선택하세요</span>
                        </div>

                        <!-- 3. 인쇄면 -->
                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select class="inline-select" name="POtype" id="POtype" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                            </select>
                            <span class="inline-note">단면 또는 양면 인쇄</span>
                        </div>

                        <!-- 4. 후가공 (기존 재질) -->
                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">후가공</label>
                            <select class="inline-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">후가공 방식을 선택하세요</span>
                        </div>

                        <!-- 5. 편집비용 -->
                        <div class="inline-form-row">
                            <label class="inline-label" for="ordertype">편집비</label>
                            <select class="inline-select" name="ordertype" id="ordertype" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                            </select>
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>

                    <!-- 상품권 프리미엄 옵션 섹션 (명함 구조 적용) -->
                    <div class="namecard-premium-options-section" id="premiumOptionsSection" style="margin-top: 15px;">
                        <!-- 한 줄 체크박스 헤더 -->
                        <div class="option-headers-row">
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="foil_enabled" name="foil_enabled" class="option-toggle" value="1">
                                <label for="foil_enabled" class="toggle-label">박</label>
                            </div>
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="numbering_enabled" name="numbering_enabled" class="option-toggle" value="1">
                                <label for="numbering_enabled" class="toggle-label">넘버링</label>
                            </div>
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="perforation_enabled" name="perforation_enabled" class="option-toggle" value="1">
                                <label for="perforation_enabled" class="toggle-label">미싱</label>
                            </div>
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="rounding_enabled" name="rounding_enabled" class="option-toggle" value="1">
                                <label for="rounding_enabled" class="toggle-label">귀돌이</label>
                            </div>
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="creasing_enabled" name="creasing_enabled" class="option-toggle" value="1">
                                <label for="creasing_enabled" class="toggle-label">오시</label>
                            </div>
                            <div class="option-price-display">
                                <span class="option-price-total" id="premiumPriceTotal">(+0원)</label>
                            </div>
                        </div>
                        <!-- 박 옵션 상세 -->
                        <div class="option-details" id="foil_options" style="display: none;">
                            <select name="foil_type" id="foil_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="gold_matte">금박무광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                                <option value="gold_gloss">금박유광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                                <option value="silver_matte">은박무광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                                <option value="silver_gloss">은박유광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                                <option value="blue_gloss">청박유광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                                <option value="red_gloss">적박유광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                                <option value="green_gloss">녹박유광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                                <option value="black_gloss">먹박유광 (500매 이하 30,000원, 초과시 매수×60원)</option>
                            </select>
                            <div class="option-note" style="font-size: 11px; color: #666; margin-top: 4px;">* 박(20mm×20mm 이하)</div>
                        </div>

                        <!-- 넘버링 옵션 상세 -->
                        <div class="option-details" id="numbering_options" style="display: none;">
                            <select name="numbering_type" id="numbering_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="single">1개 (500매 이하 60,000원, 초과시 매수×120원)</option>
                                <option value="double">2개 (500매 이하 60,000원 + 1000매당 15,000원, 초과시 매수×120원)</option>
                            </select>
                            <div class="option-note" style="font-size: 11px; color: #666; margin-top: 4px;">* 넘버링(1~9999)</div>
                        </div>

                        <!-- 미싱 옵션 상세 -->
                        <div class="option-details" id="perforation_options" style="display: none;">
                            <select name="perforation_type" id="perforation_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="horizontal">가로미싱 (500매 이하 20,000원, 초과시 매수×40원)</option>
                                <option value="vertical">세로미싱 (500매 이하 20,000원, 초과시 매수×40원)</option>
                                <option value="cross">십자미싱 (500매 이하 30,000원, 초과시 매수×60원)</option>
                            </select>
                            <div class="option-note" style="font-size: 11px; color: #666; margin-top: 4px;">* 미싱선 1줄 기준</div>
                        </div>

                        <!-- 귀돌이 옵션 상세 -->
                        <div class="option-details" id="rounding_options" style="display: none;">
                            <select name="rounding_type" id="rounding_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="4corners">네귀돌이 (500매 이하 15,000원, 초과시 매수×30원)</option>
                                <option value="2corners">두귀돌이 (500매 이하 12,000원, 초과시 매수×25원)</option>
                            </select>
                            <div class="option-note" style="font-size: 11px; color: #666; margin-top: 4px;">* R값 3mm 기준</div>
                        </div>

                        <!-- 오시 옵션 상세 -->
                        <div class="option-details" id="creasing_options" style="display: none;">
                            <select name="creasing_type" id="creasing_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="single_crease">1줄 오시 (500매 이하 18,000원, 초과시 매수×35원)</option>
                                <option value="double_crease">2줄 오시 (500매 이하 25,000원, 초과시 매수×50원)</option>
                            </select>
                            <div class="option-note" style="font-size: 11px; color: #666; margin-top: 4px;">* 접는 선 가공</div>
                        </div>

                        <!-- 숨겨진 가격 필드들 -->
                        <input type="hidden" name="foil_price" id="foil_price" value="0">
                        <input type="hidden" name="numbering_price" id="numbering_price" value="0">
                        <input type="hidden" name="perforation_price" id="perforation_price" value="0">
                        <input type="hidden" name="rounding_price" id="rounding_price" value="0">
                        <input type="hidden" name="creasing_price" id="creasing_price" value="0">
                        <input type="hidden" name="premium_options_total" id="premium_options_total" value="0">
                    </div>

                    <!-- 통일된 가격 표시 시스템 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            <span>모든 옵션을 선택하면 자동으로 계산됩니다</label>
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 - 프리미엄 스타일 -->
                    <?php if ($isQuotationMode || $isAdminQuoteMode): ?>
                    <!-- 견적서 모달 모드: 견적서에 적용 버튼 -->
                    <div class="quotation-apply-button">
                        <button type="button" class="btn-quotation-apply" onclick="applyToQuotation()">
                            견적서에 적용
                        </button>
                    </div>
                    <?php else: ?>
                    <!-- 일반 모드: 파일 업로드 및 주문하기 / 견적 요청 버튼 -->
                    <div class="action-buttons" id="actionButtons">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            파일 업로드 및 주문하기
                        </button>
                    </div>
                    <?php endif; ?>

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

    <?php
    // 상품권 모달 설정
    $modalProductName = '상품권';
    $modalProductIcon = '';
    include '../../includes/upload_modal.php';
    ?>

    <!-- 상품권 통합 갤러리 모달 색상 설정 -->
    

    <?php
    // 갤러리 에셋 자동 포함
    if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
        if (function_exists("include_gallery_assets")) { include_gallery_assets(); }
    }
    ?>
    
    <!-- 상품권 브랜드 컬러 적용 (핑크 계열) -->
    

    <?php
    // 갤러리 모달과 JavaScript는 if (function_exists("include_product_gallery")) { include_product_gallery()에서 자동 포함됨
    ?>

    <?php include "../../includes/login_modal.php"; ?>

    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <!-- 상품권/쿠폰 상세 설명 섹션 (하단 설명방법) -->
    <div class="ticket-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_ticket.php"; ?>
    </div>
    <?php endif; ?>

    <?php
    // 공통 푸터 포함 (견적서 모달에서는 제외)
    if (!$isQuotationMode && !$isAdminQuoteMode) {
        include "../../includes/footer.php";
    }
    ?>

    <!-- 상품권/쿠폰 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    

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

        // 공통 모달 JavaScript 로드
        const modalScript = document.createElement('script');
        modalScript.src = '../../includes/upload_modal.js';
        modalScript.onload = function() {
            // 로그인 체크 건너뛰기 (다른 제품과 동일)
            window.isLoggedIn = function() { return true; };
            window.checkLoginStatus = function() { return true; };
        };
        document.head.appendChild(modalScript);

        // 상품권 전용 장바구니 추가 함수
        function handleModalBasketAdd(uploadedFiles, onSuccess, onError) {
            console.log('상품권 handleModalBasketAdd 호출');

            try {
                // merchandisebond.js의 실제 구현 함수 직접 호출
                if (typeof addToBasketFromModalDirect === 'function') {
                    addToBasketFromModalDirect(onSuccess, onError);
                } else {
                    console.error('상품권: addToBasketFromModalDirect 함수를 찾을 수 없습니다.');
                    if (typeof onError === 'function') {
                        onError('상품권 장바구니 함수를 찾을 수 없습니다.');
                    } else {
                        alert('죄송합니다. 잠시 후 다시 시도해주세요.');
                    }
                }
            } catch (error) {
                console.error('상품권 장바구니 추가 오류:', error);
                if (typeof onError === 'function') {
                    onError(error.message || '장바구니 저장 중 오류가 발생했습니다.');
                } else {
                    alert('장바구니 저장 중 오류가 발생했습니다.');
                }
            }
        }

        // Phase 5: 견적 요청 함수
        window.addToQuotation = function() {
            console.log('견적 요청 시작 - 상품권');

            if (!window.currentPriceData || !window.currentPriceData.total_price) {
                alert('가격을 먼저 계산해주세요.');
                return;
            }

            const formData = new FormData();
            formData.append('product_type', 'merchandisebond');
            formData.append('MY_type', document.getElementById('MY_type').value);
            formData.append('Section', document.getElementById('Section').value);
            formData.append('POtype', document.getElementById('POtype').value);
            formData.append('MY_amount', document.getElementById('MY_amount').value);
            formData.append('ordertype', document.getElementById('ordertype').value);
            formData.append('price', Math.round(window.currentPriceData.total_price));
            formData.append('vat_price', Math.round(window.currentPriceData.vat_price));

            fetch('../quote/add_to_quotation_temp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('견적서에 추가되었습니다.');
                    window.location.href = '/mlangprintauto/quote/';
                } else {
                    alert('오류: ' + (data.message || '견적 추가 실패'));
                }
            })
            .catch(error => {
                console.error('네트워크 오류:', error);
                alert('네트워크 오류가 발생했습니다.');
            });
        };

        // merchandisebond.js에서 전역 변수와 초기화 함수들을 처리
        // 고급 갤러리 시스템 자동 로드

        // 갤러리 모달 제어 함수들 (페이지네이션 지원)
        let merchandiseBondCurrentPage = 1;
        let merchandiseBondTotalPages = 1;

        // 통일된 팝업 열기 함수 (전단지와 동일한 시스템)
        // 공통 갤러리 팝업 함수 사용 (common-gallery-popup.js)
        const openProofPopup = window.openGalleryPopup;

        // 독립 모달 함수들 제거됨 - 통합 갤러리 시스템 사용

        // 독립 갤러리 함수들 제거됨 - 통합 갤러리 시스템에서 모든 기능 처리
    </script>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js?v=<?php echo time(); ?>"></script>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
    <!-- 테마 스위처 -->
    <?php ThemeLoader::renderSwitcher('bottom-right'); ?>
    <?php ThemeLoader::renderSwitcherJS(); ?>


<?php if ($isAdminQuoteMode): ?>
    <!-- 관리자 견적서 모드: postMessage로 부모 창에 데이터 전송 -->
    <script>
    window.applyToQuotation = function() {
        console.log('🚀 [관리자 견적서-상품권] applyToQuotation() 호출');

        // 실제 필드: MY_type, Section, MY_amount
        const MY_type = document.getElementById('MY_type')?.value;
        const Section = document.getElementById('Section')?.value;
        const MY_amount = document.getElementById('MY_amount')?.value;

        if (!MY_type || !Section || !MY_amount) {
            alert('모든 필수 옵션을 선택해주세요.');
            return;
        }

        // 가격 확인 (window.currentPriceData 사용)
        if (!window.currentPriceData || !window.currentPriceData.total_price) {
            alert('가격을 먼저 계산해주세요.');
            return;
        }
        const supplyPrice = Math.round(window.currentPriceData.total_price) || 0;

        if (supplyPrice <= 0) {
            alert('가격을 먼저 계산해주세요.');
            return;
        }

        const typeSelect = document.getElementById('MY_type');
        const sectionSelect = document.getElementById('Section');
        const amountSelect = document.getElementById('MY_amount');

        const paperType = typeSelect?.selectedOptions[0]?.text || MY_type;
        const paperSection = sectionSelect?.selectedOptions[0]?.text || Section;
        const quantityText = amountSelect?.selectedOptions[0]?.text || MY_amount;

        // 2줄 형식: 종류 / 재질(규격)
        const line1 = paperType;
        const line2 = paperSection;
        const specification = `${line1}\n${line2}`;
        const quantity = parseFloat(MY_amount) || 1;

        const payload = {
            product_type: 'merchandisebond',
            product_name: '상품권',
            specification: specification,
            quantity: quantity,
            unit: '매',
            quantity_display: quantityText,
            unit_price: quantity > 0 ? Math.round(supplyPrice / quantity) : 0,
            supply_price: supplyPrice,
            MY_type: MY_type, Section: Section, MY_amount: MY_amount,
            st_price: supplyPrice,
            st_price_vat: Math.round(supplyPrice * 1.1)
        };

        console.log('📤 [관리자 견적서-상품권] postMessage 전송:', payload);
        window.parent.postMessage({ type: 'ADMIN_QUOTE_ITEM_ADDED', payload: payload }, window.location.origin);
    };
    console.log('✅ [관리자 견적서-상품권] applyToQuotation() 정의 완료');
    </script>
<?php endif; ?>
</body>
</html>