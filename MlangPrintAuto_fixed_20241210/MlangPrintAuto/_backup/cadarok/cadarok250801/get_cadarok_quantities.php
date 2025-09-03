<?php
include "../../db.php";

$my_type = $_GET['MY_type'] ?? '';
$my_fsd = $_GET['MY_Fsd'] ?? '';
$pn_type = $_GET['PN_type'] ?? '';

$options = [];

if ($my_type && $my_fsd && $pn_type) {
    $TABLE = "MlangPrintAuto_cadarok";
    $query = "SELECT DISTINCT MY_amount FROM $TABLE WHERE MY_type = ? AND MY_Fsd = ? AND PN_type = ? ORDER BY CAST(MY_amount AS DECIMAL(10,1)) ASC";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sss", $my_type, $my_fsd, $pn_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'value' => $row['MY_amount'],
                'text' => $row['MY_amount'] . '부'
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($options);
?>