<?php
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

$style = $_GET['style'] ?? '';
$section = $_GET['section'] ?? '';
$potype = $_GET['potype'] ?? '';

if (empty($style) || empty($section) || empty($potype)) {
    error_response('필수 파라미터가 누락되었습니다. (style, section, potype)');
}

$TABLE = "MlangPrintAuto_cadarok";

$query = "SELECT DISTINCT quantity 
          FROM $TABLE 
          WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
          AND Section='" . mysqli_real_escape_string($db, $section) . "' 
          AND POtype='" . mysqli_real_escape_string($db, $potype) . "'
          AND quantity IS NOT NULL 
          ORDER BY CAST(quantity AS UNSIGNED) ASC";

$result = mysqli_query($db, $query);
$quantities = [];

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $quantities[] = [
            'value' => $row['quantity'],
            'text' => format_number($row['quantity']) . '매'
        ];
    }
    if (empty($quantities)) {
        // 수량 정보가 없을 경우, potype 없이 한 번 더 조회 (일부 데이터는 potype이 없을 수 있음)
        $query_fallback = "SELECT DISTINCT quantity 
                           FROM $TABLE 
                           WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
                           AND Section='" . mysqli_real_escape_string($db, $section) . "'
                           AND quantity IS NOT NULL 
                           ORDER BY CAST(quantity AS UNSIGNED) ASC";
        $result_fallback = mysqli_query($db, $query_fallback);
        if($result_fallback) {
            while ($row_fallback = mysqli_fetch_array($result_fallback)) {
                $quantities[] = [
                    'value' => $row_fallback['quantity'],
                    'text' => format_number($row_fallback['quantity']) . '매'
                ];
            }
        }
    }
} else {
    error_response('수량 조회 중 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);
success_response($quantities);
?>