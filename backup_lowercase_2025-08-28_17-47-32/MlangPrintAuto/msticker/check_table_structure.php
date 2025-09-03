<?php
include "../../db.php";
include "../../includes/functions.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>🔍 mlangprintauto_msticker 테이블 구조 확인</h2>";

// Check if table exists
$check_table = "SHOW TABLES LIKE "MlangPrintAuto_msticker'";
$table_result = mysqli_query($db, $check_table);

if (mysqli_num_rows($table_result) > 0) {
    echo "✅ mlangprintauto_msticker 테이블이 존재합니다.<br><br>";
    
    // Show table structure
    echo "<h3>테이블 구조:</h3>";
    $structure_query = "DESCRIBE mlangprintauto_msticker";
    $structure_result = mysqli_query($db, $structure_query);
    
    if ($structure_result) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($structure_result)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Show sample data
    echo "<h3>샘플 데이터 (최대 5개):</h3>";
    $sample_query = "SELECT * FROM MlangPrintAuto_msticker LIMIT 5";
    $sample_result = mysqli_query($db, $sample_query);
    
    if ($sample_result && mysqli_num_rows($sample_result) > 0) {
        echo "<table border='1'>";
        // Header
        $row = mysqli_fetch_assoc($sample_result);
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        // Data
        mysqli_data_seek($sample_result, 0);
        while ($row = mysqli_fetch_assoc($sample_result)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ 테이블에 데이터가 없습니다.";
    }
    
} else {
    echo "❌ mlangprintauto_msticker 테이블이 존재하지 않습니다.";
    
    // Let's check what similar tables exist
    echo "<br><br><h3>유사한 테이블 검색:</h3>";
    $similar_query = "SHOW TABLES LIKE '%msticker%'";
    $similar_result = mysqli_query($db, $similar_query);
    
    if (mysqli_num_rows($similar_result) > 0) {
        while ($row = mysqli_fetch_array($similar_result)) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "msticker 관련 테이블이 없습니다.";
    }
}

mysqli_close($db);
?>