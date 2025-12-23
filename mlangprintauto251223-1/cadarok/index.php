<?php
/**
 * 카다록/리플렛 견적안내 컴팩트 시스템 - PROJECT_SUCCESS_REPORT.md 스펙 구현
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 8월 (AI Assistant - Frontend Persona)
 */

// 공통 인증 및 설정
include "../../includes/auth.php";

// 견적서 모달용 간소화 모드 체크
$isQuotationMode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';

// 공통 함수 및 데이터베이스
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("카다록/리플렛 견적안내 컴팩트 - 프리미엄");

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

// 첫 번째 카다록 종류 가져오기
$type_query = "SELECT no, title FROM mlangprintauto_transactioncate 
               WHERE Ttable='cadarok' AND BigNo='0' 
               ORDER BY no ASC 
               LIMIT 1";
$type_result = mysqli_query($db, $type_query);
if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
    $default_values['MY_type'] = $type_row['no'];
    
    // 해당 카다록 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                      WHERE Ttable='cadarok' AND BigNo='" . $type_row['no'] . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
        
        // 해당 조합의 기본 수량 가져오기 (500매 우선)
        $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_cadarok 
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
    <!-- 🎨 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    
    
    
    <!-- 카다록 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/product-layout.css">
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통합 가격 표시 시스템 -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    <!-- 통합 인라인 폼 스타일 시스템 -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- 고급 JavaScript 라이브러리 (적응형 이미지 분석 및 실시간 계산) -->
    <script src="../../includes/js/GalleryLightbox.js"></script>
    <script src="js/cadarok.js" defer></script>
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    <!-- 공통 갤러리 팝업 함수 -->

    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/cadarok-inline-extracted.css">
    <!-- 브랜드 디자인 시스템 (헤더 스타일) -->
    <link rel="stylesheet" href="../../css/brand-design-system.css">
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
    <!-- 견적서 모달용 공통 스타일 -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">
</head>
<body class="cadarok-page<?php echo $isQuotationMode ? ' quotation-modal-mode' : ''; ?>">
    <?php if (!$isQuotationMode) include "../../includes/header-ui.php"; ?>
    <?php if (!$isQuotationMode) include "../../includes/nav.php"; ?>

    <div class="product-container">
    
    
        <div class="page-title">
            <h1>📝 카다록/리플렛 견적 안내</h1>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
            <!-- 좌측: 통합 갤러리 시스템 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="카다록/리플렛 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'cadarok';
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

                <form id="cadarokForm">
                    <!-- 통일 인라인 폼 시스템 - 카다록 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">종류</label>
                            <select class="inline-select" name="MY_type" id="MY_type" required onchange="calculatePrice()">
                                <option value="">선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "mlangprintauto_transactioncate", "cadarok");
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="inline-note">카다록 종류를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">재질</label>
                            <select class="inline-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>" onchange="calculatePrice()">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 재질을 선택하세요</span>
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
                                <option value="">먼저 재질을 선택해주세요</option>
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
                    </div>

                    <!-- 통일된 가격 표시 시스템 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
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
                    <input type="hidden" name="page" value="cadarok">
                </form>
            </div>
        </div>
    </div>

    <!-- 파일 업로드 모달 (통합 컴포넌트) -->
    <?php include "../../includes/upload_modal.php"; ?>
    <script src="../../includes/upload_modal.js?v=1759243573751415300"></script>

    <!-- 갤러리 더보기 모달 - 공통 팝업으로 대체됨 (/popup/proof_gallery.php) -->
    <div id="cadarokGalleryModal" class="gallery-modal" style="display: none !important;">
        <div class="gallery-modal-overlay" onclick="closeCadarokGalleryModal()"></div>
        <div class="gallery-modal-content">
            <div class="gallery-modal-header">
                <h3>🖼️ 카다록/리플렛 갤러리 (전체)</h3>
                <button type="button" class="gallery-modal-close" onclick="closeCadarokGalleryModal()">✕</button>
            </div>
            <div class="gallery-modal-body">
                <div id="cadarokGalleryModalGrid" class="gallery-grid">
                    <!-- JavaScript로 동적 로드됨 -->
                </div>
                
                <!-- 페이지네이션 UI -->
                <div class="gallery-pagination" id="cadarokPagination" style="display: none;">
                    <div class="pagination-info">
                        <span id="cadarokPageInfo">페이지 1 / 1 (총 0개)</span>
                    </div>
                    <div class="pagination-controls">
                        <button id="cadarokPrevBtn" class="pagination-btn" onclick="loadCadarokPage('prev')" disabled>
                            ← 이전
                        </button>
                        <div class="pagination-numbers" id="cadarokPageNumbers"></div>
                        <button id="cadarokNextBtn" class="pagination-btn" onclick="loadCadarokPage('next')" disabled>
                            다음 →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../includes/login_modal.php"; ?>

    <?php if (!$isQuotationMode): ?>
    <!-- 리플렛/팜플렛 상세 설명 섹션 (하단 설명방법) -->
    <div class="cadarok-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_cadarok.php"; ?>
    </div>
    <?php endif; ?>

    <?php
    // 공통 푸터 포함 (견적서 모달에서는 제외)
    if (!$isQuotationMode) {
        include "../../includes/footer.php";
    }
    ?>

    <!-- 카다록 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    

    <script>
        // PHP 변수를 JavaScript로 전달 (PROJECT_SUCCESS_REPORT.md 스펙)
        window.phpVars = {
            MultyUploadDir: "../../PHPClass/MultyUpload",
            log_url: "<?php echo safe_html($log_info['url']); ?>",
            log_y: "<?php echo safe_html($log_info['y']); ?>",
            log_md: "<?php echo safe_html($log_info['md']); ?>",
            log_ip: "<?php echo safe_html($log_info['ip']); ?>",
            log_time: "<?php echo safe_html($log_info['time']); ?>",
            page: "cadarok",
            defaultValues: {
                MY_type: "<?php echo safe_html($default_values['MY_type']); ?>",
                Section: "<?php echo safe_html($default_values['Section']); ?>",
                POtype: "<?php echo safe_html($default_values['POtype']); ?>",
                MY_amount: "<?php echo safe_html($default_values['MY_amount']); ?>",
                ordertype: "<?php echo safe_html($default_values['ordertype']); ?>"
            }
        };

        // cadarok.js에서 전역 변수와 초기화 함수들을 처리
        // PROJECT_SUCCESS_REPORT.md 스펙에 따른 고급 갤러리 시스템 자동 로드
        
        // 갤러리 모달 제어 함수들 (페이지네이션 지원)
        let cadarokCurrentPage = 1;
        let cadarokTotalPages = 1;
        
        // 통일된 팝업 열기 함수 (전단지와 동일한 시스템)
        const openProofPopup = window.openGalleryPopup;
        
        function openCadarokGalleryModal() {
            // 공통 갤러리 팝업으로 리다이렉트
            if (typeof window.openGalleryPopup === 'function') {
                window.openGalleryPopup('카탈로그');
            } else {
                console.error('openGalleryPopup 함수를 찾을 수 없습니다.');
            }
        }
        
        function closeCadarokGalleryModal() {
            const modal = document.getElementById('cadarokGalleryModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // 카다록 갤러리 페이지 로드 함수
        function loadCadarokPage(page) {
            if (typeof page === 'string') {
                if (page === 'prev') {
                    page = Math.max(1, cadarokCurrentPage - 1);
                } else if (page === 'next') {
                    page = Math.min(cadarokTotalPages, cadarokCurrentPage + 1);
                } else {
                    page = parseInt(page);
                }
            }
            
            if (page === cadarokCurrentPage) return;
            
            const gallery = document.getElementById('cadarokGalleryModalGrid');
            if (!gallery) return;
            
            // 로딩 표시
            gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><div style="font-size: 1.5rem;">⏳</div><p>이미지를 불러오는 중...</p></div>';
            
            // API 호출
            fetch(`/api/get_real_orders_portfolio.php?category=cadarok&all=true&page=${page}&per_page=12`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // 갤러리 업데이트
                        renderCadarokFullGallery(data.data, gallery);
                        
                        // 페이지네이션 정보 업데이트
                        cadarokCurrentPage = data.pagination.current_page;
                        cadarokTotalPages = data.pagination.total_pages;
                        
                        // 페이지네이션 UI 업데이트
                        updateCadarokPagination(data.pagination);
                    } else {
                        gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><p>이미지를 불러올 수 없습니다.</p></div>';
                    }
                })
                .catch(error => {
                    console.error('카다록 이미지 로드 오류:', error);
                    gallery.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><p>이미지 로드 중 오류가 발생했습니다.</p></div>';
                });
        }
        
        // 페이지네이션 UI 업데이트
        function updateCadarokPagination(pagination) {
            // 페이지 정보 업데이트
            const pageInfo = document.getElementById('cadarokPageInfo');
            if (pageInfo) {
                pageInfo.textContent = `페이지 ${pagination.current_page} / ${pagination.total_pages} (총 ${pagination.total_count}개)`;
            }
            
            // 버튼 상태 업데이트
            const prevBtn = document.getElementById('cadarokPrevBtn');
            const nextBtn = document.getElementById('cadarokNextBtn');
            
            if (prevBtn) {
                prevBtn.disabled = !pagination.has_prev;
            }
            if (nextBtn) {
                nextBtn.disabled = !pagination.has_next;
            }
            
            // 페이지 번호 버튼 생성
            const pageNumbers = document.getElementById('cadarokPageNumbers');
            if (pageNumbers) {
                pageNumbers.innerHTML = '';
                
                const startPage = Math.max(1, pagination.current_page - 2);
                const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
                
                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.className = 'pagination-number' + (i === pagination.current_page ? ' active' : '');
                    pageBtn.textContent = i;
                    pageBtn.onclick = () => loadCadarokPage(i);
                    pageNumbers.appendChild(pageBtn);
                }
            }
            
            // 페이지네이션 섹션 표시
            const paginationSection = document.getElementById('cadarokPagination');
            if (paginationSection) {
                paginationSection.style.display = pagination.total_pages > 1 ? 'block' : 'none';
            }
        }
        
        function renderCadarokFullGallery(images, container) {
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
        // 카다록 전용 장바구니 추가 함수 (통합 모달 패턴)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
            console.log("카다록 장바구니 추가 시작");

            if (!window.currentPriceData) {
                console.error("가격 계산이 필요합니다");
                if (onError) onError("먼저 가격을 계산해주세요.");
                return;
            }

            const formData = new FormData();
            formData.append("action", "add_to_basket");
            formData.append("product_type", "cadarok");
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
    </script>

    <!-- 카다록 추가 옵션 시스템 -->
    <script src="js/cadarok-premium-options.js"></script>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js?v=<?php echo time(); ?>"></script>

    <?php
    // 데이터베이스 연결 종료
    if ($db) {
        mysqli_close($db);
    }
    ?>
</body>
</html>