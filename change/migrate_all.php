<?php
/**
 * 전체 데이터 마이그레이션
 * 구서버(dsp114.com) → 신서버(dsp1830.shop)
 * 
 * 조건:
 * - mlangorder_printauto: no > 84277 이후만 (중복 제외)
 * - member: id 기준 중복 제외
 */

require_once "/var/www/html/db.php";

echo "╔══════════════════════════════════════════╗\n";
echo "║   dsp114.com → dsp1830.shop 데이터 이전   ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

// ========================================
// 1. MlangOrder_PrintAuto 마이그레이션 (no > 84277)
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. mlangorder_printauto 마이그레이션 (no > 84277)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$source_file = "/var/www/html/change/MlangOrder_PrintAuto_utf8.sql";
$old_fields = [
    'no', 'Type', 'ImgFolder', 'Type_1', 'money_1', 'money_2', 'money_3', 
    'money_4', 'money_5', 'name', 'email', 'zip', 'zip1', 'zip2', 'phone', 
    'Hendphone', 'delivery', 'bizname', 'bank', 'bankname', 'cont', 'date', 
    'OrderStyle', 'ThingCate', 'pass', 'Gensu', 'Designer', 'logen_box_qty', 
    'logen_delivery_fee', 'logen_fee_type'
];

// 신서버 기존 no 목록 가져오기
$existing_nos = [];
$result = mysqli_query($db, "SELECT no FROM mlangorder_printauto WHERE no > 84277");
while ($row = mysqli_fetch_assoc($result)) {
    $existing_nos[$row['no']] = true;
}
echo "신서버 기존 데이터 (no > 84277): " . count($existing_nos) . "건\n";

$content = file_get_contents($source_file);
preg_match_all('/INSERT INTO MlangOrder_PrintAuto VALUES \((\d+),(.+?)\);/s', $content, $matches, PREG_SET_ORDER);

$total = count($matches);
echo "구서버 전체 데이터: {$total}건\n";

$success = $skipped_range = $skipped_dup = $failed = 0;
$field_list = implode(', ', $old_fields);

foreach ($matches as $match) {
    $no = intval($match[1]);
    
    // 84277 이하는 스킵
    if ($no <= 84277) {
        $skipped_range++;
        continue;
    }
    
    // 이미 존재하면 스킵
    if (isset($existing_nos[$no])) {
        $skipped_dup++;
        continue;
    }
    
    $values_str = $no . ',' . $match[2];
    $sql = "INSERT INTO mlangorder_printauto ($field_list) VALUES ($values_str)";
    
    if (mysqli_query($db, $sql)) {
        $success++;
        if ($success % 100 == 0) echo ".";
    } else {
        $failed++;
        if ($failed <= 3) {
            echo "\n[에러] no=$no: " . mysqli_error($db) . "\n";
        }
    }
}

echo "\n\n결과:\n";
echo "  - 범위 외 스킵 (no <= 84277): $skipped_range 건\n";
echo "  - 중복 스킵: $skipped_dup 건\n";
echo "  - 신규 추가: $success 건\n";
echo "  - 실패: $failed 건\n\n";

// ========================================
// 2. member 마이그레이션 (id 중복 제외)
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "2. member 마이그레이션 (id 중복 제외)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$source_file = "/var/www/html/change/member_utf8.sql";
$member_fields = [
    'no', 'id', 'pass', 'name', 'phone1', 'phone2', 'phone3',
    'hendphone1', 'hendphone2', 'hendphone3', 'email',
    'sample6_postcode', 'sample6_address', 'sample6_detailAddress', 'sample6_extraAddress',
    'po1', 'po2', 'po3', 'po4', 'po5', 'po6', 'po7',
    'connent', 'date', 'level', 'Logincount', 'EndLogin'
];

// 신서버 기존 id 목록 가져오기
$existing_ids = [];
$result = mysqli_query($db, "SELECT id FROM member");
while ($row = mysqli_fetch_assoc($result)) {
    $existing_ids[strtolower($row['id'])] = true;
}
echo "신서버 기존 회원: " . count($existing_ids) . "명\n";

$content = file_get_contents($source_file);
preg_match_all("/INSERT INTO member VALUES \((\d+), '([^']*)',(.+?)\);/s", $content, $matches, PREG_SET_ORDER);

$total = count($matches);
echo "구서버 전체 회원: {$total}명\n";

$success = $skipped_dup = $failed = 0;
$field_list = implode(', ', $member_fields);

foreach ($matches as $match) {
    $no = intval($match[1]);
    $id = $match[2];
    
    // 이미 존재하면 스킵
    if (isset($existing_ids[strtolower($id)])) {
        $skipped_dup++;
        continue;
    }
    
    $values_str = $no . ", '" . $id . "'," . $match[3];
    $sql = "INSERT INTO member ($field_list) VALUES ($values_str)";
    
    if (mysqli_query($db, $sql)) {
        $success++;
        $existing_ids[strtolower($id)] = true; // 추가된 id 기록
        echo "  + 추가: $id\n";
    } else {
        $failed++;
        if ($failed <= 3) {
            echo "  [에러] id=$id: " . mysqli_error($db) . "\n";
        }
    }
}

echo "\n결과:\n";
echo "  - 중복 스킵: $skipped_dup 명\n";
echo "  - 신규 추가: $success 명\n";
echo "  - 실패: $failed 명\n\n";

// ========================================
// 완료
// ========================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ 마이그레이션 완료!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
?>
