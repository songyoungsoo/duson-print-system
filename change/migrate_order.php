<?php
/**
 * MlangOrder_PrintAuto 데이터 마이그레이션
 * 구서버(dsp114.com) → 신서버(dsp1830.shop)
 */

require_once "/var/www/html/db.php";

$source_file = "/var/www/html/change/MlangOrder_PrintAuto_utf8.sql";

echo "=== MlangOrder_PrintAuto 마이그레이션 시작 ===\n";
echo "소스 파일: $source_file\n\n";

// 구서버 필드 순서 (30개)
$old_fields = [
    'no', 'Type', 'ImgFolder', 'Type_1', 'money_1', 'money_2', 'money_3', 
    'money_4', 'money_5', 'name', 'email', 'zip', 'zip1', 'zip2', 'phone', 
    'Hendphone', 'delivery', 'bizname', 'bank', 'bankname', 'cont', 'date', 
    'OrderStyle', 'ThingCate', 'pass', 'Gensu', 'Designer', 'logen_box_qty', 
    'logen_delivery_fee', 'logen_fee_type'
];

// 파일 읽기
$content = file_get_contents($source_file);
preg_match_all('/INSERT INTO MlangOrder_PrintAuto VALUES \((.+?)\);/s', $content, $matches);

$total = count($matches[1]);
echo "총 레코드 수: $total\n\n";

// 기존 데이터 백업 여부 확인
$result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto");
$row = mysqli_fetch_assoc($result);
echo "신서버 기존 데이터: {$row['cnt']}건\n";

// 테스트 모드 (처음 5건만)
$test_mode = true;
$limit = $test_mode ? 5 : $total;

echo "\n=== 테스트 모드: 처음 {$limit}건만 처리 ===\n\n";

$success = 0;
$failed = 0;

for ($i = 0; $i < min($limit, $total); $i++) {
    $values_str = $matches[1][$i];
    
    // VALUES 파싱 (CSV 형태)
    $values_str = trim($values_str);
    
    // 간단한 INSERT 생성
    $field_list = implode(', ', $old_fields);
    $sql = "INSERT INTO mlangorder_printauto ($field_list) VALUES ($values_str)";
    
    // 중복 키 처리 (IGNORE)
    $sql = str_replace("INSERT INTO", "INSERT IGNORE INTO", $sql);
    
    if (mysqli_query($db, $sql)) {
        $success++;
        echo ".";
    } else {
        $failed++;
        if ($failed <= 3) {
            echo "\n[에러] " . mysqli_error($db) . "\n";
            echo "SQL: " . substr($sql, 0, 200) . "...\n";
        }
    }
    
    if (($i + 1) % 100 == 0) {
        echo " {$i}건\n";
    }
}

echo "\n\n=== 결과 ===\n";
echo "성공: $success\n";
echo "실패: $failed\n";

if ($test_mode) {
    echo "\n⚠️ 테스트 모드입니다. 전체 마이그레이션은 \$test_mode = false로 변경 후 실행하세요.\n";
}
?>
