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

$result= mysqli_query($db, "select * from WebOffice_customer where no='$no'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$row= mysqli_fetch_array($result);
$Viewbizname="$row[bizname]";
$Viewceoname="$row[ceoname]";
$Viewtel_1="$row[tel_1]"; 
$Viewtel_2="$row[tel_2]";   
$Viewtel_3="$row[tel_3]";  
$Viewfax_1="$row[fax_1]";
$Viewfax_2="$row[fax_2]"; 
$Viewfax_3="$row[fax_3]";
$Viewzip="$row[zip]"; 
$Viewofftel_1="$row[offtel_1]";
$Viewofftel_2="$row[offtel_2]";
$Viewofftel_3="$row[offtel_3]";   
$Viewofffax_1="$row[offfax_1]"; 
$Viewofffax_2="$row[offfax_2]";
$Viewofffax_3="$row[offfax_3]"; 
$Viewoffmap="$row[offmap]";
$Viewcont="$row[cont]";
mysqli_close($db); 
?>