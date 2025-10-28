<?php
/**
 * 명함 견적안내 컴팩트 시스템 - 프리미엄 옵션 개발 버전
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산 + 프리미엄 옵션
 * Development Version: index_01.php
 * Created: 2025년 1월 (Premium Options Development)
 */

// 보안 상수 정의 후 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
if (function_exists("init_gallery_system")) { init_gallery_system("namecard"); }

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("명함 견적안내 컴팩트 - 프리미엄 옵션");

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 명함 종류 가져오기 (일반명함(쿠폰) 우선)
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate
               WHERE Ttable='NameCard' AND BigNo='0'
               ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];

    // 해당 명함 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate
                      WHERE Ttable='NameCard' AND BigNo='" . $type_row['no'] . "'
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];

        // 해당 조합의 기본 수량 가져오기 (500매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_namecard
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

    
    

    <!-- 🎯 통합 컬러 시스템 (최우선 로드) -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">

    <!-- 명함 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/product-layout.css">

    <!-- 🎨 브랜드 디자인 시스템 CSS -->
    <link rel="stylesheet" href="../../css/brand-design-system.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 🆕 Duson 통합 갤러리 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통일 인라인 폼 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">
    <link rel="stylesheet" href="../../css/unified-price-display.css">

    <!-- 🆕 프리미엄 옵션 CSS 추가 -->
    <link rel="stylesheet" href="../../css/additional-options.css">


    <!-- 공통 가격 표시 시스템 -->
    <script src="../../js/common-price-display.js" defer></script>
    <!-- 명함 전용 JavaScript -->
    <!-- <script src="../../js/namecard.js" defer></script> 🔥 프리미엄 옵션과 충돌하므로 비활성화 -->

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
    <!-- 공통 갤러리 팝업 함수 -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 명함 전용 스타일 (공통 스타일보다 먼저 로드) -->
    <link rel="stylesheet" href="../../css/namecard-inline-styles.css">

    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
</head>
<body class="namecard-page">
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>

    <div class="product-container">
        <div class="page-title">
            <h1>💳 명함 견적 안내</h1>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 -->
        <div class="product-content">
            <!-- 좌측: 갤러리 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="명함 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'namecard';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </section>

            <!-- 우측: 계산기 -->
            <aside class="product-calculator">
                <div class="calculator-header">
                    <h3>💰견적 안내</h3>
                </div>

                <form id="namecardForm">
                    <!-- 통일 인라인 폼 시스템 - 명함 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">종류</label>
                            <select name="MY_type" id="MY_type" class="inline-select" required onchange="handleTypeChange(this.value)">
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "mlangprintauto_transactioncate", 'NameCard');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="inline-note">명함 종류를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">재질</label>
                            <select name="Section" id="Section" class="inline-select" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>" onchange="handleSectionChange(this.value)">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 용지를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select name="POtype" id="POtype" class="inline-select" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                            </select>
                            <span class="inline-note">단면 또는 양면 인쇄</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_amount">수량</label>
                            <select name="MY_amount" id="MY_amount" class="inline-select" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 재질을 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하시는 수량을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="ordertype">편집비</label>
                            <select name="ordertype" id="ordertype" class="inline-select" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                            </select>
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>

                    <!-- 🆕 명함 프리미엄 옵션 섹션 -->
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
                                <span class="option-price-total" id="premiumPriceTotal">(+0원)</span>
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
                        </div>

                        <!-- 미싱(절취선) 옵션 상세 -->
                        <div class="option-details" id="perforation_options" style="display: none;">
                            <select name="perforation_type" id="perforation_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="single">1개 (500매 이하 20,000원, 초과시 매수×25원)</option>
                                <option value="double">2개 (500매 이하 20,000원 + 1000매당 15,000원, 초과시 매수×25원)</option>
                            </select>
                        </div>

                        <!-- 귀돌이는 단일 옵션이므로 셀렉트 없음 -->

                        <!-- 오시 옵션 상세 -->
                        <div class="option-details" id="creasing_options" style="display: none;">
                            <select name="creasing_type" id="creasing_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="1line">1줄 (500매 이하 20,000원, 초과시 매수×25원)</option>
                                <option value="2line">2줄 (500매 이하 20,000원, 초과시 매수×25원)</option>
                                <option value="3line">3줄 (500매 이하 20,000원 + 1000매당 15,000원, 초과시 매수×25원)</option>
                            </select>
                        </div>

                        <!-- 숨겨진 필드들 -->
                        <input type="hidden" name="foil_price" id="foil_price" value="0">
                        <input type="hidden" name="numbering_price" id="numbering_price" value="0">
                        <input type="hidden" name="perforation_price" id="perforation_price" value="0">
                        <input type="hidden" name="rounding_price" id="rounding_price" value="0">
                        <input type="hidden" name="creasing_price" id="creasing_price" value="0">
                        <input type="hidden" name="premium_options_total" id="premium_options_total" value="0">
                    </div>

                    <!-- 기본 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 - 프리미엄 스타일 -->
                    <div class="upload-order-button" id="uploadOrderButton">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            파일 업로드 및 주문하기
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
            </aside>
        </div>
    </div>

    <?php
    // 공통 업로드 모달 설정 (통일된 명명 규칙)
    $modalProductName = '명함';
    $modalProductIcon = '🃏';

    // 공통 업로드 모달 포함
    include "../../includes/upload_modal.php";
    ?>

    <!-- 통합 갤러리 모달은 include_product_gallery()에서 자동 포함됨 -->

    <?php include "../../includes/login_modal.php"; ?>

    <!-- 명함 상세 설명 섹션 (1200px 폭) - 하단 설명방법 적용 -->
    <div class="namecard-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_namecard.php"; ?>
    </div>

    <?php
    // 갤러리 모달과 JavaScript는 if (function_exists("include_product_gallery")) { include_product_gallery()에서 자동 포함됨
    ?>

    <?php include "../../includes/footer.php"; ?>

    <!-- 명함 전용 스크립트만 유지 (계산 로직 절대 건드리지 않음) -->

    <!-- 명함 전용 스크립트 -->
    <script src="js/namecard-compact.js"></script>

    <!-- 🆕 프리미엄 옵션 JavaScript 추가 -->
    <script src="js/namecard-premium-options.js"></script>

    <!-- 공통 업로드 모달 JavaScript -->
    <script src="../../includes/upload_modal.js"></script>

    <!-- 🆕 Duson 갤러리 시스템 JavaScript -->
    <script src="../../duson/js/gallery-system.js" defer></script>

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

        // 종류 변경 시 재질 옵션 업데이트
        function handleTypeChange(typeValue) {
            console.log('명함 종류 변경:', typeValue);

            const sectionSelect = document.getElementById('Section');
            const amountSelect = document.getElementById('MY_amount');

            // 하위 드롭다운들 초기화
            sectionSelect.innerHTML = '<option value="">로딩중...</option>';
            amountSelect.innerHTML = '<option value="">먼저 재질을 선택해주세요</option>';
            resetPriceDisplay();

            if (!typeValue) {
                sectionSelect.innerHTML = '<option value="">먼저 종류를 선택해주세요</option>';
                return;
            }

            // 재질 옵션 가져오기
            fetch(`/mlangprintauto/namecard/get_paper_types.php?style=${typeValue}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        sectionSelect.innerHTML = '<option value="">재질을 선택해주세요</option>';
                        data.data.forEach(option => {
                            sectionSelect.innerHTML += `<option value="${option.no}">${option.title}</option>`;
                        });

                        // 첫 번째 옵션 자동 선택
                        if (data.data.length > 0) {
                            sectionSelect.value = data.data[0].no;
                            // 재질 변경 이벤트 트리거
                            handleSectionChange(data.data[0].no);
                        }
                    } else {
                        sectionSelect.innerHTML = '<option value="">재질 로드 실패</option>';
                    }
                })
                .catch(error => {
                    console.error('재질 로드 오류:', error);
                    sectionSelect.innerHTML = '<option value="">재질 로드 실패</option>';
                });
        }

        // 재질 변경 시 수량 옵션 업데이트
        function handleSectionChange(sectionValue) {
            console.log('명함 재질 변경:', sectionValue);

            const typeValue = document.getElementById('MY_type').value;
            const amountSelect = document.getElementById('MY_amount');

            amountSelect.innerHTML = '<option value="">로딩중...</option>';
            resetPriceDisplay();

            if (!sectionValue || !typeValue) {
                amountSelect.innerHTML = '<option value="">먼저 재질을 선택해주세요</option>';
                return;
            }

            // 수량 옵션 가져오기 (기본적으로 단면으로 설정)
            const potypeValue = document.getElementById('POtype').value || '1';
            fetch(`/mlangprintauto/namecard/get_quantities.php?style=${typeValue}&section=${sectionValue}&potype=${potypeValue}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        amountSelect.innerHTML = '<option value="">수량을 선택해주세요</option>';
                        data.data.forEach(option => {
                            amountSelect.innerHTML += `<option value="${option.value}">${option.text}</option>`;
                        });

                        // 첫 번째 수량 옵션 자동 선택
                        if (data.data.length > 0) {
                            amountSelect.value = data.data[0].value;
                            // 가격 계산 트리거
                            calculatePrice();
                        }
                    } else {
                        amountSelect.innerHTML = '<option value="">수량 로드 실패</option>';
                    }
                })
                .catch(error => {
                    console.error('수량 로드 오류:', error);
                    amountSelect.innerHTML = '<option value="">수량 로드 실패</option>';
                });
        }

        // 🆕 확장된 가격 계산 함수 (프리미엄 옵션 포함)
        function calculatePrice() {
            const typeValue = document.getElementById('MY_type').value;
            const sectionValue = document.getElementById('Section').value;
            const potypeValue = document.getElementById('POtype').value;
            const amountValue = document.getElementById('MY_amount').value;
            const ordertypeValue = document.getElementById('ordertype').value;

            console.log('가격 계산 요청:', {typeValue, sectionValue, potypeValue, amountValue, ordertypeValue});

            // 모든 필드가 선택되었는지 확인
            if (!typeValue || !sectionValue || !potypeValue || !amountValue || !ordertypeValue) {
                resetPriceDisplay();
                return;
            }

            // 기본 가격 계산 AJAX 호출
            const params = new URLSearchParams({
                MY_type: typeValue,
                Section: sectionValue,
                POtype: potypeValue,
                MY_amount: amountValue,
                ordertype: ordertypeValue
            });

            fetch(`/mlangprintauto/namecard/calculate_price_ajax.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 기본 가격과 프리미엄 옵션 가격을 합산
                        const premiumTotal = calculatePremiumOptions();

                        // 올바른 가격 계산
                        const basePrice = data.data.base_price || 0;  // 공급가액 (기본 인쇄비)
                        const designPrice = data.data.design_price || 0;  // 디자인비
                        const totalSupplyPrice = basePrice + designPrice + premiumTotal;  // 총 공급가액
                        const finalTotalWithVat = Math.floor(totalSupplyPrice * 1.1);  // 부가세 포함 총액

                        console.log('💰 가격 계산 상세:', {
                            basePrice: basePrice,
                            designPrice: designPrice,
                            premiumTotal: premiumTotal,
                            totalSupplyPrice: totalSupplyPrice,
                            vatAmount: (finalTotalWithVat - totalSupplyPrice),
                            finalTotalWithVat: finalTotalWithVat
                        });

                        const totalData = {
                            ...data.data,
                            premium_options_total: premiumTotal,
                            total_supply_price: totalSupplyPrice,  // 공급가액 합계
                            final_total_with_vat: finalTotalWithVat  // 부가세 포함 최종 금액
                        };
                        updatePriceDisplay(totalData);
                        showUploadButton();
                    } else {
                        showPriceError(data.message || '가격 계산 실패');
                    }
                })
                .catch(error => {
                    console.error('가격 계산 오류:', error);
                    showPriceError('가격 계산 중 오류가 발생했습니다.');
                });
        }

        // 🆕 프리미엄 옵션 가격 계산
        function calculatePremiumOptions() {
            const quantity = parseInt(document.getElementById('MY_amount').value) || 500;
            let total = 0;

            console.log('🔧 프리미엄 옵션 계산 시작, 수량:', quantity);

            // 박 옵션 (500매 이하 30,000원, 초과시 매수×60원)
            const foilEnabled = document.getElementById('foil_enabled')?.checked;
            if (foilEnabled) {
                const price = calculateIndividualPrice('foil', quantity, 30000, 60);
                document.getElementById('foil_price').value = price;
                total += price;
                console.log('✨ 박 옵션 선택됨:', price + '원');
            } else {
                document.getElementById('foil_price').value = 0;
                console.log('❌ 박 옵션 선택 안됨');
            }

            // 넘버링 옵션 (500매 이하 60,000원, 2개는 1000매당 15,000원 추가, 초과시 매수×120원)
            const numberingEnabled = document.getElementById('numbering_enabled')?.checked;
            if (numberingEnabled) {
                const type = document.getElementById('numbering_type')?.value || 'single';
                let basePrice = 60000;

                if (type === 'double') {
                    // 2개인 경우: 기본 60,000원 + 1000매당 15,000원 추가
                    const thousandUnits = Math.ceil(quantity / 1000);
                    basePrice = 60000 + (thousandUnits * 15000);
                }

                const price = calculateIndividualPrice('numbering', quantity, basePrice, 120);
                document.getElementById('numbering_price').value = price;
                total += price;
                console.log('🔢 넘버링 옵션 선택됨:', price + '원');
            } else {
                document.getElementById('numbering_price').value = 0;
                console.log('❌ 넘버링 옵션 선택 안됨');
            }

            // 미싱 옵션 (500매 이하 20,000원, 2개는 1000매당 15,000원 추가, 초과시 매수×25원)
            if (document.getElementById('perforation_enabled')?.checked) {
                const type = document.getElementById('perforation_type')?.value || 'single';
                let basePrice = 20000;

                if (type === 'double') {
                    // 2개인 경우: 기본 20,000원 + 1000매당 15,000원 추가
                    const thousandUnits = Math.ceil(quantity / 1000);
                    basePrice = 20000 + (thousandUnits * 15000);
                }

                const price = calculateIndividualPrice('perforation', quantity, basePrice, 25);
                document.getElementById('perforation_price').value = price;
                total += price;
            } else {
                document.getElementById('perforation_price').value = 0;
            }

            // 귀돌이 옵션 (500매 이하 10,000원, 초과시 매수×24원)
            if (document.getElementById('rounding_enabled')?.checked) {
                const price = calculateIndividualPrice('rounding', quantity, 10000, 24);
                document.getElementById('rounding_price').value = price;
                total += price;
            } else {
                document.getElementById('rounding_price').value = 0;
            }

            // 오시 옵션 (500매 이하 20,000원, 3줄은 1000매당 15,000원 추가, 초과시 매수×25원)
            if (document.getElementById('creasing_enabled')?.checked) {
                const type = document.getElementById('creasing_type')?.value || '1line';
                let basePrice = 20000;

                if (type === '3line') {
                    // 3줄인 경우: 기본 20,000원 + 1000매당 15,000원 추가
                    const thousandUnits = Math.ceil(quantity / 1000);
                    basePrice = 20000 + (thousandUnits * 15000);
                }

                const price = calculateIndividualPrice('creasing', quantity, basePrice, 25);
                document.getElementById('creasing_price').value = price;
                total += price;
            } else {
                document.getElementById('creasing_price').value = 0;
            }

            // 총 프리미엄 옵션 가격 저장
            document.getElementById('premium_options_total').value = total;

            console.log('🎯 프리미엄 옵션 총액:', total + '원');

            // UI 업데이트
            updatePremiumPriceDisplay(total);

            return total;
        }

        // 🆕 개별 옵션 가격 계산 헬퍼
        function calculateIndividualPrice(optionType, quantity, basePrice500, pricePerUnit) {
            if (quantity <= 500) {
                return basePrice500;
            } else {
                const additionalUnits = quantity - 500;
                return basePrice500 + (additionalUnits * pricePerUnit);
            }
        }

        // 🆕 프리미엄 옵션 가격 표시 업데이트
        function updatePremiumPriceDisplay(total) {
            const premiumPriceElement = document.getElementById('premiumPriceTotal');
            if (premiumPriceElement) {
                if (total > 0) {
                    premiumPriceElement.textContent = `(+${total.toLocaleString()}원)`;
                    premiumPriceElement.style.color = '#d4af37';
                } else {
                    premiumPriceElement.textContent = '(+0원)';
                    premiumPriceElement.style.color = '#718096';
                }
            }
        }

        // 🆕 확장된 가격 표시 업데이트 (프리미엄 옵션 포함)
        function updatePriceDisplay(priceData) {
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');
            const priceDisplay = document.getElementById('priceDisplay');

            // 상단 녹색 가격을 공급가액으로 표시
            priceAmount.textContent = priceData.total_supply_price.toLocaleString() + '원';

            // 최종 가격 (부가세 포함)
            const finalTotal = priceData.final_total_with_vat;

            // VAT 정확히 계산 (Math.floor 사용하여 일관성 유지)
            const vatAmount = finalTotal - priceData.total_supply_price;

            console.log('💳 VAT 계산 확인:', {
                finalTotal: finalTotal,
                totalSupplyPrice: priceData.total_supply_price,
                vatAmount: vatAmount,
                premiumOptionsTotal: priceData.premium_options_total
            });

            let detailsHtml = `
                <div class="price-breakdown">
                    <div class="price-item">
                        <span class="price-item-label">인쇄비:</span>
                        <span class="price-item-value">${priceData.base_price.toLocaleString()}원</span>
                    </div>
            `;

            if (priceData.design_price > 0) {
                detailsHtml += `
                    <div class="price-item">
                        <span class="price-item-label">디자인비:</span>
                        <span class="price-item-value">${priceData.design_price.toLocaleString()}원</span>
                    </div>
                `;
            }

            if (priceData.premium_options_total > 0) {
                detailsHtml += `
                    <div class="price-item premium-options">
                        <span class="price-item-label">프리미엄 옵션:</span>
                        <span class="price-item-value">${priceData.premium_options_total.toLocaleString()}원</span>
                    </div>
                `;
            }

            detailsHtml += `
                    <div class="price-item final">
                        <span class="price-item-label">부가세 포함:</span>
                        <span class="price-item-value" style="color: #28a745; font-size: 0.98rem; font-weight: 700;">${finalTotal.toLocaleString()}원</span>
                    </div>
                </div>
            `;

            priceDetails.innerHTML = detailsHtml;
            priceDisplay.classList.add('calculated');

            // 현재 가격 데이터 저장
            window.currentPriceData = priceData;
        }

        // 가격 표시 초기화
        function resetPriceDisplay() {
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');
            const priceDisplay = document.getElementById('priceDisplay');
            const uploadButton = document.getElementById('uploadOrderButton');

            priceAmount.textContent = '견적 계산 필요';
            priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
            priceDisplay.classList.remove('calculated');
            uploadButton.style.display = 'none';

            // 프리미엄 옵션 가격 초기화
            updatePremiumPriceDisplay(0);

            window.currentPriceData = null;
        }

        // 가격 계산 오류 표시
        function showPriceError(message) {
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');

            priceAmount.textContent = '계산 오류';
            priceDetails.textContent = message;
        }

        // 업로드 버튼 표시
        function showUploadButton() {
            const uploadButton = document.getElementById('uploadOrderButton');
            uploadButton.style.display = 'block';
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('명함 프리미엄 옵션 페이지 초기화 완료');

            // 첫 번째 종류 옵션 자동 선택
            setTimeout(function() {
                const typeSelect = document.getElementById('MY_type');
                if (typeSelect && typeSelect.options.length > 1) {
                    // "선택해주세요" 다음의 첫 번째 옵션 선택
                    typeSelect.selectedIndex = 1;
                    const firstValue = typeSelect.value;
                    if (firstValue) {
                        console.log('첫 번째 종류 자동 선택:', firstValue);
                        handleTypeChange(firstValue);
                    }
                }

                // 기본값이 설정되어 있으면 첫 화면에서 자동 계산 실행
                if (typeof autoCalculatePrice === 'function') {
                    autoCalculatePrice();
                    console.log('명함: 첫 화면 자동 계산 실행');
                }

                // 🆕 프리미엄 옵션 이벤트 리스너 초기화
                initializePremiumOptionsListeners();
            }, 500); // namecard.js 로드 대기
        });

        // 🆕 프리미엄 옵션 이벤트 리스너 초기화
        function initializePremiumOptionsListeners() {
            console.log('프리미엄 옵션 이벤트 리스너 초기화');

            // 체크박스 토글 이벤트
            const toggles = document.querySelectorAll('.option-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('change', function(e) {
                    const optionType = e.target.id.replace('_enabled', '');
                    const detailsDiv = document.getElementById(`${optionType}_options`);

                    if (e.target.checked) {
                        detailsDiv.style.display = 'block';
                        console.log(`✅ ${optionType} 옵션 활성화`);
                    } else {
                        detailsDiv.style.display = 'none';
                        // 가격 필드 초기화
                        const priceField = document.getElementById(`${optionType}_price`);
                        if (priceField) priceField.value = '0';
                        console.log(`❌ ${optionType} 옵션 비활성화`);
                    }

                    // 가격 재계산
                    calculatePrice();
                });
            });

            // 옵션 선택 변경 이벤트
            const selects = document.querySelectorAll('.option-details select');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    console.log('프리미엄 옵션 선택 변경:', select.name, select.value);
                    calculatePrice();
                });
            });
        }

        // 🆕 공통 업로드 모달에서 사용할 장바구니 추가 함수
        window.handleModalBasketAdd = function(onSuccess, onError) {
            console.log('명함 장바구니 추가 시작');

            // 🔧 장바구니 추가 전에 프리미엄 옵션 재계산
            const premiumTotal = calculatePremiumOptions();
            console.log('💰 재계산된 프리미엄 옵션 총액:', premiumTotal);

            // 기본 폼 데이터 수집
            const formData = new FormData();
            formData.append('action', 'add_to_basket');
            formData.append('product_type', 'namecard');
            formData.append('MY_type', document.getElementById('MY_type').value);
            formData.append('Section', document.getElementById('Section').value);
            formData.append('POtype', document.getElementById('POtype').value);
            formData.append('MY_amount', document.getElementById('MY_amount').value);
            formData.append('ordertype', document.getElementById('ordertype').value);

            // 가격 정보 추가 (프리미엄 옵션 포함)
            if (window.currentPriceData) {
                // 공급가액 합계 (인쇄비 + 디자인비 + 프리미엄 옵션)
                const totalSupplyPrice = window.currentPriceData.total_supply_price || window.currentPriceData.base_price;
                formData.append('price', totalSupplyPrice);

                // 부가세 포함 최종 금액
                const finalTotalWithVat = window.currentPriceData.final_total_with_vat || window.currentPriceData.total_with_vat;
                formData.append('vat_price', finalTotalWithVat);
            }

            // 🆕 프리미엄 옵션 데이터 추가
            formData.append('foil_enabled', document.getElementById('foil_enabled')?.checked ? 1 : 0);
            formData.append('foil_type', document.getElementById('foil_type')?.value || '');
            formData.append('foil_price', document.getElementById('foil_price')?.value || 0);

            formData.append('numbering_enabled', document.getElementById('numbering_enabled')?.checked ? 1 : 0);
            formData.append('numbering_type', document.getElementById('numbering_type')?.value || '');
            formData.append('numbering_price', document.getElementById('numbering_price')?.value || 0);

            formData.append('perforation_enabled', document.getElementById('perforation_enabled')?.checked ? 1 : 0);
            formData.append('perforation_type', document.getElementById('perforation_type')?.value || '');
            formData.append('perforation_price', document.getElementById('perforation_price')?.value || 0);

            formData.append('rounding_enabled', document.getElementById('rounding_enabled')?.checked ? 1 : 0);
            formData.append('rounding_price', document.getElementById('rounding_price')?.value || 0);

            formData.append('creasing_enabled', document.getElementById('creasing_enabled')?.checked ? 1 : 0);
            formData.append('creasing_type', document.getElementById('creasing_type')?.value || '');
            formData.append('creasing_price', document.getElementById('creasing_price')?.value || 0);

            formData.append('premium_options_total', document.getElementById('premium_options_total')?.value || 0);

            // 작업메모 추가 (모달에서)
            const workMemo = document.getElementById('modalWorkMemo');
            if (workMemo) {
                formData.append('work_memo', workMemo.value);
            }

            // 업로드 방법 추가
            formData.append('upload_method', window.selectedUploadMethod || 'upload');

            // 업로드된 파일들 추가
            if (window.uploadedFiles && window.uploadedFiles.length > 0) {
                window.uploadedFiles.forEach((file, index) => {
                    formData.append(`uploaded_files[${index}]`, file);
                });
            }

            console.log('전송할 데이터:', {
                basic: {
                    MY_type: formData.get('MY_type'),
                    Section: formData.get('Section'),
                    POtype: formData.get('POtype'),
                    MY_amount: formData.get('MY_amount'),
                    ordertype: formData.get('ordertype')
                },
                premium: {
                    foil_enabled: formData.get('foil_enabled'),
                    foil_price: formData.get('foil_price'),
                    numbering_enabled: formData.get('numbering_enabled'),
                    numbering_price: formData.get('numbering_price'),
                    perforation_enabled: formData.get('perforation_enabled'),
                    perforation_price: formData.get('perforation_price'),
                    rounding_enabled: formData.get('rounding_enabled'),
                    rounding_price: formData.get('rounding_price'),
                    creasing_enabled: formData.get('creasing_enabled'),
                    creasing_price: formData.get('creasing_price'),
                    premium_total: formData.get('premium_options_total')
                }
            });

            // AJAX 전송
            fetch('add_to_basket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('서버 응답:', data);
                if (data.success) {
                    if (onSuccess) onSuccess(data);
                } else {
                    if (onError) onError(data.message);
                }
            })
            .catch(error => {
                console.error('네트워크 오류:', error);
                if (onError) onError('네트워크 오류가 발생했습니다.');
            });
        };

        // 🆕 namecard.js 대체 필수 기능들

        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            initializeCalculator();
            initializePremiumOptions();
        });

        function initializeCalculator() {
            const typeSelect = document.getElementById('MY_type');

            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    loadPaperTypes(this.value);
                });

                // 기본값이 설정되어 있으면 자동으로 하위 옵션들 로드
                if (typeSelect.value) {
                    loadPaperTypes(typeSelect.value);
                }
            }
        }

        // 용지 타입 로드
        function loadPaperTypes(typeValue) {
            const sectionSelect = document.getElementById('Section');
            if (!sectionSelect || !typeValue) return;

            fetch(`get_paper_types.php?type=${typeValue}`)
                .then(response => response.json())
                .then(data => {
                    sectionSelect.innerHTML = '<option value="">선택해주세요</option>';
                    if (data.success) {
                        data.data.forEach(item => {
                            const option = new Option(item.text, item.value);
                            sectionSelect.appendChild(option);
                        });

                        // 기본값 설정
                        const defaultValue = sectionSelect.getAttribute('data-default-value');
                        if (defaultValue) {
                            sectionSelect.value = defaultValue;
                            loadQuantities(typeValue, defaultValue);
                        }
                    }
                })
                .catch(error => {
                    console.error('용지 타입 로드 오류:', error);
                });
        }

        // 수량 로드 (기존 함수와 동일)
        function loadQuantities(typeValue, sectionValue) {
            const amountSelect = document.getElementById('MY_amount');
            if (!amountSelect || !typeValue || !sectionValue) return;

            fetch(`get_quantities.php?type=${typeValue}&section=${sectionValue}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        amountSelect.innerHTML = '<option value="">선택해주세요</option>';
                        data.data.forEach(item => {
                            const option = new Option(item.text, item.value);
                            amountSelect.appendChild(option);
                        });

                        // 기본값 설정
                        const defaultValue = amountSelect.getAttribute('data-default-value');
                        if (defaultValue) {
                            amountSelect.value = defaultValue;
                            calculatePrice();
                        }
                    }
                })
                .catch(error => {
                    console.error('수량 로드 오류:', error);
                });
        }

        // Section 변경 이벤트
        document.addEventListener('DOMContentLoaded', function() {
            const sectionSelect = document.getElementById('Section');
            if (sectionSelect) {
                sectionSelect.addEventListener('change', function() {
                    const typeValue = document.getElementById('MY_type').value;
                    if (typeValue && this.value) {
                        loadQuantities(typeValue, this.value);
                    }
                });
            }
        });
    </script>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>