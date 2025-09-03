<?php
/**
 * Rollback Script - 마이그레이션 롤백
 * 문제 발생 시 이전 상태로 복원
 */

require_once '../db.php';

echo "===== 마이그레이션 롤백 =====\n\n";
echo "⚠️  경고: 이 스크립트는 마이그레이션을 되돌립니다!\n";
echo "계속하시겠습니까? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'yes') {
    echo "취소되었습니다.\n";
    exit;
}

echo "\n롤백 시작...\n\n";

// 1. member 뷰가 있다면 제거
echo "1. member 뷰 제거... ";
mysqli_query($db, "DROP VIEW IF EXISTS member");
echo "완료\n";

// 2. 가장 최근의 member 백업 찾기
$backup_tables = mysqli_query($db, "SHOW TABLES LIKE 'member_backup_%' ORDER BY 1 DESC LIMIT 1");
if ($backup = mysqli_fetch_array($backup_tables)) {
    $backup_table = $backup[0];
    echo "2. 백업 테이블 발견: {$backup_table}\n";
    
    // 3. member 테이블 복원
    echo "3. member 테이블 복원... ";
    
    // 기존 member 테이블이 있다면 제거
    mysqli_query($db, "DROP TABLE IF EXISTS member");
    
    // 백업에서 복원
    $restore_query = "RENAME TABLE {$backup_table} TO member";
    if (mysqli_query($db, $restore_query)) {
        echo "✓ 성공\n";
    } else {
        // 복사 방식으로 시도
        echo "\n   RENAME 실패, CREATE & INSERT 방식 시도... ";
        $create = "CREATE TABLE member LIKE {$backup_table}";
        $insert = "INSERT INTO member SELECT * FROM {$backup_table}";
        
        if (mysqli_query($db, $create) && mysqli_query($db, $insert)) {
            echo "✓ 성공\n";
        } else {
            echo "✗ 실패: " . mysqli_error($db) . "\n";
        }
    }
} else {
    // 아카이빙된 테이블에서 복원
    echo "2. 백업 테이블이 없습니다. 아카이빙된 테이블 검색...\n";
    
    $archived_tables = mysqli_query($db, "SHOW TABLES LIKE 'member_archived_%' ORDER BY 1 DESC LIMIT 1");
    if ($archived = mysqli_fetch_array($archived_tables)) {
        $archived_table = $archived[0];
        echo "   아카이빙된 테이블 발견: {$archived_table}\n";
        
        echo "3. member 테이블 복원... ";
        mysqli_query($db, "DROP TABLE IF EXISTS member");
        
        $restore_query = "RENAME TABLE {$archived_table} TO member";
        if (mysqli_query($db, $restore_query)) {
            echo "✓ 성공\n";
        } else {
            echo "✗ 실패: " . mysqli_error($db) . "\n";
        }
    } else {
        echo "✗ 복원할 백업을 찾을 수 없습니다!\n";
        exit;
    }
}

// 4. db.php 파일 복원
echo "4. db.php 파일 복원... ";
$db_files = [
    '../db.php',
    '../MlangPrintAuto/db.php',
    '../MlangPrintAuto/cadarok/db.php',
    '../MlangPrintAuto/msticker/db.php',
    '../MlangPrintAuto/NcrFlambeau/db.php',
    '../MlangPrintAuto/Poster/db.php'
];

$restored_files = 0;
foreach ($db_files as $file) {
    // 백업 파일 찾기
    $backup_pattern = $file . '.backup_*';
    $backups = glob($backup_pattern);
    
    if (!empty($backups)) {
        // 가장 최근 백업 사용
        sort($backups);
        $latest_backup = end($backups);
        
        if (copy($latest_backup, $file)) {
            $restored_files++;
        }
    }
}
echo "✓ {$restored_files}개 파일 복원\n";

// 5. users 테이블 백업 (나중을 위해)
echo "5. users 테이블 백업... ";
$users_backup = "users_rollback_" . date('Ymd_His');
$backup_query = "CREATE TABLE {$users_backup} LIKE users";
if (mysqli_query($db, $backup_query)) {
    $copy_query = "INSERT INTO {$users_backup} SELECT * FROM users";
    mysqli_query($db, $copy_query);
    $count = mysqli_affected_rows($db);
    echo "✓ {$count}개 레코드 백업 ({$users_backup})\n";
} else {
    echo "- users 백업 실패 (이미 백업이 있을 수 있음)\n";
}

// 6. 상태 확인
echo "\n===== 롤백 완료 =====\n";

$member_check = mysqli_query($db, "SELECT COUNT(*) as cnt FROM member");
if ($member_check) {
    $member_count = mysqli_fetch_assoc($member_check)['cnt'];
    echo "✓ member 테이블 복원: {$member_count}개 레코드\n";
} else {
    echo "✗ member 테이블 확인 실패\n";
}

$users_check = mysqli_query($db, "SELECT COUNT(*) as cnt FROM users");
if ($users_check) {
    $users_count = mysqli_fetch_assoc($users_check)['cnt'];
    echo "✓ users 테이블 유지: {$users_count}개 레코드\n";
}

echo "\n롤백이 완료되었습니다.\n";
echo "이전 상태로 복원되었습니다.\n";
?>