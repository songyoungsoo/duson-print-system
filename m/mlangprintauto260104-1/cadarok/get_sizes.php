<?php
header("Content-Type: application/json; charset=utf-8");

$HomeDir = "../../";

// 데이터베이스 연결 설정 (index.php와 동일하게)
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    echo json_encode([]);
    exit;
}

mysqli_set_charset($db, "utf8");

// 파라미터 받기
$CV_no = $_GET['CV_no'] ?? '';
if (!$CV_no || !is_numeric($CV_no)) {
    echo json_encode([]);
    exit;
}

// 테이블 이름 고정 또는 파라미터화 가능
$TABLE = "mlangprintauto_transactioncate";

// 하위 규격 가져오기: BigNo = 상위 no
$sql = "SELECT no, title FROM $table WHERE BigNo = ? ORDER BY no ASC";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $CV_no);
$stmt->execute();
$result = $stmt->get_result();

$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = [
        "no" => $row['no'],
        "title" => $row['title']
    ];
}

// 연결 종료
if ($db) {
    mysqli_close($db);
}

echo json_encode($options, JSON_UNESCAPED_UNICODE);
