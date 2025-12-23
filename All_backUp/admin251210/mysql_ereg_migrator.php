<?php
/**
 * MySQL 및 EREG 함수 마이그레이션 스크립트
 * PHP 7.4 호환성을 위한 핵심 함수 변환
 *
 * 변환 대상:
 * 1. mysql_* → mysqli_* 함수
 * 2. ereg/eregi → preg_match 함수
 * 3. 에러 처리 현대화
 */

declare(strict_types=1);

// 로그 파일 설정
$logFile = __DIR__ . '/mysql_ereg_migration_log_' . date('Y-m-d_H-i-s') . '.txt';

class MysqlEregMigrator {
    private string $logFile;
    private array $stats = [
        'total_files' => 0,
        'migrated_files' => 0,
        'mysql_conversions' => 0,
        'ereg_conversions' => 0,
        'errors' => 0,
        'skipped_files' => 0
    ];

    public function __construct(string $logFile) {
        $this->logFile = $logFile;
    }

    public function writeLog(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        echo $logMessage;
    }

    public function migrateFile(string $filePath, bool $dryRun = false): bool {
        if (!file_exists($filePath)) {
            $this->writeLog("❌ 파일이 존재하지 않습니다: $filePath");
            $this->stats['errors']++;
            return false;
        }

        $originalContent = file_get_contents($filePath);
        if ($originalContent === false) {
            $this->writeLog("❌ 파일 읽기 실패: $filePath");
            $this->stats['errors']++;
            return false;
        }

        $migratedContent = $originalContent;

        // MySQL 함수 변환
        $migratedContent = $this->convertMysqlFunctions($migratedContent, $filePath);

        // EREG 함수 변환
        $migratedContent = $this->convertEregFunctions($migratedContent, $filePath);

        // 에러 처리 개선
        $migratedContent = $this->improveErrorHandling($migratedContent, $filePath);

        // 변경사항이 있는지 확인
        if ($originalContent === $migratedContent) {
            $this->writeLog("⏭️  변경사항 없음: $filePath");
            $this->stats['skipped_files']++;
            return true;
        }

        if (!$dryRun) {
            // 백업 생성
            $backupPath = $filePath . '.backup_before_mysql_ereg_' . date('YmdHis');
            if (!copy($filePath, $backupPath)) {
                $this->writeLog("❌ 백업 생성 실패: $filePath");
                $this->stats['errors']++;
                return false;
            }
            $this->writeLog("💾 백업 생성: $backupPath");

            // 마이그레이션된 코드 저장
            if (file_put_contents($filePath, $migratedContent) === false) {
                $this->writeLog("❌ 파일 저장 실패: $filePath");
                $this->stats['errors']++;
                return false;
            }
            $this->writeLog("✅ MySQL/EREG 마이그레이션 완료: $filePath");
        } else {
            $this->writeLog("🔍 [DRY RUN] MySQL/EREG 마이그레이션 예정: $filePath");
        }

        $this->stats['migrated_files']++;
        return true;
    }

    /**
     * MySQL 함수를 MySQLi로 변환
     */
    private function convertMysqlFunctions(string $content, string $filePath): string {
        $changes = [];
        $conversions = 0;

        // MySQL 연결 함수
        if (preg_match('/mysql_connect\s*\(/', $content)) {
            $content = preg_replace('/mysql_connect\s*\(/', 'mysqli_connect(', $content);
            $changes[] = "mysql_connect → mysqli_connect";
            $conversions++;
        }

        // MySQL 선택 데이터베이스
        if (preg_match('/mysql_select_db\s*\(/', $content)) {
            // mysqli_select_db($link, $db) → mysqli_select_db($link, $db) 순서 변경 필요
            $content = preg_replace_callback(
                '/mysql_select_db\s*\(\s*([^,]+),\s*([^)]+)\)/',
                function($matches) {
                    return "mysqli_select_db({$matches[2]}, {$matches[1]})";
                },
                $content
            );
            $changes[] = "mysql_select_db → mysqli_select_db (파라미터 순서 수정)";
            $conversions++;
        }

        // MySQL 쿼리 실행
        if (preg_match('/mysql_query\s*\(/', $content)) {
            // mysqli_query($link, $query) → mysqli_query($link, $query) 순서 변경
            $content = preg_replace_callback(
                '/mysql_query\s*\(\s*([^,]+)(?:,\s*([^)]+))?\)/',
                function($matches) {
                    $query = $matches[1];
                    $link = isset($matches[2]) ? $matches[2] : '$db';
                    return "mysqli_query($link, $query)";
                },
                $content
            );
            $changes[] = "mysql_query → mysqli_query (파라미터 순서 수정)";
            $conversions++;
        }

        // MySQL 결과 처리 함수들
        $mysqlFunctions = [
            'mysql_fetch_array' => 'mysqli_fetch_array',
            'mysql_fetch_assoc' => 'mysqli_fetch_assoc',
            'mysql_fetch_row' => 'mysqli_fetch_row',
            'mysql_num_rows' => 'mysqli_num_rows',
            'mysql_affected_rows' => 'mysqli_affected_rows',
            'mysql_insert_id' => 'mysqli_insert_id',
            'mysql_error' => 'mysqli_error',
            'mysql_errno' => 'mysqli_errno',
            'mysql_close' => 'mysqli_close',
            'mysql_real_escape_string' => 'mysqli_real_escape_string'
        ];

        foreach ($mysqlFunctions as $oldFunc => $newFunc) {
            if (preg_match("/{$oldFunc}\s*\(/", $content)) {
                $content = preg_replace("/{$oldFunc}\s*\(/", "{$newFunc}(", $content);
                $changes[] = "$oldFunc → $newFunc";
                $conversions++;
            }
        }

        if (!empty($changes)) {
            $this->writeLog("🔧 MySQL 함수 변환 ($filePath): " . implode(', ', $changes));
            $this->stats['mysql_conversions'] += $conversions;
        }

        return $content;
    }

    /**
     * EREG 함수를 PREG로 변환
     */
    private function convertEregFunctions(string $content, string $filePath): string {
        $changes = [];
        $conversions = 0;

        // preg_match('/^' . ) → preg_match()
        if (preg_match('/ereg\s*\(/' . '$/', $content)) {
            $content = preg_replace_callback(
                '/ereg\s*\(\s*([^,]+),\s*([^)]+)\)/',
                function($matches) {
                    $pattern = $matches[1];
                    $subject = $matches[2];
                    // 패턴에 구분자 추가
                    if (!preg_match('/^[\/~#]/', $pattern)) {
                        $pattern = "'/^' . $pattern . '$/'" ;
                    }
                    return "preg_match($pattern, $subject)";
                },
                $content
            );
            $changes[] = "ereg → preg_match";
            $conversions++;
        }

        // preg_match('/^' . ) → preg_match() with case insensitive flag
        if (preg_match('/eregi\s*\(/' . '$/i', $content)) {
            $content = preg_replace_callback(
                '/eregi\s*\(\s*([^,]+),\s*([^)]+)\)/',
                function($matches) {
                    $pattern = $matches[1];
                    $subject = $matches[2];
                    // 대소문자 구분 없음 플래그 추가
                    if (!preg_match('/^[\/~#]/', $pattern)) {
                        $pattern = "'/^' . $pattern . '$/i'";
                    }
                    return "preg_match($pattern, $subject)";
                },
                $content
            );
            $changes[] = "eregi → preg_match (case insensitive)";
            $conversions++;
        }

        // preg_replace('/^' . ) → preg_replace()
        if (preg_match('/ereg_replace\s*\(/' . '$/', $content)) {
            $content = preg_replace_callback(
                '/ereg_replace\s*\(\s*([^, ]+),\s*([^,]+),\s*([^)]+)\)/',
                function($matches) {
                    $pattern = $matches[1];
                    $replacement = $matches[2];
                    $subject = $matches[3];
                    if (!preg_match('/^[\/~#]/', $pattern)) {
                        $pattern = "'/^' . $pattern . '$/'";
                    }
                    return "preg_replace($pattern, $replacement, $subject)";
                },
                $content
            );
            $changes[] = "ereg_replace → preg_replace";
            $conversions++;
        }

        if (!empty($changes)) {
            $this->writeLog("🔧 EREG 함수 변환 ($filePath): " . implode(', ', $changes));
            $this->stats['ereg_conversions'] += $conversions;
        }

        return $content;
    }

    /**
     * 에러 처리 개선
     */
    private function improveErrorHandling(string $content, string $filePath): string {
        $changes = [];

        // 현대적인 에러 처리 추가
        if (strpos($content, 'mysqli_query') !== false && strpos($content, 'mysqli_error') === false) {
            // mysqli_query 후 에러 처리 권장사항 주석 추가
            if (strpos($content, '// 에러 처리 권장') === false) {
                $errorComment = "\n// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요\n";
                $content = preg_replace('/(mysqli_query\s*\([^;]+;)/', '$1' . $errorComment, $content, 1);
                $changes[] = "에러 처리 권장사항 주석 추가";
            }
        }

        if (!empty($changes)) {
            $this->writeLog("🛡️  에러 처리 개선 ($filePath): " . implode(', ', $changes));
        }

        return $content;
    }

    public function scanAndMigrate(string $directory, bool $dryRun = false): array {
        $this->writeLog("🚀 MySQL/EREG 마이그레이션 시작: $directory");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // 백업 폴더는 제외
                if (strpos($file->getRealPath(), '.backup_') !== false) {
                    continue;
                }

                $this->stats['total_files']++;
                $filePath = $file->getRealPath();
                $this->migrateFile($filePath, $dryRun);
            }
        }

        $this->printStats();
        return $this->stats;
    }

    private function printStats(): void {
        $this->writeLog("=== MySQL/EREG 마이그레이션 완료 요약 ===");
        $this->writeLog("총 파일 수: {$this->stats['total_files']}");
        $this->writeLog("마이그레이션된 파일: {$this->stats['migrated_files']}");
        $this->writeLog("MySQL 함수 변환: {$this->stats['mysql_conversions']}개");
        $this->writeLog("EREG 함수 변환: {$this->stats['ereg_conversions']}개");
        $this->writeLog("변경사항 없는 파일: {$this->stats['skipped_files']}");
        $this->writeLog("에러 발생: {$this->stats['errors']}");
    }
}

// 메인 실행
$migrator = new MysqlEregMigrator($logFile);

echo "=== MySQL/EREG 함수 마이그레이션 도구 ===\n";
echo "대상 디렉토리: " . __DIR__ . "\n";
echo "처리 예정: mysql_* 함수 → mysqli_*, ereg* → preg_match\n\n";

$dryRun = isset($argv[1]) && $argv[1] === '--dry-run';

if ($dryRun) {
    echo "🔍 DRY RUN 모드: 실제 변경하지 않고 미리보기만 수행합니다.\n";
} else {
    echo "⚠️  실제 MySQL/EREG 마이그레이션을 시작합니다. 백업이 자동으로 생성됩니다.\n";
    echo "MySQL 함수 92개 파일, EREG 함수 28개 파일이 처리됩니다.\n";
    echo "계속하려면 Enter를 누르세요...";
    fgets(STDIN);
}

$result = $migrator->scanAndMigrate(__DIR__, $dryRun);

echo "\n로그 파일: $logFile\n";
if (!$dryRun) {
    echo "백업 파일들은 *.backup_before_mysql_ereg_YYYYMMDDHHMMSS 형식으로 저장되었습니다.\n";
}
?>