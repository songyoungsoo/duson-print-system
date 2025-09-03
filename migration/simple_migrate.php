<?php
/**
 * Simple Migration: member → users 안전한 통합
 * 복잡한 백업 없이 안전하게 member 테이블 의존성 제거
 */

require_once '../db.php';

echo "===== Member → Users 간단 마이그레이션 =====\n\n";

// 1. 현재 상태 확인
$member_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM member"))['cnt'];
$users_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"))['cnt'];

echo "현재 상태:\n";
echo "- member 테이블: {$member_count}개 레코드\n";
echo "- users 테이블: {$users_count}개 레코드\n\n";

// 2. users 테이블에 member_no 컬럼 추가 (이미 있으면 무시)
$add_member_no = "ALTER TABLE users ADD COLUMN member_no MEDIUMINT(9) UNSIGNED DEFAULT NULL";
mysqli_query($db, $add_member_no); // 오류 무시

$add_level = "ALTER TABLE users ADD COLUMN level VARCHAR(10) DEFAULT '1'";
mysqli_query($db, $add_level); // 오류 무시

$add_login_count = "ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0";
mysqli_query($db, $add_login_count); // 오류 무시

$add_last_login = "ALTER TABLE users ADD COLUMN last_login DATETIME DEFAULT NULL";
mysqli_query($db, $add_last_login); // 오류 무시

echo "2. users 테이블 확장 완료\n";

// 3. member 테이블의 데이터 중 users에 없는 것만 추가
echo "3. 누락된 member 데이터 마이그레이션...\n";

$missing_query = "
    SELECT m.* 
    FROM member m 
    LEFT JOIN users u ON m.no = u.member_no 
    WHERE u.member_no IS NULL
";

$missing_result = mysqli_query($db, $missing_query);
$migrated = 0;
$errors = [];

while ($member = mysqli_fetch_assoc($missing_result)) {
    $username = !empty($member['id']) ? $member['id'] : 'user_' . $member['no'];
    
    // 중복 username 체크 및 처리
    $check_username = mysqli_query($db, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $username = 'member_' . $member['no'] . '_' . time();
    }
    
    // 비밀번호 해시화
    $password_hash = password_hash($member['pass'], PASSWORD_DEFAULT);
    
    // 전화번호 조합
    $phone_parts = array_filter([$member['phone1'], $member['phone2'], $member['phone3']]);
    $phone = implode('-', $phone_parts);
    
    // 안전한 날짜 처리
    $created_at = ($member['date'] && $member['date'] != '0000-00-00 00:00:00') 
                  ? $member['date'] 
                  : date('Y-m-d H:i:s');
    
    $last_login = ($member['EndLogin'] && $member['EndLogin'] != '0000-00-00 00:00:00') 
                  ? $member['EndLogin'] 
                  : null;
    
    $insert_query = "INSERT INTO users (
        username, password, name, email, phone,
        member_no, level, login_count, last_login, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $insert_query);
    mysqli_stmt_bind_param($stmt, "sssssiisss",
        $username,
        $password_hash, 
        $member['name'],
        $member['email'],
        $phone,
        $member['no'],
        $member['level'],
        $member['Logincount'],
        $last_login,
        $created_at
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $migrated++;
    } else {
        $errors[] = "Failed to migrate member no {$member['no']}: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
}

echo "   - {$migrated}개 레코드 마이그레이션 완료\n";
if (!empty($errors)) {
    echo "   - 오류: " . count($errors) . "개\n";
}

// 4. db.php 파일들 업데이트
echo "\n4. db.php 파일들 업데이트...\n";

$db_files = [
    '../db.php',
    '../MlangPrintAuto/db.php'
];

foreach ($db_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $new_content = str_replace('$admin_table = "member"', '$admin_table = "users"', $content);
        
        if ($content !== $new_content) {
            // 백업 생성
            copy($file, $file . '.member_backup');
            file_put_contents($file, $new_content);
            echo "   - 업데이트: " . basename($file) . "\n";
        }
    }
}

// 5. 호환성 뷰 생성 (기존 member 참조 코드 지원)
echo "\n5. 호환성 뷰 생성...\n";

$view_sql = "
CREATE OR REPLACE VIEW member_view AS
SELECT 
    member_no as no,
    username as id,
    SUBSTRING(password, 1, 20) as pass,
    name,
    SUBSTRING_INDEX(phone, '-', 1) as phone1,
    SUBSTRING_INDEX(SUBSTRING_INDEX(phone, '-', 2), '-', -1) as phone2,
    SUBSTRING_INDEX(phone, '-', -1) as phone3,
    '' as hendphone1, '' as hendphone2, '' as hendphone3,
    email,
    '' as sample6_postcode, '' as sample6_address,
    '' as sample6_detailAddress, '' as sample6_extraAddress,
    '' as po1, '' as po2, '' as po3, '' as po4,
    '' as po5, '' as po6, '' as po7,
    '' as connent,
    created_at as date,
    level,
    login_count as Logincount,
    last_login as EndLogin
FROM users
WHERE member_no IS NOT NULL
";

if (mysqli_query($db, $view_sql)) {
    echo "   - member_view 생성 완료\n";
} else {
    echo "   - 뷰 생성 실패: " . mysqli_error($db) . "\n";
}

// 6. 관리자 계정 확인
echo "\n6. 관리자 계정 확인...\n";
$admin_check = mysqli_query($db, "SELECT id FROM users WHERE username = 'admin'");
if (mysqli_num_rows($admin_check) == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_insert = "INSERT INTO users (username, password, name, email, level) 
                     VALUES ('admin', '$admin_password', '관리자', 'admin@duson.co.kr', '10')";
    if (mysqli_query($db, $admin_insert)) {
        echo "   - 관리자 계정 생성 (admin/admin123)\n";
    }
} else {
    echo "   - 관리자 계정 존재\n";
}

// 7. 최종 확인
echo "\n===== 마이그레이션 완료 =====\n";

$final_users_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"))['cnt'];
$users_with_member_no = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users WHERE member_no IS NOT NULL"))['cnt'];

echo "- users 테이블: {$final_users_count}개 레코드\n";
echo "- member에서 마이그레이션: {$users_with_member_no}개\n";
echo "- 새로 마이그레이션: {$migrated}개\n";

echo "\n✅ 마이그레이션 성공!\n";
echo "\n다음 단계:\n";
echo "1. 웹사이트에서 로그인/로그아웃 테스트\n";
echo "2. 모든 기능이 정상 작동하는지 확인\n";
echo "3. 문제없으면 member 테이블을 member_old로 이름 변경\n";

echo "\n백업 파일: *.member_backup\n";
echo "문제 발생시: 백업 파일로 복원 가능\n";
?>