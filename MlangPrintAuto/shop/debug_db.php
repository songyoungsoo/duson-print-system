<?php
/**
 * 데이터베이스 연결 디버그
 * 경로: MlangPrintAuto/shop/debug_db.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 데이터베이스 연결 디버그</h2>";

// 1. db.php 파일 확인
echo "<h3>1. db.php 파일 확인</h3>";
$db_file = "../../db.php";
if (file_exists($db_file)) {
    echo "<p style='color: green;'>✅ db.php 파일 존재: $db_file</p>";
} else {
    echo "<p style='color: red;'>❌ db.php 파일 없음: $db_file</p>";
    exit;
}

// 2. db.php 포함 및 연결 테스트
echo "<h3>2. 데이터베이스 연결 테스트</h3>";
try {
    include $db_file;
    
    if (isset($db) && $db) {
        echo "<p style='color: green;'>✅ \$db 변수 존재 및 연결됨</p>";
        
        // 연결 정보 확인
        $host_info = mysqli_get_host_info($db);
        echo "<p>호스트 정보: $host_info</p>";
        
        // 문자셋 확인
        $charset = mysqli_character_set_name($db);
        echo "<p>문자셋: $charset</p>";
        
    } else {
        echo "<p style='color: red;'>❌ \$db 변수가 없거나 연결 실패</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 연결 오류: " . $e->getMessage() . "</p>";
}

// 3. 테이블 존재 확인
echo "<h3>3. shop_temp 테이블 확인</h3>";
if (isset($db) && $db) {
    try {
        $result = mysqli_query($db, "SHOW TABLES LIKE 'shop_temp'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color: green;'>✅ shop_temp 테이블 존재</p>";
            
            // 테이블 구조 확인
            $desc_result = mysqli_query($db, "DESCRIBE shop_temp");
            echo "<h4>테이블 구조:</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>기본값</th></tr>";
            
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
            
            // 데이터 개수 확인
            $count_result = mysqli_query($db, "SELECT COUNT(*) as count FROM shop_temp");
            $count_row = mysqli_fetch_assoc($count_result);
            echo "<p>데이터 개수: {$count_row['count']}개</p>";
            
        } else {
            echo "<p style='color: orange;'>⚠️ shop_temp 테이블 없음</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 테이블 확인 오류: " . $e->getMessage() . "</p>";
    }
}

// 4. 세션 확인
echo "<h3>4. 세션 확인</h3>";
session_start();
$session_id = session_id();
echo "<p>세션 ID: $session_id</p>";

// 5. 장바구니 조회 테스트
echo "<h3>5. 장바구니 조회 테스트</h3>";
if (isset($db) && $db) {
    try {
        $query = "SELECT * FROM shop_temp WHERE session_id = ?";
        $stmt = mysqli_prepare($db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $session_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $items = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = $row;
            }
            
            echo "<p style='color: green;'>✅ 장바구니 조회 성공</p>";
            echo "<p>현재 세션의 장바구니 아이템: " . count($items) . "개</p>";
            
            if (!empty($items)) {
                echo "<h4>장바구니 내용:</h4>";
                foreach ($items as $item) {
                    echo "<p>- 상품 #{$item['no']}: {$item['product_type']}</p>";
                }
            }
            
            mysqli_stmt_close($stmt);
        } else {
            echo "<p style='color: red;'>❌ 쿼리 준비 실패: " . mysqli_error($db) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 장바구니 조회 오류: " . $e->getMessage() . "</p>";
    }
}

// 6. 해결 방안 제시
echo "<h3>6. 해결 방안</h3>";
echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 5px;'>";
echo "<p><strong>다음 단계:</strong></p>";
echo "<ol>";
echo "<li><a href='force_install.php'>강제 테이블 설치</a> (기존 데이터 삭제)</li>";
echo "<li><a href='migrate_table.php'>마이그레이션</a> (기존 데이터 보존)</li>";
echo "<li><a href='cart.php'>장바구니 확인</a></li>";
echo "</ol>";
echo "</div>";

if (isset($db)) {
    mysqli_close($db);
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>