<?php
/**
 * 견적서 삭제 API
 */

header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => '견적서 ID가 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 견적서 존재 여부 확인
$checkQuery = "SELECT id, quote_no, status FROM quotes WHERE id = ?";
$stmt = mysqli_prepare($db, $checkQuery);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quote = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$quote) {
    echo json_encode(['success' => false, 'message' => '견적서를 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 주문으로 전환된 견적서는 삭제 불가
if ($quote['status'] === 'converted') {
    echo json_encode(['success' => false, 'message' => '주문으로 전환된 견적서는 삭제할 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 삭제 실행 (quote_items, quote_emails는 CASCADE로 자동 삭제)
$deleteQuery = "DELETE FROM quotes WHERE id = ?";
$stmt = mysqli_prepare($db, $deleteQuery);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => '견적서가 삭제되었습니다.',
        'deleted_quote_no' => $quote['quote_no']
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => '삭제 중 오류가 발생했습니다: ' . mysqli_error($db)], JSON_UNESCAPED_UNICODE);
}

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
