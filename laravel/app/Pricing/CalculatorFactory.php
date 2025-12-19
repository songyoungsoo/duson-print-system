<?php

namespace App\Pricing;

use InvalidArgumentException;

/**
 * 품목 키에 따라 적절한 가격 계산기 인스턴스를 생성하고 반환하는 팩토리 클래스
 */
class CalculatorFactory
{
    /**
     * @var array 품목 키와 계산기 클래스를 매핑하는 배열
     */
    private array $calculatorMap = [
        'flyer' => FlyerCalculator::class,
        'business_card' => BusinessCardCalculator::class,
        // 'poster' => PosterCalculator::class, // <-- 향후 품목 추가 방식
    ];

    /**
     * 품목 키에 해당하는 계산기를 반환합니다.
     *
     * @param string $productKey 품목을 식별하는 키 (예: 'flyer', 'roll_sticker')
     * @return CalculatorInterface
     */
    public function make(string $productKey): CalculatorInterface
    {
        if (!isset($this->calculatorMap[$productKey])) {
            throw new InvalidArgumentException("'{productKey}'에 해당하는 계산기를 찾을 수 없습니다.");
        }

        $class = $this->calculatorMap[$productKey];
        return new $class();
    }

    /**
     * 팩토리와 계산을 한번에 처리하는 편의 메서드
     *
     * @param string $productKey
     * @param array $options
     * @return PriceResult
     */
    public function calculate(string $productKey, array $options): PriceResult
    {
        $calculator = $this->make($productKey);
        return $calculator->calculate($options);
    }
}
