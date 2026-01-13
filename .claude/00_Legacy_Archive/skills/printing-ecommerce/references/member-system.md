# 회원 시스템

## 기능 목록

- 회원가입 / 로그인 / 로그아웃
- 비밀번호 찾기 / 변경
- 마이페이지 (주문내역, 배송조회)
- 회원정보 수정
- 배송지 관리

## 회원가입 (register.php)

### HTML 폼
```html
<form id="registerForm" action="register_process.php" method="POST">
    <input type="text" name="user_id" required pattern="[a-z0-9]{4,20}"
           placeholder="아이디 (영문소문자, 숫자 4~20자)">
    <button type="button" onclick="checkDuplicate()">중복확인</button>
    
    <input type="password" name="password" required minlength="8"
           placeholder="비밀번호 (8자 이상)">
    <input type="password" name="password_confirm" required
           placeholder="비밀번호 확인">
    
    <input type="text" name="name" required placeholder="이름">
    <input type="tel" name="mobile" required placeholder="휴대폰번호">
    <input type="email" name="email" placeholder="이메일">
    
    <!-- 약관 동의 -->
    <label>
        <input type="checkbox" name="agree_terms" required> 이용약관 동의 (필수)
    </label>
    <label>
        <input type="checkbox" name="agree_privacy" required> 개인정보처리방침 동의 (필수)
    </label>
    <label>
        <input type="checkbox" name="agree_marketing"> 마케팅 수신 동의 (선택)
    </label>
    
    <button type="submit">가입하기</button>
</form>
```

### 가입 처리 (register_process.php)
```php
// 유효성 검사
$user_id = trim($_POST['user_id']);
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];
$name = trim($_POST['name']);
$mobile = preg_replace('/[^0-9]/', '', $_POST['mobile']);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

// 검증
if (strlen($user_id) < 4 || !preg_match('/^[a-z0-9]+$/', $user_id)) {
    die('아이디는 영문소문자와 숫자 4~20자입니다.');
}
if ($password !== $password_confirm) {
    die('비밀번호가 일치하지 않습니다.');
}
if (strlen($password) < 8) {
    die('비밀번호는 8자 이상입니다.');
}

// 중복 확인
$stmt = $pdo->prepare("SELECT idx FROM members WHERE user_id = ?");
$stmt->execute([$user_id]);
if ($stmt->fetch()) {
    die('이미 사용중인 아이디입니다.');
}

// 비밀번호 해시
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 저장
$sql = "INSERT INTO members (user_id, password, name, mobile, email, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $hashed_password, $name, $mobile, $email]);

// 가입 완료 → 로그인 페이지로
header('Location: login.php?registered=1');
```

## 로그인 (login.php)

### HTML 폼
```html
<form id="loginForm" action="login_process.php" method="POST">
    <input type="text" name="user_id" required placeholder="아이디">
    <input type="password" name="password" required placeholder="비밀번호">
    
    <label>
        <input type="checkbox" name="remember_me"> 로그인 상태 유지
    </label>
    
    <button type="submit">로그인</button>
    
    <div class="links">
        <a href="find_id.php">아이디 찾기</a>
        <a href="find_password.php">비밀번호 찾기</a>
        <a href="register.php">회원가입</a>
    </div>
</form>
```

### 로그인 처리 (login_process.php)
```php
session_start();

$user_id = trim($_POST['user_id']);
$password = $_POST['password'];
$remember_me = isset($_POST['remember_me']);

// 회원 조회
$stmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$member = $stmt->fetch();

if (!$member || !password_verify($password, $member['password'])) {
    die('아이디 또는 비밀번호가 일치하지 않습니다.');
}

// 세션 저장
$_SESSION['member_id'] = $member['idx'];
$_SESSION['user_id'] = $member['user_id'];
$_SESSION['name'] = $member['name'];

// 마지막 로그인 시간 업데이트
$pdo->prepare("UPDATE members SET last_login = NOW() WHERE idx = ?")
    ->execute([$member['idx']]);

// 자동 로그인 쿠키
if ($remember_me) {
    $token = bin2hex(random_bytes(32));
    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    
    $pdo->prepare("UPDATE members SET remember_token = ? WHERE idx = ?")
        ->execute([$token, $member['idx']]);
}

// 장바구니 병합 (비회원 → 회원)
mergeGuestCart($pdo, session_id(), $member['idx']);

// 이전 페이지로 또는 메인으로
$redirect = $_SESSION['redirect_after_login'] ?? '/';
unset($_SESSION['redirect_after_login']);
header("Location: $redirect");
```

## 로그아웃 (logout.php)

```php
session_start();

// 자동 로그인 쿠키 삭제
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');

    if (isset($_SESSION['member_id'])) {
        $pdo->prepare("UPDATE members SET remember_token = NULL WHERE idx = ?")
            ->execute([$_SESSION['member_id']]);
    }
}

// 세션 파기
session_destroy();

header('Location: /');
```

## 세션 설정 (includes/auth.php)

### 세션 유지 시간
| 설정 | 값 | 설명 |
|------|-----|------|
| **세션 유효시간** | 8시간 (28800초) | 활동 중 자동 갱신 |
| **자동 로그인** | 30일 | "로그인 상태 유지" 체크 시 |
| **세션 쿠키** | 브라우저 닫아도 8시간 유지 | httponly, samesite=Lax |

### 핵심 설정 코드
```php
// includes/auth.php
$session_lifetime = 28800; // 8시간

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $session_lifetime);

    session_set_cookie_params([
        'lifetime' => $session_lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => false,  // HTTPS 사용 시 true
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

define('REMEMBER_ME_DAYS', 30);  // 자동 로그인 30일
```

### ⚠️ 중요: 모든 페이지에서 auth.php 사용 필수

```php
// ❌ 잘못된 방법 (PHP 기본 24분 적용)
session_start();

// ✅ 올바른 방법 (8시간 적용)
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
```

### 세션 만료 시 메시지
```php
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('세션이 만료되었습니다. 다시 로그인해주세요.'); location.href='/member/login.php';</script>";
    exit;
}
```

### 자동 로그인 테이블
```sql
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at)
);
```

### 적용된 페이지 목록
- `/mypage/index.php` - 대시보드
- `/mypage/profile.php` - 회원정보수정
- `/mypage/change_password.php` - 비밀번호변경
- `/mypage/business_certificate.php` - 사업자등록증
- `/mypage/tax_invoices.php` - 세금계산서
- `/mypage/transactions.php` - 거래내역
- `/mypage/view_invoice.php` - 계산서보기
- `/mypage/withdraw.php` - 회원탈퇴

## 마이페이지 (mypage.php)

### 주문 내역
```php
// 로그인 확인
if (!isset($_SESSION['member_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: /member/login.php');
    exit;
}

$member_id = $_SESSION['member_id'];

// 주문 목록
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM orderformtree WHERE order_no = o.order_no) as item_count,
        (SELECT product_name FROM orderformtree WHERE order_no = o.order_no LIMIT 1) as first_product
        FROM orderform o
        WHERE o.member_id = ?
        ORDER BY o.created_at DESC
        LIMIT 20";
$orders = $pdo->prepare($sql);
$orders->execute([$member_id]);
```

### 주문 상태별 카운트
```php
$sql = "SELECT status, COUNT(*) as cnt FROM orderform 
        WHERE member_id = ? GROUP BY status";
$statusCounts = $pdo->prepare($sql);
$statusCounts->execute([$member_id]);
```

### 마이페이지 UI
```html
<div class="mypage">
    <h2>마이페이지</h2>
    
    <!-- 주문 현황 요약 -->
    <div class="order-summary">
        <div class="status-box">
            <span class="count"><?= $counts['pending'] ?? 0 ?></span>
            <span class="label">입금대기</span>
        </div>
        <div class="status-box">
            <span class="count"><?= $counts['paid'] ?? 0 ?></span>
            <span class="label">결제완료</span>
        </div>
        <div class="status-box">
            <span class="count"><?= $counts['printing'] ?? 0 ?></span>
            <span class="label">인쇄중</span>
        </div>
        <div class="status-box">
            <span class="count"><?= $counts['shipping'] ?? 0 ?></span>
            <span class="label">배송중</span>
        </div>
    </div>
    
    <!-- 주문 목록 -->
    <table class="order-list">
        <tr>
            <th>주문번호</th>
            <th>상품</th>
            <th>금액</th>
            <th>상태</th>
            <th>주문일</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><a href="order_detail.php?no=<?= $order['order_no'] ?>"><?= $order['order_no'] ?></a></td>
            <td><?= $order['first_product'] ?> <?php if($order['item_count'] > 1): ?>외 <?= $order['item_count']-1 ?>건<?php endif; ?></td>
            <td><?= number_format($order['total_price']) ?>원</td>
            <td><span class="status-<?= $order['status'] ?>"><?= ORDER_STATUS[$order['status']] ?></span></td>
            <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
```

## 비밀번호 찾기

### 1. 이메일 인증 방식
```php
// find_password.php
$email = $_POST['email'];
$user_id = $_POST['user_id'];

$stmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ? AND email = ?");
$stmt->execute([$user_id, $email]);
$member = $stmt->fetch();

if ($member) {
    // 임시 토큰 생성
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $pdo->prepare("UPDATE members SET reset_token = ?, reset_expires = ? WHERE idx = ?")
        ->execute([$token, $expires, $member['idx']]);
    
    // 이메일 발송
    $reset_link = "https://dsp1830.shop/member/reset_password.php?token=$token";
    sendPasswordResetEmail($email, $reset_link);
}
```

### 2. 비밀번호 재설정
```php
// reset_password.php
$token = $_GET['token'];
$new_password = $_POST['new_password'];

$stmt = $pdo->prepare("SELECT * FROM members WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$member = $stmt->fetch();

if ($member) {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE members SET password = ?, reset_token = NULL WHERE idx = ?")
        ->execute([$hashed, $member['idx']]);
}
```

## 장바구니 병합

```php
function mergeGuestCart($pdo, $session_id, $member_id) {
    // 비회원 장바구니 → 회원에게 이전
    $sql = "UPDATE shop_temp SET member_id = ? WHERE session_id = ? AND member_id = 0";
    $pdo->prepare($sql)->execute([$member_id, $session_id]);
}
```
