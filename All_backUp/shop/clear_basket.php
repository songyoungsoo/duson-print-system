<?php
// 출력 버퍼링 시작
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
    // 데이터베이스 연결
    include "../lib/func.php";
    $connect = dbconn();
    
    if (!$connect) {
        sendJsonResponse(false, '데이터베이스 연결에 실패했습니다.');
    }
    
    // UTF-8 설정
    mysqli_set_charset($connect, 'utf8');
    
    $session_id = session_id();
    
    if (empty($session_id)) {
        sendJsonResponse(false, '세션이 유효하지 않습니다.');
    }
    
    // 기존 장바구니 아이템 삭제 (스티커, 전단지)
    $query1 = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt1 = mysqli_prepare($connect, $query1);
    
    if (!$stmt1) {
        sendJsonResponse(false, '쿼리 준비 실패: ' . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($stmt1, 's', $session_id);
    $result1 = mysqli_stmt_execute($stmt1);
    $affected1 = mysqli_stmt_affected_rows($stmt1);
    mysqli_stmt_close($stmt1);
    
    // 카다록 장바구니 아이템 삭제
    $result2 = true;
    $affected2 = 0;
    
    $cadarok_table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp_cadarok'");
    if ($cadarok_table_check && mysqli_num_rows($cadarok_table_check) > 0) {
        $query2 = "DELETE FROM shop_temp_cadarok WHERE session_id = ?";
        $stmt2 = mysqli_prepare($connect, $query2);
        
        if ($stmt2) {
            mysqli_stmt_bind_param($stmt2, 's', $session_id);
            $result2 = mysqli_stmt_execute($stmt2);
            $affected2 = mysqli_stmt_affected_rows($stmt2);
            mysqli_stmt_close($stmt2);
        }
    }
    
    mysqli_close($connect);
    
    if ($result1 && $result2) {
        $total_affected = $affected1 + $affected2;
        sendJsonResponse(true, "장바구니가 비워졌습니다. ({$total_affected}개 항목 삭제)");
    } else {
        sendJsonResponse(false, '장바구니 비우기 중 일부 오류가 발생했습니다.');
    }
    
} catch (Exception $e) {
    sendJsonResponse(false, '오류가 발생했습니다: ' . $e->getMessage());
} catch (Error $e) {
    sendJsonResponse(false, '시스템 오류가 발생했습니다.');
}

// 예상치 못한 종료 시 기본 응답
sendJsonResponse(false, '알 수 없는 오류가 발생했습니다.');
?>