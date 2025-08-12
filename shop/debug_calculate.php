<?php
// 스티커 가격 계산 디버깅
session_start();
header('Content-Type: application/json');

echo "<h2>스티커 가격 계산 디버깅</h2>";

// 1. POST 데이터 확인
echo "<h3>1. POST 데이터</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// 2. 데이터베이스 연결 확인
echo "<h3>2. 데이터베이스 연결</h3>";
try {
    $HomeDir = "../../";
    include "../lib/func.php";
    $connect = dbconn();
    echo "✅ 데이터베이스 연결 성공<br>";
} catch (Exception $e) {
    echo "❌ 데이터베이스 연결 실패: " . $e->getMessage() . "<br>";
}

// 3. 테스트 계산
echo "<h3>3. 테스트 계산</h3>";
$test_data = [
    'action' => 'calculate',
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '100', 
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '00000 사각'
];

echo "테스트 데이터:<br>";
foreach ($test_data as $key => $value) {
    echo "- $key: $value<br>";
}

// 4. 실제 계산 실행
echo "<h3>4. 계산 결과</h3>";
try {
    $jong = $test_data['jong'];
    $garo = (int)$test_data['garo'];
    $sero = (int)$test_data['sero'];
    $mesu = (int)$test_data['mesu'];
    $uhyung = (int)$test_data['uhyung'];
    $domusong = $test_data['domusong'];
    
    echo "변환된 값들:<br>";
    echo "- jong: $jong<br>";
    echo "- garo: $garo<br>";
    echo "- sero: $sero<br>";
    echo "- mesu: $mesu<br>";
    echo "- uhyung: $uhyung<br>";
    echo "- domusong: $domusong<br><br>";
    
    // 면적 계산
    $area = ($garo * $sero) / 100; // cm²
    echo "면적: $area cm²<br>";
    
    // 재질별 단가
    $material_prices = [
        'jil 아트유광코팅' => 0.5,
        'jil 아트무광코팅' => 0.6,
        'jil 아트비코팅' => 0.4,
        'jka 강접아트유광코팅' => 0.7,
        'cka 초강접아트코팅' => 0.8,
        'cka 초강접아트비코팅' => 0.8,
        'jsp 유포지' => 0.3,
        'jsp 은데드롱' => 1.0,
        'jsp 투명스티커' => 1.2,
        'jil 모조비코팅' => 0.3,
        'jsp 크라프트지' => 0.4
    ];
    
    $unit_price = $material_prices[$jong] ?? 0.5;
    echo "재질 단가: $unit_price<br>";
    
    // 수량별 할인율
    $discount_rate = 1.0;
    if ($mesu >= 10000) $discount_rate = 0.8;
    elseif ($mesu >= 5000) $discount_rate = 0.85;
    elseif ($mesu >= 2000) $discount_rate = 0.9;
    elseif ($mesu >= 1000) $discount_rate = 0.95;
    
    echo "할인율: $discount_rate<br>";
    
    // 도무송 가격
    $domusong_price = 0;
    if (preg_match('/^(\d+)/', $domusong, $matches)) {
        $domusong_price = (int)$matches[1];
    }
    echo "도무송 가격: $domusong_price<br>";
    
    // 최종 계산
    $base_price = $area * $unit_price * $mesu * $discount_rate;
    $total_price = $base_price + $uhyung + $domusong_price;
    $total_price_vat = $total_price * 1.1;
    
    echo "<br><strong>계산 결과:</strong><br>";
    echo "- 기본 가격: " . number_format($base_price) . "원<br>";
    echo "- 디자인비: " . number_format($uhyung) . "원<br>";
    echo "- 도무송비: " . number_format($domusong_price) . "원<br>";
    echo "- 총 가격: " . number_format($total_price) . "원<br>";
    echo "- 부가세 포함: " . number_format($total_price_vat) . "원<br>";
    
} catch (Exception $e) {
    echo "❌ 계산 오류: " . $e->getMessage() . "<br>";
}

// 5. JSON 응답 테스트
echo "<h3>5. JSON 응답 테스트</h3>";
$json_response = [
    'success' => true,
    'price' => number_format($total_price ?? 0),
    'price_vat' => number_format($total_price_vat ?? 0)
];
echo "JSON 응답:<br>";
echo "<pre>" . json_encode($json_response, JSON_PRETTY_PRINT) . "</pre>";
?>