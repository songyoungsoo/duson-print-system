<?php
// OPcache 클리어
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared!<br>";
} else {
    echo "OPcache not available<br>";
}

// APCu 클리어
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "APCu cleared!<br>";
}

echo "<br>Cache cleared. <a href='orderlist.php'>Go to orderlist.php</a>";
?>
