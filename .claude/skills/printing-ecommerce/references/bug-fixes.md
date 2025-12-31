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
자석스티커(종이자석) / 90x60mm(후면에작은자석)     (1줄: 종류/규격)
단면인쇄 / 1,000매 / 인쇄만                        (2줄: 인쇄면/수량/디자인)
```

### 기존 주문 수정 방법 (중요!)
**코드 수정 전에 생성된 주문**은 Type_1 JSON에 Section 정보가 누락되어 있음.
이러한 주문은 DB를 직접 업데이트해야 함:

```php
// 예시: 주문번호 #104049 수정
$updated_json = json_encode([
    'product_type' => 'msticker',
    'MY_type' => '742',
    'MY_type_name' => '자석스티커(종이자석)',
    'Section' => '743',                              // 추가
    'Section_name' => '90x60mm(후면에작은자석)',      // 추가
    'POtype' => '1',                                 // 추가 (1=단면, 2=양면)
    'PN_type' => null,
    'PN_type_name' => '',
    'MY_amount' => 1000,
    'ordertype' => 'print',
    'created_at' => '2025-12-30 00:28:44'
], JSON_UNESCAPED_UNICODE);

mysqli_query($db, "UPDATE mlangorder_printauto SET Type_1 = '$updated_json' WHERE no = 104049");
```

**주의사항:**
- 새로운 주문은 자동으로 Section/POtype이 저장됨
- 기존 주문 수정 시 transactioncate 테이블에서 규격 코드 확인 필요
- Section 코드는 품목별로 다름 (예: 742=자석스티커 종류, 743=90x60mm 규격)

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

## 21. OnlineOrder_unified.php 주문 상품 목록 테이블 개선 (2025-12-30)

### 요청 사항
OnlineOrder_unified.php의 주문 상품 목록 테이블을 cart.php와 동일한 5컬럼 구조로 변경.

### 변경 전
```
| 품목 | 규격/옵션 | 공급가 |
|------|-----------|--------|
| 3컬럼 구조 (18% / 52% / 30%) |
```

### 변경 후
```
| 품목 | 규격/옵션 | 수량 | 단위 | 공급가액 |
|------|-----------|------|------|----------|
| 5컬럼 구조 (15% / 42% / 10% / 8% / 25%) |
```

### 수량/단위 표시 규칙 (cart.php와 동일)
- **전단지(inserted/leaflet)**: 수량 컬럼에 "X연 (XXX매)" 표시, 단위 컬럼은 "-"
- **양식지(ncrflambeau)**: 단위 "권"
- **카다록(cadarok)**: 단위 "부"
- **기타 제품**: 단위 "매"

### 수정 내용

#### 1. colgroup 변경 (line 567-573)
```html
<colgroup>
    <col style="width: 15%;"><!-- 품목 -->
    <col style="width: 42%;"><!-- 규격/옵션 -->
    <col style="width: 10%;"><!-- 수량 -->
    <col style="width: 8%;"><!-- 단위 -->
    <col style="width: 25%;"><!-- 공급가액 -->
</colgroup>
```

#### 2. thead 헤더 추가 (line 575-581)
```html
<th>품목</th>
<th>규격/옵션</th>
<th>수량</th>
<th>단위</th>
<th>공급가액</th>
```

#### 3. 수량/단위 계산 로직 추가 (line 602-641)
```php
// 수량/단위 계산 (cart.php와 동일한 로직)
$is_flyer = in_array($item['product_type'], ['inserted', 'leaflet']);
$unit = '매'; // Default

if ($is_flyer) {
    $unit = '연';
    $main_amount_display = formatQuantityValue($main_amount_val, 'inserted');
    $sub_amount = $item['flyer_mesu'] ?? null;
} else {
    if ($item['product_type'] == 'ncrflambeau') $unit = '권';
    elseif ($item['product_type'] == 'cadarok') $unit = '부';
}
```

#### 4. 수량/단위 td 추가 (line 857-874)
```html
<!-- 수량 -->
<td class="amount-cell">
    <span class="amount-value"><?php echo $main_amount_display; ?></span>
    <?php if ($is_flyer && $sub_amount): ?>
        <br><span class="amount-sub">(<?php echo number_format($sub_amount); ?>매)</span>
    <?php endif; ?>
</td>

<!-- 단위 -->
<td class="unit-cell">
    <?php echo $is_flyer ? '-' : $unit; ?>
</td>
```

#### 5. 테이블 전체 폭 설정 (line 563, 1271)
```css
/* 주문 상품 목록 div */
max-width: 1200px;

/* .centered-form 클래스 */
.centered-form {
    max-width: 1200px;
}
```

### 관련 파일
- `/var/www/html/mlangorder_printauto/OnlineOrder_unified.php`

---

## 22. OnlineOrder_unified.php 수량 필드 누락 수정 (2025-12-30)

### 증상
장바구니에서 주문 페이지로 이동 시 수량이 항상 "1"로 표시됨.
- DB에 `MY_amount: 1000`이 저장되어 있어도 화면에 "1"로 표시
- 봉투, 명함 등 모든 제품에서 동일 증상

### 원인
장바구니 데이터를 `$formatted_item`에 복사할 때 `MY_amount`, `mesu`, `flyer_mesu` 필드가 누락됨.

```php
// 수정 전 (line 286, 344)
$formatted_item['MY_type'] = $item['MY_type'] ?? '';
$formatted_item['ordertype'] = $item['ordertype'] ?? '';
// MY_amount, mesu, flyer_mesu 누락!
```

### 해결
2곳에 누락된 필드 추가:

```php
// 수정 후 (line 286-295, 344-353)
$formatted_item['MY_type'] = $item['MY_type'] ?? '';
$formatted_item['MY_Fsd'] = $item['MY_Fsd'] ?? '';
$formatted_item['PN_type'] = $item['PN_type'] ?? '';
$formatted_item['Section'] = $item['Section'] ?? '';
$formatted_item['POtype'] = $item['POtype'] ?? '';
$formatted_item['ordertype'] = $item['ordertype'] ?? '';
$formatted_item['MY_amount'] = $item['MY_amount'] ?? '';  // 추가
$formatted_item['mesu'] = $item['mesu'] ?? '';            // 추가
$formatted_item['flyer_mesu'] = $item['flyer_mesu'] ?? ''; // 추가
```

### 관련 파일
- `/var/www/html/mlangorder_printauto/OnlineOrder_unified.php`

---

## 23. OrderComplete_universal.php 전단지 수량 단위 수정 (2025-12-30)

### 증상
주문완료 페이지에서 전단지 수량이 "0.5매 (2,000매)"로 표시됨.
- 올바른 표시: "0.5연 (2,000매)"
- 잘못된 표시: "0.5매 (2,000매)"

### 원인
`formatQuantity()` 호출 시 `$order['unit']` 값을 사용했는데, DB에 '매'가 저장되어 있어서 '매'로 표시됨.

```php
// 수정 전 (line 438)
$qty_text = formatQuantity($my_amount, 'inserted', $order['unit'] ?? '연');
// $order['unit']이 '매'일 경우 '매'로 표시됨
```

### 해결
cart.php, OnlineOrder_unified.php와 동일한 로직으로 변경하여 전단지는 항상 '연' 사용:

```php
// 수정 후 (line 438-443)
// 전단지는 항상 '연' 사용 (cart.php, OnlineOrder_unified.php와 동일)
$yeon = floatval($my_amount);
$yeon_display = ($yeon == 0.5) ? '0.5' : number_format(intval($yeon));
$qty_text = $yeon_display . '연';
if (!empty($mesu)) $qty_text .= '(' . number_format(intval($mesu)) . '매)';
$line2[] = $qty_text;
```

### 관련 파일
- `/var/www/html/mlangorder_printauto/OrderComplete_universal.php`
- `/var/www/html/mlangprintauto/shop/cart.php` (참조 - 이미 올바르게 구현됨)
- `/var/www/html/mlangorder_printauto/OnlineOrder_unified.php` (참조 - 이미 올바르게 구현됨)

---

## 24. 전단지 표기 3개 페이지 통일 (2025-12-30)

### 증상
장바구니, 주문페이지, 주문완료페이지에서 전단지 표기가 불일치:
- 규격 필드: `Section` vs `PN_type`
- 인쇄면: `단면/양면` vs `단면컬러인쇄/양면컬러인쇄`
- 수량 형식: `0.5연 (2,000매)` vs `0.5연(2,000매)` (공백 불일치)

### 해결
3개 파일 모두 동일한 형식으로 통일:

```php
// 전단지 표기 통일 코드
if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
if (!empty($item['MY_Fsd'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_Fsd']));
if (!empty($item['PN_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['PN_type']));

if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '1' ? '단면컬러인쇄' : '양면컬러인쇄');

$yeon = !empty($item['MY_amount']) ? floatval($item['MY_amount']) : 0;
$mesu = !empty($item['flyer_mesu']) ? intval($item['flyer_mesu']) : (!empty($item['mesu']) ? intval($item['mesu']) : 0);
if ($yeon > 0) {
    $yeon_display = ($yeon == 0.5) ? '0.5' : number_format(intval($yeon));
    $qty_text = $yeon_display . '연';
    if ($mesu > 0) $qty_text .= '(' . number_format($mesu) . '매)';  // 공백 없음
    $line2_parts[] = $qty_text;
}
```

### 변경 사항
| 항목 | 수정 전 | 수정 후 |
|------|---------|---------|
| 규격 | Section | PN_type |
| 인쇄면 | 단면/양면 | 단면컬러인쇄/양면컬러인쇄 |
| 수량 형식 | 0.5연 (2,000매) | 0.5연(2,000매) |
| mesu fallback | flyer_mesu만 | flyer_mesu → mesu |

### 최종 표시 형식
```
칼라(CMYK) / 90g아트지(합판전단)
단면컬러인쇄 / 0.5연(2,000매) / 인쇄만
```

### 관련 파일
- `/var/www/html/mlangprintauto/shop/cart.php` (line 492-510)
- `/var/www/html/mlangorder_printauto/OnlineOrder_unified.php` (line 884-902)
- `/var/www/html/mlangorder_printauto/OrderComplete_universal.php` (line 416-447, 625-637)

---

## 25. 관리자 작업지시서 인쇄 기능 (2025-12-30)

### 기능 설명
관리자 주문 상세 페이지에서 **주문서 출력** 버튼 클릭 시 작업지시서 인쇄.
A4 용지 한 장에 관리자용/직원용 두 장을 인쇄하여 절취선을 따라 나눠 가짐.

### 접근 경로
```
/admin/mlangprintauto/admin.php?mode=OrderView&no={주문번호}
→ [🖨️ 주문서 출력] 버튼 클릭
```

### 인쇄 레이아웃
```
┌─────────────────────────────────┐
│    주문서 (관리자용)              │
│  주문번호 / 일시 / 주문자 / 전화   │
│  ───────────────────────────── │
│  주문상세 표 (품목/규격/수량/금액) │
│  고객정보 / 기타사항              │
│  두손기획인쇄 02-2632-1830       │
├─────────────────────────────────┤
│        ✂ 절취선                  │
├─────────────────────────────────┤
│    주문서 (직원용)               │
│  (동일 내용)                     │
│  두손기획인쇄 02-2632-1830       │
└─────────────────────────────────┘
```

### 핵심 CSS 구조
```css
/* 화면에서 인쇄 전용 영역 숨김 */
.print-only {
    display: none;
}

@media print {
    /* 화면 전용 요소 숨기기 */
    .admin-container, .screen-only, .file-section,
    .btn-group, form, .no-print {
        display: none !important;
    }

    /* 인쇄 전용 요소 표시 */
    .print-only {
        display: block !important;
    }

    /* A4 절반 크기로 각 주문서 배치 */
    .print-order {
        height: 135mm;
        page-break-inside: avoid;
    }

    /* 절취선 스타일 */
    .print-divider {
        border-top: 1pt dashed #999;
        border-bottom: 1pt dashed #999;
    }
    .print-divider::before {
        content: '✂ 절취선';
    }
}
```

### HTML 구조
```php
<!-- 인쇄 전용 (화면에서는 숨김) -->
<div class="print-only">
    <div class="print-container">
        <!-- 관리자용 -->
        <div class="print-order">
            <div class="print-title">주문서 (관리자용)</div>
            <!-- 주문 상세 테이블 -->
            <!-- 고객 정보 -->
            <div class="print-footer">두손기획인쇄 02-2632-1830</div>
        </div>

        <div class="print-divider"></div>

        <!-- 직원용 -->
        <div class="print-order">
            <div class="print-title">주문서 (직원용)</div>
            <!-- 동일 내용 -->
        </div>
    </div>
</div>

<!-- 화면 전용 (인쇄 시 숨김) -->
<div class="screen-only">
    <div class="admin-container">
        <!-- 관리자 폼 -->
    </div>
</div>
```

### 주요 클래스
| 클래스 | 용도 |
|--------|------|
| `.print-only` | 인쇄 시에만 표시 |
| `.screen-only` | 화면에서만 표시 |
| `.print-order` | 개별 작업지시서 (A4 절반) |
| `.print-divider` | 절취선 |
| `.print-title` | 주문서 제목 |
| `.print-info-section` | 정보 섹션 |
| `.print-table` | 고객정보 테이블 |
| `.print-footer` | 푸터 (연락처) |

### 관련 파일
- `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php` (인쇄 레이아웃 + CSS)
- `/var/www/html/admin/mlangprintauto/admin.php` (OrderView 모드에서 include)

### 사용 방법
1. 관리자 → 주문관리 → 주문 클릭
2. **🖨️ 주문서 출력** 버튼 클릭
3. 브라우저 인쇄 다이얼로그 → PDF 저장 또는 프린터 출력
4. 절취선을 따라 잘라서 관리자/직원이 나눠 가짐

---

## 26. 택배 목록 품목명/배송메세지 컬럼 교체 (2025-12-30)

### 변경 내용
`post_list52.php` 택배 목록 화면과 엑셀 내보내기에서 품목명과 배송메세지 컬럼의 데이터를 교체함.

### 변경 전후
| 헤더 | 변경 전 데이터 | 변경 후 데이터 |
|------|----------------|----------------|
| 품목명 | `Type_1` (A4 단면 80g 0.5연) | `Type` (Inserted, NameCard 등) |
| 배송메세지 | `Type` (Inserted, NameCard 등) | `Type_1` (A4 단면 80g 0.5연) |

### 수정 파일

#### 1. post_list52.php (테이블 표시)
```php
// 헤더 (lines 352-354) - 순서 유지
<td>품목명</td>
<td>기타</td>
<td>배송메세지</td>

// 데이터 (lines 409-411) - 교체됨
<td><?php echo htmlspecialchars($data['Type'] ?? '')?></td>
<td><?php echo htmlspecialchars($data['no'] ?? '')?></td>
<td><?php echo $type1_display?></td>
```

#### 2. export_logen_excel.php (로젠택배 엑셀)
```php
// 데이터 배열 (lines 178-180)
$data['Type'] ?? '',                    // 품목명
'dsno' . ($data['no'] ?? ''),          // 기타
$type_1_display                         // 배송메세지
```

#### 3. export_excel52.php (일반 엑셀)
```php
// 데이터 출력 (lines 129-131)
echo "<td>" . htmlspecialchars($data['Type'] ?? '') . "</td>";      // 품목명
echo "<td>" . htmlspecialchars($data['Date'] ?? '') . "</td>";      // 주문일자
echo "<td>" . htmlspecialchars($data['Type_1'] ?? '') . "</td>";    // 배송메세지
```

#### 4. export_logen_format.php (로젠택배 CSV)
```php
// 데이터 배열 (lines 166-168)
$data['Type'] ?? '',                    // 물품명
$box_count,                             // 수량(박스)
$type_1_display                         // 배송메세지
```

### 관련 URL
- 테이블: `http://dsp1830.shop/shop_admin/post_list52.php`
- 엑셀: 로젠택배 엑셀 (선택/전체) 버튼

### 관련 파일
- `/var/www/html/shop_admin/post_list52.php`
- `/var/www/html/shop_admin/export_logen_excel.php`
- `/var/www/html/shop_admin/export_excel52.php`
- `/var/www/html/shop_admin/export_logen_format.php`

---

## 27. 회원정보 비밀번호 검증 실패 수정 (2025-12-30)

### 증상
회원정보수정 페이지에서 올바른 비밀번호를 입력해도 "현재 비밀번호가 일치하지 않습니다" 오류 발생.
- 프로필 수정 불가
- 비밀번호 변경 불가
- 회원 탈퇴 불가

### 원인
DB에 **평문 비밀번호**와 **bcrypt 해시**가 혼재되어 있음:

| 사용자 | 비밀번호 저장 형태 | 길이 |
|--------|-------------------|------|
| admin | `$2y$10$3noob...` | 60자 (bcrypt) ✅ |
| heotaijun | `1234` | 4자 (평문) ❌ |
| cbs07068 | `exoexo0112` | 10자 (평문) ❌ |

`password_verify()` 함수는 **bcrypt 해시만** 검증 가능하므로, 평문 비밀번호 사용자는 항상 실패.

### 해결
평문과 bcrypt 해시 비밀번호 모두 지원하도록 검증 로직 수정:

```php
// 수정 전
if (!password_verify($current_password, $user['password'])) {
    $error = "현재 비밀번호가 일치하지 않습니다.";
}

// 수정 후
$stored_password = $user['password'];
$password_valid = false;

// bcrypt 해시인 경우 ($2y$로 시작하고 60자)
if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
    $password_valid = password_verify($current_password, $stored_password);
} else {
    // 평문 비밀번호인 경우 직접 비교
    $password_valid = ($current_password === $stored_password);
}

if (!$password_valid) {
    $error = "현재 비밀번호가 일치하지 않습니다.";
}
```

### 추가 개선: 로그인 시 자동 해시 업그레이드
평문 비밀번호로 로그인 성공 시, 자동으로 bcrypt 해시로 업그레이드:

```php
// member/login_unified.php
if ($login_success && $need_hash_upgrade) {
    $new_hash = password_hash($pass, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET password = ? WHERE id = ?";
    // ... 업데이트 실행
}
```

### 수정된 파일 (6개)
| 파일 | 용도 |
|------|------|
| `mypage/profile.php` | 회원정보수정 |
| `mypage/change_password.php` | 비밀번호변경 |
| `mypage/account.php` | 계정관리 |
| `mypage/withdraw.php` | 회원탈퇴 |
| `member/login_unified.php` | 로그인 + 자동 해시 업그레이드 |
| `member/change_password.php` | 레거시 비밀번호변경 |

### 테스트 결과
```
✅ heotaijun (평문 1234) - 올바른 비밀번호 검증 성공
✅ heotaijun (평문 1234) - 틀린 비밀번호 거부 성공
✅ cbs07068 (평문 exoexo0112) - 올바른 비밀번호 검증 성공
✅ admin (bcrypt 해시) - bcrypt 검증 로직 정상 작동
```

---

## 28. 회원정보 수정 DB 오류 - postcode 컬럼명 불일치 (2025-12-30)

### 증상
비밀번호 검증 통과 후에도 "회원정보 수정 중 오류가 발생했습니다" 메시지 발생.

### 원인
DB 컬럼명과 PHP 코드 불일치:
- **DB 테이블**: `postcode`
- **PHP 코드**: `zipcode`

```sql
-- users 테이블 실제 구조
postcode    varchar(20)    YES    NULL
```

### 해결
```php
// 수정 전
$update_query = "UPDATE users SET
                 ...
                 zipcode = ?,
                 ...";

// 수정 후
$update_query = "UPDATE users SET
                 ...
                 postcode = ?,
                 ...";

// HTML 표시도 수정
// 수정 전
value="<?php echo htmlspecialchars($user_info['zipcode'] ?? ''); ?>"

// 수정 후
value="<?php echo htmlspecialchars($user_info['postcode'] ?? ''); ?>"
```

### 수정 파일
- `mypage/profile.php` - UPDATE 쿼리 및 HTML 표시 수정

---

## 29. 주문서 출력 기능 Playwright E2E 테스트 (2025-12-30)

### 테스트 대상
관리자 주문 상세 페이지 → 주문서 출력 버튼 클릭 → 인쇄 미리보기 검증

### 테스트 URL
```
http://dsp1830.shop/admin/mlangprintauto/admin.php?mode=OrderView&no=104049
```

### 테스트 방법
Playwright를 사용한 자동화 테스트:
1. 페이지 접속
2. `window.print()` 호출 감지 설정 (모킹)
3. "🖨️ 주문서 출력" 버튼 클릭
4. `window.print()` 호출 확인
5. `@media print` CSS 적용 상태에서 스크린샷 캡처

### 테스트 코드
```python
# window.print() 호출 감지
page.evaluate("""
    () => {
        window._printCalled = false;
        window._originalPrint = window.print;
        window.print = function() {
            window._printCalled = true;
            window._printCalledWith = document.title;
        };
    }
""")

# 버튼 클릭
print_btn = page.locator('[onclick*="printOrder"]').first
print_btn.click()

# 결과 확인
print_called = page.evaluate("() => window._printCalled")

# 인쇄 미리보기 캡처
page.emulate_media(media='print')
page.screenshot(path='/tmp/print_preview.png', full_page=True)
```

### 테스트 결과
| 항목 | 결과 |
|------|------|
| 주문서 출력 버튼 발견 | ✅ `🖨️ 주문서 출력` |
| printOrder() 함수 | ✅ `window.print()` 호출 |
| window.print() 호출 | ✅ 성공 |
| PDF 파일명 형식 | ✅ `관리자_104049.pdf` |
| @media print 규칙 | ✅ 1개 발견 |
| 인쇄 레이아웃 | ✅ 관리자용/직원용 2부 |

### 인쇄 미리보기 검증 내용
```
┌─────────────────────────────────┐
│    주문서 (관리자용)              │
│  주문번호: 104049                │
│  일시: 2025-12-30 02:28:16      │
│  주문자: 관리자                   │
│  전화: 010-3712-1830            │
│  ────────────────────────────── │
│  품목: 전단지                     │
│  규격: 90g아트지 | A4 | 단면     │
│  수량: 0.5연 (2,000매)           │
│  금액: 49,000원 (부가세포함 53,900원) │
│  두손기획인쇄 02-2632-1830       │
├─────────────────────────────────┤
│        ✂ 절취선                  │
├─────────────────────────────────┤
│    주문서 (직원용)               │
│  (동일 내용)                     │
└─────────────────────────────────┘
```

### 스크린샷 위치
- 화면 뷰: `/tmp/screen_view.png`
- 인쇄 미리보기: `/tmp/print_preview.png`

### 관련 파일
- `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php` (printOrder 함수 + CSS)
- `/var/www/html/admin/mlangprintauto/admin.php` (OrderView 모드)

---

## 30. 전단지 가격 계산기 Playwright E2E 테스트 (2025-12-30)

### 테스트 대상
전단지(inserted) 제품 페이지 가격 계산기 기능 검증

### 테스트 URL
```
http://dsp1830.shop/mlangprintauto/inserted/
```

### 테스트 항목 및 결과

| 항목 | 결과 | 상세 |
|------|------|------|
| 페이지 로드 | ✅ | 정상 접속 |
| 옵션 셀렉트박스 | ✅ | 5개 모두 작동 |
| AJAX 가격 계산 | ✅ | calculate_price_ajax.php 응답 정상 |
| window.currentPriceData | ✅ | 전역 변수 설정됨 |
| 추가 옵션 (코팅) | ✅ | 가격 정상 반영 |

### 옵션 요소

| 옵션 | 필드명 | 개수 |
|------|--------|------|
| 종류 | MY_type | 1개 (칼라 CMYK) |
| 용지 | MY_Fsd | 12개 |
| 규격 | PN_type | 10개 |
| 인쇄면 | POtype | 2개 (단면/양면) |
| 수량 | MY_amount | 12개 |

### 가격 계산 검증

**기본 옵션 (A4 양면 0.5연)**:
```
공급가액: 62,000원
부가세:   6,200원
합계:    68,200원
```

**코팅 옵션 추가 시**:
```
기본가:       62,000원
코팅 옵션:    80,000원
─────────────────────
공급가액:    142,000원
부가세:      14,200원
합계:       156,200원
```

### currentPriceData 구조 (전단지)
```javascript
window.currentPriceData = {
    "Price": "62,000",              // 기본 인쇄비 (표시용)
    "PriceForm": "62000",           // 기본 인쇄비 (숫자)
    "Additional_Options": "80,000", // 추가옵션 합계
    "Additional_Options_Form": 80000,
    "Order_Price": "142,000",       // 공급가액 (표시용)
    "Order_PriceForm": 142000,      // 공급가액 (숫자) ← 장바구니 전송용
    "VAT_PriceForm": 14200,         // 부가세
    "Total_PriceForm": 156200,      // 합계 (VAT포함) ← 장바구니 전송용
    "StyleForm": "802",             // 종류 코드
    "SectionForm": "821",           // 규격 코드
    "QuantityForm": "0.5",          // 수량 (연)
    "DesignForm": "print",          // 디자인 타입
    "MY_amountRight": "2000장"      // 매수 표시
};
```

### 장바구니 연동 필드 매핑
```javascript
// add_to_basket.php 전송 시
formData.append("calculated_price", currentPriceData.Order_PriceForm);     // 공급가액
formData.append("calculated_vat_price", currentPriceData.Total_PriceForm); // VAT포함
```

### 테스트 코드 핵심
```python
# AJAX 응답 캡처
def handle_response(response):
    if 'calculate_price' in response.url:
        ajax_responses.append(response.json())

page.on("response", handle_response)

# 옵션 선택
page.locator('select[name="POtype"]').select_option(value='2')  # 양면

# 가격 계산 함수 호출
page.evaluate("() => calculatePrice()")

# 결과 확인
price_data = page.evaluate("() => window.currentPriceData")
```

### 스크린샷 위치
- `/tmp/leaflet_initial.png` (초기 상태)
- `/tmp/leaflet_final.png` (테스트 후)

### 관련 파일
- `/var/www/html/mlangprintauto/inserted/index.php` (제품 페이지)
- `/var/www/html/mlangprintauto/inserted/calculate_price_ajax.php` (가격 계산 API)
- `/var/www/html/js/inserted-logic.js` (클라이언트 로직)

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

---

## 31. 마이페이지 주문내역 페이지네이션 구현 (2025-12-30)

### 증상
마이페이지에서 사용자 주문내역 조회 기능이 없음.

### 구현 내용
`/mypage/orders.php` 신규 생성 - 로그인한 사용자의 주문 내역 조회

### 기능 상세
- **페이지당 20건** 표시
- **페이지네이션**: « (맨처음), ‹ (이전), 숫자(현재 ±5범위), › (다음), » (맨끝)
- **필터**: 검색어(이름, 전화), 상태, 날짜 범위
- **표시 정보**: 주문번호, 제품, 주문자, 연락처, 주문일, 상태, 금액(VAT포함)

### 발생한 문제 및 해결

#### 1. 500 Internal Server Error
- **원인**: `auth_required.php`에서 `$current_user['email']` 미설정
- **해결**: member 테이블에서 email/name 조회 추가

#### 2. 로그인 리다이렉트 루프
- **원인**: 레거시 로그인 시스템은 `$_SESSION['id_login_ok']` 사용, 신규 auth는 `$_SESSION['user_id']` 확인
- **해결**: `auth_required.php`에서 레거시 세션을 신규 형식으로 변환 (auth.php 로드 전에 실행)

```php
// 레거시 세션 호환성 처리
if (!isset($_SESSION['user_id']) && isset($_SESSION['id_login_ok'])) {
    $legacy_user = $_SESSION['id_login_ok'];
    if (is_array($legacy_user) && isset($legacy_user['id'])) {
        $_SESSION['user_id'] = $legacy_user['id'];
        $_SESSION['username'] = $legacy_user['id'];
    }
}
```

#### 3. COUNT는 337건인데 주문목록 0건
- **원인**: SELECT 쿼리에 존재하지 않는 `product_info` 컬럼 참조
- **해결**: `product_info` 컬럼 제거

### 테스트 결과
```
✅ Page 1: 20 orders
✅ Last page URL: ?page=17
✅ Last page: 17 orders
✅ First page URL: ?page=1
✅ Filtered (status=2): 20 orders
```

### 파일 변경
- `/var/www/html/mypage/orders.php` - 주문내역 페이지 신규
- `/var/www/html/mypage/auth_required.php` - 레거시 세션 호환성 추가, email/name 조회

### 스크린샷
- `/tmp/orders_page_1.png`
- `/tmp/orders_page_2.png`

---

## 32. 마이페이지 주문 상세보기 기능 추가 (2025-12-30)

### 요구사항
주문 내역에서 개별 주문의 상세 정보를 확인하는 기능 필요

### 구현 내용
`/mypage/order_detail.php` 신규 생성

### 기능 상세

**표시 정보**:
- 주문 정보: 주문번호, 주문일시, 제품, 상태
- 제품 상세: 규격/사양 (JSON 파싱), 수량, 품목코드
- 주문자 정보: 이름, 연락처, 이메일, 업체명
- 배송 정보: 우편번호, 주소, 배송방법, 택배조회
- 결제 정보: 공급가액, 추가옵션, VAT포함 총액
- 추가옵션: 코팅/접지/오시, 프리미엄옵션, 봉투옵션
- 업로드 파일: 파일 목록 또는 폴더 경로
- 요청사항: 고객 메모
- 교정 승인: 승인 일시, 승인자

**보안**:
- 본인 주문만 조회 가능 (email 또는 name으로 검증)
- 타인 주문 접근 시 목록 페이지로 리다이렉트

**UI 개선**:
- 주문 목록에서 행 클릭 시 상세 페이지로 이동
- 주문번호 링크 스타일 적용
- 호버 효과 추가

### 파일 변경
- `/var/www/html/mypage/order_detail.php` - 신규 생성
- `/var/www/html/mypage/orders.php` - 상세 링크 추가

### 테스트 결과
```
✅ 상세 페이지 접근 성공
✅ 섹션: 주문 정보, 제품 상세, 주문자 정보, 배송 정보, 결제 정보, 업로드 파일
✅ 뒤로가기 링크 동작
✅ JSON 규격 데이터 파싱 표시
```

---

## 33. 마이페이지 메인 페이지네이션 표준화 (2025-12-30)

### 증상
`/mypage/index.php`의 "전체 주문조회 & 배송조회" 섹션에서 페이지네이션이 모든 페이지 번호(1-34)를 표시하여 UI가 너무 길어짐.

### 수정 전
```
1 | 2 | 3 | 4 | 5 | 6 | ... | 32 | 33 | 34  (모든 페이지 표시)
```

### 수정 후
```
« | ‹ | 1 | 2 | 3 | 4 | 5 | 6 | ... | › | »  (1페이지)
« | ‹ | ... | 12 | 13 | 14 | 15 | 16 | 17 | 18 | 19 | 20 | 21 | 22 | ... | › | »  (17페이지)
```

### 구현 내용

**표준 페이지네이션 형식**:
- « (맨처음): `?page=1#order-history`
- ‹ (이전): `?page=N-1#order-history`
- 현재 페이지 ±5 범위의 페이지 번호만 표시
- ... (생략 표시): 범위 양끝에 더 있는 경우
- › (다음): `?page=N+1#order-history`
- » (맨끝): `?page=MAX#order-history`

**CSS 스타일링**:
```css
.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
}
.pagination a, .pagination span {
    min-width: 36px;
    height: 36px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.pagination a.active {
    background: #1466BA;
    color: white;
}
.pagination .disabled {
    color: #ccc;
    cursor: not-allowed;
}
```

**페이지 정보 표시**:
```
17 / 34 페이지
```

### 파일 변경
- `/mypage/index.php` (프로덕션 직접 수정)

### 테스트 결과
```
✅ Page 1: « | ‹ | 1 | 2 | 3 | 4 | 5 | 6 | ... | › | »
✅ Page 17: « | ‹ | ... | 12-22 (11개) | ... | › | »
✅ Active page 표시 정상
✅ Page info: 17 / 34 페이지
```

---

## 34. ProductSpecFormatter 표준화 (2025-12-31)

### 배경
규격/옵션 표시가 각 파일마다 다른 방식으로 구현되어 있어 일관성 문제 발생.
- cart.php, OnlineOrder_unified.php, OrderComplete_universal.php 등에서 제각각 구현
- 견적서 시스템(`mlangprintauto/quote/`)에 별도 ProductSpecFormatter 존재
- 코드 중복 및 유지보수 어려움

### 해결: 중앙 집중식 ProductSpecFormatter

**파일**: `/includes/ProductSpecFormatter.php`

**기능**:
- 모든 9개 품목에 대해 2줄 슬래시 형식 출력 지원
- HTML, Text, SingleLine 3가지 출력 포맷
- 정적 헬퍼 메서드 (getProductTypeName, getUnit, getQuantity, getPrice 등)

### 주요 메서드

#### 인스턴스 메서드 (DB 연결 필요)
```php
$formatter = new ProductSpecFormatter($db);

// HTML 형식 (2줄, <br> 구분)
$formatter->formatHtml($item);
// "90g아트지(합판인쇄) / A4 (210x297)<br>단면 / 0.5연 (2,000매) / 인쇄만"

// 텍스트 형식 (2줄, | 구분)
$formatter->formatText($item);
// "90g아트지(합판인쇄) / A4 (210x297) | 단면 / 0.5연 (2,000매) / 인쇄만"

// 한 줄 형식
$formatter->formatSingleLine($item);
// "90g아트지(합판인쇄) / A4 (210x297) / 단면 / 0.5연"
```

#### 정적 메서드 (DB 연결 불필요)
```php
// 제품 타입 한글명
ProductSpecFormatter::getProductTypeName('inserted');  // "전단지"
ProductSpecFormatter::getProductTypeName('namecard');  // "명함"

// 단위
ProductSpecFormatter::getUnit($item);  // "연", "매", "부", "권"

// 수량 숫자값
ProductSpecFormatter::getQuantity($item);  // 0.5, 1000

// 수량 표시용
ProductSpecFormatter::getQuantityDisplay($item);  // "0.5연 (2,000매)", "1,000매"

// 가격 (VAT 포함)
ProductSpecFormatter::getPrice($item);  // 156200

// 공급가액 (VAT 미포함)
ProductSpecFormatter::getSupplyPrice($item);  // 142000
```

### 견적서 시스템 통합

**변경 전**: `mlangprintauto/quote/includes/ProductSpecFormatter.php` (로컬 버전)
**변경 후**: 중앙 버전 사용 + 로컬 버전 백업

```php
// 변경된 include 경로
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/ProductSpecFormatter.php';
```

**수정된 파일**:
- `mlangprintauto/quote/includes/QuoteManager.php`
- `mlangprintauto/quote/edit.php`
- `mlangprintauto/quote/revise.php`

### 테스트 결과

**주문서 출력 (OrderFormPrint.php)**:
```
✅ [전단지] #104068: 90g아트지(합판인쇄) / A4 (210x297) | 단면 / 0.5연 (2,000매) / 인쇄만
✅ [명함] #104067: 일반명함(쿠폰) | 단면 / 500매 / 인쇄만
✅ [봉투] #104066: 소봉투 / 소봉투(100모조 220*105) | 마스터1도 / 1,000매 / 인쇄만
✅ [카다록] #104065: 카다록,리플렛 / 24절(127*260)3단 | 1,000매 / 인쇄만
✅ [NCR양식] #104064: 양식(100매철) / 계약서(A4).기타서식(A4) | 1도 / 10매 / 인쇄만
```

### 관련 파일
- `/var/www/html/includes/ProductSpecFormatter.php` - 중앙 포맷터 클래스
- `/var/www/html/mlangprintauto/quote/includes/ProductSpecFormatter.php.bak` - 백업
- `/var/www/html/mlangprintauto/quote/includes/QuoteManager.php` - include 경로 수정
- `/var/www/html/mlangprintauto/quote/edit.php` - include 경로 수정
- `/var/www/html/mlangprintauto/quote/revise.php` - include 경로 수정

### 관련 스킬
- `duson-print-rules/SKILL.md` - 규칙 2-5에 ProductSpecFormatter 사용법 추가
