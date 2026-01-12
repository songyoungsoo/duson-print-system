<?php
/**
 * 알림 재발송 API
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/services/NotificationService.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => '인증이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST 요청만 허용됩니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 최근 7일 이내 실패한 알림 조회
    $query = "SELECT * FROM notification_logs
              WHERE status = 'failed'
              AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
              ORDER BY created_at DESC
              LIMIT 50";
    $result = mysqli_query($db, $query);

    if (!$result) {
        throw new Exception('알림 조회 실패');
    }

    $notification = new NotificationService($db);
    $success = 0;
    $failed = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $sendResult = $notification->send([
            'type' => $row['notification_type'],
            'phone' => $row['recipient'],
            'email' => $row['recipient'],
            'message' => $row['message'],
            'order_id' => $row['order_id'],
            'template' => $row['template_code']
        ]);

        if ($sendResult && $sendResult['success']) {
            // 원본 로그 업데이트
            $updateQuery = "UPDATE notification_logs SET status = 'sent', sent_at = NOW(), result = 'Retry success' WHERE id = ?";
            $stmt = mysqli_prepare($db, $updateQuery);
            mysqli_stmt_bind_param($stmt, 'i', $row['id']);
            mysqli_stmt_execute($stmt);
            $success++;
        } else {
            $failed++;
        }

        // API 호출 간격
        usleep(200000);
    }

    echo json_encode([
        'success' => true,
        'message' => "{$success}건 재발송 성공, {$failed}건 실패",
        'processed' => $success,
        'failed' => $failed
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
