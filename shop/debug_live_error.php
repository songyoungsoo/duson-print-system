<?php
// 실시간 오류 진단

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🚨 실시간 오류 진단</h2>";

// 1. 브라우저에서 실제 AJAX 요청 시뮬레이션
echo "<h3>1. 실제 AJAX 요청 테스트</h3>";

$test_data = [
    'action' => 'calculate',
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '150',
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '08000 원형'
];

echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "<strong>테스트 데이터:</strong><br>";
foreach ($test_data as $key => $value) {
    echo "$key: $value<br>";
}
echo "</div><br>";

// 2. cURL로 실제 요청
$url = 'http://localhost/shop/calculate_price.php';
$postData = http_build_query($test_data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>2. 응답 결과</h3>";
echo "<div style='background: #e3f2fd; padding: 10px; border-radius: 5px;'>";
echo "<strong>HTTP 코드:</strong> $httpCode<br>";
if ($error) {
    echo "<strong>cURL 오류:</strong> $error<br>";
}
echo "<strong>응답 길이:</strong> " . strlen($response) . " bytes<br>";
echo "</div><br>";

echo "<h3>3. 원시 응답 내용</h3>";
echo "<div style='background: #fff3e0; padding: 10px; border-radius: 5px; font-family: monospace; white-space: pre-wrap;'>";
echo htmlspecialchars($response);
echo "</div><br>";

// 3. JSON 파싱 시도
echo "<h3>4. JSON 파싱 결과</h3>";
$jsonData = json_decode($response, true);
$jsonError = json_last_error();

if ($jsonError === JSON_ERROR_NONE && $jsonData) {
    echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ JSON 파싱 성공</strong><br>";
    echo "<pre>";
    print_r($jsonData);
    echo "</pre>";
    
    if (isset($jsonData['success'])) {
        if ($jsonData['success']) {
            echo "<h4>🎉 계산 성공!</h4>";
            echo "가격: " . $jsonData['price'] . "원<br>";
            echo "VAT 포함: " . $jsonData['price_vat'] . "원<br>";
        } else {
            echo "<h4>❌ 계산 실패</h4>";
            echo "오류 메시지: " . ($jsonData['message'] ?? '알 수 없는 오류') . "<br>";
        }
    }
    echo "</div>";
} else {
    echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
    echo "<strong>❌ JSON 파싱 실패</strong><br>";
    echo "JSON 오류 코드: $jsonError<br>";
    echo "JSON 오류 메시지: " . json_last_error_msg() . "<br>";
    echo "</div>";
}

// 4. 데이터베이스 연결 테스트
echo "<h3>5. 데이터베이스 상태 확인</h3>";
try {
    include "../lib/func.php";
    $connect = dbconn();
    
    if ($connect) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "✅ 데이터베이스 연결 성공<br>";
        
        // 테이블 데이터 확인
        $tables = ['shop_d1', 'shop_d2', 'shop_d3', 'shop_d4'];
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as count FROM $table";
            $result = mysqli_query($connect, $query);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                echo "$table: {$row['count']}개 레코드<br>";
            } else {
                echo "$table: 쿼리 실패<br>";
            }
        }
        echo "</div>";
        
        mysqli_close($connect);
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
        echo "❌ 데이터베이스 연결 실패<br>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
    echo "❌ 데이터베이스 오류: " . $e->getMessage() . "<br>";
    echo "</div>";
}

// 5. JavaScript 테스트 버튼
echo "<h3>6. 브라우저에서 직접 테스트</h3>";
echo "<button onclick='testAjax()' style='padding: 10px 20px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer;'>AJAX 테스트 실행</button>";
echo "<div id='ajaxResult' style='margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 5px; display: none;'></div>";

echo "<script>
function testAjax() {
    const formData = new FormData();
    formData.append('action', 'calculate');
    formData.append('jong', 'jil 아트유광코팅');
    formData.append('garo', '100');
    formData.append('sero', '150');
    formData.append('mesu', '1000');
    formData.append('uhyung', '0');
    formData.append('domusong', '08000 원형');
    
    const resultDiv = document.getElementById('ajaxResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '⏳ 요청 중...';
    
    fetch('calculate_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        resultDiv.innerHTML = '<strong>원시 응답:</strong><br><pre>' + text + '</pre>';
        
        try {
            const data = JSON.parse(text);
            if (data.success) {
                resultDiv.innerHTML += '<div style=\"background: #e8f5e8; padding: 10px; margin-top: 10px; border-radius: 5px;\"><strong>✅ 성공!</strong><br>가격: ' + data.price + '원<br>VAT 포함: ' + data.price_vat + '원</div>';
            } else {
                resultDiv.innerHTML += '<div style=\"background: #ffebee; padding: 10px; margin-top: 10px; border-radius: 5px;\"><strong>❌ 실패!</strong><br>오류: ' + data.message + '</div>';
            }
        } catch (e) {
            resultDiv.innerHTML += '<div style=\"background: #fff3e0; padding: 10px; margin-top: 10px; border-radius: 5px;\"><strong>⚠️ JSON 파싱 오류:</strong><br>' + e.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        resultDiv.innerHTML = '<div style=\"background: #ffebee; padding: 10px; border-radius: 5px;\"><strong>❌ 네트워크 오류:</strong><br>' + error.message + '</div>';
    });
}
</script>";
?>