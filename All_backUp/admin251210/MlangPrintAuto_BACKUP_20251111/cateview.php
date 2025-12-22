<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

// ✅ GGTABLE 변수 정의 (데이터베이스 연결은 CateAdmin.php에서 이미 포함됨)
$GGTABLE = 'mlangprintauto_transactioncate';

$result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$no'");
if (!$result) {
    die('Query Error: ' . mysqli_error($db));
}

$row = mysqli_fetch_array($result);
if (!$row) {
    die('데이터를 찾을 수 없습니다. No: ' . htmlspecialchars($no));
}

$View_Ttable = $row['Ttable'] ?? '';
$View_BigNo = $row['BigNo'] ?? '';
$View_title = $row['title'] ?? '';
$View_TreeNo = $row['TreeNo'] ?? '';
?>