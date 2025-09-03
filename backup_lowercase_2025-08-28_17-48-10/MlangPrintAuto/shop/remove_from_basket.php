<?php
/**
 * 장바구니 아이템 삭제
 * 경로: mlangprintauto/shop/remove_from_basket.php
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
    $item_no = $_POST['no'] ?? '';
    
    if (empty($item_no) || !is_numeric($item_no)) {
        throw new Exception('올바른 상품 번호를 입력해주세요.');
    }
    
    if (removeCartItem($connect, $session_id, $item_no)) {
        echo json_encode([
            'success' => true,
            'message' => '상품이 장바구니에서 삭제되었습니다.',
            'item_no' => $item_no
        ]);
    } else {
        throw new Exception('상품 삭제 중 오류가 발생했습니다.');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'post_data' => $_POST,
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