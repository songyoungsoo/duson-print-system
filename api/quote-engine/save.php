<?php
/**
 * 견적엔진 API — 견적서 저장/수정
 * POST /api/quote-engine/save.php
 *
 * 요청 (JSON):
 *   id=null → 신규, id=숫자 → 수정
 *   doc_type, customer_*, valid_days, payment_terms, discount_*, is_draft, items[]
 *
 * 응답:
 *   { success: true, id: 1, quote_no: "QE-20260311-001" }
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
require_once __DIR__ . '/../../includes/quote-engine/CustomerManager.php';

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


    $items = $input['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '최소 1개 이상의 품목이 필요합니다']);
        exit;
    }


    $isDraft = !empty($input['is_draft']);
    $status  = $isDraft ? 'draft' : 'completed';


    $supplyTotal = 0;
    foreach ($items as $item) {
        $supplyTotal += (int)($item['supply_price'] ?? 0);
    }
    $vatTotal    = (int)round($supplyTotal * 0.1);
    $discountAmt = (int)($input['discount_amount'] ?? 0);
    $grandTotal  = $supplyTotal + $vatTotal - $discountAmt;

    $quoteData = [
        'doc_type'          => $input['doc_type'] ?? 'quotation',
        'customer_id'       => $input['customer_id'] ?? null,
        'customer_company'  => $input['customer_company'] ?? null,
        'customer_name'     => $input['customer_name'] ?? null,
        'customer_phone'    => $input['customer_phone'] ?? null,
        'customer_email'    => $input['customer_email'] ?? null,
        'customer_address'  => $input['customer_address'] ?? null,
        'customer_biz_no'   => $input['customer_biz_no'] ?? null,
        'supply_total'      => $supplyTotal,
        'vat_total'         => $vatTotal,
        'discount_amount'   => $discountAmt,
        'discount_reason'   => $input['discount_reason'] ?? null,
        'grand_total'       => $grandTotal,
        'valid_days'        => (int)($input['valid_days'] ?? 7),
        'payment_terms'     => $input['payment_terms'] ?? '발행일로부터 7일',
        'customer_memo'     => $input['customer_memo'] ?? null,
        'admin_memo'        => $input['admin_memo'] ?? null,
        'status'            => $status,
    ];

    $engine = new QE_QuoteEngine($db);
    $quoteId = !empty($input['id']) ? (int)$input['id'] : null;


    if ($quoteId) {
        $result = $engine->updateQuote($quoteId, $quoteData, $items);
    } else {
        $result = $engine->saveQuote($quoteData, $items);
    }

    if (!$result['success']) {
        http_response_code(400);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }


    if (!empty($input['customer_id'])) {
        $customerManager = new QE_CustomerManager($db);
        $customerManager->markUsed((int)$input['customer_id']);
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
