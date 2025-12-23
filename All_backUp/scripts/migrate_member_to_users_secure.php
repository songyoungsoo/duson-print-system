<?php
/**
 * Member → Users 테이블 데이터 마이그레이션 스크립트 (보안 강화 버전)
 *
 * 목적:
 * - 레거시 member 테이블 데이터를 현대적인 users 테이블로 이전
 * - 필드명 매핑 및 데이터 변환
 * - 비밀번호 bcrypt 해싱
 * - 전화번호 통합 (phone1-phone2-phone3 → phone)
 * - 사업자정보 매핑 (po1~po7 → business_*)
 *
 * 보안 강화 사항:
 * - CLI 전용 실행 제한
 * - 백업 확인 프롬프트
 * - 환경 감지 및 운영 환경 경고
 * - 데이터베이스 연결 검증
 * - 실행 로그 파일 생성
 *
 * 실행방법:
 * php /var/www/html/scripts/migrate_member_to_users_secure.php
 *
 * 주의사항:
 * - 반드시 CLI에서만 실행
 * - 실행 전 백업 필수
 * - 트랜잭션 사용으로 실패 시 자동 롤백
 */

// ============================================
// 1. 보안 검증
// ============================================

// 1.1 CLI 전용 실행 확인
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    die("❌ 이 스크립트는 명령줄(CLI)에서만 실행할 수 있습니다.\n");
}

// 환경 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5분

// 로그 파일 생성
$log_file = __DIR__ . '/migration_' . date('Ymd_His') . '.log';
$log_handle = fopen($log_file, 'w');

if (!$log_handle) {
    die("❌ 로그 파일 생성 실패: {$log_file}\n");
}

// 로그 함수
function log_message($message) {
    global $log_handle;
    echo $message;
    fwrite($log_handle, strip_tags($message));
    flush();
}

log_message("============================================\n");
log_message("Member → Users 마이그레이션 스크립트 (보안 강화)\n");
log_message("실행 시작: " . date('Y-m-d H:i:s') . "\n");
log_message("로그 파일: {$log_file}\n");
log_message("============================================\n\n");

// 1.2 데이터베이스 연결
require_once __DIR__ . '/../db.php';

// 1.3 데이터베이스 연결 검증
if (!isset($db) || !($db instanceof mysqli)) {
    log_message("❌ 데이터베이스 연결 실패: \$db 변수가 유효하지 않습니다.\n");
    fclose($log_handle);
    exit(1);
}

if (!mysqli_ping($db)) {
    log_message("❌ 데이터베이스 연결이 활성화되지 않았습니다.\n");
    fclose($log_handle);
    exit(1);
}

log_message("✅ 데이터베이스 연결 확인 완료\n\n");

// 1.4 환경 확인
$env = get_current_environment();
log_message("⚙️  현재 환경: {$env}\n");

if ($env === 'production') {
    log_message("\n⚠️  경고: 운영(PRODUCTION) 환경에서 실행 중입니다!\n");
    log_message("⚠️  운영 데이터베이스에 직접 영향을 미칩니다.\n\n");
    log_message("정말로 운영 환경에서 계속하시겠습니까? (yes/no): ");

    $env_handle = fopen("php://stdin", "r");
    $env_confirm = trim(fgets($env_handle));
    fclose($env_handle);

    log_message($env_confirm . "\n\n");

    if (strtolower($env_confirm) !== 'yes') {
        log_message("❌ 마이그레이션이 취소되었습니다.\n");
        fclose($log_handle);
        exit(0);
    }
}

// ============================================
// 2. 백업 확인
// ============================================

log_message("============================================\n");
log_message("백업 확인\n");
log_message("============================================\n\n");
log_message("⚠️  마이그레이션 전 반드시 백업이 필요합니다!\n\n");
log_message("다음 명령어로 백업을 생성하세요:\n");
log_message("  1. mysqldump -u root dsp1830 users > users_backup_" . date('Ymd_His') . ".sql\n");
log_message("  2. mysqldump -u root dsp1830 member > member_backup_" . date('Ymd_His') . ".sql\n\n");
log_message("백업이 완료되었습니까? (yes/no): ");

$backup_handle = fopen("php://stdin", "r");
$backup_confirm = trim(fgets($backup_handle));
fclose($backup_handle);

log_message($backup_confirm . "\n\n");

if (strtolower($backup_confirm) !== 'yes') {
    log_message("❌ 백업 확인이 필요합니다. 마이그레이션이 취소되었습니다.\n");
    fclose($log_handle);
    exit(0);
}

log_message("✅ 백업 확인 완료\n\n");

// ============================================
// 3. 현재 상태 확인
// ============================================

log_message("============================================\n");
log_message("현재 데이터 확인\n");
log_message("============================================\n\n");

// 통계 변수
$stats = [
    'total_members' => 0,
    'already_migrated' => 0,
    'newly_migrated' => 0,
    'failed' => 0,
    'skipped' => 0
];

$member_count_query = "SELECT COUNT(*) as count FROM member";
$member_result = mysqli_query($db, $member_count_query);

if (!$member_result) {
    log_message("❌ member 테이블 조회 실패: " . mysqli_error($db) . "\n");
    fclose($log_handle);
    exit(1);
}

$member_count = mysqli_fetch_assoc($member_result)['count'];
$stats['total_members'] = $member_count;

$users_count_query = "SELECT COUNT(*) as count FROM users";
$users_result = mysqli_query($db, $users_count_query);

if (!$users_result) {
    log_message("❌ users 테이블 조회 실패: " . mysqli_error($db) . "\n");
    fclose($log_handle);
    exit(1);
}

$users_count = mysqli_fetch_assoc($users_result)['count'];

log_message("   - member 테이블: {$member_count}명\n");
log_message("   - users 테이블: {$users_count}명\n\n");

// ============================================
// 4. 마이그레이션 대상 확인
// ============================================

log_message("============================================\n");
log_message("마이그레이션 대상 확인\n");
log_message("============================================\n\n");

$check_query = "
    SELECT COUNT(*) as count
    FROM member m
    LEFT JOIN users u ON m.id = u.username
    WHERE u.username IS NULL
";
$check_result = mysqli_query($db, $check_query);

if (!$check_result) {
    log_message("❌ 마이그레이션 대상 조회 실패: " . mysqli_error($db) . "\n");
    fclose($log_handle);
    exit(1);
}

$to_migrate = mysqli_fetch_assoc($check_result)['count'];

log_message("   - 마이그레이션 대상: {$to_migrate}명\n");
log_message("   - 이미 마이그레이션됨: " . ($member_count - $to_migrate) . "명\n\n");

if ($to_migrate === 0) {
    log_message("✅ 모든 회원이 이미 마이그레이션되었습니다.\n");
    fclose($log_handle);
    exit(0);
}

// 최종 확인
log_message("============================================\n");
log_message("최종 확인\n");
log_message("============================================\n\n");
log_message("환경: {$env}\n");
log_message("대상: {$to_migrate}명의 회원 데이터\n");
log_message("작업: member → users 테이블 마이그레이션\n\n");
log_message("계속하시겠습니까? (yes/no): ");

$final_handle = fopen("php://stdin", "r");
$final_confirm = trim(fgets($final_handle));
fclose($final_handle);

log_message($final_confirm . "\n\n");

if (strtolower($final_confirm) !== 'yes') {
    log_message("❌ 마이그레이션이 취소되었습니다.\n");
    fclose($log_handle);
    exit(0);
}

// ============================================
// 5. 마이그레이션 실행
// ============================================

log_message("============================================\n");
log_message("마이그레이션 시작\n");
log_message("============================================\n\n");

// 트랜잭션 시작
mysqli_begin_transaction($db);

try {
    // member 테이블에서 데이터 가져오기
    $select_query = "
        SELECT
            m.no,
            m.id,
            m.pass,
            m.name,
            m.phone1,
            m.phone2,
            m.phone3,
            m.hendphone1,
            m.hendphone2,
            m.hendphone3,
            m.email,
            m.sample6_postcode,
            m.sample6_address,
            m.sample6_detailAddress,
            m.sample6_extraAddress,
            m.po1,
            m.po2,
            m.po3,
            m.po4,
            m.po5,
            m.po6,
            m.po7,
            m.level,
            m.Logincount,
            m.EndLogin,
            m.date
        FROM member m
        LEFT JOIN users u ON m.id = u.username
        WHERE u.username IS NULL
        ORDER BY m.no ASC
    ";

    $result = mysqli_query($db, $select_query);

    if (!$result) {
        throw new Exception("데이터 조회 실패: " . mysqli_error($db));
    }

    // Prepared statement 준비
    $insert_query = "
        INSERT INTO users (
            username,
            password,
            name,
            email,
            phone,
            postcode,
            address,
            detail_address,
            extra_address,
            business_number,
            business_name,
            business_owner,
            business_type,
            business_item,
            business_address,
            tax_invoice_email,
            level,
            login_count,
            last_login,
            created_at,
            migrated_from_member,
            original_member_no
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($db, $insert_query);

    if (!$stmt) {
        throw new Exception("Prepared statement 생성 실패: " . mysqli_error($db));
    }

    $counter = 0;
    $start_time = microtime(true);

    while ($member = mysqli_fetch_assoc($result)) {
        $counter++;

        try {
            // 1. username (member.id → users.username)
            $username = $member['id'];

            // 2. password (평문 → bcrypt)
            $password = password_hash($member['pass'], PASSWORD_BCRYPT);

            // 3. name
            $name = $member['name'];

            // 4. email
            $email = $member['email'];

            // 5. phone (phone1-phone2-phone3 통합)
            $phone = '';
            if (!empty($member['phone1']) && !empty($member['phone2']) && !empty($member['phone3'])) {
                $phone = $member['phone1'] . '-' . $member['phone2'] . '-' . $member['phone3'];
            } elseif (!empty($member['hendphone1']) && !empty($member['hendphone2']) && !empty($member['hendphone3'])) {
                $phone = $member['hendphone1'] . '-' . $member['hendphone2'] . '-' . $member['hendphone3'];
            }

            // 6. 주소 정보
            $postcode = $member['sample6_postcode'];
            $address = $member['sample6_address'];
            $detail_address = $member['sample6_detailAddress'];
            $extra_address = $member['sample6_extraAddress'];

            // 7. 사업자 정보 (po1~po7 → business_*)
            $business_number = $member['po1'] ?? null;
            $business_name = $member['po2'] ?? null;
            $business_owner = $member['po3'] ?? null;
            $business_type = $member['po4'] ?? null;
            $business_item = $member['po5'] ?? null;
            $business_address = $member['po6'] ?? null;
            $tax_invoice_email = $member['po7'] ?? null;

            // 8. 기타 정보
            $level = $member['level'];
            $login_count = (int)$member['Logincount'];

            // 9. 날짜 변환
            $last_login = null;
            if ($member['EndLogin'] && $member['EndLogin'] !== '1970-01-01 00:00:01') {
                $last_login = $member['EndLogin'];
            }

            $created_at = null;
            if ($member['date'] && $member['date'] !== '1970-01-01 00:00:01') {
                $created_at = $member['date'];
            } else {
                $created_at = date('Y-m-d H:i:s');
            }

            // 10. 마이그레이션 추적 정보
            $migrated_from_member = 1;
            $original_member_no = (int)$member['no'];

            // Bind parameters
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssssssssssississi",
                $username,
                $password,
                $name,
                $email,
                $phone,
                $postcode,
                $address,
                $detail_address,
                $extra_address,
                $business_number,
                $business_name,
                $business_owner,
                $business_type,
                $business_item,
                $business_address,
                $tax_invoice_email,
                $level,
                $login_count,
                $last_login,
                $created_at,
                $migrated_from_member,
                $original_member_no
            );

            // Execute
            if (mysqli_stmt_execute($stmt)) {
                $stats['newly_migrated']++;
                $progress = round(($counter / $to_migrate) * 100, 1);
                log_message("   ✓ [{$counter}/{$to_migrate}] ({$progress}%) {$username} ({$name})\n");
            } else {
                $error = mysqli_stmt_error($stmt);
                log_message("   ✗ [{$counter}/{$to_migrate}] {$username} 실패: {$error}\n");
                $stats['failed']++;
            }

        } catch (Exception $e) {
            log_message("   ✗ [{$counter}/{$to_migrate}] {$member['id']} 처리 중 오류: " . $e->getMessage() . "\n");
            $stats['failed']++;
        }
    }

    mysqli_stmt_close($stmt);

    // 트랜잭션 커밋
    mysqli_commit($db);

    $end_time = microtime(true);
    $duration = round($end_time - $start_time, 2);

    log_message("\n✅ 마이그레이션 완료! (실행 시간: {$duration}초)\n\n");

} catch (Exception $e) {
    // 오류 발생 시 롤백
    mysqli_rollback($db);
    log_message("\n❌ 오류 발생: " . $e->getMessage() . "\n");
    log_message("모든 변경사항이 롤백되었습니다.\n");
    fclose($log_handle);
    exit(1);
}

// ============================================
// 6. 최종 통계
// ============================================

log_message("============================================\n");
log_message("마이그레이션 결과\n");
log_message("============================================\n");
log_message("총 member 레코드:        {$stats['total_members']}명\n");
log_message("신규 마이그레이션:        {$stats['newly_migrated']}명\n");
log_message("실패:                     {$stats['failed']}명\n");
log_message("============================================\n\n");

// ============================================
// 7. 검증
// ============================================

log_message("============================================\n");
log_message("마이그레이션 검증\n");
log_message("============================================\n\n");

$verify_query = "
    SELECT COUNT(*) as count
    FROM users
    WHERE migrated_from_member = 1
";
$verify_result = mysqli_query($db, $verify_query);
$migrated_count = mysqli_fetch_assoc($verify_result)['count'];

log_message("   - users 테이블의 마이그레이션된 회원: {$migrated_count}명\n\n");

// 샘플 데이터 확인
log_message("샘플 데이터 확인 (최근 5개):\n");
log_message("----------------------------------------\n");

$sample_query = "
    SELECT
        u.username,
        u.name,
        u.email,
        u.phone,
        u.business_name,
        u.tax_invoice_email,
        u.original_member_no
    FROM users u
    WHERE u.migrated_from_member = 1
    ORDER BY u.id DESC
    LIMIT 5
";

$sample_result = mysqli_query($db, $sample_query);
while ($sample = mysqli_fetch_assoc($sample_result)) {
    log_message("\n   Username: {$sample['username']}\n");
    log_message("   Name: {$sample['name']}\n");
    log_message("   Email: {$sample['email']}\n");
    log_message("   Phone: {$sample['phone']}\n");
    if ($sample['business_name']) {
        log_message("   Business: {$sample['business_name']}\n");
    }
    if ($sample['tax_invoice_email']) {
        log_message("   Tax Email: {$sample['tax_invoice_email']}\n");
    }
    log_message("   Original member.no: {$sample['original_member_no']}\n");
}

log_message("\n============================================\n");
log_message("✅ 마이그레이션이 성공적으로 완료되었습니다!\n");
log_message("실행 종료: " . date('Y-m-d H:i:s') . "\n");
log_message("로그 파일: {$log_file}\n");
log_message("============================================\n");

// 데이터베이스 연결 종료
mysqli_close($db);

// 로그 파일 닫기
fclose($log_handle);

echo "\n📄 전체 로그가 저장되었습니다: {$log_file}\n\n";
