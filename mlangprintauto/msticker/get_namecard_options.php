<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결 확인
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';

// 필수 파라미터 검증
if (empty($MY_type)) {
    error_response('필수 파라미터(MY_type)가 누락되었습니다.');
}

// 1. 명함 재질(papers) 조회
$papers = getDropdownOptions($db, "mlangprintauto_transactioncate", [
    'BigNo' => $MY_type,
    'Ttable' => 'NameCard'
], 'no ASC');

// 2. 수량(quantities) 조회
$TABLE = "mlangprintauto_namecard";
$query = "SELECT DISTINCT quantity FROM {$TABLE_N} WHERE style = ? AND quantity IS NOT NULL ORDER BY CAST(quantity AS UNSIGNED) ASC";

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
if ($papers !== false && !empty($quantities)) {
    success_response([
        'papers' => $papers,
        'quantities' => $quantities
    ]);
} else {
    error_response('옵션 정보가 부족합니다.');
}

// 데이터베이스 연결 종료
mysqli_close($db);
?>
