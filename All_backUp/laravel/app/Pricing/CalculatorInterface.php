<?php

namespace App\Pricing;

/**
 * 모든 품목별 가격 계산기가 구현해야 하는 공통 인터페이스
 */
interface CalculatorInterface
{
    /**
     * 사용자가 선택한 옵션을 기반으로 가격을 계산합니다.
     *
     * @param array $options 사용자가 선택한 옵션 배열. 예: ['size' => 'a4', 'quantity' => 1000]
     * @return PriceResult 계산 결과를 담은 표준 객체
     */
    public function calculate(array $options): PriceResult;
}
