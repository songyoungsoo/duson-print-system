# 에러 처리 / 로깅 시스템

## PHP 에러 설정

### 개발 환경 (php.ini 또는 .htaccess)
```ini
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /path/to/logs/php_error.log
```

### 운영 환경
```ini
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED
log_errors = On
error_log = /path/to/logs/php_error.log
```

### PHP 코드에서 설정
```php
// inc/config.php
define('DEV_MODE', false);  // 운영: false, 개발: true

if (DEV_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
```

## 사용자 정의 에러 핸들러

### 전역 예외 처리 (inc/error_handler.php)
```php
<?php
// 예외 처리
set_exception_handler(function($e) {
    logError('EXCEPTION', $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    showErrorPage(500, '서버 오류가 발생했습니다.');
});

// PHP 에러 처리
set_error_handler(function($severity, $message, $file, $line) {
    // Notice, Deprecated는 로그만
    if ($severity === E_NOTICE || $severity === E_DEPRECATED) {
        logError('NOTICE', $message, compact('file', 'line'));
        return true;
    }
    
    // 나머지 에러
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Fatal 에러 처리
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        logError('FATAL', $error['message'], [
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        showErrorPage(500, '서버 오류가 발생했습니다.');
    }
});

// 에러 페이지 표시
function showErrorPage($code, $message) {
    if (!headers_sent()) {
        http_response_code($code);
    }
    
    // 개발 모드면 상세 정보
    if (defined('DEV_MODE') && DEV_MODE) {
        echo "<pre>Error $code: $message</pre>";
        return;
    }
    
    // 운영 모드면 에러 페이지
    $error_file = __DIR__ . "/../errors/{$code}.html";
    if (file_exists($error_file)) {
        include $error_file;
    } else {
        echo "<h1>오류가 발생했습니다</h1><p>$message</p>";
    }
    exit;
}
```

## 로깅 시스템

### 로그 함수 (inc/logger.php)
```php
<?php
define('LOG_PATH', __DIR__ . '/../logs/');

/**
 * 로그 기록
 * @param string $level ERROR, WARNING, INFO, DEBUG
 * @param string $message 메시지
 * @param array $context 추가 데이터
 * @param string $channel 로그 채널 (app, payment, order...)
 */
function logError($level, $message, $context = [], $channel = 'app') {
    $log_file = LOG_PATH . $channel . '_' . date('Y-m-d') . '.log';
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
        'url' => $_SERVER['REQUEST_URI'] ?? '',
        'user_id' => $_SESSION['member_id'] ?? 0,
    ];
    
    $line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    
    // 디렉토리 생성
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
}

// 편의 함수
function logInfo($message, $context = [], $channel = 'app') {
    logError('INFO', $message, $context, $channel);
}

function logWarning($message, $context = [], $channel = 'app') {
    logError('WARNING', $message, $context, $channel);
}

function logDebug($message, $context = [], $channel = 'app') {
    if (defined('DEV_MODE') && DEV_MODE) {
        logError('DEBUG', $message, $context, $channel);
    }
}
```

### 로그 테이블 (DB 로깅)
```sql
CREATE TABLE system_logs (
    idx BIGINT AUTO_INCREMENT PRIMARY KEY,
    channel VARCHAR(50) NOT NULL,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    context JSON,
    ip_address VARCHAR(45),
    url VARCHAR(500),
    user_id INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_channel_level (channel, level),
    INDEX idx_created (created_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

```php
function logToDatabase($level, $message, $context = [], $channel = 'app') {
    global $pdo;
    
    try {
        $sql = "INSERT INTO system_logs (channel, level, message, context, ip_address, url, user_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $pdo->prepare($sql)->execute([
            $channel,
            $level,
            $message,
            json_encode($context, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['REQUEST_URI'] ?? '',
            $_SESSION['member_id'] ?? 0
        ]);
    } catch (Exception $e) {
        // DB 로깅 실패 시 파일 로깅
        logError('ERROR', $e->getMessage(), [], 'db_error');
    }
}
```

## 활동 로그 (Audit Log)

### 테이블
```sql
CREATE TABLE activity_logs (
    idx BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('member', 'admin') NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,        -- login, logout, order_create, etc.
    target_type VARCHAR(50),             -- order, member, product...
    target_id VARCHAR(50),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_type, user_id),
    INDEX idx_action (action),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created (created_at)
);
```

### 활동 로그 함수
```php
function logActivity($action, $target_type = null, $target_id = null, $description = null) {
    global $pdo;
    
    $user_type = isset($_SESSION['admin_id']) ? 'admin' : 'member';
    $user_id = $_SESSION['admin_id'] ?? $_SESSION['member_id'] ?? 0;
    
    $sql = "INSERT INTO activity_logs (user_type, user_id, action, target_type, target_id, description, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $pdo->prepare($sql)->execute([
        $user_type,
        $user_id,
        $action,
        $target_type,
        $target_id,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

// 사용 예시
logActivity('login', 'member', $member_id, '로그인 성공');
logActivity('order_create', 'order', $order_no, "주문 생성: {$total_price}원");
logActivity('status_change', 'order', $order_no, "상태 변경: {$old_status} → {$new_status}");
logActivity('member_block', 'member', $target_member_id, "회원 차단 처리");
```

## 에러 페이지

### 404 Not Found (errors/404.html)
```html
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>페이지를 찾을 수 없습니다 - 두손기획인쇄</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; text-align: center; padding: 100px 20px; }
        h1 { font-size: 120px; margin: 0; color: #ddd; }
        h2 { margin: 20px 0; color: #333; }
        p { color: #666; }
        a { color: #d00; }
    </style>
</head>
<body>
    <h1>404</h1>
    <h2>페이지를 찾을 수 없습니다</h2>
    <p>요청하신 페이지가 존재하지 않거나 이동되었을 수 있습니다.</p>
    <p><a href="/">메인으로 돌아가기</a></p>
</body>
</html>
```

### 500 Server Error (errors/500.html)
```html
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>서버 오류 - 두손기획인쇄</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; text-align: center; padding: 100px 20px; }
        h1 { font-size: 80px; margin: 0; color: #d00; }
        h2 { margin: 20px 0; color: #333; }
        p { color: #666; }
    </style>
</head>
<body>
    <h1>500</h1>
    <h2>서버 오류가 발생했습니다</h2>
    <p>잠시 후 다시 시도해주세요.</p>
    <p>문제가 지속되면 고객센터(02-0000-0000)로 연락해주세요.</p>
    <p><a href="/">메인으로 돌아가기</a></p>
</body>
</html>
```

### .htaccess 에러 페이지 설정
```apache
ErrorDocument 400 /errors/400.html
ErrorDocument 403 /errors/403.html
ErrorDocument 404 /errors/404.html
ErrorDocument 500 /errors/500.html
ErrorDocument 503 /errors/503.html
```

## AJAX 에러 처리

### API 응답 형식
```php
// api/response.php
function apiSuccess($data = null, $message = 'Success') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function apiError($message, $code = 400, $errors = []) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 사용
try {
    // 처리...
    apiSuccess(['order_no' => $order_no], '주문이 완료되었습니다.');
} catch (Exception $e) {
    logError('ERROR', $e->getMessage());
    apiError('처리 중 오류가 발생했습니다.', 500);
}
```

### JavaScript 에러 처리
```javascript
async function apiCall(url, data) {
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        alert(error.message || '오류가 발생했습니다. 다시 시도해주세요.');
        throw error;
    }
}
```

## 로그 관리

### 로그 로테이션 (크론)
```php
// cron/rotate_logs.php
// 매일 자정 실행: 0 0 * * * php /path/to/rotate_logs.php

$log_path = '/path/to/logs/';
$retention_days = 30;

// 오래된 로그 삭제
$files = glob($log_path . '*.log');
$cutoff = strtotime("-{$retention_days} days");

foreach ($files as $file) {
    if (filemtime($file) < $cutoff) {
        unlink($file);
        echo "Deleted: $file\n";
    }
}

// DB 로그 정리
$pdo->exec("DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL {$retention_days} DAY)");
$pdo->exec("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
```

### 로그 뷰어 (관리자)
```php
// admin/logs/index.php
$channel = $_GET['channel'] ?? 'app';
$date = $_GET['date'] ?? date('Y-m-d');
$level = $_GET['level'] ?? '';

$log_file = LOG_PATH . $channel . '_' . $date . '.log';

$logs = [];
if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES);
    foreach (array_reverse($lines) as $line) {
        $log = json_decode($line, true);
        if ($log && (!$level || $log['level'] === $level)) {
            $logs[] = $log;
        }
    }
}
```

## 알림 시스템

### 중요 에러 알림
```php
function notifyCriticalError($message, $context) {
    // 이메일 알림
    $admin_email = 'admin@dsp1830.shop';
    $subject = '[긴급] 서버 오류 발생 - dsp1830.shop';
    $body = "시간: " . date('Y-m-d H:i:s') . "\n"
          . "메시지: $message\n"
          . "상세: " . print_r($context, true);
    
    mail($admin_email, $subject, $body);
    
    // 또는 Slack 웹훅
    // $slack_webhook = 'https://hooks.slack.com/...';
    // file_get_contents($slack_webhook, false, stream_context_create([...]))
}
```
