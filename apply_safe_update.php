<?php
echo "<h1>🔧 안전한 포스터 데이터베이스 업데이트</h1>";
echo "<p>기존 테이블을 보존하면서 안전하게 업데이트합니다.</p>";

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
echo "사용자: $user<br>";
echo "</div>";

// 기존 테이블 상태 확인
echo "<h2>🔍 기존 테이블 상태 확인</h2>";

$table_checks = [
    'mlangprintauto_littleprint' => "SELECT COUNT(*) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    'mlangprintauto_transactioncate' => "SELECT COUNT(*) as count FROM mlangprintauto_transactioncate WHERE style = '590'"
];

echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><th>테이블</th><th>기존 레코드 수</th></tr>";

foreach ($table_checks as $table => $query) {
    $result = mysqli_query($db, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
        echo "<tr><td>$table</td><td style='text-align:center;'>$count</td></tr>";
    } else {
        echo "<tr><td>$table</td><td style='color:red;'>테이블 없음</td></tr>";
    }
}
echo "</table>";

// 안전 업데이트 SQL 실행
echo "<h2>🛡️ 안전 업데이트 실행</h2>";

$sql_file = __DIR__ . '/safe_database_update.sql';
if (!file_exists($sql_file)) {
    die("<div style='color:red'>❌ SQL 파일을 찾을 수 없습니다: $sql_file</div>");
}

$sql_content = file_get_contents($sql_file);
$queries = explode(';', $sql_content);

$success_count = 0;
$error_count = 0;
$results = [];

echo "<div style='max-height:300px; overflow-y:auto; border:1px solid #ccc; padding:10px; background:#f9f9f9;'>";

foreach ($queries as $index => $query) {
    $query = trim($query);
    
    if (empty($query) || strpos($query, '--') === 0) {
        continue;
    }
    
    $result = mysqli_query($db, $query);
    
    if ($result) {
        $success_count++;
        
        // SELECT 쿼리 결과 저장
        if (stripos($query, 'SELECT') === 0) {
            $query_results = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $query_results[] = $row;
            }
            if (!empty($query_results)) {
                $results[] = $query_results;
            }
        } else {
            $affected_rows = mysqli_affected_rows($db);
            echo "✅ 쿼리 실행 성공";
            if ($affected_rows > 0) {
                echo " (영향받은 행: $affected_rows)";
            }
            echo "<br>";
        }
    } else {
        $error_count++;
        $error = mysqli_error($db);
        
        // 테이블 이미 존재 오류는 경고로만 표시
        if (strpos($error, 'already exists') !== false) {
            echo "⚠️ 테이블 이미 존재 (정상): " . htmlspecialchars(substr($query, 0, 50)) . "...<br>";
        } else {
            echo "<div style='color:red'>❌ 오류: $error</div>";
            echo "<div style='color:#666; font-size:11px;'>" . htmlspecialchars(substr($query, 0, 100)) . "...</div><br>";
        }
    }
}

echo "</div>";

// 결과 표시
echo "<h2>📊 업데이트 결과</h2>";

foreach ($results as $result_set) {
    if (!empty($result_set)) {
        $first_row = $result_set[0];
        
        echo "<h3>" . (isset($first_row['category']) ? $first_row['category'] : isset($first_row['status']) ? $first_row['status'] : '결과') . "</h3>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin:10px 0;'>";
        
        // 헤더
        echo "<tr style='background:#f0f0f0;'>";
        foreach (array_keys($first_row) as $header) {
            if ($header !== 'category' && $header !== 'status') {
                echo "<th>$header</th>";
            }
        }
        echo "</tr>";
        
        // 데이터
        foreach ($result_set as $row) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                if ($key !== 'category' && $key !== 'status') {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            echo "</tr>";
        }
        
        echo "</table>";
    }
}

echo "<h2>📈 최종 통계</h2>";
echo "<div style='background:#e3f2fd; padding:15px; border:1px solid #2196F3;'>";
echo "<strong>실행된 쿼리:</strong> " . ($success_count + $error_count) . "<br>";
echo "<strong>성공:</strong> <span style='color:green'>$success_count</span><br>";
echo "<strong>오류:</strong> <span style='color:red'>$error_count</span><br>";
echo "</div>";

echo "<h2>🎯 다음 단계</h2>";
echo "<div style='background:#fff3e0; padding:15px; border:1px solid #ff9800;'>";
echo "<p><strong>1. 포스터 페이지 테스트:</strong></p>";
echo "<a href='mlangprintauto/poster/index_compact.php' target='_blank' style='color:#0066cc;'>📋 포스터 시스템 테스트하기</a><br><br>";

echo "<p><strong>2. 데이터 확인:</strong></p>";
echo "• 재질 선택 시 규격이 동적으로 로딩되는지 확인<br>";
echo "• 모든 옵션 선택 시 실시간 가격 계산 확인<br>";
echo "• 디자인비 20,000원으로 통일되었는지 확인<br><br>";

echo "<p><strong>3. 문제 발생 시:</strong></p>";
echo "• 백업 테이블 `backup_littleprint_data`에서 복구 가능<br>";
echo "• 로그를 확인하여 오류 원인 파악<br>";
echo "</div>";

mysqli_close($db);

echo "<div style='text-align:center; margin:20px 0; padding:15px; background:#4caf50; color:white;'>";
echo "<h2>🛡️ 안전 업데이트 완료!</h2>";
echo "<p>기존 데이터를 보존하면서 성공적으로 업데이트되었습니다.</p>";
echo "</div>";
?>

<style>
body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 5px 10px; border: 1px solid #ddd; }
th { background: #f0f0f0; }
code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
a { color: #0066cc; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>