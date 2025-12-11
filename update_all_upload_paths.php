<?php
/**
 * 모든 품목의 업로드 경로를 mlangorder_printauto/upload/로 통일
 */

$products = [
    'namecard' => 'mlangprintauto/namecard/add_to_basket.php',
    'envelope' => 'mlangprintauto/envelope/add_to_basket.php',
    'sticker' => 'mlangprintauto/sticker/add_to_basket.php',
    'cadarok' => 'mlangprintauto/cadarok/add_to_basket.php',
    'merchandisebond' => 'mlangprintauto/merchandisebond/add_to_basket.php',
    'sticker_new' => 'mlangprintauto/sticker_new/add_to_basket.php',
    'leaflet' => 'mlangprintauto/leaflet/add_to_basket.php',
    'msticker' => 'mlangprintauto/msticker/add_to_basket.php',
    'littleprint' => 'mlangprintauto/littleprint/add_to_basket.php',
    'poster' => 'mlangprintauto/poster/add_to_basket.php',
    'ncrflambeau' => 'mlangprintauto/ncrflambeau/add_to_basket.php'
];

echo "<h2>📦 모든 품목 업로드 경로 통일 작업</h2>";
echo "<p><strong>목표 경로:</strong> <code>mlangorder_printauto/upload/temp_xxx/</code> → <code>mlangorder_printauto/upload/{주문번호}/</code></p>";
echo "<hr>";

foreach ($products as $product_name => $file_path) {
    echo "<h3>🔧 {$product_name}</h3>";
    
    if (!file_exists($file_path)) {
        echo "<p style='color: orange;'>⚠️ 파일 없음: {$file_path}</p>";
        continue;
    }
    
    $content = file_get_contents($file_path);
    
    // 현재 업로드 경로 패턴 찾기
    $patterns = [
        '/\$upload_directory\s*=\s*["\']\.\.\/\.\.\/uploads\/[^"\']+["\'];/',
        '/\$upload_directory\s*=\s*["\']\.\.\/\.\.\/PHPClass\/[^"\']+["\'];/',
        '/\$upload_directory\s*=\s*["\']\.\.\/\.\.\/ImgFolder\/[^"\']+["\'];/',
        '/\$upload_folder\s*=\s*\$base_upload_dir\s*\.\s*\$date_folder/',
    ];
    
    $found = false;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            echo "<p>✅ 현재 경로 발견:</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($matches[0]) . "</pre>";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "<p style='color: gray;'>ℹ️ 업로드 경로 패턴을 찾을 수 없습니다. (이미 수정되었거나 다른 방식 사용)</p>";
    }
    
    echo "<hr>";
}

echo "<h3>✅ 다음 단계</h3>";
echo "<ol>";
echo "<li>각 품목의 <code>add_to_basket.php</code>에서 업로드 경로를 다음과 같이 수정:</li>";
echo "<pre style='background: #e8f5e9; padding: 15px; border-left: 4px solid #4caf50;'>";
echo "\$base_upload_dir = \$_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/';\n";
echo "\$temp_folder_name = 'temp_' . \$session_id . '_' . time() . '/';\n";
echo "\$upload_folder = \$base_upload_dir . \$temp_folder_name;";
echo "</pre>";
echo "<li><code>shop/finalize_order.php</code>에서 주문 확정 시 폴더 이름을 주문번호로 변경</li>";
echo "<li>모든 품목이 동일한 경로 구조 사용: <code>mlangorder_printauto/upload/{주문번호}/</code></li>";
echo "</ol>";
?>
