<?php
include "../../mlangprintauto/ConDb.php";

header('Content-Type: application/json');

$categories = [];

if ($ConDb_A) {
    $OrderCate_LIST_script = explode(":", $ConDb_A);
    foreach ($OrderCate_LIST_script as $category) {
        $categories[] = trim($category);
    }
}

echo json_encode($categories);
?>
