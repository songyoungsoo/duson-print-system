<?php
/**
 * 로젠택배 자동 배송 접수
 *
 * 기능:
 * - 선택한 주문들을 로젠 API로 자동 접수
 * - 송장번호 즉시 발급 및 DB 저장
 * - mlangorder_printauto 테이블에 송장번호 업데이트
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/logen_api_handler.php';
require_once __DIR__ . '/delivery_calculator.php';
require_once __DIR__ . '/delivery_rules_config.php';

// POST JSON 데이터 받기
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_nos']) || !is_array($input['order_nos'])) {
    echo json_encode([
        'success' => false,
        'message' => '주문 번호가 전달되지 않았습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$orderNos = array_map('intval', $input['order_nos']);
$deliveryRules = require __DIR__ . '/delivery_rules_config.php';

// API 핸들러 초기화
$api = new LogenAPIHandler();

$results = [
    'success' => true,
    'registered' => 0,
    'failed' => 0,
    'details' => []
];

foreach ($orderNos as $orderNo) {
    // 주문 정보 조회
    $query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $orderNo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);

    if (!$order) {
        $results['failed']++;
        $results['details'][] = [
            'order_no' => $orderNo,
            'success' => false,
            'message' => '주문 정보를 찾을 수 없습니다.'
        ];
        continue;
    }

    // 택배비 및 박스 수량 자동 계산
    $deliveryInfo = getDeliveryInfo($order, $deliveryRules);

    // 우편번호 정리 (5자리)
    $zipcode = preg_replace('/[^0-9]/', '', $order['zip'] ?? '');
    if (strlen($zipcode) !== 5) {
        $zipcode = '00000'; // 기본값
    }

    // 주소 합치기
    $fullAddress = trim(($order['zip1'] ?? '') . ' ' . ($order['zip2'] ?? ''));

    // Type_1 JSON 파싱 (제품명)
    $productName = $order['Type_1'] ?? '';
    if (!empty($productName) && substr(trim($productName), 0, 1) === '{') {
        $json = json_decode($productName, true);
        if ($json && isset($json['formatted_display'])) {
            $productName = $json['formatted_display'];
        }
    }

    // API 요청 데이터 구성
    $orderData = [
        'order_no' => $orderNo,
        'name' => $order['name'] ?? '',
        'phone' => $order['phone'] ?? '',
        'mobile' => $order['Hendphone'] ?? '',
        'zipcode' => $zipcode,
        'address' => $fullAddress,
        'product_name' => $productName,
        'box_count' => $deliveryInfo['box'],
        'delivery_fee' => $deliveryInfo['price'],
        'payment_type' => '착불', // 기본값: 착불 (필요시 변경 가능)
        'remark' => $order['Type'] ?? ''
    ];

    // 로젠 API 호출 - 배송 접수
    $apiResult = $api->registerShipment($orderData);

    if ($apiResult['success']) {
        // DB에 배송 정보 저장
        $api->saveShipmentToDB($orderNo, $apiResult['invoice_no'], $apiResult);

        // mlangorder_printauto 테이블에 송장번호 업데이트
        $updateQuery = "UPDATE mlangorder_printauto
                        SET logen_tracking_no = ?,
                            waybill_date = NOW(),
                            delivery_company = '로젠택배'
                        WHERE no = ?";
        $updateStmt = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'si', $apiResult['invoice_no'], $orderNo);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);

        $results['registered']++;
        $results['details'][] = [
            'order_no' => $orderNo,
            'success' => true,
            'invoice_no' => $apiResult['invoice_no'],
            'message' => '배송 접수 성공'
        ];
    } else {
        $results['failed']++;
        $results['details'][] = [
            'order_no' => $orderNo,
            'success' => false,
            'message' => $apiResult['message']
        ];
    }
}

// 실패한 건이 있으면 전체 success = false
if ($results['failed'] > 0) {
    $results['success'] = false;
    $results['message'] = sprintf(
        '총 %d건 중 %d건 성공, %d건 실패',
        count($orderNos),
        $results['registered'],
        $results['failed']
    );
} else {
    $results['message'] = sprintf(
        '총 %d건 배송 접수 완료',
        $results['registered']
    );
}

echo json_encode($results, JSON_UNESCAPED_UNICODE);
