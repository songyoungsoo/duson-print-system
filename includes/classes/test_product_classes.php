<?php
/**
 * Product Classes 테스트 스크립트
 *
 * Phase 1에서 작성한 제품 클래스들이 정상 작동하는지 확인합니다.
 *
 * 실행: php test_product_classes.php
 */

// 데이터베이스 연결
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/ProductFactory.php';

echo "=== Product Classes Test ===\n\n";

// 테스트 카운터
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

/**
 * 테스트 결과 출력
 */
function test($name, $result) {
    global $total_tests, $passed_tests, $failed_tests;
    $total_tests++;

    if ($result) {
        $passed_tests++;
        echo "✅ PASS: $name\n";
    } else {
        $failed_tests++;
        echo "❌ FAIL: $name\n";
    }
}

// ========================================
// 1. ProductFactory 테스트
// ========================================
echo "1. ProductFactory 기본 테스트\n";
echo "------------------------------\n";

// 1-1. 지원 제품 목록 확인
$supported = ProductFactory::getSupportedProducts();
test("지원 제품 목록 반환 (9개)", count($supported) === 9);

// 1-2. 각 제품 인스턴스 생성 테스트
foreach ($supported as $product_type) {
    try {
        $product = ProductFactory::create($db, $product_type);
        $is_valid = ($product instanceof BaseProduct);
        test("ProductFactory::create('{$product_type}') 인스턴스 생성", $is_valid);

        // 제품명 확인
        $product_name = ProductFactory::getProductName($product_type);
        test("  → 제품명: {$product_name}", !empty($product_name));

        // 카테고리 확인
        $category = ProductFactory::getProductCategory($product_type);
        test("  → 카테고리: {$category}", !empty($category));

    } catch (Exception $e) {
        test("ProductFactory::create('{$product_type}') 실패", false);
        echo "     Error: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ========================================
// 2. CategoryBasedProduct 테스트
// ========================================
echo "2. CategoryBasedProduct 테스트 (명함)\n";
echo "------------------------------\n";

try {
    $namecard = ProductFactory::create($db, 'namecard');

    // 2-1. POST 데이터 설정
    $test_data = [
        'MY_type' => 'test_style',
        'Section' => 'test_section',
        'POtype' => '1',
        'MY_amount' => '100',
        'ordertype' => 'self',
        'premium_options' => '{}',
        'premium_options_total' => 0,
        'calculated_price' => 50000,
        'calculated_vat_price' => 55000
    ];

    $namecard->setFromPost($test_data);
    test("setFromPost() 성공", true);

    // 2-2. 유효성 검증
    $validation = $namecard->validate();
    test("validate() 호출 성공", isset($validation['valid']));

    // 2-3. 장바구니 데이터 생성
    $cart_data = $namecard->getCartData();
    test("getCartData() 반환 (배열)", is_array($cart_data));
    test("  → session_id 있음", isset($cart_data['session_id']));
    test("  → product_type='namecard'", $cart_data['product_type'] === 'namecard');

} catch (Exception $e) {
    test("CategoryBasedProduct 테스트 실패", false);
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ========================================
// 3. FlierProduct 테스트
// ========================================
echo "3. FlierProduct 테스트 (전단지)\n";
echo "------------------------------\n";

try {
    $flier = ProductFactory::create($db, 'inserted');

    $test_data = [
        'MY_type' => 'test_paper',
        'Section' => 'A4',
        'MY_amountRight' => '500매',
        'POtype' => '1',
        'MY_amount' => '10',
        'ordertype' => 'self',
        'calculated_price' => 100000,
        'calculated_vat_price' => 110000
    ];

    $flier->setFromPost($test_data);
    test("setFromPost() 성공 (mesu 자동 추출)", true);

    // mesu 확인
    $mesu = $flier->getMesu();
    test("mesu 추출: {$mesu}", $mesu === 500);

} catch (Exception $e) {
    test("FlierProduct 테스트 실패", false);
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ========================================
// 4. StickerProduct 테스트
// ========================================
echo "4. StickerProduct 테스트 (스티커)\n";
echo "------------------------------\n";

try {
    $sticker = ProductFactory::create($db, 'sticker');

    $test_data = [
        'jong' => '일반스티커',
        'garo' => '100',
        'sero' => '100',
        'mesu' => '1000',
        'price' => 80000,
        'st_price_vat' => 88000
    ];

    $sticker->setFromPost($test_data);
    test("setFromPost() 성공", true);

    $cart_data = $sticker->getCartData();
    test("getCartData() 반환", is_array($cart_data));
    test("  → jong='일반스티커'", $cart_data['jong'] === '일반스티커');
    test("  → garo=100, sero=100", $cart_data['garo'] === '100' && $cart_data['sero'] === '100');

} catch (Exception $e) {
    test("StickerProduct 테스트 실패", false);
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ========================================
// 5. NCRProduct 테스트
// ========================================
echo "5. NCRProduct 테스트 (NCR양식)\n";
echo "------------------------------\n";

try {
    $ncr = ProductFactory::create($db, 'ncrflambeau');

    $test_data = [
        'MY_type' => 'test_type',
        'PN_type' => 'test_spec',
        'MY_amount' => '100',
        'calculated_price' => 60000,
        'calculated_vat_price' => 66000
    ];

    $ncr->setFromPost($test_data);
    test("setFromPost() 성공", true);

    $validation = $ncr->validate();
    test("validate() 호출", isset($validation['valid']));

} catch (Exception $e) {
    test("NCRProduct 테스트 실패", false);
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ========================================
// 최종 결과
// ========================================
echo "=================================\n";
echo "테스트 결과\n";
echo "=================================\n";
echo "총 테스트: $total_tests\n";
echo "✅ 성공: $passed_tests\n";
echo "❌ 실패: $failed_tests\n";
echo "성공률: " . round($passed_tests / $total_tests * 100, 2) . "%\n";

if ($failed_tests === 0) {
    echo "\n🎉 모든 테스트 통과!\n";
} else {
    echo "\n⚠️  일부 테스트 실패. 코드를 확인하세요.\n";
}
