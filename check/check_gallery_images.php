<?php
/**
 * 갤러리 이미지 확인 스크립트
 * 각 제품별로 실제 이미지가 몇 개나 있는지 확인
 */

require_once 'db.php';
require_once 'includes/gallery_data_adapter.php';

// 제품 목록
$products = [
    'inserted' => '전단지',
    'namecard' => '명함',
    'littleprint' => '포스터',
    'merchandisebond' => '상품권',
    'envelope' => '봉투',
    'cadarok' => '카탈로그',
    'ncrflambeau' => '양식지',
    'msticker' => '자석스티커',
    'sticker' => '스티커'
];

echo "<!DOCTYPE html>\n";
echo "<html lang='ko'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>갤러리 이미지 확인</title>\n";
echo "    <style>\n";
echo "        body { font-family: 'Malgun Gothic', sans-serif; padding: 20px; background: #f5f5f5; }\n";
echo "        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo "        h1 { color: #333; border-bottom: 3px solid #0066cc; padding-bottom: 10px; }\n";
echo "        .product-check { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0066cc; }\n";
echo "        .product-check h2 { margin: 0 0 10px 0; color: #0066cc; }\n";
echo "        .status { display: inline-block; padding: 5px 10px; border-radius: 5px; font-weight: bold; }\n";
echo "        .status.ok { background: #4CAF50; color: white; }\n";
echo "        .status.warning { background: #FF9800; color: white; }\n";
echo "        .status.error { background: #f44336; color: white; }\n";
echo "        .image-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px; }\n";
echo "        .image-item { border: 1px solid #ddd; padding: 5px; text-align: center; }\n";
echo "        .image-item img { max-width: 100%; height: auto; }\n";
echo "        .image-info { font-size: 12px; color: #666; margin-top: 5px; }\n";
echo "        .summary { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <div class='container'>\n";
echo "        <h1>🖼️ 갤러리 샘플 이미지 확인</h1>\n";

$totalProducts = 0;
$productsOk = 0;
$productsWarning = 0;
$productsError = 0;

foreach ($products as $productCode => $productName) {
    $totalProducts++;

    echo "        <div class='product-check'>\n";
    echo "            <h2>📦 {$productName} ({$productCode})</h2>\n";

    // 갤러리 아이템 로드 (썸네일 4개)
    $items = load_gallery_items($productCode, null, 4, 12);
    $itemCount = count($items);

    // 상태 판단
    if ($itemCount >= 4) {
        $statusClass = 'ok';
        $statusText = '✅ 정상 (4개 이상)';
        $productsOk++;
    } elseif ($itemCount >= 1) {
        $statusClass = 'warning';
        $statusText = '⚠️ 부족 (' . $itemCount . '개)';
        $productsWarning++;
    } else {
        $statusClass = 'error';
        $statusText = '❌ 이미지 없음';
        $productsError++;
    }

    echo "            <p><span class='status {$statusClass}'>{$statusText}</span> - 총 {$itemCount}개 이미지 발견</p>\n";

    // 이미지 표시
    if ($itemCount > 0) {
        echo "            <div class='image-grid'>\n";
        foreach ($items as $idx => $item) {
            $imgPath = htmlspecialchars($item['url'] ?? '/assets/images/placeholder.jpg');
            $title = htmlspecialchars($item['title'] ?? '샘플 ' . ($idx + 1));

            echo "                <div class='image-item'>\n";
            echo "                    <img src='{$imgPath}' alt='{$title}' onerror=\"this.src='/assets/images/placeholder.jpg'\">\n";
            echo "                    <div class='image-info'>{$title}</div>\n";
            echo "                </div>\n";
        }
        echo "            </div>\n";
    } else {
        echo "            <p style='color: #999; font-style: italic;'>이미지를 찾을 수 없습니다. 데이터베이스를 확인하세요.</p>\n";
    }

    echo "        </div>\n";
}

// 요약 정보
echo "        <div class='summary'>\n";
echo "            <h3>📊 요약</h3>\n";
echo "            <p><strong>총 제품:</strong> {$totalProducts}개</p>\n";
echo "            <p><span class='status ok'>정상: {$productsOk}개</span> ";
echo "            <span class='status warning'>부족: {$productsWarning}개</span> ";
echo "            <span class='status error'>없음: {$productsError}개</span></p>\n";
echo "        </div>\n";

echo "    </div>\n";
echo "</body>\n";
echo "</html>\n";
?>
