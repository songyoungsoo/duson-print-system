<?php
require_once __DIR__ . '/base.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$jsonBody = null;
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawBody = file_get_contents('php://input');
    $jsonBody = json_decode($rawBody, true);
    if ($jsonBody && isset($jsonBody['action'])) {
        $action = $jsonBody['action'];
    }
}

switch ($action) {
    case 'delete':
        $id = intval($_POST['id'] ?? ($jsonBody['id'] ?? 0));

        if ($id <= 0) {
            jsonResponse(false, '유효하지 않은 견적 ID입니다.');
        }

        $check = mysqli_prepare($db, "SELECT id, quote_no, status FROM admin_quotes WHERE id = ?");
        mysqli_stmt_bind_param($check, "i", $id);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        $quote = mysqli_fetch_assoc($result);

        if (!$quote) {
            jsonResponse(false, '견적서를 찾을 수 없습니다.');
        }

        $del_items = mysqli_prepare($db, "DELETE FROM admin_quote_items WHERE quote_id = ?");
        mysqli_stmt_bind_param($del_items, "i", $id);
        mysqli_stmt_execute($del_items);

        $del_quote = mysqli_prepare($db, "DELETE FROM admin_quotes WHERE id = ?");
        mysqli_stmt_bind_param($del_quote, "i", $id);

        if (mysqli_stmt_execute($del_quote)) {
            jsonResponse(true, "견적 '{$quote['quote_no']}' 삭제 완료");
        } else {
            jsonResponse(false, '삭제 실패: ' . mysqli_error($db));
        }
        break;

    case 'bulk_delete':
        $ids = $jsonBody['ids'] ?? [];
        if (!is_array($ids) || count($ids) === 0) {
            jsonResponse(false, '삭제할 견적을 선택해주세요.');
        }

        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function($n) { return $n > 0; });
        if (count($ids) === 0) {
            jsonResponse(false, '유효한 견적 ID가 없습니다.');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types_str = str_repeat('i', count($ids));

        $stmt = mysqli_prepare($db, "DELETE FROM admin_quote_items WHERE quote_id IN ($placeholders)");
        mysqli_stmt_bind_param($stmt, $types_str, ...$ids);
        mysqli_stmt_execute($stmt);

        $stmt = mysqli_prepare($db, "DELETE FROM admin_quotes WHERE id IN ($placeholders)");
        mysqli_stmt_bind_param($stmt, $types_str, ...$ids);

        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            jsonResponse(true, $affected . '건 삭제 완료', ['deleted' => $affected]);
        } else {
            jsonResponse(false, '일괄 삭제 실패: ' . mysqli_error($db));
        }
        break;

    default:
        jsonResponse(false, '알 수 없는 action: ' . $action);
}
