# 파일 업로드 컴포넌트 시스템 설계

## 개요

모든 품목에서 재사용 가능한 파일 업로드 컴포넌트 시스템의 상세 설계이다. 기존의 개별 구현된 업로드 시스템을 통합하여 일관성과 유지보수성을 향상시킨다.

## 아키텍처

### 전체 시스템 구조

```
Frontend (사용자 인터페이스)
├── FileUploadComponent.php (PHP 컴포넌트)
├── UniversalFileUpload.js (JavaScript 라이브러리)
└── 품목별 페이지 (스티커, 전단지, 명함 등)

Backend (서버 로직)
├── upload_handler.php (통합 업로드 처리)
├── get_files_handler.php (파일 목록 조회)
├── delete_file_handler.php (파일 삭제 처리)
└── 품목별 설정 관리

Database (데이터 저장)
├── uploaded_files (통합 파일 정보)
└── 기존 품목별 테이블들
```

## 컴포넌트 및 인터페이스

### 1. PHP 컴포넌트 (FileUploadComponent.php)

**위치:** `includes/FileUploadComponent.php`

**기능:**
- 품목별 맞춤 설정 지원
- HTML 렌더링
- 설정 검증 및 기본값 제공

**사용법:**
```php
$uploadComponent = new FileUploadComponent([
    'product_type' => 'sticker',
    'max_file_size' => 10 * 1024 * 1024,
    'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf'],
    'allowed_extensions' => ['jpg', 'png', 'pdf'],
    'custom_messages' => [
        'title' => '스티커 디자인 파일 업로드',
        'drop_text' => '스티커 디자인 파일을 드래그하세요'
    ]
]);

echo $uploadComponent->render();
```

### 2. JavaScript 라이브러리 (UniversalFileUpload.js)

**위치:** `includes/js/UniversalFileUpload.js`

**기능:**
- 드래그앤드롭 처리
- 파일 검증
- AJAX 업로드
- 진행률 표시
- 파일 목록 관리

**초기화:**
```javascript
const uploadComponent = new UniversalFileUpload('upload_container_id', {
    product_type: 'sticker',
    max_file_size: 10 * 1024 * 1024,
    allowed_types: ['image/jpeg', 'image/png', 'application/pdf'],
    upload_url: '../../includes/upload_handler.php',
    get_files_url: '../../includes/get_files_handler.php',
    delete_file_url: '../../includes/delete_file_handler.php'
});
```

### 3. 통합 업로드 핸들러 (upload_handler.php)

**위치:** `includes/upload_handler.php`

**기능:**
- 품목별 설정 적용
- 파일 검증 (크기, 형식, 확장자)
- 안전한 파일 저장
- 데이터베이스 기록

**품목별 설정:**
```php
function getProductUploadConfig($product_type) {
    $configs = [
        'sticker' => [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'png', 'pdf']
        ],
        'leaflet' => [
            'max_file_size' => 15 * 1024 * 1024,
            'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf', 'application/zip'],
            'allowed_extensions' => ['jpg', 'png', 'pdf', 'zip']
        ],
        'namecard' => [
            'max_file_size' => 5 * 1024 * 1024,
            'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'png', 'pdf']
        ],
        'cadarok' => [
            'max_file_size' => 20 * 1024 * 1024,
            'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf', 'application/zip'],
            'allowed_extensions' => ['jpg', 'png', 'pdf', 'zip']
        ]
    ];
    
    return $configs[$product_type] ?? $default_config;
}
```

## 데이터 모델

### uploaded_files 테이블

```sql
CREATE TABLE uploaded_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_session (session_id),
    INDEX idx_product_type (product_type),
    INDEX idx_session_product (session_id, product_type)
);
```

## 품목별 적용 방법

### 1. 스티커 페이지 적용

**파일:** `MlangPrintAuto/shop/view_modern.php`

```php
// 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 스티커용 설정
$uploadComponent = new FileUploadComponent([
    'product_type' => 'sticker',
    'max_file_size' => 10 * 1024 * 1024,
    'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf'],
    'allowed_extensions' => ['jpg', 'png', 'pdf'],
    'custom_messages' => [
        'title' => '스티커 디자인 파일 업로드',
        'drop_text' => '스티커 디자인 파일을 드래그하세요'
    ]
]);

// 렌더링
echo $uploadComponent->render();
```

### 2. 전단지 페이지 적용

**파일:** `MlangPrintAuto/inserted/index.php`

```php
// 전단지용 설정
$uploadComponent = new FileUploadComponent([
    'product_type' => 'leaflet',
    'max_file_size' => 15 * 1024 * 1024,
    'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf', 'application/zip'],
    'allowed_extensions' => ['jpg', 'png', 'pdf', 'zip'],
    'custom_messages' => [
        'title' => '전단지 디자인 파일 업로드',
        'drop_text' => '전단지 디자인 파일을 드래그하세요',
        'format_text' => '지원 형식: JPG, PNG, PDF, ZIP (최대 15MB)'
    ]
]);

echo $uploadComponent->render();
```

### 3. 명함 페이지 적용

```php
// 명함용 설정
$uploadComponent = new FileUploadComponent([
    'product_type' => 'namecard',
    'max_file_size' => 5 * 1024 * 1024,
    'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf'],
    'allowed_extensions' => ['jpg', 'png', 'pdf'],
    'custom_messages' => [
        'title' => '명함 디자인 파일 업로드',
        'drop_text' => '명함 디자인 파일을 드래그하세요',
        'format_text' => '지원 형식: JPG, PNG, PDF (최대 5MB)'
    ]
]);

echo $uploadComponent->render();
```

## 보안 고려사항

### 1. 파일 검증

- **이중 검증**: MIME 타입과 확장자 모두 검증
- **크기 제한**: 품목별 최대 파일 크기 적용
- **경로 보안**: 안전한 업로드 디렉토리 사용

### 2. 세션 보안

- **세션 기반 접근 제어**: 본인이 업로드한 파일만 접근 가능
- **파일명 암호화**: 고유한 파일명으로 저장하여 직접 접근 방지

### 3. 데이터베이스 보안

- **Prepared Statements**: SQL Injection 방지
- **입력값 검증**: 모든 입력값에 대한 검증 수행

## 성능 최적화

### 1. 파일 업로드 최적화

- **청크 업로드**: 대용량 파일의 경우 청크 단위 업로드 지원
- **진행률 표시**: 실시간 업로드 진행률 표시
- **동시 업로드**: 여러 파일 동시 업로드 지원

### 2. 데이터베이스 최적화

- **인덱스 활용**: 세션ID와 품목 유형에 대한 복합 인덱스
- **자동 정리**: 오래된 임시 파일 자동 삭제 기능

## 확장성 고려사항

### 1. 새로운 품목 추가

```php
// 새 품목 설정 추가
'new_product' => [
    'max_file_size' => 25 * 1024 * 1024,
    'allowed_types' => ['image/jpeg', 'application/pdf', 'application/zip'],
    'allowed_extensions' => ['jpg', 'pdf', 'zip']
]
```

### 2. 기능 확장

- **이미지 미리보기**: 업로드된 이미지 썸네일 표시
- **파일 압축**: 자동 이미지 압축 기능
- **클라우드 저장소**: AWS S3 등 클라우드 저장소 연동

## 테스트 전략

### 1. 단위 테스트

- 파일 검증 로직 테스트
- 품목별 설정 적용 테스트
- 데이터베이스 CRUD 테스트

### 2. 통합 테스트

- 전체 업로드 플로우 테스트
- 다양한 파일 형식 테스트
- 오류 상황 처리 테스트

### 3. 사용자 테스트

- 드래그앤드롭 기능 테스트
- 다양한 브라우저 호환성 테스트
- 모바일 환경 테스트