<?php
/**
 * 주문 데이터에서 PHP 에러 메시지 제거 스크립트
 * CLI에서 실행: php scripts/clean_error_data.php
 */

// CLI에서만 실행
if (php_sapi_name() !== 'cli') {
    die("❌ 이 스크립트는 CLI에서만 실행 가능합니다.\n");
}

echo "🧹 주문 데이터 정리 스크립트\n";
echo "================================\n\n";

include __DIR__ . "/../db.php";

// mlangorder_printauto 테이블에서 에러 메시지 포함된 레코드 찾기
$query = "SELECT no, name, Type_1 FROM mlangorder_printauto
          WHERE Type_1 LIKE '%Notice:%' OR Type_1 LIKE '%Warning:%' OR Type_1 LIKE '%Error:%'";
$result = mysqli_query($db, $query);

if (!$result) {
    die("❌ 쿼리 실패: " . mysqli_error($db) . "\n");
}

$total = mysqli_num_rows($result);
echo "📊 에러 메시지 포함된 주문: {$total}건\n\n";

if ($total == 0) {
    echo "✅ 정리할 데이터가 없습니다.\n";
    exit(0);
}

$cleaned = 0;
$failed = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $no = $row['no'];
    $type1 = $row['Type_1'];

    // JSON 파싱
    $json_data = json_decode($type1, true);

    if (!$json_data) {
        echo "⚠️  주문 #{$no}: JSON 파싱 실패, 건너뜀\n";
        $failed++;
        continue;
    }

    // 각 필드에서 에러 메시지 제거
    $cleaned_data = $json_data;
    $modified = false;

    foreach ($cleaned_data as $key => &$value) {
        if (is_string($value) && (
            strpos($value, '<b>Notice</b>') !== false ||
            strpos($value, '<b>Warning</b>') !== false ||
            strpos($value, '<b>Error</b>') !== false
        )) {
            $value = ''; // 에러 메시지를 빈 문자열로 교체
            $modified = true;
            echo "  🔧 주문 #{$no}: '{$key}' 필드 정리\n";
        }
    }

    if ($modified) {
        // 정리된 JSON으로 업데이트
        $cleaned_json = json_encode($cleaned_data, JSON_UNESCAPED_UNICODE);
        $update_query = "UPDATE mlangorder_printauto SET Type_1 = ? WHERE no = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $cleaned_json, $no);

        if (mysqli_stmt_execute($stmt)) {
            echo "  ✅ 주문 #{$no}: 정리 완료\n";
            $cleaned++;
        } else {
            echo "  ❌ 주문 #{$no}: 업데이트 실패 - " . mysqli_error($db) . "\n";
            $failed++;
        }

        mysqli_stmt_close($stmt);
    }
}

echo "\n================================\n";
echo "📊 정리 결과:\n";
echo "  - 총 발견: {$total}건\n";
echo "  - 정리 완료: {$cleaned}건\n";
echo "  - 실패: {$failed}건\n";
echo "\n✅ 정리 작업 완료!\n";

mysqli_close($db);
?>
