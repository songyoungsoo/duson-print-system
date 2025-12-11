<?php
/**
 * 포스터/리플렛 견적안내 컴팩트 시스템 - PROJECT_SUCCESS_REPORT.md 스펙 구현  
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 8월 (AI Assistant - Frontend Persona)
 */

// 보안 상수 정의 후 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 📱 모달 모드 감지 (견적서 시스템에서 iframe으로 호출될 때)
$is_quotation_mode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';
$body_class = $is_quotation_mode ? ' quotation-modal-mode' : '';

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
    <!-- 🎨 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    
    
    
    <!-- 포스터 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <!-- 통합 가격 표시 시스템 -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    <!-- 통합 인라인 폼 스타일 시스템 -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- 공통 가격 표시 시스템 -->
    <script src="../../js/common-price-display.js" defer></script>
    <!-- 공통 갤러리 시스템 (helper가 자동으로 필요한 에셋 로드) -->
    <script src="../../js/poster.js?v=1759244654" defer></script>
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    <!-- 공통 갤러리 팝업 함수 -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/littleprint-inline-extracted.css">
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
    <!-- 📱 견적서 모달 모드 공통 CSS (전 제품 공통) -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">
</head>
<body class="littleprint-page<?php echo $body_class; ?>">
<?php if (!$is_quotation_mode): ?>
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>
<?php endif; ?>

    <div class="product-container">
<?php if (!$is_quotation_mode): ?>
        <div class="page-title">
            <h1>📄 포스터 견적 안내</h1>
        </div>
<?php endif; ?>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
<?php if (!$is_quotation_mode): ?>
            <!-- 좌측: 포스터 갤러리 (통합 갤러리 시스템 500×400) -->
            <div class="product-gallery">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'littleprint';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </div>
<?php endif; ?>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="product-calculator">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="posterForm">
                    <!-- 통일 인라인 폼 시스템 - 리틀프린트 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">종류</label>
                            <select class="inline-select" name="MY_type" id="MY_type" required onchange="calculatePrice()">
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
                            <span class="inline-note">포스터 종류를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">지류</label>
                            <select class="inline-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 용지를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="PN_type">규격</label>
                            <select class="inline-select" name="PN_type" id="PN_type" required onchange="calculatePrice()">
                                <option value="">먼저 지류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">인쇄 사이즈를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select class="inline-select" name="POtype" id="POtype" required onchange="calculatePrice()">
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
                            <span class="inline-note">단면 또는 양면 인쇄</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_amount">수량</label>
                            <select class="inline-select" name="MY_amount" id="MY_amount" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 규격을 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하시는 수량을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="ordertype">편집비</label>
                            <select class="inline-select" name="ordertype" id="ordertype" required onchange="calculatePrice()">
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
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>

                    <!-- 추가 옵션 섹션 (전단지 스타일) -->
                    <div class="leaflet-premium-options-section" id="premiumOptionsSection" style="margin-top: 15px;">
                        <!-- 한 줄 체크박스 헤더 -->
                        <div class="option-headers-row">
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="coating_enabled" name="coating_enabled" class="option-toggle" value="1">
                                <label for="coating_enabled" class="toggle-label">코팅</label>
                            </div>
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="folding_enabled" name="folding_enabled" class="option-toggle" value="1">
                                <label for="folding_enabled" class="toggle-label">접지</label>
                            </div>
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="creasing_enabled" name="creasing_enabled" class="option-toggle" value="1">
                                <label for="creasing_enabled" class="toggle-label">오시</label>
                            </div>
                            <div class="option-price-display">
                                <span class="option-price-total" id="premiumPriceTotal">(+0원)</span>
                            </div>
                        </div>

                        <!-- 코팅 옵션 상세 -->
                        <div class="option-details" id="coating_options" style="display: none;">
                            <select name="coating_type" id="coating_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="single">단면유광코팅</option>
                                <option value="double">양면유광코팅</option>
                                <option value="single_matte">단면무광코팅</option>
                                <option value="double_matte">양면무광코팅</option>
                            </select>
                        </div>

                        <!-- 접지 옵션 상세 -->
                        <div class="option-details" id="folding_options" style="display: none;">
                            <select name="folding_type" id="folding_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="2fold">2단접지</option>
                                <option value="3fold">3단접지</option>
                                <option value="accordion">병풍접지</option>
                                <option value="gate">대문접지</option>
                            </select>
                        </div>

                        <!-- 오시 옵션 상세 -->
                        <div class="option-details" id="creasing_options" style="display: none;">
                            <select name="creasing_lines" id="creasing_lines" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="1">1줄</option>
                                <option value="2">2줄</option>
                                <option value="3">3줄</option>
                            </select>
                        </div>

                        <!-- 숨겨진 필드들 -->
                        <input type="hidden" name="coating_price" id="coating_price" value="0">
                        <input type="hidden" name="folding_price" id="folding_price" value="0">
                        <input type="hidden" name="creasing_price" id="creasing_price" value="0">
                        <input type="hidden" name="additional_options_total" id="additional_options_total" value="0">
                    </div>

                    <!-- 통일된 가격 표시 시스템 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton">
                        <?php if ($is_quotation_mode): ?>
                            <!-- 견적서 모달 모드: 자동 계산 후 견적서 적용 버튼만 표시 -->
                            <button type="button" class="btn-upload-order" id="applyBtn" onclick="sendToQuotation()" style="background: #217346; display: none;">
                                ✅ 견적서에 적용
                            </button>
                        <?php else: ?>
                            <!-- 일반 모드: 파일 업로드 및 주문 -->
                            <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                                파일 업로드 및 주문하기
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- 숨겨진 필드들 -->
                    <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
                    <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
                    <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
                    <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
                    <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
                    <input type="hidden" name="page" value="LittlePrint">
                </form>
            </div>
        </div>
    </div>

    <!-- 파일 업로드 모달 (통합 컴포넌트) -->
    <?php include "../../includes/upload_modal.php"; ?>
    <script src="../../includes/upload_modal.js?v=1759244661"></script>

    <?php include "../../includes/login_modal.php"; ?>

<?php if (!$is_quotation_mode): ?>
    <!-- 포스터 상세 설명 섹션 (1200px 폭) - 하단 설명방법 적용 -->
    <div class="poster-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_poster.php"; ?>
    </div>

    <?php include "../../includes/footer.php"; ?>
<?php endif; ?>

    <script>
        // 포스터 가격 계산 함수 (AJAX 기반)
        function calculatePrice() {
            const form = document.getElementById('posterForm');
            if (!form) {
                console.error('posterForm을 찾을 수 없습니다');
                return;
            }

            const MY_type = document.getElementById('MY_type')?.value || '';
            const Section = document.getElementById('Section')?.value || '';
            const PN_type = document.getElementById('PN_type')?.value || '';
            const POtype = document.getElementById('POtype')?.value || '';
            const MY_amount = document.getElementById('MY_amount')?.value || '';
            const ordertype = document.getElementById('ordertype')?.value || '';

            // 필수 필드 체크
            if (!MY_type || !Section || !PN_type || !POtype || !MY_amount || !ordertype) {
                console.log('모든 필드를 선택해주세요');
                return;
            }

            // 추가 옵션 총액
            const additionalOptionsTotal = parseInt(document.getElementById('additional_options_total')?.value) || 0;

            const params = new URLSearchParams({
                MY_type: MY_type,
                Section: Section,
                PN_type: PN_type,
                POtype: POtype,
                MY_amount: MY_amount,
                ordertype: ordertype,
                additional_options_total: additionalOptionsTotal
            });

            console.log('포스터 가격 계산 요청:', params.toString());

            fetch('calculate_price_ajax.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    console.log('가격 계산 응답:', data);

                    if (data.success) {
                        const priceData = data.data;

                        // 가격 표시 업데이트
                        const priceAmount = document.getElementById('priceAmount');
                        const vatAmount = document.getElementById('vatAmount');
                        const totalAmount = document.getElementById('totalAmount');

                        if (priceAmount) priceAmount.textContent = Number(priceData.total_price).toLocaleString() + '원';
                        if (vatAmount) vatAmount.textContent = Number(priceData.vat).toLocaleString() + '원';
                        if (totalAmount) totalAmount.textContent = Number(priceData.total_with_vat).toLocaleString() + '원';

                        // window.currentPriceData 설정 (장바구니/견적서용)
                        window.currentPriceData = {
                            total_price: priceData.total_price,
                            vat_price: priceData.total_with_vat,
                            vat_amount: priceData.vat
                        };

                        console.log('✅ currentPriceData 설정:', window.currentPriceData);

                        // 견적서 모드: 견적서 적용 버튼 표시
                        const applyBtn = document.getElementById('applyBtn');
                        if (applyBtn) {
                            applyBtn.style.display = 'block';
                            console.log('✅ 견적서 적용 버튼 표시됨');
                        }
                    } else {
                        console.error('가격 계산 실패:', data.message);
                    }
                })
                .catch(error => {
                    console.error('가격 계산 오류:', error);
                });
        }

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
        // 포스터(리틀프린트) 전용 장바구니 추가 함수 (통합 모달 패턴)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
            console.log("포스터 장바구니 추가 시작");

            if (!window.currentPriceData) {
                console.error("가격 계산이 필요합니다");
                if (onError) onError("먼저 가격을 계산해주세요.");
                return;
            }

            const formData = new FormData();
            formData.append("action", "add_to_basket");
            formData.append("product_type", "littleprint");
            formData.append("MY_type", document.getElementById("MY_type").value);
            formData.append("Section", document.getElementById("Section").value);
            formData.append("POtype", document.getElementById("POtype").value);
            formData.append("MY_amount", document.getElementById("MY_amount").value);
            formData.append("ordertype", document.getElementById("ordertype").value);
            formData.append("calculated_price", Math.round(window.currentPriceData.total_price));
            formData.append("calculated_vat_price", Math.round(window.currentPriceData.vat_price));

            const workMemo = document.getElementById("modalWorkMemo");
            if (workMemo) formData.append("work_memo", workMemo.value);

            formData.append("upload_method", window.selectedUploadMethod || "upload");

            if (uploadedFiles && uploadedFiles.length > 0) {
                uploadedFiles.forEach((fileObj, index) => {
                    // ⚠️ CRITICAL FIX: fileObj.file은 실제 File 객체, fileObj는 래퍼 객체
                    formData.append("uploaded_files[" + index + "]", fileObj.file);
                });
            }

            fetch("add_to_basket.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (onSuccess) onSuccess(data);
                } else {
                    if (onError) onError(data.message);
                }
            })
            .catch(error => {
                console.error("장바구니 추가 오류:", error);
                if (onError) onError("네트워크 오류가 발생했습니다.");
            });
        };

        // 견적서 모달 모드: 견적서에 적용 함수
        window.sendToQuotation = function() {
            console.log('📤 [TUNNEL 2/5] "✅ 견적서에 적용" 버튼 클릭됨');

            if (!window.currentPriceData || !window.currentPriceData.total_price) {
                console.error('❌ 가격 데이터 없음');
                alert('먼저 견적 계산을 해주세요. "견적 계산" 버튼을 눌러주세요.');
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '📝 견적서에 입력 중...';

            try {
                // 폼에서 제품 스펙 정보 수집 (포스터 전용)
                const myTypeSelect = document.getElementById('MY_type');
                const sectionSelect = document.getElementById('Section');
                const pnTypeSelect = document.getElementById('PN_type');
                const potypeSelect = document.getElementById('POtype');
                const myAmountSelect = document.getElementById('MY_amount');
                const ordertypeSelect = document.getElementById('ordertype');

                // 선택된 옵션의 텍스트 추출
                const typeText = myTypeSelect ? myTypeSelect.options[myTypeSelect.selectedIndex].text : '';
                const sectionText = sectionSelect ? sectionSelect.options[sectionSelect.selectedIndex].text : '';
                const pnText = pnTypeSelect ? pnTypeSelect.options[pnTypeSelect.selectedIndex].text : '';
                const potypeText = potypeSelect ? potypeSelect.options[potypeSelect.selectedIndex].text : '';
                const quantityText = myAmountSelect ? myAmountSelect.options[myAmountSelect.selectedIndex].text : '';
                const ordertypeText = ordertypeSelect ? ordertypeSelect.options[ordertypeSelect.selectedIndex].text : '';

                // 규격 문자열 생성
                const specification = `${typeText} / ${sectionText} / ${pnText} / ${potypeText} / ${quantityText} / ${ordertypeText}`.trim();

                // 수량 값 추출
                const quantityValue = parseInt(myAmountSelect.value) || 100;

                // 견적서 폼에 전달할 데이터 구조
                const quotationData = {
                    product_name: '포스터',
                    specification: specification,
                    quantity: quantityValue,
                    unit: '부',
                    supply_price: Math.round(window.currentPriceData.total_price),
                    vat_price: Math.round(window.currentPriceData.total_with_vat || window.currentPriceData.vat_price)
                };

                console.log('📋 견적 데이터:', quotationData);

                // 부모 창으로 데이터 전송
                window.parent.postMessage({
                    type: 'CALCULATOR_PRICE_DATA',
                    payload: quotationData
                }, window.location.origin);

                console.log('✅ postMessage 전송 완료');

                btn.innerHTML = '✅ 견적서에 적용됨!';
                btn.style.background = '#28a745';

            } catch (error) {
                console.error('❌ 견적서 데이터 전송 실패:', error);
                alert('견적서 적용 중 오류가 발생했습니다: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };

        // 견적서 모드일 때 가격 자동 계산 및 2단계 버튼 표시
        document.addEventListener('DOMContentLoaded', function() {
            const applyBtn = document.getElementById('applyBtn');
            if (applyBtn) {
                let calculationAttempted = false;

                // window.currentPriceData 변경 감지 및 자동 계산 (setInterval 폴링)
                const observer = setInterval(function() {
                    const priceData = window.currentPriceData || (typeof currentPriceData !== 'undefined' ? currentPriceData : null);

                    // 가격 데이터가 있으면 버튼 표시
                    if (priceData && priceData.total_price) {
                        applyBtn.style.display = 'block';
                        console.log('✅ 견적서 모드: 2단계 버튼 활성화됨');
                        clearInterval(observer);
                        return;
                    }

                    // 가격 데이터가 없고, 아직 계산 시도 안 했으면 자동 계산
                    if (!calculationAttempted) {
                        const MY_type = document.getElementById('MY_type')?.value;
                        const Section = document.getElementById('Section')?.value;
                        const PN_type = document.getElementById('PN_type')?.value;
                        const POtype = document.getElementById('POtype')?.value;
                        const MY_amount = document.getElementById('MY_amount')?.value;
                        const ordertype = document.getElementById('ordertype')?.value;

                        // 모든 필드가 채워져 있으면 자동 계산
                        if (MY_type && Section && PN_type && POtype && MY_amount && ordertype) {
                            console.log('🔄 견적서 모드: 자동 가격 계산 실행');
                            calculationAttempted = true;
                            if (typeof calculatePrice === 'function') {
                                calculatePrice();
                            }
                        }
                    }
                }, 500);
            }
        });

        // poster.js에서 전역 변수와 초기화 함수들을 처리 (갤러리는 공통 시스템 사용)
    </script>

    <!-- 포스터 추가 옵션 시스템 -->
    <script src="js/littleprint-premium-options.js"></script>

    <!-- 포스터/리플렛 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->


    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
    <?php
    // 채팅 위젯 포함
    include_once __DIR__ . "/../../includes/chat_widget.php";
    ?>
</body>
</html>