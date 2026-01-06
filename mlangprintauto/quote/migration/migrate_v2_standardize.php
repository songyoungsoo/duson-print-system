<?php
/**
 * Phase 5: quotation_temp 데이터 마이그레이션 스크립트
 *
 * 기존 레거시 데이터를 Phase 3 표준 필드로 변환
 *
 * 실행: php migrate_v2_standardize.php
 * 롤백: php migrate_v2_standardize.php --rollback
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
echo "║   Phase 5: quotation_temp 데이터 마이그레이션             ║\n";
echo "║   레거시 → Phase 3 표준 필드 변환                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 롤백 모드 체크
$isRollback = in_array('--rollback', $argv);

if ($isRollback) {
    rollbackMigration($db);
    exit(0);
}

// === Step 1: 백업 테이블 생성 ===
echo "📦 [Step 1/5] 백업 테이블 생성 중...\n";

$backupTable = 'quotation_temp_backup_' . date('Ymd_His');
$createBackupQuery = "CREATE TABLE $backupTable LIKE quotation_temp";

if (!mysqli_query($db, $createBackupQuery)) {
    die("❌ 백업 테이블 생성 실패: " . mysqli_error($db) . "\n");
}

$copyDataQuery = "INSERT INTO $backupTable SELECT * FROM quotation_temp";
if (!mysqli_query($db, $copyDataQuery)) {
    die("❌ 데이터 복사 실패: " . mysqli_error($db) . "\n");
}

$backupCount = mysqli_affected_rows($db);
echo "✅ 백업 완료: {$backupCount}개 레코드 → $backupTable\n\n";

// === Step 2: Phase 3 필드 추가 (이미 있으면 스킵) ===
echo "🔧 [Step 2/5] Phase 3 표준 필드 확인 중...\n";

$columnsToAdd = [
    "spec_type VARCHAR(255) AFTER original_filename",
    "spec_material VARCHAR(255) AFTER spec_type",
    "spec_size VARCHAR(100) AFTER spec_material",
    "spec_sides VARCHAR(50) AFTER spec_size",
    "spec_design VARCHAR(50) AFTER spec_sides",
    "quantity_value DECIMAL(10,2) AFTER spec_design",
    "quantity_unit VARCHAR(10) DEFAULT '매' AFTER quantity_value",
    "quantity_sheets INT AFTER quantity_unit",
    "quantity_display VARCHAR(50) AFTER quantity_sheets",
    "price_supply INT DEFAULT 0 AFTER quantity_display",
    "price_vat INT DEFAULT 0 AFTER price_supply",
    "price_vat_amount INT AFTER price_vat",
    "data_version TINYINT DEFAULT NULL AFTER price_vat_amount"
];

$addedFields = 0;
foreach ($columnsToAdd as $columnDef) {
    preg_match('/^(\w+)/', $columnDef, $matches);
    $columnName = $matches[1];

    // 컬럼 존재 여부 확인
    $checkQuery = "SHOW COLUMNS FROM quotation_temp LIKE '$columnName'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) === 0) {
        // 컬럼 추가
        $alterQuery = "ALTER TABLE quotation_temp ADD COLUMN $columnDef";
        if (mysqli_query($db, $alterQuery)) {
            echo "  ✅ 필드 추가: $columnName\n";
            $addedFields++;
        } else {
            echo "  ⚠️  필드 추가 실패: $columnName - " . mysqli_error($db) . "\n";
        }
    } else {
        echo "  ⏭️  필드 존재: $columnName (스킵)\n";
    }
}

echo "✅ 필드 확인 완료: {$addedFields}개 추가됨\n\n";

// === Step 3: 레거시 데이터 변환 ===
echo "🔄 [Step 3/5] 레거시 데이터 변환 중...\n";

// data_version IS NULL인 레코드만 변환
$query = "SELECT * FROM quotation_temp WHERE data_version IS NULL OR data_version = 0";
$result = mysqli_query($db, $query);

if (!$result) {
    die("❌ 데이터 조회 실패: " . mysqli_error($db) . "\n");
}

$totalRecords = mysqli_num_rows($result);
echo "📊 변환 대상: {$totalRecords}개 레코드\n";

if ($totalRecords === 0) {
    echo "✅ 변환할 레거시 데이터가 없습니다.\n\n";
} else {
    $converted = 0;
    $failed = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $no = $row['no'];
        $productType = $row['product_type'] ?? '';

        if (empty($productType)) {
            // product_type이 없으면 스티커로 추정 (jong 필드 존재)
            if (!empty($row['jong'])) {
                $productType = 'sticker';
            } else {
                echo "  ⚠️  [ID: $no] product_type 누락, 스킵\n";
                $failed++;
                continue;
            }
        }

        // DataAdapter로 변환
        try {
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
                echo "  ⚠️  [ID: $no, Type: $productType] 검증 실패, 스킵\n";
                $failed++;
                continue;
            }

            // quantity_display 자동 생성 (비어있으면)
            if (empty($standardData['quantity_display'])) {
                $standardData['quantity_display'] = DataAdapter::generateQuantityDisplay($standardData);
            }

            // UPDATE 쿼리
            $updateQuery = "UPDATE quotation_temp SET
                spec_type = ?,
                spec_material = ?,
                spec_size = ?,
                spec_sides = ?,
                spec_design = ?,
                quantity_value = ?,
                quantity_unit = ?,
                quantity_sheets = ?,
                quantity_display = ?,
                price_supply = ?,
                price_vat = ?,
                price_vat_amount = ?,
                data_version = 2
                WHERE no = ?";

            $stmt = mysqli_prepare($db, $updateQuery);

            if (!$stmt) {
                echo "  ❌ [ID: $no] Prepare 실패: " . mysqli_error($db) . "\n";
                $failed++;
                continue;
            }

            mysqli_stmt_bind_param($stmt, "sssssdssissiii",
                $standardData['spec_type'],
                $standardData['spec_material'],
                $standardData['spec_size'],
                $standardData['spec_sides'],
                $standardData['spec_design'],
                $standardData['quantity_value'],
                $standardData['quantity_unit'],
                $standardData['quantity_sheets'],
                $standardData['quantity_display'],
                $standardData['price_supply'],
                $standardData['price_vat'],
                $standardData['price_vat_amount'],
                $no
            );

            if (mysqli_stmt_execute($stmt)) {
                $converted++;
                if ($converted % 10 === 0) {
                    echo "  ⏳ 진행 중... {$converted}/{$totalRecords}\n";
                }
            } else {
                echo "  ❌ [ID: $no] UPDATE 실패: " . mysqli_stmt_error($stmt) . "\n";
                $failed++;
            }

            mysqli_stmt_close($stmt);

        } catch (Exception $e) {
            echo "  ❌ [ID: $no, Type: $productType] 변환 실패: " . $e->getMessage() . "\n";
            $failed++;
        }
    }

    echo "\n";
    echo "✅ 변환 완료: {$converted}/{$totalRecords}개 성공\n";
    if ($failed > 0) {
        echo "⚠️  실패: {$failed}개\n";
    }
    echo "\n";
}

// === Step 4: 검증 ===
echo "🔍 [Step 4/5] 변환 결과 검증 중...\n";

$statsQuery = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN data_version = 2 THEN 1 ELSE 0 END) as phase3_count,
    SUM(CASE WHEN data_version IS NULL OR data_version = 0 THEN 1 ELSE 0 END) as legacy_count
    FROM quotation_temp";

$statsResult = mysqli_query($db, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

echo "📊 변환 통계:\n";
echo "  - 전체 레코드: {$stats['total']}개\n";
echo "  - Phase 3 변환: {$stats['phase3_count']}개\n";
echo "  - 레거시 남음: {$stats['legacy_count']}개\n";
echo "\n";

// 샘플 데이터 검증
echo "📋 샘플 데이터 확인 (최근 5개):\n";
$sampleQuery = "SELECT no, product_type, quantity_display, price_supply, price_vat, data_version
                FROM quotation_temp
                WHERE data_version = 2
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

// === Step 5: 완료 ===
echo "✅ [Step 5/5] 마이그레이션 완료!\n";
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   마이그레이션 성공                                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "📦 백업 테이블: $backupTable\n";
echo "🔄 롤백 명령어: php migrate_v2_standardize.php --rollback\n";
echo "\n";

// 롤백 정보를 파일에 저장
file_put_contents(
    __DIR__ . '/last_backup.txt',
    $backupTable
);

mysqli_close($db);

// === 롤백 함수 ===
function rollbackMigration($db) {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║   ⚠️  롤백 모드                                            ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    echo "\n";

    // 마지막 백업 테이블 이름 읽기
    $backupFile = __DIR__ . '/last_backup.txt';
    if (!file_exists($backupFile)) {
        die("❌ 백업 정보를 찾을 수 없습니다.\n");
    }

    $backupTable = trim(file_get_contents($backupFile));

    // 백업 테이블 존재 확인
    $checkQuery = "SHOW TABLES LIKE '$backupTable'";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) === 0) {
        die("❌ 백업 테이블을 찾을 수 없습니다: $backupTable\n");
    }

    echo "📦 백업 테이블 발견: $backupTable\n";
    echo "⚠️  quotation_temp를 백업으로 복원하시겠습니까? (yes/no): ";

    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));

    if (strtolower($line) !== 'yes') {
        echo "❌ 롤백 취소됨\n";
        exit(0);
    }

    echo "\n🔄 롤백 진행 중...\n";

    // 현재 테이블 삭제
    if (!mysqli_query($db, "DROP TABLE quotation_temp")) {
        die("❌ 현재 테이블 삭제 실패: " . mysqli_error($db) . "\n");
    }
    echo "  ✅ 현재 quotation_temp 삭제\n";

    // 백업 테이블 이름 변경
    if (!mysqli_query($db, "RENAME TABLE $backupTable TO quotation_temp")) {
        die("❌ 백업 테이블 복원 실패: " . mysqli_error($db) . "\n");
    }
    echo "  ✅ 백업 테이블 복원\n";

    echo "\n";
    echo "✅ 롤백 완료! quotation_temp가 백업으로 복원되었습니다.\n";
    echo "\n";

    mysqli_close($db);
}
?>
