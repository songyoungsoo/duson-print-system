<?php
// 1. 스크립트 시작 시 출력 버퍼링 및 에러 설정
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// 2. 안전한 JSON 응답 함수 구현
function safe_json_response($success = true, $data = null, $message = '') {
    // 이전 출력 완전 정리
    if (ob_get_length()) {
        ob_clean();
    }
    
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
    // 버퍼링 종료 및 출력
    ob_end_flush();
    exit;
}
?>