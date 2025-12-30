<?php
header("Content-Type: text/html; charset=UTF-8");
include "lib.php";
$connect = dbconn();
mysql_query("SET NAMES utf8", $connect);
mysql_select_db("duson1830", $connect);

echo "<h3>DB 데이터 확인 (최근 5건)</h3>";
echo "<pre>";

$query = "select no, name, zip, zip1, zip2, phone, Hendphone, Type, Type_1 from MlangOrder_PrintAuto where ((zip1 like '%구%') or (zip2 like '%-%')) order by no desc limit 5";
$result = mysql_query($query, $connect);

if (!$result) {
    echo "Query Error: " . mysql_error($connect);
} else {
    while ($row = mysql_fetch_array($result)) {
        echo "========================================\n";
        echo "no: " . $row['no'] . "\n";
        echo "name: [" . $row['name'] . "]\n";
        echo "zip: [" . $row['zip'] . "]\n";
        echo "zip1: [" . $row['zip1'] . "]\n";
        echo "zip2: [" . $row['zip2'] . "]\n";
        echo "phone: [" . $row['phone'] . "]\n";
        echo "Hendphone: [" . $row['Hendphone'] . "]\n";
        echo "Type: [" . $row['Type'] . "]\n";
        echo "Type_1 (처음 100자): [" . substr($row['Type_1'], 0, 100) . "]\n";
        echo "\n";
    }
}

echo "</pre>";
mysql_close($connect);
?>
