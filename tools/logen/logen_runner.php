<?php
/**
 * 로젠 자동등록 스크립트 실행/상태 조회 API
 * logen_auto.js를 백그라운드로 실행하고 진행 상황을 폴링으로 조회
 *
 * action=run    (POST) : 스크립트 실행 시작
 * action=status (GET)  : 실행 상태 + 로그 조회
 * action=kill   (POST) : 실행 중인 프로세스 강제 종료
 */

header('Content-Type: application/json; charset=utf-8');

// localhost만 허용 (로컬 WSL 전용)
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote_ip, $allowed_ips)) {
    http_response_code(403);
    echo json_encode(['error' => '로컬에서만 접근 가능합니다.']);
    exit;
}

// 관리자 인증 (세션 또는 embed 토큰)
require_once __DIR__ . '/../../admin/includes/admin_auth.php';
$is_runner_authed = isAdminLoggedIn();

if (!$is_runner_authed) {
    // embed 토큰 검증
    $eauth = $_GET['_eauth'] ?? $_POST['_eauth'] ?? '';
    $self_path = '/tools/logen/logen_runner.php';
    $expected = hash_hmac('sha256', $self_path . date('Y-m-d'), 'duson_embed_2026_secret');
    if (hash_equals($expected, $eauth)) {
        $is_runner_authed = true;
    }
}

if (!$is_runner_authed) {
    http_response_code(401);
    echo json_encode(['error' => '인증이 필요합니다.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$downloads_dir = __DIR__ . '/downloads';

// downloads 디렉토리 확인
if (!is_dir($downloads_dir)) {
    mkdir($downloads_dir, 0755, true);
}

$pid_file = $downloads_dir . '/logen.pid';

switch ($action) {
    case 'run':
        handleRun();
        break;
    case 'status':
        handleStatus();
        break;
    case 'kill':
        handleKill();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => '잘못된 action: ' . $action]);
}

function handleRun() {
    global $pid_file, $downloads_dir;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'POST 요청만 허용됩니다.']);
        return;
    }

    // 동시 실행 방지
    if (file_exists($pid_file)) {
        $existing_pid = trim(file_get_contents($pid_file));
        if ($existing_pid && file_exists("/proc/$existing_pid")) {
            http_response_code(409);
            echo json_encode([
                'error' => '이미 실행 중입니다.',
                'pid' => (int)$existing_pid
            ]);
            return;
        }
        // 프로세스가 죽었으면 PID 파일 정리
        unlink($pid_file);
    }

    // 날짜 파라미터 검증
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
        http_response_code(400);
        echo json_encode(['error' => '시작일 형식이 올바르지 않습니다. (YYYY-MM-DD)']);
        return;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
        http_response_code(400);
        echo json_encode(['error' => '종료일 형식이 올바르지 않습니다. (YYYY-MM-DD)']);
        return;
    }

    // 로그 파일 생성
    $timestamp = date('Ymd_His');
    $log_file = $downloads_dir . "/run_{$timestamp}.log";
    touch($log_file);

    // Node.js 스크립트 경로
    $script_path = __DIR__ . '/logen_auto.js';
    if (!file_exists($script_path)) {
        http_response_code(500);
        echo json_encode(['error' => 'logen_auto.js 파일을 찾을 수 없습니다.']);
        return;
    }

    // node 절대 경로 확인
    $node_path = trim(shell_exec('which node 2>/dev/null') ?: '');
    if (empty($node_path) || !file_exists($node_path)) {
        $node_path = '/usr/bin/node';
    }

    // 백그라운드 실행: nohup + & + 절대경로
    // Playwright 브라우저 경로: /home/ysung/.cache/ms-playwright (로컬 WSL 전용)
    $playwright_path = '/home/ysung/.cache/ms-playwright';
    $full_cmd = sprintf(
        'cd %s && PATH=/usr/local/bin:/usr/bin:/bin PLAYWRIGHT_BROWSERS_PATH=%s nohup %s %s --headless --date-from=%s --date-to=%s > %s 2>&1 & echo $!',
        escapeshellarg(__DIR__),
        escapeshellarg($playwright_path),
        escapeshellarg($node_path),
        escapeshellarg($script_path),
        escapeshellarg($date_from),
        escapeshellarg($date_to),
        escapeshellarg($log_file)
    );

    $pid = trim(shell_exec($full_cmd));

    if (empty($pid) || !is_numeric($pid)) {
        // 디버그: 실패 원인을 로그에 기록
        file_put_contents($log_file, "CMD: $full_cmd\nPID result: " . var_export($pid, true) . "\n");
        http_response_code(500);
        echo json_encode(['error' => '프로세스 시작에 실패했습니다. 로그를 확인하세요.']);
        return;
    }

    // PID 저장
    file_put_contents($pid_file, $pid);

    // 로그 파일명도 함께 저장 (상태 조회 시 사용)
    file_put_contents($pid_file . '.log', basename($log_file));

    echo json_encode([
        'success' => true,
        'pid' => (int)$pid,
        'logFile' => basename($log_file),
        'message' => '로젠 자동등록을 시작합니다.'
    ]);
}

function handleStatus() {
    global $pid_file, $downloads_dir;

    $running = false;
    $pid = null;
    $log_content = '';
    $total_lines = 0;

    // PID 확인
    if (file_exists($pid_file)) {
        $pid = trim(file_get_contents($pid_file));
        if ($pid && file_exists("/proc/$pid")) {
            $running = true;
        }
    }

    // 로그 파일 읽기
    $log_ref_file = $pid_file . '.log';
    if (file_exists($log_ref_file)) {
        $log_filename = trim(file_get_contents($log_ref_file));
        $log_path = $downloads_dir . '/' . $log_filename;
        if (file_exists($log_path)) {
            // 마지막 80줄 읽기
            $lines = file($log_path, FILE_IGNORE_NEW_LINES);
            if ($lines !== false) {
                $total_lines = count($lines);
                $last_lines = array_slice($lines, max(0, $total_lines - 80));
                $log_content = implode("\n", $last_lines);
            }
        }
    }

    // UTF-8 안전 처리 (이모지/박스 드로잉 문자 포함)
    if (!mb_check_encoding($log_content, 'UTF-8')) {
        $log_content = mb_convert_encoding($log_content, 'UTF-8', 'UTF-8');
    }

    echo json_encode([
        'running' => $running,
        'pid' => $pid ? (int)$pid : null,
        'log' => $log_content,
        'totalLines' => $total_lines
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}

function handleKill() {
    global $pid_file;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'POST 요청만 허용됩니다.']);
        return;
    }

    if (!file_exists($pid_file)) {
        echo json_encode(['success' => false, 'message' => '실행 중인 프로세스가 없습니다.']);
        return;
    }

    $pid = trim(file_get_contents($pid_file));

    if (empty($pid) || !file_exists("/proc/$pid")) {
        // 이미 종료됨 - PID 파일만 정리
        @unlink($pid_file);
        @unlink($pid_file . '.log');
        echo json_encode(['success' => true, 'message' => '프로세스가 이미 종료되었습니다.']);
        return;
    }

    // SIGTERM 전송
    posix_kill((int)$pid, 15);

    // 2초 대기 후 확인
    usleep(2000000);
    if (file_exists("/proc/$pid")) {
        // SIGKILL 전송
        posix_kill((int)$pid, 9);
    }

    // PID 파일 정리
    @unlink($pid_file);
    @unlink($pid_file . '.log');

    echo json_encode(['success' => true, 'message' => '프로세스를 종료했습니다.']);
}
