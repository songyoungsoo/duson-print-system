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
$page_title = '📄 두손기획인쇄 - 전단지 컴팩트 견적';
$current_page = 'leaflet';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 전단지 관련 설정
$page = "inserted";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 공통 함수 및 설정
include "../../includes/functions.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 통합 갤러리 시스템 초기화
include "../../includes/gallery_helper.php";
init_gallery_system('inserted');

// 세션 및 기본 설정
check_session();
check_db_connection($db);

// 로그 정보 생성
$log_info = generateLogInfo();

// 공통 인증 시스템 사용
include "../includes/auth.php";
$is_logged_in = isLoggedIn() || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

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

// 드롭다운 옵션을 가져오는 함수들 (컴팩트 전용 - 함수명 변경으로 충돌 방지)
function getLeafletColorOptions($connect, $GGTABLE, $page) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    return $options;
}

function getLeafletPaperTypes($connect, $GGTABLE, $color_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE TreeNo='$color_no' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    return $options;
}

function getLeafletPaperSizes($connect, $GGTABLE, $color_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE BigNo='$color_no' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    return $options;
}

// 초기 옵션 데이터 가져오기
$colorOptions = getLeafletColorOptions($connect, $GGTABLE, $page);
$firstColorNo = !empty($colorOptions) ? $colorOptions[0]['no'] : '1';
$paperTypeOptions = getLeafletPaperTypes($connect, $GGTABLE, $firstColorNo);
$paperSizeOptions = getLeafletPaperSizes($connect, $GGTABLE, $firstColorNo);

// 기본값 설정
$default_values = [
    'MY_type' => $firstColorNo,
    'MY_Fsd' => !empty($paperTypeOptions) ? $paperTypeOptions[0]['no'] : '',
    'PN_type' => !empty($paperSizeOptions) ? $paperSizeOptions[0]['no'] : '',
    'POtype' => '1', // 단면 기본
    'MY_amount' => '',
    'ordertype' => 'print' // 인쇄만 기본
];

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>
    
    <!-- 컴팩트 전용 CSS -->
    <link rel="stylesheet" href="css/leaflet-compact.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    
    <!-- 통합 갤러리 CSS -->
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php
    // 갤러리 에셋 자동 포함
    if (defined('GALLERY_ASSETS_NEEDED')) {
        include_gallery_assets();
    }
    ?>
</head>

<body>
    <div class="leaflet-card">
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>📄 전단지 견적 안내</h1>
            <p>컴팩트 버전 - 갤러리와 실시간 계산기</p>
        </div>
        
        <div class="leaflet-grid">
            <!-- 좌측: 통합 갤러리 섹션 -->
            <section class="leaflet-gallery" aria-label="전단지 샘플 갤러리">
                <?php
                // 원클릭 갤러리 포함 (공통 헬퍼 사용)
                include_product_gallery('inserted');
                ?>
            </section>
            
            <!-- 우측: 계산기 섹션 (50%) -->
            <aside class="leaflet-calculator" aria-label="실시간 견적 계산기">
                <div class="calculator-header">
                    <h3>💰 실시간 견적 계산기</h3>
                </div>
                
                <form id="orderForm" method="post">
                    <div class="options-grid">
                        <!-- 인쇄색상 -->
                        <div class="option-group">
                            <label class="option-label" for="MY_type">인쇄색상</label>
                            <select name="MY_type" id="MY_type" class="option-select" required>
                                <?php foreach ($colorOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['no']); ?>" 
                                    <?php echo ($option['no'] == $default_values['MY_type']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- 종이종류 -->
                        <div class="option-group">
                            <label class="option-label" for="MY_Fsd">종이종류</label>
                            <select name="MY_Fsd" id="MY_Fsd" class="option-select" required>
                                <?php foreach ($paperTypeOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['no']); ?>" 
                                    <?php echo ($option['no'] == $default_values['MY_Fsd']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- 종이규격 -->
                        <div class="option-group">
                            <label class="option-label" for="PN_type">종이규격</label>
                            <select name="PN_type" id="PN_type" class="option-select" required>
                                <?php 
                                foreach ($paperSizeOptions as $option): 
                                    $isA4 = false;
                                    // A4(210x297) 정확히 찾기
                                    if (stripos($option['title'], 'A4') !== false && 
                                        stripos($option['title'], '210') !== false && 
                                        stripos($option['title'], '297') !== false) {
                                        $isA4 = true;
                                    }
                                ?>
                                <option value="<?php echo htmlspecialchars($option['no']); ?>" 
                                    <?php echo $isA4 ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- 인쇄면 -->
                        <div class="option-group">
                            <label class="option-label" for="POtype">인쇄면</label>
                            <select name="POtype" id="POtype" class="option-select" required>
                                <option value="1" selected>단면 (앞면만)</option>
                                <option value="2">양면 (앞뒤 모두)</option>
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
                        <div class="option-group">
                            <label class="option-label" for="ordertype">편집디자인</label>
                            <select name="ordertype" id="ordertype" class="option-select" required>
                                <option value="total">디자인+인쇄 (전체 의뢰)</option>
                                <option value="print" selected>인쇄만 의뢰 (파일 준비완료)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-label">견적 금액</div>
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton" style="display: none;">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            📎 파일 업로드 및 주문하기
                        </button>
                    </div>
                    
                    <!-- 선택한 옵션 요약 영역 제거됨 -->
                    
                    <!-- 업로드 및 주문 버튼들 제거됨 -->
                    
                    <!-- 기존 업로드 컴포넌트 제거됨 -->
                    
                    <!-- 숨겨진 필드들 -->
                    <input type="hidden" name="log_url" value="<?php echo safe_html($log_info['url']); ?>">
                    <input type="hidden" name="log_y" value="<?php echo safe_html($log_info['y']); ?>">
                    <input type="hidden" name="log_md" value="<?php echo safe_html($log_info['md']); ?>">
                    <input type="hidden" name="log_ip" value="<?php echo safe_html($log_info['ip']); ?>">
                    <input type="hidden" name="log_time" value="<?php echo safe_html($log_info['time']); ?>">
                    <input type="hidden" name="page" value="inserted">
                    
                    <!-- 가격 정보 저장용 -->
                    <input type="hidden" name="calculated_price" id="calculated_price" value="">
                    <input type="hidden" name="calculated_vat_price" id="calculated_vat_price" value="">
                </form>
            </aside>
        </div>
    </div>

    <!-- 파일 업로드 모달 (명함 스타일 적용) -->
    <div id="uploadModal" class="upload-modal" style="display: none;">
        <div class="modal-overlay" onclick="closeUploadModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📎 전단지 디자인 파일 업로드 및 주문하기</h3>
                <button type="button" class="modal-close" onclick="closeUploadModal()">✕</button>
            </div>
            
            <div class="modal-body">
                <div class="upload-container">
                    <div class="upload-left">
                        <label class="upload-label" for="modalFileInput">파일첨부</label>
                        <div class="upload-buttons">
                            <button type="button" class="btn-upload-method active" onclick="selectUploadMethod('upload')">
                                파일업로드
                            </button>
                            <button type="button" class="btn-upload-method" onclick="selectUploadMethod('manual')" disabled>
                                디자인 의뢰 (별도 문의)
                            </button>
                        </div>
                        <div class="upload-area" id="modalUploadArea">
                            <div class="upload-dropzone" id="modalUploadDropzone">
                                <span class="upload-icon">📁</span>
                                <span class="upload-text">파일을 여기에 드래그하거나 클릭하세요</span>
                                <input type="file" id="modalFileInput" accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.psd,.zip" multiple hidden>
                            </div>
                            <div class="upload-info">
                                파일첨부 시 특수문자(#,&,'&',*,%, 등) 사용은 불가능하며 파일명이 길면 오류가 발생할 수 있습니다.<br>
                                되도록 짧고 간단한 파일명으로 작성해 주세요!
                            </div>
                        </div>
                    </div>
                    
                    <div class="upload-right">
                        <label class="upload-label">작업메모</label>
                        <textarea id="modalWorkMemo" class="memo-textarea" placeholder="특별한 요청사항이 있으시면 입력해주세요...&#10;&#10;예: 색상 조정, 크기 변경, 레이아웃 수정 등"></textarea>
                        
                        <div class="upload-notice">
                            <div class="notice-item">🖨️ 인쇄 품질 향상을 위해 고해상도 파일을 권장합니다</div>
                            <div class="notice-item">📐 재단선이 있는 경우 3mm 여백을 추가해 주세요</div>
                        </div>
                    </div>
                </div>
                
                <div class="uploaded-files" id="modalUploadedFiles" style="display: none;">
                    <h5>📂 업로드된 파일</h5>
                    <div class="file-list" id="modalFileList"></div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cart" onclick="addToBasketFromModal()">
                    🛒 장바구니에 저장
                </button>
            </div>
        </div>
    </div>

    <!-- UnifiedGallery가 자체적으로 팝업과 라이트박스를 생성합니다 -->

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <?php
    // 갤러리 모달은 include_product_gallery()에서 자동 포함됨
    ?>


    <?php
    // 공통 푸터 포함
    include "../../includes/footer.php";
    ?>

    <!-- 전단지 전용 스크립트 -->
    <script src="js/leaflet-compact.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('전단지 페이지 초기화 완료 - 통합 갤러리 시스템');
        
        // 로그인 메시지가 있으면 모달 자동 표시
        <?php if (!empty($login_message)): ?>
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
        <?php endif; ?>
    });
    </script>

    <!-- 전단지 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
    <style>
    /* =================================================================== */
    /* 전단지 갤러리 섹션 스타일 (명함과 동일한 구조, 전단지 브랜드 색상) */
    /* =================================================================== */
    .leaflet-gallery {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12), 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9);
    }
    
    /* 통합 갤러리 제목 색상 조정 (전단지 브랜드 색상 - 그린) */
    .leaflet-gallery .gallery-title {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%) !important;
        color: white !important;
    }

    @media (max-width: 768px) {
        .leaflet-gallery {
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
    /* =================================================================== */
    /* 통합 갤러리 시스템 스타일 (명함 스타일 적용) */
    /* =================================================================== */
    
    /* 메인 뷰어 스타일 */
    .main-viewer {
        width: 100%;
        height: 300px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
    }
    
    .main-viewer:hover {
        border-color: #4caf50;
        box-shadow: 0 8px 30px rgba(76, 175, 80, 0.15);
        transform: translateY(-2px);
    }
    
    .main-viewer img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .main-viewer:hover img {
        transform: scale(1.05);
    }
    
    .viewer-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .main-viewer:hover .viewer-overlay {
        opacity: 1;
    }
    
    .zoom-icon {
        font-size: 2rem;
        color: white;
        background: rgba(76, 175, 80, 0.8);
        padding: 15px;
        border-radius: 50%;
        border: 2px solid white;
    }
    
    /* 썸네일 스트립 스타일 */
    .thumbnail-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 10px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 15px;
    }
    
    .thumbnail-item {
        width: 100%;
        height: 80px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        overflow: hidden;
        position: relative;
    }
    
    .thumbnail-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.7;
        transition: all 0.3s ease;
    }
    
    .thumbnail-item:hover img,
    .thumbnail-item.active img {
        opacity: 1;
    }
    
    .thumbnail-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        border-color: #4caf50;
    }
    
    .thumbnail-item.active {
        border-color: #4caf50;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }
    
    /* 로딩 상태 */
    .thumbnail-item.loading {
        background: #f8f9fa;
    }
    
    .loading-shimmer {
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    /* 더보기 버튼 */
    .btn-view-more {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .btn-view-more:hover {
        background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }
    
    /* 팝업 갤러리 스타일 - 전단지 전용 (통합 갤러리 제외) */
    .leaflet-gallery .gallery-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }
    
    .leaflet-gallery .popup-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .leaflet-gallery .popup-content {
        position: relative;
        background: white;
        width: 90%;
        max-width: 1200px;
        height: 80vh;
        border-radius: 15px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .popup-header {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .popup-header h3 {
        margin: 0;
        font-size: 1.3rem;
    }
    
    .btn-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }
    
    .btn-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .popup-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }
    
    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .image-grid img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .image-grid img:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #4caf50;
    }
    
    /* 페이지네이션 - 페이지 내부용만 (팝업 제외) */
    .leafletGallery .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        padding: 20px;
        border-top: 1px solid #e9ecef;
    }
    
    .btn-prev, .btn-next {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .btn-prev:hover:not(:disabled), .btn-next:hover:not(:disabled) {
        background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }
    
    .btn-prev:disabled, .btn-next:disabled {
        background: #ddd;
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .page-info {
        color: #666;
        font-weight: 500;
    }
    
    /* 라이트박스 스타일 */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 3000;
    }
    
    .lightbox-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .lightbox-content img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .lightbox-prev, .lightbox-next {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: none;
        padding: 20px 15px;
        font-size: 2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .lightbox-prev {
        left: 20px;
    }
    
    .lightbox-next {
        right: 20px;
    }
    
    .lightbox-prev:hover, .lightbox-next:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: none;
        padding: 10px 15px;
        font-size: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 50%;
    }
    
    .lightbox-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
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
    .calculator-header {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%) !important;
        color: white !important;
        padding: 18px 20px !important;
        margin: -25px -25px 20px -25px !important;
        border-radius: 15px 15px 0 0 !important;
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        text-align: center !important;
        box-shadow: 0 2px 10px rgba(76, 175, 80, 0.3) !important;
        line-height: 1.2 !important;
    }

    /* leaflet-calculator 섹션에 갤러리와 동일한 배경 적용 */
    .leaflet-calculator {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 15px !important;
        padding: 25px !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.8) !important;
        position: relative !important; /* 헤더 오버플로우를 위한 설정 */
    }

    .calculator-header h3 {
        font-size: 1.2rem !important;        /* 14% 축소 */
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
    /* 통일된 가격 표시 스타일 - 녹색 큰 글씨 (인쇄비+편집비=공급가) */
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

    .price-display.calculated {
        background: linear-gradient(145deg, #d4edda 0%, #c3e6cb 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.2) !important;
        border-color: #20c997 !important;
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
    /* 6단계: 갤러리 섹션 스타일 (전단지 브랜드 컬러 - 그린) */
    /* =================================================================== */
    .gallery-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }
    
    .gallery-title {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        color: white;
        padding: 15px 20px;
        margin: -25px -25px 20px -25px;
        border-radius: 15px 15px 0 0;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 10px rgba(76, 175, 80, 0.3);
    }

    /* 포스터 방식: backgroundImage 기반 호버 확대 (전단지용) */
    .lightbox-viewer {
        width: 100%;
        height: 300px;
        background-color: #f9f9f9;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        cursor: zoom-in;
        transition: border-color 0.3s ease;
        border: 2px solid #e9ecef;
        position: relative;
        overflow: hidden;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: 50% 50%;
    }
    
    .lightbox-viewer:hover {
        border-color: #4caf50;
    }
    
    /* 썸네일 스트립 스타일 */
    .thumbnail-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 10px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .thumbnail-strip img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        opacity: 0.7;
    }
    
    .thumbnail-strip img:hover {
        opacity: 1;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        border-color: #4caf50;
    }
    
    .thumbnail-strip img.active {
        opacity: 1;
        border-color: #4caf50;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }
    
    /* 갤러리 로딩 상태 */
    .leafletGallery .loading {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        font-size: 1.1rem;
        background: white;
        border-radius: 12px;
        animation: pulse 2s infinite;
    }
    
    /* 갤러리 에러 상태 */
    .leafletGallery .error {
        text-align: center;
        padding: 40px 20px;
        color: #dc3545;
        background: #fff5f5;
        border: 1px solid #ffdddd;
        border-radius: 12px;
        font-size: 0.95rem;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* 갤러리 스타일 (ProofGalleryInline 컴포넌트와 동일) */
    .proof-gallery {
        display: flex;
        flex-direction: column;
        gap: 16px;
        width: 100%;
    }

    .proof-large {
        width: 100%; 
        height: 300px;
    }

    .proof-large .viewport {
        width: 100%; 
        height: 100%;
        border-radius: 16px; 
        overflow: hidden; 
        border: 1px solid #ddd; 
        background: #f9f9f9;
        position: relative;
    }

    .proof-large img {
        width: 100%; 
        height: 100%;
        object-fit: contain;
        transition: transform 220ms ease;
        transform-origin: center center;
    }

    /* 포스터 방식: zoom-active 클래스로 호버 확대 제어 */
    .proof-large .viewport.zoom-active img {
        transform: scale(1.35);
    }

    .proof-thumbs {
        display: grid; 
        grid-template-columns: repeat(4, 1fr); 
        gap: 10px;
        width: 100%;
    }

    .proof-thumbs .thumb {
        width: 100%; 
        height: 80px; 
        border-radius: 12px; 
        overflow: hidden; 
        border: 2px solid #ddd; 
        cursor: pointer;
        background: #f7f7f7;
        display: flex; 
        align-items: center; 
        justify-content: center;
        transition: border-color 0.3s ease, transform 0.2s ease;
    }

    .proof-thumbs .thumb:hover {
        border-color: #4caf50;
        transform: translateY(-2px);
    }

    .proof-thumbs .thumb.active {
        border-color: #4caf50;
        box-shadow: 0 4px 8px rgba(76, 175, 80, 0.2);
    }

    .proof-thumbs .thumb img {
        max-width: 100%; 
        max-height: 100%; 
        object-fit: contain; 
        display: block;
    }

    /* btn-primary 스타일은 공통 CSS (../../css/btn-primary.css)에서 로드됨 */

    /* =================================================================== */
    /* 갤러리 모달 스타일 */
    /* =================================================================== */
    .gallery-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .gallery-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(3px);
    }
    
    .gallery-modal-content {
        position: relative;
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 1000px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideUp 0.3s ease-out;
    }
    
    .gallery-modal-header {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .gallery-modal-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .gallery-modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }
    
    .gallery-modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .gallery-modal-body {
        padding: 20px;
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .gallery-grid img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .gallery-grid img:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #4caf50;
    }
    
    @keyframes modalSlideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* =================================================================== */
    /* 갤러리 페이지네이션 스타일 - 전단지 테마 (초록색) */
    /* =================================================================== */
    /* 페이지 내부 갤러리 페이지네이션만 적용 (팝업 제외) */
    .leafletGallery .gallery-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-top: 1px solid #e9ecef;
        background: #fafafa;
        border-radius: 0 0 15px 15px;
    }
    
    .leafletGallery .pagination-info {
        color: #666;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .leafletGallery .pagination-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .leafletGallery .pagination-btn {
        background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 70px;
        box-shadow: 0 2px 6px rgba(76, 175, 80, 0.2);
    }
    
    .leafletGallery .pagination-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }
    
    .leafletGallery .pagination-btn:disabled {
        background: #ddd;
        color: #999;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .leafletGallery .pagination-numbers {
        display: flex;
        align-items: center;
        gap: 4px;
        margin: 0 10px;
    }
    
    .leafletGallery .pagination-number {
        background: white;
        color: #4caf50;
        border: 1px solid #4caf50;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 32px;
        text-align: center;
    }
    
    .leafletGallery .pagination-number:hover {
        background: #4caf50;
        color: white;
        transform: scale(1.05);
    }
    
    .leafletGallery .pagination-number.active {
        background: #4caf50;
        color: white;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(76, 175, 80, 0.3);
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
</body>
</html>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>