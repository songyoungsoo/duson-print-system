<?php
// 보안 상수 정의 후 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 공통 인증 시스템
include "../../includes/auth.php";

// 견적서 모달용 간소화 모드 체크
$isQuotationMode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';

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
    
    
    
    
    <!-- 자석스티커 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css">
    <link rel="stylesheet" href="../../css/unified-gallery.css">
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
    <script src="../../includes/js/GalleryLightbox.js"></script>
    <script src="../../js/msticker.js" defer></script>
    
    <!-- 통일된 갤러리 팝업 JavaScript -->
    <script src="../../js/unified-gallery-popup.js"></script>
    
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    
    <!-- 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    
    <!-- 업로드 컴포넌트 JavaScript 라이브러리 포함 -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>
    <!-- 공통 갤러리 팝업 함수 -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/msticker-inline-extracted.css">
    <!-- 브랜드 디자인 시스템 (헤더 스타일) -->
    <link rel="stylesheet" href="../../css/brand-design-system.css">
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
    <!-- 견적서 모달용 공통 스타일 -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">
</head>
<body class="msticker-page<?php echo $isQuotationMode ? ' quotation-modal-mode' : ''; ?>">
    <?php if (!$isQuotationMode) include "../../includes/header-ui.php"; ?>
    <?php if (!$isQuotationMode) include "../../includes/nav.php"; ?>

    <div class="product-container">
        <div class="page-title">
            <h1>🧲 자석스티커 견적 안내</h1>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
            <!-- 좌측: 통합 갤러리 시스템 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="자석스티커 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'msticker';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
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

                    <?php if ($isQuotationMode): ?>
                    <!-- 견적서 모달 모드: 견적서에 적용 버튼 -->
                    <div class="quotation-apply-button">
                        <button type="button" class="btn-quotation-apply" onclick="applyToQuotation()">
                            ✓ 견적서에 적용
                        </button>
                    </div>
                    <?php else: ?>
                    <!-- 일반 모드: 파일 업로드 및 주문하기 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            📎 파일 업로드 및 주문하기
                        </button>
                    </div>
                    <?php endif; ?>

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

    <?php if (!$isQuotationMode): ?>
    <!-- 종이자석스티커 상세 설명 섹션 (하단 설명방법) -->
    <div class="msticker-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_msticker.php"; ?>
    </div>
    <?php endif; ?>

    <!-- 갤러리 더보기 모달 -->
    <div id="mstickerGalleryModal" class="gallery-modal" style="display: none;">
        <div class="gallery-modal-overlay" onclick="closeMStickerGalleryModal()"></div>
        <div class="gallery-modal-content">
            <div class="gallery-modal-header">
                <h3 class="gallery-modal-title">🔰 자석스티커 전체 갤러리</h3>
                <button type="button" class="gallery-modal-close" onclick="closeMStickerGalleryModal()">✕</button>
            </div>
            
            <div class="gallery-modal-body">
                <div class="gallery-grid" id="mstickerGalleryModalGrid">
                    <div class="gallery-loading">갤러리를 불러오는 중...</div>
                </div>
                
                <!-- 페이지네이션 -->
                <div class="gallery-pagination" id="mstickerPagination" style="display: none;">
                    <div class="pagination-info">
                        <span id="mstickerPageInfo">페이지 1 / 1 (총 0개)</span>
                    </div>
                    <div class="pagination-controls">
                        <button id="mstickerPrevBtn" class="pagination-btn" onclick="loadMStickerPage('prev')" disabled>
                            ← 이전
                        </button>
                        <div class="pagination-numbers" id="mstickerPageNumbers">
                        </div>
                        <button id="mstickerNextBtn" class="pagination-btn" onclick="loadMStickerPage('next')" disabled>
                            다음 →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
// 공통 푸터 포함 (견적서 모달에서는 제외)
if (!$isQuotationMode) {
    include "../../includes/footer.php";
}
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
        
        // 통일된 갤러리 팝업 초기화
        let unifiedMstickerGallery;
        
        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            // 통일된 갤러리 팝업 초기화
            unifiedMstickerGallery = new UnifiedGalleryPopup({
                category: 'msticker',
                apiUrl: '/api/get_real_orders_portfolio.php',
                title: '자석스티커 전체 갤러리',
                icon: '🧲',
                perPage: 18
            });
            
            // 메인 갤러리 API 방식으로 로딩 (전단지와 동일한 방법)
            loadMstickerImagesAPI();
        });
        
        // 🎯 성공했던 API 방식으로 자석스티커 갤러리 로드 (전단지와 동일)
        async function loadMstickerImagesAPI() {
            const galleryContainer = document.getElementById('mstickerGallery');
            if (!galleryContainer) return;
            
            console.log('🧲 자석스티커 갤러리 API 로딩 중...');
            galleryContainer.innerHTML = '<div class="loading">🧲 갤러리 로딩 중...</div>';
            
            try {
                const response = await fetch('/api/get_real_orders_portfolio.php?category=msticker&per_page=4');
                const data = await response.json();
                
                console.log('🧲 자석스티커 API 응답:', data);
                
                if (data.success && data.data && data.data.length > 0) {
                    console.log(`✅ 자석스티커 이미지 ${data.data.length}개 로드 성공`);
                    renderMstickerGalleryAPI(data.data, galleryContainer);
                } else {
                    console.log('⚠️ 자석스티커 이미지 데이터 없음');
                    galleryContainer.innerHTML = '<div class="error">표시할 이미지가 없습니다.</div>';
                }
            } catch (error) {
                console.error('❌ 자석스티커 갤러리 로딩 오류:', error);
                galleryContainer.innerHTML = '<div class="error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
            }
        }
        
        // API 갤러리 렌더링 (전단지 방식과 동일)
        function renderMstickerGalleryAPI(images, container) {
            console.log('🎨 자석스티커 갤러리 렌더링:', images.length + '개 이미지');
            
            // lightboxViewer div 생성 (자석스티커용)
            const viewerHtml = `
                <div class="lightbox-viewer" id="mstickerLightboxViewer">
                    <img id="mstickerMainImage" src="${images[0].path}" alt="${images[0].title}" 
                         style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;"
                         onclick="openFullScreenImage('${images[0].path}', '${images[0].title}')">
                </div>
                <div class="thumbnail-strip">
                    ${images.map((img, index) => 
                        `<img src="${img.path}" alt="${img.title}" class="${index === 0 ? 'active' : ''}"
                             onclick="changeMstickerMainImage('${img.path}', '${img.title}', this)">` 
                    ).join('')}
                </div>
            `;
            
            container.innerHTML = viewerHtml;
            
            // 자석스티커 마우스 호버 효과 적용 (전단지와 동일)
            initializeMstickerZoomEffect();
        }
        
        // 자석스티커 메인 이미지 변경 함수
        function changeMstickerMainImage(imagePath, title, thumbnail) {
            const mainImage = document.getElementById('mstickerMainImage');
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
            initializeMstickerZoomEffect();
        }
        
        // 자석스티커 이미지 줌 효과 초기화 (전단지 방식과 동일)
        function initializeMstickerZoomEffect() {
            const viewer = document.getElementById('mstickerLightboxViewer');
            const mainImage = document.getElementById('mstickerMainImage');
            
            if (!viewer || !mainImage) return;
            
            // 기존 이벤트 리스너 제거 후 재등록
            const newViewer = viewer.cloneNode(true);
            viewer.parentNode.replaceChild(newViewer, viewer);
            
            const newMainImage = document.getElementById('mstickerMainImage');
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
        
        // 통일된 갤러리 팝업 열기
        // 통일된 팝업 열기 함수 (전단지와 동일한 시스템)
        // 공통 갤러리 팝업 함수 사용 (common-gallery-popup.js)
        const openProofPopup = window.openGalleryPopup;
        
        function openMstickerGalleryModal() {
            if (unifiedMstickerGallery) {
                unifiedMstickerGallery.show();
            }
        }
        
        // 전체화면 이미지 열기
        function openFullScreenImage(imagePath, title) {
            if (window.lightboxViewer && window.lightboxViewer.showLightbox) {
                window.lightboxViewer.showLightbox(imagePath, title);
            } else {
                window.open(imagePath, '_blank');
            }
        }
        
        // 갤러리 모달 제어 함수들 (기존 코드 호환성)
        function openMStickerGalleryModal() {
            openMstickerGalleryModal(); // 새로운 통일된 방식으로 리다이렉트
        }
        
        function closeMStickerGalleryModal() {
            const modal = document.getElementById('mstickerGalleryModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // 페이지네이션 변수
        let mstickerCurrentPage = 1;
        let mstickerPaginationData = null;
        
        function loadMStickerFullGallery(page = 1) {
            const galleryGrid = document.getElementById('mstickerGalleryModalGrid');
            if (!galleryGrid) return;
            
            galleryGrid.innerHTML = '<div class="gallery-loading">갤러리를 불러오는 중...</div>';
            
            fetch(`/api/get_real_orders_portfolio.php?category=msticker&all=true&page=${page}&per_page=12`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        mstickerCurrentPage = page;
                        mstickerPaginationData = data.pagination;
                        
                        if (data.data.length > 0) {
                            renderMStickerFullGallery(data.data, galleryGrid);
                            updateMStickerPagination(data.pagination);
                        } else {
                            galleryGrid.innerHTML = '<div class="gallery-empty">표시할 이미지가 없습니다.</div>';
                            hideMStickerPagination();
                        }
                    } else {
                        galleryGrid.innerHTML = '<div class="gallery-empty">표시할 이미지가 없습니다.</div>';
                        hideMStickerPagination();
                    }
                })
                .catch(error => {
                    console.error('Gallery loading error:', error);
                    galleryGrid.innerHTML = '<div class="gallery-error">갤러리를 불러오는 중 오류가 발생했습니다.</div>';
                    hideMStickerPagination();
                });
        }
        
        function updateMStickerPagination(pagination) {
            if (!pagination || pagination.total_pages <= 1) {
                hideMStickerPagination();
                return;
            }
            
            const paginationContainer = document.getElementById('mstickerPagination');
            const pageInfo = document.getElementById('mstickerPageInfo');
            const pageNumbers = document.getElementById('mstickerPageNumbers');
            const prevBtn = document.getElementById('mstickerPrevBtn');
            const nextBtn = document.getElementById('mstickerNextBtn');
            
            if (!paginationContainer || !pageInfo || !pageNumbers || !prevBtn || !nextBtn) return;
            
            // 페이지 정보 업데이트
            pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count}개)`;
            
            // 이전/다음 버튼 상태
            prevBtn.disabled = !pagination.has_prev;
            nextBtn.disabled = !pagination.has_next;
            
            // 페이지 번호 생성
            pageNumbers.innerHTML = generateMStickerPageNumbers(pagination);
            
            // 페이지네이션 표시
            paginationContainer.style.display = 'block';
        }
        
        function generateMStickerPageNumbers(pagination) {
            let html = '';
            const current = pagination.current_page;
            const total = pagination.total_pages;
            
            // 간단한 페이지 번호 생성 (1, 2, 3... 형태)
            const startPage = Math.max(1, current - 2);
            const endPage = Math.min(total, current + 2);
            
            if (startPage > 1) {
                html += `<span class="pagination-number" onclick="loadMStickerPage(1)">1</span>`;
                if (startPage > 2) {
                    html += `<span class="pagination-ellipsis">...</span>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === current ? 'active' : '';
                html += `<span class="pagination-number ${activeClass}" onclick="loadMStickerPage(${i})">${i}</span>`;
            }
            
            if (endPage < total) {
                if (endPage < total - 1) {
                    html += `<span class="pagination-ellipsis">...</span>`;
                }
                html += `<span class="pagination-number" onclick="loadMStickerPage(${total})">${total}</span>`;
            }
            
            return html;
        }
        
        function hideMStickerPagination() {
            const paginationContainer = document.getElementById('mstickerPagination');
            if (paginationContainer) {
                paginationContainer.style.display = 'none';
            }
        }
        
        function loadMStickerPage(pageOrDirection) {
            let targetPage;
            
            if (pageOrDirection === 'prev') {
                targetPage = Math.max(1, mstickerCurrentPage - 1);
            } else if (pageOrDirection === 'next') {
                targetPage = mstickerCurrentPage + 1;
            } else {
                targetPage = parseInt(pageOrDirection);
            }
            
            if (targetPage !== mstickerCurrentPage) {
                loadMStickerFullGallery(targetPage);
            }
        }
        
        function renderMStickerFullGallery(images, container) {
            let html = '';
            images.forEach((image, index) => {
                html += `
                    <div class="gallery-item" onclick="openLightbox('${image.path}', '${image.title}')">
                        <img src="${image.path}" alt="${image.title}" loading="lazy" 
                             onerror="this.parentElement.style.display='none'">
                        <div class="gallery-item-title">${image.title}</div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }
        
        function openLightbox(imagePath, title) {
            // 기존 GalleryLightbox 시스템과 연동
            if (window.lightboxViewer && window.lightboxViewer.showLightbox) {
                window.lightboxViewer.showLightbox(imagePath, title);
            } else {
                // 기본 동작: 새 창으로 이미지 열기
                window.open(imagePath, '_blank');
            }
        }
    </script>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js"></script>

<?php
// 데이터베이스 연결 종료
if ($db) {
    mysqli_close($db);
}
?>
</body>
</html>
