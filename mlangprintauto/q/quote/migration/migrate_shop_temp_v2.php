<?php
/**
 * Phase 5: shop_temp 데이터 마이그레이션 스크립트
 *
 * 장바구니 레거시 데이터를 Phase 3 표준 필드로 변환
 * quotation_temp와 유사하지만 shop_temp 전용
 *
 * 실행: php migrate_shop_temp_v2.php
 *
 * @author Claude Code
 * @version 2.0
 * @date 2026-01-06
 */

// CLI에서만 실행 허용
if (php_sapi_name() !== 'cli') {
    die("❌ 이 스크립트는 CLI에서만 실행 가능합니다.\n");
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../includes/DataAdapter.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   Phase 5: shop_temp 데이터 마이그레이션                  ║\n";
echo "║   장바구니 데이터 → Phase 3 표준 필드 변환               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// === Step 1: 현재 상태 확인 ===
echo "📊 [Step 1/4] 현재 데이터 상태 확인 중...\n";

$statsQuery = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN quantity_display IS NOT NULL AND quantity_display != '' THEN 1 ELSE 0 END) as has_display,
    SUM(CASE WHEN quantity_display IS NULL OR quantity_display = '' THEN 1 ELSE 0 END) as needs_migration
    FROM shop_temp";

$statsResult = mysqli_query($db, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

echo "  - 전체 레코드: {$stats['total']}개\n";
echo "  - quantity_display 있음: {$stats['has_display']}개\n";
echo "  - 마이그레이션 필요: {$stats['needs_migration']}개\n";
echo "\n";

if ($stats['needs_migration'] === 0) {
    echo "✅ 모든 데이터가 이미 Phase 3 형식입니다. 마이그레이션 불필요.\n\n";
    mysqli_close($db);
    exit(0);
}

echo "⚠️  {$stats['needs_migration']}개 레코드를 업데이트합니다.\n";
echo "계속하시겠습니까? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) !== 'yes') {
    echo "❌ 마이그레이션 취소됨\n";
    exit(0);
}

// === Step 2: 백업 테이블 생성 ===
echo "\n📦 [Step 2/4] 백업 테이블 생성 중...\n";

$backupTable = 'shop_temp_backup_' . date('Ymd_His');
$createBackupQuery = "CREATE TABLE $backupTable LIKE shop_temp";

if (!mysqli_query($db, $createBackupQuery)) {
    die("❌ 백업 테이블 생성 실패: " . mysqli_error($db) . "\n");
}

$copyDataQuery = "INSERT INTO $backupTable SELECT * FROM shop_temp";
if (!mysqli_query($db, $copyDataQuery)) {
    die("❌ 데이터 복사 실패: " . mysqli_error($db) . "\n");
}

$backupCount = mysqli_affected_rows($db);
echo "✅ 백업 완료: {$backupCount}개 레코드 → $backupTable\n\n";

// === Step 3: 데이터 변환 ===
echo "🔄 [Step 3/4] 데이터 변환 중...\n";

// quantity_display가 NULL이거나 빈 문자열인 레코드만 처리
$query = "SELECT * FROM shop_temp WHERE quantity_display IS NULL OR quantity_display = ''";
$result = mysqli_query($db, $query);

if (!$result) {
    die("❌ 데이터 조회 실패: " . mysqli_error($db) . "\n");
}

$totalRecords = mysqli_num_rows($result);
echo "📊 변환 대상: {$totalRecords}개 레코드\n";

$converted = 0;
$failed = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $no = $row['no'];
    $productType = $row['product_type'] ?? '';

    if (empty($productType)) {
        // product_type이 없으면 스티커로 추정
        if (!empty($row['jong'])) {
            $productType = 'sticker';
        } else {
            $failed++;
            continue;
        }
    }

    try {
        // DataAdapter로 변환 (quotation_temp와 동일)
        $legacyData = [
            'product_type' => $productType,
            'jong' => $row['jong'] ?? '',
            'garo' => $row['garo'] ?? '',
            'sero' => $row['sero'] ?? '',
            'mesu' => $row['mesu'] ?? '',
            'domusong' => $row['domusong'] ?? '',
            'uhyung' => $row['uhyung'] ?? 0,
            'MY_type' => $row['MY_type'] ?? '',
            'MY_Fsd' => $row['MY_Fsd'] ?? '',
            'PN_type' => $row['PN_type'] ?? '',
            'MY_amount' => $row['MY_amount'] ?? '',
            'POtype' => $row['POtype'] ?? '',
            'ordertype' => $row['ordertype'] ?? '',
            'st_price' => $row['st_price'] ?? 0,
            'st_price_vat' => $row['st_price_vat'] ?? 0,
            'price' => $row['st_price'] ?? 0,
            'vat_price' => $row['st_price_vat'] ?? 0,
            'MY_type_name' => $row['MY_type_name'] ?? '',
            'Section_name' => $row['Section_name'] ?? '',
            'POtype_name' => $row['POtype_name'] ?? '',
            'quantity_display' => $row['quantity_display'] ?? ''
        ];

        $standardData = DataAdapter::legacyToStandard($legacyData, $productType);

        // 검증
        if (!DataAdapter::validateStandardData($standardData, $productType)) {
            $failed++;
            continue;
        }

        // quantity_display 자동 생성
        if (empty($standardData['quantity_display'])) {
            $standardData['quantity_display'] = DataAdapter::generateQuantityDisplay($standardData);
        }

        // ✅ shop_temp는 quantity_display만 업데이트 (다른 Phase 3 필드는 이미 있음)
        $updateQuery = "UPDATE shop_temp SET
            quantity_display = ?,
            spec_type = ?,
            spec_material = ?,
            spec_size = ?,
            quantity_value = ?,
            quantity_unit = ?,
            price_supply = ?,
            price_vat = ?
            WHERE no = ?";

        $stmt = mysqli_prepare($db, $updateQuery);

        if (!$stmt) {
            $failed++;
            continue;
        }

        mysqli_stmt_bind_param($stmt, "ssssdssii",
            $standardData['quantity_display'],
            $standardData['spec_type'],
            $standardData['spec_material'],
            $standardData['spec_size'],
            $standardData['quantity_value'],
            $standardData['quantity_unit'],
            $standardData['price_supply'],
            $standardData['price_vat'],
            $no
        );

        if (mysqli_stmt_execute($stmt)) {
            $converted++;
            if ($converted % 50 === 0) {
                echo "  ⏳ 진행 중... {$converted}/{$totalRecords}\n";
            }
        } else {
            $failed++;
        }

        mysqli_stmt_close($stmt);

    } catch (Exception $e) {
        $failed++;
    }
}

echo "\n";
echo "✅ 변환 완료: {$converted}/{$totalRecords}개 성공\n";
if ($failed > 0) {
    echo "⚠️  실패: {$failed}개\n";
}
echo "\n";

// === Step 4: 검증 ===
echo "🔍 [Step 4/4] 변환 결과 검증 중...\n";

$statsQuery = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN quantity_display IS NOT NULL AND quantity_display != '' THEN 1 ELSE 0 END) as has_display,
    SUM(CASE WHEN quantity_display IS NULL OR quantity_display = '' THEN 1 ELSE 0 END) as still_empty
    FROM shop_temp";

$statsResult = mysqli_query($db, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

echo "📊 변환 후 통계:\n";
echo "  - 전체 레코드: {$stats['total']}개\n";
echo "  - quantity_display 있음: {$stats['has_display']}개\n";
echo "  - 아직 비어있음: {$stats['still_empty']}개\n";
echo "\n";

// 샘플 데이터 확인
echo "📋 샘플 데이터 확인 (최근 5개):\n";
$sampleQuery = "SELECT no, product_type, quantity_display, price_supply, price_vat
                FROM shop_temp
                WHERE quantity_display IS NOT NULL AND quantity_display != ''
                ORDER BY no DESC
                LIMIT 5";
$sampleResult = mysqli_query($db, $sampleQuery);

while ($row = mysqli_fetch_assoc($sampleResult)) {
    echo "  - [ID: {$row['no']}] {$row['product_type']}: ";
    echo "{$row['quantity_display']}, ";
    echo "공급가: " . number_format($row['price_supply']) . "원, ";
    echo "VAT포함: " . number_format($row['price_vat']) . "원\n";
}
echo "\n";

echo "✅ shop_temp 마이그레이션 완료!\n";
echo "📦 백업 테이블: $backupTable\n";
echo "\n";

mysqli_close($db);
?>
