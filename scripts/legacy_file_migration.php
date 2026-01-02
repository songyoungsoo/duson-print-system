<?php
/**
 * 레거시 파일 마이그레이션 스크립트
 * ../shop/data/ 경로의 파일들을 새로운 경로 구조로 이동
 *
 * 사용법:
 * 1. 프로덕션 서버에서 실행: php legacy_file_migration.php --scan
 * 2. 마이그레이션 실행: php legacy_file_migration.php --migrate
 * 3. DB 경로 업데이트: php legacy_file_migration.php --update-db
 */

// 데이터베이스 연결
require_once __DIR__ . '/../db.php';
$connect = $db;

// 설정
$LEGACY_PATH = '/var/www/html/shop/data/';
$NEW_BASE_PATH = '/var/www/html/mlangorder_printauto/upload/';
$DRY_RUN = true; // true면 실제 이동 없이 시뮬레이션만

// 명령어 파싱
$action = $argv[1] ?? '--help';

switch ($action) {
    case '--scan':
        scanLegacyFiles($connect, $LEGACY_PATH);
        break;
    case '--migrate':
        migrateFiles($connect, $LEGACY_PATH, $NEW_BASE_PATH, $DRY_RUN);
        break;
    case '--update-db':
        updateDatabase($connect, $DRY_RUN);
        break;
    case '--stats':
        showStats($connect);
        break;
    default:
        showHelp();
        break;
}

/**
 * 도움말 표시
 */
function showHelp() {
    echo <<<HELP
레거시 파일 마이그레이션 도구
==============================

사용법:
  php legacy_file_migration.php [옵션]

옵션:
  --scan       레거시 경로 파일 스캔 및 상태 확인
  --stats      레거시 경로 통계 표시
  --migrate    파일을 새 경로로 이동 (DRY_RUN=true 시 시뮬레이션)
  --update-db  DB의 ImgFolder/ThingCate 경로 업데이트
  --help       이 도움말 표시

주의사항:
  1. 반드시 백업 후 실행하세요
  2. 먼저 --scan으로 상태를 확인하세요
  3. DRY_RUN=false로 변경 후 실제 마이그레이션 실행

HELP;
}

/**
 * 통계 표시
 */
function showStats($connect) {
    echo "\n=== 레거시 경로 통계 ===\n\n";

    // ImgFolder 경로 유형별 통계
    $sql = "SELECT
        CASE
            WHEN ImgFolder LIKE '../shop/data%' THEN 'Legacy (../shop/data)'
            WHEN ImgFolder LIKE '_MlangPrintAuto%' THEN 'New (_MlangPrintAuto)'
            WHEN ImgFolder IS NULL OR ImgFolder = '' THEN 'NULL/Empty'
            ELSE 'Other'
        END as folder_type,
        COUNT(*) as count,
        MIN(date) as oldest,
        MAX(date) as newest
    FROM mlangorder_printauto
    GROUP BY folder_type
    ORDER BY count DESC";

    $result = mysqli_query($connect, $sql);

    echo "ImgFolder 경로 유형:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-30s %10s %20s %20s\n", "유형", "개수", "가장 오래된", "가장 최근");
    echo str_repeat("-", 80) . "\n";

    while ($row = mysqli_fetch_assoc($result)) {
        printf("%-30s %10s %20s %20s\n",
            $row['folder_type'],
            number_format($row['count']),
            substr($row['oldest'], 0, 10),
            substr($row['newest'], 0, 10)
        );
    }

    // 2024년 이후 레거시 경로 상세
    echo "\n\n=== 2024년 이후 레거시 경로 주문 ===\n";

    $sql = "SELECT COUNT(*) as cnt
            FROM mlangorder_printauto
            WHERE ImgFolder LIKE '../shop/data%'
            AND date >= '2024-01-01'";
    $result = mysqli_query($connect, $sql);
    $row = mysqli_fetch_assoc($result);

    echo "2024년 이후 레거시 경로 주문: " . number_format($row['cnt']) . "건\n";
}

/**
 * 레거시 파일 스캔
 */
function scanLegacyFiles($connect, $legacyPath) {
    echo "\n=== 레거시 파일 스캔 ===\n\n";

    // 1. 레거시 폴더 확인
    if (!is_dir($legacyPath)) {
        echo "경고: 레거시 폴더가 존재하지 않습니다: $legacyPath\n";
        echo "      프로덕션 서버에서 이 스크립트를 실행해야 할 수 있습니다.\n\n";
    } else {
        $files = scandir($legacyPath);
        $fileCount = count(array_filter($files, fn($f) => $f !== '.' && $f !== '..'));
        echo "레거시 폴더 파일 수: $fileCount\n\n";
    }

    // 2. DB에서 레거시 경로 주문 확인
    $sql = "SELECT no, ThingCate, ImgFolder, date, OrderStyle
            FROM mlangorder_printauto
            WHERE ImgFolder LIKE '../shop/data%'
            ORDER BY no DESC
            LIMIT 20";

    $result = mysqli_query($connect, $sql);

    echo "최근 레거시 경로 주문 (최대 20건):\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-8s %-40s %-30s %-12s\n", "주문번호", "ThingCate", "ImgFolder", "날짜");
    echo str_repeat("-", 100) . "\n";

    $existCount = 0;
    $missingCount = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $thingCate = $row['ThingCate'] ?? '(없음)';
        if (strlen($thingCate) > 38) {
            $thingCate = substr($thingCate, 0, 35) . '...';
        }

        $imgFolder = $row['ImgFolder'] ?? '(없음)';
        if (strlen($imgFolder) > 28) {
            $imgFolder = substr($imgFolder, 0, 25) . '...';
        }

        // 파일 존재 여부 확인
        $filePath = $legacyPath . basename($row['ThingCate'] ?? '');
        $exists = file_exists($filePath);
        $status = $exists ? '✓' : '✗';

        if ($exists) $existCount++;
        else $missingCount++;

        printf("%-8s %-40s %-30s %-12s %s\n",
            $row['no'],
            $thingCate,
            $imgFolder,
            substr($row['date'], 0, 10),
            $status
        );
    }

    echo str_repeat("-", 100) . "\n";
    echo "파일 존재: $existCount, 파일 없음: $missingCount\n";
}

/**
 * 파일 마이그레이션
 */
function migrateFiles($connect, $legacyPath, $newBasePath, $dryRun) {
    echo "\n=== 파일 마이그레이션 " . ($dryRun ? "(시뮬레이션)" : "(실제 실행)") . " ===\n\n";

    if (!is_dir($legacyPath)) {
        echo "오류: 레거시 폴더가 없습니다: $legacyPath\n";
        return;
    }

    // 레거시 경로 주문 조회
    $sql = "SELECT no, ThingCate, ImgFolder
            FROM mlangorder_printauto
            WHERE ImgFolder LIKE '../shop/data%'
            AND ThingCate IS NOT NULL
            AND ThingCate != ''
            ORDER BY no DESC";

    $result = mysqli_query($connect, $sql);

    $successCount = 0;
    $failCount = 0;
    $skipCount = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $orderNo = $row['no'];
        $filename = basename($row['ThingCate']);
        $sourcePath = $legacyPath . $filename;
        $destDir = $newBasePath . $orderNo . '/';
        $destPath = $destDir . $filename;

        echo "주문 #$orderNo: $filename\n";

        // 소스 파일 확인
        if (!file_exists($sourcePath)) {
            echo "  → 건너뜀 (소스 파일 없음)\n";
            $skipCount++;
            continue;
        }

        if ($dryRun) {
            echo "  → [DRY RUN] $sourcePath → $destPath\n";
            $successCount++;
        } else {
            // 대상 디렉토리 생성
            if (!is_dir($destDir)) {
                if (!mkdir($destDir, 0755, true)) {
                    echo "  → 실패 (디렉토리 생성 실패)\n";
                    $failCount++;
                    continue;
                }
            }

            // 파일 복사 (원본 유지)
            if (copy($sourcePath, $destPath)) {
                echo "  → 성공: $destPath\n";
                $successCount++;
            } else {
                echo "  → 실패 (복사 실패)\n";
                $failCount++;
            }
        }
    }

    echo "\n=== 마이그레이션 결과 ===\n";
    echo "성공: $successCount\n";
    echo "실패: $failCount\n";
    echo "건너뜀: $skipCount\n";

    if ($dryRun) {
        echo "\n주의: 이것은 시뮬레이션입니다.\n";
        echo "실제 마이그레이션을 실행하려면 스크립트의 \$DRY_RUN = false로 변경하세요.\n";
    }
}

/**
 * DB 경로 업데이트
 */
function updateDatabase($connect, $dryRun) {
    echo "\n=== DB 경로 업데이트 " . ($dryRun ? "(시뮬레이션)" : "(실제 실행)") . " ===\n\n";

    // 레거시 경로 주문 조회
    $sql = "SELECT no, ThingCate, ImgFolder
            FROM mlangorder_printauto
            WHERE ImgFolder LIKE '../shop/data%'
            AND ThingCate IS NOT NULL
            AND ThingCate != ''";

    $result = mysqli_query($connect, $sql);

    $updateCount = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $orderNo = $row['no'];
        $filename = basename($row['ThingCate']);

        // 새 경로 생성
        $newImgFolder = "upload/$orderNo/";
        $newThingCate = $filename;

        if ($dryRun) {
            echo "주문 #$orderNo:\n";
            echo "  ImgFolder: {$row['ImgFolder']} → $newImgFolder\n";
            echo "  ThingCate: {$row['ThingCate']} → $newThingCate\n\n";
        } else {
            $updateSql = "UPDATE mlangorder_printauto
                          SET ImgFolder = ?, ThingCate = ?
                          WHERE no = ?";
            $stmt = mysqli_prepare($connect, $updateSql);
            mysqli_stmt_bind_param($stmt, "ssi", $newImgFolder, $newThingCate, $orderNo);

            if (mysqli_stmt_execute($stmt)) {
                echo "주문 #$orderNo: 업데이트 완료\n";
                $updateCount++;
            } else {
                echo "주문 #$orderNo: 업데이트 실패 - " . mysqli_error($connect) . "\n";
            }
            mysqli_stmt_close($stmt);
        }
    }

    echo "\n업데이트 완료: $updateCount건\n";

    if ($dryRun) {
        echo "\n주의: 이것은 시뮬레이션입니다.\n";
        echo "실제 업데이트를 실행하려면 스크립트의 \$DRY_RUN = false로 변경하세요.\n";
    }
}

echo "\n";
