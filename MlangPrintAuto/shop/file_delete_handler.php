<?php
/**
 * 파일 삭제 처리기
 * 경로: MlangPrintAuto/shop/file_delete_handler.php
 * 
 * 장바구니 아이템의 파일을 삭제하는 AJAX 처리기
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";
include "file_management_helper.php";

// 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

try {
    // 데이터베이스 연결 확인
    check_db_connection($db);
    
    // POST 데이터 검증
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_response('POST 요청만 허용됩니다.');
    }
    
    // JSON 데이터 파싱
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        error_response('잘못된 요청 데이터입니다.');
    }
    
    // 필수 파라미터 확인
    $cart_item_no = $input['cart_item_no'] ?? '';
    $file_name = $input['file_name'] ?? '';
    
    if (empty($cart_item_no) || empty($file_name)) {
        error_response('장바구니 아이템 번호와 파일명이 필요합니다.');
    }
    
    // 세션 검증 (보안)
    $session_id = session_id();
    $verify_query = "SELECT no FROM shop_temp WHERE no = ? AND session_id = ?";
    $stmt = mysqli_prepare($db, $verify_query);
    
    if (!$stmt) {
        error_response('데이터베이스 오류가 발생했습니다.');
    }
    
    mysqli_stmt_bind_param($stmt, 'is', $cart_item_no, $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        error_response('해당 장바구니 아이템을 찾을 수 없습니다.');
    }
    
    mysqli_stmt_close($stmt);
    
    // 파일 삭제 실행
    if (removeFileFromCartItem($db, $cart_item_no, $file_name)) {
        success_response([
            'cart_item_no' => $cart_item_no,
            'deleted_file' => $file_name
        ], '파일이 성공적으로 삭제되었습니다.');
    } else {
        error_response('파일 삭제에 실패했습니다.');
    }
    
} catch (Exception $e) {
    error_response('파일 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
}
?>