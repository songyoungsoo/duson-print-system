<?php
// XAMPP 환경 테스트 파일
echo "<h2>XAMPP 환경 테스트</h2>";

// 1. PHP 정보
echo "<h3>1. PHP 버전</h3>";
echo "PHP 버전: " . phpversion() . "<br>";

// 2. 데이터베이스 연결 테스트
echo "<h3>2. 데이터베이스 연결 테스트</h3>";

$host = "localhost";
$user = "root";
$password = "";
$dataname = "duson1830";

echo "연결 정보: host=$host, user=$user, database=$dataname<br>";

$db = mysqli_connect($host, $user, $password);
if (!$db) {
    echo "<span style='color:red'>MySQL 서버 연결 실패: " . mysqli_connect_error() . "</span><br>";
} else {
    echo "<span style='color:green'>MySQL 서버 연결 성공</span><br>";
    
    // 데이터베이스 선택
    $db_select = mysqli_select_db($db, $dataname);
    if (!$db_select) {
        echo "<span style='color:red'>데이터베이스 '$dataname' 선택 실패</span><br>";
        
        // 존재하는 데이터베이스 목록 표시
        echo "<h4>존재하는 데이터베이스 목록:</h4>";
        $result = mysqli_query($db, "SHOW DATABASES");
        while ($row = mysqli_fetch_array($result)) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "<span style='color:green'>데이터베이스 '$dataname' 선택 성공</span><br>";
        
        // 테이블 존재 확인
        $table_check = mysqli_query($db, "SHOW TABLES LIKE 'MlangPrintAuto_MerchandiseBond'");
        if (mysqli_num_rows($table_check) == 0) {
            echo "<span style='color:red'>테이블 'MlangPrintAuto_MerchandiseBond'가 존재하지 않습니다</span><br>";
            
            // 존재하는 테이블 목록 표시
            echo "<h4>존재하는 테이블 목록:</h4>";
            $result = mysqli_query($db, "SHOW TABLES");
            while ($row = mysqli_fetch_array($result)) {
                echo "- " . $row[0] . "<br>";
            }
        } else {
            echo "<span style='color:green'>테이블 'MlangPrintAuto_MerchandiseBond' 존재 확인</span><br>";
            
            // 테이블 데이터 확인
            $data_check = mysqli_query($db, "SELECT COUNT(*) as count FROM MlangPrintAuto_MerchandiseBond");
            $count_row = mysqli_fetch_array($data_check);
            echo "테이블 데이터 행 수: " . $count_row['count'] . "<br>";
            
            // 샘플 데이터 표시
            echo "<h4>샘플 데이터 (최대 5개):</h4>";
            $sample_data = mysqli_query($db, "SELECT * FROM MlangPrintAuto_MerchandiseBond LIMIT 5");
            echo "<table border='1'>";
            echo "<tr><th>style</th><th>Section</th><th>quantity</th><th>POtype</th><th>money</th></tr>";
            while ($row = mysqli_fetch_array($sample_data)) {
                echo "<tr>";
                echo "<td>" . $row['style'] . "</td>";
                echo "<td>" . $row['Section'] . "</td>";
                echo "<td>" . $row['quantity'] . "</td>";
                echo "<td>" . $row['POtype'] . "</td>";
                echo "<td>" . $row['money'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    mysqli_close($db);
}

// 3. GET 파라미터 테스트
echo "<h3>3. GET 파라미터 테스트</h3>";
echo "<a href='?MY_type=test&PN_type=test&MY_amount=500&POtype=1&ordertype=total'>테스트 파라미터로 실행</a><br>";

if (!empty($_GET)) {
    echo "받은 GET 파라미터:<br>";
    foreach ($_GET as $key => $value) {
        echo "$key = $value<br>";
    }
}
?>

<script>
console.log('테스트 페이지 로드 완료');
</script>