<?php
/**
 * 표준 견적서 - 이메일 발송
 *
 * 동일한 layout.php를 사용하여 이메일 HTML 본문 생성
 * 이메일 클라이언트(Gmail, Outlook, 네이버메일) 호환
 *
 * 사용법:
 *   mail.php?id=123&preview=1    - 이메일 미리보기 (발송 안 함)
 *   mail.php?id=123&send=1       - 실제 발송
 *   mail.php?id=123&to=email@ex.com&send=1 - 특정 주소로 발송
 */

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/layout.php';

// === 파라미터 처리 ===
$quoteId = intval($_GET['id'] ?? 0);
$useSample = isset($_GET['sample']);
$preview = isset($_GET['preview']);
$send = isset($_GET['send']);
$toEmail = trim($_GET['to'] ?? '');

// === 데이터 로드 ===
if ($useSample || $quoteId <= 0) {
    $data = loadQuoteDataPackage(null, 0);
} else {
    global $db;
    if (!$db) {
        require_once __DIR__ . '/../../../db.php';
    }
    $data = loadQuoteDataPackage($db, $quoteId);
}

$quote    = $data['quote'];
$items    = $data['items'];
$supplier = $data['supplier'];

// === 기본 URL (이미지 절대경로 - 이메일 필수) ===
// 실제 운영 시 도메인으로 변경
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'dsp1830.shop');

// === 이메일용 래퍼 HTML ===
$quoteHtml = renderQuoteLayout($quote, $items, $supplier, $baseUrl);

// 이메일 전체 HTML (이메일 클라이언트 호환)
$emailBody = buildEmailHtml($quote, $quoteHtml, $supplier, $baseUrl);

// === 수신자 이메일 결정 ===
if (empty($toEmail)) {
    $toEmail = $quote['customer_email'] ?? '';
}

// === 미리보기 모드 ===
if ($preview || !$send) {
    header('Content-Type: text/html; charset=utf-8');
    echo $emailBody;
    exit;
}

// === 이메일 발송 ===
if ($send) {
    if (empty($toEmail)) {
        die(json_encode(['success' => false, 'message' => '수신자 이메일이 없습니다.']));
    }

    $result = sendQuoteEmail(
        $toEmail,
        $quote,
        $emailBody,
        $supplier
    );

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 이메일용 전체 HTML 빌드
 * 이메일 클라이언트 호환성 최적화
 */
function buildEmailHtml(array $quote, string $quoteHtml, array $supplier, string $baseUrl): string {
    $companyName = htmlspecialchars($supplier['company_name'] ?? '');
    $customerName = htmlspecialchars($quote['customer_name'] ?? '고객');
    $quoteNo = htmlspecialchars($quote['quote_no'] ?? '');
    $phone = htmlspecialchars($supplier['phone'] ?? '');
    $email = htmlspecialchars($supplier['email'] ?? '');

    return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>견적서 - {$quoteNo}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:'Malgun Gothic','맑은 고딕',Arial,sans-serif;">

    <!-- 이메일 외부 테이블 -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4;">
        <tr>
            <td align="center" style="padding:20px 10px;">

                <!-- 메인 컨테이너 -->
                <table role="presentation" width="800" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;border:1px solid #dddddd;">

                    <!-- 헤더 (회사 로고/이름) -->
                    <tr>
                        <td style="background-color:#217346;padding:15px 20px;text-align:center;">
                            <span style="color:#ffffff;font-size:16px;font-weight:bold;">{$companyName}</span>
                        </td>
                    </tr>

                    <!-- 인사말 -->
                    <tr>
                        <td style="padding:25px 30px 15px 30px;font-size:14px;color:#333333;line-height:1.6;">
                            안녕하세요, <strong>{$customerName}</strong>님.<br><br>
                            요청하신 견적서를 보내드립니다.<br>
                            아래 내용을 확인해 주시기 바랍니다.
                        </td>
                    </tr>

                    <!-- 견적서 본문 -->
                    <tr>
                        <td style="padding:10px 20px;">
                            {$quoteHtml}
                        </td>
                    </tr>

                    <!-- 안내문구 -->
                    <tr>
                        <td style="padding:20px 30px;font-size:13px;color:#666666;line-height:1.6;border-top:1px solid #eeeeee;">
                            궁금하신 점이 있으시면 언제든 연락 주세요.<br>
                            감사합니다.
                        </td>
                    </tr>

                    <!-- 푸터 (회사 연락처) -->
                    <tr>
                        <td style="background-color:#f8f8f8;padding:15px 20px;font-size:12px;color:#888888;text-align:center;border-top:1px solid #eeeeee;">
                            <strong>{$companyName}</strong><br>
                            전화: {$phone} | 이메일: {$email}<br>
                            <span style="color:#aaaaaa;">본 메일은 발신 전용입니다.</span>
                        </td>
                    </tr>

                </table>
                <!-- /메인 컨테이너 -->

            </td>
        </tr>
    </table>
    <!-- /이메일 외부 테이블 -->

</body>
</html>
HTML;
}

/**
 * 이메일 발송 함수
 * PHP mail() 또는 PHPMailer 사용
 */
function sendQuoteEmail(string $to, array $quote, string $htmlBody, array $supplier): array {
    $subject = '[견적서] ' . ($quote['quote_no'] ?? '') . ' - ' . ($supplier['company_name'] ?? '');
    $fromEmail = $supplier['email'] ?? 'noreply@dsp1830.shop';
    $fromName = $supplier['company_name'] ?? '두손기획인쇄';

    // === PHPMailer 사용 (권장) ===
    $phpmailerPaths = [
        __DIR__ . '/../../../vendor/autoload.php',
        '/var/www/html/vendor/autoload.php',
    ];

    foreach ($phpmailerPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // SMTP 설정 로드
            $smtpConfig = __DIR__ . '/smtp_config.php';
            if (file_exists($smtpConfig)) {
                $smtp = require $smtpConfig;
                if (!empty($smtp['password'])) {
                    $mail->isSMTP();
                    $mail->Host = $smtp['host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp['username'];
                    $mail->Password = $smtp['password'];
                    $mail->SMTPSecure = $smtp['secure'];
                    $mail->Port = $smtp['port'];
                    $fromEmail = $smtp['from_email'];
                    $fromName = $smtp['from_name'];
                }
            }

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($fromEmail, $fromName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

            $mail->send();

            return ['success' => true, 'message' => '이메일이 발송되었습니다.'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => '발송 실패: ' . $e->getMessage()];
        }
    }

    // === PHP mail() 함수 사용 (fallback) ===
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . mb_encode_mimeheader($fromName, 'UTF-8') . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    $subject = mb_encode_mimeheader($subject, 'UTF-8');

    if (mail($to, $subject, $htmlBody, implode("\r\n", $headers))) {
        return ['success' => true, 'message' => '이메일이 발송되었습니다.'];
    }

    return ['success' => false, 'message' => 'PHP mail() 발송 실패'];
}
