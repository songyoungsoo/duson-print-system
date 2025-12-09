<?php
/**
 * 원격 서버의 mlangorder_printauto 테이블 데이터를 로컬로 동기화
 *
 * 사용법: php sync_mlangorder_from_remote.php
 * 또는 브라우저에서: http://localhost/sync_mlangorder_from_remote.php
 */

set_time_limit(300); // 5분 타임아웃

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DB 동기화</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;}.error{color:red;}.info{color:blue;}</style></head><body>";

echo "<h2>📥 원격 서버 → 로컬 DB 동기화</h2>\n";
echo "<p class='info'>원격: dsp1830.shop → 로컬: localhost</p>\n";
echo "<hr>\n";

// 환경 설정 파일 로드
require_once __DIR__ . '/config.env.php';

// 원격 DB 설정 가져오기
$remote_db_config = EnvironmentDetector::getProductionDatabaseConfig();
$remote_host = $remote_db_config['host'];
$remote_user = $remote_db_config['user'];
$remote_pass = $remote_db_config['password'];
$remote_db = $remote_db_config['database'];
$remote_charset = $remote_db_config['charset'];

// 로컬 DB 설정 가져오기
$local_db_config = get_db_config();
$local_host = $local_db_config['host'];
$local_user = $local_db_config['user'];
$local_pass = $local_db_config['password'];
$local_db = $local_db_config['database'];
$local_charset = $local_db_config['charset'];

echo "<p>🔌 원격 서버 연결 중...</p>\n";
flush();
// 원격 DB 연결
$remote_conn = @mysqli_connect($remote_host, $remote_user, $remote_pass, $remote_db);
if (!$remote_conn) {
    die("<p class='error'>❌ 원격 서버 연결 실패: " . mysqli_connect_error() . "</p></body></html>");
}
mysqli_set_charset($remote_conn, "utf8mb4");
echo "<p class='success'>✅ 원격 서버 연결 성공</p>\n";
flush();

echo "<p>🔌 로컬 DB 연결 중...</p>\n";
flush();

// 로컬 DB 연결
$local_conn = @mysqli_connect($local_host, $local_user, $local_pass, $local_db);
if (!$local_conn) {
    mysqli_close($remote_conn);
    die("<p class='error'>❌ 로컬 DB 연결 실패: " . mysqli_connect_error() . "</p></body></html>");
}
mysqli_set_charset($local_conn, "utf8mb4");
echo "<p class='success'>✅ 로컬 DB 연결 성공</p>\n";
flush();

// 원격 데이터 가져오기
echo "<p>📊 원격 데이터 조회 중...</p>\n";
flush();

$query = "SELECT * FROM mlangorder_printauto ORDER BY no DESC";
$result = mysqli_query($remote_conn, $query);

if (!$result) {
    mysqli_close($remote_conn);
    mysqli_close($local_conn);
    die("<p class='error'>❌ 원격 데이터 조회 실패: " . mysqli_error($remote_conn) . "</p></body></html>");
}

$total_rows = mysqli_num_rows($result);
echo "<p class='success'>✅ 원격 데이터 조회 완료: {$total_rows}건</p>\n";
flush();

if ($total_rows == 0) {
    echo "<p class='info'>ℹ️ 동기화할 데이터가 없습니다.</p>\n";
    mysqli_close($remote_conn);
    mysqli_close($local_conn);
    echo "</body></html>";
    exit;
}

// 로컬 테이블 백업 (선택사항)
echo "<p>💾 로컬 테이블 백업 중...</p>\n";
flush();

$backup_table = "mlangorder_printauto_backup_" . date('Ymd_His');
$backup_query = "CREATE TABLE IF NOT EXISTS `{$backup_table}` LIKE mlangorder_printauto";
if (mysqli_query($local_conn, $backup_query)) {
    $copy_query = "INSERT INTO `{$backup_table}` SELECT * FROM mlangorder_printauto";
    if (mysqli_query($local_conn, $copy_query)) {
        $backup_count = mysqli_affected_rows($local_conn);
        echo "<p class='success'>✅ 백업 완료: {$backup_count}건 → {$backup_table}</p>\n";
    }
}
flush();

// 로컬 테이블 비우기 (선택사항 - 주석 해제하면 기존 데이터 삭제)
// echo "<p>🗑️ 로컬 테이블 초기화 중...</p>\n";
// mysqli_query($local_conn, "TRUNCATE TABLE mlangorder_printauto");
// echo "<p class='success'>✅ 로컬 테이블 초기화 완료</p>\n";
// flush();

// 데이터 동기화
echo "<p>🔄 데이터 동기화 시작...</p>\n";
flush();

$inserted = 0;
$updated = 0;
$skipped = 0;
$errors = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $no = $row['no'];
    
    // 로컬에 이미 존재하는지 확인
    $check_query = "SELECT no FROM mlangorder_printauto WHERE no = ?";
    $check_stmt = mysqli_prepare($local_conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $no);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $exists = mysqli_num_rows($check_result) > 0;
    mysqli_stmt_close($check_stmt);
    
    // 필드 값 준비
    $fields = array_keys($row);
    $values = array_values($row);
    
    if ($exists) {
        // UPDATE
        $update_parts = [];
        $update_values = [];
        foreach ($fields as $field) {
            if ($field != 'no') {
                $update_parts[] = "`{$field}` = ?";
                $update_values[] = $row[$field];
            }
        }
        $update_values[] = $no; // WHERE 조건용
        
        $update_query = "UPDATE mlangorder_printauto SET " . implode(', ', $update_parts) . " WHERE no = ?";
        $update_stmt = mysqli_prepare($local_conn, $update_query);
        
        if ($update_stmt) {
            $types = str_repeat('s', count($update_values));
            mysqli_stmt_bind_param($update_stmt, $types, ...$update_values);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $updated++;
            } else {
                $errors++;
                echo "<p class='error'>⚠️ UPDATE 실패 (no={$no}): " . mysqli_error($local_conn) . "</p>\n";
            }
            mysqli_stmt_close($update_stmt);
        }
    } else {
        // INSERT
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $field_list = '`' . implode('`, `', $fields) . '`';
        
        $insert_query = "INSERT INTO mlangorder_printauto ({$field_list}) VALUES ({$placeholders})";
        $insert_stmt = mysqli_prepare($local_conn, $insert_query);
        
        if ($insert_stmt) {
            $types = str_repeat('s', count($values));
            mysqli_stmt_bind_param($insert_stmt, $types, ...$values);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $inserted++;
            } else {
                $errors++;
                echo "<p class='error'>⚠️ INSERT 실패 (no={$no}): " . mysqli_error($local_conn) . "</p>\n";
            }
            mysqli_stmt_close($insert_stmt);
        }
    }
    
    // 진행상황 표시 (100건마다)
    if (($inserted + $updated + $errors) % 100 == 0) {
        echo "<p class='info'>진행중... 삽입: {$inserted}, 업데이트: {$updated}, 오류: {$errors}</p>\n";
        flush();
    }
}

// 결과 출력
echo "<hr>\n";
echo "<h3>📊 동기화 완료</h3>\n";
echo "<ul>\n";
echo "<li class='success'>✅ 신규 삽입: {$inserted}건</li>\n";
echo "<li class='success'>✅ 업데이트: {$updated}건</li>\n";
echo "<li class='info'>ℹ️ 건너뜀: {$skipped}건</li>\n";
if ($errors > 0) {
    echo "<li class='error'>❌ 오류: {$errors}건</li>\n";
}
echo "</ul>\n";

// 연결 종료
mysqli_close($remote_conn);
mysqli_close($local_conn);

echo "<p class='success'>✅ 모든 작업 완료</p>\n";
echo "<p><a href='javascript:history.back()'>← 돌아가기</a></p>\n";
echo "</body></html>";
?>
