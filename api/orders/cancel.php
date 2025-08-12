<?php
/**
 * 주문 취소 API 엔드포인트
 * 경로: /api/orders/cancel.php
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";

header('Content-Type: application/json; charset=utf-8');

// 로그인 체크
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => '허용되지 않은 요청 방식입니다.'
    ]);
    exit;
}

// JSON 입력 파싱
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['order_no'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '주문번호가 필요합니다.'
    ]);
    exit;
}

$order_no = $input['order_no'];
$user_name = $_SESSION['user_name'] ?? '';

try {
    // 주문 정보 조회 (본인 주문인지 확인)
    $query = "SELECT order_no, status, customer_name, total_price FROM MlangOrder_PrintAuto WHERE order_no = ? AND customer_name = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $order_no, $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '해당 주문을 찾을 수 없거나 권한이 없습니다.'
        ]);
        exit;
    }
    
    // 취소 가능한 상태인지 확인
    $cancelable_statuses = ['결제대기', '접수'];
    if (!in_array($order['status'], $cancelable_statuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '현재 주문 상태에서는 취소할 수 없습니다. (취소 가능: 결제대기, 접수 상태)'
        ]);
        exit;
    }
    
    // 주문 상태를 '취소'로 업데이트
    $update_query = "UPDATE MlangOrder_PrintAuto SET 
                     status = '취소',
                     updated_at = NOW(),
                     cancel_reason = '고객 요청',
                     canceled_at = NOW()
                     WHERE order_no = ? AND customer_name = ?";
    
    $update_stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ss", $order_no, $user_name);
    $update_result = mysqli_stmt_execute($update_stmt);
    
    if (!$update_result) {
        throw new Exception("주문 상태 업데이트 실패");
    }
    
    // 취소 로그 기록 (선택사항)
    $log_query = "INSERT INTO order_cancel_log (order_no, customer_name, canceled_by, cancel_reason, created_at) 
                  VALUES (?, ?, ?, '고객 요청', NOW())";
    $log_stmt = mysqli_prepare($db, $log_query);
    if ($log_stmt) {
        mysqli_stmt_bind_param($log_stmt, "sss", $order_no, $user_name, $user_name);
        mysqli_stmt_execute($log_stmt);
    }
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'message' => '주문이 성공적으로 취소되었습니다.',
        'order_no' => $order_no,
        'new_status' => '취소'
    ]);
    
} catch (Exception $e) {
    error_log("주문 취소 오류: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '주문 취소 처리 중 오류가 발생했습니다.'
    ]);
}

// 데이터베이스 연결 종료
if (isset($stmt)) mysqli_stmt_close($stmt);
if (isset($update_stmt)) mysqli_stmt_close($update_stmt);
if (isset($log_stmt)) mysqli_stmt_close($log_stmt);
mysqli_close($db);
?>