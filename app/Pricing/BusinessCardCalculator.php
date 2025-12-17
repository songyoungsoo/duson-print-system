<?php

namespace App\Pricing;

/**
 * 명함 가격 계산기
 * - '단순 가산형' 계산 방식의 예시
 * - 기본료에 선택한 후가공 옵션들의 가격을 더해나가는 구조
 */
class BusinessCardCalculator implements CalculatorInterface
{
    // === 실제 애플리케이션에서는 이 데이터를 DB 또는 설정 파일에서 가져와야 합니다. ===

    // 용지/수량별 기본 단가표
    private const BASE_PRICES = [
        'standard' => [ // 일반지
            100 => 10000,
            200 => 12000,
            500 => 15000,
        ],
        'premium' => [ // 고급지
            100 => 15000,
            200 => 18000,
            500 => 22000,
        ]
    ];

    // 후가공 옵션 단가표 (수량 100매 기준)
    private const OPTION_PRICES = [
        'corner_rounding' => 3000,
        'uv_coating' => 5000,
        'gold_foil' => 10000, // 금박
    ];
    // ==============================================================================


    public function calculate(array $options): PriceResult
    {
        // 1. 옵션 값 추출 및 기본값 설정
        $paperType = $options['paper_type'] ?? 'standard'; // 용지 종류
        $quantity = (int)($options['quantity'] ?? 100);    // 수량

        // 후가공 옵션
        $hasCornerRounding = (bool)($options['corner_rounding'] ?? false);
        $hasUvCoating = (bool)($options['uv_coating'] ?? false);
        $hasGoldFoil = (bool)($options['gold_foil'] ?? false);

        $breakdown = [];

        // 2. 기본 가격 조회
        $basePrice = self::BASE_PRICES[$paperType][$quantity] ?? 0;
        if ($basePrice === 0) {
            return new PriceResult(0, "선택하신 용지와 수량의 견적을 찾을 수 없습니다.", $options);
        }
        $breakdown['기본가격'] = $basePrice;

        // 3. 옵션 가격 누적
        $optionPrice = 0;
        if ($hasCornerRounding) {
            $price = self::OPTION_PRICES['corner_rounding'];
            $optionPrice += $price;
            $breakdown['귀도리'] = $price;
        }
        if ($hasUvCoating) {
            $price = self::OPTION_PRICES['uv_coating'];
            $optionPrice += $price;
            $breakdown['UV코팅'] = $price;
        }
        if ($hasGoldFoil) {
            $price = self::OPTION_PRICES['gold_foil'];
            $optionPrice += $price;
            $breakdown['금박'] = $price;
        }
        
        // 4. 공급가액 합산
        $supplyPrice = $basePrice + $optionPrice;
        
        $description = sprintf(
            "명함 %s / %d매",
            $paperType,
            $quantity
        );

        return new PriceResult($supplyPrice, $description, $breakdown);
    }
}
