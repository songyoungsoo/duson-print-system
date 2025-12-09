<?php
/**
 * 이메일 자동 발송 크론잡 스크립트
 * 두손기획인쇄 - 자동화된 이메일 알림 시스템
 *
 * 실행 주기: 5분마다 권장
 * Windows Task Scheduler 또는 수동 실행
 */

// 에러 리포팅 설정
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/cron_error.log');

// 실행 시작 로그
$start_time = microtime(true);
$log_file = __DIR__ . '/email_cron.log';

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

writeLog("========================================");
writeLog("크론잡 시작");

// DB 연결
require_once __DIR__ . '/../db.php';

if (!$db) {
    writeLog("ERROR: 데이터베이스 연결 실패 - " . mysqli_connect_error());
    exit(1);
}

writeLog("데이터베이스 연결 성공");

// 이메일 알림 관리자 로드
require_once __DIR__ . '/../includes/OrderNotificationManager.php';

try {
    // 이메일 알림 관리자 인스턴스 생성
    $notificationManager = new OrderNotificationManager($db);

    writeLog("OrderNotificationManager 초기화 완료");

    // 대기 중인 이메일 개수 확인
    $pending_query = "SELECT COUNT(*) as count FROM order_email_log
                      WHERE sent_status = 'pending'";
    $result = mysqli_query($db, $pending_query);
    $row = mysqli_fetch_assoc($result);
    $pending_count = $row['count'];

    writeLog("대기 중인 이메일: {$pending_count}개");

    if ($pending_count == 0) {
        writeLog("발송할 이메일이 없습니다.");
        writeLog("크론잡 종료 (발송 없음)");
        exit(0);
    }

    // 이메일 발송 (한 번에 최대 10개)
    $limit = 10;
    writeLog("이메일 발송 시작 (최대 {$limit}개)");

    $sent_count = $notificationManager->sendPendingEmails($limit);

    writeLog("발송 완료: {$sent_count}개");

    // 실행 시간 계산
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);

    writeLog("실행 시간: {$execution_time}초");
    writeLog("크론잡 종료 (성공)");
    writeLog("========================================\n");

    // 성공 종료
    exit(0);

} catch (Exception $e) {
    writeLog("ERROR: 예외 발생 - " . $e->getMessage());
    writeLog("스택 트레이스: " . $e->getTraceAsString());
    writeLog("크론잡 종료 (오류)");
    writeLog("========================================\n");

    // 실패 종료
    exit(1);
}
