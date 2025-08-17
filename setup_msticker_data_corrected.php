<?php
echo "<h1>🧲 자석스티커 데이터 설정 (수정된 버전)</h1>";
echo "<p>기존 MlangPrintAuto_msticker 및 MlangPrintAuto_transactionCate 테이블을 활용합니다.</p>";

// 데이터베이스 연결
$host = "localhost";
$user = "root";
$password = "";
$dataname = "duson1830";

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    die("<div style='color:red'>❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "</div>");
}

mysqli_set_charset($db, "utf8mb4");

echo "<h2>📡 연결 상태</h2>";
echo "<div style='background:#e8f5e8; padding:10px; border:1px solid #4caf50;'>";
echo "✅ 데이터베이스 연결 성공<br>";
echo "데이터베이스: $dataname<br>";
echo "</div>";

$success_count = 0;
$error_count = 0;

// 1. 자석스티커 카테고리 데이터 추가 (MlangPrintAuto_transactionCate)
echo "<h2>🗂️ 1. 자석스티커 카테고리 설정</h2>";

// 기존 데이터 삭제 (선택적)
echo "<h3>🧹 기존 데이터 정리</h3>";
$cleanup_queries = [
    "DELETE FROM MlangPrintAuto_transactionCate WHERE Ttable='msticker'",
    "DELETE FROM MlangPrintAuto_msticker WHERE style LIKE 'mst_%' OR style IN ('800', '801', '802', '803', '804')"
];

foreach ($cleanup_queries as $query) {
    if (mysqli_query($db, $query)) {
        $affected = mysqli_affected_rows($db);
        echo "✅ 기존 데이터 삭제: $affected 행<br>";
    }
}

// 자석스티커 종류 추가 (BigNo = '0')
$msticker_types = [
    "('800', 'msticker', '0', '차량용 자석스티커', '')",
    "('801', 'msticker', '0', '냉장고/가전 자석스티커', '')",
    "('802', 'msticker', '0', '사업자 홍보용', '')",
    "('803', 'msticker', '0', '야외방수 자석스티커', '')"
];

$insert_types_query = "INSERT IGNORE INTO MlangPrintAuto_transactionCate (no, Ttable, BigNo, title, TreeNo) VALUES " . implode(', ', $msticker_types);

if (mysqli_query($db, $insert_types_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 자석스티커 종류 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 종류 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 자석스티커 규격 추가 (각 종류별)
$msticker_sections = [
    // 차량용 자석스티커 규격 (BigNo = '800')
    "('8001', 'msticker', '800', '소형 (10cm x 5cm)', '')",
    "('8002', 'msticker', '800', '중형 (15cm x 10cm)', '')",
    "('8003', 'msticker', '800', '대형 (20cm x 15cm)', '')",
    "('8004', 'msticker', '800', '특대형 (30cm x 20cm)', '')",
    
    // 냉장고/가전 자석스티커 규격 (BigNo = '801')
    "('8011', 'msticker', '801', '미니 (5cm x 5cm)', '')",
    "('8012', 'msticker', '801', '소형 (8cm x 6cm)', '')",
    "('8013', 'msticker', '801', '중형 (12cm x 8cm)', '')",
    "('8014', 'msticker', '801', '대형 (15cm x 10cm)', '')",
    
    // 사업자 홍보용 규격 (BigNo = '802')
    "('8021', 'msticker', '802', '명함형 (9cm x 5cm)', '')",
    "('8022', 'msticker', '802', '표준형 (15cm x 10cm)', '')",
    "('8023', 'msticker', '802', '대형 (20cm x 15cm)', '')",
    "('8024', 'msticker', '802', '점포용 (30cm x 20cm)', '')",
    
    // 야외방수 자석스티커 규격 (BigNo = '803')
    "('8031', 'msticker', '803', '소형 방수 (12cm x 8cm)', '')",
    "('8032', 'msticker', '803', '중형 방수 (18cm x 12cm)', '')",
    "('8033', 'msticker', '803', '대형 방수 (25cm x 18cm)', '')",
    "('8034', 'msticker', '803', '특대형 방수 (35cm x 25cm)', '')"
];

$insert_sections_query = "INSERT IGNORE INTO MlangPrintAuto_transactionCate (no, Ttable, BigNo, title, TreeNo) VALUES " . implode(', ', $msticker_sections);

if (mysqli_query($db, $insert_sections_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 자석스티커 규격 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 규격 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 2. 자석스티커 가격 데이터 추가 (MlangPrintAuto_msticker 테이블)
echo "<h2>💰 2. 자석스티커 가격 데이터 삽입</h2>";

$msticker_prices = [
    // 차량용 자석스티커 - 소형 (800 + 8001)
    "('800', '8001', 50, '45000', '25000')",
    "('800', '8001', 100, '65000', '25000')",
    "('800', '8001', 200, '115000', '25000')",
    "('800', '8001', 300, '165000', '25000')",
    "('800', '8001', 500, '250000', '25000')",
    "('800', '8001', 1000, '450000', '25000')",
    
    // 차량용 자석스티커 - 중형 (800 + 8002)
    "('800', '8002', 50, '60000', '25000')",
    "('800', '8002', 100, '85000', '25000')",
    "('800', '8002', 200, '150000', '25000')",
    "('800', '8002', 300, '210000', '25000')",
    "('800', '8002', 500, '320000', '25000')",
    "('800', '8002', 1000, '580000', '25000')",
    
    // 차량용 자석스티커 - 대형 (800 + 8003)
    "('800', '8003', 30, '55000', '25000')",
    "('800', '8003', 50, '75000', '25000')",
    "('800', '8003', 100, '125000', '25000')",
    "('800', '8003', 200, '220000', '25000')",
    "('800', '8003', 300, '315000', '25000')",
    "('800', '8003', 500, '480000', '25000')",
    
    // 차량용 자석스티커 - 특대형 (800 + 8004)
    "('800', '8004', 20, '65000', '25000')",
    "('800', '8004', 30, '85000', '25000')",
    "('800', '8004', 50, '135000', '25000')",
    "('800', '8004', 100, '250000', '25000')",
    "('800', '8004', 200, '450000', '25000')",
    
    // 냉장고/가전 자석스티커 - 미니 (801 + 8011)
    "('801', '8011', 100, '35000', '20000')",
    "('801', '8011', 200, '60000', '20000')",
    "('801', '8011', 300, '85000', '20000')",
    "('801', '8011', 500, '130000', '20000')",
    "('801', '8011', 1000, '240000', '20000')",
    
    // 냉장고/가전 자석스티커 - 소형 (801 + 8012)
    "('801', '8012', 100, '45000', '20000')",
    "('801', '8012', 200, '80000', '20000')",
    "('801', '8012', 300, '115000', '20000')",
    "('801', '8012', 500, '175000', '20000')",
    "('801', '8012', 1000, '320000', '20000')",
    
    // 사업자 홍보용 - 명함형 (802 + 8021)
    "('802', '8021', 500, '140000', '25000')",
    "('802', '8021', 1000, '240000', '25000')",
    "('802', '8021', 2000, '420000', '25000')",
    "('802', '8021', 3000, '580000', '25000')",
    "('802', '8021', 5000, '850000', '25000')",
    
    // 사업자 홍보용 - 표준형 (802 + 8022)
    "('802', '8022', 300, '165000', '25000')",
    "('802', '8022', 500, '250000', '25000')",
    "('802', '8022', 1000, '450000', '25000')",
    "('802', '8022', 2000, '820000', '25000')",
    
    // 야외방수 자석스티커 - 소형 방수 (803 + 8031)
    "('803', '8031', 50, '65000', '30000')",
    "('803', '8031', 100, '95000', '30000')",
    "('803', '8031', 200, '170000', '30000')",
    "('803', '8031', 300, '240000', '30000')",
    "('803', '8031', 500, '365000', '30000')",
    
    // 야외방수 자석스티커 - 중형 방수 (803 + 8032)
    "('803', '8032', 50, '85000', '30000')",
    "('803', '8032', 100, '125000', '30000')",
    "('803', '8032', 200, '225000', '30000')",
    "('803', '8032', 300, '320000', '30000')"
];

$insert_prices_query = "INSERT IGNORE INTO MlangPrintAuto_msticker (style, Section, quantity, money, DesignMoney) VALUES " . implode(', ', $msticker_prices);

if (mysqli_query($db, $insert_prices_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 자석스티커 가격 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 가격 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 3. 최종 상태 확인
echo "<h2>📊 최종 설정 상태 확인</h2>";

$final_queries = [
    "자석스티커 종류" => "SELECT COUNT(*) as count FROM MlangPrintAuto_transactionCate WHERE Ttable='msticker' AND BigNo='0'",
    "자석스티커 규격" => "SELECT COUNT(*) as count FROM MlangPrintAuto_transactionCate WHERE Ttable='msticker' AND BigNo!='0'",
    "자석스티커 가격 데이터" => "SELECT COUNT(*) as count FROM MlangPrintAuto_msticker WHERE style IN ('800', '801', '802', '803')",
    "차량용 가격 데이터" => "SELECT COUNT(*) as count FROM MlangPrintAuto_msticker WHERE style='800'"
];

echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><th>항목</th><th>개수</th></tr>";

foreach ($final_queries as $label => $query) {
    $result = mysqli_query($db, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
        echo "<tr><td>$label</td><td style='text-align:center;'><strong>$count</strong></td></tr>";
    } else {
        echo "<tr><td>$label</td><td style='color:red;'>오류</td></tr>";
    }
}

echo "</table>";

// 샘플 데이터 표시
echo "<h3>📋 샘플 가격 데이터</h3>";
$sample_query = "SELECT style, Section, quantity, money, DesignMoney FROM MlangPrintAuto_msticker WHERE style='800' ORDER BY Section, CAST(quantity AS UNSIGNED) LIMIT 10";
$sample_result = mysqli_query($db, $sample_query);

if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    echo "<table border='1' cellpadding='3' style='border-collapse:collapse; font-size:12px;'>";
    echo "<tr style='background:#f0f0f0;'><th>종류</th><th>규격</th><th>수량</th><th>기본가격</th><th>편집비</th></tr>";
    
    while ($row = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>{$row['style']}</td>";
        echo "<td>{$row['Section']}</td>";
        echo "<td>{$row['quantity']}매</td>";
        echo "<td>" . number_format($row['money']) . "원</td>";
        echo "<td>" . number_format($row['DesignMoney']) . "원</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>📈 작업 요약</h2>";
echo "<div style='background:#e3f2fd; padding:15px; border:1px solid #2196F3;'>";
echo "<strong>성공한 작업:</strong> <span style='color:green'>$success_count</span><br>";
echo "<strong>실패한 작업:</strong> <span style='color:red'>$error_count</span><br>";
echo "</div>";

echo "<h2>🎯 테스트</h2>";
echo "<div style='background:#fff3e0; padding:15px; border:1px solid #ff9800;'>";
echo "<p><strong>자석스티커 시스템 테스트:</strong></p>";
echo "<a href='MlangPrintAuto/msticker_new/index.php' target='_blank' style='color:#0066cc;'>🧲 자석스티커 페이지에서 확인하기</a><br><br>";
echo "<p><strong>확인사항:</strong></p>";
echo "• 종류 선택 → 규격 자동 로딩<br>";
echo "• 규격 선택 → 수량 자동 로딩<br>";
echo "• 모든 옵션 선택 → 실시간 가격 계산<br>";
echo "• 🛒 장바구니에 담기 기능<br>";
echo "• 📎 파일 업로드 및 주문<br>";
echo "</div>";

mysqli_close($db);

echo "<div style='text-align:center; margin:20px 0; padding:15px; background:#4caf50; color:white;'>";
echo "<h2>🧲 자석스티커 데이터 설정 완료!</h2>";
echo "<p>전용 테이블을 활용한 완벽한 동적 관계 구축이 완료되었습니다.</p>";
echo "</div>";
?>

<style>
body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 5px 10px; border: 1px solid #ddd; }
th { background: #f0f0f0; }
a { color: #0066cc; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>