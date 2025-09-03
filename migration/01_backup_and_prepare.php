<?php
/**
 * Step 1: Backup and Preparation
 * 백업 생성 및 준비 작업
 */

require_once '../db.php';

echo "===== STEP 1: 백업 및 준비 작업 =====\n\n";

// 1. member 테이블 백업
$backup_date = date('Ymd_His');
$backup_table = "member_backup_{$backup_date}";

echo "1. member 테이블 백업 중...\n";

// 먼저 member 테이블 구조를 가져와서 수정
$create_backup = "CREATE TABLE {$backup_table} AS SELECT * FROM member WHERE 1=0";
if (!mysqli_query($db, $create_backup)) {
    // CREATE AS SELECT 방식 실패시 수동으로 생성
    echo "   - CREATE AS SELECT 실패, 수동 생성 시도...\n";
    
    // member 테이블의 컬럼 정보 가져오기
    $columns = mysqli_query($db, "SHOW CREATE TABLE member");
    $create_info = mysqli_fetch_assoc($columns);
    $create_sql = $create_info['Create Table'];
    
    // 테이블명 변경 및 datetime 기본값 문제 해결
    $create_sql = str_replace("CREATE TABLE `member`", "CREATE TABLE `{$backup_table}`", $create_sql);
    $create_sql = str_replace("datetime NOT NULL", "datetime DEFAULT NULL", $create_sql);
    
    if (!mysqli_query($db, $create_sql)) {
        die("ERROR: 백업 테이블 생성 실패 - " . mysqli_error($db));
    }
}

echo "   - 백업 테이블 생성 완료: {$backup_table}\n";

$copy_data = "INSERT INTO {$backup_table} SELECT * FROM member";
if (mysqli_query($db, $copy_data)) {
    $count = mysqli_affected_rows($db);
    echo "   - 데이터 복사 완료: {$count}개 레코드\n";
} else {
    echo "   - 데이터 복사 오류: " . mysqli_error($db) . "\n";
}

// 2. users 테이블 백업
$users_backup_table = "users_backup_{$backup_date}";
echo "\n2. users 테이블 백업 중...\n";

// users 테이블이 있으면 백업
$check_users = mysqli_query($db, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_users) > 0) {
    $create_users_backup = "CREATE TABLE {$users_backup_table} LIKE users";
    if (mysqli_query($db, $create_users_backup)) {
        echo "   - users 백업 테이블 생성 완료: {$users_backup_table}\n";
        
        $copy_users = "INSERT INTO {$users_backup_table} SELECT * FROM users";
        mysqli_query($db, $copy_users);
        $users_count = mysqli_affected_rows($db);
        echo "   - users 데이터 백업 완료: {$users_count}개 레코드\n";
    }
}

// 3. users 테이블 구조 확인 및 생성/수정
echo "\n3. users 테이블 구조 확인 및 준비...\n";

// users 테이블이 없으면 생성
$create_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(200) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    phone1 VARCHAR(10) DEFAULT NULL,
    phone2 VARCHAR(10) DEFAULT NULL,
    phone3 VARCHAR(10) DEFAULT NULL,
    hendphone1 VARCHAR(10) DEFAULT NULL,
    hendphone2 VARCHAR(10) DEFAULT NULL,
    hendphone3 VARCHAR(10) DEFAULT NULL,
    postcode VARCHAR(10) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    address_detail VARCHAR(255) DEFAULT NULL,
    address_extra VARCHAR(100) DEFAULT NULL,
    sample6_postcode VARCHAR(100) DEFAULT NULL,
    sample6_address VARCHAR(100) DEFAULT NULL,
    sample6_detailAddress VARCHAR(100) DEFAULT NULL,
    sample6_extraAddress VARCHAR(100) DEFAULT NULL,
    is_business TINYINT(1) DEFAULT 0,
    business_number VARCHAR(20) DEFAULT NULL,
    business_owner VARCHAR(100) DEFAULT NULL,
    business_name VARCHAR(100) DEFAULT NULL,
    level VARCHAR(10) DEFAULT '1',
    login_count INT DEFAULT 0,
    last_login DATETIME DEFAULT NULL,
    member_no MEDIUMINT(9) UNSIGNED DEFAULT NULL COMMENT 'Original member table no',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_member_no (member_no),
    KEY idx_username (username),
    KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

if (mysqli_query($db, $create_users)) {
    echo "   - users 테이블 준비 완료\n";
} else {
    echo "   - users 테이블 생성 오류: " . mysqli_error($db) . "\n";
}

// 4. 필요한 컬럼 추가 (이미 있으면 무시)
$columns_to_add = [
    'member_no' => 'MEDIUMINT(9) UNSIGNED DEFAULT NULL COMMENT \'Original member table no\'',
    'phone1' => 'VARCHAR(10) DEFAULT NULL',
    'phone2' => 'VARCHAR(10) DEFAULT NULL', 
    'phone3' => 'VARCHAR(10) DEFAULT NULL',
    'hendphone1' => 'VARCHAR(10) DEFAULT NULL',
    'hendphone2' => 'VARCHAR(10) DEFAULT NULL',
    'hendphone3' => 'VARCHAR(10) DEFAULT NULL',
    'sample6_postcode' => 'VARCHAR(100) DEFAULT NULL',
    'sample6_address' => 'VARCHAR(100) DEFAULT NULL',
    'sample6_detailAddress' => 'VARCHAR(100) DEFAULT NULL',
    'sample6_extraAddress' => 'VARCHAR(100) DEFAULT NULL',
    'level' => 'VARCHAR(10) DEFAULT \'1\'',
    'login_count' => 'INT DEFAULT 0',
    'last_login' => 'DATETIME DEFAULT NULL'
];

foreach ($columns_to_add as $column => $definition) {
    $check_column = mysqli_query($db, "SHOW COLUMNS FROM users LIKE '{$column}'");
    if (mysqli_num_rows($check_column) == 0) {
        $alter_query = "ALTER TABLE users ADD COLUMN {$column} {$definition}";
        if (mysqli_query($db, $alter_query)) {
            echo "   - 컬럼 추가: {$column}\n";
        }
    }
}

echo "\n백업 정보:\n";
echo "- member 백업 테이블: {$backup_table}\n";
echo "- users 백업 테이블: {$users_backup_table}\n";
echo "\n준비 작업 완료!\n";
echo "다음 단계: php 02_migrate_data.php\n";
?>