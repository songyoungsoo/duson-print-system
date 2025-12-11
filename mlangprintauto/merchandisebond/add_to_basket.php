<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

// 전체 에러 처리
try {
    error_log("=== 상품권 장바구니 처리 시작 ===");

    // 세션 시작
    session_start();
    error_log("세션 시작 완료");

    // 공통 함수 포함
    error_log("functions.php 포함 시작");
    include "../../includes/functions.php";
    error_log("functions.php 포함 완료");

    error_log("StandardUploadHandler.php 포함 시작");
    require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
    error_log("StandardUploadHandler.php 포함 완료");

    error_log("db.php 포함 시작");
    include "../../db.php";
    error_log("db.php 포함 완료");

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$Section = $_POST['Section'] ?? ''; // 상품권 재질
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'merchandisebond';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 프리미엄 옵션 데이터 수집 및 검증
$premium_options = [];
$premium_total = 0;

// 프리미엄 옵션 데이터 추출
if (isset($_POST['premium_options_total'])) {
    $premium_total = intval($_POST['premium_options_total']);

    // 상품권 프리미엄 옵션들 (명함과 동일)
    $option_names = ['foil', 'numbering', 'perforation', 'rounding', 'creasing'];

    foreach ($option_names as $option) {
        if (isset($_POST["{$option}_enabled"]) && $_POST["{$option}_enabled"] == '1') {
            $premium_options["{$option}_enabled"] = true;

            // 타입이 있는 옵션들 (코팅, 엠보싱, 금박)
            if (isset($_POST["{$option}_type"]) && !empty($_POST["{$option}_type"])) {
                $premium_options["{$option}_type"] = $_POST["{$option}_type"];
            }

            // 개별 가격 저장
            if (isset($_POST["{$option}_price"])) {
                $premium_options["{$option}_price"] = intval($_POST["{$option}_price"]);
            }
        }
    }

    $premium_options['premium_options_total'] = $premium_total;
}

// JSON 형태로 변환
$premium_options_json = json_encode($premium_options, JSON_UNESCAPED_UNICODE);

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그 (안전하게)
try {
    error_log("상품권 장바구니 추가 시작");
    error_log("POST 데이터 키들: " . implode(', ', array_keys($_POST)));
    error_log("파일 데이터: " . (isset($_FILES['uploaded_files']) ? "있음" : "없음"));
} catch (Exception $e) {
    error_log("디버그 로깅 오류: " . $e->getMessage());
}

if (empty($MY_type) || empty($Section) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    $missing_fields = [];
    if (empty($MY_type)) $missing_fields[] = 'MY_type';
    if (empty($Section)) $missing_fields[] = 'Section';
    if (empty($POtype)) $missing_fields[] = 'POtype';
    if (empty($MY_amount)) $missing_fields[] = 'MY_amount';
    if (empty($ordertype)) $missing_fields[] = 'ordertype';
    
    safe_json_response(false, null, '필수 정보가 누락되었습니다: ' . implode(', ', $missing_fields));
}

// 세션 ID 가져오기
$session_id = session_id();

// 디버그 로깅 강화
error_log("=== Cart Debug Info ===");
error_log("Session ID: " . $session_id);
error_log("Action: " . $action);
error_log("MY_type: " . $MY_type);
error_log("Section: " . $Section);
error_log("POtype: " . $POtype);
error_log("MY_amount: " . $MY_amount);
error_log("Price: " . $price);
error_log("Work memo length: " . strlen($work_memo));

// 파일 업로드 처리 (StandardUploadHandler 사용)
$upload_result = StandardUploadHandler::processUpload('merchandisebond', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

error_log("상품권 업로드 결과: " . count($uploaded_files) . " 개 파일, 경로: $img_folder");

// 필요한 컬럼이 있는지 확인하고 없으면 추가
$required_columns = [
    'session_id' => 'VARCHAR(255)',
    'product_type' => 'VARCHAR(50)',
    'MY_type' => 'VARCHAR(50)',
    'Section' => 'VARCHAR(50)',
    'POtype' => 'VARCHAR(50)',
    'MY_amount' => 'VARCHAR(50)',
    'ordertype' => 'VARCHAR(50)',
    'st_price' => 'INT(11)',
    'st_price_vat' => 'INT(11)',
    'premium_options' => 'TEXT',
    'premium_options_total' => 'INT(11)',
    'work_memo' => 'TEXT',
    'upload_method' => 'VARCHAR(50)',
    'uploaded_files' => 'TEXT',
    'ThingCate' => 'VARCHAR(255)',
    'ImgFolder' => 'VARCHAR(255)'
];

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (!mysqli_query($db, $add_column_query)) {
            safe_json_response(false, null, "컬럼 $column_name 추가 오류: " . mysqli_error($db));
        }
    }
}

// ✅ 상품권: quantity = MY_amount, unit = '매'
$quantity = floatval($MY_amount);
$unit = '매';

// 장바구니에 추가 - uploaded_files, ThingCate, ImgFolder 포함
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, quantity, unit, ordertype, st_price, st_price_vat, premium_options, premium_options_total, work_memo, upload_method, uploaded_files, ThingCate, ImgFolder)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    // 🔧 FIX: 18개 필드에 맞는 타입 문자열
    // 1-6: s,s,s,s,s,s (session~MY_amount)
    // 7-9: d,s,s (quantity=실수, unit, ordertype)
    // 10-11: i,i (st_price, st_price_vat)
    // 12-13: s,i (premium_options=JSON, premium_options_total=INT)
    // 14-18: s,s,s,s,s (work_memo~ImgFolder)
    mysqli_stmt_bind_param($stmt, "ssssssdssiisisssss",
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $quantity, $unit, $ordertype, $price, $vat_price, $premium_options_json, $premium_total,
        $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder);

    if (mysqli_stmt_execute($stmt)) {
        $basket_id = mysqli_insert_id($db);
        mysqli_stmt_close($stmt);

        $response_data = [
            'basket_id' => $basket_id,
            'uploaded_files_count' => count($uploaded_files),
            'img_folder' => $img_folder,
            'thing_cate' => $thing_cate
        ];

        safe_json_response(true, $response_data, '장바구니에 추가되었습니다.');
        
    } else {
        mysqli_stmt_close($stmt);
        safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
    }
} else {
    safe_json_response(false, null, '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);

} catch (Exception $e) {
    // 치명적인 오류 처리
    error_log("상품권 장바구니 치명적 오류: " . $e->getMessage());
    error_log("오류 스택: " . $e->getTraceAsString());
    safe_json_response(false, null, '시스템 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
} catch (Error $e) {
    // PHP 7+ Fatal Error 처리
    error_log("상품권 장바구니 Fatal Error: " . $e->getMessage());
    error_log("오류 스택: " . $e->getTraceAsString());
    safe_json_response(false, null, '치명적인 오류가 발생했습니다. 관리자에게 문의해주세요.');
}

?>
