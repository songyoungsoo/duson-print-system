<?php
/**
 * 견적서 수정 API (draft 상태만)
 * PUT 요청으로 견적서 UPDATE
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/QuoteManager.php';

function jsonResponse($success, $data = [], $message = '') {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * FormData 배열 파싱 함수 (Fallback용)
 * items[0][product_name] 형식을 배열로 변환
 */
function parseItemsFromPost($post) {
    $items = [];

    foreach ($post as $key => $value) {
        // items[0][product_name] 패턴 매칭
        if (preg_match('/^items\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
            $index = intval($matches[1]);
            $field = $matches[2];

            if (!isset($items[$index])) {
                $items[$index] = [];
            }

            $items[$index][$field] = $value;
        }
    }

    // 인덱스 순서 정렬 후 재인덱싱
    ksort($items);
    return array_values($items);
}

try {
    $manager = new QuoteManager($db);

    // 견적서 ID
    $quoteId = intval($_POST['quote_id'] ?? 0);
    if (!$quoteId) {
        jsonResponse(false, [], '견적서 ID가 필요합니다.');
    }

    // 견적서 조회
    $quote = $manager->getQuoteById($quoteId);
    if (!$quote) {
        jsonResponse(false, [], '견적서를 찾을 수 없습니다.');
    }

    // draft 상태만 수정 가능
    if ($quote['status'] !== 'draft') {
        jsonResponse(false, [], '이미 발송된 견적서는 수정할 수 없습니다.');
    }

    // POST 데이터 수집
    $data = [
        'quote_id' => $quoteId,
        'quote_type' => $_POST['quote_type'] ?? 'quotation',
        'customer_name' => trim($_POST['customer_name'] ?? ''),
        'customer_company' => trim($_POST['customer_company'] ?? ''),
        'customer_phone' => trim($_POST['customer_phone'] ?? ''),
        'customer_email' => trim($_POST['customer_email'] ?? ''),
        'recipient_email' => trim($_POST['recipient_email'] ?? $_POST['customer_email'] ?? ''),
        'delivery_type' => trim($_POST['delivery_type'] ?? ''),
        'delivery_address' => trim($_POST['delivery_address'] ?? ''),
        'delivery_price' => intval($_POST['delivery_price'] ?? 0),
        'delivery_vat' => intval($_POST['delivery_vat'] ?? round(intval($_POST['delivery_price'] ?? 0) * 0.1)),
        'discount_amount' => intval($_POST['discount_amount'] ?? 0),
        'discount_reason' => trim($_POST['discount_reason'] ?? ''),
        'payment_terms' => trim($_POST['payment_terms'] ?? '발행일로부터 7일'),
        'valid_days' => intval($_POST['valid_days'] ?? 7),
        'notes' => trim($_POST['notes'] ?? ''),
        'items' => isset($_POST['items_json'])
            ? json_decode($_POST['items_json'], true)
            : parseItemsFromPost($_POST)
    ];

    // 필수 입력 검증
    if (empty($data['customer_name'])) {
        jsonResponse(false, [], '고객명은 필수입니다.');
    }

    if (empty($data['customer_email'])) {
        jsonResponse(false, [], '고객 이메일은 필수입니다.');
    }

    if (empty($data['items']) || !is_array($data['items'])) {
        jsonResponse(false, [], '품목 정보가 필요합니다.');
    }

    // 금액 계산
    $supplyTotal = 0;
    $vatTotal = 0;

    foreach ($data['items'] as $item) {
        $quantity = floatval($item['quantity'] ?? 1);
        $unitPrice = intval($item['unit_price'] ?? 0);
        $supplyPrice = intval($quantity * $unitPrice);
        $vat = intval(round($supplyPrice * 0.1));

        $supplyTotal += $supplyPrice;
        $vatTotal += $vat;
    }

    $data['supply_total'] = $supplyTotal;
    $data['vat_total'] = $vatTotal;
    $data['grand_total'] = $supplyTotal + $vatTotal + $data['delivery_price'] + $data['delivery_vat'] - $data['discount_amount'];

    // 유효기간 계산
    $data['valid_until'] = date('Y-m-d', strtotime('+' . $data['valid_days'] . ' days'));

    // 견적서 업데이트
    $result = $manager->updateQuote($data);

    if (!$result['success']) {
        jsonResponse(false, [], $result['message']);
    }

    // 이메일 발송 요청 시
    $sendEmail = ($_POST['send_email'] ?? '0') === '1';
    $emailSent = false;
    $emailError = '';

    if ($sendEmail && !empty($data['recipient_email'])) {
        // 이메일 발송 처리
        require_once __DIR__ . '/send_email.php';

        $emailResult = sendQuotationEmail(
            $db,
            $quoteId,
            $data['recipient_email'],
            $data['customer_name']
        );

        $emailSent = $emailResult['success'];
        $emailError = $emailResult['message'] ?? '';

        if ($emailSent) {
            // 상태를 'sent'로 업데이트
            $manager->updateStatus($quoteId, 'sent');
        }
    }

    jsonResponse(true, [
        'quote_id' => $quoteId,
        'quote_no' => $quote['quote_no'],
        'public_token' => $quote['public_token'],
        'public_url' => $result['public_url'] ?? '',
        'email_sent' => $emailSent,
        'email_error' => $emailError
    ], $sendEmail ? ($emailSent ? '견적서가 수정되고 이메일이 발송되었습니다.' : '견적서는 수정되었으나 이메일 발송에 실패했습니다: ' . $emailError) : '견적서가 수정되었습니다.');

} catch (Exception $e) {
    jsonResponse(false, [], '오류: ' . $e->getMessage());
}
?>
