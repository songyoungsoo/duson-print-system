<?php
/**
 * Member 테이블 데이터 마이그레이션 (최종 버전)
 * 
 * 기능:
 * 1. SQL 파일에서 member 데이터 읽기 (EUC-KR → UTF-8 변환)
 * 2. 현재 member 테이블 구조에 맞게 데이터 변환
 * 3. users 테이블과 연계 (member.id → users.username)
 * 4. 중복 체크 및 안전한 삽입
 * 
 * 실행: php /var/www/html/scripts/migrate_member_final.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

// CLI 전용
if (php_sapi_name() !== 'cli') {
    die("❌ CLI에서만 실행 가능합니다.\n");
}

echo "============================================\n";
echo "Member 데이터 마이그레이션 시작\n";
echo "시작 시간: " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n\n";

// DB 연결
require_once __DIR__ . '/../db.php';

if (!isset($db) || !($db instanceof mysqli)) {
    die("❌ 데이터베이스 연결 실패\n");
}

mysqli_set_charset($db, 'utf8mb3');
echo "✅ 데이터베이스 연결 완료\n\n";

// SQL 파일 읽기
$sql_file = __DIR__ . '/../sql251109/member.sql';
if (!file_exists($sql_file)) {
    die("❌ SQL 파일을 찾을 수 없습니다: {$sql_file}\n");
}

echo "📂 SQL 파일 읽는 중...\n";
$content = file_get_contents($sql_file);

// EUC-KR → UTF-8 변환
if (!mb_check_encoding($content, 'UTF-8')) {
    echo "🔄 인코딩 변환 중 (EUC-KR → UTF-8)...\n";
    $content = iconv('EUC-KR', 'UTF-8//IGNORE', $content);
}

// INSERT 문 추출
preg_match_all("/INSERT INTO member VALUES \((.*?)\);/s", $content, $matches);

if (empty($matches[1])) {
    die("❌ INSERT 문을 찾을 수 없습니다.\n");
}

echo "✅ " . count($matches[1]) . "개의 레코드 발견\n\n";

// 현재 member 테이블의 최대 no 확인
$result = mysqli_query($db, "SELECT MAX(no) as max_no FROM member");
$row = mysqli_fetch_assoc($result);
$start_no = ($row['max_no'] ?? 0) + 1;

echo "📊 현재 member 테이블 최대 no: " . ($start_no - 1) . "\n";
echo "📊 새 레코드 시작 no: {$start_no}\n\n";

// 통계
$stats = [
    'total' => 0,
    'inserted' => 0,
    'skipped' => 0,
    'users_created' => 0,
    'errors' => 0
];

// 트랜잭션 시작
mysqli_begin_transaction($db);

try {
    foreach ($matches[1] as $values_str) {
        $stats['total']++;
        
        // 값 파싱 (간단한 CSV 파싱)
        $values = str_getcsv($values_str, ',', "'");
        
        if (count($values) < 28) {
            echo "⚠️  레코드 {$stats['total']}: 필드 수 부족, 건너뜀\n";
            $stats['skipped']++;
            continue;
        }
        
        // 필드 매핑
        $old_no = $values[0];
        $id = mysqli_real_escape_string($db, trim($values[1]));
        $pass = mysqli_real_escape_string($db, trim($values[2]));
        $name = mysqli_real_escape_string($db, trim($values[3]));
        $phone1 = mysqli_real_escape_string($db, trim($values[4]));
        $phone2 = mysqli_real_escape_string($db, trim($values[5]));
        $phone3 = mysqli_real_escape_string($db, trim($values[6]));
        $hendphone1 = mysqli_real_escape_string($db, trim($values[7]));
        $hendphone2 = mysqli_real_escape_string($db, trim($values[8]));
        $hendphone3 = mysqli_real_escape_string($db, trim($values[9]));
        $email = mysqli_real_escape_string($db, trim($values[10]));
        $postcode = mysqli_real_escape_string($db, trim($values[11]));
        $address = mysqli_real_escape_string($db, trim($values[12]));
        $detail_address = mysqli_real_escape_string($db, trim($values[13]));
        $extra_address = mysqli_real_escape_string($db, trim($values[14]));
        $po1 = mysqli_real_escape_string($db, trim($values[15]));
        $po2 = mysqli_real_escape_string($db, trim($values[16]));
        $po3 = mysqli_real_escape_string($db, trim($values[17]));
        $po4 = mysqli_real_escape_string($db, trim($values[18]));
        $po5 = mysqli_real_escape_string($db, trim($values[19]));
        $po6 = mysqli_real_escape_string($db, trim($values[20]));
        $po7 = mysqli_real_escape_string($db, trim($values[21]));
        $connent = mysqli_real_escape_string($db, trim($values[22]));
        $date = trim($values[23]);
        $level = mysqli_real_escape_string($db, trim($values[24]));
        $logincount = intval($values[25]);
        $endlogin = trim($values[26]);
        
        // 중복 체크 (id 기준)
        $check_sql = "SELECT no FROM member WHERE id = '{$id}'";
        $check_result = mysqli_query($db, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            echo "⏭️  레코드 {$stats['total']} (id: {$id}): 이미 존재, 건너뜀\n";
            $stats['skipped']++;
            continue;
        }
        
        // member 테이블에 삽입
        $insert_sql = "INSERT INTO member (
            id, pass, name, 
            phone1, phone2, phone3,
            hendphone1, hendphone2, hendphone3,
            email, 
            sample6_postcode, sample6_address, sample6_detailAddress, sample6_extraAddress,
            po1, po2, po3, po4, po5, po6, po7,
            connent, date, level, Logincount, EndLogin
        ) VALUES (
            '{$id}', '{$pass}', '{$name}',
            '{$phone1}', '{$phone2}', '{$phone3}',
            '{$hendphone1}', '{$hendphone2}', '{$hendphone3}',
            '{$email}',
            '{$postcode}', '{$address}', '{$detail_address}', '{$extra_address}',
            '{$po1}', '{$po2}', '{$po3}', '{$po4}', '{$po5}', '{$po6}', '{$po7}',
            '{$connent}', '{$date}', '{$level}', {$logincount}, '{$endlogin}'
        )";
        
        if (!mysqli_query($db, $insert_sql)) {
            echo "❌ 레코드 {$stats['total']} (id: {$id}) 삽입 실패: " . mysqli_error($db) . "\n";
            $stats['errors']++;
            continue;
        }
        
        $new_member_no = mysqli_insert_id($db);
        $stats['inserted']++;
        
        // users 테이블 연계 확인
        $user_check_sql = "SELECT id FROM users WHERE username = '{$id}'";
        $user_result = mysqli_query($db, $user_check_sql);
        
        if (mysqli_num_rows($user_result) == 0) {
            // users 테이블에 없으면 생성
            // 비밀번호 해싱 (bcrypt)
            $hashed_password = password_hash($pass, PASSWORD_BCRYPT);
            
            // 전화번호 통합
            $phone = '';
            if ($hendphone1 && $hendphone2 && $hendphone3) {
                $phone = "{$hendphone1}-{$hendphone2}-{$hendphone3}";
            } elseif ($phone1 && $phone2 && $phone3) {
                $phone = "{$phone1}-{$phone2}-{$phone3}";
            }
            
            $user_insert_sql = "INSERT INTO users (
                username, password, email, name, phone, level, created_at
            ) VALUES (
                '{$id}', '{$hashed_password}', '{$email}', '{$name}', '{$phone}', '{$level}', NOW()
            )";
            
            if (mysqli_query($db, $user_insert_sql)) {
                $stats['users_created']++;
                echo "✅ 레코드 {$stats['total']} (id: {$id}): member + users 생성 완료\n";
            } else {
                echo "⚠️  레코드 {$stats['total']} (id: {$id}): member 생성, users 생성 실패\n";
            }
        } else {
            echo "✅ 레코드 {$stats['total']} (id: {$id}): member 생성 완료 (users 이미 존재)\n";
        }
    }
    
    // 커밋
    mysqli_commit($db);
    echo "\n✅ 트랜잭션 커밋 완료\n\n";
    
} catch (Exception $e) {
    mysqli_rollback($db);
    echo "\n❌ 오류 발생, 롤백: " . $e->getMessage() . "\n";
    exit(1);
}

// 결과 출력
echo "============================================\n";
echo "마이그레이션 완료\n";
echo "============================================\n";
echo "총 레코드: {$stats['total']}\n";
echo "삽입 성공: {$stats['inserted']}\n";
echo "건너뜀: {$stats['skipped']}\n";
echo "users 생성: {$stats['users_created']}\n";
echo "오류: {$stats['errors']}\n";
echo "완료 시간: " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n";

mysqli_close($db);
