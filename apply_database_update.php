<?php
echo "<h1>🚀 포스터 데이터베이스 업데이트 실행</h1>";
echo "<p>기존 MlangPrintAuto_LittlePrint.sql을 현재 시스템에 맞게 업데이트합니다.</p>";

// 데이터베이스 연결 (root 계정 사용)
$host = "localhost";
$user = "root";           // phpMyAdmin과 동일한 계정
$password = "";           // XAMPP 기본값
$dataname = "duson1830";

$db = mysqli_connect($host, $user, $password);
if (!$db) {
    die("<div style='color:red'>❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "</div>");
}

echo "<h2>📡 연결 상태</h2>";
echo "<div style='background:#e8f5e8; padding:10px; border:1px solid #4caf50;'>";
echo "✅ MySQL/MariaDB 연결 성공<br>";
echo "서버 정보: " . mysqli_get_server_info($db) . "<br>";
echo "클라이언트 정보: " . mysqli_get_client_info() . "<br>";
echo "</div>";

// UTF-8 설정
mysqli_set_charset($db, "utf8mb4");

// SQL 파일 읽기
$sql_file = __DIR__ . '/updated_littleprint_data.sql';
if (!file_exists($sql_file)) {
    die("<div style='color:red'>❌ SQL 파일을 찾을 수 없습니다: $sql_file</div>");
}

echo "<h2>📄 SQL 파일 처리</h2>";
$sql_content = file_get_contents($sql_file);
$queries = explode(';', $sql_content);

$success_count = 0;
$error_count = 0;
$total_queries = 0;

echo "<div style='max-height:400px; overflow-y:auto; border:1px solid #ccc; padding:10px; background:#f9f9f9;'>";

foreach ($queries as $query) {
    $query = trim($query);
    
    // 빈 쿼리나 주석만 있는 라인 건너뛰기
    if (empty($query) || strpos($query, '--') === 0) {
        continue;
    }
    
    $total_queries++;
    
    // 쿼리 실행
    $result = mysqli_query($db, $query);
    
    if ($result) {
        $success_count++;
        
        // SELECT 쿼리인 경우 결과 표시
        if (stripos($query, 'SELECT') === 0) {
            echo "<strong>📊 쿼리 결과:</strong><br>";
            if (mysqli_num_rows($result) > 0) {
                echo "<table border='1' cellpadding='3' style='margin:5px 0; font-size:12px;'>";
                
                // 헤더
                $fields = mysqli_fetch_fields($result);
                echo "<tr style='background:#f0f0f0;'>";
                foreach ($fields as $field) {
                    echo "<th>{$field->name}</th>";
                }
                echo "</tr>";
                
                // 데이터 (최대 10행만 표시)
                $row_count = 0;
                while ($row = mysqli_fetch_assoc($result) && $row_count < 10) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                    $row_count++;
                }
                echo "</table>";
                
                if (mysqli_num_rows($result) > 10) {
                    echo "<em>... 더 많은 결과가 있습니다</em><br>";
                }
            } else {
                echo "<em>결과 없음</em><br>";
            }
            echo "<br>";
        } else {
            // INSERT, CREATE, DROP 등의 경우
            $affected_rows = mysqli_affected_rows($db);
            if ($affected_rows > 0) {
                echo "✅ 쿼리 성공 (영향받은 행: $affected_rows)<br>";
            } else {
                echo "✅ 쿼리 성공<br>";
            }
        }
    } else {
        $error_count++;
        $error = mysqli_error($db);
        echo "<div style='color:red'>❌ 쿼리 오류: $error</div>";
        echo "<div style='color:#666; font-size:11px;'>쿼리: " . htmlspecialchars(substr($query, 0, 100)) . "...</div><br>";
    }
}

echo "</div>";

echo "<h2>📊 실행 결과</h2>";
echo "<div style='background:#e3f2fd; padding:15px; border:1px solid #2196F3;'>";
echo "<strong>총 쿼리 수:</strong> $total_queries<br>";
echo "<strong>성공:</strong> <span style='color:green'>$success_count</span><br>";
echo "<strong>실패:</strong> <span style='color:red'>$error_count</span><br>";
echo "<strong>성공률:</strong> " . round(($success_count / max($total_queries, 1)) * 100, 1) . "%<br>";
echo "</div>";

// 최종 확인
echo "<h2>🔍 최종 데이터 확인</h2>";

// 데이터베이스 선택
mysqli_select_db($db, $dataname);

$final_checks = [
    "총 포스터 데이터" => "SELECT COUNT(*) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    "재질 종류" => "SELECT COUNT(DISTINCT TreeSelect) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    "규격 종류" => "SELECT COUNT(DISTINCT Section) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    "수량 종류" => "SELECT COUNT(DISTINCT quantity) as count FROM mlangprintauto_littleprint WHERE style = '590'",
    "카테고리 데이터" => "SELECT COUNT(*) as count FROM mlangprintauto_transactioncate WHERE style = '590'"
];

echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
echo "<tr style='background:#f0f0f0;'><th>항목</th><th>개수</th></tr>";

foreach ($final_checks as $label => $query) {
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

echo "<h2>🎯 다음 단계</h2>";
echo "<div style='background:#fff3e0; padding:15px; border:1px solid #ff9800;'>";
echo "<p><strong>1. 포스터 페이지 테스트:</strong></p>";
echo "<a href='MlangPrintAuto/Poster/index_compact.php' target='_blank' style='color:#0066cc;'>📋 포스터 페이지에서 동작 확인하기</a><br><br>";

echo "<p><strong>2. db.php 설정 업데이트:</strong></p>";
echo "<code>C:\\xampp\\htdocs\\db.php</code> 파일에서:<br>";
echo "<code>\$user = \"root\";</code><br>";
echo "<code>\$password = \"\";</code><br><br>";

echo "<p><strong>3. 정상 작동 확인:</strong></p>";
echo "• 재질 선택 → 규격 자동 로딩<br>";
echo "• 모든 옵션 선택 → 실시간 가격 계산<br>";
echo "• 장바구니 저장 및 주문 기능<br>";
echo "</div>";

mysqli_close($db);

echo "<div style='text-align:center; margin:20px 0; padding:15px; background:#4caf50; color:white;'>";
echo "<h2>🎉 포스터 데이터베이스 업데이트 완료!</h2>";
echo "<p>기존 SQL 파일이 현재 시스템에 맞게 성공적으로 업데이트되었습니다.</p>";
echo "</div>";
?>

<style>
body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; }
h1, h2 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 5px 10px; border: 1px solid #ddd; }
th { background: #f0f0f0; }
code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
a { color: #0066cc; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>