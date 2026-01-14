<?php
/**
 * [모바일] 리플렛 가격 계산 API
 *
 * PriceCalculationService 중앙 서비스 사용 (inserted 테이블)
 * 접지/코팅/오시 추가 옵션 처리
 * 레거시 응답 형식 유지
 *
 * @migrated 2026-01-14
 */

header("Content-Type: application/json");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 중앙 서비스 로드
require_once __DIR__ . "/../../../db.php";
require_once __DIR__ . "/../../../includes/functions.php";
require_once __DIR__ . "/../../../includes/PriceCalculationService.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 수집 (GET 방식)
$params = $_GET;

// 추가 옵션 가격 받기
$additional_options_total = intval($params['premium_options_total'] ?? $params['additional_options_total'] ?? 0);
if ($additional_options_total > 0) {
    $params['additional_options_total'] = $additional_options_total;
}

// 접지/코팅/오시 옵션
$fold_type = $params['fold_type'] ?? '';
$coating_type = $params['coating_type'] ?? '';
$creasing_type = $params['creasing_type'] ?? '';

// 중앙 서비스로 기본 가격 계산 (leaflet은 inserted 테이블 사용)
$service = new PriceCalculationService($db);
$result = $service->calculate('leaflet', $params);

if ($result['success']) {
    $data = $result['data'];

    // 접지방식 추가 금액 조회
    $fold_additional_price = 0;
    if (!empty($fold_type)) {
        $fold_query = "SELECT additional_price FROM mlangprintauto_leaflet_fold WHERE fold_type='" . mysqli_real_escape_string($db, $fold_type) . "' AND is_active=1";
        $fold_result = mysqli_query($db, $fold_query);
        if ($fold_result && $fold_row = mysqli_fetch_array($fold_result)) {
            $fold_additional_price = intval($fold_row['additional_price']);
        }
    }

    // 코팅 추가 금액 조회
    $coating_additional_price = 0;
    if (!empty($coating_type)) {
        $coating_query = "SELECT base_price FROM additional_options_config WHERE option_type='" . mysqli_real_escape_string($db, $coating_type) . "' AND option_category='coating' AND is_active=1";
        $coating_result = mysqli_query($db, $coating_query);
        if ($coating_result && $coating_row = mysqli_fetch_array($coating_result)) {
            $coating_additional_price = intval($coating_row['base_price']);
        }
    }

    // 오시 추가 금액 조회
    $creasing_additional_price = 0;
    if (!empty($creasing_type)) {
        $creasing_query = "SELECT base_price FROM additional_options_config WHERE option_type='" . mysqli_real_escape_string($db, $creasing_type) . "' AND option_category='creasing' AND is_active=1";
        $creasing_result = mysqli_query($db, $creasing_query);
        if ($creasing_result && $creasing_row = mysqli_fetch_array($creasing_result)) {
            $creasing_additional_price = intval($creasing_row['base_price']);
        }
    }

    // 최종 가격 계산 (접지 + 코팅 + 오시 추가)
    $base_price = $data['base_price'];
    $design_price = $data['design_price'];
    $order_price_with_options = $base_price + $design_price + $fold_additional_price + $coating_additional_price + $creasing_additional_price + $additional_options_total;
    $vat_price = $order_price_with_options / 10;
    $total_with_vat = $order_price_with_options + $vat_price;

    $response_data = [
        'Price' => number_format($base_price),
        'DS_Price' => number_format($design_price),
        'Fold_Price' => number_format($fold_additional_price),
        'Coating_Price' => number_format($coating_additional_price),
        'Creasing_Price' => number_format($creasing_additional_price),
        'Order_Price' => number_format($order_price_with_options),
        'Additional_Options' => number_format($additional_options_total),
        'PriceForm' => $base_price,
        'DS_PriceForm' => $design_price,
        'Fold_PriceForm' => $fold_additional_price,
        'Coating_PriceForm' => $coating_additional_price,
        'Creasing_PriceForm' => $creasing_additional_price,
        'Order_PriceForm' => $order_price_with_options,
        'Additional_Options_Form' => $additional_options_total,
        'VAT_PriceForm' => $vat_price,
        'Total_PriceForm' => $total_with_vat,
        'StyleForm' => $data['StyleForm'] ?? ($params['MY_type'] ?? ''),
        'SectionForm' => $data['SectionForm'] ?? ($params['PN_type'] ?? ''),
        'QuantityForm' => $data['QuantityForm'] ?? ($params['MY_amount'] ?? ''),
        'FoldTypeForm' => $fold_type,
        'CoatingTypeForm' => $coating_type,
        'CreasingTypeForm' => $creasing_type,
        'DesignForm' => $data['DesignForm'] ?? ($params['ordertype'] ?? ''),
        'MY_amountRight' => $data['MY_amountRight'] ?? ''
    ];

    echo json_encode(['success' => true, 'data' => $response_data]);
} else {
    error_response($result['error']['message']);
}

mysqli_close($db);
