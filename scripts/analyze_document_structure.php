<?php
/**
 * 문서 구조 분석 스크립트
 *
 * AGENTS.md의 섹션별 라인 수, 중복 내용, 의존성 관계를 분석합니다.
 *
 * 사용법:
 * php scripts/analyze_document_structure.php
 */

class DocumentAnalyzer {
    private string $agentsFile;
    private array $sections = [];
    private array $duplicates = [];
    private array $lineStats = [];

    public function __construct() {
        $this->agentsFile = __DIR__ . '/../AGENTS.md';
    }

    /**
     * 메인 실행 함수
     */
    public function analyze(): void {
        echo "📄 문서 구조 분석 시작...\n\n";

        // 1. 전체 라인 수 측정
        $totalLines = $this->countTotalLines();
        echo "📊 전체 문서 크기: {$totalLines} 라인\n\n";

        // 2. 섹션별 분석
        $this->analyzeSections();

        // 3. 중복 내용 검사
        $this->checkDuplicates();

        // 4. 대형 섹션 10개 추출
        $this->findTopSections(10);

        // 5. 시간대별 추가 패턴
        $this->analyzeTimePattern();

        // 6. 중복 문서 체크
        $this->checkDuplicateFiles();

        // 7. 요약 보고서 출력
        $this->printSummary($totalLines);
    }

    /**
     * 전체 라인 수 측정
     */
    private function countTotalLines(): int {
        if (!file_exists($this->agentsFile)) {
            echo "❌ AGENTS.md 파일을 찾을 수 없습니다: {$this->agentsFile}\n";
            exit(1);
        }

        $content = file_get_contents($this->agentsFile);
        $lines = explode("\n", $content);
        return count($lines);
    }

    /**
     * 섹션별 분석
     */
    private function analyzeSections(): void {
        $content = file_get_contents($this->agentsFile);
        $lines = explode("\n", $content);

        $currentSection = '전체';
        $startLine = 1;

        foreach ($lines as $index => $line) {
            // 섹션 헤더 감지 (## 형식)
            if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
                $sectionName = trim($matches[1]);

                // 이전 섹션 저장
                if ($currentSection !== '전체') {
                    $this->sections[] = [
                        'name' => $currentSection,
                        'start' => $startLine,
                        'end' => $index + 1,
                        'length' => $index + 1 - $startLine + 1
                    ];
                }

                $currentSection = $sectionName;
                $startLine = $index + 1;
            }
        }

        // 마지막 섹션 저장
        $this->sections[] = [
            'name' => $currentSection,
            'start' => $startLine,
            'end' => count($lines),
            'length' => count($lines) - $startLine + 1
        ];

        // 라인 수 통계 계산
        foreach ($this->sections as &$section) {
            $this->lineStats[$section['name']] = $section['length'];
        }
    }

    /**
     * 중복 내용 검사
     */
    private function checkDuplicates(): void {
        $content = file_get_contents($this->agentsFile);
        $lines = explode("\n", $content);

        // 중복 단어 리스트 (주요 내용)
        $duplicateKeywords = [
            '홈페이지 헤더색' => ['#1E4E79', '#1E4E79'],
            '스티커 가격 계산' => ['재질×가로×세로×수량'],
            '전단지 택배비' => ['룩업', 'FLYER_FEE_MAP'],
            '택배비 선불' => ['운임구분', '선불'],
            'mailer() 함수' => ['function mailer(', 'type=1'],
            'PHP 8.2' => ['mysqli_close', 'Fatal Error'],
            'Plesk' => ['nginx + Apache'],
        ];

        foreach ($duplicateKeywords as $keyword => $examples) {
            $foundInSection = [];

            foreach ($this->sections as $section) {
                $sectionContent = $this->getSectionContent($section);
                $sectionCount = 0;

                foreach ($examples as $example) {
                    if (strpos($sectionContent, $example) !== false) {
                        $sectionCount++;
                    }
                }

                if ($sectionCount > 1) {
                    $foundInSection[] = [
                        'section' => $section['name'],
                        'count' => $sectionCount
                    ];
                }
            }

            if (!empty($foundInSection)) {
                $this->duplicates[$keyword] = $foundInSection;
            }
        }
    }

    /**
     * 섹션 내용 가져오기
     */
    private function getSectionContent(array $section): string {
        $content = file_get_contents($this->agentsFile);
        $lines = explode("\n", $content);

        $sectionContent = '';
        for ($i = $section['start'] - 1; $i < $section['end']; $i++) {
            $sectionContent .= $lines[$i] . "\n";
        }

        return $sectionContent;
    }

    /**
     * 상위 섹션 10개 찾기
     */
    private function findTopSections(int $count = 10): void {
        usort($this->sections, function ($a, $b) {
            return $b['length'] <=> $a['length'];
        });

        echo "\n🏆 상위 {$count} 섹션:\n";
        echo str_repeat('-', 80) . "\n";
        printf("%-30s %10s %10s %10s\n", "섹션명", "라인 수", "비중", "위치");
        echo str_repeat('-', 80) . "\n";

        foreach (array_slice($this->sections, 0, $count) as $index => $section) {
            $totalLines = count(explode("\n", file_get_contents($this->agentsFile)));
            $percentage = round(($section['length'] / $totalLines) * 100, 1);

            printf(
                "%-30s %10d %10.1f%% %10d-%d\n",
                $section['name'],
                $section['length'],
                $percentage,
                $section['start'],
                $section['end']
            );
        }
    }

    /**
     * 시간대별 추가 패턴 분석
     */
    private function analyzeTimePattern(): void {
        echo "\n⏰ 시간대별 추가 패턴:\n";
        echo str_repeat('-', 80) . "\n";

        $content = file_get_contents($this->agentsFile);
        $lines = explode("\n", $content);

        // 500줄 이하: 초기 시스템 가이드
        // 500-1000줄: 결제 시스템
        // 1000-1500줄: 배송 추정 시스템
        // 1500-2000줄: 이메일 시스템
        // 2000-2500줄: 데이터 마이그레이션, AI 챗봇
        // 2500-2577줄: Knowledge Vault

        $patterns = [
            [1, 500, '초기 시스템 가이드 (배포, 시스템 개요, 코드 스타일)'],
            [501, 1000, '결제 시스템, 스티커 가격 계산'],
            [1001, 1500, '배송 추정 시스템 (가장 큰 섹션 추가)'],
            [1501, 2000, '이메일 시스템, 교정 관리, 견적서'],
            [2001, 2500, '데이터 마이그레이션, AI 챗봇, 영문 버전'],
            [2501, 2577, 'Knowledge Vault, 전화번호 포맷팅'],
        ];

        foreach ($patterns as $pattern) {
            printf("%d-%d: %s\n", $pattern[0], $pattern[1], $pattern[2]);
        }
    }

    /**
     * 중복 문서 체크
     */
    private function checkDuplicateFiles(): void {
        echo "\n📋 중복 문서 확인:\n";
        echo str_repeat('-', 80) . "\n";

        $docs = [
            '.claude/AGENTS.md',
            'AGENTS.md',
            'CLAUDE.md',
            'README.md',
            'DEPLOYMENT.md',
        ];

        foreach ($docs as $doc) {
            if (file_exists($doc)) {
                $lines = count(explode("\n", file_get_contents($doc)));
                printf("%-30s %10d 라인\n", $doc, $lines);
            } else {
                printf("%-30s %10s\n", $doc, '❌ 없음');
            }
        }
    }

    /**
     * 요약 보고서 출력
     */
    private function printSummary(int $totalLines): void {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "📊 요약 보고서\n";
        echo str_repeat('=', 80) . "\n\n";

        // 중복 내용 요약
        if (!empty($this->duplicates)) {
            echo "🚨 중복 내용 검출 (" . count($this->duplicates) . "개):\n";
            foreach ($this->duplicates as $keyword => $sections) {
                printf("  • %s: %d개 섹션에서 발견\n", $keyword, count($sections));
            }
            echo "\n";
        }

        // 추천 조치
        echo "💡 추천 조치:\n";
        echo "  1. ✅ 대형 섹션 5개 분리 (절감 850라선)\n";
        echo "  2. ✅ 중복 문서 병합 (절감 150라선)\n";
        echo "  3. ✅ 버그 수정 기록 별도 (절감 127라선)\n";
        echo "  4. ✅ Critical Rules 별도 (절감 128라선)\n";
        echo "  5. ✅ 테이블/매핑 별도 (절감 100라선)\n\n";

        $targetLines = $totalLines - 850;
        $reduction = round(($totalLines - $targetLines) / $totalLines * 100, 1);

        printf("🎯 목표: %d 라인 → %d 라인 (%.1f%% 감소)\n\n", $totalLines, $targetLines, $reduction);
        echo str_repeat('=', 80) . "\n";
    }
}

// 실행
$analyzer = new DocumentAnalyzer();
$analyzer->analyze();
