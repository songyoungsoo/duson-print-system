<?php
// 에러 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>OrderView 디버깅</h2>";
echo "<pre>";

echo "=== Step 1: db.php include ===\n";
include "../../db.php";

if (isset($db) && $db) {
    echo "✅ \$db is set and connected\n";
    echo "Database type: " . get_class($db) . "\n";
} else {
    echo "❌ \$db is NOT set\n";
}

echo "\n=== Step 2: config.php include ===\n";
// config.php는 Basic Auth를 요구하므로 일단 건너뜁니다
// include "../config.php";
echo "Skipped for debugging\n";

echo "\n=== Step 3: Check variables ===\n";
echo "isset(\$host): " . (isset($host) ? "YES - value: '$host'" : "NO") . "\n";
echo "isset(\$user): " . (isset($user) ? "YES - value: '$user'" : "NO") . "\n";
echo "isset(\$password): " . (isset($password) ? "YES - value: '$password'" : "NO") . "\n";
echo "isset(\$dataname): " . (isset($dataname) ? "YES - value: '$dataname'" : "NO") . "\n";

echo "\n=== Step 4: Direct query test ===\n";
if ($db) {
    $result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM MlangOrder_PrintAuto");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Query successful - Order count: " . $row['cnt'] . "\n";
    } else {
        echo "❌ Query failed: " . mysqli_error($db) . "\n";
    }
}

echo "\n=== Step 5: Check OrderFormOrderTree.php ===\n";
$orderform_path = "../../MlangOrder_PrintAuto/OrderFormOrderTree.php";
if (file_exists($orderform_path)) {
    echo "✅ OrderFormOrderTree.php exists\n";
    
    // 파일 내용 확인
    $content = file_get_contents($orderform_path);
    if (strpos($content, 'new mysqli') !== false) {
        echo "⚠️  WARNING: OrderFormOrderTree.php contains 'new mysqli'\n";
        
        // 어디에 있는지 찾기
        $lines = explode("\n", $content);
        foreach ($lines as $line_num => $line) {
            if (strpos($line, 'new mysqli') !== false) {
                echo "   Line " . ($line_num + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "✅ OrderFormOrderTree.php does not contain 'new mysqli'\n";
    }
} else {
    echo "❌ OrderFormOrderTree.php not found\n";
}

echo "\n=== Step 6: Check included files ===\n";
$included_files = get_included_files();
foreach ($included_files as $file) {
    if (strpos($file, 'mlangprintauto') !== false || strpos($file, 'MlangOrder') !== false) {
        echo "Included: " . basename($file) . "\n";
    }
}

echo "</pre>";

echo '<br><br>';
echo '<a href="admin.php?mode=OrderView&no=90008" style="background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">🔍 OrderView 테스트</a>';
?>