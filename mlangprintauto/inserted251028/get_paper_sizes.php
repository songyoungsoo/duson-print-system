<?php
// 데이터베이스 연결 - db.php 사용
include "../../db.php";
$connect = $db;
if (!$connect) {
    echo json_encode([]);
    exit;
}

mysqli_set_charset($connect, "utf8");

$CV_no = $_GET['CV_no'] ?? '';
$page = $_GET['page'] ?? 'inserted';
$TABLE = "mlangprintauto_transactioncate";

$options = [];
if (!empty($CV_no)) {
$GGTABLE = "mlangprintauto_transactioncate";
    $query = "SELECT * FROM $GGTABLE WHERE BigNo='$CV_no' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
}

mysqli_close($connect);
echo json_encode($options);
?>