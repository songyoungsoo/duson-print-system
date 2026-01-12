<?php
/**
 * 일괄 처리 API
 * 주문 상태 변경, 배송 등록, 입금 확인 등 일괄 처리
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';

// 관리자 인증 확인
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => '인증이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST 요청만 허용됩니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// JSON 입력 파싱
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => '유효하지 않은 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $input['action'] ?? '';
$orderIds = $input['order_ids'] ?? [];

if (empty($action) || empty($orderIds)) {
    http_response_code(400);
    echo json_encode(['error' => 'action과 order_ids가 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 주문 ID 유효성 검사
$orderIds = array_filter(array_map('intval', $orderIds));
if (empty($orderIds)) {
    http_response_code(400);
    echo json_encode(['error' => '유효한 주문번호가 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($action) {
        case 'update_status':
            $result = batchUpdateStatus($db, $orderIds, $input['status'] ?? '');
            break;

        case 'confirm_payment':
            $result = batchConfirmPayment($db, $orderIds);
            break;

        case 'register_shipping':
            $result = batchRegisterShipping($db, $orderIds, $input['shipping_data'] ?? []);
            break;

        case 'send_notification':
            $result = batchSendNotification($db, $orderIds, $input['notification_type'] ?? '');
            break;

        case 'export_excel':
            $result = exportOrdersExcel($db, $orderIds);
            break;

        default:
            throw new Exception('지원하지 않는 작업입니다: ' . $action);
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

/**
 * 일괄 상태 변경
 */
function batchUpdateStatus($db, $orderIds, $newStatus) {
    if (empty($newStatus)) {
        throw new Exception('변경할 상태를 지정해주세요.');
    }

    require_once __DIR__ . '/../../includes/OrderStatusManager.php';

    $success = 0;
    $failed = 0;
    $errors = [];

    foreach ($orderIds as $orderId) {
        try {
            $manager = new OrderStatusManager($db, $orderId);
            if ($manager->changeStatus($newStatus, 'admin_batch', '일괄 상태 변경')) {
                $success++;
            } else {
                $failed++;
                $errors[] = "주문 #{$orderId}: 상태 변경 실패";
            }
        } catch (Exception $e) {
            $failed++;
            $errors[] = "주문 #{$orderId}: " . $e->getMessage();
        }
    }

    return [
        'success' => true,
        'message' => "{$success}건 처리 완료" . ($failed > 0 ? ", {$failed}건 실패" : ''),
        'processed' => $success,
        'failed' => $failed,
        'errors' => $errors
    ];
}

/**
 * 일괄 입금 확인
 */
function batchConfirmPayment($db, $orderIds) {
    require_once __DIR__ . '/../../includes/services/PaymentGateway.php';
    require_once __DIR__ . '/../../includes/OrderStatusManager.php';

    $paymentGateway = new PaymentGateway($db);
    $success = 0;
    $failed = 0;
    $errors = [];

    foreach ($orderIds as $orderId) {
        try {
            // 결제 정보 조회
            $query = "SELECT id FROM payments WHERE order_id = ? AND payment_method = 'bank_transfer' AND status = 'pending'";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, 'i', $orderId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $payment = mysqli_fetch_assoc($result);

            if ($payment) {
                $confirmResult = $paymentGateway->confirmBankTransfer($payment['id'], 'admin_batch');
                if ($confirmResult && $confirmResult['success']) {
                    // 주문 상태 업데이트
                    $manager = new OrderStatusManager($db, $orderId);
                    $manager->changeStatus('payment_confirmed', 'admin_batch', '일괄 입금 확인');
                    $success++;
                } else {
                    $failed++;
                    $errors[] = "주문 #{$orderId}: 입금 확인 실패";
                }
            } else {
                // 결제 정보 없으면 주문 상태만 업데이트
                $updateQuery = "UPDATE mlangorder_printauto SET payment_status = 'paid' WHERE no = ?";
                $updateStmt = mysqli_prepare($db, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, 'i', $orderId);
                if (mysqli_stmt_execute($updateStmt)) {
                    $manager = new OrderStatusManager($db, $orderId);
                    $manager->changeStatus('payment_confirmed', 'admin_batch', '일괄 입금 확인');
                    $success++;
                } else {
                    $failed++;
                    $errors[] = "주문 #{$orderId}: 결제 정보 없음";
                }
            }
        } catch (Exception $e) {
            $failed++;
            $errors[] = "주문 #{$orderId}: " . $e->getMessage();
        }
    }

    return [
        'success' => true,
        'message' => "{$success}건 입금 확인 완료" . ($failed > 0 ? ", {$failed}건 실패" : ''),
        'processed' => $success,
        'failed' => $failed,
        'errors' => $errors
    ];
}

/**
 * 일괄 배송 등록
 */
function batchRegisterShipping($db, $orderIds, $shippingData) {
    require_once __DIR__ . '/../../includes/services/DeliveryTrackingService.php';
    require_once __DIR__ . '/../../includes/OrderStatusManager.php';

    $deliveryService = new DeliveryTrackingService($db);
    $success = 0;
    $failed = 0;
    $errors = [];

    foreach ($orderIds as $orderId) {
        try {
            // 주문별 배송 정보 확인
            $shipping = $shippingData[$orderId] ?? null;

            if (!$shipping || empty($shipping['courier_code']) || empty($shipping['tracking_number'])) {
                $failed++;
                $errors[] = "주문 #{$orderId}: 배송 정보 없음";
                continue;
            }

            $result = $deliveryService->registerShipping($orderId, $shipping['courier_code'], $shipping['tracking_number']);

            if ($result && $result['success']) {
                // 주문 상태 업데이트
                $manager = new OrderStatusManager($db, $orderId);
                $manager->changeStatus('shipped', 'admin_batch', '일괄 배송 등록');
                $success++;
            } else {
                $failed++;
                $errors[] = "주문 #{$orderId}: " . ($deliveryService->getLastError() ?: '배송 등록 실패');
            }
        } catch (Exception $e) {
            $failed++;
            $errors[] = "주문 #{$orderId}: " . $e->getMessage();
        }
    }

    return [
        'success' => true,
        'message' => "{$success}건 배송 등록 완료" . ($failed > 0 ? ", {$failed}건 실패" : ''),
        'processed' => $success,
        'failed' => $failed,
        'errors' => $errors
    ];
}

/**
 * 일괄 알림 발송
 */
function batchSendNotification($db, $orderIds, $notificationType) {
    require_once __DIR__ . '/../../includes/services/NotificationService.php';

    if (empty($notificationType)) {
        throw new Exception('알림 유형을 지정해주세요.');
    }

    $notification = new NotificationService($db);
    $success = 0;
    $failed = 0;
    $errors = [];

    foreach ($orderIds as $orderId) {
        try {
            // 주문 정보 조회
            $query = "SELECT no, name, Hendphone, email, money_4, courier, tracking_number FROM mlangorder_printauto WHERE no = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, 'i', $orderId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $order = mysqli_fetch_assoc($result);

            if (!$order || empty($order['Hendphone'])) {
                $failed++;
                $errors[] = "주문 #{$orderId}: 연락처 없음";
                continue;
            }

            $sendResult = false;

            switch ($notificationType) {
                case 'payment_reminder':
                    $sendResult = $notification->send([
                        'type' => NotificationService::TYPE_SMS,
                        'phone' => $order['Hendphone'],
                        'message' => "[두손기획] {$order['name']}님, 주문번호 {$orderId}의 입금이 확인되지 않았습니다. 입금 확인 후 제작이 진행됩니다.",
                        'order_id' => $orderId
                    ]);
                    break;

                case 'shipping_start':
                    if (!empty($order['courier']) && !empty($order['tracking_number'])) {
                        $sendResult = $notification->sendShippingStart(
                            $orderId,
                            $order['Hendphone'],
                            $order['name'],
                            $order['courier'],
                            $order['tracking_number']
                        );
                    } else {
                        $errors[] = "주문 #{$orderId}: 배송 정보 없음";
                        $failed++;
                        continue 2;
                    }
                    break;

                case 'production_start':
                    $sendResult = $notification->send([
                        'type' => NotificationService::TYPE_KAKAO_ALIMTALK,
                        'template' => NotificationService::TEMPLATE_PRODUCTION_START,
                        'phone' => $order['Hendphone'],
                        'message' => "[두손기획] {$order['name']}님, 주문번호 {$orderId}의 제작이 시작되었습니다.",
                        'order_id' => $orderId
                    ]);
                    break;

                default:
                    $errors[] = "주문 #{$orderId}: 지원하지 않는 알림 유형";
                    $failed++;
                    continue 2;
            }

            if ($sendResult && $sendResult['success']) {
                $success++;
            } else {
                $failed++;
                $errors[] = "주문 #{$orderId}: 알림 발송 실패";
            }
        } catch (Exception $e) {
            $failed++;
            $errors[] = "주문 #{$orderId}: " . $e->getMessage();
        }
    }

    return [
        'success' => true,
        'message' => "{$success}건 알림 발송 완료" . ($failed > 0 ? ", {$failed}건 실패" : ''),
        'processed' => $success,
        'failed' => $failed,
        'errors' => $errors
    ];
}

/**
 * 엑셀 내보내기
 */
function exportOrdersExcel($db, $orderIds) {
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $types = str_repeat('i', count($orderIds));

    $query = "SELECT no, Type, name, email, phone, Hendphone, zip1, zip2,
                     money_1, money_2, money_3, money_4, money_5,
                     date, OrderStyle, payment_status, ship_status, courier, tracking_number
              FROM mlangorder_printauto
              WHERE no IN ($placeholders)
              ORDER BY no DESC";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$orderIds);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = [
            '주문번호' => $row['no'],
            '품목' => $row['Type'],
            '주문자' => $row['name'],
            '이메일' => $row['email'],
            '연락처' => $row['Hendphone'] ?: $row['phone'],
            '주소' => $row['zip1'] . ' ' . $row['zip2'],
            '공급가' => $row['money_1'],
            'VAT' => $row['money_2'],
            '배송비' => $row['money_3'],
            '총액' => $row['money_4'],
            '결제상태' => $row['payment_status'] ?: 'pending',
            '배송상태' => $row['ship_status'] ?: 'pending',
            '택배사' => $row['courier'],
            '운송장' => $row['tracking_number'],
            '주문일시' => $row['date']
        ];
    }

    return [
        'success' => true,
        'data' => $orders,
        'count' => count($orders)
    ];
}
