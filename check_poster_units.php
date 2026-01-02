<?php
include "/dsp1830/www/db.php";
$connect = $db;
mysqli_set_charset($connect, 'utf8mb4');

echo "<pre>";
echo "=== 포스터 주문 단위 확인 ===\n\n";

// 최근 포스터 주문 5개 확인
$query = "SELECT no, Type, LEFT(Type_1, 500) as Type_1_preview
          FROM mlangorder_printauto
          WHERE Type = '포스터'
          ORDER BY no DESC
          LIMIT 5";

$result = mysqli_query($connect, $query);

if ($result) {
    echo "최근 포스터 주문 5개:\n\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "주문 #" . $row['no'] . " (Type: " . $row['Type'] . ")\n";

        // JSON 파싱하여 formatted_display 추출
        $data = json_decode($row['Type_1_preview'], true);
        if ($data && isset($data['formatted_display'])) {
            $formatted = $data['formatted_display'];
            // 수량 라인 찾기
            if (preg_match('/수량:\s*(.+)/', $formatted, $matches)) {
                echo "  - 수량: " . $matches[1] . "\n";
                // 단위 확인
                if (strpos($matches[1], '개') !== false) {
                    echo "  ⚠️ 잘못된 단위 '개' 발견!\n";
                } elseif (strpos($matches[1], '매') !== false) {
                    echo "  ✅ 올바른 단위 '매'\n";
                }
            }
        }
        echo "\n";
    }
} else {
    echo "❌ 쿼리 실패: " . mysqli_error($connect) . "\n";
}

echo "</pre>";
mysqli_close($connect);
?>
