<?php
session_start();

$MIGRATION_PASSWORD = 'duson2026!migration';

if (isset($_POST['password'])) {
    if ($_POST['password'] === $MIGRATION_PASSWORD) {
        $_SESSION['migration_auth'] = true;
    }
}
if (empty($_SESSION['migration_auth'])) {
    echo '<form method="post"><input type="password" name="password" placeholder="비밀번호"><button>로그인</button></form>';
    exit;
}

include dirname(dirname(__DIR__)) . '/db.php';

// 파일 필터 설정 (서버별로 다르게 설정)
// dsp114.co.kr: min_no=84574, min_year=2026 (용량 제한)
// NAS: min_no=0, min_year=2000 (전체 백업)
define('FILE_FILTER_MIN_NO', 84574);    // 교정파일: 이 번호 이상만
define('FILE_FILTER_MIN_YEAR', 2026);   // 원고파일: 이 연도 이상만

$action = isset($_GET['action']) ? $_GET['action'] : '';
$SOURCE_BASE = 'http://dsp114.com';

if ($action === 'run') {
    header('Content-Type: application/json; charset=utf-8');
    set_time_limit(300);

    $type = isset($_POST['type']) ? $_POST['type'] : 'shop';
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $since = isset($_POST['since']) ? trim($_POST['since']) : '';
    $batch = 20;

    $web_root = dirname(dirname(__DIR__));
    $downloaded = 0;
    $skipped = 0;
    $errors = 0;
    $error_list = array();

    if ($type === 'shop') {
        $where_year = (FILE_FILTER_MIN_YEAR > 0) ? " AND date >= '" . intval(FILE_FILTER_MIN_YEAR) . "-01-01'" : "";
        $where_since = ($since !== '') ? " AND date >= ?" : "";
        $query = "SELECT no, ImgFolder FROM mlangorder_printauto 
                  WHERE ImgFolder LIKE '../shop/data/%'" . $where_year . $where_since . " 
                  ORDER BY no ASC LIMIT ?, ?";
        $stmt = mysqli_prepare($db, $query);
        if ($since !== '') {
            mysqli_stmt_bind_param($stmt, "sii", $since, $offset, $batch);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $offset, $batch);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $filename_utf8 = str_replace('../shop/data/', '', $row['ImgFolder']);
            $local_path = $web_root . '/shop/data/' . $filename_utf8;

            if (file_exists($local_path) && filesize($local_path) > 0) {
                $skipped++;
                continue;
            }

            $filename_euckr = @iconv('UTF-8', 'EUC-KR//IGNORE', $filename_utf8);
            if ($filename_euckr === false) $filename_euckr = $filename_utf8;
            $url = $SOURCE_BASE . '/shop/data/' . euckr_urlencode($filename_euckr);

            $content = download_file($url);
            if ($content === false) {
                $url_utf8 = $SOURCE_BASE . '/shop/data/' . rawurlencode($filename_utf8);
                $content = download_file($url_utf8);
            }
            if ($content === false) {
                $url_raw = $SOURCE_BASE . '/shop/data/' . rawurlencode($filename_euckr);
                $content = download_file($url_raw);
            }

            if ($content !== false && strlen($content) > 0) {
                $dir = dirname($local_path);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                file_put_contents($local_path, $content);
                $downloaded++;
            } else {
                $errors++;
                $error_list[] = "#{$row['no']}: $filename_utf8";
            }
        }
        mysqli_stmt_close($stmt);

    } elseif ($type === 'imgfolder') {
        $where_year = (FILE_FILTER_MIN_YEAR > 0) ? " AND date >= '" . intval(FILE_FILTER_MIN_YEAR) . "-01-01'" : "";
        $where_since = ($since !== '') ? " AND date >= ?" : "";
        $query = "SELECT no, ImgFolder FROM mlangorder_printauto 
                  WHERE ImgFolder LIKE '_MlangPrintAuto_%'" . $where_year . $where_since . " 
                  ORDER BY no ASC LIMIT ?, ?";
        $stmt = mysqli_prepare($db, $query);
        if ($since !== '') {
            mysqli_stmt_bind_param($stmt, "sii", $since, $offset, $batch);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $offset, $batch);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $folder_path = $row['ImgFolder'];
            $source_dir_url = $SOURCE_BASE . '/ImgFolder/' . $folder_path . '/';

            $local_dir = $web_root . '/ImgFolder/' . $folder_path;

            if (is_dir($local_dir)) {
                $existing = glob($local_dir . '/*');
                if ($existing && count($existing) > 0) {
                    $skipped++;
                    continue;
                }
            }

            $list_url = $SOURCE_BASE . '/export_api.php?key=duson_migration_sync_2026_xK9m&table=_file_download_list&path=' . urlencode('imgfolder/' . $folder_path);
            
            if (!is_dir($local_dir)) mkdir($local_dir, 0755, true);

            $page_html = download_file($source_dir_url);
            if ($page_html !== false && preg_match_all('/href="([^"]+\.(jpg|jpeg|png|gif|pdf|ai|psd|zip|eps|tif|tiff|bmp))/i', $page_html, $matches)) {
                $got_any = false;
                foreach ($matches[1] as $fname) {
                    $fname = basename($fname);
                    $local_file = $local_dir . '/' . $fname;
                    if (file_exists($local_file) && filesize($local_file) > 0) continue;

                    $file_url = $source_dir_url . rawurlencode($fname);
                    $content = download_file($file_url);
                    if ($content !== false && strlen($content) > 100) {
                        file_put_contents($local_file, $content);
                        $got_any = true;
                    }
                }
                if ($got_any) $downloaded++;
                else $skipped++;
            } else {
                $errors++;
                $error_list[] = "#{$row['no']}: $folder_path (dir listing failed)";
            }
        }
        mysqli_stmt_close($stmt);

    } elseif ($type === 'upload') {
        // min_no 필터: 주문번호 기준 하한선 (예: 84574 이상만)
        $where_min = (FILE_FILTER_MIN_NO > 0) ? " WHERE no >= " . intval(FILE_FILTER_MIN_NO) : " WHERE no > 0";
        $where_since = ($since !== '') ? " AND date >= ?" : "";
        $query = "SELECT no FROM mlangorder_printauto" . $where_min . $where_since . " ORDER BY no ASC LIMIT ?, ?";
        $stmt = mysqli_prepare($db, $query);
        if ($since !== '') {
            mysqli_stmt_bind_param($stmt, "sii", $since, $offset, $batch);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $offset, $batch);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $no = $row['no'];
            $local_dir = $web_root . '/mlangorder_printauto/upload/' . $no;

            if (is_dir($local_dir)) {
                $existing = glob($local_dir . '/*');
                if ($existing && count($existing) > 0) {
                    $skipped++;
                    continue;
                }
            }

            $source_dir_url = $SOURCE_BASE . '/MlangOrder_PrintAuto/upload/' . $no . '/';
            $page_html = download_file($source_dir_url);

            if ($page_html !== false && preg_match_all('/href="([^"]+\.(jpg|jpeg|png|gif|pdf|ai|psd|zip|eps|tif|tiff|bmp))/i', $page_html, $matches)) {
                if (!is_dir($local_dir)) mkdir($local_dir, 0755, true);
                $got_any = false;
                foreach ($matches[1] as $fname) {
                    $fname = basename($fname);
                    $local_file = $local_dir . '/' . $fname;
                    if (file_exists($local_file) && filesize($local_file) > 0) continue;

                    $file_url = $source_dir_url . rawurlencode($fname);
                    $content = download_file($file_url);
                    if ($content !== false && strlen($content) > 100) {
                        file_put_contents($local_file, $content);
                        $got_any = true;
                    }
                }
                if ($got_any) $downloaded++;
                else $skipped++;
            } else {
                $skipped++;
            }
        }
        mysqli_stmt_close($stmt);
    }

    echo json_encode(array(
        'type' => $type,
        'offset' => $offset,
        'batch' => $batch,
        'downloaded' => $downloaded,
        'skipped' => $skipped,
        'errors' => $errors,
        'error_list' => array_slice($error_list, 0, 10),
    ));
    exit;
}

if ($action === 'count') {
    header('Content-Type: application/json; charset=utf-8');
    $counts = array();
    $year_filter = (FILE_FILTER_MIN_YEAR > 0) ? " AND date >= '" . intval(FILE_FILTER_MIN_YEAR) . "-01-01'" : "";
    $no_filter = (FILE_FILTER_MIN_NO > 0) ? " WHERE no >= " . intval(FILE_FILTER_MIN_NO) : "";
    $res = mysqli_query($db, "SELECT COUNT(*) as c FROM mlangorder_printauto WHERE ImgFolder LIKE '../shop/data/%'" . $year_filter);
    $counts['shop'] = intval(mysqli_fetch_assoc($res)['c']);
    $res = mysqli_query($db, "SELECT COUNT(*) as c FROM mlangorder_printauto WHERE ImgFolder LIKE '_MlangPrintAuto_%'" . $year_filter);
    $counts['imgfolder'] = intval(mysqli_fetch_assoc($res)['c']);
    $res = mysqli_query($db, "SELECT COUNT(*) as c FROM mlangorder_printauto" . $no_filter);
    $counts['upload'] = intval(mysqli_fetch_assoc($res)['c']);
    echo json_encode($counts);
    exit;
}

$year_filter = (FILE_FILTER_MIN_YEAR > 0) ? " AND date >= '" . intval(FILE_FILTER_MIN_YEAR) . "-01-01'" : "";
$no_filter = (FILE_FILTER_MIN_NO > 0) ? " WHERE no >= " . intval(FILE_FILTER_MIN_NO) : "";
$res = mysqli_query($db, "SELECT COUNT(*) as c FROM mlangorder_printauto WHERE ImgFolder LIKE '../shop/data/%'" . $year_filter);
$shop_count = intval(mysqli_fetch_assoc($res)['c']);
$res = mysqli_query($db, "SELECT COUNT(*) as c FROM mlangorder_printauto WHERE ImgFolder LIKE '_MlangPrintAuto_%'" . $year_filter);
$img_count = intval(mysqli_fetch_assoc($res)['c']);
$res = mysqli_query($db, "SELECT COUNT(*) as c FROM mlangorder_printauto" . $no_filter);
$upload_count = intval(mysqli_fetch_assoc($res)['c']);

function euckr_urlencode($str) {
    $result = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $byte = ord($str[$i]);
        if (($byte >= 0x30 && $byte <= 0x39) || ($byte >= 0x41 && $byte <= 0x5A) ||
            ($byte >= 0x61 && $byte <= 0x7A) || $byte == 0x2D || $byte == 0x2E ||
            $byte == 0x5F || $byte == 0x7E) {
            $result .= $str[$i];
        } elseif ($byte == 0x20) {
            $result .= '%20';
        } else {
            $result .= '%' . strtoupper(dechex($byte));
        }
    }
    return $result;
}

function download_file($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $result = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) return $result;
    return false;
}
?>
<!DOCTYPE html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>파일 동기화 - dsp114.com → dsp114.co.kr</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f0f2f5;color:#333}
.header{background:#1a1a2e;color:#fff;padding:16px 24px}
.header h1{font-size:18px}
.header .info{font-size:12px;color:#adb5bd;margin-top:4px}
.container{max-width:900px;margin:24px auto;padding:0 16px}
.card{background:#fff;border-radius:10px;box-shadow:0 1px 8px rgba(0,0,0,.06);margin-bottom:20px;padding:20px}
.card h2{font-size:16px;margin-bottom:16px;color:#1a1a2e}
.grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px}
.type-box{background:#f8f9fa;padding:16px;border-radius:8px;text-align:center}
.type-box h3{font-size:14px;margin-bottom:8px}
.type-box .count{font-size:24px;font-weight:700;color:#4361ee}
.type-box .label{font-size:12px;color:#888;margin-bottom:12px}
.btn{padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:500;background:#4361ee;color:#fff}
.btn:hover{background:#3a56d4}
.btn:disabled{background:#aaa;cursor:not-allowed}
.btn-danger{background:#e74c3c}
#log{background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:8px;font-size:12px;line-height:1.8;max-height:500px;overflow-y:auto;white-space:pre-wrap;display:none;margin-top:16px}
.progress{height:6px;background:#e0e0e0;border-radius:3px;overflow:hidden;margin-top:12px;display:none}
.progress .fill{height:100%;background:#4361ee;transition:width .3s;width:0}
.stats{display:flex;gap:20px;margin-top:12px;font-size:13px}
.stats span{padding:4px 12px;border-radius:12px;font-weight:500}
.stats .dl{background:#e6f4ea;color:#137333}
.stats .sk{background:#e8f0fe;color:#1a73e8}
.stats .er{background:#fce8e6;color:#c5221f}
</style>
</head><body>

<div class="header">
<h1>📁 파일 동기화 (직접 다운로드)</h1>
<div class="info">dsp114.com → dsp114.co.kr | export_api 불필요, URL 직접 다운로드</div>
</div>

<div class="container">

<div class="card">
<h2>파일 유형별 현황</h2>
<div style="margin-bottom:16px;display:flex;align-items:center;gap:12px">
<label style="font-size:13px;font-weight:600;color:#555">📅 날짜 필터 (이후):</label>
<input type="date" id="sinceDate" value="2026-01-29" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px">
<span style="font-size:12px;color:#888">* 이 날짜 이후의 주문 파일만 동기화합니다</span>
</div>
<div class="grid">
<div class="type-box">
<h3>🖼️ 원고 (스티커)</h3>
<div class="count"><?= number_format($shop_count) ?></div>
<div class="label">shop/data/ 경로</div>
<button class="btn" onclick="startSync('shop', <?= $shop_count ?>)">동기화 시작</button>
</div>
<div class="type-box">
<h3>📂 원고 (일반제품)</h3>
<div class="count"><?= number_format($img_count) ?></div>
<div class="label">ImgFolder/ 경로</div>
<button class="btn" onclick="startSync('imgfolder', <?= $img_count ?>)">동기화 시작</button>
</div>
<div class="type-box">
<h3>📋 교정파일</h3>
<div class="count"><?= number_format($upload_count) ?></div>
<div class="label">upload/{no}/ 경로</div>
<button class="btn" onclick="startSync('upload', <?= $upload_count ?>)">동기화 시작</button>
</div>
</div>

<button class="btn btn-danger" id="stopBtn" onclick="stopSync()" style="display:none">⏹ 중지</button>

<div class="progress" id="progressBar"><div class="fill" id="progressFill"></div></div>
<div class="stats" id="statsArea" style="display:none">
<span class="dl">📥 다운로드: <b id="statDl">0</b></span>
<span class="sk">⏭️ 스킵: <b id="statSk">0</b></span>
<span class="er">❌ 에러: <b id="statEr">0</b></span>
</div>
<div id="log"></div>
</div>

</div>

<script>
var running = false;
var totalDl = 0, totalSk = 0, totalEr = 0;

function log(msg) {
    var el = document.getElementById('log');
    el.style.display = 'block';
    el.textContent += msg + '\n';
    el.scrollTop = el.scrollHeight;
}

function updateStats() {
    document.getElementById('statDl').textContent = totalDl;
    document.getElementById('statSk').textContent = totalSk;
    document.getElementById('statEr').textContent = totalEr;
}

function stopSync() { running = false; log('⏹ 사용자가 중지했습니다.'); }

function startSync(type, total) {
    if (running) return;
    running = true;
    totalDl = 0; totalSk = 0; totalEr = 0;
    
    var since = document.getElementById('sinceDate').value;
    var labels = {shop: '원고(스티커)', imgfolder: '원고(일반제품)', upload: '교정파일'};
    var sinceMsg = since ? ' (날짜: ' + since + ' 이후)' : ' (전체)';
    log('=== ' + labels[type] + ' 동기화 시작' + sinceMsg + ' ===');
    
    document.getElementById('stopBtn').style.display = 'inline-block';
    document.getElementById('progressBar').style.display = 'block';
    document.getElementById('statsArea').style.display = 'flex';
    updateStats();

    runBatch(type, 0, total);
}

function runBatch(type, offset, total) {
    if (!running) {
        finish();
        return;
    }

    var pct = Math.min(100, Math.round(offset / total * 100));
    document.getElementById('progressFill').style.width = pct + '%';

    var fd = new FormData();
    fd.append('type', type);
    fd.append('offset', offset);
    var since = document.getElementById('sinceDate').value;
    if (since) fd.append('since', since);

    fetch('?action=run', {method: 'POST', body: fd})
    .then(function(r) { return r.json(); })
    .then(function(data) {
        totalDl += data.downloaded;
        totalSk += data.skipped;
        totalEr += data.errors;
        updateStats();

        var processed = data.downloaded + data.skipped + data.errors;
        if (processed > 0) {
            log('[offset=' + offset + '] 다운로드:' + data.downloaded + ' 스킵:' + data.skipped + ' 에러:' + data.errors);
        }
        if (data.error_list && data.error_list.length > 0) {
            data.error_list.forEach(function(e) { log('  ⚠️ ' + e); });
        }

        if (processed > 0 && running) {
            runBatch(type, offset + data.batch, total);
        } else {
            log('\n=== 완료! 다운로드:' + totalDl + ' 스킵:' + totalSk + ' 에러:' + totalEr + ' ===');
            finish();
        }
    })
    .catch(function(err) {
        log('❌ 네트워크 에러: ' + err.message);
        finish();
    });
}

function finish() {
    running = false;
    document.getElementById('stopBtn').style.display = 'none';
    document.getElementById('progressFill').style.width = '100%';
}
</script>
</body></html>
