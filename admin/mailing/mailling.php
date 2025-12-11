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

$M123="..";
include"../top.php"; 

include "lib.inc.php";

set_time_limit(0);

$email_list = trim($HTTP_POST_VARS[email_list]);
$content    = trim($HTTP_POST_VARS[content]);
$subject    = trim($HTTP_POST_VARS[subject]);
$send_name  = trim($HTTP_POST_VARS[send_name]);
$send_email = trim($HTTP_POST_VARS[send_email]);
$test_email = trim($HTTP_POST_VARS[test_email]);

if ($test_email != "")
{
	$list[0] = $test_email;
}
else if ($email_list != "")
{
	$list = explode("\n", $email_list);
}

for ($i=0; $i<count($list); $i++)
{
    mailer($send_name, $send_email, $list[$i], $subject, $content, 1);
    echo "<p style='font-size:10pt;'>$list[$i] 님께 메일 보내는 중";
    flush();
    // 10통씩 보내고 몇초간 쉰다.
    if ((($i % 10) == 0) && ($i != 0))
        sleep(1);
}

echo "<p align=center style='font-size:10pt;'><BR><b><big>OK</big>... *^^*</b><BR><BR>모든 메일을 정상적으로 발송하였습니다.</p><BR><BR>";


include"../down.php";
?>
