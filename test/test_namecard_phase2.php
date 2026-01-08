<?php
/**
 * Phase 2 테스트: 명함 add_to_basket.php 이중 저장 검증
 */

// 데이터베이스 연결
include 'db.php';

echo "=== Phase 2: 명함 이중 저장 테스트 ===\n\n";

// 1. 현재 shop_temp 데이터 확인
$check_query = "SELECT COUNT(*) as total,
                SUM(CASE WHEN data_version = 2 THEN 1 ELSE 0 END) as phase2_count
                FROM shop_temp";
$result = mysqli_query($db, $check_query);
$row = mysqli_fetch_assoc($result);

echo "1. shop_temp 현재 상태:\n";
echo "   총 레코드: {$row['total']}건\n";
echo "   Phase 2 데이터: {$row['phase2_count']}건\n\n";

// 2. 최근 Phase 2 데이터 확인 (data_version = 2)
$recent_query = "SELECT no, product_type,
                 MY_type, Section, st_price,
                 spec_type, spec_material, spec_sides,
                 quantity_value, quantity_unit,
                 price_supply, price_vat, data_version
                 FROM shop_temp
                 WHERE data_version = 2
                 ORDER BY no DESC
                 LIMIT 5";

$recent_result = mysqli_query($db, $recent_query);

if (mysqli_num_rows($recent_result) > 0) {
    echo "2. 최근 Phase 2 데이터 샘플:\n";
    echo str_repeat('-', 80) . "\n";

    while ($cart = mysqli_fetch_assoc($recent_result)) {
        echo "ID: {$cart['no']}\n";
        echo "  제품: {$cart['product_type']}\n";
        echo "  레거시: MY_type={$cart['MY_type']}, Section={$cart['Section']}, st_price={$cart['st_price']}\n";
        echo "  표준: spec_type={$cart['spec_type']}, spec_material={$cart['spec_material']}, spec_sides={$cart['spec_sides']}\n";
        echo "  수량: {$cart['quantity_value']}{$cart['quantity_unit']}\n";
        echo "  가격: 공급가 " . number_format($cart['price_supply']) . "원, VAT포함 " . number_format($cart['price_vat']) . "원\n";
        echo "  버전: {$cart['data_version']}\n";
        echo "\n";
    }
} else {
    echo "2. Phase 2 데이터 없음 (아직 테스트 주문하지 않음)\n\n";
}

// 3. 데이터 무결성 검증
$integrity_query = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN spec_type IS NOT NULL AND data_version = 2 THEN 1 ELSE 0 END) as has_spec,
    SUM(CASE WHEN price_supply > 0 AND data_version = 2 THEN 1 ELSE 0 END) as has_price,
    SUM(CASE WHEN product_data_json IS NOT NULL AND data_version = 2 THEN 1 ELSE 0 END) as has_json
    FROM shop_temp
    WHERE data_version = 2";

$integrity_result = mysqli_query($db, $integrity_query);
$integrity = mysqli_fetch_assoc($integrity_result);

if ($integrity['total'] > 0) {
    echo "3. Phase 2 데이터 무결성 검증:\n";
    echo "   총 Phase 2 레코드: {$integrity['total']}건\n";
    echo "   spec_type 있음: {$integrity['has_spec']}건 ";
    echo ($integrity['has_spec'] == $integrity['total'] ? "✅" : "❌") . "\n";
    echo "   price_supply 있음: {$integrity['has_price']}건 ";
    echo ($integrity['has_price'] == $integrity['total'] ? "✅" : "❌") . "\n";
    echo "   product_data_json 있음: {$integrity['has_json']}건 ";
    echo ($integrity['has_json'] == $integrity['total'] ? "✅" : "❌") . "\n\n";
}

// 4. 상세 JSON 데이터 확인
$json_query = "SELECT no, product_data_json
               FROM shop_temp
               WHERE data_version = 2 AND product_type = 'namecard'
               ORDER BY no DESC
               LIMIT 1";

$json_result = mysqli_query($db, $json_query);
if ($json_row = mysqli_fetch_assoc($json_result)) {
    echo "4. product_data_json 샘플 (ID: {$json_row['no']}):\n";
    $json_data = json_decode($json_row['product_data_json'], true);
    if ($json_data) {
        echo "   product_type: {$json_data['product_type']}\n";
        echo "   data_version: {$json_data['data_version']}\n";
        echo "   spec_type: {$json_data['spec_type']}\n";
        echo "   spec_material: {$json_data['spec_material']}\n";
        echo "   quantity_value: {$json_data['quantity_value']} {$json_data['quantity_unit']}\n";
        echo "   price_supply: " . number_format($json_data['price_supply']) . "원\n";
        echo "   price_vat: " . number_format($json_data['price_vat']) . "원\n";
        echo "   ✅ JSON 파싱 성공\n";
    } else {
        echo "   ❌ JSON 파싱 실패\n";
    }
}

echo "\n=== 테스트 완료 ===\n";
echo "명함 add_to_basket.php를 브라우저에서 실행한 후 이 스크립트를 다시 실행하세요.\n";

mysqli_close($db);
