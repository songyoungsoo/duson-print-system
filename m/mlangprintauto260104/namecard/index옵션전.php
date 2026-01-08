<?php
/**
 * 명함 견적안내 컴팩트 시스템 - PROJECT_SUCCESS_REPORT.md 스펙 구현
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 8월 (AI Assistant - Frontend Persona)
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
    
    <!-- 공통 헤더 포함 -->
    <?php include "../../includes/header.php"; ?>
    
    <!-- 명함 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/namecard-compact.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../assets/css/gallery.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <link rel="stylesheet" href="../../css/unified-price-display.css">

    
    <!-- 공통 가격 표시 시스템 -->
    <script src="../../js/common-price-display.js" defer></script>
    <!-- 명함 전용 JavaScript -->
    <script src="../../js/namecard.js" defer></script>
    
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
            <!-- 좌측: 통합 갤러리 섹션 -->
            <section class="namecard-gallery namecard-privacy-protection" aria-label="명함 샘플 갤러리">
                <?php
                // 원클릭 갤러리 포함 (공통 헬퍼 사용)
                if (function_exists("include_product_gallery")) { include_product_gallery('namecard'); }
                ?>
            </section>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="calculator-section">
                <div class="calculator-header">
                    <h3>💰견적 안내</h3>
                </div>

                <form id="namecardForm">
                    <!-- 스티커 스타일 옵션 폼 -->
                    <div class="options-grid">
                        <div class="inline-form-row">
                            <span class="inline-label">종류</span>
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
                        </div>

                        <div class="inline-form-row">
                            <span class="inline-label">재질</span>
                            <select name="Section" id="Section" class="inline-select" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>" onchange="handleSectionChange(this.value)">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                        </div>

                        <div class="inline-form-row">
                            <span class="inline-label">인쇄면</span>
                            <select name="POtype" id="POtype" class="inline-select" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                            </select>
                        </div>

                        <div class="inline-form-row">
                            <span class="inline-label">수량</span>
                            <select name="MY_amount" id="MY_amount" class="inline-select" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 재질을 선택해주세요</option>
                            </select>
                        </div>

                        <div class="inline-form-row">
                            <span class="inline-label">편집</span>
                            <select name="ordertype" id="ordertype" class="inline-select" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                            </select>
                        </div>
                    </div>

                    <!-- 스티커 방식의 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 - 프리미엄 스타일 -->
                    <div class="upload-order-button" id="uploadOrderButton" style="display: none;">
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
            </div>
        </div>
    </div>

    <?php
    // 공통 업로드 모달 설정 (통일된 명명 규칙)
    $modalProductName = '명함';
    $modalProductIcon = '🃏';
    
    // 공통 업로드 모달 포함
    include "../../includes/upload_modal.php";
    ?>
    
    <!-- 기존 모달 제거됨 - 공통 모달 사용 -->

    <!-- 팝업 갤러리 시스템 -->
    <div id="galleryPopup" class="gallery-popup" style="display: none;">
        <div class="popup-overlay" onclick="closeGalleryPopup()"></div>
        <div class="popup-content">
            <div class="popup-header">
                <h3>🖼️ 명함 포트폴리오 갤러리</h3>
                <button class="btn-close" onclick="closeGalleryPopup()">✕</button>
            </div>
            
            <div class="popup-body">
                <!-- 이미지 그리드 -->
                <div class="image-grid" id="imageGrid">
                    <div class="grid-loading">
                        <div class="loading-spinner"></div>
                        <p>포트폴리오를 불러오는 중...</p>
                    </div>
                </div>
                
                <!-- 페이지네이션 -->
                <div class="pagination" id="pagination" style="display: none;">
                    <!-- 동적으로 생성 -->
                </div>
            </div>
        </div>
    </div>

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
    
    <?php
    // 갤러리 모달과 JavaScript는 if (function_exists("include_product_gallery")) { include_product_gallery()에서 자동 포함됨
    ?>
    
    <?php include "../../includes/footer.php"; ?>

    <!-- 명함 전용 컴팩트 CSS 외부 파일로 분리 -->
    <link rel="stylesheet" href="../../css/namecard-inline-styles.css">

    <!-- 명함 전용 스크립트만 유지 (계산 로직 절대 건드리지 않음) -->
    
    <!-- 명함 전용 스크립트 -->
    <script src="js/namecard-compact.js"></script>

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

        // 가격 계산 함수
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
            
            // 가격 계산 AJAX 호출
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
                        updatePriceDisplay(data.data);
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

        // 가격 표시 업데이트
        function updatePriceDisplay(priceData) {
            const priceAmount = document.getElementById('priceAmount');
            const priceDetails = document.getElementById('priceDetails');
            const priceDisplay = document.getElementById('priceDisplay');
            
            priceAmount.textContent = Math.floor(priceData.total_with_vat).toLocaleString() + '원';
            
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
            
            detailsHtml += `
                    <div class="price-item final">
                        <span class="price-item-label">부가세 포함:</span>
                        <span class="price-item-value">${Math.floor(priceData.total_with_vat).toLocaleString()}원</span>
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
            console.log('명함 페이지 초기화 완료 - 통합 갤러리 시스템');
            
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
            }, 500); // namecard.js 로드 대기
        });

        // namecard.js에서 가격 계산 및 기타 로직 처리
        // 주의: 계산기 관련 코드는 절대 수정하지 않음
    </script>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>