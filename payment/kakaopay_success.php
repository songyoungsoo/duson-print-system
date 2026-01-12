<?php
/**
 * 카카오페이 결제 성공 콜백
 */
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/services/PaymentGateway.php';
require_once __DIR__ . '/../includes/services/NotificationService.php';

// 필수 파라미터 확인
$pgToken = $_GET['pg_token'] ?? '';
$paymentId = $_SESSION['kakaopay_payment_id'] ?? 0;

if (empty($pgToken) || empty($paymentId)) {
    header('Location: /mlangorder_printauto/index.php?error=payment_failed');
    exit;
}

try {
    $paymentGateway = new PaymentGateway($db);

    // 결제 승인
    $result = $paymentGateway->approveKakaoPay($paymentId, $pgToken);

    if ($result && $result['success']) {
        // 결제 정보 조회
        $payment = $paymentGateway->getPayment($paymentId);
        $orderId = $payment['order_id'];

        // 주문 정보 조회
        $query = "SELECT name, Hendphone, money_4 FROM mlangorder_printauto WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $orderResult = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($orderResult);

        // 알림 발송
        if ($order && !empty($order['Hendphone'])) {
            $notification = new NotificationService($db);
            $notification->sendPaymentComplete(
                $orderId,
                $order['Hendphone'],
                $order['name'],
                $order['money_4'],
                '카카오페이'
            );
        }

        // 세션 정리
        unset($_SESSION['kakaopay_payment_id']);

        // 주문 완료 페이지로 이동
        header('Location: /mlangorder_printauto/OrderComplete_universal.php?no=' . $orderId . '&payment=success');
        exit;
    }

    throw new Exception($paymentGateway->getLastError() ?: '결제 승인 실패');

} catch (Exception $e) {
    error_log('[KakaoPay Success] Error: ' . $e->getMessage());
    header('Location: /mlangorder_printauto/index.php?error=payment_failed&message=' . urlencode($e->getMessage()));
    exit;
}
