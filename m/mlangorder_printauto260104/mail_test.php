<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>메일 전송 테스트</h2>";
echo "<pre>";

require_once('mailer.lib.php');

$fname = "두손기획인쇄";
$fmail = "dsp1830@naver.com";
$to = "dsp1830@naver.com";  // 테스트용으로 같은 메일로 발송
$subject = "[테스트] 메일 발송 테스트 - " . date('Y-m-d H:i:s');
$content = "이 메일은 새 비밀번호(2CP3P5BTS83Y)로 SMTP 연결 테스트입니다.<br><br>발송 시간: " . date('Y-m-d H:i:s');
$type = 1; // HTML
$file = ""; // 첨부파일 없음

echo "발송 정보:\n";
echo "- 보내는 사람: {$fname} <{$fmail}>\n";
echo "- 받는 사람: {$to}\n";
echo "- 제목: {$subject}\n";
echo "- 내용: {$content}\n\n";

echo "메일 발송 시도 중...\n\n";

$result = mailer($fname, $fmail, $to, $subject, $content, $type, $file);

echo "\n결과: " . ($result ? "성공" : "실패") . "\n";
echo "</pre>";
?>
