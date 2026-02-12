<?php
/**
 * 프리미엄 옵션 관리자 API
 * Actions: list, get, create_option, create_variant, update_variant,
 *          delete_variant, toggle_option, reorder, recalculate_orders
 */
require_once __DIR__ . '/base.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

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
    $input = json_decode(file_get_contents('php://input'), true);
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
    $input = json_decode(file_get_contents('php://input'), true);
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
    $input = json_decode(file_get_contents('php://input'), true);
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
    $input = json_decode(file_get_contents('php://input'), true);
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
    $input = json_decode(file_get_contents('php://input'), true);
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
    $input = json_decode(file_get_contents('php://input'), true);
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
    $input = json_decode(file_get_contents('php://input'), true);
    $product_type = $input['product_type'] ?? '';
    $execute = !empty($input['execute']);

    $allowed = ['namecard', 'merchandisebond', 'inserted', 'littleprint', 'cadarok', 'envelope'];
    if (!in_array($product_type, $allowed)) jsonResponse(false, '유효하지 않은 제품 유형입니다.');

    // 해당 품목 주문 중 프리미엄 옵션이 있는 주문 조회
    $json_col = in_array($product_type, ['namecard', 'merchandisebond']) ? 'premium_options' : 'additional_options';
    $total_col = in_array($product_type, ['namecard', 'merchandisebond']) ? 'premium_options_total' : 'additional_options_total';

    if ($product_type === 'envelope') {
        $total_col = 'envelope_additional_options_total';
    }

    // 해당 품목의 최근 주문 수 조회
    $product_names_map = [
        'namecard' => '명함', 'merchandisebond' => '상품권',
        'inserted' => '전단지', 'littleprint' => '포스터',
        'cadarok' => '카다록', 'envelope' => '봉투'
    ];
    $pname = $product_names_map[$product_type] ?? $product_type;

    $r = mysqli_query($db, "
        SELECT COUNT(*) AS cnt FROM mlangorder_printauto
        WHERE Type LIKE '%" . mysqli_real_escape_string($db, $pname) . "%'
        AND {$total_col} > 0
    ");
    $count = $r ? (int)mysqli_fetch_assoc($r)['cnt'] : 0;

    if (!$execute) {
        jsonResponse(true, 'OK', [
            'product_type' => $product_type,
            'affected_orders' => $count,
            'json_column' => $json_col,
            'total_column' => $total_col,
            'message' => "{$count}건의 주문이 재계산 대상입니다."
        ]);
        return;
    }

    // 실제 재계산은 복잡하므로 여기서는 영향 건수만 반환
    jsonResponse(true, '재계산 기능은 추후 구현 예정입니다.', ['affected_orders' => $count]);
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
