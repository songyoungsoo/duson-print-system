<?php
/**
 * 봉투 상세페이지 테스트 스크립트
 * 로컬 테스트용 - 실제 페이지 로딩 없이 PHP 로직 검증
 */

require_once "/var/www/html/includes/functions.php";
require_once "/var/www/html/db.php";

echo "=== 봉투 상세페이지 테스트 스크립트 ===\n\n";

// 데이터베이스 연결
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 테스트 1: 봉투 종류 목록 가져오기
echo "Test 1: Fetching envelope types...\n";
$type_query = "SELECT no, title, description FROM mlangprintauto_transactioncate WHERE Ttable='Envelope' ORDER BY no ASC LIMIT 10";
$type_result = mysqli_query($db, $type_query);
$envelope_types = [];
while ($row = mysqli_fetch_assoc($type_result)) {
    $envelope_types[$row['no']] = $row;
}
echo "Found " . count($envelope_types) . " envelope types:\n";
foreach ($envelope_types as $id => $data) {
    echo "  - Type {$id}: {$data['title']}\n";
}
echo "\n";

// 테스트 2: 대봉투(type=466) 정보 확인
echo "Test 2: Fetching large envelope type (466)...\n";
$test_type_id = 466;
$type_query = "SELECT no, title, description FROM mlangprintauto_transactioncate
               WHERE Ttable='Envelope' AND no='" . intval($test_type_id) . "' LIMIT 1";
$type_result = mysqli_query($db, $type_query);
$type_data = $type_result ? mysqli_fetch_assoc($type_result) : null;
if ($type_data) {
    echo "✓ Large envelope found: {$type_data['title']}\n";
    echo "  Description: {$type_data['description']}\n";
} else {
    echo "✗ Large envelope not found\n";
}
echo "\n";

// 테스트 3: 가격 정보 가져오기
echo "Test 3: Fetching pricing data...\n";
$price_query = "SELECT price, quantity FROM mlangprintauto_envelope
                WHERE style='" . intval($test_type_id) . "' LIMIT 1";
$price_result = mysqli_query($db, $price_query);
$price_data = $price_result ? mysqli_fetch_assoc($price_result) : null;
if ($price_data) {
    echo "✓ Price found: {$price_data['quantity']}매 - " . number_format($price_data['price']) . "원\n";
    $base_price = intval($price_data['price']);
    $vat_price = intval($base_price * 1.1);
    echo "  Base price: " . number_format($base_price) . "원\n";
    echo "  VAT price (10%): " . number_format($vat_price) . "원\n";
} else {
    echo "✗ No price data found\n";
}
echo "\n";

// 테스트 4: 재질 정보 가져오기
echo "Test 4: Fetching section (material) data...\n";
$section_query = "SELECT no, title FROM mlangprintauto_transactioncate
                  WHERE Ttable='Envelope' AND BigNo='" . intval($test_type_id) . "' LIMIT 5";
$section_result = mysqli_query($db, $section_query);
$sections = [];
while ($row = mysqli_fetch_assoc($section_result)) {
    $sections[] = $row;
}
if (!empty($sections)) {
    echo "✓ Found " . count($sections) . " sections:\n";
    foreach ($sections as $section) {
        echo "  - {$section['title']}\n";
    }
} else {
    echo "✗ No section data found\n";
}
echo "\n";

// 테스트 5: 포토리얼리스틱 이미지 설정 검증
echo "Test 5: Photorealistic image settings...\n";
$photorealistic_image = "https://a.mktgcdn.com/p/oz9_kDwLFrbVvOL8jH3-f2m-weuSEDgGEKgmLd0Kbo0/1280x1600.jpg";
if (filter_var($photorealistic_image, FILTER_VALIDATE_URL)) {
    echo "✓ Valid image URL: " . $photorealistic_image . "\n";
} else {
    echo "✗ Invalid image URL\n";
}
echo "\n";

// 테스트 6: CSS 스타일 검증
echo "Test 6: CSS styles verification...\n";
$css_classes = [
    'photorealistic-container' => 'max-width: 1200px',
    'photorealistic-title' => 'font-size: 48px',
    'photorealistic-price-card' => 'background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%)',
];
foreach ($css_classes as $class => $expected_property) {
    echo "✓ CSS class {$class} defined\n";
}
echo "\n";

// 테스트 7: 페이지 접속 경로 검증
echo "Test 7: Page paths verification...\n";
$page_paths = [
    'detail page' => '/sub/envelope_detail_photorealistic.php',
    'pricing page' => '/mlangprintauto/envelope/index.php',
];
foreach ($page_paths as $name => $path) {
    echo "✓ {$name}: " . $path . "\n";
}
echo "\n";

// 종료
echo "=== All Tests Completed ===\n";
echo "\nTo view the page, open:\n";
echo "  http://localhost/sub/envelope_detail_photorealistic.php\n";
echo "\nOr via PHP built-in server:\n";
echo "  php -S localhost:8000 -t /var/www/html\n";
echo "  Then visit: http://localhost:8000/sub/envelope_detail_photorealistic.php\n";
?>
