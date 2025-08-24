<?php
/**
 * SQL 디버그 도구
 */
session_start();
require_once dirname(__DIR__) . "/db.php";

// 데이터베이스 연결 확인
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

if ($connect) {
    mysqli_set_charset($connect, "utf8");
    echo "✅ 데이터베이스 연결 성공<br>";
} else {
    echo "❌ 데이터베이스 연결 실패<br>";
    exit;
}

echo "<h2>🔍 SQL 쿼리 디버깅</h2>";

// 기본 검색 조건
$where_conditions = ["OrderStyle IN ('6', '7', '8')"];
$where_conditions[] = "ThingCate IS NOT NULL AND ThingCate != ''";
$where_clause = implode(' AND ', $where_conditions);

echo "<h3>검색 조건:</h3>";
echo "<pre>" . htmlspecialchars($where_clause) . "</pre>";

// 전체 개수 확인
$count_sql = "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto WHERE {$where_clause}";
echo "<h3>개수 쿼리:</h3>";
echo "<pre>" . htmlspecialchars($count_sql) . "</pre>";

$count_result = mysqli_query($connect, $count_sql);
if ($count_result) {
    $total = mysqli_fetch_assoc($count_result)['total'];
    echo "✅ 총 {$total}개 주문 발견<br>";
} else {
    echo "❌ 개수 쿼리 실패: " . mysqli_error($connect) . "<br>";
}

// 실제 데이터 조회 (최신 10개)
$items_sql = "SELECT No, Type, ThingCate, name, date 
              FROM MlangOrder_PrintAuto 
              WHERE {$where_clause}
              ORDER BY No DESC 
              LIMIT 10";

echo "<h3>데이터 쿼리:</h3>";
echo "<pre>" . htmlspecialchars($items_sql) . "</pre>";

$items_result = mysqli_query($connect, $items_sql);
if ($items_result) {
    echo "✅ 데이터 쿼리 성공<br>";
    
    echo "<h3>조회된 데이터:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>No</th><th>Type</th><th>ThingCate</th><th>name</th><th>date</th><th>파일존재</th></tr>";
    
    $count = 0;
    while ($row = mysqli_fetch_assoc($items_result)) {
        $count++;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['No'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Type'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['ThingCate'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['name'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['date'] ?? 'NULL') . "</td>";
        
        // 파일 존재 확인
        $order_no = $row['No'] ?? '';
        $thing_cate = $row['ThingCate'] ?? '';
        if ($order_no && $thing_cate) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload/{$order_no}/{$thing_cate}";
            $exists = file_exists($file_path) ? "✅" : "❌";
            echo "<td>{$exists}</td>";
        } else {
            echo "<td>-</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p>총 {$count}개 행 조회됨</p>";
    
} else {
    echo "❌ 데이터 쿼리 실패: " . mysqli_error($connect) . "<br>";
}

// mysqli_fetch_assoc 테스트
echo "<h3>fetch_assoc 테스트:</h3>";
$test_sql = "SELECT No, Type FROM MlangOrder_PrintAuto WHERE OrderStyle IN ('6', '7', '8') LIMIT 1";
$test_result = mysqli_query($connect, $test_sql);
if ($test_result) {
    $test_row = mysqli_fetch_assoc($test_result);
    echo "<pre>";
    echo "var_dump 결과:\n";
    var_dump($test_row);
    echo "\nis_array: " . (is_array($test_row) ? "true" : "false") . "\n";
    echo "type: " . gettype($test_row) . "\n";
    echo "</pre>";
} else {
    echo "❌ 테스트 쿼리 실패<br>";
}

?>