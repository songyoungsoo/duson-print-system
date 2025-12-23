<?php
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

// MySQLi로 데이터베이스 연결
$db = new mysqli($host, $user, $password, $dataname);

// 연결 확인
if ($db->connect_error) {
    die("데이터베이스 연결에 실패했습니다: " . $db->connect_error);
}

// UTF-8 인코딩 설정
$db->set_charset("utf8");
?>