# 설계 문서

## 개요

현재 `MlangPrintAuto/inserted/index.php`의 정적 드롭다운 시스템을 Ajax 기반의 동적 드롭다운 시스템으로 개선하는 설계입니다. 기존 시스템은 페이지 로드 시 모든 옵션을 미리 로드하는 방식이었으나, 새로운 시스템은 사용자의 선택에 따라 실시간으로 하위 옵션을 동적으로 로드합니다.

### 핵심 개선사항
- 정적 드롭다운 → Ajax 기반 동적 드롭다운
- 서버 부하 감소 (필요한 데이터만 로드)
- 사용자 경험 향상 (올바른 조합만 선택 가능)
- 실시간 가격 계산 자동화

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
1. **사용자 선택** → 인쇄색상(MY_type) 드롭다운 변경
2. **Ajax 요청** → 선택된 값을 서버로 전송
3. **서버 처리** → 데이터베이스에서 관련 옵션 조회
4. **응답 반환** → JSON 형태로 옵션 데이터 반환
5. **UI 업데이트** → 하위 드롭다운들 동적 업데이트
6. **가격 계산** → 모든 필수 값 선택 시 자동 계산

## 컴포넌트 및 인터페이스

### 1. Frontend 컴포넌트

#### DropdownManager 클래스
```javascript
class DropdownManager {
    constructor() {
        this.endpoints = {
            paperTypes: 'ajax/get_paper_types.php',
            paperSizes: 'ajax/get_paper_sizes.php',
            calculate: 'ajax/calculate_price.php'
        };
        this.loadingStates = new Map();
    }
    
    // 드롭다운 변경 이벤트 처리
    handleDropdownChange(dropdown, targetDropdowns)
    
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

#### AjaxController 클래스 (PHP)
```php
class AjaxController {
    private $db;
    private $validator;
    
    public function __construct($database) {
        $this->db = $database;
        $this->validator = new InputValidator();
    }
    
    // 종이종류 옵션 조회
    public function getPaperTypes($printType)
    
    // 종이규격 옵션 조회  
    public function getPaperSizes($printType, $paperType)
    
    // 가격 계산
    public function calculatePrice($params)
    
    // JSON 응답 생성
    private function jsonResponse($data, $success = true)
}
```

#### InputValidator 클래스 (PHP)
```php
class InputValidator {
    public function validatePrintType($value)
    public function validatePaperType($value)
    public function validateNumeric($value, $min = null, $max = null)
    public function sanitizeInput($input)
}
```

#### DatabaseManager 클래스 (PHP)
```php
class DatabaseManager {
    private $connection;
    
    public function getPaperTypesByPrintType($printType)
    public function getPaperSizesByTypes($printType, $paperType)
    public function getPriceData($params)
    public function executeQuery($query, $params)
}
```

### 3. API 엔드포인트

#### GET /ajax/get_paper_types.php
- **입력**: `print_type` (인쇄색상 ID)
- **출력**: 해당 인쇄색상에 맞는 종이종류 목록
- **응답 형식**:
```json
{
    "success": true,
    "data": [
        {"id": "1", "title": "실제 종이종류명1"},
        {"id": "2", "title": "실제 종이종류명2"}
    ]
}
```

#### GET /ajax/get_paper_sizes.php
- **입력**: `print_type`, `paper_type`
- **출력**: 해당 조합에 맞는 종이규격 목록
- **응답 형식**:
```json
{
    "success": true,
    "data": [
        {"id": "1", "title": "실제 종이규격명1"},
        {"id": "2", "title": "실제 종이규격명2"}
    ]
}
```

#### POST /ajax/calculate_price.php
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
        "quantity_display": "1000장"
    }
}
```

## 데이터 모델

### 데이터베이스 테이블 구조

#### MlangPrintAuto_transactionCate 테이블
```sql
-- 카테고리 계층 구조 테이블
CREATE TABLE MlangPrintAuto_transactionCate (
    no INT PRIMARY KEY,           -- 고유 ID
    Ttable VARCHAR(50),          -- 제품 타입 (inserted)
    BigNo INT,                   -- 상위 카테고리 ID (0이면 최상위)
    TreeNo INT,                  -- 트리 구조 참조 ID
    title VARCHAR(255),          -- 표시명
    -- 기타 필드들...
);
```

#### MlangPrintAuto_inserted 테이블
```sql
-- 가격 정보 테이블
CREATE TABLE MlangPrintAuto_inserted (
    id INT PRIMARY KEY,
    style INT,                   -- 인쇄색상 (MY_type)
    Section INT,                 -- 종이규격 (PN_type)  
    TreeSelect INT,              -- 종이종류 (MY_Fsd)
    quantity DECIMAL(3,1),       -- 수량 (MY_amount)
    POtype INT,                  -- 인쇄면 (1:단면, 2:양면)
    money INT,                   -- 인쇄비
    DesignMoney INT,             -- 디자인비
    quantityTwo VARCHAR(50),     -- 수량 표시용 (예: "1000장")
    -- 기타 필드들...
);
```

### 데이터 관계
- **인쇄색상** (BigNo=0) → **종이종류** (TreeNo=인쇄색상.no) → **종이규격** (BigNo=인쇄색상.no)
- 가격 계산은 모든 선택값의 조합으로 MlangPrintAuto_inserted 테이블에서 조회

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
        "message": "선택된 인쇄색상이 유효하지 않습니다.",
        "details": "print_type 값이 데이터베이스에 존재하지 않습니다."
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
- **드롭다운 연동**: 상위 선택 시 하위 업데이트
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
$printType = filter_input(INPUT_GET, 'print_type', FILTER_VALIDATE_INT);
if ($printType === false || $printType === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Prepared Statement 사용
$stmt = $db->prepare("SELECT * FROM MlangPrintAuto_transactionCate WHERE TreeNo = ? AND Ttable = ?");
$stmt->bind_param("is", $printType, $tableName);
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
- Ajax 요청 응답 시간: 500ms 이하
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
- 다른 제품 타입(명함, 스티커 등)으로 확장 가능한 구조
- 새로운 드롭다운 추가 시 최소한의 코드 변경
- API 버전 관리를 통한 하위 호환성 보장

### 확장 계획
1. **1단계**: inserted(전단지) 제품 적용
2. **2단계**: NameCard(명함) 제품 확장
3. **3단계**: 전체 제품군 적용
4. **4단계**: 모바일 최적화 및 PWA 적용