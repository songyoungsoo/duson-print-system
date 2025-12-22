<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Current directory: " . __DIR__ . "<br>";
echo "Current file: " . __FILE__ . "<br>";
echo "<br>";

// 가능한 경로들 테스트
$possible_paths = [
    "../../db.php",
    "../db.php",
    "/dsp1830/www/db.php",
    "/var/www/html/db.php",
    dirname(__DIR__) . "/db.php",
    dirname(dirname(__DIR__)) . "/db.php"
];

foreach ($possible_paths as $path) {
    $full_path = realpath($path);
    if ($full_path) {
        echo "✅ EXISTS: $path → $full_path<br>";
    } else {
        echo "❌ NOT FOUND: $path<br>";
    }
}

echo "<br>Directory listing of parent:<br>";
$parent = dirname(__DIR__);
if (is_dir($parent)) {
    $files = scandir($parent);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file<br>";
        }
    }
}
?>
