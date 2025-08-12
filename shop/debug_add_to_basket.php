<?php
// add_to_basket.php 오류 진단

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 장바구니 추가 오류 진단</h2>";

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

echo "<h3>시뮬레이션 POST 데이터:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

session_start();
$session_id = session_id();
echo "<h3>세션 ID: $session_id</h3>";

// 데이터베이스 연결 테스트
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

// shop_temp 테이블 확인
echo "<h3>2. shop_temp 테이블 확인</h3>";
$query = "SHOW TABLES LIKE 'shop_temp'";
$result = mysqli_query($connect, $query);
if (mysqli_num_rows($result) > 0) {
    echo "✅ shop_temp 테이블 존재<br>";
    
    // 테이블 구조 확인
    $query = "DESCRIBE shop_temp";
    $result = mysqli_query($connect, $query);
    echo "<h4>테이블 구조:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_array($result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ shop_temp 테이블 없음<br>";
}

// 3. 입력값 검증 테스트
echo "<h3>3. 입력값 검증 테스트</h3>";
try {
    $jong = $_POST['jong'] ?? '';
    $garo = (int)($_POST['garo'] ?? 0);
    $sero = (int)($_POST['sero'] ?? 0);
    $mesu = (int)($_POST['mesu'] ?? 0);
    $uhyung = (int)($_POST['uhyung'] ?? 0);
    $domusong = $_POST['domusong'] ?? '';
    $no = $_POST['no'] ?? '';
    
    echo "파싱된 값들:<br>";
    echo "jong: $jong<br>";
    echo "garo: $garo<br>";
    echo "sero: $sero<br>";
    echo "mesu: $mesu<br>";
    echo "uhyung: $uhyung<br>";
    echo "domusong: $domusong<br>";
    echo "no: $no<br>";
    
    // 기본 검증
    if (!$garo) throw new Exception('가로사이즈를 입력하세요');
    if (!$sero) throw new Exception('세로사이즈를 입력하세요');
    
    echo "✅ 기본 입력값 검증 통과<br>";
    
} catch (Exception $e) {
    echo "❌ 입력값 검증 오류: " . $e->getMessage() . "<br>";
}

// 4. 가격 계산 함수 테스트
echo "<h3>4. 가격 계산 함수 테스트</h3>";

function calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect) {
    echo "가격 계산 함수 시작...<br>";
    
    $ab = $mesu;
    $gase = $garo * $sero;
    $j = substr($jong, 4, 10);
    $j1 = substr($jong, 0, 3);
    $d = substr($domusong, 6, 8);
    $d1 = substr($domusong, 0, 5);
    
    echo "변수 설정: j1='$j1', j='$j', d1='$d1'<br>";
    
    // 재질별 데이터베이스 조회
    $data = null;
    if ($j1 == 'jil') {   
        $query = "SELECT * FROM shop_d1"; 
        $result = mysqli_query($connect, $query); 
        if ($result) {
            $data = mysqli_fetch_array($result);
            echo "✅ jil 데이터 조회 성공<br>";
        } else {
            echo "❌ jil 데이터 조회 실패<br>";
        }
    }
    
    if (!$data) {
        throw new Exception('재질 정보를 찾을 수 없습니다: ' . $j1);
    }
    
    // 간단한 계산
    $yoyo = $data[0] ?? 0.15;
    $mg = 7000;
    
    $s_price = (($garo + 4) * ($sero + 4) * $mesu) * $yoyo;
    $st_price = round($s_price, -3) + $uhyung + ($mg * $mesu / 1000);
    $st_price_vat = $st_price * 1.1;
    
    echo "계산 결과: st_price=$st_price, st_price_vat=$st_price_vat<br>";
    
    return [
        'st_price' => $st_price,
        'st_price_vat' => $st_price_vat
    ];
}

try {
    $result = calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect);
    echo "✅ 가격 계산 성공<br>";
} catch (Exception $e) {
    echo "❌ 가격 계산 오류: " . $e->getMessage() . "<br>";
}

// 5. 데이터베이스 삽입 테스트
echo "<h3>5. 데이터베이스 삽입 테스트</h3>";
try {
    $regdate = time();
    $query = "INSERT INTO shop_temp(session_id, parent, jong, garo, sero, mesu, domusong, uhyung, st_price, st_price_vat, regdate)
              VALUES('$session_id', '$no', '$jong', '$garo', '$sero', '$mesu', '$domusong', '$uhyung', '{$result['st_price']}', '{$result['st_price_vat']}', '$regdate')";
    
    echo "실행할 쿼리:<br>";
    echo "<pre>$query</pre>";
    
    if (mysqli_query($connect, $query)) {
        echo "✅ 데이터베이스 삽입 성공<br>";
        
        // 삽입된 데이터 확인
        $check_query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC LIMIT 1";
        $check_result = mysqli_query($connect, $check_query);
        if ($check_data = mysqli_fetch_array($check_result)) {
            echo "<h4>삽입된 데이터:</h4>";
            echo "<pre>";
            print_r($check_data);
            echo "</pre>";
        }
    } else {
        echo "❌ 데이터베이스 삽입 실패: " . mysqli_error($connect) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ 데이터베이스 삽입 오류: " . $e->getMessage() . "<br>";
}

if ($connect) {
    mysqli_close($connect);
}
?>