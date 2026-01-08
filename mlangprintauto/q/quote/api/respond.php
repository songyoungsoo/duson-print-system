<?php
/**
 * 고객 견적서 응답 API
 * 승인(accept) 또는 거절(reject) 처리
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

function jsonResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// DB 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once __DIR__ . '/../includes/QuoteManager.php';

if (!$db) {
    jsonResponse(false, '데이터베이스 연결 실패');
}

// POST 데이터 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, '잘못된 요청 방식입니다.');
}

$token = trim($_POST['token'] ?? '');
$action = trim($_POST['action'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (empty($token)) {
    jsonResponse(false, '토큰이 필요합니다.');
}

if (!in_array($action, ['accept', 'reject'])) {
    jsonResponse(false, '유효하지 않은 액션입니다.');
}

$manager = new QuoteManager($db);

// 토큰으로 견적서 조회
$quote = $manager->getByToken($token);

if (!$quote) {
    jsonResponse(false, '견적서를 찾을 수 없습니다.');
}

// 이미 처리된 견적서 체크
if (in_array($quote['status'], ['accepted', 'rejected', 'converted'])) {
    $statusLabels = [
        'accepted' => '승인됨',
        'rejected' => '거절됨',
        'converted' => '주문전환됨'
    ];
    jsonResponse(false, "이 견적서는 이미 {$statusLabels[$quote['status']]} 상태입니다.");
}

// 만료 체크
if (strtotime($quote['valid_until']) < time()) {
    jsonResponse(false, '이 견적서는 만료되었습니다.');
}

// 상태 업데이트
$newStatus = ($action === 'accept') ? 'accepted' : 'rejected';

try {
    // 응답 정보와 함께 상태 업데이트
    $updateQuery = "UPDATE quotes SET
        status = ?,
        customer_notes = ?,
        responded_at = NOW(),
        updated_at = NOW()
    WHERE id = ?";

    $stmt = mysqli_prepare($db, $updateQuery);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
    }
    mysqli_stmt_bind_param($stmt, "ssi", $newStatus, $notes, $quote['id']);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('상태 업데이트 실패: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    // 성공 메시지
    $successMessages = [
        'accept' => '견적서가 승인되었습니다. 빠른 시일 내에 연락드리겠습니다.',
        'reject' => '견적서가 거절되었습니다. 다른 문의사항이 있으시면 연락주세요.'
    ];

    jsonResponse(true, $successMessages[$action], [
        'quote_no' => $quote['quote_no'],
        'status' => $newStatus
    ]);

} catch (Exception $e) {
    error_log("Quote respond error: " . $e->getMessage());
    jsonResponse(false, '처리 중 오류가 발생했습니다: ' . $e->getMessage());
}
?>
