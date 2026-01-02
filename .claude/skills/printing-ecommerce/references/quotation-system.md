# 견적 시스템

## 개요

특수 사양이나 대량 주문 시 별도 견적을 받는 시스템.
일반 가격표에 없는 제품도 견적 요청 가능.

## 견적 프로세스

```
[견적요청] → [검토/산출] → [견적발송] → [승인] → [가상상품생성] → [주문]
```

## 견적 상태 코드

```php
const QUOTE_STATUS = [
    'requested' => '견적요청',
    'reviewing' => '검토중',
    'quoted'    => '견적발송',
    'accepted'  => '승인',
    'rejected'  => '거절',
    'ordered'   => '주문완료',
    'expired'   => '기간만료',
];
```

## 고객: 견적 요청

### 견적 요청 폼
```html
<form action="quote_submit.php" method="POST" enctype="multipart/form-data">
    <h2>견적 요청</h2>
    
    <!-- 제품 종류 -->
    <select name="product_type" required>
        <option value="">제품 종류 선택</option>
        <option value="sticker">스티커</option>
        <option value="flyer">전단지</option>
        <option value="namecard">명함</option>
        <option value="envelope">봉투</option>
        <option value="catalog">카달로그</option>
        <option value="poster">포스터</option>
        <option value="other">기타</option>
    </select>
    
    <!-- 상세 사양 -->
    <div class="specifications">
        <label>사이즈</label>
        <input type="text" name="size" placeholder="예: A4, 90x50mm">
        
        <label>용지</label>
        <input type="text" name="paper" placeholder="예: 아트지 200g">
        
        <label>수량</label>
        <input type="text" name="quantity" placeholder="예: 5,000매">
        
        <label>인쇄 방식</label>
        <select name="print_type">
            <option value="offset">오프셋 인쇄</option>
            <option value="digital">디지털 인쇄</option>
            <option value="silk">실크 인쇄</option>
        </select>
        
        <label>후가공</label>
        <textarea name="finishing" placeholder="예: 유광코팅, 2단접지, 오시 1줄"></textarea>
    </div>
    
    <!-- 상세 요청사항 -->
    <label>상세 요청사항</label>
    <textarea name="description" rows="5" placeholder="추가 요청사항이나 특이사항을 입력해주세요"></textarea>
    
    <!-- 참고 파일 -->
    <label>참고 파일 (선택)</label>
    <input type="file" name="reference_file" accept=".pdf,.jpg,.png,.ai,.psd">
    
    <!-- 희망 납기일 -->
    <label>희망 납기일</label>
    <input type="date" name="desired_date" min="<?= date('Y-m-d', strtotime('+3 days')) ?>">
    
    <!-- 연락처 -->
    <label>이름</label>
    <input type="text" name="name" required value="<?= $_SESSION['name'] ?? '' ?>">
    
    <label>연락처</label>
    <input type="tel" name="phone" required value="<?= $_SESSION['phone'] ?? '' ?>">
    
    <label>이메일</label>
    <input type="email" name="email" required value="<?= $_SESSION['email'] ?? '' ?>">
    
    <button type="submit">견적 요청</button>
</form>
```

### 견적 요청 처리
```php
// quote_submit.php
$quote_no = 'Q' . date('YmdHis') . rand(100, 999);

// 파일 업로드
$file_path = null;
if (isset($_FILES['reference_file']) && $_FILES['reference_file']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['reference_file']['name'], PATHINFO_EXTENSION);
    $new_name = $quote_no . '.' . $ext;
    $file_path = '/uploads/quotations/' . $new_name;
    move_uploaded_file($_FILES['reference_file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $file_path);
}

// 사양 JSON 저장
$specifications = json_encode([
    'size' => $_POST['size'],
    'paper' => $_POST['paper'],
    'quantity' => $_POST['quantity'],
    'print_type' => $_POST['print_type'],
    'finishing' => $_POST['finishing'],
], JSON_UNESCAPED_UNICODE);

// DB 저장
$sql = "INSERT INTO quotations (
    quote_no, member_id, product_type, specifications, 
    description, reference_file, desired_date,
    name, phone, email, status, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'requested', NOW())";

$member_id = $_SESSION['member_id'] ?? 0;
$pdo->prepare($sql)->execute([
    $quote_no, $member_id, $_POST['product_type'], $specifications,
    $_POST['description'], $file_path, $_POST['desired_date'],
    $_POST['name'], $_POST['phone'], $_POST['email']
]);

// 관리자 알림
notifyAdminNewQuote($quote_no);

// 완료 페이지
header("Location: quote_complete.php?no=$quote_no");
```

## 관리자: 견적 관리

### 견적 목록 (admin/quotations/)
```php
$status_filter = $_GET['status'] ?? '';

$where = [];
$params = [];

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$sql = "SELECT q.*, m.user_id 
        FROM quotations q
        LEFT JOIN members m ON q.member_id = m.idx
        " . ($where ? "WHERE " . implode(' AND ', $where) : "") . "
        ORDER BY q.created_at DESC";
```

### 견적 발송
```php
// admin/quotations/send_quote.php
$quote_no = $_POST['quote_no'];
$quoted_price = (int)$_POST['quoted_price'];
$admin_comment = $_POST['admin_comment'];
$valid_until = $_POST['valid_until'];  // 견적 유효기간

// 견적 업데이트
$sql = "UPDATE quotations SET 
        quoted_price = ?, 
        admin_comment = ?,
        valid_until = ?,
        status = 'quoted',
        admin_id = ?,
        quoted_at = NOW()
        WHERE quote_no = ?";
$pdo->prepare($sql)->execute([
    $quoted_price, $admin_comment, $valid_until, $_SESSION['admin_id'], $quote_no
]);

// 고객에게 이메일 발송
sendQuoteEmail($quote_no);
```

### 견적서 이메일
```php
function sendQuoteEmail($quote_no) {
    $quote = getQuoteInfo($quote_no);
    $specs = json_decode($quote['specifications'], true);
    
    $accept_url = "https://dsp1830.shop/quote_accept.php?no={$quote_no}&token=" . generateQuoteToken($quote_no);
    
    $subject = "[두손기획인쇄] 요청하신 견적서입니다 (#{$quote_no})";
    $body = "
    <h2>견적서</h2>
    <p>견적번호: {$quote_no}</p>
    <p>유효기간: {$quote['valid_until']}까지</p>
    
    <h3>요청 사양</h3>
    <table>
        <tr><th>제품</th><td>{$quote['product_type']}</td></tr>
        <tr><th>사이즈</th><td>{$specs['size']}</td></tr>
        <tr><th>용지</th><td>{$specs['paper']}</td></tr>
        <tr><th>수량</th><td>{$specs['quantity']}</td></tr>
        <tr><th>후가공</th><td>{$specs['finishing']}</td></tr>
    </table>
    
    <h3>견적 금액</h3>
    <p style='font-size:24px; color:#d00;'>" . number_format($quote['quoted_price']) . "원</p>
    <p>(부가세 포함)</p>
    
    " . ($quote['admin_comment'] ? "<h3>안내사항</h3><p>{$quote['admin_comment']}</p>" : "") . "
    
    <p><a href='{$accept_url}' style='...'>견적 승인 및 주문하기</a></p>
    ";
    
    sendEmail($quote['email'], $subject, $body);
}
```

## 고객: 견적 승인 및 주문

### 견적 승인 페이지
```php
// quote_accept.php
$quote_no = $_GET['no'];
$token = $_GET['token'];

// 토큰 검증
if (!validateQuoteToken($quote_no, $token)) {
    die('잘못된 접근입니다.');
}

$quote = getQuoteInfo($quote_no);

// 유효기간 체크
if (strtotime($quote['valid_until']) < time()) {
    die('견적 유효기간이 만료되었습니다. 다시 견적 요청해주세요.');
}

// 이미 주문된 견적인지 체크
if ($quote['status'] === 'ordered') {
    die('이미 주문이 완료된 견적입니다.');
}
```

### 견적 승인 처리
```php
// quote_accept_process.php
$quote_no = $_POST['quote_no'];

// 견적 상태 변경
$sql = "UPDATE quotations SET status = 'accepted' WHERE quote_no = ?";
$pdo->prepare($sql)->execute([$quote_no]);

// 가상 상품으로 장바구니에 추가
$quote = getQuoteInfo($quote_no);
$specs = json_decode($quote['specifications'], true);

$sql = "INSERT INTO shop_temp (
    session_id, member_id, product_type, product_name,
    size, paper, quantity, quantity_display, price, quote_no, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$product_name = "견적상품 ({$quote['product_type']})";
$pdo->prepare($sql)->execute([
    session_id(),
    $quote['member_id'],
    'quotation',
    $product_name,
    $specs['size'],
    $specs['paper'],
    $specs['quantity'],
    $specs['quantity'],
    $quote['quoted_price'],
    $quote_no
]);

// 장바구니로 이동
header('Location: /mlangprintauto/shop/cart.php');
```

## 주문 완료 시 견적 상태 업데이트

```php
// 주문 완료 후 처리
function completeQuotationOrder($order_no) {
    global $pdo;
    
    // 이 주문에 포함된 견적 상품 찾기
    $sql = "SELECT quote_no FROM shop_temp WHERE order_session = ? AND quote_no IS NOT NULL";
    // 또는 orderformtree에서
    $sql = "SELECT DISTINCT t.quote_no 
            FROM orderformtree t 
            WHERE t.order_no = ? AND t.quote_no IS NOT NULL";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_no]);
    
    while ($row = $stmt->fetch()) {
        $pdo->prepare("UPDATE quotations SET status = 'ordered', ordered_at = NOW() WHERE quote_no = ?")
            ->execute([$row['quote_no']]);
    }
}
```

## 견적 유효기간 만료 처리 (크론)

```php
// cron/expire_quotations.php
// 매일 자정에 실행

$sql = "UPDATE quotations 
        SET status = 'expired' 
        WHERE status = 'quoted' 
          AND valid_until < CURDATE()";
$pdo->exec($sql);

// 로그
$affected = $pdo->rowCount();
error_log("Expired $affected quotations");
```
