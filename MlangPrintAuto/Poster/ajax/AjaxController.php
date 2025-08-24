<?php
/**
 * LittlePrint Ajax 요청을 처리하는 기본 컨트롤러 클래스
 * 공통 응답 형식과 에러 처리를 담당합니다.
 */
class AjaxController {
    private $db;
    private $validator;
    
    public function __construct($database) {
        $this->db = $database;
        $this->validator = new InputValidator();
        
        // JSON 응답 헤더 설정
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
    }
    
    /**
     * 성공 응답을 JSON 형태로 반환
     * @param mixed $data 응답 데이터
     * @return string JSON 응답
     */
    protected function successResponse($data) {
        return json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * 에러 응답을 JSON 형태로 반환
     * @param string $message 에러 메시지
     * @param string $code 에러 코드
     * @param int $httpCode HTTP 상태 코드
     * @return string JSON 응답
     */
    protected function errorResponse($message, $code = 'GENERAL_ERROR', $httpCode = 400) {
        http_response_code($httpCode);
        return json_encode([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * 데이터베이스 에러 처리
     * @param Exception $e 예외 객체
     * @return string JSON 에러 응답
     */
    protected function handleDatabaseError($e) {
        // 에러 로그 기록
        error_log("Database Error: " . $e->getMessage());
        
        return $this->errorResponse(
            '데이터베이스 처리 중 오류가 발생했습니다.',
            'DATABASE_ERROR',
            500
        );
    }
    
    /**
     * 입력 검증 에러 처리
     * @param string $field 필드명
     * @param string $message 에러 메시지
     * @return string JSON 에러 응답
     */
    protected function handleValidationError($field, $message) {
        return $this->errorResponse(
            "{$field}: {$message}",
            'VALIDATION_ERROR',
            400
        );
    }
    
    /**
     * 여러 검증 에러 처리
     * @param array $errors 에러 배열
     * @return string JSON 에러 응답
     */
    protected function handleMultipleValidationErrors($errors) {
        $errorMessages = [];
        foreach ($errors as $field => $message) {
            $errorMessages[] = "{$field}: {$message}";
        }
        
        return $this->errorResponse(
            implode(', ', $errorMessages),
            'VALIDATION_ERROR',
            400
        );
    }
    
    /**
     * 요청 메소드 검증
     * @param string $expectedMethod 예상 메소드 (GET, POST 등)
     * @return bool 검증 결과
     */
    protected function validateRequestMethod($expectedMethod) {
        if ($_SERVER['REQUEST_METHOD'] !== $expectedMethod) {
            echo $this->errorResponse(
                "잘못된 요청 메소드입니다. {$expectedMethod} 메소드를 사용해주세요.",
                'INVALID_METHOD',
                405
            );
            return false;
        }
        return true;
    }
    
    /**
     * CSRF 토큰 검증 (향후 확장용)
     * @return bool 검증 결과
     */
    protected function validateCSRFToken() {
        // 현재는 기본 구현만 제공
        // 실제 프로젝트에서는 세션 기반 CSRF 토큰 검증 구현
        return true;
    }
    
    /**
     * 종류 목록 조회 처리
     * @return string JSON 응답
     */
    public function getMainCategories() {
        if (!$this->validateRequestMethod('GET')) {
            return;
        }
        
        try {
            $categories = $this->db->getMainCategories();
            echo $this->successResponse($categories);
        } catch (Exception $e) {
            echo $this->handleDatabaseError($e);
        }
    }
    
    /**
     * 종이종류 목록 조회 처리
     * @return string JSON 응답
     */
    public function getPaperTypes() {
        if (!$this->validateRequestMethod('GET')) {
            return;
        }
        
        $categoryId = $_GET['category_id'] ?? null;
        
        if (!$categoryId) {
            echo $this->errorResponse('종류 ID가 필요합니다.', 'MISSING_PARAMETER');
            return;
        }
        
        // 입력값 검증
        $validationResult = $this->validator->validateCategoryType($categoryId);
        if (!$validationResult[0]) {
            echo $this->handleValidationError('category_id', $validationResult[2]);
            return;
        }
        
        try {
            $paperTypes = $this->db->getPaperTypesByCategory($validationResult[1]);
            echo $this->successResponse($paperTypes);
        } catch (Exception $e) {
            echo $this->handleDatabaseError($e);
        }
    }
    
    /**
     * 종이규격 목록 조회 처리
     * @return string JSON 응답
     */
    public function getPaperSizes() {
        if (!$this->validateRequestMethod('GET')) {
            return;
        }
        
        $categoryId = $_GET['category_id'] ?? null;
        
        if (!$categoryId) {
            echo $this->errorResponse('종류 ID가 필요합니다.', 'MISSING_PARAMETER');
            return;
        }
        
        // 입력값 검증
        $validationResult = $this->validator->validateCategoryType($categoryId);
        if (!$validationResult[0]) {
            echo $this->handleValidationError('category_id', $validationResult[2]);
            return;
        }
        
        try {
            $paperSizes = $this->db->getPaperSizesByCategory($validationResult[1]);
            echo $this->successResponse($paperSizes);
        } catch (Exception $e) {
            echo $this->handleDatabaseError($e);
        }
    }
    
    /**
     * 가격 계산 처리
     * @return string JSON 응답
     */
    public function calculatePrice() {
        if (!$this->validateRequestMethod('GET')) {
            return;
        }
        
        // 필수 파라미터 수집
        $params = [
            'MY_type' => $_GET['MY_type'] ?? null,
            'MY_Fsd' => $_GET['MY_Fsd'] ?? null,
            'PN_type' => $_GET['PN_type'] ?? null,
            'MY_amount' => $_GET['MY_amount'] ?? null,
            'POtype' => $_GET['POtype'] ?? null,
            'ordertype' => $_GET['ordertype'] ?? null
        ];
        
        // 필수 파라미터 확인
        foreach ($params as $key => $value) {
            if ($value === null) {
                echo $this->errorResponse("{$key} 파라미터가 필요합니다.", 'MISSING_PARAMETER');
                return;
            }
        }
        
        // 입력값 검증
        $validationResult = $this->validator->validatePriceCalculationParams($params);
        if (!$validationResult[0]) {
            echo $this->handleMultipleValidationErrors($validationResult[2]);
            return;
        }
        
        $sanitizedParams = $validationResult[1];
        
        try {
            // 데이터베이스에서 가격 정보 조회
            $priceParams = [
                'style' => $sanitizedParams['MY_type'],
                'section' => $sanitizedParams['PN_type'],
                'treeSelect' => $sanitizedParams['MY_Fsd'],
                'quantity' => $sanitizedParams['MY_amount'],
                'poType' => $sanitizedParams['POtype']
            ];
            
            $priceData = $this->db->getPriceData($priceParams);
            
            if (!$priceData) {
                // 디버그 정보 추가
                $debugInfo = [
                    'search_params' => $priceParams,
                    'table' => PRICE_TABLE
                ];
                
                if (isDebugMode()) {
                    echo $this->errorResponse(
                        '선택하신 조건에 해당하는 가격 정보를 찾을 수 없습니다. 디버그 정보: ' . json_encode($debugInfo),
                        'PRICE_NOT_FOUND',
                        404
                    );
                } else {
                    echo $this->errorResponse(
                        '선택하신 조건에 해당하는 가격 정보를 찾을 수 없습니다.',
                        'PRICE_NOT_FOUND',
                        404
                    );
                }
                return;
            }
            
            // 주문형태에 따른 가격 계산
            $orderType = $sanitizedParams['ordertype'];
            $printPrice = (int)$priceData['money'];
            $designPrice = (int)$priceData['DesignMoney'];
            
            switch ($orderType) {
                case 'print':
                    $finalPrintPrice = $printPrice;
                    $finalDesignPrice = 0;
                    break;
                case 'design':
                    $finalPrintPrice = 0;
                    $finalDesignPrice = $designPrice;
                    break;
                case 'total':
                default:
                    $finalPrintPrice = $printPrice;
                    $finalDesignPrice = $designPrice;
                    break;
            }
            
            $totalPrice = $finalPrintPrice + $finalDesignPrice;
            $vatPrice = (int)($totalPrice * 0.1);
            $finalTotalPrice = $totalPrice + $vatPrice;
            
            $result = [
                'printPrice' => $finalPrintPrice,
                'designPrice' => $finalDesignPrice,
                'subtotal' => $totalPrice,
                'vat' => $vatPrice,
                'total' => $finalTotalPrice,
                'quantityInfo' => $priceData['quantityTwo'] ?? '',
                'orderType' => $orderType
            ];
            
            echo $this->successResponse($result);
            
        } catch (Exception $e) {
            echo $this->handleDatabaseError($e);
        }
    }
}