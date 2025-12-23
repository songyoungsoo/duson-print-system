<?php
/**
 * 공통 장바구니 추가 처리
 * 모든 품목에서 사용하는 통합 장바구니 추가 로직
 */

session_start();
$session_id = session_id();

header('Content-Type: application/json; charset=utf-8');
ob_start();

// 공통 헬퍼 포함
include_once __DIR__ . '/upload_path_helper.php';

// 데이터베이스 연결
include __DIR__ . '/../db.php';
$connect = $db;

if (!$connect) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8mb4");

// POST 데이터 받기
$product_type = $_POST['product_type'] ?? '';
$action = $_POST['action'] ?? '';

if ($action !== 'add_to_basket') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

if (empty($product_type)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => '품목 타입이 지정되지 않았습니다.']);
    exit;
}

// 품목별 필수 필드 정의
$required_fields = [
    'leaflet' => ['MY_type', 'PN_type', 'MY_Fsd', 'MY_amount', 'POtype', 'ordertype'],
    'namecard' => ['paper_type', 'size', 'quantity', 'print_side'],
    'envelope' => ['envelope_type', 'size', 'quantity'],
    'sticker' => ['material', 'width', 'height', 'quantity'],
    'cadarok' => ['paper_type', 'size', 'quantity'],
    'merchandisebond' => ['bond_type', 'quantity']
];

// 파일 업로드 처리
$upload_result = getUploadPath($product_type, $session_id);
$upload_folder = $upload_result['full_path'];
$db_upload_path = $upload_result['db_path'];

$upload_count = 0;

if (!empty($_FILES['uploaded_files']['name'][0])) {
    // 폴더 생성
    if (!file_exists($upload_folder)) {
        if (!mkdir($upload_folder, 0755, true)) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => '업로드 폴더 생성 실패']);
            exit;
        }
    }
    
    // 파일 업로드
    for ($i = 0; $i < count($_FILES['uploaded_files']['name']); $i++) {
        if ($_FILES['uploaded_files']['error'][$i] === UPLOAD_ERR_OK) {
            $temp_name = $_FILES['uploaded_files']['tmp_name'][$i];
            $original_name = $_FILES['uploaded_files']['name'][$i];
            $file_size = $_FILES['uploaded_files']['size'][$i];
            
            // 파일명 안전하게 처리
            $safe_filename = preg_replace('/[^a-zA-Z0-9._-가-힣]/', '_', $original_name);
            $target_file = $upload_folder . $safe_filename;
            
            // 파일 크기 체크 (15MB)
            if ($file_size > 15 * 1024 * 1024) {
                continue;
            }
            
            if (move_uploaded_file($temp_name, $target_file)) {
                $upload_count++;
            }
        }
    }
}

// shop_temp 테이블에 저장
$price = intval($_POST['calculated_price'] ?? 0);
$vat_price = intval($_POST['calculated_vat_price'] ?? 0);
$work_memo = $_POST['work_memo'] ?? '';
$upload_method = $_POST['upload_method'] ?? 'upload';

// 품목별 데이터를 JSON으로 저장
$product_data = [];
foreach ($_POST as $key => $value) {
    if (!in_array($key, ['action', 'calculated_price', 'calculated_vat_price', 'work_memo', 'upload_method', 'product_type'])) {
        $product_data[$key] = $value;
    }
}
$product_data_json = json_encode($product_data, JSON_UNESCAPED_UNICODE);

// INSERT
$query = "INSERT INTO shop_temp (
    session_id, product_type, product_data, st_price, st_price_vat,
    work_memo, upload_method, upload_folder, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "sssddss",
    $session_id, $product_type, $product_data_json, $price, $vat_price,
    $work_memo, $upload_method, $db_upload_path
);

if (mysqli_stmt_execute($stmt)) {
    $basket_id = mysqli_insert_id($connect);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => '장바구니에 추가되었습니다.' . ($upload_count > 0 ? " (파일 {$upload_count}개 업로드 완료)" : ''),
        'basket_id' => $basket_id,
        'upload_count' => $upload_count
    ]);
} else {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => '장바구니 추가 실패: ' . mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($connect);
?>
