<?php
/**
 * 네이버 SMTP 연결 테스트
 * 기존 mlangorder_printauto의 PHPMailer 설정 사용
 */

error_reporting(E_ALL & ~E_DEPRECATED);
require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/html; charset=utf-8');

echo "<h2>네이버 SMTP 연결 테스트</h2>";
echo "<pre style='background:#f5f5f5; padding:15px; font-size:12px;'>";

$mail = new PHPMailer(true);
$mail->SMTPDebug = SMTP::DEBUG_SERVER;
$mail->Debugoutput = function($str, $level) {
    echo htmlspecialchars($str);
};

$mail->isSMTP();
$mail->Host = 'smtp.naver.com';
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'ssl';
$mail->Username = 'dsp1830';
$mail->Password = 'MC8T8Z83B149';
$mail->CharSet = 'UTF-8';

try {
    $mail->smtpConnect();
    echo "\n</pre>";
    echo "<div style='background:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-top:10px;'>";
    echo "<strong>✅ SMTP 연결 성공!</strong><br>";
    echo "이메일 발송이 가능합니다.";
    echo "</div>";
    $mail->smtpClose();
} catch (Exception $e) {
    echo "\n</pre>";
    echo "<div style='background:#f8d7da; color:#721c24; padding:15px; border-radius:5px; margin-top:10px;'>";
    echo "<strong>❌ 연결 실패</strong><br>";
    echo "오류: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>
