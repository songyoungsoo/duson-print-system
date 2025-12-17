<?php

// Composer의 오토로더를 사용한다고 가정합니다.
// 실제 Laravel과 같은 프레임워크 환경에서는 이 과정이 자동으로 처리됩니다.
require_once __DIR__ . '/app/Pricing/PriceResult.php';
require_once __DIR__ . '/app/Pricing/CalculatorInterface.php';
require_once __DIR__ . '/app/Pricing/FlyerCalculator.php';
require_once __DIR__ . '/app/Pricing/BusinessCardCalculator.php';
require_once __DIR__ . '/app/Pricing/CalculatorFactory.php';

use App\Pricing\CalculatorFactory;
use App\Pricing\PriceResult;

/**
 * =================================================================================
 * 실제 애플리케이션에서의 사용 예시 (예: ProductController.php의 한 메소드)
 * =================================================================================
 *
 * 컨트롤러는 어떤 계산기가 실행되는지 알 필요가 없습니다.
 * 단지 팩토리에 품목 키와 옵션을 전달하기만 하면 됩니다.
 */

// 1. 팩토리 인스턴스 생성 (실제로는 서비스 컨테이너를 통해 한번만 생성)
$factory = new CalculatorFactory();

echo "========================================\n";
echo "--- 전단지 계산 예시 (조합 조회형) ---
";
echo "========================================\n";

$flyerOptions = [
    'size' => 'a4',
    'paper' => 'art150',
    'print_side' => 'single_sided',
    'ream' => 1.0,
];

try {
    $flyerResult = $factory->calculate('flyer', $flyerOptions);
    print_r($flyerResult);
} catch (InvalidArgumentException $e) {
    echo "오류: " . $e->getMessage() . "\n";
}

echo "\n\n";

echo "========================================\n";
echo "--- 명함 계산 예시 (단순 가산형) ---
";
echo "========================================\n";
$cardOptions = [
    'paper_type' => 'premium',
    'quantity' => 200,
    'corner_rounding' => true,
    'gold_foil' => true,
];

try {
    $cardResult = $factory->calculate('business_card', $cardOptions);
    print_r($cardResult);
} catch (InvalidArgumentException $e) {
    echo "오류: " . $e->getMessage() . "\n";
}


/**
 * =================================================================================
 * 단위 테스트(Unit Test) 작성 예시 (예: tests/Unit/Pricing/CalculatorTest.php)
 * =================================================================================
 *
 * `phpunit`과 같은 테스트 프레임워크를 사용하여 각 계산기의 정확성을 자동으로 검증합니다.
 * 이를 통해 UI나 DB 연결 없이도 계산 로직의 신뢰도를 100% 확보할 수 있습니다.
 */

echo "\n\n========================================\n";
echo "--- 단위 테스트 실행 예시 ---
";
echo "========================================\n";

function run_test(string $name, callable $testFunction)
{
    try {
        $testFunction();
        echo "[SUCCESS] 테스트 통과: " . $name . "\n";
    } catch (Exception $e) {
        echo "[FAILURE] 테스트 실패: " . $name . " - " . $e->getMessage() . "\n";
    }
}

// 전단지 계산기 테스트
run_test("전단지 A4, 단면, 1연 가격", function () {
    $factory = new CalculatorFactory();
    $options = [
        'size' => 'a4', 'paper' => 'art150', 'print_side' => 'single_sided', 'ream' => 1.0,
    ];
    $result = $factory->calculate('flyer', $options);
    
    $expected = 80000;
    if ($result->supplyPrice !== $expected) {
        throw new Exception("예상 공급가: {$expected}, 실제: {$result->supplyPrice}");
    }
});

// 명함 계산기 테스트
run_test("명함 고급지 200매 + 귀도리 + 금박 가격", function () {
    $factory = new CalculatorFactory();
     $options = [
        'paper_type' => 'premium', 'quantity' => 200, 'corner_rounding' => true, 'gold_foil' => true,
    ];
    $result = $factory->calculate('business_card', $options);
    
    // 예상 가격: 기본료(18,000) + 귀도리(3,000) + 금박(10,000) = 31,000
    $expected = 31000;
    if ($result->supplyPrice !== $expected) {
        throw new Exception("예상 공급가: {$expected}, 실제: {$result->supplyPrice}");
    }
});

run_test("명함 일반지 500매 가격 (옵션 없음)", function () {
    $factory = new CalculatorFactory();
    $options = [
        'paper_type' => 'standard', 'quantity' => 500,
    ];
    $result = $factory->calculate('business_card', $options);
    
    $expected = 15000;
    if ($result->supplyPrice !== $expected) {
        throw new Exception("예상 공급가: {$expected}, 실제: {$result->supplyPrice}");
    }
});

?>
