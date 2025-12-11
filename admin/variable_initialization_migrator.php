<?php
/**
 * 변수 초기화 및 남은 마이그레이션 이슈 해결 스크립트
 *
 * 해결 대상:
 * 1. 변수 초기화 미완료 (mode, no, search, id, name 등)
 * 2. 짧은 PHP 태그 완전 제거
 * 3. GET/POST 변수 안전한 초기화
 */

declare(strict_types=1);

// 로그 파일 설정
$logFile = __DIR__ . '/variable_init_migration_log_' . date('Y-m-d_H-i-s') . '.txt';

class VariableInitMigrator {
    private string $logFile;
    private array $stats = [
        'total_files' => 0,
        'migrated_files' => 0,
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

        // Step 1: 짧은 PHP 태그 완전 제거
        $migratedContent = $this->fixShortPhpTags($migratedContent, $filePath);

        // Step 2: 변수 초기화 적용
        $migratedContent = $this->addVariableInitialization($migratedContent, $filePath);

        // Step 3: 추가 보안 강화
        $migratedContent = $this->addSecurityEnhancements($migratedContent, $filePath);

        // 변경사항이 있는지 확인
        if ($originalContent === $migratedContent) {
            $this->writeLog("⏭️  변경사항 없음: $filePath");
            $this->stats['skipped_files']++;
            return true;
        }

        if (!$dryRun) {
            // 백업 생성
            $backupPath = $filePath . '.backup_before_varinit_' . date('YmdHis');
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
            $this->writeLog("✅ 변수 초기화 마이그레이션 완료: $filePath");
        } else {
            $this->writeLog("🔍 [DRY RUN] 변수 초기화 마이그레이션 예정: $filePath");
        }

        $this->stats['migrated_files']++;
        return true;
    }

    /**
     * 짧은 PHP 태그 완전 제거
     */
    private function fixShortPhpTags(string $content, string $filePath): string {
        $changes = [];

        // <?로 시작하는 짧은 태그 찾기 (<?php가 아닌 것들)
        if (preg_match('/^<\?(?!php)/', $content)) {
            $content = preg_replace('/^<\?(?!php)/', "<?php\ndeclare(strict_types=1);", $content);
            $changes[] = "짧은 PHP 태그를 완전한 형태로 변환";
        }

        // 중간에 있는 짧은 태그들도 처리
        if (preg_match('/<\?(?!php)\s/', $content)) {
            $content = preg_replace('/<\?(?!php)\s/', '<?php ', $content);
            $changes[] = "중간 짧은 PHP 태그 변환";
        }

        if (!empty($changes)) {
            $this->writeLog("🏷️  PHP 태그 수정 ($filePath): " . implode(', ', $changes));
        }

        return $content;
    }

    /**
     * 변수 초기화 추가
     */
    private function addVariableInitialization(string $content, string $filePath): string {
        $changes = [];

        // PHP 선언 부분 찾기
        if (preg_match('/^<\?php\s*\n?declare\(strict_types=1\);\s*\n?/', $content, $matches)) {
            $afterDeclaration = $matches[0];

            // 이미 변수 초기화가 있는지 확인
            if (!preg_match('/\$mode\s*=\s*\$_[GP]OST\[/', $content)) {

                // 변수 초기화 코드 생성
                $initCode = "\n// ✅ PHP 7.4 호환: 입력 변수 초기화\n";
                $initCode .= "\$mode = \$_GET['mode'] ?? \$_POST['mode'] ?? '';\n";
                $initCode .= "\$no = \$_GET['no'] ?? \$_POST['no'] ?? '';\n";
                $initCode .= "\$search = \$_GET['search'] ?? \$_POST['search'] ?? '';\n";
                $initCode .= "\$id = \$_GET['id'] ?? \$_POST['id'] ?? '';\n";
                $initCode .= "\$name = \$_GET['name'] ?? \$_POST['name'] ?? '';\n";
                $initCode .= "\$code = \$_GET['code'] ?? \$_POST['code'] ?? '';\n";
                $initCode .= "\$page = \$_GET['page'] ?? \$_POST['page'] ?? '';\n\n";

                // 선언문 다음에 변수 초기화 삽입
                $content = str_replace($afterDeclaration, $afterDeclaration . $initCode, $content);
                $changes[] = "주요 변수 초기화 코드 추가";
            }
        } else {
            // PHP 파일이지만 declare가 없는 경우 - 기본적인 초기화만 추가
            if (strpos($content, '<?php') !== false && !preg_match('/\$mode\s*=\s*\$_[GP]OST\[/', $content)) {
                // authenticate() 함수 전에 변수 초기화 추가
                if (preg_match('/(function\s+authenticate\(\))/i', $content)) {
                    $initCode = "// ✅ 입력 변수 초기화\n";
                    $initCode .= "\$mode = \$_GET['mode'] ?? \$_POST['mode'] ?? '';\n";
                    $initCode .= "\$no = \$_GET['no'] ?? \$_POST['no'] ?? '';\n";
                    $initCode .= "\$search = \$_GET['search'] ?? \$_POST['search'] ?? '';\n\n";

                    $content = preg_replace('/(function\s+authenticate\(\))/i', $initCode . '$1', $content);
                    $changes[] = "기본 변수 초기화 코드 추가";
                }
            }
        }

        if (!empty($changes)) {
            $this->writeLog("🔧 변수 초기화 ($filePath): " . implode(', ', $changes));
        }

        return $content;
    }

    /**
     * 추가 보안 강화
     */
    private function addSecurityEnhancements(string $content, string $filePath): string {
        $changes = [];

        // XSS 보호가 필요한 echo 구문 찾기
        if (preg_match('/echo.*\$\w+(?!\s*\?)/', $content) && !strpos($content, 'htmlspecialchars')) {
            // 간단한 XSS 보호 추가 (기존 echo 구문은 유지하되 주석으로 권장사항 추가)
            if (strpos($content, '// XSS 보호 권장') === false) {
                $xssComment = "\n// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요\n";
                $content = preg_replace('/(<\?php\s*\n?declare\(strict_types=1\);\s*\n?)/', '$1' . $xssComment, $content);
                $changes[] = "XSS 보호 권장사항 주석 추가";
            }
        }

        if (!empty($changes)) {
            $this->writeLog("🛡️  보안 강화 ($filePath): " . implode(', ', $changes));
        }

        return $content;
    }

    public function scanAndMigrate(string $directory, bool $dryRun = false): array {
        $this->writeLog("🚀 변수 초기화 마이그레이션 시작: $directory");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // 백업 폴더는 제외
                if (strpos($file->getRealPath(), 'PHP52_BACKUP_') !== false) {
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
        $this->writeLog("=== 변수 초기화 마이그레이션 완료 요약 ===");
        $this->writeLog("총 파일 수: {$this->stats['total_files']}");
        $this->writeLog("마이그레이션된 파일: {$this->stats['migrated_files']}");
        $this->writeLog("변경사항 없는 파일: {$this->stats['skipped_files']}");
        $this->writeLog("에러 발생: {$this->stats['errors']}");
    }
}

// 메인 실행
$migrator = new VariableInitMigrator($logFile);

echo "=== 변수 초기화 및 추가 마이그레이션 도구 ===\n";
echo "대상 디렉토리: " . __DIR__ . "\n";

$dryRun = isset($argv[1]) && $argv[1] === '--dry-run';

if ($dryRun) {
    echo "🔍 DRY RUN 모드: 실제 변경하지 않고 미리보기만 수행합니다.\n";
} else {
    echo "⚠️  실제 변수 초기화 마이그레이션을 시작합니다. 백업이 자동으로 생성됩니다.\n";
    echo "계속하려면 Enter를 누르세요...";
    fgets(STDIN);
}

$result = $migrator->scanAndMigrate(__DIR__, $dryRun);

echo "\n로그 파일: $logFile\n";
if (!$dryRun) {
    echo "백업 파일들은 *.backup_before_varinit_YYYYMMDDHHMMSS 형식으로 저장되었습니다.\n";
}
?>