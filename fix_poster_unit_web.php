<?php
/**
 * 포스터 단위 수정 스크립트 (웹 서버용)
 * 1. shop_temp.unit 기본값을 '개'에서 '매'로 변경
 * 2. 주문 #103941의 formatted_display에서 "10개"를 "10매"로 수정
 */

include "/dsp1830/www/db.php";
$connect = $db;
mysqli_set_charset($connect, 'utf8mb4');

echo "<pre>";
echo "=== 포스터 단위 수정 시작 ===\n\n";

// 1. shop_temp.unit 기본값 변경
echo "1. shop_temp.unit 기본값을 '개'에서 '매'로 변경 중...\n";
$alter_query = "ALTER TABLE shop_temp MODIFY COLUMN unit varchar(10) DEFAULT '매'";
if (mysqli_query($connect, $alter_query)) {
    echo "   ✅ shop_temp.unit 기본값 변경 완료\n\n";
} else {
    echo "   ❌ 오류: " . mysqli_error($connect) . "\n\n";
}

// 2. 주문 #103941 수정
echo "2. 주문 #103941의 단위를 '10개'에서 '10매'로 수정 중...\n";
$update_query = "UPDATE mlangorder_printauto
                 SET Type_1 = JSON_SET(Type_1, '$.formatted_display',
                   '구분: 소량포스터\\n용지: 120아트/스노우\\n규격: 국2절\\n수량: 10매\\n디자인: 인쇄만')
                 WHERE no = 103941";

if (mysqli_query($connect, $update_query)) {
    echo "   ✅ 주문 #103941 수정 완료\n\n";

    // 결과 확인
    $check_query = "SELECT no, Type, JSON_EXTRACT(Type_1, '$.formatted_display') as formatted_display
                    FROM mlangorder_printauto WHERE no = 103941";
    $result = mysqli_query($connect, $check_query);
    if ($row = mysqli_fetch_assoc($result)) {
        echo "   확인: 주문 #" . $row['no'] . "\n";
        echo "   Type: " . $row['Type'] . "\n";
        echo "   Formatted Display:\n";
        $formatted = json_decode($row['formatted_display']);
        echo "   " . str_replace('\n', "\n   ", $formatted) . "\n";
    }
} else {
    echo "   ❌ 오류: " . mysqli_error($connect) . "\n";
}

echo "\n=== 수정 완료 ===\n";
echo "</pre>";

mysqli_close($connect);
?>
