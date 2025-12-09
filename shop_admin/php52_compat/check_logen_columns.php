<?php
/**
 * 로젠 컬럼 존재 여부 확인 스크립트
 * PHP 5.2 호환
 */
header('Content-Type: text/html; charset=EUC-KR');
include "lib.php";

$connect = dbconn();
if (!$connect) {
    die("DB 연결 실패");
}

echo "<h3>MlangOrder_PrintAuto 테이블 컬럼 확인</h3>";

// 테이블 구조 확인
$query = "SHOW COLUMNS FROM MlangOrder_PrintAuto LIKE 'logen_%'";
$result = mysql_query($query);

if (!$result) {
    echo "<p style='color:red'>쿼리 오류: " . mysql_error() . "</p>";
} else {
    $count = mysql_num_rows($result);
    echo "<p>logen_ 컬럼 개수: <b>" . $count . "</b></p>";

    if ($count > 0) {
        echo "<ul>";
        while ($row = mysql_fetch_assoc($result)) {
            echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>logen_ 컬럼이 없습니다. ALTER TABLE 필요:</p>";
        echo "<pre>";
        echo "ALTER TABLE MlangOrder_PrintAuto ADD COLUMN logen_box_qty INT DEFAULT NULL;\n";
        echo "ALTER TABLE MlangOrder_PrintAuto ADD COLUMN logen_delivery_fee INT DEFAULT NULL;\n";
        echo "ALTER TABLE MlangOrder_PrintAuto ADD COLUMN logen_fee_type VARCHAR(10) DEFAULT NULL;\n";
        echo "</pre>";
    }
}

// 간단한 UPDATE 테스트
echo "<h3>UPDATE 테스트</h3>";
$test_query = "UPDATE MlangOrder_PrintAuto SET logen_box_qty = logen_box_qty WHERE no = 1 LIMIT 1";
$test_result = mysql_query($test_query);
if ($test_result) {
    echo "<p style='color:green'>UPDATE 쿼리 실행 가능</p>";
} else {
    echo "<p style='color:red'>UPDATE 오류: " . mysql_error() . "</p>";
}
?>
