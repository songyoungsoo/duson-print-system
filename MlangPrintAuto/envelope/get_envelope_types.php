<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 확인
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 받기
$CV_no = $_GET['CV_no'] ?? '';

// 필수 파라미터 검증
if (empty($CV_no)) {
    error_response('필수 파라미터가 누락되었습니다.');
}

// 옵션 조회
$options = getDropdownOptions($db, 'MlangPrintAuto_transactionCate', [
    'BigNo' => $CV_no,
    'Ttable' => 'envelope'
], 'no ASC');

// 결과 반환
if ($options) {
    success_response($options);
} else {
    error_response('해당하는 종류 정보가 없습니다.');
}

// 데이터베이스 연결 종료
mysqli_close($db);
?>
