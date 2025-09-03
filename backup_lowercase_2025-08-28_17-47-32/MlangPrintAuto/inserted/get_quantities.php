<?php
header('Content-Type: application/json; charset=UTF-8');

// 데이터베이스 연결 - db.php 사용
include "../../db.php";
$connect = $db;
if (!$connect) {
    echo json_encode(['error' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8");

// GET 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_Fsd = $_GET['MY_Fsd'] ?? '';
$POtype = $_GET['POtype'] ?? '1'; // 단면/양면 파라미터 추가 (기본값: 단면)

$TABLE = "MlangPrintAuto_inserted";

// 선택된 조건에 맞는 수량 옵션들을 가져오기 (POtype 조건 추가)
$query = "SELECT DISTINCT quantity, quantityTwo 
          FROM $TABLE 
          WHERE style='$MY_type' 
          AND Section='$PN_type' 
          AND TreeSelect='$MY_Fsd'
          AND POtype='$POtype'
          AND quantity IS NOT NULL 
          AND quantityTwo IS NOT NULL 
          ORDER BY CAST(quantity AS DECIMAL(10,1)) ASC";

$result = mysqli_query($connect, $query);
$quantities = [];

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $quantities[] = [
            'value' => $row['quantity'],
            'text' => $row['quantityTwo'] . '매 (' . $row['quantity'] . '연)'
        ];
    }
}

mysqli_close($connect);

echo json_encode($quantities);
?>