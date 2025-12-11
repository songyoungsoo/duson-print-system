<?php
/**
 * 매일 실행: dsp114.com(EUC-KR) → 로컬(UTF-8) → dsp1830.shop(UTF-8) 동기화
 * 
 * 사용법:
 *   php /var/www/html/scripts/sync_orders.php
 * 
 * Crontab (매일 새벽 2시):
 *   0 2 * * * php /var/www/html/scripts/sync_orders.php >> /var/log/order_sync.log 2>&1
 */

echo "=== 동기화 시작: " . date('Y-m-d H:i:s') . " ===\n\n";

// ========== 설정 ==========
// 원격 소스 (dsp114.com - EUC-KR, MySQL 4.0)
$source_url = "http://dsp114.com/sync/export_orders.php";
$secret_key = "SYNC_SECRET_KEY_2024";

// 로컬 DB
$local_host = "localhost";
$local_user = "dsp1830";
$local_pass = "ds701018";
$local_db = "dsp1830";

// 최종 목적지 (dsp1830.shop - UTF-8)
$dest_host = "dsp1830.shop";
$dest_user = "dsp1830";
$dest_pass = "ds701018";
$dest_db = "dsp1830";

// ========== 1단계: dsp114.com → 로컬 ==========
echo "[ 1단계: dsp114.com → 로컬 ]\n";

$local_db_conn = new mysqli($local_host, $local_user, $local_pass, $local_db);
if ($local_db_conn->connect_error) {
    die("로컬 DB 연결 실패: " . $local_db_conn->connect_error . "\n");
}
$local_db_conn->set_charset('utf8mb4');

// 로컬의 마지막 no 확인 (90000 이하 범위 - dsp114.com 데이터)
$result = $local_db_conn->query("SELECT COALESCE(MAX(no), 0) as last_no FROM mlangorder_printauto WHERE no < 90000");
$row = $result->fetch_assoc();
$last_no = $row['last_no'];
echo "  로컬 마지막 no (dsp114 범위): $last_no\n";

// 원격에서 새 데이터 가져오기
$url = "{$source_url}?key={$secret_key}&last_no={$last_no}";
$euckr_data = @file_get_contents($url);

if ($euckr_data === false) {
    echo "  원격 서버(dsp114.com) 접속 실패 - 건너뜀\n";
    $new_from_114 = 0;
} elseif (strpos($euckr_data, 'Access Denied') !== false) {
    echo "  인증 실패 - 건너뜀\n";
    $new_from_114 = 0;
} else {
    // EUC-KR → UTF-8 변환
    $utf8_data = iconv('EUC-KR', 'UTF-8//IGNORE', $euckr_data);
    
    if (!empty(trim($utf8_data))) {
        $before = $local_db_conn->query("SELECT COUNT(*) as cnt FROM mlangorder_printauto")->fetch_assoc()['cnt'];
        
        $statements = explode(";\n", $utf8_data);
        foreach ($statements as $sql) {
            $sql = trim($sql);
            if (!empty($sql) && strpos($sql, 'INSERT') === 0) {
                $local_db_conn->query($sql);
            }
        }
        
        $after = $local_db_conn->query("SELECT COUNT(*) as cnt FROM mlangorder_printauto")->fetch_assoc()['cnt'];
        $new_from_114 = $after - $before;
        echo "  dsp114.com에서 추가: {$new_from_114}개\n";
    } else {
        echo "  새 데이터 없음\n";
        $new_from_114 = 0;
    }
}

// ========== 2단계: 로컬 → dsp1830.shop ==========
echo "\n[ 2단계: 로컬 → dsp1830.shop ]\n";

$dest_db_conn = @new mysqli($dest_host, $dest_user, $dest_pass, $dest_db);
if ($dest_db_conn->connect_error) {
    echo "  dsp1830.shop 연결 실패: " . $dest_db_conn->connect_error . "\n";
    $local_db_conn->close();
    exit;
}
$dest_db_conn->set_charset('utf8mb4');

// dsp1830.shop의 마지막 no 확인
$result = $dest_db_conn->query("SELECT COALESCE(MAX(no), 0) as last_no FROM mlangorder_printauto");
$dest_last_no = $result->fetch_assoc()['last_no'];
echo "  dsp1830.shop 마지막 no: $dest_last_no\n";

// 로컬에서 dsp1830.shop에 없는 데이터 조회
$fields = "no, Type, ImgFolder, uploaded_files, Type_1, mesu, money_1, money_2, money_3, money_4, money_5, 
           name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, 
           date, OrderStyle, ThingCate, pass, Gensu, Designer";

$query = "SELECT $fields FROM mlangorder_printauto WHERE no > $dest_last_no ORDER BY no";
$result = $local_db_conn->query($query);

$inserted = 0;
$errors = 0;

if ($result && $result->num_rows > 0) {
    echo "  전송할 레코드: " . $result->num_rows . "개\n";
    
    while ($row = $result->fetch_assoc()) {
        $columns = array();
        $values = array();
        
        foreach ($row as $col => $val) {
            $columns[] = "`$col`";
            if ($val === null) {
                $values[] = "NULL";
            } else {
                $values[] = "'" . $dest_db_conn->real_escape_string($val) . "'";
            }
        }
        
        $sql = "INSERT IGNORE INTO mlangorder_printauto (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";
        
        if ($dest_db_conn->query($sql)) {
            if ($dest_db_conn->affected_rows > 0) $inserted++;
        } else {
            $errors++;
        }
    }
    
    echo "  dsp1830.shop에 추가: {$inserted}개\n";
    if ($errors > 0) echo "  오류: {$errors}개\n";
} else {
    echo "  새 데이터 없음\n";
}

// 연결 종료
$local_db_conn->close();
$dest_db_conn->close();

echo "\n=== 동기화 완료: " . date('Y-m-d H:i:s') . " ===\n";
echo "요약: dsp114.com→로컬 {$new_from_114}개, 로컬→dsp1830.shop {$inserted}개\n";
?>
