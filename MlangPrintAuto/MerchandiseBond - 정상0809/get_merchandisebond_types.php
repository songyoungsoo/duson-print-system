<?php
// 상품권 종류 조회 API (간단 버전)
// 구분(MY_type)에 따른 종류(PN_type) 목록을 반환

// 오류 출력 방지 및 JSON 응답 헤더 설정
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
try {
    include "../../db.php";
    
    if (!$db) {
        throw new Exception("데이터베이스 연결 실패");
    }
    
    mysqli_set_charset($db, "utf8");
} catch (Exception $e) {
    echo json_encode([]);
    exit;
}

$category_type = $_GET['category_type'] ?? '';

if (empty($category_type)) {
    echo json_encode([]);
    exit;
}

try {
    // MlangPrintAuto_transactionCate 테이블에서 상품권 종류 조회
    // BigNo가 선택된 구분의 no와 같은 하위 항목들을 가져옴
    $query = "SELECT no, title FROM MlangPrintAuto_transactionCate 
              WHERE BigNo = ? AND Ttable = 'MerchandiseBond' 
              ORDER BY no ASC";
    
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $category_type);
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
    
} catch (Exception $e) {
    echo json_encode([]);
}

if ($db) {
    mysqli_close($db);
}
?>