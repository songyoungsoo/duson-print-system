<?php
/**
 * 전단지 옵션 장바구니 표시 테스트
 */

session_start();
$test_session_id = 'test_inserted_' . date('YmdHis');

include "db.php";
mysqli_set_charset($db, "utf8");

echo "<h1>전단지 옵션 장바구니 표시 테스트</h1>";
echo "<hr>";

// 1. 테스트 데이터 추가 (옵션 포함)
$product_type = 'inserted';
$MY_type = '1';
$PN_type = '1';
$MY_Fsd = '1';
$MY_amount = '1000';
$POtype = '1'; // 단면
$ordertype = 'print';
$mesu = 1; // 1연

// 가격
$price = 100000;
$vat_price = 110000;

// 추가 옵션 (코팅 + 접지)
$additional_options = [
    'coating_enabled' => 1,
    'coating_type' => 'single_gloss',
    'coating_price' => 20000,
    'folding_enabled' => 1,
    'folding_type' => '2fold',
    'folding_price' => 15000,
    'creasing_enabled' => 0,
    'creasing_lines' => 0,
    'creasing_price' => 0
];
$additional_options_json = json_encode($additional_options, JSON_UNESCAPED_UNICODE);
$additional_options_total = 35000; // 20000 + 15000

echo "<h2>1. 장바구니에 테스트 상품 추가</h2>";
echo "<p><strong>추가 옵션:</strong></p>";
echo "<pre>" . json_encode($additional_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// shop_temp에 추가
$regdate = time();
$query = "INSERT INTO shop_temp
          (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype,
           st_price, st_price_vat, additional_options, additional_options_total, mesu, regdate)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $query);
if ($stmt) {
    // 디버깅: 파라미터 값 확인
    echo "<p><strong>디버깅:</strong></p>";
    echo "<pre>";
    echo "additional_options_json: " . $additional_options_json . "\n";
    echo "additional_options_json length: " . strlen($additional_options_json) . "\n";
    echo "additional_options_total: " . $additional_options_total . "\n";
    echo "</pre>";

    // 🔧 FIX: 14개 파라미터 - additional_options는 's' (JSON string), additional_options_total은 'i'
    mysqli_stmt_bind_param($stmt, 'ssssssssiiisii',
        $test_session_id, $product_type, $MY_type, $PN_type, $MY_Fsd, $MY_amount, $POtype, $ordertype,
        $price, $vat_price, $additional_options_json, $additional_options_total, $mesu, $regdate
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ 장바구니에 추가 완료 (세션: " . htmlspecialchars($test_session_id) . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ 추가 실패: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";
        exit;
    }
    mysqli_stmt_close($stmt);
}

echo "<hr>";

// 2. 데이터 조회 (cart.php와 동일한 로직)
echo "<h2>2. 장바구니 데이터 조회</h2>";

$query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC LIMIT 1";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 's', $test_session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

echo "<h3>DB 저장 데이터:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>필드</th><th>값</th></tr>";
echo "<tr><td>additional_options (JSON)</td><td><pre>" . htmlspecialchars($row['additional_options']) . "</pre></td></tr>";
echo "<tr><td>additional_options_total</td><td>" . number_format($row['additional_options_total']) . "원</td></tr>";
echo "<tr><td>coating_enabled (DB 필드)</td><td>" . htmlspecialchars($row['coating_enabled'] ?? 'NULL') . "</td></tr>";
echo "<tr><td>folding_enabled (DB 필드)</td><td>" . htmlspecialchars($row['folding_enabled'] ?? 'NULL') . "</td></tr>";
echo "</table>";

echo "<hr>";

// 3. JSON 파싱 테스트 (cart.php Line 118-133)
echo "<h2>3. JSON 파싱 테스트 (cart.php 로직)</h2>";

if (!empty($row['additional_options'])) {
    $parsed_options = json_decode($row['additional_options'], true);
    if ($parsed_options && is_array($parsed_options)) {
        echo "<p style='color: green;'>✅ JSON 파싱 성공</p>";
        echo "<pre>" . json_encode($parsed_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

        // 개별 필드로 변환
        $row['coating_enabled'] = $parsed_options['coating_enabled'] ?? 0;
        $row['coating_type'] = $parsed_options['coating_type'] ?? '';
        $row['coating_price'] = $parsed_options['coating_price'] ?? 0;
        $row['folding_enabled'] = $parsed_options['folding_enabled'] ?? 0;
        $row['folding_type'] = $parsed_options['folding_type'] ?? '';
        $row['folding_price'] = $parsed_options['folding_price'] ?? 0;
        $row['creasing_enabled'] = $parsed_options['creasing_enabled'] ?? 0;
        $row['creasing_lines'] = $parsed_options['creasing_lines'] ?? 0;
        $row['creasing_price'] = $parsed_options['creasing_price'] ?? 0;

        echo "<h3>변환 후 개별 필드:</h3>";
        echo "<ul>";
        echo "<li>coating_enabled: " . $row['coating_enabled'] . "</li>";
        echo "<li>coating_type: " . htmlspecialchars($row['coating_type']) . "</li>";
        echo "<li>coating_price: " . number_format($row['coating_price']) . "원</li>";
        echo "<li>folding_enabled: " . $row['folding_enabled'] . "</li>";
        echo "<li>folding_type: " . htmlspecialchars($row['folding_type']) . "</li>";
        echo "<li>folding_price: " . number_format($row['folding_price']) . "원</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ JSON 파싱 실패</p>";
    }
} else {
    echo "<p style='color: red;'>❌ additional_options 필드가 비어있음</p>";
}

echo "<hr>";

// 4. AdditionalOptionsDisplay 클래스 테스트
echo "<h2>4. AdditionalOptionsDisplay 클래스 테스트</h2>";

require_once __DIR__ . '/includes/AdditionalOptionsDisplay.php';
$optionsDisplay = new AdditionalOptionsDisplay($db);

$options_details = $optionsDisplay->getOrderDetails($row);

if (!empty($options_details['options'])) {
    echo "<p style='color: green;'>✅ 옵션 표시 성공</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>분류</th><th>옵션명</th><th>가격</th></tr>";
    foreach ($options_details['options'] as $option) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($option['category']) . "</td>";
        echo "<td>" . htmlspecialchars($option['name']) . "</td>";
        echo "<td>" . htmlspecialchars($option['formatted_price']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>총 옵션 가격:</strong> " . number_format($options_details['total_price']) . "원</p>";
} else {
    echo "<p style='color: red;'>❌ 옵션이 표시되지 않음</p>";
    echo "<p>디버그 정보:</p>";
    echo "<pre>" . print_r($row, true) . "</pre>";
}

echo "<hr>";
echo "<h2>5. 실제 장바구니 페이지 확인</h2>";
echo "<p><a href='/mlangprintauto/shop/cart.php?session_id=" . urlencode($test_session_id) . "' target='_blank' style='font-size: 18px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; display: inline-block; border-radius: 5px;'>장바구니 보기 →</a></p>";

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
}
table {
    border-collapse: collapse;
    margin: 10px 0;
}
th {
    background: #4CAF50;
    color: white;
    padding: 10px;
}
td {
    padding: 8px;
}
pre {
    background: #f5f5f5;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow-x: auto;
}
</style>
