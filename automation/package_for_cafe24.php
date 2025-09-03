<?php
/**
 * 🚀 Cafe24 자동 배포 패키징 스크립트
 * 
 * 기능:
 * - Windows 경로를 Linux 경로로 자동 변환
 * - PHP 파일 인코딩 통일 (UTF-8 LF)
 * - 불필요한 파일 제외
 * - 배포용 ZIP 패키지 생성
 * - 설정 파일 자동 생성
 */

echo "🚀 Cafe24 배포 패키지 자동 생성 시작...\n";
echo str_repeat("=", 60) . "\n";

// 1단계: 데이터베이스 백업 생성
echo "📊 1단계: 데이터베이스 백업 생성 중...\n";
$db_backup_file = dirname(__DIR__) . '/database_backup_for_cafe24.sql';
$mysql_path = 'C:\\xampp\\mysql\\bin\\mysqldump';
$db_command = "\"{$mysql_path}\" -u duson1830 -p\"du1830\" duson1830 > \"{$db_backup_file}\"";

// Windows 환경에서 mysqldump 실행
exec($db_command . ' 2>&1', $db_output, $db_return);

if ($db_return === 0 && file_exists($db_backup_file)) {
    $backup_size = round(filesize($db_backup_file) / 1024, 2);
    echo "✅ DB 백업 완료: database_backup_for_cafe24.sql ({$backup_size} KB)\n";
} else {
    echo "⚠️ DB 백업 실패, 수동으로 생성 필요\n";
    echo "수동 명령어: mysqldump -u duson1830 -p duson1830 > database_backup_for_cafe24.sql\n";
}

// 설정
$source_dir = dirname(__DIR__); // htdocs 상위 디렉토리
$package_name = 'cafe24_deploy_' . date('Ymd_His');
$temp_dir = sys_get_temp_dir() . '/' . $package_name;
$zip_file = $source_dir . '/deployment/' . $package_name . '.zip';

// 배포 디렉토리 생성
if (!is_dir(dirname($zip_file))) {
    mkdir(dirname($zip_file), 0755, true);
}

// 제외할 파일/폴더 패턴
$exclude_patterns = [
    '/^\.git/',
    '/^\.vscode/', 
    '/^\.kiro/',
    '/^\.claude/',
    '/^\.playwright-mcp/',
    '/^node_modules/',
    '/^vendor/',
    '/^temp/',
    '/^cache/',
    '/^backup/',
    '/^logs/',
    '/\.log$/',
    '/\.bak$/',
    '/\.backup$/',
    '/debug.*\.php$/',
    '/test.*\.php$/',
    '/fix_.*\.php$/',
    '/create_.*\.php$/',
    '/analyze_.*\.php$/',
    '/check_.*\.php$/',
    '/migration.*\.php$/',
    '/repair.*\.php$/',
    '/setup.*\.php$/',
    '/cafe24.*\.zip$/',
    '/MlangPrintAuto.*\.zip$/',
    '/\.sql$/',
    '/\.md$/',
    '/CLAUDE\.md$/',
    '/README/',
    '/docs/',
    '/archive/',
    '/SuperClaude/',
    '/automation/',
    '/deployment/'
];

// 필수 포함 파일/디렉토리
$include_patterns = [
    'index.php',
    'header.php',
    'footer.php',
    'left.php',
    'db.php',
    'css/',
    'js/',
    'images/',
    'includes/',
    'mlangprintauto/',
    'MlangOrder_PrintAuto/',
    'admin/mlangprintauto/',
    'shop/',
    'member/',
    'sub/',
    'api/',
    'PHPMailer/',
    'session/',
    'up/',
    'bbs/',
    'ImgFolder/',
    'components/',
    'auth/'
];

echo "📁 임시 디렉토리 생성: $temp_dir\n";
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

$file_count = 0;
$processed_files = 0;

// 파일 복사 및 처리
echo "📋 파일 처리 중...\n";

foreach ($include_patterns as $pattern) {
    $source_path = $source_dir . '/' . $pattern;
    
    if (is_file($source_path)) {
        // 단일 파일 처리
        processFile($source_path, $temp_dir . '/' . $pattern, $exclude_patterns);
        $file_count++;
    } elseif (is_dir($source_path)) {
        // 디렉토리 재귀 처리
        $result = processDirectory($source_path, $temp_dir . '/' . $pattern, $exclude_patterns);
        $file_count += $result;
    }
}

echo "\n📦 설정 파일 생성 중...\n";

// .htaccess 파일 생성
createHtaccessFile($temp_dir . '/.htaccess');

// .user.ini 파일 생성  
createUserIniFile($temp_dir . '/.user.ini');

// config/database.php 생성
createDatabaseConfig($temp_dir . '/config/database.php');

// 배포 가이드 생성
createDeploymentGuide($temp_dir . '/DEPLOYMENT_README.txt', $package_name);

// DB 백업 파일을 패키지에 포함
if (file_exists($db_backup_file)) {
    copy($db_backup_file, $temp_dir . '/database_backup_for_cafe24.sql');
    echo "✅ DB 백업 파일을 패키지에 포함했습니다.\n";
} else {
    echo "⚠️ DB 백업 파일이 없습니다. 수동으로 추가해주세요.\n";
}

echo "\n🗜️  ZIP 압축 중...\n";

// ZIP 파일 생성
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
            $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
            $processed_files++;
        }
    }
    
    $zip->close();
    
    echo "✅ ZIP 파일 생성 완료!\n";
    echo "📍 위치: " . realpath($zip_file) . "\n";
    echo "📊 크기: " . formatFileSize(filesize($zip_file)) . "\n";
    echo "📁 파일 수: $processed_files 개\n";
} else {
    echo "❌ ZIP 파일 생성 실패\n";
    exit(1);
}

// 임시 디렉토리 정리
removeDirectory($temp_dir);
echo "\n🧹 임시 파일 정리 완료\n";

echo str_repeat("=", 60) . "\n";
echo "🎉 배포 패키지 생성 완료!\n";
echo "\n📋 다음 단계:\n";
echo "1. " . basename($zip_file) . " 파일을 카페24 FTP에 업로드\n";
echo "2. 파일매니저에서 압축 해제\n";
echo "3. DEPLOYMENT_README.txt 가이드 따라 설정\n";
echo "4. 테스트 체크리스트 실행\n";

// === 헬퍼 함수들 ===

function processFile($source_file, $target_file, $exclude_patterns) {
    // 제외 패턴 체크
    foreach ($exclude_patterns as $pattern) {
        if (preg_match($pattern, basename($source_file)) || 
            preg_match($pattern, $source_file)) {
            return false;
        }
    }
    
    $target_dir = dirname($target_file);
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $extension = strtolower(pathinfo($source_file, PATHINFO_EXTENSION));
    
    if (in_array($extension, ['php', 'html', 'css', 'js', 'txt'])) {
        // 텍스트 파일 처리 (인코딩 변환)
        $content = file_get_contents($source_file);
        
        // BOM 제거
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        // 줄바꿈 통일 (LF)
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // PHP 파일의 경우 Windows 경로 수정
        if ($extension === 'php') {
            // Windows 경로를 Linux 경로로 변경
            $content = str_replace('\\', '/', $content);
            
            // XAMPP 절대경로 제거
            $content = preg_replace('/C:\/xampp\/htdocs\//', '', $content);
            $content = preg_replace('/C:\\\\xampp\\\\htdocs\\\\/', '', $content);
            
            // localhost 참조 정리
            $content = str_replace('http://localhost/', 'http://dsp1830.shop/', $content);
        }
        
        file_put_contents($target_file, $content);
        echo "  📝 " . substr($source_file, strlen(dirname(__DIR__)) + 1) . "\n";
    } else {
        // 바이너리 파일은 그대로 복사
        copy($source_file, $target_file);
        echo "  📄 " . substr($source_file, strlen(dirname(__DIR__)) + 1) . "\n";
    }
    
    return true;
}

function processDirectory($source_dir, $target_dir, $exclude_patterns) {
    $count = 0;
    
    if (!is_dir($source_dir)) {
        return 0;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        
        $source_file = $file->getRealPath();
        $relative_path = substr($source_file, strlen($source_dir) + 1);
        $target_file = $target_dir . '/' . $relative_path;
        
        if (processFile($source_file, $target_file, $exclude_patterns)) {
            $count++;
        }
    }
    
    return $count;
}

function createHtaccessFile($file_path) {
    $content = '# Cafe24 Apache 설정
AddDefaultCharset UTF-8

# 디렉토리 브라우징 차단
Options -Indexes

# 중요 파일 접근 차단
<Files ~ "^(config|\.env|\.log|backup|\.sql|\.md)">
    Order allow,deny
    Deny from all
</Files>

<FilesMatch "\.(inc|conf|config|sql|md|bak|backup)$">
    Order deny,allow
    Deny from all
</FilesMatch>

# PHP 설정 파일 보호
<Files "db.php">
    Order allow,deny  
    Deny from all
    Allow from localhost
    Allow from 127.0.0.1
</Files>

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
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresDefault "access plus 2 days"
</IfModule>

# 보안 헤더
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
';
    
    file_put_contents($file_path, $content);
    echo "  ⚙️  .htaccess 생성\n";
}

function createUserIniFile($file_path) {
    $content = '; Cafe24 PHP 설정
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
session.cookie_secure = 0
session.cookie_httponly = 1

; 타임존
date.timezone = "Asia/Seoul"

; 파일 업로드
file_uploads = On
upload_tmp_dir = /tmp

; 보안
allow_url_fopen = Off
allow_url_include = Off
';
    
    file_put_contents($file_path, $content);
    echo "  ⚙️  .user.ini 생성\n";
}

function createDatabaseConfig($file_path) {
    $dir = dirname($file_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $content = '<?php
/**
 * 🗄️  데이터베이스 설정 (환경별 자동 전환)
 */

// 환경 감지
$is_local = (
    strpos($_SERVER["HTTP_HOST"] ?? "", "localhost") !== false ||
    strpos($_SERVER["HTTP_HOST"] ?? "", "127.0.0.1") !== false ||
    strpos($_SERVER["HTTP_HOST"] ?? "", "192.168.") !== false
);

// 환경별 DB 설정
if ($is_local) {
    // 로컬 개발 환경 (XAMPP)
    $db_config = [
        "host" => "localhost",
        "user" => "duson1830", 
        "password" => "du1830",
        "database" => "duson1830",
        "charset" => "utf8mb4"
    ];
} else {
    // 프로덕션 환경 (Cafe24)
    $db_config = [
        "host" => "localhost",
        "user" => "dsp1830",
        "password" => "ds701018", 
        "database" => "dsp1830",
        "charset" => "utf8mb4"
    ];
}

// 글로벌 변수로 할당 (기존 코드 호환)
$host = $db_config["host"];
$user = $db_config["user"]; 
$password = $db_config["password"];
$dataname = $db_config["database"];

// DB 연결 생성
try {
    $db = new mysqli($host, $user, $password, $dataname);
    $connect = $db; // 기존 코드 호환성
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    // 문자셋 설정
    $db->set_charset($db_config["charset"]);
    
    // 타임존 설정
    $db->query("SET time_zone = \"+09:00\"");
    
} catch (Exception $e) {
    // 에러 로깅
    error_log("DB Connection Error: " . $e->getMessage());
    
    // 개발 환경에서만 에러 표시
    if ($is_local) {
        die("❌ 데이터베이스 연결 실패: " . $e->getMessage());
    } else {
        die("❌ 시스템 점검 중입니다. 잠시 후 다시 시도해주세요.");
    }
}

// 연결 상태 확인 함수
function checkDbConnection() {
    global $db;
    return $db && $db->ping();
}

// 안전한 쿼리 실행 함수
function safeQuery($sql, $params = []) {
    global $db;
    
    if (!checkDbConnection()) {
        throw new Exception("Database connection lost");
    }
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    if (!empty($params)) {
        $types = str_repeat("s", count($params)); // 모든 파라미터를 문자열로 처리
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    return $stmt->get_result();
}
?>';
    
    file_put_contents($file_path, $content);
    echo "  🗄️  Database config 생성\n";
}

function createDeploymentGuide($file_path, $package_name) {
    $content = "🚀 Cafe24 배포 가이드
생성일시: " . date('Y-m-d H:i:s') . "
패키지명: {$package_name}

════════════════════════════════════════
📋 배포 절차 (순서대로 진행하세요)
════════════════════════════════════════

1. 📦 백업 생성
   - 현재 서버의 모든 파일을 backup 폴더로 이동
   - DB도 별도 백업 (phpMyAdmin 내보내기)

2. 📤 파일 업로드
   - {$package_name}.zip을 카페24 FTP 루트에 업로드
   - 파일매니저에서 압축 해제

3. 🔒 권한 설정 
   - 디렉토리: 755 (rwxr-xr-x)
   - 파일: 644 (rw-r--r--) 
   - 업로드 폴더: 775 (rwxrwxr-x)
   
   ※ 터미널 명령어:
   find . -type d -exec chmod 755 {} \\;
   find . -type f -exec chmod 644 {} \\;
   chmod 775 uploads logs

4. 🗄️  데이터베이스 확인
   - config/database.php 파일이 자동으로 환경을 감지합니다
   - 프로덕션: dsp1830 / ds701018 / dsp1830
   - 연결 오류 시 db.php 파일의 정보와 비교하세요

5. 📁 디렉토리 생성
   mkdir -p logs temp cache
   touch logs/php-error.log
   
6. ⚙️  PHP 설정 적용
   - .user.ini 파일이 자동 생성되었습니다
   - 5-10분 후 설정이 적용됩니다

════════════════════════════════════════
✅ 배포 후 필수 테스트 (10분 체크리스트)
════════════════════════════════════════

□ 기본 페이지 접속 (http://dsp1830.shop/)
□ 견적 계산 기능 테스트
  - 명함: /mlangprintauto/namecard/  
  - 스티커: /mlangprintauto/msticker/
  - 봉투: /mlangprintauto/envelope/
□ 파일 업로드 테스트
□ 이메일 발송 테스트 
□ 관리자 로그인 테스트
□ 에러 로그 확인 (logs/php-error.log)

════════════════════════════════════════
🔧 문제 해결 가이드
════════════════════════════════════════

HTTP 500 에러:
→ logs/php-error.log 확인
→ 파일 권한 재설정
→ .user.ini 설정 확인

DB 연결 오류:
→ config/database.php의 연결정보 확인
→ DB 서버 상태 확인 
→ 방화벽 설정 확인

자동 계산 안됨:
→ 브라우저 개발자도구에서 JavaScript 오류 확인
→ AJAX 요청 실패 여부 확인
→ PHP 에러 로그 확인

파일 업로드 실패:
→ uploads 폴더 권한 775 확인
→ PHP 설정에서 업로드 제한 확인
→ 디스크 용량 확인

════════════════════════════════════════
📞 긴급 상황 대응
════════════════════════════════════════

전체 사이트 다운:
1. 백업 파일로 즉시 복구
2. 원인 분석은 복구 후 진행

부분 기능 오류:
1. 에러 로그 확인 후 해당 기능만 롤백
2. config/database.php → db.php로 임시 교체 가능

데이터베이스 문제:
1. phpMyAdmin에서 백업 DB로 복구
2. 테이블별 복구도 가능

════════════════════════════════════════
📊 파일 구조 설명
════════════════════════════════════════

/                          (웹 루트)
├── config/
│   └── database.php       (자동 환경 감지 DB 설정)
├── includes/              (공통 함수)
├── mlangprintauto/        (제품별 견적 시스템)
├── admin/                 (관리자 페이지)
├── uploads/               (업로드 파일 저장소)
├── logs/                  (시스템 로그)
├── .htaccess              (Apache 설정)
├── .user.ini              (PHP 설정)
└── DEPLOYMENT_README.txt  (이 파일)

모든 설정이 자동으로 적용되므로 별도 수정이 필요하지 않습니다.
문제 발생 시 이 가이드를 참조하여 단계별로 해결하세요.

배포 완료 후 반드시 전체 기능 테스트를 수행해주세요! 🎉
";
    
    file_put_contents($file_path, $content);
    echo "  📋 배포 가이드 생성\n";
}

function removeDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

function formatFileSize($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, 2) . ' ' . $units[$i];
}
?>