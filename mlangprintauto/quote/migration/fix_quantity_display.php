<?php
/**
 * 통합 견적서 시스템 - Phase 5: 데이터 마이그레이션
 *
 * 목적: quantity_display에 단위가 없는 레코드를 찾아서 수정
 *
 * 대상 테이블:
 * 1. shop_temp - 장바구니 임시 데이터
 * 2. quotation_temp - 견적서 임시 데이터
 * 3. quote_items - 견적서 품목 데이터
 * 4. mlangorder_printauto - 주문 데이터 (Type_1 JSON 내부)
 *
 * 실행: php fix_quantity_display.php [--dry-run] [--table=tablename]
 */

// CLI 실행 체크
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>이 스크립트는 CLI에서만 실행할 수 있습니다.</h1>";
    echo "<pre>php fix_quantity_display.php [--dry-run] [--table=tablename]</pre>";
    exit(1);
}

// CLI에서 DOCUMENT_ROOT 설정
// __DIR__ = /var/www/html/mlangprintauto/quote/migration
$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$connect = $db;
mysqli_set_charset($connect, 'utf8mb4');

// 옵션 파싱
$options = getopt('', ['dry-run', 'table::', 'verbose', 'help']);
$dryRun = isset($options['dry-run']);
$targetTable = $options['table'] ?? 'all';
$verbose = isset($options['verbose']);

if (isset($options['help'])) {
    echo "Usage: php fix_quantity_display.php [options]\n";
    echo "Options:\n";
    echo "  --dry-run       Show what would be changed without making changes\n";
    echo "  --table=NAME    Only process specific table (shop_temp, quotation_temp, quote_items)\n";
    echo "  --verbose       Show detailed progress\n";
    echo "  --help          Show this help\n";
    exit(0);
}

// 단위 패턴
const UNIT_PATTERN = '/[매연부권개장]/u';

// 제품별 기본 단위
const DEFAULT_UNITS = [
    'inserted' => '연',
    'leaflet' => '연',
    'sticker' => '매',
    'sticker_new' => '매',
    'namecard' => '매',
    'envelope' => '매',
    'msticker' => '매',
    'cadarok' => '부',
    'littleprint' => '장',
    'poster' => '장',
    'merchandisebond' => '매',
    'ncrflambeau' => '권',
];

/**
 * 단위가 있는 quantity_display 생성
 */
function ensureQuantityUnit($display, $productType, $row) {
    // 이미 단위가 있으면 그대로 반환
    if (!empty($display) && preg_match(UNIT_PATTERN, $display)) {
        return $display;
    }

    // 기본 단위 결정
    $unit = DEFAULT_UNITS[$productType] ?? '매';

    // 전단지/리플렛: 연 (매 병기) 형식
    if (in_array($productType, ['inserted', 'leaflet'])) {
        $myAmount = floatval($row['MY_amount'] ?? $row['quantity_value'] ?? 0);
        $mesu = intval($row['mesu'] ?? $row['quantity_sheets'] ?? 0);

        if ($myAmount > 0) {
            $yeonDisplay = floor($myAmount) == $myAmount
                ? number_format($myAmount)
                : number_format($myAmount, 1);

            if ($mesu > 0) {
                return $yeonDisplay . '연 (' . number_format($mesu) . '매)';
            }
            return $yeonDisplay . '연';
        }
    }

    // 숫자만 있는 경우 단위 추가
    $numericValue = preg_replace('/[^0-9.,]/', '', $display);
    // 천 단위 구분자(콤마) 제거 후 숫자 변환
    $numericValue = str_replace(',', '', $numericValue);
    if (!empty($numericValue) && is_numeric($numericValue)) {
        return number_format(floatval($numericValue)) . $unit;
    }

    // MY_amount나 quantity_value에서 수량 추출
    $quantity = floatval($row['MY_amount'] ?? $row['quantity_value'] ?? $row['mesu'] ?? 1);
    return number_format($quantity) . $unit;
}

/**
 * 테이블 마이그레이션 실행
 */
function migrateTable($connect, $tableName, $dryRun, $verbose) {
    echo "\n=== Processing table: $tableName ===\n";

    // quantity_display 컬럼 존재 여부 확인
    $checkQuery = "SHOW COLUMNS FROM `$tableName` LIKE 'quantity_display'";
    $checkResult = mysqli_query($connect, $checkQuery);
    if (mysqli_num_rows($checkResult) === 0) {
        echo "  [SKIP] Table doesn't have quantity_display column\n";
        return ['processed' => 0, 'updated' => 0, 'skipped' => 0];
    }

    // 단위가 없는 레코드 찾기 (숫자만 있거나 빈 값)
    // 한글 단위(매, 연, 부, 권, 개, 장)가 없는 레코드만 선택
    $selectQuery = "SELECT * FROM `$tableName`
                    WHERE quantity_display IS NOT NULL
                    AND quantity_display != ''
                    AND quantity_display NOT REGEXP '[매연부권개장]'";

    $result = mysqli_query($connect, $selectQuery);
    if (!$result) {
        echo "  [ERROR] Query failed: " . mysqli_error($connect) . "\n";
        return ['processed' => 0, 'updated' => 0, 'skipped' => 0, 'error' => mysqli_error($connect)];
    }

    $processed = 0;
    $updated = 0;
    $skipped = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $processed++;

        // ID 필드 결정 (테이블마다 다를 수 있음)
        $idField = isset($row['no']) ? 'no' : (isset($row['id']) ? 'id' : null);
        if (!$idField) {
            echo "  [WARN] No ID field found for record\n";
            $skipped++;
            continue;
        }
        $id = $row[$idField];

        $productType = $row['product_type'] ?? '';
        $oldDisplay = $row['quantity_display'];
        $newDisplay = ensureQuantityUnit($oldDisplay, $productType, $row);

        if ($oldDisplay === $newDisplay) {
            $skipped++;
            continue;
        }

        if ($verbose) {
            echo "  [$id] $productType: '$oldDisplay' -> '$newDisplay'\n";
        }

        if (!$dryRun) {
            $updateQuery = "UPDATE `$tableName` SET quantity_display = ? WHERE $idField = ?";
            $stmt = mysqli_prepare($connect, $updateQuery);
            mysqli_stmt_bind_param($stmt, "si", $newDisplay, $id);

            if (mysqli_stmt_execute($stmt)) {
                $updated++;
            } else {
                echo "  [ERROR] Update failed for ID $id: " . mysqli_stmt_error($stmt) . "\n";
            }
            mysqli_stmt_close($stmt);
        } else {
            $updated++; // dry-run에서는 예상 업데이트 수
        }
    }

    mysqli_free_result($result);

    echo "  Processed: $processed, Updated: $updated, Skipped: $skipped\n";
    return ['processed' => $processed, 'updated' => $updated, 'skipped' => $skipped];
}

/**
 * mlangorder_printauto 테이블의 Type_1 JSON 내 quantity_display 수정
 */
function migrateOrderTable($connect, $dryRun, $verbose) {
    echo "\n=== Processing table: mlangorder_printauto (Type_1 JSON) ===\n";

    // Type_1이 JSON이고 quantity_display가 있는 레코드 찾기
    $selectQuery = "SELECT no, Type, Type_1 FROM mlangorder_printauto
                    WHERE Type_1 IS NOT NULL
                    AND Type_1 LIKE '%quantity_display%'
                    LIMIT 1000";  // 안전을 위해 제한

    $result = mysqli_query($connect, $selectQuery);
    if (!$result) {
        echo "  [ERROR] Query failed: " . mysqli_error($connect) . "\n";
        return ['processed' => 0, 'updated' => 0, 'skipped' => 0];
    }

    $processed = 0;
    $updated = 0;
    $skipped = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $processed++;
        $id = $row['no'];
        $type1 = $row['Type_1'];

        // "상품 정보: " 접두사 제거
        $jsonStr = $type1;
        $prefix = '';
        if (strpos($type1, '상품 정보: ') === 0) {
            $prefix = '상품 정보: ';
            $jsonStr = substr($type1, strlen($prefix));
        }

        $json = json_decode($jsonStr, true);
        if (!$json) {
            $skipped++;
            continue;
        }

        // quantity_display 검증
        $oldDisplay = $json['quantity_display'] ?? '';
        if (empty($oldDisplay) || preg_match(UNIT_PATTERN, $oldDisplay)) {
            $skipped++;
            continue;
        }

        // 제품 타입 추출
        $productType = $json['product_type'] ?? '';
        if (empty($productType)) {
            // Type 필드에서 추론
            $type = $row['Type'] ?? '';
            if (strpos($type, '전단') !== false) $productType = 'inserted';
            elseif (strpos($type, '명함') !== false) $productType = 'namecard';
            elseif (strpos($type, '스티커') !== false) $productType = 'sticker';
            // ... etc
        }

        $newDisplay = ensureQuantityUnit($oldDisplay, $productType, $json);

        if ($oldDisplay === $newDisplay) {
            $skipped++;
            continue;
        }

        if ($verbose) {
            echo "  [$id] $productType: '$oldDisplay' -> '$newDisplay'\n";
        }

        // JSON 업데이트
        $json['quantity_display'] = $newDisplay;
        $newJsonStr = $prefix . json_encode($json, JSON_UNESCAPED_UNICODE);

        if (!$dryRun) {
            $updateQuery = "UPDATE mlangorder_printauto SET Type_1 = ? WHERE no = ?";
            $stmt = mysqli_prepare($connect, $updateQuery);
            mysqli_stmt_bind_param($stmt, "si", $newJsonStr, $id);

            if (mysqli_stmt_execute($stmt)) {
                $updated++;
            } else {
                echo "  [ERROR] Update failed for no $id: " . mysqli_stmt_error($stmt) . "\n";
            }
            mysqli_stmt_close($stmt);
        } else {
            $updated++;
        }
    }

    mysqli_free_result($result);

    echo "  Processed: $processed, Updated: $updated, Skipped: $skipped\n";
    return ['processed' => $processed, 'updated' => $updated, 'skipped' => $skipped];
}

// ============================================
// 메인 실행
// ============================================

echo "==============================================\n";
echo " Phase 5: quantity_display 데이터 마이그레이션\n";
echo "==============================================\n";
echo "Mode: " . ($dryRun ? "DRY-RUN (no changes)" : "LIVE") . "\n";
echo "Target: " . $targetTable . "\n";

$totalStats = ['processed' => 0, 'updated' => 0, 'skipped' => 0];

// shop_temp
if ($targetTable === 'all' || $targetTable === 'shop_temp') {
    $stats = migrateTable($connect, 'shop_temp', $dryRun, $verbose);
    $totalStats['processed'] += $stats['processed'];
    $totalStats['updated'] += $stats['updated'];
    $totalStats['skipped'] += $stats['skipped'];
}

// quotation_temp
if ($targetTable === 'all' || $targetTable === 'quotation_temp') {
    $stats = migrateTable($connect, 'quotation_temp', $dryRun, $verbose);
    $totalStats['processed'] += $stats['processed'];
    $totalStats['updated'] += $stats['updated'];
    $totalStats['skipped'] += $stats['skipped'];
}

// quote_items
if ($targetTable === 'all' || $targetTable === 'quote_items') {
    $stats = migrateTable($connect, 'quote_items', $dryRun, $verbose);
    $totalStats['processed'] += $stats['processed'];
    $totalStats['updated'] += $stats['updated'];
    $totalStats['skipped'] += $stats['skipped'];
}

// mlangorder_printauto (Type_1 JSON)
if ($targetTable === 'all' || $targetTable === 'mlangorder_printauto') {
    $stats = migrateOrderTable($connect, $dryRun, $verbose);
    $totalStats['processed'] += $stats['processed'];
    $totalStats['updated'] += $stats['updated'];
    $totalStats['skipped'] += $stats['skipped'];
}

echo "\n==============================================\n";
echo " Migration Summary\n";
echo "==============================================\n";
echo "Total Processed: {$totalStats['processed']}\n";
echo "Total Updated: {$totalStats['updated']}\n";
echo "Total Skipped: {$totalStats['skipped']}\n";

if ($dryRun) {
    echo "\n[DRY-RUN] No actual changes were made.\n";
    echo "Run without --dry-run to apply changes.\n";
}

mysqli_close($connect);
