<?php
require_once __DIR__ . '/db.php';

$conn = $db;

// 샘플 견적 데이터 10개
$samples = [
    ['ABC 인쇄', 50, 80, 1000, 1, 'art', 4, 0, 0, 0, 'none', 'none', 0, 'none', 0],
    ['디자인 스튜디오', 60, 90, 2000, 2, 'yupo', 5, 1, 0, 0, 'glossy', 'none', 0, 'none', 0],
    ['브랜드 컴퍼니', 70, 100, 1500, 1, 'silver_deadlong', 3, 0, 1, 0, 'matte', 'glossy_gold', 1, 'none', 0],
    ['마케팅 에이전시', 80, 120, 3000, 3, 'clear_deadlong', 4, 1, 1, 5000, 'uv', 'matte_silver', 1, 'raised', 0],
    ['패키징 솔루션', 55, 85, 2500, 2, 'gold_paper', 5, 0, 0, 0, 'glossy', 'none', 0, 'recessed', 1],
    ['프린팅 하우스', 65, 95, 1800, 1, 'silver_paper', 2, 1, 0, 0, 'none', 'glossy_gold', 0, 'none', 0],
    ['크리에이티브 랩', 75, 110, 2200, 2, 'kraft', 3, 0, 1, 3000, 'matte', 'none', 1, 'raised', 1],
    ['비주얼 스튜디오', 45, 75, 1200, 1, 'hologram', 4, 1, 0, 0, 'glossy', 'matte_gold', 0, 'none', 0],
    ['광고 기획사', 85, 125, 3500, 3, 'art', 5, 0, 1, 8000, 'uv', 'glossy_silver', 1, 'recessed', 1],
    ['디지털 프린트', 90, 130, 4000, 2, 'yupo', 4, 1, 1, 10000, 'matte', 'none', 1, 'raised', 0]
];

$inserted = 0;

foreach ($samples as $sample) {
    list($company, $width, $height, $quantity, $knife_count, $material, $colors, 
         $need_design, $need_white, $delivery, $coating, $foil, $need_plate, $embossing, $partial) = $sample;
    
    // 견적 번호 생성
    $quote_number = 'Q' . date('Ymd') . sprintf('%04d', rand(1000, 9999));
    
    // 간단한 가격 계산 (실제 계산 로직 간소화)
    $area = $width * $height * $quantity;
    $material_prices = ['art'=>1.5, 'yupo'=>2.5, 'silver_deadlong'=>2.5, 'clear_deadlong'=>2.5, 
                        'gold_paper'=>3.5, 'silver_paper'=>3.5, 'kraft'=>2.3, 'hologram'=>5.0];
    $material_price = $material_prices[$material];
    $total_price = ($area * $material_price / 1000) + ($colors * 10000) + ($quantity * 50);
    
    $supply_price = round($total_price / 1.1); // 공급가 = 총액 / 1.1
    $vat = $total_price - $supply_price; // 부가세
    
    $sql = "INSERT INTO roll_sticker_quotes 
            (quote_number, company_name, width, height, quantity, knife_count, material, colors, 
             need_design, need_white_printing, delivery_prepaid, coating, foil, 
             embossing, partial_coating, supply_price, vat, total_price, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "✗ SQL 준비 실패: " . $conn->error . "<br>";
        continue;
    }
    $stmt->bind_param("ssddiisisississddd", 
        $quote_number, $company, $width, $height, $quantity, $knife_count, $material, $colors,
        $need_design, $need_white, $delivery, $coating, $foil, 
        $embossing, $partial, $supply_price, $vat, $total_price
    );
    
    if ($stmt->execute()) {
        $inserted++;
        echo "✓ {$company} 견적 추가 완료 (견적번호: {$quote_number})<br>";
    } else {
        echo "✗ {$company} 견적 추가 실패: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

$conn->close();

echo "<br><strong>총 {$inserted}개의 샘플 견적이 추가되었습니다.</strong><br>";
echo "<br><a href='shop/quote_list.php'>견적 리스트 보기</a>";
?>
