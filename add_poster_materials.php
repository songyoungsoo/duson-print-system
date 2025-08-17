<?php
include "db.php";
mysqli_set_charset($db, "utf8");

echo "<h2>포스터 재질 가격 데이터 추가</h2>";

// 누락된 재질들과 추정 가격 정의
$missing_materials = [
    // TreeSelect ID => [material_name, base_price_multiplier]
    '605' => ['150아트/스노우', 1.25],  // 150g (120g 대비 25% 증가)
    '606' => ['180아트/스노우', 1.5],   // 180g (120g 대비 50% 증가)  
    '607' => ['200아트/스노우', 1.75],  // 200g (120g 대비 75% 증가)
    '608' => ['250아트/스노우', 2.1],   // 250g (120g 대비 110% 증가)
    '609' => ['300아트/스노우', 2.5],   // 300g (120g 대비 150% 증가)
    '680' => ['100모조', 0.9],          // 100g (80g 대비 12.5% 증가, 120g 대비 10% 감소)
    '958' => ['200g아트/스노우지', 1.75] // 200g (120g와 동등한 200g 재질)
];

// 기준 가격 (604 - 120아트/스노우 단면 가격)
$base_prices = [
    '10' => 60000,
    '20' => 90000, 
    '50' => 180000,
    '100' => 300000
];

// 양면 인쇄 추가 비용 (기존 패턴 분석)
$double_side_multiplier = 1.5; // 단면 대비 1.5배

// DesignMoney 기본값 (기존 데이터와 동일)
$design_money = '20000';

echo "<h3>추가할 재질 데이터:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>TreeSelect</th><th>재질명</th><th>가격 배율</th><th>단면 10매 가격</th></tr>";

foreach ($missing_materials as $treeselect => $info) {
    $material_name = $info[0];
    $multiplier = $info[1];
    $sample_price = intval($base_prices['10'] * $multiplier);
    
    echo "<tr>";
    echo "<td>$treeselect</td>";
    echo "<td>$material_name</td>";
    echo "<td>" . ($multiplier * 100) . "%</td>";
    echo "<td>" . number_format($sample_price) . "원</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>데이터 추가 진행:</h3>";

$success_count = 0;
$total_count = 0;

foreach ($missing_materials as $treeselect => $info) {
    $material_name = $info[0];
    $multiplier = $info[1];
    
    echo "<h4>[$treeselect] $material_name 추가 중...</h4>";
    
    // 각 수량과 인쇄면에 대해 데이터 추가
    $quantities = ['10', '20', '50', '100'];
    $potypes = ['1', '2']; // 1=단면, 2=양면
    
    foreach ($quantities as $quantity) {
        foreach ($potypes as $potype) {
            $base_price = $base_prices[$quantity];
            
            if ($potype == '1') {
                // 단면
                $final_price = intval($base_price * $multiplier);
            } else {
                // 양면 (단면 × 1.5)
                $final_price = intval($base_price * $multiplier * $double_side_multiplier);
            }
            
            // INSERT 쿼리
            $query = "INSERT INTO mlangprintauto_littleprint 
                     (style, TreeSelect, Section, POtype, quantity, money, DesignMoney)
                     VALUES 
                     ('590', '$treeselect', '610', '$potype', '$quantity', '$final_price', '$design_money')";
            
            $result = mysqli_query($db, $query);
            $total_count++;
            
            if ($result) {
                $success_count++;
                $potype_text = ($potype == '1') ? '단면' : '양면';
                echo "✅ {$quantity}매 $potype_text: " . number_format($final_price) . "원<br>";
            } else {
                echo "❌ 오류: " . mysqli_error($db) . "<br>";
            }
        }
    }
    echo "<br>";
}

echo "<h3>📊 추가 완료 결과:</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50;'>";
echo "<strong>성공:</strong> $success_count / $total_count 건<br>";
echo "<strong>추가된 재질:</strong> " . count($missing_materials) . "개<br>";
echo "<strong>총 레코드:</strong> " . (count($missing_materials) * 4 * 2) . "개 예상<br>";
echo "</div>";

// 결과 확인
echo "<h3>🔍 추가 후 재질 확인:</h3>";
$verify_query = "SELECT DISTINCT TreeSelect FROM mlangprintauto_littleprint 
                WHERE style = '590' 
                ORDER BY TreeSelect ASC";
$verify_result = mysqli_query($db, $verify_query);

$available_materials = [];
if ($verify_result) {
    while ($row = mysqli_fetch_assoc($verify_result)) {
        $available_materials[] = $row['TreeSelect'];
        
        // 재질명 가져오기
        $name_query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = '{$row['TreeSelect']}'";
        $name_result = mysqli_query($db, $name_query);
        $material_name = mysqli_fetch_assoc($name_result)['title'] ?? '알수없음';
        
        echo "✅ [{$row['TreeSelect']}] $material_name<br>";
    }
}

echo "<p><strong>현재 사용 가능한 재질:</strong> " . count($available_materials) . "개</p>";

echo "<h3>🧪 테스트 링크:</h3>";
echo "<a href='MlangPrintAuto/Poster/index_compact.php' target='_blank'>포스터 페이지에서 확인하기</a>";

mysqli_close($db);
?>

<style>
h2, h3, h4 { color: #333; margin-top: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th { background: #f0f0f0; }
a { color: #0066cc; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>