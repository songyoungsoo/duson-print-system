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

// 초기 구분값 가져오기
$initial_type = "";
$type_result = mysqli_query($connect, "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable='NcrFlambeau' AND BigNo='0' ORDER BY no ASC LIMIT 1");
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $initial_type = $type_row['no'];
    $default_values['MY_type'] = $initial_type;
}

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
            <!-- 좌측: 갤러리 섹션 (50%) -->
            <section class="ncr-gallery" aria-label="양식지 샘플 갤러리">
                <div class="gallery-title">📋 양식지 샘플 갤러리</div>
                
                <!-- 메인 이미지 표시 영역 -->
                <div class="lightbox-viewer" id="zoomBox">
                    <!-- 배경 이미지로 표시됩니다 -->
                </div>
                
                <!-- 썸네일 이미지들 -->
                <div class="thumbnail-strip" id="thumbnailStrip">
                    <!-- 썸네일들이 여기에 동적으로 로드됩니다 -->
                </div>
                
                <!-- 로딩 상태 -->
                <div id="galleryLoading" class="gallery-loading">
                    <p>이미지를 불러오는 중...</p>
                </div>
                
                <!-- 에러 상태 -->
                <div id="galleryError" class="gallery-error" style="display: none;">
                    <p>이미지를 불러올 수 없습니다.</p>
                </div>
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

    <!-- JavaScript 파일 포함 -->
    <script src="js/ncrflambeau-compact.js"></script>
</body>
</html>