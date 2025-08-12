<?php
// 디버깅용 봉투 종류 가져오기
include "../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['error' => 'DB 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8");

$category_type = $_GET['category_type'] ?? '';

echo "<!-- 디버깅 정보 -->\n";
echo "<!-- category_type: " . htmlspecialchars($category_type) . " -->\n";

if (empty($category_type)) {
    echo json_encode(['error' => 'category_type이 비어있음']);
    exit;
}

try {
    // 1. 먼저 MlangPrintAuto_transactionCate 테이블 구조 확인
    $table_check = "SHOW TABLES LIKE 'MlangPrintAuto_transactionCate'";
    $result = mysqli_query($connect, $table_check);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['error' => 'MlangPrintAuto_transactionCate 테이블이 존재하지 않음']);
        exit;
    }
    
    // 2. 테이블 구조 확인
    $structure_query = "DESCRIBE MlangPrintAuto_transactionCate";
    $structure_result = mysqli_query($connect, $structure_query);
    
    echo "<!-- 테이블 구조: -->\n";
    while ($col = mysqli_fetch_assoc($structure_result)) {
        echo "<!-- " . $col['Field'] . " (" . $col['Type'] . ") -->\n";
    }
    
    // 3. envelope 관련 모든 데이터 확인
    $all_envelope_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable = 'envelope' LIMIT 20";
    $all_result = mysqli_query($connect, $all_envelope_query);
    
    echo "<!-- envelope 관련 모든 데이터: -->\n";
    while ($row = mysqli_fetch_assoc($all_result)) {
        echo "<!-- no: " . $row['no'] . ", title: " . $row['title'] . ", TreeNo: " . ($row['TreeNo'] ?? 'NULL') . ", BigNo: " . ($row['BigNo'] ?? 'NULL') . " -->\n";
    }
    
    // 4. 실제 쿼리 실행
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
        
        echo "<!-- 쿼리 결과 개수: " . count($options) . " -->\n";
        echo json_encode($options);
    } else {
        echo json_encode(['error' => '쿼리 준비 실패: ' . mysqli_error($connect)]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => '예외 발생: ' . $e->getMessage()]);
}

mysqli_close($connect);
?>