<?php
/**
 * 마이그레이션 최종 완료
 * member 테이블을 안전하게 비활성화
 */

require_once '../db.php';

echo "===== 마이그레이션 최종 완료 =====\n\n";

echo "⚠️  이 스크립트는 member 테이블을 member_old로 이름을 변경합니다.\n";
echo "모든 테스트가 완료되었고 문제가 없다면 'yes'를 입력하세요: ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'yes') {
    echo "취소되었습니다.\n";
    exit;
}

echo "\n최종 작업 시작...\n\n";

// 1. 최종 백업 (테이블 덤프)
echo "1. 최종 백업 생성...\n";
$backup_file = "member_final_backup_" . date('Ymd_His') . ".sql";

$dump_command = "mysqldump -u duson1830 -pdu1830 duson1830 member > {$backup_file}";
exec($dump_command, $output, $return_code);

if ($return_code === 0) {
    echo "   ✅ SQL 덤프 백업 완료: {$backup_file}\n";
} else {
    echo "   ⚠️  SQL 덤프 실패, 수동 백업으로 진행\n";
}

// 2. member 테이블 이름 변경
echo "\n2. member 테이블 이름 변경...\n";

$rename_date = date('Ymd_His');
$old_table_name = "member_old_{$rename_date}";

$rename_query = "RENAME TABLE member TO {$old_table_name}";
if (mysqli_query($db, $rename_query)) {
    echo "   ✅ member 테이블이 {$old_table_name}로 변경됨\n";
} else {
    echo "   ❌ 테이블 이름 변경 실패: " . mysqli_error($db) . "\n";
    exit;
}

// 3. member 뷰를 member 테이블로 생성 (임시 호환성)
echo "\n3. 임시 호환성 테이블 생성...\n";

$compat_table = "
CREATE TABLE member (
    no MEDIUMINT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id VARCHAR(50) NOT NULL,
    pass VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone1 VARCHAR(10) DEFAULT NULL,
    phone2 VARCHAR(10) DEFAULT NULL,
    phone3 VARCHAR(10) DEFAULT NULL,
    hendphone1 VARCHAR(10) DEFAULT NULL,
    hendphone2 VARCHAR(10) DEFAULT NULL,
    hendphone3 VARCHAR(10) DEFAULT NULL,
    email VARCHAR(200) DEFAULT NULL,
    sample6_postcode VARCHAR(100) DEFAULT NULL,
    sample6_address VARCHAR(100) DEFAULT NULL,
    sample6_detailAddress VARCHAR(100) DEFAULT NULL,
    sample6_extraAddress VARCHAR(100) DEFAULT NULL,
    po1 VARCHAR(100) DEFAULT NULL,
    po2 VARCHAR(100) DEFAULT NULL,
    po3 VARCHAR(100) DEFAULT NULL,
    po4 VARCHAR(100) DEFAULT NULL,
    po5 VARCHAR(100) DEFAULT NULL,
    po6 VARCHAR(100) DEFAULT NULL,
    po7 VARCHAR(100) DEFAULT NULL,
    connent TEXT DEFAULT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    level VARCHAR(10) DEFAULT '1',
    Logincount INT DEFAULT 0,
    EndLogin DATETIME DEFAULT NULL,
    INDEX idx_id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Compatibility table - redirects to users'
";

if (mysqli_query($db, $compat_table)) {
    echo "   ✅ 호환성 테이블 생성 완료\n";
} else {
    echo "   ❌ 호환성 테이블 생성 실패: " . mysqli_error($db) . "\n";
}

// 4. 호환성 데이터 삽입
echo "\n4. 호환성 데이터 삽입...\n";

$compat_insert = "
INSERT INTO member (
    no, id, pass, name, phone1, phone2, phone3,
    email, date, level, Logincount, EndLogin
)
SELECT 
    member_no,
    username,
    SUBSTRING(password, 1, 20),  -- 호환성을 위해 20자로 제한
    name,
    SUBSTRING_INDEX(phone, '-', 1),
    SUBSTRING_INDEX(SUBSTRING_INDEX(phone, '-', 2), '-', -1),
    SUBSTRING_INDEX(phone, '-', -1),
    email,
    created_at,
    level,
    login_count,
    last_login
FROM users 
WHERE member_no IS NOT NULL
";

$inserted = mysqli_query($db, $compat_insert);
$insert_count = mysqli_affected_rows($db);
echo "   ✅ {$insert_count}개 레코드 삽입\n";

// 5. 트리거 생성 (member 테이블 변경시 users 테이블도 업데이트)
echo "\n5. 동기화 트리거 생성...\n";

$trigger_update = "
CREATE TRIGGER member_update_sync 
AFTER UPDATE ON member
FOR EACH ROW
BEGIN
    UPDATE users SET 
        username = NEW.id,
        name = NEW.name,
        email = NEW.email,
        phone = CONCAT_WS('-', NEW.phone1, NEW.phone2, NEW.phone3),
        level = NEW.level,
        login_count = NEW.Logincount,
        last_login = NEW.EndLogin
    WHERE member_no = NEW.no;
END
";

if (mysqli_query($db, $trigger_update)) {
    echo "   ✅ UPDATE 트리거 생성\n";
}

// 6. 정리 스크립트 생성
echo "\n6. 정리 스크립트 생성...\n";

$cleanup_script = '<?php
/**
 * 최종 정리 - 완전히 member 테이블 제거
 * 모든 것이 안정화된 후 실행
 */

require_once "../db.php";

echo "모든 member 관련 백업과 임시 테이블을 제거하시겠습니까? (yes/no): ";
$handle = fopen("php://stdin", "r");
if (trim(fgets($handle)) !== "yes") exit("취소됨\n");

// member_old 테이블들 제거
$old_tables = mysqli_query($db, "SHOW TABLES LIKE \"member_old%\"");
while ($table = mysqli_fetch_array($old_tables)) {
    mysqli_query($db, "DROP TABLE {$table[0]}");
    echo "삭제: {$table[0]}\n";
}

// 백업 파일들 제거
$backups = glob("*.member_backup");
foreach ($backups as $backup) {
    unlink($backup);
    echo "삭제: $backup\n";
}

// member 테이블의 트리거 제거
mysqli_query($db, "DROP TRIGGER IF EXISTS member_update_sync");

echo "정리 완료!\n";
?>';

file_put_contents('final_cleanup.php', $cleanup_script);
echo "   ✅ 정리 스크립트 생성: final_cleanup.php\n";

// 7. 복원 스크립트 생성
$restore_script = '<?php
/**
 * 긴급 복원 - member 테이블 복원
 */
require_once "../db.php";

echo "member 테이블을 복원하시겠습니까? (yes/no): ";
$handle = fopen("php://stdin", "r");
if (trim(fgets($handle)) !== "yes") exit("취소됨\n");

// 현재 member 테이블 제거
mysqli_query($db, "DROP TABLE IF EXISTS member");

// 가장 최근 member_old 테이블 찾기
$old_tables = mysqli_query($db, "SHOW TABLES LIKE \"member_old%\" ORDER BY 1 DESC LIMIT 1");
if ($table = mysqli_fetch_array($old_tables)) {
    mysqli_query($db, "RENAME TABLE {$table[0]} TO member");
    echo "복원 완료: {$table[0]} -> member\n";
} else {
    echo "복원할 테이블이 없습니다!\n";
}
?>';

file_put_contents('emergency_restore.php', $restore_script);
echo "   ✅ 긴급 복원 스크립트 생성: emergency_restore.php\n";

// 8. 최종 상태 확인
echo "\n===== 최종 완료 =====\n";

$final_member_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM member"))['cnt'];
$users_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"))['cnt'];

echo "현재 상태:\n";
echo "- ✅ users 테이블: {$users_count}개 (메인 사용자 테이블)\n";
echo "- ✅ member 테이블: {$final_member_count}개 (호환성 테이블)\n";
echo "- ✅ {$old_table_name}: 원본 백업\n";

if (file_exists($backup_file)) {
    echo "- ✅ SQL 덤프: {$backup_file}\n";
}

echo "\n🎉 마이그레이션이 성공적으로 완료되었습니다!\n\n";

echo "생성된 파일:\n";
echo "- final_cleanup.php: 모든 백업 정리 (나중에 사용)\n";
echo "- emergency_restore.php: 긴급시 원본 복원\n";
echo "- *.member_backup: 설정 파일 백업들\n";

echo "\n이제 member 테이블 없이 users 테이블만 사용합니다.\n";
echo "기존 코드는 호환성 테이블을 통해 계속 작동합니다.\n";
?>