<?php
/**
 * Envelope 시스템용 데이터베이스 관리 클래스
 * envelope 테이블과 카테고리 테이블에 특화된 데이터베이스 작업을 처리합니다.
 */

class DatabaseManager {
    private $connection;
    
    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
        
        if (!$this->connection) {
            throw new Exception('데이터베이스 연결이 필요합니다.');
        }
        
        // 연결 상태 확인
        if (mysqli_connect_error()) {
            throw new Exception('데이터베이스 연결 실패: ' . mysqli_connect_error());
        }
        
        // UTF-8 설정
        mysqli_set_charset($this->connection, 'utf8');
        
        debugLog('DatabaseManager initialized', 'DATABASE');
    }
    
    /**
     * 구분(MY_type)에 따른 종류(PN_type) 목록 조회
     * 
     * @param int $categoryType 구분 ID
     * @return array 종류 목록
     */
    public function getEnvelopeTypesByCategory($categoryType) {
        debugLog("Getting envelope types for category: {$categoryType}", 'DATABASE');
        
        $query = "SELECT no, title FROM " . CATEGORY_TABLE . " 
                  WHERE BigNo = ? AND Ttable = ? 
                  ORDER BY no ASC";
        
        $stmt = $this->prepareStatement($query);
        $productType = PRODUCT_TYPE;
        $stmt->bind_param("is", $categoryType, $productType);
        
        if (!$stmt->execute()) {
            throw new Exception('종류 조회 쿼리 실행 실패: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $types = [];
        
        while ($row = $result->fetch_assoc()) {
            $types[] = [
                'id' => $row['no'],
                'title' => $row['title']
            ];
        }
        
        $stmt->close();
        
        debugLog("Found " . count($types) . " envelope types", 'DATABASE');
        return $types;
    }
    
    /**
     * envelope 가격 데이터 조회
     * 
     * @param array $params 검색 조건
     * @return array|null 가격 데이터
     */
    public function getEnvelopePriceData($params) {
        debugLog("Getting envelope price data: " . json_encode($params), 'DATABASE');
        
        $query = "SELECT * FROM " . ENVELOPE_TABLE . " 
                  WHERE style = ? AND Section = ? AND quantity = ? AND POtype = ?
                  LIMIT 1";
        
        $stmt = $this->prepareStatement($query);
        $stmt->bind_param("iisi", 
            $params['MY_type'], 
            $params['PN_type'], 
            $params['MY_amount'], 
            $params['POtype']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('가격 조회 쿼리 실행 실패: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $priceData = $result->fetch_assoc();
        
        $stmt->close();
        
        if ($priceData) {
            debugLog("Price data found for envelope", 'DATABASE');
        } else {
            debugLog("No price data found for envelope", 'DATABASE');
        }
        
        return $priceData;
    }
    
    /**
     * 카테고리 존재 여부 확인
     * 
     * @param int $categoryId 카테고리 ID
     * @return bool 존재 여부
     */
    public function categoryExists($categoryId) {
        debugLog("Checking if category exists: {$categoryId}", 'DATABASE');
        
        $query = "SELECT COUNT(*) as count FROM " . CATEGORY_TABLE . " 
                  WHERE no = ? AND Ttable = ? AND BigNo = 0";
        
        $stmt = $this->prepareStatement($query);
        $productType = PRODUCT_TYPE;
        $stmt->bind_param("is", $categoryId, $productType);
        
        if (!$stmt->execute()) {
            throw new Exception('카테고리 존재 확인 쿼리 실행 실패: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $exists = $row['count'] > 0;
        
        $stmt->close();
        
        debugLog("Category exists: " . ($exists ? 'true' : 'false'), 'DATABASE');
        return $exists;
    }
    
    /**
     * 종류 존재 여부 확인
     * 
     * @param int $typeId 종류 ID
     * @param int $categoryId 상위 카테고리 ID
     * @return bool 존재 여부
     */
    public function typeExists($typeId, $categoryId) {
        debugLog("Checking if type exists: {$typeId} under category: {$categoryId}", 'DATABASE');
        
        $query = "SELECT COUNT(*) as count FROM " . CATEGORY_TABLE . " 
                  WHERE no = ? AND BigNo = ? AND Ttable = ?";
        
        $stmt = $this->prepareStatement($query);
        $productType = PRODUCT_TYPE;
        $stmt->bind_param("iis", $typeId, $categoryId, $productType);
        
        if (!$stmt->execute()) {
            throw new Exception('종류 존재 확인 쿼리 실행 실패: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $exists = $row['count'] > 0;
        
        $stmt->close();
        
        debugLog("Type exists: " . ($exists ? 'true' : 'false'), 'DATABASE');
        return $exists;
    }
    
    /**
     * 모든 구분(카테고리) 목록 조회
     * 
     * @return array 구분 목록
     */
    public function getAllCategories() {
        debugLog("Getting all envelope categories", 'DATABASE');
        
        $query = "SELECT no, title FROM " . CATEGORY_TABLE . " 
                  WHERE Ttable = ? AND BigNo = 0 
                  ORDER BY no ASC";
        
        $stmt = $this->prepareStatement($query);
        $productType = PRODUCT_TYPE;
        $stmt->bind_param("s", $productType);
        
        if (!$stmt->execute()) {
            throw new Exception('카테고리 조회 쿼리 실행 실패: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $categories = [];
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'id' => $row['no'],
                'title' => $row['title']
            ];
        }
        
        $stmt->close();
        
        debugLog("Found " . count($categories) . " categories", 'DATABASE');
        return $categories;
    }
    
    /**
     * Prepared Statement 생성 및 에러 처리
     * 
     * @param string $query SQL 쿼리
     * @return mysqli_stmt Prepared Statement
     */
    private function prepareStatement($query) {
        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            throw new Exception('Prepared statement 생성 실패: ' . $this->connection->error);
        }
        
        return $stmt;
    }
    
    /**
     * 일반적인 쿼리 실행 (SELECT용)
     * 
     * @param string $query SQL 쿼리
     * @param array $params 바인딩할 파라미터
     * @return array 결과 배열
     */
    public function executeQuery($query, $params = []) {
        debugLog("Executing query: {$query}", 'DATABASE');
        
        if (empty($params)) {
            $result = mysqli_query($this->connection, $query);
            if (!$result) {
                throw new Exception('쿼리 실행 실패: ' . mysqli_error($this->connection));
            }
        } else {
            $stmt = $this->prepareStatement($query);
            
            if (!empty($params)) {
                $types = '';
                $values = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                    $values[] = $param;
                }
                
                $stmt->bind_param($types, ...$values);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Prepared statement 실행 실패: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $stmt->close();
        }
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            if (is_object($result)) {
                $result->free();
            }
        }
        
        debugLog("Query returned " . count($data) . " rows", 'DATABASE');
        return $data;
    }
    
    /**
     * 연결 종료
     */
    public function close() {
        if ($this->connection) {
            mysqli_close($this->connection);
            debugLog('Database connection closed', 'DATABASE');
        }
    }
    
    /**
     * 소멸자
     */
    public function __destruct() {
        // 연결이 아직 열려있다면 닫기
        // 주의: 다른 곳에서 사용 중일 수 있으므로 실제로는 닫지 않음
        // $this->close();
    }
}
?>