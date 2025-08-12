<?php
// 데이터베이스 연결
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$connect = mysqli_connect($host, $user, $password, $dataname);
if (!$connect) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

mysqli_set_charset($connect, "utf8");

echo "<h2>전단지 수량 데이터 확인</h2>";

// 종이 크기별 수량 데이터 확인
$query = "SELECT DISTINCT Section, quantity, quantityTwo 
          FROM MlangPrintAuto_inserted 
          WHERE quantity IN ('0.5', '1', '2') 
          ORDER BY Section, CAST(quantity AS DECIMAL(10,1))";

$result = mysqli_query($connect, $query);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 10px;'>종이규격 코드</th>";
    echo "<th style='padding: 10px;'>연수 (quantity)</th>";
    echo "<th style='padding: 10px;'>실제 매수 (quantityTwo)</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_array($result)) {
        echo "<tr>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['Section']) . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['quantity']) . "연</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['quantityTwo']) . "매</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "쿼리 오류: " . mysqli_error($connect);
}

// 종이규격 이름도 함께 확인
echo "<h3>종이규격 정보</h3>";
$paper_query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable='inserted' AND BigNo != '0' ORDER BY no";
$paper_result = mysqli_query($connect, $paper_query);

if ($paper_result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 10px;'>코드</th>";
    echo "<th style='padding: 10px;'>종이규격명</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_array($paper_result)) {
        echo "<tr>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['no']) . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['title']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($connect);
?>