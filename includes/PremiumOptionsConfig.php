<?php
/**
 * PremiumOptionsConfig - 프리미엄 옵션 중앙 설정 (SSOT)
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │  DB(premium_options + premium_option_variants)에서 읽어와    │
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
 * DB 스키마:
 *   premium_options: option_key (영문), option_name (한글)
 *   premium_option_variants: variant_key (영문), variant_name (한글), pricing_config (JSON)
 *
 * @version 2.0 - DB 기반 전환
 * @date 2026-03-06
 */

class PremiumOptionsConfig
{
    private static $memoryCache = [];
    private static $cacheTTL = 300;
    private static $cacheDir = null;

    private static function getDb()
    {
        global $db;
        if ($db && !mysqli_connect_errno()) {
            return $db;
        }
        $dbFile = __DIR__ . '/../db.php';
        if (file_exists($dbFile)) {
            require_once $dbFile;
            global $db;
            return $db;
        }
        return null;
    }

    private static function getCacheDir(): string
    {
        if (self::$cacheDir === null) {
            self::$cacheDir = __DIR__ . '/../cache';
        }
        return self::$cacheDir;
    }

    private static function readCache(string $productType): ?array
    {
        $cacheFile = self::getCacheDir() . '/premium_config_' . $productType . '.json';
        if (!file_exists($cacheFile)) {
            return null;
        }
        if ((time() - filemtime($cacheFile)) >= self::$cacheTTL) {
            @unlink($cacheFile);
            return null;
        }
        $data = @file_get_contents($cacheFile);
        if ($data === false) {
            return null;
        }
        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : null;
    }

    private static function writeCache(string $productType, array $options): void
    {
        $dir = self::getCacheDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $cacheFile = $dir . '/premium_config_' . $productType . '.json';
        @file_put_contents($cacheFile, json_encode($options, JSON_UNESCAPED_UNICODE));
    }

    public static function clearCache(string $productType = ''): void
    {
        $dir = self::getCacheDir();
        if ($productType) {
            @unlink($dir . '/premium_config_' . $productType . '.json');
            unset(self::$memoryCache[$productType]);
        } else {
            $files = glob($dir . '/premium_config_*.json');
            if ($files) {
                foreach ($files as $f) {
                    @unlink($f);
                }
            }
            self::$memoryCache = [];
        }
    }

    /**
     * DB에서 옵션을 읽어 JS 소비 가능한 형태로 변환
     *
     * 반환 포맷 (JS PremiumOptionsGeneric과 호환):
     *   'option_key' => [
     *       'name'       => '한글명',
     *       'type'       => 'select' | 'checkbox',
     *       'base_qty'   => 500,
     *       'base_price' => 30000,
     *       'per_unit'   => 12,
     *       'variants'   => [
     *           'variant_key' => '표시명',
     *           'variant_key' => ['label' => '표시명', 'additional_fee' => 15000],
     *       ],
     *   ]
     */
    private static function loadFromDb(string $productType): array
    {
        $dbConn = self::getDb();
        if (!$dbConn) {
            return [];
        }

        $stmt = mysqli_prepare($dbConn, "
            SELECT o.option_key, o.option_name, o.sort_order,
                   v.variant_key, v.variant_name, v.pricing_config, v.is_default, v.display_order
            FROM premium_options o
            JOIN premium_option_variants v ON v.option_id = o.id
            WHERE o.product_type = ? AND o.is_active = 1 AND v.is_active = 1
              AND o.option_key IS NOT NULL AND v.variant_key IS NOT NULL
            ORDER BY o.sort_order, v.display_order
        ");
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "s", $productType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $rawOptions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $optKey = $row['option_key'];
            if (!isset($rawOptions[$optKey])) {
                $rawOptions[$optKey] = [
                    'option_name' => $row['option_name'],
                    'sort_order'  => (int)$row['sort_order'],
                    'variants'    => [],
                ];
            }
            $pricing = json_decode($row['pricing_config'], true) ?: [];
            $rawOptions[$optKey]['variants'][] = [
                'variant_key'  => $row['variant_key'],
                'variant_name' => $row['variant_name'],
                'pricing'      => $pricing,
                'is_default'   => (bool)$row['is_default'],
            ];
        }
        mysqli_stmt_close($stmt);

        $options = [];
        foreach ($rawOptions as $optKey => $raw) {
            $variantCount = count($raw['variants']);
            $firstVariant = $raw['variants'][0] ?? null;
            if (!$firstVariant) continue;

            $firstPricing = $firstVariant['pricing'];

            $baseQty   = isset($firstPricing['base_500']) ? 500 : 0;
            $basePrice = $firstPricing['base_500'] ?? $firstPricing['base_price'] ?? 0;
            $perUnit   = $firstPricing['per_unit'] ?? 0;

            $type = ($variantCount <= 1) ? 'checkbox' : 'select';

            $opt = [
                'name'       => $raw['option_name'],
                'type'       => $type,
                'base_qty'   => $baseQty,
                'base_price' => (int)$basePrice,
                'per_unit'   => (int)$perUnit,
            ];

            if ($type === 'select') {
                $variants = [];
                $referenceBase = (int)$basePrice;

                foreach ($raw['variants'] as $v) {
                    $vKey  = $v['variant_key'];
                    $vName = $v['variant_name'];
                    $vPricing = $v['pricing'];

                    $explicitFee = (int)($vPricing['additional_fee'] ?? 0);

                    if ($explicitFee > 0) {
                        $totalAdditional = $explicitFee;
                    } else {
                        $variantBase = (int)($vPricing['base_500'] ?? $vPricing['base_price'] ?? 0);
                        $totalAdditional = max(0, $variantBase - $referenceBase);
                    }

                    if ($totalAdditional > 0) {
                        $variants[$vKey] = [
                            'label'          => $vName,
                            'additional_fee' => $totalAdditional,
                        ];
                    } else {
                        $variants[$vKey] = $vName;
                    }
                }
                $opt['variants'] = $variants;
            }

            $options[$optKey] = $opt;
        }

        return $options;
    }

    public static function getOptions(string $productType): array
    {
        if (isset(self::$memoryCache[$productType])) {
            return self::$memoryCache[$productType];
        }

        $cached = self::readCache($productType);
        if ($cached !== null) {
            self::$memoryCache[$productType] = $cached;
            return $cached;
        }

        $options = self::loadFromDb($productType);
        self::$memoryCache[$productType] = $options;
        self::writeCache($productType, $options);

        return $options;
    }

    public static function getSupportedProducts(): array
    {
        return ['namecard', 'merchandisebond', 'inserted', 'littleprint', 'cadarok', 'envelope'];
    }

    public static function hasOptions(string $productType): bool
    {
        return !empty(self::getOptions($productType));
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
        $all = [];
        foreach (self::getSupportedProducts() as $pt) {
            $opts = self::getOptions($pt);
            if (!empty($opts)) {
                $all[$pt] = $opts;
            }
        }
        return json_encode($all, JSON_UNESCAPED_UNICODE);
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
