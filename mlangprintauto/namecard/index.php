<?php
/**
 * 명함 견적안내 컴팩트 시스템 - 프리미엄 옵션 개발 버전
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산 + 프리미엄 옵션
 * Development Version: index_01.php
 * Created: 2025년 1월 (Premium Options Development)
 */

// 테마 시스템 로드
include_once __DIR__ . '/../../includes/theme_loader.php';

// 공통 인증 및 설정
include "../../includes/auth.php";

require_once __DIR__ . '/../../includes/mode_helper.php';

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 방문자 추적 시스템
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
if (function_exists("init_gallery_system")) { init_gallery_system("namecard"); }

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("명함 견적안내 컴팩트 - 프리미엄 옵션");

$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1',
    'MY_amount' => '',
    'ordertype' => 'print'
];

$url_nc_type = isset($_GET['type']) ? intval($_GET['type']) : 0;
$url_nc_section = isset($_GET['section']) ? intval($_GET['section']) : 0;

if ($url_nc_type) {
    $default_values['MY_type'] = $url_nc_type;
    if ($url_nc_section) {
        $default_values['Section'] = $url_nc_section;
    }
} else {
    $type_query = "SELECT no, title FROM mlangprintauto_transactioncate
                   WHERE Ttable='NameCard' AND BigNo='0'
                   ORDER BY CASE WHEN title LIKE '%일반명함%' THEN 1 ELSE 2 END, no ASC
                   LIMIT 1";
    $type_result = mysqli_query($db, $type_query);
    if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
        $default_values['MY_type'] = $type_row['no'];

        $section_query = "SELECT no, title FROM mlangprintauto_transactioncate
                          WHERE Ttable='NameCard' AND BigNo='" . $type_row['no'] . "'
                          ORDER BY no ASC LIMIT 1";
        $section_result = mysqli_query($db, $section_query);
        if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
            $default_values['Section'] = $section_row['no'];

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
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>명함 인쇄 | 명함 제작 - 두손기획인쇄</title>
    <meta name="description" content="명함 인쇄 전문 두손기획인쇄. 고급 명함, 양면 컬러, 다양한 용지 선택. 100매부터 빠른 제작. 실시간 견적 확인. 서울 영등포구.">
    <meta name="keywords" content="명함 인쇄, 명함 제작, 고급 명함, 양면 명함, 명함 가격, 명함 디자인">
    <link rel="canonical" href="https://dsp114.com/mlangprintauto/namecard/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="명함 인쇄 | 명함 제작 - 두손기획인쇄">
    <meta property="og:description" content="명함 인쇄 전문. 고급 명함, 양면 컬러, 다양한 용지. 100매부터 빠른 제작.">
    <meta property="og:url" content="https://dsp114.com/mlangprintauto/namecard/">
    <meta property="og:image" content="https://dsp114.com/ImgFolder/og-image.png">
    <meta property="og:site_name" content="두손기획인쇄">

    <!-- 🏆 Competition Edition: 테이블 디자인 시스템 (최우선 로드) -->
    <link rel="stylesheet" href="../../css/table-design-system.css">

    <!-- 🎯 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">

    <!-- 명함 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/product-layout.css?v=<?php echo filemtime(__DIR__ . '/../../css/product-layout.css'); ?>">

    <!-- 🎨 브랜드 디자인 시스템 CSS -->
    <link rel="stylesheet" href="../../css/brand-design-system.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 🆕 Duson 통합 갤러리 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통일 인라인 폼 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css?v=<?php echo filemtime(__DIR__ . '/../../css/unified-inline-form.css'); ?>">
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
    <link rel="stylesheet" href="../../css/upload-modal-common.css?v=<?php echo time(); ?>">


    <!-- 테마 시스템 CSS -->
    <?php ThemeLoader::renderCSS(); ?>

    <!-- Phase 5: 견적 요청 버튼 스타일 -->
    <style>
        /* .action-buttons, .btn-upload-order → common-styles.css SSOT 사용 */
        .btn-request-quote {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .btn-request-quote:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(240, 147, 251, 0.4);
        }

        /* 명함재질보기 버튼 */
        .btn-texture-view {
            display: inline-block;
            font-size: 0.55em;
            padding: 6px 12px;
            margin-left: 15px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 500;
            vertical-align: middle;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }
        .btn-texture-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        /* 인라인 폼 내 명함재질보기 버튼 */
        .btn-texture-inline {
            font-size: 12px;
            padding: 4px 10px;
            margin-left: 8px;
        }
        /* 모바일에서 제목 옆 버튼만 숨김 */
        @media (max-width: 768px) {
            .page-title .btn-texture-view {
                display: none;
            }
        }
    </style>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/product_schema.php'; echo_product_schema('namecard'); ?>
    <link rel="stylesheet" href="../../css/quote-gauge.css">
</head>
<body class="namecard-page<?php echo ($isQuotationMode || $isAdminQuoteMode) ? ' quotation-modal-mode' : ''; ?>" <?php ThemeLoader::renderBodyAttributes(); ?>>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/header-ui.php"; ?>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/nav.php"; ?>

    <div class="product-container">
        <div class="page-title">
            <h1>💳 명함 견적 안내
                <a href="#paper-texture-section" class="btn-texture-view" title="명함 재질 이미지 보기">📋 명함재질보기</a>
            </h1>
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
                    <h3>견적 안내</h3>
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
                            <a href="#paper-texture-section" class="btn-texture-view btn-texture-inline" title="명함 재질 이미지 보기">📋 명함재질보기</a>
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

                    <!-- 프리미엄 옵션 섹션 (PremiumOptionsGeneric이 동적 생성) -->
                    <div id="premiumOptionsSection" style="margin-top: 6px; display: none;"></div>
                    <input type="hidden" name="premium_options_total" id="premium_options_total" value="0">

                    <!-- 주문건수 선택 (모든 옵션 선택 후 건수 곱하기) -->
                    <div class="inline-form-row" style="margin-top: 6px; padding: 6px 12px; background: #f0f7ff; border: 1px solid #c5d9f0; border-radius: 8px;">
                        <label class="inline-label" for="order_count" style="color: #1565C0; font-weight: 600;">주문건수</label>
                        <select name="order_count" id="order_count" class="inline-select" onchange="updateOrderCountDisplay()">
                            <option value="1" selected>1건 (기본)</option>
                            <option value="2">2건</option>
                            <option value="3">3건</option>
                            <option value="4">4건</option>
                            <option value="5">5건</option>
                            <option value="6">6건</option>
                            <option value="7">7건</option>
                            <option value="8">8건</option>
                            <option value="9">9건</option>
                            <option value="10">10건</option>
                        </select>
                        <span class="inline-note">같은 스펙으로 여러 건 주문 (건별 디자인 가능)</span>
                    </div>

                    <!-- 건수 곱하기 요약 (건수 > 1일 때만 표시) -->
                    <div id="orderCountSummary" style="display: none; margin-top: 4px; padding: 6px 14px; background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; text-align: center;">
                    </div>

                    <!-- 기본 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <?php include __DIR__ . '/../../includes/action_buttons.php'; ?>

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

    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <!-- AI 생성 상세페이지 (기존 설명 위에 표시) -->
    <?php $detail_page_product = 'namecard'; include __DIR__ . "/../../_detail_page/detail_page_loader.php"; ?>
    <!-- 명함 상세 설명 섹션 (1100px 폭) - 하단 설명방법 적용 -->
    <div class="namecard-detail-combined" style="width: 1100px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_namecard.php"; ?>
    </div>
    <?php endif; ?>

    <?php
    // 갤러리 모달과 JavaScript는 if (function_exists("include_product_gallery")) { include_product_gallery()에서 자동 포함됨
    ?>

    <!-- 명함 전용 스크립트만 유지 (계산 로직 절대 건드리지 않음) -->

    <!-- 명함 전용 스크립트 -->
    <script src="js/namecard-compact.js"></script>

    <!-- 프리미엄 옵션 DB 로더 + JavaScript -->
    <script src="/js/premium-options-generic.js"></script>

    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <!-- 공통 업로드 모달 JavaScript (일반 모드에서만 로드) -->
    <script src="../../includes/upload_modal.js"></script>
    <!-- 일반 모드에서도 로그인 체크 건너뛰기 (다른 제품과 동일) -->
    <script>
    window.isLoggedIn = function() { return true; };
    window.checkLoginStatus = function() { return true; };
    </script>
    <?php else: ?>
    <!-- 견적서 모드: 로그인 체크 우회 -->
    <script>
    // 견적서 모드에서는 로그인 체크 건너뛰기
    window.isLoggedIn = function() { return true; };
    window.checkLoginStatus = function() { return true; };
    </script>
    <?php endif; ?>

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

                        // 명함 종류별 사이즈 결정 (카드명함=86×54, 일반/고급수입지=90×50)
                        const typeSelect = document.getElementById('MY_type');
                        const typeName = typeSelect.options[typeSelect.selectedIndex]?.text || '';
                        const isCard = typeName.indexOf('카드') > -1;
                        const ncGaro = isCard ? '86' : '90';
                        const ncSero = isCard ? '54' : '50';

                        const totalData = {
                            ...data.data,
                            premium_options_total: premiumTotal,
                            total_supply_price: totalSupplyPrice,  // 공급가액 합계
                            final_total_with_vat: finalTotalWithVat  // 부가세 포함 최종 금액
                        };

                        // specData 설정 (가격 표시 연동)
                        window.currentPriceData = {
                            ...totalData,
                            specData: {
                                garo: ncGaro,
                                sero: ncSero
                            }
                        };
                        document.dispatchEvent(new CustomEvent('priceUpdated', {detail: totalData}));

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

        // 프리미엄 옵션 가격 계산 (PremiumOptionsGeneric에서 hidden field 읽기)
        function calculatePremiumOptions() {
            // PremiumOptionsGeneric이 premium_options_total hidden field를 자동 업데이트함
            var ptField = document.getElementById('premium_options_total');
            return ptField ? parseInt(ptField.value) || 0 : 0;
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

            // 부가세 포함 금액 (항상 동일 형식)
            detailsHtml += `
                    <div class="price-item final">
                        <span class="price-item-label">부가세 포함:</span>
                        <span class="price-item-value" style="color: #28a745; font-size: 0.98rem; font-weight: 700;">${finalTotal.toLocaleString()}원</span>
                    </div>
                </div>
                `;

            // 주문건수 요약 영역 업데이트 (별도 영역)
            const orderCount = parseInt(document.getElementById('order_count')?.value) || 1;
            const summaryEl = document.getElementById('orderCountSummary');
            if (summaryEl) {
                if (orderCount > 1) {
                    const totalWithCount = finalTotal * orderCount;
                    summaryEl.style.display = 'block';
                    summaryEl.innerHTML = `
                        <div style="font-size: 13px; color: #555; margin-bottom: 6px;">
                            건당 <strong>${finalTotal.toLocaleString()}원</strong> × <strong style="color: #1565C0;">${orderCount}건</strong>
                        </div>
                        <div style="font-size: 18px; font-weight: 700; color: #d63384;">
                            합계 ${totalWithCount.toLocaleString()}원
                        </div>
                        <div style="font-size: 11px; color: #888; margin-top: 4px;">
                            같은 스펙 ${orderCount}건, 건별 디자인 가능
                        </div>
                    `;
                } else {
                    summaryEl.style.display = 'none';
                    summaryEl.innerHTML = '';
                }
            }

            priceDetails.innerHTML = detailsHtml;
            priceDisplay.classList.add('calculated');

            // priceAmount에 대표 금액 표시 (건수 포함 여부)
            const displayOrderCount = parseInt(document.getElementById('order_count')?.value) || 1;
            if (displayOrderCount > 1) {
                priceAmount.textContent = (finalTotal * displayOrderCount).toLocaleString() + '원';
            } else {
                priceAmount.textContent = priceData.total_supply_price.toLocaleString() + '원';
            }

            // 현재 가격 데이터 저장
            window.currentPriceData = priceData;
        }

        // 주문건수 변경 시 총액 표시 업데이트
        function updateOrderCountDisplay() {
            if (window.currentPriceData) {
                updatePriceDisplay(window.currentPriceData);
            }
        }

        // 가격 표시 초기화
        function resetPriceDisplay() {
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');
            const priceDisplay = document.getElementById('priceDisplay');
            const uploadButton = document.getElementById('uploadOrderButton');

            if (priceAmount) priceAmount.textContent = '견적 계산 필요';
            if (priceDetails) priceDetails.textContent = '모든 옵션을 선택하면 자동으로 계산됩니다';
            if (priceDisplay) priceDisplay.classList.remove('calculated');
            if (uploadButton) uploadButton.style.display = 'none';

            // 건수 요약 영역 초기화
            const summaryEl = document.getElementById('orderCountSummary');
            if (summaryEl) { summaryEl.style.display = 'none'; summaryEl.innerHTML = ''; }

            // 프리미엄 옵션 가격 초기화
            if (window.premiumOptionsManager && typeof window.premiumOptionsManager.reset === 'function') {
                window.premiumOptionsManager.reset();
            }

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
            if (uploadButton) {
                uploadButton.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('명함 프리미엄 옵션 페이지 초기화 완료');

            setTimeout(function() {
                const urlParams = new URLSearchParams(window.location.search);
                const urlType = urlParams.get('type');
                const urlSection = urlParams.get('section');
                const typeSelect = document.getElementById('MY_type');

                if (typeSelect && typeSelect.options.length > 1) {
                    if (urlType) {
                        typeSelect.value = urlType;
                    } else {
                        typeSelect.selectedIndex = 1;
                    }
                    const selectedValue = typeSelect.value;
                    if (selectedValue) {
                        console.log('종류 선택:', selectedValue, urlType ? '(URL)' : '(기본)');
                        handleTypeChange(selectedValue);

                        if (urlSection) {
                            setTimeout(function() {
                                const sectionSelect = document.getElementById('Section');
                                if (sectionSelect) {
                                    sectionSelect.value = urlSection;
                                    handleSectionChange(urlSection);
                                }
                            }, 600);
                        }
                    }
                }

                // 기본값이 설정되어 있으면 첫 화면에서 자동 계산 실행
                if (typeof autoCalculatePrice === 'function') {
                    autoCalculatePrice();
                    console.log('명함: 첫 화면 자동 계산 실행');
                }

                // 프리미엄 옵션은 PremiumOptionsGeneric이 자체 이벤트 리스너를 관리함
                console.log('프리미엄 옵션: PremiumOptionsGeneric으로 위임됨');
            }, 500);
        });


        // 🆕 공통 업로드 모달에서 사용할 장바구니 추가 함수
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
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
            formData.append('order_count', document.getElementById('order_count')?.value || '1');

            // 가격 정보 추가 (프리미엄 옵션 포함) - 신구 형식 모두 지원
            if (window.currentPriceData) {
                // 공급가액 합계 (신형식 우선, 구형식 fallback)
                const totalSupplyPrice = window.currentPriceData.total_supply_price
                    || window.currentPriceData.base_price
                    || window.currentPriceData.Order_PriceForm
                    || 0;
                formData.append('price', totalSupplyPrice);

                // 부가세 포함 최종 금액 (신형식 우선, 구형식 fallback)
                const finalTotalWithVat = window.currentPriceData.final_total_with_vat
                    || window.currentPriceData.total_with_vat
                    || window.currentPriceData.Total_PriceForm
                    || 0;
                formData.append('vat_price', finalTotalWithVat);
            }

            // 프리미엄 옵션 데이터 추가 (PremiumOptionsGeneric에서)
            if (window.premiumOptionsManager) {
                var selectedOpts = window.premiumOptionsManager.getSelectedOptions();
                formData.append('premium_options_data', JSON.stringify(selectedOpts));
                formData.append('premium_options_total', selectedOpts.premium_options_total || '0');
            } else {
                formData.append('premium_options_total', document.getElementById('premium_options_total')?.value || '0');
            }

            // 작업메모 추가 (모달에서)
            const workMemo = document.getElementById('modalWorkMemo');
            if (workMemo) {
                formData.append('work_memo', workMemo.value);
            }

            // 업로드 방법 추가
            formData.append('upload_method', window.selectedUploadMethod || 'upload');

            // 업로드된 파일들 추가
            if (window.uploadedFiles && window.uploadedFiles.length > 0) {
                window.uploadedFiles.forEach((fileObj, index) => {
                    // ⚠️ CRITICAL FIX: fileObj.file은 실제 File 객체, fileObj는 래퍼 객체
                    formData.append('uploaded_files[]', fileObj.file);
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
                    premium_options_data: formData.get('premium_options_data'),
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

        // Phase 5: 견적 요청 함수
        window.addToQuotation = function() {
            console.log('💰 견적 요청 시작');

            // 가격 계산 확인
            if (!window.currentPriceData || !window.currentPriceData.total_price) {
                alert('가격을 먼저 계산해주세요.');
                return;
            }

            // 프리미엄 옵션 재계산
            var premiumTotal = calculatePremiumOptions();
            console.log('💰 프리미엄 옵션 총액:', premiumTotal);

            // 폼 데이터 수집
            const formData = new FormData();
            formData.append('product_type', 'namecard');
            formData.append('MY_type', document.getElementById('MY_type').value);
            formData.append('Section', document.getElementById('Section').value);
            formData.append('POtype', document.getElementById('POtype').value);
            formData.append('MY_amount', document.getElementById('MY_amount').value);
            formData.append('ordertype', document.getElementById('ordertype').value);
            formData.append('order_count', document.getElementById('order_count')?.value || '1');
            formData.append('price', Math.round(window.currentPriceData.total_price));
            formData.append('vat_price', Math.round(window.currentPriceData.vat_price));

            // 프리미엄 옵션 추가 (PremiumOptionsGeneric에서)
            if (window.premiumOptionsManager) {
                var selectedOpts = window.premiumOptionsManager.getSelectedOptions();
                formData.append('premium_options_data', JSON.stringify(selectedOpts));
                formData.append('premium_options_total', premiumTotal);
            } else {
                formData.append('premium_options_total', premiumTotal);
            }

            // AJAX 전송
            fetch('../quote/add_to_quotation_temp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('서버 응답:', data);
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

        // 🆕 namecard.js 대체 필수 기능들
    </script>

<?php if ($isQuotationMode || $isAdminQuoteMode): ?>
    <!-- 관리자 견적서 모달용 applyToQuotation 함수 -->
    <script>
    /**
     * 견적서에 명함 품목 추가
     * calculator_modal.js가 ADMIN_QUOTE_ITEM_ADDED 메시지를 수신
     */
    window.applyToQuotation = function() {
        console.log('🚀 [관리자 견적서-명함] applyToQuotation() 호출');

        // 1. 필수 필드 검증
        const MY_type = document.getElementById('MY_type')?.value;
        const Section = document.getElementById('Section')?.value;
        const POtype = document.getElementById('POtype')?.value;
        const MY_amount = document.getElementById('MY_amount')?.value;

        if (!MY_type || !Section || !POtype || !MY_amount) {
            alert('모든 필수 옵션을 선택해주세요.');
            return;
        }

        // 2. 가격 확인
        if (!window.currentPriceData) {
            alert('가격을 먼저 계산해주세요.');
            return;
        }

        // 공급가액 계산 (VAT 미포함)
        const supplyPrice = Math.round(
            window.currentPriceData.total_price ||
            window.currentPriceData.total_supply_price ||
            window.currentPriceData.base_price ||
            window.currentPriceData.Order_PriceForm || 0
        );

        if (supplyPrice <= 0) {
            alert('유효한 가격이 계산되지 않았습니다.');
            return;
        }

        // 3. 사양 텍스트 생성 (2줄 형식)
        const typeText = document.getElementById('MY_type')?.options[document.getElementById('MY_type').selectedIndex]?.text || '';
        const sectionText = document.getElementById('Section')?.options[document.getElementById('Section').selectedIndex]?.text || '';
        const potypeText = document.getElementById('POtype')?.options[document.getElementById('POtype').selectedIndex]?.text || '';

        // 1줄: 종류 / 재질
        const line1 = [typeText, sectionText].filter(s => s).join(' / ');
        // 2줄: 인쇄
        const line2 = potypeText;
        const specification = `${line1}\n${line2}`;

        // 4. 수량 계산 (명함: 1 = 1,000매)
        let quantity = parseInt(MY_amount) || 1;
        if (quantity < 10) {
            quantity = quantity * 1000;
        }
        const quantityDisplay = quantity.toLocaleString() + '매';

        // 5. 페이로드 생성
        const payload = {
            product_type: 'namecard',
            product_name: '명함',
            specification: specification,
            quantity: quantity,
            unit: '매',
            quantity_display: quantityDisplay,
            supply_price: supplyPrice,
            // 원본 데이터
            MY_type: MY_type,
            Section: Section,
            POtype: POtype,
            MY_amount: MY_amount
        };

        console.log('📤 [명함] postMessage 전송:', payload);

        // 6. 부모 창으로 메시지 전송
        window.parent.postMessage({
            type: 'ADMIN_QUOTE_ITEM_ADDED',
            payload: payload
        }, window.location.origin);
    };

    console.log('✅ [관리자 견적서-명함] applyToQuotation() 정의 완료');
    </script>
<?php endif; ?>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js?v=<?php echo time(); ?>"></script>

    <!-- 테마 스위처 -->
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) ThemeLoader::renderSwitcher('bottom-right'); ?>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) ThemeLoader::renderSwitcherJS(); ?>

    <?php require_once __DIR__ . '/../../includes/PremiumOptionsConfig.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('premiumOptionsSection') && typeof PremiumOptionsGeneric !== 'undefined') {
            setTimeout(function() {
                var config = <?php echo PremiumOptionsConfig::toJson('namecard'); ?>;
                var poManager = new PremiumOptionsGeneric('namecard', 'premiumOptionsSection', 'MY_amount', config);
                poManager.init();
                window.premiumOptionsManager = poManager;
            }, 200);
        }
    });
    </script>


    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <?php include __DIR__ . '/../../includes/quote_gauge.php'; ?>
    <script src="/js/quote-gauge.js?v=<?php echo time(); ?>"></script>
    <?php endif; ?>

    <?php
    if ($db) {
        mysqli_close($db);
    }
    ?>

<?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/footer.php"; ?>
