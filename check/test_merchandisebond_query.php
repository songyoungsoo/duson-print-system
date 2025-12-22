<?php
include 'db.php';

$GGTABLE = "mlangprintauto_transactioncate";

echo "<h2>MerchandiseBond 쿼리 테스트</h2>";

// 원래 쿼리 (LOWER 사용)
echo "<h3>1. LOWER(Ttable)='merchandisebond' 쿼리:</h3>";
$query1 = "SELECT no, title, Ttable, BigNo FROM $GGTABLE WHERE LOWER(Ttable)='merchandisebond' AND BigNo='0' ORDER BY no ASC";
echo "<p>쿼리: <code>$query1</code></p>";
$result1 = mysqli_query($db, $query1);
if ($result1) {
    $count1 = mysqli_num_rows($result1);
    echo "<p>결과: <strong>$count1</strong>개</p>";
    if ($count1 > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>No</th><th>Title</th><th>Ttable</th></tr>";
        while ($row = mysqli_fetch_array($result1)) {
            echo "<tr><td>{$row['no']}</td><td>{$row['title']}</td><td>{$row['Ttable']}</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>쿼리 오류: " . mysqli_error($db) . "</p>";
}

// 대소문자 구분 없는 쿼리
echo "<h3>2. Ttable='merchandisebond' 쿼리 (정확히 소문자):</h3>";
$query2 = "SELECT no, title, Ttable, BigNo FROM $GGTABLE WHERE Ttable='merchandisebond' AND BigNo='0' ORDER BY no ASC";
echo "<p>쿼리: <code>$query2</code></p>";
$result2 = mysqli_query($db, $query2);
if ($result2) {
    $count2 = mysqli_num_rows($result2);
    echo "<p>결과: <strong>$count2</strong>개</p>";
    if ($count2 > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>No</th><th>Title</th><th>Ttable</th></tr>";
        while ($row = mysqli_fetch_array($result2)) {
            echo "<tr><td>{$row['no']}</td><td>{$row['title']}</td><td>{$row['Ttable']}</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>쿼리 오류: " . mysqli_error($db) . "</p>";
}

// 대문자로 시작하는 쿼리
echo "<h3>3. Ttable='MerchandiseBond' 쿼리 (원래 대소문자):</h3>";
$query3 = "SELECT no, title, Ttable, BigNo FROM $GGTABLE WHERE Ttable='MerchandiseBond' AND BigNo='0' ORDER BY no ASC";
echo "<p>쿼리: <code>$query3</code></p>";
$result3 = mysqli_query($db, $query3);
if ($result3) {
    $count3 = mysqli_num_rows($result3);
    echo "<p>결과: <strong>$count3</strong>개</p>";
    if ($count3 > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>No</th><th>Title</th><th>Ttable</th></tr>";
        while ($row = mysqli_fetch_array($result3)) {
            echo "<tr><td>{$row['no']}</td><td>{$row['title']}</td><td>{$row['Ttable']}</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>쿼리 오류: " . mysqli_error($db) . "</p>";
}

// LIKE 검색
echo "<h3>4. LIKE '%merchandisebond%' 쿼리 (대소문자 무시):</h3>";
$query4 = "SELECT no, title, Ttable, BigNo FROM $GGTABLE WHERE Ttable LIKE '%merchandisebond%' AND BigNo='0' ORDER BY no ASC";
echo "<p>쿼리: <code>$query4</code></p>";
$result4 = mysqli_query($db, $query4);
if ($result4) {
    $count4 = mysqli_num_rows($result4);
    echo "<p>결과: <strong>$count4</strong>개</p>";
    if ($count4 > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>No</th><th>Title</th><th>Ttable</th></tr>";
        while ($row = mysqli_fetch_array($result4)) {
            echo "<tr><td>{$row['no']}</td><td>{$row['title']}</td><td>{$row['Ttable']}</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>쿼리 오류: " . mysqli_error($db) . "</p>";
}

// 데이터베이스 collation 확인
echo "<h3>5. 테이블 Collation 확인:</h3>";
$query5 = "SHOW CREATE TABLE $GGTABLE";
$result5 = mysqli_query($db, $query5);
if ($result5) {
    $row = mysqli_fetch_array($result5);
    echo "<pre>" . htmlspecialchars($row[1]) . "</pre>";
}

mysqli_close($db);
?>
