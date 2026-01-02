<?php
require_once __DIR__ . '/../../db.php';

echo "=== mlangorder_printauto 테이블 구조 확인 ===\n\n";

// 테이블 구조 확인
$result = mysqli_query($db, "DESCRIBE mlangorder_printauto");

if (!$result) {
    echo "에러: " . mysqli_error($db) . "\n";
    exit;
}

echo "컬럼 목록:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "- {$row['Field']} ({$row['Type']})\n";
}

echo "\n=== 샘플 데이터 1개 조회 ===\n\n";

$result2 = mysqli_query($db, "SELECT * FROM mlangorder_printauto LIMIT 1");

if (!$result2) {
    echo "에러: " . mysqli_error($db) . "\n";
    exit;
}

if (mysqli_num_rows($result2) > 0) {
    $row = mysqli_fetch_assoc($result2);
    foreach ($row as $key => $value) {
        echo "$key: $value\n";
    }
} else {
    echo "데이터가 없습니다.\n";
}

echo "\n=== 전체 개수 ===\n\n";
$result3 = mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto");
$row3 = mysqli_fetch_assoc($result3);
echo "총 주문 수: {$row3['cnt']}\n";
?>
