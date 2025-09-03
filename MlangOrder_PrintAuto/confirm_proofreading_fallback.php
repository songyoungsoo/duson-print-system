<?php
/**
 * 교정확정 처리 스크립트 - 대안책 (기존 컬럼 사용)
 */

session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

include "../db.php";

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_no = $_POST['order_no'] ?? '';
    
    if (!empty($order_no)) {
        try {
            // 데이터베이스 연결 확인
            if (!isset($db) && isset($connect)) {
                $db = $connect;
            }
            
            if (!$db) {
                $response['message'] = '데이터베이스 연결 오류';
                echo json_encode($response);
                exit;
            }
            
            // 먼저 해당 주문이 존재하는지 확인 (기존 컬럼만 사용)
            $check_stmt = mysqli_prepare($db, "SELECT no, OrderStyle FROM mlangorder_printauto WHERE no = ?");
            mysqli_stmt_bind_param($check_stmt, "s", $order_no);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if ($order_data = mysqli_fetch_assoc($check_result)) {
                // OrderStyle을 '8' (작업완료)로 변경하여 교정확정 표시
                $stmt = mysqli_prepare($db, 
                    "UPDATE mlangorder_printauto 
                     SET OrderStyle = '8', 
                         Designer = CONCAT(IFNULL(Designer, ''), ' [교정확정]')
                     WHERE no = ?");
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $order_no);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        if (mysqli_stmt_affected_rows($stmt) > 0) {
                            $response['success'] = true;
                            $response['message'] = '교정확정이 완료되었습니다.';
                            
                            // 로그 기록
                            error_log("교정확정 완료 (대안책) - 주문번호: $order_no, IP: " . $_SERVER['REMOTE_ADDR']);
                        } else {
                            $response['message'] = '교정확정 처리 중 문제가 발생했습니다.';
                        }
                    } else {
                        $response['message'] = '데이터베이스 업데이트 실패: ' . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $response['message'] = 'SQL 준비 실패: ' . mysqli_error($db);
                }
            } else {
                $response['message'] = '존재하지 않는 주문번호입니다.';
            }
            mysqli_stmt_close($check_stmt);
        } catch (Exception $e) {
            $response['message'] = '데이터베이스 오류: ' . $e->getMessage();
        }
    } else {
        $response['message'] = '주문번호가 필요합니다.';
    }
} else {
    $response['message'] = 'POST 요청만 허용됩니다.';
}

echo json_encode($response);
?>