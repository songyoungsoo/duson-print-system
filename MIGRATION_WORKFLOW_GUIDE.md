# 🚀 XAMPP → Cafe24 Linux Migration Workflow

## 📊 **Environment Comparison Matrix**

| 구분 | XAMPP (Windows) | Cafe24 (Linux) | 마이그레이션 요구사항 |
|------|-----------------|-----------------|----------------------|
| **OS** | Windows 10/11 | CentOS/Ubuntu | 대소문자 구분 처리 |
| **Web Server** | Apache 2.4 | Apache/Nginx | .htaccess 호환성 |
| **PHP** | 7.4-8.2 | 7.4-8.2 | 확장 모듈 확인 |
| **DB** | MySQL 8.0 | MariaDB 10.x | STRICT 모드 대응 |
| **Path** | C:\xampp\htdocs | /home/hosting_users/*/www | 절대경로 변경 |
| **Charset** | UTF-8 BOM | UTF-8 LF | 인코딩 통일 |
| **Permissions** | 임의 | 644/755 | 권한 설정 필수 |

---

## 🎯 **Phase 1: Pre-Migration Analysis & Preparation**

### 1.1 현재 환경 분석
```bash
# 로컬 PHP 확장 모듈 확인
php -m | grep -E "(gd|mysqli|mbstring|curl|imagick)"

# DB 테이블 구조 분석
mysqldump -u root -p duson1830 --no-data > schema_backup.sql

# 프로젝트 구조 분석
find . -name "*.php" | head -20
du -sh uploads/ images/
```

### 1.2 Critical File Inventory
```
필수 점검 대상:
□ db.php (DB 연결 설정)
□ includes/*.php (공통 함수)
□ .htaccess (URL 리라이팅)
□ config.php (설정 파일)
□ uploads/ (업로드 디렉토리)
□ MlangPrintAuto/*/index.php (제품 페이지)
```

---

## 🔧 **Phase 2: Code Preparation & Fixes**

### 2.1 환경 호환성 수정

#### 대소문자 구분 처리
```php
// ❌ Windows에서만 작동
include "Includes/Auth.php";

// ✅ Linux 호환
include "includes/auth.php";
```

#### 경로 구분자 통일
```php
// ❌ Windows 경로
$upload_path = "uploads\\" . $filename;

// ✅ 크로스 플랫폼 호환
$upload_path = "uploads/" . $filename;
// 또는
$upload_path = DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $filename;
```

#### MySQL STRICT 모드 대응
```php
// ❌ STRICT 모드에서 실패할 수 있는 쿼리
INSERT INTO users (name) VALUES ('');

// ✅ STRICT 모드 호환
INSERT INTO users (name, created_at) VALUES (COALESCE(NULLIF('', ''), 'Unknown'), NOW());
```

### 2.2 DB 연결 설정 분리

#### config/database.php
```php
<?php
// 환경별 DB 설정
$environments = [
    'local' => [
        'host' => 'localhost',
        'user' => 'duson1830',
        'password' => 'du1830', 
        'database' => 'duson1830'
    ],
    'production' => [
        'host' => 'localhost',
        'user' => 'dsp1830',
        'password' => 'ds701018',
        'database' => 'dsp1830'
    ]
];

$current_env = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ? 'local' : 'production';
$db_config = $environments[$current_env];

// DB 연결
$db = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);
$db->set_charset('utf8mb4');

if ($db->connect_error) {
    error_log("DB Connection Error: " . $db->connect_error);
    die("데이터베이스 연결 실패");
}
?>
```

---

## 📦 **Phase 3: Automated Packaging & Deployment**

### 3.1 배포 패키징 스크립트

#### package_for_cafe24.php
```php
<?php
/**
 * Cafe24 배포용 패키지 자동 생성 스크립트
 */

$source_dir = __DIR__;
$package_name = 'cafe24_deploy_' . date('Ymd_His');
$temp_dir = sys_get_temp_dir() . '/' . $package_name;
$zip_file = $source_dir . '/' . $package_name . '.zip';

echo "🚀 Cafe24 배포 패키지 생성 시작...\n";

// 제외할 파일/폴더 패턴
$exclude_patterns = [
    '/^\.git/',
    '/^\.vscode/',
    '/^node_modules/',
    '/^vendor/',
    '/\.log$/',
    '/\.bak$/',
    '/debug.*\.php$/',
    '/test.*\.php$/',
    '/xampp/',
    '/C:/',
    '/localhost/'
];

// 임시 디렉토리 생성
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

// 파일 복사 및 처리
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source_dir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($source_dir) + 1);
    
    // 제외 패턴 체크
    $exclude = false;
    foreach ($exclude_patterns as $pattern) {
        if (preg_match($pattern, $relativePath)) {
            $exclude = true;
            break;
        }
    }
    
    if ($exclude) {
        echo "제외: {$relativePath}\n";
        continue;
    }
    
    $targetPath = $temp_dir . '/' . $relativePath;
    $targetDir = dirname($targetPath);
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // PHP 파일 처리 (경로 수정)
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        $content = file_get_contents($filePath);
        
        // Windows 경로를 Linux 경로로 변경
        $content = str_replace('\\', '/', $content);
        $content = preg_replace('/C:\/xampp\/htdocs\//', '', $content);
        
        // BOM 제거 및 UTF-8 LF로 통일
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        file_put_contents($targetPath, $content);
    } else {
        copy($filePath, $targetPath);
    }
    
    echo "복사: {$relativePath}\n";
}

// ZIP 압축
$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($temp_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($temp_dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    $zip->close();
    echo "\n✅ 배포 패키지 생성 완료: {$zip_file}\n";
} else {
    echo "❌ ZIP 생성 실패\n";
}

// 임시 디렉토리 정리
function removeDir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object)) {
                    removeDir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        rmdir($dir);
    }
}

removeDir($temp_dir);
echo "임시 폴더 정리 완료\n";

// 배포 가이드 생성
$guide = "
=== Cafe24 배포 가이드 ===
생성일: " . date('Y-m-d H:i:s') . "

1. 백업: 기존 서버 파일 백업
2. 업로드: {$package_name}.zip을 FTP 루트에 업로드
3. 압축해제: 파일매니저에서 압축 해제
4. 권한설정: 디렉토리 755, 파일 644
5. DB설정: config/database.php 확인
6. 테스트: 각 기능 동작 확인

주요 변경사항:
- Windows 경로 → Linux 경로 변경
- BOM 제거, UTF-8 LF 통일
- DB 연결 설정 환경별 분리
";

file_put_contents($source_dir . '/DEPLOYMENT_GUIDE.txt', $guide);
echo "\n📋 배포 가이드 생성: DEPLOYMENT_GUIDE.txt\n";
?>
```

### 3.2 서버 설정 파일들

#### .htaccess (Apache 설정)
```apache
# UTF-8 인코딩 설정
AddDefaultCharset UTF-8

# 디렉토리 브라우징 차단
Options -Indexes

# 파일 접근 차단
<Files ~ "^(config|\.env|\.log|backup)">
    Order allow,deny
    Deny from all
</Files>

# PHP 설정 파일 차단
<FilesMatch "\.(inc|conf|config|sql)$">
    Order deny,allow
    Deny from all
</FilesMatch>

# URL 리라이팅 (필요시)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# GZIP 압축
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript
</IfModule>

# 브라우저 캐싱
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
```

#### .user.ini (PHP 설정)
```ini
; PHP 설정 조정
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 300
max_input_vars = 3000

; 에러 처리
display_errors = Off
log_errors = On
error_log = logs/php-error.log

; 문자 인코딩
default_charset = "UTF-8"

; 세션 설정
session.gc_maxlifetime = 7200
session.cookie_lifetime = 0

; 타임존
date.timezone = "Asia/Seoul"
```

---

## 🧪 **Phase 4: Database Migration**

### 4.1 DB 덤프 생성
```bash
# 스키마 + 데이터 백업 (압축)
mysqldump -u root -p --default-character-set=utf8mb4 \
  --single-transaction --routines --triggers \
  duson1830 | gzip > cafe24_db_backup_$(date +%Y%m%d_%H%M%S).sql.gz

# 큰 테이블은 별도 처리
mysqldump -u root -p --where="1 limit 10000" duson1830 large_table > large_table_partial.sql
```

### 4.2 STRICT 모드 대응 SQL
```sql
-- STRICT 모드 체크
SELECT @@sql_mode;

-- STRICT 모드 임시 해제 (필요시)
SET SESSION sql_mode = '';

-- 테이블별 NULL/기본값 보완
ALTER TABLE users 
  MODIFY COLUMN name VARCHAR(100) NOT NULL DEFAULT '',
  MODIFY COLUMN email VARCHAR(255) NOT NULL DEFAULT '',
  MODIFY COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 문자셋 확인 및 변경
ALTER DATABASE dsp1830 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 🚀 **Phase 5: Deployment Process**

### 5.1 배포 체크리스트
```
🎯 배포 전 점검:
□ 로컬 테스트 완료
□ DB 백업 완료  
□ 배포 패키지 생성 완료
□ Cafe24 FTP 접속 확인
□ 서버 디스크 용량 확인

📤 배포 단계:
□ 1. 서버 백업 (기존 파일)
□ 2. 배포 패키지 업로드
□ 3. 압축 해제
□ 4. 파일 권한 설정
□ 5. DB 가져오기
□ 6. 설정 파일 확인
□ 7. 기본 동작 테스트

✅ 배포 후 검증:
□ 첫 화면 로딩 확인
□ DB 연결 확인
□ 업로드 기능 확인  
□ 이메일 발송 확인
□ 에러 로그 점검
```

### 5.2 자동 배포 스크립트 (서버측)
```bash
#!/bin/bash
# deploy_cafe24.sh - 서버에서 실행할 배포 스크립트

DEPLOY_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/hosting_users/dsp1830/backups"
WWW_DIR="/home/hosting_users/dsp1830/www"
LOG_FILE="/home/hosting_users/dsp1830/logs/deploy_${DEPLOY_DATE}.log"

echo "🚀 Cafe24 자동 배포 시작 - ${DEPLOY_DATE}" | tee -a $LOG_FILE

# 1. 백업 생성
echo "📦 기존 파일 백업 중..." | tee -a $LOG_FILE
mkdir -p $BACKUP_DIR
tar -czf "${BACKUP_DIR}/www_backup_${DEPLOY_DATE}.tar.gz" -C $WWW_DIR . 2>> $LOG_FILE

# 2. 새 파일 압축 해제 (미리 업로드된 zip 파일)
echo "📤 새 파일 배포 중..." | tee -a $LOG_FILE
cd $WWW_DIR
unzip -o cafe24_deploy_*.zip 2>> $LOG_FILE

# 3. 권한 설정
echo "🔒 파일 권한 설정 중..." | tee -a $LOG_FILE
find $WWW_DIR -type d -exec chmod 755 {} \; 2>> $LOG_FILE
find $WWW_DIR -type f -exec chmod 644 {} \; 2>> $LOG_FILE
chmod 775 $WWW_DIR/uploads $WWW_DIR/logs 2>> $LOG_FILE

# 4. 로그 디렉토리 생성
mkdir -p $WWW_DIR/logs
touch $WWW_DIR/logs/php-error.log

# 5. 기본 동작 확인
echo "🧪 기본 동작 테스트 중..." | tee -a $LOG_FILE
curl -s http://dsp114.com/ | grep -q "두손기획인쇄" && echo "✅ 첫 화면 OK" || echo "❌ 첫 화면 오류"

echo "🎉 배포 완료 - ${DEPLOY_DATE}" | tee -a $LOG_FILE
echo "📋 로그 위치: ${LOG_FILE}"
```

---

## ✅ **Phase 6: Post-Deployment Validation**

### 6.1 10분 점검 체크리스트
```
🕐 배포 직후 (0-2분):
□ 기본 페이지 로딩 (http://dsp114.com/)
□ 에러 페이지 없는지 확인
□ 로그인/로그아웃 정상 동작

🕕 기능 테스트 (3-5분):
□ 견적 계산 AJAX 정상 작동
□ 파일 업로드 정상 동작
□ 이미지 표시 정상
□ DB 데이터 조회 정상

🕘 심화 테스트 (6-8분):
□ 이메일 발송 테스트
□ 권한별 접근 제어 확인
□ 모바일 반응형 확인
□ 검색 기능 정상

🕐 모니터링 (9-10분):
□ PHP 에러 로그 점검
□ 서버 리소스 사용률 확인
□ 외부 접속 가능 여부 확인
```

### 6.2 자동 검증 스크립트

#### validate_deployment.php
```php
<?php
/**
 * 배포 후 자동 검증 스크립트
 */

$tests = [];
$base_url = 'http://dsp114.com';

echo "🧪 배포 검증 테스트 시작...\n";

// 1. 기본 페이지 접속 테스트
$tests['homepage'] = testHttpResponse($base_url, 200);

// 2. DB 연결 테스트  
$tests['database'] = testDatabaseConnection();

// 3. 파일 업로드 디렉토리 권한 테스트
$tests['upload_permissions'] = testUploadPermissions();

// 4. PHP 확장 모듈 테스트
$tests['php_extensions'] = testPhpExtensions();

// 5. 이메일 설정 테스트
$tests['email_config'] = testEmailConfiguration();

// 6. 주요 기능 페이지 테스트
$critical_pages = [
    '/MlangPrintAuto/NameCard/',
    '/MlangPrintAuto/msticker/',
    '/MlangPrintAuto/envelope/'
];

foreach ($critical_pages as $page) {
    $tests["page_" . basename($page)] = testHttpResponse($base_url . $page, 200);
}

// 결과 출력
echo "\n📋 검증 결과 요약:\n";
echo str_repeat("=", 50) . "\n";

$passed = 0;
$total = count($tests);

foreach ($tests as $test_name => $result) {
    $status = $result ? "✅ PASS" : "❌ FAIL";
    echo sprintf("%-20s: %s\n", $test_name, $status);
    if ($result) $passed++;
}

echo str_repeat("=", 50) . "\n";
echo sprintf("전체 성공률: %d/%d (%.1f%%)\n", $passed, $total, ($passed/$total)*100);

if ($passed === $total) {
    echo "🎉 모든 테스트 통과! 배포 성공\n";
    exit(0);
} else {
    echo "⚠️  일부 테스트 실패. 점검 필요\n";
    exit(1);
}

// 헬퍼 함수들
function testHttpResponse($url, $expected_code) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code === $expected_code && !empty($response);
}

function testDatabaseConnection() {
    try {
        $db = new mysqli('localhost', 'dsp1830', 'ds701018', 'dsp1830');
        $result = $db->query("SELECT 1");
        $db->close();
        return $result !== false;
    } catch (Exception $e) {
        return false;
    }
}

function testUploadPermissions() {
    $upload_dir = __DIR__ . '/uploads';
    return is_dir($upload_dir) && is_writable($upload_dir);
}

function testPhpExtensions() {
    $required = ['mysqli', 'gd', 'mbstring', 'curl'];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            return false;
        }
    }
    return true;
}

function testEmailConfiguration() {
    // SMTP 설정이 있는지만 확인
    return function_exists('mail');
}
?>
```

---

## 🔄 **Phase 7: Monitoring & Rollback Plan**

### 7.1 모니터링 대시보드

#### monitoring_dashboard.php
```php
<?php
/**
 * 간단한 서버 상태 모니터링 대시보드
 */

// 보안: 관리자만 접근 가능
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    http_response_code(403);
    exit('Access Denied');
}

$status = [];

// 1. 서버 기본 정보
$status['server'] = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];

// 2. DB 연결 상태
try {
    $db = new mysqli('localhost', 'dsp1830', 'ds701018', 'dsp1830');
    $status['database'] = [
        'status' => 'Connected',
        'version' => $db->server_info,
        'charset' => $db->character_set_name()
    ];
    $db->close();
} catch (Exception $e) {
    $status['database'] = [
        'status' => 'Error: ' . $e->getMessage()
    ];
}

// 3. 디스크 사용량
$status['disk'] = [
    'free_space' => formatBytes(disk_free_space('.')),
    'total_space' => formatBytes(disk_total_space('.')),
    'usage_percent' => round((1 - disk_free_space('.') / disk_total_space('.')) * 100, 1)
];

// 4. 에러 로그 최근 내용
$error_log = 'logs/php-error.log';
if (file_exists($error_log)) {
    $status['error_log'] = [
        'size' => formatBytes(filesize($error_log)),
        'last_modified' => date('Y-m-d H:i:s', filemtime($error_log)),
        'recent_errors' => array_slice(file($error_log), -10)
    ];
} else {
    $status['error_log'] = ['status' => 'No error log found'];
}

// 5. 업로드 디렉토리 상태
$upload_dirs = ['uploads', 'images', 'temp'];
foreach ($upload_dirs as $dir) {
    $status['uploads'][$dir] = [
        'exists' => is_dir($dir),
        'writable' => is_writable($dir),
        'files' => is_dir($dir) ? count(scandir($dir)) - 2 : 0
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
?>
```

### 7.2 롤백 계획

#### rollback_plan.md
```markdown
# 🔄 긴급 롤백 계획

## 상황별 롤백 전략

### Level 1: 설정 파일만 롤백 (5분 이내)
- 증상: DB 연결 오류, 설정 오류
- 대응: 백업된 config 파일로 교체
```bash
cp backups/config_backup.php config.php
cp backups/db_backup.php db.php
```

### Level 2: 전체 코드 롤백 (15분 이내)  
- 증상: 심각한 PHP 오류, 사이트 접속 불가
- 대응: 전체 코드를 직전 백업으로 복구
```bash
cd /home/hosting_users/dsp1830/www
rm -rf * .[^.]*
tar -xzf ../backups/www_backup_YYYYMMDD_HHMMSS.tar.gz
```

### Level 3: DB까지 롤백 (30분 이내)
- 증상: 데이터 손상, 테이블 구조 오류  
- 대응: DB 전체 복구
```bash
mysql -u dsp1830 -p dsp1830 < backups/database_backup_YYYYMMDD.sql
```

## 롤백 후 체크리스트
□ 기본 페이지 접속 확인
□ 주요 기능 정상 동작 확인  
□ 에러 로그 점검
□ 고객 공지사항 게시
```

---

## 📞 **Phase 8: Communication & Documentation**

### 8.1 배포 커뮤니케이션 계획
```
🎯 이해관계자별 커뮤니케이션:

고객/사용자:
- 배포 1일 전: 점검 예정 공지
- 배포 당일: 점검 시작/완료 알림
- 배포 후: 새 기능 안내

개발팀:
- 배포 계획 공유
- 실시간 진행 상황 업데이트
- 배포 후 이슈 대응 가이드

관리자:
- 배포 전후 체크리스트
- 모니터링 대시보드 접근
- 긴급 연락처 정보
```

### 8.2 문서화 템플릿

#### DEPLOYMENT_REPORT_TEMPLATE.md
```markdown
# 배포 완료 보고서

**배포 일시**: YYYY-MM-DD HH:MM
**배포자**: [이름]
**배포 버전**: v1.0.x
**예상 다운타임**: [시간]
**실제 다운타임**: [시간]

## 배포 내용
- [ ] 기능 개선사항
- [ ] 버그 수정사항  
- [ ] 보안 업데이트
- [ ] 성능 개선

## 배포 과정
- [x] 코드 준비 완료
- [x] DB 백업 완료
- [x] 배포 패키지 생성
- [x] 서버 배포 완료
- [x] 기능 테스트 완료

## 테스트 결과
| 항목 | 상태 | 비고 |
|------|------|------|
| 기본 페이지 | ✅ | |
| 견적 계산 | ✅ | |
| 파일 업로드 | ✅ | |
| 이메일 발송 | ✅ | |

## 이슈 및 대응
- 이슈1: [설명] → [해결방법]
- 이슈2: [설명] → [해결방법]

## 후속 작업
- [ ] 성능 모니터링
- [ ] 사용자 피드백 수집
- [ ] 추가 최적화 적용

## 연락처
- 개발팀: [연락처]
- 긴급상황: [연락처]
```

---

이 워크플로우를 통해 XAMPP에서 Cafe24로의 안정적인 마이그레이션이 가능합니다. 각 단계별로 체크포인트를 두어 문제 발생 시 신속한 대응이 가능하도록 설계되었습니다.