<?php
/**
 * Member 데이터 마이그레이션 (웹 버전)
 * 
 * URL: http://localhost/scripts/migrate_member_web.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Member 마이그레이션</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { line-height: 1.6; }
    </style>
</head>
<body>
<pre>
<?php

echo "============================================\n";
echo "Member 데이터 마이그레이션 시작\n";
echo "시작 시간: " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n\n";

// DB 연결
require_once __DIR__ . '/../db.php';

if (!isset($db) || !($db instanceof mysqli)) {
    echo "<span class='error'>❌ 데이터베이스 연결 실패</span>\n";
    exit;
}

mysqli_set_charset($db, 'utf8mb3');
echo "<span class='success'>✅ 데이터베이스 연결 완료</span>\n\n";

// SQL 파일 읽기
$sql_file = __DIR__ . '/../sql251109/member.sql';
if (!file_exists($sql_file)) {
    echo "<span class='error'>❌ SQL 파일을 찾을 수 없습니다: {$sql_file}</span>\n";
    exit;
}

echo "<span class='info'>📂 SQL 파일 읽는 중...</span>\n";
$content = file_get_contents($sql_file);

// EUC-KR → UTF-8 변환
if (!mb_check_encoding($content, 'UTF-8')) {
    echo "<span class='info'>🔄 인코딩 변환 중 (EUC-KR → UTF-8)...</span>\n";
    $content = iconv('EUC-KR', 'UTF-8//IGNORE', $content);
}

// INSERT 문 추출 (정규식 개선)
preg_match_all("/INSERT INTO member VALUES \(([^;]+)\);/", $content, $matches);

if (empty($matches[1])) {
    echo "<span class='error'>❌ INSERT 문을 찾을 수 없습니다.</span>\n";
    exit;
}

echo "<span class='success'>✅ " . count($matches[1]) . "개의 레코드 발견</span>\n\n";

// 정상 데이터만 필터링 (no가 1~100 사이인 것만)
$filtered_matches = [];
foreach ($matches[1] as $match) {
    // 첫 번째 필드(no)가 1~100 사이인지 확인
    if (preg_match('/^(\d+),/', $match, $no_match)) {
        $no = intval($no_match[1]);
        if ($no >= 1 && $no <= 100) {
            $filtered_matches[] = $match;
        }
    }
}

echo "<span class='info'>📊 정상 데이터 필터링: " . count($filtered_matches) . "개</span>\n\n";
$matches[1] = $filtered_matches;

// 현재 member 테이블의 최대 no 확인
$result = mysqli_query($db, "SELECT MAX(no) as max_no FROM member");
$row = mysqli_fetch_assoc($result);
$start_no = ($row['max_no'] ?? 0) + 1;

echo "<span class='info'>📊 현재 member 테이블 최대 no: " . ($start_no - 1) . "</span>\n";
echo "<span class='info'>📊 새 레코드 시작 no: {$start_no}</span>\n\n";

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
        
        // 값 파싱 (정규식으로 개선)
        // 패턴: 숫자, '문자열', '문자열', ... 형식
        preg_match_all("/(?:^|,)\s*(?:'([^']*(?:''[^']*)*)'|([^,]+))/", $values_str, $value_matches);
        
        $values = [];
        for ($i = 0; $i < count($value_matches[0]); $i++) {
            if (!empty($value_matches[1][$i])) {
                // 작은따옴표로 감싸진 값
                $values[] = str_replace("''", "'", $value_matches[1][$i]);
            } else {
                // 숫자나 NULL 등
                $values[] = trim($value_matches[2][$i]);
            }
        }
        
        if (count($values) < 27) {
            echo "<span class='warning'>⚠️  레코드 {$stats['total']}: 필드 수 부족 (" . count($values) . "개), 건너뜀</span>\n";
            $stats['skipped']++;
            continue;
        }
        
        // 필드 매핑 (인덱스 0부터 시작)
        $old_no = intval($values[0]);
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
        
        // 중복 체크
        $check_sql = "SELECT no FROM member WHERE id = '{$id}'";
        $check_result = mysqli_query($db, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            echo "<span class='warning'>⏭️  레코드 {$stats['total']} (id: {$id}): 이미 존재, 건너뜀</span>\n";
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
            echo "<span class='error'>❌ 레코드 {$stats['total']} (id: {$id}) 삽입 실패: " . mysqli_error($db) . "</span>\n";
            $stats['errors']++;
            continue;
        }
        
        $stats['inserted']++;
        
        // users 테이블 연계
        $user_check_sql = "SELECT id FROM users WHERE username = '{$id}'";
        $user_result = mysqli_query($db, $user_check_sql);
        
        if (mysqli_num_rows($user_result) == 0) {
            $hashed_password = password_hash($pass, PASSWORD_BCRYPT);
            
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
                echo "<span class='success'>✅ 레코드 {$stats['total']} (id: {$id}): member + users 생성 완료</span>\n";
            } else {
                echo "<span class='warning'>⚠️  레코드 {$stats['total']} (id: {$id}): member 생성, users 생성 실패</span>\n";
            }
        } else {
            echo "<span class='success'>✅ 레코드 {$stats['total']} (id: {$id}): member 생성 완료 (users 이미 존재)</span>\n";
        }
        
        flush();
        ob_flush();
    }
    
    mysqli_commit($db);
    echo "\n<span class='success'>✅ 트랜잭션 커밋 완료</span>\n\n";
    
} catch (Exception $e) {
    mysqli_rollback($db);
    echo "\n<span class='error'>❌ 오류 발생, 롤백: " . $e->getMessage() . "</span>\n";
    exit;
}

echo "============================================\n";
echo "마이그레이션 완료\n";
echo "============================================\n";
echo "총 레코드: {$stats['total']}\n";
echo "삽입 성공: <span class='success'>{$stats['inserted']}</span>\n";
echo "건너뜀: <span class='warning'>{$stats['skipped']}</span>\n";
echo "users 생성: <span class='success'>{$stats['users_created']}</span>\n";
echo "오류: <span class='error'>{$stats['errors']}</span>\n";
echo "완료 시간: " . date('Y-m-d H:i:s') . "\n";
echo "============================================\n";

mysqli_close($db);
?>
</pre>
</body>
</html>
