<?php
/**
 * Automated Migration Runner
 * Executes migration with automatic confirmation (for safe environments)
 */

echo "============================================\n";
echo "자동 마이그레이션 실행기\n";
echo "============================================\n\n";

// 환경 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

// CLI 확인
if (php_sapi_name() !== 'cli') {
    die("❌ CLI에서만 실행 가능합니다.\n");
}

// 백업 확인
echo "⚠️  백업 파일 확인 중...\n";
$backup_dir = __DIR__;
$backup_files = glob($backup_dir . '/*_backup_*.sql');

if (count($backup_files) < 2) {
    echo "❌ 백업 파일이 충분하지 않습니다. 먼저 백업을 생성하세요.\n";
    exit(1);
}

// 최근 백업 파일 표시
rsort($backup_files);
echo "   ✓ 발견된 최근 백업:\n";
foreach (array_slice($backup_files, 0, 2) as $backup) {
    $size = filesize($backup);
    $time = date('Y-m-d H:i:s', filemtime($backup));
    echo "      - " . basename($backup) . " (" . round($size/1024, 1) . " KB, {$time})\n";
}
echo "\n";

// 데이터베이스 연결
require_once __DIR__ . '/../db.php';

if (!isset($db) || !($db instanceof mysqli)) {
    echo "❌ 데이터베이스 연결 실패\n";
    exit(1);
}

echo "✅ 데이터베이스 연결 완료\n\n";

// 마이그레이션 대상 확인
$check_query = "
    SELECT COUNT(*) as count
    FROM member m
    LEFT JOIN users u ON m.id = u.username
    WHERE u.username IS NULL
";
$result = mysqli_query($db, $check_query);
$to_migrate = mysqli_fetch_assoc($result)['count'];

echo "📊 마이그레이션 정보:\n";
echo "   - 환경: " . get_current_environment() . "\n";
echo "   - 대상: {$to_migrate}명\n\n";

if ($to_migrate === 0) {
    echo "✅ 마이그레이션할 대상이 없습니다.\n";
    exit(0);
}

echo "🚀 마이그레이션 시작...\n\n";

// 로그 파일
$log_file = __DIR__ . '/migration_auto_' . date('Ymd_His') . '.log';
$log_handle = fopen($log_file, 'w');

function log_msg($msg) {
    global $log_handle;
    echo $msg;
    fwrite($log_handle, strip_tags($msg));
}

// 트랜잭션 시작
mysqli_begin_transaction($db);

try {
    $select_query = "
        SELECT
            m.no, m.id, m.pass, m.name, m.phone1, m.phone2, m.phone3,
            m.hendphone1, m.hendphone2, m.hendphone3, m.email,
            m.sample6_postcode, m.sample6_address, m.sample6_detailAddress, m.sample6_extraAddress,
            m.po1, m.po2, m.po3, m.po4, m.po5, m.po6, m.po7,
            m.level, m.Logincount, m.EndLogin, m.date
        FROM member m
        LEFT JOIN users u ON m.id = u.username
        WHERE u.username IS NULL
        ORDER BY m.no ASC
    ";

    $result = mysqli_query($db, $select_query);
    if (!$result) {
        throw new Exception("데이터 조회 실패: " . mysqli_error($db));
    }

    $insert_query = "
        INSERT INTO users (
            username, password, name, email, phone,
            postcode, address, detail_address, extra_address,
            business_number, business_name, business_owner,
            business_type, business_item, business_address, tax_invoice_email,
            level, login_count, last_login, created_at,
            migrated_from_member, original_member_no
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($db, $insert_query);
    if (!$stmt) {
        throw new Exception("Prepared statement 실패: " . mysqli_error($db));
    }

    $counter = 0;
    $success = 0;
    $failed = 0;

    while ($member = mysqli_fetch_assoc($result)) {
        $counter++;

        try {
            $username = $member['id'];
            $password = password_hash($member['pass'], PASSWORD_BCRYPT);
            $name = $member['name'];
            $email = $member['email'];

            $phone = '';
            if (!empty($member['phone1']) && !empty($member['phone2']) && !empty($member['phone3'])) {
                $phone = $member['phone1'] . '-' . $member['phone2'] . '-' . $member['phone3'];
            } elseif (!empty($member['hendphone1']) && !empty($member['hendphone2']) && !empty($member['hendphone3'])) {
                $phone = $member['hendphone1'] . '-' . $member['hendphone2'] . '-' . $member['hendphone3'];
            }

            $postcode = $member['sample6_postcode'];
            $address = $member['sample6_address'];
            $detail_address = $member['sample6_detailAddress'];
            $extra_address = $member['sample6_extraAddress'];

            $business_number = $member['po1'] ?? null;
            $business_name = $member['po2'] ?? null;
            $business_owner = $member['po3'] ?? null;
            $business_type = $member['po4'] ?? null;
            $business_item = $member['po5'] ?? null;
            $business_address = $member['po6'] ?? null;
            $tax_invoice_email = $member['po7'] ?? null;

            $level = $member['level'];
            $login_count = (int)$member['Logincount'];

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

            $migrated_from_member = 1;
            $original_member_no = (int)$member['no'];

            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssssssssssissii",
                $username, $password, $name, $email, $phone,
                $postcode, $address, $detail_address, $extra_address,
                $business_number, $business_name, $business_owner,
                $business_type, $business_item, $business_address, $tax_invoice_email,
                $level, $login_count, $last_login, $created_at,
                $migrated_from_member, $original_member_no
            );

            if (mysqli_stmt_execute($stmt)) {
                $success++;
                $progress = round(($counter / $to_migrate) * 100, 1);
                log_msg("   ✓ [{$counter}/{$to_migrate}] ({$progress}%) {$username} ({$name})\n");
            } else {
                $failed++;
                $error = mysqli_stmt_error($stmt);
                log_msg("   ✗ [{$counter}/{$to_migrate}] {$username} 실패: {$error}\n");
            }

        } catch (Exception $e) {
            $failed++;
            log_msg("   ✗ [{$counter}/{$to_migrate}] {$member['id']} 오류: " . $e->getMessage() . "\n");
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_commit($db);

    echo "\n============================================\n";
    echo "✅ 마이그레이션 완료!\n";
    echo "============================================\n";
    echo "성공: {$success}명\n";
    echo "실패: {$failed}명\n";
    echo "로그: {$log_file}\n";
    echo "============================================\n";

} catch (Exception $e) {
    mysqli_rollback($db);
    log_msg("\n❌ 오류 발생: " . $e->getMessage() . "\n");
    log_msg("모든 변경사항이 롤백되었습니다.\n");
    fclose($log_handle);
    exit(1);
}

mysqli_close($db);
fclose($log_handle);
