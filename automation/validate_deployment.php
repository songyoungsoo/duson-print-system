<?php
/**
 * 🧪 Cafe24 배포 후 자동 검증 스크립트
 * 
 * 기능:
 * - HTTP 응답 테스트
 * - 데이터베이스 연결 확인
 * - PHP 확장 모듈 검사
 * - 파일 권한 검증
 * - 핵심 기능 동작 확인
 * - 종합 보고서 생성
 */

// CLI에서만 실행 가능
if (php_sapi_name() !== 'cli') {
    // 웹에서 접근 시 보안을 위해 관리자 인증 필요
    session_start();
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        http_response_code(403);
        die('🔒 접근 권한이 필요합니다. 관리자 로그인 후 이용하세요.');
    }
    
    // 웹 출력용 헤더
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>배포 검증 결과</title>
        <style>
            body { font-family: "Noto Sans KR", sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .pass { color: #28a745; } .fail { color: #dc3545; } .warn { color: #ffc107; }
            .test-result { margin: 10px 0; padding: 8px; border-left: 4px solid #ddd; }
            .test-result.pass { border-left-color: #28a745; background: #d4edda; }
            .test-result.fail { border-left-color: #dc3545; background: #f8d7da; }
            .summary { margin: 20px 0; padding: 15px; background: #e9ecef; border-radius: 5px; }
        </style>
    </head>
    <body>
    <div class="container">
    <h1>🧪 Cafe24 배포 검증 결과</h1>
    <p>검증 시간: ' . date('Y-m-d H:i:s') . '</p>
    <hr>';
}

echo "🧪 Cafe24 배포 검증 테스트 시작...\n";
echo str_repeat("=", 60) . "\n";

$tests = [];
$start_time = microtime(true);

// 기본 설정
$base_url = getBaseUrl();
$is_web = php_sapi_name() !== 'cli';

echo "🌍 테스트 대상: $base_url\n\n";

// === 테스트 실행 ===

// 1. 기본 연결 테스트
echo "1️⃣  기본 연결 테스트\n";
echo str_repeat("-", 30) . "\n";

$tests['homepage'] = testHttpResponse($base_url, '기본 페이지');
$tests['admin'] = testHttpResponse($base_url . '/admin/', '관리자 페이지');

// 2. 데이터베이스 테스트
echo "\n2️⃣  데이터베이스 테스트\n";
echo str_repeat("-", 30) . "\n";

$tests['database'] = testDatabaseConnection();
$tests['db_charset'] = testDatabaseCharset();
$tests['db_tables'] = testDatabaseTables();

// 3. PHP 환경 테스트
echo "\n3️⃣  PHP 환경 테스트\n";
echo str_repeat("-", 30) . "\n";

$tests['php_version'] = testPhpVersion();
$tests['php_extensions'] = testPhpExtensions();
$tests['php_settings'] = testPhpSettings();

// 4. 파일 시스템 테스트
echo "\n4️⃣  파일 시스템 테스트\n";
echo str_repeat("-", 30) . "\n";

$tests['file_permissions'] = testFilePermissions();
$tests['upload_dirs'] = testUploadDirectories();
$tests['log_dirs'] = testLogDirectories();

// 5. 핵심 기능 테스트
echo "\n5️⃣  핵심 기능 테스트\n";
echo str_repeat("-", 30) . "\n";

$product_pages = [
    '/mlangprintauto/namecard/' => '명함',
    '/mlangprintauto/msticker/' => '자석스티커', 
    '/mlangprintauto/envelope/' => '봉투',
    '/mlangprintauto/cadarok/' => '카다록',
    '/mlangprintauto/ncrflambeau/' => '양식지',
    '/mlangprintauto/merchandisebond/' => '상품권',
    '/mlangprintauto/littleprint/' => '포스터',
    '/mlangprintauto/inserted/' => '전단지',
    '/mlangprintauto/sticker_new/' => '일반스티커'
];

foreach ($product_pages as $url => $name) {
    $tests["product_$name"] = testHttpResponse($base_url . $url, "$name 페이지");
}

// 6. 보안 테스트
echo "\n6️⃣  보안 테스트\n";
echo str_repeat("-", 30) . "\n";

$tests['config_security'] = testConfigSecurity();
$tests['directory_browsing'] = testDirectoryBrowsing();
$tests['sensitive_files'] = testSensitiveFiles();

// 7. 성능 테스트
echo "\n7️⃣  성능 테스트\n";
echo str_repeat("-", 30) . "\n";

$tests['response_time'] = testResponseTime();
$tests['memory_usage'] = testMemoryUsage();

// === 결과 요약 ===

echo "\n📋 검증 결과 요약\n";
echo str_repeat("=", 60) . "\n";

$passed = 0;
$failed = 0;
$warnings = 0;
$total = count($tests);

foreach ($tests as $test_name => $result) {
    $status_icon = '';
    $status_class = '';
    
    if ($result['status'] === 'pass') {
        $status_icon = "✅";
        $status_class = 'pass';
        $passed++;
    } elseif ($result['status'] === 'fail') {
        $status_icon = "❌";
        $status_class = 'fail';
        $failed++;
    } else {
        $status_icon = "⚠️ ";
        $status_class = 'warn';
        $warnings++;
    }
    
    $output = sprintf("%-25s: %s %s", $test_name, $status_icon, $result['message']);
    
    if ($is_web) {
        echo "<div class='test-result {$status_class}'>{$output}</div>";
    } else {
        echo $output . "\n";
    }
    
    if (!empty($result['details'])) {
        if ($is_web) {
            echo "<div style='margin-left: 20px; font-size: 0.9em; color: #666;'>" . 
                 htmlspecialchars($result['details']) . "</div>";
        } else {
            echo "   " . $result['details'] . "\n";
        }
    }
}

$execution_time = round(microtime(true) - $start_time, 2);
$success_rate = round(($passed / $total) * 100, 1);

$summary = "
📊 최종 통계:
• 총 테스트: {$total}개
• 성공: {$passed}개
• 실패: {$failed}개  
• 경고: {$warnings}개
• 성공률: {$success_rate}%
• 실행시간: {$execution_time}초
";

if ($is_web) {
    echo "<div class='summary'>" . nl2br($summary) . "</div>";
} else {
    echo $summary;
}

// 권장사항 출력
if ($failed > 0 || $warnings > 0) {
    echo "\n🔧 권장사항:\n";
    echo str_repeat("-", 30) . "\n";
    
    if ($failed > 0) {
        echo "❌ 실패한 테스트를 우선 해결하세요.\n";
        echo "• PHP 에러 로그 확인: logs/php-error.log\n";
        echo "• 파일 권한 재설정: chmod 755 폴더, chmod 644 파일\n";
        echo "• DB 연결정보 확인: config/database.php\n";
    }
    
    if ($warnings > 0) {
        echo "⚠️  경고 항목을 검토하세요.\n";
        echo "• PHP 설정 최적화 (.user.ini)\n";
        echo "• 보안 설정 강화 (.htaccess)\n";
    }
}

// 최종 결과
if ($failed === 0) {
    echo "\n🎉 배포 검증 완료! 모든 핵심 기능이 정상 작동합니다.\n";
    if ($is_web) echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; color: #155724;'><strong>🎉 배포 성공!</strong><br>모든 핵심 기능이 정상 작동합니다.</div>";
    exit(0);
} else {
    echo "\n⚠️  일부 문제가 발견되었습니다. 위 권장사항을 참조하여 해결하세요.\n";
    if ($is_web) echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; color: #721c24;'><strong>⚠️  주의 필요</strong><br>일부 문제가 발견되었습니다. 해결 후 재검증하세요.</div>";
    exit(1);
}

if ($is_web) {
    echo '</div></body></html>';
}

// === 테스트 함수들 ===

function getBaseUrl() {
    if (php_sapi_name() === 'cli') {
        return 'https://dsp114.com'; // CLI에서는 기본값
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host;
}

function testHttpResponse($url, $description = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Deployment-Validator/1.0');
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $response_time = round((microtime(true) - $start_time) * 1000, 2);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'status' => 'fail',
            'message' => "$description 연결 실패",
            'details' => "cURL Error: $error"
        ];
    }
    
    if ($http_code >= 200 && $http_code < 400) {
        $message = "$description 정상 (HTTP $http_code, {$response_time}ms)";
        return ['status' => 'pass', 'message' => $message];
    } else {
        return [
            'status' => 'fail', 
            'message' => "$description HTTP 오류",
            'details' => "HTTP $http_code, Response time: {$response_time}ms"
        ];
    }
}

function testDatabaseConnection() {
    try {
        // 환경별 DB 설정 로드
        include_once __DIR__ . '/../config/database.php';
        
        if (!isset($db) || !$db) {
            // 직접 연결 시도
            $db = new mysqli('localhost', 'dsp1830', 'ds701018', 'dsp1830');
        }
        
        if ($db->connect_error) {
            return [
                'status' => 'fail',
                'message' => 'DB 연결 실패',
                'details' => $db->connect_error
            ];
        }
        
        // 간단한 쿼리 테스트
        $result = $db->query("SELECT 1 as test");
        if (!$result) {
            return [
                'status' => 'fail',
                'message' => 'DB 쿼리 실패',
                'details' => $db->error
            ];
        }
        
        $db->close();
        return [
            'status' => 'pass',
            'message' => 'DB 연결 성공'
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'fail',
            'message' => 'DB 연결 예외 발생',
            'details' => $e->getMessage()
        ];
    }
}

function testDatabaseCharset() {
    try {
        $db = new mysqli('localhost', 'dsp1830', 'ds701018', 'dsp1830');
        
        if ($db->connect_error) {
            return ['status' => 'fail', 'message' => 'DB 연결 불가'];
        }
        
        $result = $db->query("SHOW VARIABLES LIKE 'character_set_database'");
        $row = $result->fetch_assoc();
        $charset = $row['Value'] ?? 'unknown';
        
        $db->close();
        
        if (strpos($charset, 'utf8') !== false) {
            return ['status' => 'pass', 'message' => "DB 문자셋 정상 ($charset)"];
        } else {
            return [
                'status' => 'warn',
                'message' => 'DB 문자셋 확인 필요',
                'details' => "현재: $charset, 권장: utf8mb4"
            ];
        }
        
    } catch (Exception $e) {
        return ['status' => 'fail', 'message' => 'DB 문자셋 확인 실패'];
    }
}

function testDatabaseTables() {
    try {
        $db = new mysqli('localhost', 'dsp1830', 'ds701018', 'dsp1830');
        
        if ($db->connect_error) {
            return ['status' => 'fail', 'message' => 'DB 연결 불가'];
        }
        
        // 핵심 테이블 존재 여부 확인
        $essential_tables = [
            'users', 
            'mlangprintauto_NameCard',
            'mlangprintauto_msticker', 
            'shop_temp',
            'mlangorder_printauto'
        ];
        
        $missing_tables = [];
        foreach ($essential_tables as $table) {
            $result = $db->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows === 0) {
                $missing_tables[] = $table;
            }
        }
        
        $db->close();
        
        if (empty($missing_tables)) {
            return ['status' => 'pass', 'message' => '핵심 테이블 모두 존재'];
        } else {
            return [
                'status' => 'fail',
                'message' => '누락된 테이블 발견',
                'details' => implode(', ', $missing_tables)
            ];
        }
        
    } catch (Exception $e) {
        return ['status' => 'fail', 'message' => 'DB 테이블 확인 실패'];
    }
}

function testPhpVersion() {
    $version = PHP_VERSION;
    $major = (int) substr($version, 0, 1);
    $minor = (int) substr($version, 2, 1);
    
    if ($major >= 8 || ($major == 7 && $minor >= 4)) {
        return ['status' => 'pass', 'message' => "PHP 버전 적합 ($version)"];
    } else {
        return [
            'status' => 'warn', 
            'message' => 'PHP 버전 업그레이드 권장',
            'details' => "현재: $version, 권장: 7.4 이상"
        ];
    }
}

function testPhpExtensions() {
    $required = ['mysqli', 'gd', 'mbstring', 'curl', 'json'];
    $missing = [];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    
    if (empty($missing)) {
        return ['status' => 'pass', 'message' => 'PHP 확장모듈 모두 설치됨'];
    } else {
        return [
            'status' => 'fail',
            'message' => 'PHP 확장모듈 누락',
            'details' => implode(', ', $missing)
        ];
    }
}

function testPhpSettings() {
    $settings = [
        'upload_max_filesize' => ['min' => '10M', 'current' => ini_get('upload_max_filesize')],
        'post_max_size' => ['min' => '10M', 'current' => ini_get('post_max_size')],
        'memory_limit' => ['min' => '128M', 'current' => ini_get('memory_limit')],
        'max_execution_time' => ['min' => 60, 'current' => ini_get('max_execution_time')]
    ];
    
    $issues = [];
    foreach ($settings as $setting => $config) {
        $current = $config['current'];
        $min = $config['min'];
        
        // 크기 단위 변환
        if (strpos($current, 'M') !== false || strpos($min, 'M') !== false) {
            $current_mb = (int) str_replace('M', '', $current);
            $min_mb = (int) str_replace('M', '', $min);
            
            if ($current_mb < $min_mb) {
                $issues[] = "$setting: $current (권장: $min)";
            }
        } elseif (is_numeric($current) && is_numeric($min)) {
            if ((int) $current < (int) $min) {
                $issues[] = "$setting: $current (권장: $min)";
            }
        }
    }
    
    if (empty($issues)) {
        return ['status' => 'pass', 'message' => 'PHP 설정 적합'];
    } else {
        return [
            'status' => 'warn',
            'message' => 'PHP 설정 조정 권장', 
            'details' => implode(', ', $issues)
        ];
    }
}

function testFilePermissions() {
    $critical_files = [
        'index.php' => 644,
        'includes/auth.php' => 644,
        'config/database.php' => 644
    ];
    
    $issues = [];
    foreach ($critical_files as $file => $expected) {
        $full_path = __DIR__ . '/../' . $file;
        if (file_exists($full_path)) {
            $perms = fileperms($full_path) & 0777;
            if ($perms != octdec($expected)) {
                $issues[] = "$file: " . decoct($perms) . " (권장: $expected)";
            }
        } else {
            $issues[] = "$file: 파일 없음";
        }
    }
    
    if (empty($issues)) {
        return ['status' => 'pass', 'message' => '파일 권한 적합'];
    } else {
        return [
            'status' => 'warn',
            'message' => '파일 권한 확인 필요',
            'details' => implode(', ', $issues)
        ];
    }
}

function testUploadDirectories() {
    $upload_dirs = ['uploads', 'ImgFolder', 'temp'];
    $issues = [];
    
    foreach ($upload_dirs as $dir) {
        $full_path = __DIR__ . '/../' . $dir;
        
        if (!is_dir($full_path)) {
            $issues[] = "$dir: 디렉토리 없음";
        } elseif (!is_writable($full_path)) {
            $issues[] = "$dir: 쓰기 권한 없음";
        }
    }
    
    if (empty($issues)) {
        return ['status' => 'pass', 'message' => '업로드 디렉토리 정상'];
    } else {
        return [
            'status' => 'fail',
            'message' => '업로드 디렉토리 문제',
            'details' => implode(', ', $issues)
        ];
    }
}

function testLogDirectories() {
    $log_dirs = ['logs'];
    $log_files = ['logs/php-error.log'];
    
    $issues = [];
    
    // 로그 디렉토리 확인
    foreach ($log_dirs as $dir) {
        $full_path = __DIR__ . '/../' . $dir;
        if (!is_dir($full_path)) {
            mkdir($full_path, 0755, true);
        }
        if (!is_writable($full_path)) {
            $issues[] = "$dir: 쓰기 권한 없음";
        }
    }
    
    // 로그 파일 확인
    foreach ($log_files as $file) {
        $full_path = __DIR__ . '/../' . $file;
        if (!file_exists($full_path)) {
            touch($full_path);
        }
        if (!is_writable($full_path)) {
            $issues[] = "$file: 쓰기 권한 없음";
        }
    }
    
    if (empty($issues)) {
        return ['status' => 'pass', 'message' => '로그 시스템 정상'];
    } else {
        return [
            'status' => 'warn',
            'message' => '로그 시스템 문제',
            'details' => implode(', ', $issues)
        ];
    }
}

function testConfigSecurity() {
    $sensitive_files = [
        'db.php',
        'config/database.php',
        '.env'
    ];
    
    $accessible = [];
    foreach ($sensitive_files as $file) {
        $url = getBaseUrl() . '/' . $file;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200 && !empty($response)) {
            $accessible[] = $file;
        }
    }
    
    if (empty($accessible)) {
        return ['status' => 'pass', 'message' => '설정 파일 보안 정상'];
    } else {
        return [
            'status' => 'fail',
            'message' => '설정 파일 노출 위험',
            'details' => implode(', ', $accessible)
        ];
    }
}

function testDirectoryBrowsing() {
    $test_dirs = ['/uploads/', '/includes/', '/admin/'];
    $browsable = [];
    
    foreach ($test_dirs as $dir) {
        $url = getBaseUrl() . $dir;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // 디렉토리 목록이 표시되는지 확인
        if ($http_code == 200 && (strpos($response, 'Index of') !== false || strpos($response, 'Directory Listing') !== false)) {
            $browsable[] = $dir;
        }
    }
    
    if (empty($browsable)) {
        return ['status' => 'pass', 'message' => '디렉토리 브라우징 차단됨'];
    } else {
        return [
            'status' => 'warn',
            'message' => '디렉토리 브라우징 가능',
            'details' => implode(', ', $browsable)
        ];
    }
}

function testSensitiveFiles() {
    $sensitive_patterns = [
        '/.git/',
        '/backup/',
        '/.env',
        '/config.php',
        '/.htaccess'
    ];
    
    $accessible = [];
    foreach ($sensitive_patterns as $pattern) {
        $url = getBaseUrl() . $pattern;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $accessible[] = $pattern;
        }
    }
    
    if (empty($accessible)) {
        return ['status' => 'pass', 'message' => '민감한 파일 보호됨'];
    } else {
        return [
            'status' => 'warn',
            'message' => '민감한 파일 접근 가능',
            'details' => implode(', ', $accessible)
        ];
    }
}

function testResponseTime() {
    $url = getBaseUrl();
    $start_time = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    curl_exec($ch);
    curl_close($ch);
    
    $response_time = round((microtime(true) - $start_time) * 1000, 2);
    
    if ($response_time < 2000) {
        return ['status' => 'pass', 'message' => "응답시간 양호 ({$response_time}ms)"];
    } elseif ($response_time < 5000) {
        return ['status' => 'warn', 'message' => "응답시간 보통 ({$response_time}ms)"];
    } else {
        return ['status' => 'fail', 'message' => "응답시간 느림 ({$response_time}ms)"];
    }
}

function testMemoryUsage() {
    $memory_mb = round(memory_get_usage(true) / 1024 / 1024, 2);
    $limit = ini_get('memory_limit');
    
    if ($memory_mb < 50) {
        return ['status' => 'pass', 'message' => "메모리 사용량 양호 ({$memory_mb}MB)"];
    } elseif ($memory_mb < 100) {
        return ['status' => 'warn', 'message' => "메모리 사용량 보통 ({$memory_mb}MB)"];
    } else {
        return ['status' => 'fail', 'message' => "메모리 사용량 높음 ({$memory_mb}MB)"];
    }
}
?>