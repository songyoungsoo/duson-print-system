#!/usr/bin/env php
<?php
/**
 * Curator — 문서 건강검진 CLI 도구
 *
 * 두손기획인쇄 프로젝트의 문서 상태를 자동으로 점검합니다.
 *
 * 기능:
 *   1. AGENTS.md 참조 검증 (@./ 링크, 테이블 파일 경로)
 *   2. 고아 문서 탐지 (어디서도 참조되지 않는 docs/ 파일)
 *   3. 문서 크기 모니터링 (AGENTS.md < 400줄, 개별 문서 < 300줄)
 *   4. 신선도 체크 (코드 변경 후 문서 미갱신 감지)
 *   5. CLAUDE_DOCS/ 감사 (오래된 참조 문서 탐지)
 *
 * 사용법:
 *   php scripts/curator.php              기본 리포트
 *   php scripts/curator.php --verbose    상세 출력
 *   php scripts/curator.php --json       JSON 출력
 *   php scripts/curator.php --summary    요약만 출력
 */

// ── 색상 코드 ──────────────────────────────────────────────────
define('C_RESET',   "\033[0m");
define('C_BOLD',    "\033[1m");
define('C_DIM',     "\033[2m");
define('C_RED',     "\033[31m");
define('C_GREEN',   "\033[32m");
define('C_YELLOW',  "\033[33m");
define('C_BLUE',    "\033[34m");
define('C_MAGENTA', "\033[35m");
define('C_CYAN',    "\033[36m");
define('C_WHITE',   "\033[37m");

// ── 아이콘 ──────────────────────────────────────────────────────
define('ICON_OK',    C_GREEN  . '✓' . C_RESET);
define('ICON_WARN',  C_YELLOW . '⚠' . C_RESET);
define('ICON_ERROR', C_RED    . '✗' . C_RESET);
define('ICON_INFO',  C_BLUE   . 'ℹ' . C_RESET);

// ── 메인 ────────────────────────────────────────────────────────
$projectRoot = realpath(__DIR__ . '/..');
if (!$projectRoot) {
    fwrite(STDERR, "Error: Cannot determine project root.\n");
    exit(1);
}

$verbose  = in_array('--verbose', $argv);
$jsonMode = in_array('--json', $argv);
$summary  = in_array('--summary', $argv);

$config = loadConfig($projectRoot);
$report = runAllChecks($projectRoot, $config, $verbose);

if ($jsonMode) {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    printReport($report, $verbose, $summary);
}

exit($report['score'] >= 80 ? 0 : 1);

// ═══════════════════════════════════════════════════════════════
// Functions
// ═══════════════════════════════════════════════════════════════

function loadConfig(string $root): array {
    $configPath = $root . '/docs/curator-config.json';
    if (!file_exists($configPath)) {
        fwrite(STDERR, "Warning: curator-config.json not found, using defaults.\n");
        return [
            'thresholds' => [
                'agents_max_lines' => 400,
                'doc_max_lines' => 300,
                'freshness_warn_days' => 30,
                'freshness_critical_days' => 90,
            ],
            'hub_file' => 'AGENTS.md',
            'doc_directories' => ['docs/', 'CLAUDE_DOCS/'],
            'ignore_paths' => ['docs/archive/', 'docs/plans/'],
            'code_doc_mappings' => [],
            'critical_files' => ['AGENTS.md', 'README.md'],
        ];
    }
    $json = json_decode(file_get_contents($configPath), true);
    if (!$json) {
        fwrite(STDERR, "Error: Failed to parse curator-config.json\n");
        exit(1);
    }
    return $json;
}

function runAllChecks(string $root, array $config, bool $verbose): array {
    $report = [
        'timestamp' => date('Y-m-d H:i:s'),
        'checks'    => [],
        'errors'    => 0,
        'warnings'  => 0,
        'passed'    => 0,
        'score'     => 100,
    ];

    // 1. AGENTS.md 참조 검증
    $check1 = checkAgentsReferences($root, $config);
    $report['checks']['references'] = $check1;

    // 2. 고아 문서 탐지
    $check2 = checkOrphanedDocs($root, $config);
    $report['checks']['orphans'] = $check2;

    // 3. 문서 크기 모니터링
    $check3 = checkDocSizes($root, $config);
    $report['checks']['sizes'] = $check3;

    // 4. 신선도 체크 (코드 vs 문서)
    $check4 = checkFreshness($root, $config);
    $report['checks']['freshness'] = $check4;

    // 5. CLAUDE_DOCS 감사
    $check5 = checkClaudeDocs($root, $config);
    $report['checks']['claude_docs'] = $check5;

    // 점수 계산
    foreach ($report['checks'] as $check) {
        $report['errors']   += $check['errors'];
        $report['warnings'] += $check['warnings'];
        $report['passed']   += $check['passed'];
    }

    // 점수: 에러 -10점, 경고 -3점
    $report['score'] = max(0, 100 - ($report['errors'] * 10) - ($report['warnings'] * 3));

    return $report;
}

// ── Check 1: AGENTS.md 참조 검증 ───────────────────────────────
function checkAgentsReferences(string $root, array $config): array {
    $result = ['name' => 'AGENTS.md 참조 검증', 'items' => [], 'errors' => 0, 'warnings' => 0, 'passed' => 0];
    $hubFile = $root . '/' . $config['hub_file'];

    if (!file_exists($hubFile)) {
        $result['items'][] = ['level' => 'error', 'msg' => $config['hub_file'] . ' 파일이 없습니다'];
        $result['errors']++;
        return $result;
    }

    $content = file_get_contents($hubFile);
    $lines = explode("\n", $content);

    // @./ 참조 추출
    preg_match_all('/@\.\/([\w\/\.\-]+\.(?:md|php|txt))/', $content, $matches);
    $atRefs = array_unique($matches[1]);

    foreach ($atRefs as $ref) {
        $fullPath = $root . '/' . $ref;
        if (file_exists($fullPath)) {
            $result['items'][] = ['level' => 'pass', 'msg' => "@./{$ref} → 존재함"];
            $result['passed']++;
        } else {
            $result['items'][] = ['level' => 'error', 'msg' => "@./{$ref} → " . C_RED . "파일 없음!" . C_RESET];
            $result['errors']++;
        }
    }

    // 테이블 내 백틱 파일 경로 추출 (docs/, CLAUDE_DOCS/ 패턴)
    preg_match_all('/`((?:docs|CLAUDE_DOCS)\/[\w\/\.\-]+\.md)`/', $content, $tableMatches);
    $tableRefs = array_unique($tableMatches[1]);

    foreach ($tableRefs as $ref) {
        $fullPath = $root . '/' . $ref;
        if (file_exists($fullPath)) {
            $result['items'][] = ['level' => 'pass', 'msg' => "테이블 참조 {$ref} → 존재함"];
            $result['passed']++;
        } else {
            $result['items'][] = ['level' => 'error', 'msg' => "테이블 참조 {$ref} → " . C_RED . "파일 없음!" . C_RESET];
            $result['errors']++;
        }
    }

    return $result;
}

// ── Check 2: 고아 문서 탐지 ─────────────────────────────────────
function checkOrphanedDocs(string $root, array $config): array {
    $result = ['name' => '고아 문서 탐지', 'items' => [], 'errors' => 0, 'warnings' => 0, 'passed' => 0];

    // AGENTS.md 전체 텍스트에서 참조된 파일 수집
    $hubContent = file_get_contents($root . '/' . $config['hub_file']);

    // docs/ 디렉토리의 모든 .md 파일 수집
    $allDocs = [];
    foreach (['docs/'] as $dir) {
        $fullDir = $root . '/' . $dir;
        if (!is_dir($fullDir)) continue;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'md') {
                $relPath = str_replace($root . '/', '', $file->getPathname());
                $allDocs[] = $relPath;
            }
        }
    }

    // 무시 경로 필터링
    $ignorePaths = $config['ignore_paths'] ?? [];

    foreach ($allDocs as $doc) {
        // 무시 경로 체크
        $ignored = false;
        foreach ($ignorePaths as $ignorePath) {
            if (strpos($doc, $ignorePath) === 0) {
                $ignored = true;
                break;
            }
        }
        if ($ignored) continue;

        // AGENTS.md에서 참조되는지 확인
        if (strpos($hubContent, $doc) !== false) {
            $result['items'][] = ['level' => 'pass', 'msg' => "{$doc} → AGENTS.md에서 참조됨"];
            $result['passed']++;
        } else {
            $result['items'][] = ['level' => 'warn', 'msg' => "{$doc} → " . C_YELLOW . "AGENTS.md에서 참조되지 않음" . C_RESET];
            $result['warnings']++;
        }
    }

    return $result;
}

// ── Check 3: 문서 크기 모니터링 ─────────────────────────────────
function checkDocSizes(string $root, array $config): array {
    $result = ['name' => '문서 크기 모니터링', 'items' => [], 'errors' => 0, 'warnings' => 0, 'passed' => 0];
    $thresholds = $config['thresholds'];

    // AGENTS.md 크기 체크
    $hubFile = $root . '/' . $config['hub_file'];
    if (file_exists($hubFile)) {
        $lineCount = count(file($hubFile));
        $max = $thresholds['agents_max_lines'];
        if ($lineCount > $max) {
            $result['items'][] = [
                'level' => 'error',
                'msg' => sprintf("AGENTS.md: %d줄 (한도 %d줄 초과!)", $lineCount, $max),
            ];
            $result['errors']++;
        } elseif ($lineCount > $max * 0.9) {
            $result['items'][] = [
                'level' => 'warn',
                'msg' => sprintf("AGENTS.md: %d줄 (한도 %d줄의 90%% 근접)", $lineCount, $max),
            ];
            $result['warnings']++;
        } else {
            $result['items'][] = [
                'level' => 'pass',
                'msg' => sprintf("AGENTS.md: %d줄 (한도 %d줄)", $lineCount, $max),
            ];
            $result['passed']++;
        }
    }

    // 개별 문서 크기 체크
    $docMax = $thresholds['doc_max_lines'];
    $docDirs = ['docs/features/', 'docs/components/', 'docs/operations/'];

    foreach ($docDirs as $dir) {
        $fullDir = $root . '/' . $dir;
        if (!is_dir($fullDir)) continue;
        foreach (glob($fullDir . '*.md') as $file) {
            $relPath = str_replace($root . '/', '', $file);
            $lineCount = count(file($file));
            if ($lineCount > $docMax) {
                $result['items'][] = [
                    'level' => 'warn',
                    'msg' => sprintf("%s: %d줄 (한도 %d줄 초과)", $relPath, $lineCount, $docMax),
                ];
                $result['warnings']++;
            } else {
                $result['items'][] = [
                    'level' => 'pass',
                    'msg' => sprintf("%s: %d줄", $relPath, $lineCount),
                ];
                $result['passed']++;
            }
        }
    }

    return $result;
}

// ── Check 4: 신선도 체크 (코드 vs 문서) ─────────────────────────
function checkFreshness(string $root, array $config): array {
    $result = ['name' => '신선도 체크 (코드↔문서)', 'items' => [], 'errors' => 0, 'warnings' => 0, 'passed' => 0];
    $mappings = $config['code_doc_mappings'] ?? [];
    $warnDays = $config['thresholds']['freshness_warn_days'];
    $critDays = $config['thresholds']['freshness_critical_days'];

    foreach ($mappings as $mapping) {
        $name = $mapping['name'];
        $docPath = $mapping['doc'];
        $codePaths = $mapping['code_paths'];

        // 문서 최종 수정일
        $docDate = getGitLastModified($root, $docPath);
        if (!$docDate) {
            $result['items'][] = ['level' => 'warn', 'msg' => "{$name}: 문서 {$docPath}의 git 이력 없음"];
            $result['warnings']++;
            continue;
        }

        // 코드 최종 수정일 (모든 코드 경로 중 가장 최근)
        $latestCodeDate = null;
        $latestCodePath = '';
        foreach ($codePaths as $codePath) {
            $codeDate = getGitLastModified($root, $codePath);
            if ($codeDate && (!$latestCodeDate || $codeDate > $latestCodeDate)) {
                $latestCodeDate = $codeDate;
                $latestCodePath = $codePath;
            }
        }

        if (!$latestCodeDate) {
            $result['items'][] = ['level' => 'pass', 'msg' => "{$name}: 코드 경로에 git 이력 없음 (신규 또는 미추적)"];
            $result['passed']++;
            continue;
        }

        // 코드가 문서보다 최근인지 확인
        $diffDays = (int)(($latestCodeDate->getTimestamp() - $docDate->getTimestamp()) / 86400);

        if ($diffDays <= 0) {
            $result['items'][] = [
                'level' => 'pass',
                'msg' => sprintf("%s: 문서가 최신 (문서: %s, 코드: %s)",
                    $name, $docDate->format('m-d'), $latestCodeDate->format('m-d')),
            ];
            $result['passed']++;
        } elseif ($diffDays >= $critDays) {
            $result['items'][] = [
                'level' => 'error',
                'msg' => sprintf("%s: 코드가 %d일 더 최신! (코드: %s ← %s, 문서: %s)",
                    $name, $diffDays, $latestCodeDate->format('m-d'), $latestCodePath, $docDate->format('m-d')),
            ];
            $result['errors']++;
        } elseif ($diffDays >= $warnDays) {
            $result['items'][] = [
                'level' => 'warn',
                'msg' => sprintf("%s: 코드가 %d일 더 최신 (코드: %s, 문서: %s)",
                    $name, $diffDays, $latestCodeDate->format('m-d'), $docDate->format('m-d')),
            ];
            $result['warnings']++;
        } else {
            $result['items'][] = [
                'level' => 'pass',
                'msg' => sprintf("%s: 코드가 %d일 최신 (허용 범위 내)",
                    $name, $diffDays),
            ];
            $result['passed']++;
        }
    }

    return $result;
}

// ── Check 5: CLAUDE_DOCS 감사 ───────────────────────────────────
function checkClaudeDocs(string $root, array $config): array {
    $result = ['name' => 'CLAUDE_DOCS/ 감사', 'items' => [], 'errors' => 0, 'warnings' => 0, 'passed' => 0];
    $claudeDocsDir = $root . '/CLAUDE_DOCS/';
    $critDays = $config['thresholds']['freshness_critical_days'];

    if (!is_dir($claudeDocsDir)) {
        $result['items'][] = ['level' => 'warn', 'msg' => 'CLAUDE_DOCS/ 디렉토리 없음'];
        $result['warnings']++;
        return $result;
    }

    $ignorePaths = $config['ignore_paths'] ?? [];
    $now = new DateTime();

    foreach (glob($claudeDocsDir . '*.md') as $file) {
        $relPath = str_replace($root . '/', '', $file);

        // 무시 경로 체크
        $ignored = false;
        foreach ($ignorePaths as $ignorePath) {
            if (strpos($relPath, $ignorePath) === 0) {
                $ignored = true;
                break;
            }
        }
        if ($ignored) continue;

        $lastMod = getGitLastModified($root, $relPath);
        if (!$lastMod) {
            $result['items'][] = ['level' => 'warn', 'msg' => "{$relPath}: git 이력 없음"];
            $result['warnings']++;
            continue;
        }

        $ageDays = (int)(($now->getTimestamp() - $lastMod->getTimestamp()) / 86400);

        if ($ageDays > $critDays) {
            $result['items'][] = [
                'level' => 'warn',
                'msg' => sprintf("%s: %d일 전 수정 (최종: %s) — 검토 필요",
                    $relPath, $ageDays, $lastMod->format('Y-m-d')),
            ];
            $result['warnings']++;
        } else {
            $result['items'][] = [
                'level' => 'pass',
                'msg' => sprintf("%s: %d일 전 수정 (%s)", $relPath, $ageDays, $lastMod->format('Y-m-d')),
            ];
            $result['passed']++;
        }
    }

    return $result;
}

// ── Helper: Git 최종 수정일 조회 ─────────────────────────────────
function getGitLastModified(string $root, string $path): ?DateTime {
    $fullPath = $root . '/' . $path;

    // 디렉토리인 경우 해당 디렉토리 아래 전체 기준
    $cmd = sprintf(
        'cd %s && git log -1 --format=%%aI -- %s 2>/dev/null',
        escapeshellarg($root),
        escapeshellarg($path)
    );

    $output = trim(shell_exec($cmd) ?? '');
    if (empty($output)) {
        return null;
    }

    try {
        return new DateTime($output);
    } catch (Exception $e) {
        return null;
    }
}

// ── 리포트 출력 ─────────────────────────────────────────────────
function printReport(array $report, bool $verbose, bool $summaryOnly): void {
    echo "\n";
    echo C_BOLD . C_CYAN . "╔══════════════════════════════════════════════════╗" . C_RESET . "\n";
    echo C_BOLD . C_CYAN . "║     📋 Curator — 문서 건강검진 리포트            ║" . C_RESET . "\n";
    echo C_BOLD . C_CYAN . "╚══════════════════════════════════════════════════╝" . C_RESET . "\n";
    echo C_DIM . "  실행 시각: " . $report['timestamp'] . C_RESET . "\n\n";

    if (!$summaryOnly) {
        foreach ($report['checks'] as $checkKey => $check) {
            $icon = ($check['errors'] > 0) ? ICON_ERROR :
                   (($check['warnings'] > 0) ? ICON_WARN : ICON_OK);

            echo C_BOLD . "  {$icon} {$check['name']}" . C_RESET;
            echo C_DIM . sprintf(" (✓%d ⚠%d ✗%d)", $check['passed'], $check['warnings'], $check['errors']) . C_RESET . "\n";

            if ($verbose || $check['errors'] > 0 || $check['warnings'] > 0) {
                foreach ($check['items'] as $item) {
                    if (!$verbose && $item['level'] === 'pass') continue;

                    $prefixMap = [
                        'error' => "    " . ICON_ERROR . " ",
                        'warn'  => "    " . ICON_WARN  . " ",
                        'pass'  => "    " . ICON_OK    . " ",
                    ];
                    $prefix = isset($prefixMap[$item['level']]) ? $prefixMap[$item['level']] : "    " . ICON_INFO . " ";
                    echo $prefix . $item['msg'] . "\n";
                }
            }
            echo "\n";
        }
    }

    // 점수 표시
    $score = $report['score'];
    $scoreColor = $score >= 80 ? C_GREEN : ($score >= 60 ? C_YELLOW : C_RED);
    $grade = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 60 ? 'C' : 'D'));

    echo C_BOLD . "  ─────────────────────────────────────────────" . C_RESET . "\n";
    echo sprintf("  점수: %s%s%d/100 (등급: %s)%s\n",
        C_BOLD, $scoreColor, $score, $grade, C_RESET);
    echo sprintf("  통과: %s%d%s  경고: %s%d%s  오류: %s%d%s\n",
        C_GREEN, $report['passed'], C_RESET,
        C_YELLOW, $report['warnings'], C_RESET,
        C_RED, $report['errors'], C_RESET);

    // 액션 아이템
    $actionItems = [];
    foreach ($report['checks'] as $check) {
        foreach ($check['items'] as $item) {
            if ($item['level'] === 'error') {
                $actionItems[] = $item['msg'];
            }
        }
    }

    if (!empty($actionItems)) {
        echo "\n" . C_BOLD . C_RED . "  🔧 즉시 조치 필요:" . C_RESET . "\n";
        foreach ($actionItems as $i => $action) {
            echo "    " . ($i + 1) . ". {$action}\n";
        }
    }

    echo "\n";
}
