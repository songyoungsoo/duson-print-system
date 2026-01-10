<?php
/**
 * 견적서 상태 변경 API
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
$status = trim($input['status'] ?? '');

if ($quoteId <= 0) {
    echo json_encode(['success' => false, 'message' => '견적 ID가 필요합니다.']);
    exit;
}

if (empty($status)) {
    echo json_encode(['success' => false, 'message' => '상태값이 필요합니다.']);
    exit;
}

// 유효한 상태값 검증
$validStatuses = ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => '잘못된 상태값입니다.']);
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

    // 상태 변경 실행
    $result = $manager->updateStatus($quoteId, $status);

    if ($result) {
        $statusNames = [
            'draft' => '임시저장',
            'sent' => '발송완료',
            'viewed' => '열람',
            'accepted' => '수락',
            'rejected' => '거절',
            'expired' => '만료',
            'converted' => '주문전환'
        ];

        echo json_encode([
            'success' => true,
            'message' => '상태가 \'' . ($statusNames[$status] ?? $status) . '\'(으)로 변경되었습니다.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '상태 변경에 실패했습니다.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '오류: ' . $e->getMessage()
    ]);
}
