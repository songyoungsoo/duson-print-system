<?php
/**
 * 통합 견적서 시스템 - Phase 5: 마이그레이션 검증
 *
 * 목적: 마이그레이션 후 데이터 품질 검증
 *
 * 실행: php verify_migration.php
 */

// CLI 실행 체크
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>이 스크립트는 CLI에서만 실행할 수 있습니다.</h1>";
    exit(1);
}

// CLI에서 DOCUMENT_ROOT 설정
// __DIR__ = /var/www/html/mlangprintauto/quote/migration
$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
$connect = $db;
mysqli_set_charset($connect, 'utf8mb4');

const UNIT_PATTERN = '/[매연부권개장]/u';

echo "==============================================\n";
echo " Phase 5: 마이그레이션 검증 결과\n";
echo "==============================================\n\n";

$tables = ['shop_temp', 'quotation_temp', 'quote_items'];
$allGood = true;

foreach ($tables as $table) {
    echo "--- $table ---\n";

    // 컬럼 존재 여부
    $checkQuery = "SHOW COLUMNS FROM `$table` LIKE 'quantity_display'";
    $checkResult = mysqli_query($connect, $checkQuery);
    if (mysqli_num_rows($checkResult) === 0) {
        echo "  [SKIP] quantity_display column not found\n\n";
        continue;
    }

    // 전체 레코드 수
    $totalQuery = "SELECT COUNT(*) as cnt FROM `$table` WHERE quantity_display IS NOT NULL AND quantity_display != ''";
    $totalResult = mysqli_query($connect, $totalQuery);
    $total = mysqli_fetch_assoc($totalResult)['cnt'];

    // 단위 있는 레코드 수
    $validQuery = "SELECT COUNT(*) as cnt FROM `$table`
                   WHERE quantity_display IS NOT NULL
                   AND quantity_display != ''
                   AND quantity_display REGEXP '[매연부권개장]'";
    $validResult = mysqli_query($connect, $validQuery);
    $valid = mysqli_fetch_assoc($validResult)['cnt'];

    // 단위 없는 레코드 수
    $invalidQuery = "SELECT COUNT(*) as cnt FROM `$table`
                     WHERE quantity_display IS NOT NULL
                     AND quantity_display != ''
                     AND quantity_display NOT REGEXP '[매연부권개장]'";
    $invalidResult = mysqli_query($connect, $invalidQuery);
    $invalid = mysqli_fetch_assoc($invalidResult)['cnt'];

    $percentage = $total > 0 ? round(($valid / $total) * 100, 1) : 100;

    echo "  Total records with quantity_display: $total\n";
    echo "  Records with unit: $valid ($percentage%)\n";
    echo "  Records without unit: $invalid\n";

    if ($invalid > 0) {
        $allGood = false;
        echo "  [WARNING] Some records still need migration!\n";

        // 샘플 표시
        $sampleQuery = "SELECT no, product_type, quantity_display FROM `$table`
                        WHERE quantity_display IS NOT NULL
                        AND quantity_display != ''
                        AND quantity_display NOT REGEXP '[매연부권개장]'
                        LIMIT 5";
        $sampleResult = mysqli_query($connect, $sampleQuery);
        echo "  Sample records needing fix:\n";
        while ($row = mysqli_fetch_assoc($sampleResult)) {
            echo "    - ID {$row['no']}: {$row['product_type']} = '{$row['quantity_display']}'\n";
        }
    } else {
        echo "  [OK] All records have proper units\n";
    }
    echo "\n";
}

// mlangorder_printauto 검증
echo "--- mlangorder_printauto (Type_1 JSON) ---\n";
$orderQuery = "SELECT COUNT(*) as cnt FROM mlangorder_printauto
               WHERE Type_1 LIKE '%quantity_display%'";
$orderResult = mysqli_query($connect, $orderQuery);
$orderTotal = mysqli_fetch_assoc($orderResult)['cnt'];
echo "  Records with quantity_display in Type_1: $orderTotal\n";

// Type_1에서 단위 없는 레코드 찾기 (간단한 패턴 매칭)
$invalidOrderQuery = "SELECT COUNT(*) as cnt FROM mlangorder_printauto
                      WHERE Type_1 LIKE '%\"quantity_display\":\"%'
                      AND Type_1 NOT REGEXP 'quantity_display\":\"[^\"]*[매연부권개장]'";
$invalidOrderResult = mysqli_query($connect, $invalidOrderQuery);
if ($invalidOrderResult) {
    $invalidOrders = mysqli_fetch_assoc($invalidOrderResult)['cnt'];
    echo "  Potentially invalid records: $invalidOrders\n";
} else {
    echo "  [NOTE] Could not check for invalid records in JSON\n";
}

echo "\n==============================================\n";
echo " Summary\n";
echo "==============================================\n";

if ($allGood) {
    echo "[SUCCESS] All tables have proper quantity_display units!\n";
} else {
    echo "[ACTION NEEDED] Some records still need migration.\n";
    echo "Run: php fix_quantity_display.php\n";
}

mysqli_close($connect);
