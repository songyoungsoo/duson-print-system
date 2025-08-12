<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 확인
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';
$POtype = $_GET['POtype'] ?? '';

// 필수 파라미터 검증
if (empty($MY_type)) {
    error_response('필수 파라미터(MY_type)가 누락되었습니다.');
}

// 후가공 옵션 조회
$TABLE_M = "MlangPrintAuto_merchandisebond";
$TABLE_T = "MlangPrintAuto_transactionCate";

$query = "SELECT DISTINCT t.no, t.title 
          FROM {$TABLE_M} m
          JOIN {$TABLE_T} t ON m.Section = t.no
          WHERE m.style = ? AND t.Ttable = 'MerchandiseBond'
          ORDER BY t.no ASC";

$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    error_response('데이터베이스 쿼리 준비에 실패했습니다.');
}

mysqli_stmt_bind_param($stmt, "i", $MY_type);

if (!mysqli_stmt_execute($stmt)) {
    error_response('후가공 정보 조회에 실패했습니다.');
}

$result = mysqli_stmt_get_result($stmt);
$after_processes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $after_processes[] = [
        'no' => $row['no'],
        'title' => $row['title']
    ];
}

mysqli_stmt_close($stmt);

// 결과 반환
if (!empty($after_processes)) {
    success_response($after_processes);
} else {
    error_response('해당 조건의 후가공 정보가 없습니다.');
}

// 데이터베이스 연결 종료
mysqli_close($db);
?>
