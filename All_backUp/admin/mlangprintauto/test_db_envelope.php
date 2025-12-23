<?php
// DB 연결 테스트 페이지
include"../../db.php";

echo "<h2>봉투(envelope) DB 데이터 확인</h2>";

$GGTABLE = "mlangprintauto_transactioncate";
$Ttable = "envelope";

echo "<h3>1. DB 연결 상태</h3>";
if($db) {
    echo "✅ DB 연결 성공<br>";
    echo "연결 정보: " . mysqli_get_host_info($db) . "<br><br>";
} else {
    echo "❌ DB 연결 실패<br>";
    exit;
}

echo "<h3>2. 구분(BigNo='0') 데이터 조회</h3>";
$query = "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC";
echo "쿼리: $query<br><br>";

$result = mysqli_query($db, $query);
if(!$result) {
    echo "❌ 쿼리 실행 실패: " . mysqli_error($db) . "<br>";
} else {
    $rows = mysqli_num_rows($result);
    echo "✅ 쿼리 실행 성공<br>";
    echo "결과 개수: $rows 개<br><br>";
    
    if($rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>no</th><th>title</th><th>Ttable</th><th>BigNo</th><th>TreeNo</th></tr>";
        while($row = mysqli_fetch_array($result)) {
            echo "<tr>";
            echo "<td>" . $row['no'] . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . $row['Ttable'] . "</td>";
            echo "<td>" . $row['BigNo'] . "</td>";
            echo "<td>" . ($row['TreeNo'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ 데이터가 없습니다!<br>";
    }
}

echo "<br><h3>3. 전체 envelope 관련 데이터 조회</h3>";
$query2 = "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' ORDER BY no ASC LIMIT 10";
echo "쿼리: $query2<br><br>";

$result2 = mysqli_query($db, $query2);
if(!$result2) {
    echo "❌ 쿼리 실행 실패: " . mysqli_error($db) . "<br>";
} else {
    $rows2 = mysqli_num_rows($result2);
    echo "✅ 쿼리 실행 성공<br>";
    echo "결과 개수: $rows2 개 (최대 10개 표시)<br><br>";
    
    if($rows2 > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>no</th><th>title</th><th>Ttable</th><th>BigNo</th><th>TreeNo</th></tr>";
        while($row = mysqli_fetch_array($result2)) {
            echo "<tr>";
            echo "<td>" . $row['no'] . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . $row['Ttable'] . "</td>";
            echo "<td>" . $row['BigNo'] . "</td>";
            echo "<td>" . ($row['TreeNo'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ 데이터가 없습니다!<br>";
    }
}

echo "<br><h3>4. 테이블 구조 확인</h3>";
$query3 = "DESCRIBE $GGTABLE";
$result3 = mysqli_query($db, $query3);
if($result3) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($row = mysqli_fetch_array($result3)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($db);
?>
