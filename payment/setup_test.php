<?php
/**
 * 결제 시스템 자동 설정 및 테스트
 * 두손기획인쇄 - 원클릭 설정
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><meta charset='UTF-8'><title>결제 시스템 설정</title>";
echo "<style>
body { font-family: sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
.section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h1 { color: #2c3e50; }
h2 { color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
.success { color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0; }
.error { color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0; }
.info { color: #004085; padding: 10px; background: #d1ecf1; border-left: 4px solid #17a2b8; margin: 10px 0; }
.btn { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
.btn:hover { background: #2980b9; }
pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🚀 KG이니시스 결제 시스템 자동 설정</h1>";

// DB 연결
require_once __DIR__ . '/../db.php';

if (!$db) {
    echo "<div class='error'>❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "</div>";
    die("</body></html>");
}

echo "<div class='success'>✅ 데이터베이스 연결 성공</div>";

// Step 1: 테이블 생성
echo "<div class='section'>";
echo "<h2>📋 Step 1: 결제 테이블 생성</h2>";

$tables_to_create = [
    'order_payment_log' => "CREATE TABLE IF NOT EXISTS order_payment_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_no INT NOT NULL,
        pg_name VARCHAR(20) NOT NULL DEFAULT 'inicis',
        tid VARCHAR(100) NOT NULL,
        pay_method VARCHAR(20) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        result_code VARCHAR(10) NOT NULL,
        result_msg VARCHAR(255) DEFAULT NULL,
        paid_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order_no (order_no),
        INDEX idx_tid (tid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'order_refund_log' => "CREATE TABLE IF NOT EXISTS order_refund_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        payment_log_id INT NOT NULL,
        order_no INT NOT NULL,
        tid VARCHAR(100) NOT NULL,
        refund_amount DECIMAL(10,2) NOT NULL,
        refund_reason VARCHAR(500) DEFAULT NULL,
        result_code VARCHAR(10) NOT NULL,
        result_msg VARCHAR(255) DEFAULT NULL,
        refunded_at DATETIME NOT NULL,
        refunded_by VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_payment_log_id (payment_log_id),
        INDEX idx_order_no (order_no)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'order_vbank_pending' => "CREATE TABLE IF NOT EXISTS order_vbank_pending (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_no INT NOT NULL,
        vbank_num VARCHAR(50) NOT NULL,
        vbank_name VARCHAR(50) NOT NULL,
        vbank_holder VARCHAR(50) NOT NULL,
        vbank_date VARCHAR(20) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'paid', 'expired') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        paid_at DATETIME DEFAULT NULL,
        INDEX idx_order_no (order_no),
        INDEX idx_vbank_num (vbank_num)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tables_to_create as $table_name => $create_sql) {
    if (mysqli_query($db, $create_sql)) {
        echo "<div class='success'>✅ {$table_name} 테이블 생성 완료</div>";
    } else {
        echo "<div class='error'>❌ {$table_name} 생성 실패: " . mysqli_error($db) . "</div>";
    }
}

echo "</div>";

// Step 2: 테스트 주문 생성
echo "<div class='section'>";
echo "<h2>🛒 Step 2: 테스트 주문 생성</h2>";

// 기존 테스트 주문 확인
$check_query = "SELECT * FROM mlangorder_printauto WHERE name = 'TEST_USER' AND OrderStyle = 'pending' ORDER BY no DESC LIMIT 1";
$result = mysqli_query($db, $check_query);
$existing_order = mysqli_fetch_assoc($result);

$test_order_no = 0;

if ($existing_order) {
    $test_order_no = $existing_order['no'];
    echo "<div class='info'>ℹ️ 기존 테스트 주문 사용: #{$test_order_no}</div>";
} else {
    // 새 테스트 주문 생성
    $insert_query = "INSERT INTO mlangorder_printauto
                     (Type, Product, name, phone1, email, money_1, OrderStyle, date)
                     VALUES
                     ('스티커', '테스트 스티커 100매 (10,000원)', 'TEST_USER', '010-1234-5678', 'test@example.com', 10000, 'pending', NOW())";

    if (mysqli_query($db, $insert_query)) {
        $test_order_no = mysqli_insert_id($db);
        echo "<div class='success'>✅ 테스트 주문 생성 완료: #{$test_order_no}</div>";
    } else {
        echo "<div class='error'>❌ 테스트 주문 생성 실패: " . mysqli_error($db) . "</div>";
    }
}

if ($test_order_no > 0) {
    // 주문 정보 표시
    $order_query = "SELECT * FROM mlangorder_printauto WHERE no = {$test_order_no}";
    $order_result = mysqli_query($db, $order_query);
    $order = mysqli_fetch_assoc($order_result);

    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
    echo "<strong>📦 주문 정보:</strong><br>";
    echo "주문번호: #{$order['no']}<br>";
    echo "상품명: {$order['Product']}<br>";
    echo "금액: " . number_format($order['money_1']) . "원<br>";
    echo "주문자: {$order['name']}<br>";
    echo "상태: {$order['OrderStyle']}<br>";
    echo "</div>";
}

echo "</div>";

// Step 3: 설정 확인
echo "<div class='section'>";
echo "<h2>⚙️ Step 3: KG이니시스 설정 확인</h2>";

require_once __DIR__ . '/inicis_config.php';

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<strong>테스트 모드:</strong> " . (INICIS_TEST_MODE ? '<span style="color: orange;">✓ 활성화 (실제 결제 안 됨)</span>' : '<span style="color: red;">운영 모드</span>') . "<br>";
echo "<strong>상점 아이디:</strong> " . INICIS_MID . "<br>";
echo "<strong>결제 수단:</strong> " . INICIS_PAYMENT_METHODS . "<br>";
echo "<strong>Return URL:</strong> " . INICIS_RETURN_URL . "<br>";
echo "</div>";

echo "<div class='info' style='margin-top: 15px;'>";
echo "ℹ️ <strong>테스트 카드 정보:</strong><br>";
echo "신한카드: 9410-0000-0000-0008<br>";
echo "유효기간, 비밀번호, 생년월일은 아무 숫자나 입력<br>";
echo "<small>(예: 유효기간 1225, 비밀번호 12, 생년월일 850101)</small>";
echo "</div>";

echo "</div>";

// Step 4: 로그 디렉토리 생성
echo "<div class='section'>";
echo "<h2>📁 Step 4: 로그 디렉토리 생성</h2>";

$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    if (mkdir($log_dir, 0755, true)) {
        echo "<div class='success'>✅ 로그 디렉토리 생성 완료: {$log_dir}</div>";
    } else {
        echo "<div class='error'>❌ 로그 디렉토리 생성 실패</div>";
    }
} else {
    echo "<div class='success'>✅ 로그 디렉토리 이미 존재: {$log_dir}</div>";
}

echo "</div>";

// Step 5: 테이블 존재 확인
echo "<div class='section'>";
echo "<h2>✅ Step 5: 최종 확인</h2>";

$tables_check = ['order_payment_log', 'order_refund_log', 'order_vbank_pending'];
$all_tables_exist = true;

foreach ($tables_check as $table) {
    $check = mysqli_query($db, "SHOW TABLES LIKE '{$table}'");
    if (mysqli_num_rows($check) > 0) {
        echo "<div class='success'>✅ {$table} 테이블 존재</div>";
    } else {
        echo "<div class='error'>❌ {$table} 테이블 없음</div>";
        $all_tables_exist = false;
    }
}

echo "</div>";

// 다음 단계 안내
echo "<div class='section'>";
echo "<h2>🎯 다음 단계</h2>";

if ($all_tables_exist && $test_order_no > 0) {
    echo "<div class='success'>";
    echo "<h3>✅ 설정 완료! 이제 테스트를 시작하세요.</h3>";
    echo "</div>";

    echo "<div style='margin: 20px 0;'>";
    echo "<a href='test_payment.php' class='btn'>🧪 테스트 페이지로 이동</a>";
    echo "<a href='inicis_request.php?order_no={$test_order_no}' class='btn' style='background: #27ae60;'>💳 바로 결제 테스트</a>";
    echo "</div>";

    echo "<div class='info'>";
    echo "<strong>📝 테스트 순서:</strong><br>";
    echo "1. 위 '바로 결제 테스트' 버튼 클릭<br>";
    echo "2. 결제하기 버튼 클릭 (이니시스 결제창 팝업)<br>";
    echo "3. 테스트 카드 입력: 9410-0000-0000-0008<br>";
    echo "4. 결제 완료 확인<br>";
    echo "5. DB 확인: <code>SELECT * FROM order_payment_log;</code>";
    echo "</div>";

    echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px;'>";
    echo "<strong>⚠️ 주의사항:</strong><br>";
    echo "• 팝업 차단이 해제되어 있어야 합니다<br>";
    echo "• XAMPP Apache와 MySQL이 실행 중이어야 합니다<br>";
    echo "• 테스트 모드에서는 실제 결제가 발생하지 않습니다";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ 설정 오류</h3>";
    echo "위의 오류를 해결한 후 페이지를 새로고침하세요.";
    echo "</div>";
}

echo "</div>";

// 유용한 링크
echo "<div class='section'>";
echo "<h2>🔗 유용한 링크</h2>";
echo "<ul>";
echo "<li><a href='test_payment.php' target='_blank'>테스트 페이지</a></li>";
echo "<li><a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
echo "<li><a href='../docs/PAYMENT_SYSTEM_GUIDE.md' target='_blank'>결제 시스템 가이드 문서</a></li>";
echo "<li><a href='https://manual.inicis.com/' target='_blank'>KG이니시스 매뉴얼</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
