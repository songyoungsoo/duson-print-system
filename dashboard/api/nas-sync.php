<?php
/**
 * NAS Sync API - 범용 NAS FTP 동기화
 * POST /dashboard/api/nas-sync.php
 *
 * PHP FTP 함수로 동기화 — bash/lftp 없이 어떤 서버에서든 동작.
 * NAS 접속정보를 POST로 받아 PhpFtpSync 클래스로 처리.
 * 미입력 시 기본값(sknas205) 사용.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/PhpFtpSync.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

// 웹루트 자동 감지 (로컬: /var/www/html, 프로덕션: /var/www/vhosts/.../httpdocs 등)
$webRoot = realpath(__DIR__ . '/../../');
$hasGit = is_dir($webRoot . '/.git');

// NAS 환경 감지
$isNasEnv = (strpos($_SERVER['HTTP_HOST'] ?? '', 'ipdisk.co.kr') !== false);
$hasBash = !$isNasEnv && (is_executable('/bin/bash') || is_executable('/usr/bin/bash'));
// NAS 접속정보 (POST로 받거나 기본값)
$nasHost = trim($_POST['nas_host'] ?? 'sknas205.ipdisk.co.kr');
$nasUser = trim($_POST['nas_user'] ?? 'sknas205');
$nasPass = trim($_POST['nas_pass'] ?? 'sknas205204203');
$nasRoot = trim($_POST['nas_root'] ?? '/HDD2/share');

// 보안: 접속정보 기본 검증
if (empty($nasHost) || empty($nasUser)) {
    echo json_encode(['success' => false, 'error' => 'NAS 호스트와 사용자명은 필수입니다.']);
    exit;
}

$command = '';

switch ($action) {
    case 'test':
        // FTP 연결 테스트 (PHP 내장 FTP 함수 사용 — curl 없는 NAS 환경 호환)
        $conn = @ftp_connect($nasHost, 21, 5);
        if (!$conn) {
            echo json_encode(['success' => false, 'output' => "❌ FTP 연결 실패: {$nasHost}에 연결할 수 없습니다.", 'action' => 'test', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $login = @ftp_login($conn, $nasUser, $nasPass);
        if (!$login) {
            ftp_close($conn);
            echo json_encode(['success' => false, 'output' => "❌ FTP 인증 실패: 사용자명 또는 비밀번호를 확인하세요.", 'action' => 'test', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        ftp_pasv($conn, true);
        $list = @ftp_nlist($conn, $nasRoot);
        ftp_close($conn);
        $testOutput = "✅ FTP 연결 성공\n호스트: {$nasHost}\n루트: {$nasRoot}\n";
        if (is_array($list)) {
            $testOutput .= "파일/디렉토리: " . count($list) . "개";
        }
        echo json_encode(['success' => true, 'output' => $testOutput, 'action' => 'test', 'nas_host' => $nasHost, 'return_code' => 0], JSON_UNESCAPED_UNICODE);
        exit;

    case 'mirror':
        // PHP FTP 전체 미러링 — bash/lftp 불필요
        $dryRun = !empty($_POST['dry_run']);
        $sync = new PhpFtpSync($nasHost, $nasUser, $nasPass, $nasRoot);
        $connectResult = $sync->connect();
        if (!$connectResult['success']) {
            echo json_encode(['success' => false, 'output' => $connectResult['log'], 'action' => 'mirror', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $result = $sync->uploadDirectory($webRoot, $nasRoot, null, $dryRun);
        $sync->disconnect();
        echo json_encode(['success' => $result['success'], 'output' => $result['log'], 'action' => 'mirror', 'nas_host' => $nasHost, 'return_code' => $result['success'] ? 0 : 1, 'stats' => ['uploaded' => $result['uploaded'], 'skipped' => $result['skipped'], 'failed' => $result['failed']]], JSON_UNESCAPED_UNICODE);
        exit;

    case 'changed':
        // PHP FTP 변경분만 동기화 — 날짜 기반
        $since = $_POST['since'] ?? date('Y-m-d', strtotime('-1 day'));
        $dryRun = !empty($_POST['dry_run']);
        $sync = new PhpFtpSync($nasHost, $nasUser, $nasPass, $nasRoot);
        $connectResult = $sync->connect();
        if (!$connectResult['success']) {
            echo json_encode(['success' => false, 'output' => $connectResult['log'], 'action' => 'changed', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $result = $sync->syncChanged($webRoot, $nasRoot, $since, null, $dryRun);
        $sync->disconnect();
        echo json_encode(['success' => $result['success'], 'output' => $result['log'], 'action' => 'changed', 'nas_host' => $nasHost, 'return_code' => $result['success'] ? 0 : 1, 'stats' => ['uploaded' => $result['uploaded'], 'skipped' => $result['skipped'], 'failed' => $result['failed']]], JSON_UNESCAPED_UNICODE);
        exit;

    case 'file':
        // PHP FTP 특정 파일 업로드
        $filePath = $_POST['file_path'] ?? '';
        if (empty($filePath)) {
            echo json_encode(['success' => false, 'error' => '파일 경로를 입력하세요.']);
            exit;
        }
        // 보안: 경로 검증 (상위 디렉토리 이동 방지)
        if (strpos($filePath, '..') !== false) {
            echo json_encode(['success' => false, 'error' => '잘못된 경로입니다.']);
            exit;
        }
        $dryRun = !empty($_POST['dry_run']);
        $localPath = $webRoot . '/' . ltrim($filePath, '/');
        $remotePath = $nasRoot . '/' . ltrim($filePath, '/');
        if (!file_exists($localPath)) {
            echo json_encode(['success' => false, 'error' => '파일이 존재하지 않습니다: ' . $filePath]);
            exit;
        }
        $sync = new PhpFtpSync($nasHost, $nasUser, $nasPass, $nasRoot);
        $connectResult = $sync->connect();
        if (!$connectResult['success']) {
            echo json_encode(['success' => false, 'output' => $connectResult['log'], 'action' => 'file', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // 디렉토리인 경우 재귀적 업로드
        if (is_dir($localPath)) {
            $remoteDir = $nasRoot . '/' . ltrim($filePath, '/');
            $result = $sync->uploadDirectory($localPath, $remoteDir, null, $dryRun);
        } else {
            $result = $sync->uploadFile($localPath, $remotePath, $dryRun);
        }
        $sync->disconnect();
        echo json_encode(['success' => $result['success'], 'output' => $result['log'], 'action' => 'file', 'nas_host' => $nasHost, 'return_code' => $result['success'] ? 0 : 1, 'stats' => ['uploaded' => $result['uploaded'], 'skipped' => $result['skipped'], 'failed' => $result['failed']]], JSON_UNESCAPED_UNICODE);
        exit;

    case 'status':
        // NAS 디렉토리 목록 확인 (PHP 내장 FTP 함수 사용)
        $conn = @ftp_connect($nasHost, 21, 5);
        if (!$conn) {
            echo json_encode(['success' => false, 'output' => "❌ FTP 연결 실패", 'action' => 'status', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $login = @ftp_login($conn, $nasUser, $nasPass);
        if (!$login) {
            ftp_close($conn);
            echo json_encode(['success' => false, 'output' => "❌ FTP 인증 실패", 'action' => 'status', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        ftp_pasv($conn, true);
        $list = @ftp_nlist($conn, $nasRoot);
        ftp_close($conn);
        $statusOutput = is_array($list) ? implode("\n", array_slice($list, 0, 30)) : "디렉토리 목록을 가져올 수 없습니다.";
        echo json_encode(['success' => true, 'output' => $statusOutput, 'action' => 'status', 'nas_host' => $nasHost, 'return_code' => 0], JSON_UNESCAPED_UNICODE);
        exit;

    case 'git_status':
        if ($isNasEnv) {
            // NAS: find/git 없음 → PHP로 최근 수정 파일 조회
            $recentFiles = [];
            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($webRoot, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iter as $file) {
                if ($file->getMTime() > time() - 86400 * 7) { // 최근 7일
                    $rel = str_replace($webRoot . '/', '', $file->getPathname());
                    if (strpos($rel, '.') === 0 || strpos($rel, 'node_modules') !== false) continue;
                    $recentFiles[] = date('m/d H:i', $file->getMTime()) . '  ' . $rel;
                }
            }
            rsort($recentFiles);
            $output = implode("\n", array_slice($recentFiles, 0, 20));
            $output .= "\n---\n(NAS 환경 - 최근 7일 수정 파일 표시)";
            echo json_encode(['success' => true, 'output' => $output, 'action' => 'git_status', 'nas_host' => $nasHost, 'return_code' => 0], JSON_UNESCAPED_UNICODE);
            exit;
        } elseif (!$hasGit) {
            $command = "find " . escapeshellarg($webRoot) . " -maxdepth 3 -type f -newer " . escapeshellarg($webRoot . "/index.php") . " -not -path '*/.*' -not -path '*/node_modules/*' 2>/dev/null | head -20 && echo '---' && echo '(Git 미설치 - 최근 수정 파일 표시)'";
        } else {
            $command = "cd " . escapeshellarg($webRoot) . " && git -c safe.directory=" . escapeshellarg($webRoot) . " log --oneline -5 2>&1 && echo '---' && git -c safe.directory=" . escapeshellarg($webRoot) . " diff-tree --no-commit-id --name-status -r HEAD 2>&1";
        }
        break;

    case 'env_info':
        echo json_encode([
            'success' => true,
            'has_git' => $hasGit,
            'has_bash' => $hasBash,
            'is_nas' => $isNasEnv,
            'php_ftp' => extension_loaded('ftp'),
            'web_root' => $webRoot,
            'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
        ], JSON_UNESCAPED_UNICODE);
        exit;

    case 'sync_status':
        // 자동 동기화 상태 조회 (상태 파일 읽기)
        $dbStatusFile = $webRoot . '/scripts/.db_sync_status.json';
        $uploadStatusFile = $webRoot . '/scripts/.upload_sync_status.json';
        $dbStatus = file_exists($dbStatusFile) ? json_decode(file_get_contents($dbStatusFile), true) : null;
        $uploadStatus = file_exists($uploadStatusFile) ? json_decode(file_get_contents($uploadStatusFile), true) : null;
        
        // 실행 중 여부 확인 (NAS에서는 pgrep 불가 → 항상 false)
        $dbRunning = false;
        $uploadRunning = false;
        if (!$isNasEnv) {
            $dbRunning = !empty(trim(shell_exec('pgrep -f nas_order_sync.sh 2>/dev/null') ?? ''));
            $uploadRunning = !empty(trim(shell_exec('pgrep -f sync_upload_to_nas.sh 2>/dev/null') ?? ''));
        }
        
        echo json_encode([
            'success' => true,
            'db_sync' => $dbStatus,
            'upload_sync' => $uploadStatus,
            'db_running' => $dbRunning,
            'upload_running' => $uploadRunning,
            'is_nas' => $isNasEnv,
        ], JSON_UNESCAPED_UNICODE);
        exit;

    case 'trigger_db_sync':
        // DB 동기화 — bash 스크립트 우선, 없으면 안내
        $dbScript = $webRoot . '/scripts/nas_order_sync.sh';
        if ($hasBash && file_exists($dbScript)) {
            $already = trim(shell_exec('pgrep -f nas_order_sync.sh 2>/dev/null') ?? '');
            if (!empty($already)) {
                echo json_encode(['success' => false, 'error' => '이미 실행 중입니다 (PID: ' . $already . ')']);
                exit;
            }
            $logFile = escapeshellarg($webRoot . '/scripts/db_backup.log');
            exec('nohup bash ' . escapeshellarg($dbScript) . ' >> ' . $logFile . ' 2>&1 &');
            echo json_encode(['success' => true, 'message' => 'DB 동기화 시작됨 (백그라운드)']);
        } else {
            echo json_encode(['success' => false, 'error' => '이 서버에서는 DB 동기화를 실행할 수 없습니다. DB 동기화는 로컬 서버의 cron(05:00)이 자동 실행합니다.']);
        }
        exit;

    case 'trigger_upload_sync':
        // 교정이미지 동기화 — PHP FTP로 직접 실행 가능
        $uploadDir = $webRoot . '/mlangorder_printauto/upload';
        $remoteUploadDir = $nasRoot . '/mlangorder_printauto/upload';
        if (!is_dir($uploadDir)) {
            echo json_encode(['success' => false, 'error' => '로컬 upload 디렉토리가 없습니다: ' . $uploadDir]);
            exit;
        }
        $sync = new PhpFtpSync($nasHost, $nasUser, $nasPass, $nasRoot);
        $connectResult = $sync->connect();
        if (!$connectResult['success']) {
            echo json_encode(['success' => false, 'output' => $connectResult['log'], 'action' => 'trigger_upload_sync', 'nas_host' => $nasHost, 'return_code' => 1], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // 최근 7일 변경분만 동기화 (성능 최적화)
        $since = date('Y-m-d', strtotime('-7 days'));
        $result = $sync->syncChanged($uploadDir, $remoteUploadDir, $since, [], false);
        $sync->disconnect();
        echo json_encode(['success' => $result['success'], 'output' => $result['log'], 'action' => 'trigger_upload_sync', 'nas_host' => $nasHost, 'return_code' => $result['success'] ? 0 : 1, 'message' => '교정이미지 동기화 완료', 'stats' => ['uploaded' => $result['uploaded'], 'skipped' => $result['skipped'], 'failed' => $result['failed']]], JSON_UNESCAPED_UNICODE);
        exit;

    default:
        echo json_encode(['success' => false, 'error' => '알 수 없는 액션: ' . $action]);
        exit;
}

// git_status 등 쉘 명령어 실행 (해당되는 경우만)
if (!empty($command)) {
    $output = '';
    $returnCode = 0;
    exec($command, $outputLines, $returnCode);
    $output = implode("\n", $outputLines);
    // ANSI 색상 코드 제거
    $output = preg_replace('/\033\[[0-9;]*m/', '', $output);
    echo json_encode([
        'success' => $returnCode === 0,
        'output' => $output,
        'action' => $action,
        'nas_host' => $nasHost,
        'return_code' => $returnCode,
    ], JSON_UNESCAPED_UNICODE);
}
