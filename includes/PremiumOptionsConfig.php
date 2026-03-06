<?php
/**
 * PremiumOptionsConfig - 프리미엄 옵션 중앙 설정 (SSOT)
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │  새 옵션 추가 시 이 파일의 $options 배열에만 추가하면         │
 * │  아래 모든 곳에 자동 반영됩니다:                              │
 * │                                                              │
 * │  ✅ 주문 페이지 UI  (PremiumOptionsGeneric JS)               │
 * │  ✅ 장바구니        (AdditionalOptionsDisplay.php)           │
 * │  ✅ 주문완료        (OrderComplete_universal.php)            │
 * │  ✅ 인쇄주문서      (OrderFormOrderTree.php)                 │
 * │  ✅ 관리자 상세     (dashboard/orders/view.php)              │
 * │  ✅ API            (api/premium_options_config.php)          │
 * └──────────────────────────────────────────────────────────────┘
 *
 * @version 1.0
 * @date 2026-03-06
 */

class PremiumOptionsConfig
{
    /**
     * 제품별 프리미엄 옵션 정의
     *
     * 구조:
     *   'option_key' => [
     *       'name'       => '한글명',
     *       'type'       => 'select' | 'checkbox',
     *       'base_qty'   => 500,          // 기준 수량
     *       'base_price' => 30000,        // 기준 수량 이하 가격
     *       'per_unit'   => 12,           // 기준 초과 시 매당 추가금 (0이면 정액)
     *       'note'       => '참고 문구',   // (선택) UI 노트
     *       'variants'   => [             // (select일 때만)
     *           'key' => '표시명',
     *           'key' => ['label' => '표시명', 'additional_fee' => 15000],
     *       ],
     *   ]
     *
     * DB 저장 포맷 (premium_options JSON):
     *   {option_key}_enabled  : 1
     *   {option_key}_type     : variant key (select일 때)
     *   {option_key}_price    : 계산된 가격
     */
    private static $options = [

        // ───────────────────────────────────────────
        // 명함 (namecard)
        // ───────────────────────────────────────────
        'namecard' => [
            'foil' => [
                'name'       => '박',
                'type'       => 'select',
                'base_qty'   => 500,
                'base_price' => 30000,
                'per_unit'   => 12,
                'note'       => '박(20mm×20mm 이하)',
                'variants'   => [
                    'gold_matte'   => '금박무광',
                    'gold_gloss'   => '금박유광',
                    'silver_matte' => '은박무광',
                    'silver_gloss' => '은박유광',
                    'blue_gloss'   => '청박유광',
                    'red_gloss'    => '적박유광',
                    'green_gloss'  => '녹박유광',
                    'black_gloss'  => '먹박유광',
                ],
            ],
            'numbering' => [
                'name'       => '넘버링',
                'type'       => 'select',
                'base_qty'   => 500,
                'base_price' => 60000,
                'per_unit'   => 12,
                'variants'   => [
                    'single' => '1개',
                    'double' => ['label' => '2개', 'additional_fee' => 15000],
                ],
            ],
            'perforation' => [
                'name'       => '미싱',
                'type'       => 'select',
                'base_qty'   => 500,
                'base_price' => 20000,
                'per_unit'   => 25,
                'variants'   => [
                    'single' => '1개',
                    'double' => ['label' => '2개', 'additional_fee' => 15000],
                ],
            ],
            'rounding' => [
                'name'       => '귀돌이',
                'type'       => 'checkbox',
                'base_qty'   => 500,
                'base_price' => 6000,
                'per_unit'   => 12,
            ],
            'creasing' => [
                'name'       => '오시',
                'type'       => 'select',
                'base_qty'   => 500,
                'base_price' => 20000,
                'per_unit'   => 25,
                'variants'   => [
                    '1line' => '1줄',
                    '2line' => '2줄',
                    '3line' => ['label' => '3줄', 'additional_fee' => 15000],
                ],
            ],
            'map' => [
                'name'       => '약도',
                'type'       => 'checkbox',
                'base_qty'   => 0,
                'base_price' => 30000,
                'per_unit'   => 0,
                'note'       => '약도 제작 (정액)',
            ],
            'logo' => [
                'name'       => '마크로고',
                'type'       => 'checkbox',
                'base_qty'   => 0,
                'base_price' => 10000,
                'per_unit'   => 0,
                'note'       => '로고/마크 디자인 (정액)',
            ],
        ],

        // ───────────────────────────────────────────
        // 상품권 (merchandisebond)
        // ───────────────────────────────────────────
        'merchandisebond' => [
            'foil' => [
                'name'       => '박',
                'type'       => 'select',
                'base_qty'   => 500,
                'base_price' => 30000,
                'per_unit'   => 12,
                'variants'   => [
                    'gold_matte'   => '금박무광',
                    'gold_gloss'   => '금박유광',
                    'silver_matte' => '은박무광',
                    'silver_gloss' => '은박유광',
                    'blue_gloss'   => '청박유광',
                    'red_gloss'    => '적박유광',
                    'green_gloss'  => '녹박유광',
                    'black_gloss'  => '먹박유광',
                ],
            ],
            'numbering' => [
                'name'       => '넘버링',
                'type'       => 'select',
                'base_qty'   => 500,
                'base_price' => 60000,
                'per_unit'   => 12,
                'variants'   => [
                    'single' => '1개',
                    'double' => ['label' => '2개', 'additional_fee' => 15000],
                ],
            ],
            'perforation' => [
                'name'       => '미싱',
                'type'       => 'select',
                'base_qty'   => 500,
                'base_price' => 20000,
                'per_unit'   => 25,
                'variants'   => [
                    'horizontal' => '가로미싱',
                    'vertical'   => '세로미싱',
                    'cross'      => '십자미싱',
                ],
            ],
            'rounding' => [
                'name'       => '귀돌이',
                'type'       => 'checkbox',
                'base_qty'   => 500,
                'base_price' => 6000,
                'per_unit'   => 12,
            ],
        ],
    ];

    public static function getOptions(string $productType): array
    {
        return self::$options[$productType] ?? [];
    }

    public static function getSupportedProducts(): array
    {
        return array_keys(self::$options);
    }

    public static function hasOptions(string $productType): bool
    {
        return !empty(self::$options[$productType]);
    }

    /**
     * premium_options JSON → 표시용 배열로 파싱
     * 반환: [['key'=>'foil', 'name'=>'박', 'type_name'=>'금박무광', 'price'=>30000, 'display'=>'박(금박무광)'], ...]
     */
    public static function parseSelectedOptions($premiumOptionsJson, string $productType = 'namecard'): array
    {
        $result = [];

        if (empty($premiumOptionsJson)) {
            return $result;
        }

        $data = is_string($premiumOptionsJson)
            ? json_decode($premiumOptionsJson, true)
            : $premiumOptionsJson;

        if (!$data || !is_array($data)) {
            return $result;
        }

        $config = self::getOptions($productType);

        foreach ($config as $key => $optConfig) {
            if (empty($data[$key . '_enabled']) || $data[$key . '_enabled'] != 1) {
                continue;
            }

            $price = intval($data[$key . '_price'] ?? 0);
            if ($price <= 0) {
                continue;
            }

            $typeValue = $data[$key . '_type'] ?? '';
            $typeName  = '';

            if (!empty($typeValue) && isset($optConfig['variants'])) {
                $variant = $optConfig['variants'][$typeValue] ?? null;
                if (is_array($variant)) {
                    $typeName = $variant['label'];
                } elseif (is_string($variant)) {
                    $typeName = $variant;
                }
            }

            $display = $optConfig['name'];
            if (!empty($typeName)) {
                $display .= '(' . $typeName . ')';
            }

            $result[] = [
                'key'        => $key,
                'name'       => $optConfig['name'],
                'type_value' => $typeValue,
                'type_name'  => $typeName,
                'price'      => $price,
                'display'    => $display,
            ];
        }

        return $result;
    }

    public static function toJson(string $productType): string
    {
        return json_encode(self::getOptions($productType), JSON_UNESCAPED_UNICODE);
    }

    public static function allToJson(): string
    {
        return json_encode(self::$options, JSON_UNESCAPED_UNICODE);
    }

    public static function getOptionName(string $productType, string $optionKey): string
    {
        $config = self::getOptions($productType);
        return $config[$optionKey]['name'] ?? $optionKey;
    }

    public static function getVariantName(string $productType, string $optionKey, string $variantKey): string
    {
        $config = self::getOptions($productType);
        if (!isset($config[$optionKey]['variants'][$variantKey])) {
            return $variantKey;
        }
        $v = $config[$optionKey]['variants'][$variantKey];
        return is_array($v) ? ($v['label'] ?? $variantKey) : $v;
    }
}
