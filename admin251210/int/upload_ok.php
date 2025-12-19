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

include "upload.inc";                 // 업로드 함수 include

$forbid_ext = array("php","asp","jsp","inc","c","cpp","sh");

// 그겟판의폴더를 생성시켜서 그걸 호출해버리장 즉 데이타베이스에는 저장할 필요가 읍다.
$result=func_multi_upload($upfile, $upfile_name, $upfile_size, $upfile_type, "./", $forbid_ext);

if ($result) {
     echo "
     <script language='javascript'>
     alert(' $result 개의 파일이 업로드 되었습니다.. ㅋㅋㅋ');
     </script>";
} else {
     echo "
     <script language='javascript'>
     alert('파일이 안 업로드 되었습니다.. ㅋㅋㅋ');
     </script>";
}
?> 
