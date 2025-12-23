<?php
include "db_ajax.php";
$TABLE = "mlangprintauto_transactioncate";
$GGTABLE = "mlangprintauto_transactioncate";

// CV_no 파라미터 받기 (명함 종류)
$CV_no = $_GET['CV_no'] ?? '';

if (empty($CV_no)) {
    echo json_encode([]);
    exit;
}

// 명함 하위 옵션 가져오기 (용지 종류)
$result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='$CV_no' ORDER BY no ASC");
$options = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $options[] = [
            'no' => $row['no'],
            'title' => $row['title']
        ];
    }
}

// JSON 형태로 반환
header('Content-Type: application/json; charset=utf-8');
echo json_encode($options);

mysqli_close($db);
?>