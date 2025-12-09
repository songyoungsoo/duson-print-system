<?php
/**
 * 디버그용 저장 API
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../db.php';

    if (!$db) {
        throw new Exception("DB 연결 실패");
    }

    require_once __DIR__ . '/../includes/QuoteManager.php';

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
        echo json_encode(['success' => false, 'message' => '고객명은 필수입니다.', 'debug' => 'validation'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($data['customer_email'])) {
        echo json_encode(['success' => false, 'message' => '고객 이메일은 필수입니다.', 'debug' => 'validation'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 장바구니에서 생성 여부
    $fromCart = ($_POST['from_cart'] ?? '0') === '1';

    if ($fromCart) {
        $sessionId = session_id();
        $result = $manager->createFromCart($sessionId, $data);
    } else {
        $result = $manager->createEmpty($data);
    }

    if (!$result['success']) {
        echo json_encode(['success' => false, 'message' => $result['message'], 'debug' => 'create_failed'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 이메일 발송 요청 시
    $sendEmail = ($_POST['send_email'] ?? '0') === '1';
    $emailSent = false;
    $emailError = '';

    if ($sendEmail && !empty($data['recipient_email'])) {
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
            $manager->updateStatus($result['quote_id'], 'sent');
        }
    }

    echo json_encode([
        'success' => true,
        'quote_id' => $result['quote_id'],
        'quote_no' => $result['quote_no'],
        'public_token' => $result['public_token'],
        'public_url' => $result['public_url'],
        'email_sent' => $emailSent,
        'email_error' => $emailError,
        'message' => $sendEmail ? ($emailSent ? '견적서가 저장되고 이메일이 발송되었습니다.' : '견적서는 저장되었으나 이메일 발송에 실패했습니다: ' . $emailError) : '견적서가 저장되었습니다.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '오류: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
}
?>
