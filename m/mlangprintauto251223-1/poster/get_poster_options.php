<?php
header('Content-Type: application/json; charset=utf-8');
include "../../db.php";

$style_no = $_GET['style'] ?? '';
$page = 'LittlePrint';

$response = [
    'papers' => [],
    'sections' => [],
    'quantities' => [],
    'potypes' => []
];

if ($style_no && $db) {
    // 1. Papers (종이종류)
    $paper_query = "
        SELECT DISTINCT lp.TreeSelect, tc.title 
        FROM mlangprintauto_littleprint lp
        JOIN mlangprintauto_transactioncate tc ON lp.TreeSelect = tc.no
        WHERE lp.style = ? AND tc.Ttable = ?
        ORDER BY tc.no
    ";
    $stmt = mysqli_prepare($db, $paper_query);
    mysqli_stmt_bind_param($stmt, "ss", $style_no, $page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $response['papers'][] = ['value' => $row['TreeSelect'], 'title' => $row['title']];
    }

    // 2. Sections (종이규격)
    $section_query = "
        SELECT DISTINCT lp.Section, tc.title 
        FROM mlangprintauto_littleprint lp
        JOIN mlangprintauto_transactioncate tc ON lp.Section = tc.no
        WHERE lp.style = ? AND tc.Ttable = ?
        ORDER BY tc.no
    ";
    $stmt = mysqli_prepare($db, $section_query);
    mysqli_stmt_bind_param($stmt, "ss", $style_no, $page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $response['sections'][] = ['value' => $row['Section'], 'title' => $row['title']];
    }

    // 3. Quantities (수량)
    $quantity_query = "
        SELECT DISTINCT quantity 
        FROM mlangprintauto_littleprint 
        WHERE style = ? 
        ORDER BY CAST(quantity AS UNSIGNED)
    ";
    $stmt = mysqli_prepare($db, $quantity_query);
    mysqli_stmt_bind_param($stmt, "s", $style_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $response['quantities'][] = $row['quantity'];
    }

    // 4. POtypes (인쇄면)
    $potype_query = "
        SELECT DISTINCT lp.POtype, tc.title 
        FROM mlangprintauto_littleprint lp
        JOIN mlangprintauto_transactioncate tc ON lp.POtype = tc.no
        WHERE lp.style = ? AND tc.Ttable = ?
        ORDER BY tc.no
    ";
    $stmt = mysqli_prepare($db, $potype_query);
    if (!$stmt) {
        error_log("POtype query prepare failed: " . mysqli_error($db));
    }
    mysqli_stmt_bind_param($stmt, "ss", $style_no, $page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("POtype query execute failed: " . mysqli_error($db));
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $response['potypes'][] = ['value' => $row['POtype'], 'title' => $row['title']];
    }

    mysqli_stmt_close($stmt);
}

echo json_encode($response);
mysqli_close($db);
?>