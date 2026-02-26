<?php
/**
 * 문서 분할 스크립트
 *
 * AGENTS.md의 대형 섹션을 별도 파일로 분리합니다.
 *
 * 사용법:
 * php scripts/split_documents.php
 */

class DocumentSplitter {
    private string $sourceFile;
    private string $outputDir;
    private array $targetSections = [
        '배송 추정 시스템' => 'shipping.md',
        '이메일 시스템' => 'email_system.md',
        'AI 챗봇' => 'ai_chatbot.md',
        '데이터 마이그레이션' => 'data_migration.md',
        '영문 버전' => 'english_version.md',
    ];

    public function __construct() {
        $this->sourceFile = __DIR__ . '/../AGENTS.md';
        $this->outputDir = __DIR__ . '/../docs/';  // 분할된 문서 저장 경로
    }

    /**
     * 메인 실행 함수
     */
    public function split(): void {
        echo "📄 문서 분할 시작...\n\n";

        // 1. 출력 디렉토리 생성
        $this->createOutputDirectory();

        // 2. 대상 섹션 추출 및 분리
        $this->extractSections();

        // 3. 새 AGENTS.md 인덱스 생성
        $this->generateNewIndex();

        // 4. 결과 보고서 출력
        $this->printSummary();
    }

    /**
     * 출력 디렉토리 생성
     */
    private function createOutputDirectory(): void {
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
            echo "📁 생성된 디렉토리: {$this->outputDir}\n";
        } else {
            echo "📁 기존 디렉토리: {$this->outputDir}\n";
        }
    }

    /**
     * 대상 섹션 추출 및 분리
     */
    private function extractSections(): void {
        echo "\n✂️ 섹션 분리 작업:\n";

        $content = file_get_contents($this->sourceFile);
        $lines = explode("\n", $content);

        // 섹션 감지 및 분리
        foreach ($this->targetSections as $sectionName => $targetFile) {
            $sectionData = $this->findSection($sectionName, $lines);

            if ($sectionData !== null) {
                $outputFile = $this->outputDir . $targetFile;
                $this->writeSectionFile($outputFile, $sectionData);

                printf("  ✅ %s → %s (%d라선)\n",
                    $sectionName,
                    $targetFile,
                    $sectionData['length']
                );
            } else {
                printf("  ⚠️  %s: 섹션을 찾을 수 없습니다\n", $sectionName);
            }
        }
    }

    /**
     * 섹션 찾기
     */
    private function findSection(string $sectionName, array $lines): ?array {
        $inSection = false;
        $startLine = null;
        $sectionContent = [];

        foreach ($lines as $index => $line) {
            if (strpos($line, '## ' . $sectionName) === 0) {
                $inSection = true;
                $startLine = $index;
                continue;
            }

            if ($inSection) {
                // 다음 섹션 감지
                if (strpos($line, '## ') === 0 && $index > $startLine) {
                    break;
                }
                $sectionContent[] = $line;
            }
        }

        if ($inSection && !empty($sectionContent)) {
            return [
                'start' => $startLine + 1,  // 1-based
                'end' => count($lines),
                'length' => count($sectionContent),
                'content' => implode("\n", $sectionContent)
            ];
        }

        return null;
    }

    /**
     * 섹션 파일 작성
     */
    private function writeSectionFile(string $outputFile, array $data): void {
        // 파일명 설정
        $fileName = basename($outputFile);

        // 헤더 작성
        $header = "# {$fileName}\n\n";
        $header .= "이 섹션은 AGENTS.md에서 분리된 상세 정보입니다.\n\n";
        $header .= "---\n\n";

        // 본문 작성
        $body = $data['content'];

        // 파일에 쓰기
        file_put_contents($outputFile, $header . $body);
    }

    /**
     * 새 AGENTS.md 인덱스 생성
     */
    private function generateNewIndex(): void {
        echo "\n📝 새 AGENTS.md 인덱스 생성...\n";

        $indexContent = "# Duson Planning Print System - AI 개발 가이드\n\n";
        $indexContent .= "## 📋 프로젝트 개요\n";
        $indexContent .= "@./README.md\n\n";

        $indexContent .= "## 🏗️ 핵심 기능\n\n";
        $indexContent .= "- 결제 시스템: @./docs/features/payment.md\n";
        $indexContent .= "- 배송 추정: @./docs/features/shipping.md\n";
        $indexContent .= "- 이메일 시스템: @./docs/features/email.md\n";
        $indexContent .= "- AI 챗봇: @./docs/features/ai-chatbot.md\n";
        $indexContent .= "- 인증 시스템: @./docs/features/auth.md\n\n";

        $indexContent .= "## 🛠️ 개발 가이드\n\n";
        $indexContent .= "- 코딩 표준: @./CLAUDE.md\n";
        $indexContent .= "- 환경 설정: @./README.md\n";
        $indexContent .= "- 테스트: @./README.md\n\n";

        $indexContent .= "## 🚀 배포\n\n";
        $indexContent .= "- 프로덕션: @./DEPLOYMENT.md\n\n";

        $indexContent .= "## 🔗 참조 문서\n\n";
        $indexContent .= "- 데이터베이스: @./docs/components/database.md\n";
        $indexContent .= "- API: @./docs/components/api.md\n\n";

        $indexContent .= "---\n";
        $indexContent .= "마지막 업데이트: 2026-02-26\n";

        // 원본 파일 백업
        $backupFile = $this->sourceFile . '.backup.' . date('YmdHis');
        copy($this->sourceFile, $backupFile);
        echo "  💾 백업 파일: {$backupFile}\n";

        // 새 인덱스 작성
        file_put_contents($this->sourceFile, $indexContent);
        echo "  ✅ 새 AGENTS.md 생성 (라인 수 감소)\n";
    }

    /**
     * 결과 요약
     */
    private function printSummary(): void {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "📊 분할 결과 요약\n";
        echo str_repeat('=', 80) . "\n\n";

        printf("📁 저장된 문서: %d개\n", count($this->targetSections));
        printf("📁 저장 위치: %s\n\n", $this->outputDir);

        printf("📋 상세 내용:\n");
        foreach ($this->targetSections as $name => $file) {
            $path = $this->outputDir . $file;
            if (file_exists($path)) {
                $lines = count(explode("\n", file_get_contents($path)));
                printf("  • %s: %d라선\n", $name, $lines);
            }
        }

        echo "\n💡 주의사항:\n";
        echo "  1. 원본 AGENTS.md는 백업 파일로 저장됨 (AGENTS.md.backup.YYYYMMDDHHmmss)\n";
        echo "  2. Claude Code에서 '@파일경로' 문법으로 참조 가능\n";
        echo "  3. 필요 시 원본으로 복구: cp AGENTS.md.backup.YYYYMMDDHHmmss AGENTS.md\n";
        echo "\n" . str_repeat('=', 80) . "\n";
    }
}

// 실행
$splitter = new DocumentSplitter();
$splitter->split();
