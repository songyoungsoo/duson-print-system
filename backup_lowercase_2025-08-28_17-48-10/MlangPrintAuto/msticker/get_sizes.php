<?php
// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

$CV_no = $_GET['CV_no'] ?? '';
$page = $_GET['page'] ?? 'msticker';
$TABLE = "mlangprintauto_transactioncate";

if (empty($CV_no)) {
    error_response('카테고리 번호가 필요합니다.');
}

// 규격 조회 (BigNo 기준)
$options = getDropdownOptions($db, $GGTABLE, [
    'Ttable' => $page,
    'BigNo' => $CV_no
]);

mysqli_close($db);
success_response($options);
?>