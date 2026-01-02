# 결제 시스템

## KB에스크로 연동

### 개요
- PG사: KB국민카드 에스크로
- 결제 방식: 신용카드, 가상계좌, 실시간계좌이체
- 특징: 구매자 보호 (에스크로)

### 필수 설정 값
```php
// config/payment.php
define('KB_MERCHANT_ID', 'your_merchant_id');
define('KB_MERCHANT_KEY', 'your_merchant_key');
define('KB_API_URL', 'https://kbcard.com/api/...');  // 실제 URL은 KB 문서 참조
define('KB_RETURN_URL', 'https://dsp1830.shop/payment/kb_return.php');
define('KB_CANCEL_URL', 'https://dsp1830.shop/payment/kb_cancel.php');
```

### 결제 요청 (payment_request.php)

```php
<?php
// 주문 정보
$order_no = $_POST['order_no'];
$amount = $_POST['amount'];
$product_name = $_POST['product_name'];
$buyer_name = $_POST['buyer_name'];
$buyer_email = $_POST['buyer_email'];
$buyer_tel = $_POST['buyer_tel'];

// 결제 데이터 생성
$payment_data = [
    'merchant_id' => KB_MERCHANT_ID,
    'order_no' => $order_no,
    'amount' => $amount,
    'product_name' => mb_substr($product_name, 0, 40),  // 40자 제한
    'buyer_name' => $buyer_name,
    'buyer_email' => $buyer_email,
    'buyer_tel' => $buyer_tel,
    'return_url' => KB_RETURN_URL,
    'cancel_url' => KB_CANCEL_URL,
    'timestamp' => date('YmdHis'),
];

// 해시 생성 (위변조 방지)
$hash_string = $payment_data['merchant_id'] . $payment_data['order_no'] . 
               $payment_data['amount'] . $payment_data['timestamp'] . KB_MERCHANT_KEY;
$payment_data['signature'] = hash('sha256', $hash_string);

// payments 테이블에 기록
$sql = "INSERT INTO payments (order_no, payment_method, amount, status, created_at) 
        VALUES (?, 'escrow', ?, 'pending', NOW())";
$pdo->prepare($sql)->execute([$order_no, $amount]);
?>

<!-- 결제 폼 (자동 제출) -->
<form id="paymentForm" action="<?= KB_API_URL ?>" method="POST">
    <?php foreach ($payment_data as $key => $value): ?>
    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
    <?php endforeach; ?>
</form>

<script>
document.getElementById('paymentForm').submit();
</script>
```

### 결제 완료 처리 (kb_return.php)

```php
<?php
// PG 응답 검증
$result_code = $_POST['result_code'];
$result_msg = $_POST['result_msg'];
$order_no = $_POST['order_no'];
$amount = $_POST['amount'];
$pg_tid = $_POST['pg_tid'];
$signature = $_POST['signature'];

// 서명 검증
$expected_sig = hash('sha256', $order_no . $amount . $pg_tid . KB_MERCHANT_KEY);
if ($signature !== $expected_sig) {
    die('서명 검증 실패');
}

// 결제 성공
if ($result_code === '0000') {
    // payments 업데이트
    $sql = "UPDATE payments SET 
            pg_tid = ?, pg_result_code = ?, pg_result_msg = ?, 
            status = 'paid', paid_at = NOW()
            WHERE order_no = ?";
    $pdo->prepare($sql)->execute([$pg_tid, $result_code, $result_msg, $order_no]);
    
    // orderform 상태 변경
    $sql = "UPDATE orderform SET status = 'paid' WHERE order_no = ?";
    $pdo->prepare($sql)->execute([$order_no]);
    
    // 주문 확인 이메일 발송
    sendOrderConfirmEmail($order_no);
    
    // 완료 페이지로 이동
    header("Location: /mlangprintauto/shop/order_result.php?order_no=$order_no");
} else {
    // 결제 실패
    $sql = "UPDATE payments SET pg_result_code = ?, pg_result_msg = ?, status = 'failed' 
            WHERE order_no = ?";
    $pdo->prepare($sql)->execute([$result_code, $result_msg, $order_no]);
    
    header("Location: /mlangprintauto/shop/order.php?error=" . urlencode($result_msg));
}
```

### 가상계좌 입금 통보 (kb_webhook.php)

```php
<?php
// 입금 통보 (웹훅)
$order_no = $_POST['order_no'];
$amount = $_POST['amount'];
$deposit_name = $_POST['deposit_name'];
$pg_tid = $_POST['pg_tid'];

// 입금 확인
$stmt = $pdo->prepare("SELECT * FROM payments WHERE order_no = ? AND status = 'pending'");
$stmt->execute([$order_no]);
$payment = $stmt->fetch();

if ($payment && (int)$payment['amount'] === (int)$amount) {
    // 결제 완료 처리
    $sql = "UPDATE payments SET status = 'paid', paid_at = NOW() WHERE order_no = ?";
    $pdo->prepare($sql)->execute([$order_no]);
    
    $sql = "UPDATE orderform SET status = 'paid' WHERE order_no = ?";
    $pdo->prepare($sql)->execute([$order_no]);
    
    // 이메일 발송
    sendOrderConfirmEmail($order_no);
    
    echo 'OK';
} else {
    echo 'FAIL';
}
```

## 결제 취소/환불

### 취소 요청
```php
function cancelPayment($order_no, $reason) {
    global $pdo;
    
    // 결제 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_no = ? AND status = 'paid'");
    $stmt->execute([$order_no]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        return ['success' => false, 'error' => '취소할 결제가 없습니다.'];
    }
    
    // PG 취소 API 호출
    $cancel_data = [
        'merchant_id' => KB_MERCHANT_ID,
        'pg_tid' => $payment['pg_tid'],
        'amount' => $payment['amount'],
        'reason' => $reason,
        'timestamp' => date('YmdHis'),
    ];
    
    $cancel_data['signature'] = hash('sha256', 
        $cancel_data['pg_tid'] . $cancel_data['amount'] . KB_MERCHANT_KEY);
    
    // API 호출
    $response = callKBCancelAPI($cancel_data);
    
    if ($response['result_code'] === '0000') {
        // DB 업데이트
        $sql = "UPDATE payments SET status = 'cancelled' WHERE order_no = ?";
        $pdo->prepare($sql)->execute([$order_no]);
        
        $sql = "UPDATE orderform SET status = 'cancelled' WHERE order_no = ?";
        $pdo->prepare($sql)->execute([$order_no]);
        
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => $response['result_msg']];
}
```

## 결제 상태 관리

### 상태 코드
```php
const PAYMENT_STATUS = [
    'pending'   => '결제대기',
    'paid'      => '결제완료',
    'cancelled' => '결제취소',
    'refunded'  => '환불완료',
    'failed'    => '결제실패'
];
```

### 결제 내역 조회 (관리자)
```php
$sql = "SELECT p.*, o.orderer_name, o.orderer_phone
        FROM payments p
        JOIN orderform o ON p.order_no = o.order_no
        WHERE p.created_at BETWEEN ? AND ?
        ORDER BY p.created_at DESC";
```

## 무통장 입금 (가상계좌)

### 가상계좌 발급
```html
<!-- 결제 완료 시 가상계좌 정보 표시 -->
<div class="vbank-info">
    <h3>무통장 입금 정보</h3>
    <p>은행: <?= $payment['vbank_name'] ?></p>
    <p>계좌번호: <?= $payment['vbank_num'] ?></p>
    <p>예금주: (주)두손기획인쇄</p>
    <p>입금기한: <?= $payment['vbank_date'] ?></p>
    <p>입금금액: <?= number_format($payment['amount']) ?>원</p>
    <p class="notice">※ 입금자명을 주문자명과 동일하게 입금해주세요.</p>
</div>
```

### 입금 확인 메일
```php
function sendVbankNoticeEmail($order_no) {
    $order = getOrderInfo($order_no);
    $payment = getPaymentInfo($order_no);
    
    $subject = "[두손기획인쇄] 가상계좌가 발급되었습니다.";
    $body = "
        주문번호: {$order['order_no']}
        입금은행: {$payment['vbank_name']}
        계좌번호: {$payment['vbank_num']}
        입금금액: " . number_format($payment['amount']) . "원
        입금기한: {$payment['vbank_date']}
    ";
    
    sendEmail($order['orderer_email'], $subject, $body);
}
```

## 결제 로그

```sql
CREATE TABLE payment_logs (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(20),
    action VARCHAR(50),        -- request, return, webhook, cancel
    request_data TEXT,
    response_data TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_order (order_no)
);
```

```php
function logPayment($order_no, $action, $request, $response) {
    global $pdo;
    $sql = "INSERT INTO payment_logs (order_no, action, request_data, response_data, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $pdo->prepare($sql)->execute([
        $order_no, 
        $action, 
        json_encode($request), 
        json_encode($response), 
        $_SERVER['REMOTE_ADDR']
    ]);
}
```
