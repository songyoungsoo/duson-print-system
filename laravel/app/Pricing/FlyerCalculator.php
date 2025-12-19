<?php

namespace App\Pricing;

/**
 * 전단지 가격 계산기
 * - '조합 조회형' 계산 방식의 대표 예시
 * - 사용자가 선택한 옵션의 조합에 맞는 가격을 DB 테이블에서 직접 찾아 반환
 */
class FlyerCalculator implements CalculatorInterface
{
    // === 실제 애플리케이션에서는 이 데이터를 DB에서 쿼리해야 합니다. ===
    // SELECT price FROM price_flyers WHERE size = ? AND paper = ? AND print_side = ? AND quantity_ream = ?
    private const PRICE_TABLE = [
        // A4, 아트지150g, 단면
        'a4' => [
            'art150' => [
                'single_sided' => [
                    0.5 => ['sheets' => 2000, 'price' => 49000],
                    1.0 => ['sheets' => 4000, 'price' => 80000],
                    2.0 => ['sheets' => 8000, 'price' => 120000],
                ],
                'double_sided' => [
                    0.5 => ['sheets' => 2000, 'price' => 55000],
                    1.0 => ['sheets' => 4000, 'price' => 90000],
                    2.0 => ['sheets' => 8000, 'price' => 140000],
                ]
            ]
        ],
        // A5, 스노우120g, 단면
        'a5' => [
            'snow120' => [
                'single_sided' => [
                    1.0 => ['sheets' => 8000, 'price' => 75000],
                    2.0 => ['sheets' => 16000, 'price' => 110000],
                ]
            ]
        ]
    ];
    // ==============================================================================

    public function calculate(array $options): PriceResult
    {
        // 1. 옵션 값 추출 및 기본값 설정
        $size = $options['size'] ?? 'a4';
        $paper = $options['paper'] ?? 'art150';
        $printSide = $options['print_side'] ?? 'single_sided';
        $ream = (float)($options['ream'] ?? 0.5);

        // 2. DB 가격표에서 가격 조회 (시뮬레이션)
        $priceData = self::PRICE_TABLE[$size][$paper][$printSide][$ream] ?? null;

        if ($priceData === null) {
            // 해당하는 가격 정보가 없을 경우 0원 또는 예외 처리
            // 여기서는 0원을 반환
            return new PriceResult(0, "해당 옵션의 견적을 찾을 수 없습니다.", $options);
        }

        $supplyPrice = $priceData['price'];
        $sheets = $priceData['sheets'];

        // 3. '전단지' 규칙에 따라 설명 생성 (단가 제외)
        $description = sprintf(
            "전단지 %s / %s / %s / %.1f연 (%s매)",
            $size,
            $paper,
            $printSide,
            $ream,
            number_format($sheets)
        );

        $breakdown = [
            '기본공급가' => $supplyPrice
        ];

        return new PriceResult($supplyPrice, $description, $breakdown);
    }
}
