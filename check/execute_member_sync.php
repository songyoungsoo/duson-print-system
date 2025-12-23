<?php
/**
 * 웹에서 실행할 member/users 동기화 스크립트
 * 프로덕션 서버에서 직접 실행
 */

// 보안: IP 제한 (필요시)
// $allowed_ips = ['127.0.0.1', '::1'];
// if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
//     die('Access denied');
// }

header('Content-Type: text/html; charset=utf-8');

echo "<h2>프로덕션 서버 member/users 테이블 동기화</h2>";
echo "<pre>";

// 데이터베이스 연결
include 'db.php';

if (!$db) {
    die("❌ 데이터베이스 연결 실패\n");
}

echo "✅ 데이터베이스 연결 성공\n\n";

// SQL 모드 설정
mysqli_query($db, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 0");

// 실행 확인
$confirm = $_GET['confirm'] ?? '';
if ($confirm !== 'yes') {
    echo "⚠️ 경고: 이 작업은 member와 users 테이블을 완전히 교체합니다!\n\n";
    echo "백업이 자동으로 생성되지만, 신중하게 진행하세요.\n\n";
    echo "실행하려면 URL에 ?confirm=yes를 추가하세요.\n";
    echo "예: " . $_SERVER['PHP_SELF'] . "?confirm=yes\n";
    exit;
}

echo "=== 동기화 시작 ===\n\n";

$backup_date = date('Ymd_His');

// 1. 백업 생성
echo "1. 백업 생성 중...\n";

$result = mysqli_query($db, "CREATE TABLE IF NOT EXISTS member_backup_$backup_date AS SELECT * FROM member");
if ($result) {
    $count = mysqli_query($db, "SELECT COUNT(*) as cnt FROM member_backup_$backup_date");
    $row = mysqli_fetch_assoc($count);
    echo "   ✅ member 백업: member_backup_$backup_date ({$row['cnt']}개)\n";
}

$result = mysqli_query($db, "CREATE TABLE IF NOT EXISTS users_backup_$backup_date AS SELECT * FROM users");
if ($result) {
    $count = mysqli_query($db, "SELECT COUNT(*) as cnt FROM users_backup_$backup_date");
    $row = mysqli_fetch_assoc($count);
    echo "   ✅ users 백업: users_backup_$backup_date ({$row['cnt']}개)\n";
}

// 2. SQL 파일 로드 및 실행
echo "\n2. member 테이블 교체 중...\n";

$sql_file = __DIR__ . '/sql251109/member_create_only.sql';
if (!file_exists($sql_file)) {
    die("   ❌ SQL 파일 없음: $sql_file\n");
}

// member 테이블 삭제
mysqli_query($db, "DROP TABLE IF EXISTS member");
echo "   ✅ 기존 member 테이블 삭제\n";

// SQL 파일 내용 읽기
$sql_content = file_get_contents($sql_file);

// 각 라인 단위로 SQL 실행
$lines = explode("\n", $sql_content);
$create_sql = '';
$in_create = false;
$insert_count = 0;

foreach ($lines as $line) {
    $line = trim($line);

    // 주석이나 빈 줄 무시
    if (empty($line) || $line[0] === '#') {
        continue;
    }

    // CREATE TABLE 시작
    if (strpos($line, 'CREATE TABLE member') === 0) {
        $in_create = true;
        $create_sql = $line . "\n";
        continue;
    }

    // CREATE TABLE 진행 중
    if ($in_create) {
        $create_sql .= $line . "\n";
        // ENGINE=MyISAM; 또는 ENGINE=MyISAM 으로 끝나면 CREATE TABLE 완료
        if (strpos($line, 'ENGINE=MyISAM') !== false) {
            // 세미콜론 추가 (없으면)
            if (strpos($create_sql, ';') === false) {
                $create_sql .= ";";
            }
            if (mysqli_query($db, $create_sql)) {
                echo "   ✅ member 테이블 생성 완료\n";
            } else {
                die("   ❌ 테이블 생성 실패: " . mysqli_error($db) . "\n");
            }
            $in_create = false;
            continue;
        }
        continue;
    }

    // INSERT 문 실행
    if (strpos($line, 'INSERT INTO member VALUES') === 0) {
        // 세미콜론 추가 (없으면)
        if (substr($line, -1) !== ';') {
            $line .= ';';
        }
        if (mysqli_query($db, $line)) {
            $insert_count++;
        } else {
            echo "   ⚠️ INSERT 실패: " . substr($line, 0, 80) . "...\n";
            echo "      에러: " . mysqli_error($db) . "\n";
        }
    }
}

echo "   ✅ 데이터 삽입 완료 ($insert_count개 레코드)\n";

// 레코드 수 확인
$count = mysqli_query($db, "SELECT COUNT(*) as cnt FROM member");
$row = mysqli_fetch_assoc($count);
echo "   📊 member 레코드: {$row['cnt']}개\n";

// 3. users 테이블 동기화
echo "\n3. users 테이블 동기화 중...\n";

mysqli_query($db, "DELETE FROM users");
mysqli_query($db, "ALTER TABLE users AUTO_INCREMENT = 1");
echo "   ✅ users 테이블 초기화\n";

$sync_query = "
INSERT INTO users (
    username,
    password,
    is_admin,
    name,
    email,
    phone,
    postcode,
    address,
    detail_address,
    extra_address,
    level,
    login_count,
    last_login,
    created_at,
    migrated_from_member,
    original_member_no
)
SELECT
    id as username,
    pass as password,
    CASE WHEN level = '10' THEN 1 ELSE 0 END as is_admin,
    name,
    email,
    CONCAT_WS('-', NULLIF(hendphone1, ''), NULLIF(hendphone2, ''), NULLIF(hendphone3, '')) as phone,
    sample6_postcode as postcode,
    sample6_address as address,
    sample6_detailAddress as detail_address,
    sample6_extraAddress as extra_address,
    level,
    Logincount as login_count,
    NULL as last_login,
    NOW() as created_at,
    1 as migrated_from_member,
    no as original_member_no
FROM member
WHERE id IS NOT NULL AND id != '' AND pass IS NOT NULL AND pass != ''
";

if (mysqli_query($db, $sync_query)) {
    $count = mysqli_query($db, "SELECT COUNT(*) as cnt FROM users");
    $row = mysqli_fetch_assoc($count);
    echo "   ✅ users 동기화 완료: {$row['cnt']}개\n";
} else {
    echo "   ❌ users 동기화 실패: " . mysqli_error($db) . "\n";
}

// 4. 검증
echo "\n4. 데이터 검증:\n";

$member_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM member"))['cnt'];
$users_count = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM users"))['cnt'];

echo "   📊 member: $member_count개\n";
echo "   📊 users: $users_count개\n";
echo "   " . ($member_count == $users_count ? "✅ 일치" : "⚠️ 불일치") . "\n";

// 샘플 데이터
echo "\n5. 샘플 데이터:\n";
$result = mysqli_query($db, "SELECT username, name, email, level FROM users ORDER BY id LIMIT 5");
echo "   <table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "   <tr><th>Username</th><th>Name</th><th>Email</th><th>Level</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "   <tr><td>{$row['username']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['level']}</td></tr>";
}
echo "   </table>";

mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 1");
mysqli_close($db);

echo "\n\n✅ 동기화 완료!\n";
echo "\n백업 테이블:\n";
echo "  - member_backup_$backup_date\n";
echo "  - users_backup_$backup_date\n";
echo "</pre>";
?>
