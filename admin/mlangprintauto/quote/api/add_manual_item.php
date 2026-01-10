<?php
/**
 * 수동 품목 추가 API
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// 세션 시작 및 인증 확인
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// DB 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once dirname(__DIR__) . '/includes/AdminQuoteManager.php';

// JSON 입력 파싱
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 필수 필드 검증
if (empty($input['product_name'])) {
    echo json_encode(['success' => false, 'message' => '품목명을 입력해주세요.']);
    exit;
}

$supplyPrice = intval($input['supply_price'] ?? 0);
if ($supplyPrice <= 0) {
    echo json_encode(['success' => false, 'message' => '공급가액을 입력해주세요.']);
    exit;
}

try {
    $manager = new AdminQuoteManager($db);

    // 세션 ID를 기반으로 임시 품목 저장
    $sessionId = session_id();

    $itemData = [
        'is_manual' => true,
        'product_name' => trim($input['product_name']),
        'specification' => trim($input['specification'] ?? ''),
        'quantity' => floatval($input['quantity'] ?? 1),
        'unit' => trim($input['unit'] ?? '개'),
        'supply_price' => $supplyPrice
    ];

    $itemNo = $manager->addTempItem($sessionId, $itemData);

    echo json_encode([
        'success' => true,
        'item_no' => $itemNo,
        'message' => '품목이 추가되었습니다.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '오류: ' . $e->getMessage()
    ]);
}
