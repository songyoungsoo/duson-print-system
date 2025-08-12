# Envelope Ajax 시스템

envelope 제품을 위한 Ajax 기반 동적 드롭다운 및 가격 계산 시스템입니다.

## 개요

이 시스템은 기존의 정적 드롭다운을 Ajax 기반의 동적 드롭다운으로 개선하여 사용자 경험을 향상시킵니다.

### 주요 특징

- **2단계 드롭다운**: 구분(MY_type) → 종류(PN_type)
- **실시간 가격 계산**: 모든 옵션 선택 시 자동 계산
- **보안 강화**: SQL 인젝션 방지, 입력값 검증
- **에러 처리**: 사용자 친화적 에러 메시지
- **로깅 시스템**: 디버깅 및 모니터링 지원

## 파일 구조

```
ajax/
├── config.php              # 기본 설정 및 공통 함수
├── bootstrap.php           # 시스템 초기화
├── InputValidator.php      # 입력값 검증 클래스
├── DatabaseManager.php     # 데이터베이스 관리 클래스
├── AjaxController.php      # Ajax 요청 처리 컨트롤러
├── get_envelope_types.php  # 종류 조회 API
├── calculate_envelope_price.php # 가격 계산 API
├── test_connection.php     # 연결 테스트
└── logs/                   # 로그 파일 디렉토리
```

## API 엔드포인트

### 1. 종류 조회 API

**URL**: `ajax/get_envelope_types.php`
**Method**: GET
**Parameters**:
- `category_type` (required): 구분 ID

**Response**:
```json
{
    "success": true,
    "timestamp": "2025-01-25 10:30:00",
    "data": {
        "message": "종류 목록을 성공적으로 조회했습니다.",
        "types": [
            {"id": "1", "title": "종류명1"},
            {"id": "2", "title": "종류명2"}
        ]
    }
}
```

### 2. 가격 계산 API

**URL**: `ajax/calculate_envelope_price.php`
**Method**: POST
**Parameters**:
- `MY_type` (required): 구분 ID
- `PN_type` (required): 종류 ID
- `MY_amount` (required): 수량
- `POtype` (required): 인쇄색상 (1:마스터1도, 2:마스터2도, 3:칼라4도)
- `ordertype` (required): 주문타입 (total, print, design)

**Response**:
```json
{
    "success": true,
    "timestamp": "2025-01-25 10:30:00",
    "data": {
        "message": "가격이 성공적으로 계산되었습니다.",
        "price_data": {
            "print_price": 50000,
            "design_price": 30000,
            "subtotal_price": 80000,
            "vat_price": 8000,
            "total_price": 88000,
            "quantity_display": "1000매",
            "formatted": {
                "print_price": "50,000",
                "design_price": "30,000",
                "subtotal_price": "80,000",
                "vat_price": "8,000",
                "total_price": "88,000"
            }
        }
    }
}
```

## 에러 응답

모든 API는 에러 발생 시 다음 형식으로 응답합니다:

```json
{
    "success": false,
    "timestamp": "2025-01-25 10:30:00",
    "error": {
        "code": "ERROR_CODE",
        "message": "사용자 친화적 에러 메시지",
        "details": "상세 에러 정보 (선택적)"
    }
}
```

### 주요 에러 코드

- `MISSING_PARAMETER`: 필수 파라미터 누락
- `INVALID_PARAMETER`: 잘못된 파라미터 값
- `CATEGORY_NOT_FOUND`: 존재하지 않는 구분
- `TYPE_NOT_FOUND`: 존재하지 않는 종류
- `DATABASE_ERROR`: 데이터베이스 오류
- `CALCULATION_ERROR`: 가격 계산 오류
- `INTERNAL_ERROR`: 서버 내부 오류

## 데이터베이스 테이블

### mlangprintauto_transactioncate
카테고리 계층 구조를 관리하는 테이블

- `no`: 고유 ID
- `Ttable`: 제품 타입 ('envelope')
- `BigNo`: 상위 카테고리 ID (0이면 최상위)
- `TreeNo`: 트리 구조 참조 ID
- `title`: 표시명

### mlangprintauto_envelope
가격 정보를 저장하는 테이블

- `id`: 고유 ID
- `style`: 구분 (MY_type)
- `Section`: 종류 (PN_type)
- `quantity`: 수량 (MY_amount)
- `POtype`: 인쇄색상
- `money`: 인쇄비
- `DesignMoney`: 디자인비
- `quantityTwo`: 수량 표시용

## 설정

### config.php 주요 설정

```php
// 테이블명 설정
define('ENVELOPE_TABLE', 'mlangprintauto_envelope');
define('CATEGORY_TABLE', 'mlangprintauto_transactioncate');
define('PRODUCT_TYPE', 'envelope');

// 로깅 설정
define('ENABLE_LOGGING', true);
define('DEBUG_MODE', false);

// 타임아웃 설정
define('AJAX_TIMEOUT', 30);
```

## 사용법

### JavaScript에서 API 호출

```javascript
// 종류 조회
fetch('ajax/get_envelope_types.php?category_type=1')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 처리
            console.log(data.data.types);
        } else {
            // 에러 처리
            console.error(data.error.message);
        }
    });

// 가격 계산
const formData = new FormData();
formData.append('MY_type', '1');
formData.append('PN_type', '2');
formData.append('MY_amount', '1000');
formData.append('POtype', '2');
formData.append('ordertype', 'total');

fetch('ajax/calculate_envelope_price.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // 가격 정보 처리
        console.log(data.data.price_data);
    } else {
        // 에러 처리
        console.error(data.error.message);
    }
});
```

## 테스트

### 연결 테스트

`ajax/test_connection.php`를 브라우저에서 접속하여 시스템 상태를 확인할 수 있습니다.

### 로그 확인

로그 파일은 `ajax/logs/envelope_ajax.log`에 저장됩니다.

## 보안 고려사항

1. **입력값 검증**: 모든 사용자 입력은 검증됩니다
2. **SQL 인젝션 방지**: Prepared Statement 사용
3. **XSS 방지**: 출력값 이스케이프 처리
4. **에러 정보 노출 방지**: 프로덕션에서는 상세 에러 정보 숨김

## 성능 최적화

1. **데이터베이스 인덱스**: 자주 조회되는 컬럼에 인덱스 설정
2. **쿼리 최적화**: 필요한 컬럼만 SELECT
3. **캐싱**: 브라우저 캐싱 활용
4. **압축**: Gzip 압축 사용

## 문제 해결

### 일반적인 문제

1. **데이터베이스 연결 실패**
   - db.php 파일의 연결 정보 확인
   - 데이터베이스 서버 상태 확인

2. **권한 오류**
   - 로그 디렉토리 쓰기 권한 확인
   - 파일 권한 설정 확인

3. **API 응답 없음**
   - 웹 서버 에러 로그 확인
   - PHP 에러 로그 확인

### 디버깅

1. `config.php`에서 `DEBUG_MODE`를 `true`로 설정
2. 로그 파일에서 상세 정보 확인
3. 브라우저 개발자 도구에서 네트워크 탭 확인

## 버전 정보

- **Version**: 1.0.0
- **Created**: 2025-01-25
- **Last Updated**: 2025-01-25
- **Author**: Kiro AI Assistant