<?php
// opcache 초기화 (사용 후 삭제 권장)
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ opcache_reset() 완료<br>";
}

if (function_exists('opcache_invalidate')) {
    $files = [
        '/var/www/html/mlangprintauto/quote/includes/ProductSpecFormatter.php',
        '/var/www/html/mlangprintauto/quote/includes/QuoteManager.php',
        '/var/www/html/mlangprintauto/quote/create.php',
        '/var/www/html/mlangprintauto/quote/api/save.php',
        '/var/www/html/mlangprintauto/inserted/add_to_basket.php',
        '/var/www/html/mlangprintauto/inserted/index.php',
        '/var/www/html/mlangprintauto/inserted/get_quantities.php',
        '/var/www/html/mlangprintauto/quote/includes/calculator_modal.js'
    ];
    foreach ($files as $file) {
        opcache_invalidate($file, true);
        echo "✅ invalidated: $file<br>";
    }
}

echo "<br><strong>완료! 이제 견적서를 다시 생성해 보세요.</strong>";
echo "<br><br><a href='/mlangprintauto/quote/create.php?from=cart'>견적서 생성으로 이동</a>";
