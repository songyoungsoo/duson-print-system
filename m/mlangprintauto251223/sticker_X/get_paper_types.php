<?php
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

$style = $_GET['style'] ?? '';

if (empty($style)) {
    error_response('필수 파라미터(style)가 누락되었습니다.');
}

// 'style'은 transactioncate 테이블의 'BigNo'에 해당합니다.
$options = getDropdownOptions($db, "mlangprintauto_transactioncate", [
    'Ttable' => 'Sticker',
    'BigNo' => $style
], 'no ASC');

mysqli_close($db);
success_response($options);
?>