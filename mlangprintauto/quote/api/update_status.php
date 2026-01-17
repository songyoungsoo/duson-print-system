<?php
/**
 * 견적서 상태 변경 API
 * POST 요청으로 견적서 상태 업데이트
 *
 * ✅ 2026-01-17: 보안 강화 - 인증/CSRF 체크 추가
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/security.php';

// ✅ 보안 체크: 관리자 인증 + CSRF 토큰
apiSecurityCheck(true);

function jsonResponse($success, $message = '', $data = [], $internalError = null) {
    if ($internalError) {
        error_log("[Quote API Error] " . $internalError);
    }
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // POST 데이터 수집
    $quoteId = intval($_POST['quote_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');

    // 필수 입력 검증
    if (!$quoteId) {
        jsonResponse(false, '견적서 ID가 필요합니다.');
    }

    if (!$newStatus) {
        jsonResponse(false, '변경할 상태가 필요합니다.');
    }

    // 유효한 상태 값 확인
    $validStatuses = ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted'];
    if (!in_array($newStatus, $validStatuses)) {
        jsonResponse(false, '유효하지 않은 상태입니다.');
    }

    // 견적서 존재 확인
    $checkQuery = "SELECT id, quote_no, status FROM quotes WHERE id = ?";
    $checkStmt = mysqli_prepare($db, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "i", $quoteId);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    $quote = mysqli_fetch_assoc($result);
    mysqli_stmt_close($checkStmt);

    if (!$quote) {
        jsonResponse(false, '견적서를 찾을 수 없습니다.');
    }

    // 이미 같은 상태인 경우
    if ($quote['status'] === $newStatus) {
        jsonResponse(false, '이미 해당 상태입니다.');
    }

    // 상태 업데이트
    $updateQuery = "UPDATE quotes SET status = ? WHERE id = ?";
    $updateStmt = mysqli_prepare($db, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "si", $newStatus, $quoteId);

    if (mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);

        $statusLabels = [
            'draft' => '초안',
            'sent' => '발송됨',
            'viewed' => '조회됨',
            'accepted' => '승인됨',
            'rejected' => '거절됨',
            'expired' => '만료됨',
            'converted' => '전환됨'
        ];

        jsonResponse(true, '견적서 상태가 "' . $statusLabels[$newStatus] . '"(으)로 변경되었습니다.', [
            'quote_id' => $quoteId,
            'quote_no' => $quote['quote_no'],
            'old_status' => $quote['status'],
            'new_status' => $newStatus
        ]);
    } else {
        mysqli_stmt_close($updateStmt);
        jsonResponse(false, '상태 변경에 실패했습니다.', [], mysqli_error($db));
    }

} catch (Exception $e) {
    jsonResponse(false, '오류: ' . $e->getMessage());
}

mysqli_close($db);
?>
