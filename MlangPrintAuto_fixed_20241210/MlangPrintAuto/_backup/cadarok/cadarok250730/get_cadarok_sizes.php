<?php
// 출력 버퍼링 시작 (불필요한 출력 방지)
ob_start();

// 에러 표시 비활성화 (프로덕션용)
ini_set('display_errors', 0);
error_reporting(0);

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../NameCard/db_ajax.php";

// 불필요한 출력 제거
ob_clean();

$category_type = $_GET['category_type'] ?? '';

if (empty($category_type)) {
    echo json_encode([]);
    exit;
}

try {
    $GGTABLE = "MlangPrintAuto_transactionCate";
    
    // 규격 옵션 가져오기
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
    error_log("카다록 규격 옵션 조회 오류: " . $e->getMessage());
    echo json_encode([]);
}
?>