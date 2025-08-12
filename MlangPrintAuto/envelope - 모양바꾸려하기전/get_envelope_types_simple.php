<?php
// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode([]);
    exit;
}

mysqli_set_charset($connect, "utf8");

$category_type = $_GET['category_type'] ?? '';

if (empty($category_type)) {
    echo json_encode([]);
    exit;
}

try {
    // MlangPrintAuto_transactionCate 테이블에서 봉투 종류 조회
    $query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
              WHERE TreeNo = ? AND Ttable = 'envelope' 
              ORDER BY no ASC";
    
    $stmt = mysqli_prepare($connect, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $category_type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'value' => $row['no'],
                'text' => $row['title']
            ];
        }
        
        mysqli_stmt_close($stmt);
        echo json_encode($options);
    } else {
        echo json_encode([]);
    }
    
} catch (Exception $e) {
    echo json_encode([]);
}

mysqli_close($connect);
?>