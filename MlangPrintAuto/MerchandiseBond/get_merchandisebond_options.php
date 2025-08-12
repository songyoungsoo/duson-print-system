<?php
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../NameCard/db_ajax.php";

$category_type = $_GET['category_type'] ?? '';

if (empty($category_type)) {
    echo json_encode([]);
    exit;
}

try {
    $GGTABLE = "MlangPrintAuto_transactionCate";
    
    // 후가공 옵션 가져오기
    $query = "SELECT no, title FROM $GGTABLE WHERE BigNo = ? ORDER BY no ASC";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $category_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $options = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $options[] = [
            'no' => $row['no'],
            'title' => $row['title']
        ];
    }
    
    echo json_encode($options);
    
} catch (Exception $e) {
    error_log("상품권 후가공 옵션 조회 오류: " . $e->getMessage());
    echo json_encode([]);
}
?>