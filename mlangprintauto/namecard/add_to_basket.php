<?php
// 공통 응답 함수 포함 (출력 버퍼링 및 에러 처리 포함)
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';  // Phase 2: 데이터 표준화
require_once __DIR__ . '/../../includes/ensure_shop_temp_columns.php';

// JSON 헤더 우선 설정
header('Content-Type: application/json; charset=utf-8');

// 세션 시작
session_start();

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

ensure_shop_temp_columns($db);

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$Section = $_POST['Section'] ?? ''; // 명함 재질
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'namecard';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 프리미엄 옵션 데이터 받기 — PremiumOptionsGeneric(NEW) 또는 개별 필드(OLD) 지원
$premium_options_data_raw = $_POST['premium_options_data'] ?? '';

if (!empty($premium_options_data_raw)) {
    // NEW: PremiumOptionsGeneric -> JSON 수신 -> OLD 형식으로 변환
    // getSelectedOptions() 반환: {"박":{enabled:true,variant_id:"1",price:30000}, premium_options_total:"90000"}
    $generic_data = json_decode($premium_options_data_raw, true);
    $premium_options = [];

    if (is_array($generic_data)) {
        // 한글 옵션명 -> 영문 키 매핑 (관리자 페이지 OrderFormOrderTree/OrderComplete 호환)
        $name_to_key = [
            '박' => 'foil', '넘버링' => 'numbering', '미싱' => 'perforation',
            '귀돌이' => 'rounding', '오시' => 'creasing'
        ];

        // variant_id -> variant_name 조회 (캐시 활용)
        $variant_map = [];
        $cache_file = __DIR__ . '/../../cache/premium_options_namecard.json';
        if (file_exists($cache_file)) {
            $cache = json_decode(file_get_contents($cache_file), true);
            if (!empty($cache['options'])) {
                foreach ($cache['options'] as $opt) {
                    if (!empty($opt['variants'])) {
                        foreach ($opt['variants'] as $v) {
                            $variant_map[$v['variant_id']] = $v['variant_name'];
                        }
                    }
                }
            }
        }

        foreach ($generic_data as $name => $info) {
            if ($name === 'premium_options_total' || !is_array($info)) continue;
            $key = $name_to_key[$name] ?? null;
            if (!$key) continue;

            $premium_options[$key . '_enabled'] = 1;
            $vid = $info['variant_id'] ?? '';
            $premium_options[$key . '_type'] = $variant_map[$vid] ?? $vid;
            $premium_options[$key . '_price'] = intval($info['price'] ?? 0);
        }
    }
    $premium_options['premium_options_total'] = intval($_POST['premium_options_total'] ?? 0);
} else {
    // OLD: 개별 $_POST 필드 (하드코딩 프론트엔드 호환용 fallback)
    $premium_options = [
        'foil_enabled' => $_POST['foil_enabled'] ?? 0,
        'foil_type' => $_POST['foil_type'] ?? '',
        'foil_price' => intval($_POST['foil_price'] ?? 0),
        'numbering_enabled' => $_POST['numbering_enabled'] ?? 0,
        'numbering_type' => $_POST['numbering_type'] ?? '',
        'numbering_price' => intval($_POST['numbering_price'] ?? 0),
        'perforation_enabled' => $_POST['perforation_enabled'] ?? 0,
        'perforation_type' => $_POST['perforation_type'] ?? '',
        'perforation_price' => intval($_POST['perforation_price'] ?? 0),
        'rounding_enabled' => $_POST['rounding_enabled'] ?? 0,
        'rounding_price' => intval($_POST['rounding_price'] ?? 0),
        'creasing_enabled' => $_POST['creasing_enabled'] ?? 0,
        'creasing_type' => $_POST['creasing_type'] ?? '',
        'creasing_price' => intval($_POST['creasing_price'] ?? 0),
        'premium_options_total' => intval($_POST['premium_options_total'] ?? 0)
    ];
}
$premium_options_json = json_encode($premium_options, JSON_UNESCAPED_UNICODE);
$premium_total = intval($premium_options['premium_options_total']);

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그: 받은 데이터 로그
error_log("=== 명함 장바구니 추가 시작 ===");
error_log("받은 POST 데이터: " . print_r($_POST, true));
error_log("세션 ID: " . session_id());
error_log("데이터베이스 연결 상태: " . ($db ? "OK" : "실패"));

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

// ✅ 파일 업로드 처리 (StandardUploadHandler 사용)
$upload_result = StandardUploadHandler::processUpload('namecard', $_FILES);

if (!$upload_result['success'] && !empty($upload_result['error'])) {
    safe_json_response(false, null, $upload_result['error']);
}

$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$upload_count = count($uploaded_files);

error_log("명함 업로드 결과: $upload_count 개 파일, 경로: $img_folder");

// uploaded_files를 JSON으로 변환 (테이블의 uploaded_files 컬럼에 저장)
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// 명함 옵션명 조회
$MY_type_name = '';
$Section_name = '';
$POtype_name = '';

// MY_type 이름 조회
if (!empty($MY_type)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'NameCard'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $MY_type);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $MY_type_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// Section 이름 조회
if (!empty($Section)) {
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'NameCard'";
    $name_stmt = mysqli_prepare($db, $name_query);
    if ($name_stmt) {
        mysqli_stmt_bind_param($name_stmt, "s", $Section);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        if ($name_row = mysqli_fetch_assoc($name_result)) {
            $Section_name = $name_row['title'];
        }
        mysqli_stmt_close($name_stmt);
    }
}

// POtype 이름 설정
switch ($POtype) {
    case '1':
        $POtype_name = '단면칼라';
        break;
    case '2':
        $POtype_name = '양면칼라';
        break;
    default:
        $POtype_name = '';
}

// ★ NEW: Receive quantity_display from JavaScript (dropdown text)
$quantity_display_from_dropdown = $_POST['quantity_display'] ?? '';

// ✅ Phase 2: 표준 데이터 생성 (레거시 → 표준)
$legacy_data = [
    'MY_type' => $MY_type,
    'MY_type_name' => $MY_type_name,
    'Section' => $Section,
    'Section_name' => $Section_name,
    'POtype' => $POtype,
    'POtype_name' => $POtype_name,
    'MY_amount' => $MY_amount,
    'ordertype' => $ordertype,
    'price' => $price,
    'vat_price' => $vat_price,
    'premium_options' => $premium_options_json,
    'quantity_display' => $quantity_display_from_dropdown  // ★ Pass dropdown text to DataAdapter
];

$standard_data = DataAdapter::legacyToStandard($legacy_data, 'namecard');

// 표준 필드 추출
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
$data_version = 2;  // Phase 2 신규 데이터

error_log("Phase 2: 표준 데이터 생성 완료 - spec_type: $spec_type, price_supply: $price_supply");

// ✅ 장바구니에 추가 - 레거시 + 표준 필드 모두 저장 (Dual-Write)
$insert_query = "INSERT INTO shop_temp (
    session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype,
    st_price, st_price_vat, premium_options, premium_options_total,
    MY_type_name, Section_name, POtype_name,
    work_memo, upload_method, uploaded_files, ThingCate, ImgFolder,
    spec_type, spec_material, spec_size, spec_sides, spec_design,
    quantity_value, quantity_unit, quantity_sheets, quantity_display,
    price_supply, price_vat, price_vat_amount,
    product_data_json, data_version
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

error_log("SQL 쿼리: " . $insert_query);
$stmt = mysqli_prepare($db, $insert_query);

if (!$stmt) {
    error_log("명함 장바구니 prepare 실패: " . mysqli_error($db));
    safe_json_response(false, null, 'SQL 준비 실패: ' . mysqli_error($db));
}

// 디버그 로깅
error_log("=== 명함 장바구니 저장 디버그 ===");
error_log("Session: $session_id, Product: $product_type, ImgFolder: $img_folder, ThingCate: $thing_cate");
error_log("premium_options_total: $premium_total");
error_log("Uploaded files JSON: " . $uploaded_files_json);

// Phase 2: 33개 파라미터 (레거시 19개 + 표준 14개)
// 타입 순서: session_id(s), product_type(s), MY_type(s), Section(s), POtype(s), MY_amount(s), ordertype(s),
//            st_price(d), st_price_vat(d), premium_options(s), premium_options_total(i),
//            MY_type_name(s), Section_name(s), POtype_name(s), work_memo(s), upload_method(s), uploaded_files(s), ThingCate(s), ImgFolder(s),
//            spec_type(s), spec_material(s), spec_size(s), spec_sides(s), spec_design(s),
//            quantity_value(d), quantity_unit(s), quantity_sheets(i), quantity_display(s),
//            price_supply(i), price_vat(i), price_vat_amount(i), product_data_json(s), data_version(i)
mysqli_stmt_bind_param($stmt, "sssssssddsisssssssssssssdsisiiisi",
    // 레거시 필드 (19개)
    $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype,
    $price, $vat_price, $premium_options_json, $premium_total,
    $MY_type_name, $Section_name, $POtype_name,
    $work_memo, $upload_method, $uploaded_files_json, $thing_cate, $img_folder,
    // 표준 필드 (14개)
    $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
    $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
    $price_supply, $price_vat, $price_vat_amount,
    $product_data_json, $data_version
);

if (mysqli_stmt_execute($stmt)) {
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    error_log("명함 장바구니 성공 - ID: $basket_id");

    safe_json_response(true, [
        'basket_id' => $basket_id,
        'uploaded_files_count' => count($uploaded_files),
        'upload_path' => $img_folder
    ], '장바구니에 추가되었습니다.');

} else {
    $error_msg = mysqli_stmt_error($stmt);
    error_log("명함 장바구니 저장 실패: " . $error_msg);
    mysqli_stmt_close($stmt);
    safe_json_response(false, null, '장바구니 추가 실패: ' . $error_msg);
}

mysqli_close($db);

?>
