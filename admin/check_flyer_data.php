<?php
/**
 * 전단지 데이터 진단 스크립트 v4 - JSON 구조 확인
 */
require_once __DIR__ . '/../db.php';

if (!isset($_GET['key']) || $_GET['key'] !== 'check_2024') {
    die('접근 권한이 없습니다. ?key=check_2024');
}

echo "<pre>\n";
echo "=== Type_1 JSON 구조 분석 ===\n\n";

// 최근 inserted 주문의 Type_1 전체 확인
$query = "SELECT no, Type, Type_1 FROM mlangorder_printauto
          WHERE Type = 'inserted'
          AND Type_1 IS NOT NULL AND Type_1 != ''
          ORDER BY no DESC LIMIT 1";
$result = mysqli_query($db, $query);
$row = mysqli_fetch_assoc($result);

if ($row) {
    echo "주문번호: " . $row['no'] . " (" . $row['Type'] . ")\n";
    echo "Type_1 전체:\n";
    $type1 = json_decode($row['Type_1'], true);
    if ($type1) {
        echo json_encode($type1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo $row['Type_1'] . "\n";
    }
}

echo "\n\n=== 전단지 주문의 Type_1 확인 ===\n\n";

// 전단지 타입 주문 확인
$query2 = "SELECT no, Type, Type_1 FROM mlangorder_printauto
           WHERE Type LIKE '%전단지%'
           AND Type_1 IS NOT NULL AND Type_1 != ''
           ORDER BY no DESC LIMIT 1";
$result2 = mysqli_query($db, $query2);
$row2 = mysqli_fetch_assoc($result2);

if ($row2) {
    echo "주문번호: " . $row2['no'] . " (" . $row2['Type'] . ")\n";
    echo "Type_1 전체:\n";
    $type1_2 = json_decode($row2['Type_1'], true);
    if ($type1_2) {
        echo json_encode($type1_2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo $row2['Type_1'] . "\n";
    }
}

echo "</pre>";
?>
