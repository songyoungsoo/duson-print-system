<?php
// 테마 시스템 로드
include_once __DIR__ . '/../../includes/theme_loader.php';

/**
 * 카다록/리플렛 견적안내 컴팩트 시스템 - PROJECT_SUCCESS_REPORT.md 스펙 구현
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 8월 (AI Assistant - Frontend Persona)
 */

// 공통 인증 및 설정
include "../../includes/auth.php";

require_once __DIR__ . '/../../includes/mode_helper.php';

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
$page_title = generate_page_title("카다록/리플렛 견적안내 컴팩트 - 프리미엄");

// URL 파라미터로 종류/재질 사전 선택 (네비게이션 드롭다운에서 진입 시)
$url_type = isset($_GET['type']) ? intval($_GET['type']) : 0;
$url_section = isset($_GET['section']) ? intval($_GET['section']) : 0;

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 단면
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

if ($url_type) {
    // URL 파라미터로 진입: 지정된 종류 사용
    $default_values['MY_type'] = $url_type;
    if ($url_section) {
        $default_values['Section'] = $url_section;
    } else {
        $sec_q = "SELECT no FROM mlangprintauto_transactioncate 
                  WHERE Ttable='cadarok' AND BigNo='" . intval($url_type) . "' 
                  ORDER BY no ASC LIMIT 1";
        $sec_r = mysqli_query($db, $sec_q);
        if ($sec_r && ($sec_row = mysqli_fetch_assoc($sec_r))) {
            $default_values['Section'] = $sec_row['no'];
        }
    }
} else {
    // 기본 진입: 첫 번째 카다록 종류 가져오기
    $type_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                   WHERE Ttable='cadarok' AND BigNo='0' 
                   ORDER BY no ASC 
                   LIMIT 1";
    $type_result = mysqli_query($db, $type_query);
    if ($type_result && ($type_row = mysqli_fetch_assoc($type_result))) {
        $default_values['MY_type'] = $type_row['no'];
        
        $section_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                          WHERE Ttable='cadarok' AND BigNo='" . $type_row['no'] . "' 
                          ORDER BY no ASC LIMIT 1";
        $section_result = mysqli_query($db, $section_query);
        if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
            $default_values['Section'] = $section_row['no'];
        }
    }
}

// 수량 기본값
if ($default_values['MY_type'] && $default_values['Section']) {
    $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_cadarok 
                      WHERE style='" . intval($default_values['MY_type']) . "' AND Section='" . intval($default_values['Section']) . "' 
                      ORDER BY CASE WHEN quantity='500' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
                      LIMIT 1";
    $quantity_result = mysqli_query($db, $quantity_query);
    if ($quantity_result && ($quantity_row = mysqli_fetch_assoc($quantity_result))) {
        $default_values['MY_amount'] = $quantity_row['quantity'];
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
    <title>카탈로그 제작 | 카다록 인쇄 - 두손기획인쇄</title>
    <meta name="description" content="카탈로그·브로슈어 인쇄 전문 두손기획인쇄. 중철·무선 제본 카다록 맞춤 제작. 소량부터 대량까지. 실시간 견적 확인. 서울 영등포구.">
    <meta name="keywords" content="카탈로그 인쇄, 카다록 제작, 브로슈어 인쇄, 제품 카탈로그, 중철 제본, 카다록 가격">
    <link rel="canonical" href="https://dsp114.com/mlangprintauto/cadarok/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="카탈로그 제작 | 카다록 인쇄 - 두손기획인쇄">
    <meta property="og:description" content="카탈로그·브로슈어 인쇄 전문. 중철·무선 제본 맞춤 제작. 소량부터 대량까지.">
    <meta property="og:url" content="https://dsp114.com/mlangprintauto/cadarok/">
    <meta property="og:image" content="https://dsp114.com/ImgFolder/og-image.png">
    <meta property="og:site_name" content="두손기획인쇄">

    <!-- 카다록 컴팩트 페이지 전용 CSS (PROJECT_SUCCESS_REPORT.md 스펙) -->
    <link rel="stylesheet" href="../../css/product-layout.css?v=<?php echo filemtime(__DIR__ . '/../../css/product-layout.css'); ?>">
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    <!-- 통합 가격 표시 시스템 -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    <!-- 통합 인라인 폼 스타일 시스템 -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css?v=<?php echo filemtime(__DIR__ . '/../../css/unified-inline-form.css'); ?>">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css?v=<?php echo filemtime(__DIR__ . '/../../css/additional-options.css'); ?>">

    <!-- 고급 JavaScript 라이브러리 (적응형 이미지 분석 및 실시간 계산) -->
    <script src="../../includes/js/GalleryLightbox.js"></script>
    <script src="js/cadarok.js" defer></script>
    
    <!-- 세션 ID 및 설정값 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars(session_id()); ?>">
    <meta name="default-section" content="<?php echo htmlspecialchars($default_values['Section']); ?>">
    <meta name="default-quantity" content="<?php echo htmlspecialchars($default_values['MY_amount']); ?>">
    <!-- 공통 갤러리 팝업 함수 -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 인라인 CSS 추출 파일 -->
    <link rel="stylesheet" href="css/cadarok-inline-extracted.css">
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo filemtime(__DIR__ . '/../../css/common-styles.css'); ?>">
    <link rel="stylesheet" href="../../css/upload-modal-common.css?v=<?php echo time(); ?>">

<!-- Phase 5: 견적 요청 버튼 스타일 -->
<style>
    /* .action-buttons, .btn-upload-order → common-styles.css SSOT 사용 */
    .btn-request-quote { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
    .btn-request-quote:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(240, 147, 251, 0.4); }
</style>
    <!-- 테마 시스템 CSS -->
    <?php ThemeLoader::renderCSS(); ?>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/product_schema.php'; echo_product_schema('cadarok'); ?>
    <link rel="stylesheet" href="../../css/quote-gauge.css">
</head>
<body class="cadarok-page<?php echo ($isQuotationMode || $isAdminQuoteMode) ? ' quotation-modal-mode' : ''; ?>" <?php ThemeLoader::renderBodyAttributes(); ?>>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/header-ui.php"; ?>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/nav.php"; ?>

    <div class="product-container">
    
    
        <div class="page-title">
            <h1>카다록/리플렛 견적 안내</h1>
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
                    <h3>실시간 견적 계산기</h3>
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
                        <div id="premiumOptionsSection" style="margin-top: 15px; display: none;"></div>
                    </div>

                    <!-- 통일된 가격 표시 시스템 -->
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
                    <input type="hidden" name="page" value="cadarok">
                </form>
            </div>
        </div>
    </div>

    <!-- 파일 업로드 모달 (통합 컴포넌트) -->
    <?php include "../../includes/upload_modal.php"; ?>
    <script src="../../includes/upload_modal.js?v=<?php echo time(); ?>"></script>
    <script>
    window._commonOpenUploadModal = window.openUploadModal;
    window.isLoggedIn = function() { return true; };
    window.checkLoginStatus = function() { return true; };
    </script>

    <!-- 갤러리 더보기 모달 - 공통 팝업으로 대체됨 (/popup/proof_gallery.php) -->
    <div id="cadarokGalleryModal" class="gallery-modal" style="display: none !important;">
        <div class="gallery-modal-overlay" onclick="closeCadarokGalleryModal()"></div>
        <div class="gallery-modal-content">
            <div class="gallery-modal-header">
                <h3>카다록/리플렛 갤러리 (전체)</h3>
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

    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <!-- AI 생성 상세페이지 (기존 설명 위에 표시) -->
    <?php $detail_page_product = 'cadarok'; include __DIR__ . "/../../_detail_page/detail_page_loader.php"; ?>
    <!-- 리플릿/팜플릿 상세 설명 섹션 (하단 설명방법) -->
    <div class="cadarok-detail-combined" style="width: 1100px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_cadarok.php"; ?>
    </div>
    <?php endif; ?>

    <?php
    // 공통 푸터 포함 (견적서 모달에서는 제외)
    if (!$isQuotationMode && !$isAdminQuoteMode) {
        include "../../includes/footer.php";
    }
    ?>

    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <?php include __DIR__ . '/../../includes/quote_gauge.php'; ?>
    <script src="/js/quote-gauge.js?v=<?php echo time(); ?>"></script>
    <?php endif; ?>

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
        // 공통 갤러리 팝업 함수 사용 (common-gallery-popup.js)
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
            formData.append("price", Math.round(window.currentPriceData.total_price));      // 공급가액 (VAT 미포함)
            formData.append("vat_price", Math.round(window.currentPriceData.total_with_vat));  // 합계 (VAT 포함)

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

        // Phase 5: 견적 요청 함수
        window.addToQuotation = function() {
            console.log('💰 견적 요청 시작 - 카다록');

            if (!window.currentPriceData || !window.currentPriceData.total_price) {
                alert('가격을 먼저 계산해주세요.');
                return;
            }

            const formData = new FormData();
            formData.append('product_type', 'cadarok');
            formData.append('MY_type', document.getElementById('MY_type').value);
            formData.append('Section', document.getElementById('Section').value);
            formData.append('POtype', document.getElementById('POtype').value);
            formData.append('MY_amount', document.getElementById('MY_amount').value);
            formData.append('ordertype', document.getElementById('ordertype').value);
            formData.append('price', Math.round(window.currentPriceData.total_price));      // 공급가액 (VAT 미포함)
            formData.append('vat_price', Math.round(window.currentPriceData.total_with_vat));  // 합계 (VAT 포함)

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
    </script>

    <!-- 카다록 메인 로직 (계산기, 갤러리) - 캐시 회피용 v2 -->
    <script src="js/cadarok_v2.js?v=<?php echo time(); ?>"></script>

    <!-- 카다록 추가 옵션 DB 로더 + 시스템 -->
    <script src="/js/premium-options-loader.js"></script>
    <!-- 프리미엄 옵션은 premium-options-loader.js의 PremiumOptionsGeneric 클래스가 동적 처리 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('premiumOptionsSection') && typeof PremiumOptionsGeneric !== 'undefined') {
            setTimeout(function() {
                var poManager = new PremiumOptionsGeneric('cadarok', 'premiumOptionsSection', 'MY_amount');
                poManager.init();
                window.premiumOptionsManager = poManager;
            }, 200);
        }
    });
    </script>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js?v=<?php echo time(); ?>"></script>

    <!-- 테마 스위처 -->
    <?php ThemeLoader::renderSwitcher('bottom-right'); ?>
    <?php ThemeLoader::renderSwitcherJS(); ?>


<?php if ($isAdminQuoteMode): ?>
    <!-- 관리자 견적서 모드: postMessage로 부모 창에 데이터 전송 -->
    <script>
    window.applyToQuotation = function() {
        console.log('🚀 [관리자 견적서-카다록] applyToQuotation() 호출');

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
            product_type: 'cadarok',
            product_name: '카다록',
            specification: specification,
            quantity: quantity,
            unit: '부',
            quantity_display: quantityText,
            unit_price: quantity > 0 ? Math.round(supplyPrice / quantity) : 0,
            supply_price: supplyPrice,
            MY_type: MY_type, Section: Section, MY_amount: MY_amount,
            st_price: supplyPrice,
            st_price_vat: Math.round(supplyPrice * 1.1)
        };

        console.log('📤 [관리자 견적서-카다록] postMessage 전송:', payload);
        window.parent.postMessage({ type: 'ADMIN_QUOTE_ITEM_ADDED', payload: payload }, window.location.origin);
    };
    console.log('✅ [관리자 견적서-카다록] applyToQuotation() 정의 완료');
    </script>
<?php endif; ?>
<?php if (isset($db) && $db) { mysqli_close($db); } ?>
</body>
</html>