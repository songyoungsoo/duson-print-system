<?php
/**
 * 네이버페이 결제 취소 콜백
 */
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/services/PaymentGateway.php';

$merchantPayKey = $_GET['merchantPayKey'] ?? '';

// merchantPayKey에서 payment_id 추출
preg_match('/PAY_(\d+)_/', $merchantPayKey, $matches);
$paymentId = $matches[1] ?? 0;

if ($paymentId) {
    try {
        $paymentGateway = new PaymentGateway($db);
        $paymentGateway->cancelPayment($paymentId, '사용자 취소');
    } catch (Exception $e) {
        error_log('[NaverPay Cancel] Error: ' . $e->getMessage());
    }
}

header('Location: /mlangorder_printauto/index.php?error=payment_cancelled');
exit;
