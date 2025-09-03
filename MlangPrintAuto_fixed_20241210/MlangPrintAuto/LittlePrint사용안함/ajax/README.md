# LittlePrint Ajax API 엔드포인트 구조

이 디렉토리는 LittlePrint 시스템의 Ajax 요청을 처리하는 API 엔드포인트들을 포함합니다.

## 파일 구조

```
ajax/
├── AjaxController.php         # 기본 Ajax 컨트롤러 클래스
├── InputValidator.php         # 입력값 검증 클래스 (LittlePrint 특화)
├── DatabaseManager.php        # 데이터베이스 연결 및 쿼리 관리 (LittlePrint 특화)
├── config.php                # 설정 파일 (LittlePrint 전용)
├── bootstrap.php             # 부트스트랩 파일 (공통 초기화)
├── get_paper_types.php       # 종이종류 조회 API
├── get_paper_sizes.php       # 종이규격 조회 API
├── calculate_price.php       # 가격 계산 API
├── test_connection.php       # 연결 테스트 파일
├── logs/                     # 로그 파일 디렉토리
└── README.md                # 이 파일
```

## LittlePrint 시스템 특징

### 데이터베이스 구조
- **카테고리 테이블**: `MlangPrintAuto_transactionCate` (Ttable='LittlePrint')
- **가격 테이블**: `MlangPrintAuto_LittlePrint`

### 드롭다운 관계 구조
```
종류 (Ttable='LittlePrint', BigNo=0)
├── 종이종류 (Ttable='LittlePrint', BigNo = 종류.no)
└── 종이규격 (Ttable='LittlePrint', TreeNo = 종류.no)
```

**주의**: inserted 시스템과 TreeNo/BigNo 관계가 반대입니다!

### 수량 체계
- **단위**: 매 (100매, 200매, ..., 1000매)
- **허용값**: 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000

### 인쇄면 옵션
- **양면**: 2 (기본값)
- **단면**: 1

## API 엔드포인트

### 1. 종이종류 조회
```
GET /get_paper_types.php?category_id=1
```

**응답 예시**:
```json
{
    "success": true,
    "data": [
        {"id": 10, "title": "아트지 150g"},
        {"id": 11, "title": "아트지 200g"}
    ]
}
```

### 2. 종이규격 조회
```
GET /get_paper_sizes.php?category_id=1
```

**응답 예시**:
```json
{
    "success": true,
    "data": [
        {"id": 20, "title": "A4"},
        {"id": 21, "title": "4절"}
    ]
}
```

### 3. 가격 계산
```
GET /calculate_price.php?MY_type=1&MY_Fsd=10&PN_type=20&MY_amount=100&POtype=2&ordertype=total
```

**파라미터**:
- `MY_type`: 종류 ID
- `MY_Fsd`: 종이종류 ID
- `PN_type`: 종이규격 ID
- `MY_amount`: 수량 (100~1000, 100 단위)
- `POtype`: 인쇄면 (1=단면, 2=양면)
- `ordertype`: 주문형태 (total/print/design)

**응답 예시**:
```json
{
    "success": true,
    "data": {
        "printPrice": 50000,
        "designPrice": 70000,
        "subtotal": 120000,
        "vat": 12000,
        "total": 132000,
        "quantityInfo": "100매",
        "orderType": "total"
    }
}
```

## 주요 클래스

### DatabaseManager
- LittlePrint 전용 데이터베이스 쿼리 메소드
- TreeNo/BigNo 관계 처리 (inserted와 반대)
- 카테고리와 가격 테이블 분리 처리

### InputValidator
- LittlePrint 수량 체계 검증 (100~1000매)
- 인쇄면 옵션 검증 (1, 2)
- 주문형태 검증 (total, print, design)

### AjaxController
- 공통 응답 형식 제공
- 에러 처리 및 로깅
- 입력 검증 및 보안 검사

## 사용 방법

### 1. 기본 사용법
```php
<?php
require_once __DIR__ . '/bootstrap.php';

try {
    $controller = createAjaxController();
    $controller->getPaperTypes(); // 또는 다른 메소드
} catch (Exception $e) {
    // 예외는 자동으로 처리됩니다
}
```

### 2. 직접 데이터베이스 접근
```php
<?php
require_once __DIR__ . '/bootstrap.php';

try {
    $db = createDatabaseManager();
    $categories = $db->getMainCategories();
    echo json_encode(['success' => true, 'data' => $categories]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

## 보안 기능

- CSRF 토큰 검증 (기본 구조 제공)
- SQL 인젝션 방지 (Prepared Statement 사용)
- XSS 방지 (입력값 이스케이프)
- 에러 로깅 및 모니터링
- 보안 헤더 설정

## 에러 처리

모든 에러는 다음 형식으로 반환됩니다:

```json
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "에러 메시지"
    }
}
```

## 로깅

- 에러 로그: `logs/error.log`
- 로그 형식: JSON
- 포함 정보: 타임스탬프, 메시지, 컨텍스트, IP, User-Agent

## 테스트

연결 테스트 실행:
```
http://your-domain/MlangPrintAuto/LittlePrint/ajax/test_connection.php
```

## 개발 모드

개발 모드 활성화 시 상세한 에러 정보가 표시됩니다:
```php
define('DEVELOPMENT_MODE', true);
```

## inserted 시스템과의 차이점

| 항목 | inserted | LittlePrint |
|------|----------|-------------|
| 카테고리 테이블 | MlangPrintAuto_transactionCate | 동일 |
| 가격 테이블 | MlangPrintAuto_inserted | MlangPrintAuto_LittlePrint |
| 종이종류 관계 | TreeNo = 인쇄색상.no | BigNo = 종류.no |
| 종이규격 관계 | BigNo = 인쇄색상.no | TreeNo = 종류.no |
| 수량 단위 | 연 (0.5~10) | 매 (100~1000) |
| 인쇄면 기본값 | 단면 | 양면 |