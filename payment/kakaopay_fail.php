<?php
/**
 * 카카오페이 결제 실패 콜백
 */
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/services/PaymentGateway.php';

$paymentId = $_SESSION['kakaopay_payment_id'] ?? 0;

if ($paymentId) {
    try {
        $paymentGateway = new PaymentGateway($db);

        // 결제 상태를 실패로 업데이트
        $query = "UPDATE payments SET status = 'failed', updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $paymentId);
        mysqli_stmt_execute($stmt);

    } catch (Exception $e) {
        error_log('[KakaoPay Fail] Error: ' . $e->getMessage());
    }

    unset($_SESSION['kakaopay_payment_id']);
}

// 주문 페이지로 돌아가기
header('Location: /mlangorder_printauto/index.php?error=payment_failed');
exit;
