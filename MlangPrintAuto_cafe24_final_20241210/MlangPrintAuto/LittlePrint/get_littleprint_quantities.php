<?php
session_start();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode([]);
    exit;
}

mysqli_set_charset($connect, "utf8");

$MY_type = $_GET['MY_type'] ?? '';

if (empty($MY_type)) {
    echo json_encode([]);
    exit;
}

// 포스터 수량 옵션 조회 (기본 수량들)
$quantities = [
    ['value' => '100', 'text' => '100매'],
    ['value' => '200', 'text' => '200매'],
    ['value' => '300', 'text' => '300매'],
    ['value' => '500', 'text' => '500매'],
    ['value' => '1000', 'text' => '1000매'],
    ['value' => '2000', 'text' => '2000매'],
    ['value' => '3000', 'text' => '3000매'],
    ['value' => '5000', 'text' => '5000매'],
    ['value' => '10000', 'text' => '10000매']
];

echo json_encode($quantities);

mysqli_close($connect);
?>