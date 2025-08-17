<?php
/**
 * 로그인 시스템 통합 마이그레이션 스크립트
 * member 테이블 → users 테이블 데이터 이전
 */

include "db.php";

echo "<h2>🔄 로그인 시스템 통합 마이그레이션</h2>";

// 1. member 테이블 확인
$member_check = mysqli_query($db, "SHOW TABLES LIKE 'member'");
if (mysqli_num_rows($member_check) == 0) {
    echo "<p>❌ member 테이블이 존재하지 않습니다.</p>";
    exit;
}

// 2. users 테이블 확인 및 생성
$users_check = mysqli_query($db, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_check) == 0) {
    echo "<p>📋 users 테이블을 생성합니다...</p>";
    $create_users = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        -- member 테이블 호환 필드
        member_id VARCHAR(50) DEFAULT NULL,
        old_password VARCHAR(50) DEFAULT NULL,
        login_count INT DEFAULT 0,
        last_login TIMESTAMP NULL
    )";
    
    if (mysqli_query($db, $create_users)) {
        echo "<p>✅ users 테이블이 생성되었습니다.</p>";
    } else {
        echo "<p>❌ users 테이블 생성 실패: " . mysqli_error($db) . "</p>";
        exit;
    }
}

// 3. member 테이블 데이터 조회
$member_query = "SELECT * FROM member ORDER BY id";
$member_result = mysqli_query($db, $member_query);
$member_count = mysqli_num_rows($member_result);

echo "<p>📊 member 테이블에서 {$member_count}개의 계정을 발견했습니다.</p>";

if ($member_count == 0) {
    echo "<p>ℹ️ 마이그레이션할 데이터가 없습니다.</p>";
    exit;
}

// 4. 데이터 마이그레이션
$migrated = 0;
$skipped = 0;

echo "<h3>🔄 마이그레이션 진행 중...</h3>";
echo "<ul>";

while ($member = mysqli_fetch_assoc($member_result)) {
    $member_id = $member['id'];
    $member_name = $member['name'] ?? '';
    $member_email = $member['email'] ?? '';
    $member_phone = $member['phone'] ?? '';
    $old_password = $member['pass'] ?? '';
    $login_count = $member['Logincount'] ?? 0;
    $last_login = $member['EndLogin'] ?? null;
    
    // username 생성 (id를 사용)
    $username = $member_id;
    
    // 기본 비밀번호 설정 (기존 비밀번호 또는 기본값)
    $new_password = !empty($old_password) ? $old_password : '123456';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // 중복 확인
    $check_exists = mysqli_query($db, "SELECT id FROM users WHERE username = '$username' OR member_id = '$member_id'");
    
    if (mysqli_num_rows($check_exists) > 0) {
        echo "<li>⚠️ 건너뜀: {$member_id} ({$member_name}) - 이미 존재</li>";
        $skipped++;
        continue;
    }
    
    // 데이터 삽입
    $insert_query = "INSERT INTO users (
        username, password, name, email, phone, 
        member_id, old_password, login_count, last_login
    ) VALUES (
        '$username', '$hashed_password', '$member_name', '$member_email', '$member_phone',
        '$member_id', '$old_password', '$login_count', " . ($last_login ? "'$last_login'" : "NULL") . "
    )";
    
    if (mysqli_query($db, $insert_query)) {
        echo "<li>✅ 마이그레이션: {$member_id} ({$member_name})</li>";
        $migrated++;
    } else {
        echo "<li>❌ 실패: {$member_id} ({$member_name}) - " . mysqli_error($db) . "</li>";
    }
}

echo "</ul>";

echo "<h3>📈 마이그레이션 완료</h3>";
echo "<p>✅ 성공: {$migrated}개</p>";
echo "<p>⚠️ 건너뜀: {$skipped}개</p>";
echo "<p>📊 총 처리: " . ($migrated + $skipped) . "개</p>";

// 5. 관리자 계정 확인
$admin_check = mysqli_query($db, "SELECT id FROM users WHERE username = 'admin'");
if (mysqli_num_rows($admin_check) == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_insert = "INSERT INTO users (username, password, name, email) VALUES ('admin', '$admin_password', '관리자', 'admin@dusong.co.kr')";
    
    if (mysqli_query($db, $admin_insert)) {
        echo "<p>✅ 관리자 계정이 생성되었습니다. (admin/admin123)</p>";
    }
}

echo "<h3>🎯 다음 단계</h3>";
echo "<ul>";
echo "<li>1. NcrFlambeau 페이지에서 auth.php 의존성 제거</li>";
echo "<li>2. member/login.php를 신규 시스템으로 업데이트</li>";
echo "<li>3. account/orders.php를 신규 시스템으로 업데이트</li>";
echo "<li>4. 모든 페이지의 로그인 체크 통일</li>";
echo "</ul>";

mysqli_close($db);
?>