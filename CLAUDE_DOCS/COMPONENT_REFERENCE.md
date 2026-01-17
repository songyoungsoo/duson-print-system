# 컴포넌트 레퍼런스

두손기획인쇄 시스템의 핵심 클래스 및 컴포넌트 사용 가이드

**생성일**: 2026-01-17
**버전**: 1.0

---

## 목차

1. [수량/단위 처리](#1-수량단위-처리)
2. [제품 사양 포맷팅](#2-제품-사양-포맷팅)
3. [가격 계산](#3-가격-계산)
4. [파일 업로드](#4-파일-업로드)
5. [인증/세션](#5-인증세션)
6. [데이터 어댑터](#6-데이터-어댑터)
7. [주문 서비스](#7-주문-서비스)

---

## 1. 수량/단위 처리

### 1.1 QuantityFormatter

**파일**: `/var/www/html/includes/QuantityFormatter.php`
**역할**: 수량과 단위의 단일 진실 공급원 (SSOT)

#### 단위 코드 체계

| 코드 | 단위 | 제품 |
|------|------|------|
| R | 연 | inserted (전단지/리플렛) |
| S | 매 | sticker_new, namecard, envelope, littleprint, msticker |
| B | 부 | cadarok (카다록) |
| V | 권 | ncrflambeau (NCR양식지) |
| P | 장 | 개별 인쇄물 |
| E | 개 | 기타 |

#### 기본 메서드

```php
require_once '/var/www/html/includes/QuantityFormatter.php';

// 기본 포맷팅
QuantityFormatter::format(1000, 'S');           // "1,000매"
QuantityFormatter::format(0.5, 'R', 2000);      // "0.5연 (2,000매)"
QuantityFormatter::format(10, 'B');             // "10부"
QuantityFormatter::format(5, 'V');              // "5권"

// 제품 코드로 단위 조회
QuantityFormatter::getUnitCode('inserted');     // 'R'
QuantityFormatter::getUnitCode('namecard');     // 'S'

// 단위명 조회
QuantityFormatter::getUnitName('R');            // '연'
QuantityFormatter::getUnitName('S');            // '매'
```

#### 데이터에서 포맷팅

```php
// 배열 데이터에서 직접 포맷팅
$orderData = [
    'product_type' => 'inserted',
    'MY_amount' => 0.5,
    'quantity' => 0.5
];

$display = QuantityFormatter::formatFromData($orderData);
// 내부적으로 제품 타입에 맞는 단위 코드와 값을 추출하여 포맷팅
```

#### 제품별 단위 매핑

```php
// 상수로 정의됨
const PRODUCT_UNITS = [
    'inserted'       => 'R',  // 전단지+리플렛
    'leaflet'        => 'R',  // (미사용) 이미지 경로용만
    'sticker_new'    => 'S',  // 스티커
    'msticker'       => 'S',  // 자석스티커
    'namecard'       => 'S',  // 명함
    'envelope'       => 'S',  // 봉투
    'cadarok'        => 'B',  // 카다록
    'ncrflambeau'    => 'V',  // NCR양식지
    'littleprint'    => 'P',  // 포스터
    'merchandisebond'=> 'S'   // 상품권
];
```

---

## 2. 제품 사양 포맷팅

### 2.1 ProductSpecFormatter

**파일**: `/var/www/html/includes/ProductSpecFormatter.php`
**역할**: 모든 제품의 규격/옵션을 2줄 형식으로 통일 표시

#### 출력 형식

```
Line 1: 규격 (종류 / 용지 / 크기)
Line 2: 옵션 (인쇄면 / 수량 / 디자인)
Additional: 추가옵션 (코팅, 접지 등)
```

#### 기본 사용법

```php
require_once '/var/www/html/includes/ProductSpecFormatter.php';

// DB 연결 필요
$formatter = new ProductSpecFormatter($db);

// 주문/장바구니 데이터 포맷팅
$item = [
    'product_type' => 'inserted',
    'spec_type' => 'A4',
    'spec_material' => '스노우지 150g',
    'spec_size' => '210x297',
    'quantity_display' => '0.5연',
    'spec_sides' => '양면',
    'spec_design' => '디자인의뢰'
];

$result = $formatter->format($item);
// 결과:
// [
//   'line1' => 'A4 / 스노우지 150g / 210x297',
//   'line2' => '양면 / 0.5연 (2,000매) / 디자인의뢰',
//   'additional' => ''
// ]
```

#### 추가 옵션 처리

```php
$item = [
    'product_type' => 'inserted',
    // ... 기본 필드
    'coating_enabled' => 1,
    'coating_type' => 'single',
    'folding_enabled' => 1,
    'folding_type' => '2fold'
];

$result = $formatter->format($item);
// 결과:
// [
//   'line1' => 'A4 / 스노우지 150g / 210x297',
//   'line2' => '양면 / 0.5연 (2,000매) / 디자인의뢰',
//   'additional' => '단면유광코팅, 2단접지'
// ]
```

#### 레거시 데이터 호환

```php
// data_version이 없는 레거시 데이터도 자동 처리
$legacyItem = [
    'Type' => '명함',
    'Type_1' => '{"jong":"수입지","garo":90,"sero":50}'
];

$result = $formatter->format($legacyItem);
// 내부적으로 formatLegacy() 호출하여 처리
```

#### HTML 출력 헬퍼

```php
// HTML로 바로 출력
echo $formatter->toHtml($item);
// 출력:
// <div class="spec-line1">A4 / 스노우지 150g / 210x297</div>
// <div class="spec-line2">양면 / 0.5연 (2,000매) / 디자인의뢰</div>
```

---

## 3. 가격 계산

### 3.1 PriceCalculationService

**파일**: `/var/www/html/includes/PriceCalculationService.php`
**역할**: 모든 품목의 가격 계산 중앙화

#### 기본 사용법

```php
require_once '/var/www/html/includes/PriceCalculationService.php';

$priceService = new PriceCalculationService($db);

// 가격 계산
$params = [
    'product_type' => 'inserted',
    'style' => 'A4',
    'Section' => '스노우지 150g',
    'quantity' => 0.5,
    'POtype' => 2  // 양면
];

$result = $priceService->calculate($params);
// 결과:
// [
//   'Price' => 50000,        // 공급가액
//   'DS_Price' => 10000,     // 디자인비
//   'Order_Price' => 60000,  // 합계
//   'VAT' => 6000,           // 부가세
//   'Total' => 66000         // VAT 포함 총액
// ]
```

#### 품목별 설정 조회

```php
// 품목 설정 확인
$config = $priceService->getProductConfig('inserted');
// [
//   'table' => 'mlangprintauto_inserted',
//   'type' => 'table_lookup',
//   'has_tree' => true,
//   'has_quantity_two' => true
// ]
```

#### 추가 옵션 가격

```php
// 코팅, 접지 등 추가 옵션 가격 계산
$optionParams = [
    'product_type' => 'inserted',
    'quantity' => 0.5,
    'coating_type' => 'single',
    'folding_type' => '2fold'
];

$optionPrice = $priceService->calculateOptions($optionParams);
// [
//   'coating_price' => 5000,
//   'folding_price' => 3000,
//   'total_options' => 8000
// ]
```

---

## 4. 파일 업로드

### 4.1 StandardUploadHandler

**파일**: `/var/www/html/includes/StandardUploadHandler.php`
**역할**: 모든 제품의 파일 업로드 통합 처리

#### 허용 확장자

```php
const ALLOWED_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp',  // 이미지
    'pdf', 'ai', 'psd', 'eps', 'cdr',            // 디자인
    'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', // 오피스
    'zip', 'rar', '7z',                          // 압축
    'txt', 'csv'                                 // 텍스트
];

const MAX_FILE_SIZE = 15 * 1024 * 1024;  // 15MB
```

#### 기본 사용법

```php
require_once '/var/www/html/includes/StandardUploadHandler.php';

// 파일 업로드 처리
$result = StandardUploadHandler::processUpload(
    'namecard',     // 제품 코드
    $_FILES,        // 업로드된 파일들
    []              // 추가 옵션
);

// 결과 구조
// [
//   'success' => true,
//   'files' => [
//       [
//           'original_name' => 'design.pdf',
//           'saved_name' => '1705468800_design.pdf',
//           'path' => 'upload/namecard/2026/01/17/abc123/1705468800_design.pdf',
//           'size' => 1024000
//       ]
//   ],
//   'img_folder' => 'upload/namecard/2026/01/17/abc123/',
//   'thing_cate' => 'namecard',
//   'error' => ''
// ]
```

#### 에러 처리

```php
$result = StandardUploadHandler::processUpload('namecard', $_FILES);

if (!$result['success']) {
    // 에러 처리
    echo "업로드 실패: " . $result['error'];
    exit;
}

// 성공 시 파일 정보 사용
foreach ($result['files'] as $file) {
    echo "업로드됨: " . $file['original_name'];
}
```

### 4.2 UploadPathHelper

**파일**: `/var/www/html/includes/UploadPathHelper.php`
**역할**: 업로드 경로 생성 및 관리

```php
require_once '/var/www/html/includes/UploadPathHelper.php';

// 업로드 경로 생성
$paths = UploadPathHelper::generateUploadPath('namecard');
// [
//   'full_path' => '/var/www/html/upload/namecard/2026/01/17/abc123/',
//   'db_path' => 'upload/namecard/2026/01/17/abc123/',
//   'relative_path' => '../upload/namecard/2026/01/17/abc123/'
// ]

// 경로 유효성 검사
$isValid = UploadPathHelper::validatePath($somePath);
```

### 4.3 ImagePathResolver

**파일**: `/var/www/html/includes/ImagePathResolver.php`
**역할**: 저장된 이미지 경로 해석

```php
require_once '/var/www/html/includes/ImagePathResolver.php';

// DB에 저장된 경로를 웹 URL로 변환
$dbPath = 'upload/namecard/2026/01/17/abc123/file.jpg';
$webUrl = ImagePathResolver::resolve($dbPath);
// '/upload/namecard/2026/01/17/abc123/file.jpg'

// 썸네일 경로 생성
$thumbUrl = ImagePathResolver::getThumbnail($dbPath, 150, 150);
```

---

## 5. 인증/세션

### 5.1 auth.php

**파일**: `/var/www/html/includes/auth.php`
**역할**: 세션 관리 및 인증

#### 세션 설정

```php
// 세션 유효 시간: 8시간 (28800초)
// 자동 로그인: 30일

require_once '/var/www/html/includes/auth.php';
// 자동으로 세션 시작 및 설정 적용
```

#### 세션 변수

```php
// 로그인 후 설정되는 세션 변수
$_SESSION['user_id']          // 사용자 ID
$_SESSION['user_name']        // 사용자 이름
$_SESSION['user_email']       // 이메일
$_SESSION['logged_in']        // 로그인 상태 (true/false)
$_SESSION['last_activity']    // 마지막 활동 시간

// 관리자 세션
$_SESSION['admin_logged_in']  // 관리자 로그인 상태
$_SESSION['admin_id']         // 관리자 ID
```

#### 로그인 확인

```php
require_once '/var/www/html/includes/auth.php';

// 로그인 필수 페이지
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: /login.php');
    exit;
}

// 로그인 여부 확인 (선택적)
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
```

### 5.2 auth_functions.php

**파일**: `/var/www/html/includes/auth_functions.php`
**역할**: 인증 관련 헬퍼 함수

```php
require_once '/var/www/html/includes/auth_functions.php';

// 비밀번호 해시
$hash = hashPassword('plain_password');

// 비밀번호 검증
$isValid = verifyPassword('plain_password', $hash);

// 자동 로그인 토큰 생성
$token = generateRememberToken($userId);

// 자동 로그인 토큰 검증
$userId = validateRememberToken($token);
```

---

## 6. 데이터 어댑터

### 6.1 DataAdapter

**파일**: `/var/www/html/includes/DataAdapter.php`
**역할**: 레거시 데이터와 신규 데이터 구조 변환

#### 표준 필드 변환

```php
require_once '/var/www/html/includes/DataAdapter.php';

// 레거시 데이터를 표준 형식으로 변환
$legacyData = [
    'jong' => '스노우지',
    'garo' => 210,
    'sero' => 297,
    'MY_amount' => '0.5'
];

$standardData = DataAdapter::normalize($legacyData, 'inserted');
// [
//   'spec_type' => 'A4',
//   'spec_material' => '스노우지',
//   'spec_size' => '210x297',
//   'quantity_value' => 0.5,
//   'quantity_unit' => 'R',
//   'data_version' => 2
// ]
```

#### 주문 데이터 표준화

```php
// 주문 저장 전 데이터 표준화
$orderData = DataAdapter::prepareForOrder($formData, 'namecard');
// 모든 필수 필드가 표준 형식으로 변환됨
```

---

## 7. 주문 서비스

### 7.1 OrderDataService

**파일**: `/var/www/html/includes/OrderDataService.php`
**역할**: 주문 데이터 조회 및 관리

#### 주문 조회

```php
require_once '/var/www/html/includes/OrderDataService.php';

$orderService = new OrderDataService($db);

// 주문번호로 조회
$order = $orderService->getOrderByNo($orderNo);

// 사용자별 주문 목록
$orders = $orderService->getOrdersByUser($userId, [
    'limit' => 10,
    'offset' => 0,
    'status' => 'all'  // 'all', 'pending', 'completed' 등
]);

// 최근 주문
$recentOrders = $orderService->getRecentOrders(10);
```

#### 주문 상태 변경

```php
// 주문 상태 업데이트
$orderService->updateStatus($orderNo, '제작중');
$orderService->updateStatus($orderNo, '배송중');
$orderService->updateStatus($orderNo, '배송완료');
```

### 7.2 SpecDisplayService

**파일**: `/var/www/html/includes/SpecDisplayService.php`
**역할**: 사양 표시 전용 서비스

```php
require_once '/var/www/html/includes/SpecDisplayService.php';

$specService = new SpecDisplayService($db);

// 주문 사양 표시용 데이터 조회
$spec = $specService->getDisplaySpec($orderNo);
// [
//   'line1' => '규격 정보',
//   'line2' => '옵션 정보',
//   'additional' => '추가옵션',
//   'price_display' => '55,000원'
// ]
```

---

## 8. 통합 사용 예시

### 8.1 주문 처리 전체 흐름

```php
<?php
// 주문 처리 예시

require_once '/var/www/html/includes/auth.php';
require_once '/var/www/html/db.php';
require_once '/var/www/html/includes/StandardUploadHandler.php';
require_once '/var/www/html/includes/DataAdapter.php';
require_once '/var/www/html/includes/QuantityFormatter.php';

// 1. 파일 업로드 처리
$uploadResult = StandardUploadHandler::processUpload(
    $_POST['product_type'],
    $_FILES
);

if (!$uploadResult['success']) {
    die("업로드 실패: " . $uploadResult['error']);
}

// 2. 데이터 표준화
$orderData = DataAdapter::prepareForOrder($_POST, $_POST['product_type']);
$orderData['img_folder'] = $uploadResult['img_folder'];

// 3. 수량 표시 생성
$orderData['quantity_display'] = QuantityFormatter::format(
    $orderData['quantity_value'],
    $orderData['quantity_unit']
);

// 4. DB 저장
$sql = "INSERT INTO mlangorder_printauto (...) VALUES (...)";
// ... INSERT 로직
```

### 8.2 주문 조회 및 표시

```php
<?php
// 주문 표시 예시

require_once '/var/www/html/includes/auth.php';
require_once '/var/www/html/db.php';
require_once '/var/www/html/includes/ProductSpecFormatter.php';
require_once '/var/www/html/includes/OrderDataService.php';

$orderService = new OrderDataService($db);
$formatter = new ProductSpecFormatter($db);

// 주문 조회
$order = $orderService->getOrderByNo($_GET['no']);

// 사양 포맷팅
$spec = $formatter->format($order);
?>

<div class="order-detail">
    <div class="spec-line1"><?= htmlspecialchars($spec['line1']) ?></div>
    <div class="spec-line2"><?= htmlspecialchars($spec['line2']) ?></div>
    <?php if (!empty($spec['additional'])): ?>
        <div class="spec-additional"><?= htmlspecialchars($spec['additional']) ?></div>
    <?php endif; ?>
</div>
```

---

## 부록: 빠른 참조 표

### 클래스별 주요 메서드

| 클래스 | 메서드 | 용도 |
|--------|--------|------|
| QuantityFormatter | `format()` | 수량 포맷팅 |
| QuantityFormatter | `getUnitCode()` | 제품별 단위 코드 |
| ProductSpecFormatter | `format()` | 2줄 사양 포맷 |
| ProductSpecFormatter | `toHtml()` | HTML 출력 |
| PriceCalculationService | `calculate()` | 가격 계산 |
| StandardUploadHandler | `processUpload()` | 파일 업로드 |
| DataAdapter | `normalize()` | 데이터 표준화 |
| OrderDataService | `getOrderByNo()` | 주문 조회 |

### 필수 Include 순서

```php
// 권장 순서
require_once '/var/www/html/includes/auth.php';        // 1. 인증
require_once '/var/www/html/db.php';                   // 2. DB 연결
require_once '/var/www/html/includes/QuantityFormatter.php';  // 3. 수량
require_once '/var/www/html/includes/ProductSpecFormatter.php'; // 4. 사양
// ... 기타 필요한 컴포넌트
```

---

*Document Version: 1.0*
*Last Updated: 2026-01-17*
