<?php
// 수정된 버전 테스트

echo "<h2>🔧 수정된 버전 테스트</h2>";

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

// 수정된 버전 테스트
echo "<h3>수정된 버전 (calculate_price_fixed.php) 테스트:</h3>";
$url = 'http://localhost/shop/calculate_price_fixed.php';
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
echo "<strong>응답 길이:</strong> " . strlen($response) . " bytes<br>";
echo "<strong>응답:</strong><br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "</div>";

$jsonData = json_decode($response, true);
$jsonError = json_last_error();

if ($jsonError === JSON_ERROR_NONE && $jsonData) {
    if ($jsonData['success']) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<h4>✅ 수정된 버전 성공!</h4>";
        echo "가격: " . $jsonData['price'] . "원<br>";
        echo "VAT 포함: " . $jsonData['price_vat'] . "원<br>";
        echo "</div>";
        
        // 이 버전이 성공하면 원본 파일을 교체
        echo "<div style='background: #e3f2fd; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<h4>🔄 원본 파일 교체 준비</h4>";
        echo "수정된 버전이 정상 작동하므로 원본 calculate_price.php를 교체할 수 있습니다.";
        echo "</div>";
        
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "<h4>❌ 수정된 버전 실패</h4>";
        echo "오류: " . $jsonData['message'] . "<br>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #fff3e0; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
    echo "<h4>⚠️ JSON 파싱 실패</h4>";
    echo "JSON 오류: " . json_last_error_msg() . "<br>";
    echo "오류 코드: $jsonError<br>";
    
    // 응답의 첫 부분 분석
    echo "<strong>응답 시작 부분:</strong><br>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre>";
    echo "</div>";
}

// JavaScript 테스트도 추가
echo "<h3>브라우저에서 직접 테스트:</h3>";
echo "<button onclick='testFixedVersion()' style='padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;'>수정된 버전 AJAX 테스트</button>";
echo "<div id='fixedResult' style='margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 5px; display: none;'></div>";

echo "<script>
function testFixedVersion() {
    const formData = new FormData();
    formData.append('action', 'calculate');
    formData.append('jong', 'jil 아트유광코팅');
    formData.append('garo', '100');
    formData.append('sero', '150');
    formData.append('mesu', '1000');
    formData.append('uhyung', '0');
    formData.append('domusong', '08000 원형');
    
    const resultDiv = document.getElementById('fixedResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '⏳ 요청 중...';
    
    fetch('calculate_price_fixed.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Fixed version response:', text);
        
        try {
            const data = JSON.parse(text);
            if (data.success) {
                resultDiv.innerHTML = '<div style=\"background: #e8f5e8; padding: 10px; border-radius: 5px;\"><strong>✅ 성공!</strong><br>가격: ' + data.price + '원<br>VAT 포함: ' + data.price_vat + '원</div>';
            } else {
                resultDiv.innerHTML = '<div style=\"background: #ffebee; padding: 10px; border-radius: 5px;\"><strong>❌ 실패!</strong><br>오류: ' + data.message + '</div>';
            }
        } catch (e) {
            resultDiv.innerHTML = '<div style=\"background: #fff3e0; padding: 10px; border-radius: 5px;\"><strong>⚠️ JSON 파싱 오류:</strong><br>' + e.message + '<br><strong>응답:</strong><br><pre>' + text + '</pre></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = '<div style=\"background: #ffebee; padding: 10px; border-radius: 5px;\"><strong>❌ 네트워크 오류:</strong><br>' + error.message + '</div>';
    });
}
</script>";
?>