<?php
/**
 * 견적엔진 API — 상태 변경 / 견적→거래명세서 변환
 * POST /api/quote-engine/status.php
 *
 * 상태 변경:
 *   POST Body: { "id": 1, "status": "sent" }
 *   → { success: true }
 *
 * 견적서 → 거래명세서 변환:
 *   POST ?action=convert  Body: { "id": 1 }
 *   → { success: true, new_id: 2, new_quote_no: "TX-20260311-001" }
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

    $action = trim($_GET['action'] ?? $input['action'] ?? '');
    $engine = new QE_QuoteEngine($db);

    // ════════════════════════════════════════════════════
    //  견적서 → 거래명세서 변환
    // ════════════════════════════════════════════════════
    if ($action === 'convert') {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'id가 필요합니다']);
            exit;
        }

        $result = $engine->convertToTransaction($id);

        if (!$result['success']) {
            http_response_code(400);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        }


        echo json_encode([
            'success'      => true,
            'new_id'       => $result['id'],
            'new_quote_no' => $result['quote_no'],
            'source_id'    => $result['source_quote_id'] ?? $id,
            'source_no'    => $result['source_quote_no'] ?? null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ════════════════════════════════════════════════════
    //  상태 변경 (기본 동작)
    // ════════════════════════════════════════════════════
    $id     = (int)($input['id'] ?? 0);
    $status = trim($input['status'] ?? '');

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id가 필요합니다']);
        exit;
    }

    if ($status === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'status가 필요합니다']);
        exit;
    }


    $allowed = ['draft', 'completed', 'sent', 'expired'];
    if (!in_array($status, $allowed, true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => '허용되지 않는 상태값입니다. 가능: ' . implode(', ', $allowed),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $ok = $engine->updateStatus($id, $status);

    if (!$ok) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '상태 변경 실패 (ID를 확인하세요)']);
        exit;
    }

    echo json_encode(['success' => true, 'id' => $id, 'status' => $status], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
