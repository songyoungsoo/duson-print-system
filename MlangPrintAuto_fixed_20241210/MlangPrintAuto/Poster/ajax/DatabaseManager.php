<?php
/**
 * LittlePrint용 데이터베이스 연결과 쿼리 실행을 담당하는 클래스
 * Prepared Statement를 사용하여 SQL 인젝션을 방지합니다.
 * 
 * LittlePrint 시스템의 특징:
 * - 카테고리: MlangPrintAuto_transactionCate (Ttable='LittlePrint')
 * - 가격: MlangPrintAuto_LittlePrint
 * - 관계 구조: 종이종류(BigNo), 종이규격(TreeNo) - inserted와 반대!
 */
class DatabaseManager {
    private $connection;
    private $host;
    private $username;
    private $password;
    private $database;
    
    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        
        $this->connect();
    }
    
    /**
     * 데이터베이스 연결
     * @throws Exception 연결 실패 시
     */
    private function connect() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->connection->connect_error) {
            throw new Exception("데이터베이스 연결 실패: " . $this->connection->connect_error);
        }
        
        // UTF-8 설정
        $this->connection->set_charset("utf8");
    }
    
    /**
     * 연결 상태 확인 및 재연결
     */
    private function ensureConnection() {
        if (!$this->connection->ping()) {
            $this->connect();
        }
    }
    
    /**
     * 종류(최상위 카테고리) 목록 조회
     * @return array 종류 목록
     * @throws Exception 쿼리 실행 실패 시
     */
    public function getMainCategories() {
        $this->ensureConnection();
        
        $query = "SELECT no, title 
                  FROM " . CATEGORY_TABLE . " 
                  WHERE Ttable = ? AND BigNo = '0' 
                  ORDER BY no ASC";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("쿼리 준비 실패: " . $this->connection->error);
        }
        
        $pageName = PAGE_NAME;
        $stmt->bind_param("s", $pageName);
        
        if (!$stmt->execute()) {
            throw new Exception("쿼리 실행 실패: " . $stmt->error);
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
        return $categories;
    }
    
    /**
     * 종류에 따른 종이종류 목록 조회
     * LittlePrint에서는 BigNo = 종류.no 관계 사용
     * @param int $categoryId 종류 ID
     * @return array 종이종류 목록
     * @throws Exception 쿼리 실행 실패 시
     */
    public function getPaperTypesByCategory($categoryId) {
        $this->ensureConnection();
        
        $query = "SELECT no, title 
                  FROM " . CATEGORY_TABLE . " 
                  WHERE Ttable = ? AND BigNo = ? 
                  ORDER BY no ASC";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("쿼리 준비 실패: " . $this->connection->error);
        }
        
        $pageName = PAGE_NAME;
        $stmt->bind_param("si", $pageName, $categoryId);
        
        if (!$stmt->execute()) {
            throw new Exception("쿼리 실행 실패: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $paperTypes = [];
        
        while ($row = $result->fetch_assoc()) {
            $paperTypes[] = [
                'id' => $row['no'],
                'title' => $row['title']
            ];
        }
        
        $stmt->close();
        return $paperTypes;
    }
    
    /**
     * 종류에 따른 종이규격 목록 조회
     * LittlePrint에서는 TreeNo = 종류.no 관계 사용
     * @param int $categoryId 종류 ID
     * @return array 종이규격 목록
     * @throws Exception 쿼리 실행 실패 시
     */
    public function getPaperSizesByCategory($categoryId) {
        $this->ensureConnection();
        
        $query = "SELECT no, title 
                  FROM " . CATEGORY_TABLE . " 
                  WHERE Ttable = ? AND TreeNo = ? 
                  ORDER BY no ASC";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("쿼리 준비 실패: " . $this->connection->error);
        }
        
        $pageName = PAGE_NAME;
        $stmt->bind_param("si", $pageName, $categoryId);
        
        if (!$stmt->execute()) {
            throw new Exception("쿼리 실행 실패: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $paperSizes = [];
        
        while ($row = $result->fetch_assoc()) {
            $paperSizes[] = [
                'id' => $row['no'],
                'title' => $row['title']
            ];
        }
        
        $stmt->close();
        return $paperSizes;
    }
    
    /**
     * LittlePrint 가격 계산을 위한 데이터 조회
     * @param array $params 계산 파라미터
     * @return array|null 가격 데이터
     * @throws Exception 쿼리 실행 실패 시
     */
    public function getPriceData($params) {
        $this->ensureConnection();
        
        $query = "SELECT money, DesignMoney, quantityTwo 
                  FROM " . PRICE_TABLE . " 
                  WHERE style = ? AND Section = ? AND TreeSelect = ? 
                  AND quantity = ? AND POtype = ?";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("쿼리 준비 실패: " . $this->connection->error);
        }
        
        $stmt->bind_param("iiiis", 
            $params['style'],      // 종류 (MY_type)
            $params['section'],    // 종이규격 (PN_type)
            $params['treeSelect'], // 종이종류 (MY_Fsd)
            $params['quantity'],   // 수량 (MY_amount)
            $params['poType']      // 인쇄면 (POtype)
        );
        
        if (!$stmt->execute()) {
            throw new Exception("쿼리 실행 실패: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $priceData = $result->fetch_assoc();
        
        $stmt->close();
        return $priceData;
    }
    
    /**
     * 일반적인 쿼리 실행 (Prepared Statement 사용)
     * @param string $query SQL 쿼리
     * @param array $params 파라미터 배열
     * @param string $types 파라미터 타입 문자열
     * @return mysqli_result|bool 쿼리 결과
     * @throws Exception 쿼리 실행 실패 시
     */
    public function executeQuery($query, $params = [], $types = '') {
        $this->ensureConnection();
        
        if (empty($params)) {
            $result = $this->connection->query($query);
            if (!$result) {
                throw new Exception("쿼리 실행 실패: " . $this->connection->error);
            }
            return $result;
        }
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("쿼리 준비 실패: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("쿼리 실행 실패: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * 트랜잭션 시작
     */
    public function beginTransaction() {
        $this->ensureConnection();
        $this->connection->autocommit(false);
    }
    
    /**
     * 트랜잭션 커밋
     */
    public function commit() {
        $this->connection->commit();
        $this->connection->autocommit(true);
    }
    
    /**
     * 트랜잭션 롤백
     */
    public function rollback() {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }
    
    /**
     * 연결 종료
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * 마지막 삽입 ID 반환
     * @return int 마지막 삽입 ID
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * 영향받은 행 수 반환
     * @return int 영향받은 행 수
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
}