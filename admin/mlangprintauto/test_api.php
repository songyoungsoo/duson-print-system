<?php
/**
 * API 엔드포인트 테스트 스크립트
 *
 * Usage: php test_api.php
 * Browser: http://localhost/admin/mlangprintauto/test_api.php
 */

echo "=== API 엔드포인트 테스트 ===\n\n";

// 1. product_crud.php API 테스트
echo "1. Product CRUD API 테스트\n";
echo "----------------------------\n";

// GET 요청 테스트 (명함 데이터 조회)
$api_url = "http://localhost/admin/mlangprintauto/api/product_crud.php";

$test_data = [
    'action' => 'get',
    'product' => 'namecard',
    'id' => 1
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: {$http_code}\n";
echo "Response: {$response}\n\n";

// 2. get_product_config.php API 테스트
echo "2. Product Config API 테스트\n";
echo "----------------------------\n";

$config_url = "http://localhost/admin/mlangprintauto/api/get_product_config.php?product=namecard";
$config_response = file_get_contents($config_url);
echo "Product Config Response:\n";
echo substr($config_response, 0, 500) . "...\n\n";

// 3. get_categories.php API 테스트
echo "3. Categories API 테스트\n";
echo "----------------------------\n";

$cat_url = "http://localhost/admin/mlangprintauto/api/get_categories.php?product=namecard&selector=MY_type";
$cat_response = file_get_contents($cat_url);
echo "Categories Response:\n";
echo substr($cat_response, 0, 500) . "...\n\n";

echo "=== 테스트 완료 ===\n";
?>
