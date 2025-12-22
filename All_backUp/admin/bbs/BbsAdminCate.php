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
$BbsAdminCateUrl = $BbsAdminCateUrl ?? '../..';
$BBS_ADMIN_skin = $BBS_ADMIN_skin ?? '';

$dir_path = "$BbsAdminCateUrl/bbs/skin";
$dir_handle = opendir($dir_path);

$RRT="selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";

echo("<select name='skin'><option value='0'>▒ SKIN 선택 ▒</option>");

while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")){
		if($BBS_ADMIN_skin=="$tmp"){
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp' $RRT>$tmp</option>");  
			}else{
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp'>$tmp</option>");  
			}		  }
}

echo("</select>");

closedir($dir_handle);
?>