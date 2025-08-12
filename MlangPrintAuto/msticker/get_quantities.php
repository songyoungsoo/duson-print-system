<?php
// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// GET 파라미터 받기
$style = $_GET['style'] ?? '';        // 종류 (742 등)
$Section = $_GET['Section'] ?? '';    // 규격 (743 등)

$TABLE = "MlangPrintAuto_msticker";

// 입력값 검증
if (empty($style) || empty($Section)) {
    error_response('필수 파라미터가 누락되었습니다.');
}

// 선택된 조건에 맞는 수량 옵션들을 가져오기
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
        $quantities[] = [
            'value' => $row['quantity'],
            'text' => format_number($row['quantity']) . '매'
        ];
    }
}

mysqli_close($db);
success_response($quantities);
?>