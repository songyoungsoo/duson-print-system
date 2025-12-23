<?php
include "lib.php";
$connect = dbconn();
mysql_select_db("duson1830", $connect);

// 컬럼 구조 확인
$result = mysql_query("SHOW COLUMNS FROM MlangOrder_PrintAuto");
echo "=== 테이블 컬럼 ===\n";
while ($row = mysql_fetch_array($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

// 샘플 데이터 확인
echo "\n=== 샘플 데이터 (최근 3건) ===\n";
$result2 = mysql_query("SELECT no, name, company, zip, zip1, zip2, phone, Hendphone, Type, Type_1 FROM MlangOrder_PrintAuto ORDER BY no DESC LIMIT 3");
while ($data = mysql_fetch_array($result2)) {
    echo "no: " . $data['no'] . "\n";
    echo "name: [" . $data['name'] . "]\n";
    echo "company: [" . $data['company'] . "]\n";
    echo "zip: [" . $data['zip'] . "]\n";
    echo "zip1: [" . $data['zip1'] . "]\n";
    echo "zip2: [" . $data['zip2'] . "]\n";
    echo "phone: [" . $data['phone'] . "]\n";
    echo "Type: [" . $data['Type'] . "]\n";
    echo "Type_1: [" . substr($data['Type_1'], 0, 100) . "...]\n";
    echo "---\n";
}
mysql_close($connect);
?>
