<?php 
session_start(); 
$session_id = session_id();

// 출력 버퍼 관리 및 에러 설정 (명함 성공 패턴)
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 데이터베이스 연결
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

// POST 요청 처리 (로그인)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_action'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!empty($username) && !empty($password)) {
        // 신규 users 테이블에서 확인
        $query = "SELECT * FROM users WHERE username = ? OR member_id = ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            $login_success = false;
            
            // 해시된 비밀번호 확인
            if (password_verify($password, $user['password'])) {
                $login_success = true;
            }
            // 기존 평문 비밀번호 확인 (호환성)
            elseif (!empty($user['old_password']) && $password === $user['old_password']) {
                $login_success = true;
            }
            
            if ($login_success) {
                // 세션 설정
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['id_login_ok'] = array('id' => $user['username'], 'pass' => $password);
                setcookie("id_login_ok", $user['username'], 0, "/");
                
                // 페이지 리다이렉트 (새로고침 대신)
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            }
        }
        
        $login_message = '아이디 또는 비밀번호가 올바르지 않습니다.';
    } else {
        $login_message = '아이디와 비밀번호를 입력해주세요.';
    }
}

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

// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 파일 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 컴팩트 전용 CSS -->
    <link rel="stylesheet" href="css/ncrflambeau-compact.css">
    
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">
    <link rel="stylesheet" href="../../css/btn-primary.css">
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="ncr-card">
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>📋 양식지(NCR) 자동견적</h1>
            <p>컴팩트 버전 - 한눈에 간편하게</p>
        </div>
        
        <div class="ncr-grid">
            <!-- 좌측: 통합 갤러리 시스템 -->
            <section class="ncrflambeau-gallery" aria-label="양식지 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 사용 (3줄로 완전 간소화)
                include_once "../../includes/gallery_helper.php";
                include_product_gallery('ncrflambeau', ['mainSize' => [500, 400]]);
                ?>
            </section>
            
            <!-- 우측: 계산기 섹션 (50%) -->
            <aside class="ncr-calculator" aria-label="실시간 견적 계산기">
                <div class="calculator-header">
                    <h3>🏠 실시간 견적 계산기</h3>
                </div>
                
                <form id="ncr-quote-form" method="post">
                    <div class="options-grid">
                        <!-- 구분 -->
                        <div class="option-group">
                            <label class="option-label" for="MY_type">양식 구분</label>
                            <select name="MY_type" id="MY_type" class="option-select" required>
                                <option value="">구분을 선택해주세요</option>
                                <?php
                                $categories = getCategoryOptions($db, 'MlangPrintAuto_transactionCate', 'NcrFlambeau');
                                foreach ($categories as $category) {
                                    $selected = ($category['no'] == $default_values['MY_type']) ? 'selected' : '';
                                    echo "<option value='" . safe_html($category['no']) . "' $selected>" . safe_html($category['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- 규격 -->
                        <div class="option-group">
                            <label class="option-label" for="MY_Fsd">규격</label>
                            <select name="MY_Fsd" id="MY_Fsd" class="option-select" required>
                                <option value="">먼저 구분을 선택해주세요</option>
                            </select>
                        </div>
                        
                        <!-- 색상 -->
                        <div class="option-group">
                            <label class="option-label" for="PN_type">색상</label>
                            <select name="PN_type" id="PN_type" class="option-select" required>
                                <option value="">먼저 구분을 선택해주세요</option>
                            </select>
                        </div>
                        
                        <!-- 수량 -->
                        <div class="option-group">
                            <label class="option-label" for="MY_amount">수량</label>
                            <select name="MY_amount" id="MY_amount" class="option-select" required>
                                <option value="">수량을 선택해주세요</option>
                            </select>
                        </div>
                        
                        <!-- 편집디자인 -->
                        <div class="option-group full-width">
                            <label class="option-label" for="ordertype">편집디자인</label>
                            <select name="ordertype" id="ordertype" class="option-select" required>
                                <option value="">편집 방식을 선택해주세요</option>
                                <option value="total">디자인+인쇄</option>
                                <option value="print" selected>인쇄만 의뢰</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-label">견적 금액</div>
                        <div class="price-amount" id="priceAmount">0원</div>
                        <div class="price-details" id="priceDetails">
                            옵션을 선택하시면<br>실시간으로 가격이 계산됩니다
                        </div>
                    </div>
                    
                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton" style="display: none;">
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
                    <input type="hidden" name="page" value="NcrFlambeau">
                    
                    <!-- 가격 정보 저장용 -->
                    <input type="hidden" name="calculated_price" id="calculated_price" value="">
                    <input type="hidden" name="calculated_vat_price" id="calculated_vat_price" value="">
                </form>
            </aside>
        </div>
    </div>

    <!-- 파일 업로드 모달 (명함 성공 패턴 적용) -->
    <div id="uploadModal" class="upload-modal" style="display: none;">
        <div class="modal-overlay" onclick="closeUploadModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📎 양식지 디자인 파일 업로드</h3>
                <button type="button" class="modal-close" onclick="closeUploadModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="upload-section">
                    <div class="upload-title">📎 파일 업로드</div>
                    <div class="upload-container">
                        <div class="upload-left">
                            <label class="upload-label">업로드 방법</label>
                            <div class="upload-buttons">
                                <button type="button" class="btn-upload-method active" data-method="upload" onclick="selectUploadMethod('upload')">
                                    📁 파일 업로드
                                </button>
                                <button type="button" class="btn-upload-method" data-method="email" onclick="selectUploadMethod('email')">
                                    📧 이메일 전송
                                </button>
                            </div>
                            <div class="upload-area" id="modalUploadArea">
                                <div class="upload-dropzone" id="modalUploadDropzone">
                                    <span class="upload-icon">📁</span>
                                    <span class="upload-text">파일을 여기에 드래그하거나 클릭하세요</span>
                                    <input type="file" id="modalFileInput" accept=".jpg,.jpeg,.png,.pdf,.zip" multiple hidden>
                                </div>
                                <div class="upload-info">
                                    파일첨부 특수문자(#,&,'&',*,%, 등) 사용은 불가능하며 파일명이 길면 오류가 발생하니 되도록 짧고 간단하게 작성해 주세요!<br>
                                    지원 형식: JPG, PNG, PDF, ZIP (최대 15MB)
                                </div>
                            </div>
                        </div>
                        
                        <div class="upload-right">
                            <label class="upload-label">작업메모</label>
                            <textarea id="modalWorkMemo" class="memo-textarea" placeholder="작업 관련 요청사항이나 특별한 지시사항을 입력해주세요.&#10;&#10;예시:&#10;- 색상을 더 진하게 해주세요&#10;- 글자 크기를 조금 더 크게&#10;- 배경색을 파란색으로 변경"></textarea>
                            
                            <div class="upload-notice">
                                <div class="notice-item">📋 택배 무료배송은 결제금액 총 3만원 이상시에 한함</div>
                                <div class="notice-item">📋 당일(익일)주문 전날 주문 제품과 동일 불가</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="uploaded-files" id="modalUploadedFiles" style="display: none;">
                        <h5>📂 업로드된 파일</h5>
                        <div class="file-list" id="modalFileList"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cart" onclick="addToBasketFromModal()" style="max-width: none;">
                    🛒 장바구니에 저장
                </button>
            </div>
        </div>
    </div>

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <?php
    // 공통 푸터 포함
    include "../../includes/footer.php";
    ?>

    <!-- 양식지(NCR) 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    <style>
    /* =================================================================== */
    /* 1단계: Page-title 컴팩트화 (1/2 높이 축소) */
    /* =================================================================== */
    .page-title {
        padding: 12px 0 !important;          /* 1/2 축소 */
        margin-bottom: 15px !important;      /* 1/2 축소 */
        border-radius: 10px !important;      /* 2/3 축소 */
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
    }

    .page-title h1 {
        font-size: 1.6rem !important;        /* 27% 축소 */
        line-height: 1.2 !important;         /* 타이트 */
        margin: 0 !important;
        color: white !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
    }

    .page-title p {
        margin: 4px 0 0 0 !important;        /* 1/2 축소 */
        font-size: 0.85rem !important;       /* 15% 축소 */
        line-height: 1.3 !important;
        color: white !important;
        opacity: 0.9 !important;
    }

    /* =================================================================== */
    /* 2단계: Calculator-header 컴팩트화 (gallery-title과 완전히 동일한 디자인) */
    /* =================================================================== */
    .calculator-header, .price-section h3, .price-calculator h3 {
        background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%) !important;
        color: white !important;
        padding: 18px 20px !important;
        margin: -25px -25px 20px -25px !important;
        border-radius: 15px 15px 0 0 !important;
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 2px 10px rgba(21, 101, 192, 0.3) !important;
        line-height: 1.2 !important;
    }

    /* ncr-calculator 섹션에 갤러리와 동일한 배경 적용 */
    .ncr-calculator {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 15px !important;
        padding: 25px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        position: relative !important; /* 헤더 오버플로우를 위한 설정 */
    }

    .calculator-header h3 {
        font-size: 1.1rem !important;        /* gallery-title과 동일 */
        line-height: 1.2 !important;
        margin: 0 !important;
        color: white !important;
        font-weight: 600 !important;
    }

    .calculator-subtitle {
        font-size: 0.85rem !important;
        margin: 0 !important;
        opacity: 0.9 !important;
    }

    /* =================================================================== */
    /* 3단계: 통일된 가격 표시 - 녹색 큰 글씨 (인쇄비+편집비=공급가) */
    /* =================================================================== */
    .price-display {
        background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border: 2px solid #28a745 !important;
        border-radius: 12px !important;
        padding: 15px 20px !important;
        text-align: center !important;
        margin: 20px 0 !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.1) !important;
    }

    .price-display.calculated {
        background: linear-gradient(145deg, #d4edda 0%, #c3e6cb 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.2) !important;
        border-color: #20c997 !important;
    }

    .price-display .price-label {
        font-size: 0.9rem !important;
        color: #495057 !important;
        margin-bottom: 8px !important;
        font-weight: 500 !important;
    }

    .price-display .price-amount {
        font-size: 2.2rem !important;
        font-weight: 700 !important;
        color: #28a745 !important;
        margin: 10px 0 !important;
        line-height: 1.2 !important;
        text-shadow: 0 2px 4px rgba(40, 167, 69, 0.3) !important;
        letter-spacing: -0.5px !important;
    }

    .price-display .price-details {
        font-size: 0.8rem !important;
        color: #6c757d !important;
        line-height: 1.4 !important;
        margin-top: 8px !important;
    }

    .price-display:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.15) !important;
    }

    /* =================================================================== */
    /* 4단계: Form 요소 컴팩트화 (패딩 1/2 축소) */
    /* =================================================================== */
    .option-select, select, input[type="text"], input[type="email"], textarea {
        padding: 6px 15px !important;        /* 상하 패딩 1/2 */
    }

    .option-group {
        margin-bottom: 8px !important;       /* 33% 축소 */
    }

    /* =================================================================== */
    /* 5단계: 기타 요소들 컴팩트화 */
    /* =================================================================== */
    .calculator-section {
        padding: 0px 25px !important;        /* 더 타이트하게 */
        min-height: 400px !important;
    }

    .options-grid {
        gap: 12px !important;                /* 25% 축소 */
    }

    .upload-order-button {
        margin-top: 8px !important;          /* 20% 축소 */
    }

    /* =================================================================== */
    /* 6단계: 갤러리 섹션 스타일 (양식지 브랜드 컬러 - 네이비 블루) */
    /* =================================================================== */
    .ncr-gallery {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9);
    }
    
    /* 통합 갤러리 제목 색상 조정 (양식지 브랜드 컬러) - 견적계산기와 동일하게 */
    .ncr-gallery .gallery-title {
        background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%) !important;
        color: white !important;
        padding: 18px 20px !important;
        margin: -25px -25px 20px -25px !important;
        border-radius: 15px 15px 0 0 !important;
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        position: relative !important;
    }

    /* =================================================================== */
    /* 7단계: 반응형 최적화 */
    /* =================================================================== */
    @media (max-width: 768px) {
        /* 모바일에서는 축소 정도 완화 */
        .page-title { 
            padding: 15px 0 !important;       /* 데스크톱보다 약간 여유 */
        }
        
        .page-title h1 {
            font-size: 1.4rem !important;     /* 가독성 고려 */
        }
        
        .calculator-header { 
            padding: 15px 20px !important;    /* 터치 친화적 */
        }
        
        .price-display .price-amount {
            font-size: 1.5rem !important;     /* 모바일 가독성 */
        }
        
        .option-select, select, input[type="text"], input[type="email"], textarea {
            padding: 10px 15px !important;    /* 터치 영역 확보 */
        }

        .gallery-section {
            padding: 20px;
            margin: 0 -10px;
            border-radius: 10px;
        }
        
        .gallery-title {
            margin: -20px -20px 15px -20px;
            padding: 12px 15px;
            font-size: 1rem;
        }
    }
    </style>

    <!-- JavaScript 파일 포함 -->
    <script src="js/ncrflambeau-compact.js"></script>
    
    <!-- 양식지 인라인 갤러리 시스템 (전단지와 동일) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('양식지 페이지 초기화 완료 - 인라인 갤러리 시스템');
        
        // 양식지 갤러리 로드
        loadNcrGallery();
    });
    
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
                    <div style="padding: 20px; text-align: center; color: #6c757d;">
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