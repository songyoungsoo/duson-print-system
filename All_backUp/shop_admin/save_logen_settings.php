<?php
/**
 * 로젠택배 설정값 저장 API
 * 박스수량, 택배비, 운임구분을 DB에 저장
 */
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../db.php';

// POST 데이터 받기
$order_no = isset($_POST['order_no']) ? intval($_POST['order_no']) : 0;
$field = isset($_POST['field']) ? trim($_POST['field']) : '';
$value = isset($_POST['value']) ? trim($_POST['value']) : '';

// 유효성 검사
if ($order_no <= 0) {
    echo json_encode(['success' => false, 'error' => '잘못된 주문번호']);
    exit;
}

// 허용된 필드만 업데이트
$allowed_fields = [
    'box_qty' => 'logen_box_qty',
    'delivery_fee' => 'logen_delivery_fee',
    'fee_type' => 'logen_fee_type'
];

if (!isset($allowed_fields[$field])) {
    echo json_encode(['success' => false, 'error' => '잘못된 필드명']);
    exit;
}

$db_field = $allowed_fields[$field];

// 값 처리
if ($field === 'box_qty') {
    $value = intval($value);
    if ($value < 1) $value = 1;
    if ($value > 99) $value = 99;
} elseif ($field === 'delivery_fee') {
    $value = intval($value);
    if ($value < 0) $value = 0;
} elseif ($field === 'fee_type') {
    $allowed_types = ['착불', '신용', '퀵'];
    if (!in_array($value, $allowed_types)) {
        $value = '착불';
    }
}

// DB 업데이트
$query = "UPDATE mlangorder_printauto SET $db_field = ? WHERE no = ?";
$stmt = mysqli_prepare($db, $query);

if ($field === 'fee_type') {
    mysqli_stmt_bind_param($stmt, "si", $value, $order_no);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $value, $order_no);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'order_no' => $order_no,
        'field' => $field,
        'value' => $value
    ]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
}

mysqli_stmt_close($stmt);
