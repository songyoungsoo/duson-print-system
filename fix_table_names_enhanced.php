<?php
/**
 * 향상된 테이블명 대소문자 일괄 수정 스크립트 (Enhanced & Safe)
 * 백업 생성, 롤백 기능, 향상된 검증 및 로깅 포함
 */

// 설정
$baseDirectory = __DIR__ . '/MlangPrintAuto';
$backupDirectory = __DIR__ . '/backup_' . date('Y-m-d_H-i-s');
$logFile = __DIR__ . '/table_fix_log_' . date('Y-m-d_H-i-s') . '.txt';

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

// 테이블명 매핑 (소문자 -> 대문자)
$tableMapping = [
    'mlangprintauto_transactioncate' => 'MlangPrintAuto_transactionCate',
    'mlangprintauto_namecard' => 'MlangPrintAuto_NameCard', 
    'mlangprintauto_envelope' => 'MlangPrintAuto_envelope',
    'mlangprintauto_littleprint' => 'MlangPrintAuto_LittlePrint',
    'mlangprintauto_merchandisebond' => 'MlangPrintAuto_MerchandiseBond',
    'mlangprintauto_ncrflambeau' => 'MlangPrintAuto_NcrFlambeau',
    'mlangprintauto_cadarok' => 'MlangPrintAuto_cadarok',
    'mlangprintauto_inserted' => 'MlangPrintAuto_inserted',
    'mlangprintauto_msticker' => 'MlangPrintAuto_msticker',
    'mlangprintauto_sticker' => 'MlangPrintAuto_sticker',
    'mlangorder_printauto' => 'MlangOrder_PrintAuto',
    'mlang_portfolio_bbs' => 'Mlang_portfolio_bbs'
];

/**
 * 로그 작성 함수
 */
function writeLog($message, $type = 'INFO') {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][$type] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    echo $logMessage;
}

/**
 * 파일이 제외 대상인지 확인
 */
function shouldExcludeFile($filePath) {
    global $excludePatterns;
    
    foreach ($excludePatterns as $pattern) {
        if (stripos($filePath, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * 백업 디렉토리 생성
 */
function createBackupDirectory($backupDir) {
    if (!file_exists($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            writeLog("백업 디렉토리 생성 실패: $backupDir", 'ERROR');
            return false;
        }
        writeLog("백업 디렉토리 생성 완료: $backupDir", 'SUCCESS');
    }
    return true;
}

/**
 * 파일 백업 생성
 */
function backupFile($originalPath, $backupDir) {
    global $stats;
    
    $relativePath = str_replace(__DIR__ . '/', '', $originalPath);
    $backupPath = $backupDir . '/' . $relativePath;
    
    // 백업 디렉토리 구조 생성
    $backupFileDir = dirname($backupPath);
    if (!file_exists($backupFileDir)) {
        mkdir($backupFileDir, 0755, true);
    }
    
    if (copy($originalPath, $backupPath)) {
        $stats['backed_up']++;
        return true;
    } else {
        writeLog("백업 실패: $originalPath", 'ERROR');
        $stats['errors']++;
        return false;
    }
}

/**
 * 파일 내용 검증
 */
function validateFileContent($content) {
    // PHP 구문 검증
    if (strpos($content, '<?php') === false && strpos($content, '<?') === false) {
        return false; // PHP 파일이 아님
    }
    
    // 기본적인 구문 무결성 검사
    $openTags = substr_count($content, '<?php') + substr_count($content, '<?=') + substr_count($content, '<?');
    $closeTags = substr_count($content, '?>');
    
    // 열린 태그가 닫힌 태그보다 많거나 같아야 함 (닫는 태그는 선택사항)
    if ($openTags < $closeTags) {
        return false;
    }
    
    return true;
}

/**
 * 파일 처리 (향상된 버전)
 */
function processFileEnhanced($file, $tableMapping, $backupDir) {
    global $stats;
    
    try {
        // 파일 읽기
        $content = file_get_contents($file);
        if ($content === false) {
            writeLog("파일 읽기 실패: $file", 'ERROR');
            $stats['errors']++;
            return false;
        }
        
        $originalContent = $content;
        $modified = false;
        $changes = [];
        
        // 파일 내용 검증
        if (!validateFileContent($content)) {
            writeLog("유효하지 않은 PHP 파일: $file", 'WARNING');
            $stats['skipped']++;
            return false;
        }
        
        // 백업 생성
        if (!backupFile($file, $backupDir)) {
            writeLog("백업 실패로 인해 수정 중단: $file", 'ERROR');
            return false;
        }
        
        // 테이블명 변경 처리
        foreach ($tableMapping as $oldName => $newName) {
            $patterns = [
                // FROM 절
                '/FROM\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                // JOIN 절  
                '/JOIN\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                // UPDATE 절
                '/UPDATE\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                // INSERT INTO 절
                '/INSERT\s+INTO\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                // DELETE FROM 절
                '/DELETE\s+FROM\s+`?' . preg_quote($oldName, '/') . '`?\b/i',
                // SHOW TABLES LIKE 절
                '/SHOW\s+TABLES\s+LIKE\s+["\']' . preg_quote($oldName, '/') . '["\']/i',
                // 변수에 할당된 테이블명
                '/\$[A-Za-z_]+\s*=\s*["\']' . preg_quote($oldName, '/') . '["\']/i',
                // 쿼리 문자열 내 테이블명 (따옴표로 둘러싸인)
                '/["\']' . preg_quote($oldName, '/') . '\b["\']/i'
            ];
            
            foreach ($patterns as $pattern) {
                $matches = [];
                if (preg_match_all($pattern, $content, $matches)) {
                    $newContent = preg_replace($pattern, function($match) use ($oldName, $newName) {
                        return str_ireplace($oldName, $newName, $match[0]);
                    }, $content);
                    
                    if ($newContent !== $content) {
                        $content = $newContent;
                        $modified = true;
                        $changes[] = "$oldName → $newName";
                    }
                }
            }
        }
        
        // 변경사항이 있는 경우 파일 저장
        if ($modified) {
            // 수정된 내용 재검증
            if (!validateFileContent($content)) {
                writeLog("수정 후 검증 실패: $file", 'ERROR');
                $stats['errors']++;
                return false;
            }
            
            if (file_put_contents($file, $content) !== false) {
                $stats['modified']++;
                $changesText = implode(', ', array_unique($changes));
                writeLog("✅ 수정 완료: $file → [$changesText]", 'SUCCESS');
                return true;
            } else {
                writeLog("파일 저장 실패: $file", 'ERROR');
                $stats['errors']++;
                return false;
            }
        }
        
        return false;
        
    } catch (Exception $e) {
        writeLog("파일 처리 중 예외 발생: $file → " . $e->getMessage(), 'ERROR');
        $stats['errors']++;
        return false;
    }
}

/**
 * 디렉토리 스캔 (향상된 버전)
 */
function scanDirectoryEnhanced($dir, $tableMapping, $backupDir) {
    global $stats;
    
    if (!is_dir($dir)) {
        writeLog("디렉토리가 존재하지 않습니다: $dir", 'ERROR');
        return;
    }
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        // 제외 대상 확인
        if (shouldExcludeFile($path)) {
            writeLog("제외됨: " . str_replace(__DIR__, '', $path), 'INFO');
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

/**
 * 롤백 스크립트 생성
 */
function createRollbackScript($backupDir) {
    $rollbackScript = __DIR__ . '/rollback_table_changes.php';
    $content = "<?php
/**
 * 테이블명 변경 롤백 스크립트
 * 백업 위치: $backupDir
 */

function rollbackChanges() {
    \$backupDir = '$backupDir';
    
    if (!is_dir(\$backupDir)) {
        echo \"백업 디렉토리를 찾을 수 없습니다: \$backupDir\\n\";
        return false;
    }
    
    \$iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(\$backupDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach (\$iterator as \$file) {
        if (\$file->getExtension() === 'php') {
            \$backupPath = \$file->getRealPath();
            \$originalPath = str_replace(\$backupDir, __DIR__, \$backupPath);
            
            if (copy(\$backupPath, \$originalPath)) {
                echo \"✅ 복원됨: \$originalPath\\n\";
            } else {
                echo \"❌ 복원 실패: \$originalPath\\n\";
            }
        }
    }
    
    echo \"\\n롤백 완료\\n\";
    return true;
}

// 사용자 확인
echo \"정말로 롤백하시겠습니까? (y/N): \";
\$input = trim(fgets(STDIN));

if (strtolower(\$input) === 'y' || strtolower(\$input) === 'yes') {
    rollbackChanges();
} else {
    echo \"롤백이 취소되었습니다.\\n\";
}
?>";
    
    file_put_contents($rollbackScript, $content);
    writeLog("롤백 스크립트 생성: $rollbackScript", 'INFO');
}

/**
 * 실행 전 안전성 검사
 */
function performSafetyChecks() {
    global $baseDirectory, $backupDirectory;
    
    writeLog("=== 안전성 검사 시작 ===", 'INFO');
    
    // 1. 대상 디렉토리 존재 확인
    if (!is_dir($baseDirectory)) {
        writeLog("대상 디렉토리가 존재하지 않습니다: $baseDirectory", 'ERROR');
        return false;
    }
    
    // 2. 쓰기 권한 확인
    if (!is_writable($baseDirectory)) {
        writeLog("대상 디렉토리에 쓰기 권한이 없습니다: $baseDirectory", 'ERROR');
        return false;
    }
    
    // 3. 백업 디렉토리 생성 가능 확인
    $parentDir = dirname($backupDirectory);
    if (!is_writable($parentDir)) {
        writeLog("백업 디렉토리를 생성할 수 없습니다: $parentDir", 'ERROR');
        return false;
    }
    
    // 4. 디스크 용량 확인 (최소 100MB)
    $freeBytes = disk_free_space($parentDir);
    $requiredBytes = 100 * 1024 * 1024; // 100MB
    if ($freeBytes < $requiredBytes) {
        writeLog("디스크 공간이 부족합니다. 필요: 100MB, 현재: " . round($freeBytes/1024/1024) . "MB", 'ERROR');
        return false;
    }
    
    writeLog("✅ 모든 안전성 검사 통과", 'SUCCESS');
    return true;
}

/**
 * 실행 결과 요약 출력
 */
function printSummary() {
    global $stats, $backupDirectory, $logFile;
    
    writeLog("", 'INFO');
    writeLog("=== 실행 결과 요약 ===", 'INFO');
    writeLog("검사한 파일: {$stats['scanned']}개", 'INFO');
    writeLog("수정된 파일: {$stats['modified']}개", 'SUCCESS');
    writeLog("백업된 파일: {$stats['backed_up']}개", 'INFO');
    writeLog("제외된 파일: {$stats['skipped']}개", 'INFO');
    writeLog("오류 발생: {$stats['errors']}개", ($stats['errors'] > 0 ? 'ERROR' : 'INFO'));
    writeLog("", 'INFO');
    writeLog("백업 위치: $backupDirectory", 'INFO');
    writeLog("로그 파일: $logFile", 'INFO');
    writeLog("롤백 스크립트: rollback_table_changes.php", 'INFO');
}

/**
 * 사용자 확인 요청
 */
function requestUserConfirmation() {
    global $baseDirectory, $tableMapping;
    
    echo "\n=== 테이블명 수정 작업 확인 ===\n\n";
    echo "대상 디렉토리: $baseDirectory\n";
    echo "수정할 테이블: " . count($tableMapping) . "개\n";
    echo "백업 생성: 예\n";
    echo "롤백 가능: 예\n\n";
    
    echo "계속 진행하시겠습니까? (y/N): ";
    $input = trim(fgets(STDIN));
    
    return (strtolower($input) === 'y' || strtolower($input) === 'yes');
}

// ==================== 메인 실행 ====================

writeLog("=== 향상된 테이블명 수정 스크립트 시작 ===", 'INFO');

try {
    // 1. 안전성 검사
    if (!performSafetyChecks()) {
        writeLog("안전성 검사 실패로 인해 작업을 중단합니다.", 'ERROR');
        exit(1);
    }
    
    // 2. 사용자 확인 (CLI 모드에서만)
    if (php_sapi_name() === 'cli' && !requestUserConfirmation()) {
        writeLog("사용자가 작업을 취소했습니다.", 'INFO');
        exit(0);
    }
    
    // 3. 백업 디렉토리 생성
    if (!createBackupDirectory($backupDirectory)) {
        writeLog("백업 디렉토리 생성 실패로 인해 작업을 중단합니다.", 'ERROR');
        exit(1);
    }
    
    // 4. 파일 처리 실행
    writeLog("파일 처리 시작...", 'INFO');
    scanDirectoryEnhanced($baseDirectory, $tableMapping, $backupDirectory);
    
    // 5. 롤백 스크립트 생성
    createRollbackScript($backupDirectory);
    
    // 6. 실행 결과 요약
    printSummary();
    
    if ($stats['errors'] > 0) {
        writeLog("일부 오류가 발생했습니다. 로그를 확인해주세요.", 'WARNING');
        exit(1);
    } else {
        writeLog("모든 작업이 성공적으로 완료되었습니다.", 'SUCCESS');
        exit(0);
    }
    
} catch (Exception $e) {
    writeLog("치명적인 오류 발생: " . $e->getMessage(), 'ERROR');
    writeLog("스택 트레이스: " . $e->getTraceAsString(), 'ERROR');
    exit(1);
}
?>