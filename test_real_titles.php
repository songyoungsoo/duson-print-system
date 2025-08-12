<?php
/**
 * 🧪 실제 제목 표시 테스트 (JOIN 쿼리 적용)
 */

include "db.php";
include "includes/SmartFieldComponent.php";

if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h1>🧪 실제 제목 표시 테스트 (JOIN 쿼리 적용)</h1>";

// 포스터 스마트 컴포넌트 생성
$smartComponent = new SmartFieldComponent($db, 'poster');

echo "<h2>📋 포스터 스마트 컴포넌트 - 실제 제목 표시</h2>";

try {
    echo "<h3>🎯 수정된 필드 렌더링 결과</h3>";
    
    // MY_type 필드 (style → 소량포스터)
    echo "<h4>🏷️ MY_type 필드 (590 → 소량포스터)</h4>";
    echo $smartComponent->renderField('MY_type');
    
    // PN_type 필드 (Section → 국2절)  
    echo "<h4>📏 PN_type 필드 (610 → 국2절)</h4>";
    echo $smartComponent->renderField('PN_type');
    
    // MY_Fsd 필드 (TreeSelect → 120아트/스노우, 80모조)
    echo "<h4>📄 MY_Fsd 필드 (604 → 120아트/스노우, 679 → 80모조)</h4>";
    echo $smartComponent->renderField('MY_Fsd');
    
    // POtype 필드 (특별 처리 → 단면/양면)
    echo "<h4>🔄 POtype 필드 (1 → 단면, 2 → 양면)</h4>";
    echo $smartComponent->renderField('POtype');

    echo "<hr>";
    
    // 전체 필드를 한 번에 렌더링
    echo "<h3>🎨 전체 포스터 주문 폼 (실제 제목 표시)</h3>";
    echo $smartComponent->renderAllFields();

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}

echo "<h2>🔍 JOIN 쿼리 직접 테스트</h2>";

// JOIN 쿼리가 제대로 작동하는지 직접 확인
echo "<h3>📊 style 필드 JOIN 결과</h3>";
$style_query = "SELECT DISTINCT 
                    lt.style as value,
                    COALESCE(tc.title, lt.style) as text
                FROM mlangprintauto_littleprint lt 
                LEFT JOIN mlangprintauto_transactioncate tc ON tc.no = lt.style
                WHERE lt.style IS NOT NULL AND lt.style != '' 
                ORDER BY lt.style";

$style_result = mysqli_query($db, $style_query);
if ($style_result) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($style_result)) {
        echo "<li><strong>값: {$row['value']}</strong> → 표시: {$row['text']}</li>";
    }
    echo "</ul>";
}

echo "<h3>📊 TreeSelect 필드 JOIN 결과</h3>";
$tree_query = "SELECT DISTINCT 
                   lt.TreeSelect as value,
                   COALESCE(tc.title, lt.TreeSelect) as text
               FROM mlangprintauto_littleprint lt 
               LEFT JOIN mlangprintauto_transactioncate tc ON tc.no = lt.TreeSelect
               WHERE lt.TreeSelect IS NOT NULL AND lt.TreeSelect != '' 
               ORDER BY lt.TreeSelect";

$tree_result = mysqli_query($db, $tree_query);
if ($tree_result) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($tree_result)) {
        echo "<li><strong>값: {$row['value']}</strong> → 표시: {$row['text']}</li>";
    }
    echo "</ul>";
}

echo "<h3>📊 Section 필드 JOIN 결과</h3>";
$section_query = "SELECT DISTINCT 
                     lt.Section as value,
                     COALESCE(tc.title, lt.Section) as text
                 FROM mlangprintauto_littleprint lt 
                 LEFT JOIN mlangprintauto_transactioncate tc ON tc.no = lt.Section
                 WHERE lt.Section IS NOT NULL AND lt.Section != '' 
                 ORDER BY lt.Section";

$section_result = mysqli_query($db, $section_query);
if ($section_result) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($section_result)) {
        echo "<li><strong>값: {$row['value']}</strong> → 표시: {$row['text']}</li>";
    }
    echo "</ul>";
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

.smart-field-group {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.field-group-title {
    margin: 0 0 20px 0;
    color: #495057;
    font-size: 1.2rem;
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid #28a745;
    padding-bottom: 10px;
}

ul li {
    margin: 5px 0;
    padding: 5px;
    background-color: #f8f9fa;
}

hr {
    margin: 30px 0;
}
</style>