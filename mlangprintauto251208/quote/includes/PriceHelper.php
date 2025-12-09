<?php
/**
 * 가격 계산 헬퍼 함수
 * 단가 계산 시 소수점 2자리까지 허용
 */

/**
 * 단가 계산 함수
 *
 * @param float|int $supply 공급가
 * @param float|int $qty 수량
 * @return array ['price' => 계산된 단가, 'valid' => true, 'display' => 표시용 문자열]
 *
 * 로직:
 * 1. 공급가 / 수량 = 단가
 * 2. 단가를 소수점 2자리로 반올림
 * 3. 역계산 검증: 단가 × 수량 = 공급가 확인 (무한소수 방지)
 * 4. 정수면 소수점 없이, 소수면 불필요한 0 제거하여 표시
 */
function calcUnitPrice($supply, $qty) {
    // 유효성 검사
    if ($qty <= 0 || $supply <= 0) {
        return ['price' => 0, 'valid' => false, 'display' => ''];
    }

    // 단가 계산
    $unitPrice = $supply / $qty;

    // 소수점 2자리로 반올림
    $rounded = round($unitPrice, 2);

    // 역계산 검증: 단가 × 수량 = 공급가?
    // 무한소수(3.333...)의 경우 역계산 시 원래 공급가와 다름
    $recalculated = round($rounded * $qty);
    if ($recalculated != $supply) {
        // 무한소수로 정확한 복원 불가 - 단가 표시 안 함
        return ['price' => $rounded, 'valid' => false, 'display' => ''];
    }

    // 정수인 경우 소수점 제거
    if ($rounded == floor($rounded)) {
        $display = number_format(intval($rounded));
    } else {
        // 소수점 표시, 불필요한 0 제거 (3.30 → 3.3, 3.33 → 3.33)
        $formatted = number_format($rounded, 2, '.', ',');
        $display = rtrim($formatted, '0');
        $display = rtrim($display, '.');
    }

    return ['price' => $rounded, 'valid' => true, 'display' => $display];
}

/**
 * 단가 표시용 포맷 함수
 *
 * @param float|int $supply 공급가
 * @param float|int $qty 수량
 * @param string $emptyValue 단가가 유효하지 않을 때 표시할 값 (기본: "-")
 * @return string 포맷된 단가 문자열
 */
function formatUnitPrice($supply, $qty, $emptyValue = '-') {
    $result = calcUnitPrice($supply, $qty);
    return $result['valid'] ? $result['display'] : $emptyValue;
}
?>
