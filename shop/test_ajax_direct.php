<?php
// AJAX 요청 직접 테스트

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 AJAX 요청 직접 테스트</h2>";

// POST 데이터 설정
$_POST = [
    'action' => 'calculate',
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '150',
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '08000 원형'
];

echo "<h3>전송할 데이터:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// cURL로 AJAX 요청 시뮬레이션
$url = 'http://localhost/shop/calculate_price.php';
$data = http_build_query($_POST);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>응답 결과:</h3>";
echo "HTTP 코드: $http_code<br>";
echo "응답 내용:<br>";
echo "<pre>$response</pre>";

// JSON 파싱 테스트
$json_data = json_decode($response, true);
if ($json_data) {
    echo "<h3>JSON 파싱 결과:</h3>";
    echo "<pre>";
    print_r($json_data);
    echo "</pre>";
    
    if (isset($json_data['success']) && $json_data['success']) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "<h4>✅ 성공!</h4>";
        echo "가격: " . $json_data['price'] . "원<br>";
        echo "VAT 포함: " . $json_data['price_vat'] . "원";
        echo "</div>";
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
        echo "<h4>❌ 실패!</h4>";
        echo "오류 메시지: " . ($json_data['message'] ?? '알 수 없는 오류');
        echo "</div>";
    }
} else {
    echo "<div style='background: #fff3e0; padding: 10px; border-radius: 5px;'>";
    echo "<h4>⚠️ JSON 파싱 실패</h4>";
    echo "응답이 올바른 JSON 형식이 아닙니다.";
    echo "</div>";
}
?>