# 배송 시스템

## 배송 프로세스

```
[결제완료] → [인쇄중] → [발송완료] → [배송중] → [배송완료]
```

## 배송 정보 테이블

```sql
CREATE TABLE order_shipping (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(20) NOT NULL,
    
    -- 배송지 정보
    receiver_name VARCHAR(50),
    receiver_phone VARCHAR(20),
    postcode VARCHAR(10),
    address VARCHAR(255),
    address_detail VARCHAR(255),
    
    -- 택배 정보
    courier_code VARCHAR(20),       -- CJ, HANJIN, LOTTE...
    courier_name VARCHAR(50),       -- CJ대한통운, 한진택배...
    tracking_no VARCHAR(50),
    
    -- 상태
    status VARCHAR(20) DEFAULT 'pending',  -- pending/shipped/in_transit/delivered
    
    -- 일시
    shipped_at DATETIME,
    delivered_at DATETIME,
    
    memo TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_order (order_no),
    INDEX idx_tracking (courier_code, tracking_no)
);
```

## 택배사 코드

```php
const COURIERS = [
    'CJ'      => ['name' => 'CJ대한통운', 'url' => 'https://www.cjlogistics.com/ko/tool/parcel/tracking?gnbInvcNo='],
    'HANJIN'  => ['name' => '한진택배', 'url' => 'https://www.hanjin.com/kor/CMS/DeliveryMgr/WaybillResult.do?mession=&wblnum='],
    'LOTTE'   => ['name' => '롯데택배', 'url' => 'https://www.lotteglogis.com/home/reservation/tracking/linkView?InvNo='],
    'POST'    => ['name' => '우체국택배', 'url' => 'https://service.epost.go.kr/trace.RetrieveDomRi498.postal?sid1='],
    'LOGEN'   => ['name' => '로젠택배', 'url' => 'https://www.ilogen.com/web/personal/trace/'],
    'DAESIN'  => ['name' => '대신택배', 'url' => 'https://www.ds3211.co.kr/freight/internalFreightSearch.ht?billno='],
];

function getTrackingUrl($courier_code, $tracking_no) {
    $courier = COURIERS[$courier_code] ?? null;
    if (!$courier) return null;
    return $courier['url'] . $tracking_no;
}
```

## 운송장 등록 (관리자)

### 운송장 입력 폼
```html
<form id="shippingForm" action="update_shipping.php" method="POST">
    <input type="hidden" name="order_no" value="<?= $order_no ?>">
    
    <select name="courier_code" required>
        <option value="">택배사 선택</option>
        <?php foreach (COURIERS as $code => $info): ?>
        <option value="<?= $code ?>"><?= $info['name'] ?></option>
        <?php endforeach; ?>
    </select>
    
    <input type="text" name="tracking_no" placeholder="운송장번호" required
           pattern="[0-9]+" title="숫자만 입력">
    
    <button type="submit">등록</button>
</form>
```

### 운송장 처리
```php
// admin/update_shipping.php
$order_no = $_POST['order_no'];
$courier_code = $_POST['courier_code'];
$tracking_no = preg_replace('/[^0-9]/', '', $_POST['tracking_no']);
$courier_name = COURIERS[$courier_code]['name'] ?? '';

// order_shipping 저장
$sql = "INSERT INTO order_shipping (order_no, courier_code, courier_name, tracking_no, status, shipped_at)
        VALUES (?, ?, ?, ?, 'shipped', NOW())
        ON DUPLICATE KEY UPDATE 
        courier_code = VALUES(courier_code),
        courier_name = VALUES(courier_name),
        tracking_no = VALUES(tracking_no),
        status = 'shipped',
        shipped_at = NOW()";
$pdo->prepare($sql)->execute([$order_no, $courier_code, $courier_name, $tracking_no]);

// orderform 상태 변경
$sql = "UPDATE orderform SET status = 'shipping', courier = ?, tracking_no = ? WHERE order_no = ?";
$pdo->prepare($sql)->execute([$courier_name, $tracking_no, $order_no]);

// 배송 알림 이메일
sendShippingNoticeEmail($order_no, $courier_name, $tracking_no);

// SMS 발송 (선택)
sendShippingSMS($order_no, $courier_name, $tracking_no);
```

## 배송 조회 (고객)

### 배송 조회 페이지
```php
// member/tracking.php
$order_no = $_GET['order_no'];

// 배송 정보 조회
$sql = "SELECT s.*, o.receiver_name, o.receiver_phone
        FROM order_shipping s
        JOIN orderform o ON s.order_no = o.order_no
        WHERE s.order_no = ?";
$shipping = $pdo->prepare($sql)->execute([$order_no])->fetch();

$tracking_url = getTrackingUrl($shipping['courier_code'], $shipping['tracking_no']);
```

### 배송 조회 UI
```html
<div class="shipping-info">
    <h3>배송 정보</h3>
    
    <?php if ($shipping['tracking_no']): ?>
    <table>
        <tr>
            <th>택배사</th>
            <td><?= $shipping['courier_name'] ?></td>
        </tr>
        <tr>
            <th>운송장번호</th>
            <td>
                <?= $shipping['tracking_no'] ?>
                <a href="<?= $tracking_url ?>" target="_blank" class="btn-track">
                    배송조회
                </a>
            </td>
        </tr>
        <tr>
            <th>발송일</th>
            <td><?= $shipping['shipped_at'] ?></td>
        </tr>
    </table>
    
    <!-- iframe으로 실시간 조회 (선택) -->
    <iframe src="<?= $tracking_url ?>" width="100%" height="400"></iframe>
    
    <?php else: ?>
    <p class="notice">아직 발송 전입니다. 발송 시 안내 드리겠습니다.</p>
    <?php endif; ?>
</div>
```

## 배송 알림

### 발송 완료 이메일
```php
function sendShippingNoticeEmail($order_no, $courier_name, $tracking_no) {
    $order = getOrderInfo($order_no);
    $tracking_url = getTrackingUrl($order['courier_code'], $tracking_no);
    
    $subject = "[두손기획인쇄] 주문하신 상품이 발송되었습니다.";
    $body = "
    <h2>배송 안내</h2>
    <p>주문번호: {$order_no}</p>
    <p>택배사: {$courier_name}</p>
    <p>운송장번호: {$tracking_no}</p>
    <p><a href='{$tracking_url}'>배송조회하기</a></p>
    ";
    
    sendEmail($order['orderer_email'], $subject, $body);
}
```

### 발송 완료 SMS
```php
function sendShippingSMS($order_no, $courier_name, $tracking_no) {
    $order = getOrderInfo($order_no);
    
    $message = "[두손기획인쇄] 발송완료\n"
             . "택배: {$courier_name}\n"
             . "운송장: {$tracking_no}";
    
    // SMS API 호출 (예: 알리고)
    sendSMS($order['orderer_phone'], $message);
}
```

## 배송비 계산

```php
const SHIPPING_RULES = [
    'free_threshold' => 50000,    // 5만원 이상 무료배송
    'base_fee' => 3000,           // 기본 배송비
    'island_extra' => 3000,       // 도서산간 추가
];

function calculateShippingFee($total_price, $postcode) {
    // 무료배송 조건
    if ($total_price >= SHIPPING_RULES['free_threshold']) {
        return 0;
    }
    
    $fee = SHIPPING_RULES['base_fee'];
    
    // 도서산간 지역 체크
    if (isIslandArea($postcode)) {
        $fee += SHIPPING_RULES['island_extra'];
    }
    
    return $fee;
}

// 도서산간 지역 체크 (우편번호 기준)
function isIslandArea($postcode) {
    $island_postcodes = [
        '63' => true,  // 제주
        // 기타 도서산간 우편번호...
    ];
    
    $prefix = substr($postcode, 0, 2);
    return isset($island_postcodes[$prefix]);
}
```

## 배송 현황 대시보드 (관리자)

```php
// 배송 현황 요약
$sql = "SELECT 
    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as ready_to_ship,
    SUM(CASE WHEN status = 'printing' THEN 1 ELSE 0 END) as printing,
    SUM(CASE WHEN status = 'shipping' THEN 1 ELSE 0 END) as in_transit,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as delivered_today
FROM orderform
WHERE DATE(created_at) = CURDATE()";
```

### 미발송 주문 목록
```php
$sql = "SELECT o.*, 
        DATEDIFF(NOW(), o.created_at) as days_elapsed
FROM orderform o
WHERE o.status = 'paid'
  AND o.tracking_no IS NULL
ORDER BY o.created_at ASC";
```
