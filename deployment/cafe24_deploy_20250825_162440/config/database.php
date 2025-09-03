<?php
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
?>