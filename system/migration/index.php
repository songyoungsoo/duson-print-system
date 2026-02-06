<?php
session_start();

$MIGRATION_PASSWORD = 'duson2026!migration';

if (isset($_POST['logout'])) {
    unset($_SESSION['migration_auth']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['password'])) {
    if ($_POST['password'] === $MIGRATION_PASSWORD) {
        $_SESSION['migration_auth'] = true;
    } else {
        $login_error = '비밀번호가 올바르지 않습니다.';
    }
}

if (empty($_SESSION['migration_auth'])) {
    showLogin(isset($login_error) ? $login_error : '');
    exit;
}

include "../../db.php";
require_once __DIR__ . '/MigrationSync.php';

/**
 * ========================================
 * 서버별 마이그레이션 설정
 * ========================================
 * 
 * dsp114.co.kr (임대 서버 - 용량 제한):
 *   FILE_FILTER_MIN_NO = 75000
 *   FILE_FILTER_MIN_YEAR = 2026
 * 
 * dsp1830.ipdisk.co.kr:8000 (NAS - 전체 백업):
 *   FILE_FILTER_MIN_NO = 0
 *   FILE_FILTER_MIN_YEAR = 2000
 */
define('FILE_FILTER_MIN_NO', 75000);    // 교정파일: 이 번호 이상만
define('FILE_FILTER_MIN_YEAR', 2026);   // 원고파일: 이 연도 이상만

$sync = new MigrationSync($db);
$sync->setFileFilters(FILE_FILTER_MIN_NO, FILE_FILTER_MIN_YEAR);

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'sync' && isset($_POST['tables'])) {
    header('Content-Type: application/json; charset=utf-8');
    set_time_limit(600);
    $results = array();
    $since = isset($_POST['since']) ? trim($_POST['since']) : '';
    foreach ($_POST['tables'] as $table) {
        $results[] = $sync->syncTable($table, $since);
    }
    echo json_encode(array('results' => $results));
    exit;
}

// 마지막 동기화 정보 API
if ($action === 'last_sync_info') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($sync->getLastSync());
    exit;
}

// 마지막 동기화 시점 저장 API
if ($action === 'save_last_sync') {
    header('Content-Type: application/json; charset=utf-8');
    $results = isset($_POST['results']) ? json_decode($_POST['results'], true) : array();
    $sync->saveLastSync($results);
    echo json_encode(array('ok' => true, 'saved_at' => date('Y-m-d H:i:s')));
    exit;
}

// 동기화 대상 테이블 목록 API
if ($action === 'sync_targets') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('tables' => $sync->getSyncTargetTables()));
    exit;
}

// 파일 동기화 API
if ($action === 'file_sync') {
    header('Content-Type: application/json; charset=utf-8');
    set_time_limit(1800); // 30분 (파일이 많으므로)
    $type = isset($_POST['file_type']) ? $_POST['file_type'] : '';
    $since = isset($_POST['since']) ? trim($_POST['since']) : '';
    if (!in_array($type, array('upload', 'shop', 'imgfolder'))) {
        echo json_encode(array('error' => 'Invalid file type'));
        exit;
    }
    $result = $sync->syncFiles($type, $since);
    echo json_encode(array('result' => $result));
    exit;
}

// 파일 현황 API
if ($action === 'file_stats') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($sync->getFileStats());
    exit;
}

// 오래된 교정파일 폴더 삭제 API
if ($action === 'cleanup_upload') {
    header('Content-Type: text/plain; charset=utf-8');
    set_time_limit(0);
    
    $upload_dir = dirname(dirname(__DIR__)) . '/mlangorder_printauto/upload';
    $threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 75000;
    $deleted_count = 0;
    $error_count = 0;
    $freed_bytes = 0;

    echo "=== 교정파일 폴더 정리 시작 ===\n";
    echo "경로: $upload_dir\n";
    echo "삭제 기준: {$threshold}번 미만\n\n";

    if (!is_dir($upload_dir)) {
        die("ERROR: 디렉토리 없음: $upload_dir\n");
    }

    $dirs = scandir($upload_dir);
    $to_delete = array();
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        if (!is_numeric($dir)) continue;
        if (intval($dir) < $threshold) {
            $to_delete[] = $dir;
        }
    }

    echo "삭제 대상: " . count($to_delete) . "개 폴더\n\n";
    flush();

    foreach ($to_delete as $dir) {
        $dir_path = $upload_dir . '/' . $dir;
        if (!is_dir($dir_path)) continue;
        
        $files = @scandir($dir_path);
        if ($files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $file_path = $dir_path . '/' . $file;
                if (is_file($file_path)) {
                    $freed_bytes += filesize($file_path);
                    @unlink($file_path);
                }
            }
        }
        
        if (@rmdir($dir_path)) {
            $deleted_count++;
            if ($deleted_count % 500 == 0) {
                echo "진행: {$deleted_count}개 폴더 삭제됨...\n";
                flush();
            }
        } else {
            $error_count++;
        }
    }

    $freed_mb = round($freed_bytes / 1024 / 1024, 2);
    echo "\n=== 정리 완료 ===\n";
    echo "삭제된 폴더: {$deleted_count}개\n";
    echo "에러: {$error_count}건\n";
    echo "확보된 용량: {$freed_mb} MB\n";
    exit;
}

// 디스크 용량 체크 API
if ($action === 'disk_usage') {
    header('Content-Type: application/json; charset=utf-8');
    $base = dirname(dirname(__DIR__));
    
    // 전체 디스크 정보
    $total = @disk_total_space($base);
    $free = @disk_free_space($base);
    $used = $total - $free;
    
    // 주요 디렉토리별 사용량
    function getDirSize($path) {
        $size = 0;
        if (!is_dir($path)) return 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }
    
    $dirs_to_check = array(
        'mlangorder_printauto/upload' => '교정파일',
        'shop/data' => '원고(스티커)',
        'ImgFolder' => '원고(일반)',
        'system/migration/logs' => '마이그레이션 로그'
    );
    
    $dir_sizes = array();
    foreach ($dirs_to_check as $dir => $label) {
        $path = $base . '/' . $dir;
        if (is_dir($path)) {
            $size = getDirSize($path);
            $dir_sizes[$dir] = array(
                'label' => $label,
                'size_bytes' => $size,
                'size_mb' => round($size / 1024 / 1024, 2),
                'size_gb' => round($size / 1024 / 1024 / 1024, 2)
            );
        }
    }
    
    echo json_encode(array(
        'disk' => array(
            'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            'used_gb' => round($used / 1024 / 1024 / 1024, 2),
            'free_gb' => round($free / 1024 / 1024 / 1024, 2),
            'used_percent' => round(($used / $total) * 100, 1)
        ),
        'directories' => $dir_sizes
    ));
    exit;
}

// 디렉토리 권한 체크 API
if ($action === 'check_permissions') {
    header('Content-Type: application/json; charset=utf-8');
    $base = dirname(dirname(__DIR__));
    $dirs = array(
        'mlangorder_printauto/upload' => '교정파일',
        'shop/data' => '원고(스티커)',
        'ImgFolder' => '원고(일반)'
    );
    $results = array();
    foreach ($dirs as $dir => $label) {
        $path = $base . '/' . $dir;
        $info = array(
            'label' => $label,
            'path' => $path,
            'exists' => file_exists($path),
            'is_dir' => is_dir($path),
            'readable' => is_readable($path),
            'writable' => is_writable($path),
            'perms' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A',
            'mkdir_test' => false
        );
        // mkdir 테스트
        if (is_dir($path)) {
            $test_dir = $path . '/test_perm_' . time();
            error_clear_last();
            $mkdir_result = @mkdir($test_dir, 0755, true);
            $last_error = error_get_last();
            if ($mkdir_result) {
                $info['mkdir_test'] = true;
                $info['mkdir_error'] = null;
                @rmdir($test_dir);
            } else {
                $info['mkdir_test'] = false;
                $info['mkdir_error'] = $last_error ? $last_error['message'] : 'Unknown error';
            }
            // 파일 쓰기 테스트
            $test_file = $path . '/test_write_' . time() . '.txt';
            error_clear_last();
            $write_result = @file_put_contents($test_file, 'test');
            $last_error = error_get_last();
            if ($write_result !== false) {
                $info['write_test'] = true;
                $info['write_error'] = null;
                @unlink($test_file);
            } else {
                $info['write_test'] = false;
                $info['write_error'] = $last_error ? $last_error['message'] : 'Unknown error';
            }
        }
        $results[$dir] = $info;
    }
    echo json_encode(array(
        'php_user' => function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user(),
        'base_dir' => $base,
        'directories' => $results
    ));
    exit;
}

if ($action === 'tables') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($sync->getSourceTables());
    exit;
}

if ($action === 'log' && isset($_GET['file'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo $sync->getLogContent($_GET['file']);
    exit;
}

$source_tables = $sync->getSourceTables();
$log_files = $sync->getLogFiles();
$file_stats = $sync->getFileStats();
$last_sync = $sync->getLastSync();
$sync_targets = $sync->getSyncTargetTables();

showDashboard($source_tables, $log_files, $file_stats, $last_sync, $sync_targets);

function showLogin($error = '') {
?>
<!DOCTYPE html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>마이그레이션 도구</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f0f2f5;display:flex;justify-content:center;align-items:center;min-height:100vh}
.login-box{background:#fff;padding:40px;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,.1);width:360px}
.login-box h2{margin-bottom:24px;color:#1a1a2e;text-align:center}
.login-box input[type=password]{width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:15px;margin-bottom:16px}
.login-box button{width:100%;padding:12px;background:#4361ee;color:#fff;border:none;border-radius:8px;font-size:15px;cursor:pointer}
.login-box button:hover{background:#3a56d4}
.error{color:#e74c3c;font-size:13px;margin-bottom:12px;text-align:center}
</style>
</head><body>
<div class="login-box">
<h2>🔄 마이그레이션 도구</h2>
<?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error)?></div><?php endif; ?>
<form method="post">
<input type="password" name="password" placeholder="관리자 비밀번호" autofocus>
<button type="submit">로그인</button>
</form>
</div>
</body></html>
<?php
}

function showDashboard($source_tables, $log_files, $file_stats = array(), $last_sync = array(), $sync_targets = array()) {
    $tables = isset($source_tables['tables']) ? $source_tables['tables'] : array();
    $table_error = isset($source_tables['error']) ? $source_tables['error'] : '';
    $last_sync_display = isset($last_sync['last_sync_display']) ? $last_sync['last_sync_display'] : '없음';
    $last_sync_date = isset($last_sync['last_sync']) ? $last_sync['last_sync'] : '';
?>
<!DOCTYPE html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>마이그레이션 도구 - dsp114.com → dsp114.co.kr</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f0f2f5;color:#333}
.header{background:#1a1a2e;color:#fff;padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
.header h1{font-size:18px;font-weight:600}
.header .info{font-size:12px;color:#adb5bd}
.container{max-width:1100px;margin:24px auto;padding:0 16px}
.card{background:#fff;border-radius:10px;box-shadow:0 1px 8px rgba(0,0,0,.06);margin-bottom:20px;overflow:hidden}
.card-header{padding:16px 20px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center}
.card-header h2{font-size:16px;color:#1a1a2e}
.card-body{padding:20px}
table{width:100%;border-collapse:collapse}
th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #f0f0f0;font-size:13px}
th{background:#f8f9fa;font-weight:600;color:#555}
tr:hover{background:#f8fbff}
.badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:500}
.badge-blue{background:#e8f0fe;color:#1a73e8}
.badge-green{background:#e6f4ea;color:#137333}
.badge-red{background:#fce8e6;color:#c5221f}
.btn{padding:8px 16px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:500;transition:background .2s}
.btn-primary{background:#4361ee;color:#fff}
.btn-primary:hover{background:#3a56d4}
.btn-primary:disabled{background:#aaa;cursor:not-allowed}
.btn-sm{padding:5px 10px;font-size:12px}
.btn-danger{background:#e74c3c;color:#fff}
.btn-outline{background:#fff;color:#555;border:1px solid #ddd}
.btn-outline:hover{background:#f5f5f5}
.form-row{display:flex;gap:12px;align-items:center;margin-bottom:16px;flex-wrap:wrap}
.form-row label{font-size:13px;color:#555;white-space:nowrap}
.form-row input[type=date]{padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px}
input[type=checkbox]{width:16px;height:16px;cursor:pointer}
#result-area{margin-top:16px;display:none}
#result-area pre{background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:8px;overflow-x:auto;font-size:12px;line-height:1.6;max-height:400px;overflow-y:auto}
.progress-bar{height:4px;background:#e0e0e0;border-radius:2px;overflow:hidden;margin-bottom:12px;display:none}
.progress-bar .fill{height:100%;background:#4361ee;transition:width .3s;width:0}
.log-list{max-height:200px;overflow-y:auto}
.log-list a{display:block;padding:6px 0;color:#4361ee;text-decoration:none;font-size:13px;border-bottom:1px solid #f5f5f5}
.log-list a:hover{color:#3a56d4}
.logout-btn{background:transparent;color:#adb5bd;border:1px solid #555;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:12px}
.logout-btn:hover{color:#fff;border-color:#fff}
.flow{font-size:13px;color:#888;margin-bottom:4px}
</style>
</head><body>

<div class="header">
<div>
<h1>🔄 데이터 마이그레이션</h1>
<div class="info">dsp114.com → dsp114.co.kr | 전화번호/ID 기준 중복 제외</div>
</div>
<form method="post" style="display:inline"><input type="hidden" name="logout" value="1"><button type="submit" class="logout-btn">로그아웃</button></form>
</div>

<div class="container">

<!-- 통합 원클릭 동기화 -->
<div class="card" style="border:2px solid #4361ee">
<div class="card-header" style="background:#f0f4ff">
<h2>🚀 전체 동기화 (DB + 파일 원클릭)</h2>
<div style="font-size:12px;color:#666">마지막 동기화: <strong id="lastSyncDisplay"><?php echo htmlspecialchars($last_sync_display)?></strong></div>
</div>
<div class="card-body">
<div style="margin-bottom:16px;font-size:13px;color:#555">
<p>DB 테이블 <?php echo count($sync_targets)?>개 + 파일 3종(교정/스티커원고/일반원고)을 순차적으로 동기화합니다.</p>
<p style="margin-top:6px;color:#888">마지막 동기화 시점 이후 데이터만 자동으로 가져옵니다. (날짜 수동 지정도 가능)</p>
</div>
<div class="form-row">
<button class="btn btn-primary" id="syncAllBtn" onclick="startSyncAll()" style="padding:12px 32px;font-size:15px;font-weight:600">🔄 전체 동기화 시작</button>
<button class="btn btn-danger" id="syncAllStopBtn" onclick="stopSyncAll()" style="display:none;padding:12px 24px">⏹ 중지</button>
</div>
<div class="progress-bar" id="syncAllProgressBar"><div class="fill" id="syncAllProgressFill"></div></div>
<div id="syncAllStatus" style="display:none;margin-top:12px;font-size:13px;color:#555"></div>
<div id="syncAllResult" style="display:none;margin-top:12px">
<pre id="syncAllResultText" style="background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:8px;overflow-x:auto;font-size:12px;line-height:1.6;max-height:500px;overflow-y:auto"></pre>
</div>
</div>
</div>

<div class="card">
<div class="card-header">
<h2>📋 소스 테이블 목록 (dsp114.com)</h2>
<button class="btn btn-outline btn-sm" onclick="refreshTables()">새로고침</button>
</div>
<div class="card-body">
<?php if ($table_error): ?>
<div style="color:#c5221f;padding:12px;background:#fce8e6;border-radius:6px"><?php echo htmlspecialchars($table_error)?></div>
<?php else: ?>
<div class="form-row">
<label>날짜 필터 (이후):</label>
<input type="date" id="since_date" value="">
<button class="btn btn-primary" id="syncBtn" onclick="startSync()">선택 항목 동기화</button>
<label style="margin-left:auto;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"> 전체선택</label>
</div>
<div class="progress-bar" id="progressBar"><div class="fill" id="progressFill"></div></div>
<table>
<thead><tr><th style="width:40px"></th><th>테이블</th><th style="text-align:right">소스 건수</th><th>상태</th></tr></thead>
<tbody>
<?php foreach ($tables as $t): ?>
<tr>
<td><input type="checkbox" name="sync_table" value="<?php echo htmlspecialchars($t['table'])?>" class="table-cb"></td>
<td><strong><?php echo htmlspecialchars($t['table'])?></strong></td>
<td style="text-align:right"><?php echo number_format($t['count'])?></td>
<td><span class="badge badge-blue"><?php echo number_format($t['count'])?> rows</span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>

<div id="result-area" class="card">
<div class="card-header"><h2>📊 동기화 결과</h2></div>
<div class="card-body"><pre id="result-text"></pre></div>
</div>

<!-- 파일 동기화 섹션 -->
<div class="card">
<div class="card-header">
<h2>📁 파일 동기화 (교정파일 + 원고파일)</h2>
<button class="btn btn-outline btn-sm" onclick="refreshFileStats()">새로고침</button>
</div>
<div class="card-body">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px">

<div style="background:#f8f9fa;padding:16px;border-radius:8px">
<div style="font-size:14px;font-weight:600;margin-bottom:8px">🖼️ 교정파일</div>
<div style="font-size:12px;color:#888;margin-bottom:6px">관리자 업로드 이미지</div>
<div style="font-size:13px;color:#555">
<div>소스 주문 수: <strong id="stat-upload-total"><?php echo isset($file_stats['upload']['total_orders']) ? number_format($file_stats['upload']['total_orders']) : '-' ?></strong></div>
<div>로컬 파일 수: <strong id="stat-upload-local"><?php echo number_format($file_stats['upload']['local'] ?? 0) ?></strong></div>
<div style="font-size:11px;color:#888;margin-top:4px">경로: upload/{no}/</div>
</div>
<button class="btn btn-primary btn-sm" style="margin-top:12px" onclick="startFileSync('upload')">동기화</button>
</div>

<div style="background:#f8f9fa;padding:16px;border-radius:8px">
<div style="font-size:14px;font-weight:600;margin-bottom:8px">📄 원고 (스티커)</div>
<div style="font-size:12px;color:#888;margin-bottom:6px">고객 업로드 - shop/data/</div>
<div style="font-size:13px;color:#555">
<div>소스 주문 수: <strong id="stat-shop-total"><?php echo isset($file_stats['shop']['total_orders']) ? number_format($file_stats['shop']['total_orders']) : '-' ?></strong></div>
<div>로컬 파일 수: <strong id="stat-shop-local"><?php echo number_format($file_stats['shop']['local'] ?? 0) ?></strong></div>
<div style="font-size:11px;color:#888;margin-top:4px">경로: shop/data/</div>
</div>
<button class="btn btn-primary btn-sm" style="margin-top:12px" onclick="startFileSync('shop')">동기화</button>
</div>

<div style="background:#f8f9fa;padding:16px;border-radius:8px">
<div style="font-size:14px;font-weight:600;margin-bottom:8px">📂 원고 (일반제품)</div>
<div style="font-size:12px;color:#888;margin-bottom:6px">전단지,명함,봉투 등</div>
<div style="font-size:13px;color:#555">
<div>소스 주문 수: <strong id="stat-imgfolder-total"><?php echo isset($file_stats['imgfolder']['total_orders']) ? number_format($file_stats['imgfolder']['total_orders']) : '-' ?></strong></div>
<div>로컬 파일 수: <strong id="stat-imgfolder-local"><?php echo number_format($file_stats['imgfolder']['local'] ?? 0) ?></strong></div>
<div style="font-size:11px;color:#888;margin-top:4px">경로: ImgFolder/_MlangPrintAuto_*/</div>
</div>
<button class="btn btn-primary btn-sm" style="margin-top:12px" onclick="startFileSync('imgfolder')">동기화</button>
</div>

</div>

<div class="form-row">
<label>날짜 필터 (이후):</label>
<input type="date" id="file_since_date" value="">
</div>

<div class="progress-bar" id="fileProgressBar"><div class="fill" id="fileProgressFill"></div></div>
<div id="file-result-area" style="display:none">
<pre id="file-result-text" style="background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:8px;overflow-x:auto;font-size:12px;line-height:1.6;max-height:400px;overflow-y:auto"></pre>
</div>
</div>
</div>

<div class="card">
<div class="card-header"><h2>📝 동기화 로그</h2></div>
<div class="card-body">
<?php if (empty($log_files)): ?>
<div style="color:#888;font-size:13px">아직 로그가 없습니다.</div>
<?php else: ?>
<div class="log-list">
<?php foreach ($log_files as $f): ?>
<a href="?action=log&file=<?php echo urlencode(basename($f))?>" target="_blank"><?php echo htmlspecialchars(basename($f))?></a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>

</div>

<script>
function toggleSelectAll(el) {
    document.querySelectorAll('.table-cb').forEach(function(cb) { cb.checked = el.checked; });
}

function refreshTables() { location.reload(); }

function refreshFileStats() {
    fetch(location.pathname + '?action=file_stats')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.upload) {
            document.getElementById('stat-upload-total').textContent = data.upload.total_orders || '-';
            document.getElementById('stat-upload-local').textContent = data.upload.local || 0;
        }
        if (data.shop) {
            document.getElementById('stat-shop-total').textContent = data.shop.total_orders || '-';
            document.getElementById('stat-shop-local').textContent = data.shop.local || 0;
        }
        if (data.imgfolder) {
            document.getElementById('stat-imgfolder-total').textContent = data.imgfolder.total_orders || '-';
            document.getElementById('stat-imgfolder-local').textContent = data.imgfolder.local || 0;
        }
    })
    .catch(function(err) { alert('Error: ' + err.message); });
}

function startFileSync(type) {
    var labels = {'upload': '교정파일', 'shop': '원고파일(스티커)', 'imgfolder': '원고파일(일반제품)'};
    var label = labels[type] || type;
    if (!confirm(label + ' 동기화를 시작하시겠습니까?\n시간이 오래 걸릴 수 있습니다.')) return;

    var since = document.getElementById('file_since_date').value;
    var bar = document.getElementById('fileProgressBar');
    var fill = document.getElementById('fileProgressFill');
    var resultArea = document.getElementById('file-result-area');
    var resultText = document.getElementById('file-result-text');

    bar.style.display = 'block';
    fill.style.width = '30%';
    resultArea.style.display = 'block';
    resultText.textContent = label + ' 동기화 진행 중...\n';

    var formData = new FormData();
    formData.append('action', 'file_sync');
    formData.append('file_type', type);
    if (since) formData.append('since', since);

    fetch(location.pathname, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        fill.style.width = '100%';
        var r = data.result;
        var output = '=== ' + label + ' 동기화 완료 ===\n\n';
        output += '📥 다운로드: ' + r.downloaded + '건\n';
        output += '⏭️ 스킵(이미 존재): ' + r.skipped + '건\n';
        output += '❌ 에러: ' + r.errors + '건\n';
        output += '⏱️ 소요시간: ' + r.elapsed_seconds + '초\n';
        if (r.error_messages && r.error_messages.length > 0) {
            output += '\n⚠️ 에러 상세:\n';
            r.error_messages.forEach(function(e) { output += '  ' + e + '\n'; });
        }
        resultText.textContent = output;
        setTimeout(function() { bar.style.display = 'none'; fill.style.width = '0'; }, 2000);
        refreshFileStats();
    })
    .catch(function(err) {
        resultText.textContent += '\n❌ 오류: ' + err.message;
        bar.style.display = 'none';
    });
}

// ============================================================
// 전체 동기화 (원클릭)
// ============================================================
var syncAllRunning = false;
var syncAllLog = '';

// 동기화 대상 테이블 (서버에서 전달)
var SYNC_TABLES = <?php echo json_encode($sync_targets)?>;
var FILE_TYPES = ['upload', 'shop', 'imgfolder'];
var FILE_LABELS = {'upload': '교정파일', 'shop': '원고(스티커)', 'imgfolder': '원고(일반제품)'};

function syncAllAppend(msg) {
    syncAllLog += msg + '\n';
    var el = document.getElementById('syncAllResultText');
    el.textContent = syncAllLog;
    el.scrollTop = el.scrollHeight;
}

function syncAllUpdateStatus(msg) {
    document.getElementById('syncAllStatus').textContent = msg;
}

function stopSyncAll() {
    syncAllRunning = false;
    syncAllAppend('\n⏹ 사용자가 중지했습니다.');
    syncAllUpdateStatus('중지됨');
}

function startSyncAll() {
    if (syncAllRunning) return;
    syncAllRunning = true;
    syncAllLog = '';

    var btn = document.getElementById('syncAllBtn');
    var stopBtn = document.getElementById('syncAllStopBtn');
    var bar = document.getElementById('syncAllProgressBar');
    var fill = document.getElementById('syncAllProgressFill');
    var status = document.getElementById('syncAllStatus');
    var result = document.getElementById('syncAllResult');

    btn.disabled = true;
    btn.textContent = '동기화 중...';
    stopBtn.style.display = 'inline-block';
    bar.style.display = 'block';
    fill.style.width = '0%';
    status.style.display = 'block';
    result.style.display = 'block';

    // 마지막 동기화 시점 가져오기 (자동 since)
    var lastSync = '<?php echo addslashes($last_sync_date)?>';
    var sinceParam = lastSync || '';

    syncAllAppend('=== 전체 동기화 시작 ===');
    syncAllAppend('마지막 동기화: ' + (sinceParam || '없음 (전체)'));
    syncAllAppend('');

    var totalSteps = SYNC_TABLES.length + FILE_TYPES.length;
    var currentStep = 0;
    var allResults = [];

    // 순차적으로 실행: DB 테이블 → 파일 3종
    syncDbTables(0, sinceParam, totalSteps, currentStep, allResults, function(step, results) {
        if (!syncAllRunning) { finishSyncAll(btn, stopBtn, bar, fill, results); return; }
        // DB 완료 후 파일 동기화
        syncAllAppend('\n--- 파일 동기화 ---\n');
        syncFileTypes(0, sinceParam, totalSteps, step, results, function(finalResults) {
            finishSyncAll(btn, stopBtn, bar, fill, finalResults);
        });
    });
}

function syncDbTables(idx, since, totalSteps, currentStep, results, callback) {
    if (!syncAllRunning || idx >= SYNC_TABLES.length) {
        callback(currentStep + idx, results);
        return;
    }

    var table = SYNC_TABLES[idx];
    var pct = Math.round(((currentStep + idx) / totalSteps) * 100);
    document.getElementById('syncAllProgressFill').style.width = pct + '%';
    syncAllUpdateStatus('DB 동기화: ' + table + ' (' + (idx + 1) + '/' + SYNC_TABLES.length + ')');

    var fd = new FormData();
    fd.append('action', 'sync');
    fd.append('tables[]', table);
    if (since) fd.append('since', since);

    fetch(location.pathname, {method: 'POST', body: fd})
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.results && data.results[0]) {
            var r = data.results[0];
            var line = '📦 ' + r.table + ': +' + r.inserted + '건, 스킵 ' + r.skipped + '건';
            if (r.errors > 0) line += ', 에러 ' + r.errors + '건';
            line += ' (' + r.elapsed_seconds + '초)';
            syncAllAppend(line);
            results.push(r);
        }
        syncDbTables(idx + 1, since, totalSteps, currentStep, results, callback);
    })
    .catch(function(err) {
        syncAllAppend('❌ ' + table + ': ' + err.message);
        results.push({table: table, error: err.message});
        syncDbTables(idx + 1, since, totalSteps, currentStep, results, callback);
    });
}

function syncFileTypes(idx, since, totalSteps, currentStep, results, callback) {
    if (!syncAllRunning || idx >= FILE_TYPES.length) {
        callback(results);
        return;
    }

    var type = FILE_TYPES[idx];
    var label = FILE_LABELS[type];
    var pct = Math.round(((currentStep + idx) / totalSteps) * 100);
    document.getElementById('syncAllProgressFill').style.width = pct + '%';
    syncAllUpdateStatus('파일 동기화: ' + label + ' (' + (idx + 1) + '/' + FILE_TYPES.length + ')');

    var fd = new FormData();
    fd.append('action', 'file_sync');
    fd.append('file_type', type);
    if (since) fd.append('since', since);

    fetch(location.pathname, {method: 'POST', body: fd})
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.result) {
            var r = data.result;
            var line = '📁 ' + label + ': 다운로드 ' + r.downloaded + '건, 스킵 ' + r.skipped + '건';
            if (r.errors > 0) line += ', 에러 ' + r.errors + '건';
            line += ' (' + r.elapsed_seconds + '초)';
            syncAllAppend(line);
            results.push({type: type, label: label, downloaded: r.downloaded, skipped: r.skipped, errors: r.errors});
        }
        syncFileTypes(idx + 1, since, totalSteps, currentStep, results, callback);
    })
    .catch(function(err) {
        syncAllAppend('❌ ' + label + ': ' + err.message);
        results.push({type: type, label: label, error: err.message});
        syncFileTypes(idx + 1, since, totalSteps, currentStep, results, callback);
    });
}

function finishSyncAll(btn, stopBtn, bar, fill, results) {
    fill.style.width = '100%';
    btn.disabled = false;
    btn.textContent = '🔄 전체 동기화 시작';
    stopBtn.style.display = 'none';
    syncAllRunning = false;

    // 요약
    var totalInserted = 0, totalDownloaded = 0, totalErrors = 0;
    results.forEach(function(r) {
        if (r.inserted) totalInserted += r.inserted;
        if (r.downloaded) totalDownloaded += r.downloaded;
        if (r.errors) totalErrors += r.errors;
    });

    syncAllAppend('\n=== 전체 동기화 완료 ===');
    syncAllAppend('DB 추가: ' + totalInserted + '건 | 파일 다운로드: ' + totalDownloaded + '건 | 에러: ' + totalErrors + '건');

    syncAllUpdateStatus('완료!');

    // 마지막 동기화 시점 저장
    var fd = new FormData();
    fd.append('action', 'save_last_sync');
    fd.append('results', JSON.stringify({db_inserted: totalInserted, files_downloaded: totalDownloaded, errors: totalErrors}));
    fetch(location.pathname, {method: 'POST', body: fd})
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.saved_at) {
            document.getElementById('lastSyncDisplay').textContent = data.saved_at;
            syncAllAppend('✅ 마지막 동기화 시점 저장: ' + data.saved_at);
        }
    });

    setTimeout(function() { bar.style.display = 'none'; fill.style.width = '0'; }, 3000);
}

// ============================================================
// 개별 DB 동기화 (기존)
// ============================================================
function startSync() {
    var checked = document.querySelectorAll('.table-cb:checked');
    if (checked.length === 0) { alert('동기화할 테이블을 선택하세요.'); return; }

    var tables = [];
    checked.forEach(function(cb) { tables.push(cb.value); });

    var since = document.getElementById('since_date').value;
    var btn = document.getElementById('syncBtn');
    btn.disabled = true;
    btn.textContent = '동기화 중...';

    var bar = document.getElementById('progressBar');
    var fill = document.getElementById('progressFill');
    bar.style.display = 'block';
    fill.style.width = '30%';

    var resultArea = document.getElementById('result-area');
    var resultText = document.getElementById('result-text');
    resultArea.style.display = 'block';
    resultText.textContent = '동기화 진행 중... (' + tables.join(', ') + ')\n';

    var formData = new FormData();
    formData.append('action', 'sync');
    if (since) formData.append('since', since);
    tables.forEach(function(t) { formData.append('tables[]', t); });

    fetch(location.pathname, { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        fill.style.width = '100%';
        var output = '=== 동기화 완료 ===\n\n';
        if (data.results) {
            data.results.forEach(function(r) {
                output += '📦 ' + r.table + '\n';
                output += '   추가: ' + r.inserted + '건 | 스킵(중복): ' + r.skipped + '건 | 에러: ' + r.errors + '건\n';
                output += '   소요시간: ' + r.elapsed_seconds + '초\n';
                if (r.error_messages && r.error_messages.length > 0) {
                    r.error_messages.forEach(function(e) { output += '   ⚠️ ' + e + '\n'; });
                }
                output += '\n';
            });
        }
        resultText.textContent = output;
        btn.disabled = false;
        btn.textContent = '선택 항목 동기화';
        setTimeout(function() { bar.style.display = 'none'; fill.style.width = '0'; }, 2000);
    })
    .catch(function(err) {
        resultText.textContent += '\n❌ 오류: ' + err.message;
        btn.disabled = false;
        btn.textContent = '선택 항목 동기화';
        bar.style.display = 'none';
    });
}
</script>
</body></html>
<?php
}
?>
