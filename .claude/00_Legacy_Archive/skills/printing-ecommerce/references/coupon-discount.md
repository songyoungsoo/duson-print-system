# 쿠폰 / 할인 시스템

## 테이블 구조

### 쿠폰
```sql
CREATE TABLE coupons (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,      -- 쿠폰 코드
    name VARCHAR(100) NOT NULL,            -- 쿠폰명
    
    -- 할인 정보
    discount_type ENUM('percent', 'fixed') NOT NULL,  -- 정률/정액
    discount_value INT NOT NULL,            -- 할인값 (% 또는 원)
    min_order_amount INT DEFAULT 0,         -- 최소 주문금액
    max_discount INT DEFAULT 0,             -- 최대 할인금액 (정률 시)
    
    -- 사용 조건
    use_limit INT DEFAULT 0,                -- 총 사용 가능 횟수 (0=무제한)
    use_count INT DEFAULT 0,                -- 현재 사용 횟수
    per_user_limit INT DEFAULT 1,           -- 1인당 사용 횟수
    
    -- 적용 대상
    target_type ENUM('all', 'category', 'product') DEFAULT 'all',
    target_ids TEXT,                        -- JSON: ["sticker", "namecard"]
    
    -- 유효기간
    start_date DATETIME,
    end_date DATETIME,
    
    status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_status_date (status, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 쿠폰 사용 내역
```sql
CREATE TABLE coupon_usage (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    member_id INT NOT NULL,
    order_no VARCHAR(20) NOT NULL,
    discount_amount INT NOT NULL,           -- 실제 할인 금액
    used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_coupon (coupon_id),
    INDEX idx_member (member_id),
    INDEX idx_order (order_no),
    UNIQUE KEY uk_order_coupon (order_no, coupon_id)
);
```

### 회원 보유 쿠폰
```sql
CREATE TABLE member_coupons (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    coupon_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'available',  -- available/used/expired
    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    used_at DATETIME,
    
    INDEX idx_member_status (member_id, status),
    UNIQUE KEY uk_member_coupon (member_id, coupon_id)
);
```

## 쿠폰 유효성 검증

```php
class CouponValidator {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 쿠폰 검증
     * @return array ['valid' => bool, 'coupon' => array|null, 'error' => string|null]
     */
    public function validate($code, $member_id, $cart_items, $cart_total) {
        // 1. 쿠폰 조회
        $coupon = $this->getCoupon($code);
        if (!$coupon) {
            return ['valid' => false, 'error' => '유효하지 않은 쿠폰입니다.'];
        }
        
        // 2. 상태 확인
        if ($coupon['status'] !== 'active') {
            return ['valid' => false, 'error' => '사용할 수 없는 쿠폰입니다.'];
        }
        
        // 3. 유효기간 확인
        $now = date('Y-m-d H:i:s');
        if ($coupon['start_date'] && $now < $coupon['start_date']) {
            return ['valid' => false, 'error' => '아직 사용 기간이 아닙니다.'];
        }
        if ($coupon['end_date'] && $now > $coupon['end_date']) {
            return ['valid' => false, 'error' => '사용 기간이 만료된 쿠폰입니다.'];
        }
        
        // 4. 총 사용 횟수 확인
        if ($coupon['use_limit'] > 0 && $coupon['use_count'] >= $coupon['use_limit']) {
            return ['valid' => false, 'error' => '소진된 쿠폰입니다.'];
        }
        
        // 5. 회원별 사용 횟수 확인
        if ($member_id > 0) {
            $usage_count = $this->getMemberUsageCount($coupon['idx'], $member_id);
            if ($usage_count >= $coupon['per_user_limit']) {
                return ['valid' => false, 'error' => '이미 사용한 쿠폰입니다.'];
            }
        }
        
        // 6. 최소 주문금액 확인
        if ($cart_total < $coupon['min_order_amount']) {
            $min = number_format($coupon['min_order_amount']);
            return ['valid' => false, 'error' => "최소 주문금액 {$min}원 이상 주문 시 사용 가능합니다."];
        }
        
        // 7. 적용 대상 확인
        if (!$this->checkTarget($coupon, $cart_items)) {
            return ['valid' => false, 'error' => '해당 상품에는 적용할 수 없는 쿠폰입니다.'];
        }
        
        // 8. 할인 금액 계산
        $discount = $this->calculateDiscount($coupon, $cart_total);
        
        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount
        ];
    }
    
    private function getCoupon($code) {
        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }
    
    private function getMemberUsageCount($coupon_id, $member_id) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND member_id = ?
        ");
        $stmt->execute([$coupon_id, $member_id]);
        return $stmt->fetchColumn();
    }
    
    private function checkTarget($coupon, $cart_items) {
        if ($coupon['target_type'] === 'all') return true;
        
        $target_ids = json_decode($coupon['target_ids'], true) ?: [];
        
        foreach ($cart_items as $item) {
            $match = false;
            
            if ($coupon['target_type'] === 'category') {
                $match = in_array($item['product_type'], $target_ids);
            } elseif ($coupon['target_type'] === 'product') {
                $match = in_array($item['product_id'], $target_ids);
            }
            
            if ($match) return true;
        }
        
        return false;
    }
    
    private function calculateDiscount($coupon, $amount) {
        if ($coupon['discount_type'] === 'percent') {
            $discount = (int)($amount * $coupon['discount_value'] / 100);
            
            // 최대 할인금액 제한
            if ($coupon['max_discount'] > 0 && $discount > $coupon['max_discount']) {
                $discount = $coupon['max_discount'];
            }
        } else {
            $discount = $coupon['discount_value'];
        }
        
        // 할인이 주문금액을 초과하지 않도록
        return min($discount, $amount);
    }
}
```

## 주문서 쿠폰 적용 UI

### HTML
```html
<div class="coupon-section">
    <h3>쿠폰 적용</h3>
    
    <div class="coupon-input">
        <input type="text" id="couponCode" placeholder="쿠폰 코드 입력">
        <button type="button" onclick="applyCoupon()">적용</button>
    </div>
    
    <!-- 보유 쿠폰 선택 -->
    <?php if ($member_id): ?>
    <div class="my-coupons">
        <select id="myCouponSelect" onchange="selectMyCoupon(this)">
            <option value="">보유 쿠폰 선택</option>
            <?php foreach ($my_coupons as $mc): ?>
            <option value="<?= $mc['code'] ?>" data-discount="<?= $mc['discount_desc'] ?>">
                <?= h($mc['name']) ?> (<?= $mc['discount_desc'] ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    
    <!-- 적용된 쿠폰 표시 -->
    <div id="appliedCoupon" style="display:none;">
        <span class="coupon-name"></span>
        <span class="coupon-discount"></span>
        <button type="button" onclick="removeCoupon()">취소</button>
    </div>
    
    <input type="hidden" name="coupon_code" id="appliedCouponCode">
    <input type="hidden" name="coupon_discount" id="appliedCouponDiscount">
</div>
```

### JavaScript
```javascript
let appliedCoupon = null;

async function applyCoupon() {
    const code = document.getElementById('couponCode').value.trim();
    if (!code) {
        alert('쿠폰 코드를 입력해주세요.');
        return;
    }
    
    const res = await fetch('/api/validate_coupon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            code: code,
            cart_total: cartTotal
        })
    });
    
    const result = await res.json();
    
    if (result.valid) {
        appliedCoupon = result;
        showAppliedCoupon(result.coupon.name, result.discount);
    } else {
        alert(result.error);
    }
}

function selectMyCoupon(select) {
    const code = select.value;
    if (code) {
        document.getElementById('couponCode').value = code;
        applyCoupon();
    }
}

function showAppliedCoupon(name, discount) {
    document.querySelector('#appliedCoupon .coupon-name').textContent = name;
    document.querySelector('#appliedCoupon .coupon-discount').textContent = 
        '-' + discount.toLocaleString() + '원';
    document.getElementById('appliedCoupon').style.display = 'block';
    
    document.getElementById('appliedCouponCode').value = appliedCoupon.coupon.code;
    document.getElementById('appliedCouponDiscount').value = discount;
    
    updateTotalPrice();
}

function removeCoupon() {
    appliedCoupon = null;
    document.getElementById('appliedCoupon').style.display = 'none';
    document.getElementById('appliedCouponCode').value = '';
    document.getElementById('appliedCouponDiscount').value = 0;
    document.getElementById('couponCode').value = '';
    document.getElementById('myCouponSelect').value = '';
    
    updateTotalPrice();
}

function updateTotalPrice() {
    const subtotal = cartTotal;
    const shipping = shippingFee;
    const discount = appliedCoupon ? appliedCoupon.discount : 0;
    
    const total = subtotal + shipping - discount;
    
    document.getElementById('orderTotal').textContent = total.toLocaleString() + '원';
    document.getElementById('discountAmount').textContent = '-' + discount.toLocaleString() + '원';
}
```

## 쿠폰 사용 처리

```php
// order_process.php
if (!empty($_POST['coupon_code'])) {
    $coupon_code = $_POST['coupon_code'];
    $coupon_discount = intval($_POST['coupon_discount']);
    
    $validator = new CouponValidator($pdo);
    $result = $validator->validate($coupon_code, $member_id, $cart_items, $cart_total);
    
    if (!$result['valid']) {
        die('쿠폰 오류: ' . $result['error']);
    }
    
    // 실제 할인금액 검증 (프론트 조작 방지)
    if ($coupon_discount !== $result['discount']) {
        $coupon_discount = $result['discount'];
    }
    
    // 쿠폰 사용 기록
    $pdo->prepare("
        INSERT INTO coupon_usage (coupon_id, member_id, order_no, discount_amount)
        VALUES (?, ?, ?, ?)
    ")->execute([
        $result['coupon']['idx'],
        $member_id,
        $order_no,
        $coupon_discount
    ]);
    
    // 쿠폰 사용 횟수 증가
    $pdo->prepare("UPDATE coupons SET use_count = use_count + 1 WHERE idx = ?")
        ->execute([$result['coupon']['idx']]);
    
    // 회원 쿠폰 상태 변경
    if ($member_id) {
        $pdo->prepare("
            UPDATE member_coupons SET status = 'used', used_at = NOW()
            WHERE member_id = ? AND coupon_id = ?
        ")->execute([$member_id, $result['coupon']['idx']]);
    }
}
```

## 쿠폰 발급

### 자동 발급 (회원가입 시)
```php
function issueWelcomeCoupon($member_id) {
    global $pdo;
    
    // 신규가입 쿠폰 ID
    $coupon_id = 1;  // 또는 설정에서 가져오기
    
    $pdo->prepare("
        INSERT IGNORE INTO member_coupons (member_id, coupon_id, issued_at)
        VALUES (?, ?, NOW())
    ")->execute([$member_id, $coupon_id]);
}
```

### 일괄 발급 (관리자)
```php
// admin/coupon/bulk_issue.php
$coupon_id = $_POST['coupon_id'];
$member_ids = $_POST['member_ids'];  // 배열

foreach ($member_ids as $member_id) {
    $pdo->prepare("
        INSERT IGNORE INTO member_coupons (member_id, coupon_id, issued_at)
        VALUES (?, ?, NOW())
    ")->execute([$member_id, $coupon_id]);
}
```

## 대량 주문 할인

```php
// 자동 할인 (가격 계산 시 적용)
const BULK_DISCOUNTS = [
    5000  => 0.03,   // 5,000원 이상: 3%
    10000 => 0.05,   // 10,000원 이상: 5%
    30000 => 0.07,   // 30,000원 이상: 7%
    50000 => 0.10,   // 50,000원 이상: 10%
];

function calculateBulkDiscount($amount) {
    $discount_rate = 0;
    
    foreach (BULK_DISCOUNTS as $threshold => $rate) {
        if ($amount >= $threshold) {
            $discount_rate = $rate;
        }
    }
    
    return (int)($amount * $discount_rate);
}
```

## 관리자: 쿠폰 관리

### 쿠폰 생성 폼
```html
<form action="save.php" method="POST">
    <div class="form-group">
        <label>쿠폰코드</label>
        <input type="text" name="code" required>
        <button type="button" onclick="generateCode()">자동생성</button>
    </div>
    
    <div class="form-group">
        <label>쿠폰명</label>
        <input type="text" name="name" required>
    </div>
    
    <div class="form-group">
        <label>할인 유형</label>
        <select name="discount_type">
            <option value="percent">정률 (%)</option>
            <option value="fixed">정액 (원)</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>할인값</label>
        <input type="number" name="discount_value" required>
    </div>
    
    <div class="form-group">
        <label>최소 주문금액</label>
        <input type="number" name="min_order_amount" value="0">
    </div>
    
    <div class="form-group">
        <label>유효기간</label>
        <input type="datetime-local" name="start_date">
        ~
        <input type="datetime-local" name="end_date">
    </div>
    
    <button type="submit">저장</button>
</form>

<script>
function generateCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 10; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.querySelector('input[name="code"]').value = code;
}
</script>
```
