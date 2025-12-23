<?php
header("Content-Type: application/json");

include "../../db.php";
$connect = $db;

if (!$connect) {
    die(json_encode(['success' => false, 'error' => 'DB connection failed']));
}

mysqli_set_charset($connect, "utf8");

$my_type = $_GET['MY_type'] ?? '';
$pn_type = $_GET['PN_type'] ?? '';
$my_fsd = $_GET['MY_Fsd'] ?? '';

// 기본 옵션이 모두 선택되었는지 확인
if (empty($my_type) || empty($pn_type) || empty($my_fsd)) {
    echo json_encode(['success' => false, 'error' => 'Required parameters are missing.', 'data' => []]);
    exit;
}

$query = "SELECT DISTINCT quantity, quantityTwo 
          FROM mlangprintauto_inserted 
          WHERE style = ? AND Section = ? AND TreeSelect = ? 
          ORDER BY quantity ASC";

$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "sss", $my_type, $pn_type, $my_fsd);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Query failed']);
    exit;
}

$quantities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quantities[] = [
        'quantity' => $row['quantity'],
        'unit' => $row['quantityTwo']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $quantities
]);

mysqli_close($connect);
?>