<?php
/**
 * NotificationService
 *
 * 알림 통합 서비스
 * - 카카오 알림톡
 * - SMS (CoolSMS)
 * - 이메일
 *
 * @package DusonPrint
 * @since 2026-01-13
 */

class NotificationService
{
    private $db;
    private $config;
    private $lastError;

    // 알림 타입 상수
    const TYPE_KAKAO_ALIMTALK = 'kakao_alimtalk';
    const TYPE_SMS = 'sms';
    const TYPE_EMAIL = 'email';

    // 알림 상태
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    // 주문 관련 템플릿 코드
    const TEMPLATE_ORDER_CONFIRMED = 'ORDER_CONFIRMED';
    const TEMPLATE_PAYMENT_COMPLETE = 'PAYMENT_COMPLETE';
    const TEMPLATE_PROOF_REQUEST = 'PROOF_REQUEST';
    const TEMPLATE_PROOF_APPROVED = 'PROOF_APPROVED';
    const TEMPLATE_PRODUCTION_START = 'PRODUCTION_START';
    const TEMPLATE_SHIPPING_START = 'SHIPPING_START';
    const TEMPLATE_DELIVERY_COMPLETE = 'DELIVERY_COMPLETE';

    public function __construct($db)
    {
        $this->db = $db;
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $this->config = [
            'kakao' => [
                'api_key' => getenv('KAKAO_ALIMTALK_API_KEY') ?: '',
                'user_id' => getenv('KAKAO_ALIMTALK_USER_ID') ?: '',
                'sender_key' => getenv('KAKAO_ALIMTALK_SENDER_KEY') ?: '',
                'api_url' => 'https://alimtalk-api.bizmsg.kr/v2/sender/send'
            ],
            'coolsms' => [
                'api_key' => getenv('COOLSMS_API_KEY') ?: '',
                'api_secret' => getenv('COOLSMS_API_SECRET') ?: '',
                'sender' => getenv('COOLSMS_SENDER') ?: '',
                'api_url' => 'https://api.coolsms.co.kr/messages/v4/send-many'
            ],
            'email' => [
                'from' => getenv('EMAIL_FROM') ?: 'noreply@dsp1830.shop',
                'from_name' => '두손기획 인쇄',
                'smtp_host' => getenv('SMTP_HOST') ?: '',
                'smtp_port' => getenv('SMTP_PORT') ?: 587,
                'smtp_user' => getenv('SMTP_USER') ?: '',
                'smtp_pass' => getenv('SMTP_PASS') ?: ''
            ],
            'company' => [
                'name' => '두손기획',
                'phone' => '02-1234-5678',
                'url' => 'https://dsp1830.shop'
            ]
        ];
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function sendOrderConfirmed($orderId, $phone, $customerName, $orderSummary)
    {
        $message = $this->buildOrderConfirmedMessage($orderId, $customerName, $orderSummary);
        return $this->send([
            'type' => self::TYPE_KAKAO_ALIMTALK,
            'template' => self::TEMPLATE_ORDER_CONFIRMED,
            'phone' => $phone,
            'message' => $message,
            'order_id' => $orderId
        ]);
    }

    public function sendPaymentComplete($orderId, $phone, $customerName, $amount, $paymentMethod)
    {
        $message = $this->buildPaymentCompleteMessage($orderId, $customerName, $amount, $paymentMethod);
        return $this->send([
            'type' => self::TYPE_KAKAO_ALIMTALK,
            'template' => self::TEMPLATE_PAYMENT_COMPLETE,
            'phone' => $phone,
            'message' => $message,
            'order_id' => $orderId
        ]);
    }

    public function sendProofRequest($orderId, $phone, $customerName, $proofUrl)
    {
        $message = $this->buildProofRequestMessage($orderId, $customerName, $proofUrl);
        return $this->send([
            'type' => self::TYPE_KAKAO_ALIMTALK,
            'template' => self::TEMPLATE_PROOF_REQUEST,
            'phone' => $phone,
            'message' => $message,
            'order_id' => $orderId,
            'buttons' => [
                ['type' => 'WL', 'name' => '교정 확인하기', 'linkMobile' => $proofUrl, 'linkPc' => $proofUrl]
            ]
        ]);
    }

    public function sendShippingStart($orderId, $phone, $customerName, $courier, $trackingNumber)
    {
        $trackingUrl = $this->getTrackingUrl($courier, $trackingNumber);
        $message = $this->buildShippingStartMessage($orderId, $customerName, $courier, $trackingNumber, $trackingUrl);
        return $this->send([
            'type' => self::TYPE_KAKAO_ALIMTALK,
            'template' => self::TEMPLATE_SHIPPING_START,
            'phone' => $phone,
            'message' => $message,
            'order_id' => $orderId,
            'buttons' => [
                ['type' => 'WL', 'name' => '배송 조회', 'linkMobile' => $trackingUrl, 'linkPc' => $trackingUrl]
            ]
        ]);
    }

    public function send($params)
    {
        $type = $params['type'] ?? self::TYPE_SMS;
        $phone = $params['phone'] ?? '';
        $message = $params['message'] ?? '';

        if (empty($phone) || empty($message)) {
            $this->lastError = '전화번호 또는 메시지가 비어있습니다.';
            return false;
        }

        $phone = $this->normalizePhone($phone);
        $logId = $this->logNotification($params);

        try {
            switch ($type) {
                case self::TYPE_KAKAO_ALIMTALK:
                    $result = $this->sendKakaoAlimtalk($phone, $message, $params);
                    break;
                case self::TYPE_SMS:
                    $result = $this->sendSMS($phone, $message);
                    break;
                case self::TYPE_EMAIL:
                    $result = $this->sendEmail($params);
                    break;
                default:
                    throw new Exception('지원하지 않는 알림 타입: ' . $type);
            }

            $this->updateNotificationLog($logId, self::STATUS_SENT, $result);
            return ['success' => true, 'log_id' => $logId, 'type' => $type];

        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            $this->updateNotificationLog($logId, self::STATUS_FAILED, $e->getMessage());

            if ($type === self::TYPE_KAKAO_ALIMTALK) {
                return $this->fallbackToSMS($phone, $message, $params, $logId);
            }
            return false;
        }
    }

    private function sendKakaoAlimtalk($phone, $message, $params)
    {
        $config = $this->config['kakao'];
        $data = [
            'senderKey' => $config['sender_key'],
            'templateCode' => $params['template'] ?? '',
            'recipientList' => [
                ['recipientNo' => $phone, 'templateParameter' => ['message' => $message]]
            ]
        ];

        if (!empty($params['buttons'])) {
            $data['recipientList'][0]['buttons'] = $params['buttons'];
        }

        $headers = ['Content-Type: application/json', 'userid: ' . $config['user_id']];
        $response = $this->httpRequest($config['api_url'], 'POST', $headers, $data);

        if (!$response || !isset($response['code']) || $response['code'] !== '1000') {
            throw new Exception('알림톡 전송 실패: ' . json_encode($response));
        }
        return $response;
    }

    private function sendSMS($phone, $message)
    {
        $config = $this->config['coolsms'];
        $messageType = strlen($message) > 80 ? 'LMS' : 'SMS';

        $data = [
            'messages' => [
                ['to' => $phone, 'from' => $config['sender'], 'text' => $message, 'type' => $messageType]
            ]
        ];

        $timestamp = time();
        $salt = bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256', $timestamp . $salt, $config['api_secret']);

        $headers = [
            'Content-Type: application/json',
            'Authorization: HMAC-SHA256 apiKey=' . $config['api_key'] . ', date=' . $timestamp . ', salt=' . $salt . ', signature=' . $signature
        ];

        $response = $this->httpRequest($config['api_url'], 'POST', $headers, $data);

        if (!$response || isset($response['errorCode'])) {
            throw new Exception('SMS 전송 실패: ' . json_encode($response));
        }
        return $response;
    }

    private function sendEmail($params)
    {
        $config = $this->config['email'];
        $to = $params['email'] ?? '';
        $subject = $params['subject'] ?? '두손기획 알림';
        $body = $params['message'] ?? '';
        $isHtml = $params['is_html'] ?? false;

        if (empty($to)) {
            throw new Exception('이메일 주소가 비어있습니다.');
        }

        $headers = "From: {$config['from_name']} <{$config['from']}>\r\n";
        $headers .= $isHtml ? "Content-Type: text/html; charset=UTF-8\r\n" : "Content-Type: text/plain; charset=UTF-8\r\n";

        $result = mail($to, $subject, $body, $headers);
        if (!$result) {
            throw new Exception('이메일 전송 실패');
        }
        return ['success' => true];
    }

    private function fallbackToSMS($phone, $message, $params, $originalLogId)
    {
        try {
            $smsMessage = $this->shortenMessageForSMS($message);
            $result = $this->sendSMS($phone, $smsMessage);
            $this->updateNotificationLog($originalLogId, self::STATUS_SENT, ['fallback' => 'sms', 'result' => $result]);
            return ['success' => true, 'log_id' => $originalLogId, 'type' => 'sms_fallback'];
        } catch (Exception $e) {
            $this->lastError = 'SMS 폴백도 실패: ' . $e->getMessage();
            return false;
        }
    }

    private function buildOrderConfirmedMessage($orderId, $customerName, $orderSummary)
    {
        $company = $this->config['company'];
        return "[{$company['name']}] 주문 접수 안내\n\n안녕하세요, {$customerName}님.\n주문이 정상적으로 접수되었습니다.\n\n■ 주문번호: {$orderId}\n■ 주문내역: {$orderSummary}\n\n결제 확인 후 제작이 진행됩니다.\n문의사항은 {$company['phone']}로 연락주세요.\n\n감사합니다.";
    }

    private function buildPaymentCompleteMessage($orderId, $customerName, $amount, $paymentMethod)
    {
        $company = $this->config['company'];
        $formattedAmount = number_format($amount);
        return "[{$company['name']}] 결제 완료 안내\n\n안녕하세요, {$customerName}님.\n결제가 정상적으로 완료되었습니다.\n\n■ 주문번호: {$orderId}\n■ 결제금액: {$formattedAmount}원\n■ 결제방법: {$paymentMethod}\n\n디자인 확인 후 제작이 시작됩니다.\n\n감사합니다.";
    }

    private function buildProofRequestMessage($orderId, $customerName, $proofUrl)
    {
        $company = $this->config['company'];
        return "[{$company['name']}] 교정 확인 요청\n\n안녕하세요, {$customerName}님.\n인쇄 교정본이 준비되었습니다.\n\n■ 주문번호: {$orderId}\n\n아래 링크에서 교정본을 확인하시고\n승인 또는 수정 요청을 해주세요.\n\n▶ 교정 확인: {$proofUrl}\n\n48시간 내 응답이 없으면 자동 승인됩니다.\n\n감사합니다.";
    }

    private function buildShippingStartMessage($orderId, $customerName, $courier, $trackingNumber, $trackingUrl)
    {
        $company = $this->config['company'];
        return "[{$company['name']}] 배송 시작 안내\n\n안녕하세요, {$customerName}님.\n주문하신 상품이 발송되었습니다.\n\n■ 주문번호: {$orderId}\n■ 택배사: {$courier}\n■ 운송장번호: {$trackingNumber}\n\n▶ 배송조회: {$trackingUrl}\n\n감사합니다.";
    }

    private function getTrackingUrl($courier, $trackingNumber)
    {
        $baseUrls = [
            'CJ대한통운' => 'https://www.cjlogistics.com/ko/tool/parcel/tracking?gnbInvcNo=',
            '한진택배' => 'https://www.hanjin.com/kor/CMS/DeliveryMgr/WaybillResult.do?mession=&wblnumText2=',
            '롯데택배' => 'https://www.lotteglogis.com/home/reservation/tracking/linkView?InvNo=',
            '로젠택배' => 'https://www.ilogen.com/web/personal/trace/',
            '우체국택배' => 'https://service.epost.go.kr/trace.RetrieveDomRi498.postal?sid1='
        ];
        $baseUrl = $baseUrls[$courier] ?? 'https://tracker.delivery/#/';
        return $baseUrl . $trackingNumber;
    }

    private function shortenMessageForSMS($message)
    {
        $maxLength = 2000;
        $message = preg_replace('/\n{2,}/', "\n", $message);
        $message = preg_replace('/■ /', '', $message);
        $message = preg_replace('/▶ /', '', $message);
        if (strlen($message) > $maxLength) {
            $message = mb_substr($message, 0, $maxLength - 10) . '...';
        }
        return $message;
    }

    private function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($phone, '82') === 0) {
            $phone = '0' . substr($phone, 2);
        }
        return $phone;
    }

    private function logNotification($params)
    {
        $query = "INSERT INTO notification_logs (order_id, notification_type, template_code, recipient, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->db, $query);
        $orderId = $params['order_id'] ?? 0;
        $type = $params['type'] ?? '';
        $template = $params['template'] ?? '';
        $recipient = $params['phone'] ?? $params['email'] ?? '';
        $message = $params['message'] ?? '';
        $status = self::STATUS_PENDING;
        mysqli_stmt_bind_param($stmt, 'isssss', $orderId, $type, $template, $recipient, $message, $status);
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->db);
        }
        return 0;
    }

    private function updateNotificationLog($logId, $status, $result = null)
    {
        if (!$logId) return;
        $query = "UPDATE notification_logs SET status = ?, result = ?, sent_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        $resultJson = is_array($result) ? json_encode($result) : $result;
        mysqli_stmt_bind_param($stmt, 'ssi', $status, $resultJson, $logId);
        mysqli_stmt_execute($stmt);
    }

    private function httpRequest($url, $method, $headers, $body)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            throw new Exception('HTTP 요청 실패: ' . $error);
        }
        return json_decode($response, true);
    }

    public static function getCreateTableSQL()
    {
        return "CREATE TABLE IF NOT EXISTS `notification_logs` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NULL,
            `notification_type` VARCHAR(50) NOT NULL,
            `template_code` VARCHAR(100) NULL,
            `recipient` VARCHAR(100) NOT NULL,
            `message` TEXT NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `result` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `sent_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_order_id` (`order_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }
}
