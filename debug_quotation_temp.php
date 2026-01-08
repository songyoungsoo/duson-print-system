<?php
/**
 * quotation_temp 데이터 디버깅 스크립트
 * 스테이징 서버에서 실제 데이터를 확인
 */
session_start();
require_once '/var/www/html/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/ProductSpecFormatter.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>quotation_temp Debug</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
<h1>quotation_temp 디버깅</h1>

<?php
$sessionId = session_id();
echo "<p><strong>Session ID:</strong> $sessionId</p>";

// quotation_temp 데이터 조회
$query = "SELECT * FROM quotation_temp WHERE session_id = ? ORDER BY regdate DESC LIMIT 10";
$stmt = mysqli_prepare($db, $query);

if (!$stmt) {
    echo "<p class='error'>❌ 쿼리 준비 실패: " . mysqli_error($db) . "</p>";
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $sessionId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
mysqli_stmt_close($stmt);

echo "<h2>quotation_temp 데이터 (" . count($items) . "개 품목)</h2>";

if (count($items) == 0) {
    echo "<p class='warning'>⚠️ quotation_temp에 데이터가 없습니다.</p>";
    echo "<p>테스트 데이터를 추가하려면 계산기 모달에서 품목을 추가하세요.</p>";
} else {
    foreach ($items as $index => $item) {
        echo "<h3>품목 #" . ($index + 1) . " (ID: {$item['id']})</h3>";

        echo "<table>";
        echo "<tr><th>필드</th><th>값</th><th>isset</th><th>empty</th></tr>";

        $fields = ['product_type', 'product_name', 'mesu', 'MY_amount', 'quantity_display', 'data_version', 'st_price', 'st_price_vat'];

        foreach ($fields as $field) {
            $value = $item[$field] ?? 'NULL';
            $isset_check = isset($item[$field]) ? '✅ YES' : '❌ NO';
            $empty_check = empty($item[$field]) ? '⚠️ YES' : '✅ NO';

            if ($field == 'quantity_display' && empty($item[$field])) {
                $value = "<span class='error'>$value</span>";
            } elseif ($field == 'quantity_display' && !empty($item[$field])) {
                $value = "<span class='success'>$value</span>";
            }

            echo "<tr><td><strong>$field</strong></td><td>$value</td><td>$isset_check</td><td>$empty_check</td></tr>";
        }

        echo "</table>";

        // ProductSpecFormatter 테스트
        echo "<h4>ProductSpecFormatter::getQuantityDisplay() 테스트</h4>";
        echo "<pre>";

        ob_start();
        $qtyDisplay = ProductSpecFormatter::getQuantityDisplay($item);
        $debug_output = ob_get_clean();

        echo "결과: <strong style='color: " . ($qtyDisplay == '1' ? 'red' : 'green') . "'>$qtyDisplay</strong>\n";

        if ($qtyDisplay == '1') {
            echo "\n❌ <span class='error'>문제 발생: '1'이 반환됨</span>\n";
        } else {
            echo "\n✅ <span class='success'>정상: '$qtyDisplay'가 반환됨</span>\n";
        }

        echo "</pre>";

        // 전체 데이터 출력
        echo "<details><summary>전체 데이터 보기 (클릭)</summary><pre>";
        print_r($item);
        echo "</pre></details>";

        echo "<hr>";
    }
}

// 테이블 스키마 확인
echo "<h2>quotation_temp 테이블 스키마</h2>";
$schema_query = "DESCRIBE quotation_temp";
$schema_result = mysqli_query($db, $schema_query);

if ($schema_result) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($field = mysqli_fetch_assoc($schema_result)) {
        $field_name = $field['Field'];
        $highlight = in_array($field_name, ['quantity_display', 'mesu', 'data_version']) ? "style='background: #ffffcc;'" : "";
        echo "<tr $highlight>";
        echo "<td><strong>{$field['Field']}</strong></td>";
        echo "<td>{$field['Type']}</td>";
        echo "<td>{$field['Null']}</td>";
        echo "<td>{$field['Key']}</td>";
        echo "<td>{$field['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ 스키마 조회 실패: " . mysqli_error($db) . "</p>";
}

// ProductSpecFormatter 파일 정보
echo "<h2>ProductSpecFormatter 파일 정보</h2>";
$formatter_path = $_SERVER['DOCUMENT_ROOT'] . '/includes/ProductSpecFormatter.php';
echo "<table>";
echo "<tr><th>항목</th><th>값</th></tr>";
echo "<tr><td>DOCUMENT_ROOT</td><td>{$_SERVER['DOCUMENT_ROOT']}</td></tr>";
echo "<tr><td>파일 경로</td><td>$formatter_path</td></tr>";
echo "<tr><td>파일 존재</td><td>" . (file_exists($formatter_path) ? '✅ YES' : '❌ NO') . "</td></tr>";
if (file_exists($formatter_path)) {
    echo "<tr><td>파일 크기</td><td>" . number_format(filesize($formatter_path)) . " bytes</td></tr>";
    echo "<tr><td>수정 시간</td><td>" . date('Y-m-d H:i:s', filemtime($formatter_path)) . "</td></tr>";
}
echo "</table>";

mysqli_close($db);
?>

</body>
</html>
