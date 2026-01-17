<?php
session_start();
$session_id = session_id();

// 테마 시스템 로드
include_once __DIR__ . '/../../includes/theme_loader.php';

// 견적서 모달용 간소화 모드 체크
$isQuotationMode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';
$isAdminQuoteMode = isset($_GET['mode']) && $_GET['mode'] === 'admin_quote';

// 출력 버퍼 관리 및 에러 설정 (명함 성공 패턴)
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 보안 상수 정의 후 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 방문자 추적 시스템
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

// 페이지 설정
$page_title = '두손기획인쇄 - 전단지 컴팩트 견적';
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

// 통합 갤러리 시스템
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

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_html($page_title); ?></title>

    <!-- 세션 ID 메타 태그 -->
    <meta name="session-id" content="<?php echo htmlspecialchars($session_id); ?>">

    <!-- 🎨 통합 컬러 시스템 (우선 로딩) -->
    <link rel="stylesheet" href="../../css/color-system-unified.css">

    <!-- 전단지 전용 컴팩트 레이아웃 CSS -->
    <link rel="stylesheet" href="../../css/product-layout.css?v=<?php echo filemtime(__DIR__ . '/../../css/product-layout.css'); ?>">

    <!-- 🎯 통합 공통 스타일 CSS (먼저 로드) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">

    <!-- 📎 공통 파일 업로드 모달 CSS (최종 로드로 최우선권 확보) -->
    <link rel="stylesheet" href="../../css/upload-modal-common.css?v=<?php echo time(); ?>">

    <!-- 🎨 브랜드 디자인 시스템 CSS -->
    <link rel="stylesheet" href="../../css/brand-design-system.css">

    <!-- 추가 옵션 시스템 전용 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- 🆕 Duson 통합 갤러리 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">

    <!-- 통일 인라인 폼 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css?v=<?php echo filemtime(__DIR__ . '/../../css/unified-inline-form.css'); ?>">

    <?php
    // 통합 갤러리 시스템 에셋 포함
    if (defined("GALLERY_ASSETS_NEEDED") && function_exists("include_gallery_assets")) {
        include_gallery_assets();
    }
    ?>

    <!-- 노토 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- 통합 갤러리 시스템 CSS (위에서 자동 포함됨) -->

    <!-- 파일 업로드 컴포넌트 JavaScript -->
    <script src="../../includes/js/UniversalFileUpload.js"></script>

    <!-- 견적서 모달용 공통 스타일 -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">

    <!-- 테마 시스템 CSS -->
    <?php ThemeLoader::renderCSS(); ?>

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
</head>

<body class="inserted-page<?php echo ($isQuotationMode || $isAdminQuoteMode) ? ' quotation-modal-mode' : ''; ?>" <?php ThemeLoader::renderBodyAttributes(); ?>>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/header-ui.php"; ?>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) include "../../includes/nav.php"; ?>

    <div class="product-container">
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>전단지 견적 안내</h1>
        </div>

        <div class="product-content">
            <!-- 좌측: 통합 갤러리 시스템 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="전단지 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'inserted';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </section>

            <!-- 우측: 계산기 섹션 -->
            <aside class="product-calculator" aria-label="실시간 견적 계산기">
                <form id="orderForm" name="choiceForm" method="post">
                    <!-- 통일 인라인 폼 시스템 - 전단지 페이지 -->
                    <div class="inline-form-container">
                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_type">색상</label>
                            <select name="MY_type" id="MY_type" class="inline-select" required>
                                <?php foreach ($colorOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['no']); ?>"
                                    <?php echo ($option['no'] == $default_values['MY_type']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="inline-note">인쇄 색상을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_Fsd">종류</label>
                            <select name="MY_Fsd" id="MY_Fsd" class="inline-select" required>
                                <option value="">먼저 인쇄색상을 선택해주세요</option>
                                <?php foreach ($paperTypeOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['no']); ?>"
                                    <?php echo ($option['no'] == $default_values['MY_Fsd']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="inline-note">원하는 용지를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="PN_type">규격</label>
                            <select name="PN_type" id="PN_type" class="inline-select" required>
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
                            <span class="inline-note">인쇄 사이즈를 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="POtype">인쇄면</label>
                            <select name="POtype" id="POtype" class="inline-select" required>
                                <option value="1" selected>단면</option>
                                <option value="2">양면</option>
                            </select>
                            <span class="inline-note">단면 또는 양면 인쇄</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="MY_amount">수량</label>
                            <select name="MY_amount" id="MY_amount" class="inline-select" required>
                                <option value="">먼저 규격을 선택해주세요</option>
                            </select>
                            <span class="inline-note">원하시는 수량을 선택하세요</span>
                        </div>

                        <div class="inline-form-row">
                            <label class="inline-label" for="ordertype">편집비</label>
                            <select name="ordertype" id="ordertype" class="inline-select" required>
                                <option value="print" selected>인쇄만 의뢰</option>
                                <option value="total">디자인+인쇄</option>
                            </select>
                            <span class="inline-note">디자인 작업 포함 여부</span>
                        </div>
                    </div>
                    
                    <!-- 추가 옵션 섹션 -->
                    <!-- 🆕 전단지 추가 옵션 섹션 (명함 스타일) -->
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

                    <?php if ($isQuotationMode || $isAdminQuoteMode): ?>
                    <!-- 견적서 모달 모드: 견적서에 적용 버튼 -->
                    <div class="quotation-apply-button">
                        <button type="button" class="btn-quotation-apply" onclick="applyToQuotation()">
                            ✓ 견적서에 적용
                        </button>
                    </div>
                    <?php else: ?>
                    <!-- 일반 모드: 파일 업로드 및 주문하기 / 견적 요청 버튼 -->
                    <div class="action-buttons" id="actionButtons">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            파일 업로드 및 주문하기
                        </button>
                    </div>
                    <?php endif; ?>
                    
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
                    <input type="hidden" name="MY_amountRight" id="MY_amountRight" value="">
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

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
    <!-- 합판 전단지 상세 설명 섹션 (하단 설명방법) -->
    <div class="inserted-detail-combined">
        <?php include "explane_inserted.php"; ?>
    </div>


    <!-- 전단지 안내 섹션 -->
    <section class="flyer-info-section">
        <div class="flyer-info-grid">
            <!-- 합판 전단지 카드 -->
            <div class="flyer-card">
                <!-- 제목 (네모 박스 반전글) -->
                <div class="hapan-title">
                    <h3>합판 전단지</h3>
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
                        <p>TIP! 작업 템플릿을 다운 받아 사용하시면 더욱 정확하고 편리하게 작업하실 수 있습니다!</p>
                    </div>
                </div>
            </div>
            
            <!-- 독판 전단지 카드 -->
            <div class="flyer-card">
                <!-- 제목 (네모 박스 반전글) -->
                <div class="dokpan-title">
                    <h3>독판 전단지</h3>
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
    <?php endif; ?>

    <?php
    // 공통 푸터 포함 (견적서 모달에서는 제외)
    if (!$isQuotationMode && !$isAdminQuoteMode) {
        include "../../includes/footer.php";
    }
    ?>

    <!-- 공통 업로드 모달 JavaScript -->
    <script src="../../includes/upload_modal.js?v=1759243573751415300"></script>
    
    <!-- 전단지 전용 스크립트 -->
    <script src="js/leaflet-compact.js?v=<?php echo time(); ?>"></script>

    <!-- 🆕 추가 옵션 시스템 스크립트 (명함 스타일) -->
    <script src="js/leaflet-premium-options.js?v=<?php echo time(); ?>"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('전단지 페이지 초기화 완료 - 통합 갤러리 시스템');

        // ✅ 추가 옵션은 additional-options.js에서 관리
        // (중복 이벤트 리스너 제거 - additional-options.js가 자동으로 처리)

        // 로그인 메시지가 있으면 모달 자동 표시
        <?php if (!empty($login_message)): ?>
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
        <?php endif; ?>
    });
        // 전단지 전용 장바구니 추가 함수 (통합 모달 패턴)
        window.handleModalBasketAdd = function(onSuccess, onError) {
            console.log("전단지 장바구니 추가 시작");

            // 현재 가격 데이터가 없으면 에러
            if (!window.currentPriceData || !window.currentPriceData.Order_PriceForm) {
                console.error("가격 계산이 필요합니다. currentPriceData:", window.currentPriceData);
                if (onError) onError("먼저 견적 계산을 해주세요. '견적 계산' 버튼을 눌러주세요.");
                return;
            }

            console.log("✅ 가격 데이터 확인:", window.currentPriceData);

            const formData = new FormData();
            formData.append("action", "add_to_basket");
            formData.append("product_type", "inserted"); // ✅ 전단지는 inserted로 저장

            // 전단지 폼 필드 (실제 ID 사용)
            const myType = document.getElementById("MY_type");
            const myFsd = document.getElementById("MY_Fsd");
            const pnType = document.getElementById("PN_type");
            const poType = document.getElementById("POtype");
            const myAmount = document.getElementById("MY_amount");
            const orderType = document.getElementById("ordertype");

            if (!myType || !myFsd || !pnType || !poType || !myAmount || !orderType) {
                console.error("필수 폼 필드를 찾을 수 없습니다");
                if (onError) onError("필수 옵션을 모두 선택해주세요.");
                return;
            }

            formData.append("MY_type", myType.value);
            formData.append("MY_Fsd", myFsd.value);
            formData.append("PN_type", pnType.value);
            formData.append("POtype", poType.value);
            formData.append("MY_amount", myAmount.value);
            formData.append("ordertype", orderType.value);

            // 가격 데이터 안전하게 처리
            const totalPrice = window.currentPriceData && window.currentPriceData.Order_PriceForm
                ? Math.round(window.currentPriceData.Order_PriceForm)
                : 0;
            const vatPrice = window.currentPriceData && window.currentPriceData.Total_PriceForm
                ? Math.round(window.currentPriceData.Total_PriceForm)
                : 0;

            console.log("💰 전달할 가격 정보:", {totalPrice, vatPrice, currentPriceData: window.currentPriceData});

            formData.append("price", totalPrice);
            formData.append("vat_price", vatPrice);

            // 매수(MY_amountRight) 데이터 전송 (quantityTwo)
            const myAmountRight = document.getElementById("MY_amountRight");
            if (myAmountRight && myAmountRight.value) {
                formData.append("MY_amountRight", myAmountRight.value);
                console.log("📊 매수 데이터:", myAmountRight.value);
            }

            // 추가 옵션 데이터 포함 (올바른 ID 사용)
            const coatingToggle = document.getElementById("coating_enabled");
            const foldingToggle = document.getElementById("folding_enabled");
            const creasingToggle = document.getElementById("creasing_enabled");

            if (coatingToggle && coatingToggle.checked) {
                formData.append("coating_enabled", "1");
                const coatingType = document.getElementById("coating_type");
                if (coatingType) {
                    formData.append("coating_type", coatingType.value);
                }
                const coatingPrice = document.getElementById("coating_price");
                if (coatingPrice) {
                    formData.append("coating_price", coatingPrice.value); // hidden input은 .value 사용
                }
            } else {
                formData.append("coating_enabled", "0");
                formData.append("coating_type", "");
                formData.append("coating_price", "0");
            }

            if (foldingToggle && foldingToggle.checked) {
                formData.append("folding_enabled", "1");
                const foldingType = document.getElementById("folding_type");
                if (foldingType) {
                    formData.append("folding_type", foldingType.value);
                }
                const foldingPrice = document.getElementById("folding_price");
                if (foldingPrice) {
                    formData.append("folding_price", foldingPrice.value);
                }
            } else {
                formData.append("folding_enabled", "0");
                formData.append("folding_type", "");
                formData.append("folding_price", "0");
            }

            if (creasingToggle && creasingToggle.checked) {
                formData.append("creasing_enabled", "1");
                const creasingLines = document.getElementById("creasing_lines");
                if (creasingLines) {
                    formData.append("creasing_lines", creasingLines.value);
                }
                const creasingPrice = document.getElementById("creasing_price");
                if (creasingPrice) {
                    formData.append("creasing_price", creasingPrice.value);
                }
            } else {
                formData.append("creasing_enabled", "0");
                formData.append("creasing_lines", "0");
                formData.append("creasing_price", "0");
            }

            // 추가 옵션 총액 (hidden input)
            const additionalOptionsTotal = document.getElementById("additional_options_total");
            if (additionalOptionsTotal) {
                formData.append("additional_options_total", additionalOptionsTotal.value);
            } else {
                formData.append("additional_options_total", "0");
            }

            const workMemo = document.getElementById("modalWorkMemo");
            if (workMemo) formData.append("work_memo", workMemo.value);

            formData.append("upload_method", window.selectedUploadMethod || "upload");

            // ✅ 업로드된 파일들 추가 (window.uploadedFiles 사용 - 명함 패턴)
            if (window.uploadedFiles && window.uploadedFiles.length > 0) {
                console.log("📎 전송 전 uploadedFiles 상태:", window.uploadedFiles);
                window.uploadedFiles.forEach((fileObj, index) => {
                    console.log(`📎 파일 ${index} 추가:`, {
                        name: fileObj.name,
                        size: fileObj.size,
                        type: fileObj.type,
                        hasFileObject: !!fileObj.file,
                        isActualFile: fileObj.file instanceof File
                    });
                    // ⚠️ CRITICAL FIX: fileObj.file은 실제 File 객체, fileObj는 래퍼 객체
                    formData.append("uploaded_files[]", fileObj.file);
                });
                console.log("📎 전송할 파일 개수:", window.uploadedFiles.length);
            } else {
                console.log("⚠️ 업로드된 파일 없음");
            }

            // 🔍 [추가된 디버그] 전송 직전 데이터 확인
            const finalMesuValue = formData.get("MY_amountRight");
            console.log(`[DEBUG] fetch 직전 MY_amountRight 값: ${finalMesuValue}`);

            // 🔍 FormData 내용 확인 (디버그)
            console.log("📦 FormData entries:");
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(`  ${key}:`, {name: value.name, size: value.size, type: value.type});
                } else {
                    console.log(`  ${key}:`, value);
                }
            }

            fetch("add_to_basket.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                console.log("응답 상태:", response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP 오류: ${response.status} ${response.statusText}`);
                }
                return response.text(); // 먼저 텍스트로 받아서 확인
            })
            .then(text => {
                console.log("서버 응답 (원본):", text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        if (onSuccess) onSuccess(data);
                    } else {
                        if (onError) onError(data.message || "장바구니 추가 실패");
                    }
                } catch (e) {
                    console.error("JSON 파싱 오류:", e);
                    console.error("응답 내용:", text);
                    if (onError) onError("서버 응답 형식 오류: " + text.substring(0, 100));
                }
            })
            .catch(error => {
                console.error("장바구니 추가 오류:", error);
                if (onError) onError("네트워크 오류: " + error.message);
            });
        };

        // Phase 5: 견적 요청 함수
        window.addToQuotation = function() {
            console.log('💰 견적 요청 시작 - 전단지');

            // 가격 계산 확인
            if (!window.currentPriceData || !window.currentPriceData.total_price) {
                alert('가격을 먼저 계산해주세요.');
                return;
            }

            // 프리미엄 옵션 재계산
            const premiumTotal = calculatePremiumOptions();
            console.log('💰 프리미엄 옵션 총액:', premiumTotal);

            // 폼 데이터 수집
            const formData = new FormData();
            formData.append('product_type', 'inserted');
            formData.append('MY_type', document.getElementById('MY_type').value);
            formData.append('PN_type', document.getElementById('PN_type').value);
            formData.append('MY_Fsd', document.getElementById('MY_Fsd').value);
            formData.append('POtype', document.getElementById('POtype').value);
            formData.append('MY_amount', document.getElementById('MY_amount').value);
            formData.append('mesu', document.getElementById('mesu').value);
            formData.append('ordertype', document.getElementById('ordertype').value);
            formData.append('price', Math.round(window.currentPriceData.total_price));
            formData.append('vat_price', Math.round(window.currentPriceData.vat_price));

            // 프리미엄 옵션 추가
            ['coating', 'folding', 'creasing', 'binding', 'packaging'].forEach(option => {
                const checkbox = document.getElementById(option + '_enabled');
                if (checkbox && checkbox.checked) {
                    formData.append(option + '_enabled', '1');
                    const typeSelect = document.getElementById(option + '_type');
                    if (typeSelect) {
                        formData.append(option + '_type', typeSelect.value);
                    }
                    formData.append(option + '_price', document.getElementById(option + '_price').value || '0');
                }
            });
            formData.append('premium_options_total', premiumTotal);

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

    <!-- 통합 갤러리 시스템 JavaScript -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 견적서 모달 공통 JavaScript -->
    <script src="../../js/quotation-modal-common.js?v=<?php echo time(); ?>"></script>

    <!-- 전단지 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->

    <!-- 테마 스위처 -->
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) ThemeLoader::renderSwitcher('bottom-right'); ?>
    <?php if (!$isQuotationMode && !$isAdminQuoteMode) ThemeLoader::renderSwitcherJS(); ?>

<?php if ($isAdminQuoteMode): ?>
    <!-- 관리자 견적서 모드: postMessage로 부모 창에 데이터 전송 -->
    <script>
    window.applyToQuotation = function() {
        console.log('🚀 [관리자 견적서-전단지] applyToQuotation() 호출');

        // 1. 필수 필드 검증
        const MY_type = document.getElementById('MY_type')?.value;
        const MY_Fsd = document.getElementById('MY_Fsd')?.value;
        const PN_type = document.getElementById('PN_type')?.value;
        const MY_amount = document.getElementById('MY_amount')?.value;

        if (!MY_type || !MY_Fsd || !PN_type || !MY_amount) {
            alert('모든 필수 옵션을 선택해주세요.');
            return;
        }

        // 2. 가격 확인 (window.currentPriceData 사용)
        if (!window.currentPriceData || !window.currentPriceData.Order_PriceForm) {
            alert('가격을 먼저 계산해주세요.');
            return;
        }
        const supplyPrice = Math.round(window.currentPriceData.Order_PriceForm) || 0;

        if (supplyPrice <= 0) {
            alert('가격을 먼저 계산해주세요.');
            return;
        }

        // 3. 옵션 텍스트 추출 (정확한 필드 매핑)
        const colorSelect = document.getElementById('MY_type');      // 색상: 칼라(CMYK)
        const paperSelect = document.getElementById('MY_Fsd');       // 용지: 90g아트지(합판전단)
        const sizeSelect = document.getElementById('PN_type');       // 규격: A4 (210x297)
        const sidesSelect = document.getElementById('POtype');       // 인쇄면: 단면/양면
        const amountSelect = document.getElementById('MY_amount');
        const orderTypeSelect = document.getElementById('ordertype');

        const colorText = colorSelect?.selectedOptions[0]?.text || '';           // "칼라(CMYK)"
        const paperText = paperSelect?.selectedOptions[0]?.text || '';           // "90g아트지(합판전단)"
        const sizeText = sizeSelect?.selectedOptions[0]?.text || '';             // "A4 (210x297)"
        const sidesValue = sidesSelect?.value || '1';                            // "1"=단면, "2"=양면
        const sidesText = sidesValue === '2' ? '양면칼라' : '단면칼라';          // "단면칼라" 또는 "양면칼라"
        const quantityText = amountSelect?.selectedOptions[0]?.text || MY_amount;
        const orderType = orderTypeSelect?.value || 'print';
        const designText = orderType === 'total' ? '디자인+인쇄' : '인쇄만';

        // 4. 수량 파싱 (연 단위 + 매수 표시)
        let quantity = parseFloat(MY_amount) || 1;
        let unit = '연';

        // ✅ 매수는 DB에서 가져온 quantityTwo 값 사용 (계산 금지)
        // window.currentPriceData.MY_amountRight = "250장" 형식
        let sheets = 0;
        const myAmountRight = window.currentPriceData?.MY_amountRight || '';
        const sheetsMatch = myAmountRight.match(/(\d{1,3}(?:,\d{3})*|\d+)/);
        if (sheetsMatch && sheetsMatch[1]) {
            sheets = parseInt(sheetsMatch[1].replace(/,/g, ''));
        }

        // 수량 표시: "0.5연 (250매)" 형식 - DB에서 읽어온 매수 사용
        const formattedQty = Number.isInteger(quantity) ? quantity.toLocaleString() : quantity;
        const quantityDisplay = sheets > 0
            ? `${formattedQty}연 (${sheets.toLocaleString()}매)`
            : `${formattedQty}연`;

        // 5. 규격 문자열 생성 (2줄 형식) - 장바구니/주문서와 동일한 형식
        // 1줄: 색상 / 용지 / 규격 (예: 칼라(CMYK) / 90g아트지(합판전단) / A4 (210x297))
        const line1 = `${colorText} / ${paperText} / ${sizeText}`;
        // 2줄: 인쇄면 / 수량 / 인쇄만(또는 디자인+인쇄) (예: 단면칼라 / 0.5연 (2,000매) / 인쇄만)
        const line2 = `${sidesText} / ${quantityDisplay} / ${designText}`;
        const specification = `${line1}\n${line2}`;

        // 6. 부모 창에 데이터 전송
        const payload = {
            product_type: 'inserted',
            product_name: '전단지',
            specification: specification,
            quantity: quantity,
            unit: unit,
            qty_sheets: sheets,  // 매수 추가
            quantity_display: quantityDisplay,
            unit_price: supplyPrice,  // 전단지는 연 단가
            supply_price: supplyPrice,
            // 원본 데이터
            MY_type: MY_type,
            MY_Fsd: MY_Fsd,
            PN_type: PN_type,
            MY_amount: MY_amount,
            ordertype: orderType,  // 인쇄만/디자인+인쇄
            st_price: supplyPrice,
            st_price_vat: Math.round(supplyPrice * 1.1)
        };

        console.log('📤 [관리자 견적서-전단지] postMessage 전송:', payload);

        window.parent.postMessage({
            type: 'ADMIN_QUOTE_ITEM_ADDED',
            payload: payload
        }, window.location.origin);
    };

    console.log('✅ [관리자 견적서-전단지] applyToQuotation() 정의 완료');
    </script>
<?php endif; ?>
</body>
</html>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>