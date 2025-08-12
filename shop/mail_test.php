<?php
include_once('mailer.lib.php');
 
// mailer("보내는 사람 이름", "보내는 사람 메일주소", "받는 사람 메일주소", "제목", "내용", "1");
mailer("$fname", "$fmail", "$email", "$name", "$content", 2);//$fname, $fmail, $to, $subject, $content, $type=0,

//mailer("test", "dsp1830@naver.com", "dsp1830@naver.com", "테스트메일", "잘가야", 1);
?>
