# 명함 주문 시스템 동적 드롭다운 기능 설계

## 개요

명함 주문 시스템에서 명함종류 선택에 따라 명함재질 드롭다운이 동적으로 변경되는 기능을 구현합니다. 이 시스템은 AJAX 기반으로 작동하며, 사용자 경험을 향상시키고 정확한 가격 계산을 제공합니다.

## 아키텍처

### 시스템 구조
```
Frontend (JavaScript)
    ↓ AJAX 요청
Backend (PHP)
    ↓ SQL 쿼리
Database (MySQL)
    ↓ 결과 반환
Backend (PHP)
    ↓ JSON 응답
Frontend (JavaScript)
    ↓ DOM 업데이트
User Interface
```

### 데이터 흐름
1. 사용자가 명함종류 드롭다운 변경
2. JavaScript 이벤트 핸들러 실행
3. AJAX 요청으로 서버에 선택된 카테고리 ID 전송
4. PHP 스크립트가 데이터베이스에서 하위 카테고리 조회
5. JSON 형태로 결과 반환
6. JavaScript가 명함재질 드롭다운 업데이트
7. 자동으로 가격 계산 실행

## 컴포넌트 및 인터페이스

### 1. Frontend 컴포넌트

#### DropdownManager 클래스
```javascript
class DropdownManager {
    constructor() {
        this.nameCardTypeSelect = document.querySelector('select[name="MY_type"]');
        this.materialSelect = document.querySelector('select[name="PN_type"]');
        this.loadingIndicator = null;
    }
    
    init() {
        // 이벤트 리스너 등록
        // 초기 데이터 로드
    }
    
    handleTypeChange(selectedValue) {
        // 명함종류 변경 처리
    }
    
    loadMaterials(categoryId) {
        // AJAX로 재질 옵션 로드
    }
    
    updateMaterialDropdown(materials) {
        // 드롭다운 옵션 업데이트
    }
    
    showLoading() {
        // 로딩 인디케이터 표시
    }
    
    hideLoading() {
        // 로딩 인디케이터 숨김
    }
}
```

### 2. Backend 컴포넌트

#### get_materials.php
```php
<?php
// AJAX 요청 처리용 PHP 스크립트
// 카테고리 ID를 받아서 하위 재질 목록 반환

class MaterialProvider {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function getMaterialsByCategory($categoryId) {
        // 데이터베이스에서 하위 카테고리 조회
        // JSON 형태로 반환
    }
    
    public function validateRequest($request) {
        // 요청 데이터 검증
    }
}
?>
```

### 3. 데이터베이스 인터페이스

#### 테이블 구조 최적화
```sql
-- 기존 테이블 구조 활용
-- MlangPrintAuto_transactionCate 테이블
-- BigNo 필드를 통한 부모-자식 관계 정의
```

## 데이터 모델

### 카테고리 데이터 구조
```json
{
    "success": true,
    "data": [
        {
            "id": "276",
            "title": "칼라코팅",
            "parent_id": "275"
        },
        {
            "id": "277", 
            "title": "칼라비코팅",
            "parent_id": "275"
        }
    ],
    "message": "재질 목록을 성공적으로 가져왔습니다."
}
```

### 오류 응답 구조
```json
{
    "success": false,
    "data": [],
    "message": "요청한 카테고리에 대한 재질 정보를 찾을 수 없습니다."
}
```

## 오류 처리

### 1. 네트워크 오류
- AJAX 요청 실패 시 재시도 메커니즘 (최대 3회)
- 타임아웃 설정 (5초)
- 사용자에게 명확한 오류 메시지 표시

### 2. 데이터 오류
- 빈 결과 처리: "해당 종류에 대한 재질 정보가 없습니다" 메시지
- 잘못된 데이터 형식: 기본값으로 복원
- 데이터베이스 연결 오류: 캐시된 데이터 사용 또는 오프라인 모드

### 3. 사용자 입력 오류
- 유효하지 않은 카테고리 선택 시 첫 번째 옵션으로 자동 복원
- JavaScript 비활성화 환경에서는 전체 페이지 새로고침으로 대체

## 테스트 전략

### 1. 단위 테스트
- DropdownManager 클래스의 각 메서드 테스트
- MaterialProvider 클래스의 데이터 조회 기능 테스트
- 데이터 검증 로직 테스트

### 2. 통합 테스트
- Frontend와 Backend 간의 AJAX 통신 테스트
- 데이터베이스 연동 테스트
- 전체 워크플로우 테스트

### 3. 사용자 인터페이스 테스트
- 다양한 브라우저에서의 호환성 테스트
- 모바일 디바이스에서의 반응성 테스트
- 접근성 테스트 (키보드 네비게이션, 스크린 리더)

### 4. 성능 테스트
- 대량 데이터 로딩 시 응답 시간 측정
- 동시 사용자 요청 처리 능력 테스트
- 메모리 사용량 모니터링

## 보안 고려사항

### 1. 입력 검증
- SQL 인젝션 방지를 위한 Prepared Statements 사용
- XSS 공격 방지를 위한 출력 데이터 이스케이핑
- CSRF 토큰을 통한 요청 검증

### 2. 데이터 접근 제어
- 권한이 없는 카테고리 접근 차단
- 세션 기반 사용자 인증 (필요시)
- API 요청 빈도 제한 (Rate Limiting)

## 성능 최적화

### 1. 캐싱 전략
- 자주 조회되는 카테고리 데이터 메모리 캐싱
- 브라우저 로컬 스토리지를 활용한 클라이언트 사이드 캐싱
- CDN을 통한 정적 리소스 배포

### 2. 데이터베이스 최적화
- 카테고리 조회를 위한 인덱스 추가
- 쿼리 실행 계획 최적화
- 연결 풀링을 통한 데이터베이스 연결 관리

### 3. 네트워크 최적화
- JSON 응답 데이터 압축
- HTTP/2 지원으로 다중 요청 처리 개선
- 불필요한 데이터 전송 최소화

## 확장성 고려사항

### 1. 새로운 카테고리 추가
- 데이터베이스 스키마 변경 없이 새로운 카테고리 추가 가능
- 관리자 인터페이스를 통한 카테고리 관리 기능

### 2. 다국어 지원
- 카테고리 제목의 다국어 버전 지원
- 사용자 언어 설정에 따른 동적 언어 변경

### 3. 모바일 최적화
- 터치 인터페이스에 최적화된 드롭다운 디자인
- 반응형 레이아웃 지원
- 오프라인 모드에서의 기본 기능 제공