<?php
include "db_ajax.php";

// 파라미터 받기
$NC_type = $_GET['NC_type'] ?? '';
$NC_paper = $_GET['NC_paper'] ?? '';
$NC_amount = $_GET['NC_amount'] ?? '';

if (empty($NC_type) || empty($NC_paper) || empty($NC_amount)) {
    echo json_encode([]);
    exit;
}

// 해당 조합의 양면/단면 옵션 가져오기
$result = mysqli_query($db, "SELECT DISTINCT POtype FROM mlangprintauto_namecard WHERE style='$NC_type' AND Section='$NC_paper' AND quantity='$NC_amount' ORDER BY POtype ASC");
$sides = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $potype = $row['POtype'];
        $side_name = ($potype == '1') ? '단면' : (($potype == '2') ? '양면' : 'POtype ' . $potype);
        
        $sides[] = [
            'value' => $potype,
            'text' => $side_name
        ];
    }
}

// JSON 형태로 반환
header('Content-Type: application/json; charset=utf-8');
echo json_encode($sides);

mysqli_close($db);
?>