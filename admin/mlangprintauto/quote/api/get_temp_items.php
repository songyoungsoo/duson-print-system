<?php
/**
 * 임시 품목 목록 조회 API
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// 세션 시작 및 인증 확인
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// DB 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once dirname(__DIR__) . '/includes/AdminQuoteManager.php';
require_once dirname(__DIR__) . '/includes/PriceHelper.php';

try {
    $manager = new AdminQuoteManager($db);
    $priceHelper = new PriceHelper($db);

    // 세션 ID 기반으로 임시 품목 조회
    $sessionId = session_id();
    $tempItems = $manager->getTempItems($sessionId);

    // 견적 품목 형식으로 변환
    $formattedItems = [];
    foreach ($tempItems as $temp) {
        if (!empty($temp['is_manual'])) {
            // 수동 입력 품목
            $formattedItems[] = [
                'no' => $temp['no'],
                'is_manual' => 1,
                'product_name' => $temp['manual_product_name'],
                'specification' => $temp['manual_specification'],
                'quantity' => floatval($temp['manual_quantity']),
                'unit' => $temp['manual_unit'],
                'quantity_display' => number_format($temp['manual_quantity'],
                    $temp['manual_quantity'] == intval($temp['manual_quantity']) ? 0 : 1) . $temp['manual_unit'],
                'unit_price' => $temp['manual_quantity'] > 0
                    ? round($temp['manual_supply_price'] / $temp['manual_quantity'], 2)
                    : 0,
                'supply_price' => intval($temp['manual_supply_price']),
                'product_type' => '',
                'source_data' => null
            ];
        } else {
            // 계산기 품목 - PriceHelper로 변환
            $formatted = $priceHelper->formatCalculatorItem($temp);
            $formatted['no'] = $temp['no'];
            $formatted['is_manual'] = 0;
            $formattedItems[] = $formatted;
        }
    }

    echo json_encode([
        'success' => true,
        'items' => $formattedItems,
        'count' => count($formattedItems)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '오류: ' . $e->getMessage()
    ]);
}
