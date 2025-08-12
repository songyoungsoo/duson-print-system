<?php
// 간단한 버전의 가격 계산

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // POST 데이터 확인
    if (!isset($_POST['action']) || $_POST['action'] !== 'calculate') {
        throw new Exception('잘못된 요청입니다.');
    }
    
    // 입력값 받기
    $jong = $_POST['jong'] ?? '';
    $garo = (int)($_POST['garo'] ?? 0);
    $sero = (int)($_POST['sero'] ?? 0);
    $mesu = (int)($_POST['mesu'] ?? 0);
    $uhyung = (int)($_POST['uhyung'] ?? 0);
    $domusong = $_POST['domusong'] ?? '';
    
    // 기본 검증
    if (!$garo || !$sero || !$mesu) {
        throw new Exception('필수 입력값이 누락되었습니다.');
    }
    
    if ($garo > 590 || $sero > 590) {
        throw new Exception('사이즈가 너무 큽니다.');
    }
    
    // 데이터베이스 연결
    include "../lib/func.php";
    $connect = dbconn();
    
    if (!$connect) {
        throw new Exception('데이터베이스 연결 실패');
    }
    
    // 재질 코드 추출
    $j1 = substr($jong, 0, 3);
    
    // 간단한 가격 계산
    $base_rate = 0.15; // 기본 요율
    $base_cost = 7000; // 기본 비용
    
    // 재질별 요율 조회 (간단화)
    $table_map = [
        'jil' => 'shop_d1',
        'jka' => 'shop_d2',
        'jsp' => 'shop_d3',
        'cka' => 'shop_d4'
    ];
    
    if (isset($table_map[$j1])) {
        $query = "SELECT * FROM {$table_map[$j1]} LIMIT 1";
        $result = mysqli_query($connect, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_array($result);
            
            // 수량별 요율 선택
            if ($mesu <= 1000) {
                $base_rate = $data[0] ?? 0.15;
            } else if ($mesu <= 4000) {
                $base_rate = $data[1] ?? 0.14;
            } else if ($mesu <= 5000) {
                $base_rate = $data[2] ?? 0.13;
            } else {
                $base_rate = $data[3] ?? 0.12;
            }
        }
    }
    
    // 도무송 가격 추출
    $domusong_price = 0;
    if (preg_match('/^(\d+)/', $domusong, $matches)) {
        $domusong_price = (int)$matches[1];
    }
    
    // 최종 가격 계산
    $area_price = ($garo + 4) * ($sero + 4) * $mesu * $base_rate;
    $total_price = $area_price + $base_cost + $domusong_price + $uhyung;
    $total_price_vat = $total_price * 1.1;
    
    // 세션에 저장
    $_SESSION['temp_order'] = [
        'jong' => $jong,
        'garo' => $garo,
        'sero' => $sero,
        'mesu' => $mesu,
        'uhyung' => $uhyung,
        'domusong' => $domusong,
        'price' => $total_price,
        'price_vat' => $total_price_vat,
        'created_at' => time()
    ];
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'price' => number_format($total_price),
        'price_vat' => number_format($total_price_vat),
        'debug' => [
            'j1' => $j1,
            'base_rate' => $base_rate,
            'area_price' => $area_price,
            'domusong_price' => $domusong_price
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // 오류 응답
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($connect)) {
    mysqli_close($connect);
}
?>