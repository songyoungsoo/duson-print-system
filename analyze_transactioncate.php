<?php
/**
 * 🔍 mlangprintauto_transactioncate 테이블 상세 분석
 * 번호-제목 매핑 관계 파악
 */

include "db.php";

if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h1>🔍 mlangprintauto_transactioncate 테이블 상세 분석</h1>";

// 테이블 구조 확인
echo "<h2>📋 테이블 구조</h2>";
$structure_query = "DESCRIBE mlangprintauto_transactioncate";
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
}

// 전체 데이터 샘플 (포스터 관련)
echo "<h2>🎯 포스터 관련 데이터 분석</h2>";

// LittlePrint 페이지 관련 데이터 조회
$poster_query = "SELECT no, Ttable, BigNo, title, TreeNo 
                 FROM mlangprintauto_transactioncate 
                 WHERE Ttable = 'littleprint' OR Ttable LIKE '%poster%' OR Ttable LIKE '%little%'
                 ORDER BY no";

$poster_result = mysqli_query($db, $poster_query);

if ($poster_result && mysqli_num_rows($poster_result) > 0) {
    echo "<h3>📄 포스터(LittlePrint) 관련 카테고리</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>번호(no)</th><th>테이블(Ttable)</th><th>BigNo</th><th>제목(title)</th><th>TreeNo</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($poster_result)) {
        echo "<tr>";
        echo "<td><strong>{$row['no']}</strong></td>";
        echo "<td>{$row['Ttable']}</td>";
        echo "<td>{$row['BigNo']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['TreeNo']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>포스터 관련 데이터를 찾을 수 없습니다.</p>";
}

// 포스터 데이터에서 사용되는 번호들과 매칭 확인
echo "<h2>🔗 포스터 데이터의 번호-제목 매칭 확인</h2>";

// mlangprintauto_littleprint에서 사용되는 번호들 확인
$littleprint_numbers = "SELECT DISTINCT style, Section, TreeSelect, POtype 
                        FROM mlangprintauto_littleprint 
                        ORDER BY style, Section, TreeSelect";

$numbers_result = mysqli_query($db, $littleprint_numbers);
$used_numbers = [];

if ($numbers_result) {
    while ($row = mysqli_fetch_assoc($numbers_result)) {
        $used_numbers['style'][] = $row['style'];
        $used_numbers['Section'][] = $row['Section'];  
        $used_numbers['TreeSelect'][] = $row['TreeSelect'];
        $used_numbers['POtype'][] = $row['POtype'];
    }
    
    // 중복 제거
    foreach ($used_numbers as $field => &$values) {
        $values = array_unique($values);
    }
}

echo "<h3>📊 포스터 테이블에서 사용되는 번호들</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>필드</th><th>사용되는 번호들</th><th>transactioncate에서 찾은 제목</th>";
echo "</tr>";

foreach ($used_numbers as $field => $numbers) {
    echo "<tr>";
    echo "<td><strong>{$field}</strong></td>";
    echo "<td>" . implode(', ', $numbers) . "</td>";
    
    // 각 번호에 대한 제목 찾기
    $titles = [];
    foreach ($numbers as $number) {
        $title_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = '{$number}' LIMIT 1";
        $title_result = mysqli_query($db, $title_query);
        
        if ($title_result && mysqli_num_rows($title_result) > 0) {
            $title_row = mysqli_fetch_assoc($title_result);
            $titles[] = $number . ": " . $title_row['title'];
        } else {
            $titles[] = $number . ": (제목 없음)";
        }
    }
    echo "<td>" . implode("<br>", $titles) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 전체적인 카테고리 구조 파악
echo "<h2>🌳 전체 카테고리 구조 분석</h2>";

$all_categories = "SELECT Ttable, COUNT(*) as count, 
                   MIN(no) as min_no, MAX(no) as max_no,
                   GROUP_CONCAT(DISTINCT BigNo ORDER BY BigNo) as big_nos
                   FROM mlangprintauto_transactioncate 
                   GROUP BY Ttable 
                   ORDER BY count DESC";

$all_result = mysqli_query($db, $all_categories);

if ($all_result) {
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>테이블명</th><th>항목 수</th><th>번호 범위</th><th>BigNo 값들</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($all_result)) {
        echo "<tr>";
        echo "<td><strong>{$row['Ttable']}</strong></td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>{$row['min_no']} ~ {$row['max_no']}</td>";
        echo "<td>{$row['big_nos']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    margin: 20px;
    background-color: #f8f9fa;
}

h1, h2, h3 {
    color: #495057;
}

table {
    background-color: white;
    margin: 10px 0;
    width: 100%;
    max-width: 1200px;
}

th {
    background-color: #e9ecef !important;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

td {
    max-width: 300px;
    word-wrap: break-word;
}
</style>