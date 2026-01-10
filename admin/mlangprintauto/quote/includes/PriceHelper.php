<?php
/**
 * 가격 조회 헬퍼 - 계산 금지 원칙
 *
 * 핵심 규칙:
 * - 모든 금액은 DB에서 조회 (계산 금지)
 * - 스티커만 예외: 단가 x 수량 = 공급가액
 * - VAT는 저장 직전에만 계산: 공급가액 x 0.1
 */
class PriceHelper
{
    private $db;

    /**
     * 제품 유형별 한글명 매핑
     */
    private static $productTypeNames = [
        'sticker' => '스티커',
        'namecard' => '명함',
        'inserted' => '전단지',
        'leaflet' => '리플렛',
        'envelope' => '봉투',
        'cadarok' => '카다록',
        'littleprint' => '포스터',
        'merchandisebond' => '상품권',
        'ncrflambeau' => 'NCR양식',
        'msticker' => '자석스티커',
    ];

    /**
     * 단위 매핑
     */
    private static $unitMap = [
        'sticker' => '매',
        'namecard' => '매',
        'inserted' => '연',
        'leaflet' => '연',
        'envelope' => '매',
        'cadarok' => '부',
        'littleprint' => '장',
        'merchandisebond' => '매',
        'ncrflambeau' => '권',
        'msticker' => '매',
    ];

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * 제품 유형 한글명 반환
     */
    public static function getProductTypeName(string $productType): string
    {
        return self::$productTypeNames[$productType] ?? '기타';
    }

    /**
     * 제품 유형별 기본 단위 반환
     */
    public static function getDefaultUnit(string $productType): string
    {
        return self::$unitMap[$productType] ?? '개';
    }

    /**
     * 계산기/장바구니 품목 → 견적 품목 형식 변환
     *
     * @param array $calcItem 계산기/장바구니 데이터 (shop_temp/admin_quotation_temp 형식)
     * @return array 견적 품목 형식
     */
    public function formatCalculatorItem(array $calcItem): array
    {
        $productType = $calcItem['product_type'] ?? '';

        // 품명 결정
        $productName = self::getProductTypeName($productType);

        // 규격/사양 포맷
        $spec = $this->formatSpecification($calcItem);

        // 수량/단위 추출
        $quantityInfo = $this->extractQuantityInfo($calcItem);

        // 공급가액: DB에서 그대로 조회 (st_price = 공급가액)
        $supplyPrice = intval($calcItem['st_price'] ?? 0);

        // 단가 결정 (스티커만 계산, 나머지는 역산 표시용)
        $unitPrice = $this->calculateUnitPrice($calcItem, $supplyPrice);

        // 전단지/리플렛 연 단위 표시
        $quantityDisplay = $quantityInfo['display'];
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $quantityDisplay = $this->formatLeafletQuantity($calcItem);
        }

        return [
            'product_name' => $productName,
            'product_type' => $productType,
            'specification' => $spec,
            'quantity' => $quantityInfo['value'],
            'unit' => $quantityInfo['unit'],
            'quantity_display' => $quantityDisplay,
            'unit_price' => $unitPrice,
            'supply_price' => $supplyPrice,
            'source_data' => $calcItem
        ];
    }

    /**
     * 규격/사양 포맷팅
     */
    private function formatSpecification(array $item): string
    {
        $productType = $item['product_type'] ?? '';
        $parts = [];

        switch ($productType) {
            case 'sticker':
            case 'msticker':
                if (!empty($item['jong'])) {
                    $parts[] = '재질: ' . $item['jong'];
                }
                if (!empty($item['garo']) && !empty($item['sero'])) {
                    $parts[] = '크기: ' . $item['garo'] . 'mm x ' . $item['sero'] . 'mm';
                }
                if (!empty($item['domusong'])) {
                    // domusong에서 숫자 제거하고 모양만 표시
                    $shape = preg_replace('/^\d+\s*/', '', $item['domusong']);
                    if (!empty($shape)) {
                        $parts[] = '모양: ' . $shape;
                    }
                }
                break;

            case 'inserted':
            case 'leaflet':
                if (!empty($item['spec_type'])) {
                    $parts[] = $item['spec_type'];
                }
                if (!empty($item['spec_size'])) {
                    $parts[] = $item['spec_size'];
                }
                if (!empty($item['spec_material'])) {
                    $parts[] = $item['spec_material'];
                }
                if (!empty($item['spec_sides'])) {
                    $parts[] = $item['spec_sides'];
                }
                break;

            case 'namecard':
                if (!empty($item['spec_type'])) {
                    $parts[] = $item['spec_type'];
                }
                if (!empty($item['spec_material'])) {
                    $parts[] = $item['spec_material'];
                }
                if (!empty($item['spec_sides'])) {
                    $parts[] = $item['spec_sides'];
                }
                break;

            case 'envelope':
                if (!empty($item['spec_type'])) {
                    $parts[] = $item['spec_type'];
                }
                if (!empty($item['spec_material'])) {
                    $parts[] = $item['spec_material'];
                }
                break;

            default:
                // 범용 포맷
                if (!empty($item['spec_type'])) {
                    $parts[] = $item['spec_type'];
                }
                if (!empty($item['spec_material'])) {
                    $parts[] = $item['spec_material'];
                }
                if (!empty($item['spec_size'])) {
                    $parts[] = $item['spec_size'];
                }
                if (!empty($item['spec_sides'])) {
                    $parts[] = $item['spec_sides'];
                }
                break;
        }

        // 옵션 추가 (규격에 포함, 별도 줄 아님)
        $options = $this->formatOptions($item);
        if (!empty($options)) {
            $parts[] = $options;
        }

        return implode(' / ', array_filter($parts));
    }

    /**
     * 옵션 포맷팅 (별도 줄 금지, 규격에 포함)
     */
    private function formatOptions(array $item): string
    {
        $options = [];

        if (!empty($item['coating_enabled']) && $item['coating_enabled'] == 1) {
            $coatingType = $item['coating_type'] ?? '코팅';
            $options[] = $coatingType;
        }

        if (!empty($item['folding_enabled']) && $item['folding_enabled'] == 1) {
            $foldingType = $item['folding_type'] ?? '접지';
            $options[] = $foldingType;
        }

        if (!empty($item['creasing_enabled']) && $item['creasing_enabled'] == 1) {
            $creasingLines = intval($item['creasing_lines'] ?? 1);
            $options[] = '오시 ' . $creasingLines . '줄';
        }

        return !empty($options) ? '옵션: ' . implode(', ', $options) : '';
    }

    /**
     * 수량 정보 추출
     */
    private function extractQuantityInfo(array $item): array
    {
        $productType = $item['product_type'] ?? '';
        $unit = self::getDefaultUnit($productType);

        switch ($productType) {
            case 'sticker':
            case 'msticker':
                $value = floatval($item['mesu'] ?? 0);
                $display = number_format($value) . '매';
                break;

            case 'inserted':
            case 'leaflet':
                // 연 단위 (MY_amount가 연, mesu가 매)
                $reams = floatval($item['MY_amount'] ?? 0);
                if ($reams > 0) {
                    $value = $reams;
                    $unit = '연';
                    $display = number_format($reams, $reams == intval($reams) ? 0 : 1) . '연';
                } else {
                    $value = floatval($item['mesu'] ?? 0);
                    $unit = '매';
                    $display = number_format($value) . '매';
                }
                break;

            case 'namecard':
                $value = floatval($item['MY_amount'] ?? $item['mesu'] ?? 500);
                $display = number_format($value) . '매';
                break;

            case 'envelope':
                $value = floatval($item['MY_amount'] ?? $item['mesu'] ?? 1000);
                $display = number_format($value) . '매';
                break;

            case 'ncrflambeau':
                $value = floatval($item['MY_amount'] ?? 10);
                $unit = '권';
                $display = number_format($value) . '권';
                break;

            case 'cadarok':
                $value = floatval($item['MY_amount'] ?? $item['mesu'] ?? 100);
                $unit = '부';
                $display = number_format($value) . '부';
                break;

            default:
                $value = floatval($item['MY_amount'] ?? $item['mesu'] ?? 1);
                $display = number_format($value) . $unit;
                break;
        }

        return [
            'value' => $value,
            'unit' => $unit,
            'display' => $display
        ];
    }

    /**
     * 단가 계산 (스티커만 수학적 계산, 나머지는 역산 표시용)
     *
     * @param array $calcItem 계산기 데이터
     * @param int $supplyPrice 공급가액
     * @return float 단가
     */
    private function calculateUnitPrice(array $calcItem, int $supplyPrice): float
    {
        $productType = $calcItem['product_type'] ?? '';

        // 스티커만 단가 x 수량 계산 허용
        if ($productType === 'sticker') {
            $mesu = intval($calcItem['mesu'] ?? 0);
            return $mesu > 0 ? round($supplyPrice / $mesu, 2) : 0;
        }

        // 나머지 제품: 역산 (표시용, 실제 계산에 사용하지 않음)
        $quantityInfo = $this->extractQuantityInfo($calcItem);
        $quantity = $quantityInfo['value'];

        return $quantity > 0 ? round($supplyPrice / $quantity, 2) : 0;
    }

    /**
     * 전단지 연 단위 표시 (견적서 전용 규칙)
     * 형식: "0.5연 (2,000매)"
     */
    private function formatLeafletQuantity(array $item): string
    {
        $reams = floatval($item['MY_amount'] ?? 0);
        $sheets = intval($item['mesu'] ?? 0);

        if ($reams > 0) {
            $display = number_format($reams, $reams == intval($reams) ? 0 : 1) . '연';
            if ($sheets > 0) {
                $display .= ' (' . number_format($sheets) . '매)';
            }
            return $display;
        }

        return number_format($sheets) . '매';
    }

    /**
     * 공급가액 합계 → VAT/총액 계산
     * (견적 저장 직전에만 호출)
     *
     * @param array $items 품목 배열 (각 품목에 supply_price 필드 필요)
     * @return array ['supply_total', 'vat_total', 'grand_total']
     */
    public function calculateTotals(array $items): array
    {
        $supplyTotal = 0;
        foreach ($items as $item) {
            $supplyTotal += intval($item['supply_price'] ?? 0);
        }

        $vatTotal = intval(round($supplyTotal * 0.1));
        $grandTotal = $supplyTotal + $vatTotal;

        return [
            'supply_total' => $supplyTotal,
            'vat_total' => $vatTotal,
            'grand_total' => $grandTotal
        ];
    }

    /**
     * 수동 입력 품목 포맷팅
     *
     * @param array $manualItem 수동 입력 데이터
     * @return array 견적 품목 형식
     */
    public function formatManualItem(array $manualItem): array
    {
        $productName = trim($manualItem['product_name'] ?? '');
        $specification = trim($manualItem['specification'] ?? '');
        $quantity = floatval($manualItem['quantity'] ?? 1);
        $unit = trim($manualItem['unit'] ?? '개');
        $supplyPrice = intval($manualItem['supply_price'] ?? 0);

        // 단가 역산 (표시용)
        $unitPrice = $quantity > 0 ? round($supplyPrice / $quantity, 2) : 0;

        // 수량 표시
        $quantityDisplay = number_format($quantity, $quantity == intval($quantity) ? 0 : 1) . $unit;

        return [
            'product_name' => $productName,
            'product_type' => '',
            'specification' => $specification,
            'quantity' => $quantity,
            'unit' => $unit,
            'quantity_display' => $quantityDisplay,
            'unit_price' => $unitPrice,
            'supply_price' => $supplyPrice,
            'source_data' => null
        ];
    }
}
