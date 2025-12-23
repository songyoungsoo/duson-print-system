<?php
declare(strict_types=1);	


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

include"../../db.php";
include"../config.php"; // 관리자 로그인
  	
	header("Content-disposition: filename=$TableName.sql");
	header("Content-type: application/octetstream");
	header("Pragma: no-cache");
	header("Expires: 0");
	
     $pResult = mysqli_query($db,  "show variables" );
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

     
    echo "-- 프로그램명: Mlang Web 관리프로그램3.0 - $TableName MySql DataBase DUMP\n";
    echo "\n";
    echo "-- 프로그램/홈페이지제작 문의 : http://www.websil.net - webmaster@websil.net\n";
	echo "\n";
    echo "-- Source from  <MySQL DUMP [Mlang (http://www.script.ne.kr-webmaster@script.ne.kr)]>\n";
 	echo "\n";
 	
     while( 1 ) 
     {
     	$rowArray = mysqli_fetch_row( $pResult );
     	
     	if( $rowArray == false ) break;
     	if( $rowArray[0] == "basedir" )
     		$bindir = $rowArray[1]."bin/";
     }
     passthru( $bindir."mysqldump --user=$user --password=$password $dataname $TableName" );
     
     mysqli_close($db);
?>