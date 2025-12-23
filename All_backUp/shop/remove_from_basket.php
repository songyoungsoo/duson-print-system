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
    $no_param = $_POST['no'] ?? '';
    
    if (!$no_param) {
        sendJsonResponse(false, '삭제할 항목을 선택해주세요.');
    }
    
    // 카다록 상품인지 확인 (cadarok_ 접두사가 있는지)
    if (strpos($no_param, 'cadarok_') === 0) {
        // 카다록 상품 삭제
        $cadarok_no = (int)str_replace('cadarok_', '', $no_param);
        
        // 카다록 테이블 존재 확인
        $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp_cadarok'");
        if (mysqli_num_rows($table_check) == 0) {
            sendJsonResponse(false, '카다록 장바구니 테이블을 찾을 수 없습니다.');
        }
        
        // 카다록 항목 존재 확인
        $check_query = "SELECT no FROM shop_temp_cadarok WHERE no = ? AND session_id = ?";
        $check_stmt = mysqli_prepare($connect, $check_query);
        
        if (!$check_stmt) {
            sendJsonResponse(false, '쿼리 준비 실패: ' . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($check_stmt, 'is', $cadarok_no, $session_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) === 0) {
            mysqli_stmt_close($check_stmt);
            sendJsonResponse(false, '삭제할 카다록 항목을 찾을 수 없습니다.');
        }
        
        mysqli_stmt_close($check_stmt);
        
        // 카다록 항목 삭제
        $delete_query = "DELETE FROM shop_temp_cadarok WHERE no = ? AND session_id = ?";
        $delete_stmt = mysqli_prepare($connect, $delete_query);
        
        if (!$delete_stmt) {
            sendJsonResponse(false, '삭제 쿼리 준비 실패: ' . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($delete_stmt, 'is', $cadarok_no, $session_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $affected_rows = mysqli_stmt_affected_rows($delete_stmt);
            mysqli_stmt_close($delete_stmt);
            mysqli_close($connect);
            
            if ($affected_rows > 0) {
                sendJsonResponse(true, '카다록 상품이 삭제되었습니다.');
            } else {
                sendJsonResponse(false, '삭제할 카다록 항목을 찾을 수 없습니다.');
            }
        } else {
            mysqli_stmt_close($delete_stmt);
            sendJsonResponse(false, '카다록 삭제 실행 중 오류가 발생했습니다.');
        }
        
    } else {
        // 기존 상품 (스티커, 전단지) 삭제
        $no = (int)$no_param;
        
        if (!$no) {
            sendJsonResponse(false, '삭제할 항목을 선택해주세요.');
        }
        
        // 먼저 해당 항목이 존재하는지 확인
        $check_query = "SELECT no FROM shop_temp WHERE no = ? AND session_id = ?";
        $check_stmt = mysqli_prepare($connect, $check_query);
        
        if (!$check_stmt) {
            sendJsonResponse(false, '쿼리 준비 실패: ' . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($check_stmt, 'is', $no, $session_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) === 0) {
            mysqli_stmt_close($check_stmt);
            sendJsonResponse(false, '삭제할 항목을 찾을 수 없습니다.');
        }
        
        mysqli_stmt_close($check_stmt);
        
        // 안전한 삭제 쿼리 실행
        $delete_query = "DELETE FROM shop_temp WHERE no = ? AND session_id = ?";
        $delete_stmt = mysqli_prepare($connect, $delete_query);
        
        if (!$delete_stmt) {
            sendJsonResponse(false, '삭제 쿼리 준비 실패: ' . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($delete_stmt, 'is', $no, $session_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $affected_rows = mysqli_stmt_affected_rows($delete_stmt);
            mysqli_stmt_close($delete_stmt);
            mysqli_close($connect);
            
            if ($affected_rows > 0) {
                sendJsonResponse(true, '상품이 삭제되었습니다.');
            } else {
                sendJsonResponse(false, '삭제할 항목을 찾을 수 없습니다.');
            }
        } else {
            mysqli_stmt_close($delete_stmt);
            sendJsonResponse(false, '삭제 실행 중 오류가 발생했습니다.');
        }
    }
    
} catch (Exception $e) {
    sendJsonResponse(false, '오류가 발생했습니다: ' . $e->getMessage());
} catch (Error $e) {
    sendJsonResponse(false, '시스템 오류가 발생했습니다.');
}

// 예상치 못한 종료 시 기본 응답
sendJsonResponse(false, '알 수 없는 오류가 발생했습니다.');
?>