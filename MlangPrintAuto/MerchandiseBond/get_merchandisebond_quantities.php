<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 확인
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';

// 필수 파라미터 검증
if (empty($MY_type)) {
    error_response('필수 파라미터(MY_type)가 누락되었습니다.');
}

// 수량 옵션 조회
$TABLE = "MlangPrintAuto_merchandisebond";
$query = "SELECT DISTINCT quantity FROM {$TABLE} WHERE style = ? AND quantity IS NOT NULL ORDER BY CAST(quantity AS UNSIGNED) ASC";

$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    error_response('데이터베이스 쿼리 준비에 실패했습니다.');
}

mysqli_stmt_bind_param($stmt, "i", $MY_type);

if (!mysqli_stmt_execute($stmt)) {
    error_response('수량 정보 조회에 실패했습니다.');
}

$result = mysqli_stmt_get_result($stmt);
$quantities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quantities[] = [
        'value' => $row['quantity'],
        'text' => format_number($row['quantity']) . '매'
    ];
}

mysqli_stmt_close($stmt);

// 결과 반환
if (!empty($quantities)) {
    success_response($quantities);
} else {
    error_response('해당 종류의 수량 정보가 없습니다.');
}

// 데이터베이스 연결 종료
mysqli_close($db);
?>
