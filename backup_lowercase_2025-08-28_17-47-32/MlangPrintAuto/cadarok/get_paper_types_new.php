<?php
/**
 * 카다록 용지 옵션 조회 API (통합 장바구니 호환 버전)
 * 경로: mlangprintauto/cadarok/get_paper_types_new.php
 */

header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

try {
    $size = $_POST['size'] ?? '';
    
    if (empty($size)) {
        throw new Exception('규격을 선택해주세요.');
    }

    $page = "cadarok";
    $GGTABLE = "MlangPrintAuto_transactionCate";
    
    // 선택된 규격에 따른 용지 옵션 조회
    $stmt = mysqli_prepare($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable = ? AND BigNo = ? ORDER BY no ASC");
    mysqli_stmt_bind_param($stmt, "ss", $page, $size);
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