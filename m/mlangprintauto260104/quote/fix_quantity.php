<?php
/**
 * quote_items 테이블의 quantity 컬럼을 DECIMAL로 변경
 * 0.5연 등 소수점 수량 지원
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

echo "<h2>quote_items 테이블 quantity 컬럼 수정</h2>";
echo "<pre>";

// 1. 현재 테이블 구조 확인
echo "1. 현재 테이블 구조 확인\n";
$result = mysqli_query($db, "DESCRIBE quote_items");
if ($result) {
    echo "컬럼명\t\t타입\t\t\tNull\tKey\tDefault\n";
    echo str_repeat("-", 70) . "\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "{$row['Field']}\t\t{$row['Type']}\t\t{$row['Null']}\t{$row['Key']}\t{$row['Default']}\n";
    }
} else {
    echo "테이블 조회 실패: " . mysqli_error($db) . "\n";
}

echo "\n\n2. quantity 컬럼 타입 변경 (INT → DECIMAL(10,2))\n";

// 2. quantity 컬럼 타입 변경
$alterQuery = "ALTER TABLE quote_items MODIFY COLUMN quantity DECIMAL(10,2) NOT NULL DEFAULT 1";
if (mysqli_query($db, $alterQuery)) {
    echo "✅ 성공: quantity 컬럼이 DECIMAL(10,2)로 변경되었습니다.\n";
} else {
    echo "❌ 실패: " . mysqli_error($db) . "\n";
}

// 3. 변경 후 확인
echo "\n3. 변경 후 테이블 구조 확인\n";
$result = mysqli_query($db, "DESCRIBE quote_items quantity");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "quantity 컬럼: {$row['Type']}\n";
}

echo "\n완료!\n";
echo "</pre>";
?>
