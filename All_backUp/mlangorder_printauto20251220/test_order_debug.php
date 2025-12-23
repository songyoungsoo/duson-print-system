<?php
/**
 * 주문 처리 디버깅 페이지
 * 경로: mlangorder_printauto/test_order_debug.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../db.php";
$connect = $db;
mysqli_set_charset($connect, 'utf8mb4');

echo "<h1>주문 처리 디버깅</h1>";
echo "<hr>";

// 1. PHP 버전 확인
echo "<h2>1. PHP 버전</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "<hr>";

// 2. upload_path_manager.php 로드 확인
echo "<h2>2. upload_path_manager.php 로드 확인</h2>";
if (file_exists("../includes/upload_path_manager.php")) {
    include "../includes/upload_path_manager.php";
    echo "✅ upload_path_manager.php 파일 존재<br>";
    
    if (function_exists('validateUploadPathInfo')) {
        echo "✅ validateUploadPathInfo() 함수 존재<br>";
    } else {
        echo "❌ validateUploadPathInfo() 함수 없음<br>";
    }
    
    if (function_exists('generateUploadPath')) {
        echo "✅ generateUploadPath() 함수 존재<br>";
    } else {
        echo "❌ generateUploadPath() 함수 없음<br>";
    }
} else {
    echo "❌ upload_path_manager.php 파일 없음<br>";
}
echo "<hr>";

// 3. 최근 shop_temp 데이터 확인
echo "<h2>3. 최근 shop_temp 데이터 (ImgFolder/ThingCate)</h2>";
$query = "SELECT no, product_type, ImgFolder, ThingCate, created_at 
          FROM shop_temp 
          WHERE ImgFolder IS NOT NULL 
          ORDER BY no DESC 
          LIMIT 5";

$result = mysqli_query($connect, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>No</th><th>Product</th><th>ImgFolder</th><th>ThingCate</th><th>Created</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['no']}</td>";
        echo "<td>{$row['product_type']}</td>";
        echo "<td style='font-size:10px;'>{$row['ImgFolder']}</td>";
        echo "<td>{$row['ThingCate']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "데이터 없음 또는 오류: " . mysqli_error($connect);
}
echo "<hr>";

// 4. 최근 mlangorder_printauto 데이터 확인 (전체 최신 10개)
echo "<h2>4. 최근 mlangorder_printauto 데이터 (전체 최신 10개)</h2>";
$query2 = "SELECT No, Type, ImgFolder, ThingCate, name, date 
           FROM mlangorder_printauto 
           ORDER BY No DESC 
           LIMIT 10";

$result2 = mysqli_query($connect, $query2);

if ($result2 && mysqli_num_rows($result2) > 0) {
    echo "<table border='1' cellpadding='5' style='width:100%;'>";
    echo "<tr style='background:#e0e0e0;'>";
    echo "<th>No</th><th>Type</th><th>Name</th><th>ImgFolder</th><th>ThingCate</th><th>Date</th><th>상세</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result2)) {
        $is_legacy = strpos($row['ImgFolder'], '_MlangPrintAuto_') === 0;
        $row_color = $is_legacy ? '#e8f5e9' : '#fff';
        
        echo "<tr style='background:{$row_color};'>";
        echo "<td><strong>{$row['No']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td style='font-size:9px; max-width:300px; word-break:break-all;'>";
        if ($is_legacy) {
            echo "✅ <strong style='color:green;'>레거시</strong><br>";
        }
        echo $row['ImgFolder'];
        echo "</td>";
        echo "<td style='font-size:10px;'>{$row['ThingCate']}</td>";
        echo "<td>{$row['date']}</td>";
        echo "<td><a href='admin.php?mode=OrderView&no={$row['No']}' target='_blank'>보기</a></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p style='color:green;'>✅ = 신버전 레거시 경로 형식</p>";
} else {
    echo "데이터 없음 또는 오류: " . mysqli_error($connect);
}
echo "<hr>";

// 5. 에러 로그 확인 (최근 10줄)
echo "<h2>5. PHP 에러 로그 (최근 항목)</h2>";
$error_log_path = ini_get('error_log');
echo "에러 로그 경로: " . ($error_log_path ?: '기본 경로') . "<br><br>";

if ($error_log_path && file_exists($error_log_path)) {
    $lines = file($error_log_path);
    $recent_lines = array_slice($lines, -20);
    
    echo "<pre style='background:#f5f5f5; padding:10px; overflow:auto; max-height:300px;'>";
    foreach ($recent_lines as $line) {
        if (strpos($line, 'ImgFolder') !== false || strpos($line, 'ProcessOrder') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "에러 로그 파일에 접근할 수 없습니다.<br>";
}

mysqli_close($connect);

echo "<hr>";
echo "<p><a href='OnlineOrder_unified.php'>← 주문 페이지로 돌아가기</a></p>";
?>
