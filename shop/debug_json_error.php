<?php
// JSON 오류 정확한 진단

error_reporting(E_ALL);
ini_set('display_errors', 0); // 화면 출력 끄기

echo "<h2>🔍 JSON 오류 정확한 진단</h2>";

// 출력 버퍼링 시작
ob_start();

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

// calculate_price.php 직접 실행
try {
    session_start();
    
    $HomeDir = "../../";
    include "../lib/func.php";
    $connect = dbconn();
    
    if ($_POST['action'] !== 'calculate') {
        throw new Exception('잘못된 요청입니다.');
    }
    
    $jong = $_POST['jong'] ?? '';
    $garo = (int)($_POST['garo'] ?? 0);
    $sero = (int)($_POST['sero'] ?? 0);
    $mesu = (int)($_POST['mesu'] ?? 0);
    $uhyung = (int)($_POST['uhyung'] ?? 0);
    $domusong = $_POST['domusong'] ?? '';
    
    // 입력값 검증
    if (!$garo) throw new Exception('가로사이즈를 입력하세요');
    if (!$sero) throw new Exception('세로사이즈를 입력하세요');
    
    // 간단한 계산으로 대체
    $base_price = ($garo + 4) * ($sero + 4) * $mesu * 0.15;
    $domusong_price = (int)substr($domusong, 0, 5);
    $total_price = $base_price + $domusong_price + 7000;
    $total_price_vat = $total_price * 1.1;
    
    $result = [
        'success' => true,
        'price' => number_format($total_price),
        'price_vat' => number_format($total_price_vat)
    ];
    
    // 출력 버퍼 내용 확인
    $buffer_content = ob_get_contents();
    ob_clean();
    
    echo "<h3>1. 출력 버퍼 내용 (JSON 이전 출력)</h3>";
    if (empty($buffer_content)) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>✅ 출력 버퍼 깨끗함</div>";
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
        echo "❌ 출력 버퍼에 내용 발견:<br>";
        echo "<pre>" . htmlspecialchars($buffer_content) . "</pre>";
        echo "</div>";
    }
    
    echo "<h3>2. JSON 출력 테스트</h3>";
    $json_output = json_encode($result, JSON_UNESCAPED_UNICODE);
    
    if ($json_output === false) {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
        echo "❌ JSON 인코딩 실패<br>";
        echo "오류: " . json_last_error_msg() . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "✅ JSON 인코딩 성공<br>";
        echo "<strong>JSON 출력:</strong><br>";
        echo "<pre>" . htmlspecialchars($json_output) . "</pre>";
        echo "</div>";
        
        // JSON 파싱 테스트
        $parsed = json_decode($json_output, true);
        if ($parsed) {
            echo "<div style='background: #e3f2fd; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
            echo "✅ JSON 파싱 테스트 성공<br>";
            echo "<pre>";
            print_r($parsed);
            echo "</pre>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    $buffer_content = ob_get_contents();
    ob_clean();
    
    echo "<h3>❌ 예외 발생</h3>";
    echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px;'>";
    echo "오류 메시지: " . $e->getMessage() . "<br>";
    echo "파일: " . $e->getFile() . "<br>";
    echo "라인: " . $e->getLine() . "<br>";
    echo "</div>";
    
    if (!empty($buffer_content)) {
        echo "<h3>출력 버퍼 내용:</h3>";
        echo "<pre>" . htmlspecialchars($buffer_content) . "</pre>";
    }
    
    $error_result = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    $error_json = json_encode($error_result, JSON_UNESCAPED_UNICODE);
    echo "<h3>오류 JSON:</h3>";
    echo "<pre>" . htmlspecialchars($error_json) . "</pre>";
}

ob_end_clean();

// 3. 실제 HTTP 요청 테스트
echo "<h3>3. 실제 HTTP 요청 테스트</h3>";
$url = 'http://localhost/shop/calculate_price.php';
$postData = http_build_query($_POST);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // 헤더도 포함

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "<strong>HTTP 코드:</strong> $httpCode<br>";
echo "<strong>전체 응답 (헤더 포함):</strong><br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "</div>";

// 헤더와 본문 분리
$parts = explode("\r\n\r\n", $response, 2);
if (count($parts) == 2) {
    $headers = $parts[0];
    $body = $parts[1];
    
    echo "<h4>응답 헤더:</h4>";
    echo "<pre>" . htmlspecialchars($headers) . "</pre>";
    
    echo "<h4>응답 본문:</h4>";
    echo "<pre>" . htmlspecialchars($body) . "</pre>";
    
    // JSON 파싱 시도
    $json_data = json_decode($body, true);
    if ($json_data) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "✅ JSON 파싱 성공<br>";
        echo "<pre>";
        print_r($json_data);
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #ffebee; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "❌ JSON 파싱 실패<br>";
        echo "JSON 오류: " . json_last_error_msg() . "<br>";
        echo "응답 길이: " . strlen($body) . " bytes<br>";
        
        // 첫 100자만 표시
        echo "응답 시작 부분: " . htmlspecialchars(substr($body, 0, 100)) . "<br>";
        echo "</div>";
    }
}
?>