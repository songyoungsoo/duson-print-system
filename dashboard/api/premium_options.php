<?php
/**
 * 프리미엄 옵션 관리자 API
 * Actions: list, get, create_option, create_variant, update_variant,
 *          delete_variant, toggle_option, reorder, recalculate_orders
 */
require_once __DIR__ . '/base.php';

// JSON body를 한 번만 읽어서 글로벌 변수에 저장
$_JSON_INPUT = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_JSON_INPUT = json_decode(file_get_contents('php://input'), true) ?: [];
}

$action = $_GET['action'] ?? '';
if (!$action && $_JSON_INPUT) {
    $action = $_JSON_INPUT['action'] ?? $_POST['action'] ?? '';
}

switch ($action) {
    case 'list':
        handleList();
        break;
    case 'get':
        handleGet();
        break;
    case 'create_option':
        handleCreateOption();
        break;
    case 'create_variant':
        handleCreateVariant();
        break;
    case 'update_variant':
        handleUpdateVariant();
        break;
    case 'delete_variant':
        handleDeleteVariant();
        break;
    case 'toggle_option':
        handleToggleOption();
        break;
    case 'reorder':
        handleReorder();
        break;
    case 'recalculate_orders':
        handleRecalculateOrders();
        break;
    default:
        jsonResponse(false, '유효하지 않은 액션입니다.');
}

/**
 * 품목별 옵션+variants 전체 조회
 * GET ?action=list&product_type=namecard
 */
function handleList() {
    global $db;
    $product_type = $_GET['product_type'] ?? '';
    $allowed = ['namecard', 'merchandisebond', 'inserted', 'littleprint', 'cadarok', 'envelope'];

    if (!in_array($product_type, $allowed)) {
        jsonResponse(false, '유효하지 않은 제품 유형입니다.');
    }

    $stmt = mysqli_prepare($db, "
        SELECT o.id AS option_id, o.option_name, o.option_type, o.sort_order, o.is_active,
               v.id AS variant_id, v.variant_name, v.pricing_config, v.is_default, v.display_order, v.is_active AS variant_active
        FROM premium_options o
        LEFT JOIN premium_option_variants v ON v.option_id = o.id
        WHERE o.product_type = ?
        ORDER BY o.sort_order, v.display_order
    ");
    mysqli_stmt_bind_param($stmt, "s", $product_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $options = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $oid = $row['option_id'];
        if (!isset($options[$oid])) {
            $options[$oid] = [
                'option_id' => (int)$oid,
                'option_name' => $row['option_name'],
                'option_type' => $row['option_type'],
                'sort_order' => (int)$row['sort_order'],
                'is_active' => (bool)$row['is_active'],
                'variants' => []
            ];
        }
        if ($row['variant_id']) {
            $options[$oid]['variants'][] = [
                'variant_id' => (int)$row['variant_id'],
                'variant_name' => $row['variant_name'],
                'pricing_config' => json_decode($row['pricing_config'], true),
                'is_default' => (bool)$row['is_default'],
                'display_order' => (int)$row['display_order'],
                'is_active' => (bool)$row['variant_active']
            ];
        }
    }
    mysqli_stmt_close($stmt);

    jsonResponse(true, 'OK', ['product_type' => $product_type, 'options' => array_values($options)]);
}

/**
 * 단일 variant 상세
 * GET ?action=get&id=123
 */
function handleGet() {
    global $db;
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(false, 'ID가 필요합니다.');

    $stmt = mysqli_prepare($db, "
        SELECT v.*, o.product_type, o.option_name
        FROM premium_option_variants v
        JOIN premium_options o ON v.option_id = o.id
        WHERE v.id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) jsonResponse(false, 'Variant를 찾을 수 없습니다.');

    $row['pricing_config'] = json_decode($row['pricing_config'], true);
    jsonResponse(true, 'OK', $row);
}

/**
 * 새 옵션 카테고리 추가
 * POST: product_type, option_name
 */
function handleCreateOption() {
    global $db;
    global $_JSON_INPUT; $input = $_JSON_INPUT ?: [];
    $product_type = $input['product_type'] ?? '';
    $option_name = trim($input['option_name'] ?? '');

    $allowed = ['namecard', 'merchandisebond', 'inserted', 'littleprint', 'cadarok', 'envelope'];
    if (!in_array($product_type, $allowed)) jsonResponse(false, '유효하지 않은 제품 유형입니다.');
    if (empty($option_name)) jsonResponse(false, '옵션 이름이 필요합니다.');

    // 최대 sort_order 조회
    $r = mysqli_query($db, "SELECT MAX(sort_order) AS max_sort FROM premium_options WHERE product_type='" . mysqli_real_escape_string($db, $product_type) . "'");
    $max_sort = (int)mysqli_fetch_assoc($r)['max_sort'];

    $stmt = mysqli_prepare($db, "INSERT INTO premium_options (product_type, option_name, sort_order) VALUES (?, ?, ?)");
    $sort = $max_sort + 1;
    mysqli_stmt_bind_param($stmt, "ssi", $product_type, $option_name, $sort);

    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($db);
        mysqli_stmt_close($stmt);
        invalidateCache($product_type);
        jsonResponse(true, '옵션이 추가되었습니다.', ['option_id' => $new_id]);
    } else {
        $error = mysqli_error($db);
        mysqli_stmt_close($stmt);
        if (strpos($error, 'Duplicate') !== false) {
            jsonResponse(false, '이미 존재하는 옵션입니다.');
        }
        jsonResponse(false, '옵션 추가 실패: ' . $error);
    }
}

/**
 * 옵션에 새 variant 추가
 * POST: option_id, variant_name, pricing_config
 */
function handleCreateVariant() {
    global $db;
    global $_JSON_INPUT; $input = $_JSON_INPUT ?: [];
    $option_id = (int)($input['option_id'] ?? 0);
    $variant_name = trim($input['variant_name'] ?? '');
    $pricing_config = $input['pricing_config'] ?? [];

    if (!$option_id) jsonResponse(false, 'option_id가 필요합니다.');
    if (empty($variant_name)) jsonResponse(false, 'variant 이름이 필요합니다.');
    if (empty($pricing_config)) jsonResponse(false, '가격 설정이 필요합니다.');

    // option 존재 확인 + product_type 조회
    $stmt = mysqli_prepare($db, "SELECT product_type FROM premium_options WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $option_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $opt = mysqli_fetch_assoc($r);
    mysqli_stmt_close($stmt);

    if (!$opt) jsonResponse(false, '옵션을 찾을 수 없습니다.');

    // 최대 display_order
    $r2 = mysqli_query($db, "SELECT MAX(display_order) AS max_order FROM premium_option_variants WHERE option_id={$option_id}");
    $max_order = (int)mysqli_fetch_assoc($r2)['max_order'];

    $json = json_encode($pricing_config, JSON_UNESCAPED_UNICODE);
    $display_order = $max_order + 1;
    $stmt = mysqli_prepare($db, "INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, display_order) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issi", $option_id, $variant_name, $json, $display_order);

    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($db);
        mysqli_stmt_close($stmt);
        invalidateCache($opt['product_type']);
        jsonResponse(true, 'Variant가 추가되었습니다.', ['variant_id' => $new_id]);
    } else {
        $error = mysqli_error($db);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Variant 추가 실패: ' . $error);
    }
}

/**
 * variant 가격 변경
 * POST: variant_id, pricing_config, (optional) variant_name, is_default
 */
function handleUpdateVariant() {
    global $db;
    global $_JSON_INPUT; $input = $_JSON_INPUT ?: [];
    $variant_id = (int)($input['variant_id'] ?? 0);
    if (!$variant_id) jsonResponse(false, 'variant_id가 필요합니다.');

    // 기존 데이터 조회
    $stmt = mysqli_prepare($db, "
        SELECT v.*, o.product_type FROM premium_option_variants v
        JOIN premium_options o ON v.option_id = o.id WHERE v.id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $variant_id);
    mysqli_stmt_execute($stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$existing) jsonResponse(false, 'Variant를 찾을 수 없습니다.');

    $updates = [];
    $params = [];
    $types = '';

    if (isset($input['pricing_config'])) {
        $updates[] = "pricing_config = ?";
        $params[] = json_encode($input['pricing_config'], JSON_UNESCAPED_UNICODE);
        $types .= 's';
    }
    if (isset($input['variant_name'])) {
        $updates[] = "variant_name = ?";
        $params[] = trim($input['variant_name']);
        $types .= 's';
    }
    if (isset($input['is_default'])) {
        $updates[] = "is_default = ?";
        $params[] = $input['is_default'] ? 1 : 0;
        $types .= 'i';
    }
    if (isset($input['is_active'])) {
        $updates[] = "is_active = ?";
        $params[] = $input['is_active'] ? 1 : 0;
        $types .= 'i';
    }

    if (empty($updates)) jsonResponse(false, '변경할 항목이 없습니다.');

    $sql = "UPDATE premium_option_variants SET " . implode(', ', $updates) . " WHERE id = ?";
    $params[] = $variant_id;
    $types .= 'i';

    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        invalidateCache($existing['product_type']);
        jsonResponse(true, '변경되었습니다.');
    } else {
        $error = mysqli_error($db);
        mysqli_stmt_close($stmt);
        jsonResponse(false, '변경 실패: ' . $error);
    }
}

/**
 * variant 삭제 (soft delete = is_active=0)
 * POST: variant_id
 */
function handleDeleteVariant() {
    global $db;
    global $_JSON_INPUT; $input = $_JSON_INPUT ?: [];
    $variant_id = (int)($input['variant_id'] ?? 0);
    if (!$variant_id) jsonResponse(false, 'variant_id가 필요합니다.');

    // product_type 조회
    $stmt = mysqli_prepare($db, "
        SELECT o.product_type FROM premium_option_variants v
        JOIN premium_options o ON v.option_id = o.id WHERE v.id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $variant_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$r) jsonResponse(false, 'Variant를 찾을 수 없습니다.');

    $stmt = mysqli_prepare($db, "UPDATE premium_option_variants SET is_active = 0 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $variant_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    invalidateCache($r['product_type']);
    jsonResponse(true, '삭제되었습니다.');
}

/**
 * 옵션 활성/비활성 토글
 * POST: option_id
 */
function handleToggleOption() {
    global $db;
    global $_JSON_INPUT; $input = $_JSON_INPUT ?: [];
    $option_id = (int)($input['option_id'] ?? 0);
    if (!$option_id) jsonResponse(false, 'option_id가 필요합니다.');

    $stmt = mysqli_prepare($db, "SELECT product_type, is_active FROM premium_options WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $option_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row) jsonResponse(false, '옵션을 찾을 수 없습니다.');

    $new_active = $row['is_active'] ? 0 : 1;
    $stmt = mysqli_prepare($db, "UPDATE premium_options SET is_active = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $new_active, $option_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    invalidateCache($row['product_type']);
    jsonResponse(true, $new_active ? '활성화되었습니다.' : '비활성화되었습니다.', ['is_active' => (bool)$new_active]);
}

/**
 * 정렬 순서 변경
 * POST: items [{option_id, sort_order}] or [{variant_id, display_order}]
 */
function handleReorder() {
    global $db;
    global $_JSON_INPUT; $input = $_JSON_INPUT ?: [];
    $items = $input['items'] ?? [];
    $type = $input['type'] ?? 'option'; // 'option' or 'variant'

    if (empty($items)) jsonResponse(false, '항목이 필요합니다.');

    $product_type = null;

    foreach ($items as $item) {
        if ($type === 'option') {
            $stmt = mysqli_prepare($db, "UPDATE premium_options SET sort_order = ? WHERE id = ?");
            $sort = (int)$item['sort_order'];
            $id = (int)$item['option_id'];
            mysqli_stmt_bind_param($stmt, "ii", $sort, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if (!$product_type) {
                $r = mysqli_query($db, "SELECT product_type FROM premium_options WHERE id={$id}");
                $product_type = mysqli_fetch_assoc($r)['product_type'] ?? null;
            }
        } else {
            $stmt = mysqli_prepare($db, "UPDATE premium_option_variants SET display_order = ? WHERE id = ?");
            $order = (int)$item['display_order'];
            $id = (int)$item['variant_id'];
            mysqli_stmt_bind_param($stmt, "ii", $order, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if (!$product_type) {
                $r = mysqli_query($db, "
                    SELECT o.product_type FROM premium_option_variants v
                    JOIN premium_options o ON v.option_id = o.id WHERE v.id={$id}
                ");
                $product_type = mysqli_fetch_assoc($r)['product_type'] ?? null;
            }
        }
    }

    if ($product_type) invalidateCache($product_type);
    jsonResponse(true, '정렬 순서가 변경되었습니다.');
}

/**
 * 기존 주문 재계산 (미리보기 + 실행)
 * POST: product_type, (optional) execute=true
 */
function handleRecalculateOrders() {
    global $db;
    global $_JSON_INPUT; $input = $_JSON_INPUT ?: [];
    $product_type = $input['product_type'] ?? '';
    $execute = !empty($input['execute']);

    $allowed = ['namecard', 'merchandisebond', 'inserted', 'littleprint', 'cadarok', 'envelope'];
    if (!in_array($product_type, $allowed)) jsonResponse(false, '유효하지 않은 제품 유형입니다.');

    // DB에서 현재 가격 데이터 로드
    $db_prices = loadDBPrices($db, $product_type);
    if (empty($db_prices)) {
        jsonResponse(false, 'DB에 해당 품목의 가격 데이터가 없습니다.');
    }

    // 패턴별 분기
    if (in_array($product_type, ['namecard', 'merchandisebond'])) {
        $result = recalculatePatternA($db, $product_type, $db_prices, $execute);
    } elseif ($product_type === 'envelope') {
        $result = recalculatePatternC($db, $product_type, $db_prices, $execute);
    } else {
        $result = recalculatePatternB($db, $product_type, $db_prices, $execute);
    }

    jsonResponse(true, 'OK', $result);
}

/**
 * DB에서 현재 가격 데이터 로드
 * @return array [option_name => [variant_name => pricing_config, ...], ...]
 */
function loadDBPrices($db, $product_type) {
    $stmt = mysqli_prepare($db, "
        SELECT o.option_name, v.variant_name, v.pricing_config
        FROM premium_options o
        JOIN premium_option_variants v ON v.option_id = o.id
        WHERE o.product_type = ? AND o.is_active = 1 AND v.is_active = 1
    ");
    mysqli_stmt_bind_param($stmt, "s", $product_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $prices = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $prices[$row['option_name']][$row['variant_name']] = json_decode($row['pricing_config'], true);
    }
    mysqli_stmt_close($stmt);
    return $prices;
}

/**
 * Pattern A 가격 계산 (명함/상품권): qty ≤ 500 → base_500; qty > 500 → base_500 + (qty-500) × per_unit + additional_fee
 */
function calcPatternA($pricing_config, $quantity) {
    $base = (int)($pricing_config['base_500'] ?? 0);
    $per_unit = (int)($pricing_config['per_unit'] ?? 0);
    $additional = (int)($pricing_config['additional_fee'] ?? 0);

    if ($quantity <= 500) {
        return $base;
    }
    return $base + (($quantity - 500) * $per_unit) + $additional;
}

/**
 * Pattern B 가격 계산 (전단지/포스터/카다록): base_price × max(quantity, 1)
 */
function calcPatternB($pricing_config, $quantity) {
    $base = (int)($pricing_config['base_price'] ?? $pricing_config['per_unit'] ?? 0);
    $multiplier = max(floatval($quantity), 1);
    return (int)round($base * $multiplier);
}

/**
 * Pattern C 가격 계산 (봉투): 수량 구간별 고정가 or 매당 가격
 */
function calcPatternC($pricing_config, $quantity) {
    $tiers = $pricing_config['tiers'] ?? [];
    $over_per_unit = (int)($pricing_config['over_1000_per_unit'] ?? 40);

    // 구간별 가격
    foreach ($tiers as $tier) {
        if ($quantity <= ($tier['max_qty'] ?? 0)) {
            return (int)($tier['price'] ?? 0);
        }
    }
    // 1000매 초과: 매당 가격
    return $quantity * $over_per_unit;
}

/**
 * JS option key → DB variant name 매핑 (명함/상품권)
 */
function getPatternAVariantMap() {
    return [
        'foil' => [
            'option_name' => '박',
            'variants' => [
                'gold_matte' => '금박무광', 'gold_gloss' => '금박유광',
                'silver_matte' => '은박무광', 'silver_gloss' => '은박유광',
                'blue_gloss' => '청박', 'red_gloss' => '적박',
                'green_gloss' => '녹박', 'black_gloss' => '먹박'
            ]
        ],
        'numbering' => [
            'option_name' => '넘버링',
            'variants' => ['single' => '1개', 'double' => '2개']
        ],
        'perforation' => [
            'option_name' => '미싱',
            'variants' => ['single' => '1개', 'double' => '2개']
        ],
        'rounding' => [
            'option_name' => '귀돌이',
            'variants' => [null => '전체']  // flat 구조
        ],
        'creasing' => [
            'option_name' => '오시',
            'variants' => ['1line' => '1줄', '2line' => '2줄', '3line' => '3줄']
        ]
    ];
}

/**
 * JS option key → DB variant name 매핑 (전단지/포스터/카다록)
 */
function getPatternBVariantMap() {
    return [
        'coating' => [
            'option_name' => '코팅',
            'variants' => [
                'single' => '단면유광', 'double' => '양면유광',
                'single_matte' => '단면무광', 'double_matte' => '양면무광'
            ]
        ],
        'folding' => [
            'option_name' => '접지',
            'variants' => ['2fold' => '2단', '3fold' => '3단', 'accordion' => '병풍', 'gate' => '대문']
        ],
        'creasing' => [
            'option_name' => '오시',
            'variants' => ['1' => '1줄', '2' => '2줄', '3' => '3줄']
        ]
    ];
}

/**
 * Pattern A 재계산 (명함/상품권)
 * 주문 JSON: premium_options → {foil_enabled, foil_type, foil_price, ...}
 */
function recalculatePatternA($db, $product_type, $db_prices, $execute) {
    $variant_map = getPatternAVariantMap();
    $esc_type = mysqli_real_escape_string($db, $product_type);

    // 대상 주문 조회
    $sql = "SELECT no, premium_options, premium_options_total, quantity_value
            FROM mlangorder_printauto
            WHERE product_type = '{$esc_type}'
            AND premium_options_total > 0
            AND premium_options IS NOT NULL AND LENGTH(premium_options) > 10";
    $result = mysqli_query($db, $sql);

    $changes = [];
    $updated = 0;
    $errors = [];

    while ($order = mysqli_fetch_assoc($result)) {
        $opts = json_decode($order['premium_options'], true);
        if (!$opts) continue;

        $quantity = (int)floatval($order['quantity_value'] ?? 500);
        if ($quantity <= 0) $quantity = 500;

        $old_total = (int)$order['premium_options_total'];
        $new_total = 0;
        $detail = [];

        // 각 옵션별 재계산
        $option_keys = [
            'foil' => ['enabled' => 'foil_enabled', 'type' => 'foil_type', 'price' => 'foil_price'],
            'numbering' => ['enabled' => 'numbering_enabled', 'type' => 'numbering_type', 'price' => 'numbering_price'],
            'perforation' => ['enabled' => 'perforation_enabled', 'type' => 'perforation_type', 'price' => 'perforation_price'],
            'rounding' => ['enabled' => 'rounding_enabled', 'type' => null, 'price' => 'rounding_price'],
            'creasing' => ['enabled' => 'creasing_enabled', 'type' => 'creasing_type', 'price' => 'creasing_price'],
        ];

        foreach ($option_keys as $js_key => $fields) {
            $enabled = !empty($opts[$fields['enabled']]) && $opts[$fields['enabled']] !== '0';
            if (!$enabled) continue;

            $js_type = $fields['type'] ? ($opts[$fields['type']] ?? '') : null;
            $map = $variant_map[$js_key] ?? null;
            if (!$map) continue;

            $db_option_name = $map['option_name'];
            $db_variant_name = null;

            if ($js_key === 'rounding') {
                $db_variant_name = '전체';
            } elseif ($js_type && isset($map['variants'][$js_type])) {
                $db_variant_name = $map['variants'][$js_type];
            }

            if (!$db_variant_name || !isset($db_prices[$db_option_name][$db_variant_name])) {
                // DB에 해당 variant가 없으면 기존 가격 유지
                $new_total += (int)($opts[$fields['price']] ?? 0);
                continue;
            }

            $pricing = $db_prices[$db_option_name][$db_variant_name];
            $new_price = calcPatternA($pricing, $quantity);
            $old_price = (int)($opts[$fields['price']] ?? 0);
            $new_total += $new_price;

            if ($old_price !== $new_price) {
                $detail[] = [
                    'option' => $db_option_name,
                    'variant' => $db_variant_name,
                    'old_price' => $old_price,
                    'new_price' => $new_price
                ];
                // JSON 업데이트
                $opts[$fields['price']] = $new_price;
            }
        }

        $opts['premium_options_total'] = $new_total;

        if ($old_total !== $new_total) {
            $changes[] = [
                'no' => (int)$order['no'],
                'quantity' => $quantity,
                'old_total' => $old_total,
                'new_total' => $new_total,
                'diff' => $new_total - $old_total,
                'details' => $detail
            ];

            if ($execute) {
                $new_json = json_encode($opts, JSON_UNESCAPED_UNICODE);
                $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET premium_options = ?, premium_options_total = ? WHERE no = ?");
                mysqli_stmt_bind_param($stmt, "sii", $new_json, $new_total, $order['no']);
                if (mysqli_stmt_execute($stmt)) {
                    $updated++;
                } else {
                    $errors[] = "주문 #{$order['no']}: " . mysqli_error($db);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    return buildRecalcResult($product_type, $changes, $updated, $errors, $execute);
}

/**
 * Pattern B 재계산 (전단지/포스터/카다록)
 * 개별 컬럼: coating_enabled, coating_type, coating_price, folding_*, creasing_*
 */
function recalculatePatternB($db, $product_type, $db_prices, $execute) {
    $variant_map = getPatternBVariantMap();
    $esc_type = mysqli_real_escape_string($db, $product_type);

    $sql = "SELECT no, coating_enabled, coating_type, coating_price,
                   folding_enabled, folding_type, folding_price,
                   creasing_enabled, creasing_lines, creasing_price,
                   additional_options_total, quantity_value
            FROM mlangorder_printauto
            WHERE product_type = '{$esc_type}'
            AND additional_options_total > 0";
    $result = mysqli_query($db, $sql);

    $changes = [];
    $updated = 0;
    $errors = [];

    while ($order = mysqli_fetch_assoc($result)) {
        $quantity = floatval($order['quantity_value'] ?? 1);
        if ($quantity <= 0) $quantity = 1;

        $old_total = (int)$order['additional_options_total'];
        $new_total = 0;
        $detail = [];
        $updates = [];

        // 코팅
        if (!empty($order['coating_enabled'])) {
            $js_type = $order['coating_type'] ?? '';
            $db_variant_name = $variant_map['coating']['variants'][$js_type] ?? null;
            $old_price = (int)($order['coating_price'] ?? 0);

            if ($db_variant_name && isset($db_prices['코팅'][$db_variant_name])) {
                $new_price = calcPatternB($db_prices['코팅'][$db_variant_name], $quantity);
            } else {
                $new_price = $old_price;
            }
            $new_total += $new_price;
            if ($old_price !== $new_price) {
                $detail[] = ['option' => '코팅', 'variant' => $db_variant_name ?? $js_type, 'old_price' => $old_price, 'new_price' => $new_price];
                $updates['coating_price'] = $new_price;
            }
        }

        // 접지
        if (!empty($order['folding_enabled'])) {
            $js_type = $order['folding_type'] ?? '';
            $db_variant_name = $variant_map['folding']['variants'][$js_type] ?? null;
            $old_price = (int)($order['folding_price'] ?? 0);

            if ($db_variant_name && isset($db_prices['접지'][$db_variant_name])) {
                $new_price = calcPatternB($db_prices['접지'][$db_variant_name], $quantity);
            } else {
                $new_price = $old_price;
            }
            $new_total += $new_price;
            if ($old_price !== $new_price) {
                $detail[] = ['option' => '접지', 'variant' => $db_variant_name ?? $js_type, 'old_price' => $old_price, 'new_price' => $new_price];
                $updates['folding_price'] = $new_price;
            }
        }

        // 오시
        if (!empty($order['creasing_enabled'])) {
            $js_type = (string)($order['creasing_lines'] ?? '');
            $db_variant_name = $variant_map['creasing']['variants'][$js_type] ?? null;
            $old_price = (int)($order['creasing_price'] ?? 0);

            if ($db_variant_name && isset($db_prices['오시'][$db_variant_name])) {
                $new_price = calcPatternB($db_prices['오시'][$db_variant_name], $quantity);
            } else {
                $new_price = $old_price;
            }
            $new_total += $new_price;
            if ($old_price !== $new_price) {
                $detail[] = ['option' => '오시', 'variant' => $db_variant_name ?? $js_type, 'old_price' => $old_price, 'new_price' => $new_price];
                $updates['creasing_price'] = $new_price;
            }
        }

        if ($old_total !== $new_total) {
            $changes[] = [
                'no' => (int)$order['no'],
                'quantity' => $quantity,
                'old_total' => $old_total,
                'new_total' => $new_total,
                'diff' => $new_total - $old_total,
                'details' => $detail
            ];

            if ($execute) {
                $set_parts = ["additional_options_total = " . (int)$new_total];
                foreach ($updates as $col => $val) {
                    $set_parts[] = "{$col} = " . (int)$val;
                }
                $sql_update = "UPDATE mlangorder_printauto SET " . implode(', ', $set_parts) . " WHERE no = " . (int)$order['no'];
                if (mysqli_query($db, $sql_update)) {
                    $updated++;
                } else {
                    $errors[] = "주문 #{$order['no']}: " . mysqli_error($db);
                }
            }
        }
    }

    return buildRecalcResult($product_type, $changes, $updated, $errors, $execute);
}

/**
 * Pattern C 재계산 (봉투)
 */
function recalculatePatternC($db, $product_type, $db_prices, $execute) {
    $esc_type = mysqli_real_escape_string($db, $product_type);

    $sql = "SELECT no, envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price,
                   envelope_additional_options_total, quantity_value
            FROM mlangorder_printauto
            WHERE product_type = '{$esc_type}'
            AND envelope_additional_options_total > 0";
    $result = mysqli_query($db, $sql);

    $changes = [];
    $updated = 0;
    $errors = [];

    // 봉투 양면테이프 가격 데이터
    $tape_pricing = $db_prices['양면테이프']['기본'] ?? null;

    while ($order = mysqli_fetch_assoc($result)) {
        if (empty($order['envelope_tape_enabled'])) continue;

        $tape_qty = (int)($order['envelope_tape_quantity'] ?? 0);
        if ($tape_qty <= 0) {
            $tape_qty = (int)floatval($order['quantity_value'] ?? 0);
        }
        $old_price = (int)$order['envelope_tape_price'];
        $old_total = (int)$order['envelope_additional_options_total'];

        if ($tape_pricing && $tape_qty > 0) {
            $new_price = calcPatternC($tape_pricing, $tape_qty);
        } else {
            $new_price = $old_price;
        }

        $new_total = $new_price;

        if ($old_total !== $new_total) {
            $changes[] = [
                'no' => (int)$order['no'],
                'quantity' => $tape_qty,
                'old_total' => $old_total,
                'new_total' => $new_total,
                'diff' => $new_total - $old_total,
                'details' => [['option' => '양면테이프', 'variant' => '기본', 'old_price' => $old_price, 'new_price' => $new_price]]
            ];

            if ($execute) {
                $sql_update = "UPDATE mlangorder_printauto SET
                    envelope_tape_price = " . (int)$new_price . ",
                    envelope_additional_options_total = " . (int)$new_total . "
                    WHERE no = " . (int)$order['no'];
                if (mysqli_query($db, $sql_update)) {
                    $updated++;
                } else {
                    $errors[] = "주문 #{$order['no']}: " . mysqli_error($db);
                }
            }
        }
    }

    return buildRecalcResult($product_type, $changes, $updated, $errors, $execute);
}

/**
 * 재계산 결과 구성
 */
function buildRecalcResult($product_type, $changes, $updated, $errors, $execute) {
    $total_diff = array_sum(array_column($changes, 'diff'));
    $result = [
        'product_type' => $product_type,
        'affected_orders' => count($changes),
        'total_diff' => $total_diff,
        'changes' => array_slice($changes, 0, 50), // 최대 50건 상세 표시
    ];

    if ($execute) {
        $result['updated'] = $updated;
        $result['message'] = "{$updated}건 주문이 재계산되었습니다.";
        if (!empty($errors)) {
            $result['errors'] = $errors;
        }
    } else {
        $result['message'] = count($changes) > 0
            ? count($changes) . "건의 주문에서 가격 변동이 감지되었습니다. (총 차이: " . number_format($total_diff) . "원)"
            : "재계산 대상 주문이 없습니다.";
    }

    return $result;
}

/**
 * 공개 API 캐시 무효화
 */
function invalidateCache($product_type) {
    $cache_file = $_SERVER['DOCUMENT_ROOT'] . '/cache/premium_options_' . $product_type . '.json';
    if (file_exists($cache_file)) {
        @unlink($cache_file);
    }
}
