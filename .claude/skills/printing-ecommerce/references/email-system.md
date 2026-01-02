# 이메일 시스템

## PHPMailer + 네이버 SMTP

### 설치
```bash
composer require phpmailer/phpmailer
```

### 설정 파일 (config/mail.php)
```php
<?php
define('SMTP_HOST', 'smtp.naver.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_naver_id@naver.com');
define('SMTP_PASS', 'your_password');  // 또는 앱 비밀번호
define('SMTP_FROM_EMAIL', 'your_naver_id@naver.com');
define('SMTP_FROM_NAME', '두손기획인쇄');
```

### 기본 발송 함수 (inc/mail.php)
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body, $attachments = []) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP 설정
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // 발신자/수신자
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // 내용
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = wrapEmailTemplate($body);
        $mail->AltBody = strip_tags($body);
        
        // 첨부파일
        foreach ($attachments as $file) {
            if (file_exists($file['path'])) {
                $mail->addAttachment($file['path'], $file['name'] ?? basename($file['path']));
            }
        }
        
        $mail->send();
        logEmail($to, $subject, 'success');
        return true;
        
    } catch (Exception $e) {
        logEmail($to, $subject, 'failed', $mail->ErrorInfo);
        return false;
    }
}

// 이메일 템플릿 래퍼
function wrapEmailTemplate($content) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Malgun Gothic', sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px 20px; background: #fff; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
            th { background: #f9f9f9; }
            .btn { display: inline-block; padding: 12px 30px; background: #d00; color: white; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>두손기획인쇄</h1>
            </div>
            <div class='content'>
                {$content}
            </div>
            <div class='footer'>
                <p>두손기획인쇄 | 대표: 홍길동 | 사업자등록번호: 000-00-00000</p>
                <p>주소: 서울시 OO구 OO로 00</p>
                <p>고객센터: 02-0000-0000 | 이메일: info@dsp1830.shop</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// 이메일 로그
function logEmail($to, $subject, $status, $error = null) {
    global $pdo;
    $sql = "INSERT INTO email_logs (recipient, subject, status, error_msg, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $pdo->prepare($sql)->execute([$to, $subject, $status, $error]);
}
```

## 이메일 종류별 템플릿

### 1. 주문 확인
```php
function sendOrderConfirmEmail($order_no) {
    $order = getOrderInfo($order_no);
    $items = getOrderItems($order_no);
    
    $items_html = '';
    foreach ($items as $item) {
        $items_html .= "<tr>
            <td>{$item['product_name']}</td>
            <td>{$item['quantity_display']}</td>
            <td style='text-align:right'>" . number_format($item['price']) . "원</td>
        </tr>";
    }
    
    $body = "
    <h2>주문이 완료되었습니다</h2>
    <p>주문해 주셔서 감사합니다.</p>
    
    <h3>주문 정보</h3>
    <table>
        <tr><th>주문번호</th><td>{$order['order_no']}</td></tr>
        <tr><th>주문일시</th><td>{$order['created_at']}</td></tr>
        <tr><th>결제금액</th><td><strong>" . number_format($order['total_price']) . "원</strong></td></tr>
    </table>
    
    <h3>주문 상품</h3>
    <table>
        <tr><th>상품명</th><th>수량</th><th>금액</th></tr>
        {$items_html}
    </table>
    
    <h3>배송지 정보</h3>
    <table>
        <tr><th>수령인</th><td>{$order['receiver_name']}</td></tr>
        <tr><th>연락처</th><td>{$order['receiver_phone']}</td></tr>
        <tr><th>주소</th><td>{$order['address']} {$order['address_detail']}</td></tr>
    </table>
    
    <p style='margin-top:30px'>
        <a href='https://dsp1830.shop/member/order_detail.php?no={$order_no}' class='btn'>주문 상세보기</a>
    </p>
    ";
    
    return sendEmail($order['orderer_email'], "[두손기획인쇄] 주문이 완료되었습니다 (#{$order_no})", $body);
}
```

### 2. 교정 요청
```php
function sendProofRequestEmail($order_no) {
    $order = getOrderInfo($order_no);
    $token = generateProofToken($order_no);
    $proof_url = "https://dsp1830.shop/sub/checkboard.php?no={$order_no}&token={$token}";
    
    $body = "
    <h2>시안 확인 요청</h2>
    <p>{$order['orderer_name']}님, 인쇄 시안이 준비되었습니다.</p>
    
    <p>아래 버튼을 클릭하여 시안을 확인하시고,<br>
    이상이 없으면 <strong>승인</strong>을, 수정이 필요하면 <strong>수정요청</strong>을 해주세요.</p>
    
    <p style='margin:30px 0'>
        <a href='{$proof_url}' class='btn'>시안 확인하기</a>
    </p>
    
    <p style='color:#d00'>※ 승인 후에는 수정이 어려우니 꼼꼼히 확인해주세요.</p>
    
    <table>
        <tr><th>주문번호</th><td>{$order_no}</td></tr>
    </table>
    ";
    
    return sendEmail($order['orderer_email'], "[두손기획인쇄] 시안을 확인해주세요", $body);
}
```

### 3. 발송 알림
```php
function sendShippingNoticeEmail($order_no) {
    $order = getOrderInfo($order_no);
    $tracking_url = getTrackingUrl($order['courier_code'], $order['tracking_no']);
    
    $body = "
    <h2>상품이 발송되었습니다</h2>
    <p>{$order['orderer_name']}님, 주문하신 상품이 발송되었습니다.</p>
    
    <h3>배송 정보</h3>
    <table>
        <tr><th>주문번호</th><td>{$order_no}</td></tr>
        <tr><th>택배사</th><td>{$order['courier']}</td></tr>
        <tr><th>운송장번호</th><td>{$order['tracking_no']}</td></tr>
    </table>
    
    <p style='margin:30px 0'>
        <a href='{$tracking_url}' class='btn' target='_blank'>배송 조회하기</a>
    </p>
    
    <p>배송은 발송 후 1~2일(영업일 기준) 소요됩니다.<br>
    도서산간 지역은 1~2일 추가 소요될 수 있습니다.</p>
    ";
    
    return sendEmail($order['orderer_email'], "[두손기획인쇄] 상품이 발송되었습니다", $body);
}
```

### 4. 비밀번호 재설정
```php
function sendPasswordResetEmail($email, $reset_url) {
    $body = "
    <h2>비밀번호 재설정</h2>
    <p>비밀번호 재설정을 요청하셨습니다.</p>
    <p>아래 버튼을 클릭하여 새 비밀번호를 설정해주세요.</p>
    
    <p style='margin:30px 0'>
        <a href='{$reset_url}' class='btn'>비밀번호 재설정</a>
    </p>
    
    <p style='color:#666; font-size:12px'>
        ※ 이 링크는 1시간 동안 유효합니다.<br>
        ※ 본인이 요청하지 않은 경우 이 메일을 무시하세요.
    </p>
    ";
    
    return sendEmail($email, "[두손기획인쇄] 비밀번호 재설정", $body);
}
```

## 이메일 큐 시스템 (대량 발송용)

### 테이블
```sql
CREATE TABLE email_queue (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    attachments TEXT,           -- JSON
    status VARCHAR(20) DEFAULT 'pending',  -- pending, sent, failed
    attempts INT DEFAULT 0,
    error_msg TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME,
    
    INDEX idx_status (status)
);
```

### 큐에 추가
```php
function queueEmail($to, $subject, $body, $attachments = []) {
    global $pdo;
    $sql = "INSERT INTO email_queue (recipient, subject, body, attachments, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $pdo->prepare($sql)->execute([
        $to, $subject, $body, json_encode($attachments)
    ]);
}
```

### 큐 처리 (크론)
```php
// cron/process_email_queue.php
// 1분마다 실행: * * * * * php /path/to/process_email_queue.php

require_once __DIR__ . '/../inc/mail.php';

$sql = "SELECT * FROM email_queue 
        WHERE status = 'pending' AND attempts < 3 
        ORDER BY created_at ASC LIMIT 10";
$emails = $pdo->query($sql)->fetchAll();

foreach ($emails as $email) {
    $attachments = json_decode($email['attachments'], true) ?: [];
    
    $result = sendEmail($email['recipient'], $email['subject'], $email['body'], $attachments);
    
    if ($result) {
        $sql = "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE idx = ?";
    } else {
        $sql = "UPDATE email_queue SET attempts = attempts + 1, 
                status = CASE WHEN attempts >= 2 THEN 'failed' ELSE 'pending' END
                WHERE idx = ?";
    }
    $pdo->prepare($sql)->execute([$email['idx']]);
    
    // 초당 발송 제한 (네이버 제한 고려)
    sleep(1);
}
```

## 네이버 SMTP 제한 사항

- 일일 발송 제한: 500통 (개인), 2000통 (비즈니스)
- 초당 발송 제한: 1~2통
- 첨부파일: 25MB 이하

## 트러블슈팅

### SMTP 연결 실패
```php
// 디버그 모드
$mail->SMTPDebug = 2;  // 개발 환경에서만
```

### SSL 인증서 오류
```php
$mail->SMTPOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
];
```
