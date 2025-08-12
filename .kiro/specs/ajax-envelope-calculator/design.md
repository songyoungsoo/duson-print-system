# 설계 문서

## 개요

현재 `MlangPrintAuto/envelope/index.php`의 정적 드롭다운 시스템을 Ajax 기반의 동적 드롭다운 시스템으로 개선하는 설계입니다. envelope 시스템은 2단계 드롭다운 구조를 가지고 있어 inserted 시스템보다 단순하고 안전하게 구현할 수 있습니다.

### 핵심 개선사항
- 정적 드롭다운 → Ajax 기반 동적 드롭다운
- 2단계 드롭다운 구조 (구분 → 종류)
- 서버 부하 감소 (필요한 데이터만 로드)
- 사용자 경험 향상 (올바른 조합만 선택 가능)
- 실시간 가격 계산 자동화 (별도 계산 버튼 불필요)
- 기존 시스템과의 완전한 호환성 보장

## 아키텍처

### 시스템 구조
```
Frontend (JavaScript)
    ↓ Ajax 요청
Backend API (PHP)
    ↓ 데이터베이스 쿼리
Database (MySQL)
    ↓ 결과 반환
Backend API (PHP)
    ↓ JSON 응답
Frontend (JavaScript)
    ↓ DOM 업데이트
User Interface
```

### 데이터 흐름
1. **사용자 선택** → 구분(MY_type) 드롭다운 변경
2. **드롭다운 초기화** → 종류(PN_type) 드롭다운 초기화 및 로딩 표시
3. **Ajax 요청** → 선택된 구분 값을 서버로 전송
4. **서버 처리** → 데이터베이스에서 해당 구분의 종류 옵션 조회
5. **응답 반환** → JSON 형태로 종류 데이터 반환
6. **UI 업데이트** → 종류(PN_type) 드롭다운 동적 업데이트
7. **가격 계산** → 모든 필수 값 선택 완료 시 자동 계산 실행
8. **결과 표시** → 인쇄비, 디자인비, 총액 실시간 업데이트

## 컴포넌트 및 인터페이스

### 1. Frontend 컴포넌트

#### EnvelopeDropdownManager 클래스
```javascript
class EnvelopeDropdownManager {
    constructor() {
        this.endpoints = {
            envelopeTypes: 'ajax/get_envelope_types.php',
            calculate: 'ajax/calculate_envelope_price.php'
        };
        this.loadingStates = new Map();
        this.dropdownChain = ['MY_type', 'PN_type']; // 2단계 구조
    }
    
    // 드롭다운 변경 이벤트 처리
    handleDropdownChange(dropdown, targetDropdown)
    
    // Ajax 요청 실행
    makeAjaxRequest(url, data, callback)
    
    // 드롭다운 옵션 업데이트
    updateDropdownOptions(dropdown, options)
    
    // 로딩 상태 관리
    setLoadingState(dropdown, isLoading)
    
    // 가격 계산 트리거
    triggerPriceCalculation()
}
```

#### LoadingIndicator 클래스
```javascript
class LoadingIndicator {
    show(element)
    hide(element)
    showSpinner(dropdown)
    hideSpinner(dropdown)
}
```

#### ErrorHandler 클래스
```javascript
class ErrorHandler {
    handleAjaxError(xhr, status, error)
    showUserMessage(message, type)
    logError(error, context)
}
```

### 2. Backend API 컴포넌트

#### EnvelopeAjaxController 클래스 (PHP)
```php
class EnvelopeAjaxController {
    private $db;
    private $validator;
    
    public function __construct($database) {
        $this->db = $database;
        $this->validator = new InputValidator();
    }
    
    // 종류 옵션 조회 (구분 기반)
    public function getEnvelopeTypes($categoryType)
    
    // 가격 계산 (모든 드롭다운 값 기반)
    public function calculatePrice($params)
    
    // JSON 응답 생성
    private function jsonResponse($data, $success = true)
    
    // 에러 응답 생성
    private function errorResponse($message, $code = 'GENERAL_ERROR')
}
```

#### InputValidator 클래스 (PHP)
```php
class InputValidator {
    public function validateCategoryType($value)
    public function validateEnvelopeType($value)
    public function validateNumeric($value, $min = null, $max = null)
    public function sanitizeInput($input)
}
```

#### DatabaseManager 클래스 (PHP)
```php
class DatabaseManager {
    private $connection;
    
    public function getEnvelopeTypesByCategory($categoryType)
    public function getEnvelopePriceData($params)
    public function executeQuery($query, $params)
}
```

### 3. API 엔드포인트

#### GET /ajax/get_envelope_types.php
- **입력**: `category_type` (구분 ID)
- **출력**: 해당 구분에 맞는 종류 목록
- **응답 형식**:
```json
{
    "success": true,
    "data": [
        {"id": "1", "title": "실제 종류명1"},
        {"id": "2", "title": "실제 종류명2"}
    ]
}
```

#### POST /ajax/calculate_envelope_price.php
- **입력**: 모든 드롭다운 선택값
- **출력**: 계산된 가격 정보
- **응답 형식**:
```json
{
    "success": true,
    "data": {
        "print_price": 50000,
        "design_price": 30000,
        "total_price": 80000,
        "quantity_display": "1000매"
    }
}
```

#### 실시간 가격 계산 특징
- **자동 트리거**: 모든 필수 드롭다운 선택 완료 시 자동 실행
- **별도 버튼 불필요**: 기존 calc_ok 버튼 클릭 없이 즉시 계산
- **필수값 검증**: 누락된 값이 있으면 가격 필드 초기화 및 안내 메시지 표시
- **기존 연동**: 계산 결과를 기존 폼 필드에 자동 입력하여 주문 프로세스와 연동

## 데이터 모델

### 데이터베이스 테이블 구조

#### mlangprintauto_transactioncate 테이블
```sql
-- 카테고리 계층 구조 테이블
CREATE TABLE mlangprintauto_transactioncate (
    no INT PRIMARY KEY,           -- 고유 ID
    Ttable VARCHAR(50),          -- 제품 타입 (envelope)
    BigNo INT,                   -- 상위 카테고리 ID (0이면 최상위)
    TreeNo INT,                  -- 트리 구조 참조 ID
    title VARCHAR(255),          -- 표시명
    -- 기타 필드들...
);
```

#### mlangprintauto_envelope 테이블
```sql
-- 가격 정보 테이블
CREATE TABLE mlangprintauto_envelope (
    id INT PRIMARY KEY,
    style INT,                   -- 구분 (MY_type)
    Section INT,                 -- 종류 (PN_type)  
    quantity VARCHAR(50),        -- 수량 (MY_amount)
    POtype INT,                  -- 인쇄색상 (1:마스터1도, 2:마스터2도, 3:칼라4도)
    money INT,                   -- 인쇄비
    DesignMoney INT,             -- 디자인비
    quantityTwo VARCHAR(50),     -- 수량 표시용 (예: "1000매")
    -- 기타 필드들...
);
```

### 데이터 관계
- **구분** (BigNo=0, Ttable='envelope') → **종류** (BigNo=구분.no, Ttable='envelope')
- 가격 계산은 모든 선택값의 조합으로 mlangprintauto_envelope 테이블에서 조회

## 에러 처리

### 클라이언트 사이드 에러 처리
1. **네트워크 에러**: 연결 실패, 타임아웃
   - 사용자에게 재시도 옵션 제공
   - 이전 상태로 복원

2. **데이터 에러**: 잘못된 응답, 빈 데이터
   - 적절한 안내 메시지 표시
   - 드롭다운 초기화

3. **사용자 입력 에러**: 필수값 누락
   - 실시간 유효성 검사
   - 시각적 피드백 제공

### 서버 사이드 에러 처리
1. **입력 검증 에러**
   - HTTP 400 Bad Request
   - 구체적인 에러 메시지 반환

2. **데이터베이스 에러**
   - HTTP 500 Internal Server Error
   - 로그 기록 및 일반적인 에러 메시지 반환

3. **권한 에러**
   - HTTP 403 Forbidden
   - 접근 거부 메시지 반환

### 에러 응답 형식
```json
{
    "success": false,
    "error": {
        "code": "INVALID_INPUT",
        "message": "선택된 구분이 유효하지 않습니다.",
        "details": "category_type 값이 데이터베이스에 존재하지 않습니다."
    }
}
```

## 테스팅 전략

### 1. 단위 테스트
- **JavaScript 함수**: 각 드롭다운 처리 함수
- **PHP 클래스**: 데이터베이스 쿼리, 입력 검증
- **API 엔드포인트**: 각 API의 입출력 검증

### 2. 통합 테스트
- **Ajax 통신**: 프론트엔드-백엔드 연동
- **데이터베이스 연동**: 실제 데이터로 테스트
- **에러 시나리오**: 네트워크 실패, 잘못된 데이터

### 3. 사용자 인터페이스 테스트
- **드롭다운 연동**: 구분 선택 시 종류 업데이트
- **로딩 상태**: 스피너 표시/숨김
- **에러 메시지**: 사용자 친화적 메시지 표시

### 4. 성능 테스트
- **응답 시간**: Ajax 요청 응답 속도
- **동시 요청**: 여러 사용자 동시 접속
- **메모리 사용량**: JavaScript 메모리 누수 검사

### 테스트 데이터 시나리오
1. **정상 시나리오**: 모든 드롭다운 정상 선택
2. **빈 데이터 시나리오**: 해당하는 옵션이 없는 경우
3. **네트워크 지연 시나리오**: 느린 네트워크 환경
4. **에러 시나리오**: 서버 에러, 잘못된 입력값

## 보안 고려사항

### 1. 입력 검증
- 모든 사용자 입력에 대한 서버 사이드 검증
- SQL 인젝션 방지를 위한 Prepared Statement 사용
- XSS 방지를 위한 출력 이스케이프 처리

### 2. 인증 및 권한
- 세션 기반 사용자 인증 유지
- CSRF 토큰을 통한 요청 검증
- API 엔드포인트 접근 권한 확인

### 3. 데이터 보호
- 민감한 정보 로그 기록 금지
- 에러 메시지에서 시스템 정보 노출 방지
- 데이터베이스 연결 정보 보안

### 보안 구현 예시
```php
// 입력 검증 및 이스케이프
$categoryType = filter_input(INPUT_GET, 'category_type', FILTER_VALIDATE_INT);
if ($categoryType === false || $categoryType === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Prepared Statement 사용
$stmt = $db->prepare("SELECT * FROM mlangprintauto_transactioncate WHERE BigNo = ? AND Ttable = ?");
$stmt->bind_param("is", $categoryType, 'envelope');
$stmt->execute();
```

## 성능 최적화

### 1. 프론트엔드 최적화
- **Ajax 요청 캐싱**: 동일한 요청 결과 캐시
- **디바운싱**: 연속된 요청 방지
- **DOM 조작 최소화**: 배치 업데이트 사용

### 2. 백엔드 최적화
- **데이터베이스 인덱스**: 자주 조회되는 컬럼에 인덱스 추가
- **쿼리 최적화**: 불필요한 JOIN 제거, 필요한 컬럼만 SELECT
- **응답 압축**: Gzip 압축 활성화

### 3. 캐싱 전략
- **브라우저 캐싱**: 정적 자원에 대한 캐시 헤더 설정
- **서버 사이드 캐싱**: 자주 조회되는 데이터 메모리 캐시
- **CDN 활용**: 정적 자원 CDN 배포

### 성능 목표
- Ajax 요청 응답 시간: 300ms 이하 (envelope은 더 단순한 구조)
- 페이지 로드 시간: 2초 이하
- 동시 사용자: 100명 이상 지원

## 호환성 및 확장성

### 1. 기존 시스템 호환성
- 기존 폼 제출 기능 유지
- 기존 JavaScript 함수들과 충돌 방지
- 파일 업로드 기능과의 연동 보장

### 2. 브라우저 호환성
- Internet Explorer 11 이상
- Chrome, Firefox, Safari 최신 버전
- 모바일 브라우저 지원

### 3. 확장 가능성
- 다른 제품 타입으로 확장 가능한 구조
- 새로운 드롭다운 추가 시 최소한의 코드 변경
- API 버전 관리를 통한 하위 호환성 보장

### envelope 시스템의 장점
1. **단순한 구조**: 2단계 드롭다운으로 구현 복잡도 낮음
2. **안전한 적용**: 기존 시스템 영향 최소화
3. **빠른 성능**: 적은 데이터 처리로 빠른 응답
4. **확장 용이**: 성공 후 다른 시스템으로 확장 가능

### envelope 시스템 특화 설계 결정사항

#### 2단계 드롭다운 구조 선택 이유
- **단순성**: inserted 시스템의 복잡한 4단계 구조 대비 관리 용이
- **안정성**: 적은 의존성으로 에러 발생 가능성 최소화
- **성능**: 적은 Ajax 요청으로 빠른 응답 시간 보장
- **사용자 경험**: 직관적인 선택 흐름으로 사용자 혼란 방지

#### 기존 시스템 호환성 보장 전략
- **점진적 적용**: 기존 정적 드롭다운과 Ajax 시스템 병행 운영
- **폴백 메커니즘**: Ajax 실패 시 기존 방식으로 자동 전환
- **기존 함수 보존**: change_Field, calc_ok 등 기존 함수 유지
- **데이터 구조 유지**: 기존 폼 필드명과 값 형식 완전 호환