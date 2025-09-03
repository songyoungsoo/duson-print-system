<?php
$M123 = "..";
include "../top.php"; 

include "lib.inc.php";
include "../../db.php"; // 데이터베이스 연결 설정 포함

set_time_limit(0);

$email_list = trim($_POST['email_list'] ?? '');
$content    = trim($_POST['content'] ?? '');
$subject    = trim($_POST['subject'] ?? '');
$send_name  = trim($_POST['send_name'] ?? '');
$send_email = trim($_POST['send_email'] ?? '');
$test_email = trim($_POST['test_email'] ?? '');

$list = [];
if ($test_email != "") {
    $list[] = $test_email;
} else if ($email_list != "") {
    $list = explode("\n", $email_list);
}

$i = 0; // 루프 카운터 초기화

foreach ($list as $email) {
    mailer($send_name, $send_email, $email, $subject, $content, 1);
    echo "<p style='font-size:10pt;'>$email 에게 메일 전송 완료</p>";
    flush();
    // 10개의 메일을 전송한 후 잠시 대기
    if ((($i % 10) == 0) && ($i != 0)) {
        sleep(1);
    }
    $i++; // 루프 카운터 증가
}

echo "<p align='center' style='font-size:10pt;'><br><b><big>OK</big>... *^^*</b><br><br>모든 메일 전송을 완료했습니다.</p><br><br>";

include "../down.php";
?>
