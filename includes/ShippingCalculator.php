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
        'A6' => ['per_box' => 6000, 'half_ream_fee' => 3500],  // 1연=6,000원(A4 1연 동일무게), 0.5연=3,500원특약(A4박스)
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

    // ===== 스티커 후지(이형지) 무게 =====
    // 스티커 = 재질 gsm + 후지 120gsm (점착면 보호 이형지)
    const STICKER_BACKING_GSM = 120;

    // ===== 스티커 재질별 gsm 매핑 (레거시 데이터 호환) =====
    // 재질명에 숫자g가 없는 구 데이터용 키워드 → gsm fallback
    // 순서 중요: '초강접'을 '강접'보다 먼저 매칭해야 함
    const STICKER_GSM_MAP = [
        '초강접' => 90,
        '강접'   => 90,
        '아트'   => 90,
        '모조'   => 80,
        '유포'   => 80,
        '은데드롱' => 25,
        '투명'   => 25,
        '크라프트' => 57,
    ];

    // ===== NCR양식지 용지 gsm =====
    // NCR지(상지/중지/하지) 공통 60gsm, 일반 양식지는 이름의 숫자가 gsm
    const NCR_GSM = 60;
    const NCR_SETS_PER_VOLUME = 50;  // 1권 = 50조

    // NCR 규격명 → 용지 사이즈 매핑
    // DB transactioncate의 Section title에서 사이즈 추출용
    const NCR_SIZE_MAP = [
        'A4'  => 'A4',   // "A4 거래명세서", "계약서(A4)", "80모조A4단면"
        'A5'  => 'A5',   // "A5"
        '16절' => 'B5',  // "16절" → B5 상당
        '32절' => 'B6',  // "32절 거래명세표" → B6 상당
        '빌지' => 'A6',  // "빌지, 영수증" → 85×190mm ≈ A6 크기
        '영수증' => 'A6', // "빌지 영수증"
        '48절' => 'A6',  // "48절 빌지, 영수증"
    ];

    // ===== 박스 그룹핑 규칙 (2026-03-09) =====
    // 소량 합포장 가능 그룹 — 이 제품들은 1박스에 혼합 가능 (총 20kg까지)
    // 예: 명함 3kg + 스티커 3kg + 상품권 3kg = 1박스
    const MIXABLE_PRODUCTS = ['namecard', 'sticker', 'merchandisebond'];

    // 별도 박스 필수 — 다른 제품과 절대 혼합 불가 (각각 독립 박스)
    const SEPARATE_BOX_PRODUCTS = ['envelope', 'inserted', 'ncrflambeau', 'littleprint', 'cadarok', 'msticker'];

    // 무조건 착불 — 선불 불가, 택배사에서 수취인에게 직접 청구
    const ALWAYS_COD_PRODUCTS = ['msticker'];

    // 박스 최대 중량 (kg) — 모든 그룹 공통
    const MAX_BOX_WEIGHT_KG = 20;

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

        // 묶음배송: 박스 그룹핑 규칙 적용 (2026-03-09)
        // - 합포장 가능(명함+스티커+상품권): 1박스에 혼합, 20kg 초과 시 분리
        // - 별도 박스(봉투/전단지/양식지/포스터/카다록/자석스티커): 각각 독립 박스
        // - 자석스티커: 무조건 착불 (택배비 0)
        $boxGroups = [];
        $hasCodItems = false;

        if ($packingMode === 'bundle' && count($cartItems) > 1) {
            // 1) 아이템을 박스 그룹으로 분류
            foreach ($results as $estimate) {
                $group = self::getBoxGroup($estimate['product_type']);
                if (!isset($boxGroups[$group])) {
                    $boxGroups[$group] = ['weight_g' => 0, 'items' => [], 'has_cod' => false];
                }
                $boxGroups[$group]['weight_g'] += $estimate['total_weight_g'];
                $boxGroups[$group]['items'][] = $estimate;
                if (self::isAlwaysCOD($estimate['product_type'])) {
                    $boxGroups[$group]['has_cod'] = true;
                    $hasCodItems = true;
                }
            }

            // 2) 그룹별 박스수/택배비 재계산
            $totalBoxes = 0;
            $totalFee = 0;
            $groupDetails = [];

            foreach ($boxGroups as $groupKey => $groupData) {
                $groupWeightKg = round($groupData['weight_g'] / 1000, 1);
                $groupBoxes = max(1, (int)ceil($groupWeightKg / self::MAX_BOX_WEIGHT_KG));

                if ($groupData['has_cod']) {
                    // 착불 제품: 택배비 0 (수취인 부담)
                    $groupFee = 0;
                } else {
                    $groupFee = self::estimateFeeByWeight($groupBoxes, $groupWeightKg);
                }

                $totalBoxes += $groupBoxes;
                $totalFee += $groupFee;

                $groupDetails[] = [
                    'group'     => $groupKey,
                    'label'     => self::getGroupLabel($groupKey),
                    'weight_kg' => $groupWeightKg,
                    'boxes'     => $groupBoxes,
                    'fee'       => $groupFee,
                    'is_cod'    => $groupData['has_cod'],
                    'item_count'=> count($groupData['items']),
                ];
            }
        }

        // 택배비 라벨 생성
        $feeLabel = '';
        if ($packingMode === 'bundle' && count($cartItems) > 1) {
            $groupCount = count($boxGroups);
            if ($groupCount === 1) {
                $feeLabel = '묶음배송 무게별 차등';
            } else {
                $feeLabel = '묶음배송 ' . $groupCount . '그룹 (' . ($hasCodItems ? '착불 포함' : '무게별 차등') . ')';
            }
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
            'box_groups'     => $boxGroups ? array_values($groupDetails ?? []) : [],
            'has_cod_items'  => $hasCodItems,
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

        // NCR양식지: NCR 60g / 일반양식지 모조gsm 기반 무게 계산
        if ($productType === 'ncrflambeau') {
            return self::estimateNcrItem($item);
        }

        // 스티커: gsm + 후지(120g) 기반 실제 무게 계산
        if ($productType === 'sticker') {
            return self::estimateStickerItem($item);
        }

        // 소형 제품 (명함, 상품권): 간이 계산
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

        // NCR양식지: NCR 60g / 일반양식지 모조gsm 기반 무게 계산
        if (preg_match('/NcrFlambeau|ncrflambeau/i', $type)) {
            return self::estimateNcrFromOrder($type1Raw, $orderData);
        }

        // 스티커: gsm + 후지(120g) 기반 실제 무게 계산
        if (preg_match("/sticker/i", $type)) {
            $material = $orderData['jong'] ?? $orderData['spec_material'] ?? $type1Raw;
            $gsm = self::parseStickerGSM($material);
            $totalGsm = $gsm + self::STICKER_BACKING_GSM;
            
            // 사이즈 파싱: garo/sero 직접 또는 Type_1에서 추출
            $garo = intval($orderData['garo'] ?? 0);
            $sero = intval($orderData['sero'] ?? 0);
            if ($garo <= 0 || $sero <= 0) {
                // Type_1 또는 spec_size에서 "50x50mm" 파싱
                $sizeStr = $orderData['spec_size'] ?? $type1Raw;
                if (preg_match('/(\d+)\s*[xX×]\s*(\d+)/', $sizeStr, $m)) {
                    $garo = intval($m[1]);
                    $sero = intval($m[2]);
                }
            }
            
            $quantity = intval($orderData['quantity_sheets'] ?? $orderData['mesu'] ?? $orderData['quantity_value'] ?? 500);
            
            if ($garo > 0 && $sero > 0) {
                $areaM2 = ($garo / 1000) * ($sero / 1000);
                $paperWeightG = $totalGsm * $areaM2 * $quantity;
                $boxes = max(1, (int)ceil($paperWeightG / 1000 / 20));
                $totalWeightKg = round($paperWeightG / 1000, 1);  // 부자재 무게 미포함
                $fee = self::estimateFeeByWeight($boxes, $totalWeightKg);
                return [
                    'boxes'     => $boxes,
                    'fee'       => $fee,
                    'weight_kg' => $totalWeightKg,
                    'fee_type'  => '스티커(후지포함)',
                    'calculable'=> true,
                ];
            }
            // 사이즈 모를 때 fallback
            return [
                'boxes'     => 1,
                'fee'       => 3000,
                'weight_kg' => 2.0,
                'fee_type'  => '스티커(미감지)',
                'calculable'=> false,
            ];
        }

        // 소형 제품 감지 (명함, 상품권)
        if (preg_match("/NameCard|MerchandiseBond/i", $type)) {
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
     * NCR양식지 주문의 무게/박스 추정 (관리자 경로)
     * Type_1 형식: "NCR 2매(100매철)\nA4 거래명세서...\n1도\n\n30\n인쇄만"
     *                "양식(100매철)\n80모조A4단면\n1도\n\n40\n인쇄만"
     *                "빌지 영수증 (85-190mm)100매철\n...\n2도\n\n20\n디자인+인쇄"
     */
    private static function estimateNcrFromOrder(string $type1Raw, array $orderData): array
    {
        $lines = preg_split('/\r?\n/', trim($type1Raw));
        $styleLine = trim($lines[0] ?? '');    // "NCR 2매(100매철)" or "양식(100매철)"
        $specLine = trim($lines[1] ?? '');      // "A4 거래명세서..." or "80모조A4단면"
        
        // 1) NCR vs 일반양식지 판단 → gsm 결정
        $isNcr = (mb_strpos($styleLine, 'NCR') !== false);
        $gsm = self::NCR_GSM; // 기본 60g
        if (!$isNcr) {
            $combinedText = $styleLine . ' ' . $specLine;
            if (preg_match('/(\d+)\s*모조/u', $combinedText, $m)) {
                $gsm = intval($m[1]);
            } elseif (preg_match('/(\d+)\s*g/i', $combinedText, $m)) {
                $gsm = intval($m[1]);
            } else {
                $gsm = 80; // 양식지 기본값 80g 모조
            }
        }
        
        // 2) 수량(권수) 파싱: Type_1의 5번째 줄 (숫자만 있는 줄)
        $volumes = 0;
        if (!empty($orderData['quantity_value'])) {
            $volumes = intval($orderData['quantity_value']);
        }
        if ($volumes <= 0) {
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (preg_match('/^[\d,]+$/', $trimmed) && intval(str_replace(',', '', $trimmed)) >= 1) {
                    $volumes = intval(str_replace(',', '', $trimmed));
                    break;
                }
            }
        }
        if ($volumes <= 0) $volumes = 10; // 기본값
        
        // 3) 겹수 파싱: NCR 2매/3매/4매 → multiplier, 양식지는 1
        $multiplier = 1; // 양식지 기본 (매철 내 매수 그대로)
        if ($isNcr) {
            if (preg_match('/([2-4])매/u', $styleLine, $m)) {
                $multiplier = intval($m[1]);
            } else {
                $multiplier = 2; // NCR 기본 2매
            }
        }
        
        // 매철 매수 파싱: "100매철" → 100, "150매철" → 150, "200매철" → 200
        $sheetsPerVolume = self::NCR_SETS_PER_VOLUME * $multiplier; // 기본 50조 × 겹수
        if (preg_match('/(\d+)매철/u', $styleLine, $m)) {
            $sheetsPerVolume = intval($m[1]);
        } elseif (!$isNcr) {
            // 양식지/거래명세표 등 매철 표기 없는 경우 기본 100매/권
            $sheetsPerVolume = 100;
        }
        
        $totalSheets = $volumes * $sheetsPerVolume;
        if ($totalSheets <= 0) $totalSheets = 500;
        
        // 4) 용지 사이즈 파싱
        $paperSize = 'A4'; // 기본값
        $combinedSpec = $styleLine . ' ' . $specLine;
        foreach (self::NCR_SIZE_MAP as $keyword => $size) {
            if (mb_strpos($combinedSpec, $keyword) !== false) {
                $paperSize = $size;
                break;
            }
        }
        
        // 5) 무게 계산
        $areaM2 = self::PAPER_AREAS[$paperSize] ?? self::PAPER_AREAS['A4'];
        $paperWeightG = $gsm * $areaM2 * $totalSheets;
        
        // 6) 박스/택배비
        $boxes = max(1, (int)ceil($paperWeightG / 1000 / 20));
        $totalWeightKg = round($paperWeightG / 1000, 1);  // 부자재 무게 미포함
        $fee = self::estimateFeeByWeight($boxes, $totalWeightKg);
        
        $feeType = $isNcr ? 'NCR' : 'NCR양식지';
        
        return [
            'boxes'      => $boxes,
            'fee'        => $fee,
            'weight_kg'  => $totalWeightKg,
            'weight_per_box_kg' => ($boxes > 0) ? round($totalWeightKg / $boxes, 1) : $totalWeightKg,
            'gsm'        => $gsm,
            'paper_size' => $paperSize,
            'quantity'   => $totalSheets,
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
        // 봉투 종류 감지 (spec_size/PN_type 텍스트 → MY_type/Section 코드 fallback)
        $sizeCode = $item['spec_size'] ?? $item['PN_type'] ?? '';
        $envelopeType = '소봉투';
        if (preg_match('/대봉투/u', $sizeCode)) {
            $envelopeType = '대봉투';
        } elseif (preg_match('/䆏켓소봉투|자켓소봉투/u', $sizeCode)) {
            $envelopeType = '䆏켓소봉투';
        } elseif (preg_match('/A4\s*자켓/ui', $sizeCode)) {
            $envelopeType = 'A4자켓';
        } elseif (preg_match('/A4\s*소봉투/ui', $sizeCode)) {
            $envelopeType = 'A4소봉투';
        }
        // MY_type/Section 코드로 대봉투 감지 (getDeliveryConfigKey와 동일 로직)
        if ($envelopeType === '소봉투') {
            $myType = $item['MY_type'] ?? '';
            $section = $item['Section'] ?? '';
            $largeSections = ['473','474','741','935','936','985','994'];
            if ($myType === '466' || in_array($section, $largeSections)) {
                $envelopeType = '대봉투';
            }
        }

        $spec = self::ENVELOPE_SPECS[$envelopeType] ?? self::ENVELOPE_SPECS['소봉투'];

        // 대봉투: parseGSM이 기본값(100g) 반환 시 → 120g로 보정 (주문 시 120g/150g 선택에 따라 실제 gsm 적용)
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
        
        // quantity (프론트엔드 장바구니에서 직접 전달하는 수량)
        if (!empty($item['quantity'])) {
            $val = intval($item['quantity']);
            if ($val > 0) return $val;
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
     * 스티커 재질명에서 gsm 파싱 (후지 제외 순수 재질 gsm)
     * 예: "아트지유광-90g" → 90, "아트유광코팅" → 90 (keyword fallback)
     */
    private static function parseStickerGSM(string $material): int
    {
        // 1) 재질명에 숫자g가 있으면 그것 사용 (새 포맷: "아트지유광-90g")
        if (preg_match('/(\d+)\s*g/i', $material, $m)) {
            return intval($m[1]);
        }
        
        // 2) 레거시 데이터: 키워드로 gsm 추정 (구 데이터에는 gsm 없음)
        foreach (self::STICKER_GSM_MAP as $keyword => $gsm) {
            if (mb_strpos($material, $keyword) !== false) {
                return $gsm;
            }
        }
        
        // 3) 기본값: 스티커 대부분 90gsm
        return 90;
    }


    /**
     * NCR양식지 무게 추정 (장바구니 경로)
     * 
     * NCR지: 상지/중지/하지 공통 60gsm
     * 일반양식지: 이름의 숫자가 gsm ("80모조"=80g, "100모조"=100g)
     * 1권 = 50조 × 겹수(2매/3매/4매)
     * 
     * @param array $item 장바구니 아이템 (spec_sides, spec_material, quantity_sheets 등)
     * @return array
     */
    private static function estimateNcrItem(array $item): array
    {
        // 1) NCR vs 일반양식지 판단 → gsm 결정
        //    spec_sides = MY_type_name = 스타일명 ("NCR 2매(100매철)", "양식(100매철)", "빌지 영수증...")
        //    spec_material = MY_Fsd_name = 규격명 ("A4 거래명세서...", "80모조A4단면", "16절")
        $specSides = $item['spec_sides'] ?? '';
        $specMaterial = $item['spec_material'] ?? '';
        $isNcr = (mb_strpos($specSides, 'NCR') !== false);
        
        $gsm = self::NCR_GSM; // 기본 60g (NCR)
        if (!$isNcr) {
            // 일반양식지: "80모조", "100모조" 등에서 gsm 추출
            $combinedText = $specSides . ' ' . $specMaterial;
            if (preg_match('/(\d+)\s*모조/u', $combinedText, $m)) {
                $gsm = intval($m[1]);
            } elseif (preg_match('/(\d+)\s*g/i', $combinedText, $m)) {
                $gsm = intval($m[1]);
            } else {
                $gsm = 80; // 양식지 기본값 80g 모조
            }
        }
        
        // 2) 총 매수 파싱: quantity_sheets가 DataAdapter에서 이미 계산됨
        $totalSheets = intval($item['quantity_sheets'] ?? 0);
        $volumes = intval($item['quantity_value'] ?? 0); // 권수
        
        if ($totalSheets <= 0 && $volumes > 0) {
            // fallback: 권수 × 매/권 기준 계산
            if ($isNcr) {
                // NCR: 권수 × 50조 × 겹수(2/3/4)
                $multiplier = 2; // 기본값
                if (preg_match('/([2-4])매/u', $specSides, $m)) {
                    $multiplier = intval($m[1]);
                }
                // 매철 파싱: "100매철" → 100
                $sheetsPerVol = self::NCR_SETS_PER_VOLUME * $multiplier;
                if (preg_match('/(\d+)매철/u', $specSides, $m)) {
                    $sheetsPerVol = intval($m[1]);
                }
                $totalSheets = $volumes * $sheetsPerVol;
            } else {
                // 양식지: 매철 파싱, 없으면 100매/권
                $sheetsPerVol = 100; // 기본 100매철
                if (preg_match('/(\d+)매철/u', $specSides, $m)) {
                    $sheetsPerVol = intval($m[1]);
                }
                $totalSheets = $volumes * $sheetsPerVol;
            }
        }
        if ($totalSheets <= 0) {
            $totalSheets = 500; // 최종 fallback
        }
        
        // 3) 용지 사이즈 파싱: spec_material에서 A4/A5/16절/32절/빌지 추출
        $paperSize = 'A4'; // 기본값
        $combinedSpec = $specSides . ' ' . $specMaterial;
        foreach (self::NCR_SIZE_MAP as $keyword => $size) {
            if (mb_strpos($combinedSpec, $keyword) !== false) {
                $paperSize = $size;
                break;
            }
        }
        
        // 4) 면적 (m²)
        $areaM2 = self::PAPER_AREAS[$paperSize] ?? self::PAPER_AREAS['A4'];
        
        // 5) 무게 계산: 총매수 × gsm × 면적
        $paperWeightG = $gsm * $areaM2 * $totalSheets;
        
        // 6) 박스 계산: 20kg 초과 시 분리
        $boxes = max(1, (int)ceil($paperWeightG / 1000 / 20));
        $totalWeightG = (int)round($paperWeightG);  // 부자재(박스) 무게 미포함
        $totalWeightKg = round($totalWeightG / 1000, 1);
        
        // 7) 택배비 추정 (무게 기반)
        $estimatedFee = self::estimateFeeByWeight($boxes, $totalWeightKg);
        
        return [
            'product_type'    => 'ncrflambeau',
            'paper_size'      => $paperSize,
            'gsm'             => $gsm,
            'quantity'         => $totalSheets,
            'quantity_volumes' => $volumes,
            'coating'          => 'none',
            'paper_weight_g'   => (int)round($paperWeightG),
            'box_weight_g'     => 0,  // 부자재 무게 미포함
            'total_weight_g'   => $totalWeightG,
            'total_weight_kg'  => $totalWeightKg,
            'boxes'            => $boxes,
            'weight_per_box_kg'=> round(($totalWeightG / $boxes) / 1000, 1),
            'box_type'         => '소형 박스',
            'max_per_box'      => 0,
            'calculable'       => true,
            'estimated_fee'    => $estimatedFee,
            'fee_label'        => 'NCR 무게별 차등',
            'is_ncr'           => $isNcr,
        ];
    }

    /**
     * 스티커 무게 추정 (재질 gsm + 후지 120gsm + 실제 면적 기반)
     * 
     * 스티커는 사이즈가 자유 입력(mm)이므로 표준 용지 규격이 아닌
     * (garo × sero) mm 로 면적을 직접 계산하는 로직.
     * 
     * @param array $item 장바구니 아이템 (jong/garo/sero/mesu 또는 spec_material/spec_size)
     * @return array
     */
    private static function estimateStickerItem(array $item): array
    {
        // 재질명에서 gsm 파싱
        $material = $item['spec_material'] ?? $item['jong'] ?? '';
        $gsm = self::parseStickerGSM($material);
        
        // 후지(이형지) 120gsm 가산 → 실제 총 gsm
        $totalGsm = $gsm + self::STICKER_BACKING_GSM;
        
        // 스티커 사이즈 (mm) 파싱
        $garo = 0;
        $sero = 0;
        
        // 우선: 직접 필드 (jong/garo/sero)
        if (!empty($item['garo'])) $garo = intval($item['garo']);
        if (!empty($item['sero'])) $sero = intval($item['sero']);
        
        // Fallback: spec_size "단50x50mm" 파싱
        if ($garo <= 0 || $sero <= 0) {
            $specSize = $item['spec_size'] ?? '';
            if (preg_match('/(\d+)\s*[xX×]\s*(\d+)/', $specSize, $m)) {
                $garo = intval($m[1]);
                $sero = intval($m[2]);
            }
        }
        
        // 수량 파싱
        $quantity = intval($item['quantity_sheets'] ?? $item['mesu'] ?? 0);
        if ($quantity <= 0) $quantity = intval($item['MY_amount'] ?? 500);
        
        // 무게 계산
        $calculable = false;
        if ($garo > 0 && $sero > 0) {
            // 면적 (m²) = (mm/1000) × (mm/1000)
            $areaM2 = ($garo / 1000) * ($sero / 1000);
            // 1매 무게(g) = 총 gsm(재질+후지) × 면적(m²)
            $weightPerPiece = $totalGsm * $areaM2;
            $paperWeightG = $weightPerPiece * $quantity;
            $calculable = true;
        } else {
            // 사이즈 모를 때 fallback (5g/매 추정)
            $paperWeightG = 5.0 * $quantity;
        }
        
        // 박스 계산: 20kg 초과 시 분리
        $boxes = max(1, (int)ceil($paperWeightG / 1000 / 20));
        $totalWeightG = (int)round($paperWeightG);  // 부자재(박스) 무게 미포함
        $totalWeightKg = round($totalWeightG / 1000, 1);
        
        // 택배비 추정 (무게 기반)
        $estimatedFee = self::estimateFeeByWeight($boxes, $totalWeightKg);
        
        return [
            'product_type'    => 'sticker',
            'paper_size'      => $garo . 'x' . $sero . 'mm',
            'gsm'             => $gsm,
            'gsm_with_backing'=> $totalGsm,
            'quantity'         => $quantity,
            'coating'          => 'none',
            'paper_weight_g'   => (int)round($paperWeightG),
            'box_weight_g'     => 0,  // 부자재 무게 미포함
            'total_weight_g'   => $totalWeightG,
            'total_weight_kg'  => $totalWeightKg,
            'boxes'            => $boxes,
            'weight_per_box_kg'=> round(($totalWeightG / $boxes) / 1000, 1),
            'box_type'         => '소형 박스',
            'max_per_box'      => 0,
            'calculable'       => $calculable,
            'estimated_fee'    => $estimatedFee,
            'fee_label'        => '무게별 차등',
        ];
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
            'namecard', 'msticker', 
            'merchandisebond'
            // ⚠️ sticker 제거 — 스티커는 gsm+후지 기반 실제 계산 사용
            // ⚠️ envelope 제거 — 봉투는 크기/gsm 기반 실제 계산 사용
        ]);
    }

    /**
     * 제품 타입으로 박스 그룹 결정
     * 
     * 합포장 가능 제품(명함/스티커/상품권)은 'mixable' 그룹으로 묶여 1박스에 혼합 가능.
     * 별도 박스 제품(봉투/전단지/양식지/포스터/카다록/자석스티커)은 각 product_type이 그룹키.
     * 같은 종류 그룹주문(예: 스티커 여러 건)은 자동으로 같은 그룹에 합산됨.
     * 
     * @param string $productType 제품 타입 코드
     * @return string 박스 그룹 키 ('mixable' 또는 제품 타입명)
     */
    public static function getBoxGroup(string $productType): string
    {
        if (in_array($productType, self::MIXABLE_PRODUCTS)) {
            return 'mixable';  // 합포장 그룹
        }
        // 별도 박스 제품은 제품 타입 자체가 그룹
        return $productType;
    }

    /**
     * 제품이 무조건 착불인지 확인
     * 자석스티커는 무조건 착불 (무게/부피 때문에 택배사에서 직접 청구)
     * 
     * @param string $productType 제품 타입 코드
     * @return bool
     */
    public static function isAlwaysCOD(string $productType): bool
    {
        return in_array($productType, self::ALWAYS_COD_PRODUCTS);
    }

    /**
     * 박스 그룹 라벨 반환 (UI 표시용)
     */
    private static function getGroupLabel(string $groupKey): string
    {
        $labels = [
            'mixable'        => '합포장 (명함+스티커+상품권)',
            'envelope'       => '봉투 (별도박스)',
            'inserted'       => '전단지 (별도박스)',
            'ncrflambeau'    => '양식지 (별도박스)',
            'littleprint'    => '포스터 (별도박스)',
            'cadarok'        => '카다록 (별도박스)',
            'msticker'       => '자석스티커 (별도박스, 착불)',
        ];
        return $labels[$groupKey] ?? $groupKey . ' (별도박스)';
    }

    /**
     * 소형 제품 간이 추정 (명함, 스티커, 상품권, 봉투)
     */
    private static function estimateSmallProduct(string $productType, int $quantity, array $item): array
    {
        $defaultWeights = [
            'namecard'        => ['per_unit_g' => 2,   'box_g' => 500,  'max_per_box' => 5000],  // 실측: 90×50mm 250~350gsm+코팅 ≈ 1.1~1.6g → 안전마진 2g
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

    // ====================================================================
    // 선불 택배비 자동 분류 시스템 (2026-03-09)
    // delivery_rules_config.php 기반 고정 요금 체계
    // ====================================================================

    /**
     * 장바구니 아이템들의 선불 택배비 분류 및 요금 계산
     * delivery_rules_config.php의 고정 요금 사용 (무게 계산 아님)
     *
     * @param array $cartItems 장바구니 아이템 배열 (OnlineOrder에서 전달)
     * @return array [
     *   'type'     => 'auto' | 'call_required' | 'cod_only' | 'mixed',
     *   'items'    => [...per-item classification],
     *   'total_fee'  => int (자동선불 합계, VAT 미포함),
     *   'total_boxes' => int,
     *   'has_cod'  => bool,
     *   'has_call_required' => bool,
     *   'call_required_products' => string[] (전화요망 제품명 목록),
     *   'notice'   => string (고객에게 보여줄 안내 메시지)
     * ]
     */
    public static function classifyPrepaid(array $cartItems): array
    {
        $configPath = dirname(__DIR__) . '/shop_admin/delivery_rules_config.php';
        $config = file_exists($configPath) ? include($configPath) : [];

        $result = [
            'type'       => 'auto',
            'items'      => [],
            'total_fee'  => 0,
            'total_boxes' => 0,
            'has_cod'    => false,
            'has_call_required' => false,
            'call_required_products' => [],
            'notice'     => '',
        ];

        foreach ($cartItems as $item) {
            $classification = self::classifyItemPrepaid($item, $config);
            $result['items'][] = $classification;

            if ($classification['prepaid_type'] === 'cod_only') {
                $result['has_cod'] = true;
            } elseif ($classification['prepaid_type'] === 'call_required') {
                $result['has_call_required'] = true;
                $result['call_required_products'][] = $classification['product_label'];
            } else {
                $result['total_fee'] += $classification['fee'];
                $result['total_boxes'] += $classification['boxes'];
            }
        }

        // 합포장 그룹(스티커+명함+상품권) 합산 무게 10kg 초과 → 전화요망
        $mixableWeight = 0;
        foreach ($cartItems as $item) {
            $pt = $item['product_type'] ?? '';
            if (in_array($pt, self::MIXABLE_PRODUCTS) || $pt === 'sticker_new') {
                $est = self::estimateFromCart([$item], 'individual');
                $mixableWeight += ($est['total_weight_kg'] ?? 0);
            }
        }
        if ($mixableWeight > 10 && !$result['has_call_required'] && !$result['has_cod']) {
            $result['type'] = 'call_required';
            $result['has_call_required'] = true;
            $result['total_fee'] = 0;
            $result['notice'] = '묶음배송 합산 약 ' . round($mixableWeight, 1) . 'kg — 택배비는 전화 문의 후 확정됩니다. (☎ 02-2632-1830)';
            return $result;
        }

        // 최종 분류 결정
        if ($result['has_cod'] && count($cartItems) === 1) {
            $result['type'] = 'cod_only';
            $result['notice'] = '자석스티커는 착불만 가능합니다.';
        } elseif ($result['has_call_required']) {
            $result['type'] = 'call_required';
            $products = implode(', ', $result['call_required_products']);
            $result['notice'] = $products . ' — 택배비는 전화 문의 후 확정됩니다. (☎ 02-2632-1830)';
        } elseif ($result['has_cod']) {
            $result['type'] = 'mixed';
            $result['notice'] = '자석스티커는 착불 별도 배송됩니다. 나머지 선불 택배비: ' . number_format($result['total_fee']) . '원';
        }

        return $result;
    }

    /**
     * 개별 아이템의 선불 분류
     */
    private static function classifyItemPrepaid(array $item, array $config): array
    {
        $productType = $item['product_type'] ?? '';
        $configKey = self::getDeliveryConfigKey($productType, $item);
        $entry = $config[$configKey] ?? $config['default'] ?? [];

        $prepaidType = $entry['prepaid_type'] ?? 'call_required';
        $label = $entry['label'] ?? $productType;

        // 착불/전화요망은 바로 반환
        if ($prepaidType !== 'auto') {
            return [
                'product_type'  => $productType,
                'config_key'    => $configKey,
                'product_label' => $label,
                'prepaid_type'  => $prepaidType,
                'fee'           => 0,
                'boxes'         => 0,
                'rule_label'    => '',
            ];
        }

        // 스티커: 사이즈 자유입력이라 무게 기반 판단
        if ($productType === 'sticker_new' || $configKey === 'sticker') {
            $estimate = self::estimateStickerItem($item);
            $weightKg = $estimate['total_weight_kg'] ?? 0;

            // 10kg 초과 → 전화요망 (관리자 개입)
            if ($weightKg > 10) {
                return [
                    'product_type'  => $productType,
                    'config_key'    => $configKey,
                    'product_label' => $label,
                    'prepaid_type'  => 'call_required',
                    'fee'           => 0,
                    'boxes'         => 0,
                    'rule_label'    => '약 ' . round($weightKg, 1) . 'kg (전화요망)',
                ];
            }

            // 10kg 이하 → 자동선불 3,000원 1박스
            return [
                'product_type'  => $productType,
                'config_key'    => $configKey,
                'product_label' => $label,
                'prepaid_type'  => 'auto',
                'fee'           => 3000,
                'boxes'         => 1,
                'rule_label'    => '1박스 (약 ' . round($weightKg, 1) . 'kg)',
            ];
        }

        // 수량 추출 → 규칙 매칭
        $quantity = self::getQuantityForConfig($productType, $item);
        $rules = $entry['rules'] ?? [];
        $matched = self::findMatchingRule($rules, $quantity);

        return [
            'product_type'  => $productType,
            'config_key'    => $configKey,
            'product_label' => $label,
            'prepaid_type'  => 'auto',
            'fee'           => $matched['price'] ?? 0,
            'boxes'         => $matched['box'] ?? 1,
            'rule_label'    => $matched['label'] ?? '',
        ];
    }

    /**
     * 제품 타입 + 스펙 → delivery_rules_config key 매핑
     *
     * 대부분 product_type 그대로 사용.
     * 전단지: 사이즈 + 평량으로 세분화 (inserted_a4, inserted_b5 등)
     * 봉투: 소봉투/대봉투 구분
     */
    public static function getDeliveryConfigKey(string $productType, array $item = []): string
    {
        // 전단지: 사이즈 + 평량 기반 분류
        if ($productType === 'inserted') {
            return self::classifyInsertedConfigKey($item);
        }

        // 봉투: 소봉투 vs 대봉투
        if ($productType === 'envelope') {
            $size = $item['spec_size'] ?? '';
            // spec_size에 '대봉투' 포함 여부 먼저 체크
            if (!empty($size) && mb_strpos($size, '대봉투') !== false) {
                return 'envelope_large';
            }
            // MY_Fsd 코드로 판단 (Section = 대봉투 하위 규격)
            $myFsd = $item['MY_Fsd'] ?? '';
            if (!empty($myFsd)) {
                $fsdType = self::envelopeFsdCodeToType($myFsd);
                if ($fsdType === '대봉투') return 'envelope_large';
            }
            // Section 코드로 판단 (Section = 대봉투 하위 규격 no)
            $section = $item['Section'] ?? '';
            if (!empty($section)) {
                $largeSections = ['473','474','741','935','936','985','994'];  // 대봉투 하위 규격 no
                if (in_array($section, $largeSections)) return 'envelope_large';
            }
            // MY_type 코드로 판단 (대봉투 카테고리 no=466)
            $myType = $item['MY_type'] ?? '';
            if ($myType === '466') return 'envelope_large';
            return 'envelope_small';  // 소봉투, A4자켓 등 → 모두 소봉투
        }

        // 스티커: sticker_new → sticker
        if ($productType === 'sticker_new') {
            return 'sticker';
        }

        // 나머지: product_type 그대로
        return $productType;
    }

    /**
     * 전단지 config key 결정
     * 표준 사이즈(A4, A5, B5, B6) + 합판인쇄(100g 이하) → 자동선불
     * 대형(B4, A3, 4절, 국2절) 또는 고평량(120g+) → 전화 요망
     */
    private static function classifyInsertedConfigKey(array $item): string
    {
        // spec_size → PN_type 코드 fallback
        $size = $item['spec_size'] ?? '';
        if (empty($size)) {
            $size = self::pnTypeCodeToSize($item['PN_type'] ?? '');
        }
        $size = self::normalizePaperSize($size);

        // spec_material → MY_Fsd 코드 fallback
        $material = $item['spec_material'] ?? '';
        if (empty($material)) {
            $material = self::myFsdCodeToMaterial($item['MY_Fsd'] ?? '');
        }
        $gsm = self::extractGsmFromSpec($material);

        // 고평량 (120g 초과) → 전화 요망
        if ($gsm > 120) {
            return 'inserted_large';
        }

        // 사이즈 매핑 (합판 90g 기준)
        $sizeMap = [
            'A3' => 'inserted_a3',
            'A4' => 'inserted_a4',
            'A5' => 'inserted_a5',
            'B4' => 'inserted_b4',
            'B5' => 'inserted_b5',
            'B6' => 'inserted_b6',
        ];

        return $sizeMap[$size] ?? 'inserted_large';
    }

    /**
     * 용지 사이즈 정규화
     * 'A4 (210x297)' → 'A4', 'B5(16절)182x257' → 'B5', '16절' → 'B5' 등
     */
    private static function normalizePaperSize(string $size): string
    {
        // 절수 변환
        $map = ['16절' => 'B5', '32절' => 'B6', '8절' => 'B4', '4절' => '4절', '국2절' => '국2절'];
        if (isset($map[$size])) return $map[$size];

        // 'A4 (210x297)', 'B5(16절)182x257' 등에서 앞부분 추출
        if (preg_match('/^(A[3-5]|B[4-6])/', $size, $m)) {
            return $m[1];
        }

        // 절수가 문자열 안에 있는 경우: 'B5(16절)' 등
        if (preg_match('/(\d+)절/', $size, $m)) {
            $jolMap = ['16' => 'B5', '32' => 'B6', '8' => 'B4', '4' => '4절', '2' => '국2절'];
            return $jolMap[$m[1]] ?? $size;
        }

        return $size;
    }

    /**
     * spec_material에서 gsm 추출
     * 예: '100g 아트지' → 100, '80g 모조' → 80, '250g 아트지' → 250
     */
    private static function extractGsmFromSpec(string $material): int
    {
        if (preg_match('/(\d+)g/', $material, $m)) {
            return (int)$m[1];
        }
        // gsm 불명 — 합판인쇄 기본(100g)으로 간주 (자동선불 허용)
        return 100;
    }

    /**
     * PN_type DB 코드(no) → 사이즈 문자열 변환
     * mlangprintauto_transactioncate: no=821 → 'A4', no=818 → 'B5' 등
     */
    private static function pnTypeCodeToSize(string $code): string
    {
        $map = [
            '818' => 'B5',   // B5(16절)182x257
            '820' => 'B6',   // B6(32절)127x182
            '821' => 'A4',   // A4 (210x297)
            '822' => 'A5',   // A5(147x210)
            '823' => 'B4',   // B4(8절) 257x367
            '824' => 'A3',   // A3 (297x423)
            '825' => '4절',  // 4절(367x517)
            '826' => '국2절', // 국2절 423x597
        ];
        return $map[$code] ?? '';
    }

    /**
     * MY_Fsd DB 코드(no) → 재질명 변환 (GSM 추출용)
     * mlangprintauto_transactioncate: no=626 → '90g아트지' 등
     */
    private static function myFsdCodeToMaterial(string $code): string
    {
        $map = [
            '626' => '90g아트지',       // 합판전단
            '807' => '80g모조',         // 복사용지
            '808' => '100g모조',
            '809' => '120g모조',
            '943' => '150g모조',
            '714' => '120g아트지',      // 독판인쇄
            '715' => '150g아트지',
            '716' => '180g아트지',
            '717' => '200g아트지',
            '806' => '250g아트지',
            '924' => '300g아트지',
        ];
        return $map[$code] ?? '';
    }

    /**
     * 봉투 MY_Fsd DB 코드(no) → 소봉투/대봉투 타입 변환
     */
    private static function envelopeFsdCodeToType(string $code): string
    {
        $large = ['473','474','935','936','985','994'];  // 대봉투 코드들
        if (in_array($code, $large)) {
            return '대봉투';
        }
        return '소봉투';  // 나머지는 모두 소봉투
    }

    /**
     * 아이템에서 config 매칭용 수량 추출
     *
     * - NCR양식지: quantity_value (권수)
     * - 전단지: quantity_sheets (매수) — 연수×sheets_per_ream은 프론트에서 변환
     * - 기타: quantity_sheets → mesu → MY_amount 순 fallback
     */
    private static function getQuantityForConfig(string $productType, array $item): int
    {
        // NCR: 권수 사용
        if ($productType === 'ncrflambeau') {
            return (int)($item['quantity_value'] ?? $item['MY_amount'] ?? 0);
        }

        // 매수 우선
        $sheets = (int)($item['quantity_sheets'] ?? 0);
        if ($sheets > 0) return $sheets;

        // fallback: mesu (스티커)
        $mesu = (int)($item['mesu'] ?? 0);
        if ($mesu > 0) return $mesu;

        // 전단지: MY_amount는 연수 → 매수로 변환 필요
        $amount = (float)($item['MY_amount'] ?? 0);
        if ($productType === 'inserted' && $amount > 0) {
            $sheetsPerReam = self::getSheetsPerReam($item);
            return (int)($amount * $sheetsPerReam);
        }

        // fallback: quantity 필드 (명함, 봉투, 포스터 등 매수 단위 제품)
        $qty = (int)($item['quantity'] ?? 0);
        if ($qty > 0) return $qty;

        return (int)$amount;
    }
    /**
     * 전단지 1연당 매수 (PN_type 사이즈 기준)
     * A4: 4,000매, A5: 8,000매, B5(16절): 8,000매, B6(32절): 16,000매
     */
    private static function getSheetsPerReam(array $item): int
    {
        $size = $item['spec_size'] ?? '';
        if (empty($size)) {
            $size = self::pnTypeCodeToSize($item['PN_type'] ?? '');
        }
        $size = self::normalizePaperSize($size);

        $map = [
            'A4' => 4000,
            'A5' => 8000,
            'B5' => 8000,
            'B6' => 16000,
            'B4' => 4000,   // 8절: 1연=4,000매
            'A3' => 2000,
        ];
        return $map[$size] ?? 4000;  // 기본 A4 기준
    }

    /**
     * 수량에 맞는 규칙 찾기
     */
    private static function findMatchingRule(array $rules, int $quantity): array
    {
        foreach ($rules as $rule) {
            if ($quantity >= $rule['min'] && $quantity <= $rule['max']) {
                return $rule;
            }
        }
        // 매칭 안 되면 마지막 규칙 (최대 구간)
        return !empty($rules) ? end($rules) : ['price' => 0, 'box' => 0, 'label' => ''];
    }
}
