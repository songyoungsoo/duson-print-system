<?php
include "db_ajax.php";

echo "<h3>명함 종류별 수량 데이터 확인</h3>";

// 일반명함과 고급수입지의 수량 데이터 확인
$namecard_types = [
    '275' => '일반명함(쿠폰)',
    '278' => '고급수입지'
];

foreach ($namecard_types as $type_id => $type_name) {
    echo "<h4>$type_name (ID: $type_id) 수량 옵션:</h4>";
    
    $query = "SELECT DISTINCT quantity FROM mlangprintauto_namecard WHERE style='$type_id' ORDER BY quantity ASC";
    $result = mysqli_query($db, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $quantities = [];
        while ($row = mysqli_fetch_array($result)) {
            $quantities[] = $row['quantity'];
        }
        echo "수량: " . implode(", ", $quantities) . "<br><br>";
    } else {
        echo "수량 데이터 없음<br><br>";
    }
}

// 카드명함도 확인
echo "<h4>카드명함(PET명함) (ID: 704) 수량 옵션:</h4>";
$query = "SELECT DISTINCT quantity FROM mlangprintauto_namecard WHERE style='704' ORDER BY quantity ASC";
$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $quantities = [];
    while ($row = mysqli_fetch_array($result)) {
        $quantities[] = $row['quantity'];
    }
    echo "수량: " . implode(", ", $quantities) . "<br>";
} else {
    echo "수량 데이터 없음<br>";
}

mysqli_close($db);
?>