<?php
/**
 * 로젠택배 설정값 저장 API
 * PHP 5.2 호환 버전 (dsp114.com용)
 * 박스수량, 택배비, 운임구분을 DB에 저장
 */
header('Content-Type: application/json; charset=EUC-KR');

include "lib.php";
$connect = dbconn();  // DB 연결 필수!

// POST 데이터 받기
$order_no = isset($_POST['order_no']) ? intval($_POST['order_no']) : 0;
$field = isset($_POST['field']) ? trim($_POST['field']) : '';
$value = isset($_POST['value']) ? trim($_POST['value']) : '';

// 유효성 검사
if ($order_no <= 0) {
    echo json_encode(array('success' => false, 'error' => '잘못된 주문번호'));
    exit;
}

// 허용된 필드만 업데이트
$allowed_fields = array(
    'box_qty' => 'logen_box_qty',
    'delivery_fee' => 'logen_delivery_fee',
    'fee_type' => 'logen_fee_type'
);

if (!isset($allowed_fields[$field])) {
    echo json_encode(array('success' => false, 'error' => '잘못된 필드명'));
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
    $allowed_types = array('착불', '신용', '퀵');
    if (!in_array($value, $allowed_types)) {
        $value = '착불';
    }
    // 문자열은 이스케이프 처리
    $value = mysql_real_escape_string($value);
}

// DB 업데이트 (PHP 5.2용 mysql_query)
if ($field === 'fee_type') {
    $query = "UPDATE MlangOrder_PrintAuto SET $db_field = '$value' WHERE no = $order_no";
} else {
    $query = "UPDATE MlangOrder_PrintAuto SET $db_field = $value WHERE no = $order_no";
}

$result = mysql_query($query);

if ($result) {
    echo json_encode(array(
        'success' => true,
        'order_no' => $order_no,
        'field' => $field,
        'value' => $value
    ));
} else {
    echo json_encode(array('success' => false, 'error' => mysql_error()));
}
