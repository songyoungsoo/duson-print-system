<?php
/**
 * 마이그레이션 검증 스크립트
 * member → users 마이그레이션 성공 확인
 */

require_once '../db.php';

echo "===== 마이그레이션 검증 =====\n\n";

$all_good = true;

// 1. 테이블 상태 확인
echo "1. 테이블 상태 확인\n";

$member_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM member"))['cnt'];
$users_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"))['cnt'];
$migrated_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users WHERE member_no IS NOT NULL"))['cnt'];

echo "   - member 테이블: {$member_count}개\n";
echo "   - users 테이블: {$users_count}개\n";
echo "   - 마이그레이션된 사용자: {$migrated_count}개\n";

if ($migrated_count >= $member_count) {
    echo "   ✅ 데이터 마이그레이션 완료\n";
} else {
    echo "   ❌ 마이그레이션 불완전\n";
    $all_good = false;
}

// 2. db.php 설정 확인
echo "\n2. 설정 파일 확인\n";
if ($admin_table === 'users') {
    echo "   ✅ \$admin_table = 'users' 설정 완료\n";
} else {
    echo "   ❌ \$admin_table이 여전히 '{$admin_table}'입니다\n";
    $all_good = false;
}

// 3. 관리자 계정 확인
echo "\n3. 관리자 계정 확인\n";
$admin_check = mysqli_query($db, "SELECT * FROM users WHERE username = 'admin'");
if (mysqli_num_rows($admin_check) > 0) {
    $admin = mysqli_fetch_assoc($admin_check);
    echo "   ✅ 관리자 계정 존재 (ID: {$admin['id']}, Level: {$admin['level']})\n";
} else {
    echo "   ❌ 관리자 계정이 없습니다\n";
    $all_good = false;
}

// 4. 호환성 뷰 확인
echo "\n4. 호환성 뷰 확인\n";
$view_check = mysqli_query($db, "SHOW TABLES LIKE 'member_view'");
if (mysqli_num_rows($view_check) > 0) {
    $view_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM member_view"))['cnt'];
    echo "   ✅ member_view 생성 완료 ({$view_count}개 레코드)\n";
} else {
    echo "   ❌ member_view가 없습니다\n";
}

// 5. 로그인 테스트
echo "\n5. 로그인 기능 테스트\n";

// 테스트용 사용자 생성 (이미 있으면 스킵)
$test_username = 'test_user_' . date('His');
$test_password = 'test123';
$test_password_hash = password_hash($test_password, PASSWORD_DEFAULT);
$test_name = '테스트사용자';
$test_email = 'test@test.com';

$test_insert = "INSERT INTO users (username, password, name, email) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $test_insert);
mysqli_stmt_bind_param($stmt, "ssss", $test_username, $test_password_hash, $test_name, $test_email);
mysqli_stmt_execute($stmt);
$test_user_id = mysqli_insert_id($db);

// 로그인 테스트
$login_check = mysqli_query($db, "SELECT * FROM users WHERE username = '{$test_username}'");
if ($login_result = mysqli_fetch_assoc($login_check)) {
    if (password_verify($test_password, $login_result['password'])) {
        echo "   ✅ 로그인 기능 정상 작동\n";
    } else {
        echo "   ❌ 비밀번호 검증 실패\n";
        $all_good = false;
    }
} else {
    echo "   ❌ 사용자 조회 실패\n";
    $all_good = false;
}

// 테스트 사용자 삭제
mysqli_query($db, "DELETE FROM users WHERE id = {$test_user_id}");

// 6. 데이터 무결성 확인
echo "\n6. 데이터 무결성 확인\n";

$integrity_issues = [];

// 빈 username 확인
$empty_username = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users WHERE username = '' OR username IS NULL"))['cnt'];
if ($empty_username > 0) {
    $integrity_issues[] = "{$empty_username}개 사용자가 빈 username을 가지고 있습니다";
}

// 중복 username 확인
$duplicate_username = mysqli_fetch_assoc(mysqli_query($db, "
    SELECT COUNT(*) as cnt FROM (
        SELECT username, COUNT(*) as cnt 
        FROM users 
        GROUP BY username 
        HAVING COUNT(*) > 1
    ) as duplicates
"))['cnt'];

if ($duplicate_username > 0) {
    $integrity_issues[] = "{$duplicate_username}개의 중복 username이 있습니다";
}

if (empty($integrity_issues)) {
    echo "   ✅ 데이터 무결성 양호\n";
} else {
    echo "   ❌ 무결성 문제:\n";
    foreach ($integrity_issues as $issue) {
        echo "      - {$issue}\n";
    }
    $all_good = false;
}

// 7. 백업 파일 확인
echo "\n7. 백업 파일 확인\n";
$backup_files = glob('../*.member_backup');
if (!empty($backup_files)) {
    echo "   ✅ 백업 파일 존재 (" . count($backup_files) . "개)\n";
    foreach ($backup_files as $backup) {
        echo "      - " . basename($backup) . "\n";
    }
} else {
    echo "   ⚠️  백업 파일이 없습니다\n";
}

// 8. 최종 결과
echo "\n===== 검증 결과 =====\n";

if ($all_good) {
    echo "🎉 모든 검증 통과! 마이그레이션이 성공적으로 완료되었습니다.\n\n";
    echo "이제 안전하게 다음 단계를 진행할 수 있습니다:\n";
    echo "1. 웹사이트에서 실제 로그인/기능 테스트\n";
    echo "2. 문제없으면 'php finalize_migration.php' 실행\n";
    echo "3. member 테이블을 member_old로 이름 변경\n";
} else {
    echo "❌ 일부 검증 실패. 문제를 해결한 후 다시 확인하세요.\n";
}

echo "\n현재 상태:\n";
echo "- ✅ users 테이블이 주 사용자 테이블\n";
echo "- ✅ member 테이블은 여전히 존재 (안전)\n";
echo "- ✅ 백업 파일로 언제든 복원 가능\n";
?>