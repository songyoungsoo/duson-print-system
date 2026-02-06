<?php
// 명함 성공 패턴 적용 - 안전한 JSON 응답 처리
require_once __DIR__ . '/../../includes/safe_json_response.php';
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';  // Phase 2: 데이터 표준화
require_once __DIR__ . '/../../includes/ensure_shop_temp_columns.php';

ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

session_start();
$session_id = session_id();

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

ensure_shop_temp_columns($db);

// POST 데이터 받기
$product_type = $_POST['product_type'] ?? 'ncrflambeau';
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$calculated_price = $_POST['calculated_price'] ?? 0;
$calculated_vat_price = $_POST['calculated_vat_price'] ?? 0;

// 추가 옵션 데이터 수집 (넘버링 + 미싱만)
$additional_options = [];
$additional_total = intval($_POST['additional_options_total'] ?? 0);

if ($additional_total > 0) {
    // 넘버링 (folding_enabled로 전송됨)
    if (isset($_POST['folding_enabled']) && $_POST['folding_enabled'] == '1') {
        $additional_options['folding_enabled'] = true;
        $additional_options['folding_type'] = $_POST['folding_type'] ?? '';
        $additional_options['folding_price'] = intval($_POST['folding_price'] ?? 0);
    }

    // 미싱 (creasing_enabled로 전송됨)
    if (isset($_POST['creasing_enabled']) && $_POST['creasing_enabled'] == '1') {
        $additional_options['creasing_enabled'] = true;
        $additional_options['creasing_lines'] = $_POST['creasing_lines'] ?? '';
        $additional_options['creasing_price'] = intval($_POST['creasing_price'] ?? 0);
    }

    $additional_options['additional_options_total'] = $additional_total;
}

$additional_options_json = json_encode($additional_options, JSON_UNESCAPED_UNICODE);

// 필수 필드 검증
if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount) || empty($ordertype)) {
    safe_json_response(false, null, '모든 옵션을 선택해주세요.');
}

if (empty($calculated_price) || empty($calculated_vat_price)) {
    safe_json_response(false, null, '가격 정보가 없습니다. 다시 계산해주세요.');
}

try {
    // 파일 업로드 처리 (StandardUploadHandler 사용)
    $upload_result = StandardUploadHandler::processUpload('ncrflambeau', $_FILES);

    if (!$upload_result['success'] && !empty($upload_result['error'])) {
        safe_json_response(false, null, $upload_result['error']);
    }

    $uploaded_files = $upload_result['files'];
    $img_folder = $upload_result['img_folder'];
    $thing_cate = $upload_result['thing_cate'];
    $upload_count = count($uploaded_files);

    error_log("양식지 업로드 결과: $upload_count 개 파일, 경로: $img_folder");

    // ✅ INSERT 방식으로 통일 - 모든 데이터를 한 번에 저장
    $work_memo = $_POST['work_memo'] ?? '';
    $upload_method = $_POST['upload_method'] ?? 'upload';
    $price = intval($calculated_price);
    $vat_price = intval($calculated_vat_price);

    // uploaded_files를 JSON으로 변환 (inserted 패턴)
    $uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

    // NCR 옵션명 조회
    $MY_type_name = '';
    $MY_Fsd_name = '';
    $PN_type_name = '';

    // MY_type 이름 조회 (도수)
    if (!empty($MY_type)) {
        $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'NcrFlambeau'";
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

    // MY_Fsd 이름 조회 (용지)
    if (!empty($MY_Fsd)) {
        $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'NcrFlambeau'";
        $name_stmt = mysqli_prepare($db, $name_query);
        if ($name_stmt) {
            mysqli_stmt_bind_param($name_stmt, "s", $MY_Fsd);
            mysqli_stmt_execute($name_stmt);
            $name_result = mysqli_stmt_get_result($name_stmt);
            if ($name_row = mysqli_fetch_assoc($name_result)) {
                $MY_Fsd_name = $name_row['title'];
            }
            mysqli_stmt_close($name_stmt);
        }
    }

    // PN_type 이름 조회 (타입)
    if (!empty($PN_type)) {
        $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? AND Ttable = 'NcrFlambeau'";
        $name_stmt = mysqli_prepare($db, $name_query);
        if ($name_stmt) {
            mysqli_stmt_bind_param($name_stmt, "s", $PN_type);
            mysqli_stmt_execute($name_stmt);
            $name_result = mysqli_stmt_get_result($name_stmt);
            if ($name_row = mysqli_fetch_assoc($name_result)) {
                $PN_type_name = $name_row['title'];
            }
            mysqli_stmt_close($name_stmt);
        }
    }

    // ★ NEW: Receive quantity_display from JavaScript (dropdown text)
    $quantity_display_from_dropdown = $_POST['quantity_display'] ?? '';

    // ✅ Phase 2: 표준 데이터 생성 (레거시 → 표준)
    $legacy_data = [
        'MY_type' => $MY_type,
        'MY_type_name' => $MY_type_name,
        'MY_Fsd' => $MY_Fsd,
        'MY_Fsd_name' => $MY_Fsd_name,
        'PN_type' => $PN_type,
        'PN_type_name' => $PN_type_name,
        'MY_amount' => $MY_amount,
        'ordertype' => $ordertype,
        'price' => $price,
        'vat_price' => $vat_price,
        'premium_options' => $additional_options_json,
        'quantity_display' => $quantity_display_from_dropdown  // ★ Pass dropdown text to DataAdapter
    ];

    $standard_data = DataAdapter::legacyToStandard($legacy_data, 'ncrflambeau');

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

    error_log("Phase 2: NCR 표준 데이터 생성 완료 - spec_type: $spec_type, price_supply: $price_supply");

    // ✅ 장바구니에 추가 - 레거시 + 표준 필드 모두 저장 (Dual-Write)
    $insert_query = "INSERT INTO shop_temp (
        session_id, product_type, MY_type, MY_Fsd, PN_type, MY_amount, ordertype,
        st_price, st_price_vat, premium_options, premium_options_total,
        work_memo, upload_method, ImgFolder, ThingCate, uploaded_files,
        spec_type, spec_material, spec_size, spec_sides, spec_design,
        quantity_value, quantity_unit, quantity_sheets, quantity_display,
        price_supply, price_vat, price_vat_amount,
        product_data_json, data_version
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $insert_query);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
    }

    // Phase 2: 30개 파라미터 (레거시 16개 + 표준 14개)
    // 타입 순서: session_id(s), product_type(s), MY_type(s), MY_Fsd(s), PN_type(s), MY_amount(s), ordertype(s),
    //            st_price(d), st_price_vat(d), premium_options(s), premium_options_total(i),
    //            work_memo(s), upload_method(s), ImgFolder(s), ThingCate(s), uploaded_files(s),
    //            spec_type(s), spec_material(s), spec_size(s), spec_sides(s), spec_design(s),
    //            quantity_value(d), quantity_unit(s), quantity_sheets(i), quantity_display(s),
    //            price_supply(i), price_vat(i), price_vat_amount(i), product_data_json(s), data_version(i)
    // ✅ 2026-01-15: 타입 문자열 수정 - 위치13 upload_method(i→s), 위치25 quantity_display(i→s)
    mysqli_stmt_bind_param($stmt, "sssssssddsissssssssssdsisiiisi",
        // 레거시 필드 (16개)
        $session_id, $product_type, $MY_type, $MY_Fsd, $PN_type, $MY_amount, $ordertype,
        $price, $vat_price, $additional_options_json, $additional_total,
        $work_memo, $upload_method, $img_folder, $thing_cate, $uploaded_files_json,
        // 표준 필드 (14개)
        $spec_type, $spec_material, $spec_size, $spec_sides, $spec_design,
        $quantity_value, $quantity_unit, $quantity_sheets, $quantity_display,
        $price_supply, $price_vat, $price_vat_amount,
        $product_data_json, $data_version
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('INSERT 실패: ' . mysqli_stmt_error($stmt));
    }
    
    $basket_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);
    
    // 성공 로그
    error_log("NcrFlambeau 장바구니 추가 성공: basket_id=$basket_id, session_id=$session_id, upload_count=$upload_count");
    
    $message = '장바구니에 추가되었습니다.';
    if ($upload_count > 0) {
        $message .= " (파일 {$upload_count}개 업로드 완료)";
    }
    
    safe_json_response(true, ['basket_id' => $basket_id, 'upload_count' => $upload_count], $message);
    
} catch (Exception $e) {
    error_log("NcrFlambeau 장바구니 추가 오류: " . $e->getMessage());
    safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . $e->getMessage());
}

mysqli_close($db);
?>