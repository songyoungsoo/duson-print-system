<?php
include_once('PHPMailer/PHPMailer.php');
include_once('PHPMailer/SMTP.php');
include_once('PHPMailer/Exception.php');

// 네이버 메일 전송
// 메일 -> 환경설정 -> POP3/IMAP 설정 -> POP3/SMTP & IMAP/SMTP 중에 IMAP/SMTP 사용

// 메일 보내기 (파일 여러개 첨부 가능)
// mailer("보내는 사람 이름", "보내는 사람 메일주소", "받는 사람 메일주소", "제목", "내용", "type", "파일", "cc", "bcc");
// type : text=0, html=1, text+html=2

// ex) mailer("kOO", "zzxp@naver.com", "zzxp@naver.com", "제목 테스트", "내용 테스트", 1);
//$fname = "두손기획인쇄";
//$fmail = "dsp1830@naver.com";
//$to = "dsp1830@naver.com";
//$subject = "테스트메일입니다";
//$content = "네이버메일이 잘안가네요";
//$file = "a.jpg";

$fname = "duson";//두손기획인쇄
$fmail = "dsp1830@naver.com";
$to = $email;
$subject = $subject;
$content = $body;
//$file = "";

function mailer($fname, $fmail, $to, $subject, $content, $type=1, $file="", $cc="", $bcc="")
{
    if ($type != 1) {
        $content = nl2br($content);
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = 0; // 0이면 디버그 모드 비활성화, 1이면 디버그 모드 활성화
    $mail->SMTPSecure = "ssl"; // SSL을 사용
    $mail->SMTPAuth = true; // SMTP 인증 사용

    $mail->Host = "smtp.naver.com"; // 네이버 SMTP 서버
    $mail->Port = 465; // SMTP 포트 번호
    $mail->Username = "dsp1830"; // 네이버 SMTP 계정 아이디
    $mail->Password = "asd741010*"; // 네이버 SMTP 계정 비밀번호

    $mail->CharSet = "UTF-8";
    $mail->setFrom($fmail, $fname);
    $mail->addAddress($to);
    if ($cc != "") {
        $mail->addCC($cc);
    }
    if ($bcc != "") {
        $mail->addBCC($bcc);
    }
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $content;

    if ($file != "") {
        foreach ($file as $f) {
            $mail->addAttachment($f['path'], $f['name']);
        }
    }

    if (!$mail->send()) {
        return false;
    } else {
        return true;
    }
}

// 파일을 첨부함
function attach_file($filename, $tmp_name)
{
    // 서버에 업로드 되는 파일은 확장자를 주지 않는다. (보안 취약점)
    $dest_file = './tmp/' . str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    $tmpfile = array("name" => $filename, "path" => $dest_file);
    return $tmpfile;
}
?>