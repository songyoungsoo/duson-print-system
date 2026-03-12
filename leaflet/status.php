<?php
/**
 * status.php — V2 Leaflet Factory Progress Polling Endpoint
 * 
 * Python 에이전트가 실시간으로 업데이트하는 status.json을 읽어 반환합니다.
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'GET 요청만 허용됩니다.']);
    exit;
}

$jobId = trim($_GET['job_id'] ?? '');

if ($jobId === '') {
    echo json_encode(['success' => false, 'error' => 'job_id가 필요합니다.']);
    exit;
}

$jobId = basename($jobId); // Directory traversal 방지
$outputBase = realpath(__DIR__ . '/../_leaflet_factory/output');
$jobDir = $outputBase . '/' . $jobId;

if (!is_dir($jobDir)) {
    echo json_encode(['success' => false, 'error' => '작업을 찾을 수 없습니다: ' . $jobId]);
    exit;
}

$statusPath = $jobDir . '/status.json';

// status.json 파일이 존재하면 그대로 읽어서 반환
if (file_exists($statusPath)) {
    $statusContent = file_get_contents($statusPath);
    echo $statusContent;
    exit;
}

// 파일이 아직 생성되지 않은 초기 상태
echo json_encode([
    'status' => 'running',
    'progress' => 0,
    'current_step_name' => '준비 중...',
    'logs' => []
], JSON_UNESCAPED_UNICODE);
