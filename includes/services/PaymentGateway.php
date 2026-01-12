<?php
/**
 * PaymentGateway Service
 *
 * 결제 시스템 통합 서비스
 * - 네이버페이
 * - 카카오페이
 * - 무통장입금
 *
 * @package DusonPrint
 * @since 2026-01-13
 */

class PaymentGateway
{
    private $db;
    private $config;
    private $lastError;

    // 결제 상태 상수
    const STATUS_PENDING = 'pending';           // 결제 대기
    const STATUS_PAID = 'paid';                 // 결제 완료
    const STATUS_CANCELLED = 'cancelled';       // 결제 취소
    const STATUS_REFUNDED = 'refunded';         // 환불 완료
    const STATUS_FAILED = 'failed';             // 결제 실패

    // 결제 수단 상수
    const METHOD_BANK_TRANSFER = 'bank_transfer';  // 무통장입금
    const METHOD_NAVERPAY = 'naverpay';            // 네이버페이
    const METHOD_KAKAOPAY = 'kakaopay';            // 카카오페이
    const METHOD_CARD = 'card';                    // 신용카드 (KG이니시스)

    /**
     * 생성자
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->loadConfig();
    }

    /**
     * 설정 로드
     */
    private function loadConfig()
    {
        $this->config = [
            // 네이버페이 설정
            'naverpay' => [
                'client_id' => getenv('NAVERPAY_CLIENT_ID') ?: '',
                'client_secret' => getenv('NAVERPAY_CLIENT_SECRET') ?: '',
                'chain_id' => getenv('NAVERPAY_CHAIN_ID') ?: '',
                'mode' => getenv('NAVERPAY_MODE') ?: 'sandbox', // sandbox or production
                'api_url' => [
                    'sandbox' => 'https://dev.apis.naver.com/naverpay-partner/naverpay',
                    'production' => 'https://apis.naver.com/naverpay-partner/naverpay'
                ]
            ],
            // 카카오페이 설정
            'kakaopay' => [
                'cid' => getenv('KAKAOPAY_CID') ?: 'TC0ONETIME', // 테스트용 CID
                'admin_key' => getenv('KAKAOPAY_ADMIN_KEY') ?: '',
                'mode' => getenv('KAKAOPAY_MODE') ?: 'sandbox',
                'api_url' => [
                    'sandbox' => 'https://open-api.kakaopay.com/online/v1/payment',
                    'production' => 'https://open-api.kakaopay.com/online/v1/payment'
                ]
            ],
            // 무통장입금 계좌 정보
            'bank_accounts' => [
                [
                    'bank' => '국민은행',
                    'account' => '123-456-789012',
                    'holder' => '두손기획'
                ],
                [
                    'bank' => '신한은행',
                    'account' => '110-123-456789',
                    'holder' => '두손기획'
                ]
            ],
            // 콜백 URL
            'callback_urls' => [
                'naverpay_return' => '/payment/naverpay_return.php',
                'naverpay_cancel' => '/payment/naverpay_cancel.php',
                'kakaopay_success' => '/payment/kakaopay_success.php',
                'kakaopay_cancel' => '/payment/kakaopay_cancel.php',
                'kakaopay_fail' => '/payment/kakaopay_fail.php'
            ]
        ];
    }

    /**
     * 마지막 에러 메시지 반환
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * 결제 생성
     *
     * @param array $orderData 주문 데이터
     * @param string $paymentMethod 결제 수단
     * @return array|false 결제 정보 또는 실패
     */
    public function createPayment($orderData, $paymentMethod)
    {
        try {
            // 결제 레코드 생성
            $paymentId = $this->createPaymentRecord($orderData, $paymentMethod);

            if (!$paymentId) {
                throw new Exception('결제 레코드 생성 실패');
            }

            switch ($paymentMethod) {
                case self::METHOD_BANK_TRANSFER:
                    return $this->initBankTransfer($paymentId, $orderData);

                case self::METHOD_NAVERPAY:
                    return $this->initNaverPay($paymentId, $orderData);

                case self::METHOD_KAKAOPAY:
                    return $this->initKakaoPay($paymentId, $orderData);

                default:
                    throw new Exception('지원하지 않는 결제 수단: ' . $paymentMethod);
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            $this->logPaymentError('createPayment', $e->getMessage(), $orderData);
            return false;
        }
    }

    /**
     * 결제 레코드 생성
     */
    private function createPaymentRecord($orderData, $paymentMethod)
    {
        $orderId = $orderData['order_id'] ?? 0;
        $amount = $orderData['amount'] ?? 0;
        $amountVat = $orderData['amount_vat'] ?? floor($amount * 1.1);

        $query = "INSERT INTO payments (
            order_id, payment_method, amount, amount_vat,
            status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = mysqli_prepare($this->db, $query);
        $status = self::STATUS_PENDING;

        mysqli_stmt_bind_param($stmt, 'isdds',
            $orderId, $paymentMethod, $amount, $amountVat, $status
        );

        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->db);
        }

        return false;
    }

    /**
     * 무통장입금 초기화
     */
    private function initBankTransfer($paymentId, $orderData)
    {
        $depositDeadline = date('Y-m-d H:i:s', strtotime('+3 days'));

        // 입금 기한 업데이트
        $query = "UPDATE payments SET
            deposit_deadline = ?,
            bank_info = ?
            WHERE id = ?";

        $stmt = mysqli_prepare($this->db, $query);
        $bankInfo = json_encode($this->config['bank_accounts'], JSON_UNESCAPED_UNICODE);

        mysqli_stmt_bind_param($stmt, 'ssi', $depositDeadline, $bankInfo, $paymentId);
        mysqli_stmt_execute($stmt);

        return [
            'payment_id' => $paymentId,
            'method' => self::METHOD_BANK_TRANSFER,
            'status' => self::STATUS_PENDING,
            'bank_accounts' => $this->config['bank_accounts'],
            'deposit_deadline' => $depositDeadline,
            'amount' => $orderData['amount'],
            'amount_vat' => $orderData['amount_vat'] ?? floor($orderData['amount'] * 1.1)
        ];
    }

    /**
     * 네이버페이 결제 초기화
     */
    private function initNaverPay($paymentId, $orderData)
    {
        $naverpayConfig = $this->config['naverpay'];
        $mode = $naverpayConfig['mode'];
        $apiUrl = $naverpayConfig['api_url'][$mode];

        // 상품 정보 구성
        $productItems = [];
        $productName = $orderData['product_name'] ?? '인쇄 주문';

        $productItems[] = [
            'categoryType' => 'PRODUCT',
            'categoryId' => 'PRINT',
            'uid' => 'ORDER_' . $orderData['order_id'],
            'name' => $productName,
            'count' => 1
        ];

        $requestBody = [
            'merchantPayKey' => 'PAY_' . $paymentId . '_' . time(),
            'productName' => $productName,
            'productCount' => 1,
            'totalPayAmount' => (int)($orderData['amount_vat'] ?? floor($orderData['amount'] * 1.1)),
            'taxScopeAmount' => (int)$orderData['amount'],
            'taxExScopeAmount' => 0,
            'returnUrl' => $this->getFullUrl($this->config['callback_urls']['naverpay_return']),
            'productItems' => $productItems
        ];

        // API 호출
        $response = $this->callNaverPayAPI('/payments/v2.2/reserve', $requestBody);

        if ($response && isset($response['body']['reserveId'])) {
            // 예약 정보 저장
            $this->updatePaymentExternalId($paymentId, 'naverpay', $response['body']['reserveId']);

            return [
                'payment_id' => $paymentId,
                'method' => self::METHOD_NAVERPAY,
                'status' => self::STATUS_PENDING,
                'reserve_id' => $response['body']['reserveId'],
                'redirect_url' => $response['body']['redirectUrl'] ?? null
            ];
        }

        throw new Exception('네이버페이 예약 실패: ' . json_encode($response));
    }

    /**
     * 카카오페이 결제 초기화
     */
    private function initKakaoPay($paymentId, $orderData)
    {
        $kakaopayConfig = $this->config['kakaopay'];

        $partnerOrderId = 'ORDER_' . $orderData['order_id'];
        $partnerUserId = 'USER_' . ($orderData['user_id'] ?? session_id());
        $itemName = $orderData['product_name'] ?? '인쇄 주문';
        $totalAmount = (int)($orderData['amount_vat'] ?? floor($orderData['amount'] * 1.1));
        $taxFreeAmount = 0;

        $requestBody = [
            'cid' => $kakaopayConfig['cid'],
            'partner_order_id' => $partnerOrderId,
            'partner_user_id' => $partnerUserId,
            'item_name' => mb_substr($itemName, 0, 100),
            'quantity' => 1,
            'total_amount' => $totalAmount,
            'tax_free_amount' => $taxFreeAmount,
            'approval_url' => $this->getFullUrl($this->config['callback_urls']['kakaopay_success']),
            'cancel_url' => $this->getFullUrl($this->config['callback_urls']['kakaopay_cancel']),
            'fail_url' => $this->getFullUrl($this->config['callback_urls']['kakaopay_fail'])
        ];

        // API 호출
        $response = $this->callKakaoPayAPI('/ready', $requestBody);

        if ($response && isset($response['tid'])) {
            // TID 저장
            $this->updatePaymentExternalId($paymentId, 'kakaopay', $response['tid']);

            // 추가 정보 저장 (partner_order_id, partner_user_id)
            $this->savePaymentMeta($paymentId, [
                'partner_order_id' => $partnerOrderId,
                'partner_user_id' => $partnerUserId
            ]);

            return [
                'payment_id' => $paymentId,
                'method' => self::METHOD_KAKAOPAY,
                'status' => self::STATUS_PENDING,
                'tid' => $response['tid'],
                'redirect_url_pc' => $response['next_redirect_pc_url'] ?? null,
                'redirect_url_mobile' => $response['next_redirect_mobile_url'] ?? null,
                'redirect_url_app' => $response['next_redirect_app_url'] ?? null
            ];
        }

        throw new Exception('카카오페이 준비 실패: ' . json_encode($response));
    }

    /**
     * 네이버페이 결제 승인
     */
    public function approveNaverPay($paymentId, $paymentResult)
    {
        try {
            $payment = $this->getPayment($paymentId);

            if (!$payment) {
                throw new Exception('결제 정보를 찾을 수 없습니다.');
            }

            $requestBody = [
                'paymentId' => $paymentResult['paymentId']
            ];

            $response = $this->callNaverPayAPI('/payments/v2.2/apply', $requestBody);

            if ($response && $response['code'] === 'Success') {
                $this->updatePaymentStatus($paymentId, self::STATUS_PAID, [
                    'approved_at' => date('Y-m-d H:i:s'),
                    'payment_result' => json_encode($response['body'])
                ]);

                return [
                    'success' => true,
                    'payment_id' => $paymentId,
                    'method' => self::METHOD_NAVERPAY
                ];
            }

            throw new Exception('네이버페이 승인 실패: ' . ($response['message'] ?? 'Unknown error'));
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            $this->logPaymentError('approveNaverPay', $e->getMessage(), ['payment_id' => $paymentId]);
            return false;
        }
    }

    /**
     * 카카오페이 결제 승인
     */
    public function approveKakaoPay($paymentId, $pgToken)
    {
        try {
            $payment = $this->getPayment($paymentId);

            if (!$payment) {
                throw new Exception('결제 정보를 찾을 수 없습니다.');
            }

            $meta = $this->getPaymentMeta($paymentId);
            $kakaopayConfig = $this->config['kakaopay'];

            $requestBody = [
                'cid' => $kakaopayConfig['cid'],
                'tid' => $payment['external_id'],
                'partner_order_id' => $meta['partner_order_id'],
                'partner_user_id' => $meta['partner_user_id'],
                'pg_token' => $pgToken
            ];

            $response = $this->callKakaoPayAPI('/approve', $requestBody);

            if ($response && isset($response['aid'])) {
                $this->updatePaymentStatus($paymentId, self::STATUS_PAID, [
                    'approved_at' => date('Y-m-d H:i:s'),
                    'payment_result' => json_encode($response)
                ]);

                return [
                    'success' => true,
                    'payment_id' => $paymentId,
                    'method' => self::METHOD_KAKAOPAY,
                    'aid' => $response['aid']
                ];
            }

            throw new Exception('카카오페이 승인 실패: ' . json_encode($response));
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            $this->logPaymentError('approveKakaoPay', $e->getMessage(), ['payment_id' => $paymentId]);
            return false;
        }
    }

    /**
     * 무통장입금 확인 (관리자용)
     */
    public function confirmBankTransfer($paymentId, $adminId, $depositorName = null)
    {
        try {
            $payment = $this->getPayment($paymentId);

            if (!$payment) {
                throw new Exception('결제 정보를 찾을 수 없습니다.');
            }

            if ($payment['payment_method'] !== self::METHOD_BANK_TRANSFER) {
                throw new Exception('무통장입금 결제가 아닙니다.');
            }

            $this->updatePaymentStatus($paymentId, self::STATUS_PAID, [
                'approved_at' => date('Y-m-d H:i:s'),
                'confirmed_by' => $adminId,
                'depositor_name' => $depositorName
            ]);

            // 주문 상태도 업데이트
            $this->updateOrderPaymentStatus($payment['order_id'], 'paid');

            return [
                'success' => true,
                'payment_id' => $paymentId,
                'method' => self::METHOD_BANK_TRANSFER
            ];
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * 결제 취소
     */
    public function cancelPayment($paymentId, $reason = '')
    {
        try {
            $payment = $this->getPayment($paymentId);

            if (!$payment) {
                throw new Exception('결제 정보를 찾을 수 없습니다.');
            }

            // 이미 취소된 경우
            if ($payment['status'] === self::STATUS_CANCELLED) {
                return ['success' => true, 'message' => '이미 취소된 결제입니다.'];
            }

            // 결제 완료 상태인 경우 PG사 취소 요청
            if ($payment['status'] === self::STATUS_PAID) {
                switch ($payment['payment_method']) {
                    case self::METHOD_NAVERPAY:
                        $this->cancelNaverPay($payment);
                        break;
                    case self::METHOD_KAKAOPAY:
                        $this->cancelKakaoPay($payment);
                        break;
                    case self::METHOD_BANK_TRANSFER:
                        // 무통장입금은 별도 환불 처리 필요
                        break;
                }
            }

            $this->updatePaymentStatus($paymentId, self::STATUS_CANCELLED, [
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancel_reason' => $reason
            ]);

            return ['success' => true, 'payment_id' => $paymentId];
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * 네이버페이 취소
     */
    private function cancelNaverPay($payment)
    {
        $requestBody = [
            'paymentId' => $payment['external_id'],
            'cancelAmount' => (int)$payment['amount_vat'],
            'cancelReason' => '주문 취소',
            'cancelRequester' => 2 // 1: 구매자, 2: 가맹점 관리자
        ];

        $response = $this->callNaverPayAPI('/payments/v1/cancel', $requestBody);

        if (!$response || $response['code'] !== 'Success') {
            throw new Exception('네이버페이 취소 실패: ' . json_encode($response));
        }

        return $response;
    }

    /**
     * 카카오페이 취소
     */
    private function cancelKakaoPay($payment)
    {
        $kakaopayConfig = $this->config['kakaopay'];

        $requestBody = [
            'cid' => $kakaopayConfig['cid'],
            'tid' => $payment['external_id'],
            'cancel_amount' => (int)$payment['amount_vat'],
            'cancel_tax_free_amount' => 0
        ];

        $response = $this->callKakaoPayAPI('/cancel', $requestBody);

        if (!$response || !isset($response['aid'])) {
            throw new Exception('카카오페이 취소 실패: ' . json_encode($response));
        }

        return $response;
    }

    /**
     * 결제 정보 조회
     */
    public function getPayment($paymentId)
    {
        $query = "SELECT * FROM payments WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $paymentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);
    }

    /**
     * 주문별 결제 목록 조회
     */
    public function getPaymentsByOrder($orderId)
    {
        $query = "SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $payments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $payments[] = $row;
        }

        return $payments;
    }

    /**
     * 결제 상태 업데이트
     */
    private function updatePaymentStatus($paymentId, $status, $additionalData = [])
    {
        $setClause = "status = ?, updated_at = NOW()";
        $params = [$status];
        $types = 's';

        foreach ($additionalData as $key => $value) {
            $setClause .= ", $key = ?";
            $params[] = $value;
            $types .= 's';
        }

        $params[] = $paymentId;
        $types .= 'i';

        $query = "UPDATE payments SET $setClause WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);

        return mysqli_stmt_execute($stmt);
    }

    /**
     * 외부 결제 ID 업데이트
     */
    private function updatePaymentExternalId($paymentId, $provider, $externalId)
    {
        $query = "UPDATE payments SET external_provider = ?, external_id = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $provider, $externalId, $paymentId);

        return mysqli_stmt_execute($stmt);
    }

    /**
     * 결제 메타 정보 저장
     */
    private function savePaymentMeta($paymentId, $meta)
    {
        $query = "UPDATE payments SET meta = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE);
        mysqli_stmt_bind_param($stmt, 'si', $metaJson, $paymentId);

        return mysqli_stmt_execute($stmt);
    }

    /**
     * 결제 메타 정보 조회
     */
    private function getPaymentMeta($paymentId)
    {
        $payment = $this->getPayment($paymentId);
        return json_decode($payment['meta'] ?? '{}', true);
    }

    /**
     * 주문 결제 상태 업데이트
     */
    private function updateOrderPaymentStatus($orderId, $paymentStatus)
    {
        $query = "UPDATE mlangorder_printauto SET payment_status = ? WHERE no = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'si', $paymentStatus, $orderId);

        return mysqli_stmt_execute($stmt);
    }

    /**
     * 네이버페이 API 호출
     */
    private function callNaverPayAPI($endpoint, $body)
    {
        $config = $this->config['naverpay'];
        $mode = $config['mode'];
        $url = $config['api_url'][$mode] . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'X-Naver-Client-Id: ' . $config['client_id'],
            'X-Naver-Client-Secret: ' . $config['client_secret'],
            'X-NaverPay-Chain-Id: ' . $config['chain_id']
        ];

        return $this->httpRequest($url, 'POST', $headers, $body);
    }

    /**
     * 카카오페이 API 호출
     */
    private function callKakaoPayAPI($endpoint, $body)
    {
        $config = $this->config['kakaopay'];
        $mode = $config['mode'];
        $url = $config['api_url'][$mode] . $endpoint;

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: SECRET_KEY ' . $config['admin_key']
        ];

        return $this->httpRequest($url, 'POST', $headers, $body, true);
    }

    /**
     * HTTP 요청
     */
    private function httpRequest($url, $method, $headers, $body, $formEncoded = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($formEncoded) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            $this->logPaymentError('httpRequest', $error, ['url' => $url]);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * 전체 URL 생성
     */
    private function getFullUrl($path)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $protocol . '://' . $host . $path;
    }

    /**
     * 결제 오류 로그
     */
    private function logPaymentError($action, $message, $context = [])
    {
        $logMessage = sprintf(
            "[PaymentGateway][%s] %s | Context: %s",
            $action,
            $message,
            json_encode($context, JSON_UNESCAPED_UNICODE)
        );

        error_log($logMessage);
    }

    /**
     * payments 테이블 생성 SQL (설치용)
     */
    public static function getCreateTableSQL()
    {
        return "
        CREATE TABLE IF NOT EXISTS `payments` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `payment_method` VARCHAR(50) NOT NULL,
            `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `amount_vat` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `external_provider` VARCHAR(50) NULL,
            `external_id` VARCHAR(255) NULL,
            `meta` TEXT NULL,
            `bank_info` TEXT NULL,
            `deposit_deadline` DATETIME NULL,
            `depositor_name` VARCHAR(100) NULL,
            `approved_at` DATETIME NULL,
            `cancelled_at` DATETIME NULL,
            `cancel_reason` TEXT NULL,
            `confirmed_by` INT(11) NULL,
            `payment_result` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_order_id` (`order_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_payment_method` (`payment_method`),
            INDEX `idx_external_id` (`external_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
}
