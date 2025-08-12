# 관리자 페이지 주문내용 표시 오류 수정 설계

## 개요

관리자 페이지에서 주문 상세 정보가 올바르게 표시되지 않는 문제를 해결하기 위한 시스템 설계입니다. 현재 파일 다운로드는 정상 작동하지만, Type_1 필드의 주문내용이 비어있거나 올바르게 파싱되지 않는 상황을 개선합니다.

## 아키텍처

### 현재 시스템 구조

```
프론트엔드 주문 페이지
    ↓ (주문 데이터)
ProcessOrder_unified.php (주문 처리)
    ↓ (데이터 저장)
MlangOrder_PrintAuto 테이블
    ↓ (데이터 조회)
admin.php (관리자 페이지)
    ↓ (상세 표시)
OrderFormOrderTree.php (주문 상세)
```

### 문제점 분석

1. **데이터 저장 단계**: ProcessOrder_unified.php에서 Type_1 필드에 주문 상세 정보를 저장하지만 형식이 일관되지 않음
2. **데이터 표시 단계**: OrderFormOrderTree.php에서 Type_1 필드를 단순히 텍스트로만 표시
3. **카테고리 변환**: 숫자 코드를 한글명으로 변환하는 로직 부족

## 컴포넌트 및 인터페이스

### 1. 주문 데이터 저장 개선

#### OrderDataFormatter 클래스
```php
class OrderDataFormatter {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * 품목별 주문 정보를 표준화된 형식으로 포맷
     */
    public function formatOrderData($product_type, $item_data) {
        switch ($product_type) {
            case 'sticker':
                return $this->formatStickerData($item_data);
            case 'cadarok':
                return $this->formatCadarokData($item_data);
            case 'namecard':
                return $this->formatNamecardData($item_data);
            // ... 기타 품목들
        }
    }
    
    /**
     * 카테고리 번호를 한글명으로 변환
     */
    public function getCategoryName($category_no) {
        $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $category_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['title'];
        }
        return "정보 없음";
    }
}
```

#### 스티커 데이터 포맷팅
```php
private function formatStickerData($item) {
    $formatted = "=== 스티커 주문 정보 ===\n";
    $formatted .= "재질: " . ($item['jong'] ?? '정보 없음') . "\n";
    $formatted .= "크기: " . ($item['garo'] ?? '0') . "mm × " . ($item['sero'] ?? '0') . "mm\n";
    $formatted .= "수량: " . number_format($item['mesu'] ?? 0) . "매\n";
    $formatted .= "모양: " . ($item['domusong'] ?? '정보 없음') . "\n";
    $formatted .= "편집비: " . number_format($item['uhyung'] ?? 0) . "원\n";
    
    return $formatted;
}
```

#### 카다록 데이터 포맷팅
```php
private function formatCadarokData($item) {
    $formatted = "=== 카다록/리플렛 주문 정보 ===\n";
    $formatted .= "구분: " . $this->getCategoryName($item['MY_type']) . "\n";
    $formatted .= "규격: " . $this->getCategoryName($item['MY_Fsd']) . "\n";
    $formatted .= "종이종류: " . $this->getCategoryName($item['PN_type']) . "\n";
    $formatted .= "수량: " . ($item['MY_amount'] ?? '0') . "부\n";
    $formatted .= "주문방법: " . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만') . "\n";
    
    return $formatted;
}
```

### 2. 관리자 페이지 표시 개선

#### OrderDisplayManager 클래스
```php
class OrderDisplayManager {
    private $db;
    private $formatter;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
        $this->formatter = new OrderDataFormatter($database_connection);
    }
    
    /**
     * 주문 상세 정보를 HTML 형태로 포맷
     */
    public function formatOrderForDisplay($order_data) {
        if (empty($order_data['Type_1'])) {
            return $this->generateEmptyOrderDisplay();
        }
        
        // JSON 형태인지 확인
        $parsed_data = json_decode($order_data['Type_1'], true);
        if ($parsed_data) {
            return $this->formatJsonOrderData($parsed_data);
        }
        
        // 기존 텍스트 형태 처리
        return $this->formatTextOrderData($order_data['Type_1']);
    }
    
    /**
     * 빈 주문 정보 표시
     */
    private function generateEmptyOrderDisplay() {
        return "<div class='order-info-empty'>
                    <h4>⚠️ 주문 정보 없음</h4>
                    <p>이 주문의 상세 정보가 저장되지 않았습니다.</p>
                    <p>주문 번호: {$order_data['no']}</p>
                    <p>주문 유형: {$order_data['Type']}</p>
                </div>";
    }
}
```

### 3. 디버깅 도구

#### OrderDebugger 클래스
```php
class OrderDebugger {
    private $db;
    private $log_file;
    
    public function __construct($database_connection, $log_file_path = null) {
        $this->db = $database_connection;
        $this->log_file = $log_file_path ?? '../logs/order_debug.log';
    }
    
    /**
     * 주문 데이터 저장 과정 로깅
     */
    public function logOrderSave($order_no, $product_type, $raw_data, $formatted_data) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'ORDER_SAVE',
            'order_no' => $order_no,
            'product_type' => $product_type,
            'raw_data' => $raw_data,
            'formatted_data' => $formatted_data
        ];
        
        $this->writeLog($log_entry);
    }
    
    /**
     * 주문 데이터 조회 과정 로깅
     */
    public function logOrderView($order_no, $retrieved_data, $parsing_result) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'ORDER_VIEW',
            'order_no' => $order_no,
            'retrieved_data' => $retrieved_data,
            'parsing_result' => $parsing_result
        ];
        
        $this->writeLog($log_entry);
    }
    
    /**
     * 특정 주문의 전체 데이터 흐름 추적
     */
    public function traceOrderFlow($order_no) {
        // 주문 테이블에서 데이터 조회
        $order_query = "SELECT * FROM MlangOrder_PrintAuto WHERE no = ?";
        $stmt = mysqli_prepare($this->db, $order_query);
        mysqli_stmt_bind_param($stmt, 'i', $order_no);
        mysqli_stmt_execute($stmt);
        $order_result = mysqli_stmt_get_result($stmt);
        $order_data = mysqli_fetch_assoc($order_result);
        
        // 업로드된 파일 확인
        $upload_dir = "../MlangOrder_PrintAuto/upload/$order_no";
        $files = [];
        if (is_dir($upload_dir)) {
            $files = array_diff(scandir($upload_dir), ['.', '..']);
        }
        
        // 장바구니 히스토리 확인 (가능한 경우)
        $cart_query = "SELECT * FROM shop_temp WHERE order_id = ?";
        $cart_stmt = mysqli_prepare($this->db, $cart_query);
        mysqli_stmt_bind_param($cart_stmt, 's', $order_no);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);
        $cart_data = [];
        while ($row = mysqli_fetch_assoc($cart_result)) {
            $cart_data[] = $row;
        }
        
        return [
            'order_data' => $order_data,
            'uploaded_files' => $files,
            'cart_history' => $cart_data,
            'trace_timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
```

## 데이터 모델

### 1. MlangOrder_PrintAuto 테이블 구조 개선

현재 구조:
```sql
CREATE TABLE MlangOrder_PrintAuto (
    no INT PRIMARY KEY,
    Type VARCHAR(50),           -- 상품 유형
    Type_1 TEXT,               -- 주문 상세 정보 (개선 대상)
    money_1 INT,               -- 인쇄비
    money_2 INT,               -- 디자인비  
    money_3 INT,               -- 부가세
    money_4 INT,               -- 인쇄비+디자인비
    money_5 INT,               -- 총 합계
    name VARCHAR(100),         -- 주문자명
    email VARCHAR(100),        -- 이메일
    -- ... 기타 필드들
);
```

개선된 Type_1 필드 사용법:
```json
{
    "product_type": "cadarok",
    "order_details": {
        "MY_type": "691",
        "MY_type_name": "카다록,리플렛",
        "MY_Fsd": "692", 
        "MY_Fsd_name": "24절(127*260)3단",
        "PN_type": "699",
        "PN_type_name": "150g(A/T,S/W)",
        "MY_amount": "1000",
        "ordertype": "print"
    },
    "formatted_display": "구분: 카다록,리플렛\n규격: 24절(127*260)3단\n종이종류: 150g(A/T,S/W)\n수량: 1,000부\n주문방법: 인쇄만",
    "created_at": "2025-01-09 10:30:00"
}
```

### 2. 디버그 로그 테이블 (선택사항)

```sql
CREATE TABLE order_debug_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no INT,
    action VARCHAR(50),
    log_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_no (order_no),
    INDEX idx_action (action)
);
```

## 오류 처리

### 1. 데이터 저장 시 오류 처리

```php
try {
    $formatter = new OrderDataFormatter($connect);
    $formatted_data = $formatter->formatOrderData($product_type, $item_data);
    
    // JSON 형태로 저장
    $type_1_data = json_encode([
        'product_type' => $product_type,
        'order_details' => $item_data,
        'formatted_display' => $formatted_data,
        'created_at' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
    // 데이터베이스 저장
    $stmt = mysqli_prepare($connect, $insert_query);
    mysqli_stmt_bind_param($stmt, 'issi...', $new_no, $product_type_name, $type_1_data, ...);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('주문 데이터 저장 실패: ' . mysqli_stmt_error($stmt));
    }
    
} catch (Exception $e) {
    // 오류 로깅
    error_log("Order save error for order $new_no: " . $e->getMessage());
    
    // 사용자에게 알림
    throw new Exception('주문 처리 중 오류가 발생했습니다. 관리자에게 문의해주세요.');
}
```

### 2. 데이터 표시 시 오류 처리

```php
try {
    $display_manager = new OrderDisplayManager($db);
    $formatted_display = $display_manager->formatOrderForDisplay($order_data);
    
} catch (Exception $e) {
    // 오류 로깅
    error_log("Order display error for order {$order_data['no']}: " . $e->getMessage());
    
    // 폴백 표시
    $formatted_display = "<div class='order-info-error'>
                            <h4>⚠️ 표시 오류</h4>
                            <p>주문 정보를 표시하는 중 오류가 발생했습니다.</p>
                            <p>원본 데이터: " . htmlspecialchars($order_data['Type_1']) . "</p>
                          </div>";
}
```

## 테스트 전략

### 1. 단위 테스트

```php
class OrderDataFormatterTest extends PHPUnit\Framework\TestCase {
    private $formatter;
    private $mock_db;
    
    public function setUp(): void {
        $this->mock_db = $this->createMock(mysqli::class);
        $this->formatter = new OrderDataFormatter($this->mock_db);
    }
    
    public function testFormatStickerData() {
        $item_data = [
            'jong' => '아트지유광',
            'garo' => 100,
            'sero' => 100,
            'mesu' => 1000,
            'domusong' => '사각',
            'uhyung' => 10000
        ];
        
        $result = $this->formatter->formatOrderData('sticker', $item_data);
        
        $this->assertStringContains('스티커 주문 정보', $result);
        $this->assertStringContains('아트지유광', $result);
        $this->assertStringContains('100mm × 100mm', $result);
    }
}
```

### 2. 통합 테스트

```php
class OrderFlowIntegrationTest extends PHPUnit\Framework\TestCase {
    public function testCompleteOrderFlow() {
        // 1. 주문 데이터 생성
        $order_data = $this->createTestOrderData();
        
        // 2. ProcessOrder_unified.php 시뮬레이션
        $order_processor = new OrderProcessor($this->db);
        $order_no = $order_processor->processOrder($order_data);
        
        // 3. 관리자 페이지에서 조회
        $admin_viewer = new OrderDisplayManager($this->db);
        $display_result = $admin_viewer->getOrderForAdmin($order_no);
        
        // 4. 검증
        $this->assertNotEmpty($display_result['formatted_display']);
        $this->assertStringContains('주문 정보', $display_result['formatted_display']);
    }
}
```

### 3. 데이터 무결성 테스트

```php
class OrderDataIntegrityTest extends PHPUnit\Framework\TestCase {
    public function testAllExistingOrdersCanBeDisplayed() {
        $query = "SELECT no FROM MlangOrder_PrintAuto ORDER BY no DESC LIMIT 100";
        $result = mysqli_query($this->db, $query);
        
        $display_manager = new OrderDisplayManager($this->db);
        $failed_orders = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            try {
                $order_data = $this->getOrderData($row['no']);
                $display_result = $display_manager->formatOrderForDisplay($order_data);
                
                if (empty($display_result)) {
                    $failed_orders[] = $row['no'];
                }
            } catch (Exception $e) {
                $failed_orders[] = $row['no'];
            }
        }
        
        $this->assertEmpty($failed_orders, 
            "Failed to display orders: " . implode(', ', $failed_orders));
    }
}
```

## 배포 계획

### 1단계: 백업 및 준비
- 현재 MlangOrder_PrintAuto 테이블 백업
- 테스트 환경에서 새로운 코드 검증

### 2단계: 점진적 배포
- OrderDataFormatter 클래스 배포
- ProcessOrder_unified.php 업데이트 (새 주문부터 개선된 형식 적용)
- 기존 주문 데이터는 그대로 유지

### 3단계: 관리자 페이지 개선
- OrderDisplayManager 클래스 배포
- OrderFormOrderTree.php 업데이트
- 기존 데이터와 새 데이터 모두 올바르게 표시되는지 확인

### 4단계: 디버깅 도구 추가
- OrderDebugger 클래스 배포
- 로그 시스템 활성화
- 모니터링 대시보드 구축

### 5단계: 데이터 마이그레이션 (선택사항)
- 기존 주문 데이터를 새로운 형식으로 변환
- 변환 스크립트 실행 및 검증

## 모니터링 및 유지보수

### 1. 실시간 모니터링
- 주문 저장 실패율 모니터링
- 관리자 페이지 표시 오류율 모니터링
- 응답 시간 모니터링

### 2. 정기 점검
- 주간 데이터 무결성 검사
- 월간 성능 리포트
- 분기별 시스템 최적화

### 3. 오류 대응
- 자동 오류 알림 시스템
- 오류 복구 프로세스
- 데이터 복원 절차

이 설계를 통해 관리자 페이지에서 주문내용이 올바르게 표시되고, 향후 유지보수가 용이한 시스템을 구축할 수 있습니다.