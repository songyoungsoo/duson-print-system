<?php
// 장바구니 추가 오류 실시간 진단

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 장바구니 추가 오류 실시간 진단</h2>";

// POST 데이터 시뮬레이션
$_POST = [
    'action' => 'add_to_basket',
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '150',
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '08000 원형',
    'no' => ''
];

echo "<h3>전송 데이터:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// 1. 세션 시작
session_start();
$session_id = session_id();
echo "<h3>세션 ID: $session_id</h3>";

// 2. 데이터베이스 연결 테스트
echo "<h3>데이터베이스 연결 테스트:</h3>";
try {
    include "../lib/func.php";
    $connect = dbconn();
    
    if ($connect) {
        echo "✅ 데이터베이스 연결 성공<br>";
        mysqli_set_charset($connect, 'utf8');
        echo "✅ UTF-8 문자셋 설정 완료<br>";
    } else {
        echo "❌ 데이터베이스 연결 실패<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ 데이터베이스 연결 오류: " . $e->getMessage() . "<br>";
    exit;
}

// 3. shop_temp 테이블 확인
echo "<h3>shop_temp 테이블 확인:</h3>";
$query = "DESCRIBE shop_temp";
$result = mysqli_query($connect, $query);
if ($result) {
    echo "✅ shop_temp 테이블 존재<br>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = mysqli_fetch_array($result)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ shop_temp 테이블 확인 실패: " . mysqli_error($connect) . "<br>";
}

// 4. 입력값 처리 테스트
echo "<h3>입력값 처리 테스트:</h3>";
$jong = $_POST['jong'] ?? '';
$garo = (int)($_POST['garo'] ?? 0);
$sero = (int)($_POST['sero'] ?? 0);
$mesu = (int)($_POST['mesu'] ?? 0);
$uhyung = (int)($_POST['uhyung'] ?? 0);
$domusong = $_POST['domusong'] ?? '';
$no = $_POST['no'] ?? '';

echo "jong: '$jong'<br>";
echo "garo: $garo<br>";
echo "sero: $sero<br>";
echo "mesu: $mesu<br>";
echo "uhyung: $uhyung<br>";
echo "domusong: '$domusong'<br>";
echo "no: '$no'<br>";

// 5. 가격 계산 테스트
echo "<h3>가격 계산 테스트:</h3>";
$base_price = ($garo + 4) * ($sero + 4) * $mesu * 0.15;
$domusong_price = (int)substr($domusong, 0, 5);
$st_price = $base_price + $uhyung + $domusong_price + 7000;
$st_price_vat = $st_price * 1.1;

echo "base_price: " . number_format($base_price) . "<br>";
echo "domusong_price: " . number_format($domusong_price) . "<br>";
echo "st_price: " . number_format($st_price) . "<br>";
echo "st_price_vat: " . number_format($st_price_vat) . "<br>";

// 6. SQL 쿼리 테스트
echo "<h3>SQL 쿼리 테스트:</h3>";
$regdate = time();

// SQL 인젝션 방지
$session_id_escaped = mysqli_real_escape_string($connect, $session_id);
$no_escaped = mysqli_real_escape_string($connect, $no);
$jong_escaped = mysqli_real_escape_string($connect, $jong);
$domusong_escaped = mysqli_real_escape_string($connect, $domusong);

$query = "INSERT INTO shop_temp(session_id, parent, jong, garo, sero, mesu, domusong, uhyung, st_price, st_price_vat, regdate)
          VALUES('$session_id_escaped', '$no_escaped', '$jong_escaped', '$garo', '$sero', '$mesu', '$domusong_escaped', '$uhyung', '$st_price', '$st_price_vat', '$regdate')";

echo "<strong>실행할 쿼리:</strong><br>";
echo "<pre>" . htmlspecialchars($query) . "</pre>";

// 7. 실제 삽입 실행
echo "<h3>실제 삽입 실행:</h3>";
if (mysqli_query($connect, $query)) {
    echo "✅ 데이터 삽입 성공!<br>";
    
    // 삽입된 데이터 확인
    $check_query = "SELECT * FROM shop_temp WHERE session_id='$session_id_escaped' ORDER BY no DESC LIMIT 1";
    $check_result = mysqli_query($connect, $check_query);
    if ($check_data = mysqli_fetch_array($check_result)) {
        echo "<h4>삽입된 데이터:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach ($check_data as $key => $value) {
            if (!is_numeric($key)) {
                echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
            }
        }
        echo "</table>";
    }
} else {
    echo "❌ 데이터 삽입 실패: " . mysqli_error($connect) . "<br>";
}

// 8. JSON 응답 테스트
echo "<h3>JSON 응답 테스트:</h3>";
$response = [
    'success' => true,
    'message' => '장바구니에 추가되었습니다.',
    'price' => number_format($st_price),
    'price_vat' => number_format($st_price_vat)
];

echo "<strong>JSON 응답:</strong><br>";
echo "<pre>" . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";

if ($connect) {
    mysqli_close($connect);
}
?>