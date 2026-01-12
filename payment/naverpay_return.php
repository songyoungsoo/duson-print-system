<?php
/**
 * 네이버페이 결제 완료 콜백
 */
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/services/PaymentGateway.php';
require_once __DIR__ . '/../includes/services/NotificationService.php';

// 네이버페이에서 전달하는 파라미터
$resultCode = $_GET['resultCode'] ?? '';
$paymentId = $_GET['paymentId'] ?? '';
$merchantPayKey = $_GET['merchantPayKey'] ?? '';

// merchantPayKey에서 payment_id 추출 (PAY_{id}_{timestamp})
preg_match('/PAY_(\d+)_/', $merchantPayKey, $matches);
$internalPaymentId = $matches[1] ?? 0;

if ($resultCode !== 'Success' || empty($paymentId) || empty($internalPaymentId)) {
    header('Location: /mlangorder_printauto/index.php?error=payment_failed');
    exit;
}

try {
    $paymentGateway = new PaymentGateway($db);

    // 결제 승인
    $result = $paymentGateway->approveNaverPay($internalPaymentId, [
        'paymentId' => $paymentId
    ]);

    if ($result && $result['success']) {
        // 결제 정보 조회
        $payment = $paymentGateway->getPayment($internalPaymentId);
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
                '네이버페이'
            );
        }

        // 주문 완료 페이지로 이동
        header('Location: /mlangorder_printauto/OrderComplete_universal.php?no=' . $orderId . '&payment=success');
        exit;
    }

    throw new Exception($paymentGateway->getLastError() ?: '결제 승인 실패');

} catch (Exception $e) {
    error_log('[NaverPay Return] Error: ' . $e->getMessage());
    header('Location: /mlangorder_printauto/index.php?error=payment_failed&message=' . urlencode($e->getMessage()));
    exit;
}
