<?php
echo "PHP is working!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Files in chat directory:<br>";
foreach (glob(__DIR__ . '/*') as $file) {
    echo basename($file) . "<br>";
}
?>
