<?php
// 데이터베이스 연결
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$connect = mysqli_connect($host, $user, $password, $dataname);
if (!$connect) {
    echo json_encode([]);
    exit;
}

mysqli_set_charset($connect, "utf8");

$CV_no = $_GET['CV_no'] ?? '';
$page = $_GET['page'] ?? 'inserted';
$GGTABLE = "MlangPrintAuto_transactionCate";

$options = [];
if (!empty($CV_no)) {
    $query = "SELECT * FROM $GGTABLE WHERE TreeNo='$CV_no' ORDER BY no ASC";
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