<?php
/**
 * 주문 파일 확인 스크립트
 * 주문번호로 업로드된 파일 경로와 실제 파일 존재 여부 확인
 */

header('Content-Type: text/plain; charset=utf-8');

include "db.php";

$order_no = $_GET['order_no'] ?? '84198';

echo "=== 주문 파일 확인 ===\n";
echo "주문번호: $order_no\n\n";

// 먼저 mlangorder_printauto 테이블 확인
$query = "SELECT no, product_type, ImgFolder, ThingCate, uploaded_files FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 없으면 shop_temp (장바구니) 확인
if (!$row) {
    echo "⚠️ mlangorder_printauto에 없음, shop_temp 확인 중...\n\n";
    $query = "SELECT no, product_type, ImgFolder, ThingCate, uploaded_files, upload_folder FROM shop_temp WHERE no = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $order_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if ($row) {
    echo "📦 주문 정보:\n";
    echo "  - 품목: " . ($row['product_type'] ?? 'N/A') . "\n";
    echo "  - ImgFolder: " . ($row['ImgFolder'] ?? 'N/A') . "\n";
    echo "  - ThingCate: " . ($row['ThingCate'] ?? 'N/A') . "\n";
    echo "  - uploaded_files: " . ($row['uploaded_files'] ?? 'N/A') . "\n\n";
    
    // 파일 경로 확인
    if (!empty($row['ImgFolder'])) {
        $img_folder = $row['ImgFolder'];
        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $img_folder;
        
        echo "📁 파일 경로:\n";
        echo "  - 상대 경로: $img_folder\n";
        echo "  - 절대 경로: $full_path\n";
        echo "  - 폴더 존재: " . (file_exists($full_path) ? '✅ Yes' : '❌ No') . "\n\n";
        
        // 폴더 내 파일 목록
        if (file_exists($full_path) && is_dir($full_path)) {
            echo "📄 폴더 내 파일:\n";
            $files = scandir($full_path);
            $file_count = 0;
            
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $file_path = $full_path . '/' . $file;
                    $file_size = filesize($file_path);
                    echo "  ✅ $file (" . number_format($file_size) . " bytes)\n";
                    $file_count++;
                }
            }
            
            if ($file_count == 0) {
                echo "  ⚠️ 폴더가 비어있습니다.\n";
            }
            
            echo "\n총 파일 수: $file_count\n";
        } else {
            echo "❌ 폴더가 존재하지 않거나 접근할 수 없습니다.\n";
        }
    } else {
        echo "⚠️ ImgFolder 정보가 없습니다.\n";
    }
    
    // 다운로드 URL 생성
    echo "\n🔗 다운로드 URL:\n";
    echo "  - 개별: http://dsp1830.shop/admin/mlangprintauto/download.php?order_no=$order_no\n";
    echo "  - ZIP: http://dsp1830.shop/admin/mlangprintauto/download_all.php?order_no=$order_no\n";
    
} else {
    echo "❌ 주문을 찾을 수 없습니다.\n";
}

mysqli_stmt_close($stmt);
mysqli_close($db);
?>
