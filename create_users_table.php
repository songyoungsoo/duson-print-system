<?php
/**
 * USERS 테이블 생성
 */

include 'db.php';
$connect = $db;

if (!$connect) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>🔧 USERS 테이블 생성</h2>";
echo "<pre>";

// Step 1: 현재 테이블 상태 확인
echo "=== 1단계: 현재 테이블 상태 확인 ===\n";

$show_tables = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($show_tables) > 0) {
    echo "⚠️  USERS 테이블이 이미 존재합니다.\n";
    
    // 백업 생성
    $backup_table = "users_backup_" . date('YmdHis');
    if (mysqli_query($connect, "CREATE TABLE {$backup_table} AS SELECT * FROM users")) {
        echo "✅ 기존 USERS 테이블 백업 완료: {$backup_table}\n";
    }
    
    // 기존 테이블 삭제
    if (mysqli_query($connect, "DROP TABLE users")) {
        echo "✅ 기존 USERS 테이블 삭제 완료\n";
    }
} else {
    echo "✅ USERS 테이블이 존재하지 않습니다. 새로 생성합니다.\n";
}

// Step 2: USERS 테이블 생성
echo "\n=== 2단계: USERS 테이블 생성 ===\n";

$create_users_query = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL COMMENT '로그인 ID',
    password VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호',
    name VARCHAR(100) NOT NULL COMMENT '실명',
    email VARCHAR(200) DEFAULT NULL COMMENT '이메일',
    phone VARCHAR(50) DEFAULT NULL COMMENT '전화번호',
    postcode VARCHAR(20) DEFAULT NULL COMMENT '우편번호',
    address VARCHAR(200) DEFAULT NULL COMMENT '기본주소',
    detail_address VARCHAR(200) DEFAULT NULL COMMENT '상세주소',
    extra_address VARCHAR(200) DEFAULT NULL COMMENT '참고항목',
    business_number VARCHAR(50) DEFAULT NULL COMMENT '사업자등록번호',
    business_name VARCHAR(100) DEFAULT NULL COMMENT '상호명',
    business_owner VARCHAR(100) DEFAULT NULL COMMENT '대표자명',
    business_type VARCHAR(100) DEFAULT NULL COMMENT '업태',
    business_item VARCHAR(100) DEFAULT NULL COMMENT '업종',
    business_address VARCHAR(300) DEFAULT NULL COMMENT '사업장주소',
    level VARCHAR(10) DEFAULT '5' COMMENT '회원등급',
    login_count INT DEFAULT 0 COMMENT '로그인 횟수',
    last_login DATETIME DEFAULT NULL COMMENT '최종로그인시간',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    migrated_from_member TINYINT(1) DEFAULT 1 COMMENT 'MEMBER 테이블에서 이전됨',
    original_member_no INT DEFAULT NULL COMMENT '원본 MEMBER 테이블의 no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='통합 사용자 테이블'";

if (mysqli_query($connect, $create_users_query)) {
    echo "✅ USERS 테이블 생성 성공!\n";
} else {
    echo "❌ USERS 테이블 생성 실패: " . mysqli_error($connect) . "\n";
    exit;
}

// Step 3: 테이블 구조 확인
echo "\n=== 3단계: 생성된 테이블 구조 확인 ===\n";

$describe = mysqli_query($connect, "DESCRIBE users");
echo sprintf("%-20s %-15s %-5s %-5s %-10s %-20s\n", "Field", "Type", "Null", "Key", "Default", "Comment");
echo str_repeat("-", 90) . "\n";
while ($row = mysqli_fetch_assoc($describe)) {
    echo sprintf("%-20s %-15s %-5s %-5s %-10s %-20s\n", 
        $row['Field'], 
        substr($row['Type'], 0, 14), 
        $row['Null'], 
        $row['Key'], 
        $row['Default'] ?: 'NULL',
        substr($row['Comment'], 0, 19)
    );
}

// Step 4: 기본 관리자 계정 생성
echo "\n=== 4단계: 기본 관리자 계정 생성 ===\n";

$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_insert = "INSERT INTO users (username, password, name, email, level, business_name, business_owner) 
                VALUES ('admin', ?, '관리자', 'admin@duson.co.kr', '1', '두손기획인쇄', '관리자')";

$stmt = mysqli_prepare($connect, $admin_insert);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $admin_password);
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ 관리자 계정 생성 완료 (admin/admin123)\n";
    } else {
        echo "❌ 관리자 계정 생성 실패: " . mysqli_error($connect) . "\n";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "❌ 관리자 계정 준비 실패: " . mysqli_error($connect) . "\n";
}

// Step 5: 테이블 생성 확인
echo "\n=== 5단계: 최종 확인 ===\n";

$count_check = mysqli_query($connect, "SELECT COUNT(*) as count FROM users");
$count = mysqli_fetch_assoc($count_check)['count'];
echo "✅ USERS 테이블에 {$count}개 레코드가 있습니다.\n";

$sample_data = mysqli_query($connect, "SELECT id, username, name, email FROM users LIMIT 3");
if (mysqli_num_rows($sample_data) > 0) {
    echo "\n📊 샘플 데이터:\n";
    echo sprintf("%-5s %-15s %-15s %-25s\n", "ID", "Username", "Name", "Email");
    echo str_repeat("-", 65) . "\n";
    while ($row = mysqli_fetch_assoc($sample_data)) {
        echo sprintf("%-5s %-15s %-15s %-25s\n", 
            $row['id'], 
            $row['username'], 
            $row['name'], 
            $row['email']
        );
    }
}

echo "\n🎉 USERS 테이블 생성이 완료되었습니다!\n";
echo "이제 마이그레이션을 실행할 수 있습니다.\n";

echo "</pre>";

echo '<br><br>';
echo '<a href="import_and_migrate_members.php" style="background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🚀 회원 마이그레이션 실행</a> ';
echo '<a href="index.php" style="background:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">🏠 메인으로</a>';
?>