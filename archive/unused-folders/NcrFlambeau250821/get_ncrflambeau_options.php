<?php
header('Content-Type: application/json; charset=utf-8');
include "../../db.php";

$style_no = $_GET['style'] ?? '';
$page = 'ncrflambeau';

$response = [
    'my_fsd_options' => [], // 규격
    'pn_type_options' => [], // 색상 및 재질
    'quantities' => [],      // 수량
    'ordertypes' => []       // 편집디자인
];

if ($style_no && $db) {
    // 1. MY_Fsd (규격) - Section 컬럼에서 가져오기
    $my_fsd_query = "
        SELECT DISTINCT lp.Section, tc.title 
        FROM MlangPrintAuto_NcrFlambeau lp
        JOIN MlangPrintAuto_transactionCate tc ON lp.Section = tc.no
        WHERE lp.style = ? AND tc.Ttable = ?
        ORDER BY tc.no
    ";
    $stmt = mysqli_prepare($db, $my_fsd_query);
    mysqli_stmt_bind_param($stmt, "ss", $style_no, $page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $response['my_fsd_options'][] = ['value' => $row['Section'], 'title' => $row['title']];
    }

    // 2. PN_type (색상 및 재질) - TreeSelect 컬럼에서 가져오기
    $pn_type_query = "
        SELECT DISTINCT lp.TreeSelect, tc.title 
        FROM MlangPrintAuto_NcrFlambeau lp
        JOIN MlangPrintAuto_transactionCate tc ON lp.TreeSelect = tc.no
        WHERE lp.style = ? AND tc.Ttable = ?
        ORDER BY tc.no
    ";
    $stmt = mysqli_prepare($db, $pn_type_query);
    mysqli_stmt_bind_param($stmt, "ss", $style_no, $page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $response['pn_type_options'][] = ['value' => $row['TreeSelect'], 'title' => $row['title']];
    }

    // 3. Quantities (수량) - quantity 컬럼에서 가져오기
    $quantity_query = "
        SELECT DISTINCT quantity 
        FROM MlangPrintAuto_NcrFlambeau 
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

    // 4. Ordertypes (편집디자인) - 정적 옵션
    $response['ordertypes'] = [
        ['value' => 'total', 'title' => '디자인+인쇄'],
        ['value' => 'print', 'title' => '인쇄만 의뢰']
    ];

    mysqli_stmt_close($stmt);
}

echo json_encode($response);
mysqli_close($db);
?>