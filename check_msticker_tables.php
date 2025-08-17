<?php
echo "<h1>🔍 자석스티커 테이블 구조 확인</h1>";

// 데이터베이스 연결
$host = "localhost";
$user = "root";
$password = "";
$dataname = "duson1830";

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    die("<div style='color:red'>❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "</div>");
}

mysqli_set_charset($db, "utf8mb4");

echo "<h2>📊 데이터베이스 연결 성공</h2>";

// 테이블 목록 확인
echo "<h2>🗂️ 관련 테이블 확인</h2>";

$tables_to_check = [
    'mlangprintauto_transactioncate',
    'MlangPrintAuto_transactionCate', 
    'mlangprintauto_msticker',
    'MlangPrintAuto_msticker',
    'mlangprintauto_namecard',
    'MlangPrintAuto_namecard'
];

foreach ($tables_to_check as $table) {
    $check_query = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($db, $check_query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<h3>✅ $table 테이블 존재</h3>";
        
        // 테이블 구조 확인
        $desc_query = "DESCRIBE $table";
        $desc_result = mysqli_query($db, $desc_query);
        
        if ($desc_result) {
            echo "<table border='1' cellpadding='3' style='border-collapse:collapse; margin:10px 0;'>";
            echo "<tr style='background:#f0f0f0;'><th>필드</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            while ($row = mysqli_fetch_assoc($desc_result)) {
                echo "<tr>";
                echo "<td>{$row['Field']}</td>";
                echo "<td>{$row['Type']}</td>";
                echo "<td>{$row['Null']}</td>";
                echo "<td>{$row['Key']}</td>";
                echo "<td>{$row['Default']}</td>";
                echo "<td>{$row['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // 데이터 샘플 확인
            $sample_query = "SELECT * FROM $table LIMIT 5";
            $sample_result = mysqli_query($db, $sample_query);
            
            if ($sample_result && mysqli_num_rows($sample_result) > 0) {
                echo "<h4>📋 샘플 데이터:</h4>";
                echo "<table border='1' cellpadding='3' style='border-collapse:collapse; margin:10px 0; font-size:12px;'>";
                
                // 헤더
                $first_row = mysqli_fetch_assoc($sample_result);
                echo "<tr style='background:#f0f0f0;'>";
                foreach (array_keys($first_row) as $column) {
                    echo "<th>$column</th>";
                }
                echo "</tr>";
                
                // 첫 번째 행 출력
                echo "<tr>";
                foreach ($first_row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                }
                echo "</tr>";
                
                // 나머지 행들
                while ($row = mysqli_fetch_assoc($sample_result)) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>⚠️ 데이터가 없습니다.</p>";
            }
        }
    } else {
        echo "<p>❌ $table 테이블 없음</p>";
    }
}

// 명함 시스템 구조 확인 (참고용)
echo "<h2>🔍 명함 시스템 참고 구조</h2>";

$namecard_cate_query = "SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='NameCard' ORDER BY BigNo, sort LIMIT 10";
$namecard_cate_result = mysqli_query($db, $namecard_cate_query);

if ($namecard_cate_result && mysqli_num_rows($namecard_cate_result) > 0) {
    echo "<h3>📋 명함 카테고리 구조:</h3>";
    echo "<table border='1' cellpadding='3' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'><th>no</th><th>title</th><th>Ttable</th><th>BigNo</th><th>sort</th></tr>";
    
    while ($row = mysqli_fetch_assoc($namecard_cate_result)) {
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['Ttable']}</td>";
        echo "<td>{$row['BigNo']}</td>";
        echo "<td>{$row['sort']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 명함 가격 테이블 구조
$namecard_price_query = "SELECT * FROM MlangPrintAuto_namecard LIMIT 5";
$namecard_price_result = mysqli_query($db, $namecard_price_query);

if ($namecard_price_result && mysqli_num_rows($namecard_price_result) > 0) {
    echo "<h3>💰 명함 가격 테이블 구조:</h3>";
    echo "<table border='1' cellpadding='3' style='border-collapse:collapse; font-size:12px;'>";
    
    $first_row = mysqli_fetch_assoc($namecard_price_result);
    echo "<tr style='background:#f0f0f0;'>";
    foreach (array_keys($first_row) as $column) {
        echo "<th>$column</th>";
    }
    echo "</tr>";
    
    echo "<tr>";
    foreach ($first_row as $value) {
        echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
    }
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($namecard_price_result)) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>

<style>
body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 5px 8px; border: 1px solid #ddd; }
th { background: #f0f0f0; }
</style>