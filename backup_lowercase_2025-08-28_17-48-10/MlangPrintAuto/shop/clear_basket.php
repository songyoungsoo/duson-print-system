<?php
/**
 * 장바구니 비우기
 * 경로: mlangprintauto/shop/clear_basket.php
 */

session_start();
header('Content-Type: application/json');

// 경로 수정: mlangprintauto/shop/에서 루트의 db.php 접근
include "../../db.php";
$connect = $db;

// 헬퍼 함수 포함
include "../shop_temp_helper.php";

try {
    if (!$connect) {
        throw new Exception('데이터베이스 연결에 실패했습니다.');
    }
    
    $session_id = session_id();
    
    if (clearCart($connect, $session_id)) {
        echo json_encode([
            'success' => true,
            'message' => '장바구니가 비워졌습니다.'
        ]);
    } else {
        throw new Exception('장바구니 비우기 중 오류가 발생했습니다.');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'session_id' => session_id(),
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile()
        ]
    ]);
}

if ($connect) {
    mysqli_close($connect);
}
?>