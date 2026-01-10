<?php
/**
 * 견적서 삭제 API
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
$quoteId = intval($input['quote_id'] ?? 0);
if ($quoteId <= 0) {
    echo json_encode(['success' => false, 'message' => '견적 ID가 필요합니다.']);
    exit;
}

try {
    $manager = new AdminQuoteManager($db);

    // 견적 존재 확인
    $quote = $manager->getQuote($quoteId);
    if (!$quote) {
        echo json_encode(['success' => false, 'message' => '견적을 찾을 수 없습니다.']);
        exit;
    }

    // 삭제 실행 (CASCADE로 품목도 자동 삭제)
    $result = $manager->deleteQuote($quoteId);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => '견적이 삭제되었습니다.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '견적 삭제에 실패했습니다.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '오류: ' . $e->getMessage()
    ]);
}
