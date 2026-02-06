<?php
// 안전한 장바구니 추가 (JSON 전용)

require_once __DIR__ . '/../includes/ensure_shop_temp_columns.php';

// 모든 출력을 버퍼링
ob_start();

// 오류를 로그로만 기록
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// JSON 응답 함수
function sendJsonResponse($success, $message, $data = []) {
    // 모든 이전 출력 제거
    ob_clean();
    
    header('Content-Type: application/json; charset=UTF-8');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
    ob_end_flush();
    exit;
}

try {
    // POST 데이터 확인
    if (!isset($_POST['action']) || $_POST['action'] !== 'add_to_basket') {
        sendJsonResponse(false, '잘못된 요청입니다.');
    }
    
    // 데이터베이스 연결
    include "../lib/func.php";
    $connect = dbconn();
    
    if (!$connect) {
        sendJsonResponse(false, '데이터베이스 연결에 실패했습니다.');
    }
    
    // UTF-8 설정
    mysqli_set_charset($connect, 'utf8');
    
    ensure_shop_temp_columns($connect);
    
    // 입력값 받기
    $session_id = session_id();
    $jong = trim($_POST['jong'] ?? '');
    $garo = (int)($_POST['garo'] ?? 0);
    $sero = (int)($_POST['sero'] ?? 0);
    $mesu = (int)($_POST['mesu'] ?? 0);
    $uhyung = (int)($_POST['uhyung'] ?? 0);
    $domusong = trim($_POST['domusong'] ?? '');
    $no = trim($_POST['no'] ?? '');
    
    // 입력값 검증
    if (empty($jong)) {
        sendJsonResponse(false, '재질을 선택해주세요.');
    }
    
    if ($garo <= 0 || $sero <= 0) {
        sendJsonResponse(false, '가로, 세로 크기를 올바르게 입력해주세요.');
    }
    
    if ($mesu <= 0) {
        sendJsonResponse(false, '수량을 선택해주세요.');
    }
    
    if (empty($domusong)) {
        sendJsonResponse(false, '도무송을 선택해주세요.');
    }
    
    // calculate_price.php와 동일한 가격 계산 로직 사용
    try {
        $result = calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect);
        $st_price = $result['st_price'];
        $st_price_vat = $result['st_price_vat'];
    } catch (Exception $e) {
        sendJsonResponse(false, '가격 계산 중 오류가 발생했습니다: ' . $e->getMessage());
    }
    
    // 데이터베이스 저장
    $regdate = time();
    
    // 안전한 쿼리 준비 (product_type 포함)
    $stmt = mysqli_prepare($connect, 
        "INSERT INTO shop_temp(session_id, parent, jong, garo, sero, mesu, domusong, uhyung, st_price, st_price_vat, regdate, product_type) 
         VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sticker')"
    );
    
    if (!$stmt) {
        sendJsonResponse(false, '쿼리 준비 실패: ' . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($stmt, 'sssiiisiddi', 
        $session_id, $no, $jong, $garo, $sero, $mesu, $domusong, $uhyung, $st_price, $st_price_vat, $regdate
    );
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($connect);
        
        sendJsonResponse(true, '장바구니에 추가되었습니다.', [
            'price' => number_format($st_price),
            'price_vat' => number_format($st_price_vat)
        ]);
    } else {
        mysqli_stmt_close($stmt);
        sendJsonResponse(false, '데이터 저장에 실패했습니다.');
    }
    
} catch (Exception $e) {
    sendJsonResponse(false, '오류가 발생했습니다: ' . $e->getMessage());
} catch (Error $e) {
    sendJsonResponse(false, '시스템 오류가 발생했습니다.');
}

// basket_post.php와 동일한 가격 계산 함수
function calculatePriceFromBasketLogic($jong, $garo, $sero, $mesu, $uhyung, $domusong, $connect) {
    try {
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
        
        // 수량별 요율 및 기본비용 설정
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
        
    } catch (Exception $e) {
        throw new Exception('가격 계산 중 오류가 발생했습니다: ' . $e->getMessage());
    }
}

// 예상치 못한 종료 시 기본 응답
sendJsonResponse(false, '알 수 없는 오류가 발생했습니다.');
?>