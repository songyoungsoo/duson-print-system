<?php
/**
 * roll_sticker_settings 테이블 데이터를 SQL INSERT 문으로 출력
 */
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "-- roll_sticker_settings 테이블 데이터 백업\n";
echo "-- 생성일: " . date('Y-m-d H:i:s') . "\n\n";

// 테이블 존재 확인
$check_table = $db->query("SHOW TABLES LIKE 'roll_sticker_settings'");
if (!$check_table || $check_table->num_rows == 0) {
    echo "-- 오류: roll_sticker_settings 테이블이 존재하지 않습니다.\n";
    exit;
}

// 데이터 조회
$result = $db->query("SELECT * FROM roll_sticker_settings ORDER BY setting_key");

if (!$result || $result->num_rows == 0) {
    echo "-- 데이터가 없습니다.\n";
    exit;
}

echo "-- 총 " . $result->num_rows . "개의 설정\n\n";
echo "TRUNCATE TABLE roll_sticker_settings;\n\n";

while ($row = $result->fetch_assoc()) {
    $setting_key = $db->real_escape_string($row['setting_key']);
    $setting_value = $row['setting_value'];
    $description = isset($row['description']) ? "'" . $db->real_escape_string($row['description']) . "'" : "NULL";
    
    echo "INSERT INTO roll_sticker_settings (setting_key, setting_value, description) VALUES ";
    echo "('$setting_key', $setting_value, $description);\n";
}

$db->close();
?>
