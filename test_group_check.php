<?php
include "db.php";
$result = mysqli_query($db, "SELECT no, name, product_type, st_price, item_group_id, item_group_seq, session_id FROM shop_temp WHERE no IN (8392,8393,8394) ORDER BY no");
$rows = [];
if ($result) { while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; } }
header('Content-Type: application/json');
echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
