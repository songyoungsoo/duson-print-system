<?php
// 모든 품목의 업로드 경로 확인

// 구버전 DB 연결
$old_db = mysqli_connect("dsp114.com", "duson1830", "du1830", "duson1830");
mysqli_set_charset($old_db, "utf8");

echo "<h2>구버전 시스템 - 품목별 업로드 경로</h2>";

$products = [
    '전단지' => "Type LIKE '%전단%' OR Type LIKE '%inserted%'",
    '명함' => "Type LIKE '%명함%' OR Type LIKE '%namecard%'",
    '봉투' => "Type LIKE '%봉투%' OR Type LIKE '%envelope%'",
    '스티커' => "Type LIKE '%스티커%' OR Type LIKE '%sticker%'",
    '카다록' => "Type LIKE '%카다록%' OR Type LIKE '%cadarok%'",
    '상품권' => "Type LIKE '%상품권%' OR Type LIKE '%bond%'"
];

foreach ($products as $product_name => $condition) {
    echo "<h3>📦 {$product_name}</h3>";
    
    $query = "SELECT no, Type, ImgFolder, ThingCate FROM MlangOrder_PrintAuto 
              WHERE ({$condition}) AND ImgFolder IS NOT NULL AND ImgFolder != '' 
              ORDER BY no DESC LIMIT 3";
    
    $result = mysqli_query($old_db, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='5' style='margin-bottom: 20px;'>";
        echo "<tr><th>주문번호</th><th>Type</th><th>ImgFolder</th><th>ThingCate</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['no'] . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td style='font-size: 11px;'>" . htmlspecialchars($row['ImgFolder']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ThingCate']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: #999;'>데이터 없음</p>";
    }
}

mysqli_close($old_db);

echo "<hr>";
echo "<h2>신버전 시스템 - 현재 설정된 경로</h2>";

$current_paths = [
    '전단지 (inserted)' => 'mlangorder_printauto/upload/temp_xxx/',
    '명함 (namecard)' => 'uploads/날짜/IP/',
    '봉투 (envelope)' => 'PHPClass/MultyUpload/Upload/경로/년/월일/IP/시간/',
    '스티커 (sticker)' => 'uploads/날짜/IP/',
    '카다록 (cadarok)' => 'uploads/날짜/IP/',
    '상품권 (merchandisebond)' => 'ImgFolder/경로/년/월일/IP/시간/',
    '스티커_신규 (sticker_new)' => 'uploads/sticker_new/세션ID_타임스탬프/'
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>품목</th><th>현재 경로</th></tr>";
foreach ($current_paths as $product => $path) {
    echo "<tr>";
    echo "<td><strong>$product</strong></td>";
    echo "<td><code>$path</code></td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>⚠️ 결론</h3>";
echo "<p>구버전 시스템의 실제 경로를 확인한 후, 모든 품목을 <strong>mlangorder_printauto/upload/{주문번호}/</strong> 경로로 통일해야 합니다.</p>";
?>
