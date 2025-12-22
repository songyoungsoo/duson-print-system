<?php
/**
 * 업로드된 파일 목록 조회 핸들러
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.']);
    exit;
}

mysqli_set_charset($connect, "utf8");

$session_id = session_id();
$product_type = $_GET['product_type'] ?? 'general';

// 업로드된 파일 목록 조회
$query = "SELECT * FROM uploaded_files 
          WHERE session_id = ? AND product_type = ? 
          ORDER BY upload_date DESC";

$stmt = mysqli_prepare($connect, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $session_id, $product_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $files = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $files[] = [
            'id' => $row['id'],
            'original_name' => $row['original_name'],
            'file_name' => $row['file_name'],
            'file_path' => $row['file_path'],
            'file_size' => $row['file_size'],
            'file_type' => $row['file_type'],
            'upload_date' => $row['upload_date']
        ];
    }
    
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true,
        'files' => $files
    ]);
} else {
    echo json_encode(['success' => false, 'message' => '파일 목록 조회에 실패했습니다.']);
}

mysqli_close($connect);
?>