<?php
include "db.php";
mysqli_set_charset($db, "utf8");

echo "<h2>포스터 규격 데이터 추가</h2>";

// 재질 목록
$materials = [604, 605, 606, 607, 608, 609, 679, 680, 958];

// 추가할 규격들 (610은 이미 있으므로 제외)
$new_sections = [
    '611' => '국전',
    '612' => '4절', 
    '613' => '2절'
];

// 기본 데이터 구조 (610 기준으로 복사)
$base_data = [
    'style' => '590',
    'POtype' => ['1', '2'], // 단면, 양면
    'quantity' => ['10', '20', '50', '100'],
    'DesignMoney' => '20000'
];

echo "<h3>추가할 규격:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Section ID</th><th>규격명</th><th>가격 정책</th></tr>";
foreach ($new_sections as $section_id => $section_name) {
    echo "<tr>";
    echo "<td>$section_id</td>";
    echo "<td>$section_name</td>";
    echo "<td>국2절 기준 동일 가격</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>데이터 추가 진행:</h3>";

$success_count = 0;
$total_count = 0;

foreach ($materials as $material_id) {
    // 재질명 가져오기
    $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = '$material_id'";
    $name_result = mysqli_query($db, $name_query);
    $material_name = mysqli_fetch_assoc($name_result)['title'] ?? '알수없음';
    
    echo "<h4>[$material_id] $material_name 규격 추가:</h4>";
    
    foreach ($new_sections as $section_id => $section_name) {
        echo "<strong>규격 [$section_id] $section_name 추가:</strong><br>";
        
        // 610 기준 가격 데이터 가져오기
        $price_query = "SELECT quantity, money, POtype FROM mlangprintauto_littleprint 
                       WHERE style = '590' AND TreeSelect = '$material_id' AND Section = '610'
                       ORDER BY POtype, CAST(quantity AS UNSIGNED)";
        $price_result = mysqli_query($db, $price_query);
        
        if ($price_result && mysqli_num_rows($price_result) > 0) {
            while ($price_row = mysqli_fetch_assoc($price_result)) {
                $quantity = $price_row['quantity'];
                $money = $price_row['money'];
                $potype = $price_row['POtype'];
                
                // 새 규격에 대해 동일한 가격으로 데이터 추가
                $insert_query = "INSERT INTO mlangprintauto_littleprint 
                               (style, TreeSelect, Section, POtype, quantity, money, DesignMoney)
                               VALUES 
                               ('590', '$material_id', '$section_id', '$potype', '$quantity', '$money', '20000')";
                
                $insert_result = mysqli_query($db, $insert_query);
                $total_count++;
                
                if ($insert_result) {
                    $success_count++;
                    $potype_text = ($potype == '1') ? '단면' : '양면';
                    echo "✅ {$quantity}매 $potype_text: " . number_format($money) . "원<br>";
                } else {
                    echo "❌ 오류: " . mysqli_error($db) . "<br>";
                }
            }
        } else {
            echo "❌ 기준 데이터(610) 없음<br>";
        }
        echo "<br>";
    }
    echo "<br>";
}

echo "<h3>📊 추가 완료 결과:</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50;'>";
echo "<strong>성공:</strong> $success_count / $total_count 건<br>";
echo "<strong>추가된 규격:</strong> " . count($new_sections) . "개<br>";
echo "<strong>대상 재질:</strong> " . count($materials) . "개<br>";
echo "<strong>예상 총 레코드:</strong> " . (count($materials) * count($new_sections) * 4 * 2) . "개<br>";
echo "</div>";

// 결과 확인
echo "<h3>🔍 추가 후 규격 확인:</h3>";
$test_material = $materials[0]; // 첫 번째 재질로 테스트

$verify_query = "SELECT DISTINCT Section FROM mlangprintauto_littleprint 
                WHERE style = '590' AND TreeSelect = '$test_material'
                ORDER BY Section ASC";
$verify_result = mysqli_query($db, $verify_query);

echo "<strong>재질 [$test_material]의 사용 가능한 규격:</strong><br>";
if ($verify_result) {
    while ($row = mysqli_fetch_assoc($verify_result)) {
        $section_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = '{$row['Section']}'";
        $section_result = mysqli_query($db, $section_query);
        $section_name = mysqli_fetch_assoc($section_result)['title'] ?? '알수없음';
        
        echo "✅ [{$row['Section']}] $section_name<br>";
    }
}

echo "<h3>🧪 테스트 링크:</h3>";
echo "<a href='mlangprintauto/poster/index_compact.php' target='_blank'>포스터 페이지에서 확인하기</a><br>";
echo "<a href='mlangprintauto/poster/get_paper_sizes.php?section=$test_material' target='_blank'>규격 API 테스트 ($test_material)</a>";

mysqli_close($db);
?>

<style>
h2, h3, h4 { color: #333; margin-top: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th { background: #f0f0f0; }
a { color: #0066cc; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>