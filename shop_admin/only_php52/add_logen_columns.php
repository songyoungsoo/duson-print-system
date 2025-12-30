<?php
/**
 * 로젠 컬럼 추가 스크립트
 * PHP 5.2 호환
 */
header('Content-Type: text/html; charset=EUC-KR');
include "lib.php";

$connect = dbconn();
if (!$connect) {
    die("DB 연결 실패");
}

echo "<h3>MlangOrder_PrintAuto 테이블에 로젠 컬럼 추가</h3>";

$columns = array(
    "logen_box_qty INT DEFAULT NULL COMMENT '박스수량'",
    "logen_delivery_fee INT DEFAULT NULL COMMENT '택배비'",
    "logen_fee_type VARCHAR(10) DEFAULT NULL COMMENT '운임구분'"
);

foreach ($columns as $col) {
    // 컬럼명 추출
    $parts = explode(' ', trim($col));
    $col_name = $parts[0];

    // 이미 존재하는지 확인
    $check = mysql_query("SHOW COLUMNS FROM MlangOrder_PrintAuto LIKE '$col_name'");
    if (mysql_num_rows($check) > 0) {
        echo "<p>$col_name: 이미 존재함</p>";
        continue;
    }

    // 컬럼 추가
    $alter_query = "ALTER TABLE MlangOrder_PrintAuto ADD COLUMN $col";
    $result = mysql_query($alter_query);

    if ($result) {
        echo "<p style='color:green'>$col_name: 추가 완료</p>";
    } else {
        echo "<p style='color:red'>$col_name: 추가 실패 - " . mysql_error() . "</p>";
    }
}

echo "<h3>완료</h3>";
echo "<p><a href='check_logen_columns.php'>컬럼 확인 페이지로 이동</a></p>";
echo "<p><a href='post_list52.php'>로젠주소추출 페이지로 이동</a></p>";
?>
