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
$GGTABLE = "mlangprintauto_transactioncate";
$TABLE = "mlangprintauto_transactioncate";

// 공통 함수 및 설정
include "../../includes/functions.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 통합 갤러리 시스템 초기화
if (file_exists('../../includes/gallery_helper.php')) { if (file_exists('../../includes/gallery_helper.php')) { include_once '../../includes/gallery_helper.php'; } }
if (function_exists("init_gallery_system")) { init_gallery_system("inserted"); }

// 세션 및 기본 설정
check_session();
check_db_connection($db);

// 로그 정보 생성
$log_info = generateLogInfo();

// 공통 인증 시스템 사용
include "../../includes/auth.php";
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

// 기본값 설정 - 첫 번째 옵션을 자동 선택
$default_values = [
    'MY_type' => $firstColorNo,
    'MY_Fsd' => !empty($paperTypeOptions) ? $paperTypeOptions[0]['no'] : '',
    'PN_type' => !empty($paperSizeOptions) ? $paperSizeOptions[0]['no'] : '',
    'POtype' => '1', // 단면 기본
    'MY_amount' => '',
    'ordertype' => 'print' // 인쇄만 기본
];

// 디버그: 기본값 확인
echo "<!-- Debug: paperTypeOptions count: " . count($paperTypeOptions) . " -->";
echo "<!-- Debug: default MY_Fsd: " . $default_values['MY_Fsd'] . " -->";
if (!empty($paperTypeOptions)) {
    echo "<!-- Debug: first paperType: " . $paperTypeOptions[0]['title'] . " -->";
}

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
    <!-- 인라인 스타일 분리 CSS -->
    <link rel="stylesheet" href="css/leaflet-inline.css">
    <!-- 공통 버튼 스타일 CSS -->
    <link rel="stylesheet" href="../../css/btn-primary.css">
    
    <!-- 통합 갤러리 CSS -->
    <link rel="stylesheet" href="../../assets/css/gallery.css">
    <!-- 컴팩트 폼 그리드 CSS (모든 품목 공통) -->
    <link rel="stylesheet" href="../../css/compact-form.css">
    
    <!-- 통합 가격 표시 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-price-display.css">
    
    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php
    // 갤러리 에셋 자동 포함
    if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
        if (function_exists("include_gallery_assets")) { include_gallery_assets(); }
    }
    ?>
</head>

<body class="leaflet-page">
    <div class="leaflet-card">
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>📄 전단지 견적 안내</h1>
            <!-- <p>컴팩트 버전 - 갤러리와 실시간 계산기</p> -->
        </div>
        
        <div class="leaflet-grid">
            <!-- 좌측: 통합 갤러리 섹션 -->
            <section class="leaflet-gallery" aria-label="전단지 샘플 갤러리">
                <?php
                // 원클릭 갤러리 포함 (공통 헬퍼 사용)
                if (function_exists("include_product_gallery")) { include_product_gallery('inserted'); }
                ?>
            </section>
            
            <!-- 우측: 계산기 섹션 (50%) -->
            <aside class="calculator-section leaflet-style" aria-label="실시간 견적 계산기">
                <div class="calculator-header">
                    <h3>💰견적 안내</h3>
                </div>
                
                <form id="orderForm" method="post">
                    <div class="options-grid form-grid-compact">
                        <!-- 인쇄색상 -->
                        <div class="option-group form-field">
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
                        <div class="option-group form-field">
                            <label class="option-label" for="MY_Fsd">종이종류</label>
                            <select name="MY_Fsd" id="MY_Fsd" class="option-select" required>
                                <?php if (empty($paperTypeOptions)): ?>
                                <option value="">종이종류를 선택해주세요</option>
                                <?php else: ?>
                                <?php foreach ($paperTypeOptions as $index => $option): ?>
                                <option value="<?php echo htmlspecialchars($option['no']); ?>" 
                                    <?php echo ($index === 0 || $option['no'] == $default_values['MY_Fsd']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['title']); ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- 종이규격 -->
                        <div class="option-group form-field">
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
                                <option value="">수량을 선택해주세요</option>
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
                    
                    <!-- 스티커 방식의 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-label">견적 금액</div>
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            모든 옵션을 선택하면 자동으로 계산됩니다
                        </div>
                    </div>

                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton">
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
    <div id="uploadModal" class="upload-modal">
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
                
                <div class="uploaded-files" id="modalUploadedFiles">
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

    <!-- 갤러리 모달은 include_product_gallery()에서 자동 포함됨 -->

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <?php
    // 갤러리 모달은 if (function_exists("include_product_gallery")) { include_product_gallery()에서 자동 포함됨
    ?>


    <!-- 전단지 안내 섹션 -->
    <section style="margin-top: 5px; margin-bottom: 0.2rem;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
            <!-- 합판 전단지 카드 -->
            <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border: 1px solid #e9ecef;">
                <!-- 제목 (네모 박스 반전글) -->
                <div style="background: #4caf50; color: white; padding: 20px; text-align: center;">
                    <h3 style="margin: 0; font-size: 1.3rem; font-weight: 700;">📄 합판 전단지</h3>
                </div>
                
                <!-- 헤어라인 -->
                <div style="height: 2px; background: linear-gradient(90deg, transparent, #4caf50, transparent);"></div>
                
                <!-- 내용 -->
                <div style="padding: 25px; line-height: 1.6;">
                    <p style="margin-bottom: 1.5rem; color: #495057; font-weight: 500;">일정량의 고객 인쇄물을 한판에 모아서 인쇄 제작하는 상품으로 저렴한 가격과 빠른 제작시간이 특징인 상품입니다. 일반 길거리 대량 배포용 전단지를 제작하실 때 선택하시면 됩니다.</p>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 1rem;">
                        <h4 style="color: #4caf50; margin-bottom: 10px; font-size: 1rem;">📏 제작 가능 사이즈</h4>
                        <ul style="margin: 0; padding-left: 1.2rem; color: #555; font-size: 0.9rem;">
                            <li>A2 (420 x 594 mm)</li>
                            <li>A3 (297 x 420 mm)</li>
                            <li>A4 (210 x 297 mm)</li>
                            <li>4절 (367 x 517mm)</li>
                            <li>8절 (257 x 367 mm)</li>
                            <li>16절 (182 x 257 mm)</li>
                        </ul>
                        <p style="margin-top: 10px; color: #666; font-size: 0.85rem;"><strong>작업사이즈:</strong> 재단사이즈에서 사방 1.5mm씩 여분</p>
                    </div>
                    
                    <div style="background: #e8f5e8; padding: 12px; border-radius: 8px; border-left: 4px solid #4caf50;">
                        <p style="margin: 0; color: #2e7d32; font-size: 0.9rem; font-weight: 500;">💡 TIP! 작업 템플릿을 다운 받아 사용하시면 더욱 정확하고 편리하게 작업하실 수 있습니다!</p>
                    </div>
                </div>
            </div>
            
            <!-- 독판 전단지 카드 -->
            <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border: 1px solid #e9ecef;">
                <!-- 제목 (네모 박스 반전글) -->
                <div style="background: #2196f3; color: white; padding: 20px; text-align: center;">
                    <h3 style="margin: 0; font-size: 1.3rem; font-weight: 700;">📋 독판 전단지</h3>
                </div>
                
                <!-- 헤어라인 -->
                <div style="height: 2px; background: linear-gradient(90deg, transparent, #2196f3, transparent);"></div>
                
                <!-- 내용 -->
                <div style="padding: 25px; line-height: 1.6;">
                    <p style="margin-bottom: 1.5rem; color: #495057; font-weight: 500;">나만의 인쇄물을 단독으로 인쇄할 수 있는 상품으로 고급 인쇄물 제작을 원할 때 선택하시면 됩니다. 다양한 용지 선택과 후가공 선택이 가능한 상품입니다.</p>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 1rem;">
                        <h4 style="color: #2196f3; margin-bottom: 10px; font-size: 1rem;">⚙️ 상세 정보</h4>
                        <ul style="margin: 0; padding-left: 1.2rem; color: #555; font-size: 0.9rem;">
                            <li><strong>작업사이즈:</strong> 재단사이즈에서 사방 1.5mm씩 여분</li>
                            <li><strong>인쇄유형:</strong> 옵셋인쇄</li>
                            <li><strong>출고:</strong> 매일 출고</li>
                            <li><strong>후가공:</strong> 각종 박, 형압, 엠보, 타공, 접지, 코팅, 도무송, 접착, 오시, 미싱, 넘버링</li>
                            <li><strong>재질:</strong> 아트지, 스노우화이트, 모조지 등</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
</body>
</html>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>