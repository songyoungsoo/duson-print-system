<?php
/**
 * 🔍 데이터베이스 구조 분석 도구
 * 스마트 컴포넌트와 실제 DB 테이블 매핑 확인용
 */

include "db.php";

if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h1>🔍 두손기획인쇄 데이터베이스 구조 분석</h1>";

// MlangPrintAuto 관련 테이블 목록 조회
echo "<h2>📋 MlangPrintAuto 관련 테이블들</h2>";
$table_query = "SHOW TABLES LIKE '%MlangPrintAuto%'";
$result = mysqli_query($db, $table_query);

$tables = [];
if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $tables[] = $row[0];
    }
}

echo "<ul>";
foreach ($tables as $table) {
    echo "<li><strong>{$table}</strong></li>";
}
echo "</ul>";

// 주요 테이블들의 구조 분석
$important_tables = [
    'MlangPrintAuto_littleprint',  // 포스터/전단지
    'MlangPrintAuto_MerchandiseBond', // 쿠폰
    'MlangPrintAuto_transactionCate', // 카테고리
    'MlangPrintAuto_namecard', // 명함 (있다면)
];

foreach ($important_tables as $table) {
    if (in_array($table, $tables)) {
        echo "<h3>🗂️ {$table} 테이블 구조</h3>";
        
        // 테이블 구조 조회
        $structure_query = "DESCRIBE {$table}";
        $structure_result = mysqli_query($db, $structure_query);
        
        if ($structure_result) {
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>기본값</th><th>Extra</th>";
            echo "</tr>";
            
            while ($field = mysqli_fetch_assoc($structure_result)) {
                echo "<tr>";
                echo "<td><strong>{$field['Field']}</strong></td>";
                echo "<td>{$field['Type']}</td>";
                echo "<td>{$field['Null']}</td>";
                echo "<td>{$field['Key']}</td>";
                echo "<td>{$field['Default']}</td>";
                echo "<td>{$field['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // 샘플 데이터 조회 (최근 5개)
            echo "<h4>📊 샘플 데이터 (최근 5개)</h4>";
            $sample_query = "SELECT * FROM {$table} ORDER BY seq DESC LIMIT 5";
            $sample_result = mysqli_query($db, $sample_query);
            
            if ($sample_result && mysqli_num_rows($sample_result) > 0) {
                echo "<table border='1' cellpadding='3' cellspacing='0' style='border-collapse: collapse; font-size: 0.8rem;'>";
                
                // 헤더 출력
                $first_row = mysqli_fetch_assoc($sample_result);
                echo "<tr style='background-color: #e0e0e0;'>";
                foreach (array_keys($first_row) as $column) {
                    echo "<th>{$column}</th>";
                }
                echo "</tr>";
                
                // 첫 번째 행 출력
                echo "<tr>";
                foreach ($first_row as $value) {
                    $display_value = strlen($value) > 30 ? substr($value, 0, 30) . '...' : $value;
                    echo "<td>{$display_value}</td>";
                }
                echo "</tr>";
                
                // 나머지 행들 출력
                while ($row = mysqli_fetch_assoc($sample_result)) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        $display_value = strlen($value) > 30 ? substr($value, 0, 30) . '...' : $value;
                        echo "<td>{$display_value}</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>샘플 데이터가 없습니다.</p>";
            }
        }
        echo "<hr>";
    }
}

// 실제 사용되는 필드값들 분석
echo "<h2>🎯 실제 필드값 분석</h2>";

if (in_array('MlangPrintAuto_littleprint', $tables)) {
    echo "<h3>📊 MlangPrintAuto_littleprint 필드값 분포</h3>";
    
    // 주요 필드들의 고유값 조회
    $fields_to_analyze = ['MY_type', 'MY_Fsd', 'PN_type', 'POtype', 'ordertype'];
    
    foreach ($fields_to_analyze as $field) {
        echo "<h4>🔍 {$field} 필드의 고유값들</h4>";
        
        $values_query = "SELECT DISTINCT {$field}, COUNT(*) as count 
                         FROM MlangPrintAuto_littleprint 
                         WHERE {$field} IS NOT NULL AND {$field} != '' 
                         GROUP BY {$field} 
                         ORDER BY count DESC 
                         LIMIT 10";
        
        $values_result = mysqli_query($db, $values_query);
        
        if ($values_result) {
            echo "<ul>";
            while ($row = mysqli_fetch_assoc($values_result)) {
                echo "<li><strong>{$row[$field]}</strong> ({$row['count']}개)</li>";
            }
            echo "</ul>";
        }
    }
}

// 쿠폰 테이블도 분석
if (in_array('MlangPrintAuto_MerchandiseBond', $tables)) {
    echo "<h3>📊 MlangPrintAuto_MerchandiseBond 필드값 분포</h3>";
    
    $fields_to_analyze = ['MY_type', 'PN_type', 'POtype', 'ordertype'];
    
    foreach ($fields_to_analyze as $field) {
        echo "<h4>🔍 {$field} 필드의 고유값들</h4>";
        
        $values_query = "SELECT DISTINCT {$field}, COUNT(*) as count 
                         FROM MlangPrintAuto_MerchandiseBond 
                         WHERE {$field} IS NOT NULL AND {$field} != '' 
                         GROUP BY {$field} 
                         ORDER BY count DESC 
                         LIMIT 10";
        
        $values_result = mysqli_query($db, $values_query);
        
        if ($values_result) {
            echo "<ul>";
            while ($row = mysqli_fetch_assoc($values_result)) {
                echo "<li><strong>{$row[$field]}</strong> ({$row['count']}개)</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>데이터 조회 실패 또는 해당 필드 없음</p>";
        }
    }
}

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    margin: 20px;
    background-color: #f8f9fa;
}

h1, h2, h3, h4 {
    color: #495057;
}

table {
    background-color: white;
    margin: 10px 0;
    width: 100%;
    max-width: 1000px;
}

th {
    background-color: #e9ecef !important;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

ul li {
    margin: 5px 0;
}

hr {
    margin: 30px 0;
}
</style>