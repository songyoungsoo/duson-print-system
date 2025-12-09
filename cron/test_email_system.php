<?php
/**
 * 이메일 시스템 테스트 스크립트
 * 두손기획인쇄 - 크론잡 및 이메일 발송 테스트
 *
 * 사용법: http://localhost/cron/test_email_system.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>📧 이메일 시스템 테스트</h1>";
echo "<hr>";

// DB 연결
require_once __DIR__ . '/../db.php';

if (!$db) {
    die("<p style='color:red;'>❌ 데이터베이스 연결 실패: " . mysqli_connect_error() . "</p>");
}

echo "<p style='color:green;'>✅ 데이터베이스 연결 성공</p>";

// OrderNotificationManager 로드
require_once __DIR__ . '/../includes/OrderNotificationManager.php';

echo "<p style='color:green;'>✅ OrderNotificationManager 로드 완료</p>";

// 1. 대기 중인 이메일 개수 확인
echo "<h2>1️⃣ 대기 중인 이메일 확인</h2>";

$pending_query = "SELECT COUNT(*) as count FROM order_email_log WHERE sent_status = 'pending'";
$result = mysqli_query($db, $pending_query);
$row = mysqli_fetch_assoc($result);
$pending_count = $row['count'];

echo "<p>대기 중인 이메일: <strong>{$pending_count}개</strong></p>";

if ($pending_count > 0) {
    // 대기 중인 이메일 목록 표시
    echo "<h3>대기 중인 이메일 목록:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>주문번호</th><th>유형</th><th>수신자</th><th>생성일시</th></tr>";

    $list_query = "SELECT id, order_no, email_type, recipient, created_at
                   FROM order_email_log
                   WHERE sent_status = 'pending'
                   ORDER BY created_at ASC
                   LIMIT 10";
    $result = mysqli_query($db, $list_query);

    while ($email = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$email['id']}</td>";
        echo "<td>{$email['order_no']}</td>";
        echo "<td>{$email['email_type']}</td>";
        echo "<td>{$email['recipient']}</td>";
        echo "<td>{$email['created_at']}</td>";
        echo "</tr>";
    }

    echo "</table>";
}

// 2. 테스트 이메일 큐 추가
echo "<hr>";
echo "<h2>2️⃣ 테스트 이메일 큐 추가</h2>";

// 첫 번째 주문 조회
$order_query = "SELECT * FROM mlangorder_printauto ORDER BY no DESC LIMIT 1";
$result = mysqli_query($db, $order_query);
$test_order = mysqli_fetch_assoc($result);

if ($test_order) {
    echo "<p>테스트 주문: #{$test_order['no']} - {$test_order['name']}</p>";

    // 테스트 이메일 주소 (실제 이메일로 변경 권장)
    $test_email = "test@example.com";

    // 테스트 이메일 큐에 추가
    $insert_query = "INSERT INTO order_email_log
                     (order_no, email_type, recipient, subject, body, sent_status, created_at)
                     VALUES (?, 'test', ?, '테스트 이메일', '크론잡 테스트입니다.', 'pending', NOW())";

    $stmt = mysqli_prepare($db, $insert_query);
    mysqli_stmt_bind_param($stmt, 'is', $test_order['no'], $test_email);

    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($db);
        echo "<p style='color:green;'>✅ 테스트 이메일 큐 추가 완료 (ID: {$new_id})</p>";
    } else {
        echo "<p style='color:red;'>❌ 테스트 이메일 큐 추가 실패: " . mysqli_error($db) . "</p>";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color:orange;'>⚠️ 주문이 없습니다. 주문을 먼저 생성하세요.</p>";
}

// 3. 크론잡 스크립트 실행 (수동)
echo "<hr>";
echo "<h2>3️⃣ 크론잡 수동 실행</h2>";

try {
    $notificationManager = new OrderNotificationManager($db);

    echo "<p>이메일 발송 시작...</p>";

    $result = $notificationManager->sendPendingEmails(5);

    echo "<p style='color:green;'>✅ 발송 완료: {$result['sent']}개</p>";
    echo "<p style='color:red;'>❌ 발송 실패: {$result['failed']}개</p>";

    if (!empty($result['errors'])) {
        echo "<h3>발송 실패 상세:</h3>";
        echo "<ul>";
        foreach ($result['errors'] as $error) {
            echo "<li>이메일 ID {$error['email_id']}: {$error['error']}</li>";
        }
        echo "</ul>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ 예외 발생: " . $e->getMessage() . "</p>";
}

// 4. 발송 결과 확인
echo "<hr>";
echo "<h2>4️⃣ 발송 결과 확인</h2>";

$stats_query = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN sent_status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN sent_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN sent_status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM order_email_log";

$result = mysqli_query($db, $stats_query);
$stats = mysqli_fetch_assoc($result);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>전체</th><th>발송 완료</th><th>대기 중</th><th>발송 실패</th></tr>";
echo "<tr>";
echo "<td>{$stats['total']}</td>";
echo "<td style='color:green;'>{$stats['sent']}</td>";
echo "<td style='color:orange;'>{$stats['pending']}</td>";
echo "<td style='color:red;'>{$stats['failed']}</td>";
echo "</tr>";
echo "</table>";

// 5. 최근 발송 이력
echo "<hr>";
echo "<h2>5️⃣ 최근 발송 이력 (최대 10개)</h2>";

$history_query = "SELECT id, order_no, email_type, recipient, sent_status, sent_at, error_message
                  FROM order_email_log
                  ORDER BY id DESC
                  LIMIT 10";

$result = mysqli_query($db, $history_query);

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; font-size: 12px;'>";
echo "<tr><th>ID</th><th>주문번호</th><th>유형</th><th>수신자</th><th>상태</th><th>발송일시</th><th>에러</th></tr>";

while ($log = mysqli_fetch_assoc($result)) {
    $status_color = [
        'sent' => 'green',
        'pending' => 'orange',
        'failed' => 'red'
    ][$log['sent_status']] ?? 'black';

    echo "<tr>";
    echo "<td>{$log['id']}</td>";
    echo "<td>{$log['order_no']}</td>";
    echo "<td>{$log['email_type']}</td>";
    echo "<td>{$log['recipient']}</td>";
    echo "<td style='color:{$status_color};'><strong>{$log['sent_status']}</strong></td>";
    echo "<td>{$log['sent_at']}</td>";
    echo "<td>" . htmlspecialchars($log['error_message'] ?? '') . "</td>";
    echo "</tr>";
}

echo "</table>";

// 6. 로그 파일 확인
echo "<hr>";
echo "<h2>6️⃣ 크론 로그 파일</h2>";

$log_file = __DIR__ . '/email_cron.log';

if (file_exists($log_file)) {
    echo "<p>로그 파일: <code>{$log_file}</code></p>";
    echo "<h3>최근 로그 (최대 30줄):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: scroll;'>";

    $log_lines = file($log_file);
    $recent_lines = array_slice($log_lines, -30);

    echo htmlspecialchars(implode('', $recent_lines));
    echo "</pre>";
} else {
    echo "<p style='color:orange;'>⚠️ 로그 파일이 아직 생성되지 않았습니다.</p>";
    echo "<p>크론잡을 실행하거나 send_emails.php를 직접 실행하세요.</p>";
}

// 7. 다음 단계 안내
echo "<hr>";
echo "<h2>7️⃣ 다음 단계</h2>";
echo "<ol>";
echo "<li><strong>수동 테스트</strong>: <code>C:\\xampp\\htdocs\\cron\\run_email_cron.bat</code> 실행</li>";
echo "<li><strong>Windows Task Scheduler 설정</strong>: 5분마다 자동 실행 등록</li>";
echo "<li><strong>실제 이메일 테스트</strong>: SMTP 설정 후 실제 이메일 발송 확인</li>";
echo "<li><strong>모니터링</strong>: 로그 파일 및 DB 통계 주기적 확인</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>테스트 완료: " . date('Y-m-d H:i:s') . "</em></p>";
?>
