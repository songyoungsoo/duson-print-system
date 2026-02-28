<?php
/**
 * NAS Sync API - 범용 NAS FTP 동기화
 * POST /dashboard/api/nas-sync.php
 *
 * NAS 접속정보를 POST로 받아 환경변수로 스크립트에 전달.
 * 미입력 시 기본값(sknas205) 사용.
 */
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$scriptPath = '/var/www/html/scripts/sync_to_sknas.sh';

if (!file_exists($scriptPath)) {
    echo json_encode(['success' => false, 'error' => '동기화 스크립트를 찾을 수 없습니다.']);
    exit;
}

// NAS 접속정보 (POST로 받거나 기본값)
$nasHost = trim($_POST['nas_host'] ?? 'sknas205.ipdisk.co.kr');
$nasUser = trim($_POST['nas_user'] ?? 'sknas205');
$nasPass = trim($_POST['nas_pass'] ?? 'sknas205204203');
$nasRoot = trim($_POST['nas_root'] ?? '/HDD1/duson260118');

// 보안: 접속정보 기본 검증
if (empty($nasHost) || empty($nasUser)) {
    echo json_encode(['success' => false, 'error' => 'NAS 호스트와 사용자명은 필수입니다.']);
    exit;
}

// 환경변수 prefix (스크립트에 전달)
$envPrefix = sprintf(
    'NAS_HOST=%s NAS_USER=%s NAS_PASS=%s NAS_ROOT=%s',
    escapeshellarg($nasHost),
    escapeshellarg($nasUser),
    escapeshellarg($nasPass),
    escapeshellarg($nasRoot)
);

$command = '';

switch ($action) {
    case 'test':
        // FTP 연결 테스트만 (curl 직접 사용)
        $ftpUrl = escapeshellarg("ftp://{$nasHost}/");
        $ftpAuth = escapeshellarg("{$nasUser}:{$nasPass}");
        $command = "curl -s --connect-timeout 5 -l {$ftpUrl} --user {$ftpAuth} 2>&1 && echo '✅ FTP 연결 성공' || echo '❌ FTP 연결 실패'";
        break;

    case 'mirror':
        $dryRun = !empty($_POST['dry_run']) ? ' --dry-run' : '';
        $command = "{$envPrefix} bash {$scriptPath}{$dryRun} 2>&1";
        break;

    case 'changed':
        $since = $_POST['since'] ?? '';
        $dryRun = !empty($_POST['dry_run']) ? ' --dry-run' : '';
        $sinceArg = $since ? " {$since}" : '';
        $command = "{$envPrefix} bash {$scriptPath} --changed{$sinceArg}{$dryRun} 2>&1";
        break;

    case 'file':
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
        $dryRun = !empty($_POST['dry_run']) ? ' --dry-run' : '';
        $command = "{$envPrefix} bash {$scriptPath} --file " . escapeshellarg($filePath) . "{$dryRun} 2>&1";
        break;

    case 'status':
        // NAS 디렉토리 목록 확인
        $ftpUrl = escapeshellarg("ftp://{$nasHost}{$nasRoot}/");
        $ftpAuth = escapeshellarg("{$nasUser}:{$nasPass}");
        $command = "curl -s --connect-timeout 5 -l {$ftpUrl} --user {$ftpAuth} 2>&1 | head -30";
        break;

    case 'git_status':
        // 마지막 커밋 변경사항 확인 (NAS 무관)
        $command = "cd /var/www/html && git log --oneline -5 2>&1 && echo '---' && git diff-tree --no-commit-id --name-status -r HEAD 2>&1";
        break;

    default:
        echo json_encode(['success' => false, 'error' => '알 수 없는 액션: ' . $action]);
        exit;
}

// 명령 실행
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
