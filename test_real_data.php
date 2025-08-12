<?php
/**
 * 🧪 실제 데이터로 스마트 컴포넌트 테스트
 */

include "db.php";
include "includes/SmartFieldComponent.php";

if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h1>🧪 실제 데이터로 스마트 컴포넌트 테스트</h1>";

// 포스터 스마트 컴포넌트 생성
$smartComponent = new SmartFieldComponent($db, 'poster');

echo "<h2>📋 포스터 스마트 컴포넌트 테스트</h2>";

// 디버그 정보 표시
echo $smartComponent->debugComponent();

echo "<h3>🎯 실제 필드 렌더링 테스트</h3>";

try {
    // MY_type 필드 (실제로는 style 컬럼)
    echo "<h4>🏷️ MY_type 필드 (실제: style 컬럼)</h4>";
    echo $smartComponent->renderField('MY_type');
    
    // PN_type 필드 (실제로는 Section 컬럼)  
    echo "<h4>📏 PN_type 필드 (실제: Section 컬럼)</h4>";
    echo $smartComponent->renderField('PN_type');
    
    // MY_Fsd 필드 (실제로는 TreeSelect 컬럼)
    echo "<h4>📄 MY_Fsd 필드 (실제: TreeSelect 컬럼)</h4>";
    echo $smartComponent->renderField('MY_Fsd');
    
    // POtype 필드 (실제로도 POtype 컬럼 - 일치!)
    echo "<h4>🔄 POtype 필드 (실제: POtype 컬럼)</h4>";
    echo $smartComponent->renderField('POtype');

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}

echo "<h3>📊 실제 데이터베이스 내용 확인</h3>";

// mlangprintauto_littleprint 테이블의 실제 데이터 확인
echo "<h4>🗂️ mlangprintauto_littleprint 테이블 샘플 데이터</h4>";
$query = "SELECT style, Section, TreeSelect, POtype, quantity, money, DesignMoney FROM mlangprintauto_littleprint LIMIT 5";
$result = mysqli_query($db, $query);

if ($result) {
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>style<br>(MY_type)</th><th>Section<br>(PN_type)</th><th>TreeSelect<br>(MY_Fsd)</th>";
    echo "<th>POtype</th><th>quantity<br>(MY_amount)</th><th>money</th><th>DesignMoney<br>(ordertype)</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>{$row['style']}</strong></td>";
        echo "<td>{$row['Section']}</td>";
        echo "<td>{$row['TreeSelect']}</td>";
        echo "<td>{$row['POtype']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td>{$row['money']}</td>";
        echo "<td>{$row['DesignMoney']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ 데이터 조회 실패: " . mysqli_error($db) . "</p>";
}

// 각 필드의 고유값들 확인
echo "<h4>🔍 각 필드의 실제 고유값들</h4>";

$fields = [
    'style' => 'MY_type (구분)',
    'Section' => 'PN_type (종이규격)', 
    'TreeSelect' => 'MY_Fsd (종이종류)',
    'POtype' => 'POtype (인쇄면)'
];

foreach ($fields as $real_field => $smart_field) {
    echo "<h5>📋 {$smart_field}</h5>";
    
    $values_query = "SELECT DISTINCT {$real_field}, COUNT(*) as count 
                     FROM mlangprintauto_littleprint 
                     WHERE {$real_field} IS NOT NULL AND {$real_field} != '' 
                     GROUP BY {$real_field} 
                     ORDER BY count DESC 
                     LIMIT 10";
    
    $values_result = mysqli_query($db, $values_query);
    
    if ($values_result) {
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($values_result)) {
            echo "<li><strong>{$row[$real_field]}</strong> ({$row['count']}개)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>조회 실패: " . mysqli_error($db) . "</p>";
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

h1, h2, h3, h4, h5 {
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

select {
    width: 300px;
    padding: 8px;
    margin: 5px 0;
}

.form-group {
    margin: 15px 0;
    padding: 10px;
    border: 1px solid #dee2e6;
    background-color: white;
    border-radius: 5px;
}
</style>