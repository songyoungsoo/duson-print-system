# 버그 수정 이력

## 1. 수량 표시 불일치 문제

### 증상
전단지 주문 시 "0.5연 (2,000매)" 형식의 수량이 장바구니와 주문서에서 다르게 표시됨.
- 장바구니: "0.5연 (2,000매)" ✅
- 주문서: "2000" ❌
- 주문완료: "2000" ❌

### 원인
`shop_temp` 테이블에 `quantity`와 `quantity_display` 두 필드가 있는데,
`orderformtree`로 복사할 때 `quantity_display`를 누락함.

### 해결
```php
// 수정 전 (order_process.php)
$sql = "INSERT INTO orderformtree (order_no, product_type, product_name, quantity, price)
        SELECT ?, product_type, product_name, quantity, price FROM shop_temp WHERE session_id = ?";

// 수정 후
$sql = "INSERT INTO orderformtree (order_no, product_type, product_name, quantity, quantity_display, price)
        SELECT ?, product_type, product_name, quantity, quantity_display, price FROM shop_temp WHERE session_id = ?";
```

### DB 마이그레이션
```sql
-- orderformtree에 quantity_display 컬럼 추가
ALTER TABLE orderformtree ADD COLUMN quantity_display VARCHAR(100) AFTER quantity;

-- 기존 데이터 마이그레이션 (필요시)
UPDATE orderformtree SET quantity_display = quantity WHERE quantity_display IS NULL;
```

---

## 2. 추가옵션 누락 문제

### 증상
코팅, 접지, 오시 등 추가옵션을 선택했는데 주문서/관리자페이지에 표시 안 됨.

### 원인
1. `shop_temp.options` 필드가 JSON 형식인데 파싱 안 함
2. 주문서 화면에서 옵션 표시 코드 누락

### 해결

#### cart_add.php 수정
```php
// 추가옵션 JSON으로 저장
$options = [];
if (!empty($_POST['coating'])) $options['coating'] = $_POST['coating'];
if (!empty($_POST['folding'])) $options['folding'] = $_POST['folding'];
if (!empty($_POST['scoring'])) $options['scoring'] = $_POST['scoring'];

$options_json = !empty($options) ? json_encode($options, JSON_UNESCAPED_UNICODE) : null;
```

#### 옵션 표시 헬퍼 함수
```php
function formatOptions($options_json) {
    if (empty($options_json)) return '-';
    
    $options = json_decode($options_json, true);
    if (!$options) return '-';
    
    $labels = [
        'coating' => '코팅',
        'folding' => '접지',
        'scoring' => '오시',
    ];
    
    $result = [];
    foreach ($options as $key => $value) {
        $label = $labels[$key] ?? $key;
        $result[] = "{$label}: {$value}";
    }
    
    return implode(', ', $result);
}

// 사용
<td><?= formatOptions($item['options']) ?></td>
```

---

## 3. 세션 만료로 장바구니 초기화

### 증상
장바구니에 상품을 담고 잠시 후 돌아오면 장바구니가 비어있음.

### 원인
- PHP 기본 세션 유효시간 24분
- `session.gc_maxlifetime` 설정 부족

### 해결

#### php.ini 또는 .htaccess
```ini
session.gc_maxlifetime = 86400  ; 24시간
session.cookie_lifetime = 86400
```

#### PHP 코드에서 설정
```php
// inc/session.php
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_set_cookie_params(86400);
session_start();
```

#### 회원 로그인 시 장바구니 연동
```php
// 비회원 장바구니를 회원 ID로 연결
function mergeGuestCart($session_id, $member_id) {
    global $pdo;
    $sql = "UPDATE shop_temp SET member_id = ? WHERE session_id = ? AND member_id = 0";
    $pdo->prepare($sql)->execute([$member_id, $session_id]);
}
```

---

## 4. 파일 업로드 실패 (대용량)

### 증상
50MB 이상 인쇄 파일 업로드 시 "업로드 실패" 또는 빈 화면

### 원인
PHP 기본 설정이 작은 파일만 허용

### 해결

#### php.ini
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

#### .htaccess (Cafe24)
```apache
php_value upload_max_filesize 100M
php_value post_max_size 100M
php_value max_execution_time 300
```

#### 청크 업로드 적용
대용량 파일은 청크로 분할 업로드 → `file-upload.md` 참고

---

## 5. 가격 계산 오류 (실수 연산)

### 증상
특정 수량에서 가격이 소수점으로 표시되거나 1원 차이 발생

### 원인
JavaScript 부동소수점 연산 오류

### 해결
```javascript
// 수정 전
const total = basePrice * quantity * 1.1;  // 부가세 포함

// 수정 후 - 정수 연산
const total = Math.round(basePrice * quantity * 1.1);

// 또는 반올림 후 정수 변환
const total = parseInt(Math.round(basePrice * quantity * 1.1));
```

---

## 6. 주소 API 모바일 팝업 문제

### 증상
모바일에서 다음 주소 API 팝업이 안 열리거나 화면 밖으로 나감

### 해결
```javascript
// 수정 전
new daum.Postcode({...}).open();

// 수정 후 - 임베드 방식
new daum.Postcode({
    oncomplete: function(data) {
        document.getElementById('postcode').value = data.zonecode;
        document.getElementById('address').value = data.roadAddress;
    },
    width: '100%',
    height: '100%'
}).embed(document.getElementById('addressLayer'));

// 레이어 표시
document.getElementById('addressLayer').style.display = 'block';
```

---

## 7. IE11 호환성 문제

### 증상
Internet Explorer 11에서 JavaScript 오류 발생

### 원인
ES6+ 문법 사용 (화살표 함수, const/let, 템플릿 리터럴)

### 해결
```javascript
// 수정 전 (ES6)
const items = cart.map(item => item.price);
const total = items.reduce((a, b) => a + b, 0);
const html = `<p>총액: ${total}원</p>`;

// 수정 후 (ES5 호환)
var items = cart.map(function(item) { return item.price; });
var total = items.reduce(function(a, b) { return a + b; }, 0);
var html = '<p>총액: ' + total + '원</p>';
```

또는 Babel 트랜스파일 적용

---

## 8. 관리자 목록 페이징 오류

### 증상
페이지 2 이상 클릭 시 1페이지 데이터만 표시

### 원인
LIMIT 계산 오류

### 해결
```php
// 수정 전
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = $page * $limit;  // 오류: 1페이지가 20부터 시작

// 수정 후
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;  // 1페이지는 0부터

$sql = "SELECT * FROM orderform ORDER BY created_at DESC LIMIT $offset, $limit";
```

---

## 9. 이메일 발송 실패 (네이버 SMTP)

### 증상
주문 확인 이메일이 발송되지 않음

### 원인
1. 네이버 2단계 인증 활성화 시 앱 비밀번호 필요
2. SMTP 포트 오류

### 해결
```php
// 네이버 앱 비밀번호 발급 필요
define('SMTP_PASS', 'app_password_here');  // 계정 비밀번호 아님!

// 포트 확인
define('SMTP_PORT', 587);  // TLS
// 또는
define('SMTP_PORT', 465);  // SSL

// SSL 사용 시
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
```

---

## 10. Cafe24 배포 후 경로 오류

### 증상
로컬에서 잘 되던 기능이 Cafe24 업로드 후 404 에러

### 원인
1. 대소문자 구분 (Linux vs Windows)
2. 절대 경로 문제

### 해결
```php
// 수정 전
require_once '/inc/dbcon.php';  // 절대 경로

// 수정 후
require_once __DIR__ . '/../inc/dbcon.php';  // 상대 경로

// 파일명 대소문자 통일
// Windows: Header.php ✅
// Linux: header.php 와 Header.php 는 다른 파일!
```

---

## 11. 수량 표시 규칙 (간소화됨)

### 핵심 원칙
**전 품목 통틀어 0.5연(전단지)만 소수점 표시, 나머지는 모두 정수**

### 올바른 표시 규칙
- **전단지(inserted)**: `0.5`만 소수점, 나머지 정수 (예: `0.5연`, `1연`, `2연`)
- **다른 모든 품목**: 항상 정수 (예: `500매`, `1,000매`)

### 이유
전단지는 "연" 단위를 사용하며, 실제로 0.5연만 소수점이 필요합니다.
1.5연, 2.5연 등은 실제 주문에 존재하지 않습니다.

### 구현 코드 (간소화)

#### 공통 함수: `includes/quantity_formatter.php`
```php
// 권장: 공통 함수 사용
include "includes/quantity_formatter.php";
echo formatQuantity($quantity, 'inserted');      // "0.5연" 또는 "1연"
echo formatQuantityValue($quantity, 'inserted'); // "0.5" 또는 "1"
```

#### JavaScript (간소화)
```javascript
// 0.5만 소수점, 나머지 정수
function formatQuantityValue(quantity) {
    const qty = parseFloat(quantity);
    if (qty === 0.5) return '0.5';
    return parseInt(qty).toLocaleString();
}
```

#### PHP (간소화)
```php
// 0.5만 소수점, 나머지 정수
$display = ($quantity == 0.5) ? '0.5' : number_format(intval($quantity));
```

### 예시

| 원본 수량 | 전단지 표시 | 다른 품목 표시 |
|----------|------------|---------------|
| `0.5` | `0.5연` ✅ | N/A (정수만) |
| `1` | `1연` ✅ | `1매` ✅ |
| `500` | `500연` ✅ | `500매` ✅ |

### 관련 문서
- **스킬**: `duson-print-rules` - 수량/규격/옵션 표기 규칙 (상세)
- **공통 함수**: `/var/www/html/includes/quantity_formatter.php`




---

## 12. 봉투/자석스티커/카다록 공급가액 표시 오류

### 증상
봉투, 자석스티커, 카다록 주문 시 공급가액이 합계금액으로 잘못 표시됨.
- 공급가: 50,000원 (예상) → 55,000원 (표시) ❌
- 합계: 55,000원 (예상) → 55,000원 (표시) ✅

### 원인
봉투/자석스티커/카다록은 다른 가격 구조 사용:
- `total_price` = 공급가액 (VAT 미포함)
- `total_with_vat` = 합계 (VAT 포함)

프론트엔드는 `calculated_price`, `calculated_vat_price`로 전송하지만,
백엔드 `add_to_basket.php`에서 `price`, `vat_price`로 수신하려다 실패.

### 해결

#### envelope/add_to_basket.php
```php
// 수정 전
$price = $_POST['price'] ?? 0;
$vat_price = $_POST['vat_price'] ?? 0;

// 수정 후
$calculated_price = $_POST['calculated_price'] ?? 0;  // 공급가액 (VAT 미포함)
$calculated_vat_price = $_POST['calculated_vat_price'] ?? 0;  // 합계 (VAT 포함)

// bind_param도 수정
mysqli_stmt_bind_param($stmt, "...",
    $calculated_price, $calculated_vat_price,  // 수정된 변수명
    ...
);
```

#### msticker/add_to_basket.php, cadarok/add_to_basket.php
동일한 수정 적용.

### 데이터 흐름
```
프론트엔드 (index.php)
└─ calculated_price = total_price (공급가액)
└─ calculated_vat_price = total_with_vat (합계)

백엔드 (add_to_basket.php)
└─ $calculated_price → shop_temp.st_price
└─ $calculated_vat_price → shop_temp.st_price_vat

주문 처리 (ProcessOrder_unified.php)
└─ st_price → mlangorder_printauto.money_4 (공급가액)
└─ st_price_vat → mlangorder_printauto.money_5 (합계)

주문 완료 (OrderComplete_universal.php)
└─ money_4 표시 (공급가)
└─ money_5 표시 (합계)
```

### 관련 파일
- `/var/www/html/mlangprintauto/envelope/add_to_basket.php`
- `/var/www/html/mlangprintauto/msticker/add_to_basket.php`
- `/var/www/html/mlangprintauto/cadarok/add_to_basket.php`

---

## 13. msticker.js 무한 재귀 호출 오류

### 증상
자석스티커 페이지에서 업로드 버튼 클릭 시:
```
Uncaught RangeError: Maximum call stack size exceeded
    at openUploadModal (msticker.js:473)
```

### 원인
`openUploadModal()` 함수가 `window.openUploadModal()`을 호출하는데, 이것이 자기 자신임.
```javascript
// 문제 코드
function openUploadModal() {
    if (!currentPriceData) { ... }
    window.openUploadModal();  // ← 자기 자신 호출 = 무한 루프
}
```

### 해결
```javascript
// 수정 후 - 직접 모달 조작
function openUploadModal() {
    if (!currentPriceData) {
        showUserMessage('먼저 가격을 계산해주세요.', 'warning');
        return;
    }

    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
```

### 관련 파일
- `/var/www/html/js/msticker.js`

---

## 14. 카다록/봉투 장바구니 가격 0 문제

### 증상
카다록, 봉투 장바구니 추가 시 `st_price: 0.00`으로 저장됨.
- 가격 계산은 정상 표시
- 장바구니 추가 후 총액이 0원

### 원인 1: window.currentPriceData 미설정
JavaScript 파일에서 `let currentPriceData`로 로컬 변수 사용.
index.php에서는 `window.currentPriceData`를 참조하여 불일치 발생.

### 원인 2: POST 필드명 불일치 (주요 원인)
JavaScript에서 `price`, `vat_price`로 전송하지만,
PHP에서 `calculated_price`, `calculated_vat_price`를 기대함.

```javascript
// 문제 코드 (cadarok.js, envelope.js)
formData.set('price', Math.round(currentPriceData.total_price));
formData.set('vat_price', Math.round(currentPriceData.total_with_vat));

// PHP 기대값 (add_to_basket.php)
$calculated_price = $_POST['calculated_price'] ?? 0;  // price가 아님!
$calculated_vat_price = $_POST['calculated_vat_price'] ?? 0;
```

### 해결

#### 1. window.currentPriceData 전역 설정
```javascript
// cadarok.js, envelope.js
currentPriceData = priceData;
window.currentPriceData = priceData;  // 전역 설정 추가
```

#### 2. POST 필드명 수정 (핵심!)
```javascript
// 수정 후 (cadarok.js:747-748, envelope.js:690-691)
formData.set('calculated_price', Math.round(currentPriceData.total_price));
formData.set('calculated_vat_price', Math.round(currentPriceData.total_with_vat));
```

### 관련 파일
- `/var/www/html/mlangprintauto/cadarok/js/cadarok.js`
- `/var/www/html/js/envelope.js`
- `/var/www/html/mlangprintauto/cadarok/add_to_basket.php`
- `/var/www/html/mlangprintauto/envelope/add_to_basket.php`

---

## 15. Type_1 JSON vs Text 형식 불일치

### 증상
주문 완료 페이지에서 일부 제품 규격이 제대로 표시되지 않음.
- 전단지, 스티커: 정상 (JSON 형식)
- 카다록, 명함, 자석스티커, 양식지, 상품권: 오류 (Text 형식)

### 원인
`ProcessOrder_unified.php`에서 제품별로 다른 형식 사용:
```php
// JSON 형식 (정상)
$product_info = json_encode($data, JSON_UNESCAPED_UNICODE);

// Text 형식 (문제)
$product_info = "카다록 / $paper / $qty";
```

### 해결
모든 제품을 JSON 형식으로 통일 (ProcessOrder_unified.php):

```php
// 모든 제품에 동일 패턴 적용
case 'namecard':
    $namecard_data = [
        'product_type' => 'namecard',
        'MY_type' => $item['MY_type'],
        'MY_type_name' => getCategoryName($connect, $item['MY_type']),
        'MY_Fsd' => $item['MY_Fsd'],
        'Section_name' => getCategoryName($connect, $item['MY_Fsd']),
        'POtype' => $item['POtype'],
        'POtype_name' => ($item['POtype'] == '1' ? '단면' : '양면'),
        'MY_amount' => intval($item['MY_amount'] ?? 0),
        'created_at' => date('Y-m-d H:i:s')
    ];
    $product_info = json_encode($namecard_data, JSON_UNESCAPED_UNICODE);
    break;
```

### 핵심 필드 규칙
- `*_name` 필드: 사람이 읽을 수 있는 이름 (getCategoryName 결과)
- 원본 코드 필드: DB 참조용 유지
- `MY_amount`: 정수로 변환 (`intval`)

### 관련 파일
- `/var/www/html/mlangorder_printauto/ProcessOrder_unified.php`
- `/var/www/html/mlangorder_printauto/OrderComplete_universal.php`

---

## 16. 장바구니 가격 표시 변경 (공급가액 + 부가세 별도)

### 요청 사항
장바구니 페이지에서:
- "총액" → "공급가액"으로 변경
- VAT 포함 금액 → 공급가액(VAT 미포함)으로 변경
- "부가세포함" → "부가세 별도"로 변경

### 변경 전
```
| 품목 | 규격/옵션 | 수량 | 단위 | 총액 | 관리 |
|------|-----------|------|------|------|------|
| 카다록 | ... | 1000 | 매 | 부가세포함 294,800원 | ✕ |
```

### 변경 후
```
| 품목 | 규격/옵션 | 수량 | 단위 | 공급가액 | 관리 |
|------|-----------|------|------|----------|------|
| 카다록 | ... | 1000 | 매 | 부가세 별도 268,000원 | ✕ |
```

### 수정 내용

#### cart.php 수정 사항
1. **테이블 헤더** (라인 308)
   ```php
   // Before
   <th>총액</th>

   // After
   <th>공급가액</th>
   ```

2. **각 상품 가격 표시** (라인 458-462)
   ```php
   // Before
   <div class="price-label">부가세포함</div>
   <div class="price-total"><?php echo number_format($final_price_vat); ?>원</div>

   // After
   <div class="price-label">부가세 별도</div>
   <div class="price-total"><?php echo number_format($final_price); ?>원</div>
   ```

3. **주문 요약** - 변경 없음 (상품금액/부가세/총 결제금액 유지)

### 관련 파일
- `/var/www/html/mlangprintauto/shop/cart.php`

---

## 17. 카다록 규격 표시 누락 (Type_1 JSON 필드 매핑 오류)

### 증상
OrderComplete_universal.php에서 카다록 주문 시 규격이 표시되지 않음.
- 표시: `카다록,리플렛 / 1,000매 / 인쇄만` (규격 누락)
- 기대: `카다록,리플렛 / 24절(127*260)3단` + `양면컬러인쇄 / 500부 / 디자인+인쇄`

### 원인
`ProcessOrder_unified.php`에서 카다록 Type_1 JSON 생성 시 잘못된 필드 매핑:
```php
// 버그 코드
$section_name = getCategoryName($connect, $item['PN_type']);  // PN_type은 비어있음!
$style_name = getCategoryName($connect, $item['MY_Fsd']);     // MY_Fsd도 비어있음!

// shop_temp 실제 데이터
// - MY_type: 691 (종류)
// - Section: 692 (규격) ← 이 값을 사용해야 함
// - PN_type: empty
// - MY_Fsd: empty
```

### 해결

#### ProcessOrder_unified.php (라인 254-278)
```php
// 수정 전 (버그)
$style_name = getCategoryName($connect, $item['MY_Fsd']);
$section_name = getCategoryName($connect, $item['PN_type']);

$cadarok_data = [
    'MY_Fsd' => $item['MY_Fsd'],
    'Section_name' => $section_name,  // empty
    'PN_type' => $item['PN_type'],
    'PN_type_name' => $style_name,    // empty
    ...
];

// 수정 후
$section_name = getCategoryName($connect, $item['Section']);  // Section 필드 사용!
$paper_name = getCategoryName($connect, $item['PN_type']);

$cadarok_data = [
    'Section' => $item['Section'],           // 추가
    'Section_name' => $section_name,         // "24절(127*260)3단"
    'PN_type' => $item['PN_type'],
    'PN_type_name' => $paper_name,
    'POtype' => $item['POtype'] ?? '',       // 추가 (인쇄면)
    ...
];
```

### 카다록 shop_temp 필드 매핑

| shop_temp 필드 | 용도 | 예시 값 |
|---------------|------|---------|
| MY_type | 종류 | 691 → "카다록,리플렛" |
| Section | 규격 | 692 → "24절(127*260)3단" |
| PN_type | 용지 | (대부분 비어있음) |
| MY_Fsd | 미사용 | (비어있음) |
| POtype | 인쇄면 | 1=단면, 2=양면 |

### 테스트 결과

**주문번호 #104037 (수정 후)**:
```
카다록,리플렛 / 24절(127*260)3단     (1줄: 종류/규격)
양면컬러인쇄 / 500부 / 디자인+인쇄    (2줄: 인쇄면/수량/디자인)
```

### 기존 주문 수정 방법
```php
// 주문번호 104028 예시
$updated_json = json_encode([
    'product_type' => 'cadarok',
    'MY_type' => '691',
    'MY_type_name' => '카다록,리플렛',
    'Section' => '692',
    'Section_name' => '24절(127*260)3단',
    'PN_type' => null,
    'PN_type_name' => '',
    'POtype' => '',
    'MY_amount' => 1000,
    'ordertype' => 'print',
    'created_at' => '2025-12-29 23:08:45'
], JSON_UNESCAPED_UNICODE);

mysqli_query($db, "UPDATE mlangorder_printauto SET Type_1 = '$updated_json' WHERE no = 104028");
```

### 관련 파일
- `/var/www/html/mlangorder_printauto/ProcessOrder_unified.php`
- `/var/www/html/mlangorder_printauto/OrderComplete_universal.php`

### 관련 스킬
- `duson-print-rules/SKILL.md` - 주의사항 #5에 필드 매핑 규칙 추가

---

## 18. 자석스티커(msticker) 규격 표시 누락 (2025-12-30 수정)

### 증상
자석스티커 주문 완료 페이지에서 규격이 표시되지 않음.

**주문번호 #104049 (수정 전)**:
```
자석스티커(종이자석)
1,000매 / 인쇄만
```
→ **규격(Section) 누락!**

### 원인
ProcessOrder_unified.php에서 자석스티커 필드 매핑 오류:
- `$item['PN_type']` 사용 ❌ (항상 null)
- `$item['Section']` 사용해야 함 ✅

**shop_temp 필드 구조 (자석스티커)**:
| 필드명 | 의미 | 예시 값 |
|--------|------|---------|
| `MY_type` | 종류 | 742 (자석스티커) |
| `Section` | 규격 | 743 (90x50mm) |
| `POtype` | 인쇄면 | 1=단면, 2=양면 |
| `MY_amount` | 수량 | 1000 |

### 수정 사항

**ProcessOrder_unified.php** (line 378-399):
```php
case 'msticker':
    $product_type_name = '자석스티커';
    // 자석스티커 필드 매핑: MY_type=종류, Section=규격, POtype=인쇄면
    $type_name = getCategoryName($connect, $item['MY_type']);      // 종류
    $section_name = getCategoryName($connect, $item['Section']);   // 규격

    $msticker_data = [
        'product_type' => 'msticker',
        'MY_type' => $item['MY_type'],
        'MY_type_name' => $type_name,
        'Section' => $item['Section'],
        'Section_name' => $section_name,
        'POtype' => $item['POtype'] ?? '',
        'MY_amount' => $qty,
        'ordertype' => $item['ordertype'],
        'created_at' => date('Y-m-d H:i:s')
    ];
```

**OrderComplete_universal.php** (line 278-299):
```php
case 'msticker':
    // 필드 매핑: MY_type=종류, Section=규격, POtype=인쇄면
    $type_display = $json_data['MY_type_name'] ?? getCategoryName($connect, $json_data['MY_type'] ?? '');
    $section_display = $json_data['Section_name'] ?? getCategoryName($connect, $json_data['Section'] ?? '');
    $potype = $json_data['POtype'] ?? '';
    // ... (2줄 슬래시 형식 출력)
```

### 수정 후 예상 결과
**주문번호 (수정 후)**:
```
자석스티커(종이자석) / 90x50mm     (1줄: 종류/규격)
단면인쇄 / 1,000매 / 인쇄만         (2줄: 인쇄면/수량/디자인)
```

### 관련 파일
- `/var/www/html/mlangorder_printauto/ProcessOrder_unified.php`
- `/var/www/html/mlangorder_printauto/OrderComplete_universal.php`
- `/var/www/html/mlangprintauto/shop/cart.php`

### 관련 스킬
- `duson-print-rules/SKILL.md` - 주의사항 #6에 자석스티커 필드 매핑 규칙 추가

---

## 19. 양식지(ncrflambeau) 넘버링/미싱 옵션 변경 (2025-12-30)

### 요청 사항
양식지 페이지의 넘버링/미싱 옵션을 체크 시 "전화문의 1688-2384"만 표시되도록 변경.

### 변경 전
```html
<!-- 넘버링 옵션 -->
<option value="numbering">전화문의 1688-2384</option>

<!-- 미싱 옵션 -->
<option value="1">1줄</option>
<option value="2">2줄</option>
<option value="3">3줄</option>
```

### 변경 후
```html
<!-- 넘버링 옵션 -->
<option value="numbering">전화문의 1688-2384</option>

<!-- 미싱 옵션 (통합) -->
<option value="mising">전화문의 1688-2384</option>
```

### 수정 파일
- `/var/www/html/mlangprintauto/ncrflambeau/index.php`

---

## 20. 견적 요청 버튼 삭제 (2025-12-30)

### 요청 사항
모든 9개 품목 페이지에서 "견적 요청" 버튼 삭제, "파일 업로드 및 주문하기" 버튼만 유지.

### 삭제된 코드
```html
<button type="button" class="btn-request-quote" onclick="addToQuotation()">
    견적 요청
</button>
```

### 수정된 파일 (9개)
- `/var/www/html/mlangprintauto/inserted/index.php`
- `/var/www/html/mlangprintauto/namecard/index.php`
- `/var/www/html/mlangprintauto/envelope/index.php`
- `/var/www/html/mlangprintauto/sticker_new/index.php`
- `/var/www/html/mlangprintauto/msticker/index.php`
- `/var/www/html/mlangprintauto/cadarok/index.php`
- `/var/www/html/mlangprintauto/littleprint/index.php`
- `/var/www/html/mlangprintauto/merchandisebond/index.php`
- `/var/www/html/mlangprintauto/ncrflambeau/index.php`

---

## 버그 리포트 양식

```
## 버그 제목
[간단한 설명]

## 발생 환경
- 페이지:
- 브라우저:
- 기기: PC / 모바일

## 재현 순서
1.
2.
3.

## 예상 결과
[정상 동작 시 예상]

## 실제 결과
[실제 발생한 문제]

## 스크린샷
[있으면 첨부]
```
