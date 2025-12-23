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

$host="localhost";
$user="imepa";
$dataname="imepa";
$password="imepa1004";
$adminid="0000";
$adminpasswd="0000";
$table="MlangFriendSite";
$AdStyle="text";
$AdWidth="180";
$AdHeight="50";
$AdCate="정보통신:교육,학문:정부,관공서:기업:뉴스:스포츠:금융:경제,재테크:결혼,웨딩:여성:개인홈페이지:기타";
$db=mysqli_connect($host,$user,$password);
mysqli_select_db($db, "$dataname");
$Copyright="Copyright (c) 2004 by <a href='http://www.script.ne.kr' target='_blank'><font style='font-size:8pt; color:#ADADAD;'>스크립트네꺼</font></a> Comp. All right Reserved.";
?>