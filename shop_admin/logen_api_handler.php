<?php
/**
 * 로젠택배 API 핸들러
 *
 * 기능:
 * - 배송 접수 (송장번호 자동 발급)
 * - 배송 조회 (실시간 배송 상태)
 * - API 통신 로그 기록
 */

require_once __DIR__ . '/logen_api_config.php';
require_once __DIR__ . '/../db.php';

class LogenAPIHandler {
    private $custCd;
    private $userId;
    private $password;
    private $apiUrl;
    private $testMode;
    private $db;

    public function __construct() {
        $this->custCd = LOGEN_CUST_CD;
        $this->userId = LOGEN_USER_ID;
        $this->password = LOGEN_PASSWORD;

        // API Base URL (테스트/운영 자동 선택)
        $baseUrl = LOGEN_TEST_MODE ? 'https://topenapi.ilogen.com' : LOGEN_API_BASE_URL;
        $this->apiUrl = $baseUrl;
        $this->testMode = LOGEN_TEST_MODE;

        global $db;
        $this->db = $db;
    }

    /**
     * 송장번호 발급 (공식 API)
     *
     * @param int $quantity 발급할 송장번호 개수 (1~9999)
     * @return array ['success' => bool, 'invoice_numbers' => array, 'message' => string]
     */
    public function getSlipNumbers($quantity = 1) {
        $endpoint = $this->apiUrl . LOGEN_API_GET_SLIP_NO;

        // 수량 검증
        if ($quantity < 1 || $quantity > 9999) {
            return [
                'success' => false,
                'invoice_numbers' => [],
                'message' => '송장번호 발급 개수는 1~9999 범위여야 합니다.'
            ];
        }

        // API 요청 데이터 (공식 문서 기준)
        $payload = [
            'userId' => $this->userId,  // 연동업체코드 (du1830)
            'slipQty' => $quantity      // 채번 개수
        ];

        $result = $this->callAPI('POST', $endpoint, $payload);
        $this->logAPI('getSlipNumbers', $payload, $result);

        // 응답 처리
        if ($result['success'] && isset($result['data']['resultCd'])) {
            $data = $result['data'];

            // resultCd가 TRUE 또는 SUCCESS인 경우
            if ($data['resultCd'] === 'TRUE' || $data['resultMsg'] === 'SUCCESS') {
                return [
                    'success' => true,
                    'invoice_numbers' => $data['slipNo'] ?? [],
                    'start_no' => $data['startSlipNo'] ?? '',
                    'close_no' => $data['closeSlipNo'] ?? '',
                    'message' => '송장번호 발급 성공'
                ];
            }
        }

        return [
            'success' => false,
            'invoice_numbers' => [],
            'message' => $result['data']['resultMsg'] ?? 'API 호출 실패'
        ];
    }

    /**
     * 배송 접수 (송장번호 발급 + 주문 연결)
     *
     * 로젠 API는 송장번호 발급만 제공하므로:
     * 1. getSlipNumbers()로 송장번호 발급
     * 2. 발급받은 송장번호를 주문에 할당
     *
     * @param array $orderData 주문 데이터
     * @return array ['success' => bool, 'invoice_no' => string, 'message' => string]
     */
    public function registerShipment($orderData) {
        // 1. 송장번호 1개 발급
        $slipResult = $this->getSlipNumbers(1);

        if (!$slipResult['success']) {
            return [
                'success' => false,
                'invoice_no' => null,
                'message' => '송장번호 발급 실패: ' . $slipResult['message']
            ];
        }

        // 2. 발급받은 송장번호
        $invoiceNo = $slipResult['invoice_numbers'][0] ?? null;

        if (!$invoiceNo) {
            return [
                'success' => false,
                'invoice_no' => null,
                'message' => '송장번호가 반환되지 않았습니다.'
            ];
        }

        // 3. 성공 반환 (배송 정보 등록은 iLOGEN 시스템에서 수동 처리)
        return [
            'success' => true,
            'invoice_no' => $invoiceNo,
            'message' => '송장번호 발급 완료',
            'order_data' => $orderData  // 나중에 iLOGEN 업로드용 데이터
        ];
    }

    /**
     * 배송 상태 조회
     *
     * @param string $invoiceNo 송장번호
     * @return array ['success' => bool, 'status' => string, 'data' => array]
     */
    public function getTrackingStatus($invoiceNo) {
        $endpoint = $this->apiUrl . '/shipment/tracking';

        $payload = [
            'custCd' => $this->custCd,
            'userId' => $this->userId,
            'password' => $this->password,
            'invoiceNo' => $invoiceNo
        ];

        $result = $this->callAPI('GET', $endpoint, $payload);
        $this->logAPI('getTrackingStatus', $payload, $result);

        if ($result['success']) {
            return [
                'success' => true,
                'status' => $result['data']['status'] ?? '상태 불명',
                'data' => $result['data']
            ];
        }

        return [
            'success' => false,
            'status' => null,
            'data' => null
        ];
    }

    /**
     * API 통신 핵심 메서드
     *
     * @param string $method HTTP 메서드 (GET/POST)
     * @param string $endpoint API 엔드포인트 URL
     * @param array $data 요청 데이터
     * @return array ['success' => bool, 'code' => int, 'data' => array]
     */
    private function callAPI($method, $endpoint, $data) {
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json'
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            $endpoint .= '?' . http_build_query($data);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => LOGEN_API_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => !$this->testMode, // 테스트 모드에서는 SSL 검증 스킵
            CURLOPT_ENCODING => 'UTF-8'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'code' => 0,
                'data' => ['error' => $error]
            ];
        }

        $responseData = json_decode($response, true);

        return [
            'success' => ($httpCode === 200 || $httpCode === 201),
            'code' => $httpCode,
            'data' => $responseData ?? ['raw' => $response]
        ];
    }

    /**
     * API 호출 로그 기록
     */
    private function logAPI($action, $request, $response) {
        $logDir = dirname(LOGEN_LOG_PATH);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logEntry = sprintf(
            "[%s] %s\nRequest: %s\nResponse: %s\n%s\n",
            date('Y-m-d H:i:s'),
            $action,
            json_encode($request, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            str_repeat('-', 80)
        );

        file_put_contents(LOGEN_LOG_PATH, $logEntry, FILE_APPEND);
    }

    /**
     * 전화번호 형식 정리 (하이픈 제거)
     */
    private function formatPhoneNumber($phone) {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * DB에 배송 정보 저장
     */
    public function saveShipmentToDB($orderNo, $invoiceNo, $apiResponse) {
        $query = "INSERT INTO logen_shipment
                  (order_no, invoice_no, custCd, shipment_status, registered_at, last_updated, api_response)
                  VALUES (?, ?, ?, ?, NOW(), NOW(), ?)
                  ON DUPLICATE KEY UPDATE
                  invoice_no = VALUES(invoice_no),
                  shipment_status = VALUES(shipment_status),
                  last_updated = NOW(),
                  api_response = VALUES(api_response)";

        $stmt = mysqli_prepare($this->db, $query);
        $status = '배송접수';
        $responseJson = json_encode($apiResponse, JSON_UNESCAPED_UNICODE);

        mysqli_stmt_bind_param($stmt, 'issss',
            $orderNo,
            $invoiceNo,
            $this->custCd,
            $status,
            $responseJson
        );

        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    /**
     * DB에서 배송 상태 업데이트
     */
    public function updateShipmentStatus($invoiceNo, $status, $apiResponse) {
        $query = "UPDATE logen_shipment
                  SET shipment_status = ?,
                      last_updated = NOW(),
                      api_response = ?
                  WHERE invoice_no = ?";

        $stmt = mysqli_prepare($this->db, $query);
        $responseJson = json_encode($apiResponse, JSON_UNESCAPED_UNICODE);

        mysqli_stmt_bind_param($stmt, 'sss',
            $status,
            $responseJson,
            $invoiceNo
        );

        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }
}
