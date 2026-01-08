<?php
/**
 * 주문 페이지 문제 진단 스크립트
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$session_id = session_id();
$issues = [];
$fixes = [];

echo "<html><head><meta charset='UTF-8'><title>주문 페이지 진단</title></head><body>";
echo "<h1>🔍 주문 페이지 문제 진단</h1>";

// 1. 세션 확인
echo "<h2>1. 세션 상태</h2>";
echo "현재 세션 ID: <code>$session_id</code><br>";
echo "세션 저장 경로: " . session_save_path() . "<br>";
if (empty(session_save_path()) || !is_writable(session_save_path())) {
    $issues[] = "❌ 세션 저장 경로에 쓰기 권한 없음";
    $fixes[] = "sudo chmod 777 " . session_save_path();
}

// 2. 데이터베이스 연결
echo "<h2>2. 데이터베이스</h2>";
if ($db) {
    echo "✅ 데이터베이스 연결 성공<br>";
} else {
    echo "❌ 데이터베이스 연결 실패<br>";
    $issues[] = "데이터베이스 연결 실패";
}

// 3. shop_temp 테이블
echo "<h2>3. 장바구니 데이터</h2>";
$query = "SELECT COUNT(*) as count FROM shop_temp WHERE session_id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$cart_count = $row['count'];

if ($cart_count == 0) {
    echo "<span style='color: red;'>❌ 현재 세션에 장바구니 데이터 없음</span><br>";
    $issues[] = "장바구니가 비어있음";
    $fixes[] = "상품 페이지에서 장바구니에 상품을 추가하세요";
    
    // 다른 세션 확인
    $all_query = "SELECT session_id, COUNT(*) as count FROM shop_temp GROUP BY session_id LIMIT 5";
    $all_result = mysqli_query($db, $all_query);
    echo "<p>다른 세션의 장바구니:</p><ul>";
    while ($sess = mysqli_fetch_assoc($all_result)) {
        echo "<li>{$sess['session_id']}: {$sess['count']}개</li>";
    }
    echo "</ul>";
} else {
    echo "<span style='color: green;'>✅ 장바구니에 {$cart_count}개 아이템</span><br>";
}

// 4. 필수 파일 확인
echo "<h2>4. 필수 파일 존재 여부</h2>";
$required_files = [
    '/var/www/html/mlangorder_printauto/OnlineOrder_unified.php',
    '/var/www/html/mlangprintauto/shop/cart.php',
    '/var/www/html/includes/ProductSpecFormatter.php',
    '/var/www/html/includes/AdditionalOptionsDisplay.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ " . basename($file) . "<br>";
    } else {
        echo "❌ " . basename($file) . " 없음<br>";
        $issues[] = basename($file) . " 파일 없음";
    }
}

// 5. PHP 오류 로그
echo "<h2>5. 최근 PHP 오류 (OnlineOrder 관련)</h2>";
$log_file = '/var/log/apache2/error.log';
if (file_exists($log_file)) {
    $log_lines = shell_exec("grep 'OnlineOrder' $log_file | tail -10");
    if (empty($log_lines)) {
        echo "✅ 최근 OnlineOrder 관련 오류 없음<br>";
    } else {
        echo "<pre style='background: #f0f0f0; padding: 10px;'>$log_lines</pre>";
    }
}

// 결과 요약
echo "<h2>📋 진단 결과</h2>";
if (empty($issues)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 4px; color: #155724;'>";
    echo "<h3>✅ 시스템 정상!</h3>";
    echo "<p>주문 페이지가 정상 작동해야 합니다.</p>";
    if ($cart_count > 0) {
        echo "<p><a href='/mlangorder_printauto/OnlineOrder_unified.php' style='padding: 10px 20px; background: #1976d2; color: white; text-decoration: none; border-radius: 4px;'>주문 페이지로 이동</a></p>";
    } else {
        echo "<p><a href='/mlangprintauto/namecard/index.php'>먼저 상품을 장바구니에 추가하세요</a></p>";
    }
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 4px; color: #721c24;'>";
    echo "<h3>⚠️ 발견된 문제:</h3>";
    echo "<ol>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ol>";
    echo "<h3>🔧 해결 방법:</h3>";
    echo "<ol>";
    foreach ($fixes as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
mysqli_close($db);
?>
