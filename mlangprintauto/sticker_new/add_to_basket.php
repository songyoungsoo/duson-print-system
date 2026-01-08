<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';  // Phase 2

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

// 세션 시작
session_start();
$session_id = session_id();

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";
$connect = $db;

// ========================================
// 견적서 모드 감지 (품목 코드 영향 없음)
// ========================================
$target_mode = $_GET['mode'] ?? 'normal';
$target_table = ($target_mode === 'quotation') ? 'quotation_temp' : 'shop_temp';

// 데이터베이스 연결 체크
check_db_connection($connect);
mysqli_set_charset($connect, "utf8");

// POST 데이터 받기 - 새 모달 형식 지원
$action = $_POST['action'] ?? 'add_to_basket';
$product_type = $_POST['product_type'] ?? 'sticker';

// 스티커 전용 필드 (새 모달 필드명 지원)
$jong = $_POST['jong'] ?? '';
$garo = $_POST['garo'] ?? '';
$sero = $_POST['sero'] ?? '';
$mesu = $_POST['mesu'] ?? '';
$uhyung = intval($_POST['uhyung'] ?? 0); // 🔧 DB uhyung 컬럼이 int이므로 변환 필요
$domusong = $_POST['domusong'] ?? '';

// 가격 정보 (새 모달에서는 price 필드로 전송)
$st_price = intval($_POST['price'] ?? $_POST['st_price'] ?? 0);
$st_price_vat = intval($_POST['st_price_vat'] ?? $st_price);

// 추가 정보 (새 모달 필드명 지원)
$work_memo = $_POST['memo'] ?? $_POST['work_memo'] ?? '';
$customer_name = $_POST['customerName'] ?? '';
$customer_phone = $_POST['customerPhone'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 공통 필드 (다른 상품용)
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$MY_comment = $_POST['MY_comment'] ?? '';

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("스티커 장바구니 추가 - 받은 데이터: " . print_r($_POST, true));

// 상품별 입력값 검증
if ($product_type === 'sticker') {
    if (empty($jong) || empty($garo) || empty($sero) || empty($mesu)) {
        $missing_fields = [];
        if (empty($jong)) $missing_fields[] = 'jong';
        if (empty($garo)) $missing_fields[] = 'garo';
        if (empty($sero)) $missing_fields[] = 'sero';
        if (empty($mesu)) $missing_fields[] = 'mesu';
        
        safe_json_response(false, null, '필수 정보가 누락되었습니다: ' . implode(', ', $missing_fields));
    }
} else {
    if (empty($MY_type) || empty($st_price)) {
        safe_json_response(false, null, '필수 입력값이 누락되었습니다.');
    }
}

// 세션 ID 가져오기
$session_id = session_id();

// 디버그 로깅 강화
error_log("=== Sticker Cart Debug Info ===");
error_log("Session ID: " . $session_id);
error_log("Action: " . $action);
error_log("Jong: " . $jong);
error_log("Size: " . $garo . "x" . $sero);
error_log("Quantity: " . $mesu);
error_log("Price: " . $st_price);
error_log("Work memo length: " . strlen($work_memo));

// 파일 업로드 처리 (StandardUploadHandler 사용)
$upload_result = StandardUploadHandler::processUpload('sticker', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

error_log("스티커 업로드 결과: " . count($uploaded_files) . " 개 파일, 경로: $img_folder");

// 파일명 정리 함수
function sanitize_filename($filename) {
    // 한글 및 특수문자 처리
    $filename = preg_replace('/[^a-zA-Z0-9가-힣._-]/', '_', $filename);
    return $filename;
}

// 장바구니 또는 견적서 임시 테이블이 없으면 생성
$create_table_query = "CREATE TABLE IF NOT EXISTS {$target_table} (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) NOT NULL DEFAULT 'sticker',
    jong VARCHAR(200),
    garo VARCHAR(50),
    sero VARCHAR(50),
    mesu VARCHAR(50),
    uhyung INT(1) DEFAULT 0,
    domusong VARCHAR(200),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    MY_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_product_type (product_type)
)";

if (!mysqli_query($connect, $create_table_query)) {
    echo json_encode(['success' => false, 'message' => '테이블 생성 오류: ' . mysqli_error($connect)]);
    exit;
}

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'session_id' => 'VARCHAR(255) NOT NULL',
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'sticker'",
    'jong' => 'VARCHAR(200)',
    'garo' => 'VARCHAR(50)',
    'sero' => 'VARCHAR(50)',
    'mesu' => 'VARCHAR(50)',
    'uhyung' => 'VARCHAR(200)', // 새 모달에서는 문자열 형태
    'domusong' => 'VARCHAR(200)',
    'MY_type' => 'VARCHAR(50)',
    'MY_Fsd' => 'VARCHAR(50)',
    'PN_type' => 'VARCHAR(50)',
    'MY_amount' => 'VARCHAR(50)',
    'ordertype' => 'VARCHAR(50)',
    'MY_comment' => 'TEXT',
    'st_price' => 'INT(11) DEFAULT 0',
    'st_price_vat' => 'INT(11) DEFAULT 0',
    'work_memo' => 'TEXT',
    'customer_name' => 'VARCHAR(100)', // 고객명 추가
    'customer_phone' => 'VARCHAR(50)', // 연락처 추가
    'upload_method' => 'VARCHAR(50)',
    'uploaded_files' => 'TEXT',
    'ThingCate' => 'VARCHAR(255)',
    'ImgFolder' => 'VARCHAR(255)'
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM {$target_table} LIKE '$column_name'";
    $column_result = mysqli_query($connect, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE {$target_table} ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($connect, $add_column_query)) {
            safe_json_response(false, null, "컬럼 $column_name 추가 오류: " . mysqli_error($connect));
        }
    }
}

// 장바구니에 추가 - 새 모달 정보 포함
if ($product_type === 'sticker') {
    // ★ NEW: Receive quantity_display from JavaScript (dropdown text)
    $quantity_display_from_dropdown = $_POST['quantity_display'] ?? '';

    // Phase 2: 표준 데이터 생성
    $legacy_data = [
        'jong' => $jong,
        'garo' => $garo,
        'sero' => $sero,
        'mesu' => $mesu,
        'uhyung' => $uhyung,
        'domusong' => $domusong,
        'price' => $st_price,
        'price_vat' => $st_price_vat,
        'quantity_display' => $quantity_display_from_dropdown  // ★ Pass dropdown text
    ];
    $standard_data = DataAdapter::legacyToStandard($legacy_data, 'sticker');

    $spec_type = $standard_data['spec_type'];
    $spec_material = $standard_data['spec_material'];
    $spec_size = $standard_data['spec_size'];
    $spec_sides = $standard_data['spec_sides'];
    $spec_design = $standard_data['spec_design'];
    $quantity_value = $standard_data['quantity_value'];
    $quantity_unit = $standard_data['quantity_unit'];
    $quantity_sheets = $standard_data['quantity_sheets'];
    $quantity_display = $standard_data['quantity_display'];  // ★ Use value from DataAdapter
    $price_supply = $standard_data['price_supply'];
    $price_vat = $standard_data['price_vat'];
    $price_vat_amount = $standard_data['price_vat_amount'];
    $product_data_json = json_encode($standard_data, JSON_UNESCAPED_UNICODE);
    $data_version = 2;

    // ✅ DEBUG: 저장 직전 값 확인
    error_log("add_to_basket DEBUG: quantity_display before DB save = {$quantity_display}");
    error_log("add_to_basket DEBUG: target_table = {$target_table}, mode = {$target_mode}");

    // ✅ quotation_temp는 product_data_json 필드가 없으므로 분기 처리
    if ($target_table === 'quotation_temp') {
        // quotation_temp: product_data_json 제외 (30개 필드)
        $insert_query = "INSERT INTO {$target_table} (
            session_id, product_type, jong, garo, sero, mesu, uhyung, domusong, st_price, st_price_vat,
            customer_name, customer_phone, work_memo, upload_method, uploaded_files, ThingCate, ImgFolder,
            spec_type, spec_material, spec_size, spec_sides, spec_design,
            quantity_value, quantity_unit, quantity_sheets, quantity_display,
            price_supply, price_vat, price_vat_amount, data_version
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($connect, $insert_query);
        if ($stmt) {
            // 30개 파라미터 (product_data_json 제외)
            mysqli_stmt_bind_param($stmt, "sssssssddissssssssssssdsiiiiii",
                $session_id, $product_type, $jong, $garo, $sero, $mesu, $uhyung, $domusong, $st_price, $st_price_vat,
                $customer_name, $customer_phone, $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder,
                $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
                $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
                $price_supply, $price_vat, $price_vat_amount, $data_version);
        }
    } else {
        // shop_temp: product_data_json 포함 (31개 필드)
        $insert_query = "INSERT INTO {$target_table} (
            session_id, product_type, jong, garo, sero, mesu, uhyung, domusong, st_price, st_price_vat,
            customer_name, customer_phone, work_memo, upload_method, uploaded_files, ThingCate, ImgFolder,
            spec_type, spec_material, spec_size, spec_sides, spec_design,
            quantity_value, quantity_unit, quantity_sheets, quantity_display,
            price_supply, price_vat, price_vat_amount, product_data_json, data_version
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($connect, $insert_query);
        if ($stmt) {
            // 31개 파라미터 (product_data_json 포함)
            mysqli_stmt_bind_param($stmt, "sssssssddissssssssssssdsiiiiisi",
                $session_id, $product_type, $jong, $garo, $sero, $mesu, $uhyung, $domusong, $st_price, $st_price_vat,
                $customer_name, $customer_phone, $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder,
                $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
                $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
                $price_supply, $price_vat, $price_vat_amount, $product_data_json, $data_version);
        }
    }

    if ($stmt) {
        
        if (mysqli_stmt_execute($stmt)) {
            $basket_id = mysqli_insert_id($connect);
            mysqli_stmt_close($stmt);

            $response_data = [
                'basket_id' => $basket_id,
                'uploaded_files_count' => count($uploaded_files),
                'img_folder' => $img_folder,
                'thing_cate' => $thing_cate,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'product_info' => [
                    'jong' => $jong,
                    'size' => $garo . 'x' . $sero . 'mm',
                    'quantity' => $mesu . '매',
                    'price' => number_format($st_price) . '원'
                ]
            ];

            $success_msg = ($target_mode === 'quotation') ? '견적서에 추가되었습니다.' : '장바구니에 추가되었습니다.';
            safe_json_response(true, $response_data, $success_msg);
            
        } else {
            mysqli_stmt_close($stmt);
            safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
        }
    } else {
        safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect));
    }
} else {
    // 카다록 등 다른 상품
    $insert_query = "INSERT INTO {$target_table} (session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, ordertype, MY_comment, st_price, st_price_vat, work_memo, upload_method, uploaded_files, ThingCate, ImgFolder)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($connect, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssiisssss",
            $session_id, $product_type, $MY_type, $MY_Fsd, $PN_type, $MY_amount, $ordertype, $MY_comment, $st_price, $st_price_vat,
            $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder);

        if (mysqli_stmt_execute($stmt)) {
            $success_msg = ($target_mode === 'quotation') ? '견적서에 추가되었습니다.' : '장바구니에 추가되었습니다.';
            safe_json_response(true, null, $success_msg);
        } else {
            mysqli_stmt_close($stmt);
            $error_context = ($target_mode === 'quotation') ? '견적서' : '장바구니';
            safe_json_response(false, null, "{$error_context} 추가 중 오류가 발생했습니다: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
    } else {
        safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect));
    }
}

mysqli_close($connect);
?>