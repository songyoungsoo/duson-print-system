<?php
/**
 * PriceCalculationService - 가격 계산 중앙화 서비스
 *
 * 모든 품목의 가격 계산을 단일 진입점으로 처리
 * SSOT 원칙: 가격 계산 로직의 유일한 소스
 *
 * @version 1.0.0
 * @created 2026-01-14
 */

class PriceCalculationService
{
    private $db;

    /**
     * 품목별 설정
     * table: 가격 조회 테이블명
     * fields: DB 컬럼명 → 표준 파라미터명 매핑
     * type: 'table_lookup' | 'formula'
     * has_tree: TreeSelect 필드 사용 여부
     */
    private const PRODUCT_CONFIGS = [
        'inserted' => [
            'table' => 'mlangprintauto_inserted',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'TreeSelect' => 'tree_id',
                'quantity' => 'quantity',
                'POtype' => 'po_type'
            ],
            'type' => 'table_lookup',
            'has_tree' => true,
            'has_quantity_two' => true
        ],
        'leaflet' => [
            'table' => 'mlangprintauto_inserted',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'TreeSelect' => 'tree_id',
                'quantity' => 'quantity',
                'POtype' => 'po_type'
            ],
            'type' => 'table_lookup',
            'has_tree' => true,
            'has_quantity_two' => true
        ],
        'namecard' => [
            'table' => 'mlangprintauto_namecard',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'quantity' => 'quantity',
                'POtype' => 'po_type'
            ],
            'type' => 'table_lookup',
            'has_tree' => false,
            'supports_premium_options' => true
        ],
        'envelope' => [
            'table' => 'mlangprintauto_envelope',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'quantity' => 'quantity',
                'POtype' => 'po_type'
            ],
            'type' => 'table_lookup',
            'has_tree' => false,
            'supports_tape_option' => true
        ],
        'littleprint' => [
            'table' => 'mlangprintauto_littleprint',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'TreeSelect' => 'tree_id',
                'quantity' => 'quantity',
                'POtype' => 'po_type'
            ],
            'type' => 'table_lookup',
            'has_tree' => true
        ],
        'merchandisebond' => [
            'table' => 'mlangprintauto_merchandisebond',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'quantity' => 'quantity',
                'POtype' => 'po_type'
            ],
            'type' => 'table_lookup',
            'has_tree' => false
        ],
        'cadarok' => [
            'table' => 'mlangprintauto_cadarok',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'quantity' => 'quantity',
                'POtype' => 'po_type'
            ],
            'type' => 'table_lookup',
            'has_tree' => false
        ],
        'ncrflambeau' => [
            'table' => 'mlangprintauto_ncrflambeau',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'tree_id',      // NCR: MY_Fsd → Section
                'TreeSelect' => 'section_id', // NCR: PN_type → TreeSelect
                'quantity' => 'quantity'
            ],
            'type' => 'table_lookup',
            'has_tree' => true  // NCR은 TreeSelect 사용
        ],
        'msticker' => [
            'table' => 'mlangprintauto_msticker',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'quantity' => 'quantity'
            ],
            'type' => 'table_lookup',
            'has_tree' => false
        ],
        'sticker_new' => [
            'type' => 'formula',
            'calculator' => 'calculateStickerPrice'
        ],
        'sticker' => [
            'table' => 'mlangprintauto_sticker',
            'fields' => [
                'style' => 'style_id',
                'Section' => 'section_id',
                'POtype' => 'po_type',
                'quantity' => 'quantity'
            ],
            'type' => 'table_lookup',
            'has_tree' => false,
            'po_type_optional' => true  // POtype 없이도 조회 가능
        ]
    ];

    /**
     * 레거시 파라미터명 → 표준 파라미터명 매핑
     */
    private const LEGACY_PARAM_MAP = [
        // 전단지 레거시
        'MY_type' => 'style_id',
        'PN_type' => 'section_id',
        'MY_Fsd' => 'tree_id',
        'MY_amount' => 'quantity',
        'POtype' => 'po_type',
        'ordertype' => 'design_type',
        // 기타 품목 레거시
        'Section' => 'section_id',
        'TreeSelect' => 'tree_id',
        'style' => 'style_id',
        'potype' => 'po_type',
        // 스티커 레거시
        'jong' => 'material',
        'garo' => 'width',
        'sero' => 'height',
        'mesu' => 'quantity',
        'uhyung' => 'frame_cost',
        'domusong' => 'die_cut'
    ];

    /**
     * 생성자
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * 품목 설정 존재 여부 확인
     *
     * @param string $productType 품목 코드
     * @return bool
     */
    public function hasProductConfig(string $productType): bool
    {
        return isset(self::PRODUCT_CONFIGS[$productType]);
    }

    /**
     * 메인 진입점: 품목별 가격 계산
     *
     * @param string $productType 품목 코드 (inserted, namecard, etc.)
     * @param array $params 파라미터 (레거시 또는 표준 모두 지원)
     * @return array 계산 결과
     */
    public function calculate(string $productType, array $params): array
    {
        // 품목 설정 확인
        if (!isset(self::PRODUCT_CONFIGS[$productType])) {
            return $this->errorResponse("지원하지 않는 품목입니다: {$productType}");
        }

        $config = self::PRODUCT_CONFIGS[$productType];

        // 파라미터 정규화
        $normalizedParams = $this->normalizeParams($params);

        // 계산 타입별 분기
        if ($config['type'] === 'table_lookup') {
            return $this->lookupTablePrice($productType, $normalizedParams, $config);
        } elseif ($config['type'] === 'formula') {
            $calculator = $config['calculator'];
            return $this->$calculator($normalizedParams);
        }

        return $this->errorResponse("알 수 없는 계산 타입입니다.");
    }

    /**
     * 레거시 파라미터를 표준 파라미터로 정규화
     */
    private function normalizeParams(array $params): array
    {
        $normalized = [];

        foreach ($params as $key => $value) {
            // 레거시 키를 표준 키로 변환
            $standardKey = self::LEGACY_PARAM_MAP[$key] ?? $key;
            $normalized[$standardKey] = $value;
        }

        return $normalized;
    }

    /**
     * 테이블 조회 방식 가격 계산
     */
    private function lookupTablePrice(string $productType, array $params, array $config): array
    {
        $table = $config['table'];
        $fields = $config['fields'];

        // 필수 파라미터 검증
        $requiredFields = array_values($fields);
        foreach ($requiredFields as $field) {
            if ($field === 'po_type' && !($config['has_tree'] ?? false)) {
                continue; // po_type은 일부 품목에서 선택적
            }
            if (empty($params[$field]) && $field !== 'tree_id') {
                // tree_id는 inserted/leaflet만 필수
                if ($field === 'tree_id' && !($config['has_tree'] ?? false)) {
                    continue;
                }
                return $this->errorResponse("필수 파라미터가 누락되었습니다: {$field}");
            }
        }

        // 쿼리 조건 구성
        $conditions = [];
        foreach ($fields as $dbCol => $paramKey) {
            if (!empty($params[$paramKey])) {
                $value = mysqli_real_escape_string($this->db, $params[$paramKey]);

                // quantity는 float 비교 (0.5 = 0.50)
                if ($dbCol === 'quantity') {
                    $floatVal = floatval($value);
                    $conditions[] = "{$dbCol} = {$floatVal}";
                } else {
                    $conditions[] = "{$dbCol} = '{$value}'";
                }
            }
        }

        // SELECT 컬럼 (quantityTwo 포함 여부)
        $selectCols = 'money, DesignMoney';
        if ($config['has_quantity_two'] ?? false) {
            $selectCols .= ', quantityTwo';
        }

        $query = "SELECT {$selectCols} FROM {$table} WHERE " . implode(' AND ', $conditions);
        $result = mysqli_query($this->db, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            // POtype 없이 재시도 (fallback)
            $conditionsWithoutPotype = array_filter($conditions, function($c) {
                return strpos($c, 'POtype') === false;
            });

            if (count($conditionsWithoutPotype) < count($conditions)) {
                $queryFallback = "SELECT {$selectCols} FROM {$table} WHERE " . implode(' AND ', $conditionsWithoutPotype);
                $result = mysqli_query($this->db, $queryFallback);
            }

            if (!$result || mysqli_num_rows($result) === 0) {
                return $this->errorResponse('해당 조건의 가격 정보를 찾을 수 없습니다.');
            }
        }

        $row = mysqli_fetch_assoc($result);

        // 가격 계산
        $basePrice = (int)$row['money'];
        $designPriceDB = (int)$row['DesignMoney'];

        // 디자인 타입에 따른 가격 적용
        $designType = $params['design_type'] ?? 'total';

        if ($designType === 'print') {
            $designPrice = 0;
        } elseif ($designType === 'design') {
            $basePrice = 0;
            $designPrice = $designPriceDB;
        } else { // total
            $designPrice = $designPriceDB;
        }

        // 추가 옵션 가격
        $additionalOptions = $this->calculateAdditionalOptions($productType, $params, $config);

        // 최종 가격 계산
        $orderPrice = $basePrice + $designPrice + $additionalOptions['total'];
        $vatPrice = round($orderPrice * 0.1);
        $totalPrice = $orderPrice + $vatPrice;

        // 응답 구성
        $response = [
            'success' => true,
            'data' => [
                // 표준 응답 필드
                'base_price' => $basePrice,
                'design_price' => $designPrice,
                'additional_options_total' => $additionalOptions['total'],
                'additional_options_details' => $additionalOptions['details'],
                'order_price' => $orderPrice,
                'vat_price' => $vatPrice,
                'total_price' => $totalPrice,
                'total_with_vat' => $totalPrice,

                // 레거시 호환 필드 (number_format 적용)
                'Price' => number_format($basePrice),
                'DS_Price' => number_format($designPrice),
                'Order_Price' => number_format($orderPrice),
                'PriceForm' => $basePrice,
                'DS_PriceForm' => $designPrice,
                'Order_PriceForm' => $orderPrice,
                'VAT_PriceForm' => $vatPrice,
                'Total_PriceForm' => $totalPrice,

                // 입력 파라미터 에코백
                'StyleForm' => $params['style_id'] ?? '',
                'SectionForm' => $params['section_id'] ?? '',
                'QuantityForm' => $params['quantity'] ?? '',
                'DesignForm' => $designType
            ]
        ];

        // 전단지 매수 추가
        if (($config['has_quantity_two'] ?? false) && isset($row['quantityTwo'])) {
            $response['data']['quantity_sheets'] = (int)$row['quantityTwo'];
            $response['data']['MY_amountRight'] = number_format($row['quantityTwo']) . '장';
        }

        return $response;
    }

    /**
     * 추가 옵션 가격 계산
     */
    private function calculateAdditionalOptions(string $productType, array $params, array $config): array
    {
        $total = 0;
        $details = [];

        // 직접 전달된 추가 옵션 총액
        if (isset($params['additional_options_total'])) {
            $total = (int)$params['additional_options_total'];
            return ['total' => $total, 'details' => $details];
        }

        // 프리미엄 옵션 총액 (명함 등)
        if (isset($params['premium_options_total'])) {
            $total = (int)$params['premium_options_total'];
            return ['total' => $total, 'details' => $details];
        }

        // 봉투 테이프 옵션
        if (($config['supports_tape_option'] ?? false) && !empty($params['envelope_tape_enabled'])) {
            $tapeQuantity = (int)($params['quantity'] ?? 0);
            if ($tapeQuantity > 0) {
                $tapePrice = ($tapeQuantity === 500) ? 25000 : ($tapeQuantity * 40);
                $total += $tapePrice;
                $details['envelope_tape'] = [
                    'quantity' => $tapeQuantity,
                    'price' => $tapePrice
                ];
            }
        }

        // 명함 프리미엄 옵션 (JSON)
        if (($config['supports_premium_options'] ?? false) && !empty($params['premium_options'])) {
            $premiumOptions = is_string($params['premium_options'])
                ? json_decode($params['premium_options'], true)
                : $params['premium_options'];

            if (is_array($premiumOptions)) {
                $premiumPrices = [
                    'foil' => ['name' => '박', 'price' => 30000],
                    'numbering' => ['name' => '넘버링', 'price' => 60000],
                    'perforation' => ['name' => '미싱', 'price' => 20000],
                    'rounding' => ['name' => '귀돌이', 'price' => 20000],
                    'creasing' => ['name' => '오시', 'price' => 20000]
                ];

                foreach ($premiumPrices as $key => $info) {
                    if (!empty($premiumOptions[$key])) {
                        $total += $info['price'];
                        $details[$key] = $info;
                    }
                }
            }
        }

        return ['total' => $total, 'details' => $details];
    }

    /**
     * 스티커 공식 기반 가격 계산
     */
    private function calculateStickerPrice(array $params): array
    {
        // 필수 파라미터 검증
        $material = $params['material'] ?? '';
        $width = (int)($params['width'] ?? 0);
        $height = (int)($params['height'] ?? 0);
        $quantity = (int)($params['quantity'] ?? 0);
        $frameCost = (int)($params['frame_cost'] ?? 0);
        $dieCut = $params['die_cut'] ?? '';

        if (empty($material)) {
            return $this->errorResponse('재질을 선택하세요');
        }
        if ($width <= 0) {
            return $this->errorResponse('가로사이즈를 입력하세요');
        }
        if ($height <= 0) {
            return $this->errorResponse('세로사이즈를 입력하세요');
        }
        if ($quantity <= 0) {
            return $this->errorResponse('수량을 입력하세요');
        }

        // 범위 검증
        if ($width > 590) {
            return $this->errorResponse('가로사이즈를 590mm이하만 입력할 수 있습니다');
        }
        if ($height > 590) {
            return $this->errorResponse('세로사이즈를 590mm이하만 입력할 수 있습니다');
        }
        if (($width * $height) > 250000 && $quantity > 5000) {
            return $this->errorResponse('500mm이상 대형사이즈를 5000매이상 주문은 전화요청바랍니다');
        }
        if ($quantity > 10000) {
            return $this->errorResponse('1만매 이상은 할인가 적용-전화주시기바랍니다');
        }

        // 도무송 강제 선택 검증
        if (($width < 50 || $height < 60) && ($width < 60 || $height < 50) && $dieCut == '00000 사각') {
            return $this->errorResponse('가로,세로사이즈가 50mmx60mm 미만일 경우, 도무송을 선택해야 합니다.');
        }

        // 재질별 제한 검증
        $materialName = substr($material, 4, 10);
        $blockedMaterials = ['금지스티커', '금박스티커', '롤형스티커'];
        if (in_array($materialName, $blockedMaterials)) {
            return $this->errorResponse($materialName . '는 전화 또는 메일로 견적 문의하세요');
        }

        // 재질 코드 추출
        $materialCode = substr($material, 0, 3);

        // 도무송 정보 추출
        $dieCutCost = (int)substr($dieCut, 0, 5);

        // 기본값 설정
        $rate = 0.15;
        $baseCost = 7000;
        $thomsonCost = 9;

        // 재질별 데이터베이스 조회
        $tableMap = [
            'jil' => 'shop_d1',
            'jka' => 'shop_d2',
            'jsp' => 'shop_d3',
            'cka' => 'shop_d4'
        ];

        if (isset($tableMap[$materialCode])) {
            $query = "SELECT * FROM {$tableMap[$materialCode]} LIMIT 1";
            $result = mysqli_query($this->db, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_array($result);

                // 수량별 요율 및 기본비용 설정
                if ($quantity <= 1000) {
                    $rate = $data[0] ?? 0.15;
                    $baseCost = 7000;
                } elseif ($quantity <= 4000) {
                    $rate = $data[1] ?? 0.14;
                    $baseCost = 6500;
                } elseif ($quantity <= 5000) {
                    $rate = $data[2] ?? 0.13;
                    $baseCost = 6500;
                } elseif ($quantity <= 9000) {
                    $rate = $data[3] ?? 0.12;
                    $baseCost = 6000;
                } elseif ($quantity <= 10000) {
                    $rate = $data[4] ?? 0.11;
                    $baseCost = 5500;
                } elseif ($quantity <= 50000) {
                    $rate = $data[5] ?? 0.10;
                    $baseCost = 5000;
                } else {
                    $rate = $data[6] ?? 0.09;
                    $baseCost = 5000;
                }
            }
        }

        // 재질별 톰슨비용
        if (in_array($materialCode, ['jsp', 'jka', 'cka'])) {
            $thomsonCost = 14;
        }

        // 도무송칼 크기 계산
        $maxSize = max($width, $height);

        // 사이즈별 마진비율
        $sizeMargin = ($width * $height <= 18000) ? 1 : 1.25;

        // 도무송 비용 계산
        $dieCutTotal = 0;
        if ($dieCutCost > 0) {
            if ($quantity == 500) {
                $dieCutTotal = (($dieCutCost + ($maxSize * 20)) * 900 / 1000) + (900 * $thomsonCost);
            } elseif ($quantity == 1000) {
                $dieCutTotal = (($dieCutCost + ($maxSize * 20)) * $quantity / 1000) + ($quantity * $thomsonCost);
            } elseif ($quantity > 1000) {
                $dieCutTotal = (($dieCutCost + ($maxSize * 20)) * $quantity / 1000) + ($quantity * ($thomsonCost / 9));
            }
        }

        // 특수용지 비용
        $specialCost = 0;
        if ($materialCode == 'jsp') {
            $specialCost = ($quantity == 500) ? (10000 * ($quantity + 400) / 1000) : (10000 * $quantity / 1000);
        } elseif ($materialCode == 'jka') {
            $specialCost = ($quantity == 500) ? (4000 * ($quantity + 400) / 1000) : (10000 * $quantity / 1000);
        } elseif ($materialCode == 'cka') {
            $specialCost = ($quantity == 500) ? (4000 * ($quantity + 400) / 1000) : (10000 * $quantity / 1000);
        }

        // 최종 가격 계산
        if ($quantity == 500) {
            $rawPrice = (($width + 4) * ($height + 4) * ($quantity + 400)) * $rate + $specialCost + $dieCutTotal;
            $price = round($rawPrice * $sizeMargin, -3) + $frameCost + ($baseCost * ($quantity + 400) / 1000);
        } else {
            $rawPrice = (($width + 4) * ($height + 4) * $quantity) * $rate + $specialCost + $dieCutTotal;
            $price = round($rawPrice * $sizeMargin, -3) + $frameCost + ($baseCost * $quantity / 1000);
        }

        $priceWithVat = $price * 1.1;

        return [
            'success' => true,
            'data' => [
                'base_price' => (int)$price,
                'design_price' => 0,
                'order_price' => (int)$price,
                'vat_price' => (int)($price * 0.1),
                'total_price' => (int)$priceWithVat,
                'total_with_vat' => (int)$priceWithVat,

                // 레거시 호환
                'price' => number_format($price),
                'price_vat' => number_format($priceWithVat),
                'raw_price' => (int)$price,
                'raw_price_vat' => (int)$priceWithVat,
                'st_price' => (int)$price,
                'st_price_vat' => (int)$priceWithVat
            ]
        ];
    }

    /**
     * 에러 응답 생성
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => [
                'message' => $message
            ]
        ];
    }

    /**
     * 지원 품목 목록 조회
     */
    public static function getSupportedProducts(): array
    {
        return array_keys(self::PRODUCT_CONFIGS);
    }

    /**
     * 품목별 설정 조회
     */
    public static function getProductConfig(string $productType): ?array
    {
        return self::PRODUCT_CONFIGS[$productType] ?? null;
    }
}
