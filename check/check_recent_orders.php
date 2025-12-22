<?php
header('Content-Type: text/plain; charset=utf-8');
include "db.php";

echo "=== 최근 주문 확인 ===\n\n";

// 최근 mlangorder_printauto 주문 5개
echo "📦 mlangorder_printauto (최근 5개):\n";
$query = "SELECT no, product_type, ImgFolder, created_at FROM mlangorder_printauto ORDER BY no DESC LIMIT 5";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    echo sprintf("  #%d - %s - ImgFolder: %s\n", 
        $row['no'], 
        $row['product_type'] ?? 'N/A',
        $row['ImgFolder'] ? substr($row['ImgFolder'], 0, 50) . '...' : 'NULL'
    );
}

echo "\n📦 shop_temp (최근 5개):\n";
$query = "SELECT no, product_type, ImgFolder, upload_folder FROM shop_temp ORDER BY no DESC LIMIT 5";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $folder = $row['ImgFolder'] ?? $row['upload_folder'] ?? 'NULL';
    echo sprintf("  #%d - %s - Folder: %s\n", 
        $row['no'], 
        $row['product_type'] ?? 'N/A',
        $folder ? substr($folder, 0, 50) . '...' : 'NULL'
    );
}

mysqli_close($db);
?>
