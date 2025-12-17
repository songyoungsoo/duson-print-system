<?php

namespace App\Pricing;

/**
 * 계산 결과를 담는 표준 데이터 전송 객체(DTO)
 * 모든 계산기는 이 객체 형식으로 결과를 반환해야 합니다.
 */
class PriceResult
{
    /**
     * @var int 공급가액
     */
    public int $supplyPrice;

    /**
     * @var int 부가세
     */
    public int $vat;

    /**
     * @var int 최종 합계 금액 (공급가 + 부가세)
     */
    public int $totalPrice;

    /**
     * @var string 계산 내역 또는 설명
     */
    public string $description;

    /**
     * @var array 계산에 사용된 상세 내역 (디버깅 또는 견적서 표시용)
     */
    public array $breakdown;

    public function __construct(int $supplyPrice, string $description = '', array $breakdown = [])
    {
        $this->supplyPrice = $supplyPrice;
        $this->vat = (int) round($supplyPrice * 0.1);
        $this->totalPrice = $this->supplyPrice + $this->vat;
        $this->description = $description;
        $this->breakdown = $breakdown;
    }
}
