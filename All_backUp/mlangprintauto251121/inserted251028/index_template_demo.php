<?php 
session_start(); 
$session_id = session_id();

// 출력 버퍼 관리 및 에러 설정
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 보안 상수 정의 후 데이터베이스 연결
include "../../db.php";
$connect = $db;

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 전단지 관련 설정
$page = "inserted";
$GGTABLE = "mlangprintauto_transactioncate";
$TABLE = "mlangprintauto_transactioncate";

// 공통 함수 및 설정
include "../../includes/functions.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { 
    include_once '../../includes/gallery_helper.php'; 
}
if (function_exists("init_gallery_system")) { 
    init_gallery_system("inserted"); 
}

// 세션 및 기본 설정
check_session();
check_db_connection($db);

// 로그 정보 생성
$log_info = generateLogInfo();

// 공통 인증 시스템 사용
include "../../includes/auth.php";
$is_logged_in = isLoggedIn() || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

// 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';

// 🎯 통합 템플릿 설정
$page_title = '📄 두손기획인쇄 - 전단지 컴팩트 견적';
$body_class = 'leaflet-page';
$additional_css = [
    'css/leaflet-compact.css',
    '../../css/btn-primary.css',
    '../../assets/css/gallery.css',
    '../../css/compact-form.css',
    '../../css/unified-price-display.css',
    '../../css/page-title-common.css',
    '../../css/unified-calculator-layout.css'
];

// 갤러리 에셋 자동 포함
if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
    include_gallery_assets();
}

// 🏗️ 통합 템플릿 시작 (헤더 + 네비게이션)
include "../../includes/template_start.php";
?>

    <!-- 📄 메인 컨텐츠 영역 (품목별로 다름) -->
    <div class="customer-container">
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>📄 전단지 견적 안내</h1>
        </div>
        
        <div class="leaflet-grid">
            <!-- 좌측: 통합 갤러리 섹션 (50%) -->
            <section class="leaflet-gallery" aria-label="전단지 샘플 갤러리">
                <?php
                // 원클릭 갤러리 포함 (공통 헬퍼 사용)
                if (function_exists("include_product_gallery")) { 
                    include_product_gallery('inserted'); 
                }
                ?>
            </section>
            
            <!-- 우측: 계산기 섹션 (50%) -->
            <aside class="calculator-section leaflet-style" aria-label="실시간 견적 계산기">
                
                <form id="orderForm" method="post">
                    <div class="options-grid form-grid-compact">
                        <!-- 인쇄색상 -->
                        <div class="option-group form-field">
                            <label class="option-label" for="MY_type">인쇄색상</label>
                            <select name="MY_type" id="MY_type" class="option-select" required>
                                <option value="4">4도 (칼라인쇄)</option>
                                <option value="1">1도 (단색인쇄)</option>
                            </select>
                        </div>
                        
                        <!-- 종이종류 -->
                        <div class="option-group form-field">
                            <label class="option-label" for="MY_Fsd">종이종류</label>
                            <select name="MY_Fsd" id="MY_Fsd" class="option-select" required>
                                <option value="1">아트지 150g</option>
                                <option value="2">아트지 200g</option>
                                <option value="3">스노우지 80g</option>
                            </select>
                        </div>
                        
                        <!-- 종이규격 -->
                        <div class="option-group form-field">
                            <label class="option-label" for="PN_type">종이규격</label>
                            <select name="PN_type" id="PN_type" class="option-select" required>
                                <option value="A4" selected>A4 (210x297)</option>
                                <option value="A5">A5 (148x210)</option>
                                <option value="A3">A3 (297x420)</option>
                            </select>
                        </div>
                        
                        <!-- 인쇄면 -->
                        <div class="option-group form-field">
                            <label class="option-label" for="POtype">인쇄면</label>
                            <select name="POtype" id="POtype" class="option-select" required>
                                <option value="1" selected>단면 (앞면만)</option>
                                <option value="2">양면 (앞뒤 모두)</option>
                            </select>
                        </div>
                        
                        <!-- 수량 -->
                        <div class="option-group form-field">
                            <label class="option-label" for="MY_amount">수량</label>
                            <select name="MY_amount" id="MY_amount" class="option-select" required>
                                <option value="100">100매</option>
                                <option value="200">200매</option>
                                <option value="300">300매</option>
                                <option value="500">500매</option>
                                <option value="1000">1,000매</option>
                            </select>
                        </div>
                        
                        <!-- 편집디자인 -->
                        <div class="option-group form-field full-width">
                            <label class="option-label" for="ordertype">편집디자인</label>
                            <select name="ordertype" id="ordertype" class="option-select" required>
                                <option value="total">디자인+인쇄 (전체 의뢰)</option>
                                <option value="print" selected>인쇄만 의뢰 (파일 준비완료)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-label">예상 견적 금액</div>
                        <div class="price-amount" id="totalPrice">옵션을 선택해주세요</div>
                        <div class="price-details">부가세 및 배송비 별도</div>
                    </div>
                    
                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            📁 파일 업로드 & 주문하기
                        </button>
                    </div>
                </form>
                
            </aside>
        </div>
    </div>

<?php 
// 🏗️ 통합 템플릿 종료 (푸터 + JavaScript)
include "../../includes/template_end.php"; 
?>