<?php
/**
 * 🧪 완전 DB 기반 동적 로딩 테스트
 * 모든 옵션을 데이터베이스에서 동적으로 가져오는지 확인
 */

include "db.php";
include "includes/SmartFieldComponent.php";

if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h1>🧪 완전 DB 기반 동적 로딩 테스트</h1>";

// 포스터 스마트 컴포넌트 생성
$smartComponent = new SmartFieldComponent($db, 'poster');

echo "<h2>📋 포스터 스마트 컴포넌트 - 완전 DB 기반 동적 로딩</h2>";

try {
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;'>";
    
    // 왼쪽: 각 필드별 개별 테스트
    echo "<div>";
    echo "<h3>🎯 각 필드별 개별 테스트</h3>";
    
    // MY_type 필드 (style → transactioncate JOIN)
    echo "<h4>🏷️ MY_type 필드 (JOIN 결과)</h4>";
    echo $smartComponent->renderField('MY_type');
    
    // PN_type 필드 (Section → transactioncate JOIN)
    echo "<h4>📏 PN_type 필드 (JOIN 결과)</h4>";
    echo $smartComponent->renderField('PN_type');
    
    // MY_Fsd 필드 (TreeSelect → transactioncate JOIN)
    echo "<h4>📄 MY_Fsd 필드 (JOIN 결과)</h4>";
    echo $smartComponent->renderField('MY_Fsd');
    
    // POtype 필드 (DB 기반 동적 처리)
    echo "<h4>🔄 POtype 필드 (DB 기반 동적)</h4>";
    echo $smartComponent->renderField('POtype');
    
    // MY_amount 필드 (quantity에서 동적 로딩)
    echo "<h4>📊 MY_amount 필드 (quantity 기반)</h4>";
    echo $smartComponent->renderField('MY_amount');
    
    // ordertype 필드 (DesignMoney에서 동적 로딩)
    echo "<h4>✏️ ordertype 필드 (DesignMoney 기반)</h4>";
    echo $smartComponent->renderField('ordertype');
    
    echo "</div>";
    
    // 오른쪽: 전체 폼 렌더링
    echo "<div>";
    echo "<h3>🎨 전체 폼 렌더링 (완전 동적)</h3>";
    echo $smartComponent->renderAllFields();
    echo "</div>";
    
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}

// 다른 제품도 테스트해보기
echo "<hr>";
echo "<h2>🔄 다른 제품들도 테스트</h2>";

$products_to_test = [
    'leaflet' => '전단지',
    'namecard' => '명함',
    'envelope' => '봉투'
];

foreach ($products_to_test as $product_code => $product_name) {
    echo "<h3>📋 {$product_name} ({$product_code})</h3>";
    
    try {
        $productComponent = new SmartFieldComponent($db, $product_code);
        
        echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 10px 0;'>";
        
        // 주요 필드들만 간단히 테스트
        $test_fields = ['MY_type', 'PN_type', 'POtype'];
        
        foreach ($test_fields as $field) {
            if (ProductFieldMapper::isFieldActive($product_code, $field)) {
                echo "<div>";
                echo "<h5>" . ProductFieldMapper::getFieldContext($product_code, $field)['label'] . "</h5>";
                echo $productComponent->renderField($field, '', ['show_icon' => false, 'show_label' => false]);
                echo "</div>";
            }
        }
        
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ {$product_name} 오류: " . $e->getMessage() . "</p>";
    }
}

// 실제 DB 쿼리 결과 확인
echo "<hr>";
echo "<h2>🔍 실제 DB 쿼리 결과 직접 확인</h2>";

// 포스터 테이블의 각 필드별 실제 값들 확인
echo "<h3>📊 포스터 테이블 실제 데이터 분포</h3>";

$fields_to_check = [
    'style' => 'MY_type (구분)',
    'Section' => 'PN_type (종이규격)', 
    'TreeSelect' => 'MY_Fsd (종이종류)',
    'POtype' => 'POtype (인쇄면)',
    'quantity' => 'MY_amount (수량)',
    'DesignMoney' => 'ordertype (편집비)'
];

echo "<div style='display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;'>";

foreach ($fields_to_check as $db_field => $smart_field) {
    echo "<div>";
    echo "<h4>🔍 {$smart_field}</h4>";
    
    if ($db_field === 'quantity') {
        // 수량은 숫자 정렬
        $query = "SELECT DISTINCT {$db_field}, COUNT(*) as count 
                  FROM mlangprintauto_littleprint 
                  WHERE {$db_field} IS NOT NULL 
                  GROUP BY {$db_field} 
                  ORDER BY CAST({$db_field} AS UNSIGNED)";
    } elseif ($db_field === 'DesignMoney') {
        // 편집비도 숫자 정렬
        $query = "SELECT DISTINCT {$db_field}, COUNT(*) as count 
                  FROM mlangprintauto_littleprint 
                  WHERE {$db_field} IS NOT NULL 
                  GROUP BY {$db_field} 
                  ORDER BY CAST({$db_field} AS UNSIGNED)";
    } else {
        $query = "SELECT DISTINCT {$db_field}, COUNT(*) as count 
                  FROM mlangprintauto_littleprint 
                  WHERE {$db_field} IS NOT NULL 
                  GROUP BY {$db_field} 
                  ORDER BY {$db_field}";
    }
    
    $result = mysqli_query($db, $query);
    
    if ($result) {
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li><strong>{$row[$db_field]}</strong> ({$row['count']}개)</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
}

echo "</div>";

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

select {
    width: 100%;
    max-width: 300px;
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
    margin: 3px 0;
    padding: 3px;
    background-color: #f8f9fa;
    font-size: 0.9rem;
}

hr {
    margin: 30px 0;
}

div {
    word-wrap: break-word;
}
</style>