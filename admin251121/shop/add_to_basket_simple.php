<?php
// 출력 버퍼링 시작 (예상치 못한 출력 방지)
ob_start();

session_start();
header('Content-Type: application/json; charset=UTF-8');

// 오류를 로그로만 기록 (JSON 응답 방해 방지)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    include "../lib/func.php";
    $connect = dbconn();
    
    // UTF-8 문자셋 설정
    if ($connect) {
        mysqli_set_charset($connect, 'utf8');
    }
    
    if (!$connect) {
        throw new Exception('데이터베이스 연결 실패');
    }
    
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
    
    // 기본 검증
    if (!$garo || !$sero || !$mesu) {
        throw new Exception('필수 입력값이 누락되었습니다.');
    }
    
    // 간단한 가격 계산 (복잡한 로직 대신)
    $base_price = ($garo + 4) * ($sero + 4) * $mesu * 0.15; // 기본 요율 0.15
    $domusong_price = (int)substr($domusong, 0, 5); // 도무송 가격 추출
    $st_price = $base_price + $uhyung + $domusong_price + 7000; // 기본비용 7000
    $st_price_vat = $st_price * 1.1;
    
    // 데이터베이스에 저장
    $regdate = time();
    
    // SQL 인젝션 방지
    $session_id = mysqli_real_escape_string($connect, $session_id);
    $no = mysqli_real_escape_string($connect, $no);
    $jong = mysqli_real_escape_string($connect, $jong);
    $domusong = mysqli_real_escape_string($connect, $domusong);
    
    $query = "INSERT INTO shop_temp(session_id, parent, jong, garo, sero, mesu, domusong, uhyung, st_price, st_price_vat, regdate)
              VALUES('$session_id', '$no', '$jong', '$garo', '$sero', '$mesu', '$domusong', '$uhyung', '$st_price', '$st_price_vat', '$regdate')";
    
    if (mysqli_query($connect, $query)) {
        // 출력 버퍼 정리
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => '장바구니에 추가되었습니다.',
            'price' => number_format($st_price),
            'price_vat' => number_format($st_price_vat)
        ]);
    } else {
        throw new Exception('데이터베이스 저장 실패: ' . mysqli_error($connect));
    }
    
} catch (Exception $e) {
    // 출력 버퍼 정리
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($connect)) {
    mysqli_close($connect);
}

// 출력 버퍼 종료
ob_end_flush();
?>