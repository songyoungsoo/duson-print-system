<?php
/**
 * 로젠택배 설정값 저장 API
 * PHP 5.2 호환 버전 (dsp114.com용)
 * 박스수량, 택배비, 운임구분을 DB에 저장
 */

// 출력 버퍼링으로 lib.php의 HTML 출력 캡처
ob_start();
include "lib.php";
$captured = ob_get_clean();

// lib.php 인증 실패 시 <script> 출력됨 (alert)
// 주의: <style>은 인증 성공해도 항상 출력되므로 체크하지 않음
if (strpos($captured, '<script>') !== false) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('success' => false, 'error' => 'Login required'));
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

$connect = dbconn();
if (!$connect) {
    echo json_encode(array('success' => false, 'error' => 'DB connection failed'));
    exit;
}

// POST 데이터 받기
$order_no = isset($_POST['order_no']) ? intval($_POST['order_no']) : 0;
$field = isset($_POST['field']) ? trim($_POST['field']) : '';
$value = isset($_POST['value']) ? trim($_POST['value']) : '';

// 유효성 검사
if ($order_no <= 0) {
    echo json_encode(array('success' => false, 'error' => 'Invalid order number'));
    exit;
}

// 허용된 필드만
$allowed_fields = array('box_qty', 'delivery_fee', 'fee_type');
if (!in_array($field, $allowed_fields)) {
    echo json_encode(array('success' => false, 'error' => 'Invalid field'));
    exit;
}

// 필드명 매핑 (폼 필드 -> DB 컬럼)
$field_map = array(
    'box_qty' => 'logen_box_qty',
    'delivery_fee' => 'logen_delivery_fee',
    'fee_type' => 'logen_fee_type'
);
$db_field = $field_map[$field];

// 값 처리
if ($field === 'box_qty' || $field === 'delivery_fee') {
    $value = intval($value);
    if ($value < 0) $value = 0;
} else {
    // fee_type (EUC-KR 문자열)
    $allowed_types = array('착불', '신용', '퀵');
    if (!in_array($value, $allowed_types)) {
        $value = '착불';
    }
    $value = mysql_real_escape_string($value);
}

// DB 업데이트
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
    echo json_encode(array(
        'success' => false,
        'error' => 'DB update failed: ' . mysql_error()
    ));
}
