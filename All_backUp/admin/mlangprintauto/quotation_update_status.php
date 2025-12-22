<?php
/**
 * 견적서 상태 변경 API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

include "../../db.php";
mysqli_set_charset($db, 'utf8mb4');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$id = intval($data['id'] ?? 0);
$status = trim($data['status'] ?? '');

$valid_statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired'];

if ($id <= 0 || !in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = mysqli_prepare($db, "UPDATE quotations SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => '상태가 변경되었습니다.'], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => '변경 실패: ' . mysqli_error($db)], JSON_UNESCAPED_UNICODE);
}

mysqli_stmt_close($stmt);
?>
