---
inclusion: always
---

# 일반 스티커 통합 시스템 가이드

## 시스템 개요

인쇄 자동화 시스템에 일반 스티커를 통합하는 프로젝트입니다. 기존 시스템의 복잡한 구조를 유지하면서도 사용자 경험을 개선하고 관리 효율성을 높이는 것이 목표입니다.

## 핵심 특징

### 1. 이중 스티커 시스템
- **일반 스티커**: 복잡한 수식 기반 가격 계산 (`/MlangPrintAuto/shop/view_modern.php`)
- **자석 스티커**: 테이블 기반 가격 계산 (`/MlangPrintAuto/msticker/index.php`)
- 네비게이션에서 드롭다운 메뉴로 구분

### 2. 가격 계산 방식 차이
```php
// 일반 스티커: 수식 기반 계산
$price = (($garo + 4) * ($sero + 4) * $mesu) * $yoyo + $jsp + $jka + $cka + $d1_cost;

// 다른 상품: 테이블 기반 계산  
$query = "SELECT * FROM MlangPrintAuto_* WHERE style='$MY_type' AND Section='$PN_type'...";
```

### 3. 데이터베이스 구조
- **shop_temp**: 통합 장바구니 (상품별 전용 필드 포함)
- **MlangOrder_PrintAuto**: 통합 주문 테이블
  - `ImgFolder`: 업로드 파일 폴더 경로
  - `ThingCate`: 대표 이미지 파일명
  - `Type_1`: 상품 상세 정보 (JSON)

### 4. 상품별 필드 매핑

#### 일반 스티커 전용 필드
- `jong`: 재질 (아트지유광, 투명스티커 등)
- `garo`, `sero`: 가로/세로 크기 (mm)
- `mesu`: 수량 (매)
- `uhyung`: 편집비 (원)
- `domusong`: 모양 (사각, 원형, 별모양 등)

#### 공통 필드 (다른 상품용)
- `MY_type`: 구분/카테고리
- `MY_Fsd`: 종이종류/스타일
- `PN_type`: 종이규격/섹션
- `MY_amount`: 수량
- `POtype`: 단/양면
- `ordertype`: 주문타입

## 구현 시 주의사항

### 1. 호환성 유지
- 기존 시스템과의 완전한 호환성 보장
- 기존 사용자 데이터 보존
- 점진적 통합 방식 적용

### 2. 성능 고려
- 복잡한 스티커 계산 로직 최적화 필요
- 데이터베이스 쿼리 성능 모니터링
- 프론트엔드 JavaScript 최적화

### 3. 보안 강화
- 스티커 크기/수량 입력값 검증 강화
- SQL Injection 방지 (prepared statements 사용)
- XSS 공격 방지 (htmlspecialchars 사용)

### 4. 오류 처리
- 사용자 친화적 오류 메시지
- 적절한 예외 처리 및 로깅
- 시스템 복구 메커니즘

## 개발 가이드라인

### 1. 코드 구조
```php
// 통합 가격 계산 인터페이스
interface PriceCalculatorInterface {
    public function calculatePrice($product_type, $options);
    public function validateOptions($product_type, $options);
    public function formatPriceResponse($price_data);
}

// 상품별 구현
class StickerPriceCalculator implements PriceCalculatorInterface { ... }
class TableBasedPriceCalculator implements PriceCalculatorInterface { ... }
```

### 2. 데이터 검증
```php
// 스티커 입력값 검증
if ($garo > 590) throw new Exception('가로사이즈를 590mm이하만 입력할 수 있습니다');
if ($sero > 590) throw new Exception('세로사이즈를 590mm이하만 입력할 수 있습니다');
if (($garo * $sero) > 250000 && $mesu > 5000) {
    throw new Exception('500mm이상 대형사이즈를 5000매이상 주문은 전화요청바랍니다');
}
```

### 3. JSON 데이터 구조
```php
// Type_1 필드에 저장되는 상품 상세 정보
$sticker_details = [
    'jong' => '아트지유광',
    'garo' => 100,
    'sero' => 100, 
    'mesu' => 1000,
    'uhyung' => 10000,
    'domusong' => '00000 사각'
];

$leaflet_details = [
    'MY_type' => '802',
    'MY_Fsd' => '714',
    'PN_type' => '821',
    'POtype' => '1',
    'MY_amount' => '7',
    'ordertype' => 'total'
];
```

## 테스트 시나리오

### 1. 기본 기능 테스트
- 네비게이션 드롭다운 메뉴 동작
- 스티커 가격 계산 정확성
- 장바구니 추가/삭제 기능
- 통합 주문 처리

### 2. 경계값 테스트
- 최소/최대 크기 입력
- 대량 주문 처리
- 특수 재질 선택
- 복잡한 모양 선택

### 3. 오류 상황 테스트
- 잘못된 입력값 처리
- 네트워크 오류 처리
- 데이터베이스 연결 실패
- 세션 만료 처리

## 최신 업데이트 (2025.08.08)

### 파일 업로드 컴포넌트 시스템 구축 완료

#### 🎉 주요 성과
1. **범용 업로드 컴포넌트 개발**
   - `FileUploadComponent.php` - 품목별 맞춤 설정 지원
   - `UniversalFileUpload.js` - 드래그앤드롭, 진행률 표시
   - 통합 핸들러 시스템 (upload/get/delete)

2. **스티커 페이지 컴포넌트 적용 완료**
   - 기존 복잡한 업로드 코드 → 간단한 컴포넌트 호출
   - 10MB 제한, JPG/PNG/PDF 지원
   - 세션 기반 보안 적용

3. **확장 가능한 구조 확립**
   - 품목별 설정만 변경하여 쉬운 적용
   - 새 품목 추가 시 개발 시간 70% 단축 예상

#### 📁 생성된 파일들
- `includes/FileUploadComponent.php` - PHP 컴포넌트 클래스
- `includes/js/UniversalFileUpload.js` - JavaScript 라이브러리
- `includes/upload_handler.php` - 통합 업로드 처리
- `includes/get_files_handler.php` - 파일 목록 조회
- `includes/delete_file_handler.php` - 파일 삭제 처리

#### 🔄 다른 품목 적용 방법
```php
// 전단지용 설정 예시
$uploadComponent = new FileUploadComponent([
    'product_type' => 'leaflet',
    'max_file_size' => 15 * 1024 * 1024, // 15MB
    'allowed_extensions' => ['jpg', 'png', 'pdf', 'zip'],
    'custom_messages' => [
        'title' => '전단지 디자인 파일 업로드'
    ]
]);
echo $uploadComponent->render();
```

## 배포 체크리스트

- [x] 파일 업로드 컴포넌트 시스템 구축
- [x] 스티커 페이지 컴포넌트 적용
- [ ] 다른 품목 페이지 적용 (전단지, 명함, 카다록 등)
- [ ] 데이터베이스 스키마 업데이트
- [ ] 기존 데이터 마이그레이션
- [ ] 성능 테스트 완료
- [ ] 보안 점검 완료
- [ ] 사용자 매뉴얼 업데이트
- [ ] 관리자 교육 완료
- [ ] 백업 및 롤백 계획 수립
- [ ] 모니터링 시스템 설정