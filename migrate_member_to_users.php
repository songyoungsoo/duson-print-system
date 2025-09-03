<?php
/**
 * MEMBER 테이블에서 USERS 테이블로 회원 데이터 마이그레이션
 * 실행 전 반드시 데이터 백업하세요!
 */

include 'db.php';
$connect = $db;

if (!$connect) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>🔄 MEMBER → USERS 마이그레이션</h2>";
echo "<pre>";

// Step 1: 현재 상태 확인
echo "=== 1단계: 현재 상태 확인 ===\n";

$member_check = mysqli_query($connect, "SELECT COUNT(*) as count FROM member");
$member_count = mysqli_fetch_assoc($member_check)['count'] ?? 0;
echo "MEMBER 테이블 회원 수: {$member_count}명\n";

$users_check = mysqli_query($connect, "SELECT COUNT(*) as count FROM users");
$users_count = mysqli_fetch_assoc($users_check)['count'] ?? 0;
echo "USERS 테이블 회원 수: {$users_count}명\n";

if ($member_count == 0) {
    echo "❌ MEMBER 테이블에 마이그레이션할 데이터가 없습니다.\n";
    exit;
}

// Step 2: USERS 테이블 백업
echo "\n=== 2단계: 기존 USERS 테이블 백업 ===\n";
$backup_table = "users_backup_" . date('YmdHis');
$backup_query = "CREATE TABLE {$backup_table} AS SELECT * FROM users";
if (mysqli_query($connect, $backup_query)) {
    echo "✅ USERS 테이블 백업 완료: {$backup_table}\n";
} else {
    echo "❌ 백업 실패: " . mysqli_error($connect) . "\n";
    exit;
}

// Step 3: MEMBER 테이블 구조 분석
echo "\n=== 3단계: MEMBER 테이블 구조 분석 ===\n";
$member_structure = mysqli_query($connect, "DESCRIBE member");
$member_fields = [];
while ($field = mysqli_fetch_assoc($member_structure)) {
    $member_fields[] = $field['Field'];
    echo "- {$field['Field']} ({$field['Type']})\n";
}

// Step 4: USERS 테이블 생성
echo "\n=== 4단계: USERS 테이블 생성 ===\n";
$create_users_query = "CREATE TABLE IF NOT EXISTS users (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if (mysqli_query($connect, $create_users_query)) {
    echo "✅ USERS 테이블 생성 완료\n";
} else {
    echo "❌ USERS 테이블 생성 실패: " . mysqli_error($connect) . "\n";
    exit;
}

// Step 5: 필드 매핑 설정 (MEMBER 테이블 기준)
echo "\n=== 5단계: 필드 매핑 설정 ===\n";
$field_mapping = [
    // MEMBER 필드 => USERS 필드
    'id' => 'username',        // 로그인 ID
    'pass' => 'password',      // 비밀번호
    'name' => 'name',          // 이름
    'email' => 'email',        // 이메일
    'sample6_postcode' => 'postcode',
    'sample6_address' => 'address', 
    'sample6_detailAddress' => 'detail_address',
    'sample6_extraAddress' => 'extra_address',
    'po1' => 'business_number',  // 사업자번호
    'po2' => 'business_name',    // 상호명
    'po3' => 'business_owner',   // 대표자
    'po4' => 'business_type',    // 업태
    'po5' => 'business_item',    // 업종
    'po6' => 'business_address', // 사업장주소
    'level' => 'level',          // 회원등급
    'Logincount' => 'login_count', // 로그인횟수
    'EndLogin' => 'last_login'     // 최종로그인
];

// 실제 존재하는 필드만 매핑에 포함
$actual_mapping = [];
foreach ($field_mapping as $member_field => $users_field) {
    if (in_array($member_field, $member_fields)) {
        $actual_mapping[$member_field] = $users_field;
        echo "✅ {$member_field} → {$users_field}\n";
    }
}

if (empty($actual_mapping)) {
    echo "❌ 매핑 가능한 필드가 없습니다. 수동으로 확인이 필요합니다.\n";
    exit;
}

// Step 5: 데이터 마이그레이션
echo "\n=== 5단계: 데이터 마이그레이션 ===\n";

// SELECT 쿼리 구성
$select_fields = array_keys($actual_mapping);
$select_query = "SELECT " . implode(', ', $select_fields) . " FROM member";
$member_data = mysqli_query($connect, $select_query);

if (!$member_data) {
    echo "❌ MEMBER 데이터 조회 실패: " . mysqli_error($connect) . "\n";
    exit;
}

$migrated_count = 0;
$error_count = 0;

while ($row = mysqli_fetch_assoc($member_data)) {
    // 데이터 변환
    $users_data = [];
    
    foreach ($actual_mapping as $member_field => $users_field) {
        $value = $row[$member_field];
        
        // 특수 처리: 비밀번호 해싱
        if ($users_field === 'password' && !empty($value)) {
            // 이미 해시된 비밀번호인지 확인 (password_hash는 $로 시작)
            if (strpos($value, '$') !== 0) {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
        }
        
        // username 중복 처리
        if ($users_field === 'username') {
            $check_duplicate = mysqli_query($connect, "SELECT id FROM users WHERE username = '" . mysqli_real_escape_string($connect, $value) . "'");
            if (mysqli_num_rows($check_duplicate) > 0) {
                $value = $value . '_' . time() . rand(100, 999);
                echo "⚠️  중복 username 처리: {$value}\n";
            }
        }
        
        // 날짜 형식 처리
        if ($users_field === 'last_login' && $value === '0000-00-00 00:00:00') {
            $value = NULL;
        }
        
        $users_data[$users_field] = $value;
    }
    
    // 전화번호 조합 (phone1-phone2-phone3)
    if (!empty($row['phone1']) || !empty($row['phone2']) || !empty($row['phone3'])) {
        $phone_parts = array_filter([$row['phone1'], $row['phone2'], $row['phone3']]);
        if (!empty($phone_parts)) {
            $users_data['phone'] = implode('-', $phone_parts);
        }
    }
    
    // 휴대폰이 있으면 phone 필드로 대체 (더 중요하므로)
    if (!empty($row['hendphone1']) || !empty($row['hendphone2']) || !empty($row['hendphone3'])) {
        $mobile_parts = array_filter([$row['hendphone1'], $row['hendphone2'], $row['hendphone3']]);
        if (!empty($mobile_parts)) {
            $users_data['phone'] = implode('-', $mobile_parts);
        }
    }
    
    // 필수 필드 확인 및 기본값 설정
    if (empty($users_data['username'])) {
        $users_data['username'] = 'user_' . time() . rand(1000, 9999);
    }
    if (empty($users_data['name'])) {
        $users_data['name'] = $users_data['username'];
    }
    if (empty($users_data['password'])) {
        $users_data['password'] = password_hash('temp123', PASSWORD_DEFAULT);
        echo "⚠️  임시 비밀번호 설정: temp123\n";
    }
    
    // INSERT 쿼리 실행
    $insert_fields = array_keys($users_data);
    $insert_values = array_values($users_data);
    $placeholders = str_repeat('?,', count($insert_values) - 1) . '?';
    
    $insert_query = "INSERT INTO users (" . implode(', ', $insert_fields) . ") VALUES ({$placeholders})";
    $stmt = mysqli_prepare($connect, $insert_query);
    
    if ($stmt) {
        $types = str_repeat('s', count($insert_values));
        mysqli_stmt_bind_param($stmt, $types, ...$insert_values);
        
        if (mysqli_stmt_execute($stmt)) {
            $migrated_count++;
            echo "✅ {$users_data['username']} 마이그레이션 완료\n";
        } else {
            $error_count++;
            echo "❌ {$users_data['username']} 실패: " . mysqli_error($connect) . "\n";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_count++;
        echo "❌ 준비 실패: " . mysqli_error($connect) . "\n";
    }
}

// Step 6: 결과 확인
echo "\n=== 6단계: 마이그레이션 결과 ===\n";
echo "✅ 성공: {$migrated_count}명\n";
echo "❌ 실패: {$error_count}명\n";

$final_users_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM users"))['count'];
echo "📊 최종 USERS 테이블 회원 수: {$final_users_count}명\n";

echo "\n=== 완료 ===\n";
echo "백업 테이블: {$backup_table}\n";
echo "문제 발생 시 다음 명령으로 복구:\n";
echo "DROP TABLE users; RENAME TABLE {$backup_table} TO users;\n";

echo "</pre>";

echo '<br><br>';
echo '<a href="index.php" style="background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">메인 페이지로 이동</a> ';
echo '<a href="check_tables_new.php" style="background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-left:10px;">테이블 확인</a>';
?>