<?php
// 간단한 버전 테스트

echo "<h2>🧪 간단한 버전 테스트</h2>";

$test_data = [
    'action' => 'calculate',
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '150',
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '08000 원형'
];

echo "<h3>테스트 데이터:</h3>";
echo "<pre>";
print_r($test_data);
echo "</pre>";

// 간단한 버전 테스트
echo "<h3>간단한 버전 (calculate_price_simple.php) 테스트:</h3>";
$url = 'http://localhost/shop/calculate_price_simple.php';
$postData = http_build_query($test_data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "<strong>HTTP 코드:</strong> $httpCode<br>";
echo "<strong>응답:</strong><br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "</div>";

$jsonData = json_decode($response, true);
if ($jsonData) {
    if ($jsonData['success']) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<h4>✅ 간단한 버전 성공!</h4>";
        echo "가격: " . $jsonData['price'] . "원<br>";
        echo "VAT 포함: " . $jsonData['price_vat'] . "원<br>";
        if (isset($jsonData['debug'])) {
            echo "<strong>디버그 정보:</strong><br>";
            echo "<pre>";
            print_r($jsonData['debug']);
            echo "</pre>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<h4>❌ 간단한 버전 실패</h4>";
        echo "오류: " . $jsonData['message'] . "<br>";
        if (isset($jsonData['debug'])) {
            echo "<strong>디버그 정보:</strong><br>";
            echo "<pre>";
            print_r($jsonData['debug']);
            echo "</pre>";
        }
        echo "</div>";
    }
}

// 원본 버전 테스트
echo "<h3>원본 버전 (calculate_price.php) 테스트:</h3>";
$url2 = 'http://localhost/shop/calculate_price.php';

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url2);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HEADER, false);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "<strong>HTTP 코드:</strong> $httpCode2<br>";
echo "<strong>응답:</strong><br>";
echo "<pre>" . htmlspecialchars($response2) . "</pre>";
echo "</div>";

$jsonData2 = json_decode($response2, true);
if ($jsonData2) {
    if ($jsonData2['success']) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<h4>✅ 원본 버전 성공!</h4>";
        echo "가격: " . $jsonData2['price'] . "원<br>";
        echo "VAT 포함: " . $jsonData2['price_vat'] . "원<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<h4>❌ 원본 버전 실패</h4>";
        echo "오류: " . $jsonData2['message'] . "<br>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #fff3e0; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
    echo "<h4>⚠️ 원본 버전 JSON 파싱 실패</h4>";
    echo "JSON 오류: " . json_last_error_msg() . "<br>";
    echo "</div>";
}
?>