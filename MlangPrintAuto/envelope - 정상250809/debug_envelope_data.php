<?php
// 봉투 데이터 디버깅
include __DIR__ . "/../../db.php";
$connect = $db;

if (!$connect) {
    die("DB 연결 실패");
}

mysqli_set_charset($connect, "utf8");

echo "=== 봉투 구분 데이터 (BigNo = 0) ===\n";

$category_query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable = 'envelope' AND BigNo = 0 ORDER BY no ASC";
$category_result = mysqli_query($connect, $category_query);

$categories = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row;
    echo "구분 {$row['no']}: {$row['title']}\n";
}

echo "\n=== 각 구분별 하위 종류들 ===\n";

foreach ($categories as $category) {
    echo "\n[{$category['title']}] 구분의 하위 종류들:\n";
    
    $types_query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE BigNo = {$category['no']} AND Ttable = 'envelope' ORDER BY no ASC";
    $types_result = mysqli_query($connect, $types_query);
    
    if (mysqli_num_rows($types_result) > 0) {
        while ($type_row = mysqli_fetch_assoc($types_result)) {
            echo "  종류 {$type_row['no']}: {$type_row['title']}\n";
        }
    } else {
        echo "  하위 종류 없음\n";
    }
}

echo "\n=== 가격 테이블 샘플 ===\n";
$price_query = "SELECT style, Section, quantity, POtype, money, DesignMoney FROM MlangPrintAuto_envelope LIMIT 5";
$price_result = mysqli_query($connect, $price_query);

if ($price_result && mysqli_num_rows($price_result) > 0) {
    while ($price_row = mysqli_fetch_assoc($price_result)) {
        echo "style: {$price_row['style']}, Section: {$price_row['Section']}, quantity: {$price_row['quantity']}, POtype: {$price_row['POtype']}, money: {$price_row['money']}, DesignMoney: {$price_row['DesignMoney']}\n";
    }
} else {
    echo "가격 데이터 없음\n";
}

mysqli_close($connect);
?>