<?php
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

$style = $_GET['style'] ?? '';
$Section = $_GET['Section'] ?? '';
$lang = $_GET['lang'] ?? 'ko';

if (empty($style) || empty($Section)) {
    error_response('필수 파라미터가 누락되었습니다. (style, Section)');
}

$TABLE = "mlangprintauto_msticker";

$query = "SELECT DISTINCT quantity 
          FROM $TABLE 
          WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
          AND Section='" . mysqli_real_escape_string($db, $Section) . "'
          AND quantity IS NOT NULL 
          ORDER BY CAST(quantity AS UNSIGNED) ASC";

$result = mysqli_query($db, $query);
$quantities = [];

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $unit = ($lang === 'en') ? ' sheets' : '매';
        $quantities[] = [
            'value' => $row['quantity'],
            'text' => format_number($row['quantity']) . $unit
        ];
    }
    // 자석스티커는 단순한 구조이므로 fallback 불필요
} else {
    error_response('수량 조회 중 오류가 발생했습니다: ' . mysqli_error($db));
}

mysqli_close($db);
success_response($quantities);
?>