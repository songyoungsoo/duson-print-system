<?php
/**
 * 카다록 규격 옵션 조회 API (통합 장바구니 호환 버전)
 * 경로: MlangPrintAuto/cadarok/get_sizes_new.php
 */

header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

try {
    $type = $_POST['type'] ?? '';
    
    if (empty($type)) {
        throw new Exception('구분을 선택해주세요.');
    }

    $page = "cadarok";
    $GGTABLE = "MlangPrintAuto_transactionCate";
    
    // 선택된 구분에 따른 규격 옵션 조회
    $stmt = mysqli_prepare($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable = ? AND BigNo = ? ORDER BY no ASC");
    mysqli_stmt_bind_param($stmt, "ss", $page, $type);
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
    
    echo json_encode($options, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if ($connect) {
    mysqli_close($connect);
}
?>