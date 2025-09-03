<?php
/**
 * Step 2: Migrate Data from member to users
 * member 테이블 데이터를 users 테이블로 마이그레이션
 */

require_once '../db.php';

echo "===== STEP 2: 데이터 마이그레이션 =====\n\n";

// 1. 현재 상태 확인
$member_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM member"))['cnt'];
$users_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"))['cnt'];

echo "현재 상태:\n";
echo "- member 테이블: {$member_count}개 레코드\n";
echo "- users 테이블: {$users_count}개 레코드\n\n";

// 2. member 테이블의 모든 데이터 조회
$query = "SELECT * FROM member ORDER BY no";
$result = mysqli_query($db, $query);

if (!$result) {
    die("ERROR: member 테이블 조회 실패 - " . mysqli_error($db));
}

$migrated = 0;
$updated = 0;
$skipped = 0;
$errors = [];

echo "마이그레이션 시작...\n";

while ($member = mysqli_fetch_assoc($result)) {
    // username 생성 (id 필드 사용)
    $username = trim($member['id']);
    if (empty($username)) {
        $username = 'user_' . $member['no']; // id가 없으면 no 기반으로 생성
    }
    
    // 이미 존재하는지 확인 (username 또는 member_no로)
    $check_query = "SELECT id, username FROM users 
                    WHERE username = ? OR member_no = ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, "si", $username, $member['no']);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // 이미 존재하면 업데이트
        $existing = mysqli_fetch_assoc($check_result);
        
        // 업데이트 쿼리
        $update_query = "UPDATE users SET 
            name = ?,
            email = ?,
            phone1 = ?, phone2 = ?, phone3 = ?,
            hendphone1 = ?, hendphone2 = ?, hendphone3 = ?,
            sample6_postcode = ?,
            sample6_address = ?,
            sample6_detailAddress = ?,
            sample6_extraAddress = ?,
            level = ?,
            login_count = ?,
            last_login = ?,
            member_no = ?
            WHERE id = ?";
            
        $update_stmt = mysqli_prepare($db, $update_query);
        
        // phone 조합
        $phone_parts = array_filter([$member['phone1'], $member['phone2'], $member['phone3']]);
        $phone = implode('-', $phone_parts);
        
        mysqli_stmt_bind_param($update_stmt, "sssssssssssssisii",
            $member['name'],
            $member['email'],
            $member['phone1'], $member['phone2'], $member['phone3'],
            $member['hendphone1'], $member['hendphone2'], $member['hendphone3'],
            $member['sample6_postcode'],
            $member['sample6_address'],
            $member['sample6_detailAddress'],
            $member['sample6_extraAddress'],
            $member['level'],
            $member['Logincount'],
            $member['EndLogin'],
            $member['no'],
            $existing['id']
        );
        
        if (mysqli_stmt_execute($update_stmt)) {
            $updated++;
        } else {
            $errors[] = "Update failed for member no {$member['no']}: " . mysqli_stmt_error($update_stmt);
        }
        mysqli_stmt_close($update_stmt);
        
    } else {
        // 새로 추가
        $insert_query = "INSERT INTO users (
            username, password, name, email,
            phone, phone1, phone2, phone3,
            hendphone1, hendphone2, hendphone3,
            sample6_postcode, sample6_address,
            sample6_detailAddress, sample6_extraAddress,
            level, login_count, last_login,
            member_no, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_stmt = mysqli_prepare($db, $insert_query);
        
        // 비밀번호 처리 (기존 평문 비밀번호를 해시화)
        $password_hash = password_hash($member['pass'], PASSWORD_DEFAULT);
        
        // phone 조합
        $phone_parts = array_filter([$member['phone1'], $member['phone2'], $member['phone3']]);
        $phone = implode('-', $phone_parts);
        
        mysqli_stmt_bind_param($insert_stmt, "ssssssssssssssssisis",
            $username,
            $password_hash,
            $member['name'],
            $member['email'],
            $phone,
            $member['phone1'], $member['phone2'], $member['phone3'],
            $member['hendphone1'], $member['hendphone2'], $member['hendphone3'],
            $member['sample6_postcode'],
            $member['sample6_address'],
            $member['sample6_detailAddress'],
            $member['sample6_extraAddress'],
            $member['level'],
            $member['Logincount'],
            $member['EndLogin'],
            $member['no'],
            $member['date']
        );
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $migrated++;
        } else {
            $errors[] = "Insert failed for member no {$member['no']}: " . mysqli_stmt_error($insert_stmt);
        }
        mysqli_stmt_close($insert_stmt);
    }
    
    mysqli_stmt_close($check_stmt);
}

// 3. 결과 출력
echo "\n===== 마이그레이션 완료 =====\n";
echo "- 신규 마이그레이션: {$migrated}개\n";
echo "- 업데이트: {$updated}개\n";
echo "- 건너뜀: {$skipped}개\n";

if (count($errors) > 0) {
    echo "\n오류 발생:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

// 4. 최종 확인
$final_users_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"))['cnt'];
echo "\n최종 users 테이블: {$final_users_count}개 레코드\n";

// 5. 관리자 계정 확인 및 생성
$admin_check = mysqli_query($db, "SELECT id FROM users WHERE username = 'admin'");
if (mysqli_num_rows($admin_check) == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_insert = "INSERT INTO users (username, password, name, email, level) 
                     VALUES ('admin', '{$admin_password}', '관리자', 'admin@duson.co.kr', '10')";
    if (mysqli_query($db, $admin_insert)) {
        echo "\n관리자 계정 생성 완료 (admin/admin123)\n";
    }
}

echo "\n다음 단계: php 03_update_references.php\n";
?>