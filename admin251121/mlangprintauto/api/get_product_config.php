<?php
/**
 * 품목 설정 정보 조회 API
 * selector_labels 등 프론트엔드에서 필요한 설정 반환
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../includes/product_config.php';

// 입력 받기
$product = $_GET['product'] ?? '';

// 품목 검증
if (!$product) {
    echo json_encode([
        'success' => false,
        'message' => '품목을 선택해주세요'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$config = ProductConfig::getConfig($product);
if (!$config) {
    echo json_encode([
        'success' => false,
        'message' => '잘못된 품목입니다'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 프론트엔드에 필요한 설정만 반환
echo json_encode([
    'success' => true,
    'name' => $config['name'],
    'selectors' => $config['selectors'],
    'selector_labels' => $config['selector_labels']
], JSON_UNESCAPED_UNICODE);
?>
