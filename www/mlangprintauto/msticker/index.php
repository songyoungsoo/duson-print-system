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
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate 
               WHERE Ttable='msticker' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%종이%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 자석스티커 종류의 첫 번째 규격 가져오기
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                      WHERE Ttable='msticker' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_row = mysqli_fetch_assoc($section_result)) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (100매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_msticker 
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
    <!-- 🎨 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    <!-- 공통 갤러리 팝업 함수 (샘플더보기 버튼용) -->
    <script src="../../js/common-gallery-popup.js"></script>
    
    
    
    
    <!-- 자석스티커 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css">

    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통합 가격 표시 시스템 -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    <!-- 통일 인라인 폼 스타일 시스템 -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- 통일된 갤러리 팝업 CSS -->
    
    <!-- 고급 JavaScript 라이브러리 -->

    <script src="../../js/msticker.js" defer></script>
    
    <!-- 통일된 갤러리 팝업 JavaScript -->

    
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    
    <!-- 업로드 컴포넌트 JavaScript 라이브러리 포함 -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>


    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/msticker-inline-extracted.css">
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
</head>
<body class="msticker-page">
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>

    <div class="product-container">
        <div class="page-title">
            <h1>🧲 자석스티커 견적 안내</h1>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
                        <!-- 좌측: 갤러리 -->
                        <section class="product-gallery" aria-label="자석스티커 샘플 갤러리">
                            <?php
                            $gallery_product = 'msticker';
                            include '../../includes/simple_gallery_include.php';
                            ?>
                        </section>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="product-calculator">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="mstickerForm">
                    <!-- 통일 인라인 폼 시스템 - Msticker 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">종류</label>
                            <select class="inline-select" name="MY_type" id="MY_type" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "mlangprintauto_transactioncate", 'msticker');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="inline-note">자석스티커 종류를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">규격</label>
                            <select class="inline-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 규격을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select class="inline-select" name="POtype" id="POtype" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
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
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                            </select>
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>

                    <!-- 통일된 가격 표시 시스템 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            <span>모든 옵션을 선택하면 자동으로 계산됩니다</span>
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 - 프리미엄 스타일 -->
                    <div class="upload-order-button" id="uploadOrderButton">
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

    <?php 
    // 자석스티커 모달 설정
    $modalProductName = '자석스티커';
    $modalProductIcon = '🏷️';
    include '../../includes/upload_modal.php'; 
    ?>

    <?php include "../../includes/login_modal.php"; ?>

    <!-- 종이자석스티커 상세 설명 섹션 (하단 설명방법) -->
    <div class="msticker-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_msticker.php"; ?>
    </div>



<?php
// 공통 푸터 포함
include "../../includes/footer.php";
?>

    <!-- 자석스티커 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    

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

        // 자석스티커 전용 장바구니 추가 함수 (upload_modal.js 호드 전에 정의)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
            console.log('🧲 자석스티커 handleModalBasketAdd 호출');
            console.log('currentPriceData:', window.currentPriceData || currentPriceData);

            const priceData = window.currentPriceData || currentPriceData;

            if (!priceData) {
                onError('먼저 가격을 계산해주세요.');
                return;
            }

            // 로딩 상태 표시
            const cartButton = document.querySelector('.btn-cart');
            if (!cartButton) {
                onError('장바구니 버튼을 찾을 수 없습니다.');
                return;
            }

            const originalText = cartButton.innerHTML;
            cartButton.innerHTML = '🔄 저장 중...';
            cartButton.disabled = true;
            cartButton.style.opacity = '0.7';

            const form = document.getElementById('mstickerForm');
            const workMemoElement = document.getElementById('modalWorkMemo');
            const workMemo = workMemoElement ? workMemoElement.value : '';

            if (!form) {
                cartButton.innerHTML = originalText;
                cartButton.disabled = false;
                cartButton.style.opacity = '1';
                onError('양식을 찾을 수 없습니다.');
                return;
            }

            const formData = new FormData(form);

            // 선택된 옵션의 텍스트 정보 수집
            const typeSelect = document.getElementById('MY_type');
            const sectionSelect = document.getElementById('Section');
            const potypeSelect = document.getElementById('POtype');
            const quantitySelect = document.getElementById('MY_amount');
            const ordertypeSelect = document.getElementById('ordertype');

            const selectedOptions = {
                type_text: typeSelect.options[typeSelect.selectedIndex].text,
                section_text: sectionSelect.options[sectionSelect.selectedIndex].text,
                potype_text: potypeSelect.options[potypeSelect.selectedIndex].text,
                quantity_text: quantitySelect.options[quantitySelect.selectedIndex].text,
                ordertype_text: ordertypeSelect.options[ordertypeSelect.selectedIndex].text
            };

            // 기본 주문 정보
            formData.set('action', 'add_to_basket');
            formData.set('price', Math.round(priceData.total_price));
            formData.set('vat_price', Math.round(priceData.total_with_vat));
            formData.set('product_type', 'msticker');

            // 자석스티커 상세 정보 추가
            formData.set('selected_options', JSON.stringify(selectedOptions));

            // 추가 정보
            formData.set('work_memo', workMemo);
            formData.set('upload_method', window.selectedUploadMethod || 'upload');

            // 업로드된 파일들 추가
            if (uploadedFiles && uploadedFiles.length > 0) {
                uploadedFiles.forEach((fileObj, index) => {
                    formData.append(`uploaded_files[${index}]`, fileObj.file);
                });

                // 파일 정보 JSON
                const fileInfoArray = uploadedFiles.map(fileObj => ({
                    name: fileObj.name,
                    size: fileObj.size,
                    type: fileObj.type
                }));
                formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
            }

            fetch('/mlangprintauto/msticker/add_to_basket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);

                try {
                    const response = JSON.parse(text);
                    console.log('Parsed response:', response);

                    if (response.success) {
                        onSuccess();
                    } else {
                        cartButton.innerHTML = originalText;
                        cartButton.disabled = false;
                        cartButton.style.opacity = '1';
                        onError(response.message || '알 수 없는 오류');
                    }
                } catch (parseError) {
                    cartButton.innerHTML = originalText;
                    cartButton.disabled = false;
                    cartButton.style.opacity = '1';
                    console.error('JSON Parse Error:', parseError);
                    onError('서버 응답 처리 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                cartButton.innerHTML = originalText;
                cartButton.disabled = false;
                cartButton.style.opacity = '1';
                console.error('Fetch Error:', error);
                onError('장바구니 저장 중 네트워크 오류가 발생했습니다: ' + error.message);
            });
        };

        // 공통 모달 JavaScript 로드 (자석스티커 함수 정의 후)
        const modalScript = document.createElement('script');
        modalScript.src = '../../includes/upload_modal.js';
        document.head.appendChild(modalScript);
        
        // 자석스티커 전용 msticker.js가 모든 기능을 처리합니다
        
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
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate 
               WHERE Ttable='msticker' AND BigNo='0' 
               ORDER BY CASE WHEN title LIKE '%종이%' THEN 1 ELSE 2 END, no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 자석스티커 종류의 첫 번째 규격 가져오기
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                      WHERE Ttable='msticker' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (100매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_msticker 
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
    <!-- 🎨 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    <!-- 공통 갤러리 팝업 함수 (샘플더보기 버튼용) -->
    <script src="../../js/common-gallery-popup.js"></script>
    
    
    
    
    <!-- 자석스티커 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css">
    
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통합 가격 표시 시스템 -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    <!-- 통일 인라인 폼 스타일 시스템 -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    
    <!-- 고급 JavaScript 라이브러리 -->
    
    <script src="../../js/msticker.js" defer></script>
    
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    
    <!-- 업로드 컴포넌트 JavaScript 라이브러리 포함 -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>
    

    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/msticker-inline-extracted.css">
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
</head>
<body class="msticker-page">
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>

    <div class="product-container">
        <div class="page-title">
            <h1>🧲 자석스티커 견적 안내</h1>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
            <!-- 좌측: 갤러리 -->
            <section class="product-gallery" aria-label="자석스티커 샘플 갤러리">
                <?php
                $gallery_product = 'msticker';
                include '../../includes/simple_gallery_include.php';
                ?>
            </section>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="product-calculator">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>

                <form id="mstickerForm">
                    <!-- 통일 인라인 폼 시스템 - Msticker 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">종류</label>
                            <select class="inline-select" name="MY_type" id="MY_type" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "mlangprintauto_transactioncate", 'msticker');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="inline-note">자석스티커 종류를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">규격</label>
                            <select class="inline-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 규격을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select class="inline-select" name="POtype" id="POtype" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
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
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                            </select>
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>

                    <!-- 통일된 가격 표시 시스템 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            <span>모든 옵션을 선택하면 자동으로 계산됩니다</span>
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 - 프리미엄 스타일 -->
                    <div class="upload-order-button" id="uploadOrderButton">
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

    <?php 
    // 자석스티커 모달 설정
    $modalProductName = '자석스티커';
    $modalProductIcon = '🏷️';
    include '../../includes/upload_modal.php'; 
    ?>

    <?php include "../../includes/login_modal.php"; ?>

    <!-- 종이자석스티커 상세 설명 섹션 (하단 설명방법) -->
    <div class="msticker-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_msticker.php"; ?>
    </div>


<?php
// 공통 푸터 포함
include "../../includes/footer.php";
?>

    <!-- 자석스티커 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    

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

        // 자석스티커 전용 장바구니 추가 함수 (upload_modal.js 호드 전에 정의)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
            console.log('🧲 자석스티커 handleModalBasketAdd 호출');
            console.log('currentPriceData:', window.currentPriceData || currentPriceData);

            const priceData = window.currentPriceData || currentPriceData;

            if (!priceData) {
                onError('먼저 가격을 계산해주세요.');
                return;
            }

            // 로딩 상태 표시
            const cartButton = document.querySelector('.btn-cart');
            if (!cartButton) {
                onError('장바구니 버튼을 찾을 수 없습니다.');
                return;
            }

            const originalText = cartButton.innerHTML;
            cartButton.innerHTML = '🔄 저장 중...';
            cartButton.disabled = true;
            cartButton.style.opacity = '0.7';

            const form = document.getElementById('mstickerForm');
            const workMemoElement = document.getElementById('modalWorkMemo');
            const workMemo = workMemoElement ? workMemoElement.value : '';

            if (!form) {
                cartButton.innerHTML = originalText;
                cartButton.disabled = false;
                cartButton.style.opacity = '1';
                onError('양식을 찾을 수 없습니다.');
                return;
            }

            const formData = new FormData(form);

            // 선택된 옵션의 텍스트 정보 수집
            const typeSelect = document.getElementById('MY_type');
            const sectionSelect = document.getElementById('Section');
            const potypeSelect = document.getElementById('POtype');
            const quantitySelect = document.getElementById('MY_amount');
            const ordertypeSelect = document.getElementById('ordertype');

            const selectedOptions = {
                type_text: typeSelect.options[typeSelect.selectedIndex].text,
                section_text: sectionSelect.options[sectionSelect.selectedIndex].text,
                potype_text: potypeSelect.options[potypeSelect.selectedIndex].text,
                quantity_text: quantitySelect.options[quantitySelect.selectedIndex].text,
                ordertype_text: ordertypeSelect.options[ordertypeSelect.selectedIndex].text
            };

            // 기본 주문 정보
            formData.set('action', 'add_to_basket');
            formData.set('price', Math.round(priceData.total_price));
            formData.set('vat_price', Math.round(priceData.total_with_vat));
            formData.set('product_type', 'msticker');

            // 자석스티커 상세 정보 추가
            formData.set('selected_options', JSON.stringify(selectedOptions));

            // 추가 정보
            formData.set('work_memo', workMemo);
            formData.set('upload_method', window.selectedUploadMethod || 'upload');

            // 업로드된 파일들 추가
            if (uploadedFiles && uploadedFiles.length > 0) {
                uploadedFiles.forEach((fileObj, index) => {
                    formData.append(`uploaded_files[${index}]`, fileObj.file);
                });

                // 파일 정보 JSON
                const fileInfoArray = uploadedFiles.map(fileObj => ({
                    name: fileObj.name,
                    size: fileObj.size,
                    type: fileObj.type
                }));
                formData.set('uploaded_files_info', JSON.stringify(fileInfoArray));
            }

            fetch('/mlangprintauto/msticker/add_to_basket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);

                try {
                    const response = JSON.parse(text);
                    console.log('Parsed response:', response);

                    if (response.success) {
                        onSuccess();
                    } else {
                        cartButton.innerHTML = originalText;
                        cartButton.disabled = false;
                        cartButton.style.opacity = '1';
                        onError(response.message || '알 수 없는 오류');
                    }
                } catch (parseError) {
                    cartButton.innerHTML = originalText;
                    cartButton.disabled = false;
                    cartButton.style.opacity = '1';
                    console.error('JSON Parse Error:', parseError);
                    onError('서버 응답 처리 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                cartButton.innerHTML = originalText;
                cartButton.disabled = false;
                cartButton.style.opacity = '1';
                console.error('Fetch Error:', error);
                onError('장바구니 저장 중 네트워크 오류가 발생했습니다: ' + error.message);
            });
        };

        // 공통 모달 JavaScript 로드 (자석스티커 함수 정의 후)
        const modalScript = document.createElement('script');
        modalScript.src = '../../includes/upload_modal.js';
        document.head.appendChild(modalScript);
        
        // 자석스티커 전용 msticker.js가 모든 기능을 처리합니다
        

