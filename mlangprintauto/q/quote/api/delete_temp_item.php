<?php
/**
 * 임시 테이블 항목 삭제 API
 * quotation_temp 또는 shop_temp에서 특정 항목 삭제
 *
 * POST 파라미터:
 * - table: 테이블명 (quotation_temp 또는 shop_temp)
 * - id: 삭제할 레코드 ID
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/safe_json_response.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// DB 연결 확인
if (!isset($db) || !$db) {
    safe_json_response(false, null, 'DB 연결 실패');
}
mysqli_set_charset($db, "utf8mb4");

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_json_response(false, null, 'POST 요청만 허용됩니다.');
}

// 파라미터 검증
$table = $_POST['table'] ?? '';
$id = intval($_POST['id'] ?? 0);

// 허용된 테이블만 처리
$allowedTables = ['quotation_temp', 'shop_temp'];
if (!in_array($table, $allowedTables)) {
    safe_json_response(false, null, '허용되지 않은 테이블입니다.');
}

if ($id <= 0) {
    safe_json_response(false, null, '유효하지 않은 ID입니다.');
}

// 세션 기반 권한 확인 (해당 세션의 항목만 삭제 가능)
$sessionId = session_id();

// 먼저 해당 항목이 현재 세션의 것인지 확인
$checkQuery = "SELECT no FROM `$table` WHERE no = ? AND session_id = ?";
$checkStmt = mysqli_prepare($db, $checkQuery);
mysqli_stmt_bind_param($checkStmt, "is", $id, $sessionId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($checkResult) === 0) {
    mysqli_stmt_close($checkStmt);
    safe_json_response(false, null, '삭제 권한이 없거나 항목이 존재하지 않습니다.');
}
mysqli_stmt_close($checkStmt);

// 삭제 실행
$deleteQuery = "DELETE FROM `$table` WHERE no = ? AND session_id = ?";
$deleteStmt = mysqli_prepare($db, $deleteQuery);
mysqli_stmt_bind_param($deleteStmt, "is", $id, $sessionId);

if (mysqli_stmt_execute($deleteStmt)) {
    $affectedRows = mysqli_stmt_affected_rows($deleteStmt);
    mysqli_stmt_close($deleteStmt);

    if ($affectedRows > 0) {
        safe_json_response(true, ['deleted_id' => $id], '항목이 삭제되었습니다.');
    } else {
        safe_json_response(false, null, '삭제할 항목이 없습니다.');
    }
} else {
    $error = mysqli_stmt_error($deleteStmt);
    mysqli_stmt_close($deleteStmt);
    safe_json_response(false, null, '삭제 실패: ' . $error);
}
