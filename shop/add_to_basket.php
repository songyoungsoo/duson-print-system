<?php
session_start();
header('Content-Type: application/json');

include "../lib/func.php";
$connect = dbconn();

try {
    if ($_POST['action'] !== 'add_to_basket') {
        throw new Exception('잘못된 요청입니다.');
    }
    
    $session_id = session_id();
    $jong = $_POST['jong'] ?? '';
    $garo = (int)($_POST['garo'] ?? 0);
    $sero = (int)($_POST['sero'] ?? 0);
    $mesu = (int)($_POST['mesu'] ?? 0);
    $uhyung = (int)($_POST['uhyung'] ?? 0);
    $domusong = $_POST['domusong'] ?? '';
    $no = $_POST['no'] ?? '';
    
    // 입력값 검증 (basket_post.php와 동일)
    if (!$garo) throw new Exception('가로사이즈를 입력하세요');
    if (!$sero) throw new Exception('세로사이즈를 입력하세요');
    if ($garo > 590) throw new Exception('가로사이즈를 590mm이하만 입력할 수 있습니다');
    if ($sero > 590) throw new Exception('세로사이즈를 590mm이하만 입력할 수 있습니다');
    if (($garo * $sero) > 250000 && $mesu > 5000) throw new Exception('500mm이상 대형사이즈를 5000매이상 주문은 전화요청바랍니다');
    if (10000 < $mesu) throw new Exception('1만매 이상은 할인가 적용-전화주시기바랍니다');
    
    // 도무송 강제 선택 검증
    if (
        ($garo < 50 || $sero < 60) &&
        ($garo < 60 || $sero < 50) &&
        $domusong == '00000 사각'
    ) {
        throw new Exception('가로,세로사이즈가 50mmx60mm 미만일 경우, 도무송을 선택해야 합니다.');
    }
    
    // 재질별 제한 검증
    $j = substr($jong, 4, 10);
    if ($j == '금지스티커') throw new Exception('금지스티커는 전화 또는 메일로 견적 문의하세요');
    if ($j == '금박스티커') throw new Exception('금박스티커는 전화 또는 메일로 견적 문의하세요');
    if ($j == '롤형스티커') throw new Exception('롤스티커는 전화 또는 메일로 견적 문의하세요');
    
    // 가격 계산 (basket_post.php와 동일한 로직)
    $result = calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect);
    
    // 장바구니에 추가
    $regdate = time();
    
    // SQL 인젝션 방지를 위한 이스케이프 처리
    $session_id = mysqli_real_escape_string($connect, $session_id);
    $no = mysqli_real_escape_string($connect, $no);
    $jong = mysqli_real_escape_string($connect, $jong);
    $domusong = mysqli_real_escape_string($connect, $domusong);
    
    $query = "INSERT INTO shop_temp(session_id, parent, jong, garo, sero, mesu, domusong, uhyung, st_price, st_price_vat, regdate)
              VALUES('$session_id', '$no', '$jong', '$garo', '$sero', '$mesu', '$domusong', '$uhyung', '{$result['st_price']}', '{$result['st_price_vat']}', '$regdate')";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode([
            'success' => true,
            'message' => '장바구니에 추가되었습니다.'
        ]);
    } else {
        throw new Exception('데이터베이스 저장 중 오류가 발생했습니다: ' . mysqli_error($connect));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// basket_post.php와 동일한 가격 계산 함수
function calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect) {
    $ab = $mesu;
    $gase = $garo * $sero;
    $j = substr($jong, 4, 10);
    $j1 = substr($jong, 0, 3);
    $d = substr($domusong, 6, 8);
    $d1 = substr($domusong, 0, 5);
    
    // 재질별 데이터베이스 조회
    $data = null;
    if ($j1 == 'jil') {   
        $query = "SELECT * FROM shop_d1"; 
        $result = mysqli_query($connect, $query); 
        if ($result) $data = mysqli_fetch_array($result); 
    } else if ($j1 == 'jka') {   
        $query = "SELECT * FROM shop_d2"; 
        $result = mysqli_query($connect, $query); 
        if ($result) $data = mysqli_fetch_array($result); 
    } else if ($j1 == 'jsp') {   
        $query = "SELECT * FROM shop_d3"; 
        $result = mysqli_query($connect, $query); 
        if ($result) $data = mysqli_fetch_array($result); 
    } else if ($j1 == 'cka') {   
        $query = "SELECT * FROM shop_d4"; 
        $result = mysqli_query($connect, $query); 
        if ($result) $data = mysqli_fetch_array($result); 
    }
    
    if (!$data) {
        throw new Exception('재질 정보를 찾을 수 없습니다: ' . $j1);
    }
    
    // 수량별 요율 및 기본비용 설정 (기본값 설정)
    $yoyo = $data[0] ?? 0.15; // 기본 요율
    $mg = 7000; // 기본 비용
    
    if ($ab <= 1000) {
        $yoyo = $data[0] ?? 0.15;
        $mg = 7000;
    } else if ($ab > 1000 and $ab <= 4000) {
        $yoyo = $data[1] ?? 0.14;
        $mg = 6500;
    } else if ($ab > 4000 and $ab <= 5000) {
        $yoyo = $data[2] ?? 0.13;
        $mg = 6500;
    } else if ($ab > 5000 and $ab <= 9000) {
        $yoyo = $data[3] ?? 0.12;
        $mg = 6000;
    } else if ($ab > 9000 and $ab <= 10000) {
        $yoyo = $data[4] ?? 0.11;
        $mg = 5500;
    } else if ($ab > 10000 and $ab <= 50000) {
        $yoyo = $data[5] ?? 0.10;
        $mg = 5000;
    } else if ($ab > 50000) {
        $yoyo = $data[6] ?? 0.09;
        $mg = 5000;
    }
    
    // 재질별 톰슨비용
    $ts = 9; // 기본값
    if ($j1 == 'jsp' || $j1 == 'jka' || $j1 == 'cka') {
        $ts = 14;
    }
    
    // 도무송칼 크기 계산
    $d2 = ($garo >= $sero) ? $garo : $sero;

    
    
    // 큰사이즈 마진비율
    $gase_rate = ($gase <= 18000) ? 1 : 1.25;
    
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
    
    return [
        'st_price' => $st_price,
        'st_price_vat' => $st_price_vat
    ];
}

if ($connect) {
    mysqli_close($connect);
}
?>