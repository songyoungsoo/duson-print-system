<?php
// Temporary test setup - find orders and optionally set delivery fee
$HomeDir = __DIR__;
include "$HomeDir/db.php";
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $r = mysqli_query($db, "SELECT no, name, delivery, money_5, logen_delivery_fee, logen_fee_type FROM mlangorder_printauto ORDER BY no DESC LIMIT 5");
    $rows = [];
    while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} elseif ($action === 'set') {
    $no = intval($_GET['no'] ?? 0);
    $fee = intval($_GET['fee'] ?? 3500);
    if ($no > 0) {
        $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET logen_delivery_fee = ?, logen_fee_type = '선불' WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "ii", $fee, $no);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true, 'no' => $no, 'fee' => $fee]);
    }
} elseif ($action === 'reset') {
    $no = intval($_GET['no'] ?? 0);
    if ($no > 0) {
        $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET logen_delivery_fee = 0, logen_fee_type = '' WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $no);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true, 'reset' => $no]);
    }
}
mysqli_close($db);
