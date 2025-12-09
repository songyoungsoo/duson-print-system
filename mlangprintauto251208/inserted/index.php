<?php 
session_start(); 
$session_id = session_id();

// 📱 모달 모드 감지 (견적서 시스템에서 iframe으로 호출될 때) - 최우선 설정
$is_quotation_mode = isset($_GET['mode']) && $_GET['mode'] === 'quotation';
$body_class = $is_quotation_mode ? ' quotation-modal-mode' : '';

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
    <link rel="stylesheet" href="../../css/product-layout.css">

    <!-- 🎯 통합 공통 스타일 CSS (먼저 로드) -->
    <link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">

    <!-- 📎 공통 파일 업로드 모달 CSS (최종 로드로 최우선권 확보) -->
    <link rel="stylesheet" href="../../css/upload-modal-common.css?v=<?php echo time(); ?>">

    <!-- 🎨 브랜드 디자인 시스템 CSS -->
    <link rel="stylesheet" href="../../css/brand-design-system.css">

    <!-- 📱 견적서 모달 모드 공통 CSS (전 제품 공통) -->
    <link rel="stylesheet" href="../../css/quotation-modal-common.css">

    <!-- 추가 옵션 시스템 전용 CSS -->
    <link rel="stylesheet" href="../../css/additional-options.css">

    <!-- 🆕 Duson 통합 갤러리 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-gallery.css">

    <!-- 통일 인라인 폼 시스템 CSS -->
    <link rel="stylesheet" href="../../css/unified-inline-form.css">

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
</head>

<body class="inserted-page<?php echo $body_class; ?>">
    <?php if (!$is_quotation_mode): ?>
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>
    <?php endif; ?>

    <div class="product-container">
        <?php if (!$is_quotation_mode): ?>
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>📄 전단지 견적 안내</h1>
        </div>
        <?php endif; ?>

        <div class="product-content">
            <?php if (!$is_quotation_mode): ?>
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
            <?php endif; ?>

            <!-- 우측: 계산기 섹션 -->
            <aside class="product-calculator" aria-label="실시간 견적 계산기">
                <form id="orderForm" method="post">
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
                            <button type="button" id="downloadTemplateBtn" class="template-download-btn"
                                    onclick="downloadTemplate()"
                                    style="margin-top: 5px; padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                📐 작업 템플릿 다운로드 (AI/SVG)
                            </button>
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

                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton">
                        <?php if ($is_quotation_mode): ?>
                            <!-- 견적서 모달 모드: 견적서에 적용 버튼만 표시 (실시간 계산) -->
                            <button type="button" class="btn-upload-order" id="applyBtn" onclick="sendToQuotation()" style="background: #217346;">
                                ✅ 견적서에 적용
                            </button>
                        <?php else: ?>
                            <!-- 일반 모드: 파일 업로드 및 주문 -->
                            <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                                파일 업로드 및 주문하기
                            </button>
                        <?php endif; ?>
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

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <?php if (!$is_quotation_mode): ?>
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
                        <p style="margin-bottom: 12px;">💡 TIP! 작업 템플릿을 다운 받아 사용하시면 더욱 정확하고 편리하게 작업하실 수 있습니다!</p>

                        <!-- 템플릿 다운로드 메뉴 (모든 사이즈) -->
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="color: white; margin: 0 0 10px 0; font-size: 15px; font-weight: 600;">📐 작업 템플릿 다운로드 (AI/SVG)</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px;">
                                <button onclick="downloadTemplateSize(210, 297, 'A4')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">A4 (210×297mm)</button>
                                <button onclick="downloadTemplateSize(147, 210, 'A5')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">A5 (147×210mm)</button>
                                <button onclick="downloadTemplateSize(105, 147, 'A6')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">A6 (105×147mm)</button>
                                <button onclick="downloadTemplateSize(297, 423, 'A3')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">A3 (297×423mm)</button>
                                <button onclick="downloadTemplateSize(257, 367, 'B4')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">B4 (257×367mm)</button>
                                <button onclick="downloadTemplateSize(182, 257, 'B5')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">B5 (182×257mm)</button>
                                <button onclick="downloadTemplateSize(127, 182, 'B6')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">B6 (127×182mm)</button>
                                <button onclick="downloadTemplateSize(423, 597, '국2절')" style="padding: 8px 12px; background: white; color: #667eea; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.3s;">국2절 (423×597mm)</button>
                            </div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 11px; margin: 10px 0 0 0;">
                                ✓ 재단여유 +1.5mm / 안전선 -2mm 포함 | ✓ 일러스트레이터로 바로 열기
                            </p>
                        </div>
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

    <?php endif; ?>

    <?php if (!$is_quotation_mode): ?>
    <?php
    // 공통 푸터 포함
    include "../../includes/footer.php";
    ?>
    <?php endif; ?>

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

    /**
     * 작업 템플릿 다운로드 함수
     * 현재 선택된 사이즈에 맞는 SVG 템플릿 생성
     */
    function downloadTemplate() {
        const sizeSelect = document.getElementById('PN_type');
        if (!sizeSelect || !sizeSelect.value) {
            alert('먼저 규격을 선택해주세요.');
            return;
        }

        // 선택된 옵션의 텍스트 가져오기 (예: "A4 (210x297)")
        const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
        const sizeText = selectedOption.text;

        // 사이즈 파싱 (숫자 추출)
        // 패턴 1: "A4 (210x297)" 또는 "B5(16절)182x257"
        const sizeMatch = sizeText.match(/(\d+)\s*[xX×]\s*(\d+)/);

        if (!sizeMatch) {
            alert('사이즈 정보를 찾을 수 없습니다. 관리자에게 문의해주세요.');
            console.error('사이즈 파싱 실패:', sizeText);
            return;
        }

        const width = parseInt(sizeMatch[1]);
        const height = parseInt(sizeMatch[2]);

        // 제품명 추출 (예: "A4", "B5")
        const productNameMatch = sizeText.match(/^[A-Z]\d+|[A-Z가-힣]+\d*절|국\d+절/);
        const productName = productNameMatch ? productNameMatch[0] : '전단지';

        // 전단지 설정: 재단여유 +1.5mm, 안전선 -2mm
        const bleed = 1.5;
        const safe = 2;

        // template_generator.php 호출
        const url = `/template_generator.php?width=${width}&height=${height}&bleed=${bleed}&safe=${safe}&product=전단지_${productName}`;

        console.log('템플릿 다운로드:', { width, height, bleed, safe, productName, url });

        // 새 창에서 다운로드 (즉시 다운로드)
        window.location.href = url;
    }

    /**
     * 특정 사이즈의 작업 템플릿 다운로드
     * @param {number} width - 가로 사이즈 (mm)
     * @param {number} height - 세로 사이즈 (mm)
     * @param {string} productName - 제품명 (예: "A4", "B5")
     */
    function downloadTemplateSize(width, height, productName) {
        // 전단지 설정: 재단여유 +1.5mm, 안전선 -2mm
        const bleed = 1.5;
        const safe = 2;

        // template_generator.php 호출
        const url = `/template_generator.php?width=${width}&height=${height}&bleed=${bleed}&safe=${safe}&product=전단지_${productName}`;

        console.log('템플릿 다운로드:', { width, height, bleed, safe, productName, url });

        // 즉시 다운로드
        window.location.href = url;
    }

        // 전단지 전용 장바구니 추가 함수 (통합 모달 패턴)
        // ⚠️ 파라미터 순서: uploadedFiles, onSuccess, onError (upload_modal.js와 일치해야 함)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
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

            formData.append("calculated_price", totalPrice);
            formData.append("calculated_vat_price", vatPrice);

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

        /**
         * 견적서에 데이터 전송 (전단지 전용)
         */
        window.sendToQuotation = function() {
            console.log('📤 [TUNNEL 2/5] "✅ 견적서에 적용" 버튼 클릭됨');

            // 필수 필드 확인
            const myTypeSelect = document.getElementById('MY_type');
            const myFsdSelect = document.getElementById('MY_Fsd');
            const pnTypeSelect = document.getElementById('PN_type');
            const potypeSelect = document.getElementById('POtype');
            const myAmountSelect = document.getElementById('MY_amount');
            const ordertypeSelect = document.getElementById('ordertype');

            if (!myTypeSelect || !myTypeSelect.value ||
                !myFsdSelect || !myFsdSelect.value ||
                !pnTypeSelect || !pnTypeSelect.value ||
                !potypeSelect || !potypeSelect.value ||
                !myAmountSelect || !myAmountSelect.value ||
                !ordertypeSelect || !ordertypeSelect.value) {
                alert('모든 옵션을 선택해주세요.');
                return;
            }

            // 가격 표시 영역에서 가격 읽기
            const priceAmountElement = document.getElementById('priceAmount');
            if (!priceAmountElement) {
                alert('가격 표시 영역을 찾을 수 없습니다.');
                return;
            }

            const priceText = priceAmountElement.textContent || priceAmountElement.innerText;
            console.log('💰 가격 표시 영역 내용:', priceText);

            // 가격에 숫자가 포함되어 있는지 확인
            const hasPrice = /\d/.test(priceText) && !priceText.includes('견적 계산 필요') && !priceText.includes('계산 중');
            if (!hasPrice) {
                alert('가격이 계산되지 않았습니다. 모든 옵션을 선택하면 자동으로 계산됩니다.');
                return;
            }

            console.log('✅ 가격 확인 완료:', priceText);

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '📝 견적서에 입력 중...';

            try {
                // 선택된 옵션의 텍스트 추출
                const myTypeText = myTypeSelect.options[myTypeSelect.selectedIndex].text;
                const myFsdText = myFsdSelect.options[myFsdSelect.selectedIndex].text;
                const pnTypeText = pnTypeSelect.options[pnTypeSelect.selectedIndex].text;
                const potypeText = potypeSelect.options[potypeSelect.selectedIndex].text;
                const myAmountText = myAmountSelect.options[myAmountSelect.selectedIndex].text;
                const ordertypeText = ordertypeSelect.options[ordertypeSelect.selectedIndex].text;

                // 추가 옵션 정보 수집
                const coatingEnabled = document.getElementById('coating_enabled');
                const foldingEnabled = document.getElementById('folding_enabled');
                const creasingEnabled = document.getElementById('creasing_enabled');
                
                let optionsText = '';
                if (coatingEnabled && coatingEnabled.checked) {
                    const coatingType = document.getElementById('coating_type');
                    if (coatingType && coatingType.value) {
                        optionsText += ' / ' + coatingType.options[coatingType.selectedIndex].text;
                    }
                }
                if (foldingEnabled && foldingEnabled.checked) {
                    const foldingType = document.getElementById('folding_type');
                    if (foldingType && foldingType.value) {
                        optionsText += ' / ' + foldingType.options[foldingType.selectedIndex].text;
                    }
                }
                if (creasingEnabled && creasingEnabled.checked) {
                    const creasingLines = document.getElementById('creasing_lines');
                    if (creasingLines && creasingLines.value) {
                        optionsText += ' / 오시 ' + creasingLines.options[creasingLines.selectedIndex].text;
                    }
                }

                // 규격 문자열 생성
                const specification = `${myTypeText} / ${myFsdText} / ${pnTypeText} / ${potypeText} / ${ordertypeText}${optionsText}`.trim();

                // 수량 값 추출 - value에 quantityTwo (실제 매수)가 저장되어 있음
                const quantityValue = parseInt(myAmountSelect.value) || 1000;
                
                console.log('📊 수량 추출:', { value: myAmountSelect.value, text: myAmountText, quantityValue });

                // 가격 표시 영역에서 가격 추출
                // "123,456원" 형식에서 숫자만 추출
                const priceMatch = priceText.match(/[\d,]+/);
                const totalPrice = priceMatch ? parseInt(priceMatch[0].replace(/,/g, '')) : 0;

                // 부가세 포함 가격 계산 (공급가액 * 1.1)
                const vatPrice = Math.round(totalPrice * 1.1);

                console.log('💰 추출된 가격:', { priceText, totalPrice, vatPrice });

                // 견적서 폼에 전달할 데이터 구조
                const quotationData = {
                    product_name: '전단지',
                    specification: specification,
                    quantity: quantityValue,
                    unit: '매',
                    supply_price: totalPrice,
                    vat_price: vatPrice,

                    // 원본 계산 데이터도 포함 (디버깅용)
                    _debug: {
                        MY_type: myTypeSelect.value,
                        MY_Fsd: myFsdSelect.value,
                        PN_type: pnTypeSelect.value,
                        POtype: potypeSelect.value,
                        MY_amount: myAmountSelect.value,
                        ordertype: ordertypeSelect ? ordertypeSelect.value : '',
                        calculated_price: window.currentPriceData
                    }
                };

                console.log('📨 [TUNNEL 3/5] 견적서 데이터 전송:', quotationData);

                // 부모 창으로 데이터 전송 (calculator_modal.js의 handlePriceData가 수신)
                window.parent.postMessage({
                    type: 'CALCULATOR_PRICE_DATA',
                    payload: quotationData
                }, window.location.origin);

                // 성공 피드백
                btn.innerHTML = '✅ 견적서에 적용됨!';
                btn.style.background = '#28a745';

                console.log('✅ [TUNNEL 5/5] 견적서 폼 입력 완료 - 모달은 자동으로 닫힙니다');

            } catch (error) {
                console.error('❌ 견적서 데이터 전송 실패:', error);
                alert('견적서 적용 중 오류가 발생했습니다: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.style.background = '#217346';
            }
        };
    </script>

    <!-- 통합 갤러리 시스템 JavaScript -->
    <script src="../../js/common-gallery-popup.js"></script>

    <!-- 전단지 전용 컴팩트 디자인 적용 (Frontend-Compact-Design-Guide.md 기반) -->
</body>
</html>

<?php
// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>