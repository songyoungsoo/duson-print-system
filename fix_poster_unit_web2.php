<?php
/**
 * 포스터 단위 수정 스크립트 (웹 서버용 - MySQL 5.x 호환)
 * 1. shop_temp.unit 기본값을 '개'에서 '매'로 변경 ✅ 완료됨
 * 2. 주문 #103941의 formatted_display에서 "10개"를 "10매"로 수정
 */

include "/dsp1830/www/db.php";
$connect = $db;
mysqli_set_charset($connect, 'utf8mb4');

echo "<pre>";
echo "=== 포스터 단위 수정 (MySQL 5.x 호환) ===\n\n";

// 1. shop_temp.unit 기본값은 이미 변경됨
echo "1. shop_temp.unit 기본값: ✅ 이미 '매'로 변경됨\n\n";

// 2. 주문 #103941 수정 (JSON 문자열 교체 방식)
echo "2. 주문 #103941의 단위를 '10개'에서 '10매'로 수정 중...\n";

// 먼저 현재 Type_1 조회
$query = "SELECT Type_1 FROM mlangorder_printauto WHERE no = 103941";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $type_1 = $row['Type_1'];
    echo "   현재 Type_1: " . substr($type_1, 0, 150) . "...\n\n";

    // JSON 디코딩
    $data = json_decode($type_1, true);
    if ($data && isset($data['formatted_display'])) {
        // formatted_display에서 "10개"를 "10매"로 교체
        $data['formatted_display'] = str_replace('10개', '10매', $data['formatted_display']);

        // JSON 재인코딩
        $new_type_1 = json_encode($data, JSON_UNESCAPED_UNICODE);

        // UPDATE 실행
        $update_query = "UPDATE mlangorder_printauto SET Type_1 = ? WHERE no = 103941";
        $stmt = mysqli_prepare($connect, $update_query);
        mysqli_stmt_bind_param($stmt, 's', $new_type_1);

        if (mysqli_stmt_execute($stmt)) {
            echo "   ✅ 주문 #103941 수정 완료\n\n";

            // 결과 확인
            $check_query = "SELECT no, Type, Type_1 FROM mlangorder_printauto WHERE no = 103941";
            $check_result = mysqli_query($connect, $check_query);
            $check_row = mysqli_fetch_assoc($check_result);

            $check_data = json_decode($check_row['Type_1'], true);
            echo "   확인:\n";
            echo "   - 주문번호: " . $check_row['no'] . "\n";
            echo "   - Type: " . $check_row['Type'] . "\n";
            echo "   - Formatted Display:\n";
            $lines = explode('\n', $check_data['formatted_display']);
            foreach ($lines as $line) {
                echo "     " . $line . "\n";
            }
        } else {
            echo "   ❌ 수정 실패: " . mysqli_stmt_error($stmt) . "\n";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "   ❌ JSON 파싱 실패\n";
    }
} else {
    echo "   ❌ 주문 #103941을 찾을 수 없습니다\n";
}

echo "\n=== 수정 완료 ===\n";
echo "</pre>";

mysqli_close($connect);
?>
