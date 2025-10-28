<?php
session_start(); 
$session_id = session_id();

// 출력 버퍼 관리 및 에러 설정 (명함 성공 패턴)
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 보안 상수 정의 후 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 페이지 설정
$page_title = '📋 두손기획인쇄 - 양식지(NCR) 컴팩트 견적';
$current_page = 'ncrflambeau';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 공통 함수 및 설정
include "../../includes/functions.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);

// 로그 정보 생성
$log_info = generateLogInfo();

// 로그인 처리 (auth.php 대신 로컬 처리)
$login_message = '';
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

// 공통 인증 시스템 사용
include "../../includes/auth.php";

// 사용자 정보 설정
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
} elseif (isset($_COOKIE['id_login_ok'])) {
    $user_name = $_COOKIE['id_login_ok'];
} else {
    $user_name = '';
}

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 기본값 설정 (명함 패턴 적용)
$default_values = [
    'MY_type' => '',      // 구분
    'MY_Fsd' => '',       // 규격
    'PN_type' => '',      // 색상
    'MY_amount' => '',    // 수량
    'ordertype' => 'print' // 편집디자인 (인쇄만 기본)
];

// 기본값을 양식(100매철)로 설정 (no: 475)
$default_values['MY_type'] = '475'; // 양식(100매철)

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <!-- 🎨 통합 컬러 시스템 -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>

    <!-- 세션 ID 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars($session_id); ?>">

    <!-- 컴팩트 전용 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css">
    
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <link rel="stylesheet" href="../../css/btn-primary.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- 통합 가격 표시 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    <!-- 통일 인라인 폼 스타일 시스템 -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">
    <!-- 추가 옵션 시스템 CSS (전단지와 동일) -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- 🎯 통합 공통 스타일 CSS (최종 로딩으로 최우선권 확보) -->

    <!-- 공통 갤러리 팝업 함수 -->
    <script src="../../js/common-gallery-popup.js"></script>
    <!-- 파일 업로드 컴포넌트 JavaScript -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>
    <!-- 🎯 통합 공통 스타일 CSS (최종 로드로 최우선 적용) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">
    <link rel="stylesheet" href="../../css/upload-modal-common.css">
</head>

<body class="ncrflambeau-page">
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>

    <div class="product-container">
    
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>📋 양식지(NCR) 견적 안내</h1>
        </div>
        
        <div class="product-content">
            <!-- 좌측: 통합 갤러리 시스템 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="양식지 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'ncrflambeau';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </section>
            
            <!-- 우측: 계산기 섹션 (50%) -->
            <aside class="product-calculator" aria-label="실시간 견적 계산기">
                
                <form id="ncr-quote-form" method="post">
                    <!-- 통일 인라인 폼 시스템 - NcrFlambeau 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">구분</label>
                            <select class="inline-select" name="MY_type" id="MY_type" required>
                                <option value="">구분을 선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, "mlangprintauto_transactioncate", "NcrFlambeau");
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="inline-note">양식지 구분을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_Fsd">규격</label>
                            <select class="inline-select" name="MY_Fsd" id="MY_Fsd" required>
                                <option value="">먼저 구분을 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하는 규격을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="PN_type">색상</label>
                            <select class="inline-select" name="PN_type" id="PN_type" required>
                                <option value="">먼저 구분을 선택해주세요</option>
                            </select>
                            <span class="inline-note">인쇄 색상을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_amount">수량</label>
                            <select class="inline-select" name="MY_amount" id="MY_amount" required>
                                <option value="">수량을 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하시는 수량을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="ordertype">편집비</label>
                            <select class="inline-select" name="ordertype" id="ordertype" required>
                                <option value="">편집 방식을 선택해주세요</option>
                                <option value="print" selected>인쇄만 의뢰</option>
                                <option value="total">디자인+인쇄</option>
                            </select>
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>

                    <!-- 🆕 추가 옵션 섹션 -->
                    <div class="premium-options-section" id="premiumOptionsSection" style="margin-top: 15px;">
                        <!-- 한 줄 체크박스 헤더 -->
                        <div class="option-headers-row">
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="folding_enabled" name="folding_enabled" class="option-toggle" value="1">
                                <label for="folding_enabled" class="toggle-label">넘버링</label>
                            </div>
                            <div class="option-checkbox-group">
                                <input type="checkbox" id="creasing_enabled" name="creasing_enabled" class="option-toggle" value="1">
                                <label for="creasing_enabled" class="toggle-label">미싱</label>
                            </div>
                            <div class="option-price-display">
                                <span class="option-price-total" id="premiumPriceTotal">(+0원)</span>
                            </div>
                        </div>

                        <!-- 넘버링 옵션 상세 -->
                        <div class="option-details" id="folding_options" style="display: none;">
                            <select name="folding_type" id="folding_type" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="numbering">전화 문의 1688-2384</option>
                            </select>
                        </div>

                        <!-- 미싱 옵션 상세 -->
                        <div class="option-details" id="creasing_options" style="display: none;">
                            <select name="creasing_lines" id="creasing_lines" class="option-select">
                                <option value="">선택하세요</option>
                                <option value="1">1줄</option>
                                <option value="2">2줄</option>
                                <option value="3">3줄</option>
                            </select>
                        </div>

                        <!-- 숨겨진 필드들 -->
                        <input type="hidden" name="folding_price" id="folding_price" value="0">
                        <input type="hidden" name="creasing_price" id="creasing_price" value="0">
                        <input type="hidden" name="additional_options_total" id="additional_options_total" value="0">
                    </div>

                    <!-- 통일된 가격 표시 (라벨 없는 깔끔한 스타일) -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 -->
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
                    <input type="hidden" name="page" value="NcrFlambeau">

                    <!-- 가격 정보 저장용 -->
                    <input type="hidden" name="calculated_price" id="calculated_price" value="">
                    <input type="hidden" name="calculated_vat_price" id="calculated_vat_price" value="">
                </form>
            </aside>
        </div>
    </div>

    <?php 
    // NCR양식 모달 설정
    $modalProductName = 'NCR양식';
    $modalProductIcon = '📄';
    include '../../includes/upload_modal.php'; 
    ?>

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <!-- NCR양식지 상세 설명 섹션 (1200px 폭) - 하단 설명방법 적용 -->
    <div class="ncrflambeau-detail-combined" style="width: 1200px; max-width: 100%; margin: 7.5px auto; padding: 25px; background: #f8f9fa; border-radius: 12px; border: 1px solid #e0e0e0;">
        <?php include "explane_ncrflambeau.php"; ?>
    </div>

    <?php
    // 공통 푸터 포함
    include "../../includes/footer.php";
    ?>


    <!-- 공통 가격 표시 시스템 -->
    <script src="../../js/common-price-display.js" defer></script>
    <!-- NCR양식 계산기 JavaScript -->
    <script src="js/ncrflambeau-compact.js?v=<?php echo time(); ?>"></script>
    <!-- 🆕 추가 옵션 시스템 스크립트 (전단지와 동일) -->
    <script src="js/ncrflambeau-premium-options.js?v=<?php echo time(); ?>"></script>
    
    <!-- 양식지 인라인 갤러리 시스템 (전단지와 동일) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('양식지 페이지 초기화 완료 - 인라인 갤러리 시스템');
        
        // 공통 모달 JavaScript 로드
        const modalScript = document.createElement('script');
        modalScript.src = '../../includes/upload_modal.js';
        document.head.appendChild(modalScript);
        
        // 양식지 갤러리 로드
        loadNcrGallery();
    });
    
    // NCR양식 전용 장바구니 추가 함수
    function handleModalBasketAdd(uploadedFiles, onSuccess, onError) {
        console.log('NCR양식 handleModalBasketAdd 호출, 파일 수:', uploadedFiles.length);

        if (!window.currentPriceData) {
            onError('먼저 가격을 계산해주세요.');
            return;
        }

        const form = document.getElementById('ncr-quote-form');
        if (!form) {
            onError('폼을 찾을 수 없습니다.');
            return;
        }

        const formData = new FormData(form);

        // 기본 주문 정보
        formData.set('action', 'add_to_basket');
        formData.set('calculated_price', Math.round(window.currentPriceData.total_price));
        formData.set('calculated_vat_price', Math.round(window.currentPriceData.vat_price));
        formData.set('product_type', 'ncrflambeau');

        // 작업메모
        const workMemoElement = document.getElementById('modalWorkMemo');
        if (workMemoElement) {
            formData.set('work_memo', workMemoElement.value);
        }

        formData.set('upload_method', window.selectedUploadMethod || 'upload');

        // 업로드된 파일들 추가
        uploadedFiles.forEach((fileObj, index) => {
            formData.append(`uploaded_files[${index}]`, fileObj.file);
        });

        // 추가 옵션 데이터 추가
        const additionalOptionsTotal = parseInt(document.getElementById('additional_options_total')?.value || 0);
        formData.set('additional_options_total', additionalOptionsTotal);

        // 넘버링 옵션
        if (document.getElementById('folding_enabled')?.checked) {
            formData.set('folding_enabled', '1');
            formData.set('folding_type', document.getElementById('folding_type')?.value || '');
            formData.set('folding_price', document.getElementById('folding_price')?.value || '0');
        }

        // 미싱 옵션
        if (document.getElementById('creasing_enabled')?.checked) {
            formData.set('creasing_enabled', '1');
            formData.set('creasing_lines', document.getElementById('creasing_lines')?.value || '');
            formData.set('creasing_price', document.getElementById('creasing_price')?.value || '0');
        }

        // AJAX 요청
        fetch('add_to_basket.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                onSuccess();
            } else {
                onError(data.message);
            }
        })
        .catch(error => {
            console.error('장바구니 추가 오류:', error);
            onError(error.message);
        });
    }
    
    // 양식지 갤러리 로드 (전단지와 동일한 API 방식)
    async function loadNcrGallery() {
        try {
            console.log('🔍 API에서 양식지 이미지 로드 시작');
            
            const response = await fetch('/api/get_real_orders_portfolio.php?category=ncrflambeau&per_page=4', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📊 API 응답 데이터:', data);
            
            if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
                console.log(`✅ ${data.data.length}개 양식지 이미지 발견!`);
                renderNcrGallery(data.data);
            } else {
                console.warn('⚠️ API에서 유효한 데이터를 받지 못함:', data);
                showNcrPlaceholderImages();
            }
        } catch (error) {
            console.error('❌ API 호출 실패:', error);
            showNcrPlaceholderImages();
        }
    }
    
    // 양식지 갤러리 렌더링
    function renderNcrGallery(images) {
        console.log('🎨 양식지 갤러리 렌더링 시작, 이미지 수:', images.length);
        
        const mainImage = document.getElementById('mainImage');
        const thumbnailStrip = document.getElementById('thumbnailStrip');
        
        if (!mainImage || !thumbnailStrip) {
            console.error('❌ 갤러리 요소를 찾을 수 없음:', {
                mainImage: !!mainImage,
                thumbnailStrip: !!thumbnailStrip
            });
            return;
        }
        
        // 이미지 데이터 검증
        const validImages = images.filter(img => img && img.path && img.path.trim());
        if (validImages.length === 0) {
            console.warn('⚠️ 유효한 이미지가 없음');
            showNcrPlaceholderImages();
            return;
        }
        
        // 첫 번째 이미지를 메인 이미지로 설정
        const firstImage = validImages[0];
        mainImage.src = firstImage.path;
        mainImage.alt = firstImage.title || '양식지 샘플';
        
        // 썸네일 생성
        thumbnailStrip.innerHTML = validImages.map((img, index) => {
            const title = img.title || `양식지 샘플 ${index + 1}`;
            const isActive = index === 0;
            
            return `
                <div class="thumbnail-item ${isActive ? 'active' : ''}" 
                     data-img="${img.path.replace(/"/g, '&quot;')}" 
                     data-index="${index}"
                     role="listitem"
                     tabindex="0"
                     aria-label="${title.replace(/"/g, '&quot;')}"
                     aria-selected="${isActive}"
                     onclick="selectNcrThumb(this)"
                     onkeypress="handleNcrThumbKeypress(event, this)">
                    <img src="${img.path.replace(/"/g, '&quot;')}" 
                         alt="${title.replace(/"/g, '&quot;')}"
                         loading="lazy"
                         onerror="handleImageError(this)">
                </div>
            `;
        }).join('');
        
        console.log(`✅ 양식지 갤러리 렌더링 완료 - ${validImages.length}개 이미지`);
    }
    
    // 양식지 썸네일 선택
    function selectNcrThumb(thumbElement) {
        // 모든 썸네일에서 active 클래스 제거
        document.querySelectorAll('.thumbnail-item').forEach(thumb => {
            thumb.classList.remove('active');
            thumb.setAttribute('aria-selected', 'false');
        });
        
        // 선택된 썸네일에 active 클래스 추가
        thumbElement.classList.add('active');
        thumbElement.setAttribute('aria-selected', 'true');
        
        // 메인 이미지 변경
        const mainImage = document.getElementById('mainImage');
        const newImageSrc = thumbElement.getAttribute('data-img');
        const title = thumbElement.getAttribute('aria-label');
        
        if (mainImage && newImageSrc) {
            mainImage.src = newImageSrc;
            mainImage.alt = title;
        }
    }
    
    // 키보드 네비게이션
    function handleNcrThumbKeypress(event, thumbElement) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            selectNcrThumb(thumbElement);
        }
    }
    
    // 이미지 오류 처리
    function handleImageError(imgElement) {
        imgElement.src = '/images/placeholder.jpg';
        imgElement.alt = '이미지를 불러올 수 없습니다';
    }
    
    // 플레이스홀더 이미지 표시
    function showNcrPlaceholderImages() {
        const thumbnailStrip = document.getElementById('thumbnailStrip');
        const mainImage = document.getElementById('mainImage');
        
        if (thumbnailStrip) {
            thumbnailStrip.innerHTML = `
                <div class="thumbnail-item loading">
                    <div class="gallery-placeholder">
                        📋 양식지 샘플 준비 중...
                    </div>
                </div>
            `;
        }
        
        if (mainImage) {
            mainImage.src = '/images/placeholder.jpg';
            mainImage.alt = '양식지 샘플 준비 중';
        }
    }
    </script>
</body>
</html>