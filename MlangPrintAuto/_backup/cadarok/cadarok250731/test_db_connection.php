<?php
// 카다록 시스템 데이터베이스 연결 테스트
echo "<h2>🔍 카다록 시스템 데이터베이스 연결 테스트</h2>";

// 1. db_xampp.php 연결 테스트
echo "<h3>1. db_xampp.php 연결 테스트</h3>";
include "../../db_xampp.php";

if ($db) {
    echo "✅ 데이터베이스 연결 성공!<br>";
    echo "- 호스트: localhost<br>";
    echo "- 사용자: root<br>";
    echo "- 데이터베이스: duson1830<br><br>";
    
    // 2. 카다록 테이블 존재 확인
    echo "<h3>2. 카다록 테이블 존재 확인</h3>";
    $TABLE = "MlangPrintAuto_cadarok";
    $table_check = mysqli_query($db, "SHOW TABLES LIKE '$TABLE'");
    
    if (mysqli_num_rows($table_check) > 0) {
        echo "✅ $TABLE 테이블 존재<br><br>";
        
        // 3. 샘플 데이터 확인
        echo "<h3>3. 카다록 샘플 데이터 확인</h3>";
        $sample_query = "SELECT * FROM $TABLE LIMIT 3";
        $sample_result = mysqli_query($db, $sample_query);
        
        if ($sample_result && mysqli_num_rows($sample_result) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>style</th><th>Section</th><th>quantity</th><th>TreeSelect</th><th>money</th></tr>";
            while ($row = mysqli_fetch_array($sample_result)) {
                echo "<tr>";
                echo "<td>" . ($row['style'] ?? 'NULL') . "</td>";
                echo "<td>" . ($row['Section'] ?? 'NULL') . "</td>";
                echo "<td>" . ($row['quantity'] ?? 'NULL') . "</td>";
                echo "<td>" . ($row['TreeSelect'] ?? 'NULL') . "</td>";
                echo "<td>" . ($row['money'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table><br>";
        } else {
            echo "❌ 카다록 테이블에 데이터가 없습니다.<br><br>";
        }
    } else {
        echo "❌ $TABLE 테이블이 존재하지 않습니다.<br><br>";
    }
    
    // 4. transactionCate 테이블 확인
    echo "<h3>4. 카다록 옵션 테이블 확인</h3>";
    $GGTABLE = "MlangPrintAuto_transactionCate";
    $cate_check = mysqli_query($db, "SHOW TABLES LIKE '$GGTABLE'");
    
    if (mysqli_num_rows($cate_check) > 0) {
        echo "✅ $GGTABLE 테이블 존재<br>";
        
        // 카다록 관련 데이터 확인
        $cate_query = "SELECT * FROM $GGTABLE WHERE Ttable='cadarok' LIMIT 5";
        $cate_result = mysqli_query($db, $cate_query);
        
        if ($cate_result && mysqli_num_rows($cate_result) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>no</th><th>BigNo</th><th>TreeNo</th><th>title</th></tr>";
            while ($row = mysqli_fetch_array($cate_result)) {
                echo "<tr>";
                echo "<td>" . $row['no'] . "</td>";
                echo "<td>" . $row['BigNo'] . "</td>";
                echo "<td>" . $row['TreeNo'] . "</td>";
                echo "<td>" . $row['title'] . "</td>";
                echo "</tr>";
            }
            echo "</table><br>";
        } else {
            echo "❌ 카다록 옵션 데이터가 없습니다.<br><br>";
        }
    } else {
        echo "❌ $GGTABLE 테이블이 존재하지 않습니다.<br><br>";
    }
    
    echo "<h3>5. 결론</h3>";
    echo "<p>✅ 데이터베이스 연결이 정상적으로 작동합니다!</p>";
    echo "<p>이제 카다록 시스템을 테스트할 수 있습니다.</p>";
    
} else {
    echo "❌ 데이터베이스 연결 실패<br>";
    echo "MySQL 서버가 실행 중인지 확인해주세요.<br>";
}

mysqli_close($db);
?>