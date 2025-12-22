<?php
/**
 * OPcache 강제 클리어 스크립트
 * 사용 후 즉시 삭제할 것
 */

// OPcache가 활성화되어 있는지 확인
if (function_exists('opcache_reset')) {
    // 전체 OPcache 리셋
    $result = opcache_reset();
    echo "OPcache reset: " . ($result ? "SUCCESS" : "FAILED") . "\n";
} else {
    echo "OPcache is not available\n";
}

// 특정 파일만 무효화
if (function_exists('opcache_invalidate')) {
    $file = __DIR__ . '/cart.php';
    $result = opcache_invalidate($file, true);
    echo "cart.php invalidate: " . ($result ? "SUCCESS" : "FAILED") . "\n";
}

// OPcache 상태 출력
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    echo "\nOPcache Status:\n";
    echo "- Enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
    echo "- Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "- Memory used: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
}

echo "\n완료! 이 파일을 즉시 삭제하세요.\n";
?>
