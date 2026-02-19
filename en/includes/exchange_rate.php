<?php
/**
 * Exchange Rate Helper — KRW/USD
 * 
 * Fetches the previous business day's KRW/USD exchange rate from api.manana.kr.
 * Caches the result in a local JSON file for 24 hours.
 * 
 * Usage:
 *   include 'en/includes/exchange_rate.php';
 *   $rate = getExchangeRate(); // Returns array with rate, date, cached info
 *   $usd = convertKrwToUsd(92400, $rate['rate']); // "63.76"
 */

define('EXCHANGE_RATE_CACHE_FILE', __DIR__ . '/exchange_rate_cache.json');
define('EXCHANGE_RATE_CACHE_TTL', 86400);
define('EXCHANGE_RATE_API_URL', 'https://api.manana.kr/exchange/rate/KRW/USD.json');

/**
 * Get KRW/USD exchange rate with 24-hour file cache.
 * 
 * @return array{rate: float, date: string, cached: bool, source: string}
 */
function getExchangeRate() {
    if (file_exists(EXCHANGE_RATE_CACHE_FILE)) {
        $cacheContent = file_get_contents(EXCHANGE_RATE_CACHE_FILE);
        $cache = json_decode($cacheContent, true);
        
        if ($cache && isset($cache['fetched_at'])) {
            $age = time() - $cache['fetched_at'];
            if ($age < EXCHANGE_RATE_CACHE_TTL) {
                return [
                    'rate' => (float) $cache['rate'],
                    'date' => $cache['date'],
                    'cached' => true,
                    'source' => 'api.manana.kr',
                    'cache_age_hours' => round($age / 3600, 1)
                ];
            }
        }
    }
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'DusonPrint/1.0'
        ]
    ]);
    
    $response = @file_get_contents(EXCHANGE_RATE_API_URL, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (is_array($data) && !empty($data[0]) && isset($data[0]['rate'])) {
            $rate = (float) $data[0]['rate'];
            $date = $data[0]['date'] ?? date('Y-m-d');
            
            $dateOnly = substr($date, 0, 10);
            
            $cacheData = [
                'rate' => $rate,
                'date' => $dateOnly,
                'raw_date' => $date,
                'fetched_at' => time(),
                'fetched_date' => date('Y-m-d H:i:s')
            ];
            @file_put_contents(EXCHANGE_RATE_CACHE_FILE, json_encode($cacheData, JSON_PRETTY_PRINT));
            
            return [
                'rate' => $rate,
                'date' => $dateOnly,
                'cached' => false,
                'source' => 'api.manana.kr',
                'cache_age_hours' => 0
            ];
        }
    }
    
    if (file_exists(EXCHANGE_RATE_CACHE_FILE)) {
        $cache = json_decode(file_get_contents(EXCHANGE_RATE_CACHE_FILE), true);
        if ($cache && isset($cache['rate'])) {
            return [
                'rate' => (float) $cache['rate'],
                'date' => $cache['date'] ?? 'unknown',
                'cached' => true,
                'source' => 'api.manana.kr (stale cache)',
                'cache_age_hours' => round((time() - ($cache['fetched_at'] ?? 0)) / 3600, 1)
            ];
        }
    }
    
    return [
        'rate' => 1450.0,
        'date' => date('Y-m-d'),
        'cached' => false,
        'source' => 'fallback estimate',
        'cache_age_hours' => 0
    ];
}

/**
 * Convert KRW amount to USD string.
 * 
 * @param int|float $krw Amount in Korean Won
 * @param float $rate KRW per 1 USD
 * @return string Formatted USD amount (e.g., "63.76")
 */
function convertKrwToUsd($krw, $rate) {
    if ($rate <= 0) return '0.00';
    return number_format($krw / $rate, 2);
}

/**
 * Get exchange rate disclosure text for display.
 * 
 * @param array $rateInfo Return value from getExchangeRate()
 * @return string Human-readable disclosure
 */
function getExchangeRateDisclosure($rateInfo) {
    $date = $rateInfo['date'] ?? 'N/A';
    $rate = number_format($rateInfo['rate'], 2);
    return "Exchange rate: ₩{$rate} = \$1.00 USD (as of {$date}, source: Yahoo Finance via api.manana.kr). Rate updates daily. Actual charges may vary.";
}
