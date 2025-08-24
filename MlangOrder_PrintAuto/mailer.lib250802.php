<?php
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function mailer($fname, $fmail, $to, $subject, $content, $type = 1, $file = [], $cc = "", $bcc = "")
{
    if ($type != 1) {
        $content = nl2br($content);
    }

    try {
        $mail = new PHPMailer(true); // 예외 활성화
        
        // 서버 설정
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = "smtp.naver.com";
        $mail->SMTPAuth = true;
        $mail->Username = "dsp1830";
        $mail->Password = "asd741010*";
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;
        
        // 타임아웃 설정
        $mail->Timeout = 30;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // 발신자 설정
        $mail->CharSet = "UTF-8";
        $mail->setFrom($fmail, $fname);
        
        // 수신자 설정
        $mail->addAddress($to);
        
        if (!empty($cc)) {
            $mail->addCC($cc);
        }

        if (!empty($bcc)) {
            $mail->addBCC($bcc);
        }
        
        // 메일 내용
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $content;

        // 첨부파일
        if (is_array($file)) {
            foreach ($file as $f) {
                if (isset($f['path']) && isset($f['name'])) {
                    $mail->addAttachment($f['path'], $f['name']);
                }
            }
        }

        $result = $mail->send();
        return $result;
        
    } catch (Exception $e) {
        // 로그에 에러 기록 (실제 서비스에서는 로그 파일에 기록)
        error_log("Mailer Error: " . $e->getMessage());
        return false;
    }
}

function attach_file($filename, $tmp_name)
{
    $dest_file = './tmp/' . basename($tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    return ["name" => $filename, "path" => $dest_file];
}

// 테스트 예제
$fname = "두손기획인쇄";
$fmail = "dsp1830@naver.com";
$to = "받는사람@도메인.com";
$subject = "테스트메일입니다";
$content = "네이버메일이 잘안가네요";
$file = []; // 첨부 파일이 있으면 여기에 추가

if (mailer($fname, $fmail, $to, $subject, $content, 1, $file)) {
    echo "메일 전송 성공";
} else {
    echo "메일 전송 실패";
}
?>
