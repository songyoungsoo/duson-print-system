<?php
/**
 * 수량 표시 포맷팅 헬퍼 함수
 *
 * 0.5연 (전단지 전용)만 소수점 표시
 * 나머지 모든 값은 정수로 표시
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

    // 0.5만 소수점 표시, 나머지는 정수
    if ($qty == 0.5) {
        return '0.5' . $unit;
    }

    return number_format(intval($qty), 0) . $unit;
}

/**
 * 수량 값만 포맷팅 (단위 제외)
 *
 * @param mixed $quantity 수량 값
 * @param string $product_type 제품 타입 (사용 안 함, 호환성 유지)
 * @return string 포맷팅된 수량 (단위 제외)
 */
function formatQuantityValue($quantity, $product_type = '') {
    if (empty($quantity) && $quantity !== 0 && $quantity !== '0') {
        return '';
    }

    $qty = floatval($quantity);

    // 0.5만 소수점 표시, 나머지는 정수
    if ($qty == 0.5) {
        return '0.5';
    }

    return number_format(intval($qty), 0);
}

/**
 * JavaScript용 수량 포맷팅 함수 생성
 *
 * @return string JavaScript 함수 코드
 */
function getQuantityFormatterJS() {
    return <<<'JS'
/**
 * 수량 포맷팅 (JavaScript)
 * 0.5만 소수점, 나머지는 정수
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

    // 0.5만 소수점, 나머지 정수
    if (qty === 0.5) {
        return '0.5' + unit;
    }

    return parseInt(qty).toLocaleString() + unit;
}

/**
 * 수량 값만 포맷팅 (단위 제외)
 */
function formatQuantityValue(quantity, productType) {
    if (!quantity && quantity !== 0) return '';

    const qty = parseFloat(quantity);

    // 0.5만 소수점, 나머지 정수
    if (qty === 0.5) {
        return '0.5';
    }

    return parseInt(qty).toLocaleString();
}
JS;
}
?>
