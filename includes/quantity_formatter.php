<?php
/**
 * 수량 표시 포맷팅 헬퍼 함수
 *
 * 정수면 소수점 없이, 소수면 불필요한 0 제거
 * 예: 500.00 → 500, 0.50 → 0.5, 1.25 → 1.25
 *
 * @param mixed $quantity 수량 값
 * @param string $product_type 제품 타입 (inserted, namecard, envelope 등)
 * @param string|null $unit 단위 (연, 매, 권 등) - null이면 자동 판단
 * @return string 포맷팅된 수량 문자열
 */
function formatQuantity($quantity, $product_type = '', $unit = null) {
    if (empty($quantity) && $quantity !== 0 && $quantity !== '0') {
        return '';
    }

    $qty = floatval($quantity);

    // 단위 자동 판단
    if ($unit === null) {
        if ($product_type === 'inserted' || $product_type === 'leaflet') {
            $unit = '연';
        } else {
            $unit = '매';
        }
    }

    // 정수면 소수점 없이, 소수면 불필요한 0 제거
    return formatQuantityNum($qty) . $unit;
}

/**
 * 수량 값만 포맷팅 (단위 제외)
 * 정수면 소수점 없이, 소수면 불필요한 0 제거
 *
 * @param mixed $quantity 수량 값
 * @param string $product_type 제품 타입 (사용 안 함, 호환성 유지)
 * @return string 포맷팅된 수량 (단위 제외)
 */
function formatQuantityValue($quantity, $product_type = '') {
    if (empty($quantity) && $quantity !== 0 && $quantity !== '0') {
        return '';
    }

    return formatQuantityNum(floatval($quantity));
}

/**
 * 수량 숫자 포맷팅 (불필요한 소수점 제거)
 * 500.00 → 500, 0.50 → 0.5, 1.25 → 1.25
 *
 * @param mixed $num 수량 값
 * @return string 포맷된 수량
 */
function formatQuantityNum($num) {
    if (empty($num) && $num !== 0 && $num !== '0' && $num !== 0.0) {
        return '-';
    }
    if (!is_numeric($num)) {
        return '-';
    }
    $float_val = floatval($num);
    // 정수면 소수점 없이
    if (floor($float_val) == $float_val) {
        return number_format($float_val);
    }
    // 소수면 불필요한 0 제거 (0.50 → 0.5)
    return rtrim(rtrim(number_format($float_val, 2), '0'), '.');
}

/**
 * JavaScript용 수량 포맷팅 함수 생성
 *
 * @return string JavaScript 함수 코드
 */
function getQuantityFormatterJS() {
    return <<<'JS'
/**
 * 수량 숫자 포맷팅 (불필요한 소수점 제거)
 * 500.00 → 500, 0.50 → 0.5
 */
function formatQuantityNum(num) {
    if (!num && num !== 0) return '-';
    const qty = parseFloat(num);
    if (Number.isInteger(qty)) {
        return qty.toLocaleString();
    }
    // 소수점 2자리까지 표시 후 불필요한 0 제거
    return parseFloat(qty.toFixed(2)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * 수량 포맷팅 (JavaScript)
 * 정수면 소수점 없이, 소수면 불필요한 0 제거
 *
 * @param {number|string} quantity 수량
 * @param {string} productType 제품 타입
 * @param {string|null} unit 단위 (optional)
 * @returns {string} 포맷팅된 수량
 */
function formatQuantity(quantity, productType, unit) {
    if (!quantity && quantity !== 0) return '';

    const qty = parseFloat(quantity);
    const isFlyer = productType === 'inserted' || productType === 'leaflet';

    // 단위 자동 판단
    if (!unit) {
        unit = isFlyer ? '연' : '매';
    }

    return formatQuantityNum(qty) + unit;
}

/**
 * 수량 값만 포맷팅 (단위 제외)
 */
function formatQuantityValue(quantity, productType) {
    if (!quantity && quantity !== 0) return '';
    return formatQuantityNum(parseFloat(quantity));
}
JS;
}
?>
