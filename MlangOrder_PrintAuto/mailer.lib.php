<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require_once('PHPMailer/PHPMailer.php');
require_once('PHPMailer/SMTP.php');
require_once('PHPMailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 네이버 메일 전송
// 메일 -> 환경설정 -> POP3/IMAP 설정 -> POP3/SMTP & IMAP/SMTP 중에 IMAP/SMTP 사용

// 메일 보내기 (파일 여러개 첨부 가능)
// mailer("보내는 사람 이름", "보내는 사람 메일주소", "받는 사람 메일주소", "제목", "내용", "type");
// type : text=0, html=1, text+html=2

// ex) mailer("kOO", "zzxp@naver.com", "zzxp@naver.com", "제목 테스트", "내용 테스트", 1);
//$fname = "두손기획인쇄";
//$fmail = "dsp1830@naver.com";
//$to = "dsp1830@naver.com";
//$subject = "테스트메일입니다";
//$content = "네이버메일이 잘안가네요";
//$file = "a.jpg";

// 기본값들 (함수에서 파라미터로 받으므로 주석 처리)
// $fname = "duson";//두손기획인쇄
// $fmail = "dsp1830@naver.com";
// $to = $email;
// $subject = $subject;
// $content = $body;
//$file = "";
function mailer($fname, $fmail, $to, $subject, $content, $type=1, $file, $cc="", $bcc="")
{
    if ($type != 1)
        $content = nl2br($content);

    $mail = new PHPMailer(); // defaults to using php "mail()"
	
	$mail->IsSMTP(); 
	$mail->SMTPDebug = 0; 
	$mail->SMTPSecure = "ssl";
	$mail->SMTPAuth = true; 

	$mail->Host = "smtp.naver.com"; 
	$mail->Port = 465; 
	$mail->Username = "dsp1830";
	$mail->Password = "du701018*"; 
//asd741010*
    $mail->CharSet = "UTF-8";
    $mail->From = $fmail;
    $mail->FromName = $fname;
    $mail->Subject = $subject;
    $mail->AltBody = ""; // optional, comment out and test
    $mail->msgHTML($content);
    $mail->addAddress($to);
    if ($cc)
        $mail->addCC($cc);
    if ($bcc)
        $mail->addBCC($bcc);

    if ($file != "") {
       foreach ($file as $f) {
           $mail->addAttachment($f['path'], $f['name']);
      }
    }
    $result = $mail->send();
    if (!$result) {
        error_log("메일 발송 실패: " . $mail->ErrorInfo);
        echo "메일 발송 실패: " . $mail->ErrorInfo . "<br>";
    } else {
        error_log("메일 발송 성공");
        echo "메일 발송 성공<br>";
    }
    return $result;
}

// 파일을 첨부함
function attach_file($filename, $tmp_name)
{
    // 서버에 업로드 되는 파일은 확장자를 주지 않는다. (보안 취약점)
    $dest_file = './tmp/'.str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    $tmpfile = array("name" => $filename, "path" => $dest_file);
    return $tmpfile;
}
?>
