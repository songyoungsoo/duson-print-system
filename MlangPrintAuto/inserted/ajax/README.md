# Ajax API 엔드포인트 구조

이 디렉토리는 드롭다운 계산기의 Ajax 요청을 처리하는 API 엔드포인트들을 포함합니다.

## 파일 구조

```
ajax/
├── AjaxController.php      # 기본 Ajax 컨트롤러 클래스
├── InputValidator.php      # 입력값 검증 클래스
├── DatabaseManager.php     # 데이터베이스 연결 및 쿼리 관리
├── config.php             # 설정 파일
├── bootstrap.php          # 부트스트랩 파일 (공통 초기화)
├── test_connection.php    # 연결 테스트 파일
├── logs/                  # 로그 파일 디렉토리
└── README.md             # 이 파일
```

## 주요 클래스

### AjaxController
- Ajax 요청의 기본 처리를 담당
- 공통 응답 형식 제공 (성공/에러)
- 에러 처리 및 로깅
- 입력 검증 및 보안 검사

### InputValidator
- 사용자 입력값의 유효성 검증
- SQL 인젝션 방지
- 데이터 타입 검증 및 정리

### DatabaseManager
- 데이터베이스 연결 관리
- Prepared Statement를 사용한 안전한 쿼리 실행
- 트랜잭션 지원
- 연결 상태 모니터링

## 사용 방법

### 1. 기본 사용법
```php
<?php
require_once __DIR__ . '/bootstrap.php';

try {
    $controller = createAjaxController();
    
    // 입력값 검증
    $validator = new InputValidator();
    $result = $validator->validatePrintType($_GET['print_type']);
    
    if (!$result[0]) {
        echo $controller->errorResponse($result[2], 'VALIDATION_ERROR');
        exit;
    }
    
    // 성공 응답
    echo $controller->successResponse(['data' => 'success']);
    
} catch (Exception $e) {
    // 예외는 자동으로 처리됩니다
}
```

### 2. 데이터베이스 쿼리
```php
<?php
require_once __DIR__ . '/bootstrap.php';

try {
    $db = createDatabaseManager();
    $paperTypes = $db->getPaperTypesByPrintType(1);
    
    echo json_encode(['success' => true, 'data' => $paperTypes]);
    
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
http://your-domain/MlangPrintAuto/inserted/ajax/test_connection.php
```

## 개발 모드

개발 모드 활성화 시 상세한 에러 정보가 표시됩니다:
```php
define('DEVELOPMENT_MODE', true);
```