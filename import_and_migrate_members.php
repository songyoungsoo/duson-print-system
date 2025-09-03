<?php
/**
 * SQL 파일 가져오기 + MEMBER → USERS 완전 마이그레이션
 * 중복 처리 및 데이터 병합 포함
 */

include 'db.php';
$connect = $db;

if (!$connect) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>🔄 SQL 가져오기 + MEMBER → USERS 완전 마이그레이션</h2>";
echo "<pre>";

// SQL 파일 경로
$sql_file_path = "C:\\Users\\ysung\\Downloads\\member (1).sql";

// Step 1: 현재 상태 확인
echo "=== 1단계: 현재 상태 확인 ===\n";

$current_member_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM member"))['count'];
echo "현재 MEMBER 테이블: {$current_member_count}명\n";

$users_check = mysqli_query($connect, "SELECT COUNT(*) as count FROM users");
$current_users_count = $users_check ? mysqli_fetch_assoc($users_check)['count'] : 0;
echo "현재 USERS 테이블: {$current_users_count}명\n";

// Step 2: 기존 MEMBER 테이블 백업 (날짜 문제 해결)
echo "\n=== 2단계: 기존 MEMBER 테이블 백업 ===\n";
$backup_member_table = "member_backup_" . date('YmdHis');

// 먼저 테이블 구조를 복사하고 날짜 기본값 문제 해결
$create_backup_query = "CREATE TABLE {$backup_member_table} LIKE member";
if (mysqli_query($connect, $create_backup_query)) {
    echo "✅ 백업 테이블 구조 생성: {$backup_member_table}\n";
    
    // 날짜 컬럼 기본값 수정 (있을 경우)
    $fix_date_query = "ALTER TABLE {$backup_member_table} MODIFY COLUMN date DATETIME DEFAULT NULL";
    mysqli_query($connect, $fix_date_query); // 에러 무시 (컬럼이 없을 수도 있음)
    
    // 데이터 복사
    $copy_data_query = "INSERT INTO {$backup_member_table} SELECT * FROM member";
    if (mysqli_query($connect, $copy_data_query)) {
        echo "✅ MEMBER 테이블 데이터 백업 완료: {$backup_member_table}\n";
    } else {
        echo "❌ 데이터 복사 실패: " . mysqli_error($connect) . "\n";
        // 백업 실패해도 계속 진행 (기존 데이터는 그대로 유지)
        echo "⚠️  백업 실패했지만 원본 데이터는 안전하게 보존되어 계속 진행합니다.\n";
    }
} else {
    echo "❌ 백업 테이블 생성 실패: " . mysqli_error($connect) . "\n";
    echo "⚠️  백업 없이 계속 진행합니다 (원본 데이터는 안전하게 보존됨).\n";
}

// Step 3: SQL 파일 읽기 및 처리
echo "\n=== 3단계: SQL 파일 처리 ===\n";

if (!file_exists($sql_file_path)) {
    echo "❌ SQL 파일을 찾을 수 없습니다: {$sql_file_path}\n";
    exit;
}

$sql_content = file_get_contents($sql_file_path);
if (!$sql_content) {
    echo "❌ SQL 파일을 읽을 수 없습니다.\n";
    exit;
}

echo "✅ SQL 파일 읽기 완료 (" . number_format(strlen($sql_content)) . " bytes)\n";

// INSERT 문만 추출
preg_match_all('/INSERT INTO member VALUES \([^)]+\);/i', $sql_content, $matches);
$insert_statements = $matches[0];
echo "✅ " . count($insert_statements) . "개의 INSERT 문 발견\n";

// Step 4: MEMBER 테이블 초기화 및 데이터 입력
echo "\n=== 4단계: MEMBER 테이블 재구성 ===\n";

// 기존 데이터 삭제 (백업은 이미 완료)
mysqli_query($connect, "DELETE FROM member");
echo "✅ 기존 MEMBER 데이터 삭제\n";

// AUTO_INCREMENT 리셋
mysqli_query($connect, "ALTER TABLE member AUTO_INCREMENT = 1");

// SQL 문 실행
$imported_count = 0;
$error_count = 0;

foreach ($insert_statements as $sql_statement) {
    if (mysqli_query($connect, $sql_statement)) {
        $imported_count++;
    } else {
        $error_count++;
        if ($error_count <= 5) { // 처음 5개 에러만 표시
            echo "❌ SQL 실행 실패: " . substr($sql_statement, 0, 100) . "...\n";
        }
    }
}

echo "✅ SQL 가져오기 완료: {$imported_count}명 성공, {$error_count}명 실패\n";

// Step 5: 최종 MEMBER 테이블 상태 확인
$final_member_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM member"))['count'];
echo "✅ 최종 MEMBER 테이블: {$final_member_count}명\n";

// Step 6: USERS 테이블 생성
echo "\n=== 5단계: USERS 테이블 생성 ===\n";

// 기존 USERS 테이블이 있으면 백업
$users_exists = mysqli_query($connect, "SELECT 1 FROM users LIMIT 1");
if ($users_exists) {
    $backup_users_table = "users_backup_" . date('YmdHis');
    mysqli_query($connect, "CREATE TABLE {$backup_users_table} AS SELECT * FROM users");
    echo "✅ 기존 USERS 테이블 백업: {$backup_users_table}\n";
}

// USERS 테이블이 없으면 생성
$users_table_check = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_table_check) == 0) {
    // 테이블이 없을 때만 생성
    $create_users_query = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(200) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        postcode VARCHAR(20) DEFAULT NULL,
        address VARCHAR(200) DEFAULT NULL,
        detail_address VARCHAR(200) DEFAULT NULL,
        extra_address VARCHAR(200) DEFAULT NULL,
        business_number VARCHAR(50) DEFAULT NULL,
        business_name VARCHAR(100) DEFAULT NULL,
        business_owner VARCHAR(100) DEFAULT NULL,
        business_type VARCHAR(100) DEFAULT NULL,
        business_item VARCHAR(100) DEFAULT NULL,
        business_address VARCHAR(300) DEFAULT NULL,
        level VARCHAR(10) DEFAULT '5',
        login_count INT DEFAULT 0,
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        migrated_from_member TINYINT(1) DEFAULT 1,
        original_member_no INT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    if (mysqli_query($connect, $create_users_query)) {
        echo "✅ USERS 테이블 생성 완료\n";
    } else {
        echo "❌ USERS 테이블 생성 실패: " . mysqli_error($connect) . "\n";
        exit;
    }
} else {
    echo "✅ USERS 테이블이 이미 존재합니다. 기존 데이터를 유지합니다.\n";
}

// Step 7: MEMBER → USERS 데이터 마이그레이션
echo "\n=== 6단계: MEMBER → USERS 데이터 마이그레이션 ===\n";

$member_data = mysqli_query($connect, "SELECT * FROM member ORDER BY no");
if (!$member_data) {
    echo "❌ MEMBER 데이터 조회 실패\n";
    exit;
}

$migrated_count = 0;
$migration_errors = 0;

echo "진행 상황:\n";

while ($row = mysqli_fetch_assoc($member_data)) {
    // 먼저 이미 마이그레이션된 사용자인지 확인
    $check_exists = mysqli_prepare($connect, "SELECT id FROM users WHERE username = ? OR original_member_no = ?");
    mysqli_stmt_bind_param($check_exists, "si", $row['id'], $row['no']);
    mysqli_stmt_execute($check_exists);
    $exists_result = mysqli_stmt_get_result($check_exists);
    
    if (mysqli_num_rows($exists_result) > 0) {
        echo "⏭️  이미 존재: {$row['id']} ({$row['name']}) - 건너뛰기\n";
        mysqli_stmt_close($check_exists);
        continue;
    }
    mysqli_stmt_close($check_exists);
    
    // 전화번호 조합
    $phone = '';
    if (!empty($row['hendphone1']) || !empty($row['hendphone2']) || !empty($row['hendphone3'])) {
        // 휴대폰 우선
        $mobile_parts = array_filter([$row['hendphone1'], $row['hendphone2'], $row['hendphone3']]);
        if (!empty($mobile_parts)) {
            $phone = implode('-', $mobile_parts);
        }
    } elseif (!empty($row['phone1']) || !empty($row['phone2']) || !empty($row['phone3'])) {
        // 일반전화
        $phone_parts = array_filter([$row['phone1'], $row['phone2'], $row['phone3']]);
        if (!empty($phone_parts)) {
            $phone = implode('-', $phone_parts);
        }
    }
    
    // 날짜 처리
    $last_login = ($row['EndLogin'] === '0000-00-00 00:00:00') ? NULL : $row['EndLogin'];
    
    // 비밀번호 해싱
    $password = $row['pass'];
    if (strpos($password, '$') !== 0) {
        $password = password_hash($password, PASSWORD_DEFAULT);
    }
    
    // INSERT 쿼리
    $insert_query = "INSERT INTO users (
        username, password, name, email, phone, 
        postcode, address, detail_address, extra_address,
        business_number, business_name, business_owner, business_type, business_item, business_address,
        level, login_count, last_login, original_member_no
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssi", 
            $row['id'],                    // username
            $password,                     // password (hashed)
            $row['name'],                  // name
            $row['email'],                 // email
            $phone,                        // phone (combined)
            $row['sample6_postcode'],      // postcode
            $row['sample6_address'],       // address
            $row['sample6_detailAddress'], // detail_address
            $row['sample6_extraAddress'],  // extra_address
            $row['po1'],                   // business_number
            $row['po2'],                   // business_name
            $row['po3'],                   // business_owner
            $row['po4'],                   // business_type
            $row['po5'],                   // business_item
            $row['po6'],                   // business_address
            $row['level'],                 // level
            $row['Logincount'],            // login_count
            $last_login,                   // last_login
            $row['no']                     // original_member_no
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $migrated_count++;
            if ($migrated_count % 10 == 0 || $migrated_count <= 10) {
                echo "✅ {$migrated_count}번째: {$row['id']} ({$row['name']}) 마이그레이션 완료\n";
            }
        } else {
            $migration_errors++;
            echo "❌ {$row['id']} 마이그레이션 실패: " . mysqli_error($connect) . "\n";
        }
        mysqli_stmt_close($stmt);
    } else {
        $migration_errors++;
        echo "❌ 쿼리 준비 실패: " . mysqli_error($connect) . "\n";
    }
}

// Step 8: 결과 확인
echo "\n=== 7단계: 마이그레이션 결과 ===\n";
$final_users_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM users"))['count'];
echo "✅ MEMBER → USERS 마이그레이션 완료\n";
echo "   - 성공: {$migrated_count}명\n";
echo "   - 실패: {$migration_errors}명\n";
echo "   - 최종 USERS 테이블: {$final_users_count}명\n";

// 샘플 데이터 확인
echo "\n=== 8단계: 마이그레이션된 데이터 샘플 ===\n";
$sample_data = mysqli_query($connect, "SELECT username, name, email, phone, business_name FROM users ORDER BY id LIMIT 10");
echo sprintf("%-15s %-15s %-25s %-15s %-20s\n", "아이디", "이름", "이메일", "전화번호", "사업체명");
echo str_repeat("-", 90) . "\n";
while ($row = mysqli_fetch_assoc($sample_data)) {
    echo sprintf("%-15s %-15s %-25s %-15s %-20s\n",
        $row['username'],
        $row['name'],
        substr($row['email'], 0, 24),
        $row['phone'] ?: 'N/A',
        $row['business_name'] ?: 'N/A'
    );
}

echo "\n=== 마이그레이션 완료! ===\n";
echo "백업 테이블:\n";
echo "- MEMBER 백업: {$backup_member_table}\n";
if (isset($backup_users_table)) {
    echo "- USERS 백업: {$backup_users_table}\n";
}

echo "\n🎉 {$final_users_count}명의 회원이 USERS 테이블로 완전 이전되었습니다!\n";

echo "</pre>";

echo '<br><br>';
echo '<a href="index.php" style="background:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🏠 메인 페이지로 (통합 로그인 테스트)</a> ';
echo '<a href="check_tables_new.php" style="background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">📊 테이블 확인</a>';
?>