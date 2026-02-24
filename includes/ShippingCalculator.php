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
    // ===== 사이즈별 1연 = 매수 (전지 500매 × 절수) =====
    // 전지(국전지) 1연 = 500매, 각 사이즈로 절단 시 절수만큼 곱함
    const SHEETS_PER_REAM = [
        '국2절' => 1000,   // 500 × 2
        '4절'   => 2000,   // 500 × 4
        'A3'    => 2000,   // 500 × 4 (국4절 상당)
        'B4'    => 4000,   // 500 × 8 (국8절 상당)
        'A4'    => 4000,   // 500 × 8 (국8절 상당) — 0.5연=2,000매 검증됨
        'A5'    => 8000,   // 500 × 16
        'B5'    => 8000,   // 500 × 16
        'A6'    => 16000,  // 500 × 32
        'B6'    => 16000,  // 500 × 32
    ];

    // ===== 봉투 펼침면 사이즈 (mm) =====
    // 봉투 종이 사이즈(펼침면) — 접기 전 원지 크기로 면적 직접 계산
    // 면적(m²) = 가로mm/1000 × 세로mm/1000 (factor 불필요)
    const ENVELOPE_SPECS = [
        '대봉투'     => ['width' => 510, 'height' => 387],  // 대봉투 펼침면 510×387mm
        'A4자켓'     => ['width' => 262, 'height' => 238],  // 자켓형 펼침면 262×238mm
        'A4소봉투'   => ['width' => 238, 'height' => 262],  // 소봉투 펼침면 238×262mm
        '소봉투'     => ['width' => 238, 'height' => 262],  // 소봉투 펼침면 238×262mm
        '쟈켓소봉투' => ['width' => 262, 'height' => 238],  // 자켓소봉투 = 자켓형과 동일
    ];

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
    // A4, A5, A6 → A3 박스 (430×300×165mm, 실측)
    // B5, B6 → 8절 박스 (390×270×165mm, 16절특약: 4,000매/box 고정)
    // B4, A3, 4절, 국2절 → 대형 박스 (별도)
    const BOX_SPECS = [
        'A3_box' => [
            'name'   => 'A3 박스',
            'width'  => 430, 'depth' => 300, 'height' => 165,
            'weight' => 500,
            'columns' => 2,
            'sizes'  => ['A4', 'A5', 'A6'],
        ],
        '16_box' => [
            'name'   => '8절 박스',
            'width'  => 390, 'depth' => 270, 'height' => 165,
            'weight' => 500,
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
    const FALLBACK_A4_FEE = 3500;  // A4특약 (로젠 계약 요금)
    const FALLBACK_ENVELOPE_FEE = 3500;  // 대봉투특약 (로젠 계약: 500매 1박스 3,500원)

    // 전단지 규격별 로젠 특약 택배비
    const FLYER_FEE_MAP = [
        'A6' => ['per_box' => 4000, 'half_ream_fee' => 3500],  // 1연=4,000원, 0.5연=3,500원특약(A4박스)
        'A5' => ['per_box' => 6000],                             // 1연=6,000원/box
        'A4' => ['per_box' => 6000, 'half_ream_fee' => 3500],   // 1연=6,000원, 0.5연=3,500원특약(A4박스)
        'B5' => ['per_box' => 3500],                             // 16절특약 3,500원/box
        'B4' => ['per_box' => 3500],                             // 8절특약 3,500원/box
        'A3' => ['per_box' => 6000],                             // A3특약 6,000원/box
        'B6' => ['per_box' => 3500],                             // 32절 = 16절특약 귀속 3,500원/box
    ];

    // 전단지 규격별 1박스 최대 매수 (실측/영업 기준)
    const FLYER_MAX_PER_BOX = [
        'A6' => 16000,  // A3 박스에 16,000매 (1연)
        'A5' => 8000,   // A3 박스에 8,000매 (1연) — 2×2열
        'A4' => 4000,   // A3 박스에 4,000매 (1연) — 2×1열
        'B5' => 4000,   // 8절 박스에 4,000매 (16절특약)
        'B4' => 2000,   // 박스당 2,000매 → 1연(4,000)=2박스
        'A3' => 2000,   // A3 박스에 2,000매 (1연)
        'B6' => 8000,   // 8절 박스에 8,000매 (B5의 절반 크기 → 2배 수량, 16절특약 귀속)
    ];

    public static $cachedRates = null;

    // ===== 코팅 무게 가산율 =====
    const COATING_WEIGHT_FACTOR = [
        'none'       => 1.00,
        'glossy'     => 1.04,  // 유광 코팅 +4%
        'matte'      => 1.04,  // 무광 코팅 +4%
        'laminating' => 1.12,  // 라미네이팅 +12%
    ];

    // 실측: 90g 아트지 2,000매 = 165mm → 1장 = 0.0825mm → 벌크계수 = 0.0825/0.09 = 11/12
    const BULK_FACTOR = 11/12;

    /**
     * 장바구니 아이템 배열로부터 전체 배송 추정 계산
     * 
     * @param array $cartItems 장바구니 아이템 배열
     * @param string $packingMode 'bundle'(묶음배송) | 'individual'(개별포장)
     * @return array ['items' => [...], 'total_weight_g' => int, 'total_boxes' => int, 'total_weight_kg' => float]
     */
    public static function estimateFromCart(array $cartItems, string $packingMode = 'individual'): array
    {
        $results = [];
        $totalWeightG = 0;
        $totalBoxes = 0;
        $totalFee = 0;

        foreach ($cartItems as $item) {
            $estimate = self::estimateItem($item);
            $results[] = $estimate;
            $totalWeightG += $estimate['total_weight_g'];
            $totalBoxes += $estimate['boxes'];
            $totalFee += $estimate['estimated_fee'] ?? 0;
        }

        // 묶음배송: 전체 무게 합산 → 20kg 기준 박스 재분리 + 택배비 재계산
        if ($packingMode === 'bundle' && count($cartItems) > 1) {
            $totalWeightKg = $totalWeightG / 1000;
            $totalBoxes = max(1, (int)ceil($totalWeightKg / 20));
            // 묶음 시 전체 무게 기준 택배비 재계산 (개별 fee 합산 대신)
            $totalFee = self::estimateFeeByWeight($totalBoxes, round($totalWeightKg, 1));
        }

        // 택배비 라벨 생성
        $feeLabel = '';
        if ($packingMode === 'bundle' && count($cartItems) > 1) {
            $feeLabel = '묶음배송 무게별 차등';
        } elseif (count($results) === 1 && !empty($results[0]['fee_label'])) {
            $feeLabel = $results[0]['fee_label'];
        } else {
            $feeLabel = '개별 합산';
        }

        return [
            'items'          => $results,
            'total_weight_g' => $totalWeightG,
            'total_weight_kg'=> round($totalWeightG / 1000, 1),
            'total_boxes'    => $totalBoxes,
            'total_fee'      => $totalFee,
            'fee_label'      => $feeLabel,
            'packing_mode'   => $packingMode,
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
        // spec_material/spec_size 우선 (텍스트명, 예: "90g아트지", "A4")
        // MY_Fsd/PN_type은 숫자 코드("626", "821")일 수 있어 파싱 실패
        $paperCode   = $item['spec_material'] ?? $item['MY_Fsd'] ?? '';
        $sizeCode    = $item['spec_size'] ?? $item['PN_type'] ?? '';
        $coating     = self::detectCoating($item);

        // 평량 파싱 (예: "90g아트지" → 90, "스노우250g" → 250)
        $gsm = self::parseGSM($paperCode);

        // 사이즈 파싱 (예: "A4", "16절", "B5") — parseQuantity보다 먼저!
        $paperSize = self::parsePaperSize($sizeCode);

        // 수량 파싱 (사이즈 필요: 연→매 변환에 SHEETS_PER_REAM 사용)
        $quantity    = self::parseQuantity($item, $paperSize);

        // 소형 제품 (명함, 스티커, 상품권): 간이 계산
        if (self::isSmallProduct($productType)) {
            return self::estimateSmallProduct($productType, $quantity, $item);
        }

        // 봉투: 크기/gsm 기반 실제 무게 계산
        if ($productType === 'envelope') {
            return self::estimateEnvelopeItem($item, $gsm, $quantity);
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

        // 박스당 최대 매수
        $maxPerBox = 0;
        if ($sheetThicknessMM > 0) {
            $sheetsPerColumn = floor($boxSpec['height'] / $sheetThicknessMM);
            $maxPerBox = $sheetsPerColumn * $boxSpec['columns'];
        }
        // 전단지: 영업 실측 기준 maxPerBox 오버라이드
        if ($productType === 'inserted' && isset(self::FLYER_MAX_PER_BOX[$paperSize])) {
            $maxPerBox = self::FLYER_MAX_PER_BOX[$paperSize];
        }
        // B5(16절) 비전단지도 4,000매/box 고정 (로젠 계약)
        elseif ($paperSize === 'B5') {
            $maxPerBox = 4000;
        }

        // 필요 박스수
        $boxes = ($maxPerBox > 0) ? (int)ceil($quantity / $maxPerBox) : 1;

        // 박스 무게 포함 총 무게
        $boxWeightG = $boxes * $boxSpec['weight'];
        $totalWeightG = (int)round($paperWeightG + $boxWeightG);

        // 박스당 무게
        $weightPerBoxG = ($boxes > 0) ? (int)round($totalWeightG / $boxes) : $totalWeightG;

        // ✅ 로젠택배 특약 택배비 추정
        $estimatedFee = self::estimateFee($paperSize, $boxes, round($totalWeightG / 1000, 1), $productType, $quantity);
        $feeLabel = self::getFeeLabel($productType, $paperSize, $boxes, round($totalWeightG / 1000, 1), $quantity);

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
            'estimated_fee'    => $estimatedFee,
            'fee_label'        => $feeLabel,
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

        // 봉투: Type_1에서 크기/gsm 파싱하여 실제 무게 계산
        if (preg_match("/envelop/i", $type)) {
            return self::estimateEnvelopeFromOrder($type1Raw);
        }

        // 소형 제품 감지 (명함, 스티커, 상품권)
        if (preg_match("/NameCard|MerchandiseBond|sticker/i", $type)) {
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

        // 실제 매수 파싱: quantity_display에서 "(2,000매)" 추출 (가장 정확)
        $actualSheets = 0;
        $qtyDisplay = $orderData['quantity_display'] ?? '';
        if (preg_match('/[\(（]\s*([\d,]+)\s*매\s*[\)）]/u', $qtyDisplay, $m)) {
            $actualSheets = intval(str_replace(',', '', $m[1]));
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
        
        // 매수 환산: ① quantity_display 파싱 → ② SHEETS_PER_REAM 변환 → ③ fallback
        if ($actualSheets > 0) {
            $quantity = $actualSheets;
        } else {
            $sheetsPerReam = self::SHEETS_PER_REAM[$paperSize] ?? 500;
            $quantity = (int)($yeon * $sheetsPerReam);
        }
        $paperWeightG = $sheetWeight * $quantity;
        
        // 박스 계산
        $boxSpec = self::getBoxSpec($paperSize);
        $sheetThickness = ($gsm / 1000) * self::BULK_FACTOR;
        $maxPerBox = 0;
        if ($sheetThickness > 0) {
            $maxPerBox = floor($boxSpec['height'] / $sheetThickness) * $boxSpec['columns'];
        }
        // B5(16절) 비전단지: 4,000매/box 고정 (로젠 계약)
        if ($paperSize === 'B5') {
            $maxPerBox = 4000;
        }
        // 전단지: FLYER_MAX_PER_BOX 오버라이드
        $isFlyer = preg_match('/inserted/i', $type);
        if ($isFlyer && isset(self::FLYER_MAX_PER_BOX[$paperSize])) {
            $maxPerBox = self::FLYER_MAX_PER_BOX[$paperSize];
        }
        $boxes = ($maxPerBox > 0) ? (int)ceil($quantity / $maxPerBox) : (int)ceil($yeon);
        
        $boxWeightG = $boxes * $boxSpec['weight'];
        $totalWeightG = (int)round($paperWeightG + $boxWeightG);
        $totalWeightKg = round($totalWeightG / 1000, 1);

        // 택배비 추정 — 전단지는 규격별 특약, 나머지는 무게 기반
        $productTypeForFee = $isFlyer ? 'inserted' : '';
        $fee = self::estimateFee($paperSize, $boxes, $totalWeightKg, $productTypeForFee, $quantity);

        // fee_type 라벨
        $feeType = '무게기반';
        if ($isFlyer && isset(self::FLYER_FEE_MAP[$paperSize])) {
            $feeType = $paperSize . '특약';
        } elseif ($paperSize === 'B5') {
            $feeType = '16절특약';
        }

        return [
            'boxes'      => $boxes,
            'fee'        => $fee,
            'weight_kg'  => $totalWeightKg,
            'weight_per_box_kg' => ($boxes > 0) ? round($totalWeightKg / $boxes, 1) : $totalWeightKg,
            'gsm'        => $gsm,
            'paper_size' => $paperSize,
            'quantity'   => $quantity,
            'fee_type'   => $feeType,
            'calculable' => true,
        ];
    }

    /**
     * 봉투 주문의 무게/박스 추정
     * Type_1 형식: "대봉투\n대봉투330*243(120g모조)\n칼라4도(옵셋)\n500\n디자인+인쇄"
     *              "소봉투\n소봉투(100모조 220*105)\n마스터2도\n1000\n디자인+인쇄"
     */
    private static function estimateEnvelopeFromOrder(string $type1Raw): array
    {
        $lines = preg_split('/\r?\n/', trim($type1Raw));

        // 봉투 종류 감지 (1번째 줄 또는 2번째 줄)
        $envelopeType = '소봉투'; // 기본값
        $specLine = $lines[1] ?? $lines[0] ?? '';
        if (preg_match('/대봉투/u', $type1Raw)) $envelopeType = '대봉투';
        elseif (preg_match('/쟈켓소봉투|자켓소봉투/u', $type1Raw)) $envelopeType = '쟈켓소봉투';
        elseif (preg_match('/A4\s*자켓/ui', $type1Raw)) $envelopeType = 'A4자켓';
        elseif (preg_match('/A4\s*소봉투/ui', $type1Raw)) $envelopeType = 'A4소봉투';

        // gsm 파싱: "120g모조", "100모조" 등
        $gsm = ($envelopeType === '대봉투') ? 120 : 100; // 대봉투 기본 120g, 나머지 100g
        if (preg_match('/(\d+)\s*g/i', $type1Raw, $m)) {
            $gsm = intval($m[1]);
        } elseif (preg_match('/\((\d+)모조/u', $type1Raw, $m)) {
            $gsm = intval($m[1]);
        }

        // 수량 파싱: 3번째 줄 (숫자만 있는 줄)
        $quantity = 500; // 기본값
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^[\d,]+$/', $trimmed) && intval(str_replace(',', '', $trimmed)) >= 100) {
                $quantity = intval(str_replace(',', '', $trimmed));
                break;
            }
        }

        // 봉투 펼침면 사이즈로 면적 계산 (Type_1의 완성 치수가 아닌 원지 사이즈 사용)
        $spec = self::ENVELOPE_SPECS[$envelopeType] ?? self::ENVELOPE_SPECS['소봉투'];
        // 1매 무게 계산 (g)
        // 봉투 펼침면 사이즈로 면적 직접 계산 (가로×세로 = 원지 전체 면적)
        $areaM2 = ($spec['width'] / 1000) * ($spec['height'] / 1000);
        $weightPerPiece = $gsm * $areaM2; // g

        // 총 무게
        $paperWeightG = $weightPerPiece * $quantity;
        // 박스 분리: 20kg 초과 시 분리 (500매 고정 아님)
        $totalWeightKg = round($paperWeightG / 1000, 1);

        // 대봉투 특약: 3,500원/box (로젠 계약)
        $isEnvelopeSpecial = ($envelopeType === '대봉투');
        $boxes = max(1, (int)ceil($totalWeightKg / 20));
        $totalWeightG = (int)round($paperWeightG);  // 부자재(박스) 무게 미포함

        // 택배비: 대봉투는 특약, 나머지는 무게 기반
        if ($isEnvelopeSpecial) {
            $rates = self::loadRates();
            $feePerBox = $rates['logen_envelope'][0]['fee'] ?? self::FALLBACK_ENVELOPE_FEE;
            $fee = $boxes * $feePerBox;
        } else {
            $fee = self::estimateFeeByWeight($boxes, $totalWeightKg);
        }

        return [
            'boxes'      => $boxes,
            'fee'        => $fee,
            'weight_kg'  => $totalWeightKg,
            'weight_per_box_kg' => ($boxes > 0) ? round($totalWeightKg / $boxes, 1) : $totalWeightKg,
            'gsm'        => $gsm,
            'paper_size' => $envelopeType,
            'quantity'   => $quantity,
            'fee_type'   => $isEnvelopeSpecial ? '대봉투특약' : '봉투',
            'calculable' => true,
        ];
    }

    /**
     * 봉투 장바구니 아이템의 무게/박스 추정 (cart 경로)
     */
    private static function estimateEnvelopeItem(array $item, int $gsm, int $quantity): array
    {
        // 봉투 종류 감지
        $sizeCode = $item['PN_type'] ?? $item['spec_size'] ?? '';
        $envelopeType = '소봉투';
        if (preg_match('/대봉투/u', $sizeCode)) $envelopeType = '대봉투';
        elseif (preg_match('/쟈켓소봉투|자켓소봉투/u', $sizeCode)) $envelopeType = '쟈켓소봉투';
        elseif (preg_match('/A4\s*자켓/ui', $sizeCode)) $envelopeType = 'A4자켓';
        elseif (preg_match('/A4\s*소봉투/ui', $sizeCode)) $envelopeType = 'A4소봉투';

        $spec = self::ENVELOPE_SPECS[$envelopeType] ?? self::ENVELOPE_SPECS['소봉투'];

        // 대봉투 기본 120g (parseGSM이 100g 기본값을 반환했을 때 보정)
        if ($envelopeType === '대봉투' && $gsm === 100) {
            $gsm = 120;
        }

        // 1매 무게 (g): 펼침면 가로×세로 = 원지 전체 면적
        $areaM2 = ($spec['width'] / 1000) * ($spec['height'] / 1000);
        $weightPerPiece = $gsm * $areaM2;

        $paperWeightG = $weightPerPiece * $quantity;
        $totalWeightG = (int)round($paperWeightG);  // 부자재(박스) 무게 미포함
        $totalWeightKg = round($totalWeightG / 1000, 1);

        // 박스 분리: 20kg 초과 시 분리 (AGENTS.md 기준 — 모든 제품 공통)
        $boxes = max(1, (int)ceil($totalWeightKg / 20));

        // 택배비 추정: 대봉투는 특약, 나머지는 무게 기반
        $estimatedFee = self::estimateEnvelopeFee($envelopeType, $boxes, $totalWeightKg);
        $isSpecial = ($envelopeType === '대봉투');
        $feeLabel = $isSpecial ? '대봉투특약 ' . number_format(self::FALLBACK_ENVELOPE_FEE) . '원/box' : '무게별 차등';

        return [
            'product_type'    => 'envelope',
            'paper_size'      => $envelopeType,
            'gsm'             => $gsm,
            'quantity'         => $quantity,
            'coating'          => 'none',
            'paper_weight_g'   => (int)round($paperWeightG),
            'box_weight_g'     => 0,  // 부자재 무게 미포함
            'total_weight_g'   => $totalWeightG,
            'total_weight_kg'  => $totalWeightKg,
            'boxes'            => $boxes,
            'weight_per_box_kg'=> ($boxes > 0) ? round(($paperWeightG / $boxes) / 1000, 1) : round($paperWeightG / 1000, 1),
            'box_type'         => '봉투 박스',
            'max_per_box'      => 0,  // 무게 기준 분리
            'estimated_fee'    => $estimatedFee,
            'fee_label'        => $feeLabel,
            'calculable'       => true,
        ];
    }

    /**
     * 순수 무게 기반 택배비 (봉투, 대형 등)
     */
    private static function estimateFeeByWeight(int $boxes, float $totalWeightKg): int
    {
        $rates = self::loadRates();
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
     * 택배비 추정 (관리자 참고용)
     * DB shipping_rates 테이블에서 요금 조회, 실패 시 하드코딩 fallback
     */
    public static function estimateFee(string $paperSize, int $boxes, float $totalWeightKg, string $productType = '', int $quantity = 0): int
    {
        // 전단지 규격별 특약
        if ($productType === 'inserted' && isset(self::FLYER_FEE_MAP[$paperSize])) {
            $feeMap = self::FLYER_FEE_MAP[$paperSize];
            // 0.5연 특약 (A4, A6)
            if (isset($feeMap['half_ream_fee']) && $quantity > 0) {
                $sheetsPerReam = self::SHEETS_PER_REAM[$paperSize] ?? 4000;
                $reams = $quantity / $sheetsPerReam;
                if ($reams <= 0.5) {
                    return $feeMap['half_ream_fee'];
                }
            }
            return $boxes * $feeMap['per_box'];
        }

        // 봉투: 대봉투 특약, 나머지 무게별
        if ($productType === 'envelope') {
            if ($paperSize === '대봉투') {
                $rates = self::loadRates();
                $feePerBox = $rates['logen_envelope'][0]['fee'] ?? self::FALLBACK_ENVELOPE_FEE;
                return $boxes * $feePerBox;
            }
            return self::estimateFeeByWeight($boxes, $totalWeightKg);
        }

        // 비전단지 B5: 16절특약
        if (in_array($paperSize, ['B5'])) {
            $rates = self::loadRates();
            $fee16 = $rates['logen_16'][0]['fee'] ?? self::FALLBACK_16_FEE;
            return $boxes * $fee16;
        }

        // 나머지: 무게별 차등
        return self::estimateFeeByWeight($boxes, $totalWeightKg);
    }

    /**
     * 봉투 전용 택배비 추정
     * 대봉투: 3,500원/box (로젠 특약)
     * 소봉투/자켓: 무게별 차등 (≤3kg:3000, ≤10kg:3500, ≤15kg:4000, ≤20kg:5000, >20kg:6000)
     */
    private static function estimateEnvelopeFee(string $envelopeType, int $boxes, float $totalWeightKg): int
    {
        if ($envelopeType === '대봉투') {
            $rates = self::loadRates();
            $feePerBox = $rates['logen_envelope'][0]['fee'] ?? self::FALLBACK_ENVELOPE_FEE;
            return $boxes * $feePerBox;
        }
        // 소봉투/자켓: 무게별 차등
        return self::estimateFeeByWeight($boxes, $totalWeightKg);
    }

    /**
     * 택배비 요금 근거 라벨 반환 (프론트 표시용)
     */
    private static function getFeeLabel(string $productType, string $paperSize, int $boxes, float $totalWeightKg, int $quantity = 0): string
    {
        if ($productType === 'inserted' && isset(self::FLYER_FEE_MAP[$paperSize])) {
            $feeMap = self::FLYER_FEE_MAP[$paperSize];
            // 0.5연 특약 체크
            if (isset($feeMap['half_ream_fee']) && $quantity > 0) {
                $sheetsPerReam = self::SHEETS_PER_REAM[$paperSize] ?? 4000;
                if ($quantity / $sheetsPerReam <= 0.5) {
                    return $paperSize . ' 0.5연특약 ' . number_format($feeMap['half_ream_fee']) . '원';
                }
            }
            return $paperSize . '특약 ' . number_format($feeMap['per_box']) . '원/box';
        }
        if ($productType === 'envelope') {
            if ($paperSize === '대봉투') return '대봉투특약 ' . number_format(self::FALLBACK_ENVELOPE_FEE) . '원/box';
            return '무게별 차등';
        }
        return '무게별 차등';
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
        $rates['logen_envelope'] = [['max_kg' => 99, 'fee' => self::FALLBACK_ENVELOPE_FEE, 'label' => '대봉투 특약']];
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
     * @param string $paperSize 사이즈 (연→매 변환에 SHEETS_PER_REAM 참조)
     */
    private static function parseQuantity(array $item, string $paperSize = 'A4'): int
    {
        // flyer_mesu (전단지 연 단위 → 매수 변환)
        if (!empty($item['flyer_mesu'])) {
            $yeon = floatval($item['flyer_mesu']);
            $sheetsPerReam = self::SHEETS_PER_REAM[$paperSize] ?? 4000;
            if ($yeon > 0) return (int)($yeon * $sheetsPerReam);
        }
        
        // quantity_sheets (정확한 매수 — DB에 이미 계산된 값)
        if (!empty($item['quantity_sheets'])) {
            $val = intval($item['quantity_sheets']);
            if ($val > 0) return $val;
        }

        // mesu (매수 직접 — 명함/스티커 등에서 사용)
        if (!empty($item['mesu'])) {
            $val = intval(floatval($item['mesu']));
            if ($val > 0) return $val;
        }

        // MY_amount (수량 — decimal "500.00" 등)
        // ⚠️ 전단지는 MY_amount가 "연" 단위 (0.5, 1, 2, 3 등)이므로 매수 변환 필요!
        if (!empty($item['MY_amount'])) {
            $val = floatval($item['MY_amount']);
            if ($val > 0) {
                // 전단지(inserted): MY_amount ≤ 20이면 연 단위로 판단 → 매수 변환
                $productType = $item['product_type'] ?? '';
                if ($productType === 'inserted' && $val <= 20) {
                    $sheetsPerReam = self::SHEETS_PER_REAM[$paperSize] ?? 4000;
                    return (int)($val * $sheetsPerReam);
                }
                return intval($val);
            }
        }

        return 500; // 기본값
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
            'merchandisebond'
            // ⚠️ envelope 제거 — 봉투는 크기/gsm 기반 실제 계산 사용
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
