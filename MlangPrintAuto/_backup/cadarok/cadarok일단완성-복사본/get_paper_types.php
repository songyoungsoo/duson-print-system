<?php
header("Content-Type: application/json; charset=utf-8");

$HomeDir = "../../";
include "$HomeDir/db.php";

// 파라미터 받기
$CV_no = $_GET['CV_no'] ?? '';
if (!$CV_no || !is_numeric($CV_no)) {
    echo json_encode([]);
    exit;
}

// 테이블 이름 고정 또는 인자 기반 설정 가능
$table = "mlangprintauto_transactioncate";

// 종이종류는 TreeNo = 상위 규격 no
$sql = "SELECT no, title FROM $table WHERE TreeNo = ? ORDER BY no ASC";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $CV_no);
$stmt->execute();
$result = $stmt->get_result();

$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = [
        "no" => $row['no'],
        "title" => $row['title']
    ];
}

echo json_encode($options, JSON_UNESCAPED_UNICODE);
