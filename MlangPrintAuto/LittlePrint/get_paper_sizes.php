<?php
// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

$CV_no = $_GET['CV_no'] ?? '';
$page = $_GET['page'] ?? 'LittlePrint';
$GGTABLE = "MlangPrintAuto_transactionCate";

if (empty($CV_no)) {
    error_response('카테고리 번호가 필요합니다.');
}

// 공통함수를 사용하여 종이규격 조회
$options = getPaperSizes($db, $GGTABLE, $CV_no);

mysqli_close($db);
success_response($options);
?>