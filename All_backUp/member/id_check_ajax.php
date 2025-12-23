<?php
/**
 * AJAX 아이디 중복 체크 API
 *
 * 응답 형식: JSON
 * { "available": true/false, "message": "메시지" }
 */

header('Content-Type: application/json');

include "../db.php";

// GET 파라미터에서 아이디 받기
$id = isset($_GET['id']) ? trim($_GET['id']) : '';

// 기본 응답
$response = [
    'available' => false,
    'message' => ''
];

// 입력 검증
if (empty($id)) {
    $response['message'] = '아이디를 입력해주세요.';
    echo json_encode($response);
    exit;
}

if (strlen($id) < 4 || strlen($id) > 20) {
    $response['message'] = '아이디는 4-20자여야 합니다.';
    echo json_encode($response);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9]+$/', $id)) {
    $response['message'] = '아이디는 영문자와 숫자만 사용할 수 있습니다.';
    echo json_encode($response);
    exit;
}

// users 테이블에서 중복 체크
$query = "SELECT username FROM users WHERE username = ?";
$stmt = mysqli_prepare($db, $query);

if (!$stmt) {
    $response['message'] = '데이터베이스 오류가 발생했습니다.';
    echo json_encode($response);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $response['available'] = false;
    $response['message'] = '이미 사용 중인 아이디입니다.';
} else {
    $response['available'] = true;
    $response['message'] = '사용 가능한 아이디입니다.';
}

mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode($response);
?>
