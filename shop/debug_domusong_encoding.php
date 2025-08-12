<?php
// 도무송 한글 깨짐 문제 진단

header("Content-Type: text/html; charset=UTF-8");
session_start();

echo "<h2>🔍 도무송 한글 깨짐 문제 진단</h2>";

include "../lib/func.php";
$connect = dbconn();

// 1. 데이터베이스 문자셋 확인
echo "<h3>1. 데이터베이스 문자셋 확인</h3>";
$query = "SHOW VARIABLES LIKE 'character_set%'";
$result = mysqli_query($connect, $query);
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Variable</th><th>Value</th></tr>";
while ($row = mysqli_fetch_array($result)) {
    echo "<tr><td>{$row['Variable_name']}</td><td>{$row['Value']}</td></tr>";
}
echo "</table>";

// 2. shop_temp 테이블 문자셋 확인
echo "<h3>2. shop_temp 테이블 구조 및 문자셋</h3>";
$query = "SHOW CREATE TABLE shop_temp";
$result = mysqli_query($connect, $query);
if ($row = mysqli_fetch_array($result)) {
    echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
}

// 3. 현재 저장된 도무송 데이터 확인
echo "<h3>3. 현재 저장된 도무송 데이터</h3>";
$session_id = session_id();
$query = "SELECT no, domusong, HEX(domusong) as hex_domusong FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC LIMIT 5";
$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>NO</th><th>도무송 원본</th><th>HEX 값</th><th>substr 결과</th><th>길이</th></tr>";
    
    while ($row = mysqli_fetch_array($result)) {
        $domusong = $row['domusong'];
        $hex = $row['hex_domusong'];
        $substr_result = substr($domusong, 6, 8);
        
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>" . htmlspecialchars($domusong) . "</td>";
        echo "<td>$hex</td>";
        echo "<td>" . htmlspecialchars($substr_result) . "</td>";
        echo "<td>" . strlen($domusong) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>저장된 데이터가 없습니다.</p>";
}

// 4. 도무송 옵션 테스트
echo "<h3>4. 도무송 옵션 테스트</h3>";
$test_options = [
    '00000 사각',
    '08000 원형',
    '08000 타원',
    '19000 복잡'
];

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>원본</th><th>substr(0,5)</th><th>substr(6,8)</th><th>mb_substr(6,8)</th><th>원본 길이</th><th>바이트 길이</th></tr>";

foreach ($test_options as $option) {
    $price = substr($option, 0, 5);
    $name = substr($option, 6, 8);
    $mb_name = mb_substr($option, 6, 8, 'UTF-8');
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($option) . "</td>";
    echo "<td>" . htmlspecialchars($price) . "</td>";
    echo "<td>" . htmlspecialchars($name) . "</td>";
    echo "<td>" . htmlspecialchars($mb_name) . "</td>";
    echo "<td>" . mb_strlen($option, 'UTF-8') . "</td>";
    echo "<td>" . strlen($option) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 5. 문자열 분석
echo "<h3>5. 문자열 상세 분석</h3>";
$test_string = '08000 원형';
echo "<p><strong>테스트 문자열:</strong> " . htmlspecialchars($test_string) . "</p>";
echo "<p><strong>문자 단위 분석:</strong></p>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>위치</th><th>문자</th><th>바이트</th><th>HEX</th></tr>";

for ($i = 0; $i < strlen($test_string); $i++) {
    $char = $test_string[$i];
    $byte = ord($char);
    $hex = dechex($byte);
    
    echo "<tr>";
    echo "<td>$i</td>";
    echo "<td>" . htmlspecialchars($char) . "</td>";
    echo "<td>$byte</td>";
    echo "<td>$hex</td>";
    echo "</tr>";
}
echo "</table>";

// 6. 올바른 문자열 추출 방법 제안
echo "<h3>6. 올바른 문자열 추출 방법</h3>";
$test_domusong = '08000 원형';
echo "<p><strong>원본:</strong> " . htmlspecialchars($test_domusong) . "</p>";

// 방법 1: substr 사용
$method1 = substr($test_domusong, 6);
echo "<p><strong>방법 1 (substr):</strong> " . htmlspecialchars($method1) . "</p>";

// 방법 2: mb_substr 사용
$method2 = mb_substr($test_domusong, 6, null, 'UTF-8');
echo "<p><strong>방법 2 (mb_substr):</strong> " . htmlspecialchars($method2) . "</p>";

// 방법 3: explode 사용
$parts = explode(' ', $test_domusong, 2);
$method3 = isset($parts[1]) ? $parts[1] : '';
echo "<p><strong>방법 3 (explode):</strong> " . htmlspecialchars($method3) . "</p>";

// 방법 4: preg_replace 사용
$method4 = preg_replace('/^\d+\s+/', '', $test_domusong);
echo "<p><strong>방법 4 (preg_replace):</strong> " . htmlspecialchars($method4) . "</p>";

if ($connect) {
    mysqli_close($connect);
}
?>