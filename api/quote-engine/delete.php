<?php
/**
 * 견적엔진 API — 견적서 삭제
 * POST /api/quote-engine/delete.php
 *
 * 요청 (JSON):
 *   단건: { "id": 1 }
 *   다건: { "ids": [1, 2, 3] }
 *
 * 응답:
 *   { success: true }
 *   다건: { success: true, deleted: 3, failed: 0, errors: [] }
 */

header('Content-Type: application/json; charset=utf-8');
session_start();


if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../db.php';
mysqli_set_charset($db, 'utf8mb4');
require_once __DIR__ . '/../../includes/quote-engine/QuoteEngine.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'POST만 허용됩니다']);
        exit;
    }


    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '잘못된 JSON 형식입니다']);
        exit;
    }

    $engine = new QE_QuoteEngine($db);


    if (!empty($input['ids']) && is_array($input['ids'])) {
        $deleted = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($input['ids'] as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                $failed++;
                $errors[] = "잘못된 ID: {$id}";
                continue;
            }

            $result = $engine->deleteQuote($id);
            if ($result['success']) {
                $deleted++;
            } else {
                $failed++;
                $errors[] = "ID {$id}: " . ($result['error'] ?? '삭제 실패');
            }
        }

        echo json_encode([
            'success' => ($deleted > 0),
            'deleted' => $deleted,
            'failed'  => $failed,
            'errors'  => $errors,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id 또는 ids가 필요합니다']);
        exit;
    }

    $result = $engine->deleteQuote($id);

    if (!$result['success']) {
        http_response_code(400);
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
