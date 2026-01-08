<?php
/**
 * 견적서 개정판 생성 API (sent 상태만)
 * POST 요청으로 개정판 생성
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
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
 */
function parseItemsFromPost($post) {
    $items = [];

    foreach ($post as $key => $value) {
        if (preg_match('/^items\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
            $index = intval($matches[1]);
            $field = $matches[2];

            if (!isset($items[$index])) {
                $items[$index] = [];
            }

            $items[$index][$field] = $value;
        }
    }

    ksort($items);
    return array_values($items);
}

try {
    $manager = new QuoteManager($db);

    // 원본 견적서 ID
    $originalQuoteId = intval($_POST['original_quote_id'] ?? 0);
    if (!$originalQuoteId) {
        jsonResponse(false, [], '원본 견적서 ID가 필요합니다.');
    }

    // POST 데이터 수집
    $data = [
        'quote_type' => $_POST['quote_type'] ?? 'quotation',
        'customer_name' => trim($_POST['customer_name'] ?? ''),
        'customer_company' => trim($_POST['customer_company'] ?? ''),
        'customer_phone' => trim($_POST['customer_phone'] ?? ''),
        'customer_email' => trim($_POST['customer_email'] ?? ''),
        'recipient_email' => trim($_POST['recipient_email'] ?? $_POST['customer_email'] ?? ''),
        'delivery_type' => trim($_POST['delivery_type'] ?? ''),
        'delivery_address' => trim($_POST['delivery_address'] ?? ''),
        'delivery_price' => intval($_POST['delivery_price'] ?? 0),
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

    // 금액 계산 (공급가 우선 - 사용자 입력값 그대로 사용)
    $supplyTotal = 0;
    $vatTotal = 0;

    foreach ($data['items'] as $item) {
        // 공급가를 직접 사용 (재계산하지 않음)
        $supplyPrice = intval($item['supply_price'] ?? 0);
        $vat = intval(round($supplyPrice * 0.1));

        $supplyTotal += $supplyPrice;
        $vatTotal += $vat;
    }

    $data['supply_total'] = $supplyTotal;
    $data['vat_total'] = $vatTotal;
    $data['grand_total'] = $supplyTotal + $vatTotal - $data['discount_amount'] + $data['delivery_price'];

    // 개정판 생성
    $result = $manager->createRevision($originalQuoteId, $data);

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
            $result['quote_id'],
            $data['recipient_email'],
            $data['customer_name']
        );

        $emailSent = $emailResult['success'];
        $emailError = $emailResult['message'] ?? '';

        if ($emailSent) {
            // 상태를 'sent'로 업데이트
            $manager->updateStatus($result['quote_id'], 'sent');
        }
    }

    jsonResponse(true, [
        'quote_id' => $result['quote_id'],
        'quote_no' => $result['quote_no'],
        'public_token' => $result['public_token'],
        'public_url' => $result['public_url'],
        'email_sent' => $emailSent,
        'email_error' => $emailError
    ], $sendEmail ? ($emailSent ? $result['message'] . ' 이메일이 발송되었습니다.' : $result['message'] . ' 이메일 발송에 실패했습니다: ' . $emailError) : $result['message']);

} catch (Exception $e) {
    jsonResponse(false, [], '오류: ' . $e->getMessage());
}
?>
