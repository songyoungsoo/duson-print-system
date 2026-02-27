<?php
declare(strict_types=1);

return [
    'name' => '두손기획인쇄',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => getenv('APP_DEBUG') === 'true',
    'url' => getenv('APP_URL') ?: 'https://dsp114.com',
    'timezone' => 'Asia/Seoul',
    'locale' => 'ko',
];
