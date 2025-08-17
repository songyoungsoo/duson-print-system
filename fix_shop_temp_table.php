<?php
echo "<h1>🛠️ shop_temp 테이블 수정</h1>";
echo "<p>장바구니 테이블 구조를 최신화합니다.</p>";

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
echo "</div>";

echo "<h2>🔍 기존 shop_temp 테이블 확인</h2>";

// 기존 테이블 구조 확인
$table_check = mysqli_query($db, "SHOW TABLES LIKE 'shop_temp'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ shop_temp 테이블 존재<br>";
    
    // 현재 구조 표시
    $desc_result = mysqli_query($db, "DESCRIBE shop_temp");
    echo "<h3>현재 테이블 구조:</h3>";
    echo "<table border='1' cellpadding='3' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'><th>필드</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = mysqli_fetch_assoc($desc_result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 기존 데이터 개수 확인
    $count_result = mysqli_query($db, "SELECT COUNT(*) as count FROM shop_temp");
    $count_row = mysqli_fetch_assoc($count_result);
    echo "<p>기존 데이터: <strong>{$count_row['count']}</strong>개</p>";
} else {
    echo "❌ shop_temp 테이블이 없습니다.<br>";
}

echo "<h2>🔧 테이블 구조 업데이트</h2>";

// 최신 테이블 구조로 생성/업데이트
$create_query = "CREATE TABLE IF NOT EXISTS shop_temp (
    no INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) NOT NULL DEFAULT 'poster',
    MY_type VARCHAR(50),
    TreeSelect VARCHAR(50),
    Section VARCHAR(50),
    PN_type VARCHAR(50),
    MY_amount VARCHAR(50),
    POtype VARCHAR(10),
    ordertype VARCHAR(50),
    st_price INT DEFAULT 0,
    st_price_vat INT DEFAULT 0,
    work_memo TEXT,
    upload_method VARCHAR(20) DEFAULT 'upload',
    uploaded_files_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_session (session_id),
    KEY idx_product (product_type),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='통합 장바구니 테이블'";

if (mysqli_query($db, $create_query)) {
    echo "✅ shop_temp 테이블 생성/업데이트 완료<br>";
} else {
    echo "❌ 테이블 생성 실패: " . mysqli_error($db) . "<br>";
}

// 필수 컬럼들 추가 (기존 테이블에 없을 수 있는 컬럼들)
$required_columns = [
    'product_type' => "VARCHAR(50) NOT NULL DEFAULT 'poster'",
    'Section' => "VARCHAR(50)",
    'PN_type' => "VARCHAR(50)",
    'work_memo' => "TEXT",
    'upload_method' => "VARCHAR(20) DEFAULT 'upload'",
    'uploaded_files_info' => "TEXT",
    'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

echo "<h3>필수 컬럼 추가:</h3>";

foreach ($required_columns as $column_name => $column_definition) {
    $check_column_query = "SHOW COLUMNS FROM shop_temp LIKE '$column_name'";
    $column_result = mysqli_query($db, $check_column_query);
    
    if (mysqli_num_rows($column_result) == 0) {
        $add_column_query = "ALTER TABLE shop_temp ADD COLUMN $column_name $column_definition";
        if (mysqli_query($db, $add_column_query)) {
            echo "✅ $column_name 컬럼 추가 완료<br>";
        } else {
            echo "⚠️ $column_name 컬럼 추가 실패: " . mysqli_error($db) . "<br>";
        }
    } else {
        echo "✅ $column_name 컬럼 이미 존재<br>";
    }
}

// 인덱스 추가
echo "<h3>인덱스 최적화:</h3>";

$indexes = [
    'idx_session' => 'session_id',
    'idx_product' => 'product_type',
    'idx_created' => 'created_at'
];

foreach ($indexes as $index_name => $column) {
    $add_index_query = "ALTER TABLE shop_temp ADD INDEX $index_name ($column)";
    if (mysqli_query($db, $add_index_query)) {
        echo "✅ $index_name 인덱스 추가 완료<br>";
    } else {
        $error = mysqli_error($db);
        if (strpos($error, 'Duplicate key') !== false) {
            echo "✅ $index_name 인덱스 이미 존재<br>";
        } else {
            echo "⚠️ $index_name 인덱스 추가 실패: $error<br>";
        }
    }
}

echo "<h2>🔍 업데이트된 테이블 구조</h2>";

$final_desc_result = mysqli_query($db, "DESCRIBE shop_temp");
echo "<table border='1' cellpadding='3' style='border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><th>필드</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($final_desc_result)) {
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

echo "<h2>🧪 테스트 데이터 삽입</h2>";

// 테스트 세션 ID 생성
$test_session_id = 'test_' . uniqid();

$test_data = [
    'session_id' => $test_session_id,
    'product_type' => 'poster',
    'MY_type' => '590',
    'Section' => '604',
    'PN_type' => '610',
    'MY_amount' => '10',
    'POtype' => '1',
    'ordertype' => '디자인+인쇄',
    'st_price' => 60000,
    'st_price_vat' => 66000
];

$insert_query = "INSERT INTO shop_temp (session_id, product_type, MY_type, Section, PN_type, MY_amount, POtype, ordertype, st_price, st_price_vat) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssssssii", 
        $test_data['session_id'],
        $test_data['product_type'],
        $test_data['MY_type'],
        $test_data['Section'],
        $test_data['PN_type'],
        $test_data['MY_amount'],
        $test_data['POtype'],
        $test_data['ordertype'],
        $test_data['st_price'],
        $test_data['st_price_vat']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($db);
        echo "✅ 테스트 데이터 삽입 성공 (ID: $insert_id)<br>";
    } else {
        echo "❌ 테스트 데이터 삽입 실패: " . mysqli_stmt_error($stmt) . "<br>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "❌ Prepared statement 실패: " . mysqli_error($db) . "<br>";
}

echo "<h2>📊 최종 상태</h2>";

$final_count_result = mysqli_query($db, "SELECT COUNT(*) as count FROM shop_temp");
$final_count_row = mysqli_fetch_assoc($final_count_result);

$recent_data_result = mysqli_query($db, "SELECT * FROM shop_temp ORDER BY created_at DESC LIMIT 3");

echo "<p><strong>총 레코드 수:</strong> {$final_count_row['count']}개</p>";

echo "<h3>최근 데이터:</h3>";
echo "<table border='1' cellpadding='3' style='border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><th>ID</th><th>세션</th><th>제품</th><th>구분</th><th>재질</th><th>규격</th><th>수량</th><th>가격</th><th>생성일</th></tr>";

while ($row = mysqli_fetch_assoc($recent_data_result)) {
    echo "<tr>";
    echo "<td>{$row['no']}</td>";
    echo "<td>" . substr($row['session_id'], 0, 10) . "...</td>";
    echo "<td>{$row['product_type']}</td>";
    echo "<td>{$row['MY_type']}</td>";
    echo "<td>{$row['Section']}</td>";
    echo "<td>{$row['PN_type']}</td>";
    echo "<td>{$row['MY_amount']}</td>";
    echo "<td>" . number_format($row['st_price_vat']) . "원</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🎯 다음 단계</h2>";
echo "<div style='background:#fff3e0; padding:15px; border:1px solid #ff9800;'>";
echo "<p><strong>1. 포스터 페이지에서 장바구니 테스트:</strong></p>";
echo "<a href='MlangPrintAuto/Poster/index_compact.php' target='_blank'>📋 포스터 페이지 열기</a><br><br>";

echo "<p><strong>2. 테스트 절차:</strong></p>";
echo "• 모든 옵션 선택 후 가격 계산<br>";
echo "• 🛒 장바구니에 담기 버튼 클릭<br>";
echo "• 오류 없이 성공 메시지 확인<br>";
echo "• 브라우저 개발자 도구 Network 탭에서 200 응답 확인<br><br>";

echo "<p><strong>3. 디버깅:</strong></p>";
echo "• F12 → Network 탭에서 add_to_basket.php 요청 확인<br>";
echo "• Console 탭에서 JavaScript 오류 확인<br>";
echo "</div>";

mysqli_close($db);

echo "<div style='text-align:center; margin:20px 0; padding:15px; background:#4caf50; color:white;'>";
echo "<h2>🛠️ shop_temp 테이블 수정 완료!</h2>";
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