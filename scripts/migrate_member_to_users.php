<?php
/**
 * Member → Users 테이블 데이터 마이그레이션 스크립트
 *
 * 목적:
 * - 레거시 member 테이블 데이터를 현대적인 users 테이블로 이전
 * - 필드명 매핑 및 데이터 변환
 * - 비밀번호 bcrypt 해싱
 * - 전화번호 통합 (phone1-phone2-phone3 → phone)
 * - 사업자정보 매핑 (po1~po7 → business_*)
 *
 * 실행방법:
 * php /var/www/html/scripts/migrate_member_to_users.php
 *
 * 주의사항:
 * - 기존 users 데이터는 건너뜀 (중복 방지)
 * - 마이그레이션 전 users 테이블 백업 권장
 * - 트랜잭션 사용으로 실패 시 자동 롤백
 */

// 환경 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5분

// 데이터베이스 연결
require_once __DIR__ . '/../db.php';

echo "============================================\n";
echo "Member → Users 마이그레이션 스크립트\n";
echo "============================================\n\n";

// 통계 변수
$stats = [
    'total_members' => 0,
    'already_migrated' => 0,
    'newly_migrated' => 0,
    'failed' => 0,
    'skipped' => 0
];

// 1. 현재 상태 확인
echo "[1] 현재 데이터 확인 중...\n";

$member_count_query = "SELECT COUNT(*) as count FROM member";
$member_result = mysqli_query($db, $member_count_query);
$member_count = mysqli_fetch_assoc($member_result)['count'];
$stats['total_members'] = $member_count;

$users_count_query = "SELECT COUNT(*) as count FROM users";
$users_result = mysqli_query($db, $users_count_query);
$users_count = mysqli_fetch_assoc($users_result)['count'];

echo "   - member 테이블: {$member_count}명\n";
echo "   - users 테이블: {$users_count}명\n\n";

// 2. 마이그레이션 대상 확인
echo "[2] 마이그레이션 대상 확인 중...\n";

$check_query = "
    SELECT COUNT(*) as count
    FROM member m
    LEFT JOIN users u ON m.id = u.username
    WHERE u.username IS NULL
";
$check_result = mysqli_query($db, $check_query);
$to_migrate = mysqli_fetch_assoc($check_result)['count'];

echo "   - 마이그레이션 대상: {$to_migrate}명\n";
echo "   - 이미 마이그레이션됨: " . ($member_count - $to_migrate) . "명\n\n";

if ($to_migrate === 0) {
    echo "✅ 모든 회원이 이미 마이그레이션되었습니다.\n";
    exit(0);
}

// 사용자 확인
echo "계속하시겠습니까? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "\n❌ 마이그레이션이 취소되었습니다.\n";
    exit(0);
}

echo "\n[3] 마이그레이션 시작...\n\n";

// 3. 마이그레이션 실행
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

    while ($member = mysqli_fetch_assoc($result)) {
        $counter++;

        try {
            // 1. username (member.id → users.username)
            $username = $member['id'];

            // 2. password (평문 → bcrypt)
            // 기존 비밀번호가 짧으므로 bcrypt로 재해싱
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
                // 일반전화 없으면 핸드폰 사용
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
                echo "   ✓ [{$counter}/{$to_migrate}] {$username} ({$name})\n";
            } else {
                $error = mysqli_stmt_error($stmt);
                echo "   ✗ [{$counter}/{$to_migrate}] {$username} 실패: {$error}\n";
                $stats['failed']++;
            }

        } catch (Exception $e) {
            echo "   ✗ [{$counter}/{$to_migrate}] {$member['id']} 처리 중 오류: " . $e->getMessage() . "\n";
            $stats['failed']++;
        }
    }

    mysqli_stmt_close($stmt);

    // 트랜잭션 커밋
    mysqli_commit($db);

    echo "\n[4] 마이그레이션 완료!\n\n";

} catch (Exception $e) {
    // 오류 발생 시 롤백
    mysqli_rollback($db);
    echo "\n❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "모든 변경사항이 롤백되었습니다.\n";
    exit(1);
}

// 4. 최종 통계
echo "============================================\n";
echo "마이그레이션 결과\n";
echo "============================================\n";
echo "총 member 레코드:        {$stats['total_members']}명\n";
echo "신규 마이그레이션:        {$stats['newly_migrated']}명\n";
echo "실패:                     {$stats['failed']}명\n";
echo "============================================\n\n";

// 5. 검증
echo "[5] 마이그레이션 검증 중...\n";

$verify_query = "
    SELECT COUNT(*) as count
    FROM users
    WHERE migrated_from_member = 1
";
$verify_result = mysqli_query($db, $verify_query);
$migrated_count = mysqli_fetch_assoc($verify_result)['count'];

echo "   - users 테이블의 마이그레이션된 회원: {$migrated_count}명\n";

// 샘플 데이터 확인
echo "\n[6] 샘플 데이터 확인:\n";
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
    echo "\n   Username: {$sample['username']}\n";
    echo "   Name: {$sample['name']}\n";
    echo "   Email: {$sample['email']}\n";
    echo "   Phone: {$sample['phone']}\n";
    if ($sample['business_name']) {
        echo "   Business: {$sample['business_name']}\n";
    }
    if ($sample['tax_invoice_email']) {
        echo "   Tax Email: {$sample['tax_invoice_email']}\n";
    }
    echo "   Original member.no: {$sample['original_member_no']}\n";
}

echo "\n✅ 마이그레이션이 성공적으로 완료되었습니다!\n\n";

// 데이터베이스 연결 종료
mysqli_close($db);
