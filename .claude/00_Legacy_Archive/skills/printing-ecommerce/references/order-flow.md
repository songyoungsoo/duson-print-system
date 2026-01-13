# 주문 플로우

## 5단계 프로세스

```
[1.제품선택] → [2.장바구니] → [3.주문서] → [4.결제] → [5.완료]
```

## 1단계: 제품 선택

### 가격 계산기 UI
```html
<form id="priceForm">
    <select name="size">...</select>      <!-- 사이즈 -->
    <select name="paper">...</select>     <!-- 용지 -->
    <select name="quantity">...</select>  <!-- 수량 -->
    <select name="sides">...</select>     <!-- 단면/양면 -->
    
    <!-- 추가옵션 -->
    <input type="checkbox" name="coating">  <!-- 코팅 -->
    <input type="checkbox" name="folding">  <!-- 접지 -->
    <input type="checkbox" name="scoring">  <!-- 오시 -->
    
    <div id="totalPrice">0원</div>
    <button type="button" onclick="addToCart()">장바구니</button>
</form>
```

### 장바구니 추가 (AJAX)
```javascript
function addToCart() {
    const data = {
        product_type: 'inserted',        // 제품 타입
        size: $('#size').val(),
        paper: $('#paper').val(),
        quantity: $('#quantity').val(),
        quantity_display: '0.5연 (2,000매)', // 표시용 수량
        sides: $('#sides').val(),
        options: getSelectedOptions(),    // 추가옵션 JSON
        price: calculatePrice(),
        file_path: uploadedFilePath       // 업로드된 파일
    };
    
    $.post('/mlangprintauto/shop/cart_add.php', data, function(res) {
        if (res.success) {
            updateCartCount();
            alert('장바구니에 추가되었습니다.');
        }
    });
}
```

## 2단계: 장바구니 (cart.php)

### shop_temp 테이블 구조
```sql
CREATE TABLE shop_temp (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    member_id INT,
    product_type VARCHAR(50),
    product_name VARCHAR(100),
    size VARCHAR(50),
    paper VARCHAR(100),
    quantity VARCHAR(50),           -- 원본값: "2000"
    quantity_display VARCHAR(100),  -- 표시용: "0.5연 (2,000매)"
    sides VARCHAR(20),
    options TEXT,                   -- JSON: {"coating":"유광", "folding":"2단"}
    price INT,
    file_path VARCHAR(255),
    created_at DATETIME
);
```

### 장바구니 목록 조회
```php
$session_id = session_id();
$member_id = $_SESSION['member_id'] ?? 0;

$sql = "SELECT * FROM shop_temp 
        WHERE session_id = ? OR member_id = ?
        ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id, $member_id]);
$cartItems = $stmt->fetchAll();
```

### 수량 수정
```php
// cart_update.php
$idx = $_POST['idx'];
$quantity = $_POST['quantity'];
$quantity_display = $_POST['quantity_display'];

$sql = "UPDATE shop_temp SET quantity = ?, quantity_display = ? WHERE idx = ?";
```

### 삭제
```php
// cart_delete.php
$sql = "DELETE FROM shop_temp WHERE idx = ? AND session_id = ?";
```

### 수량 단위 표시 규칙

제품별 수량 단위가 3개 페이지(장바구니, 주문페이지, 주문완료)에서 통일되어야 함.

| 제품 | 단위 | 표시 예시 |
|------|------|-----------|
| 전단지 (inserted/leaflet) | 연 | 0.5연(2,000매), 1연(4,000매) |
| 카다록 (cadarok) | 부 | 100부, 500부 |
| 포스터 (littleprint) | 매 | 100매, 500매 |
| 양식지 (ncrflambeau) | 권 | 10권, 50권 |
| 기타 제품 | 매 | 100매, 500매 |

### 규격/옵션 표시: 통합 서비스 (필수)

> **상세 문서**: `spec-display-rules.md` 참조

**핵심 서비스 파일**:
- `/includes/SpecDisplayService.php` - 통합 출력 서비스
- `/includes/ProductSpecFormatter.php` - 2줄 형식 포맷터

```php
// SpecDisplayService 사용 (권장)
$specDisplayService = new SpecDisplayService($db);
$displayData = $specDisplayService->getDisplayData($item);

$displayData['line1'];           // "카다록,리플렛 / 24절(127*260)3단"
$displayData['line2'];           // "단면칼라 / 1,000부 / 인쇄만"
$displayData['quantity_value'];  // 1000 (숫자만)
$displayData['unit'];            // "부"

// ProductSpecFormatter 사용
$specFormatter = new ProductSpecFormatter($db);
$specs = $specFormatter->format($item);
$specs['line1'];  // 규격
$specs['line2'];  // 옵션
```

**테이블 컬럼 너비 표준 (2026-01-12 확정)**:
```
관리자/주문서출력: | NO:6% | 품목:17% | 규격/옵션:44% | 수량:11% | 단위:9% | 공급가액:13% |
```

**관련 파일:**
- `/var/www/html/includes/SpecDisplayService.php` - 통합 출력 서비스
- `/var/www/html/includes/ProductSpecFormatter.php` - 중앙 포맷터
- `/var/www/html/mlangprintauto/shop/cart.php` - 장바구니
- `/var/www/html/mlangorder_printauto/OnlineOrder_unified.php` - 주문 페이지
- `/var/www/html/mlangorder_printauto/OrderComplete_universal.php` - 주문 완료
- `/var/www/html/mlangorder_printauto/OrderFormOrderTree.php` - 관리자 주문상세/출력

## 3단계: 주문서 (OnlineOrder_unified.php)

### 페이지 레이아웃 구조
```
┌─────────────────────────────────────────────┐
│ 주문 정보 입력 (헤더)                         │
│ - 배경: #1E90FF (파란색)                      │
│ - 폰트: 흰색, 2.8rem                         │
├─────────────────────────────────────────────┤
│ 주문 상품 목록 (5컬럼 테이블)                  │
│ - 품목 (15%, 중앙정렬)                        │
│ - 규격/옵션 (42%)                            │
│ - 수량 (10%, 중앙정렬)                        │
│ - 단위 (8%, 중앙정렬)                         │
│ - 공급가액 (25%, 우측정렬)                    │
├─────────────────────────────────────────────┤
│ 주문 요약 (상품금액 / 부가세 / 총 결제금액)    │
├─────────────────────────────────────────────┤
│ 신청자 정보 입력 폼                           │
├─────────────────────────────────────────────┤
│ 배송지 정보 입력 폼                           │
└─────────────────────────────────────────────┘
```

### 관련 파일
- `/var/www/html/mlangorder_printauto/OnlineOrder_unified.php` - 통합 주문서 페이지

### 배송 정보 폼
```html
<form id="orderForm" action="order_process.php" method="POST">
    <!-- 주문자 정보 -->
    <input name="orderer_name" required>
    <input name="orderer_phone" required>
    <input name="orderer_email">
    
    <!-- 배송지 정보 -->
    <input name="receiver_name" required>
    <input name="receiver_phone" required>
    <input name="postcode" id="postcode">
    <input name="address" id="address">
    <input name="address_detail">
    
    <!-- 결제 방식 -->
    <select name="payment_method">
        <option value="escrow">KB에스크로</option>
        <option value="card">신용카드</option>
        <option value="vbank">가상계좌</option>
    </select>
    
    <!-- 요청사항 -->
    <textarea name="memo"></textarea>
    
    <button type="submit">결제하기</button>
</form>
```

### 다음 주소 API 연동
```html
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
function searchAddress() {
    new daum.Postcode({
        oncomplete: function(data) {
            document.getElementById('postcode').value = data.zonecode;
            document.getElementById('address').value = data.roadAddress;
        }
    }).open();
}
</script>
```

## 4단계: 결제 (order_process.php)

### 주문서 생성
```php
// 주문번호 생성
$order_no = date('YmdHis') . rand(1000, 9999);

// orderform 저장
$sql = "INSERT INTO orderform (
    order_no, member_id, orderer_name, orderer_phone, orderer_email,
    receiver_name, receiver_phone, postcode, address, address_detail,
    payment_method, total_price, status, memo, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";

// orderformtree 저장 (장바구니 → 주문상세)
$sql = "INSERT INTO orderformtree (
    order_no, product_type, product_name, size, paper, 
    quantity, quantity_display, sides, options, price, file_path
) SELECT ?, product_type, product_name, size, paper,
    quantity, quantity_display, sides, options, price, file_path
FROM shop_temp WHERE session_id = ?";

// 장바구니 비우기
$sql = "DELETE FROM shop_temp WHERE session_id = ?";
```

### KB에스크로 결제 연동
```php
// → payment-system.md 참고
```

## 5단계: 주문 완료 (order_result.php)

### 완료 페이지 표시
```php
$order_no = $_GET['order_no'];

// 주문 정보 조회
$order = $pdo->query("SELECT * FROM orderform WHERE order_no = ?")->fetch();
$items = $pdo->query("SELECT * FROM orderformtree WHERE order_no = ?")->fetchAll();

// 주문 확인 이메일 발송
sendOrderConfirmEmail($order, $items);
```

### 주문 완료 UI (7컬럼 테이블)

| 컬럼 | 설명 | 너비 |
|------|------|------|
| 주문번호 | 주문 고유 ID (#1234) | 10% |
| 품목 | 제품명 (명함, 스티커 등) | 12% |
| 규격/옵션 | 용지, 사이즈, 추가옵션 등 상세 | 38% |
| 수량 | 숫자만 표시 | 10% |
| 단위 | 매, 연, 부, 권 등 | 8% |
| 공급가액 | VAT 별도 금액 | 12% |
| 상태 | 입금대기, 제작중, 완료 등 | 10% |

```html
<div class="order-complete">
    <h2>주문이 완료되었습니다!</h2>

    <table class="order-table">
        <thead>
            <tr>
                <th>주문번호</th>
                <th>품목</th>
                <th>규격/옵션</th>
                <th>수량</th>
                <th>단위</th>
                <th>공급가액</th>
                <th>상태</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td class="col-order-no">#<?= $item['no'] ?></td>
                <td class="col-product"><?= $item['Type'] ?></td>
                <td class="col-details"><?= displayProductDetails($item) ?></td>
                <td class="col-quantity"><?= $item['quantity_display'] ?></td>
                <td class="col-unit"><?= $item['unit'] ?></td>
                <td class="col-price"><?= number_format($item['price_supply']) ?>원</td>
                <td class="col-status"><span class="status-badge">입금대기</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 결제 금액 요약 -->
    <div class="order-summary">
        <div class="summary-box">상품금액: <?= number_format($total_supply) ?>원</div>
        <div class="summary-box">부가세: <?= number_format($total_vat) ?>원</div>
        <div class="summary-box total">총 결제금액: <?= number_format($total_with_vat) ?>원</div>
    </div>

    <a href="/member/mypage.php">주문내역 보기</a>
</div>
```

### 관련 파일
- `/var/www/html/mlangorder_printauto/OrderComplete_universal.php` - 통합 주문완료 페이지

## 메뉴 일관성

모든 단계에서 **동일한 헤더/메뉴** 유지:
- 사용자가 다른 제품 추가 가능
- 신뢰감 유지
- 결제 중에만 이탈 확인 팝업 표시
