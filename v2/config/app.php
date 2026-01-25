<?php
declare(strict_types=1);

return [
    'name' => '두손기획인쇄',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => getenv('APP_DEBUG') === 'true',
    'url' => getenv('APP_URL') ?: 'https://dsp1830.shop',
    'timezone' => 'Asia/Seoul',
    'locale' => 'ko',
];
