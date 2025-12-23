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
$table="BizMap";
$AdCate="경기,서울,인천:강원:충남,대전:충북:경북,대구:경남,울산,부산:전북:전남:제주";
$db=mysqli_connect($host,$user,$password);
mysqli_select_db($db, "$dataname");
$Copyright="Copyright (c) 2004 by <a href='http://www.script.ne.kr' target='_blank'><font style='font-size:8pt; color:#ADADAD;'>스크립트네꺼</font></a> Comp. All right Reserved.";
?>