<?php
/**
 * 견적엔진 API — 가격 계산
 * POST /api/quote-engine/calculate.php
 *
 * 요청 (JSON 또는 form-data):
 *   DB lookup 제품: product, style, section(=Section), quantity, tree_select, po_type, design_type, premium_options
 *   스티커: product=sticker, jong, garo, sero, mesu, domusong, uhyung
 *
 * 응답:
 *   { success, supply_price, vat, total, product_name, specification, unit, quantity, unit_price, source_data }
 */

header('Content-Type: application/json; charset=utf-8');
session_start();


if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../db.php';
mysqli_set_charset($db, 'utf8mb4');
require_once __DIR__ . '/../../includes/quote-engine/PriceCalculator.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'POST만 허용됩니다']);
        exit;
    }


    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '잘못된 JSON 형식입니다']);
            exit;
        }
    } else {
        $input = $_POST;
    }


    $product = trim($input['product'] ?? '');
    if ($product === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'product(품목코드)는 필수입니다']);
        exit;
    }

    // 파라미터 매핑 (프론트 필드명 → 엔진 필드명)
    $params = [];

    if ($product === 'sticker') {
        // 스티커: 공식 기반 파라미터
        $params['jong']     = $input['jong'] ?? '';
        $params['garo']     = $input['garo'] ?? 0;
        $params['sero']     = $input['sero'] ?? 0;
        $params['mesu']     = $input['mesu'] ?? 0;
        $params['domusong'] = $input['domusong'] ?? '';
        $params['uhyung']   = $input['uhyung'] ?? 0;
    } else {
        // DB lookup 제품: 카테고리 + 수량
        $params['style']      = $input['style'] ?? '';
        $params['Section']    = $input['section'] ?? $input['Section'] ?? '';
        $params['quantity']   = $input['quantity'] ?? '';
        $params['TreeSelect'] = $input['tree_select'] ?? $input['TreeSelect'] ?? '';
        $params['POtype']     = $input['po_type'] ?? $input['POtype'] ?? '';
        $params['ordertype']  = $input['design_type'] ?? $input['ordertype'] ?? 'print';
    }

    // 가격 계산 실행
    $calculator = new QE_PriceCalculator($db);
    $result = $calculator->calculate($product, $params);

    if (!$result['success']) {
        http_response_code(400);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 프리미엄 옵션 추가 가격 반영
    $premiumTotal = 0;
    $premiumDetails = [];

    if (!empty($input['premium_options']) && is_array($input['premium_options'])) {
        $premiumOptions = $calculator->getPremiumOptions($product);

        foreach ($input['premium_options'] as $optionName => $variantId) {
            $variantId = (int)$variantId;
            // 선택한 variant 찾기
            foreach ($premiumOptions as $optGroup) {
                if ($optGroup['option_name'] === $optionName) {
                    foreach ($optGroup['variants'] as $v) {
                        if ($v['variant_id'] === $variantId) {
                            $pricing = $v['pricing_config'];
                            $addPrice = 0;

                            // pricing_config에서 가격 계산 (고정 금액 또는 비율)
                            if (isset($pricing['fixed'])) {
                                $addPrice = (int)$pricing['fixed'];
                            } elseif (isset($pricing['rate'])) {
                                $addPrice = (int)round($result['supply_price'] * floatval($pricing['rate']));
                            }

                            if ($addPrice > 0) {
                                $premiumTotal += $addPrice;
                                $premiumDetails[] = [
                                    'option_name'  => $optionName,
                                    'variant_name' => $v['variant_name'],
                                    'price'        => $addPrice,
                                ];
                            }
                            break 2;
                        }
                    }
                }
            }
        }
    }

    // 프리미엄 옵션 가격 합산
    if ($premiumTotal > 0) {
        $result['supply_price'] += $premiumTotal;
        $result['vat'] = (int)round($result['supply_price'] * 0.1);
        $result['total'] = $result['supply_price'] + $result['vat'];

        $qty = floatval($result['quantity'] ?? 1);
        $result['unit_price'] = ($qty > 0) ? (int)round($result['supply_price'] / $qty) : 0;
    }

    $result['premium_options'] = $premiumDetails;
    $result['premium_total'] = $premiumTotal;

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
