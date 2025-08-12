# 일반 스티커 통합 시스템 설계

## 개요

기존 인쇄 자동화 시스템에 일반 스티커를 통합하고, 네비게이션 구조를 개선하여 사용자 경험을 향상시키는 시스템의 상세 설계이다.

## 아키텍처

### 전체 시스템 구조

```
Frontend (사용자 인터페이스)
├── 네비게이션 (드롭다운 메뉴)
├── 일반 스티커 주문 페이지
├── 통합 장바구니
└── 주문 페이지

Backend (서버 로직)
├── 통합 가격 계산 엔진
├── 장바구니 관리 시스템
├── 주문 처리 시스템
└── 관리자 시스템

Database (데이터 저장)
├── shop_temp (통합 장바구니)
├── MlangOrder_PrintAuto (통합 주문)
├── shop_d1~d4 (스티커 가격 테이블)
└── MlangPrintAuto_* (기타 상품 테이블)
```

## 컴포넌트 및 인터페이스

### 1. 네비게이션 컴포넌트

**파일:** `includes/nav.php`

**기능:**
- 드롭다운 메뉴를 통한 스티커 유형 선택
- 현재 페이지 활성화 표시
- 반응형 디자인 지원

**인터페이스:**
```php
interface NavigationInterface {
    public function renderNavigation($current_page);
    public function renderDropdownMenu($menu_items);
    public function isActivePage($page_name, $current_page);
}
```

### 2. 통합 가격 계산 엔진

**파일:** `includes/UnifiedPriceCalculator.php`

**기능:**
- 상품별 가격 계산 방식 분기
- 스티커 수식 기반 계산
- 테이블 기반 계산
- 통일된 응답 형식

**인터페이스:**
```php
interface PriceCalculatorInterface {
    public function calculatePrice($product_type, $options);
    public function validateOptions($product_type, $options);
    public function formatPriceResponse($price_data);
}
```

### 3. 장바구니 관리 시스템

**파일:** `MlangPrintAuto/shop_temp_helper.php`

**기능:**
- 상품별 장바구니 추가
- 동적 테이블 컬럼 관리
- 장바구니 아이템 표시 포맷팅
- 통합 주문 처리

**인터페이스:**
```php
interface CartManagerInterface {
    public function addToCart($product_type, $options, $price_data);
    public function getCartItems($session_id);
    public function formatCartItem($item);
    public function removeFromCart($item_id, $session_id);
}
```

### 4. 주문 처리 시스템

**파일:** `MlangOrder_PrintAuto/UnifiedOrderProcessor.php`

**기능:**
- 상품별 주문 데이터 준비
- 파일 폴더 경로 생성
- 주문 정보 저장
- 고객 알림 발송

**인터페이스:**
```php
interface OrderProcessorInterface {
    public function processOrder($cart_items, $customer_info);
    public function prepareOrderData($item, $customer_info);
    public function generateImageFolder($customer_info);
    public function saveOrder($order_data);
}
```

## 데이터 모델

### 1. shop_temp 테이블 (통합 장바구니)

```sql
CREATE TABLE shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) NOT NULL,
    
    -- 공통 필드
    MY_type VARCHAR(50),
    MY_Fsd VARCHAR(50),
    PN_type VARCHAR(50),
    MY_amount VARCHAR(50),
    POtype VARCHAR(10),
    ordertype VARCHAR(50),
    
    -- 스티커 전용 필드
    jong VARCHAR(200),
    garo VARCHAR(50),
    sero VARCHAR(50),
    mesu VARCHAR(50),
    uhyung INT(1) DEFAULT 0,
    domusong VARCHAR(200),
    
    -- 가격 정보
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    
    -- 추가 정보
    MY_comment TEXT,
    img VARCHAR(200),
    regdate INT(11),
    
    INDEX idx_session (session_id),
    INDEX idx_product_type (product_type)
);
```

### 2. MlangOrder_PrintAuto 테이블 (통합 주문)

```sql
CREATE TABLE MlangOrder_PrintAuto (
    no INT AUTO_INCREMENT PRIMARY KEY,
    Type VARCHAR(50),               -- 상품 유형
    ImgFolder VARCHAR(200),         -- 업로드 파일 폴더 경로
    Type_1 TEXT,                   -- 상품 상세 정보 (JSON)
    
    -- 가격 정보
    money_1 VARCHAR(50),           -- 기본 인쇄비
    money_2 VARCHAR(50),           -- 디자인/편집비
    money_3 VARCHAR(50),           -- 소계
    money_4 VARCHAR(50),           -- 부가세
    money_5 VARCHAR(50),           -- 총액
    
    -- 주문자 정보
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(50),
    Hendphone VARCHAR(50),
    
    -- 배송 정보
    zip VARCHAR(10),
    zip1 VARCHAR(100),
    zip2 VARCHAR(100),
    delivery VARCHAR(200),
    
    -- 사업자 정보
    bizname VARCHAR(100),
    bank VARCHAR(50),
    bankname VARCHAR(50),
    
    -- 추가 정보
    cont TEXT,
    date DATETIME,
    OrderStyle VARCHAR(10),
    ThingCate VARCHAR(100),        -- 대표 이미지 파일명
    pass VARCHAR(50),
    Gensu INT DEFAULT 0,
    Designer VARCHAR(50),
    
    -- 상태 관리
    status ENUM('new', 'processing', 'proofing', 'approved', 'printing', 'shipping', 'completed', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 오류 처리

### 1. 가격 계산 오류

**오류 유형:**
- 필수 입력값 누락
- 크기/수량 제한 초과
- 데이터베이스 연결 실패
- 계산 로직 오류

**처리 방식:**
```php
try {
    $price = $calculator->calculatePrice($product_type, $options);
    return ['success' => true, 'data' => $price];
} catch (ValidationException $e) {
    return ['success' => false, 'error' => 'validation', 'message' => $e->getMessage()];
} catch (DatabaseException $e) {
    return ['success' => false, 'error' => 'database', 'message' => '시스템 오류가 발생했습니다.'];
} catch (Exception $e) {
    return ['success' => false, 'error' => 'general', 'message' => '알 수 없는 오류가 발생했습니다.'];
}
```

### 2. 장바구니 오류

**오류 유형:**
- 세션 만료
- 테이블 구조 불일치
- 중복 추가
- 권한 오류

**처리 방식:**
- 자동 테이블 컬럼 추가
- 세션 재생성
- 사용자 친화적 오류 메시지
- 로그 기록

### 3. 주문 처리 오류

**오류 유형:**
- 결제 정보 오류
- 파일 업로드 실패
- 이메일 발송 실패
- 재고 부족

**처리 방식:**
- 트랜잭션 롤백
- 오류 알림
- 관리자 통지
- 고객 안내

## 테스트 전략

### 1. 단위 테스트

**대상:**
- 가격 계산 로직
- 데이터 검증 함수
- 포맷팅 함수
- 유틸리티 함수

**도구:**
- PHPUnit
- 모의 객체(Mock)
- 테스트 데이터베이스

### 2. 통합 테스트

**대상:**
- 전체 주문 플로우
- 장바구니 연동
- 이메일 발송
- 파일 업로드

**시나리오:**
- 정상 주문 처리
- 오류 상황 처리
- 동시 접속 처리
- 대용량 데이터 처리

### 3. 사용자 테스트

**대상:**
- 사용자 인터페이스
- 사용성
- 접근성
- 성능

**방법:**
- A/B 테스트
- 사용자 피드백
- 성능 모니터링
- 오류 추적

## 보안 고려사항

### 1. 입력값 검증

- SQL Injection 방지
- XSS 공격 방지
- CSRF 토큰 사용
- 파일 업로드 검증

### 2. 세션 보안

- 세션 하이재킹 방지
- 세션 고정 공격 방지
- 적절한 세션 만료 시간
- 보안 쿠키 설정

### 3. 데이터 보호

- 개인정보 암호화
- 결제 정보 보안
- 파일 접근 제한
- 로그 보안

## 성능 최적화

### 1. 데이터베이스 최적화

- 인덱스 최적화
- 쿼리 성능 개선
- 연결 풀링
- 캐싱 전략

### 2. 프론트엔드 최적화

- JavaScript 최적화
- CSS 압축
- 이미지 최적화
- CDN 활용

### 3. 서버 최적화

- PHP 성능 튜닝
- 메모리 관리
- 파일 시스템 최적화
- 로드 밸런싱