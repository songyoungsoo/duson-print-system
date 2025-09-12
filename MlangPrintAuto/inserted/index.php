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
    
    <!-- 전단지 전용 컴팩트 레이아웃 CSS - 테스트용 비활성화 -->
    <!-- <link rel="stylesheet" href="css/leaflet-compact.css"> -->
    
    <!-- 갤러리 시스템 CSS -->
    <link rel="stylesheet" href="../../assets/css/gallery.css">
    
    <!-- 🎯 통합 공통 스타일 CSS (최종 로딩으로 최우선권 확보) -->
    <link rel="stylesheet" href="../../css/common-styles.css">
    
    <!-- 추가 옵션 시스템 전용 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">
    
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

<body>
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
                <form id="orderForm" method="post">
                    <div class="options-grid form-grid-compact">
                        <!-- 인쇄색상 -->
                        <div class="form-group-horizontal">
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
                        <div class="form-group-horizontal">
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
                        <div class="form-group-horizontal">
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
                        <div class="form-group-horizontal">
                            <label class="option-label" for="POtype">인쇄면</label>
                            <select name="POtype" id="POtype" class="option-select" required>
                                <option value="1" selected>단면 (앞면만)</option>
                                <option value="2">양면 (앞뒤 모두)</option>
                            </select>
                        </div>
                        
                        <!-- 수량 -->
                        <div class="form-group-horizontal">
                            <label class="option-label" for="MY_amount">수량</label>
                            <select name="MY_amount" id="MY_amount" class="option-select" required>
                                <option value="">수량을 선택해주세요</option>
                            </select>
                        </div>
                        
                        <!-- 편집디자인 -->
                        <div class="form-group-horizontal">
                            <label class="option-label" for="ordertype">편집디자인</label>
                            <select name="ordertype" id="ordertype" class="option-select" required>
                                <option value="total">디자인+인쇄</option>
                                <option value="print" selected>인쇄만 의뢰</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php
                    // 추가 옵션 시스템 포함
                    include_once "../../includes/AdditionalOptions.php";
                    $additionalOptions = getAdditionalOptions($connect);
                    echo $additionalOptions->generateOptionsHtml('inserted');
                    ?>
                    
                    <!-- 실시간 가격 표시 -->
                    <div class="price-display" id="priceDisplay">
                        <div class="price-amount" id="priceAmount">견적 계산 필요</div>
                        <div class="price-details" id="priceDetails">
                            <!-- 인라인 가격 표시 (예시) -->
                            <div class="price-breakdown">
                                <div class="price-item">
                                    <span class="price-item-label">인쇄비:</span>
                                    <span class="price-item-value">계산 중</span>
                                </div>
                                <div class="price-divider"></div>
                                <div class="price-item">
                                    <span class="price-item-label">디자인비:</span>
                                    <span class="price-item-value">계산 중</span>
                                </div>
                                <div class="price-divider"></div>
                                <div class="price-item final">
                                    <span class="price-item-label">부가세 포함:</span>
                                    <span class="price-item-value">계산 중</span>
                                </div>
                            </div>
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
                    <input type="hidden" name="price" id="calculated_price" value="">
                    <input type="hidden" name="vat_price" id="calculated_vat_price" value="">
                </form>
            </aside>
        </div>
    </div>

    <?php
    // 전단지 모달 설정
    $modalProductName = '전단지';
    $modalProductIcon = '📎';
    
    // 공통 업로드 모달 포함
    include "../../includes/upload_modal.php";
    ?>

    <!-- 갤러리 모달은 include_product_gallery()에서 자동 포함됨 -->

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <?php
    // 갤러리 모달은 if (function_exists("include_product_gallery")) { include_product_gallery()에서 자동 포함됨
    ?>


    <!-- 전단지 안내 섹션 -->
    <section class="flyer-info-section">
        <div class="flyer-info-grid">
            <!-- 합판 전단지 카드 -->
            <div class="flyer-card">
                <!-- 제목 (네모 박스 반전글) -->
                <div class="hapan-title">
                    <h3>📄 합판 전단지</h3>
                </div>
                
                <!-- 헤어라인 -->
                <div class="flyer-hairline"></div>
                
                <!-- 내용 -->
                <div class="flyer-content">
                    <p>일정량의 고객 인쇄물을 한판에 모아서 인쇄 제작하는 상품으로 저렴한 가격과 빠른 제작시간이 특징인 상품입니다. 일반 길거리 대량 배포용 전단지를 제작하실 때 선택하시면 됩니다.</p>
                    
                    <div class="flyer-specs">
                        <h4>📏 제작 가능 사이즈</h4>
                        <ul>
                            <li>A2 (420 x 594 mm)</li>
                            <li>A3 (297 x 420 mm)</li>
                            <li>A4 (210 x 297 mm)</li>
                            <li>4절 (367 x 517mm)</li>
                            <li>8절 (257 x 367 mm)</li>
                            <li>16절 (182 x 257 mm)</li>
                        </ul>
                        <p><strong>작업사이즈:</strong> 재단사이즈에서 사방 1.5mm씩 여분</p>
                    </div>
                    
                    <div class="flyer-tip">
                        <p>💡 TIP! 작업 템플릿을 다운 받아 사용하시면 더욱 정확하고 편리하게 작업하실 수 있습니다!</p>
                    </div>
                </div>
            </div>
            
            <!-- 독판 전단지 카드 -->
            <div class="flyer-card">
                <!-- 제목 (네모 박스 반전글) -->
                <div class="dokpan-title">
                    <h3>📋 독판 전단지</h3>
                </div>
                
                <!-- 헤어라인 -->
                <div class="flyer-hairline"></div>
                
                <!-- 내용 -->
                <div class="flyer-content">
                    <p>나만의 인쇄물을 단독으로 인쇄할 수 있는 상품으로 고급 인쇄물 제작을 원할 때 선택하시면 됩니다. 다양한 용지 선택과 후가공 선택이 가능한 상품입니다.</p>
                    
                    <div class="flyer-specs">
                        <h4>⚙️ 상세 정보</h4>
                        <ul>
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

    <!-- 공통 업로드 모달 JavaScript -->
    <script src="../../includes/upload_modal.js"></script>
    
    <!-- 전단지 전용 스크립트 -->
    <script src="js/leaflet-compact.js"></script>
    
    <!-- 추가 옵션 시스템 스크립트 -->
    <script src="js/additional-options.js"></script>
    
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
    <!-- 모든 스타일은 common-styles.css에서 통합 관리됨 -->
</body>
</html>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>