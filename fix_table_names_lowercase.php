<?php
/**
 * 향상된 테이블명 소문자 변환 스크립트 (Enhanced & Safe)
 * 모든 테이블명을 소문자로 변환 (DB가 소문자로 변경되었으므로)
 */

// 웹 실행 환경에서만 동작
if (php_sapi_name() === 'cli') {
    die("이 스크립트는 웹 브라우저에서 실행해주세요: http://localhost/fix_table_names_lowercase.php\n");
}

header('Content-Type: text/html; charset=utf-8');

// 설정
$baseDirectory = __DIR__ . '/MlangPrintAuto';
$backupDirectory = __DIR__ . '/backup_lowercase_' . date('Y-m-d_H-i-s');
$logFile = __DIR__ . '/table_lowercase_log_' . date('Y-m-d_H-i-s') . '.txt';

// 통계 변수
$stats = [
    'scanned' => 0,
    'modified' => 0,
    'backed_up' => 0,
    'errors' => 0,
    'skipped' => 0
];

// 제외할 디렉토리/파일 패턴
$excludePatterns = [
    '/backup/',
    '/사용안함/',
    '/_backup/', 
    '/old/',
    '/temp/',
    '.bak',
    '.backup',
    '.old',
    'backup',
    '백업',
    '사용안함'
];

// 테이블명 매핑 (대문자 -> 소문자)
$tableMapping = [
    'MlangPrintAuto_transactionCate' => 'mlangprintauto_transactioncate',
    'MlangPrintAuto_NameCard' => 'mlangprintauto_namecard',
    'MlangPrintAuto_envelope' => 'mlangprintauto_envelope', 
    'MlangPrintAuto_LittlePrint' => 'mlangprintauto_littleprint',
    'MlangPrintAuto_MerchandiseBond' => 'mlangprintauto_merchandisebond',
    'MlangPrintAuto_NcrFlambeau' => 'mlangprintauto_ncrflambeau',
    'MlangPrintAuto_cadarok' => 'mlangprintauto_cadarok',
    'MlangPrintAuto_inserted' => 'mlangprintauto_inserted',
    'MlangPrintAuto_msticker' => 'mlangprintauto_msticker',
    'MlangPrintAuto_sticker' => 'mlangprintauto_sticker',
    'MlangOrder_PrintAuto' => 'mlangorder_printauto',
    'Mlang_portfolio_bbs' => 'mlang_portfolio_bbs'
];

$messages = [];

function addMessage($message, $type = 'info') {
    global $messages, $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $messages[] = ['time' => $timestamp, 'type' => $type, 'message' => $message];
    
    $logMessage = "[$timestamp][$type] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

function shouldExcludeFile($filePath) {
    global $excludePatterns;
    
    foreach ($excludePatterns as $pattern) {
        if (stripos($filePath, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

function createBackupDirectory($backupDir) {
    if (!file_exists($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            addMessage("백업 디렉토리 생성 실패: $backupDir", 'error');
            return false;
        }
        addMessage("백업 디렉토리 생성 완료: $backupDir", 'success');
    }
    return true;
}

function backupFile($originalPath, $backupDir) {
    global $stats;
    
    $relativePath = str_replace(__DIR__ . '/', '', $originalPath);
    $backupPath = $backupDir . '/' . $relativePath;
    
    $backupFileDir = dirname($backupPath);
    if (!file_exists($backupFileDir)) {
        mkdir($backupFileDir, 0755, true);
    }
    
    if (copy($originalPath, $backupPath)) {
        $stats['backed_up']++;
        return true;
    } else {
        addMessage("백업 실패: $originalPath", 'error');
        $stats['errors']++;
        return false;
    }
}

function validateFileContent($content) {
    if (strpos($content, '<?php') === false && strpos($content, '<?') === false) {
        return false;
    }
    
    $openTags = substr_count($content, '<?php') + substr_count($content, '<?=') + substr_count($content, '<?');
    $closeTags = substr_count($content, '?>');
    
    if ($openTags < $closeTags) {
        return false;
    }
    
    return true;
}

function processFileEnhanced($file, $tableMapping, $backupDir) {
    global $stats;
    
    try {
        $content = file_get_contents($file);
        if ($content === false) {
            addMessage("파일 읽기 실패: $file", 'error');
            $stats['errors']++;
            return false;
        }
        
        $originalContent = $content;
        $modified = false;
        $changes = [];
        
        if (!validateFileContent($content)) {
            addMessage("유효하지 않은 PHP 파일: $file", 'warning');
            $stats['skipped']++;
            return false;
        }
        
        if (!backupFile($file, $backupDir)) {
            addMessage("백업 실패로 인해 수정 중단: $file", 'error');
            return false;
        }
        
        foreach ($tableMapping as $oldName => $newName) {
            $patterns = [
                '/FROM\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                '/JOIN\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                '/UPDATE\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                '/INSERT\s+INTO\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                '/DELETE\s+FROM\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                '/SHOW\s+TABLES\s+LIKE\s+["\']' . preg_quote($oldName, '/') . '["\']/i',
                '/\$[A-Za-z_]+\s*=\s*["\']' . preg_quote($oldName, '/') . '["\']/i',
                '/["\']' . preg_quote($oldName, '/') . '\b["\']/i'
            ];
            
            foreach ($patterns as $i => $pattern) {
                $replacements = [
                    'FROM ' . $newName,
                    'JOIN ' . $newName, 
                    'UPDATE ' . $newName,
                    'INSERT INTO ' . $newName,
                    'DELETE FROM ' . $newName,
                    'SHOW TABLES LIKE "' . $newName . '"',
                    '$TABLE = "' . $newName . '"',
                    '"' . $newName . '"'
                ];
                
                $newContent = preg_replace($pattern, $replacements[$i], $content);
                
                if ($newContent !== $content) {
                    $content = $newContent;
                    $modified = true;
                    $changes[] = "$oldName → $newName";
                }
            }
        }
        
        if ($modified) {
            if (!validateFileContent($content)) {
                addMessage("수정 후 검증 실패: $file", 'error');
                $stats['errors']++;
                return false;
            }
            
            if (file_put_contents($file, $content) !== false) {
                $stats['modified']++;
                $changesText = implode(', ', array_unique($changes));
                addMessage("✅ 수정 완료: " . str_replace(__DIR__, '', $file) . " → [$changesText]", 'success');
                return true;
            } else {
                addMessage("파일 저장 실패: $file", 'error');
                $stats['errors']++;
                return false;
            }
        }
        
        return false;
        
    } catch (Exception $e) {
        addMessage("파일 처리 중 예외 발생: $file → " . $e->getMessage(), 'error');
        $stats['errors']++;
        return false;
    }
}

function scanDirectoryEnhanced($dir, $tableMapping, $backupDir) {
    global $stats;
    
    if (!is_dir($dir)) {
        addMessage("디렉토리가 존재하지 않습니다: $dir", 'error');
        return;
    }
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (shouldExcludeFile($path)) {
            addMessage("제외됨: " . str_replace(__DIR__, '', $path), 'info');
            $stats['skipped']++;
            continue;
        }
        
        if (is_dir($path)) {
            scanDirectoryEnhanced($path, $tableMapping, $backupDir);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $stats['scanned']++;
            processFileEnhanced($path, $tableMapping, $backupDir);
        }
    }
}

function createRollbackScript($backupDir) {
    $rollbackScript = __DIR__ . '/rollback_lowercase_changes.php';
    $content = "<?php
/**
 * 소문자 테이블명 변경 롤백 스크립트
 * 백업 위치: $backupDir
 */

header('Content-Type: text/html; charset=utf-8');

function rollbackChanges() {
    \$backupDir = '$backupDir';
    
    if (!is_dir(\$backupDir)) {
        echo \"<p style='color:red'>백업 디렉토리를 찾을 수 없습니다: \$backupDir</p>\";
        return false;
    }
    
    \$restored = 0;
    \$failed = 0;
    
    \$iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(\$backupDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach (\$iterator as \$file) {
        if (\$file->getExtension() === 'php') {
            \$backupPath = \$file->getRealPath();
            \$originalPath = str_replace(\$backupDir, __DIR__, \$backupPath);
            
            if (copy(\$backupPath, \$originalPath)) {
                echo \"<p style='color:green'>✅ 복원됨: \" . str_replace(__DIR__, '', \$originalPath) . \"</p>\";
                \$restored++;
            } else {
                echo \"<p style='color:red'>❌ 복원 실패: \" . str_replace(__DIR__, '', \$originalPath) . \"</p>\";
                \$failed++;
            }
        }
    }
    
    echo \"<h3>롤백 완료</h3>\";
    echo \"<p>복원된 파일: \$restored개</p>\";
    echo \"<p>실패한 파일: \$failed개</p>\";
    return true;
}

echo '<h1>🔄 소문자 변환 롤백</h1>';

if (isset(\$_GET['confirm']) && \$_GET['confirm'] === 'yes') {
    rollbackChanges();
} else {
    echo '<p>정말로 롤백하시겠습니까?</p>';
    echo '<a href=\"?confirm=yes\" style=\"background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">예, 롤백 실행</a>';
    echo ' ';
    echo '<a href=\"../\" style=\"background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">취소</a>';
}
?>";
    
    file_put_contents($rollbackScript, $content);
    addMessage("롤백 스크립트 생성: rollback_lowercase_changes.php", 'info');
}

function performSafetyChecks() {
    global $baseDirectory, $backupDirectory;
    
    addMessage("=== 안전성 검사 시작 ===", 'info');
    
    if (!is_dir($baseDirectory)) {
        addMessage("대상 디렉토리가 존재하지 않습니다: $baseDirectory", 'error');
        return false;
    }
    
    if (!is_writable($baseDirectory)) {
        addMessage("대상 디렉토리에 쓰기 권한이 없습니다: $baseDirectory", 'error');
        return false;
    }
    
    $parentDir = dirname($backupDirectory);
    if (!is_writable($parentDir)) {
        addMessage("백업 디렉토리를 생성할 수 없습니다: $parentDir", 'error');
        return false;
    }
    
    $freeBytes = disk_free_space($parentDir);
    $requiredBytes = 100 * 1024 * 1024; // 100MB
    if ($freeBytes < $requiredBytes) {
        addMessage("디스크 공간이 부족합니다. 필요: 100MB, 현재: " . round($freeBytes/1024/1024) . "MB", 'error');
        return false;
    }
    
    addMessage("✅ 모든 안전성 검사 통과", 'success');
    return true;
}

// 실행 부분
$execute = isset($_GET['execute']) && $_GET['execute'] === 'yes';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테이블명 소문자 변환 도구 (Enhanced & Safe)</title>
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            font-size: 16px;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: monospace;
        }
        .info { background: #d1ecf1; border-left: 4px solid #bee5eb; }
        .success { background: #d4edda; border-left: 4px solid #c3e6cb; }
        .warning { background: #fff3cd; border-left: 4px solid #ffeaa7; }
        .error { background: #f8d7da; border-left: 4px solid #f5c6cb; }
        .stats {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .config-box {
            background: #f1f3f4;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .log-container {
            max-height: 400px;
            overflow-y: auto;
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 테이블명 소문자 변환 도구</h1>
        
        <?php if (!$execute): ?>
            <div class="config-box">
                <h2>📋 작업 설정</h2>
                <p><strong>대상 디렉토리:</strong> <?= $baseDirectory ?></p>
                <p><strong>수정할 테이블:</strong> <?= count($tableMapping) ?>개</p>
                <p><strong>변환 방향:</strong> 대문자 → 소문자</p>
                <p><strong>백업 생성:</strong> 예 (<?= basename($backupDirectory) ?>)</p>
                <p><strong>롤백 가능:</strong> 예</p>
                <p><strong>제외 패턴:</strong> <?= implode(', ', $excludePatterns) ?></p>
            </div>
            
            <div class="config-box">
                <h3>🔄 테이블명 변환 매핑</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="background: #34495e; color: white;">
                        <th style="padding: 8px; border: 1px solid #ddd;">현재 (대문자)</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">변경 후 (소문자)</th>
                    </tr>
                    <?php foreach ($tableMapping as $old => $new): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd; font-family: monospace;"><?= $old ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; font-family: monospace;"><?= $new ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="?execute=yes" class="btn btn-success">🚀 소문자 변환 시작</a>
                <a href="../" class="btn btn-secondary">취소</a>
            </div>
            
        <?php else: ?>
            <h2>🔄 소문자 변환 실행 중...</h2>
            
            <?php
            try {
                if (!performSafetyChecks()) {
                    addMessage("안전성 검사 실패로 인해 작업을 중단합니다.", 'error');
                } else {
                    if (!createBackupDirectory($backupDirectory)) {
                        addMessage("백업 디렉토리 생성 실패로 인해 작업을 중단합니다.", 'error');
                    } else {
                        addMessage("파일 처리 시작...", 'info');
                        scanDirectoryEnhanced($baseDirectory, $tableMapping, $backupDirectory);
                        createRollbackScript($backupDirectory);
                        addMessage("모든 작업이 완료되었습니다.", 'success');
                    }
                }
            } catch (Exception $e) {
                addMessage("치명적인 오류 발생: " . $e->getMessage(), 'error');
            }
            ?>
            
            <div class="stats">
                <h3>📊 실행 결과</h3>
                <p><strong>검사한 파일:</strong> <?= $stats['scanned'] ?>개</p>
                <p><strong>수정된 파일:</strong> <?= $stats['modified'] ?>개</p>
                <p><strong>백업된 파일:</strong> <?= $stats['backed_up'] ?>개</p>
                <p><strong>제외된 파일:</strong> <?= $stats['skipped'] ?>개</p>
                <p><strong>오류 발생:</strong> <?= $stats['errors'] ?>개</p>
            </div>
            
            <div class="config-box">
                <h3>📁 생성된 파일</h3>
                <p><strong>백업 위치:</strong> <?= basename($backupDirectory) ?>/</p>
                <p><strong>로그 파일:</strong> <?= basename($logFile) ?></p>
                <p><strong>롤백 스크립트:</strong> rollback_lowercase_changes.php</p>
            </div>
            
            <div class="log-container">
                <h3>📜 실행 로그</h3>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['type'] ?>">
                        [<?= $msg['time'] ?>] <?= htmlspecialchars($msg['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="rollback_lowercase_changes.php" class="btn btn-danger">🔄 롤백 실행</a>
                <a href="../" class="btn btn-secondary">완료</a>
                <a href="?" class="btn btn-secondary">다시 실행</a>
            </div>
            
        <?php endif; ?>
    </div>
</body>
</html>