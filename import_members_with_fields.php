<?php
/**
 * SQL 파일에서 필드명 매칭으로 안전하게 회원 가져오기
 */

include 'db.php';
$connect = $db;

if (!$connect) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>🔄 필드명 매칭 방식으로 회원 가져오기</h2>";
echo "<pre>";

// SQL 파일 경로
$sql_file_path = "C:\\Users\\ysung\\Downloads\\member (1).sql";

// Step 1: SQL 파일 읽기
echo "=== 1단계: SQL 파일 읽기 ===\n";
if (!file_exists($sql_file_path)) {
    echo "❌ SQL 파일을 찾을 수 없습니다: {$sql_file_path}\n";
    exit;
}

$sql_content = file_get_contents($sql_file_path);
echo "✅ SQL 파일 읽기 완료 (" . number_format(strlen($sql_content)) . " bytes)\n";

// Step 2: INSERT 문 파싱
echo "\n=== 2단계: INSERT 문 파싱 ===\n";

// VALUES 부분만 추출하는 정규식
preg_match_all('/INSERT INTO member VALUES \((.*?)\);/si', $sql_content, $matches);
$value_sets = $matches[1];
echo "✅ " . count($value_sets) . "개의 INSERT 문 발견\n";

// Step 3: 필드 순서 정의 (SQL 파일의 CREATE TABLE 기준)
$field_order = [
    'no', 'id', 'pass', 'name', 
    'phone1', 'phone2', 'phone3',
    'hendphone1', 'hendphone2', 'hendphone3',
    'email', 'sample6_postcode', 'sample6_address', 'sample6_detailAddress', 'sample6_extraAddress',
    'po1', 'po2', 'po3', 'po4', 'po5', 'po6', 'po7',
    'connent', 'date', 'level', 'Logincount', 'EndLogin'
];

echo "필드 순서: " . implode(', ', $field_order) . "\n";

// Step 4: 기존 MEMBER 테이블 백업
echo "\n=== 3단계: 기존 MEMBER 테이블 백업 ===\n";
$backup_table = "member_backup_" . date('YmdHis');
$create_backup = "CREATE TABLE {$backup_table} LIKE member";
if (mysqli_query($connect, $create_backup)) {
    $copy_data = "INSERT INTO {$backup_table} SELECT * FROM member";
    if (mysqli_query($connect, $copy_data)) {
        echo "✅ MEMBER 테이블 백업 완료: {$backup_table}\n";
    }
}

// Step 5: MEMBER 테이블 초기화
echo "\n=== 4단계: MEMBER 테이블 재구성 ===\n";
mysqli_query($connect, "DELETE FROM member");
mysqli_query($connect, "ALTER TABLE member AUTO_INCREMENT = 1");
echo "✅ MEMBER 테이블 초기화 완료\n";

// Step 6: 데이터 파싱 및 삽입
echo "\n=== 5단계: 데이터 삽입 ===\n";
$success_count = 0;
$error_count = 0;

foreach ($value_sets as $index => $value_set) {
    // 값 파싱 - 쉼표로 분리하되 따옴표 안의 쉼표는 무시
    $values = [];
    $current_value = '';
    $in_quotes = false;
    $escape_next = false;
    
    for ($i = 0; $i < strlen($value_set); $i++) {
        $char = $value_set[$i];
        
        if ($escape_next) {
            $current_value .= $char;
            $escape_next = false;
            continue;
        }
        
        if ($char === '\\') {
            $escape_next = true;
            $current_value .= $char;
            continue;
        }
        
        if ($char === "'") {
            $in_quotes = !$in_quotes;
        }
        
        if ($char === ',' && !$in_quotes) {
            // 값 추가
            $values[] = trim($current_value, " \t\n\r\0\x0B'");
            $current_value = '';
        } else {
            $current_value .= $char;
        }
    }
    // 마지막 값 추가
    if ($current_value !== '') {
        $values[] = trim($current_value, " \t\n\r\0\x0B'");
    }
    
    // 필드와 값 매칭
    if (count($values) != count($field_order)) {
        echo "⚠️  레코드 #{$index}: 필드 수 불일치 (예상: " . count($field_order) . ", 실제: " . count($values) . ")\n";
        $error_count++;
        continue;
    }
    
    // 연관 배열 생성
    $data = array_combine($field_order, $values);
    
    // 필드명 명시 INSERT 쿼리 생성
    $insert_fields = [];
    $insert_values = [];
    $placeholders = [];
    
    foreach ($data as $field => $value) {
        $insert_fields[] = "`$field`";
        // NULL 값 처리
        if ($value === 'NULL' || $value === '') {
            $insert_values[] = NULL;
        } else {
            $insert_values[] = $value;
        }
        $placeholders[] = '?';
    }
    
    $insert_query = "INSERT INTO member (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = mysqli_prepare($connect, $insert_query);
    if ($stmt) {
        // 타입 문자열 생성 (모두 string으로 처리)
        $types = str_repeat('s', count($insert_values));
        
        // bind_param에 참조로 전달
        $bind_values = [];
        foreach ($insert_values as $key => $value) {
            $bind_values[] = &$insert_values[$key];
        }
        
        array_unshift($bind_values, $types);
        call_user_func_array(array($stmt, 'bind_param'), $bind_values);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_count++;
            if ($success_count <= 5 || $success_count % 50 == 0) {
                echo "✅ #{$success_count}: {$data['id']} ({$data['name']}) 삽입 성공\n";
            }
        } else {
            $error_count++;
            echo "❌ #{$index}: {$data['id']} 삽입 실패 - " . mysqli_error($connect) . "\n";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_count++;
        echo "❌ #{$index}: 쿼리 준비 실패 - " . mysqli_error($connect) . "\n";
    }
}

// Step 7: 결과 확인
echo "\n=== 6단계: 결과 확인 ===\n";
echo "✅ 성공: {$success_count}개\n";
echo "❌ 실패: {$error_count}개\n";

$final_count = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM member"))['count'];
echo "📊 최종 MEMBER 테이블: {$final_count}명\n";

// Step 8: USERS 테이블로 마이그레이션
echo "\n=== 7단계: USERS 테이블로 마이그레이션 ===\n";

// USERS 테이블이 없으면 생성
$users_exists = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_exists) == 0) {
    echo "⚠️  USERS 테이블이 없습니다. 먼저 create_users_table.php를 실행하세요.\n";
} else {
    // 마이그레이션 실행
    $member_data = mysqli_query($connect, "SELECT * FROM member ORDER BY no");
    $migrated = 0;
    $skipped = 0;
    
    while ($row = mysqli_fetch_assoc($member_data)) {
        // 중복 확인
        $check = mysqli_prepare($connect, "SELECT id FROM users WHERE username = ? OR original_member_no = ?");
        mysqli_stmt_bind_param($check, "si", $row['id'], $row['no']);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        
        if (mysqli_num_rows($result) > 0) {
            $skipped++;
            mysqli_stmt_close($check);
            continue;
        }
        mysqli_stmt_close($check);
        
        // 전화번호 조합
        $phone = '';
        if (!empty($row['hendphone1']) && !empty($row['hendphone2']) && !empty($row['hendphone3'])) {
            $phone = $row['hendphone1'] . '-' . $row['hendphone2'] . '-' . $row['hendphone3'];
        } elseif (!empty($row['phone1']) && !empty($row['phone2']) && !empty($row['phone3'])) {
            $phone = $row['phone1'] . '-' . $row['phone2'] . '-' . $row['phone3'];
        }
        
        // 비밀번호 해싱
        $password = password_hash($row['pass'], PASSWORD_DEFAULT);
        
        // USERS 테이블에 삽입
        $insert_user = "INSERT INTO users (
            username, password, name, email, phone,
            postcode, address, detail_address, extra_address,
            business_number, business_name, business_owner, business_type, business_item, business_address,
            level, login_count, last_login, original_member_no, migrated_from_member
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($connect, $insert_user);
        if ($stmt) {
            $migrated_flag = 1;
            $last_login = ($row['EndLogin'] === '0000-00-00 00:00:00') ? NULL : $row['EndLogin'];
            
            mysqli_stmt_bind_param($stmt, "ssssssssssssssssisii",
                $row['id'], $password, $row['name'], $row['email'], $phone,
                $row['sample6_postcode'], $row['sample6_address'], $row['sample6_detailAddress'], $row['sample6_extraAddress'],
                $row['po1'], $row['po2'], $row['po3'], $row['po4'], $row['po5'], $row['po6'],
                $row['level'], $row['Logincount'], $last_login, $row['no'], $migrated_flag
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $migrated++;
                if ($migrated <= 5 || $migrated % 50 == 0) {
                    echo "✅ 마이그레이션 #{$migrated}: {$row['id']} ({$row['name']})\n";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    echo "\n📊 마이그레이션 완료:\n";
    echo "✅ 성공: {$migrated}명\n";
    echo "⏭️  건너뜀 (이미 존재): {$skipped}명\n";
    
    $total_users = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as count FROM users"))['count'];
    echo "📊 최종 USERS 테이블: {$total_users}명\n";
}

echo "\n🎉 완료!\n";
echo "</pre>";

echo '<br><br>';
echo '<a href="index.php" style="background:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🏠 메인으로</a> ';
echo '<a href="check_migration_gaps.php" style="background:#17a2b8;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">📊 마이그레이션 확인</a>';
?>