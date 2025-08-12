<?php
// 대봉투 API 테스트
include "db.php";

$GGTABLE = "MlangPrintAuto_transactionCate";
$page = "envelope";

// 대봉투 ID 찾기
$result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' AND title LIKE '%대봉투%' ORDER BY no ASC");
if ($row = mysqli_fetch_array($result)) {
    $big_envelope_id = $row['no'];
    echo "대봉투 ID: " . $big_envelope_id . "\n";
    
    // 대봉투 하위 종류 조회
    $sub_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='$big_envelope_id' ORDER BY no ASC");
    echo "대봉투 하위 종류:\n";
    while ($sub_row = mysqli_fetch_array($sub_result)) {
        echo "- ID: " . $sub_row['no'] . ", 제목: " . $sub_row['title'] . "\n";
    }
} else {
    echo "대봉투를 찾을 수 없습니다.\n";
}
?>