<?php
/**
 * 트러스빌 전자세금계산서 API 클래스
 * 경로: /lib/TrusbillAPI.php
 */

require_once __DIR__ . '/../config/trusbill_config.php';

class TrusbillAPI {
    private $api_key;
    private $api_secret;
    private $api_endpoint;
    private $test_mode;
    
    public function __construct() {
        $this->api_key = TRUSBILL_API_KEY;
        $this->api_secret = TRUSBILL_API_SECRET;
        $this->api_endpoint = TRUSBILL_API_ENDPOINT;
        $this->test_mode = TRUSBILL_TEST_MODE;
    }
    
    /**
     * 전자세금계산서 발급
     * 
     * @param array $invoice_data 세금계산서 데이터
     * @return array 발급 결과
     */
    public function issueInvoice($invoice_data) {
        $endpoint = $this->api_endpoint . '/taxinvoice/issue';
        
        $data = [
            'writeDate' => $invoice_data['issue_date'], // 작성일자 (YYYYMMDD)
            'issueType' => '01', // 발급유형 (01: 정발급, 02: 역발급)
            'taxType' => '01', // 과세형태 (01: 과세, 02: 영세, 03: 면세)
            'purposeType' => '02', // 영수/청구 (01: 영수, 02: 청구)
            
            // 공급자 정보
            'invoicerCorpNum' => SUPPLIER_BUSINESS_NUMBER,
            'invoicerCorpName' => SUPPLIER_COMPANY_NAME,
            'invoicerCEOName' => SUPPLIER_CEO_NAME,
            'invoicerAddr' => SUPPLIER_ADDRESS,
            'invoicerBizClass' => SUPPLIER_BUSINESS_TYPE,
            'invoicerBizType' => SUPPLIER_BUSINESS_ITEM,
            'invoicerContactName' => SUPPLIER_CONTACT_NAME,
            'invoicerEmail' => SUPPLIER_EMAIL,
            'invoicerTEL' => SUPPLIER_PHONE,
            
            // 공급받는자 정보
            'invoiceeCorpNum' => $invoice_data['customer_business_number'],
            'invoiceeCorpName' => $invoice_data['customer_company_name'],
            'invoiceeCEOName' => $invoice_data['customer_ceo_name'],
            'invoiceeAddr' => $invoice_data['customer_address'],
            'invoiceeBizClass' => $invoice_data['customer_business_type'] ?? '',
            'invoiceeBizType' => $invoice_data['customer_business_item'] ?? '',
            'invoiceeContactName' => $invoice_data['customer_contact_name'] ?? '',
            'invoiceeEmail' => $invoice_data['customer_email'] ?? '',
            'invoiceeTEL' => $invoice_data['customer_phone'] ?? '',
            
            // 금액 정보
            'supplyCostTotal' => $invoice_data['supply_amount'], // 공급가액 합계
            'taxTotal' => $invoice_data['tax_amount'], // 세액 합계
            'totalAmount' => $invoice_data['total_amount'], // 합계금액
            
            // 품목 정보
            'detailList' => $invoice_data['items']
        ];
        
        $this->log('발급 요청', $data);
        
        $result = $this->sendRequest('POST', $endpoint, $data);
        
        $this->log('발급 응답', $result);
        
        return $result;
    }
    
    /**
     * 전자세금계산서 조회
     * 
     * @param string $nts_confirm_num 국세청 승인번호
     * @return array 조회 결과
     */
    public function getInvoice($nts_confirm_num) {
        $endpoint = $this->api_endpoint . '/taxinvoice/' . $nts_confirm_num;
        
        $result = $this->sendRequest('GET', $endpoint);
        
        $this->log('조회 응답', $result);
        
        return $result;
    }
    
    /**
     * 전자세금계산서 취소
     * 
     * @param string $nts_confirm_num 국세청 승인번호
     * @param string $memo 취소 사유
     * @return array 취소 결과
     */
    public function cancelInvoice($nts_confirm_num, $memo = '') {
        $endpoint = $this->api_endpoint . '/taxinvoice/' . $nts_confirm_num . '/cancel';
        
        $data = [
            'memo' => $memo
        ];
        
        $this->log('취소 요청', $data);
        
        $result = $this->sendRequest('POST', $endpoint, $data);
        
        $this->log('취소 응답', $result);
        
        return $result;
    }
    
    /**
     * API 요청 전송
     * 
     * @param string $method HTTP 메소드
     * @param string $endpoint API 엔드포인트
     * @param array $data 요청 데이터
     * @return array 응답 결과
     */
    private function sendRequest($method, $endpoint, $data = null) {
        // API 키가 설정되지 않은 경우
        if (empty($this->api_key) || empty($this->api_secret)) {
            return [
                'success' => false,
                'error' => 'API_KEY_NOT_SET',
                'message' => 'API 키가 설정되지 않았습니다. config/trusbill_config.php 파일을 확인하세요.'
            ];
        }
        
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key,
            'X-API-Secret: ' . $this->api_secret
        ];
        
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        if ($curl_error) {
            return [
                'success' => false,
                'error' => 'CURL_ERROR',
                'message' => $curl_error
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return [
                'success' => true,
                'data' => $result
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'UNKNOWN_ERROR',
                'message' => $result['message'] ?? '알 수 없는 오류가 발생했습니다.',
                'http_code' => $http_code
            ];
        }
    }
    
    /**
     * 로그 기록
     * 
     * @param string $type 로그 타입
     * @param mixed $data 로그 데이터
     */
    private function log($type, $data) {
        if (!TRUSBILL_LOG_ENABLED) {
            return;
        }
        
        $log_file = TRUSBILL_LOG_PATH . date('Y-m-d') . '.log';
        $log_message = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $type,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
    
    /**
     * 품목 데이터 생성 헬퍼
     * 
     * @param string $item_name 품목명
     * @param int $quantity 수량
     * @param int $unit_price 단가
     * @param int $supply_amount 공급가액
     * @param int $tax_amount 세액
     * @return array 품목 데이터
     */
    public static function createItem($item_name, $quantity, $unit_price, $supply_amount, $tax_amount) {
        return [
            'purchaseDT' => date('Ymd'), // 거래일자
            'itemName' => $item_name, // 품목명
            'spec' => '', // 규격
            'qty' => $quantity, // 수량
            'unitCost' => $unit_price, // 단가
            'supplyCost' => $supply_amount, // 공급가액
            'tax' => $tax_amount, // 세액
            'remark' => '' // 비고
        ];
    }
}
