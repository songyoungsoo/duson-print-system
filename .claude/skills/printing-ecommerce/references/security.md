# 보안 가이드

## 1. SQL Injection 방지

### ❌ 잘못된 예
```php
// 직접 변수 삽입 - 절대 금지!
$sql = "SELECT * FROM members WHERE user_id = '$user_id'";
$sql = "SELECT * FROM members WHERE user_id = " . $_GET['id'];
```

### ✅ 올바른 예 - Prepared Statement
```php
// PDO 사용
$sql = "SELECT * FROM members WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

// Named Parameter
$sql = "SELECT * FROM members WHERE user_id = :user_id AND status = :status";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id, ':status' => 'active']);

// MySQLi 사용
$stmt = $mysqli->prepare("SELECT * FROM members WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
```

### PDO 연결 설정
```php
// inc/dbcon.php
$dsn = "mysql:host=localhost;dbname=dsp1830;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,  // 실제 prepared statement 사용
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    die("서버 오류가 발생했습니다.");
}
```

## 2. XSS (Cross-Site Scripting) 방지

### 출력 시 이스케이프
```php
// HTML 출력
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// 헬퍼 함수
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// 사용
<p>이름: <?= h($user['name']) ?></p>
<input value="<?= h($search_keyword) ?>">
```

### JavaScript 내 출력
```php
// JSON으로 안전하게 전달
<script>
const userData = <?= json_encode($user, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
```

### 허용된 HTML만 통과 (게시판 등)
```php
// HTMLPurifier 사용
require_once 'vendor/htmlpurifier/library/HTMLPurifier.auto.php';

$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.Allowed', 'p,b,i,u,a[href],img[src|alt],br,ul,ol,li');
$purifier = new HTMLPurifier($config);

$clean_html = $purifier->purify($dirty_html);
```

## 3. CSRF (Cross-Site Request Forgery) 방지

### 토큰 생성 및 검증
```php
// 세션에 토큰 저장
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 토큰 검증
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}
```

### 폼에 토큰 포함
```html
<form method="POST" action="process.php">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <!-- 다른 필드들 -->
    <button type="submit">제출</button>
</form>
```

### 처리 시 검증
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die('잘못된 요청입니다.');
    }
    // 정상 처리
}
```

## 4. 파일 업로드 보안

### 확장자 검증
```php
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd'];
$file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_extensions)) {
    die('허용되지 않는 파일 형식입니다.');
}
```

### MIME 타입 검증
```php
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['file']['tmp_name']);

$allowed_mimes = [
    'image/jpeg', 'image/png', 'image/gif',
    'application/pdf', 'application/postscript'
];

if (!in_array($mime, $allowed_mimes)) {
    die('허용되지 않는 파일 형식입니다.');
}
```

### 안전한 파일명 생성
```php
// 원본 파일명 사용 금지
$safe_filename = uniqid() . '_' . time() . '.' . $file_ext;

// 또는 해시 사용
$safe_filename = md5(uniqid(mt_rand(), true)) . '.' . $file_ext;
```

### 업로드 폴더 보안 (.htaccess)
```apache
# uploads/.htaccess
# PHP 실행 방지
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# 또는 모든 스크립트 실행 방지
Options -ExecCGI
AddHandler cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi
```

## 5. 세션 보안

### 세션 설정
```php
// 세션 시작 전 설정
ini_set('session.cookie_httponly', 1);      // JavaScript 접근 차단
ini_set('session.cookie_secure', 1);        // HTTPS에서만 전송
ini_set('session.cookie_samesite', 'Lax');  // CSRF 방지
ini_set('session.use_strict_mode', 1);      // 세션 고정 방지
ini_set('session.gc_maxlifetime', 3600);    // 1시간

session_start();
```

### 로그인 시 세션 재생성
```php
// 로그인 성공 후
session_regenerate_id(true);
$_SESSION['member_id'] = $member['idx'];
$_SESSION['last_activity'] = time();
```

### 세션 타임아웃 체크
```php
$timeout = 3600; // 1시간

if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > $timeout)) {
    session_destroy();
    header('Location: /member/login.php?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();
```

## 6. 비밀번호 보안

### 해시 저장
```php
// 저장 시
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 검증 시
if (password_verify($input_password, $stored_hash)) {
    // 로그인 성공
}

// 해시 갱신 필요 여부 체크 (알고리즘 업그레이드 시)
if (password_needs_rehash($stored_hash, PASSWORD_DEFAULT)) {
    $new_hash = password_hash($input_password, PASSWORD_DEFAULT);
    // DB 업데이트
}
```

### 비밀번호 정책
```php
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = '비밀번호는 8자 이상이어야 합니다.';
    }
    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = '영문자를 포함해야 합니다.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = '숫자를 포함해야 합니다.';
    }
    
    return $errors;
}
```

## 7. 입력값 검증

### 공통 검증 함수
```php
// 이메일
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// 전화번호
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^01[0-9]{8,9}$/', $phone);
}

// 숫자만
function validateNumeric($value) {
    return is_numeric($value) && $value >= 0;
}

// 한글 이름
function validateKoreanName($name) {
    return preg_match('/^[가-힣]{2,10}$/', $name);
}
```

### 화이트리스트 검증
```php
// 상태값 검증
$allowed_statuses = ['pending', 'paid', 'printing', 'shipping', 'completed'];
if (!in_array($status, $allowed_statuses)) {
    die('잘못된 상태값입니다.');
}

// 정렬 필드 검증
$allowed_sort = ['created_at', 'price', 'name'];
$sort = in_array($_GET['sort'] ?? '', $allowed_sort) ? $_GET['sort'] : 'created_at';
```

## 8. HTTP 보안 헤더

### .htaccess 또는 PHP
```php
// 보안 헤더 설정
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline'");
header("Referrer-Policy: strict-origin-when-cross-origin");
```

## 9. 에러 처리

### 운영 환경 설정
```php
// php.ini 또는 .htaccess
// 운영 환경
display_errors = Off
log_errors = On
error_log = /path/to/error.log

// 개발 환경
display_errors = On
```

### 사용자 정의 에러 처리
```php
set_exception_handler(function($e) {
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    
    if (defined('DEV_MODE') && DEV_MODE) {
        echo "<pre>" . $e . "</pre>";
    } else {
        include 'error_page.php';
    }
});
```

## 10. 관리자 페이지 보안

### IP 제한
```php
// admin/inc/auth.php
$allowed_ips = ['123.456.789.0', '111.222.333.444'];

if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    // 또는 로그인 추가 인증 요구
    http_response_code(403);
    die('접근이 거부되었습니다.');
}
```

### 로그인 시도 제한
```php
function checkLoginAttempts($user_id) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) FROM login_attempts 
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
    $count = $pdo->prepare($sql)->execute([$user_id])->fetchColumn();
    
    if ($count >= 5) {
        return false; // 30분간 차단
    }
    return true;
}

function recordLoginAttempt($user_id, $success) {
    global $pdo;
    $sql = "INSERT INTO login_attempts (user_id, ip_address, success, created_at) 
            VALUES (?, ?, ?, NOW())";
    $pdo->prepare($sql)->execute([$user_id, $_SERVER['REMOTE_ADDR'], $success]);
}
```

## 보안 체크리스트

- [ ] 모든 DB 쿼리에 Prepared Statement 사용
- [ ] 모든 출력에 htmlspecialchars() 적용
- [ ] 폼에 CSRF 토큰 적용
- [ ] 파일 업로드 확장자/MIME 검증
- [ ] 비밀번호 password_hash() 사용
- [ ] 세션 설정 보안 옵션 적용
- [ ] 관리자 페이지 접근 제한
- [ ] 에러 메시지 사용자에게 노출 금지
- [ ] HTTPS 적용
- [ ] 보안 헤더 설정
