<?php
/**
 * 제품 CRUD 통합 테스트
 * product_manager.php의 실제 동작 검증
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/ProductConfig.php';

echo "=== 제품 CRUD 통합 테스트 ===\n\n";

// 1. ProductConfig 테스트
echo "1. ProductConfig 로딩 테스트\n";
echo "----------------------------\n";

$products = ['namecard', 'inserted', 'envelope', 'sticker', 'msticker',
             'cadarok', 'littleprint', 'merchandisebond', 'ncrflambeau'];

foreach ($products as $product) {
    if (ProductConfig::isValidProduct($product)) {
        $config = ProductConfig::getConfig($product);
        echo "✅ {$product}: {$config['name']} - 테이블: {$config['table']}\n";
    } else {
        echo "❌ {$product}: 설정 로드 실패\n";
    }
}

echo "\n2. 명함 데이터 조회 테스트\n";
echo "----------------------------\n";

$config = ProductConfig::getConfig('namecard');
$table = $config['table'];

$query = "SELECT * FROM {$table} LIMIT 5";
$result = mysqli_query($db, $query);

if ($result) {
    $count = mysqli_num_rows($result);
    echo "✅ 조회 성공: {$count}건\n";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "- ID: {$row['no']}, 카테고리: {$row['MY_type']}, 규격: {$row['PN_type']}\n";
    }
} else {
    echo "❌ 조회 실패: " . mysqli_error($db) . "\n";
}

echo "\n3. 검색 기능 테스트\n";
echo "----------------------------\n";

$search_keyword = '일반';
$search_query = "SELECT * FROM {$table}
                 WHERE MY_type LIKE ? OR PN_type LIKE ?
                 LIMIT 3";

$stmt = mysqli_prepare($db, $search_query);
$search_param = "%{$search_keyword}%";
mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $count = mysqli_num_rows($result);
    echo "✅ 검색 성공 ('{$search_keyword}'): {$count}건\n";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "- {$row['MY_type']} / {$row['PN_type']}\n";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "❌ 검색 실패\n";
}

echo "\n4. 페이지 접근 테스트\n";
echo "----------------------------\n";

$pages = [
    'product_manager.php' => '제품 관리 메인',
    'product_manager.php?product=namecard' => '명함 리스트',
    'order_manager.php' => '주문 관리',
    'index.php' => '대시보드'
];

foreach ($pages as $page => $name) {
    $url = "http://localhost/admin/mlangprintauto/{$page}";
    $headers = @get_headers($url);

    if ($headers && strpos($headers[0], '200') !== false) {
        echo "✅ {$name}: 접근 가능\n";
    } else {
        echo "❌ {$name}: 접근 불가\n";
    }
}

echo "\n=== 테스트 완료 ===\n";

mysqli_close($db);
?>
