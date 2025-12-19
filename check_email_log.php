<?php
header('Content-Type: text/plain; charset=utf-8');

include "db.php";

echo "=== 이메일 발송 로그 확인 ===\n\n";

// 테이블 존재 확인
$table_check = safe_mysqli_query($db, "SHOW TABLES LIKE 'order_email_log'");
if (mysqli_num_rows($table_check) == 0) {
    echo "❌ order_email_log 테이블이 존재하지 않습니다.\n";
    echo "이메일 발송 시스템이 설치되지 않았을 수 있습니다.\n";
    exit;
}

echo "✅ order_email_log 테이블 존재\n\n";

// 최근 10개 로그 조회
$query = "SELECT id, order_no, recipient_email, sent_status, created_at, sent_at, error_message
          FROM order_email_log
          ORDER BY created_at DESC
          LIMIT 10";

$result = safe_mysqli_query($db, $query);

if (mysqli_num_rows($result) == 0) {
    echo "📭 이메일 발송 로그가 없습니다.\n";
} else {
    echo "📧 최근 10개 이메일 로그:\n\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: {$row['id']} | 주문번호: {$row['order_no']} | 수신자: {$row['recipient_email']}\n";
        echo "상태: {$row['sent_status']} | 생성: {$row['created_at']} | 발송: {$row['sent_at']}\n";
        if ($row['error_message']) {
            echo "에러: {$row['error_message']}\n";
        }
        echo "---\n";
    }
}

// 발송 통계
echo "\n=== 발송 통계 ===\n";
$stats_query = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN sent_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN sent_status = 'sent' THEN 1 ELSE 0 END) as sent,
    SUM(CASE WHEN sent_status = 'failed' THEN 1 ELSE 0 END) as failed
FROM order_email_log";

$stats_result = safe_mysqli_query($db, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

echo "전체: {$stats['total']}개\n";
echo "대기 중: {$stats['pending']}개\n";
echo "발송 완료: {$stats['sent']}개\n";
echo "발송 실패: {$stats['failed']}개\n";
?>
