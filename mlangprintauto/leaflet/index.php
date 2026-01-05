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

// 방문자 추적 시스템
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visitor_tracker.php';

// 페이지 설정
$page_title = '📄 두손기획인쇄 - 리플렛 컴팩트 견적';
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

<body class="inserted-page">
    <?php include "../../includes/header-ui.php"; ?>
    <?php include "../../includes/nav.php"; ?>

    <div class="product-container">
        <!-- 페이지 타이틀 -->
        <div class="page-title">
            <h1>📄 리플렛 견적 안내</h1>
        </div>

        <div class="product-content">
            <!-- 좌측: 통합 갤러리 시스템 (500×400 마우스 호버 줌) -->
            <section class="product-gallery" aria-label="리플렛 샘플 갤러리">
                <?php
                // 통합 갤러리 시스템 (500×400 마우스 호버 줌)
                $gallery_product = 'leaflet';
                if (file_exists('../../includes/simple_gallery_include.php')) {
                    include '../../includes/simple_gallery_include.php';
                }
                ?>
            </section>

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

                    <!-- 파일 업로드 및 주문 버튼 -->
                    <div class="upload-order-button" id="uploadOrderButton">
                        <button type="button" class="btn-upload-order" onclick="openUploadModal()">
                            파일 업로드 및 주문하기
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
                    <input type="hidden" name="page" value="leaflet">
                    
                    <!-- 가격 정보 저장용 -->
                    <input type="hidden" name="price" id="calculated_price" value="">
                    <input type="hidden" name="vat_price" id="calculated_vat_price" value="">
                </form>
            </aside>
        </div>
    </div>

    <?php
    // 리플렛 모달 설정
    $modalProductName = '리플렛';
    $modalProductIcon = '📎';
    
    // 공통 업로드 모달 포함
    include "../../includes/upload_modal.php";
    ?>

    <?php
    // 공통 로그인 모달 포함
    include "../../includes/login_modal.php";
    ?>

    <!-- 리플렛 상세 설명 섹션 (필요시 추가) -->
    <section class="product-info-section">
        <!-- 리플렛 관련 안내사항을 여기에 추가할 수 있습니다. -->
    </section>

    <!-- 공통 푸터 포함 -->
    <?php include "../../includes/footer.php"; ?>

    <!-- 공통 업로드 모달 JavaScript -->
    <script src="../../includes/upload_modal.js?v=1759243573751415300"></script>
    
    <!-- 리플렛 전용 스크립트 -->
    <script src="calculator.js?v=<?php echo time(); ?>"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('리플렛 페이지 초기화 완료 - 통합 갤러리 시스템');

        // ✅ 추가 옵션은 calculator.js에서 관리

        // 로그인 메시지가 있으면 모달 자동 표시
        <?php if (!empty($login_message)): ?>
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
        <?php endif; ?>
    });
        // 리플렛 전용 장바구니 추가 함수 (통합 모달 패턴)
        window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
            console.log("리플렛 장바구니 추가 시작");

            // 현재 가격 데이터가 없으면 에러
            if (!window.currentPriceData || !window.currentPriceData.Order_PriceForm) {
                console.error("가격 계산이 필요합니다. currentPriceData:", window.currentPriceData);
                if (onError) onError("먼저 견적 계산을 해주세요. '견적 계산' 버튼을 눌러주세요.");
                return;
            }

            console.log("✅ 가격 데이터 확인:", window.currentPriceData);

            const formData = new FormData();
            formData.append("action", "add_to_basket");
            formData.append("product_type", "leaflet"); // 리플렛은 leaflet으로 저장

            // 폼 필드 (실제 ID 사용)
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

            if (uploadedFiles && uploadedFiles.length > 0) {
                uploadedFiles.forEach((file, index) => {
                    formData.append("uploaded_files[" + index + "]", file);
                });
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