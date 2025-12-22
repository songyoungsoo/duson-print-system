<?php
// shop_temp 테이블 컬럼 확인 스크립트
include "db.php";

echo "<h2>shop_temp 테이블 구조 확인</h2>";

$query = "SHOW COLUMNS FROM shop_temp";
$result = mysqli_query($db, $query);

if (!$result) {
    die("쿼리 실패: " . mysqli_error($db));
}

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// 필요한 컬럼 체크
$required_columns = [
    'session_id', 'product_type', 'MY_type', 'PN_type', 'MY_Fsd', 'MY_amount',
    'POtype', 'ordertype', 'st_price', 'st_price_vat', 'additional_options',
    'additional_options_total', 'ImgFolder', 'ThingCate', 'original_filename'
];

echo "<h3>필수 컬럼 존재 여부</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>컬럼명</th><th>상태</th></tr>";

foreach ($required_columns as $col) {
    $exists = in_array($col, $columns);
    $status = $exists ? "<span style='color: green;'>✓ 존재</span>" : "<span style='color: red;'>✗ 없음</span>";
    echo "<tr><td>$col</td><td>$status</td></tr>";
}
echo "</table>";

// 누락된 컬럼 확인
$missing = array_diff($required_columns, $columns);
if (count($missing) > 0) {
    echo "<h3 style='color: red;'>⚠️ 누락된 컬럼: " . implode(', ', $missing) . "</h3>";
    echo "<p>다음 SQL을 실행하세요:</p>";
    echo "<pre>";
    foreach ($missing as $col) {
        $type = '';
        switch ($col) {
            case 'session_id':
            case 'product_type':
            case 'MY_type':
            case 'PN_type':
            case 'MY_Fsd':
            case 'MY_amount':
            case 'POtype':
            case 'ordertype':
                $type = 'VARCHAR(50)';
                break;
            case 'st_price':
            case 'st_price_vat':
            case 'additional_options_total':
                $type = 'INT(11)';
                break;
            case 'additional_options':
            case 'ThingCate':
            case 'original_filename':
                $type = 'TEXT';
                break;
            case 'ImgFolder':
                $type = 'VARCHAR(255)';
                break;
        }
        echo "ALTER TABLE shop_temp ADD COLUMN $col $type;\n";
    }
    echo "</pre>";
} else {
    echo "<h3 style='color: green;'>✓ 모든 필수 컬럼이 존재합니다</h3>";
}

mysqli_close($db);
?>
