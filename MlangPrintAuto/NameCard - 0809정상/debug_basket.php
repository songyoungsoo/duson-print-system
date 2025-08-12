<?php
// 세션 시작
session_start();

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

echo "<h2>🔍 명함 장바구니 디버그</h2>";

echo "<h3>📊 POST 데이터:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>🔑 세션 정보:</h3>";
echo "세션 ID: " . session_id() . "<br>";
echo "세션 상태: " . (session_status() == PHP_SESSION_ACTIVE ? '활성' : '비활성') . "<br>";

echo "<h3>🗄️ shop_temp 테이블 구조:</h3>";
$table_query = "DESCRIBE shop_temp";
$result = mysqli_query($db, $table_query);
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "테이블 구조 조회 오류: " . mysqli_error($db);
}

echo "<h3>📦 현재 장바구니 내용:</h3>";
$session_id = session_id();
$cart_query = "SELECT * FROM shop_temp WHERE session_id = '$session_id'";
$cart_result = mysqli_query($db, $cart_query);
if ($cart_result && mysqli_num_rows($cart_result) > 0) {
    echo "<table border='1'>";
    $first_row = true;
    while ($row = mysqli_fetch_assoc($cart_result)) {
        if ($first_row) {
            echo "<tr>";
            foreach (array_keys($row) as $column) {
                echo "<th>$column</th>";
            }
            echo "</tr>";
            $first_row = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "장바구니가 비어있습니다.";
}

mysqli_close($db);
?>