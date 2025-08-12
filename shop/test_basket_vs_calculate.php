<?php
// basket_post.php와 calculate_price.php 로직 비교 테스트

include "../lib/func.php";
$connect = dbconn();

// 테스트 데이터
$test_data = [
    'jong' => 'jil 아트유광코팅',
    'garo' => 100,
    'sero' => 150,
    'mesu' => 1000,
    'uhyung' => 0,
    'domusong' => '08000 원형'
];

echo "<h2>🔍 basket_post.php vs calculate_price.php 로직 비교</h2>";
echo "<h3>테스트 데이터:</h3>";
echo "<pre>";
print_r($test_data);
echo "</pre>";

// calculate_price.php의 함수 포함
function calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect) {
    $ab = $mesu;
    $gase = $garo * $sero;
    $j = substr($jong, 4, 10);
    $j1 = substr($jong, 0, 3);
    $d = substr($domusong, 6, 8);
    $d1 = substr($domusong, 0, 5);
    
    echo "<h4>🔧 계산 과정:</h4>";
    echo "재질 코드: j1 = '$j1', j = '$j'<br>";
    echo "도무송: d1 = '$d1', d = '$d'<br>";
    echo "면적: gase = $gase<br>";
    
    // 재질별 데이터베이스 조회
    if ($j1 == 'jil') {   
        $query = "SELECT * FROM shop_d1"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result); 
    } else if ($j1 == 'jka') {   
        $query = "SELECT * FROM shop_d2"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result); 
    } else if ($j1 == 'jsp') {   
        $query = "SELECT * FROM shop_d3"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result); 
    } else if ($j1 == 'cka') {   
        $query = "SELECT * FROM shop_d4"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result); 
    }
    
    echo "DB 요율 데이터: ";
    print_r($data);
    echo "<br>";
    
    // 수량별 요율 및 기본비용 설정
    if ($ab <= 1000) {
        $yoyo = $data[0];
        $mg = 7000;
    } else if ($ab > 1000 and $ab <= 4000) {
        $yoyo = $data[1];
        $mg = 6500;
    } else if ($ab > 4000 and $ab <= 5000) {
        $yoyo = $data[2];
        $mg = 6500;
    } else if ($ab > 5000 and $ab <= 9000) {
        $yoyo = $data[3];
        $mg = 6000;
    } else if ($ab > 9000 and $ab <= 10000) {
        $yoyo = $data[4];
        $mg = 5500;
    } else if ($ab > 10000 and $ab <= 50000) {
        $yoyo = $data[5];
        $mg = 5000;
    } else if ($ab > 50000) {
        $yoyo = $data[6];
        $mg = 5000;
    }
    
    echo "선택된 요율: yoyo = $yoyo, 기본비용: mg = $mg<br>";
    
    // 재질별 톰슨비용
    if ($j1 == 'jsp' || $j1 == 'jka' || $j1 == 'cka') {
        $ts = 14;
    }   
    if ($j1 == 'jil') {
        $ts = 9;
    }
    
    echo "톰슨비용: ts = $ts<br>";
    
    // 도무송칼 크기 계산
    if ($garo >= $sero) {
        $d2 = $garo;
    } else {
        $d2 = $sero;
    }
    
    echo "도무송칼 크기: d2 = $d2<br>";
    
    // 큰사이즈 마진비율
    if ($gase <= 18000) {
        $gase_rate = 1;
    }
    if ($gase > 18000) {
        $gase_rate = 1.25;
    }
    
    echo "사이즈 마진비율: gase_rate = $gase_rate<br>";
    
    // 도무송 비용 계산
    if ($d1 > 0 && $mesu == 500) {
        $d1_cost = (($d1 + ($d2 * 20)) * 900 / 1000) + (900 * $ts);
    } elseif ($d1 > 0 && $mesu == 1000) {
        $d1_cost = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * $ts);
    } elseif ($d1 > 0 && $mesu > 1000) {
        $d1_cost = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * ($ts / 9));
    } else {
        $d1_cost = 0;
    }
    
    echo "도무송 비용: d1_cost = $d1_cost<br>";
    
    // 특수용지 기본비용
    if ($j1 == 'jsp' && $mesu == 500) {
        $jsp = 10000 * ($mesu + 400) / 1000;
    } elseif ($j1 == 'jsp' && $mesu > 500) {
        $jsp = 10000 * $mesu / 1000;
    } else {
        $jsp = 0;
    }
    
    // 강접용지 기본비용
    if ($j1 == 'jka' && $mesu == 500) {
        $jka = 4000 * ($mesu + 400) / 1000;
    } elseif ($j1 == 'jka' && $mesu > 500) {
        $jka = 10000 * $mesu / 1000;
    } else {
        $jka = 0;
    }
    
    // 초강접용지 기본비용
    if ($j1 == 'cka' && $mesu == 500) {
        $cka = 4000 * ($mesu + 400) / 1000;
    } elseif ($j1 == 'cka' && $mesu > 500) {
        $cka = 10000 * $mesu / 1000;
    } else {
        $cka = 0;
    }
    
    echo "특수용지 비용: jsp = $jsp, jka = $jka, cka = $cka<br>";
    
    // 최종 가격 계산
    if ($mesu == 500) {
        $s_price = (($garo + 4) * ($sero + 4) * ($mesu + 400)) * $yoyo + $jsp + $jka + $cka + $d1_cost;
        $st_price = round($s_price * $gase_rate, -3) + $uhyung + ($mg * ($mesu + 400) / 1000);
        $st_price_vat = $st_price * 1.1;
    } else {
        $s_price = (($garo + 4) * ($sero + 4) * $mesu) * $yoyo + $jsp + $jka + $cka + $d1_cost;
        $st_price = round($s_price * $gase_rate, -3) + $uhyung + ($mg * $mesu / 1000);
        $st_price_vat = $st_price * 1.1;
    }
    
    echo "<h4>💰 최종 계산:</h4>";
    echo "기본 가격: s_price = $s_price<br>";
    echo "최종 가격: st_price = " . number_format($st_price) . "원<br>";
    echo "VAT 포함: st_price_vat = " . number_format($st_price_vat) . "원<br>";
    
    return [
        'st_price' => $st_price,
        'st_price_vat' => $st_price_vat
    ];
}

// 테스트 실행
$result = calculatePriceFromBasketLogic(
    $test_data['jong'],
    $test_data['garo'],
    $test_data['sero'],
    $test_data['mesu'],
    $test_data['uhyung'],
    $test_data['domusong'],
    $connect
);

echo "<h3>✅ 최종 결과:</h3>";
echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
echo "<strong>가격: " . number_format($result['st_price']) . "원</strong><br>";
echo "<strong>VAT 포함: " . number_format($result['st_price_vat']) . "원</strong>";
echo "</div>";

mysqli_close($connect);
?>