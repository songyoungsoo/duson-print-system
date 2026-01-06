<?php
/**
 * 통합 가격 계산 API
 *
 * POST /api/calculate_price.php
 *
 * 11개 품목의 가격을 계산합니다.
 * - formula: 스티커, 자석스티커 (수식 기반)
 * - table: 명함, 전단지, 기타 (DB 테이블 조회)
 *
 * @author Claude Code
 * @version 2.0
 * @date 2026-01-06
 */

session_start();
require_once __DIR__ . '/../../../includes/safe_json_response.php';
require_once __DIR__ . '/../includes/CalculatorConfig.php';
require_once __DIR__ . '/../../../includes/DataAdapter.php';
require_once __DIR__ . '/../../../db.php';

header('Content-Type: application/json; charset=utf-8');

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_json_response(false, null, 'POST 요청만 허용됩니다.');
}

// 제품 타입 검증
$productType = $_POST['product_type'] ?? '';

if (empty($productType)) {
    safe_json_response(false, null, '제품 타입이 필요합니다.');
}

if (!CalculatorConfig::isValidProduct($productType)) {
    safe_json_response(false, null, '유효하지 않은 제품입니다.');
}

// 가격 계산 타입 확인
$calcType = CalculatorConfig::getPriceCalculationType($productType);

try {
    if ($calcType === 'formula') {
        // 수식 기반 계산 (스티커, 자석스티커)
        $result = calculateFormulaPrice($productType, $_POST, $db);
    } else {
        // 테이블 조회 기반 계산 (명함, 전단지 등)
        $result = calculateTablePrice($productType, $_POST, $db);
    }

    // 성공 응답
    safe_json_response(true, $result, '가격 계산 완료');

} catch (Exception $e) {
    error_log("가격 계산 오류 ($productType): " . $e->getMessage());
    safe_json_response(false, null, $e->getMessage());
}

/**
 * 수식 기반 가격 계산 (스티커, 자석스티커)
 */
function calculateFormulaPrice($productType, $data, $db) {
    // 필수 필드 검증
    $required = ['jong', 'domusong', 'garo', 'sero', 'mesu', 'uhyung'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("필수 필드 누락: $field");
        }
    }

    $jong = $data['jong'];
    $domusong = $data['domusong'];
    $garo = floatval($data['garo']);
    $sero = floatval($data['sero']);
    $mesu = intval($data['mesu']);
    $uhyung = intval($data['uhyung']);

    // 가격 계산 (기존 스티커 로직)
    // (가로+4) × (세로+4) × 수량 × 요율
    $area = ($garo + 4) * ($sero + 4);
    $areaPerSheet = $area / 10000; // cm²로 변환

    // DB에서 요율 조회 (shop_d1~d4 테이블)
    $rateTable = 'shop_d1'; // 기본 요율 테이블
    $query = "SELECT * FROM $rateTable WHERE mesu <= ? ORDER BY mesu DESC LIMIT 1";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $mesu);
    mysqli_stmt_execute($stmt);
    $rateResult = mysqli_stmt_get_result($stmt);
    $rateRow = mysqli_fetch_assoc($rateResult);

    if (!$rateRow) {
        throw new Exception('가격 요율을 찾을 수 없습니다.');
    }

    // 기본 가격 계산
    $rate = floatval($rateRow['rate'] ?? 10); // 기본 요율
    $basePrice = $areaPerSheet * $mesu * $rate;

    // 도무송 추가 비용
    $domusongCost = 0;
    if ($domusong !== '0사각') {
        $domusongCost = $mesu * 5; // 예: 사각이 아니면 매당 5원 추가
    }

    // 디자인 비용
    $designCost = 0;
    if ($uhyung == 1) {
        $designCost = 30000; // 디자인 기본 비용
    }

    // 총 공급가
    $supplyPrice = intval($basePrice + $domusongCost + $designCost);

    // VAT 포함가
    $totalPrice = intval($supplyPrice * 1.1);

    // 표준 필드 생성 (DataAdapter 사용)
    $adapterInput = [
        'product_type' => $productType,
        'jong' => $jong,
        'garo' => $garo,
        'sero' => $sero,
        'mesu' => $mesu,
        'domusong' => $domusong,
        'uhyung' => $uhyung,
        'st_price' => $supplyPrice,
        'st_price_vat' => $totalPrice
    ];

    $standardData = DataAdapter::legacyToStandard($adapterInput, $productType);

    return [
        'supply_price' => $supplyPrice,
        'total_price' => $totalPrice,
        'form_data' => array_merge($data, [
            'product_type' => $productType,
            'supply_price' => $supplyPrice,
            'total_price' => $totalPrice,
            'calculated_price' => $supplyPrice,
            'calculated_vat_price' => $totalPrice,
            'quantity_display' => $standardData['quantity_display'] ?? number_format($mesu)
        ])
    ];
}

/**
 * 테이블 조회 기반 가격 계산 (명함, 전단지 등)
 */
function calculateTablePrice($productType, $data, $db) {
    $tableName = CalculatorConfig::getDBTable($productType);

    if (!$tableName) {
        throw new Exception('DB 테이블을 찾을 수 없습니다.');
    }

    // 제품별 쿼리 조건 구성
    switch ($productType) {
        case 'namecard':
            $result = calculateNamecardPrice($data, $db, $tableName);
            break;

        case 'inserted':
            $result = calculateInsertedPrice($data, $db, $tableName);
            break;

        case 'envelope':
            $result = calculateEnvelopePrice($data, $db, $tableName);
            break;

        default:
            $result = calculateGenericPrice($productType, $data, $db, $tableName);
            break;
    }

    return $result;
}

/**
 * 명함 가격 계산
 */
function calculateNamecardPrice($data, $db, $tableName) {
    // 필수 필드 검증
    $required = ['MY_type', 'Section', 'POtype', 'MY_amount'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("필수 필드 누락: $field");
        }
    }

    $MY_type = $data['MY_type'];
    $Section = $data['Section'];
    $POtype = $data['POtype'];
    $MY_amount = $data['MY_amount'];

    // DB 조회
    $query = "SELECT Order_PriceForm, Total_PriceForm
              FROM $tableName
              WHERE MY_type = ? AND Section = ? AND POtype = ? AND MY_amount = ?
              LIMIT 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $MY_type, $Section, $POtype, $MY_amount);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        throw new Exception('해당 조건의 가격을 찾을 수 없습니다.');
    }

    $supplyPrice = intval($row['Order_PriceForm']);
    $totalPrice = intval($row['Total_PriceForm']);

    // 표준 필드 생성
    $adapterInput = array_merge($data, [
        'product_type' => 'namecard',
        'st_price' => $supplyPrice,
        'st_price_vat' => $totalPrice
    ]);

    $standardData = DataAdapter::legacyToStandard($adapterInput, 'namecard');

    return [
        'supply_price' => $supplyPrice,
        'total_price' => $totalPrice,
        'form_data' => array_merge($data, [
            'product_type' => 'namecard',
            'supply_price' => $supplyPrice,
            'total_price' => $totalPrice,
            'calculated_price' => $supplyPrice,
            'calculated_vat_price' => $totalPrice,
            'quantity_display' => $standardData['quantity_display'] ?? ($MY_amount . '매')
        ])
    ];
}

/**
 * 전단지 가격 계산
 */
function calculateInsertedPrice($data, $db, $tableName) {
    // 필수 필드 검증
    $required = ['MY_type', 'PN_type', 'MY_Fsd', 'MY_amount', 'POtype'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("필수 필드 누락: $field");
        }
    }

    $MY_type = $data['MY_type'];
    $PN_type = $data['PN_type'];
    $MY_Fsd = $data['MY_Fsd'];
    $MY_amount = $data['MY_amount'];
    $POtype = $data['POtype'];

    // DB 조회
    $query = "SELECT Order_PriceForm, Total_PriceForm
              FROM $tableName
              WHERE MY_type = ? AND PN_type = ? AND MY_Fsd = ?
                AND MY_amount = ? AND POtype = ?
              LIMIT 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        throw new Exception('해당 조건의 가격을 찾을 수 없습니다.');
    }

    $supplyPrice = intval($row['Order_PriceForm']);
    $totalPrice = intval($row['Total_PriceForm']);

    // 전단지는 연 단위이므로 매수 계산 필요
    $mesu = intval(floatval($MY_amount) * 500); // 1연 = 500매

    // 표준 필드 생성
    $adapterInput = array_merge($data, [
        'product_type' => 'inserted',
        'mesu' => $mesu,
        'st_price' => $supplyPrice,
        'st_price_vat' => $totalPrice
    ]);

    $standardData = DataAdapter::legacyToStandard($adapterInput, 'inserted');

    return [
        'supply_price' => $supplyPrice,
        'total_price' => $totalPrice,
        'form_data' => array_merge($data, [
            'product_type' => 'inserted',
            'mesu' => $mesu,
            'supply_price' => $supplyPrice,
            'total_price' => $totalPrice,
            'calculated_price' => $supplyPrice,
            'calculated_vat_price' => $totalPrice,
            'quantity_display' => $standardData['quantity_display'] ?? ($MY_amount . '연 (' . number_format($mesu) . '매)')
        ])
    ];
}

/**
 * 봉투 가격 계산
 */
function calculateEnvelopePrice($data, $db, $tableName) {
    // 필수 필드 검증
    $required = ['MY_type', 'Section', 'POtype', 'MY_amount'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("필수 필드 누락: $field");
        }
    }

    $MY_type = $data['MY_type'];
    $Section = $data['Section'];
    $POtype = $data['POtype'];
    $MY_amount = $data['MY_amount'];

    // DB 조회
    $query = "SELECT Order_PriceForm, Total_PriceForm
              FROM $tableName
              WHERE MY_type = ? AND Section = ? AND POtype = ? AND MY_amount = ?
              LIMIT 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $MY_type, $Section, $POtype, $MY_amount);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        throw new Exception('해당 조건의 가격을 찾을 수 없습니다.');
    }

    $supplyPrice = intval($row['Order_PriceForm']);
    $totalPrice = intval($row['Total_PriceForm']);

    // 표준 필드 생성
    $adapterInput = array_merge($data, [
        'product_type' => 'envelope',
        'st_price' => $supplyPrice,
        'st_price_vat' => $totalPrice
    ]);

    $standardData = DataAdapter::legacyToStandard($adapterInput, 'envelope');

    return [
        'supply_price' => $supplyPrice,
        'total_price' => $totalPrice,
        'form_data' => array_merge($data, [
            'product_type' => 'envelope',
            'supply_price' => $supplyPrice,
            'total_price' => $totalPrice,
            'calculated_price' => $supplyPrice,
            'calculated_vat_price' => $totalPrice,
            'quantity_display' => $standardData['quantity_display'] ?? ($MY_amount . '매')
        ])
    ];
}

/**
 * 일반 제품 가격 계산 (기본 패턴)
 */
function calculateGenericPrice($productType, $data, $db, $tableName) {
    // 최소한 MY_type과 MY_amount는 필요
    if (empty($data['MY_type']) || empty($data['MY_amount'])) {
        throw new Exception('종류와 수량을 선택해주세요.');
    }

    $MY_type = $data['MY_type'];
    $MY_amount = $data['MY_amount'];

    // 간단한 조회
    $query = "SELECT Order_PriceForm, Total_PriceForm
              FROM $tableName
              WHERE MY_type = ? AND MY_amount = ?
              LIMIT 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $MY_type, $MY_amount);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        throw new Exception('해당 조건의 가격을 찾을 수 없습니다.');
    }

    $supplyPrice = intval($row['Order_PriceForm']);
    $totalPrice = intval($row['Total_PriceForm']);

    $unit = CalculatorConfig::getUnit($productType);

    return [
        'supply_price' => $supplyPrice,
        'total_price' => $totalPrice,
        'form_data' => array_merge($data, [
            'product_type' => $productType,
            'supply_price' => $supplyPrice,
            'total_price' => $totalPrice,
            'calculated_price' => $supplyPrice,
            'calculated_vat_price' => $totalPrice,
            'quantity_display' => $MY_amount . $unit
        ])
    ];
}

mysqli_close($db);
?>
