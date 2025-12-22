<?php
/**
 * 장바구니 저장 테스트 스크립트
 * 프리미엄 옵션 데이터베이스 저장 테스트
 */
session_start();
include "../../db.php";
include "../../includes/functions.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>🧪 장바구니 저장 테스트</h2>";

// 1. shop_temp 테이블 구조 확인
echo "<h3>1. shop_temp 테이블 구조</h3>";
$describe_query = "DESCRIBE shop_temp";
$describe_result = mysqli_query($db, $describe_query);

if ($describe_result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($describe_result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='color: red;'>테이블 구조 조회 실패: " . mysqli_error($db) . "</div>";
}

// 2. 테스트 데이터로 INSERT 시뮬레이션
echo "<h3>2. INSERT 쿼리 테스트</h3>";

$session_id = session_id();
$product_type = 'namecard';
$MY_type = '1';
$Section = '1';
$POtype = '1';
$MY_amount = '500';
$ordertype = 'print';
$price = 50000;
$vat_price = 55000;

$premium_options = [
    'foil_enabled' => 1,
    'foil_type' => 'gold_matte',
    'foil_price' => 30000,
    'numbering_enabled' => 0,
    'numbering_type' => '',
    'numbering_price' => 0,
    'perforation_enabled' => 0,
    'perforation_type' => '',
    'perforation_price' => 0,
    'rounding_enabled' => 0,
    'rounding_price' => 0,
    'creasing_enabled' => 0,
    'creasing_type' => '',
    'creasing_price' => 0,
    'premium_options_total' => 30000
];

$premium_options_json = json_encode($premium_options, JSON_UNESCAPED_UNICODE);
$premium_total = intval($premium_options['premium_options_total']);

echo "<strong>테스트 데이터:</strong><br>";
echo "Session ID: $session_id<br>";
echo "Product Type: $product_type<br>";
echo "Premium Options JSON: $premium_options_json<br>";
echo "Premium Total: $premium_total<br><br>";

$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype, st_price, st_price_vat, premium_options, premium_options_total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

echo "<strong>INSERT 쿼리:</strong><br>";
echo "<code>$insert_query</code><br><br>";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    echo "<div style='color: green;'>✅ PreparedStatement 생성 성공</div>";

    $bind_result = mysqli_stmt_bind_param($stmt, "sssssssiisi",
        $session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype, $price, $vat_price, $premium_options_json, $premium_total);

    if ($bind_result) {
        echo "<div style='color: green;'>✅ 파라미터 바인딩 성공</div>";

        if (mysqli_stmt_execute($stmt)) {
            $basket_id = mysqli_insert_id($db);
            echo "<div style='color: green;'>✅ INSERT 실행 성공! basket_id: $basket_id</div>";

            // 저장된 데이터 확인
            $select_query = "SELECT * FROM shop_temp WHERE no = ?";
            $select_stmt = mysqli_prepare($db, $select_query);
            mysqli_stmt_bind_param($select_stmt, "i", $basket_id);
            mysqli_stmt_execute($select_stmt);
            $result = mysqli_stmt_get_result($select_stmt);

            if ($saved_data = mysqli_fetch_assoc($result)) {
                echo "<h4>저장된 데이터:</h4>";
                echo "<table border='1' cellpadding='5'>";
                foreach ($saved_data as $key => $value) {
                    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
                }
                echo "</table>";
            }
            mysqli_stmt_close($select_stmt);

        } else {
            echo "<div style='color: red;'>❌ INSERT 실행 실패: " . mysqli_stmt_error($stmt) . "</div>";
        }
    } else {
        echo "<div style='color: red;'>❌ 파라미터 바인딩 실패</div>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<div style='color: red;'>❌ PreparedStatement 생성 실패: " . mysqli_error($db) . "</div>";
}

// 3. 현재 세션의 장바구니 데이터 확인
echo "<h3>3. 현재 세션 장바구니 데이터</h3>";
$cart_query = "SELECT * FROM shop_temp WHERE session_id = ?";
$cart_stmt = mysqli_prepare($db, $cart_query);

if ($cart_stmt) {
    mysqli_stmt_bind_param($cart_stmt, 's', $session_id);
    mysqli_stmt_execute($cart_stmt);
    $cart_result = mysqli_stmt_get_result($cart_stmt);

    if (mysqli_num_rows($cart_result) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>No</th><th>Product Type</th><th>Premium Options</th><th>Premium Total</th><th>Created</th></tr>";

        while ($row = mysqli_fetch_assoc($cart_result)) {
            echo "<tr>";
            echo "<td>{$row['no']}</td>";
            echo "<td>{$row['product_type']}</td>";
            echo "<td style='max-width: 300px; word-break: break-all;'>{$row['premium_options']}</td>";
            echo "<td>" . number_format($row['premium_options_total']) . "원</td>";
            echo "<td>" . (isset($row['created_at']) ? $row['created_at'] : 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='color: orange;'>현재 세션에 장바구니 데이터가 없습니다.</div>";
    }
    mysqli_stmt_close($cart_stmt);
}

mysqli_close($db);
?>

<style>
table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
h2, h3 { color: #333; }
code { background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style>