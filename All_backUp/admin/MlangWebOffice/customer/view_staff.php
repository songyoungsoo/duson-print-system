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

$result= mysqli_query($db, "select * from WebOffice_customer_staff where no='$customer_no'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$row= mysqli_fetch_array($result);
$staffViewcustomer_no="$row[customer_no]";
$staffViewname="$row[name]";
$staffViewtel_1="$row[tel_1]";
$staffViewtel_2="$row[tel_2]"; 
$staffViewtel_3="$row[tel_3]";
$staffViewwork="$row[work]";
$staffViewphoto="$row[photo]"; 
mysqli_close($db); 
?>