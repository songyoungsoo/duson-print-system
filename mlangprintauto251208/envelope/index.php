<?php
/**
 * 봉투 견적안내 컴팩트 시스템 - NameCard 시스템 구조 적용
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

// 📱 모달 모드 감지 (견적서 시스템에서 iframe으로 호출될 때)
$is_quotation_mode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';
$body_class = $is_quotation_mode ? ' quotation-modal-mode' : '';

// 공통 인증 및 설정
include "../../includes/auth.php";

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
if (function_exists("init_gallery_system")) { init_gallery_system("envelope"); }

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("봉투 견적안내 컴팩트 - 프리미엄");

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 봉투 종류 가져오기
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate 
               WHERE Ttable='Envelope' AND BigNo='0' 
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 봉투 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                      WHERE Ttable='Envelope' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (1000매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_envelope 
                          WHERE style='" . $type_row['no'] . "' AND Section='" . $section_row['no'] . "' 
                          ORDER BY CASE WHEN quantity='1000' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
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
    <!-- 🎨 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    
    
    
    <!-- 봉투 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css">
    <!-- 통합 가격 표시 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통일 인라인 폼 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- jQuery 라이브러리 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- PrintNet 스텁 (explane05.php의 PrintNet 참조 오류 방지) -->
    <script src="js/printnet-stub.js"></script>

    <!-- 통합 갤러리 JavaScript 라이브러리 -->
    <script src="../NameCard/js/unified-gallery.js"></script>
    <script src="../../js/unified-gallery-popup.js"></script>
    
    <!-- 봉투 전용 JavaScript -->
    <script src="../../js/envelope.js" defer></script>
    
    
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

    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/envelope-inline-extracted.css">
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">

    <!-- 📱 견적서 모달 모드 공통 CSS (전 제품 공통) -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">
</head>
<body class="envelope-page<?php echo $body_class; ?>">
<?php if (!$is_quotation_mode): ?>
<?php include "../../includes/header-ui.php"; ?>
<?php include "../../includes/nav.php"; ?>
<?php endif; ?>

    <div class="product-container">

<?php if (!$is_quotation_mode): ?>
        <div class="page-title">
            <h1>✉️ 봉투 견적 안내</h1>
        </div>
<?php endif; ?>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
<?php if (!$is_quotation_mode): ?>
            <!-- 좌측: 통합 갤러리 시스템 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="봉투 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'envelope';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </section>
<?php endif; ?>

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="product-calculator">
                <div class="calculator-header">
                    <h3>💰견적 안내</h3>
                </div>

                <form id="envelopeForm">
                    <!-- 통일 인라인 폼 시스템 - 봉투 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">종류</label>
                            <select class="inline-select" name="MY_type" id="MY_type" required>
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "mlangprintauto_transactioncate", "Envelope");
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="inline-note">봉투 종류를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">재질</label>
                            <select class="inline-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 용지를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select class="inline-select" name="POtype" id="POtype" required>
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>단면</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>양면</option>
                            </select>
                            <span class="inline-note">단면 또는 양면 인쇄</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_amount">수량</label>
                            <select class="inline-select" name="MY_amount" id="MY_amount" onchange="onQuantityChange()" required data-default-value="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
                                <option value="">먼저 재질을 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하시는 수량을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="ordertype">편집비</label>
                            <select class="inline-select" name="ordertype" id="ordertype" required>
                                <option value="">선택해주세요</option>
                                <option value="print" <?php echo ($default_values['ordertype'] == 'print') ? 'selected' : ''; ?>>인쇄만 의뢰</option>
                                <option value="total" <?php echo ($default_values['ordertype'] == 'total') ? 'selected' : ''; ?>>디자인+인쇄</option>
                            </select>
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>

                    <!-- 양면테이프 옵션 섹션 -->
                    <div class="tape-option-section" style="margin: 7.5px 0; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e0e0e0;">
                        <div class="tape-option-header" style="display: flex; align-items: center; gap: 15px;">
                            <div class="tape-checkbox-group" style="display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" id="envelope_tape_enabled" name="envelope_tape_enabled" value="1" onchange="calculateTapePrice()">
                                <label for="envelope_tape_enabled" style="font-weight: 500; color: #495057; font-size: 0.85rem;">양면테이프</label>
                            </div>
                            <div class="tape-price-display" id="tapePriceDisplay" style="font-weight: bold; color: #28a745; font-size: 0.85rem;">(+0원)</div>
                        </div>

                        <input type="hidden" name="envelope_tape_price" id="envelope_tape_price" value="0">
                        <input type="hidden" name="envelope_additional_options_total" id="envelope_additional_options_total" value="0">
                    </div>

                    <!-- 스티커 방식의 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 - 프리미엄 스타일 -->
                    <div class="upload-order-button" id="uploadOrderButton">
                        <?php if ($is_quotation_mode): ?>
                            <!-- 견적서 모달 모드: 자동 계산 후 견적서 적용 버튼만 표시 -->
                            <button type="button" class="btn-upload-order" id="applyBtn" onclick="sendToQuotation()" style="background: #217346; display: none;">
                                ✅ 견적서에 적용
                            </button>
                        <?php else: ?>
                            <!-- 일반 모드: 파일 업로드 및 주문 -->
                            <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                                📎 파일 업로드 및 주문하기
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- 숨겨진 필드들 -->
                    <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
                    <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
                    <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
                    <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
                    <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
                    <input type="hidden" name="page" value="Envelope">
                </form>
            </div>
        </div>
    </div>

    <!-- 파일 업로드 모달 (통합 컴포넌트) -->
    <?php include "../../includes/upload_modal.php"; ?>
    <script src="../../includes/upload_modal.js?v=1759243573751415300"></script>

    <?php include "../../includes/login_modal.php"; ?>

<?php if (!$is_quotation_mode): ?>
    <!-- 옵셋봉투 및 작업 시 유의사항 통합 섹션 (1200px 폭) -->
    <div class="envelope-detail-combined" style="width: 1200px; max-width: 100%; margin: 30px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane05.php"; ?>
    </div>

    <?php include "../../includes/footer.php"; ?>
<?php endif; ?>

    <!-- 봉투 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    

    <script>
        // PHP 변수를 JavaScript로 전달 (PROJECT_SUCCESS_REPORT.md 스펙)
        window.phpVars = {
            MultyUploadDir: "../../PHPClass/MultyUpload",
            log_url: "<?php echo safe_html($log_info['url']); ?>",
            log_y: "<?php echo safe_html($log_info['y']); ?>",
            log_md: "<?php echo safe_html($log_info['md']); ?>",
            log_ip: "<?php echo safe_html($log_info['ip']); ?>",
            log_time: "<?php echo safe_html($log_info['time']); ?>",
            page: "Envelope",
            defaultValues: {
                MY_type: "<?php echo safe_html($default_values['MY_type']); ?>",
                Section: "<?php echo safe_html($default_values['Section']); ?>",
                POtype: "<?php echo safe_html($default_values['POtype']); ?>",
                MY_amount: "<?php echo safe_html($default_values['MY_amount']); ?>",
                ordertype: "<?php echo safe_html($default_values['ordertype']); ?>"
            }
        };

        // envelope.js에서 전역 변수와 초기화 함수들을 처리
        // 고급 갤러리 시스템 자동 로드
        
        <?php if (!$is_quotation_mode): ?>
        // 통일된 갤러리 팝업 초기화
        let unifiedEnvelopeGallery;
        
        // 페이지 로드 시 초기화 (견적서 모달에서는 갤러리 제외)
        document.addEventListener('DOMContentLoaded', function() {
            // 통일된 갤러리 팝업 초기화
            unifiedEnvelopeGallery = new UnifiedGalleryPopup({
                category: 'envelope',
                apiUrl: '/api/get_real_orders_portfolio.php',
                title: '봉투 전체 갤러리',
                icon: '✉️',
                perPage: 18
            });
            
            // 메인 갤러리 API 방식으로 로딩 (전단지와 동일한 방법)
            loadEnvelopeImagesAPI();
        });
        
        // 🎯 성공했던 API 방식으로 봉투 갤러리 로드 (전단지와 동일)
        async function loadEnvelopeImagesAPI() {
            const galleryContainer = document.getElementById('envelopeGallery');
            if (!galleryContainer) return;
            
            console.log('✉️ 봉투 갤러리 API 로딩 중...');
            galleryContainer.innerHTML = '<div class="loading">✉️ 갤러리 로딩 중...</div>';
            
            try {
                const response = await fetch('/api/get_real_orders_portfolio.php?category=envelope&per_page=4');
                const data = await response.json();
                
                console.log('✉️ 봉투 API 응답:', data);
                
                if (data.success && data.data && data.data.length > 0) {
                    console.log(`✅ 봉투 이미지 ${data.data.length}개 로드 성공`);
                    renderEnvelopeGalleryAPI(data.data, galleryContainer);
                } else {
                    console.log('⚠️ 봉투 이미지 데이터 없음');
                    galleryContainer.innerHTML = '<div class="error">표시할 이미지가 없습니다.</div>';
                }
            } catch (error) {
                console.error('❌ 봉투 갤러리 로딩 오류:', error);
                galleryContainer.innerHTML = '<div class="error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
            }
        }
        
        // API 갤러리 렌더링 (전단지 방식과 동일)
        function renderEnvelopeGalleryAPI(images, container) {
            console.log('🎨 봉투 갤러리 렌더링:', images.length + '개 이미지');
            
            // lightboxViewer div 생성 (봉투용)
            const viewerHtml = `
                <div class="lightbox-viewer" id="envelopeLightboxViewer">
                    <img id="envelopeMainImage" src="${images[0].path}" alt="${images[0].title}" 
                         style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;"
                         onclick="openFullScreenImage('${images[0].path}', '${images[0].title}')">
                </div>
                <div class="thumbnail-strip">
                    ${images.map((img, index) => 
                        `<img src="${img.path}" alt="${img.title}" class="${index === 0 ? 'active' : ''}"
                             onclick="changeEnvelopeMainImage('${img.path}', '${img.title}', this)">` 
                    ).join('')}
                </div>
            `;
            
            container.innerHTML = viewerHtml;
            
            // 봉투 마우스 호버 효과 적용 (전단지와 동일)
            initializeEnvelopeZoomEffect();
        }
        
        // 봉투 메인 이미지 변경 함수
        function changeEnvelopeMainImage(imagePath, title, thumbnail) {
            const mainImage = document.getElementById('envelopeMainImage');
            if (mainImage) {
                mainImage.src = imagePath;
                mainImage.alt = title;
                mainImage.onclick = () => openFullScreenImage(imagePath, title);
            }
            
            // 썸네일 활성 상태 업데이트
            const thumbnails = document.querySelectorAll('.thumbnail-strip img');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
            
            // 줌 효과 재초기화
            initializeEnvelopeZoomEffect();
        }
        
        // 봉투 이미지 줌 효과 초기화 (전단지 방식과 동일)
        function initializeEnvelopeZoomEffect() {
            const viewer = document.getElementById('envelopeLightboxViewer');
            const mainImage = document.getElementById('envelopeMainImage');
            
            if (!viewer || !mainImage) return;
            
            // 기존 이벤트 리스너 제거 후 재등록
            const newViewer = viewer.cloneNode(true);
            viewer.parentNode.replaceChild(newViewer, viewer);
            
            const newMainImage = document.getElementById('envelopeMainImage');
            if (!newMainImage) return;
            
            let isZoomed = false;
            
            newViewer.addEventListener('mousemove', function(e) {
                if (isZoomed) return;

                const rect = newViewer.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                // CSS 클래스로 줌 적용 (unified-gallery.css의 scale(2.0) 자동 적용)
                newViewer.classList.add('zoom-active');
                newMainImage.style.transformOrigin = `${x}% ${y}%`;
            });

            newViewer.addEventListener('mouseleave', function() {
                if (isZoomed) return;
                // CSS 클래스 제거로 줌 해제
                newViewer.classList.remove('zoom-active');
                newMainImage.style.transformOrigin = 'center center';
            });
            
            newViewer.addEventListener('click', function(e) {
                if (e.target === newMainImage) {
                    const imagePath = newMainImage.src;
                    const title = newMainImage.alt;
                    openFullScreenImage(imagePath, title);
                }
            });
        }
        
        // 통일된 갤러리 팝업 열기 (전단지와 동일한 시스템)
        // 공통 갤러리 팝업 함수 사용 (common-gallery-popup.js)
        const openProofPopup = window.openGalleryPopup;
        
        // 전체화면 이미지 열기
        function openFullScreenImage(imagePath, title) {
            if (window.lightboxViewer && window.lightboxViewer.showLightbox) {
                window.lightboxViewer.showLightbox(imagePath, title);
            } else {
                window.open(imagePath, '_blank');
            }
        }
        <?php endif; // 갤러리 관련 스크립트 끝 ?>

        // 양면테이프 옵션 관련 함수들
        function calculateTapePrice() {
            const tapeEnabled = document.getElementById('envelope_tape_enabled')?.checked;
            const mainQuantity = parseInt(document.getElementById('MY_amount')?.value) || 0;
            const tapePriceDisplay = document.getElementById('tapePriceDisplay');
            const tapePriceField = document.getElementById('envelope_tape_price');
            const additionalOptionsField = document.getElementById('envelope_additional_options_total');

            let tapePrice = 0;

            if (tapeEnabled && mainQuantity > 0) {
                if (mainQuantity === 500) {
                    tapePrice = 25000; // 500매: 25,000원 고정
                } else {
                    tapePrice = mainQuantity * 40; // 기타 수량: 수량 × 40원
                }
            }

            // 화면에 가격 표시 업데이트
            if (tapePriceDisplay) {
                tapePriceDisplay.textContent = tapePrice > 0 ? `(+${tapePrice.toLocaleString()}원)` : '(+0원)';
            }

            // 숨겨진 필드 업데이트
            if (tapePriceField) tapePriceField.value = tapePrice;
            if (additionalOptionsField) additionalOptionsField.value = tapePrice;

            // 메인 가격 계산 다시 실행 (envelope.js의 함수 호출)
            if (typeof calculatePrice === 'function') {
                calculatePrice();
            }
        }

        // 메인 수량 변경 시 테이프 옵션도 업데이트
        function onQuantityChange() {
            const tapeEnabled = document.getElementById('envelope_tape_enabled')?.checked;
            if (tapeEnabled) {
                calculateTapePrice(); // 테이프 가격 다시 계산
            }
            // envelope.js의 기본 계산 함수도 호출
            if (typeof calculatePrice === 'function') {
                calculatePrice();
            }
        }
        // 봉투 전용 장바구니 추가 함수 (통합 모달 패턴)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
            console.log("봉투 장바구니 추가 시작");

            if (!window.currentPriceData) {
                console.error("가격 계산이 필요합니다");
                if (onError) onError("먼저 가격을 계산해주세요.");
                return;
            }

            const formData = new FormData();
            formData.append("action", "add_to_basket");
            formData.append("product_type", "envelope");
            formData.append("MY_type", document.getElementById("MY_type").value);
            formData.append("Section", document.getElementById("Section").value);
            formData.append("POtype", document.getElementById("POtype").value);
            formData.append("MY_amount", document.getElementById("MY_amount").value);
            formData.append("ordertype", document.getElementById("ordertype").value);
            formData.append("calculated_price", Math.round(window.currentPriceData.total_price));
            formData.append("calculated_vat_price", Math.round(window.currentPriceData.vat_price));

            // 양면테이프 옵션 추가
            const tapeEnabled = document.getElementById("envelope_tape_enabled")?.checked;
            const tapePrice = parseInt(document.getElementById("envelope_tape_price")?.value) || 0;
            formData.append("envelope_tape_enabled", tapeEnabled ? "1" : "0");
            formData.append("envelope_tape_price", tapePrice);

            const workMemo = document.getElementById("modalWorkMemo");
            if (workMemo) formData.append("work_memo", workMemo.value);

            formData.append("upload_method", window.selectedUploadMethod || "upload");

            if (uploadedFiles && uploadedFiles.length > 0) {
                uploadedFiles.forEach((file, index) => {
                    formData.append("uploaded_files[" + index + "]", file);
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

        /**
         * 견적서 모달에서 견적서로 데이터 전송
         */
        window.sendToQuotation = function() {
            console.log('📤 [TUNNEL 2/5] "✅ 견적서에 적용" 버튼 클릭됨');

            // window.currentPriceData 또는 로컬 currentPriceData 변수 확인
            const priceData = window.currentPriceData || (typeof currentPriceData !== 'undefined' ? currentPriceData : null);
            console.log('📊 가격 데이터 확인:', priceData);

            if (!priceData || !priceData.total_price) {
                alert('먼저 견적 계산을 해주세요.');
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '📝 견적서에 입력 중...';

            try {
                // 제품 스펙 정보 수집 (봉투 전용)
                const myTypeSelect = document.getElementById('MY_type');
                const sectionSelect = document.getElementById('Section');  // ✅ PN_type → Section으로 수정
                const myAmountSelect = document.getElementById('MY_amount');
                const ordertypeSelect = document.getElementById('ordertype');

                const typeText = myTypeSelect ? myTypeSelect.options[myTypeSelect.selectedIndex].text : '';
                const sectionText = sectionSelect ? sectionSelect.options[sectionSelect.selectedIndex].text : '';  // ✅ 용지 규격
                const quantityText = myAmountSelect ? myAmountSelect.options[myAmountSelect.selectedIndex].text : '';
                const ordertypeText = ordertypeSelect ? ordertypeSelect.options[ordertypeSelect.selectedIndex].text : '';

                // ✅ 추가옵션 (양면테이프) 수집
                const additionalOptions = [];
                const tapeEnabled = document.getElementById('envelope_tape_enabled');
                if (tapeEnabled && tapeEnabled.checked) {
                    additionalOptions.push('양면테이프');
                }

                // 규격 문자열 생성 (추가옵션 포함)
                let specification = `${typeText} / ${sectionText} / ${quantityText} / ${ordertypeText}`.trim();
                if (additionalOptions.length > 0) {
                    specification += ' + ' + additionalOptions.join(', ');
                }
                const quantityValue = parseInt(myAmountSelect.value) || 1000;

                const quotationData = {
                    product_name: '봉투',
                    specification: specification,
                    quantity: quantityValue,
                    unit: '개',
                    supply_price: parseInt(priceData.total_price) || 0,
                    vat_price: parseInt(priceData.total_with_vat || priceData.vat_price) || 0
                };

                console.log('📨 [TUNNEL 3/5] 견적서 데이터 전송:', quotationData);

                window.parent.postMessage({
                    type: 'CALCULATOR_PRICE_DATA',
                    payload: quotationData
                }, window.location.origin);

                btn.innerHTML = '✅ 견적서에 적용됨!';
                btn.style.background = '#28a745';

                console.log('✅ [TUNNEL 5/5] 견적서 폼 입력 완료');

            } catch (error) {
                console.error('❌ 견적서 데이터 전송 실패:', error);
                alert('견적서 적용 중 오류가 발생했습니다: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.style.background = '#217346';
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
                        const POtype = document.getElementById('POtype')?.value;
                        const MY_amount = document.getElementById('MY_amount')?.value;
                        const ordertype = document.getElementById('ordertype')?.value;

                        // 모든 필드가 채워져 있으면 자동 계산
                        if (MY_type && Section && POtype && MY_amount && ordertype) {
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

    </script>

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