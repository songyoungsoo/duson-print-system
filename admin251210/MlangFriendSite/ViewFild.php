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

$result= mysqli_query($db, "select * from $table where no='$ModifyCode'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{  
$GF_title="$row[title]";    
$GF_cont="$row[cont]";  
$GF_url="$row[url]";  
$GF_cate="$row[cate]";  
$GF_upfile="$row[upfile]";  
$GF_count="$row[count]";  
$GF_date="$row[date]";  
}

}else{echo("<p align=center><b>DB 에 $ModifyCode 의 등록 자료가 없음.</b></p>"); exit;}
?>