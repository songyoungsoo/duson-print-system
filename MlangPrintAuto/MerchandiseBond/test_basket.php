<?php
// 상품권 장바구니 테스트
session_start();
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

echo "<h2>상품권 장바구니 테스트</h2>";

if (!$connect) {
    echo "<p style='color: red;'>데이터베이스 연결 실패</p>";
    exit;
}

mysqli_set_charset($connect, "utf8");

echo "<p>데이터베이스 연결 성공</p>";
echo "<p>세션 ID: " . $session_id . "</p>";

// 테스트 데이터
$test_data = [
    'MY_type' => '1',
    'PN_type' => '2', 
    'MY_amount' => '1000',
    'POtype' => '1',
    'ordertype' => 'total',
    'price' => 50000,
    'vat_price' => 55000,
    'MY_comment' => '테스트 주문'
];

echo "<h3>테스트 데이터:</h3>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

// 테이블 생성 시도
$create_table_query = "CREATE TABLE IF NOT EXISTS shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) DEFAULT 'merchandisebond',
    MY_type VARCHAR(50),
    PN_type VARCHAR(50),
    MY_amount VARCHAR(50),
    POtype VARCHAR(50),
    ordertype VARCHAR(50),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    MY_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($connect, $create_table_query)) {
    echo "<p style='color: green;'>테이블 생성/확인 성공</p>";
} else {
    echo "<p style='color: red;'>테이블 생성 실패: " . mysqli_error($connect) . "</p>";
}

// 장바구니에 추가 시도
$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_amount, POtype, ordertype, st_price, st_price_vat, MY_comment) VALUES (?, 'merchandisebond', ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($connect, $insert_query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ssssssiis', 
        $session_id, 
        $test_data['MY_type'], 
        $test_data['PN_type'], 
        $test_data['MY_amount'], 
        $test_data['POtype'], 
        $test_data['ordertype'], 
        $test_data['price'], 
        $test_data['vat_price'], 
        $test_data['MY_comment']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>장바구니 추가 성공!</p>";
        $insert_id = mysqli_insert_id($connect);
        echo "<p>추가된 레코드 ID: " . $insert_id . "</p>";
    } else {
        echo "<p style='color: red;'>장바구니 추가 실패: " . mysqli_stmt_error($stmt) . "</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>쿼리 준비 실패: " . mysqli_error($connect) . "</p>";
}

// 장바구니 내용 확인
echo "<h3>현재 장바구니 내용:</h3>";
$select_query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY created_at DESC LIMIT 5";
$select_stmt = mysqli_prepare($connect, $select_query);

if ($select_stmt) {
    mysqli_stmt_bind_param($select_stmt, 's', $session_id);
    mysqli_stmt_execute($select_stmt);
    $result = mysqli_stmt_get_result($select_stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>제품타입</th><th>MY_type</th><th>PN_type</th><th>수량</th><th>POtype</th><th>주문방법</th><th>가격</th><th>VAT포함</th><th>등록시간</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['no'] . "</td>";
            echo "<td>" . $row['product_type'] . "</td>";
            echo "<td>" . $row['MY_type'] . "</td>";
            echo "<td>" . $row['PN_type'] . "</td>";
            echo "<td>" . $row['MY_amount'] . "</td>";
            echo "<td>" . $row['POtype'] . "</td>";
            echo "<td>" . $row['ordertype'] . "</td>";
            echo "<td>" . number_format($row['st_price']) . "원</td>";
            echo "<td>" . number_format($row['st_price_vat']) . "원</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>장바구니가 비어있습니다.</p>";
    }
    
    mysqli_stmt_close($select_stmt);
}

mysqli_close($connect);
?>

<h3>실제 AJAX 테스트</h3>
<form id="testForm">
    <p>MY_type: <input type="text" name="MY_type" value="1"></p>
    <p>PN_type: <input type="text" name="PN_type" value="2"></p>
    <p>MY_amount: <input type="text" name="MY_amount" value="1000"></p>
    <p>POtype: <input type="text" name="POtype" value="1"></p>
    <p>ordertype: <input type="text" name="ordertype" value="total"></p>
    <p>price: <input type="text" name="price" value="50000"></p>
    <p>vat_price: <input type="text" name="vat_price" value="55000"></p>
    <p>MY_comment: <input type="text" name="MY_comment" value="테스트"></p>
    <p><button type="button" onclick="testAjax()">AJAX 테스트</button></p>
</form>

<div id="ajaxResult"></div>

<script>
function testAjax() {
    var form = document.getElementById('testForm');
    var formData = new FormData(form);
    
    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('ajaxResult').innerHTML = 
            '<h4>AJAX 결과:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>';
    })
    .catch(error => {
        document.getElementById('ajaxResult').innerHTML = 
            '<h4>AJAX 오류:</h4><pre>' + error.toString() + '</pre>';
    });
}
</script>