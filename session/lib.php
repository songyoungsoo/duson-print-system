<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

error_reporting(E_ALL);
ini_set("display_errors", 1);

$db_name = "duson1830";
$connect = mysqli_connect("localhost", "duson1830", "du1830") or die("데이터베이스 연결에 실패하였습니다!");

if (!$connect) {
    echo "MySQL 접속 중 오류가 발생했습니다.";
    echo mysqli_error();
}

mysqli_select_db($db_name, $connect); // DB 선택

// 데이터베이스 연결 및 선택 완료
?>
<style>
td,input,li,a{font-size:9pt}
th {
  background-color: #CCCCFF;
  font-size: 9pt;
  text-decoration: none;
}
border{border-color:#CCC}
</style>
?>