<?php
// 디버그용 간단한 장바구니 추가 스크립트
session_start();
header('Content-Type: application/json; charset=utf-8');

// 에러 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // 1. 기본 정보 수집
    $debug_info = [
        'step' => '1_initial',
        'session_id' => session_id(),
        'post_received' => !empty($_POST),
        'post_count' => count($_POST)
    ];
    error_log("DEBUG 1: " . json_encode($debug_info));

    // 2. 데이터베이스 연결
    include "../../db.php";
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    error_log("DEBUG 2: Database connected");

    // 3. 가장 간단한 데이터만으로 삽입 테스트
    $simple_query = "INSERT INTO shop_temp (session_id, product_type) VALUES (?, ?)";
    $stmt = mysqli_prepare($db, $simple_query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($db));
    }

    error_log("DEBUG 3: Prepare successful");

    $session_id = session_id();
    $product_type = 'envelope_test';

    $bind_result = mysqli_stmt_bind_param($stmt, "ss", $session_id, $product_type);
    if (!$bind_result) {
        throw new Exception("Bind failed: " . mysqli_stmt_error($stmt));
    }

    error_log("DEBUG 4: Bind successful");

    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($db);
        error_log("DEBUG 5: Insert successful - ID: $insert_id");

        // 테스트 데이터 정리
        mysqli_query($db, "DELETE FROM shop_temp WHERE no = $insert_id");

        echo json_encode([
            'success' => true,
            'message' => 'Basic cart test successful',
            'insert_id' => $insert_id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_step' => $debug_info['step'] ?? 'unknown'
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($db) && $db) {
    mysqli_close($db);
}
?>