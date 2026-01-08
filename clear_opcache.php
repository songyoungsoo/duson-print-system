<?php
/**
 * PHP OPcache 클리어 스크립트
 * http://dsp1830.shop/clear_opcache.php 접속
 */

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache cleared successfully!<br>";
        echo "Time: " . date('Y-m-d H:i:s') . "<br>";
        echo "Server: " . $_SERVER['HTTP_HOST'] . "<br>";
        echo "<br>Please reload your page.";
    } else {
        echo "❌ Failed to clear OPcache<br>";
        echo "Reason: May require Apache restart";
    }
} else {
    echo "⚠️ OPcache is not enabled on this server<br>";
    echo "PHP Version: " . PHP_VERSION;
}

echo "<hr>";
echo "OPcache Status:<br>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "<pre>";
    print_r($status);
    echo "</pre>";
}
?>
