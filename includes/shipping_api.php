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
