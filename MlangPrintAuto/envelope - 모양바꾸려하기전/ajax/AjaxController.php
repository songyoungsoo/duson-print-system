<?php
/**
 * Envelope 시스템용 Ajax 컨트롤러
 * Ajax 요청을 처리하고 적절한 응답을 반환합니다.
 */

class AjaxController {
    private $db;
    private $validator;
    private $dbManager;
    
    public function __construct($database) {
        $this->db = $database;
        $this->validator = new InputValidator();
        $this->dbManager = new DatabaseManager($database);
        
        debugLog('AjaxController initialized', 'CONTROLLER');
    }
    
    /**
     * 구분에 따른 종류 목록 조회
     * 
     * @param int $categoryType 구분 ID
     * @return array 종류 목록
     */
    public function getEnvelopeTypes($categoryType) {
        debugLog("Getting envelope types for category: {$categoryType}", 'CONTROLLER');
        
        try {
            // 입력값 검증
            $validatedCategoryType = $this->validator->validateCategoryType($categoryType);
            
            // 카테고리 존재 여부 확인
            if (!$this->dbManager->categoryExists($validatedCategoryType)) {
                errorResponse('CATEGORY_NOT_FOUND', '존재하지 않는 구분입니다.');
            }
            
            // 종류 목록 조회
            $types = $this->dbManager->getEnvelopeTypesByCategory($validatedCategoryType);
            
            // 결과가 없는 경우
            if (empty($types)) {
                debugLog("No envelope types found for category: {$validatedCategoryType}", 'CONTROLLER');
                return [
                    'message' => '해당 구분에 대한 종류가 없습니다.',
                    'types' => []
                ];
            }
            
            debugLog("Found " . count($types) . " envelope types", 'CONTROLLER');
            return [
                'message' => '종류 목록을 성공적으로 조회했습니다.',
                'types' => $types
            ];
            
        } catch (Exception $e) {
            logMessage("Error in getEnvelopeTypes: " . $e->getMessage(), 'ERROR');
            errorResponse('DATABASE_ERROR', '종류 목록 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * envelope 가격 계산
     * 
     * @param array $params 계산 파라미터
     * @return array 계산 결과
     */
    public function calculatePrice($params) {
        debugLog("Calculating envelope price", 'CONTROLLER');
        
        try {
            // 입력값 검증
            $validatedParams = $this->validator->validatePriceCalculationParams($params);
            
            // 카테고리와 타입 존재 여부 확인
            if (!$this->dbManager->categoryExists($validatedParams['MY_type'])) {
                errorResponse('CATEGORY_NOT_FOUND', '존재하지 않는 구분입니다.');
            }
            
            if (!$this->dbManager->typeExists($validatedParams['PN_type'], $validatedParams['MY_type'])) {
                errorResponse('TYPE_NOT_FOUND', '존재하지 않는 종류입니다.');
            }
            
            // 가격 데이터 조회
            $priceData = $this->dbManager->getEnvelopePriceData($validatedParams);
            
            if (!$priceData) {
                debugLog("No price data found for params: " . json_encode($validatedParams), 'CONTROLLER');
                return [
                    'message' => '해당 조건에 대한 가격 정보가 없습니다.',
                    'price_data' => null
                ];
            }
            
            // 가격 계산
            $calculatedPrice = $this->calculatePriceFromData($priceData, $validatedParams['ordertype']);
            
            debugLog("Price calculated successfully", 'CONTROLLER');
            return [
                'message' => '가격이 성공적으로 계산되었습니다.',
                'price_data' => $calculatedPrice
            ];
            
        } catch (Exception $e) {
            logMessage("Error in calculatePrice: " . $e->getMessage(), 'ERROR');
            errorResponse('CALCULATION_ERROR', '가격 계산 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 가격 데이터로부터 실제 가격 계산
     * 
     * @param array $priceData 데이터베이스에서 조회한 가격 데이터
     * @param string $orderType 주문 타입
     * @return array 계산된 가격 정보
     */
    private function calculatePriceFromData($priceData, $orderType) {
        debugLog("Calculating price from data for order type: {$orderType}", 'CONTROLLER');
        
        $printPrice = 0;
        $designPrice = 0;
        
        switch ($orderType) {
            case 'print':
                // 인쇄만 의뢰
                $printPrice = intval($priceData['money'] ?? 0);
                $designPrice = 0;
                break;
                
            case 'design':
                // 디자인만 의뢰
                $printPrice = 0;
                $designPrice = intval($priceData['DesignMoney'] ?? 0);
                break;
                
            case 'total':
            default:
                // 디자인 + 인쇄
                $printPrice = intval($priceData['money'] ?? 0);
                $designPrice = intval($priceData['DesignMoney'] ?? 0);
                break;
        }
        
        $totalPrice = $printPrice + $designPrice;
        $vatPrice = intval($totalPrice / 10); // 부가세 10%
        $finalPrice = $totalPrice + $vatPrice;
        
        $result = [
            'print_price' => $printPrice,
            'design_price' => $designPrice,
            'subtotal_price' => $totalPrice,
            'vat_price' => $vatPrice,
            'total_price' => $finalPrice,
            'formatted' => [
                'print_price' => number_format($printPrice),
                'design_price' => number_format($designPrice),
                'subtotal_price' => number_format($totalPrice),
                'vat_price' => number_format($vatPrice),
                'total_price' => number_format($finalPrice)
            ]
        ];
        
        debugLog("Price calculation result: " . json_encode($result), 'CONTROLLER');
        return $result;
    }
    
    /**
     * 모든 구분 목록 조회
     * 
     * @return array 구분 목록
     */
    public function getAllCategories() {
        debugLog("Getting all envelope categories", 'CONTROLLER');
        
        try {
            $categories = $this->dbManager->getAllCategories();
            
            if (empty($categories)) {
                debugLog("No categories found", 'CONTROLLER');
                return [
                    'message' => '구분 목록이 없습니다.',
                    'categories' => []
                ];
            }
            
            debugLog("Found " . count($categories) . " categories", 'CONTROLLER');
            return [
                'message' => '구분 목록을 성공적으로 조회했습니다.',
                'categories' => $categories
            ];
            
        } catch (Exception $e) {
            logMessage("Error in getAllCategories: " . $e->getMessage(), 'ERROR');
            errorResponse('DATABASE_ERROR', '구분 목록 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 시스템 상태 확인
     * 
     * @return array 시스템 상태 정보
     */
    public function getSystemStatus() {
        debugLog("Checking system status", 'CONTROLLER');
        
        try {
            // 데이터베이스 연결 상태 확인
            $dbStatus = $this->db ? 'connected' : 'disconnected';
            
            // 테이블 존재 여부 확인
            $tables = [
                ENVELOPE_TABLE => $this->checkTableExists(ENVELOPE_TABLE),
                CATEGORY_TABLE => $this->checkTableExists(CATEGORY_TABLE)
            ];
            
            $status = [
                'system' => 'envelope_ajax',
                'version' => '1.0.0',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => $dbStatus,
                'tables' => $tables,
                'debug_mode' => DEBUG_MODE,
                'logging' => ENABLE_LOGGING
            ];
            
            debugLog("System status checked", 'CONTROLLER');
            return $status;
            
        } catch (Exception $e) {
            logMessage("Error in getSystemStatus: " . $e->getMessage(), 'ERROR');
            errorResponse('SYSTEM_ERROR', '시스템 상태 확인 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 테이블 존재 여부 확인
     * 
     * @param string $tableName 테이블명
     * @return bool 존재 여부
     */
    private function checkTableExists($tableName) {
        try {
            $query = "SHOW TABLES LIKE ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("s", $tableName);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            
            return $exists;
        } catch (Exception $e) {
            debugLog("Error checking table existence: " . $e->getMessage(), 'CONTROLLER');
            return false;
        }
    }
    
    /**
     * JSON 응답 생성
     * 
     * @param mixed $data 응답 데이터
     * @param bool $success 성공 여부
     * @param int $httpCode HTTP 상태 코드
     */
    public function jsonResponse($data, $success = true, $httpCode = 200) {
        jsonResponse($data, $success, $httpCode);
    }
    
    /**
     * 에러 응답 생성
     * 
     * @param string $code 에러 코드
     * @param string $message 에러 메시지
     * @param mixed $details 에러 상세 정보
     * @param int $httpCode HTTP 상태 코드
     */
    public function errorResponse($code, $message, $details = null, $httpCode = 400) {
        errorResponse($code, $message, $details, $httpCode);
    }
}
?>