<?php
// 채팅 시스템 설정 파일
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 데이터베이스 연결
require_once __DIR__ . '/../db.php';

// 업로드 디렉토리 설정
define('CHAT_UPLOAD_DIR', '../chat_uploads/');
define('CHAT_UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB

// 업로드 디렉토리 생성
if (!file_exists(CHAT_UPLOAD_DIR)) {
    mkdir(CHAT_UPLOAD_DIR, 0755, true);
}

// 현재 로그인 사용자 정보 가져오기
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '사용자',
            'is_admin' => $_SESSION['is_admin'] ?? false
        ];
    }

    // 세션이 없으면 임시 사용자 생성 (테스트용)
    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['temp_user_id'] = 'guest_' . uniqid();
        $_SESSION['temp_user_name'] = '손님_' . substr(uniqid(), -4);
    }

    return [
        'id' => $_SESSION['temp_user_id'],
        'name' => $_SESSION['temp_user_name'],
        'is_admin' => false
    ];
}

// JSON 응답 헬퍼 함수
function jsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
