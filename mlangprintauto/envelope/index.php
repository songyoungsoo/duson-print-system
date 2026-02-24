<?php
// 테마 시스템 로드
include_once __DIR__ . '/../../includes/theme_loader.php';

/**
 * 봉투 견적안내 컴팩트 시스템 - NameCard 시스템 구조 적용
 * Features: 적응형 이미지 분석, 부드러운 애니메이션, 실시간 가격 계산
 * Created: 2025년 12월 (AI Assistant - Frontend Persona)
 */

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
if (function_exists("init_gallery_system")) { init_gallery_system("envelope"); }

// 데이터베이스 연결 및 설정
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 및 페이지 설정
$log_info = generateLogInfo();
$page_title = generate_page_title("봉투 견적안내 컴팩트 - 프리미엄");

// URL 파라미터로 종류/재질 사전 선택 (네비게이션 드롭다운에서 진입 시)
$url_type = isset($_GET['type']) ? intval($_GET['type']) : 0;
$url_section = isset($_GET['section']) ? intval($_GET['section']) : 0;

// 기본값 설정 (데이터베이스에서 가져오기) - PROJECT_SUCCESS_REPORT.md 스펙
$default_values = [
    'MY_type' => '',
    'Section' => '',
    'POtype' => '1', // 기본값: 마스터1도
    'MY_amount' => '',
    'ordertype' => 'print' // 기본값: 인쇄만
];

if ($url_type) {
    // URL 파라미터로 진입: 지정된 종류와 재질 사용
    $default_values['MY_type'] = $url_type;
    if ($url_section) {
        $default_values['Section'] = $url_section;
    } else {
        // 해당 종류의 첫 번째 재질 가져오기
        $sec_q = "SELECT no FROM mlangprintauto_transactioncate 
                  WHERE Ttable='Envelope' AND BigNo='" . intval($url_type) . "' 
                  ORDER BY no ASC LIMIT 1";
        $sec_r = mysqli_query($db, $sec_q);
        if ($sec_r && ($sec_row = mysqli_fetch_assoc($sec_r))) {
            $default_values['Section'] = $sec_row['no'];
        }
    }
} else {
    // 기본 진입: 대봉투를 기본 종류로 설정 (사업자 요청)
    $default_type_no = 466; // 대봉투
    $default_values['MY_type'] = $default_type_no;
    
    // 해당 봉투 종류의 첫 번째 재질 가져오기
    $section_query = "SELECT no, title FROM mlangprintauto_transactioncate 
                      WHERE Ttable='Envelope' AND BigNo='" . intval($default_type_no) . "' 
                      ORDER BY no ASC LIMIT 1";
    $section_result = mysqli_query($db, $section_query);
    if ($section_result && ($section_row = mysqli_fetch_assoc($section_result))) {
        $default_values['Section'] = $section_row['no'];
    }
}

// 수량 기본값: Section이 있으면 해당 조합의 기본 수량 가져오기
if ($default_values['MY_type'] && $default_values['Section']) {
    $quantity_query = "SELECT DISTINCT quantity FROM mlangprintauto_envelope 
                      WHERE style='" . intval($default_values['MY_type']) . "' AND Section='" . intval($default_values['Section']) . "' 
                      ORDER BY CASE WHEN quantity='1000' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC 
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
    <title>봉투 인쇄 | 봉투 제작 - 두손기획인쇄</title>
    <meta name="description" content="봉투 인쇄 전문 두손기획인쇄. 대봉투, 소봉투, 창봉투 맞춤 제작. 회사 로고·주소 인쇄. 규격·비규격 모두 가능. 실시간 견적 확인.">
    <meta name="keywords" content="봉투 인쇄, 봉투 제작, 대봉투, 소봉투, 창봉투, 회사봉투, 서류봉투">
    <link rel="canonical" href="https://dsp114.co.kr/mlangprintauto/envelope/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="봉투 인쇄 | 봉투 제작 - 두손기획인쇄">
    <meta property="og:description" content="봉투 인쇄 전문. 대봉투, 소봉투, 창봉투 맞춤 제작. 회사 로고 인쇄 가능.">
    <meta property="og:url" content="https://dsp114.co.kr/mlangprintauto/envelope/">
    <meta property="og:image" content="https://dsp114.co.kr/ImgFolder/dusonlogo1.png">
    <meta property="og:site_name" content="두손기획인쇄">

    <!-- 봉투 컴팩트 페이지 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css?v=<?php echo filemtime(__DIR__ . '/../../css/product-layout.css'); ?>">
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
    <link rel="stylesheet" href="../../css/unified-inline-form.css?v=<?php echo filemtime(__DIR__ . '/../../css/unified-inline-form.css'); ?>">
    <!-- 추가 옵션 시스템 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- jQuery 라이브러리 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- 통합 갤러리 JavaScript 라이브러리 -->
    <script src="../NameCard/js/unified-gallery.js"></script>
    <script src="../../js/unified-gallery-popup.js"></script>
    
    <!-- 프리미엄 옵션 DB 로더 -->
    <script src="/js/premium-options-loader.js"></script>
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
    <link rel="stylesheet" href="../../css/upload-modal-common.css?v=<?php echo time(); ?>">
    <!-- 견적서 모달용 공통 스타일 -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">
    <link rel="stylesheet" href="../../css/quote-gauge.css">

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
    </style>
    <!-- 테마 시스템 CSS -->
    <?php ThemeLoader::renderCSS(); ?>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/product_schema.php'; echo_product_schema('envelope'); ?>
</head>
<body class="envelope-page<?php echo ($isQuotationMode || $isAdminQuoteMode) ? ' quotation-modal-mode' : ''; ?>" <?php ThemeLoader::renderBodyAttributes(); ?>>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/header-ui.php"; ?>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/nav.php"; ?>

    <style>
        /* 봉투재질보기 버튼 스타일 */
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        /* 인라인 폼 내 봉투재질보기 버튼 */
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

    <div class="product-container">
        <div class="page-title">
            <h1>봉투 견적 안내
                <a href="#envelope-texture-section" class="btn-texture-view" title="봉투 재질 이미지 보기">📋 봉투재질보기</a>
            </h1>
        </div>

        <!-- 컴팩트 2단 그리드 레이아웃 (500px 갤러리 + 나머지 계산기) -->
        <div class="product-content">
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

            <!-- 우측: 실시간 가격 계산기 (동적 옵션 로딩 및 자동 계산) -->
            <div class="product-calculator">
                <div class="calculator-header">
                    <h3>견적 안내</h3>
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
                            <a href="#envelope-texture-section" class="btn-texture-view btn-texture-inline" title="봉투 재질 이미지 보기">📋 봉투재질보기</a>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="Section">재질</label>
                            <select class="inline-select" name="Section" id="Section" required data-default-value="<?php echo htmlspecialchars($default_values['Section']); ?>">
                                <option value="">먼저 종류를 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 용지를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄 색상</label>
                            <select class="inline-select" name="POtype" id="POtype" required>
                                <option value="">선택해주세요</option>
                                <option value="1" <?php echo ($default_values['POtype'] == '1') ? 'selected' : ''; ?>>마스터1도</option>
                                <option value="2" <?php echo ($default_values['POtype'] == '2') ? 'selected' : ''; ?>>마스터2도</option>
                                <option value="3" <?php echo ($default_values['POtype'] == '3') ? 'selected' : ''; ?>>칼라4도(옵셋)</option>
                            </select>
                            <span class="inline-note">인쇄 도수 선택</span>
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

                    <?php include __DIR__ . '/../../includes/action_buttons.php'; ?>

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
    <script src="../../includes/upload_modal.js?v=<?php echo time(); ?>"></script>
    <!-- 로그인 체크 건너뛰기 (다른 제품과 동일) -->
    <script>
    window.isLoggedIn = function() { return true; };
    window.checkLoginStatus = function() { return true; };
    </script>

    <?php include "../../includes/login_modal.php"; ?>

    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <!-- 옵셋봉투 및 작업 시 유의사항 통합 섹션 (1200px 폭) -->
    <div class="envelope-detail-combined" style="width: 1100px; max-width: 100%; margin: 30px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane05.php"; ?>
    </div>
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
        
        // 통일된 갤러리 팝업 초기화
        let unifiedEnvelopeGallery;
        
        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            // 통일된 갤러리 팝업 초기화
            unifiedEnvelopeGallery = new UnifiedGalleryPopup({
                category: 'envelope',
                apiUrl: '/api/get_real_orders_portfolio.php',
                title: '봉투 전체 갤러리',
                icon: '',
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

        // 양면테이프 가격 설정 (DB에서 덮어쓸 수 있음)
        var envelopeTapePricing = {
            tiers: [
                { max_qty: 500, price: 25000 },
                { max_qty: 1000, price: 40000 }
            ],
            over_1000_per_unit: 40
        };

        // DB에서 봉투 옵션 가격 로드
        (async function() {
            if (typeof loadPremiumOptionsFromDB === 'function') {
                try {
                    const dbData = await loadPremiumOptionsFromDB('envelope');
                    if (dbData) {
                        dbData.forEach(function(opt) {
                            if (opt.option_name === '양면테이프' && opt.variants && opt.variants.length > 0) {
                                var pc = opt.variants[0].pricing_config;
                                if (pc && pc.tiers) {
                                    envelopeTapePricing = pc;
                                    console.log('✅ 봉투 테이프 가격 DB 적용 완료');
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.warn('봉투 DB 가격 로드 실패, 하드코딩 사용');
                }
            }
        })();

        // 양면테이프 옵션 관련 함수들
        function calculateTapePrice() {
            const tapeEnabled = document.getElementById('envelope_tape_enabled')?.checked;
            const mainQuantity = parseInt(document.getElementById('MY_amount')?.value) || 0;
            const tapePriceDisplay = document.getElementById('tapePriceDisplay');
            const tapePriceField = document.getElementById('envelope_tape_price');
            const additionalOptionsField = document.getElementById('envelope_additional_options_total');

            let tapePrice = 0;

            if (tapeEnabled && mainQuantity > 0) {
                // DB 가격 기반 계산
                var matched = false;
                for (var i = 0; i < envelopeTapePricing.tiers.length; i++) {
                    var tier = envelopeTapePricing.tiers[i];
                    if (mainQuantity <= tier.max_qty) {
                        tapePrice = tier.price;
                        matched = true;
                        break;
                    }
                }
                if (!matched) {
                    // 최대 tier 초과: per_unit 계산
                    tapePrice = mainQuantity * (envelopeTapePricing.over_1000_per_unit || 40);
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
            formData.append("price", Math.round(window.currentPriceData.total_price));      // 공급가액 (VAT 미포함)
            formData.append("vat_price", Math.round(window.currentPriceData.total_with_vat));  // 합계 (VAT 포함)

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

        // Phase 5: 견적 요청 함수
        window.addToQuotation = function() {
            console.log('💰 견적 요청 시작 - 봉투');

            // 가격 계산 확인
            if (!window.currentPriceData || !window.currentPriceData.total_price) {
                alert('가격을 먼저 계산해주세요.');
                return;
            }

            // 폼 데이터 수집
            const formData = new FormData();
            formData.append('product_type', 'envelope');
            formData.append('MY_type', document.getElementById('MY_type').value);
            formData.append('Section', document.getElementById('Section').value);
            formData.append('POtype', document.getElementById('POtype').value);
            formData.append('MY_amount', document.getElementById('MY_amount').value);
            formData.append('ordertype', document.getElementById('ordertype').value);
            formData.append('price', Math.round(window.currentPriceData.total_price));      // 공급가액 (VAT 미포함)
            formData.append('vat_price', Math.round(window.currentPriceData.total_with_vat));  // 합계 (VAT 포함)

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
    </script>

    <?php if ($isQuotationMode || $isAdminQuoteMode): ?>
    <!-- 관리자 견적서 모달용 applyToQuotation 함수 -->
    <script>
    /**
     * 견적서에 봉투 품목 추가
     * calculator_modal.js가 ADMIN_QUOTE_ITEM_ADDED 메시지를 수신
     */
    window.applyToQuotation = function() {
        console.log('🚀 [관리자 견적서-봉투] applyToQuotation() 호출');

        // 1. 필수 필드 검증
        const MY_type = document.getElementById('MY_type')?.value;
        const Section = document.getElementById('Section')?.value;
        const POtype = document.getElementById('POtype')?.value;
        const MY_amount = document.getElementById('MY_amount')?.value;
        const ordertype = document.getElementById('ordertype')?.value;

        if (!MY_type || !Section || !POtype || !MY_amount || !ordertype) {
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
        const ordertypeText = document.getElementById('ordertype')?.options[document.getElementById('ordertype').selectedIndex]?.text || '';

        // 양면테이프 옵션 체크
        const tapeEnabled = document.getElementById('envelope_tape_enabled')?.checked;
        const tapePrice = parseInt(document.getElementById('envelope_tape_price')?.value) || 0;

        // 1줄: 종류 / 재질
        const line1 = [typeText, sectionText].filter(s => s).join(' / ');
        // 2줄: 인쇄색상 / 편집비 (+ 양면테이프)
        let line2Parts = [potypeText, ordertypeText];
        if (tapeEnabled && tapePrice > 0) {
            line2Parts.push('양면테이프');
        }
        const line2 = line2Parts.filter(s => s).join(' / ');
        const specification = `${line1}\n${line2}`;

        // 4. 페이로드 생성
        const payload = {
            product_code: 'envelope',
            product_name: '봉투',
            quantity: parseInt(MY_amount),
            quantity_unit: '매',
            supply_price: supplyPrice,
            specification: specification,
            options: {
                MY_type: MY_type,
                Section: Section,
                POtype: POtype,
                MY_amount: MY_amount,
                ordertype: ordertype,
                envelope_tape_enabled: tapeEnabled ? '1' : '0',
                envelope_tape_price: tapePrice
            }
        };

        console.log('📤 [봉투] postMessage 전송:', payload);

        // 5. 부모 창으로 메시지 전송
        window.parent.postMessage({
            type: 'ADMIN_QUOTE_ITEM_ADDED',
            payload: payload
        }, window.location.origin);
    };
    </script>
    <?php endif; ?>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js?v=<?php echo time(); ?>"></script>

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