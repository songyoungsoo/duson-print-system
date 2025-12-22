<?php
/**
 * 교정확정 상태 확인 스크립트
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

include "../db.php";

$response = array('success' => false, 'confirmed' => false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_no = $_POST['order_no'] ?? '';
    
    if (!empty($order_no)) {
        try {
            // 데이터베이스 연결 확인
            if (!isset($db) && isset($connect)) {
                $db = $connect;
            }
            
            $stmt = mysqli_prepare($db, "SELECT proofreading_confirmed FROM mlangorder_printauto WHERE no = ?");
            mysqli_stmt_bind_param($stmt, "s", $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $response['success'] = true;
                $response['confirmed'] = ($row['proofreading_confirmed'] == 1);
            } else {
                $response['message'] = '주문을 찾을 수 없습니다.';
            }
            
            mysqli_stmt_close($stmt);
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