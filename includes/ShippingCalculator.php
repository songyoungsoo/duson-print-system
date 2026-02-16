<?php
/**
 * ShippingCalculator.php — 배송 무게/박스 계산 공통 모듈
 * 
 * 용도: 주문 품목의 용지 평량·사이즈·매수로 무게→박스수 추정
 * 사용처: 
 *   - 고객 주문 페이지 (택배 선불 시 무게/박스 표시)
 *   - 관리자 로젠택배 관리 (택배비 추정 참고)
 *   - 로젠택배 엑셀 내보내기
 * 
 * ⚠️ 추정값입니다. 실제 택배비는 관리자가 전화 확인 후 확정합니다.
 * 
 * @since 2026-02-16
 */

class ShippingCalculator
{
    // ===== 용지 규격별 1매 면적 (m²) =====
    const PAPER_AREAS = [
        'A3'  => 0.297 * 0.420,   // 0.12474
        'A4'  => 0.210 * 0.297,   // 0.06237
        'A5'  => 0.148 * 0.210,   // 0.03108
        'A6'  => 0.105 * 0.148,   // 0.01554
        'B4'  => 0.257 * 0.364,   // 0.09355 (8절)
        'B5'  => 0.182 * 0.257,   // 0.04677 (16절)
        'B6'  => 0.128 * 0.182,   // 0.02330 (32절)
        '4절' => 0.394 * 0.545,   // 0.21473
        '국2절' => 0.440 * 0.615, // 0.27060
    ];

    // ===== 규격 → 박스 규격 매핑 =====
    // A4, A5, A6 → A3 박스 (430×300×160mm)
    // B5, B6 → 16절 박스 (400×275×160mm)
    // B4, A3, 4절, 국2절 → 대형 박스 (별도)
    const BOX_SPECS = [
        'A3_box' => [
            'name'   => 'A3 박스',
            'width'  => 430, 'depth' => 300, 'height' => 160, // mm
            'weight' => 1500, // 박스 자체 무게 (g) ≈ 1.5kg
            'columns' => 2,   // A4 2열 배치 (210×2=420 ≤ 430)
            'sizes'  => ['A4', 'A5', 'A6'],
        ],
        '16_box' => [
            'name'   => '16절 박스',
            'width'  => 400, 'depth' => 275, 'height' => 160,
            'weight' => 1200, // 박스 자체 무게 (g) ≈ 1.2kg
            'columns' => 2,
            'sizes'  => ['B5', 'B6'],
        ],
        'large_box' => [
            'name'   => '대형 박스',
            'width'  => 560, 'depth' => 410, 'height' => 200,
            'weight' => 2000,
            'columns' => 1,
            'sizes'  => ['B4', 'A3', '4절', '국2절'],
        ],
    ];

    // ===== 요금 하드코딩 fallback (DB 조회 실패 시) =====
    const FALLBACK_LOGEN_RATES = [
        ['max_kg' => 3,  'fee' => 3000],
        ['max_kg' => 10, 'fee' => 3500],
        ['max_kg' => 15, 'fee' => 4000],
        ['max_kg' => 20, 'fee' => 5000],
        ['max_kg' => 23, 'fee' => 6000],
    ];
    const FALLBACK_16_FEE = 3500;

    public static $cachedRates = null;

    // ===== 코팅 무게 가산율 =====
    const COATING_WEIGHT_FACTOR = [
        'none'       => 1.00,
        'glossy'     => 1.04,  // 유광 코팅 +4%
        'matte'      => 1.04,  // 무광 코팅 +4%
        'laminating' => 1.12,  // 라미네이팅 +12%
    ];

    // ===== 1장 두께 (mm), 평량 기준 =====
    // 두께 ≈ 평량(gsm) / 1000 × 1.2 (벌크 계수)
    const BULK_FACTOR = 1.2;

    /**
     * 장바구니 아이템 배열로부터 전체 배송 추정 계산
     * 
     * @param array $cartItems 장바구니 아이템 배열
     * @return array ['items' => [...], 'total_weight_g' => int, 'total_boxes' => int, 'total_weight_kg' => float]
     */
    public static function estimateFromCart(array $cartItems): array
    {
        $results = [];
        $totalWeightG = 0;
        $totalBoxes = 0;

        foreach ($cartItems as $item) {
            $estimate = self::estimateItem($item);
            $results[] = $estimate;
            $totalWeightG += $estimate['total_weight_g'];
            $totalBoxes += $estimate['boxes'];
        }

        return [
            'items'          => $results,
            'total_weight_g' => $totalWeightG,
            'total_weight_kg'=> round($totalWeightG / 1000, 1),
            'total_boxes'    => $totalBoxes,
            'is_estimate'    => true,
        ];
    }

    /**
     * 단일 품목의 무게/박스 추정
     * 
     * @param array $item ['product_type', 'MY_Fsd'(용지), 'PN_type'(사이즈), 'MY_amount'(수량), ...]
     * @return array
     */
    public static function estimateItem(array $item): array
    {
        $productType = $item['product_type'] ?? '';
        $paperCode   = $item['MY_Fsd'] ?? $item['spec_material'] ?? '';
        $sizeCode    = $item['PN_type'] ?? $item['spec_size'] ?? '';
        $quantity    = self::parseQuantity($item);
        $coating     = self::detectCoating($item);

        // 평량 파싱 (예: "90g아트지" → 90, "스노우250g" → 250)
        $gsm = self::parseGSM($paperCode);

        // 사이즈 파싱 (예: "A4", "16절", "B5")
        $paperSize = self::parsePaperSize($sizeCode);

        // 소형 제품 (명함, 스티커, 상품권, 봉투 등): 간이 계산
        if (self::isSmallProduct($productType)) {
            return self::estimateSmallProduct($productType, $quantity, $item);
        }

        // 면적 (m²)
        $areaM2 = self::PAPER_AREAS[$paperSize] ?? self::PAPER_AREAS['A4'];

        // 1매 무게 (g)
        $sheetWeightG = $gsm * $areaM2;

        // 코팅 가산
        $coatingFactor = self::COATING_WEIGHT_FACTOR[$coating] ?? 1.00;
        $sheetWeightG *= $coatingFactor;

        // 총 종이 무게 (g)
        $paperWeightG = $sheetWeightG * $quantity;

        // 박스 규격 결정
        $boxSpec = self::getBoxSpec($paperSize);

        // 1장 두께 (mm)
        $sheetThicknessMM = ($gsm / 1000) * self::BULK_FACTOR;

        // 박스당 최대 매수: 박스높이 / 1장두께 × 열수
        $maxPerBox = 0;
        if ($sheetThicknessMM > 0) {
            $sheetsPerColumn = floor($boxSpec['height'] / $sheetThicknessMM);
            $maxPerBox = $sheetsPerColumn * $boxSpec['columns'];
        }

        // 필요 박스수
        $boxes = ($maxPerBox > 0) ? (int)ceil($quantity / $maxPerBox) : 1;

        // 박스 무게 포함 총 무게
        $boxWeightG = $boxes * $boxSpec['weight'];
        $totalWeightG = (int)round($paperWeightG + $boxWeightG);

        // 박스당 무게
        $weightPerBoxG = ($boxes > 0) ? (int)round($totalWeightG / $boxes) : $totalWeightG;

        return [
            'product_type'    => $productType,
            'paper_size'      => $paperSize,
            'gsm'             => $gsm,
            'quantity'         => $quantity,
            'coating'          => $coating,
            'paper_weight_g'   => (int)round($paperWeightG),
            'box_weight_g'     => $boxWeightG,
            'total_weight_g'   => $totalWeightG,
            'total_weight_kg'  => round($totalWeightG / 1000, 1),
            'boxes'            => $boxes,
            'weight_per_box_kg'=> round($weightPerBoxG / 1000, 1),
            'box_type'         => $boxSpec['name'],
            'max_per_box'      => $maxPerBox,
            'calculable'       => true,
        ];
    }

    /**
     * 관리자용: 주문 DB 데이터로 추정 (post_list74.php 대체)
     * 
     * @param array $orderData DB row (Type, Type_1, quantity_value 포함)
     * @return array ['boxes' => int, 'fee' => int, 'weight_kg' => float]
     */
    public static function estimateFromOrder(array $orderData): array
    {
        $type1Raw = $orderData['Type_1'] ?? '';
        $type = $orderData['Type'] ?? '';

        // 소형 제품 감지
        if (preg_match("/NameCard|MerchandiseBond|sticker|envelop/i", $type)) {
            return [
                'boxes'     => 1,
                'fee'       => 3000,
                'weight_kg' => 2.0,
                'fee_type'  => '소형',
                'calculable'=> false,
            ];
        }

        // Type_1에서 JSON 파싱 시도
        $specData = [];
        if (!empty($type1Raw) && substr(trim($type1Raw), 0, 1) === '{') {
            $decoded = json_decode($type1Raw, true);
            if ($decoded) $specData = $decoded;
        }

        // 규격 감지
        $paperSize = '';
        if (preg_match('/16절|B5/i', $type1Raw)) $paperSize = 'B5';
        elseif (preg_match('/32절|B6/i', $type1Raw)) $paperSize = 'B6';
        elseif (preg_match('/8절|B4/i', $type1Raw)) $paperSize = 'B4';
        elseif (preg_match('/A3/i', $type1Raw)) $paperSize = 'A3';
        elseif (preg_match('/A4/i', $type1Raw)) $paperSize = 'A4';
        elseif (preg_match('/A5/i', $type1Raw)) $paperSize = 'A5';
        elseif (preg_match('/A6/i', $type1Raw)) $paperSize = 'A6';

        if (empty($paperSize)) {
            // 규격 미감지 → 기본값
            return [
                'boxes'     => 1,
                'fee'       => 3000,
                'weight_kg' => 2.0,
                'fee_type'  => '미감지',
                'calculable'=> false,
            ];
        }

        // 연수 감지
        $yeon = 1;
        if (!empty($orderData['quantity_value']) && floatval($orderData['quantity_value']) > 0) {
            $yeon = floatval($orderData['quantity_value']);
        }

        // 평량 감지 (Type_1 또는 spec_material에서)
        $gsm = 0;
        $materialStr = $specData['spec_material'] ?? $type1Raw;
        if (preg_match('/(\d+)\s*g/i', $materialStr, $m)) {
            $gsm = intval($m[1]);
        }
        if ($gsm <= 0) $gsm = 100; // 기본 100gsm

        // 무게 계산
        $area = self::PAPER_AREAS[$paperSize] ?? self::PAPER_AREAS['A4'];
        $sheetWeight = $gsm * $area; // 1매 무게 (g)
        
        // 1연 = 500매 (전단지 기준), 매수 환산
        $quantity = $yeon * 500;
        $paperWeightG = $sheetWeight * $quantity;
        
        // 박스 계산
        $boxSpec = self::getBoxSpec($paperSize);
        $sheetThickness = ($gsm / 1000) * self::BULK_FACTOR;
        $maxPerBox = 0;
        if ($sheetThickness > 0) {
            $maxPerBox = floor($boxSpec['height'] / $sheetThickness) * $boxSpec['columns'];
        }
        $boxes = ($maxPerBox > 0) ? (int)ceil($quantity / $maxPerBox) : (int)ceil($yeon);
        
        $boxWeightG = $boxes * $boxSpec['weight'];
        $totalWeightG = (int)round($paperWeightG + $boxWeightG);
        $totalWeightKg = round($totalWeightG / 1000, 1);

        // 택배비 추정
        $fee = self::estimateFee($paperSize, $boxes, $totalWeightKg);

        return [
            'boxes'      => $boxes,
            'fee'        => $fee,
            'weight_kg'  => $totalWeightKg,
            'weight_per_box_kg' => ($boxes > 0) ? round($totalWeightKg / $boxes, 1) : $totalWeightKg,
            'gsm'        => $gsm,
            'paper_size' => $paperSize,
            'quantity'   => $quantity,
            'fee_type'   => ($paperSize === 'B5' || $paperSize === 'B6') ? '16절특약' : 'A4특약',
            'calculable' => true,
        ];
    }

    /**
     * 택배비 추정 (관리자 참고용)
     * DB shipping_rates 테이블에서 요금 조회, 실패 시 하드코딩 fallback
     */
    public static function estimateFee(string $paperSize, int $boxes, float $totalWeightKg): int
    {
        $rates = self::loadRates();

        if (in_array($paperSize, ['B5', 'B6'])) {
            $fee16 = $rates['logen_16'][0]['fee'] ?? self::FALLBACK_16_FEE;
            return $boxes * $fee16;
        }

        $weightPerBox = ($boxes > 0) ? $totalWeightKg / $boxes : $totalWeightKg;
        $weightRates = $rates['logen_weight'] ?? [];

        $feePerBox = 3000;
        foreach ($weightRates as $rate) {
            if ($weightPerBox <= $rate['max_kg']) {
                $feePerBox = $rate['fee'];
                break;
            }
        }

        return $boxes * $feePerBox;
    }

    /**
     * DB에서 요금표 로드 (캐싱, fallback 포함)
     * @return array ['logen_weight' => [...], 'logen_16' => [...]]
     */
    public static function loadRates($db = null): array
    {
        if (self::$cachedRates !== null) {
            return self::$cachedRates;
        }

        $rates = ['logen_weight' => [], 'logen_16' => []];

        try {
            if (!$db) {
                require_once __DIR__ . '/../db.php';
                global $connect;
                $db = $connect ?? null;
            }

            if ($db) {
                $sql = "SELECT rate_group, label, max_kg, fee FROM shipping_rates WHERE is_active = 1 ORDER BY rate_group, sort_order";
                $result = mysqli_query($db, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $rates[$row['rate_group']][] = [
                            'max_kg' => floatval($row['max_kg']),
                            'fee'    => intval($row['fee']),
                            'label'  => $row['label'],
                        ];
                    }
                    self::$cachedRates = $rates;
                    return $rates;
                }
            }
        } catch (\Exception $e) {
            // DB 실패 → fallback
        }

        // fallback: 하드코딩 값
        $rates['logen_weight'] = self::FALLBACK_LOGEN_RATES;
        $rates['logen_16'] = [['max_kg' => 99, 'fee' => self::FALLBACK_16_FEE, 'label' => '16절 고정']];
        self::$cachedRates = $rates;
        return $rates;
    }

    /**
     * 현재 적용 중인 요금표 반환 (관리자 화면 표시용)
     */
    public static function getRatesForDisplay($db = null): array
    {
        $rates = self::loadRates($db);
        return $rates;
    }

    // ===== Private Helpers =====

    /**
     * 수량 파싱: MY_amount, flyer_mesu, mesu 등에서 매수 추출
     */
    private static function parseQuantity(array $item): int
    {
        // flyer_mesu (전단지 연 단위 → 매수 변환)
        if (!empty($item['flyer_mesu'])) {
            $yeon = floatval($item['flyer_mesu']);
            if ($yeon > 0) return (int)($yeon * 500); // 1연 = 500매
        }
        
        // MY_amount (일반 수량)
        if (!empty($item['MY_amount'])) {
            $val = intval(preg_replace('/[^0-9]/', '', $item['MY_amount']));
            if ($val > 0) return $val;
        }

        // mesu (매수 직접)
        if (!empty($item['mesu'])) {
            $val = intval(preg_replace('/[^0-9]/', '', $item['mesu']));
            if ($val > 0) return $val;
        }

        return 500; // 기본 1연(500매)
    }

    /**
     * 평량(gsm) 파싱: "90g아트지" → 90, "스노우250g" → 250
     */
    private static function parseGSM(string $paperCode): int
    {
        if (preg_match('/(\d+)\s*g/i', $paperCode, $m)) {
            return intval($m[1]);
        }
        // 평량 못 찾으면 제품 유형별 기본값
        return 100;
    }

    /**
     * 사이즈 파싱: "A4", "16절", "B5" 등
     */
    private static function parsePaperSize(string $sizeCode): string
    {
        if (preg_match('/16절|B5/i', $sizeCode)) return 'B5';
        if (preg_match('/32절|B6/i', $sizeCode)) return 'B6';
        if (preg_match('/8절|B4/i', $sizeCode)) return 'B4';
        if (preg_match('/4절/i', $sizeCode)) return '4절';
        if (preg_match('/국2절/i', $sizeCode)) return '국2절';
        if (preg_match('/A3/i', $sizeCode)) return 'A3';
        if (preg_match('/A4/i', $sizeCode)) return 'A4';
        if (preg_match('/A5/i', $sizeCode)) return 'A5';
        if (preg_match('/A6/i', $sizeCode)) return 'A6';
        return 'A4'; // 기본값
    }

    /**
     * 코팅 감지
     */
    private static function detectCoating(array $item): string
    {
        $coating = $item['coating'] ?? $item['POtype'] ?? '';
        if (preg_match('/라미/i', $coating)) return 'laminating';
        if (preg_match('/유광/i', $coating)) return 'glossy';
        if (preg_match('/무광/i', $coating)) return 'matte';
        return 'none';
    }

    /**
     * 소형 제품 여부
     */
    private static function isSmallProduct(string $productType): bool
    {
        return in_array($productType, [
            'namecard', 'sticker', 'msticker', 
            'envelope', 'merchandisebond'
        ]);
    }

    /**
     * 소형 제품 간이 추정 (명함, 스티커, 상품권, 봉투)
     */
    private static function estimateSmallProduct(string $productType, int $quantity, array $item): array
    {
        $defaultWeights = [
            'namecard'        => ['per_unit_g' => 3,   'box_g' => 500,  'max_per_box' => 5000],
            'sticker'         => ['per_unit_g' => 5,   'box_g' => 500,  'max_per_box' => 3000],
            'msticker'        => ['per_unit_g' => 15,  'box_g' => 500,  'max_per_box' => 1000],
            'envelope'        => ['per_unit_g' => 10,  'box_g' => 800,  'max_per_box' => 500],
            'merchandisebond' => ['per_unit_g' => 3,   'box_g' => 500,  'max_per_box' => 5000],
        ];

        $spec = $defaultWeights[$productType] ?? $defaultWeights['namecard'];

        $paperWeightG = $quantity * $spec['per_unit_g'];
        $boxes = max(1, (int)ceil($quantity / $spec['max_per_box']));
        $boxWeightG = $boxes * $spec['box_g'];
        $totalWeightG = $paperWeightG + $boxWeightG;

        return [
            'product_type'    => $productType,
            'paper_size'      => '-',
            'gsm'             => 0,
            'quantity'         => $quantity,
            'coating'          => 'none',
            'paper_weight_g'   => $paperWeightG,
            'box_weight_g'     => $boxWeightG,
            'total_weight_g'   => $totalWeightG,
            'total_weight_kg'  => round($totalWeightG / 1000, 1),
            'boxes'            => $boxes,
            'weight_per_box_kg'=> round(($totalWeightG / $boxes) / 1000, 1),
            'box_type'         => '소형 박스',
            'max_per_box'      => $spec['max_per_box'],
            'calculable'       => false, // 소형은 추정 정확도 낮음
        ];
    }

    /**
     * 사이즈에 맞는 박스 규격 반환
     */
    private static function getBoxSpec(string $paperSize): array
    {
        foreach (self::BOX_SPECS as $spec) {
            if (in_array($paperSize, $spec['sizes'])) {
                return $spec;
            }
        }
        return self::BOX_SPECS['A3_box']; // 기본값
    }
}
