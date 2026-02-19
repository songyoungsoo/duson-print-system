<?php
/**
 * English Quote Request API
 * Receives JSON POST from /en/ landing page quote form
 * Sends email notification to admin (dsp1830@naver.com)
 */
header('Content-Type: application/json; charset=utf-8');

function jsonOut($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(false, 'POST requests only.');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonOut(false, 'Invalid request data.');
}

// Validate required fields
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$product = trim($input['product'] ?? '');
$quantity = trim($input['quantity'] ?? '');
$specs = trim($input['specs'] ?? '');
$notes = trim($input['notes'] ?? '');

if (empty($name)) jsonOut(false, 'Please enter your name.');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) jsonOut(false, 'Please enter a valid email address.');
if (empty($phone)) jsonOut(false, 'Please enter your phone number.');
if (empty($product)) jsonOut(false, 'Please select a product.');
if (empty($quantity)) jsonOut(false, 'Please enter the quantity.');

// Sanitize
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$product = htmlspecialchars($product, ENT_QUOTES, 'UTF-8');
$quantity = htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8');
$specs = htmlspecialchars($specs, ENT_QUOTES, 'UTF-8');
$notes = htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');

// Product name mapping
$productNames = [
    'stickers' => 'Stickers & Labels',
    'flyers' => 'Flyers & Leaflets',
    'business-cards' => 'Business Cards',
    'envelopes' => 'Envelopes',
    'catalogs' => 'Catalogs & Booklets',
    'posters' => 'Posters',
    'ncr-forms' => 'NCR Forms',
    'gift-vouchers' => 'Gift Vouchers',
    'magnetic-stickers' => 'Magnetic Stickers',
    'other' => 'Other / Multiple',
];
$productLabel = $productNames[$product] ?? $product;

$timestamp = date('Y-m-d H:i:s');

// Build admin notification email
$adminEmail = 'dsp1830@naver.com';
$adminSubject = "[EN Quote] {$productLabel} - {$name}";
$adminBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1a1a2e; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #1E4E79; color: white; padding: 20px 24px; border-radius: 12px 12px 0 0;">
        <h2 style="margin: 0; font-size: 18px;">🌐 New International Quote Request</h2>
        <p style="margin: 6px 0 0; font-size: 13px; opacity: 0.8;">From English landing page (dsp114.co.kr/en/)</p>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: none; padding: 24px; border-radius: 0 0 12px 12px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; width: 120px; font-size: 14px;">Name</td><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 14px;">{$name}</td></tr>
            <tr><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px;">Email</td><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px;"><a href="mailto:{$email}" style="color: #1E4E79;">{$email}</a></td></tr>
            <tr><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px;">Phone</td><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px;">{$phone}</td></tr>
            <tr><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px;">Product</td><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 14px;">{$productLabel}</td></tr>
            <tr><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px;">Quantity</td><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px;">{$quantity}</td></tr>
            <tr><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px;">Specs</td><td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px;">{$specs}</td></tr>
            <tr><td style="padding: 10px 0; color: #64748b; font-size: 14px; vertical-align: top;">Notes</td><td style="padding: 10px 0; font-size: 14px;">{$notes}</td></tr>
        </table>
        <div style="margin-top: 20px; padding: 12px 16px; background: #f8fafc; border-radius: 8px; font-size: 13px; color: #64748b;">
            Received: {$timestamp} KST<br>
            Reply to: <a href="mailto:{$email}" style="color: #1E4E79;">{$email}</a>
        </div>
    </div>
</body>
</html>
HTML;

// Customer confirmation email
$customerSubject = "Quote Request Received - Duson Print";
$customerBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1a1a2e; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #1E4E79; color: white; padding: 24px; border-radius: 12px 12px 0 0; text-align: center;">
        <h2 style="margin: 0; font-size: 20px;">Thank You for Your Inquiry</h2>
        <p style="margin: 8px 0 0; font-size: 14px; opacity: 0.8;">We've received your quote request</p>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: none; padding: 24px; border-radius: 0 0 12px 12px;">
        <p style="font-size: 15px; line-height: 1.7; margin-bottom: 16px;">Dear {$name},</p>
        <p style="font-size: 15px; line-height: 1.7; margin-bottom: 16px;">Thank you for reaching out to Duson Print. We've received your quote request for <strong>{$productLabel}</strong> and our team will review it promptly.</p>
        <p style="font-size: 15px; line-height: 1.7; margin-bottom: 16px;">You can expect a detailed quote within <strong>24 hours</strong> (during business days).</p>
        <div style="background: #f8fafc; border-radius: 10px; padding: 20px; margin: 20px 0;">
            <div style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">Your Request Summary</div>
            <div style="font-size: 14px; line-height: 2;">
                <strong>Product:</strong> {$productLabel}<br>
                <strong>Quantity:</strong> {$quantity}<br>
                <strong>Specs:</strong> {$specs}<br>
            </div>
        </div>
        <p style="font-size: 15px; line-height: 1.7;">If you have any urgent questions, please don't hesitate to contact us:</p>
        <div style="margin: 16px 0; font-size: 14px; line-height: 1.8;">
            📞 <a href="tel:+82-2-2632-1830" style="color: #1E4E79; text-decoration: none;">+82-2-2632-1830</a><br>
            📧 <a href="mailto:dsp1830@naver.com" style="color: #1E4E79; text-decoration: none;">dsp1830@naver.com</a>
        </div>
        <p style="font-size: 14px; color: #64748b; margin-top: 24px;">Best regards,<br><strong>Duson Planning Print</strong><br>Seoul, South Korea</p>
    </div>
</body>
</html>
HTML;

// Send emails using mailer.lib.php
$mailerPath = dirname(__DIR__) . '/mlangorder_printauto/mailer.lib.php';
$emailSent = false;

if (file_exists($mailerPath)) {
    require_once $mailerPath;

    // Buffer output — mailer.lib.php echoes debug text ("메일 발송 성공")
    ob_start();

    // Send admin notification
    $adminResult = mailer(
        'Duson Print EN',
        'dsp1830@naver.com',
        $adminEmail,
        $adminSubject,
        $adminBody,
        1,
        ""
    );

    // Send customer confirmation
    $customerResult = mailer(
        'Duson Print',
        'dsp1830@naver.com',
        $email,
        $customerSubject,
        $customerBody,
        1,
        ""
    );

    ob_end_clean(); // Discard mailer debug output

    $emailSent = $adminResult;
} else {
    // Fallback: PHP mail()
    $headers = "From: dsp1830@naver.com\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $emailSent = mail($adminEmail, $adminSubject, $adminBody, $headers);
}

// Log the request regardless of email result
$logDir = dirname(__DIR__) . '/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logEntry = date('Y-m-d H:i:s') . " | EN_QUOTE | {$name} | {$email} | {$phone} | {$productLabel} | {$quantity} | email:" . ($emailSent ? 'OK' : 'FAIL') . "\n";
@file_put_contents($logDir . '/en_quote_log.txt', $logEntry, FILE_APPEND);

if ($emailSent) {
    jsonOut(true, "Your quote request has been sent successfully! We'll respond within 24 hours.");
} else {
    // Even if email fails, the request is logged
    jsonOut(true, "Your request has been received. We'll get back to you at {$email} within 24 hours.");
}
