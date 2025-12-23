<?php
/**
 * 보안 데이터베이스 설정 파일
 * 환경 변수 사용으로 민감한 정보 보호
 */

// .env 파일 로드 함수
function loadEnv($file) {
    if (!file_exists($file)) {
        throw new Exception('.env 파일을 찾을 수 없습니다: ' . $file);
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // 주석 무시
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

// .env 파일 로드
loadEnv($_SERVER['DOCUMENT_ROOT'] . '/.env');

// 데이터베이스 연결 정보
$db_config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'user' => $_ENV['DB_USER'] ?? '',
    'pass' => $_ENV['DB_PASS'] ?? '',
    'name' => $_ENV['DB_NAME'] ?? ''
];

// 빈 값 체크
if (empty($db_config['user']) || empty($db_config['name'])) {
    throw new Exception('데이터베이스 설정이 완전하지 않습니다.');
}

// 데이터베이스 연결
try {
    $db = new mysqli(
        $db_config['host'], 
        $db_config['user'], 
        $db_config['pass'], 
        $db_config['name']
    );
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    // UTF-8 설정
    $db->set_charset("utf8");
    
    // 하위 호환성을 위한 변수 설정
    $connect = $db;
    
} catch (Exception $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    die("데이터베이스 연결에 실패했습니다.");
}
?>