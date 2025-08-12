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
$MY_amount = $_GET['MY_amount'] ?? '';
$POtype = $_GET['POtype'] ?? '';
$PN_type = $_GET['PN_type'] ?? ''; // 후가공 (Section)
$ordertype = $_GET['ordertype'] ?? '';

// 필수 파라미터 검증
if (empty($MY_type) || empty($MY_amount) || empty($POtype) || empty($PN_type) || empty($ordertype)) {
    error_response('모든 옵션을 선택해주세요.');
}

// 가격 정보 조회
$TABLE = "MlangPrintAuto_merchandisebond";
$query = "SELECT money, DesignMoney FROM {$TABLE} WHERE style = ? AND quantity = ? AND POtype = ? AND Section = ?";

$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    error_response('데이터베이스 쿼리 준비에 실패했습니다.');
}

mysqli_stmt_bind_param($stmt, "iiii", $MY_type, $MY_amount, $POtype, $PN_type);

if (!mysqli_stmt_execute($stmt)) {
    error_response('가격 정보 조회에 실패했습니다.');
}

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    error_response('해당 조건의 가격 정보가 없습니다. 다른 옵션을 선택해주세요.');
}

// 가격 계산
$base_price = (int)($row['money'] ?? 0);
$design_price = ($ordertype === 'total') ? (int)($row['DesignMoney'] ?? 0) : 0;
$total_price = $base_price + $design_price;
$total_with_vat = $total_price * 1.1;

// 결과 반환
success_response([
    'base_price' => $base_price,
    'design_price' => $design_price,
    'total_price' => $total_price,
    'total_with_vat' => $total_with_vat
]);

// 데이터베이스 연결 종료
mysqli_close($db);
?>
