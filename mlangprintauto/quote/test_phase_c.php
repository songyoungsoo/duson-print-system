<?php
/**
 * Phase C 테스트 스크립트
 * quote_source 및 is_manual_entry 자동 설정 테스트
 */

session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';

echo "=== Phase C 테스트 시작 ===\n\n";

$manager = new QuoteManager($db);

// ===== 테스트 1: 고객 견적 요청 (quote_source='customer') =====
echo "테스트 1: 고객 견적 요청\n";
echo "- 예상: quote_source='customer', is_manual_entry=0\n";

// 고객 세션 시뮬레이션 (admin_logged_in 없음)
unset($_SESSION['admin_logged_in']);

// shop_temp 데이터 추가
$insertResult = mysqli_query($db, "INSERT INTO shop_temp (
    session_id, product_type, st_price, st_price_vat,
    customer_name, customer_phone,
    MY_amount, unit, regdate
) VALUES (
    'test_customer_phase_c', 'namecard', 10000, 1000,
    '테스트고객A', '010-1111-1111',
    100, '박스', UNIX_TIMESTAMP()
)");
if (!$insertResult) {
    echo "❌ shop_temp INSERT 실패: " . mysqli_error($db) . "\n";
}

$customerData = [
    'customer_name' => '테스트고객A',
    'customer_email' => 'customer@test.com',
    'customer_phone' => '010-1111-1111',
    'customer_company' => '테스트회사A',
    'supply_total' => 10000,
    'vat_total' => 1000,
    'grand_total' => 11000,
    'created_by' => 0
];

try {
    $result1 = $manager->createFromCart('test_customer_phase_c', $customerData);

    if (empty($result1) || !isset($result1['success']) || $result1['success'] === false) {
        echo "❌ 견적서 생성 실패: " . ($result1['message'] ?? 'Unknown error') . "\n\n";
    } else {
        echo "✅ 견적서 생성 성공: " . $result1['quote_no'] . " (ID: " . $result1['quote_id'] . ")\n";

        // DB 확인
        $quote1 = $manager->getById($result1['quote_id']);
        $items1 = $manager->getQuoteItems($result1['quote_id']);

        echo "  - quote_source: " . ($quote1['quote_source'] ?? 'NULL') . "\n";
        echo "  - 품목 수: " . count($items1) . "\n";
        if (!empty($items1)) {
            echo "  - 첫 품목 is_manual_entry: " . ($items1[0]['is_manual_entry'] ?? 'NULL') . "\n";
        }

        if ($quote1['quote_source'] === 'customer' &&
            !empty($items1) && $items1[0]['is_manual_entry'] == 0) {
            echo "✅ 테스트 1 통과!\n\n";
        } else {
            echo "❌ 테스트 1 실패!\n\n";
        }
    }
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n\n";
}

// ===== 테스트 2: 관리자 자동계산 견적 (quote_source='admin_auto') =====
echo "테스트 2: 관리자 자동계산 견적\n";
echo "- 예상: quote_source='admin_auto', is_manual_entry=0\n";

// quotation_temp 데이터 추가
$insertResult2 = mysqli_query($db, "INSERT INTO quotation_temp (
    session_id, product_type, st_price, st_price_vat,
    MY_amount, unit, regdate
) VALUES (
    'test_admin_auto_phase_c', 'inserted', 20000, 2000,
    1000, '매', UNIX_TIMESTAMP()
)");
if (!$insertResult2) {
    echo "❌ quotation_temp INSERT 실패: " . mysqli_error($db) . "\n";
}

// 관리자 세션 시뮬레이션
$_SESSION['admin_logged_in'] = true;

$adminAutoData = [
    'customer_name' => '테스트고객B',
    'customer_email' => 'customerb@test.com',
    'customer_phone' => '010-2222-2222',
    'customer_company' => '테스트회사B',
    'supply_total' => 20000,
    'vat_total' => 2000,
    'grand_total' => 22000,
    'created_by' => 1
];

try {
    $result2 = $manager->createFromCart('test_admin_auto_phase_c', $adminAutoData);

    if (empty($result2) || !isset($result2['success']) || $result2['success'] === false) {
        echo "❌ 견적서 생성 실패: " . ($result2['message'] ?? 'Unknown error') . "\n\n";
    } else {
        echo "✅ 견적서 생성 성공: " . $result2['quote_no'] . " (ID: " . $result2['quote_id'] . ")\n";

        // DB 확인
        $quote2 = $manager->getById($result2['quote_id']);
        $items2 = $manager->getQuoteItems($result2['quote_id']);

        echo "  - quote_source: " . ($quote2['quote_source'] ?? 'NULL') . "\n";
        echo "  - 품목 수: " . count($items2) . "\n";
        if (!empty($items2)) {
            echo "  - 첫 품목 is_manual_entry: " . ($items2[0]['is_manual_entry'] ?? 'NULL') . "\n";
        }

        if ($quote2['quote_source'] === 'admin_auto' &&
            !empty($items2) && $items2[0]['is_manual_entry'] == 0) {
            echo "✅ 테스트 2 통과!\n\n";
        } else {
            echo "❌ 테스트 2 실패!\n\n";
        }
    }
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n\n";
}

// ===== 테스트 3: 관리자 수동입력 견적 (quote_source='admin_manual') =====
echo "테스트 3: 관리자 수동입력 견적\n";
echo "- 예상: quote_source='admin_manual', is_manual_entry=1\n";

// 관리자 세션 유지
$_SESSION['admin_logged_in'] = true;

$adminManualData = [
    'customer_name' => '테스트고객C',
    'customer_email' => 'customerc@test.com',
    'customer_phone' => '010-3333-3333',
    'customer_company' => '테스트회사C',
    'supply_total' => 30000,
    'vat_total' => 3000,
    'grand_total' => 33000,
    'created_by' => 1,
    'items' => [
        [
            'product_type' => 'custom',
            'product_name' => '수동입력제품',
            'specification' => '직접입력한 사양',
            'quantity' => 100,
            'unit' => '개',
            'supply_price' => 30000
        ]
    ]
];

try {
    $result3 = $manager->createEmpty($adminManualData);
    echo "✅ 견적서 생성 성공: " . $result3['quote_no'] . " (ID: " . $result3['quote_id'] . ")\n";

    // DB 확인
    $quote3 = $manager->getById($result3['quote_id']);
    $items3 = $manager->getQuoteItems($result3['quote_id']);

    echo "  - quote_source: " . ($quote3['quote_source'] ?? 'NULL') . "\n";
    echo "  - 품목 수: " . count($items3) . "\n";
    if (!empty($items3)) {
        echo "  - 첫 품목 is_manual_entry: " . ($items3[0]['is_manual_entry'] ?? 'NULL') . "\n";
    }

    if ($quote3['quote_source'] === 'admin_manual' &&
        !empty($items3) && $items3[0]['is_manual_entry'] == 1) {
        echo "✅ 테스트 3 통과!\n\n";
    } else {
        echo "❌ 테스트 3 실패!\n\n";
    }
} catch (Exception $e) {
    echo "❌ 오류: " . $e->getMessage() . "\n\n";
}

// ===== 최종 결과 요약 =====
echo "=== 테스트 완료 ===\n";
echo "생성된 견적서:\n";
echo "1. " . ($result1['quote_no'] ?? 'N/A') . " - customer\n";
echo "2. " . ($result2['quote_no'] ?? 'N/A') . " - admin_auto\n";
echo "3. " . ($result3['quote_no'] ?? 'N/A') . " - admin_manual\n";

// 테스트 데이터 정리
echo "\n테스트 데이터를 정리하시겠습니까? (수동으로 실행: DELETE FROM quotes WHERE id IN (...))\n";
?>
