<?php
/**
 * @endpoint POST /api/quote/calculate_price.php
 * @param string product       제품 타입 (필수)
 * @param array  params        제품별 계산 파라미터 (필수)
 * @param bool   save_temp     true면 admin_quotation_temp에 저장 (선택)
 * @return {success, payload: QuoteItemPayload, raw_price, ?item_no}
 */

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function sendJsonResponse($data) {
    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

try {
    if (empty($_SESSION['admin_logged_in'])) {
        sendJsonResponse(['success' => false, 'message' => '관리자 로그인이 필요합니다.']);
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/PriceCalculationService.php';

    $adapterBasePath = $_SERVER['DOCUMENT_ROOT'] . '/admin/mlangprintauto/quote/includes';
    require_once $adapterBasePath . '/QuoteItemPayload.php';
    require_once $adapterBasePath . '/QuoteAdapterInterface.php';
    require_once $adapterBasePath . '/QuoteAdapterFactory.php';

    if (!$db) {
        throw new Exception('데이터베이스 연결에 실패했습니다.');
    }
    mysqli_set_charset($db, "utf8");

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception('잘못된 JSON 요청입니다.');
        }
    } else {
        $input = array_merge($_GET, $_POST);
    }

    $productType = $input['product'] ?? $input['product_type'] ?? '';
    if (empty($productType)) {
        throw new Exception('제품 타입(product)을 지정해주세요.');
    }

    if (!QuoteAdapterFactory::supports($productType)) {
        throw new Exception(
            "지원하지 않는 제품입니다: '{$productType}'. 지원: " .
            implode(', ', QuoteAdapterFactory::getProductTypes())
        );
    }

    $params = $input['params'] ?? $input;
    unset($params['product'], $params['product_type'], $params['save_temp']);

    $adapter = QuoteAdapterFactory::create($productType);

    $service = new PriceCalculationService($db);
    $pcsProductType = ($adapter->getProductType() === 'sticker') ? 'sticker_new' : $adapter->getProductType();

    $priceResult = $service->calculate($pcsProductType, $params);

    if (!($priceResult['success'] ?? false)) {
        $errorMsg = $priceResult['error']['message'] ?? '가격 계산에 실패했습니다.';
        sendJsonResponse(['success' => false, 'message' => $errorMsg, 'raw_price' => $priceResult]);
    }

    $calcParams = $params;
    if (isset($priceResult['data'])) {
        foreach (['StyleForm', 'SectionForm', 'QuantityForm', 'DesignForm', 'MY_amountRight', 'quantity_sheets'] as $echoField) {
            if (isset($priceResult['data'][$echoField])) {
                $calcParams[$echoField] = $priceResult['data'][$echoField];
            }
        }
    }

    $payload = $adapter->normalize($calcParams, $priceResult);

    $errors = $payload->validate();
    if (!empty($errors)) {
        sendJsonResponse([
            'success' => false,
            'message' => '정규화 검증 실패: ' . implode(', ', $errors),
            'raw_price' => $priceResult
        ]);
    }

    $response = [
        'success' => true,
        'payload' => $payload->toArray(),
        'raw_price' => $priceResult
    ];

    $saveTemp = !empty($input['save_temp']);
    if ($saveTemp) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/mlangprintauto/quote/includes/AdminQuoteManager.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/mlangprintauto/quote/includes/PriceHelper.php';

        $manager = new AdminQuoteManager($db);
        $adminSessionId = session_id();
        $payloadArray = $payload->toArray();

        $calcItem = [
            'is_manual' => false,
            'product_type' => $payloadArray['product_type'],
            'specification' => $payloadArray['specification'],
            'unit_price' => $payloadArray['unit_price'],
            'qty_sheets' => $payloadArray['qty_sheets'] ?? 0,
            'quantity_display' => $payloadArray['quantity_display'],
            'st_price' => $payloadArray['supply_price'],
            'st_price_vat' => $payloadArray['total_price'],
            'additional_options_total' => 0,
        ];

        $rawParams = $payloadArray['raw_params'] ?? [];
        foreach (['jong', 'garo', 'sero', 'mesu', 'domusong', 'uhyung',
                   'MY_type', 'MY_Fsd', 'PN_type', 'MY_amount', 'POtype',
                   'ordertype', 'Section', 'spec_type', 'spec_material',
                   'spec_size', 'spec_sides', 'spec_design'] as $paramKey) {
            $calcItem[$paramKey] = $rawParams[$paramKey] ?? ($params[$paramKey] ?? '');
        }

        $options = $payloadArray['options'] ?? [];
        $calcItem['coating_enabled'] = intval($options['coating_enabled'] ?? 0);
        $calcItem['coating_type'] = $options['coating_type'] ?? '';
        $calcItem['coating_price'] = intval($options['coating_price'] ?? 0);
        $calcItem['folding_enabled'] = intval($options['folding_enabled'] ?? 0);
        $calcItem['folding_type'] = $options['folding_type'] ?? '';
        $calcItem['folding_price'] = intval($options['folding_price'] ?? 0);
        $calcItem['creasing_enabled'] = intval($options['creasing_enabled'] ?? 0);
        $calcItem['creasing_lines'] = intval($options['creasing_lines'] ?? 0);
        $calcItem['creasing_price'] = intval($options['creasing_price'] ?? 0);
        $calcItem['additional_options_total'] = intval($options['premium_options_total'] ?? $options['additional_options_total'] ?? 0);

        $itemNo = $manager->addTempItem($adminSessionId, $calcItem);
        $response['item_no'] = $itemNo;
        $response['saved'] = true;
    }

    sendJsonResponse($response);

} catch (Exception $e) {
    sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $t) {
    sendJsonResponse(['success' => false, 'message' => '서버 오류: ' . $t->getMessage()]);
}

if (isset($db) && $db) {
    mysqli_close($db);
}

ob_end_flush();
