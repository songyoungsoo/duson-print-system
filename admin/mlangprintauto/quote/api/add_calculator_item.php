<?php
/**
 * 관리자 견적서 - 계산기 품목 추가 API
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once dirname(__DIR__) . '/includes/AdminQuoteManager.php';
require_once dirname(__DIR__) . '/includes/PriceHelper.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

$adminSessionId = session_id();

try {
    $manager = new AdminQuoteManager($db);

    // 품목 데이터 정규화
    $productType = $input['product_type'] ?? '';
    $productName = $input['product_name'] ?? PriceHelper::getProductTypeName($productType);
    $specification = $input['specification'] ?? '';
    $quantity = floatval($input['quantity'] ?? 1);
    $unit = $input['unit'] ?? '개';
    $quantityDisplay = $input['quantity_display'] ?? '';
    $unitPrice = floatval($input['unit_price'] ?? 0);
    $supplyPrice = intval($input['supply_price'] ?? 0);

    // quantity_display가 없으면 생성
    if (empty($quantityDisplay)) {
        $quantityDisplay = number_format($quantity, ($quantity == floor($quantity)) ? 0 : 1) . $unit;
    }

    // 스티커는 단가 × 수량으로 공급가액 계산 허용
    if ($productType === 'sticker' && $unitPrice > 0 && $supplyPrice == 0) {
        $supplyPrice = intval($unitPrice * $quantity);
    }

    // admin_quotation_temp에 저장
    $calcItem = [
        'is_manual' => false,
        'product_type' => $productType,
        'specification' => $specification,
        'unit_price' => $unitPrice,
        'jong' => $input['jong'] ?? '',
        'garo' => $input['garo'] ?? '',
        'sero' => $input['sero'] ?? '',
        'mesu' => $input['mesu'] ?? '',
        'domusong' => $input['domusong'] ?? '',
        'uhyung' => intval($input['uhyung'] ?? 0),
        'MY_type' => $input['MY_type'] ?? '',
        'MY_Fsd' => $input['MY_Fsd'] ?? '',
        'PN_type' => $input['PN_type'] ?? '',
        'MY_amount' => $input['MY_amount'] ?? '',
        'POtype' => $input['POtype'] ?? '',
        'ordertype' => $input['ordertype'] ?? '',
        'st_price' => $supplyPrice,
        'st_price_vat' => intval($supplyPrice * 1.1),
        'Section' => $input['Section'] ?? '',
        'spec_type' => $input['spec_type'] ?? '',
        'spec_material' => $input['spec_material'] ?? '',
        'spec_size' => $input['spec_size'] ?? '',
        'spec_sides' => $input['spec_sides'] ?? '',
        'spec_design' => $input['spec_design'] ?? '',
        'quantity_display' => $quantityDisplay,
        'coating_enabled' => intval($input['coating_enabled'] ?? 0),
        'coating_type' => $input['coating_type'] ?? '',
        'coating_price' => intval($input['coating_price'] ?? 0),
        'folding_enabled' => intval($input['folding_enabled'] ?? 0),
        'folding_type' => $input['folding_type'] ?? '',
        'folding_price' => intval($input['folding_price'] ?? 0),
        'creasing_enabled' => intval($input['creasing_enabled'] ?? 0),
        'creasing_lines' => intval($input['creasing_lines'] ?? 0),
        'creasing_price' => intval($input['creasing_price'] ?? 0),
        'additional_options_total' => intval($input['additional_options_total'] ?? 0)
    ];

    $itemNo = $manager->addTempItem($adminSessionId, $calcItem);

    // 응답 데이터
    $responseItem = [
        'product_type' => $productType,
        'product_name' => $productName,
        'specification' => $specification,
        'quantity' => $quantity,
        'unit' => $unit,
        'quantity_display' => $quantityDisplay,
        'unit_price' => $unitPrice,
        'supply_price' => $supplyPrice
    ];

    echo json_encode([
        'success' => true,
        'item_no' => $itemNo,
        'item' => $responseItem
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '오류: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
