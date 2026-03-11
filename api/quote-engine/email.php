<?php
/**
 * 견적엔진 API — 이메일 발송
 * POST /api/quote-engine/email.php
 * Body: { "id": 123, "to": "email@example.com" }
 *
 * Response: { "success": true/false, "message": "..." }
 */
header('Content-Type: application/json; charset=utf-8');
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST only']);
    exit;
}

require_once __DIR__ . '/../../db.php';
mysqli_set_charset($db, 'utf8mb4');
require_once __DIR__ . '/../../includes/quote-engine/QuoteEngine.php';

$input   = json_decode(file_get_contents('php://input'), true);
$quoteId = intval($input['id'] ?? 0);
$toEmail = trim($input['to'] ?? '');

if (!$quoteId) {
    echo json_encode(['success' => false, 'error' => 'Missing quote ID']);
    exit;
}

// ── Load quote ──
$engine = new QE_QuoteEngine($db);
$quote  = $engine->getQuote($quoteId);
if (!$quote) {
    echo json_encode(['success' => false, 'error' => 'Quote not found']);
    exit;
}

// ── Determine email recipient ──
if (empty($toEmail)) {
    $toEmail = $quote['customer_email'] ?? '';
}
if (empty($toEmail)) {
    echo json_encode(['success' => false, 'error' => '수신자 이메일이 없습니다']);
    exit;
}

$items = $quote['items'] ?? [];

// ── Convert QE items to standard layout format ──
$layoutItems = [];
foreach ($items as $item) {
    $layoutItems[] = [
        'product_name'    => $item['product_name'],
        'specification'   => $item['specification'] ?? '',
        'quantity_display' => number_format($item['quantity']),
        'unit'            => $item['unit'] ?? '개',
        'unit_price'      => $item['unit_price'],
        'supply_price'    => $item['supply_price'],
        'notes'           => $item['note'] ?? '',
    ];
}

// ── Build layout-compatible quote data ──
$layoutQuote = [
    'quote_no'         => $quote['quote_no'],
    'quote_date'       => $quote['created_at'],
    'customer_company' => $quote['customer_company'] ?? '',
    'customer_name'    => $quote['customer_name'] ?? '',
    'customer_email'   => $quote['customer_email'] ?? '',
    'validity_days'    => $quote['valid_days'] ?? 7,
];

// ── Supplier info (same as standard system) ──
$supplier = [
    'company_name'   => '두손기획인쇄',
    'business_no'    => '107-06-45106',
    'ceo_name'       => '차경선',
    'address'        => '서울시 영등포구 영등포로 36길9 송호빌딩 1층',
    'phone'          => '02-2632-1830',
    'fax'            => '02-2632-1829',
    'email'          => 'dsp1830@naver.com',
    'account_holder' => '두손기획인쇄 차경선',
    'bank_accounts'  => [
        ['bank_name' => '국민은행', 'account_no' => '999-1688-2384'],
        ['bank_name' => '신한은행', 'account_no' => '110-342-543507'],
        ['bank_name' => '농협',     'account_no' => '301-2632-1830-11'],
    ],
];

// ── Render standard layout HTML ──
require_once __DIR__ . '/../../mlangprintauto/quote/standard/layout.php';

$baseUrl   = 'https://dsp114.com';
$quoteHtml = renderQuoteLayout($layoutQuote, $layoutItems, $supplier, $baseUrl);

// ── Build email HTML wrapper (matches standard/mail.php pattern) ──
$emailBody = qeBuildEmailHtml($quote, $quoteHtml, $supplier);

// ── Determine subject ──
$docLabel = ($quote['doc_type'] === 'transaction') ? '거래명세서' : '견적서';
$subject  = "[{$docLabel}] " . ($quote['quote_no'] ?? '') . ' - ' . ($supplier['company_name'] ?? '');

// ── Send email ──
$result = qeSendEmail($toEmail, $subject, $emailBody, $supplier);

// ── Update status on success ──
if ($result['success']) {
    $engine->updateStatus($quoteId, 'sent');
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;


// ═══════════════════════════════════════════════════════════════════
//  Helper functions
// ═══════════════════════════════════════════════════════════════════

/**
 * Build email HTML wrapper (green header, greeting, quote body, footer)
 * Pattern from standard/mail.php buildEmailHtml()
 */
function qeBuildEmailHtml(array $quote, string $quoteHtml, array $supplier): string
{
    $companyName  = htmlspecialchars($supplier['company_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $customerName = htmlspecialchars($quote['customer_name'] ?? '고객', ENT_QUOTES, 'UTF-8');
    $quoteNo      = htmlspecialchars($quote['quote_no'] ?? '', ENT_QUOTES, 'UTF-8');
    $phone        = htmlspecialchars($supplier['phone'] ?? '', ENT_QUOTES, 'UTF-8');
    $email        = htmlspecialchars($supplier['email'] ?? '', ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>견적서 - {$quoteNo}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td { font-family: Arial, sans-serif; }
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
 * Send email via PHPMailer (with SMTP config) or PHP mail() fallback
 * Pattern from standard/mail.php sendQuoteEmail()
 */
function qeSendEmail(string $to, string $subject, string $htmlBody, array $supplier): array
{
    $fromEmail = $supplier['email'] ?? 'noreply@dsp114.com';
    $fromName  = $supplier['company_name'] ?? '두손기획인쇄';

    // ── Composer autoload ──
    $autoloadPaths = [
        __DIR__ . '/../../vendor/autoload.php',
        '/var/www/html/vendor/autoload.php',
    ];
    foreach ($autoloadPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }

    // ── PHPMailer (preferred) ──
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // SMTP config (same location as standard system)
            $smtpConfig = __DIR__ . '/../../mlangprintauto/quote/standard/smtp_config.php';
            if (file_exists($smtpConfig)) {
                $smtp = require $smtpConfig;
                if (!empty($smtp['password'])) {
                    $mail->isSMTP();
                    $mail->Host       = $smtp['host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtp['username'];
                    $mail->Password   = $smtp['password'];
                    $mail->SMTPSecure = $smtp['secure'];
                    $mail->Port       = $smtp['port'];
                    $fromEmail = $smtp['from_email'];
                    $fromName  = $smtp['from_name'];
                }
            }

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($fromEmail, $fromName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

            $mail->send();

            return ['success' => true, 'message' => '이메일이 발송되었습니다.'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => '발송 실패: ' . $e->getMessage()];
        }
    }

    // ── PHP mail() fallback ──
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . mb_encode_mimeheader($fromName, 'UTF-8') . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8');

    if (mail($to, $encodedSubject, $htmlBody, implode("\r\n", $headers))) {
        return ['success' => true, 'message' => '이메일이 발송되었습니다.'];
    }

    return ['success' => false, 'message' => 'PHP mail() 발송 실패'];
}
