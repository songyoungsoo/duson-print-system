<?php
session_start();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode([]);
    exit;
}

mysqli_set_charset($connect, "utf8");

$CV_no = $_GET['CV_no'] ?? '';

if (empty($CV_no)) {
    echo json_encode([]);
    exit;
}

// 포스터 규격 옵션 조회
$query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE BigNo = ? AND Ttable = 'LittlePrint' ORDER BY no ASC";
$stmt = mysqli_prepare($connect, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $CV_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $options = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $options[] = [
            'no' => $row['no'],
            'title' => $row['title']
        ];
    }
    
    mysqli_stmt_close($stmt);
    echo json_encode($options);
} else {
    echo json_encode([]);
}

mysqli_close($connect);
?>