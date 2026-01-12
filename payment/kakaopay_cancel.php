<?php
/**
 * 카카오페이 결제 취소 콜백
 */
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/services/PaymentGateway.php';

$paymentId = $_SESSION['kakaopay_payment_id'] ?? 0;

if ($paymentId) {
    try {
        $paymentGateway = new PaymentGateway($db);
        $paymentGateway->cancelPayment($paymentId, '사용자 취소');
    } catch (Exception $e) {
        error_log('[KakaoPay Cancel] Error: ' . $e->getMessage());
    }

    unset($_SESSION['kakaopay_payment_id']);
}

// 주문 페이지로 돌아가기
header('Location: /mlangorder_printauto/index.php?error=payment_cancelled');
exit;
