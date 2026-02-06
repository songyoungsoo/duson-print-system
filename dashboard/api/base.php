<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../admin/includes/admin_auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

function requireApiAuth() {
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.', 'data' => null], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function jsonResponse($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

requireApiAuth();
