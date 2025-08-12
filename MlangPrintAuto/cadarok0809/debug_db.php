<?php
// 데이터베이스 연결 설정
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$connect = mysqli_connect($host, $user, $password, $dataname);
if (!$connect) {
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}

mysqli_set_charset($connect, "utf8");

$page = "cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";

echo "<h2>데이터베이스 구조 확인</h2>";

// 1. 구분 데이터 확인
echo "<h3>1. 구분 (BigNo = 0)</h3>";
$res = mysqli_query($connect, "SELECT no, title, BigNo, TreeNo FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
while ($row = mysqli_fetch_assoc($res)) {
    echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
}

// 2. 첫 번째 구분의 하위 규격 확인
$first_type_result = mysqli_query($connect, "SELECT no FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC LIMIT 1");
if ($first_type = mysqli_fetch_assoc($first_type_result)) {
    $first_type_no = $first_type['no'];
    
    echo "<h3>2. 첫 번째 구분({$first_type_no})의 규격들 (BigNo = {$first_type_no})</h3>";
    $res = mysqli_query($connect, "SELECT no, title, BigNo, TreeNo FROM $GGTABLE WHERE Ttable='$page' AND BigNo='$first_type_no' ORDER BY no ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
    }
    
    // 3. 첫 번째 규격의 종이종류 확인
    $first_size_result = mysqli_query($connect, "SELECT no FROM $GGTABLE WHERE Ttable='$page' AND BigNo='$first_type_no' ORDER BY no ASC LIMIT 1");
    if ($first_size = mysqli_fetch_assoc($first_size_result)) {
        $first_size_no = $first_size['no'];
        
        echo "<h3>3. 첫 번째 규격({$first_size_no})의 종이종류들 (TreeNo = {$first_size_no})</h3>";
        $res = mysqli_query($connect, "SELECT no, title, BigNo, TreeNo FROM $GGTABLE WHERE TreeNo='$first_size_no' ORDER BY no ASC");
        while ($row = mysqli_fetch_assoc($res)) {
            echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
        }
        
        // 4. 전체 카다록 관련 데이터 확인
        echo "<h3>4. 전체 카다록 데이터</h3>";
        $res = mysqli_query($connect, "SELECT no, title, BigNo, TreeNo FROM $GGTABLE WHERE Ttable='$page' ORDER BY BigNo, TreeNo, no ASC");
        while ($row = mysqli_fetch_assoc($res)) {
            echo "no: {$row['no']}, title: {$row['title']}, BigNo: {$row['BigNo']}, TreeNo: {$row['TreeNo']}<br>";
        }
    }
}

mysqli_close($connect);
?>