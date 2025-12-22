<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='utf-8'><title>Database Setup</title></head><body>\n";
echo "<h2>채팅 시스템 데이터베이스 설정</h2>\n";

try {
    require_once __DIR__ . '/../db.php';
    echo "<p>✓ 데이터베이스 연결 성공</p>\n";

    // 1. 테이블 생성
    echo "<h3>1. 테이블 생성 중...</h3>\n";
    $create_sql = file_get_contents(__DIR__ . '/create_chat_system.sql');

    if (!$create_sql) {
        throw new Exception('create_chat_system.sql 파일을 읽을 수 없습니다.');
    }

    // SQL 문을 세미콜론으로 분리
    $statements = array_filter(
        array_map('trim', explode(';', $create_sql)),
        function($stmt) {
            return !empty($stmt) &&
                   stripos($stmt, 'SET') !== 0 &&
                   stripos($stmt, '--') !== 0;
        }
    );

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (mysqli_query($db, $statement)) {
                // 테이블 이름 추출
                preg_match('/CREATE TABLE `?(\w+)`?/i', $statement, $matches);
                if (!empty($matches[1])) {
                    echo "<p>✓ {$matches[1]} 테이블 생성 완료</p>\n";
                }
            } else {
                echo "<p>⚠️ SQL 실행 오류: " . mysqli_error($db) . "</p>\n";
            }
        }
    }

    // 2. 샘플 데이터 삽입
    echo "<h3>2. 샘플 데이터 삽입 중...</h3>\n";
    $insert_sql = file_get_contents(__DIR__ . '/insert_sample_data.sql');

    if (!$insert_sql) {
        throw new Exception('insert_sample_data.sql 파일을 읽을 수 없습니다.');
    }

    // SQL 문을 세미콜론으로 분리
    $insert_statements = array_filter(
        array_map('trim', explode(';', $insert_sql)),
        function($stmt) {
            return !empty($stmt) &&
                   stripos($stmt, 'SET') !== 0 &&
                   stripos($stmt, '--') !== 0 &&
                   stripos($stmt, 'SELECT') !== 0;  // 확인 쿼리 제외
        }
    );

    foreach ($insert_statements as $statement) {
        if (!empty($statement)) {
            if (mysqli_query($db, $statement)) {
                if (stripos($statement, 'INSERT') === 0) {
                    $affected = mysqli_affected_rows($db);
                    echo "<p>✓ {$affected}개 행 삽입 완료</p>\n";
                }
            } else {
                echo "<p>⚠️ SQL 실행 오류: " . mysqli_error($db) . "</p>\n";
            }
        }
    }

    // 3. 결과 확인
    echo "<h3>3. 설정 결과</h3>\n";

    $tables = ['chatrooms', 'chatparticipants', 'chatmessages', 'chatstaff', 'chatsettings'];
    foreach ($tables as $table) {
        $result = mysqli_query($db, "SELECT COUNT(*) as count FROM `$table`");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "<p>✓ {$table}: {$row['count']}개 데이터</p>\n";
        }
    }

    echo "<h3>✅ 설정 완료!</h3>\n";
    echo "<p><a href='admin.php'>관리자 페이지로 이동</a></p>\n";

} catch (Exception $e) {
    echo "<p style='color:red;'>✗ 오류: " . $e->getMessage() . "</p>\n";
}

echo "</body></html>";
?>
