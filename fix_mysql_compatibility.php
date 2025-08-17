<?php
echo "<h1>🔧 MySQL 호환성 문제 해결</h1>";
echo "<p>기존 SQL 파일의 구문을 현재 MySQL/MariaDB 버전에 맞게 수정합니다.</p>";

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
echo "서버 정보: " . mysqli_get_server_info($db) . "<br>";
echo "데이터베이스: $dataname<br>";
echo "</div>";

echo "<h2>🔍 현재 테이블 상태</h2>";

// 테이블 존재 확인
$tables_to_check = ['mlangprintauto_littleprint', 'mlangprintauto_transactioncate'];
$existing_tables = [];

foreach ($tables_to_check as $table) {
    $check_query = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($db, $check_query);
    if ($result && mysqli_num_rows($result) > 0) {
        $existing_tables[] = $table;
        
        // 데이터 개수 확인
        $count_query = "SELECT COUNT(*) as count FROM `$table`";
        $count_result = mysqli_query($db, $count_query);
        if ($count_result) {
            $count_row = mysqli_fetch_assoc($count_result);
            echo "✅ <strong>$table</strong>: {$count_row['count']}개 레코드<br>";
        }
    } else {
        echo "❌ <strong>$table</strong>: 테이블 없음<br>";
    }
}

echo "<h2>🛠️ 호환성 수정 작업</h2>";

$success_count = 0;
$error_count = 0;

// 1. 기존 잘못된 테이블 구조가 있다면 수정
if (in_array('mlangprintauto_littleprint', $existing_tables)) {
    echo "<h3>📋 littleprint 테이블 구조 수정</h3>";
    
    // 기존 테이블 구조 확인
    $desc_query = "DESCRIBE mlangprintauto_littleprint";
    $desc_result = mysqli_query($db, $desc_query);
    
    echo "<table border='1' cellpadding='3' style='margin:10px 0; font-size:12px;'>";
    echo "<tr style='background:#f0f0f0;'><th>필드</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $has_id_column = false;
    while ($row = mysqli_fetch_assoc($desc_result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'id') {
            $has_id_column = true;
        }
    }
    echo "</table>";
    
    // AUTO_INCREMENT ID 컬럼이 없으면 추가
    if (!$has_id_column) {
        echo "<p>🔧 AUTO_INCREMENT ID 컬럼 추가...</p>";
        $add_id_query = "ALTER TABLE mlangprintauto_littleprint ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST";
        if (mysqli_query($db, $add_id_query)) {
            echo "✅ ID 컬럼 추가 성공<br>";
            $success_count++;
        } else {
            echo "❌ ID 컬럼 추가 실패: " . mysqli_error($db) . "<br>";
            $error_count++;
        }
    }
    
    // 컬럼 타입 수정
    echo "<p>🔧 컬럼 타입 최적화...</p>";
    $alter_queries = [
        "ALTER TABLE mlangprintauto_littleprint MODIFY COLUMN style VARCHAR(10) NOT NULL DEFAULT '590'",
        "ALTER TABLE mlangprintauto_littleprint MODIFY COLUMN TreeSelect VARCHAR(10) NOT NULL", 
        "ALTER TABLE mlangprintauto_littleprint MODIFY COLUMN Section VARCHAR(10) NOT NULL",
        "ALTER TABLE mlangprintauto_littleprint MODIFY COLUMN POtype VARCHAR(2) NOT NULL",
        "ALTER TABLE mlangprintauto_littleprint MODIFY COLUMN quantity VARCHAR(10) NOT NULL",
        "ALTER TABLE mlangprintauto_littleprint MODIFY COLUMN money INT NOT NULL",
        "ALTER TABLE mlangprintauto_littleprint MODIFY COLUMN DesignMoney INT NOT NULL DEFAULT 20000"
    ];
    
    foreach ($alter_queries as $query) {
        if (mysqli_query($db, $query)) {
            echo "✅ 컬럼 수정 성공<br>";
            $success_count++;
        } else {
            $error = mysqli_error($db);
            if (strpos($error, 'Duplicate column') === false && strpos($error, 'already exists') === false) {
                echo "⚠️ 컬럼 수정 건너뜀: " . substr($error, 0, 50) . "...<br>";
            }
        }
    }
    
} else {
    // 테이블이 없으면 새로 생성 (현대적 문법 사용)
    echo "<h3>🆕 littleprint 테이블 생성</h3>";
    
    $create_table_query = "CREATE TABLE mlangprintauto_littleprint (
        id INT AUTO_INCREMENT PRIMARY KEY,
        no MEDIUMINT UNSIGNED,
        style VARCHAR(10) NOT NULL DEFAULT '590',
        Section VARCHAR(10) NOT NULL,
        quantity VARCHAR(10) NOT NULL,
        money INT NOT NULL,
        TreeSelect VARCHAR(10) NOT NULL,
        DesignMoney INT NOT NULL DEFAULT 20000,
        POtype VARCHAR(2) NOT NULL,
        quantityTwo VARCHAR(100) NOT NULL DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_style (style),
        KEY idx_treeselect (TreeSelect),
        KEY idx_section (Section)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($db, $create_table_query)) {
        echo "✅ 테이블 생성 성공<br>";
        $success_count++;
    } else {
        echo "❌ 테이블 생성 실패: " . mysqli_error($db) . "<br>";
        $error_count++;
    }
}

// 2. transactioncate 테이블 처리
if (!in_array('mlangprintauto_transactioncate', $existing_tables)) {
    echo "<h3>🆕 transactioncate 테이블 생성</h3>";
    
    $create_cate_query = "CREATE TABLE mlangprintauto_transactioncate (
        no VARCHAR(10) PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        style VARCHAR(10) DEFAULT '590',
        sort INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($db, $create_cate_query)) {
        echo "✅ 카테고리 테이블 생성 성공<br>";
        $success_count++;
    } else {
        echo "❌ 카테고리 테이블 생성 실패: " . mysqli_error($db) . "<br>";
        $error_count++;
    }
}

// 3. 기본 카테고리 데이터 삽입
echo "<h3>📋 기본 카테고리 데이터 삽입</h3>";

$category_data = [
    "('590', '포스터', '590', 1)",
    "('604', '120아트/스노우', '590', 10)",
    "('605', '150아트/스노우', '590', 11)", 
    "('606', '180아트/스노우', '590', 12)",
    "('607', '200아트/스노우', '590', 13)",
    "('608', '250아트/스노우', '590', 14)",
    "('609', '300아트/스노우', '590', 15)",
    "('679', '80모조', '590', 16)",
    "('680', '100모조', '590', 17)",
    "('958', '200g아트/스노우지', '590', 18)",
    "('610', '국2절', '590', 20)",
    "('611', '국전', '590', 21)",
    "('612', '4절', '590', 22)",
    "('613', '2절', '590', 23)"
];

$insert_cate_query = "INSERT IGNORE INTO mlangprintauto_transactioncate (no, title, style, sort) VALUES " . implode(', ', $category_data);

if (mysqli_query($db, $insert_cate_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 카테고리 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 카테고리 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 4. 기본 포스터 데이터 삽입 (필수 데이터만)
echo "<h3>📋 기본 포스터 가격 데이터 삽입</h3>";

$poster_data = [
    // 120아트/스노우 국2절 단면
    "('590', '604', '610', '1', '10', 60000, 20000, '', '')",
    "('590', '604', '610', '1', '20', 90000, 20000, '', '')",
    "('590', '604', '610', '1', '50', 180000, 20000, '', '')",
    "('590', '604', '610', '1', '100', 300000, 20000, '', '')",
    
    // 80모조 국2절 단면  
    "('590', '679', '610', '1', '10', 54000, 20000, '', '')",
    "('590', '679', '610', '1', '20', 81000, 20000, '', '')",
    "('590', '679', '610', '1', '50', 162000, 20000, '', '')",
    "('590', '679', '610', '1', '100', 270000, 20000, '', '')",
    
    // 120아트/스노우 국2절 양면
    "('590', '604', '610', '2', '10', 90000, 20000, '', '')",
    "('590', '604', '610', '2', '20', 135000, 20000, '', '')",
    "('590', '604', '610', '2', '50', 270000, 20000, '', '')",
    "('590', '604', '610', '2', '100', 450000, 20000, '', '')",
    
    // 기타 규격들
    "('590', '604', '611', '1', '10', 80000, 20000, '', '')",
    "('590', '604', '611', '1', '50', 200000, 20000, '', '')",
    "('590', '604', '612', '1', '10', 45000, 20000, '', '')",
    "('590', '604', '612', '1', '50', 120000, 20000, '', '')",
    "('590', '604', '613', '1', '10', 100000, 20000, '', '')",
    "('590', '604', '613', '1', '50', 250000, 20000, '', '')"
];

$insert_poster_query = "INSERT IGNORE INTO mlangprintauto_littleprint (style, TreeSelect, Section, POtype, quantity, money, DesignMoney, quantityTwo, no) VALUES " . implode(', ', $poster_data);

if (mysqli_query($db, $insert_poster_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 포스터 데이터 삽입 성공 (추가된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 포스터 데이터 삽입 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 5. 디자인비 통일
echo "<h3>🔧 디자인비 통일 (20,000원)</h3>";

$update_design_query = "UPDATE mlangprintauto_littleprint SET DesignMoney = 20000 WHERE style = '590' AND (DesignMoney != 20000 OR DesignMoney IS NULL)";

if (mysqli_query($db, $update_design_query)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ 디자인비 통일 완료 (수정된 행: $affected)<br>";
    $success_count++;
} else {
    echo "❌ 디자인비 통일 실패: " . mysqli_error($db) . "<br>";
    $error_count++;
}

// 6. 최종 상태 확인
echo "<h2>📊 최종 상태 확인</h2>";

$final_queries = [
    "포스터 총 데이터" => "SELECT COUNT(*) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    "재질 종류" => "SELECT COUNT(DISTINCT TreeSelect) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    "규격 종류" => "SELECT COUNT(DISTINCT Section) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    "카테고리 데이터" => "SELECT COUNT(*) as count FROM mlangprintauto_transactioncate WHERE style = '590'"
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

echo "<h2>📈 작업 요약</h2>";
echo "<div style='background:#e3f2fd; padding:15px; border:1px solid #2196F3;'>";
echo "<strong>성공한 작업:</strong> <span style='color:green'>$success_count</span><br>";
echo "<strong>실패한 작업:</strong> <span style='color:red'>$error_count</span><br>";
echo "</div>";

echo "<h2>🎯 테스트</h2>";
echo "<div style='background:#fff3e0; padding:15px; border:1px solid #ff9800;'>";
echo "<p><strong>포스터 시스템 테스트:</strong></p>";
echo "<a href='MlangPrintAuto/Poster/index_compact.php' target='_blank' style='color:#0066cc;'>📋 포스터 페이지에서 확인하기</a><br><br>";
echo "<p><strong>확인사항:</strong></p>";
echo "• 재질 선택 → 규격 자동 로딩<br>";
echo "• 모든 옵션 선택 → 실시간 가격 계산<br>";
echo "• 디자인비 20,000원 적용 확인<br>";
echo "</div>";

mysqli_close($db);

echo "<div style='text-align:center; margin:20px 0; padding:15px; background:#4caf50; color:white;'>";
echo "<h2>🔧 MySQL 호환성 문제 해결 완료!</h2>";
echo "<p>구식 문법을 현대적 MySQL/MariaDB 문법으로 수정했습니다.</p>";
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