<?php
/**
 * 견적서 저장 API
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
if (empty($input['customer_name'])) {
    echo json_encode(['success' => false, 'message' => '담당자명을 입력해주세요.']);
    exit;
}

if (empty($input['items']) || !is_array($input['items'])) {
    echo json_encode(['success' => false, 'message' => '최소 1개 이상의 품목이 필요합니다.']);
    exit;
}

try {
    $manager = new AdminQuoteManager($db);

    // 견적 기본 정보
    $quoteData = [
        'quote_no' => $input['quote_no'] ?? '',
        'customer_company' => trim($input['customer_company'] ?? ''),
        'customer_name' => trim($input['customer_name']),
        'customer_phone' => trim($input['customer_phone'] ?? ''),
        'customer_email' => trim($input['customer_email'] ?? ''),
        'customer_address' => trim($input['customer_address'] ?? ''),
        'customer_memo' => trim($input['customer_memo'] ?? ''),
        'admin_memo' => trim($input['admin_memo'] ?? ''),
        'created_by' => $_SESSION['admin_id'] ?? $_SESSION['admin_name'] ?? 'admin'
    ];

    // 품목 배열 정규화
    $items = [];
    foreach ($input['items'] as $item) {
        $items[] = [
            'source_type' => $item['source_type'] ?? 'manual',
            'product_type' => $item['product_type'] ?? '',
            'product_name' => trim($item['product_name'] ?? ''),
            'specification' => trim($item['specification'] ?? ''),
            'quantity' => floatval($item['quantity'] ?? 1),
            'unit' => $item['unit'] ?? '개',
            'quantity_display' => $item['quantity_display'] ?? '',
            'unit_price' => floatval($item['unit_price'] ?? 0),
            'supply_price' => intval($item['supply_price'] ?? 0),
            'source_data' => $item['source_data'] ?? null,
            'notes' => $item['notes'] ?? ''
        ];
    }

    // 임시저장 여부
    $isDraft = !empty($input['is_draft']);

    // 견적 저장
    $quoteId = $manager->saveQuote($quoteData, $items, $isDraft);

    // 임시 품목 정리 (저장 성공 시)
    $sessionId = session_id();
    $manager->clearTempItems($sessionId);

    echo json_encode([
        'success' => true,
        'quote_id' => $quoteId,
        'message' => $isDraft ? '임시저장되었습니다.' : '저장되었습니다.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '저장 오류: ' . $e->getMessage()
    ]);
}
