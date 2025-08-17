<?php
echo "<h1>🧲 자석스티커 데이터베이스 설정</h1>";
echo "<p>자석스티커 시스템을 위한 데이터베이스 테이블과 기본 데이터를 설정합니다.</p>";

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

// 1. 자석스티커 카테고리 테이블 (MlangPrintAuto_transactionCate 활용)
echo "<h2>🗂️ 1. 자석스티커 카테고리 설정</h2>";

// 자석스티커 카테고리 데이터 삽입
$category_data = [
    // 자석스티커 종류 (BigNo = '0')
    "('mst_001', '차량용 자석스티커', 'msticker', '0', 1)",
    "('mst_002', '냉장고/가전 자석스티커', 'msticker', '0', 2)",
    "('mst_003', '사업자 홍보용', 'msticker', '0', 3)",
    "('mst_004', '야외방수 자석스티커', 'msticker', '0', 4)",
    
    // 차량용 자석스티커 규격 (BigNo = 'mst_001')
    "('size_small_1', '소형 (10cm x 5cm)', 'msticker', 'mst_001', 10)",
    "('size_medium_1', '중형 (15cm x 10cm)', 'msticker', 'mst_001', 11)",
    "('size_large_1', '대형 (20cm x 15cm)', 'msticker', 'mst_001', 12)",
    "('size_xlarge_1', '특대형 (30cm x 20cm)', 'msticker', 'mst_001', 13)",
    
    // 냉장고/가전 자석스티커 규격 (BigNo = 'mst_002')
    "('size_mini_2', '미니 (5cm x 5cm)', 'msticker', 'mst_002', 20)",
    "('size_small_2', '소형 (8cm x 6cm)', 'msticker', 'mst_002', 21)",
    "('size_medium_2', '중형 (12cm x 8cm)', 'msticker', 'mst_002', 22)",
    "('size_large_2', '대형 (15cm x 10cm)', 'msticker', 'mst_002', 23)",
    
    // 사업자 홍보용 규격 (BigNo = 'mst_003')
    "('size_business_1', '명함형 (9cm x 5cm)', 'msticker', 'mst_003', 30)",
    "('size_business_2', '표준형 (15cm x 10cm)', 'msticker', 'mst_003', 31)",
    "('size_business_3', '대형 (20cm x 15cm)', 'msticker', 'mst_003', 32)",
    "('size_business_4', '점포용 (30cm x 20cm)', 'msticker', 'mst_003', 33)",
    
    // 야외방수 자석스티커 규격 (BigNo = 'mst_004')
    "('size_outdoor_1', '소형 방수 (12cm x 8cm)', 'msticker', 'mst_004', 40)",
    "('size_outdoor_2', '중형 방수 (18cm x 12cm)', 'msticker', 'mst_004', 41)",
    "('size_outdoor_3', '대형 방수 (25cm x 18cm)', 'msticker', 'mst_004', 42)",
    "('size_outdoor_4', '특대형 방수 (35cm x 25cm)', 'msticker', 'mst_004', 43)"
];

$insert_cate_query = "INSERT IGNORE INTO MlangPrintAuto_transactionCate (no, title, Ttable, BigNo, sort) VALUES " . implode(', ', $category_data);

if (mysqli_query($db, $insert_cate_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 자석스티커 카테고리 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 카테고리 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 2. 자석스티커 가격 테이블 생성
echo "<h2>💰 2. 자석스티커 가격 테이블 생성</h2>";

$create_msticker_table_query = "CREATE TABLE IF NOT EXISTS MlangPrintAuto_msticker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no MEDIUMINT UNSIGNED,
    style VARCHAR(10) NOT NULL DEFAULT 'mst_001',
    Section VARCHAR(10) NOT NULL,
    quantity VARCHAR(10) NOT NULL,
    money INT NOT NULL,
    TreeSelect VARCHAR(10) NOT NULL DEFAULT '',
    DesignMoney INT NOT NULL DEFAULT 25000,
    POtype VARCHAR(2) NOT NULL,
    quantityTwo VARCHAR(100) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_style (style),
    KEY idx_section (Section),
    KEY idx_quantity (quantity),
    KEY idx_potype (POtype)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='자석스티커 가격 정보 테이블'";

if (mysqli_query($db, $create_msticker_table_query)) {
    echo "✅ 자석스티커 가격 테이블 생성 성공<br>";
    $success_count++;
} else {
    echo "❌ 가격 테이블 생성 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 3. 자석스티커 기본 가격 데이터 삽입
echo "<h2>📊 3. 자석스티커 기본 가격 데이터 삽입</h2>";

$msticker_data = [
    // 차량용 자석스티커 (mst_001) - 소형 (size_small_1)
    "('mst_001', 'size_small_1', '100', 65000, '', 25000, '1', '', '')",
    "('mst_001', 'size_small_1', '200', 115000, '', 25000, '1', '', '')",
    "('mst_001', 'size_small_1', '300', 165000, '', 25000, '1', '', '')",
    "('mst_001', 'size_small_1', '500', 250000, '', 25000, '1', '', '')",
    "('mst_001', 'size_small_1', '1000', 450000, '', 25000, '1', '', '')",
    
    // 차량용 자석스티커 (mst_001) - 중형 (size_medium_1)
    "('mst_001', 'size_medium_1', '100', 85000, '', 25000, '1', '', '')",
    "('mst_001', 'size_medium_1', '200', 150000, '', 25000, '1', '', '')",
    "('mst_001', 'size_medium_1', '300', 210000, '', 25000, '1', '', '')",
    "('mst_001', 'size_medium_1', '500', 320000, '', 25000, '1', '', '')",
    "('mst_001', 'size_medium_1', '1000', 580000, '', 25000, '1', '', '')",
    
    // 차량용 자석스티커 (mst_001) - 대형 (size_large_1)
    "('mst_001', 'size_large_1', '50', 75000, '', 25000, '1', '', '')",
    "('mst_001', 'size_large_1', '100', 125000, '', 25000, '1', '', '')",
    "('mst_001', 'size_large_1', '200', 220000, '', 25000, '1', '', '')",
    "('mst_001', 'size_large_1', '300', 315000, '', 25000, '1', '', '')",
    "('mst_001', 'size_large_1', '500', 480000, '', 25000, '1', '', '')",
    
    // 차량용 자석스티커 (mst_001) - 특대형 (size_xlarge_1) 
    "('mst_001', 'size_xlarge_1', '30', 85000, '', 25000, '1', '', '')",
    "('mst_001', 'size_xlarge_1', '50', 135000, '', 25000, '1', '', '')",
    "('mst_001', 'size_xlarge_1', '100', 250000, '', 25000, '1', '', '')",
    "('mst_001', 'size_xlarge_1', '200', 450000, '', 25000, '1', '', '')",
    
    // 냉장고/가전 자석스티커 (mst_002) - 미니 (size_mini_2)
    "('mst_002', 'size_mini_2', '100', 45000, '', 20000, '1', '', '')",
    "('mst_002', 'size_mini_2', '200', 80000, '', 20000, '1', '', '')",
    "('mst_002', 'size_mini_2', '300', 115000, '', 20000, '1', '', '')",
    "('mst_002', 'size_mini_2', '500', 175000, '', 20000, '1', '', '')",
    "('mst_002', 'size_mini_2', '1000', 320000, '', 20000, '1', '', '')",
    
    // 냉장고/가전 자석스티커 (mst_002) - 소형 (size_small_2)
    "('mst_002', 'size_small_2', '100', 55000, '', 20000, '1', '', '')",
    "('mst_002', 'size_small_2', '200', 95000, '', 20000, '1', '', '')",
    "('mst_002', 'size_small_2', '300', 135000, '', 20000, '1', '', '')",
    "('mst_002', 'size_small_2', '500', 205000, '', 20000, '1', '', '')",
    
    // 사업자 홍보용 (mst_003) - 명함형 (size_business_1)
    "('mst_003', 'size_business_1', '500', 180000, '', 25000, '1', '', '')",
    "('mst_003', 'size_business_1', '1000', 320000, '', 25000, '1', '', '')",
    "('mst_003', 'size_business_1', '2000', 580000, '', 25000, '1', '', '')",
    "('mst_003', 'size_business_1', '3000', 820000, '', 25000, '1', '', '')",
    "('mst_003', 'size_business_1', '5000', 1250000, '', 25000, '1', '', '')",
    
    // 사업자 홍보용 (mst_003) - 표준형 (size_business_2)
    "('mst_003', 'size_business_2', '300', 195000, '', 25000, '1', '', '')",
    "('mst_003', 'size_business_2', '500', 285000, '', 25000, '1', '', '')",
    "('mst_003', 'size_business_2', '1000', 520000, '', 25000, '1', '', '')",
    "('mst_003', 'size_business_2', '2000', 950000, '', 25000, '1', '', '')",
    
    // 야외방수 자석스티커 (mst_004) - 소형 방수 (size_outdoor_1)
    "('mst_004', 'size_outdoor_1', '100', 95000, '', 30000, '1', '', '')",
    "('mst_004', 'size_outdoor_1', '200', 170000, '', 30000, '1', '', '')",
    "('mst_004', 'size_outdoor_1', '300', 240000, '', 30000, '1', '', '')",
    "('mst_004', 'size_outdoor_1', '500', 365000, '', 30000, '1', '', '')",
    
    // 야외방수 자석스티커 (mst_004) - 중형 방수 (size_outdoor_2)
    "('mst_004', 'size_outdoor_2', '100', 125000, '', 30000, '1', '', '')",
    "('mst_004', 'size_outdoor_2', '200', 225000, '', 30000, '1', '', '')",
    "('mst_004', 'size_outdoor_2', '300', 320000, '', 30000, '1', '', '')",
    
    // 양면 인쇄 옵션 (주요 규격만)
    "('mst_001', 'size_small_1', '100', 104000, '', 25000, '2', '', '')",
    "('mst_001', 'size_small_1', '200', 184000, '', 25000, '2', '', '')",
    "('mst_001', 'size_medium_1', '100', 136000, '', 25000, '2', '', '')",
    "('mst_001', 'size_medium_1', '200', 240000, '', 25000, '2', '', '')",
    "('mst_001', 'size_large_1', '100', 200000, '', 25000, '2', '', '')",
    "('mst_002', 'size_mini_2', '100', 72000, '', 20000, '2', '', '')",
    "('mst_002', 'size_small_2', '100', 88000, '', 20000, '2', '', '')",
    "('mst_003', 'size_business_1', '500', 288000, '', 25000, '2', '', '')",
    "('mst_004', 'size_outdoor_1', '100', 152000, '', 30000, '2', '', '')"
];

$insert_msticker_query = "INSERT IGNORE INTO MlangPrintAuto_msticker (style, Section, quantity, money, TreeSelect, DesignMoney, POtype, quantityTwo, no) VALUES " . implode(', ', $msticker_data);

if (mysqli_query($db, $insert_msticker_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 자석스티커 가격 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 가격 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 4. shop_temp 테이블 업데이트 (자석스티커 지원)
echo "<h2>🛒 4. 장바구니 테이블 업데이트</h2>";

$shop_temp_columns = [
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'poster'",
    'work_memo' => "TEXT",
    'upload_method' => "VARCHAR(20) DEFAULT 'upload'",
    'uploaded_files_info' => "TEXT",
    'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($shop_temp_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (mysqli_query($db, $add_column_query)) {
            echo "✅ shop_temp.$column_name 컬럼 추가 완료<br>";
            $success_count++;
        } else {
            echo "⚠️ shop_temp.$column_name 컬럼 추가 실패: " . mysqli_error($db) . "<br>";
            $error_count++;
        }
    } else {
        echo "✅ shop_temp.$column_name 컬럼 이미 존재<br>";
    }
}

// 5. 최종 상태 확인
echo "<h2>📊 5. 최종 설정 상태 확인</h2>";

$final_queries = [
    "자석스티커 종류" => "SELECT COUNT(*) as count FROM MlangPrintAuto_transactionCate WHERE Ttable='msticker' AND BigNo='0'",
    "자석스티커 재질" => "SELECT COUNT(*) as count FROM MlangPrintAuto_transactionCate WHERE Ttable='msticker' AND BigNo!='0'",
    "자석스티커 가격 데이터" => "SELECT COUNT(*) as count FROM MlangPrintAuto_msticker",
    "가격 데이터 예시" => "SELECT COUNT(*) as count FROM MlangPrintAuto_msticker WHERE style='mst_001'"
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
$sample_query = "SELECT style, Section, quantity, money, DesignMoney, POtype FROM MlangPrintAuto_msticker WHERE style='mst_001' ORDER BY Section, POtype, CAST(quantity AS UNSIGNED) LIMIT 10";
$sample_result = mysqli_query($db, $sample_query);

if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    echo "<table border='1' cellpadding='3' style='border-collapse:collapse; font-size:12px;'>";
    echo "<tr style='background:#f0f0f0;'><th>종류</th><th>재질</th><th>수량</th><th>기본가격</th><th>편집비</th><th>인쇄면</th></tr>";
    
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
echo "• 종류 선택 → 재질 자동 로딩<br>";
echo "• 재질 선택 → 수량 자동 로딩<br>";
echo "• 모든 옵션 선택 → 실시간 가격 계산<br>";
echo "• 🛒 장바구니에 담기 기능<br>";
echo "• 📎 파일 업로드 및 주문<br>";
echo "</div>";

mysqli_close($db);

echo "<div style='text-align:center; margin:20px 0; padding:15px; background:#4caf50; color:white;'>";
echo "<h2>🧲 자석스티커 데이터베이스 설정 완료!</h2>";
echo "<p>강력한 자석으로 어디든 붙이는 프리미엄 자석스티커 시스템이 준비되었습니다.</p>";
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