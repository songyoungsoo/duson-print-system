<?php
/**
 * Step 5: Finalize Migration
 * 마이그레이션 최종 완료 (선택사항)
 * 
 * 주의: 이 스크립트는 member 테이블을 비활성화합니다!
 * 모든 테스트가 완료된 후에만 실행하세요.
 */

require_once '../db.php';

echo "===== STEP 5: 마이그레이션 최종 완료 =====\n\n";
echo "⚠️  경고: 이 스크립트는 member 테이블을 비활성화합니다!\n";
echo "계속하시겠습니까? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$answer = trim($line);

if ($answer !== 'yes') {
    echo "취소되었습니다.\n";
    exit;
}

echo "\n진행 중...\n\n";

// 1. member 테이블 이름 변경 (삭제 대신 보관)
$rename_date = date('Ymd_His');
$archived_name = "member_archived_{$rename_date}";

echo "1. member 테이블 아카이빙... ";
$rename_query = "RENAME TABLE member TO {$archived_name}";
if (mysqli_query($db, $rename_query)) {
    echo "✓ 완료 (새 이름: {$archived_name})\n";
} else {
    echo "✗ 실패: " . mysqli_error($db) . "\n";
    echo "   (이미 아카이빙되었거나 다른 프로세스에서 사용 중일 수 있습니다)\n";
}

// 2. 뷰 생성 (호환성을 위한 임시 조치)
echo "2. 호환성 뷰 생성... ";
$create_view = "
CREATE OR REPLACE VIEW member AS
SELECT 
    member_no as no,
    username as id,
    password as pass,
    name,
    phone1, phone2, phone3,
    hendphone1, hendphone2, hendphone3,
    email,
    sample6_postcode,
    sample6_address,
    sample6_detailAddress,
    sample6_extraAddress,
    '' as po1, '' as po2, '' as po3,
    '' as po4, '' as po5, '' as po6, '' as po7,
    '' as connent,
    created_at as date,
    level,
    login_count as Logincount,
    last_login as EndLogin
FROM users
WHERE member_no IS NOT NULL
";

if (mysqli_query($db, $create_view)) {
    echo "✓ 완료\n";
} else {
    echo "✗ 실패: " . mysqli_error($db) . "\n";
}

// 3. 권한 정리
echo "3. 권한 정리... ";
mysqli_query($db, "FLUSH PRIVILEGES");
echo "✓ 완료\n";

// 4. 최종 리포트 생성
echo "\n===== 최종 마이그레이션 리포트 =====\n";

$report_file = "migration_report_" . date('Ymd_His') . ".txt";
$report_content = "마이그레이션 완료 리포트\n";
$report_content .= "========================\n";
$report_content .= "일시: " . date('Y-m-d H:i:s') . "\n\n";

// users 테이블 통계
$users_stats = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"));
$report_content .= "Users 테이블:\n";
$report_content .= "- 총 레코드: {$users_stats['cnt']}개\n\n";

// 아카이빙된 테이블
$archived_tables = mysqli_query($db, "SHOW TABLES LIKE 'member_%'");
$report_content .= "아카이빙된 테이블:\n";
while ($table = mysqli_fetch_array($archived_tables)) {
    $report_content .= "- {$table[0]}\n";
}

// 백업 테이블
$backup_tables = mysqli_query($db, "SHOW TABLES LIKE '%backup%'");
$report_content .= "\n백업 테이블:\n";
while ($table = mysqli_fetch_array($backup_tables)) {
    $count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM {$table[0]}"));
    $report_content .= "- {$table[0]}: {$count['cnt']}개 레코드\n";
}

file_put_contents($report_file, $report_content);
echo "\n리포트 생성: {$report_file}\n";

// 5. 정리 스크립트 생성
$cleanup_script = '<?php
/**
 * 정리 스크립트 - 백업 테이블 제거
 * 마이그레이션이 안정화된 후 실행
 */

require_once "../db.php";

echo "백업 테이블을 정리하시겠습니까? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== "yes") {
    echo "취소되었습니다.\n";
    exit;
}

// 백업 테이블 목록
$backup_tables = mysqli_query($db, "SHOW TABLES LIKE \"%backup%\"");
while ($table = mysqli_fetch_array($backup_tables)) {
    echo "삭제 중: {$table[0]}... ";
    if (mysqli_query($db, "DROP TABLE {$table[0]}")) {
        echo "✓\n";
    } else {
        echo "✗\n";
    }
}

// 아카이빙된 member 테이블
$archived_tables = mysqli_query($db, "SHOW TABLES LIKE \"member_archived%\"");
while ($table = mysqli_fetch_array($archived_tables)) {
    echo "삭제 중: {$table[0]}... ";
    if (mysqli_query($db, "DROP TABLE {$table[0]}")) {
        echo "✓\n";
    } else {
        echo "✗\n";
    }
}

echo "정리 완료!\n";
?>';

file_put_contents('cleanup_backups.php', $cleanup_script);
echo "정리 스크립트 생성: cleanup_backups.php\n";

echo "\n===== 마이그레이션 완료 =====\n";
echo "✓ member 테이블이 {$archived_name}로 아카이빙되었습니다.\n";
echo "✓ users 테이블이 메인 사용자 테이블이 되었습니다.\n";
echo "✓ 호환성을 위한 member 뷰가 생성되었습니다.\n";
echo "\n중요: \n";
echo "- 모든 기능을 테스트하세요.\n";
echo "- 문제가 없으면 cleanup_backups.php를 실행하여 백업을 정리할 수 있습니다.\n";
echo "- 문제가 발생하면 rollback.php를 실행하여 되돌릴 수 있습니다.\n";
?>