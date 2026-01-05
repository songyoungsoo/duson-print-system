<?php
/**
 * 방문자 추적 테이블 생성
 */
require_once __DIR__ . '/../db.php';

echo "<h2>방문자 추적 테이블 생성</h2>";

// 1. 방문자 로그 테이블
$sql1 = "CREATE TABLE IF NOT EXISTS visitor_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    page VARCHAR(500),
    user_agent TEXT,
    referer VARCHAR(500),
    visit_time DATETIME NOT NULL,
    session_id VARCHAR(64),
    country VARCHAR(50),
    is_bot TINYINT(1) DEFAULT 0,
    INDEX idx_ip (ip),
    INDEX idx_time (visit_time),
    INDEX idx_page (page(100)),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $sql1)) {
    echo "✅ visitor_logs 테이블 생성 완료<br>";
} else {
    echo "❌ visitor_logs 오류: " . mysqli_error($db) . "<br>";
}

// 2. IP 차단 목록 테이블
$sql2 = "CREATE TABLE IF NOT EXISTS blocked_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL UNIQUE,
    reason VARCHAR(255),
    hit_count INT DEFAULT 0,
    blocked_at DATETIME NOT NULL,
    expires_at DATETIME,
    is_permanent TINYINT(1) DEFAULT 0,
    INDEX idx_ip (ip),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $sql2)) {
    echo "✅ blocked_ips 테이블 생성 완료<br>";
} else {
    echo "❌ blocked_ips 오류: " . mysqli_error($db) . "<br>";
}

// 3. 일별 통계 요약 테이블 (성능 최적화용)
$sql3 = "CREATE TABLE IF NOT EXISTS visitor_daily_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE NOT NULL,
    total_visits INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    page_views INT DEFAULT 0,
    bot_visits INT DEFAULT 0,
    top_page VARCHAR(255),
    top_referer VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_date (stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $sql3)) {
    echo "✅ visitor_daily_stats 테이블 생성 완료<br>";
} else {
    echo "❌ visitor_daily_stats 오류: " . mysqli_error($db) . "<br>";
}

echo "<br><strong>완료!</strong> 이 파일은 삭제해도 됩니다.";
echo "<br><br><a href='/admin/dashboard.php'>← 대시보드로 이동</a>";
?>
