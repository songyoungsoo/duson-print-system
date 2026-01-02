<?php
/**
 * 견적서 저장 API
 * POST 요청으로 견적서 저장
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

try {
    $manager = new QuoteManager($db);

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
        'supply_total' => intval($_POST['supply_total'] ?? 0),
        'vat_total' => intval($_POST['vat_total'] ?? 0),
        'discount_amount' => intval($_POST['discount_amount'] ?? 0),
        'discount_reason' => trim($_POST['discount_reason'] ?? ''),
        'grand_total' => intval($_POST['grand_total'] ?? 0),
        'payment_terms' => trim($_POST['payment_terms'] ?? '발행일로부터 7일'),
        'valid_days' => intval($_POST['valid_days'] ?? 7),
        'notes' => trim($_POST['notes'] ?? ''),
        'created_by' => intval($_SESSION['user_id'] ?? 0),
        'items' => $_POST['items'] ?? []
    ];

    // 필수 입력 검증
    if (empty($data['customer_name'])) {
        jsonResponse(false, [], '고객명은 필수입니다.');
    }

    if (empty($data['customer_email'])) {
        jsonResponse(false, [], '고객 이메일은 필수입니다.');
    }

    // 장바구니에서 생성 여부
    $fromCart = ($_POST['from_cart'] ?? '0') === '1';

    if ($fromCart) {
        // 장바구니 연동 생성
        $sessionId = session_id();
        $result = $manager->createFromCart($sessionId, $data);
    } else {
        // 빈 견적서 (수동 입력) 생성
        $result = $manager->createEmpty($data);
    }

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
    ], $sendEmail ? ($emailSent ? '견적서가 저장되고 이메일이 발송되었습니다.' : '견적서는 저장되었으나 이메일 발송에 실패했습니다: ' . $emailError) : '견적서가 저장되었습니다.');

} catch (Exception $e) {
    jsonResponse(false, [], '오류: ' . $e->getMessage());
}
?>
