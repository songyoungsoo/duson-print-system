<?php
include "../../db.php";
// 예시로 MySQLi를 사용하는 방법입니다. 실제 데이터베이스 연결 및 쿼리는 여기서 처리해야 합니다.
$db = new mysqli($host, $user, $password, $dataname);

// 오류 확인
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// no 변수는 적절히 처리되어야 합니다 (예: 필터링, 유효성 검사)
$no = isset($_REQUEST['no']) ? $_REQUEST['no'] : null;
$GGTABLE = isset($_REQUEST['GGTABLE']) ? $_REQUEST['GGTABLE'] : null;
$no = mysqli_real_escape_string($db, $no);

// 쿼리 준비 및 실행
$query = "SELECT * FROM $GGTABLE WHERE no='$no'";
$result = $db->query($query);

if (!$result) {
    die("Query failed: " . $db->error);
}

// 결과 가져오기
$row = $result->fetch_assoc();

// 변수 할당
$View_Ttable = $row['Ttable'];
$View_style = $row['style'];
$View_BigNo = $row['BigNo'];
$View_title = $row['title'];
$View_TreeNo = $row['TreeNo'];

// 연결 닫기
$db->close();
?>
