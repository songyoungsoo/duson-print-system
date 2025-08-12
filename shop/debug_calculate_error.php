<?php
// calculate_price.php 오류 진단

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h2>🔍 calculate_price.php 오류 진단</h2>";

// 1. 데이터베이스 연결 테스트
echo "<h3>1. 데이터베이스 연결 테스트</h3>";
try {
    include "../lib/func.php";
    $connect = dbconn();
    if ($connect) {
        echo "✅ 데이터베이스 연결 성공<br>";
    } else {
        echo "❌ 데이터베이스 연결 실패<br>";
    }
} catch (Exception $e) {
    echo "❌ 데이터베이스 연결 오류: " . $e->getMessage() . "<br>";
}

// 2. 테이블 존재 확인
echo "<h3>2. 필요한 테이블 존재 확인</h3>";
$tables = ['shop_d1', 'shop_d2', 'shop_d3', 'shop_d4'];
foreach ($tables as $table) {
    $query = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($connect, $query);
    if (mysqli_num_rows($result) > 0) {
        echo "✅ $table 테이블 존재<br>";
        
        // 테이블 데이터 확인
        $data_query = "SELECT * FROM $table LIMIT 1";
        $data_result = mysqli_query($connect, $data_query);
        if ($data_result && mysqli_num_rows($data_result) > 0) {
            $data = mysqli_fetch_array($data_result);
            echo "&nbsp;&nbsp;&nbsp;데이터 샘플: ";
            print_r(array_slice($data, 0, 7)); // 첫 7개 컬럼만 표시
            echo "<br>";
        } else {
            echo "&nbsp;&nbsp;&nbsp;❌ 데이터 없음<br>";
        }
    } else {
        echo "❌ $table 테이블 없음<br>";
    }
}

// 3. POST 데이터 시뮬레이션 테스트
echo "<h3>3. POST 데이터 시뮬레이션 테스트</h3>";
$_POST = [
    'action' => 'calculate',
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '150',
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '08000 원형'
];

echo "시뮬레이션 POST 데이터:<br>";
print_r($_POST);
echo "<br><br>";

// 4. 함수 실행 테스트
echo "<h3>4. 가격 계산 함수 테스트</h3>";

function calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect) {
    echo "함수 시작...<br>";
    
    $ab = $mesu;
    $gase = $garo * $sero;
    $j = substr($jong, 4, 10);
    $j1 = substr($jong, 0, 3);
    $d = substr($domusong, 6, 8);
    $d1 = substr($domusong, 0, 5);
    
    echo "변수 설정: j1='$j1', j='$j', d1='$d1', d='$d'<br>";
    
    // 재질별 데이터베이스 조회
    $data = null;
    if ($j1 == 'jil') {   
        $query = "SELECT * FROM shop_d1"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result);
        echo "jil 데이터 조회 완료<br>";
    } else if ($j1 == 'jka') {   
        $query = "SELECT * FROM shop_d2"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result);
        echo "jka 데이터 조회 완료<br>";
    } else if ($j1 == 'jsp') {   
        $query = "SELECT * FROM shop_d3"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result);
        echo "jsp 데이터 조회 완료<br>";
    } else if ($j1 == 'cka') {   
        $query = "SELECT * FROM shop_d4"; 
        $result = mysqli_query($connect, $query); 
        $data = mysqli_fetch_array($result);
        echo "cka 데이터 조회 완료<br>";
    } else {
        echo "❌ 알 수 없는 재질 코드: $j1<br>";
        return ['st_price' => 0, 'st_price_vat' => 0];
    }
    
    if (!$data) {
        echo "❌ 데이터베이스에서 데이터를 가져올 수 없음<br>";
        return ['st_price' => 0, 'st_price_vat' => 0];
    }
    
    echo "DB 데이터: ";
    print_r(array_slice($data, 0, 7));
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
    
    echo "요율: $yoyo, 기본비용: $mg<br>";
    
    // 재질별 톰슨비용
    $ts = 9; // 기본값
    if ($j1 == 'jsp' || $j1 == 'jka' || $j1 == 'cka') {
        $ts = 14;
    }   
    if ($j1 == 'jil') {
        $ts = 9;
    }
    
    echo "톰슨비용: $ts<br>";
    
    // 도무송칼 크기 계산
    if ($garo >= $sero) {
        $d2 = $garo;
    } else {
        $d2 = $sero;
    }
    
    // 큰사이즈 마진비율
    if ($gase <= 18000) {
        $gase_rate = 1;
    } else {
        $gase_rate = 1.25;
    }
    
    echo "도무송칼 크기: $d2, 마진비율: $gase_rate<br>";
    
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
    
    echo "도무송 비용: $d1_cost<br>";
    
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
    
    echo "특수용지 비용: jsp=$jsp, jka=$jka, cka=$cka<br>";
    
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
    
    echo "기본가격: $s_price<br>";
    echo "최종가격: $st_price<br>";
    echo "VAT포함: $st_price_vat<br>";
    
    return [
        'st_price' => $st_price,
        'st_price_vat' => $st_price_vat
    ];
}

try {
    $jong = $_POST['jong'];
    $garo = (int)$_POST['garo'];
    $sero = (int)$_POST['sero'];
    $mesu = (int)$_POST['mesu'];
    $uhyung = (int)$_POST['uhyung'];
    $domusong = $_POST['domusong'];
    
    echo "입력값: jong=$jong, garo=$garo, sero=$sero, mesu=$mesu, uhyung=$uhyung, domusong=$domusong<br><br>";
    
    $result = calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect);
    
    echo "<h3>✅ 최종 결과</h3>";
    echo "가격: " . number_format($result['st_price']) . "원<br>";
    echo "VAT 포함: " . number_format($result['st_price_vat']) . "원<br>";
    
} catch (Exception $e) {
    echo "<h3>❌ 오류 발생</h3>";
    echo "오류 메시지: " . $e->getMessage() . "<br>";
    echo "오류 위치: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

if ($connect) {
    mysqli_close($connect);
}
?>