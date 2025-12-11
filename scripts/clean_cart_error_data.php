<?php
/**
 * 장바구니(shop_temp) 데이터에서 PHP 에러 메시지 제거 스크립트
 * CLI에서 실행: php scripts/clean_cart_error_data.php
 */

// CLI에서만 실행
if (php_sapi_name() !== 'cli') {
    die("❌ 이 스크립트는 CLI에서만 실행 가능합니다.\n");
}

echo "🧹 장바구니 데이터 정리 스크립트\n";
echo "================================\n\n";

include __DIR__ . "/../db.php";

// shop_temp 테이블에서 에러 메시지 포함된 레코드 찾기
$query = "SELECT no, product_info FROM shop_temp
          WHERE product_info LIKE '%Notice:%' OR product_info LIKE '%Warning:%' OR product_info LIKE '%Error:%'";
$result = mysqli_query($db, $query);

if (!$result) {
    die("❌ 쿼리 실패: " . mysqli_error($db) . "\n");
}

$total = mysqli_num_rows($result);
echo "📊 에러 메시지 포함된 장바구니 항목: {$total}건\n\n";

if ($total == 0) {
    echo "✅ 정리할 데이터가 없습니다.\n";
    exit(0);
}

$cleaned = 0;
$failed = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $no = $row['no'];
    $product_info = $row['product_info'];

    // JSON 파싱
    $json_data = json_decode($product_info, true);

    if (!$json_data) {
        echo "⚠️  항목 #{$no}: JSON 파싱 실패, 건너뜀\n";
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
            strpos($value, '<b>Error</b>') !== false ||
            strpos($value, 'Undefined index') !== false
        )) {
            // 에러 메시지 제거
            $value = preg_replace('/<br \/>\s*<b>(Notice|Warning|Error)<\/b>:.*?<br \/>\s*/', '', $value);
            $value = trim($value);
            $modified = true;
            echo "  🔧 항목 #{$no}: '{$key}' 필드 정리 ('{$value}'로 설정)\n";
        }
    }

    if ($modified) {
        // 정리된 JSON으로 업데이트
        $cleaned_json = json_encode($cleaned_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $update_query = "UPDATE shop_temp SET product_info = ? WHERE no = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $cleaned_json, $no);

        if (mysqli_stmt_execute($stmt)) {
            echo "  ✅ 항목 #{$no}: 정리 완료\n";
            $cleaned++;
        } else {
            echo "  ❌ 항목 #{$no}: 업데이트 실패 - " . mysqli_error($db) . "\n";
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
