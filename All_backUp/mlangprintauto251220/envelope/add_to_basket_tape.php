<?php
/**
 * 봉투 장바구니 추가 - 양면테이프 옵션 지원 버전
 * Created: 2025-01-17
 */

// 공통 응답 함수 포함
require_once __DIR__ . '/../../includes/safe_json_response.php';

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 세션 시작
session_start();

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 받기
$action = $_POST['action'] ?? '';
$MY_type = $_POST['MY_type'] ?? '';
$Section = $_POST['Section'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;
$product_type = $_POST['product_type'] ?? 'envelope';

// 추가 정보
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';
$uploaded_files_info = $_POST['uploaded_files_info'] ?? '';

// 양면테이프 옵션 데이터
$tape_enabled = isset($_POST['tape_enabled']) ? 1 : 0;
$tape_quantity = $_POST['tape_quantity'] ?? '';
$tape_price = (int)($_POST['tape_price'] ?? 0);

// 입력값 검증
if (!in_array($action, ['add_to_basket', 'add_to_basket_and_order'])) {
    safe_json_response(false, null, '잘못된 액션입니다.');
}

// 디버그 로그
error_log("=== 봉투 장바구니 추가 (양면테이프) ===");
error_log("양면테이프 활성화: " . ($tape_enabled ? 'YES' : 'NO'));
error_log("양면테이프 수량: " . $tape_quantity);
error_log("양면테이프 가격: " . $tape_price);

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

// 파일 업로드 처리
$uploaded_files = [];
$log_url = $_POST['log_url'] ?? '';
$log_y = $_POST['log_y'] ?? '';
$log_md = $_POST['log_md'] ?? '';
$log_ip = $_POST['log_ip'] ?? '';
$log_time = $_POST['log_time'] ?? '';

// 기존 시스템과 동일한 ImgFolder 경로 생성
$img_folder = "{$log_url}/{$log_y}/{$log_md}/{$log_ip}/{$log_time}";
$upload_directory = "../../PHPClass/MultyUpload/Upload/{$img_folder}/";

if (!empty($_FILES['uploaded_files'])) {
    // 디렉토리 생성
    if (!file_exists($upload_directory)) {
        mkdir($upload_directory, 0755, true);
    }

    foreach ($_FILES['uploaded_files']['name'] as $key => $filename) {
        if ($_FILES['uploaded_files']['error'][$key] == UPLOAD_ERR_OK) {
            $temp_file = $_FILES['uploaded_files']['tmp_name'][$key];
            $target_filename = time() . "_" . $filename;
            $target_path = $upload_directory . $target_filename;

            if (move_uploaded_file($temp_file, $target_path)) {
                $uploaded_files[] = $target_filename;
            }
        }
    }
}

// 업로드된 파일 정보 문자열 생성
$uploaded_files_str = implode('|', $uploaded_files);

// 옵션 타입 정보 가져오기
$type_title = '';
$section_title = '';
$po_title = '';

// MY_type 정보
$type_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ?";
$stmt = mysqli_prepare($db, $type_query);
mysqli_stmt_bind_param($stmt, "s", $MY_type);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $type_title = $row['title'];
}
mysqli_stmt_close($stmt);

// Section 정보
$section_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ?";
$stmt = mysqli_prepare($db, $section_query);
mysqli_stmt_bind_param($stmt, "s", $Section);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $section_title = $row['title'];
}
mysqli_stmt_close($stmt);

// POtype 텍스트 변환
switch($POtype) {
    case '1': $po_title = '마스터1도'; break;
    case '2': $po_title = '마스터2도'; break;
    case '3': $po_title = '칼라4도(옵셋)'; break;
    default: $po_title = '알 수 없음';
}

// shop_temp 테이블에 저장
$insertQuery = "INSERT INTO shop_temp (
    session_id, Page, MY_type, PD_type, MY_Fsd,
    PN_type, MY_amount, ordertype, Total_amount, Designfee,
    Total_amount_DesignFee, printtype, GangRun, pageContents,
    product_name, work_memo, upload_method, uploaded_files, ImgFolder,
    tape_enabled, tape_quantity, tape_price,
    created_at
) VALUES (?, 'envelope', ?, ?, '', ?, ?, ?, ?, 0, ?, '', 0, '',
    ?, ?, ?, ?, ?,
    ?, ?, ?,
    NOW())";

// 제품명 생성
$product_name = "봉투 - {$type_title} / {$section_title} / {$po_title} / {$MY_amount}매";

// 양면테이프 정보 추가
if ($tape_enabled) {
    $product_name .= " / 양면테이프 {$tape_quantity}매";
}

// 총 가격 계산 (양면테이프 포함)
$total_price = $price + $tape_price;
$total_vat_price = $vat_price + round($tape_price * 1.1);

// 파라미터 바인딩
$stmt = mysqli_prepare($db, $insertQuery);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($db));
    safe_json_response(false, null, '데이터베이스 준비 오류: ' . mysqli_error($db));
}

mysqli_stmt_bind_param($stmt, "ssssssdsdssssiis",
    $session_id, $MY_type, $Section, $POtype,
    $MY_amount, $ordertype, $total_price, $total_vat_price,
    $product_name, $work_memo, $upload_method, $uploaded_files_str, $img_folder,
    $tape_enabled, $tape_quantity, $tape_price
);

if (mysqli_stmt_execute($stmt)) {
    $insert_id = mysqli_insert_id($db);

    error_log("장바구니 추가 성공. ID: " . $insert_id);
    error_log("제품명: " . $product_name);
    error_log("총 가격: " . $total_price);

    $response_data = [
        'cart_id' => $insert_id,
        'product_name' => $product_name,
        'price' => $total_price,
        'vat_price' => $total_vat_price,
        'tape_enabled' => $tape_enabled,
        'tape_price' => $tape_price
    ];

    if ($action === 'add_to_basket_and_order') {
        // 바로 주문하기
        $response_data['redirect'] = '/shop/order.php';
    } else {
        // 장바구니로 이동
        $response_data['redirect'] = '/shop/basket.php';
    }

    safe_json_response(true, $response_data, '장바구니에 추가되었습니다.');
} else {
    error_log("Execute failed: " . mysqli_stmt_error($stmt));
    safe_json_response(false, null, '장바구니 추가 중 오류가 발생했습니다: ' . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);
mysqli_close($db);
?>