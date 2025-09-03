<?php
include "db_ajax.php";

// 파라미터 받기
$NC_type = $_GET['NC_type'] ?? '';
$NC_paper = $_GET['NC_paper'] ?? '';

if (empty($NC_type)) {
    echo json_encode([]);
    exit;
}

// 용지가 선택된 경우 해당 조합의 수량만 가져오기, 아니면 전체 명함 종류의 수량
if (!empty($NC_paper)) {
    $query = "SELECT DISTINCT quantity FROM mlangprintauto_namecard WHERE style='$NC_type' AND Section='$NC_paper' ORDER BY quantity ASC";
} else {
    $query = "SELECT DISTINCT quantity FROM mlangprintauto_namecard WHERE style='$NC_type' ORDER BY quantity ASC";
}

$result = mysqli_query($db, $query);
$quantities = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $quantity = $row['quantity'];
        $quantities[] = [
            'value' => $quantity,
            'text' => number_format($quantity) . '매'
        ];
    }
}

// JSON 형태로 반환
header('Content-Type: application/json; charset=utf-8');
echo json_encode($quantities);

mysqli_close($db);
?>