<?php
/**
 * shipping_api.php — 배송 추정 AJAX API
 * 
 * Actions:
 *   estimate    — 장바구니 아이템으로 무게/박스 추정
 *   rates       — 현재 요금표 조회
 *   rates_save  — 요금표 수정 (관리자 전용)
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/ShippingCalculator.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'estimate':
        $cartJson = $_POST['cart_items'] ?? '';
        $cartItems = json_decode($cartJson, true);

        if (!$cartItems || !is_array($cartItems)) {
            echo json_encode(['success' => false, 'error' => 'cart_items required']);
            exit;
        }

        $result = ShippingCalculator::estimateFromCart($cartItems);
        echo json_encode(['success' => true, 'data' => $result]);
        break;

    case 'rates':
        $rates = ShippingCalculator::getRatesForDisplay($connect ?? null);
        echo json_encode(['success' => true, 'data' => $rates]);
        break;

    case 'order_estimate':
        // 관리자용: 주문번호로 배송 추정 + 기존 logen 데이터 반환
        $orderNo = intval($_GET['no'] ?? $_POST['no'] ?? 0);
        if (!$orderNo) {
            echo json_encode(['success' => false, 'error' => 'no required']);
            exit;
        }

        $stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $orderNo);
        mysqli_stmt_execute($stmt);
        $orderResult = mysqli_stmt_get_result($stmt);
        $orderRow = mysqli_fetch_assoc($orderResult);
        mysqli_stmt_close($stmt);

        if (!$orderRow) {
            echo json_encode(['success' => false, 'error' => 'order not found']);
            exit;
        }

        $estimate = ShippingCalculator::estimateFromOrder($orderRow);

        echo json_encode([
            'success' => true,
            'data' => [
                'estimate' => $estimate,
                'delivery' => $orderRow['delivery'] ?? '',
                'logen_box_qty' => $orderRow['logen_box_qty'],
                'logen_delivery_fee' => $orderRow['logen_delivery_fee'],
                'logen_fee_type' => $orderRow['logen_fee_type'] ?? '',
                'logen_tracking_no' => $orderRow['logen_tracking_no'] ?? ''
            ]
        ]);
        break;

    case 'logen_save':
        // 관리자용: 배송 정보 저장
        $orderNo = intval($_POST['no'] ?? 0);
        if (!$orderNo) {
            echo json_encode(['success' => false, 'error' => 'no required']);
            exit;
        }

        $logenBoxQty = isset($_POST['logen_box_qty']) && $_POST['logen_box_qty'] !== '' ? intval($_POST['logen_box_qty']) : null;
        $logenDeliveryFee = isset($_POST['logen_delivery_fee']) && $_POST['logen_delivery_fee'] !== '' ? intval($_POST['logen_delivery_fee']) : null;
        $logenFeeType = $_POST['logen_fee_type'] ?? '';
        $logenTrackingNo = trim($_POST['logen_tracking_no'] ?? '');

        $updateQuery = "UPDATE mlangorder_printauto SET logen_box_qty = ?, logen_delivery_fee = ?, logen_fee_type = ?, logen_tracking_no = ? WHERE no = ?";
        $stmt = mysqli_prepare($db, $updateQuery);
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
            exit;
        }

        // bind_param 검증: 5 placeholders, "iissi", 5 vars
        $placeholder_count = substr_count($updateQuery, '?');  // 5
        $type_string_logen = "iissi";
        $type_count = strlen($type_string_logen);              // 5
        $var_count = 5;                                        // 5

        mysqli_stmt_bind_param($stmt, $type_string_logen, $logenBoxQty, $logenDeliveryFee, $logenFeeType, $logenTrackingNo, $orderNo);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => '배송 정보가 저장되었습니다.']);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
        break;

    case 'rates_save':
        session_start();
        if (empty($_SESSION['is_admin'])) {
            echo json_encode(['success' => false, 'error' => 'admin_required']);
            exit;
        }

        $ratesJson = $_POST['rates'] ?? '';
        $ratesData = json_decode($ratesJson, true);

        if (!$ratesData || !is_array($ratesData)) {
            echo json_encode(['success' => false, 'error' => 'rates data required']);
            exit;
        }

        $db = $connect ?? null;
        if (!$db) {
            echo json_encode(['success' => false, 'error' => 'db_error']);
            exit;
        }

        mysqli_begin_transaction($db);
        try {
            mysqli_query($db, "DELETE FROM shipping_rates");

            $stmt = mysqli_prepare($db, "INSERT INTO shipping_rates (rate_group, label, max_kg, fee, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");

            $sortOrder = 0;
            foreach ($ratesData as $group => $items) {
                foreach ($items as $item) {
                    $sortOrder++;
                    $label = $item['label'] ?? '';
                    $maxKg = floatval($item['max_kg'] ?? 0);
                    $fee = intval($item['fee'] ?? 0);

                    if ($maxKg <= 0 || $fee <= 0) continue;

                    mysqli_stmt_bind_param($stmt, "ssdii", $group, $label, $maxKg, $fee, $sortOrder);
                    mysqli_stmt_execute($stmt);
                }
            }

            mysqli_commit($db);
            ShippingCalculator::$cachedRates = null;

            echo json_encode(['success' => true, 'message' => '요금표가 저장되었습니다.']);
        } catch (\Exception $e) {
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'unknown action']);
}
