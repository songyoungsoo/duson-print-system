<?php
/**
 * 주문 프로세스 테스트 스크립트
 */

session_start();
$test_session_id = 'test_' . time();
$_SESSION['test_mode'] = true;

include "db.php";
mysqli_set_charset($db, "utf8");

echo "<h1>주문 프로세스 테스트</h1>";
echo "<hr>";

// 1. 장바구니 테스트 데이터 확인
echo "<h2>1. 장바구니 상태 확인</h2>";
$query = "SELECT session_id, product_type, st_price, st_price_vat, created_at
          FROM shop_temp
          ORDER BY created_at DESC
          LIMIT 5";
$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>세션ID</th><th>제품</th><th>가격</th><th>VAT포함</th><th>생성일시</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars(substr($row['session_id'], 0, 20)) . "...</td>";
        echo "<td>" . htmlspecialchars($row['product_type']) . "</td>";
        echo "<td>" . number_format($row['st_price']) . "원</td>";
        echo "<td>" . number_format($row['st_price_vat']) . "원</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 첫 번째 세션으로 테스트
    mysqli_data_seek($result, 0);
    $test_cart = mysqli_fetch_assoc($result);
    $test_session_id = $test_cart['session_id'];
    echo "<p><strong>테스트 세션 ID:</strong> " . htmlspecialchars($test_session_id) . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ 장바구니에 데이터가 없습니다. 먼저 제품을 장바구니에 추가해주세요.</p>";
    echo "<p><a href='/mlangprintauto/namecard/'>명함 페이지에서 테스트 주문 추가하기</a></p>";
}

echo "<hr>";

// 2. 최근 주문 내역 확인
echo "<h2>2. 최근 주문 내역 (최근 5건)</h2>";
$query = "SELECT no, name_1, email, Type, money_4, money_5, date
          FROM mlangorder_printauto
          ORDER BY no DESC
          LIMIT 5";
$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>주문번호</th><th>주문자</th><th>이메일</th><th>제품</th><th>가격</th><th>VAT포함</th><th>주문일시</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['no']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name_1']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . number_format($row['money_4']) . "원</td>";
        echo "<td>" . number_format($row['money_5']) . "원</td>";
        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>주문 내역이 없습니다.</p>";
}

echo "<hr>";

// 3. ProcessOrder_unified.php 파일 존재 및 권한 확인
echo "<h2>3. 주문 처리 파일 점검</h2>";
$process_file = __DIR__ . "/mlangorder_printauto/ProcessOrder_unified.php";
if (file_exists($process_file)) {
    echo "<p>✅ ProcessOrder_unified.php 존재</p>";
    echo "<p>파일 크기: " . number_format(filesize($process_file)) . " bytes</p>";
    echo "<p>수정 시간: " . date('Y-m-d H:i:s', filemtime($process_file)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ ProcessOrder_unified.php 파일이 없습니다!</p>";
}

$complete_file = __DIR__ . "/mlangorder_printauto/OrderComplete_universal.php";
if (file_exists($complete_file)) {
    echo "<p>✅ OrderComplete_universal.php 존재</p>";
    echo "<p>파일 크기: " . number_format(filesize($complete_file)) . " bytes</p>";
    echo "<p>수정 시간: " . date('Y-m-d H:i:s', filemtime($complete_file)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ OrderComplete_universal.php 파일이 없습니다!</p>";
}

echo "<hr>";

// 4. 테스트 주문서 링크
if (isset($test_session_id) && !empty($test_session_id)) {
    echo "<h2>4. 테스트 주문하기</h2>";
    echo "<p>아래 링크로 주문서를 작성하고 테스트 주문을 진행하세요:</p>";
    echo "<p><a href='/mlangorder_printauto/OnlineOrder_unified.php?session_id=" . urlencode($test_session_id) . "' target='_blank' style='font-size: 18px; color: blue; text-decoration: underline;'>📋 테스트 주문서 작성하기</a></p>";
}

echo "<hr>";

// 5. PHP 에러 로그 확인 (최근 10줄)
echo "<h2>5. 최근 PHP 에러 로그 (주문 관련)</h2>";
$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file)) {
    $log_lines = array_slice(file($log_file), -20);
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: scroll;'>";
    foreach ($log_lines as $line) {
        if (stripos($line, '주문') !== false || stripos($line, 'order') !== false || stripos($line, 'process') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p>에러 로그 파일을 찾을 수 없습니다.</p>";
}

mysqli_close($db);
?>

<style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin: 10px 0;
}
th {
    background: #4CAF50;
    color: white;
    padding: 10px;
}
td {
    padding: 8px;
}
tr:nth-child(even) {
    background: #f2f2f2;
}
</style>
