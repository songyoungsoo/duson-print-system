<?php
echo "<h1>🧲 자석스티커 데이터 설정 (기존 테이블 활용)</h1>";
echo "<p>기존 MlangPrintAuto_transactionCate 및 MlangPrintAuto_NameCard 테이블에 자석스티커 데이터를 추가합니다.</p>";

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

// 자석스티커 종류 추가 (BigNo = '0')
$msticker_types = [
    "('mst_car', '차량용 자석스티커', 'NameCard', '0', 801)",
    "('mst_home', '냉장고/가전 자석스티커', 'NameCard', '0', 802)",
    "('mst_biz', '사업자 홍보용', 'NameCard', '0', 803)",
    "('mst_outdoor', '야외방수 자석스티커', 'NameCard', '0', 804)"
];

$insert_types_query = "INSERT IGNORE INTO MlangPrintAuto_transactionCate (no, title, Ttable, BigNo, sort) VALUES " . implode(', ', $msticker_types);

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
    // 차량용 자석스티커 규격 (BigNo = 'mst_car')
    "('mst_car_s', '소형 (10cm x 5cm)', 'NameCard', 'mst_car', 811)",
    "('mst_car_m', '중형 (15cm x 10cm)', 'NameCard', 'mst_car', 812)",
    "('mst_car_l', '대형 (20cm x 15cm)', 'NameCard', 'mst_car', 813)",
    "('mst_car_xl', '특대형 (30cm x 20cm)', 'NameCard', 'mst_car', 814)",
    
    // 냉장고/가전 자석스티커 규격 (BigNo = 'mst_home')
    "('mst_home_mini', '미니 (5cm x 5cm)', 'NameCard', 'mst_home', 821)",
    "('mst_home_s', '소형 (8cm x 6cm)', 'NameCard', 'mst_home', 822)",
    "('mst_home_m', '중형 (12cm x 8cm)', 'NameCard', 'mst_home', 823)",
    "('mst_home_l', '대형 (15cm x 10cm)', 'NameCard', 'mst_home', 824)",
    
    // 사업자 홍보용 규격 (BigNo = 'mst_biz')
    "('mst_biz_card', '명함형 (9cm x 5cm)', 'NameCard', 'mst_biz', 831)",
    "('mst_biz_std', '표준형 (15cm x 10cm)', 'NameCard', 'mst_biz', 832)",
    "('mst_biz_big', '대형 (20cm x 15cm)', 'NameCard', 'mst_biz', 833)",
    "('mst_biz_shop', '점포용 (30cm x 20cm)', 'NameCard', 'mst_biz', 834)",
    
    // 야외방수 자석스티커 규격 (BigNo = 'mst_outdoor')
    "('mst_out_s', '소형 방수 (12cm x 8cm)', 'NameCard', 'mst_outdoor', 841)",
    "('mst_out_m', '중형 방수 (18cm x 12cm)', 'NameCard', 'mst_outdoor', 842)",
    "('mst_out_l', '대형 방수 (25cm x 18cm)', 'NameCard', 'mst_outdoor', 843)",
    "('mst_out_xl', '특대형 방수 (35cm x 25cm)', 'NameCard', 'mst_outdoor', 844)"
];

$insert_sections_query = "INSERT IGNORE INTO MlangPrintAuto_transactionCate (no, title, Ttable, BigNo, sort) VALUES " . implode(', ', $msticker_sections);

if (mysqli_query($db, $insert_sections_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 자석스티커 규격 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 규격 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 2. 자석스티커 가격 데이터 추가 (MlangPrintAuto_NameCard 테이블 활용)
echo "<h2>💰 2. 자석스티커 가격 데이터 삽입</h2>";

$msticker_prices = [
    // 차량용 자석스티커 - 소형 (mst_car + mst_car_s)
    "('mst_car', 'mst_car_s', '50', 45000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_s', '100', 65000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_s', '200', 115000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_s', '300', 165000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_s', '500', 250000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_s', '1000', 450000, '', 25000, '1', '', '')",
    
    // 차량용 자석스티커 - 중형 (mst_car + mst_car_m)
    "('mst_car', 'mst_car_m', '50', 60000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_m', '100', 85000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_m', '200', 150000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_m', '300', 210000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_m', '500', 320000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_m', '1000', 580000, '', 25000, '1', '', '')",
    
    // 차량용 자석스티커 - 대형 (mst_car + mst_car_l)
    "('mst_car', 'mst_car_l', '30', 55000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_l', '50', 75000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_l', '100', 125000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_l', '200', 220000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_l', '300', 315000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_l', '500', 480000, '', 25000, '1', '', '')",
    
    // 차량용 자석스티커 - 특대형 (mst_car + mst_car_xl)
    "('mst_car', 'mst_car_xl', '20', 65000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_xl', '30', 85000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_xl', '50', 135000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_xl', '100', 250000, '', 25000, '1', '', '')",
    "('mst_car', 'mst_car_xl', '200', 450000, '', 25000, '1', '', '')",
    
    // 냉장고/가전 자석스티커 - 미니 (mst_home + mst_home_mini)
    "('mst_home', 'mst_home_mini', '100', 35000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_mini', '200', 60000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_mini', '300', 85000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_mini', '500', 130000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_mini', '1000', 240000, '', 20000, '1', '', '')",
    
    // 냉장고/가전 자석스티커 - 소형 (mst_home + mst_home_s)
    "('mst_home', 'mst_home_s', '100', 45000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_s', '200', 80000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_s', '300', 115000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_s', '500', 175000, '', 20000, '1', '', '')",
    "('mst_home', 'mst_home_s', '1000', 320000, '', 20000, '1', '', '')",
    
    // 사업자 홍보용 - 명함형 (mst_biz + mst_biz_card)
    "('mst_biz', 'mst_biz_card', '500', 140000, '', 25000, '1', '', '')",
    "('mst_biz', 'mst_biz_card', '1000', 240000, '', 25000, '1', '', '')",
    "('mst_biz', 'mst_biz_card', '2000', 420000, '', 25000, '1', '', '')",
    "('mst_biz', 'mst_biz_card', '3000', 580000, '', 25000, '1', '', '')",
    "('mst_biz', 'mst_biz_card', '5000', 850000, '', 25000, '1', '', '')",
    
    // 사업자 홍보용 - 표준형 (mst_biz + mst_biz_std)
    "('mst_biz', 'mst_biz_std', '300', 165000, '', 25000, '1', '', '')",
    "('mst_biz', 'mst_biz_std', '500', 250000, '', 25000, '1', '', '')",
    "('mst_biz', 'mst_biz_std', '1000', 450000, '', 25000, '1', '', '')",
    "('mst_biz', 'mst_biz_std', '2000', 820000, '', 25000, '1', '', '')",
    
    // 야외방수 자석스티커 - 소형 방수 (mst_outdoor + mst_out_s)
    "('mst_outdoor', 'mst_out_s', '50', 65000, '', 30000, '1', '', '')",
    "('mst_outdoor', 'mst_out_s', '100', 95000, '', 30000, '1', '', '')",
    "('mst_outdoor', 'mst_out_s', '200', 170000, '', 30000, '1', '', '')",
    "('mst_outdoor', 'mst_out_s', '300', 240000, '', 30000, '1', '', '')",
    "('mst_outdoor', 'mst_out_s', '500', 365000, '', 30000, '1', '', '')",
    
    // 야외방수 자석스티커 - 중형 방수 (mst_outdoor + mst_out_m)
    "('mst_outdoor', 'mst_out_m', '50', 85000, '', 30000, '1', '', '')",
    "('mst_outdoor', 'mst_out_m', '100', 125000, '', 30000, '1', '', '')",
    "('mst_outdoor', 'mst_out_m', '200', 225000, '', 30000, '1', '', '')",
    "('mst_outdoor', 'mst_out_m', '300', 320000, '', 30000, '1', '', '')",
    
    // 양면 인쇄 옵션 (주요 규격만)
    "('mst_car', 'mst_car_s', '100', 104000, '', 25000, '2', '', '')",
    "('mst_car', 'mst_car_s', '200', 184000, '', 25000, '2', '', '')",
    "('mst_car', 'mst_car_m', '100', 136000, '', 25000, '2', '', '')",
    "('mst_car', 'mst_car_m', '200', 240000, '', 25000, '2', '', '')",
    "('mst_car', 'mst_car_l', '100', 200000, '', 25000, '2', '', '')",
    "('mst_home', 'mst_home_mini', '100', 56000, '', 20000, '2', '', '')",
    "('mst_home', 'mst_home_s', '100', 72000, '', 20000, '2', '', '')",
    "('mst_biz', 'mst_biz_card', '500', 224000, '', 25000, '2', '', '')",
    "('mst_outdoor', 'mst_out_s', '100', 152000, '', 30000, '2', '', '')"
];

$insert_prices_query = "INSERT IGNORE INTO MlangPrintAuto_NameCard (style, Section, quantity, money, TreeSelect, DesignMoney, POtype, quantityTwo, no) VALUES " . implode(', ', $msticker_prices);

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
    "자석스티커 종류" => "SELECT COUNT(*) as count FROM MlangPrintAuto_transactionCate WHERE Ttable='NameCard' AND BigNo='0' AND no LIKE 'mst_%'",
    "자석스티커 규격" => "SELECT COUNT(*) as count FROM MlangPrintAuto_transactionCate WHERE Ttable='NameCard' AND BigNo LIKE 'mst_%' AND BigNo!='0'",
    "자석스티커 가격 데이터" => "SELECT COUNT(*) as count FROM MlangPrintAuto_NameCard WHERE style LIKE 'mst_%'",
    "차량용 가격 데이터" => "SELECT COUNT(*) as count FROM MlangPrintAuto_NameCard WHERE style='mst_car'"
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
$sample_query = "SELECT style, Section, quantity, money, DesignMoney, POtype FROM MlangPrintAuto_namecard WHERE style='mst_car' ORDER BY Section, POtype, CAST(quantity AS UNSIGNED) LIMIT 10";
$sample_result = mysqli_query($db, $sample_query);

if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    echo "<table border='1' cellpadding='3' style='border-collapse:collapse; font-size:12px;'>";
    echo "<tr style='background:#f0f0f0;'><th>종류</th><th>규격</th><th>수량</th><th>기본가격</th><th>편집비</th><th>인쇄면</th></tr>";
    
    while ($row = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>{$row['style']}</td>";
        echo "<td>{$row['Section']}</td>";
        echo "<td>{$row['quantity']}매</td>";
        echo "<td>" . number_format($row['money']) . "원</td>";
        echo "<td>" . number_format($row['DesignMoney']) . "원</td>";
        echo "<td>" . ($row['POtype'] == '1' ? '단면' : '양면') . "</td>";
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
echo "<p>기존 테이블 구조를 활용한 동적 관계 구축이 완료되었습니다.</p>";
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