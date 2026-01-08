<?php
/**
 * ========================================
 * 캐시 클리어 스크립트
 * ========================================
 *
 * 용도: PHP opcache와 브라우저 캐시를 강제로 클리어
 * 실행: http://localhost/clear_cache.php
 *
 * ⚠️ 사용 후 반드시 삭제하세요!
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='utf-8'><title>캐시 클리어</title></head><body>";
echo "<h1>🧹 캐시 클리어 실행</h1>";

// 1. PHP opcache 클리어
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<p>✅ PHP opcache 클리어 성공</p>";
    } else {
        echo "<p>❌ PHP opcache 클리어 실패</p>";
    }
} else {
    echo "<p>ℹ️ PHP opcache가 활성화되지 않았습니다</p>";
}

// 2. PHP realpath cache 클리어
clearstatcache(true);
echo "<p>✅ PHP realpath cache 클리어 완료</p>";

// 3. 브라우저 캐시 클리어 안내
echo "<hr>";
echo "<h2>🌐 브라우저 캐시 클리어 방법</h2>";
echo "<ol>";
echo "<li><strong>Chrome/Edge:</strong> Ctrl+Shift+Delete → 캐시된 이미지 및 파일 → 삭제</li>";
echo "<li><strong>또는:</strong> DevTools(F12) → Network 탭 → Disable cache 체크</li>";
echo "<li><strong>강제 새로고침:</strong> Ctrl+F5 또는 Ctrl+Shift+R</li>";
echo "</ol>";

echo "<hr>";
echo "<h2>📋 확인 사항</h2>";
echo "<ul>";
echo "<li>이 페이지를 본 후 <strong>Ctrl+F5</strong>로 강제 새로고침하세요</li>";
echo "<li>카다록, 봉투, 자석스티커 페이지를 각각 <strong>Ctrl+F5</strong>로 새로고침하세요</li>";
echo "<li>테스트 후 이 파일(<code>clear_cache.php</code>)을 삭제하세요</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>현재 시간:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='javascript:location.reload(true);'>🔄 이 페이지 강제 새로고침</a></p>";

echo "</body></html>";
?>
