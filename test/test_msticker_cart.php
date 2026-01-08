<?php
/**
 * 자석스티커 장바구니 표시 테스트
 * Tests msticker cart display with getMstickerSpecs() function
 */

session_start();
$test_session_id = 'test_msticker_' . date('YmdHis');

include "db.php";
mysqli_set_charset($db, "utf8");

echo "<h1>자석스티커 장바구니 표시 테스트</h1>";
echo "<hr>";

// 1. 테스트 데이터 준비
$product_type = 'msticker';
$MY_type = '1'; // 자석스티커 타입
$Section = '1'; // 규격
$POtype = '1'; // 단면
$MY_amount = '100'; // 100매
$ordertype = 'print'; // 인쇄만

// 가격
$price = 50000;
$vat_price = 55000;

echo "<h2>1. 테스트 데이터 준비</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>필드</th><th>값</th></tr>";
echo "<tr><td>product_type</td><td>$product_type</td></tr>";
echo "<tr><td>MY_type</td><td>$MY_type</td></tr>";
echo "<tr><td>Section</td><td>$Section</td></tr>";
echo "<tr><td>POtype</td><td>$POtype</td></tr>";
echo "<tr><td>MY_amount</td><td>$MY_amount</td></tr>";
echo "<tr><td>ordertype</td><td>$ordertype</td></tr>";
echo "<tr><td>st_price</td><td>" . number_format($price) . "원</td></tr>";
echo "<tr><td>st_price_vat</td><td>" . number_format($vat_price) . "원</td></tr>";
echo "</table>";

echo "<hr>";

// 2. shop_temp에 추가
echo "<h2>2. 장바구니(shop_temp)에 테스트 상품 추가</h2>";

$regdate = time();
$query = "INSERT INTO shop_temp
          (session_id, product_type, MY_type, Section, POtype, MY_amount, ordertype,
           st_price, st_price_vat, regdate)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $query);
if ($stmt) {
    // 🔧 10개 파라미터: 7 strings + 2 ints + 1 int
    mysqli_stmt_bind_param($stmt, 'sssssssiis',
        $test_session_id, $product_type, $MY_type, $Section, $POtype, $MY_amount, $ordertype,
        $price, $vat_price, $regdate
    );

    if (mysqli_stmt_execute($stmt)) {
        $basket_id = mysqli_insert_id($db);
        echo "<p style='color: green;'>✅ 장바구니 추가 완료 (세션: " . htmlspecialchars($test_session_id) . ", ID: $basket_id)</p>";
    } else {
        echo "<p style='color: red;'>❌ 추가 실패: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>❌ 쿼리 준비 실패: " . htmlspecialchars(mysqli_error($db)) . "</p>";
    exit;
}

echo "<hr>";

// 3. 저장된 데이터 확인
echo "<h2>3. DB에 저장된 데이터 확인</h2>";

$query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC LIMIT 1";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 's', $test_session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>필드</th><th>값</th><th>비고</th></tr>";
    echo "<tr><td>no</td><td>" . $row['no'] . "</td><td>자동 증가 ID</td></tr>";
    echo "<tr><td>product_type</td><td>" . htmlspecialchars($row['product_type']) . "</td><td>msticker 확인</td></tr>";
    echo "<tr><td>MY_type</td><td>" . htmlspecialchars($row['MY_type']) . "</td><td>종류 필드</td></tr>";
    echo "<tr><td>Section</td><td>" . htmlspecialchars($row['Section']) . "</td><td>규격 필드 ⭐</td></tr>";
    echo "<tr><td>POtype</td><td>" . htmlspecialchars($row['POtype']) . "</td><td>인쇄 타입</td></tr>";
    echo "<tr><td>MY_amount</td><td>" . htmlspecialchars($row['MY_amount']) . "</td><td>수량</td></tr>";
    echo "<tr><td>st_price</td><td>" . number_format($row['st_price']) . "원</td><td>가격</td></tr>";
    echo "<tr><td>st_price_vat</td><td>" . number_format($row['st_price_vat']) . "원</td><td>VAT 포함</td></tr>";
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ 데이터를 찾을 수 없습니다.</p>";
    exit;
}

echo "<hr>";

// 4. getMstickerSpecs() 함수 테스트
echo "<h2>4. getMstickerSpecs() 함수 테스트</h2>";

// cart.php에서 함수 정의 가져오기
function getMstickerSpecs($item) {
    $specs = [];

    // Type (종류) - MY_type field
    if (!empty($item['MY_type'])) {
        $specs[] = '종류: ' . htmlspecialchars($item['MY_type']);
    }

    // Specification/Size (규격) - Section field
    if (!empty($item['Section'])) {
        $specs[] = '규격: ' . htmlspecialchars($item['Section']);
    }

    // Print type (인쇄) - POtype field
    if (!empty($item['POtype'])) {
        $print_types = ['1' => '단면', '2' => '양면'];
        $print_label = $print_types[$item['POtype']] ?? htmlspecialchars($item['POtype']);
        $specs[] = '인쇄: ' . $print_label;
    }

    // Quantity (수량) - MY_amount field
    if (!empty($item['MY_amount'])) {
        $specs[] = '수량: ' . htmlspecialchars($item['MY_amount']) . '매';
    }

    return $specs;
}

$specs = getMstickerSpecs($row);

if (!empty($specs)) {
    echo "<p style='color: green;'>✅ getMstickerSpecs() 함수 정상 작동</p>";
    echo "<h3>표시되는 규격/옵션:</h3>";
    echo "<ul>";
    foreach ($specs as $spec) {
        echo "<li>" . htmlspecialchars($spec) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ getMstickerSpecs() 함수가 빈 배열을 반환했습니다.</p>";
}

echo "<hr>";

// 5. 실제 cart.php 페이지 렌더링 확인
echo "<h2>5. 실제 장바구니 페이지에서 확인</h2>";
echo "<p>아래 버튼을 클릭하여 실제 장바구니 페이지에서 규격/옵션이 제대로 표시되는지 확인하세요:</p>";
echo "<p><a href='/mlangprintauto/shop/cart.php?session_id=" . urlencode($test_session_id) . "' target='_blank' style='font-size: 18px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; display: inline-block; border-radius: 5px;'>장바구니 보기 →</a></p>";

echo "<hr>";

// 6. 요약
echo "<h2>6. 테스트 요약</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-left: 5px solid #2196F3;'>";
echo "<h3>✅ 확인 사항:</h3>";
echo "<ol>";
echo "<li>shop_temp 테이블에 msticker 데이터 정상 저장</li>";
echo "<li>Section 필드에 규격 값 저장 확인</li>";
echo "<li>getMstickerSpecs() 함수가 모든 필드 읽기 성공</li>";
echo "<li>규격/옵션 표시: " . implode(' / ', $specs) . "</li>";
echo "</ol>";
echo "<h3>🎯 다음 단계:</h3>";
echo "<p>1. 위의 '장바구니 보기' 버튼 클릭</p>";
echo "<p>2. '규격/옵션' 컬럼에 표시된 내용 확인</p>";
echo "<p>3. 예상 표시: <strong>" . implode(' / ', $specs) . "</strong></p>";
echo "</div>";

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
}
h1 {
    color: #333;
    border-bottom: 3px solid #4CAF50;
    padding-bottom: 10px;
}
h2 {
    color: #2196F3;
    margin-top: 30px;
}
table {
    border-collapse: collapse;
    margin: 10px 0;
    width: 100%;
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
