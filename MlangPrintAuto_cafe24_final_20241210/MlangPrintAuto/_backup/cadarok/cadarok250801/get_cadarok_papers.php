<?php
include "../../db.php";

$category_type = $_GET['category_type'] ?? '';

$options = [];
if ($category_type) {
    $GGTABLE = "MlangPrintAuto_transactionCate";
    $query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' AND TreeNo=? ORDER BY no ASC";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $category_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($options);
?>