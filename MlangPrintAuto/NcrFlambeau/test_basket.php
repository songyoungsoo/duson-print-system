<?php
// 장바구니 추가 테스트
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// POST 데이터 시뮬레이션
$_POST = [
    'action' => 'add_to_basket',
    'MY_type' => '475',
    'MY_Fsd' => '484',
    'PN_type' => '505',
    'MY_amount' => '60',
    'ordertype' => 'total',
    'price' => '150000',
    'vat_price' => '165000',
    'product_type' => 'ncrflambeau',
    'comment' => '테스트 주문',
    'log_url' => 'test_url',
    'log_y' => '2025',
    'log_md' => '0804',
    'log_ip' => '127.0.0.1',
    'log_time' => time()
];

echo "<h2>장바구니 추가 테스트</h2>";
echo "<h3>POST 데이터:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// add_to_basket.php 로직 실행
include "add_to_basket.php";
?>