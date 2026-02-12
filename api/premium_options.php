<?php
/**
 * 프리미엄 옵션 공개 API (고객용, 읽기 전용)
 * GET ?product_type=namecard — 해당 품목의 활성 옵션+가격 반환
 *
 * 인증 불필요, 캐시 지원
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../db.php';

$product_type = $_GET['product_type'] ?? '';

// 허용된 product_type만 (스티커/자석스티커/NCR 제외)
$allowed_types = ['namecard', 'merchandisebond', 'inserted', 'littleprint', 'cadarok', 'envelope'];

if (!in_array($product_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '유효하지 않은 제품 유형입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 파일 기반 캐시 (5분)
$cache_dir = __DIR__ . '/../cache';
if (!is_dir($cache_dir)) {
    @mkdir($cache_dir, 0755, true);
}
$cache_file = $cache_dir . '/premium_options_' . $product_type . '.json';
$cache_ttl = 300; // 5분

if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    echo file_get_contents($cache_file);
    exit;
}

// DB에서 조회
$stmt = mysqli_prepare($db, "
    SELECT o.id AS option_id, o.option_name, o.sort_order,
           v.id AS variant_id, v.variant_name, v.pricing_config, v.is_default, v.display_order
    FROM premium_options o
    JOIN premium_option_variants v ON v.option_id = o.id
    WHERE o.product_type = ? AND o.is_active = 1 AND v.is_active = 1
    ORDER BY o.sort_order, v.display_order
");
mysqli_stmt_bind_param($stmt, "s", $product_type);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$options = [];
while ($row = mysqli_fetch_assoc($result)) {
    $option_name = $row['option_name'];
    if (!isset($options[$option_name])) {
        $options[$option_name] = [
            'option_id' => (int)$row['option_id'],
            'option_name' => $option_name,
            'sort_order' => (int)$row['sort_order'],
            'variants' => []
        ];
    }
    $options[$option_name]['variants'][] = [
        'variant_id' => (int)$row['variant_id'],
        'variant_name' => $row['variant_name'],
        'pricing_config' => json_decode($row['pricing_config'], true),
        'is_default' => (bool)$row['is_default'],
        'display_order' => (int)$row['display_order']
    ];
}
mysqli_stmt_close($stmt);

$response = json_encode([
    'success' => true,
    'product_type' => $product_type,
    'options' => array_values($options)
], JSON_UNESCAPED_UNICODE);

// 캐시 저장
@file_put_contents($cache_file, $response);

echo $response;
