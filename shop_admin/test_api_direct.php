<?php
/**
 * LOGEN API 직접 테스트
 */

require_once __DIR__ . '/logen_api_config.php';

echo "=== LOGEN API 설정 확인 ===\n";
echo "고객사 코드: " . LOGEN_CUST_CD . "\n";
echo "사용자명: " . LOGEN_USER_ID . "\n";
echo "비밀번호: " . str_repeat('*', strlen(LOGEN_PASSWORD)) . "\n";
echo "API URL: " . LOGEN_API_BASE_URL . LOGEN_API_GET_SLIP_NO . "\n\n";

echo "=== API 요청 시작 ===\n";

$endpoint = LOGEN_API_BASE_URL . LOGEN_API_GET_SLIP_NO;
$payload = [
    'userId' => LOGEN_USER_ID,
    'slipQty' => 1
];

echo "엔드포인트: $endpoint\n";
echo "요청 데이터: " . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "=== API 응답 ===\n";
echo "HTTP 코드: $httpCode\n";
if ($error) {
    echo "cURL 에러: $error\n";
}
echo "응답 내용:\n";
echo $response . "\n";

if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);
    echo "\n=== 파싱된 응답 ===\n";
    print_r($data);
}
