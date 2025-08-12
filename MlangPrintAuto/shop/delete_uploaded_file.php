<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.']);
    exit;
}

mysqli_set_charset($connect, "utf8");

$session_id = session_id();
$file_id = intval($_POST['file_id'] ?? 0);

if ($file_id <= 0) {
    echo json_encode(['success' => false, 'message' => '잘못된 파일 ID입니다.']);
    exit;
}

// 파일 정보 조회 (보안을 위해 세션 ID 확인)
$query = "SELECT * FROM uploaded_files WHERE id = ? AND session_id = ?";
$stmt = mysqli_prepare($connect, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "is", $file_id, $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $filePath = '../../' . $row['file_path'];
        
        // 실제 파일 삭제
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // 데이터베이스에서 삭제
        $deleteQuery = "DELETE FROM uploaded_files WHERE id = ? AND session_id = ?";
        $deleteStmt = mysqli_prepare($connect, $deleteQuery);
        
        if ($deleteStmt) {
            mysqli_stmt_bind_param($deleteStmt, "is", $file_id, $session_id);
            
            if (mysqli_stmt_execute($deleteStmt)) {
                echo json_encode(['success' => true, 'message' => '파일이 삭제되었습니다.']);
            } else {
                echo json_encode(['success' => false, 'message' => '파일 삭제에 실패했습니다.']);
            }
            
            mysqli_stmt_close($deleteStmt);
        } else {
            echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '파일을 찾을 수 없습니다.']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다.']);
}

mysqli_close($connect);
?>