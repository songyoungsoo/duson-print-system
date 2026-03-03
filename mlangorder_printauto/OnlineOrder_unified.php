<?php
/**
 * 통합 장바구니 주문 처리
 * 경로: mlangorder_printauto/OnlineOrder_unified.php
 * 수정일: 2025-12-19 - 상품정보 컬럼 분리 (통합장바구니와 동일하게)
 */

session_start();
// 🔧 CRITICAL FIX: 장바구니에서 전달된 세션 ID 우선 사용 (세션 불일치 문제 해결)
$session_id = !empty($_POST['cart_session_id']) ? $_POST['cart_session_id'] : session_id();
error_log("OnlineOrder: Using session_id = $session_id (current: " . session_id() . ", POST: " . ($_POST['cart_session_id'] ?? 'none') . ")");

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// 통합 인증 시스템 로드
include "../includes/auth.php";

// 헬퍼 함수 포함
include "../mlangprintauto/shop_temp_helper.php";

// 추가 옵션 표시 클래스 포함
include "../includes/AdditionalOptionsDisplay.php";
include "../includes/quantity_formatter.php";
include "../includes/ProductSpecFormatter.php";
$optionsDisplay = new AdditionalOptionsDisplay($connect);
$specFormatter = new ProductSpecFormatter($connect);

/**
 * ID로 한글명 가져오기 함수 (장바구니와 동일)
 */
function getKoreanName($connect, $id)
{
    if (!$connect || !$id) {
        return $id;
    }

    // ID가 이미 한글이면 그대로 반환
    if (preg_match('/[가-힣]/u', $id)) {
        return $id;
    }

    // 숫자와 문자열 모두 처리
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? OR title = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        error_log("getKoreanName prepare failed: " . mysqli_error($connect));
        return $id;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $id, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }

    mysqli_stmt_close($stmt);
    return $id;
}

/**
 * 스티커 규격 정보 포맷팅 함수 (장바구니와 동일)
 */
function getStickerSpecs($item) {
    $specs = [];

    // Material (재질)
    if (!empty($item['jong'])) {
        $material = preg_replace('/^(jil|jsp|jka|cka)\s+/', '', $item['jong']);
        $specs[] = '재질: ' . htmlspecialchars($material);
    }

    // Size (크기)
    if (!empty($item['garo']) && !empty($item['sero'])) {
        $specs[] = '크기: ' . htmlspecialchars($item['garo']) . ' × ' . htmlspecialchars($item['sero']) . 'mm';
    }

    // Shape (모양)
    if (!empty($item['domusong'])) {
        $shape_parts = explode(' ', $item['domusong'], 2);
        $shape_name = isset($shape_parts[1]) ? $shape_parts[1] : $item['domusong'];
        $specs[] = '모양: ' . htmlspecialchars($shape_name);
    }

    // Edit type (편집) - Only if not 0
    if (!empty($item['uhyung']) && $item['uhyung'] != '0') {
        $edit_types = ['10000' => '기본편집', '30000' => '고급편집'];
        $edit_label = $edit_types[$item['uhyung']] ?? htmlspecialchars($item['uhyung']) . '원';
        $specs[] = '편집: ' . $edit_label;
    }

    return $specs;
}

/**
 * 자석스티커 규격 정보 포맷팅 함수
 */
function getMstickerSpecs($item) {
    global $connect;
    $specs = [];

    // Type (종류) - MY_type field
    if (!empty($item['MY_type'])) {
        $type_name = getKoreanName($connect, $item['MY_type']);
        $specs[] = '종류: ' . htmlspecialchars($type_name);
    }

    // Specification/Size (규격) - Section field
    if (!empty($item['Section'])) {
        $section_name = getKoreanName($connect, $item['Section']);
        $specs[] = '규격: ' . htmlspecialchars($section_name);
    }

    // Print type (인쇄) - POtype field
    if (!empty($item['POtype'])) {
        $print_types = ['1' => '단면', '2' => '양면'];
        $print_label = $print_types[$item['POtype']] ?? htmlspecialchars($item['POtype']);
        $specs[] = '인쇄: ' . $print_label;
    }

    // Quantity (수량) - MY_amount field
    if (!empty($item['MY_amount'])) {
        $specs[] = '수량: ' . formatQuantity($item['MY_amount'], 'msticker', '매');
    }

    return $specs;
}

// 페이지 설정
$page_title = '주문 정보 입력';
$current_page = 'order';

// 추가 CSS 연결
$additional_css = [
    '/css/common-styles.css',
    '/css/product-layout.css'
];

// 주문 타입 확인
$is_direct_order = isset($_GET['direct_order']) && $_GET['direct_order'] == '1';
$is_post_order = !empty($_POST['product_type']) && !is_array($_POST['product_type']); // 단일 상품 직접 주문
$is_cart_post_order = !empty($_POST['product_type']) && is_array($_POST['product_type']); // 장바구니에서 온 주문
$cart_items = [];
$total_info = ['total' => 0, 'total_vat' => 0, 'count' => 0];

if ($is_post_order) {
    // POST로 온 직접 주문 처리 (카다록 등)
    $product_type = $_POST['product_type'] ?? 'cadarok';
    
    if ($product_type == 'cadarok') {
        // 카다록 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'cadarok',
            'type_text' => $_POST['selected_category'] ?? '',
            'size_text' => $_POST['selected_size'] ?? '',
            'paper_text' => $_POST['selected_paper'] ?? '',
            'quantity_text' => $_POST['selected_quantity'] ?? '',
            'design_text' => $_POST['selected_order'] ?? '',
            'price' => intval($_POST['Price'] ?? 0),
            'vat_price' => intval($_POST['Total_Price'] ?? 0),
            'MY_type' => $_POST['MY_type'] ?? '',
            'MY_Fsd' => $_POST['MY_Fsd'] ?? '',
            'PN_type' => $_POST['PN_type'] ?? '',
            'MY_amount' => $_POST['MY_amount'] ?? '',
            'ordertype' => $_POST['ordertype'] ?? '',
            'MY_comment' => '카다록/리플렛 주문'
        ];
        
        $cart_items = [$direct_item];
        $total_info = [
            'total' => $direct_item['price'],
            'total_vat' => $direct_item['vat_price'],
            'count' => 1
        ];
        $is_direct_order = true;
    }
} elseif ($is_direct_order) {
    // GET으로 온 직접 주문 처리 (기존)
    $product_type = $_GET['product_type'] ?? 'leaflet';
    
    if ($product_type == 'envelope') {
        // 봉투 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'envelope',
            'type_text' => $_GET['type_text'] ?? '',
            'size_text' => $_GET['size_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'MY_type' => $_GET['MY_type'] ?? '',
            'PN_type' => $_GET['PN_type'] ?? '',
            'MY_amount' => $_GET['MY_amount'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? '',
            'MY_comment' => $_GET['MY_comment'] ?? ''
        ];
    } elseif ($product_type == 'merchandisebond') {
        // 상품권 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'merchandisebond',
            'type_text' => $_GET['type_text'] ?? '',
            'size_text' => $_GET['size_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'po_text' => $_GET['po_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'MY_type' => $_GET['MY_type'] ?? '',
            'PN_type' => $_GET['PN_type'] ?? '',
            'MY_amount' => $_GET['MY_amount'] ?? '',
            'POtype' => $_GET['POtype'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? '',
            'MY_comment' => $_GET['MY_comment'] ?? ''
        ];
    } elseif ($product_type == 'namecard') {
        // 명함 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'namecard',
            'type_text' => $_GET['type_text'] ?? '',
            'paper_text' => $_GET['paper_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'sides_text' => $_GET['sides_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'NC_type' => $_GET['NC_type'] ?? '',
            'NC_paper' => $_GET['NC_paper'] ?? '',
            'NC_amount' => $_GET['NC_amount'] ?? '',
            'NC_sides' => $_GET['NC_sides'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? '',
            'NC_comment' => $_GET['NC_comment'] ?? ''
        ];
    } else {
        // 전단지 직접 주문 (기존)
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => $_GET['product_type'] ?? 'leaflet',
            'color_text' => $_GET['color_text'] ?? '',
            'paper_type_text' => $_GET['paper_type_text'] ?? '',
            'paper_size_text' => $_GET['paper_size_text'] ?? '',
            'sides_text' => $_GET['sides_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'MY_type' => $_GET['MY_type'] ?? '',
            'MY_Fsd' => $_GET['MY_Fsd'] ?? '',
            'PN_type' => $_GET['PN_type'] ?? '',
            'POtype' => $_GET['POtype'] ?? '',
            'MY_amount' => $_GET['MY_amount'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? ''
        ];
    }
    
    $cart_items[] = $direct_item;
    $total_info = [
        'total' => $direct_item['price'],
        'total_vat' => $direct_item['vat_price'],
        'count' => 1
    ];
} elseif ($is_cart_post_order) {
    // 장바구니에서 온 POST 데이터 처리 - 실제 세션 데이터 사용
    error_log("Debug: Processing cart POST data");
    
    // 실제 장바구니 데이터를 세션에서 가져와서 자세한 정보 표시
    $cart_result = getCartItems($connect, $session_id);
    
    if ($cart_result) {
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $formatted_item = formatCartItemForDisplay($connect, $item);

            // 스티커/자석스티커: 원본 데이터 보존 (장바구니와 동일한 표시를 위해)
            if ($item['product_type'] === 'sticker' || $item['product_type'] === 'msticker') {
                $formatted_item['jong'] = $item['jong'] ?? '';
                $formatted_item['garo'] = $item['garo'] ?? '';
                $formatted_item['sero'] = $item['sero'] ?? '';
                $formatted_item['mesu'] = $item['mesu'] ?? '';
                $formatted_item['domusong'] = $item['domusong'] ?? '';
                $formatted_item['uhyung'] = $item['uhyung'] ?? '';
            }

            // 기타 제품: MY_type, MY_Fsd, PN_type, Section, POtype, ordertype, MY_amount, mesu 보존
            $formatted_item['MY_type'] = $item['MY_type'] ?? '';
            $formatted_item['MY_Fsd'] = $item['MY_Fsd'] ?? '';
            $formatted_item['PN_type'] = $item['PN_type'] ?? '';
            $formatted_item['Section'] = $item['Section'] ?? '';
            $formatted_item['POtype'] = $item['POtype'] ?? '';
            $formatted_item['ordertype'] = $item['ordertype'] ?? '';
            $formatted_item['MY_amount'] = $item['MY_amount'] ?? '';
            $formatted_item['mesu'] = $item['mesu'] ?? '';
            $formatted_item['flyer_mesu'] = $item['flyer_mesu'] ?? '';

            // 🔧 모든 제품의 한글명 필드 복사
            $formatted_item['MY_type_name'] = $item['MY_type_name'] ?? '';
            $formatted_item['MY_Fsd_name'] = $item['MY_Fsd_name'] ?? '';
            $formatted_item['PN_type_name'] = $item['PN_type_name'] ?? '';
            $formatted_item['Section_name'] = $item['Section_name'] ?? '';
            $formatted_item['POtype_name'] = $item['POtype_name'] ?? '';

            $cart_items[] = $formatted_item;
            error_log("Debug: Cart POST item: " . $item['product_type'] . " - " . $item['st_price_vat']);
        }
        $total_info = calculateCartTotal($connect, $session_id);
    } else {
        // 세션 데이터가 없으면 POST 데이터로 기본 구성
        error_log("Debug: No session data, using POST fallback");
        $product_types = $_POST['product_type'] ?? [];
        $prices = $_POST['price'] ?? [];
        $prices_vat = $_POST['price_vat'] ?? [];
        
        for ($i = 0; $i < count($product_types); $i++) {
            $cart_items[] = [
                'no' => 'cart_' . $i,
                'product_type' => $product_types[$i] ?? '',
                'name' => ucfirst($product_types[$i] ?? '상품'),
                'st_price' => floatval($prices[$i] ?? 0),
                'st_price_vat' => floatval($prices_vat[$i] ?? 0),
                'details' => ['정보' => '장바구니 상품']
            ];
        }
        
        $total_info = [
            'total' => intval($_POST['total_price'] ?? 0),
            'total_vat' => intval($_POST['total_price_vat'] ?? 0),
            'count' => intval($_POST['items_count'] ?? 0)
        ];
    }
    
    error_log("Debug: Cart POST items loaded: " . count($cart_items));
} else {
    // 세션 장바구니 데이터 조회 - 디버깅 추가
    error_log("Debug: Getting cart items for session_id: " . $session_id);
    $cart_result = getCartItems($connect, $session_id);
    error_log("Debug: Cart result: " . ($cart_result ? 'found' : 'not found'));
    
    if ($cart_result) {
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $formatted_item = formatCartItemForDisplay($connect, $item);

            // 스티커/자석스티커: 원본 데이터 보존 (장바구니와 동일한 표시를 위해)
            if ($item['product_type'] === 'sticker' || $item['product_type'] === 'msticker') {
                $formatted_item['jong'] = $item['jong'] ?? '';
                $formatted_item['garo'] = $item['garo'] ?? '';
                $formatted_item['sero'] = $item['sero'] ?? '';
                $formatted_item['mesu'] = $item['mesu'] ?? '';
                $formatted_item['domusong'] = $item['domusong'] ?? '';
                $formatted_item['uhyung'] = $item['uhyung'] ?? '';
            }

            // 기타 제품: MY_type, MY_Fsd, PN_type, Section, POtype, ordertype, MY_amount, mesu 보존
            $formatted_item['MY_type'] = $item['MY_type'] ?? '';
            $formatted_item['MY_Fsd'] = $item['MY_Fsd'] ?? '';
            $formatted_item['PN_type'] = $item['PN_type'] ?? '';
            $formatted_item['Section'] = $item['Section'] ?? '';
            $formatted_item['POtype'] = $item['POtype'] ?? '';
            $formatted_item['ordertype'] = $item['ordertype'] ?? '';
            $formatted_item['MY_amount'] = $item['MY_amount'] ?? '';
            $formatted_item['mesu'] = $item['mesu'] ?? '';
            $formatted_item['flyer_mesu'] = $item['flyer_mesu'] ?? '';

            // 🔧 모든 제품의 한글명 필드 복사
            $formatted_item['MY_type_name'] = $item['MY_type_name'] ?? '';
            $formatted_item['MY_Fsd_name'] = $item['MY_Fsd_name'] ?? '';
            $formatted_item['PN_type_name'] = $item['PN_type_name'] ?? '';
            $formatted_item['Section_name'] = $item['Section_name'] ?? '';
            $formatted_item['POtype_name'] = $item['POtype_name'] ?? '';

            $cart_items[] = $formatted_item;
            error_log("Debug: Added cart item: " . $item['product_type'] . " - " . $item['st_price_vat']);
        }
        $total_info = calculateCartTotal($connect, $session_id);
        error_log("Debug: Total cart items: " . count($cart_items));
    } else {
        error_log("Debug: No cart result found");
    }
    
    // 장바구니가 비어있으면 리다이렉트
    if (empty($cart_items)) {
        error_log("Debug: Cart is empty, redirecting");
        echo "<script>alert('장바구니가 비어있습니다.'); location.href='../mlangprintauto/shop/cart.php';</script>";
        exit;
    }
}

// ⚠️ CRITICAL FIX: total과 total_vat의 의미가 반대로 들어오는 경우가 있어 값을 교환하여 정정
// 문제의 원인: 간혹 price는 VAT 포함, vat_price는 VAT 제외 금액으로 POST 되거나
//             shop_temp 테이블에 st_price, st_price_vat가 잘못 저장되는 경우가 발견됨.
//             (예: 공급가액으로 442,200원, 총 결제 금액으로 402,000원이 들어오는 경우)
// 해결책: `$total_info['total']`이 VAT 제외 공급가액, `$total_info['total_vat']`이 VAT 포함 총 결제 금액으로
//         일관되게 유지되도록 이 시점에서 값을 검증하고 필요 시 교환 및 재계산한다.

$current_total_exclusive = $total_info['total'];       // 현재 $total_info['total'] (가정: 공급가액)
$current_total_inclusive = $total_info['total_vat'];    // 현재 $total_info['total_vat'] (가정: 총 결제 금액)

// 1단계: 만약 현재 '총 결제 금액'으로 들어온 값이 '공급가액'으로 들어온 값보다 작으면, 두 값이 바뀐 것으로 판단하고 교환
// 예: total=442200 (VAT포함), total_vat=402000 (VAT제외) 인 경우
if ($current_total_inclusive < $current_total_exclusive) {
    $total_info['total'] = $current_total_inclusive;       // 실제 공급가액
    $total_info['total_vat'] = $current_total_exclusive;    // 실제 총 결제 금액
}
// 2단계: VAT 계산이 맞는지 확인 (10% 오차 범위 허용)
// 현재 $total_info['total']이 공급가액, $total_info['total_vat']이 총 결제 금액이라고 간주.
$calculated_vat_inclusive = round($total_info['total'] * 1.1);
$difference = abs($total_info['total_vat'] - $calculated_vat_inclusive);

// 10원 이상의 오차가 있다면 재계산하여 VAT를 정확히 맞춘다.
if ($difference > 10) { 
    $total_info['total_vat'] = $calculated_vat_inclusive;
}

// 최종적으로 $total_info['total'] = 공급가액 (VAT 제외)
//           $total_info['total_vat'] = 총 결제 금액 (VAT 포함)
//          이 되도록 조정되었음.

// 로그인 상태는 이미 auth.php에서 처리됨
// 회원 정보 가져오기 (로그인되어 있을 때만)
$user_info = null;
$debug_info = [];

if ($is_logged_in && isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $debug_info[] = "Loading user info for user_id: " . $user_id;

    if (!$connect) {
        $debug_info[] = "ERROR: No database connection";
    } else {
        // 1. users 테이블에서 회원 정보 조회
        $user_query = "SELECT * FROM users WHERE id = ?";
        $stmt = safe_mysqli_prepare($connect, $user_query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user_info = mysqli_fetch_assoc($result);
                $debug_info[] = "User info loaded from users table";

                // member 테이블 폴백 제거됨 (2026-02-02)
                // users 테이블에 주소가 없으면 폼 필드가 빈 상태로 표시됨
                if (empty($user_info['postcode']) && empty($user_info['address'])) {
                    $debug_info[] = "No address in users table (member fallback removed)";
                }

                $debug_info[] = "Available fields: " . implode(', ', array_keys($user_info));
                $debug_info[] = "Name: " . ($user_info['name'] ?? 'none');
                $debug_info[] = "Address fields: address=" . ($user_info['address'] ?? 'none') .
                               ", postcode=" . ($user_info['postcode'] ?? 'none');
            } else {
                $debug_info[] = "ERROR: No user found with id: " . $user_id;
                // 테이블 존재 여부 확인
                $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
                if ($table_check && mysqli_num_rows($table_check) > 0) {
                    $debug_info[] = "Table 'users' exists";
                    // 전체 사용자 수 확인
                    $count_result = mysqli_query($connect, "SELECT COUNT(*) as total FROM users");
                    if ($count_result) {
                        $count_row = mysqli_fetch_assoc($count_result);
                        $debug_info[] = "Total users in table: " . $count_row['total'];
                    }
                } else {
                    $debug_info[] = "ERROR: Table 'users' does not exist";
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $debug_info[] = "ERROR: Failed to prepare user query: " . mysqli_error($connect);
        }
    }
} else {
    $debug_info[] = "Not logged in or missing session data";
    $debug_info[] = "is_logged_in: " . ($is_logged_in ? 'true' : 'false');
    $debug_info[] = "SESSION user_id: " . ($_SESSION['user_id'] ?? 'not set');
}

// 디버깅을 위해 로그 출력
foreach ($debug_info as $info) {
    error_log("UserInfo Debug: " . $info);
}

// 공통 헤더 포함 - header-ui.php로 대체됨 (구식 헤더 비활성화)
// include "../includes/header.php";
// include "../includes/nav.php";

// 디버깅 정보 임시 표시 (개발용 - localhost만) - 주석 처리
/*
if (!empty($debug_info) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    echo "<div style='position: fixed; top: 10px; right: 10px; background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; font-size: 11px; z-index: 9999; max-width: 350px; max-height: 400px; overflow-y: auto;'>";
    echo "<strong>🔍 회원정보 디버깅:</strong><br>";
    foreach ($debug_info as $info) {
        echo "• " . htmlspecialchars($info) . "<br>";
    }

    // 추가 세션 정보 표시
    echo "<hr style='margin: 8px 0;'>";
    echo "<strong>📋 세션 정보:</strong><br>";
    echo "• Session ID: " . htmlspecialchars(session_id()) . "<br>";
    echo "• user_id in SESSION: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
    echo "• duson_member_id: " . ($_SESSION['duson_member_id'] ?? 'NOT SET') . "<br>";
    echo "• is_logged_in var: " . ($is_logged_in ? 'TRUE' : 'FALSE') . "<br>";

    // user_info 내용 표시
    if ($user_info) {
        echo "<hr style='margin: 8px 0;'>";
        echo "<strong>👤 user_info 내용:</strong><br>";
        echo "• name: " . ($user_info['name'] ?? 'NULL') . "<br>";
        echo "• email: " . ($user_info['email'] ?? 'NULL') . "<br>";
        echo "• phone: " . ($user_info['phone'] ?? 'NULL') . "<br>";
        echo "• postcode: " . ($user_info['postcode'] ?? 'NULL') . "<br>";
        echo "• address: " . ($user_info['address'] ?? 'NULL') . "<br>";
        echo "• detail_address: " . ($user_info['detail_address'] ?? 'NULL') . "<br>";

        // 주소 정보 없음 경고
        if (empty($user_info['postcode']) && empty($user_info['address'])) {
            echo "<hr style='margin: 8px 0;'>";
            echo "<strong style='color: red;'>⚠️ 주소 정보가 없습니다!</strong><br>";
            echo "회원정보 수정에서 주소를 등록해주세요.<br>";
            echo "<a href='/mypage/profile.php' target='_blank' style='color: blue;'>회원정보 수정 →</a>";
        }
    }

    echo "</div>";
}
*/
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 정보 입력 - 두손기획인쇄</title>

    <!-- 엑셀 스타일 CSS 추가 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- 헤더 스타일 (header-ui.php용) -->
    <link rel="stylesheet" href="../css/common-styles.css">

    <link rel="stylesheet" href="../css/excel-unified-style.css">
</head>
<body>

<?php include "../includes/header-ui.php"; ?>

<div class="container" style="font-family: 'Noto Sans KR', sans-serif; font-size: 14px; color: #222; line-height: 1.4; padding: 0.5rem 1rem; margin-top: -1rem;">
    <!-- 주문 정보 입력 폼 -->
    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-header" style="background-color: #1E90FF; color: white; text-align: center; padding: 0.5rem;">
            <h2 style="margin: 0; font-size: 2.8rem; color: white;">주문 정보 입력</h2>
        </div>

        <div class="centered-form" style="padding: 0.8rem;">
            <!-- 주문 상품 목록 (엑셀 스타일 테이블 - 통합장바구니와 동일) -->
            <div style="margin-bottom: 1.5rem; max-width: 1100px; margin-left: auto; margin-right: auto;">
                <h3 style="color: #4a5568; font-weight: 600; font-size: 16px; margin-bottom: 1rem;">주문 상품 목록</h3>
                <div class="excel-cart-table-wrapper">
                    <table class="excel-cart-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <colgroup>
                            <col style="width: 15%;"><!-- 품목 -->
                            <col style="width: 42%;"><!-- 규격/옵션 -->
                            <col style="width: 10%;"><!-- 수량 -->
                            <col style="width: 8%;"><!-- 단위 -->
                            <col style="width: 25%;"><!-- 공급가액 -->
                        </colgroup>
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">품목</th>
                                <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">규격/옵션</th>
                                <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">수량</th>
                                <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">단위</th>
                                <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">공급가액</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                    // 상품명 매핑 (cart.php와 동일)
                    $product_info_map = [
                        'cadarok' => ['name' => '카다록', 'icon' => '', 'color' => '#e3f2fd'],
                        'sticker' => ['name' => '스티커', 'icon' => '', 'color' => '#f3e5f5'],
                        'msticker' => ['name' => '자석스티커', 'icon' => '', 'color' => '#e8f5e8'],
                        'leaflet' => ['name' => '전단지', 'icon' => '', 'color' => '#fff3e0'],
                        'inserted' => ['name' => '전단지', 'icon' => '', 'color' => '#fff3e0'],
                        'namecard' => ['name' => '명함', 'icon' => '', 'color' => '#fce4ec'],
                        'envelope' => ['name' => '봉투', 'icon' => '', 'color' => '#e0f2f1'],
                        'merchandisebond' => ['name' => '상품권', 'icon' => '', 'color' => '#f1f8e9'],
                        'littleprint' => ['name' => '포스터', 'icon' => '', 'color' => '#e8eaf6'],
                        'poster' => ['name' => '포스터', 'icon' => '', 'color' => '#e8eaf6'],
                        'ncrflambeau' => ['name' => '양식지', 'icon' => '', 'color' => '#e8eaf6']
                    ];

                    // 건수 그룹 전처리: item_group_id별로 그룹화 (cart.php와 동일 로직)
                    $order_groups = [];
                    $order_ungrouped = [];
                    foreach ($cart_items as $ci) {
                        $gid = $ci['item_group_id'] ?? null;
                        if (!empty($gid)) {
                            $order_groups[$gid][] = $ci;
                        } else {
                            $order_ungrouped[] = $ci;
                        }
                    }

                    // 그룹 아이템 렌더링 (축약: 1행 + ×N건 배지)
                    foreach ($order_groups as $gid => $group_items):
                        $group_count = count($group_items);
                        $first_item = $group_items[0];
                        $group_total_price = array_sum(array_column($group_items, 'st_price'));
                        $item = $first_item; // 대표 아이템으로 규격/수량 표시

                        $product = $product_info_map[$item['product_type']] ?? ['name' => '상품', 'icon' => '', 'color' => '#f5f5f5'];

                        // 수량/단위 계산 (기존 로직 유지)
                        $is_flyer = in_array($item['product_type'], ['inserted', 'leaflet']);
                        $main_amount_val = 1;
                        $main_amount_display = '1';
                        $unit = '매';
                        $sub_amount = null;

                        if ($is_direct_order) {
                            $main_amount_val = !empty($item['MY_amount']) ? floatval($item['MY_amount']) : 1;
                            if ($is_flyer) {
                                $unit = '연';
                                $main_amount_display = formatQuantityValue($main_amount_val, 'inserted');
                                if (!empty($item['mesu'])) $sub_amount = intval($item['mesu']);
                            } else {
                                $main_amount_display = formatQuantityValue($main_amount_val, $item['product_type']);
                                if ($item['product_type'] == 'ncrflambeau') $unit = '권';
                                elseif ($item['product_type'] == 'cadarok') $unit = '부';
                            }
                        } else {
                            if ($is_flyer) {
                                $unit = '연';
                                $main_amount_val = !empty($item['MY_amount']) ? floatval($item['MY_amount']) : 1;
                                $main_amount_display = formatQuantityValue($main_amount_val, 'inserted');
                                $sub_amount = $item['mesu'] ?? $item['flyer_mesu'] ?? null;
                            } else {
                                $main_amount_val = !empty($item['mesu']) ? intval($item['mesu']) : (!empty($item['MY_amount']) ? intval($item['MY_amount']) : 1);
                                $main_amount_display = formatQuantityValue($main_amount_val, $item['product_type']);
                                if ($item['product_type'] == 'ncrflambeau') $unit = '권';
                                elseif ($item['product_type'] == 'cadarok') $unit = '부';
                            }
                        }
                    ?>
                    <tr<?php if ($group_count > 1): ?> style="background: #f8fafc;"<?php endif; ?>>
                        <td style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: center;">
                            <div class="product-name" style="font-weight: 600; color: #2d3748; font-size: 15px;">
                                <?php echo $product['name']; ?>
                                <?php if ($group_count > 1): ?>
                                <span style="display: inline-block; background: #e74c3c; color: white; font-size: 11px; padding: 1px 8px; border-radius: 10px; margin-left: 4px; font-weight: bold;">×<?php echo $group_count; ?>건</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="border: 1px solid #ccc; padding: 10px; vertical-align: top;">
                            <div class="specs-cell" style="line-height: 1.6;">
                                <?php $specs = $specFormatter->format($item); ?>
                                <?php if (!empty($specs['line1'])): ?>
                                    <div class="spec-line" style="color: #2d3748; margin-bottom: 2px;"><?php echo htmlspecialchars($specs['line1']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($specs['line2'])): ?>
                                    <div class="spec-line" style="color: #4a5568;"><?php echo htmlspecialchars($specs['line2']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($specs['additional'])): ?>
                                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                        <div style="color: #e53e3e; font-weight: 600; font-size: 12px; margin-bottom: 4px;">추가옵션</div>
                                        <div style="color: #2d3748; font-size: 11px;"><?php echo htmlspecialchars($specs['additional']); ?></div>
                                    </div>
                                <?php endif; ?>
                        </td>
                        <td class="amount-cell <?php echo $is_flyer ? 'leaflet' : ''; ?>" style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: center;">
                            <span class="amount-value" style="font-weight: 600; font-size: 15px;"><?php echo $main_amount_display; ?></span>
                            <?php if ($is_flyer && $sub_amount): ?>
                                <br><span class="amount-sub" style="font-size: 12px; color: #1e88ff;">(<?php echo number_format($sub_amount); ?>매)</span>
                            <?php endif; ?>
                        </td>
                        <td class="unit-cell" style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: center;">
                            <span class="amount-unit" style="font-size: 14px; color: #2d3748;"><?php echo $unit; ?></span>
                        </td>
                        <td class="td-right" style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: right;">
                            <div class="price-label" style="font-size: 11px; color: #718096; margin-bottom: 2px;">부가세 별도</div>
                            <?php if ($group_count > 1): ?>
                            <div style="font-size: 12px; color: #718096; margin-bottom: 2px;">
                                <?php echo number_format($is_direct_order ? $first_item['price'] : $first_item['st_price']); ?>원 × <?php echo $group_count; ?>건
                            </div>
                            <div class="price-total" style="font-weight: 600; font-size: 15px; color: #2d3748;">
                                = <?php echo number_format($group_total_price); ?>원
                            </div>
                            <?php else: ?>
                            <div class="price-total" style="font-weight: 600; font-size: 15px; color: #2d3748;">
                                <?php echo number_format($is_direct_order ? $first_item['price'] : $first_item['st_price']); ?>원
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php
                    // 비그룹 아이템 렌더링 (기존 로직 유지)
                    foreach ($order_ungrouped as $index => $item):
                        $product = $product_info_map[$item['product_type']] ?? ['name' => '상품', 'icon' => '', 'color' => '#f5f5f5'];

                        $is_flyer = in_array($item['product_type'], ['inserted', 'leaflet']);
                        $show_sheet_count = ($is_flyer && !empty($item['flyer_mesu']));

                        $main_amount_val = 1;
                        $main_amount_display = '1';
                        $unit = '매';
                        $sub_amount = null;

                        if ($is_direct_order) {
                            $main_amount_val = !empty($item['MY_amount']) ? floatval($item['MY_amount']) : 1;
                            if ($is_flyer) {
                                $unit = '연';
                                $main_amount_display = formatQuantityValue($main_amount_val, 'inserted');
                                if (!empty($item['mesu'])) $sub_amount = intval($item['mesu']);
                            } else {
                                $main_amount_display = formatQuantityValue($main_amount_val, $item['product_type']);
                                if ($item['product_type'] == 'ncrflambeau') $unit = '권';
                                elseif ($item['product_type'] == 'cadarok') $unit = '부';
                            }
                        } else {
                            if ($is_flyer) {
                                $unit = '연';
                                $main_amount_val = !empty($item['MY_amount']) ? floatval($item['MY_amount']) : 1;
                                $main_amount_display = formatQuantityValue($main_amount_val, 'inserted');
                                $sub_amount = $item['mesu'] ?? $item['flyer_mesu'] ?? null;
                            } else {
                                $main_amount_val = !empty($item['mesu']) ? intval($item['mesu']) : (!empty($item['MY_amount']) ? intval($item['MY_amount']) : 1);
                                $main_amount_display = formatQuantityValue($main_amount_val, $item['product_type']);
                                if ($item['product_type'] == 'ncrflambeau') $unit = '권';
                                elseif ($item['product_type'] == 'cadarok') $unit = '부';
                            }
                        }
                    ?>
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: center;">
                            <div class="product-name" style="font-weight: 600; color: #2d3748; font-size: 15px;">
                                <?php echo $product['name']; ?>
                            </div>
                        </td>
                        <td style="border: 1px solid #ccc; padding: 10px; vertical-align: top;">
                            <div class="specs-cell" style="line-height: 1.6;">
                                <?php
                                $specs = $specFormatter->format($item);
                                ?>
                                <?php if (!empty($specs['line1'])): ?>
                                    <div class="spec-line" style="color: #2d3748; margin-bottom: 2px;"><?php echo htmlspecialchars($specs['line1']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($specs['line2'])): ?>
                                    <div class="spec-line" style="color: #4a5568;"><?php echo htmlspecialchars($specs['line2']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($specs['additional'])): ?>
                                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                        <div style="color: #e53e3e; font-weight: 600; font-size: 12px; margin-bottom: 4px;">추가옵션</div>
                                        <div style="color: #2d3748; font-size: 11px;"><?php echo htmlspecialchars($specs['additional']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $comment_field = ($item['product_type'] === 'namecard' && !empty($item['NC_comment']))
                                    ? $item['NC_comment']
                                    : ($item['MY_comment'] ?? '');
                                ?>
                                <?php if (!empty($comment_field)): ?>
                                    <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                                        <strong>요청사항:</strong> <?php echo htmlspecialchars($comment_field); ?>
                                    </div>
                                <?php endif; ?>
                        </td>
                        <td class="amount-cell <?php echo $is_flyer ? 'leaflet' : ''; ?>" style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: center;">
                            <span class="amount-value" style="font-weight: 600; font-size: 15px;"><?php echo $main_amount_display; ?></span>
                            <?php if ($is_flyer && $sub_amount): ?>
                                <br><span class="amount-sub" style="font-size: 12px; color: #1e88ff;">(<?php echo number_format($sub_amount); ?>매)</span>
                            <?php endif; ?>
                        </td>
                        <td class="unit-cell" style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: center;">
                            <span class="amount-unit" style="font-size: 14px; color: #2d3748;"><?php echo $unit; ?></span>
                        </td>
                        <td class="td-right" style="border: 1px solid #ccc; padding: 10px; vertical-align: middle; text-align: right;">
                            <div class="price-label" style="font-size: 11px; color: #718096; margin-bottom: 2px;">부가세 별도</div>
                            <div class="price-total" style="font-weight: 600; font-size: 15px; color: #2d3748;">
                                <?php echo number_format($is_direct_order ? $item['price'] : $item['st_price']); ?>원
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 주문 요약 (장바구니 스타일) - 상품 목록 아래 배치 -->
            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; max-width: 1100px; margin-left: auto; margin-right: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div style="color: #4a5568; font-weight: 600; font-size: 16px;">주문 요약</div>
                    <div style="color: #718096; font-size: 13px;">총 <?php echo $total_info['count']; ?>개 상품</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div style="text-align: center; padding: 12px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="color: #718096; font-size: 12px; margin-bottom: 4px;">상품금액</div>
                        <div style="color: #2d3748; font-weight: 600; font-size: 15px;"><?php echo number_format($total_info['total']); ?>원</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="color: #718096; font-size: 12px; margin-bottom: 4px;">부가세</div>
                        <div style="color: #2d3748; font-weight: 600; font-size: 15px;"><?php echo number_format($total_info['total_vat'] - $total_info['total']); ?>원</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background-color: #1E90FF; border: 1px solid #1873CC; border-radius: 6px; color: white;">
                        <div style="opacity: 0.9; font-size: 12px; margin-bottom: 4px;">총 결제금액</div>
                        <div style="font-weight: 700; font-size: 18px;"><?php echo number_format($total_info['total_vat']); ?>원</div>
                    </div>
                </div>
            </div>

            <!-- 주문자 정보 입력 폼 -->
            <form method="post" action="ProcessOrder_unified.php" id="orderForm" onsubmit="return prepareBusinessAddress()">
                <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf.php'; csrf_field(); ?>
                <!-- 주문 데이터를 hidden으로 전달 -->
                <input type="hidden" name="total_price" id="total_price" 
                       value="<?php echo $total_info['total']; ?>" 
                       onchange="calculateAmountDisplay()">
                <input type="hidden" name="total_price_vat" id="total_price_vat" 
                       value="<?php echo $total_info['total_vat']; ?>" 
                       onchange="calculateAmountDisplay()">
                <input type="hidden" name="items_count" value="<?php echo $total_info['count']; ?>">
                <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                <input type="hidden" name="is_direct_order" value="<?php echo $is_direct_order ? '1' : '0'; ?>">
                
                <?php if ($is_direct_order): ?>
                    <!-- 직접 주문 데이터 전달 -->
                    <input type="hidden" name="direct_product_type" value="<?php echo htmlspecialchars($cart_items[0]['product_type']); ?>">
                    <input type="hidden" name="direct_MY_type" value="<?php echo htmlspecialchars($cart_items[0]['MY_type']); ?>">
                    <input type="hidden" name="direct_MY_Fsd" value="<?php echo htmlspecialchars($cart_items[0]['MY_Fsd']); ?>">
                    <input type="hidden" name="direct_PN_type" value="<?php echo htmlspecialchars($cart_items[0]['PN_type']); ?>">
                    <input type="hidden" name="direct_POtype" value="<?php echo htmlspecialchars($cart_items[0]['POtype']); ?>">
                    <input type="hidden" name="direct_MY_amount" value="<?php echo htmlspecialchars($cart_items[0]['MY_amount']); ?>">
                    <input type="hidden" name="direct_ordertype" value="<?php echo htmlspecialchars($cart_items[0]['ordertype']); ?>">
                    <input type="hidden" name="direct_color_text" value="<?php echo htmlspecialchars($cart_items[0]['color_text']); ?>">
                    <input type="hidden" name="direct_paper_type_text" value="<?php echo htmlspecialchars($cart_items[0]['paper_type_text']); ?>">
                    <input type="hidden" name="direct_paper_size_text" value="<?php echo htmlspecialchars($cart_items[0]['paper_size_text']); ?>">
                    <input type="hidden" name="direct_sides_text" value="<?php echo htmlspecialchars($cart_items[0]['sides_text']); ?>">
                    <input type="hidden" name="direct_quantity_text" value="<?php echo htmlspecialchars($cart_items[0]['quantity_text']); ?>">
                    <input type="hidden" name="direct_design_text" value="<?php echo htmlspecialchars($cart_items[0]['design_text']); ?>">
                    <input type="hidden" name="direct_price" value="<?php echo $cart_items[0]['price']; ?>">
                    <input type="hidden" name="direct_vat_price" value="<?php echo $cart_items[0]['vat_price']; ?>">
                <?php endif; ?>
                
                <?php if (!$is_logged_in): ?>
                    <!-- 비회원인 경우 기본값으로 different 설정 -->
                    <input type="hidden" name="address_option" value="different">
                <?php endif; ?>
                
                <h3>신청자 정보</h3>
                <?php if ($is_logged_in): ?>
                    <div style="background: #e8f5e8; padding: 0.8rem; border-radius: 4px; margin-bottom: 1rem; border-left: 3px solid #27ae60;">
                        <p class="description-text" style="margin: 0; color: #27ae60; font-weight: bold;">로그인된 회원 정보가 자동으로 입력됩니다</p>
                        <p class="small-text" style="margin: 0.3rem 0 0 0; color: #666;">정보가 변경된 경우 직접 수정해주세요</p>
                    </div>
                <?php else: ?>
                    <div style="background: #e3f2fd; padding: 0.8rem; border-radius: 4px; margin-bottom: 1rem; border-left: 3px solid #2196f3;">
                        <p class="description-text" style="margin: 0; color: #1976d2; font-weight: bold;">
                            회원이신가요?
                            <button type="button" onclick="showLoginModal(); return false;" style="background: #2196f3; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 15px; margin-left: 0.5rem; cursor: pointer;">
                                로그인하기
                            </button>
                        </p>
                        <p class="small-text" style="margin: 0.3rem 0 0 0; color: #666;">로그인하시면 회원 정보가 자동으로 입력됩니다</p>
                    </div>
                    <p class="description-text" style="color: #666; margin-bottom: 1rem;">* 신청자 정보를 정확히 입력 바랍니다.</p>
                <?php endif; ?>

                <!-- 신청자 정보 - 엑셀 테이블 스타일 -->
                <div class="excel-cart-table-wrapper" style="margin-bottom: 1.5rem;">
                    <table class="excel-cart-table">
                        <colgroup>
                            <col style="width: 15%;">
                            <col style="width: 35%;">
                            <col style="width: 15%;">
                            <col style="width: 35%;">
                        </colgroup>
                        <tbody>
                            <tr>
                                <th class="th-left" style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">성명/상호 *</th>
                                <td style="border: 1px solid #ccc; padding: 5px;">
                                    <input type="text" name="username" required
                                           value="<?php
                                           if ($is_logged_in && $user_info) {
                                               $default_name = $user_info['name'] ?? '';
                                               if (empty($default_name) || $default_name === '0') {
                                                   $default_name = $user_info['username'] ?? '';
                                                   if (empty($default_name) && !empty($user_info['email'])) {
                                                       $email_parts = explode('@', $user_info['email']);
                                                       $default_name = $email_parts[0];
                                                   }
                                               }
                                               echo htmlspecialchars($default_name);
                                           }
                                           ?>"
                                           placeholder="성명 또는 상호명"
                                           style="width: 100%; padding: 8px; border: none; background: transparent;">
                                </td>
                                <th class="th-left" style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">이메일 *</th>
                                <td style="border: 1px solid #ccc; padding: 5px;">
                                    <input type="email" name="email" required
                                           value="<?php echo $is_logged_in ? htmlspecialchars($user_info['email'] ?? '') : ''; ?>"
                                           placeholder="이메일 주소"
                                           style="width: 100%; padding: 8px; border: none; background: transparent;">
                                </td>
                            </tr>
                            <tr>
                                <th class="th-left" style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">전화번호 *</th>
                                <td style="border: 1px solid #ccc; padding: 5px;">
                                    <input type="tel" name="phone" required
                                           value="<?php echo $is_logged_in ? htmlspecialchars($user_info['phone'] ?? '') : ''; ?>"
                                           placeholder="전화번호"
                                           style="width: 100%; padding: 8px; border: none; background: transparent;">
                                </td>
                                <th class="th-left" style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">핸드폰</th>
                                <td style="border: 1px solid #ccc; padding: 5px;">
                                    <input type="tel" name="Hendphone"
                                           placeholder="핸드폰 번호"
                                           style="width: 100%; padding: 8px; border: none; background: transparent;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- 수령지 정보 섹션 -->
                <h3>우편물 수령지</h3>

                <?php if ($is_logged_in): ?>
                    <div style="margin-bottom: 1rem; display: flex; gap: 2rem; align-items: center;">
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="use_member_address" name="address_option" value="member" checked onchange="toggleAddressInput()"
                                   style="margin-right: 0.5rem; transform: scale(1.1);">
                            <label for="use_member_address" class="description-text" style="font-weight: 600; color: #2c3e50; cursor: pointer;">
                                회원 정보 주소 사용
                            </label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="use_different_address" name="address_option" value="different" onchange="toggleAddressInput()"
                                   style="margin-right: 0.5rem; transform: scale(1.1);">
                            <label for="use_different_address" class="description-text" style="font-weight: 600; color: #2c3e50; cursor: pointer;">
                                다른 수령지 사용
                            </label>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="address_section" style="margin-bottom: 1rem;">
                    <div style="display: flex; gap: 0.8rem; margin-bottom: 0.6rem;">
                        <input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호" readonly
                               style="width: 140px;">
                        <button type="button" onclick="sample6_execDaumPostcode()"
                                style="background: #3498db; color: white; border: none; cursor: pointer;">
                            우편번호 찾기
                        </button>
                    </div>
                    <input type="text" id="sample6_address" name="sample6_address" placeholder="주소" readonly required
                           style="width: 100%; margin-bottom: 0.6rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem;">
                        <input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소">
                        <input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목">
                    </div>
                </div>

                <!-- 물품수령방법 -->
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.6rem; font-weight: 600; color: #2c3e50;">
                        물품수령방법
                    </label>
                    <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="delivery_parcel" name="delivery_method" value="택배" checked
                                   style="margin-right: 0.3rem; transform: scale(1.1);">
                            <label for="delivery_parcel" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                택배
                            </label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="delivery_visit" name="delivery_method" value="방문(방문시 전화)"
                                   style="margin-right: 0.3rem; transform: scale(1.1);">
                            <label for="delivery_visit" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                방문(방문시 전화)
                            </label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="delivery_motorcycle" name="delivery_method" value="오토바이"
                                   style="margin-right: 0.3rem; transform: scale(1.1);">
                            <label for="delivery_motorcycle" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                오토바이
                            </label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="delivery_damas" name="delivery_method" value="다마스"
                                   style="margin-right: 0.3rem; transform: scale(1.1);">
                            <label for="delivery_damas" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                다마스
                            </label>
                        </div>
                    </div>

                    <!-- 택배 선택 시: 운임구분 + 배송정보 -->
                    <div id="shipping_options_area" style="display: none; margin-top: 0.5rem;">
                        <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 0.7rem;">
                            <span style="font-weight: 600; color: #555; font-size: 0.9rem;">운임구분:</span>
                            <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                <input type="radio" name="shipping_fee_type" value="착불" checked
                                       style="margin-right: 0.3rem;" onchange="toggleShippingInfo()">
                                <span style="font-weight: 500; color: #2c3e50;">착불</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                <input type="radio" name="shipping_fee_type" value="선불"
                                       style="margin-right: 0.3rem;" onchange="toggleShippingInfo()">
                                <span style="font-weight: 500; color: #2c3e50;">선불</span>
                            </label>
                        </div>

                        <!-- 묶음배송/개별포장 선택 (2건 이상일 때만 표시) -->
                        <?php if (count($cart_items) > 1): ?>
                        <div id="packing_mode_area" style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 0.7rem;">
                            <span style="font-weight: 600; color: #555; font-size: 0.9rem;">배송방식:</span>
                            <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                <input type="radio" name="shipping_bundle_type" value="bundle" checked
                                       style="margin-right: 0.3rem;" onchange="onPackingModeChange()">
                                <span style="font-weight: 500; color: #2c3e50;">묶음배송</span>
                                <span style="font-size: 0.75rem; color: #888; margin-left: 4px;">(1건으로 합포장)</span>
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                <input type="radio" name="shipping_bundle_type" value="individual"
                                       style="margin-right: 0.3rem;" onchange="onPackingModeChange()">
                                <span style="font-weight: 500; color: #2c3e50;">개별포장</span>
                                <span style="font-size: 0.75rem; color: #888; margin-left: 4px;">(품목별 별도 박스)</span>
                            </label>
                        </div>
                        <?php endif; ?>

                        <div id="shipping_prepaid_info" style="display: none; background: #f0f7ff; border: 1px solid #b8d4f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                                <span style="font-size: 1.1rem;">📦</span>
                                <span style="font-weight: 700; color: #1E4E79; font-size: 0.95rem;">배송 정보</span>
                                <span style="background: #e0a800; color: #fff; font-size: 0.7rem; padding: 1px 6px; border-radius: 3px; font-weight: 600;">추정</span>
                            </div>
                            <div id="shipping_estimate_content" style="font-size: 0.9rem; color: #333; line-height: 1.7;">
                                <div>예상 무게: <strong id="est_weight">계산 중...</strong> <span style="color: #888; font-size: 0.8rem;">(부자재 포함)</span></div>
                                <div>예상 박스: <strong id="est_boxes">계산 중...</strong></div>
                                <div>추정 택배비: <strong id="est_fee">계산 중...</strong> <span id="est_fee_label" style="color: #888; font-size: 0.8rem;"></span></div>
                            </div>
                            <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #d0e3f5; font-size: 0.82rem; color: #666; line-height: 1.5;">
                                ※ 추정치이며 실제와 다를 수 있습니다<br>
                                <span style="display: inline-block; margin-top: 4px; background: #dc3545; color: #fff; font-size: 1rem; font-weight: 700; padding: 6px 12px; border-radius: 5px;">☎ 02-2632-1830 전화 후 택배비가 확정됩니다</span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="shipping_fee_type" id="hidden_shipping_fee_type" value="착불">
                    <input type="hidden" name="shipping_bundle_type" id="hidden_shipping_bundle_type" value="<?php echo count($cart_items) > 1 ? 'bundle' : ''; ?>">
                </div>

                <!-- 결제방법 -->
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.6rem; font-weight: 600; color: #2c3e50;">
                        결제방법
                    </label>
                    <div style="display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="payment_transfer" name="payment_method" value="계좌이체" checked
                                   style="margin-right: 0.3rem; transform: scale(1.1);" onchange="toggleDepositorName()">
                            <label for="payment_transfer" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                계좌이체
                            </label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="payment_card" name="payment_method" value="카드결제"
                                   style="margin-right: 0.3rem; transform: scale(1.1);" onchange="toggleDepositorName()">
                            <label for="payment_card" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                카드결제
                            </label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="payment_cash" name="payment_method" value="현금"
                                   style="margin-right: 0.3rem; transform: scale(1.1);" onchange="toggleDepositorName()">
                            <label for="payment_cash" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                현금
                            </label>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <input type="radio" id="payment_other" name="payment_method" value="기타"
                                   style="margin-right: 0.3rem; transform: scale(1.1);" onchange="toggleDepositorName()">
                            <label for="payment_other" style="font-weight: 500; color: #2c3e50; cursor: pointer; margin: 0;">
                                기타<span style="font-size: 0.85rem; color: #888;">(요청사항에 기재)</span>
                            </label>
                        </div>
                    </div>
                    <!-- 입금자명 (계좌이체 선택 시만 표시) -->
                    <div id="depositor_name_section" style="margin-top: 0.8rem;">
                        <div style="display: flex; align-items: center; gap: 0.8rem;">
                            <label style="font-weight: 600; color: #2c3e50; white-space: nowrap; min-width: 70px;">입금자명</label>
                            <input type="text" name="bankname" placeholder="입금자명을 입력하세요"
                                   style="flex: 1; max-width: 300px; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.95rem;">
                        </div>
                    </div>
                </div>

                <!-- 요청사항 -->
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; color: #2c3e50;">
                        요청사항
                    </label>
                    <textarea name="cont" rows="3"
                              style="width: 100%; resize: vertical;"
                              placeholder="추가 요청사항이 있으시면 입력해주세요"></textarea>
                </div>
                
                <!-- 사업자 정보 섹션 -->
                <div style="margin-bottom: 1rem; border: 1px solid #e0e0e0; border-radius: 4px; padding: 1rem; background: #f8f9fa;">
                    <div style="display: flex; align-items: center; margin-bottom: 0.8rem;">
                        <input type="checkbox" id="is_business" name="is_business" value="1" onchange="toggleBusinessInfo()"
                               style="margin-right: 0.5rem; transform: scale(1.1);">
                        <label for="is_business" style="font-weight: 600; color: #3498db; cursor: pointer; font-size: 1rem;">
                            사업자 주문 (세금계산서 발행 필요시 체크)
                        </label>
                    </div>

                    <div id="business_info" style="display: none;">
                        <!-- 사업자 정보 가로 배치 -->
                        <div class="business-info-horizontal">
                            <!-- 0줄: 상호(회사명) -->
                            <div class="info-row-single">
                                <div class="info-field-full">
                                    <label>상호(회사명) *</label>
                                    <input type="text" name="business_name"
                                           placeholder="상호명을 입력하세요">
                                </div>
                            </div>
                            <!-- 1줄: 사업자등록번호 + 대표자명 -->
                            <div class="info-row">
                                <div class="info-field">
                                    <label>사업자등록번호</label>
                                    <input type="text" name="business_number"
                                           placeholder="000-00-00000" maxlength="12">
                                </div>
                                <div class="info-field">
                                    <label>대표자명</label>
                                    <input type="text" name="business_owner"
                                           placeholder="대표자 성명">
                                </div>
                            </div>
                            <!-- 2줄: 사업장 주소 -->
                            <div class="info-row-single">
                                <div style="display: grid; grid-template-columns: 110px 1fr; gap: 5px; align-items: start;">
                                    <label style="white-space: nowrap; font-weight: 600; color: #2c3e50; margin: 0; padding-top: 8px;">사업장 주소</label>
                                    <div>
                                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                            <input type="text" id="business_postcode" placeholder="우편번호" readonly
                                                   style="width: 140px;">
                                            <button type="button" onclick="execBusinessDaumPostcode()"
                                                    style="background: #3498db; color: white; border: none; padding: 8px 16px; cursor: pointer; border-radius: 3px; white-space: nowrap;">
                                                우편번호 찾기
                                            </button>
                                        </div>
                                        <input type="text" id="business_address" placeholder="주소" readonly
                                               style="width: 100%; margin-bottom: 0.5rem;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                            <input type="text" id="business_detailAddress" placeholder="상세주소">
                                            <input type="text" id="business_extraAddress" placeholder="참고항목">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 3줄: 업태 + 종목 -->
                            <div class="info-row">
                                <div class="info-field">
                                    <label>업태</label>
                                    <input type="text" name="business_type"
                                           placeholder="제조업, 서비스업">
                                </div>
                                <div class="info-field">
                                    <label>종목</label>
                                    <input type="text" name="business_item"
                                           placeholder="인쇄업, 광고업">
                                </div>
                            </div>
                            <!-- 4줄: 세금용 메일 -->
                            <div class="info-row-single">
                                <div class="info-field-full">
                                    <label>세금용 메일 *</label>
                                    <input type="email" name="tax_invoice_email"
                                           placeholder="세금계산서를 받을 이메일 주소를 입력하세요">
                                </div>
                            </div>
                        </div>

                        <div style="background: #e8f4fd; padding: 0.6rem; border-radius: 4px; margin-top: 0.8rem;">
                            <p class="small-text" style="margin: 0; color: #2c3e50;"><strong>안내:</strong></p>
                            <p class="small-text" style="margin: 0.2rem 0 0 0; color: #666;">• 세금계산서 발행을 원하시면 정확한 사업자 정보를 입력해주세요</p>
                            <p class="small-text" style="margin: 0.2rem 0 0 0; color: #666;">• 사업자등록번호는 하이픈(-) 포함하여 입력해주세요</p>
                            <p class="small-text" style="margin: 0.2rem 0 0 0; color: #666;">• 일반 연락용 이메일과 다른 경우 별도로 입력해주세요</p>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <button type="submit"
                            style="background-color: #D9534F; color: white; border: none; padding: 12px 36px; border-radius: 20px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(217, 83, 79, 0.25);">
                        주문 완료하기
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 초컴팩트 레이아웃을 위한 반응형 스타일 -->
<style>
/* 전체 페이지 높이 최적화 */
body {
    margin: 0;
    padding: 0;
    line-height: 1.2 !important;
}

.container {
    max-width: 1100px;
    margin: 0 auto !important;
    padding: 0.3rem 0.8rem !important;
}

.card {
    margin-bottom: 0.5rem !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
}

.card-header {
    padding: 0.4rem !important;
}

.card-header h2 {
    font-size: 0.9rem !important;
    margin: 0 !important;
}

.card-header p {
    font-size: 0.7rem !important;
    margin: 0.1rem 0 0 0 !important;
}

/* ===== 통일된 폰트 크기 시스템 ===== */
/* 섹션 제목 */
h3 {
    margin: 0.5rem 0 0.6rem 0;
    font-size: 0.95rem;
    line-height: 1.2;
}

/* 입력 요소 기본 스타일 */
input, textarea, select {
    line-height: 1.2;
    border-radius: 3px;
    font-size: 0.875rem;
    padding: 8px 10px;
    border: 1px solid #ddd;
    box-sizing: border-box;
}

/* 레이블 통일 */
label {
    font-size: 0.875rem;
    line-height: 1.2;
}

/* 버튼 */
button {
    line-height: 1.3;
    border-radius: 4px;
    font-size: 0.875rem;
}

/* 설명 텍스트 */
.description-text, .info-text {
    font-size: 0.8rem;
    line-height: 1.3;
}

/* 작은 텍스트 (안내문구) */
.small-text {
    font-size: 0.75rem;
    line-height: 1.2;
}

/* 6열 그리드 시스템 */
.flex-grid-6 {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.4rem;
    align-items: end;
    margin-bottom: 0.5rem;
}

.flex-grid-6 .col-1 { grid-column: span 1; }
.flex-grid-6 .col-2 { grid-column: span 2; }
.flex-grid-6 .col-3 { grid-column: span 3; }
.flex-grid-6 .col-4 { grid-column: span 4; }
.flex-grid-6 .col-5 { grid-column: span 5; }
.flex-grid-6 .col-6 { grid-column: span 6; }

/* 중앙 집중형 레이아웃 */
.centered-form {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* ===== 정돈된 폼 테이블 레이아웃 ===== */
/* 2열 그리드 테이블형 레이아웃 */
.form-table-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.6rem 1rem;
    margin-bottom: 1rem;
}

.form-table-grid .form-field {
    display: flex;
    flex-direction: column;
}

.form-table-grid .form-field label {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-table-grid .form-field input,
.form-table-grid .form-field textarea {
    width: 100%;
    border: 1px solid #ddd;
}

.form-table-grid .form-field.full-width {
    grid-column: span 2;
}


/* ===== 사업자 정보 가로 배치 레이아웃 ===== */
.business-info-horizontal {
    margin-bottom: 1rem;
}

.business-info-horizontal .info-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 0.8rem;
}

.business-info-horizontal .info-row-single {
    margin-bottom: 0.8rem;
}

.business-info-horizontal .info-field {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 5px;
    align-items: center;
}

/* 두 번째 필드 (대표자명, 종목) label 너비 조정 */
.business-info-horizontal .info-row .info-field:nth-child(2) {
    grid-template-columns: 70px 1fr;
}

.business-info-horizontal .info-field-full {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 5px;
    align-items: start;
}

.business-info-horizontal .info-field label,
.business-info-horizontal .info-field-full label {
    white-space: nowrap;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    text-align: left;
}

.business-info-horizontal .info-field input,
.business-info-horizontal .info-field-full input,
.business-info-horizontal .info-field-full textarea {
    width: 100%;
}

/* 1행 4칸 그리드 레이아웃 (레거시 호환) */
.single-row-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 0.5rem !important;
    align-items: end !important;
    margin-bottom: 0.8rem !important;
}

.single-row-grid > div {
    min-width: 0; /* 그리드 오버플로우 방지 */
}

/* 컴팩트 그리드를 6열로 강제 변경 (기존 사업자 정보용) */
.compact-info-grid {
    display: grid !important;
    grid-template-columns: repeat(6, 1fr) !important;
    gap: 0.4rem !important;
    align-items: end !important;
    margin-bottom: 0.5rem !important;
    justify-content: center !important;
}

/* 기본 span 설정 - 자동으로 2칸씩 차지 */
.compact-info-grid > div {
    grid-column: span 2;
}

/* 이메일 필드는 더 넓게 (3칸) */
.compact-info-grid > div:has(input[type="email"]) {
    grid-column: span 3 !important;
}

/* 빈 공간 생성 */
.grid-spacer {
    grid-column: span 1;
}

/* 폼 테이블 그리드 반응형 처리 */
@media (max-width: 768px) {
    .form-table-grid {
        grid-template-columns: 1fr !important;
        gap: 0.6rem !important;
    }

    .form-table-grid .form-field.full-width {
        grid-column: span 1 !important;
    }
}

/* 1행 4칸 그리드 반응형 처리 */
@media (max-width: 1024px) {
    .single-row-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.4rem !important;
    }
}

@media (max-width: 480px) {
    .single-row-grid {
        grid-template-columns: 1fr !important;
        gap: 0.3rem !important;
    }

    .single-row-grid label {
        font-size: 0.8rem !important;
        margin-bottom: 0.1rem !important;
    }

    .single-row-grid input {
        padding: 6px 8px !important;
        font-size: 0.85rem !important;
    }
}

/* 초컴팩트 레이아웃을 위한 반응형 처리 (기존 사업자 정보용) */
@media (max-width: 1024px) {
    .compact-info-grid {
        grid-template-columns: repeat(4, 1fr) !important;
    }
    .compact-info-grid > div {
        grid-column: span 2 !important;
    }
}

@media (max-width: 768px) {
    .compact-info-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .compact-info-grid > div {
        grid-column: span 1 !important;
    }
    
    .compact-info-grid label {
        font-size: 0.7rem !important;
        margin-bottom: 0.1rem !important;
    }
    
    .compact-info-grid input {
        padding: 4px 6px !important;
        font-size: 0.75rem !important;
    }
    
    /* 모바일에서 전체 마진 더 줄이기 */
    h3 {
        margin-bottom: 0.3rem !important;
        font-size: 0.85rem !important;
    }
    
    .container > div {
        padding: 1rem !important;
    }
}

@media (max-width: 480px) {
    .compact-info-grid {
        grid-template-columns: 1fr !important;
        gap: 0.4rem !important;
    }
    
    /* 매우 작은 화면에서 더 컴팩트하게 */
    .compact-info-grid label {
        font-size: 0.75rem !important;
        margin-bottom: 0.1rem !important;
    }
    
    .compact-info-grid input {
        padding: 5px 6px !important;
        font-size: 0.8rem !important;
    }
}
</style>

<!-- 로그인 모달 포함 -->
<div id="loginModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0; color: white;">🔐 로그인</h3>
            <span class="close" onclick="hideLoginModal()">&times;</span>
        </div>
        
        <?php if (!empty($login_message)): ?>
            <div class="login-message <?php echo strpos($login_message, '성공') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($login_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="modal-tabs">
            <button class="tab-btn active" onclick="switchTab('login')" id="loginTab">로그인</button>
            <button class="tab-btn" onclick="switchTab('register')" id="registerTab">회원가입</button>
        </div>
        
        <!-- 로그인 폼 -->
        <div id="loginForm" class="tab-content active">
            <form method="POST" action="">
                <input type="hidden" name="login_action" value="1">
                <input type="hidden" name="cart_session_id" value="<?php echo htmlspecialchars($session_id); ?>">
                <div class="form-group">
                    <label>아이디</label>
                    <input type="text" name="username" required placeholder="아이디를 입력하세요">
                </div>
                <div class="form-group">
                    <label>비밀번호</label>
                    <input type="password" name="password" required placeholder="비밀번호를 입력하세요">
                </div>
                <button type="submit" class="btn-primary">로그인</button>
            </form>
        </div>
        
        <!-- 회원가입 폼 -->
        <div id="registerForm" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="register_action" value="1">
                <input type="hidden" name="cart_session_id" value="<?php echo htmlspecialchars($session_id); ?>">
                <div class="form-group">
                    <label>아이디 *</label>
                    <input type="text" name="reg_username" required placeholder="아이디를 입력하세요">
                </div>
                <div class="form-group">
                    <label>비밀번호 * (6자 이상)</label>
                    <input type="password" name="reg_password" required placeholder="비밀번호를 입력하세요">
                </div>
                <div class="form-group">
                    <label>비밀번호 확인 *</label>
                    <input type="password" name="reg_confirm_password" required placeholder="비밀번호를 다시 입력하세요">
                </div>
                <div class="form-group">
                    <label>이름 *</label>
                    <input type="text" name="reg_name" required placeholder="이름을 입력하세요">
                </div>
                <div class="form-group">
                    <label>이메일</label>
                    <input type="email" name="reg_email" placeholder="이메일을 입력하세요">
                </div>
                <div class="form-group">
                    <label>전화번호</label>
                    <input type="tel" name="reg_phone" placeholder="전화번호를 입력하세요">
                </div>
                <button type="submit" class="btn-primary">회원가입</button>
            </form>
        </div>
    </div>
</div>

<!-- 로그인 모달 스타일 -->
<style>
/* ID 선택자로 구체성 높이기 - common-styles.css의 min-width: 1000px 오버라이드 */
#loginModal.modal {
    position: fixed;
    z-index: 9999999 !important; /* 챗봇(999999)보다 높게 설정 */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

#loginModal .modal-content {
    background: white;
    padding: 0;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    overflow: hidden;
    width: 360px;
    max-width: 95%;
    min-width: auto;
}

#loginModal .modal-header {
    background-color: #1E90FF;
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#loginModal .close {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    color: white;
}

#loginModal .close:hover {
    opacity: 0.7;
}

#loginModal .modal-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

#loginModal .tab-btn {
    flex: 1;
    padding: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

#loginModal .tab-btn.active {
    background: white;
    border-bottom: 2px solid #3498db;
    color: #3498db;
}

#loginModal .tab-content {
    display: none;
    padding: 1.5rem;
}

#loginModal .tab-content.active {
    display: block;
}

#loginModal .form-group {
    margin-bottom: 1rem;
}

#loginModal .form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 500;
    color: #2c3e50;
    font-size: 0.9rem;
}

#loginModal .form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    box-sizing: border-box;
    line-height: 1.5;
}

#loginModal .form-group input:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

#loginModal .btn-primary {
    width: 100%;
    padding: 0.75rem;
    background-color: #1E90FF;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    line-height: 1.5;
}

#loginModal .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

#loginModal .login-message {
    padding: 0.75rem;
    margin: 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
}

#loginModal .login-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

#loginModal .login-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<!-- 다음 우편번호 서비스 -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
function sample6_execDaumPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            var addr = '';
            var extraAddr = '';

            if (data.userSelectedType === 'R') {
                addr = data.roadAddress;
            } else {
                addr = data.jibunAddress;
            }

            if(data.userSelectedType === 'R'){
                if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                    extraAddr += data.bname;
                }
                if(data.buildingName !== '' && data.apartment === 'Y'){
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                if(extraAddr !== ''){
                    extraAddr = ' (' + extraAddr + ')';
                }
                document.getElementById("sample6_extraAddress").value = extraAddr;
            } else {
                document.getElementById("sample6_extraAddress").value = '';
            }

            document.getElementById('sample6_postcode').value = data.zonecode;
            document.getElementById('sample6_address').value = addr;
            document.getElementById("sample6_detailAddress").focus();
        }
    }).open();
}

// 사업장 주소 검색 함수
function execBusinessDaumPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            var addr = '';
            var extraAddr = '';

            if (data.userSelectedType === 'R') {
                addr = data.roadAddress;
            } else {
                addr = data.jibunAddress;
            }

            if(data.userSelectedType === 'R'){
                if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                    extraAddr += data.bname;
                }
                if(data.buildingName !== '' && data.apartment === 'Y'){
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                if(extraAddr !== ''){
                    extraAddr = ' (' + extraAddr + ')';
                }
                document.getElementById("business_extraAddress").value = extraAddr;
            } else {
                document.getElementById("business_extraAddress").value = '';
            }

            document.getElementById('business_postcode').value = data.zonecode;
            document.getElementById('business_address').value = addr;
            document.getElementById("business_detailAddress").focus();
        }
    }).open();
}

// 사업장 주소 합치기 함수
function prepareBusinessAddress() {
    // 계좌이체 선택 시 입금자명 필수 검증
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (paymentMethod && paymentMethod.value === '계좌이체') {
        const banknameInput = document.querySelector('input[name="bankname"]');
        const bankname = banknameInput ? banknameInput.value.trim() : '';

        if (!bankname) {
            alert('계좌이체를 선택하셨습니다.\n\n입금자명을 반드시 입력해주세요.\n(입금 확인을 위해 필요합니다)');
            if (banknameInput) banknameInput.focus();
            return false;
        }

        // 입금자명이 주문자명과 다르면 경고 모달 2회 표시
        const orderName = document.querySelector('input[name="username"]');
        if (orderName && orderName.value.trim() && bankname !== orderName.value.trim()) {
            // 비동기 모달 처리 — 폼 제출 중단 후 모달 완료 시 재제출
            if (!window._depositorWarningPassed) {
                showDepositorWarning(orderName.value.trim(), bankname, banknameInput);
                return false;
            }
            // 2회 경고 통과 후 플래그 리셋
            window._depositorWarningPassed = false;
        }
    }

    const checkbox = document.getElementById('is_business');

    if (checkbox && checkbox.checked) {
        const postcode = document.getElementById('business_postcode').value;
        const address = document.getElementById('business_address').value;
        const detailAddress = document.getElementById('business_detailAddress').value;
        const extraAddress = document.getElementById('business_extraAddress').value;

        // 사업장 주소를 합쳐서 business_address에 저장
        let fullAddress = '';
        if (postcode) fullAddress += '[' + postcode + '] ';
        if (address) fullAddress += address;
        if (detailAddress) fullAddress += ' ' + detailAddress;
        if (extraAddress) fullAddress += ' ' + extraAddress;

        // 합쳐진 주소를 hidden input으로 전송
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'business_address';
        hiddenInput.value = fullAddress.trim();
        document.getElementById('orderForm').appendChild(hiddenInput);
    }

    // 로딩 스피너 표시
    if (typeof showDusonLoading === 'function') {
        showDusonLoading('주문 처리 중...');
    }

    return true;
}

// name 속성으로 빈 필드에만 값 채우기
function fillIfEmpty(fieldName, value) {
    if (!value) return;
    const field = document.querySelector('input[name="' + fieldName + '"]');
    if (field && !field.value.trim()) {
        field.value = value;
    }
}

// 사업장 주소 자동 채움 (DB에 "[우편번호] 주소 상세주소" 형태로 저장된 경우 파싱)
function fillBusinessAddress(fullAddress) {
    if (!fullAddress) return;
    const postcodeEl = document.getElementById('business_postcode');
    const addressEl = document.getElementById('business_address');
    const detailEl = document.getElementById('business_detailAddress');

    // 이미 주소가 채워져 있으면 건너뛰기
    if (addressEl && addressEl.value.trim()) return;

    // "[07301] 서울 영등포구 ... 1층  (영등포동4가)" 형태 파싱
    let postcode = '';
    let address = fullAddress;
    let detail = '';

    // 우편번호 추출: [12345] 또는 (12345) 패턴
    const postcodeMatch = fullAddress.match(/^\[(\d{5})\]\s*/);
    if (postcodeMatch) {
        postcode = postcodeMatch[1];
        address = fullAddress.substring(postcodeMatch[0].length);
    }

    // 상세주소 분리: 쉼표나 괄호 뒤의 부분을 상세주소로
    // 예: "서울 영등포구 영등포로36길 9 1층  (영등포동4가)" → 주소 + 상세
    const commaIdx = address.indexOf(',');
    if (commaIdx > 0) {
        detail = address.substring(commaIdx + 1).trim();
        address = address.substring(0, commaIdx).trim();
    }

    if (postcodeEl && postcode) postcodeEl.value = postcode;
    if (addressEl) addressEl.value = address;
    if (detailEl && detail) detailEl.value = detail;
}

// 사업자 정보 토글 함수
function toggleBusinessInfo() {
    const checkbox = document.getElementById('is_business');
    const businessInfo = document.getElementById('business_info');

    if (checkbox.checked) {
        businessInfo.style.display = 'block';
        // 사업자 정보 필드들을 필수로 만들기
        const businessFields = businessInfo.querySelectorAll('input[name^="business_"], input[name="tax_invoice_email"]');
        businessFields.forEach(field => {
                if (field.name === 'business_name' || field.name === 'business_number' || field.name === 'business_owner' || field.name === 'tax_invoice_email') {
                field.required = true;
            }
        });
        // 회원 사업자 정보 자동 채움 (빈 필드만)
        if (typeof memberInfo !== 'undefined' && memberInfo) {
            fillIfEmpty('business_name', memberInfo.businessName);
            fillIfEmpty('business_number', memberInfo.businessNumber);
            fillIfEmpty('business_owner', memberInfo.businessOwner);
            fillIfEmpty('business_type', memberInfo.businessType);
            fillIfEmpty('business_item', memberInfo.businessItem);
            fillIfEmpty('tax_invoice_email', memberInfo.taxInvoiceEmail);
            // 사업장 주소 자동 채움 (id 기반 필드)
            if (memberInfo.businessAddress) {
                fillBusinessAddress(memberInfo.businessAddress);
            }
        }
    } else {
        businessInfo.style.display = 'none';
        // 사업자 정보 필드들의 필수 속성 제거 및 값 초기화
        const businessFields = businessInfo.querySelectorAll('input[name^="business_"], input[name="tax_invoice_email"]');
        businessFields.forEach(field => {
            field.required = false;
            field.value = '';
        });
        // 사업장 주소 id 기반 필드도 초기화
        ['business_postcode', 'business_address', 'business_detailAddress', 'business_extraAddress'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }
}

// ===== 입금자명 ≠ 주문자명 경고 모달 (2회 표시) =====
window._depositorWarningPassed = false;
window._depositorWarningCount = 0;

function showDepositorWarning(orderNameVal, banknameVal, banknameInput) {
    window._depositorWarningCount = 0;
    _showDepositorModal(orderNameVal, banknameVal, banknameInput);
}

function _showDepositorModal(orderNameVal, banknameVal, banknameInput) {
    window._depositorWarningCount++;
    var isSecond = (window._depositorWarningCount >= 2);

    // 오버레이
    var overlay = document.createElement('div');
    overlay.id = 'depositor-warn-overlay';
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;';

    // 모달 박스
    var modal = document.createElement('div');
    modal.style.cssText = 'background:#fff;border-radius:12px;padding:28px 24px 20px;max-width:400px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.25);text-align:center;';

    // 아이콘
    var icon = document.createElement('div');
    icon.style.cssText = 'font-size:48px;margin-bottom:12px;';
    icon.textContent = isSecond ? '🚨' : '⚠️';

    // 제목
    var title = document.createElement('div');
    title.style.cssText = 'font-size:18px;font-weight:700;color:#c0392b;margin-bottom:14px;';
    title.textContent = isSecond ? '[ 최종 확인 ] 입금자명이 다릅니다!' : '입금자명이 주문자명과 다릅니다!';

    // 비교 박스
    var infoBox = document.createElement('div');
    infoBox.style.cssText = 'background:#fff3f3;border:1px solid #e74c3c;border-radius:8px;padding:14px 16px;margin-bottom:14px;text-align:left;';
    infoBox.innerHTML =
        '<div style="margin-bottom:6px;"><span style="color:#666;font-size:13px;">주문자명:</span> <strong style="color:#2c3e50;font-size:15px;">' + orderNameVal + '</strong></div>' +
        '<div><span style="color:#666;font-size:13px;">입금자명:</span> <strong style="color:#c0392b;font-size:15px;">' + banknameVal + '</strong></div>';

    // 안내 문구
    var msg = document.createElement('div');
    msg.style.cssText = 'font-size:13px;color:#555;margin-bottom:18px;line-height:1.6;';
    if (isSecond) {
        msg.innerHTML = '입금자명이 다르면 <strong style="color:#c0392b;">입금 확인이 불가</strong>할 수 있습니다.<br>반드시 <strong style="color:#c0392b;">☎ 02-2632-1830</strong>으로 연락해주세요.';
    } else {
        msg.innerHTML = '입금자명이 다를 경우 반드시<br><strong>☎ 02-2632-1830</strong>으로 알려주셔야<br>입금 확인이 가능합니다.';
    }

    // 버튼 영역
    var btnWrap = document.createElement('div');
    btnWrap.style.cssText = 'display:flex;gap:10px;justify-content:center;';

    // 수정 버튼
    var btnFix = document.createElement('button');
    btnFix.type = 'button';
    btnFix.textContent = '입금자명 수정';
    btnFix.style.cssText = 'flex:1;padding:10px;border:1px solid #ccc;border-radius:6px;background:#f5f5f5;font-size:14px;font-weight:600;cursor:pointer;color:#333;';
    btnFix.onclick = function() {
        overlay.remove();
        window._depositorWarningCount = 0;
        if (banknameInput) banknameInput.focus();
    };

    // 계속 버튼
    var btnCont = document.createElement('button');
    btnCont.type = 'button';
    btnCont.style.cssText = 'flex:1;padding:10px;border:none;border-radius:6px;background:#c0392b;color:#fff;font-size:14px;font-weight:600;cursor:pointer;';
    if (isSecond) {
        btnCont.textContent = '이대로 주문하기';
        btnCont.onclick = function() {
            overlay.remove();
            window._depositorWarningPassed = true;
            // 주문 폼 재제출 (반드시 #orderForm 지정 — 헤더의 로그아웃 폼 오선택 방지)
            var form = document.getElementById('orderForm');
            if (form) {
                if (form.requestSubmit) {
                    form.requestSubmit();
                } else {
                    // requestSubmit 미지원 브라우저 fallback
                    if (prepareBusinessAddress() !== false) {
                        form.submit();
                    }
                }
            }
        };
    } else {
        btnCont.textContent = '확인했습니다';
        btnCont.onclick = function() {
            overlay.remove();
            _showDepositorModal(orderNameVal, banknameVal, banknameInput);
        };
    }

    btnWrap.appendChild(btnFix);
    btnWrap.appendChild(btnCont);

    // 카운터 표시 (1/2 또는 2/2)
    var counter = document.createElement('div');
    counter.style.cssText = 'margin-top:12px;font-size:11px;color:#999;';
    counter.textContent = '(' + window._depositorWarningCount + '/2)';

    modal.appendChild(icon);
    modal.appendChild(title);
    modal.appendChild(infoBox);
    modal.appendChild(msg);
    modal.appendChild(btnWrap);
    modal.appendChild(counter);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
}

// 결제방법 변경 시 입금자명 표시/숨김
function toggleDepositorName() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    const section = document.getElementById('depositor_name_section');
    const input = section.querySelector('input[name="bankname"]');
    if (selected && selected.value === '계좌이체') {
        section.style.display = 'block';
        if (input) input.required = true;
        // 로그인 회원이면 주문자명을 입금자명 기본값으로
        if (input && !input.value.trim()) {
            const orderName = document.querySelector('input[name="username"]');
            if (orderName && orderName.value.trim()) {
                input.value = orderName.value.trim();
            }
        }
    } else {
        section.style.display = 'none';
        if (input) {
            input.required = false;
            input.value = '';
        }
    }
}

// 회원 주소 정보 로드 함수
function loadMemberAddress() {
    console.log('loadMemberAddress() called');
    
    if (!memberInfo) {
        console.log('No member info available');
        return;
    }
    
    console.log('Loading member address...', memberInfo);
    
    // 주소 필드에 회원 정보 입력
    if (memberInfo.postcode) {
        const postcodeField = document.getElementById('sample6_postcode');
        if (postcodeField) postcodeField.value = memberInfo.postcode;
    }
    
    if (memberInfo.address) {
        const addressField = document.getElementById('sample6_address');
        if (addressField) addressField.value = memberInfo.address;
    }
    
    if (memberInfo.detailAddress) {
        const detailField = document.getElementById('sample6_detailAddress');
        if (detailField) detailField.value = memberInfo.detailAddress;
    }
    
    if (memberInfo.extraAddress) {
        const extraField = document.getElementById('sample6_extraAddress');
        if (extraField) extraField.value = memberInfo.extraAddress;
    }
    
    console.log('Member address loaded successfully');
}

// 주소 입력 방식 토글 함수
function toggleAddressInput() {
    const memberAddressRadio = document.getElementById('use_member_address');
    const addressSection = document.getElementById('address_section');
    const addressFields = ['sample6_postcode', 'sample6_address', 'sample6_detailAddress', 'sample6_extraAddress'];
    
    if (memberAddressRadio && memberAddressRadio.checked) {
        // 회원 주소 사용 - 필드 비활성화 및 회원 정보로 채우기
        console.log('Using member address - loading member info...');
        addressSection.style.opacity = '0.6';
        addressSection.style.pointerEvents = 'none';
        
        // 회원 정보로 주소 필드 채우기
        loadMemberAddress();
        
        addressFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.required = false;
        });
    } else {
        // 다른 주소 사용 - 필드 활성화
        addressSection.style.opacity = '1';
        addressSection.style.pointerEvents = 'auto';
        
        // 주소 필드를 필수로 설정
        const addressField = document.getElementById('sample6_address');
        if (addressField) addressField.required = true;
        
        // 필드 초기화
        addressFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.value = '';
        });
    }
}

// 로그인 모달 관련 함수들
function showLoginModal() {
    const modal = document.getElementById('loginModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
    
    // 모달 컨텐츠 클릭 시 이벤트 전파 중지 (모달이 닫히는 것 방지)
    if (modalContent && !modalContent.hasAttribute('data-click-handler')) {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation(); // 클릭 이벤트가 모달 배경으로 전파되지 않도록
        });
        modalContent.setAttribute('data-click-handler', 'true');
    }
}

function hideLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // 스크롤 복원
}

function switchTab(tab) {
    // 모든 탭 버튼과 콘텐츠 비활성화
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // 선택된 탭 활성화
    if (tab === 'login') {
        document.getElementById('loginTab').classList.add('active');
        document.getElementById('loginForm').classList.add('active');
    } else if (tab === 'register') {
        document.getElementById('registerTab').classList.add('active');
        document.getElementById('registerForm').classList.add('active');
    }
}

// 모달 배경(overlay) 클릭 시에만 닫기 (드래그 방지)
(function() {
    const modal = document.getElementById('loginModal');
    let mouseDownTarget = null;
    
    // mousedown 시 타겟 기록
    modal.addEventListener('mousedown', function(event) {
        mouseDownTarget = event.target;
    });
    
    // mouseup 시 실제 클릭인지 확인
    modal.addEventListener('mouseup', function(event) {
        // mousedown과 mouseup이 같은 요소(모달 배경)에서 발생했을 때만 닫기
        if (event.target === this && mouseDownTarget === this) {
            hideLoginModal();
        }
        mouseDownTarget = null;
    });
    
    // click 이벤트는 사용 안 함 (드래그와 충돌)
})();

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('loginModal');
        if (modal && modal.style.display === 'flex') {
            hideLoginModal();
        }
    }
});

<?php if (!empty($login_message) && (strpos($login_message, '성공') !== false)): ?>
    // 로그인 성공 시 페이지 새로고침
    setTimeout(function() {
        location.reload();
    }, 1500);
<?php elseif (!empty($login_message)): ?>
    // 로그인 시도 후 메시지가 있으면 모달 표시
    document.addEventListener('DOMContentLoaded', function() {
        showLoginModal();
    });
<?php endif; ?>

// 회원 정보를 JavaScript 변수로 전달
<?php if ($is_logged_in && $user_info): ?>
var memberInfo = {
    postcode: '<?php echo htmlspecialchars($user_info['postcode'] ?? $user_info['zip'] ?? ''); ?>',
    address: '<?php echo htmlspecialchars($user_info['address'] ?? $user_info['zip1'] ?? ''); ?>',
    detailAddress: '<?php echo htmlspecialchars($user_info['detail_address'] ?? $user_info['zip2'] ?? ''); ?>',
    extraAddress: '<?php echo htmlspecialchars($user_info['extra_address'] ?? ''); ?>',
    name: '<?php echo htmlspecialchars($user_info['name'] ?? ''); ?>',
    email: '<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>',
    phone: '<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>',
    // 사업자 정보
    businessName: '<?php echo htmlspecialchars($user_info['business_name'] ?? ''); ?>',
    businessNumber: '<?php echo htmlspecialchars($user_info['business_number'] ?? ''); ?>',
    businessOwner: '<?php echo htmlspecialchars($user_info['business_owner'] ?? ''); ?>',
    businessType: '<?php echo htmlspecialchars($user_info['business_type'] ?? ''); ?>',
    businessItem: '<?php echo htmlspecialchars($user_info['business_item'] ?? ''); ?>',
    businessAddress: '<?php echo htmlspecialchars($user_info['business_address'] ?? ''); ?>',
    taxInvoiceEmail: '<?php echo htmlspecialchars($user_info['tax_invoice_email'] ?? ''); ?>'
};
console.log('Member info loaded:', memberInfo);
<?php else: ?>
var memberInfo = null;
console.log('No member info available');
<?php endif; ?>

// 금액 계산 및 표시 함수
function calculateAmountDisplay() {
    // 공급가액 계산 (부가세 제외 금액)
    var total_vat = parseFloat(document.getElementById('total_price_vat').value) || 0;
    var price_supply = total_vat;  // money_4 = money_5 - money_5 * 0.1
    
    // 부가세 계산
    var total_price = parseFloat(document.getElementById('total_price').value) || 0;
    var price_vat_amount = total_price - price_supply;
    
    // 한국어 금액 포맷팅
    var price_supply_kor = price_supply.toLocaleString('ko-KR');
    var price_vat_amount_kor = price_vat_amount.toLocaleString('ko-KR');
    var total_price_kor = total_price.toLocaleString('ko-KR');
    
    // 화면에 표시
    document.getElementById('display_price_supply').textContent = price_supply_kor + '원';
    document.getElementById('display_price_vat_amount').textContent = price_vat_amount_kor + '원';
    document.getElementById('display_total_price').textContent = total_price_kor + '원';
}

// 페이지 로드 시 실행
document.addEventListener('DOMContentLoaded', function() {
    // 회원 정보 자동 입력 먼저 실행
    <?php if ($is_logged_in && $user_info): ?>
        console.log('Loading member address on page load...');
        loadMemberAddress();
    <?php endif; ?>
    
    // 페이지 로드 시 주소 입력 방식 초기화
    <?php if ($is_logged_in): ?>
        setTimeout(() => toggleAddressInput(), 100); // 약간의 지연 후 실행
    <?php endif; ?>
    
    // 페이지 로드 시 금액 계산 및 표시
    calculateAmountDisplay();
    
    const businessNumberInput = document.querySelector('input[name="business_number"]');
    if (businessNumberInput) {
        businessNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length >= 3 && value.length <= 5) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            } else if (value.length > 5) {
                value = value.substring(0, 3) + '-' + value.substring(3, 5) + '-' + value.substring(5, 10);
            }
            e.target.value = value;
        });
    }
});

// ===== 배송 운임구분 (택배 선불/착불) =====
var cartItemsForShipping = <?php echo json_encode(array_map(function($item) {
    return [
        'product_type'   => $item['product_type'] ?? '',
        'MY_Fsd'         => $item['MY_Fsd'] ?? '',
        'PN_type'        => $item['PN_type'] ?? '',
        'MY_amount'      => $item['MY_amount'] ?? '',
        'mesu'           => $item['mesu'] ?? '',
        'flyer_mesu'     => $item['flyer_mesu'] ?? '',
        'POtype'         => $item['POtype'] ?? '',
        'spec_material'  => $item['spec_material'] ?? $item['MY_Fsd_name'] ?? '',
        'spec_size'      => $item['spec_size'] ?? $item['PN_type_name'] ?? '',
        'quantity_sheets'=> $item['quantity_sheets'] ?? '',
        'quantity_value' => $item['quantity_value'] ?? '',
        'quantity_unit'  => $item['quantity_unit'] ?? '',
    ];
}, $cart_items)); ?>;

function toggleDeliveryOptions() {
    var selected = document.querySelector('input[name="delivery_method"]:checked');
    var area = document.getElementById('shipping_options_area');
    var hiddenType = document.getElementById('hidden_shipping_fee_type');
    if (!area) return;
    if (selected && selected.value === '택배') {
        area.style.display = 'block';
    } else {
        area.style.display = 'none';
        if (hiddenType) hiddenType.value = '';
        var info = document.getElementById('shipping_prepaid_info');
        if (info) info.style.display = 'none';
    }
}

function toggleShippingInfo() {
    var feeType = document.querySelector('input[name="shipping_fee_type"]:checked');
    var infoDiv = document.getElementById('shipping_prepaid_info');
    var hiddenType = document.getElementById('hidden_shipping_fee_type');
    if (!infoDiv) return;
    if (feeType && feeType.value === '선불') {
        infoDiv.style.display = 'block';
        if (hiddenType) hiddenType.value = '선불';
        fetchShippingEstimate();
    } else {
        infoDiv.style.display = 'none';
        if (hiddenType) hiddenType.value = '착불';
    }
}

function getPackingMode() {
    var sel = document.querySelector('input[name="shipping_bundle_type"]:checked');
    return sel ? sel.value : '';
}

function onPackingModeChange() {
    var hidden = document.getElementById('hidden_shipping_bundle_type');
    if (hidden) hidden.value = getPackingMode();
    // 선불 상태이면 추정 재계산
    var feeType = document.querySelector('input[name="shipping_fee_type"]:checked');
    if (feeType && feeType.value === '선불') {
        fetchShippingEstimate();
    }
}

function fetchShippingEstimate() {
    var weightEl = document.getElementById('est_weight');
    var boxesEl = document.getElementById('est_boxes');
    var feeEl = document.getElementById('est_fee');
    var feeLabelEl = document.getElementById('est_fee_label');
    if (!weightEl || !boxesEl) return;
    if (!cartItemsForShipping || cartItemsForShipping.length === 0) {
        weightEl.textContent = '데이터 없음';
        boxesEl.textContent = '-';
        if (feeEl) feeEl.textContent = '-';
        return;
    }
    weightEl.textContent = '계산 중...';
    boxesEl.textContent = '계산 중...';
    if (feeEl) feeEl.textContent = '계산 중...';
    var formData = new FormData();
    formData.append('action', 'estimate');
    formData.append('cart_items', JSON.stringify(cartItemsForShipping));
    var packingMode = getPackingMode();
    if (packingMode) formData.append('packing_mode', packingMode);
    fetch('/includes/shipping_api.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success && res.data) {
                weightEl.textContent = '약 ' + res.data.total_weight_kg + 'kg';
                var boxLabel = res.data.total_boxes + '박스';
                if (res.data.packing_mode === 'bundle') boxLabel += ' (묶음)';
                boxesEl.textContent = boxLabel;
                // 택배비 표시
                if (feeEl && res.data.total_fee !== undefined) {
                    if (res.data.total_fee > 0) {
                        feeEl.textContent = '약 ' + Number(res.data.total_fee).toLocaleString() + '원';
                    } else {
                        feeEl.textContent = '-';
                    }
                }
                if (feeLabelEl && res.data.fee_label) {
                    feeLabelEl.textContent = '(' + res.data.fee_label + ')';
                }
            } else {
                weightEl.textContent = '계산 불가';
                boxesEl.textContent = '-';
                if (feeEl) feeEl.textContent = '-';
            }
        })
        .catch(function() {
            weightEl.textContent = '계산 오류';
            boxesEl.textContent = '-';
            if (feeEl) feeEl.textContent = '-';
        });
}

// 배송방법 라디오 이벤트 바인딩
document.querySelectorAll('input[name="delivery_method"]').forEach(function(radio) {
    radio.addEventListener('change', toggleDeliveryOptions);
});
// 운임구분 라디오 이벤트 바인딩
document.querySelectorAll('input[name="shipping_fee_type"]').forEach(function(radio) {
    radio.addEventListener('change', toggleShippingInfo);
});
// 초기 상태 설정
toggleDeliveryOptions();
</script>

<?php
// 공통 푸터 포함
include "../includes/footer.php";
?>