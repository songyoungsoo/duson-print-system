<?php
require 'PHPMailer/PHPMailerAutoload.php';

function mailer($fname, $fmail, $to, $subject, $content, $type = 1, $file = [], $cc = "", $bcc = "")
{
    if ($type != 1) {
        $content = nl2br($content);
    }

    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->SMTPSecure = "ssl";
    $mail->SMTPAuth = true;
    $mail->Host = "smtp.naver.com";
    $mail->Port = 465;
    $mail->Username = "dsp1830";
    $mail->Password = "asd741010*";

    $mail->CharSet = "UTF-8";
    $mail->setFrom($fmail, $fname);
    $mail->Subject = $subject;
    $mail->msgHTML($content);
    $mail->addAddress($to);

    if (!empty($cc)) {
        $mail->addCC($cc);
    }

    if (!empty($bcc)) {
        $mail->addBCC($bcc);
    }

    foreach ($file as $f) {
        $mail->addAttachment($f['path'], $f['name']);
    }

    return $mail->send();
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
