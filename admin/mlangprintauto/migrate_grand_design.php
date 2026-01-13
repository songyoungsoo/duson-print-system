<?php
/**
 * Grand Design 마이그레이션 스크립트
 *
 * mlangorder_printauto → orders + order_items + order_options
 *
 * 기능:
 * - 배치 처리 (1000건씩)
 * - Dry-Run 모드 지원
 * - legacy_data JSON에 원본 보존
 * - 롤백 가능 (migration_log 테이블)
 *
 * 사용법:
 * - Dry-Run: /admin/mlangprintauto/migrate_grand_design.php?mode=dry_run
 * - 실행: /admin/mlangprintauto/migrate_grand_design.php?mode=execute
 * - 특정 범위: ?mode=execute&start_no=1000&end_no=2000
 * - 배치 크기: ?batch_size=500
 *
 * @package DusonPrint
 * @since 2026-01-13
 */

// 인증 체크
require_once '../../includes/auth.php';
require_once '../../db.php';
require_once '../../includes/DataAdapter.php';
require_once '../../includes/QuantityFormatter.php';
require_once '../../includes/ProductSpecFormatter.php';

// 관리자 권한 체크
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_level'] < 5) {
    die('관리자 권한이 필요합니다.');
}

// 실행 시간 제한 해제
set_time_limit(0);
ini_set('memory_limit', '512M');

// 파라미터
$mode = $_GET['mode'] ?? 'info';
$batchSize = intval($_GET['batch_size'] ?? 1000);
$startNo = isset($_GET['start_no']) ? intval($_GET['start_no']) : null;
$endNo = isset($_GET['end_no']) ? intval($_GET['end_no']) : null;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grand Design Migration</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #4a90d9; padding-bottom: 10px; }
        .info-box { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .progress { background: #e9ecef; border-radius: 4px; height: 30px; margin: 10px 0; }
        .progress-bar { background: #4a90d9; height: 100%; border-radius: 4px; text-align: center; line-height: 30px; color: white; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #4a90d9; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .log-entry { padding: 5px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔄 Grand Design Migration Tool</h1>

<?php
// 현재 상태 조회
$stats = getMigrationStats($db);

if ($mode === 'info') {
    showInfo($stats, $batchSize);
} elseif ($mode === 'dry_run') {
    runMigration($db, $stats, $batchSize, $startNo, $endNo, true);
} elseif ($mode === 'execute') {
    runMigration($db, $stats, $batchSize, $startNo, $endNo, false);
} elseif ($mode === 'verify') {
    verifyMigration($db);
} elseif ($mode === 'rollback') {
    rollbackMigration($db, $_GET['order_id'] ?? null);
}

/**
 * 마이그레이션 통계 조회
 */
function getMigrationStats($db) {
    $stats = [];

    // 레거시 테이블 통계
    $result = mysqli_query($db, "SELECT COUNT(*) as total, MIN(no) as min_no, MAX(no) as max_no FROM mlangorder_printauto");
    $row = mysqli_fetch_assoc($result);
    $stats['legacy_total'] = intval($row['total']);
    $stats['legacy_min'] = intval($row['min_no']);
    $stats['legacy_max'] = intval($row['max_no']);

    // 제품별 통계
    $result = mysqli_query($db, "SELECT product_type, COUNT(*) as cnt FROM mlangorder_printauto GROUP BY product_type ORDER BY cnt DESC");
    $stats['legacy_by_product'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['legacy_by_product'][$row['product_type'] ?: 'NULL'] = intval($row['cnt']);
    }

    // 새 테이블 존재 여부 확인
    $result = mysqli_query($db, "SHOW TABLES LIKE 'orders'");
    $stats['new_tables_exist'] = mysqli_num_rows($result) > 0;

    if ($stats['new_tables_exist']) {
        // 새 테이블 통계
        $result = mysqli_query($db, "SELECT COUNT(*) as total FROM orders");
        $row = mysqli_fetch_assoc($result);
        $stats['new_orders'] = intval($row['total']);

        $result = mysqli_query($db, "SELECT COUNT(*) as total FROM order_items");
        $row = mysqli_fetch_assoc($result);
        $stats['new_items'] = intval($row['total']);

        // 마이그레이션 로그
        $result = mysqli_query($db, "SELECT status, COUNT(*) as cnt FROM migration_log GROUP BY status");
        $stats['migration_log'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['migration_log'][$row['status']] = intval($row['cnt']);
        }
    }

    return $stats;
}

/**
 * 정보 표시
 */
function showInfo($stats, $batchSize) {
    echo '<div class="info-box">';
    echo '<h3>📊 현재 상태</h3>';
    echo '<table>';
    echo '<tr><th>항목</th><th>값</th></tr>';
    echo '<tr><td>레거시 주문 수</td><td>' . number_format($stats['legacy_total']) . '건</td></tr>';
    echo '<tr><td>No 범위</td><td>' . number_format($stats['legacy_min']) . ' ~ ' . number_format($stats['legacy_max']) . '</td></tr>';
    echo '<tr><td>새 테이블 존재</td><td>' . ($stats['new_tables_exist'] ? '<span class="success">✅ 예</span>' : '<span class="error">❌ 아니오</span>') . '</td></tr>';

    if ($stats['new_tables_exist']) {
        echo '<tr><td>마이그레이션된 주문</td><td>' . number_format($stats['new_orders'] ?? 0) . '건</td></tr>';
        echo '<tr><td>마이그레이션된 품목</td><td>' . number_format($stats['new_items'] ?? 0) . '건</td></tr>';
    }
    echo '</table>';
    echo '</div>';

    // 제품별 통계
    echo '<div class="info-box">';
    echo '<h3>📦 제품별 통계</h3>';
    echo '<table>';
    echo '<tr><th>제품 타입</th><th>건수</th><th>비율</th></tr>';
    foreach ($stats['legacy_by_product'] as $type => $count) {
        $percent = round($count / $stats['legacy_total'] * 100, 2);
        echo "<tr><td>{$type}</td><td>" . number_format($count) . "</td><td>{$percent}%</td></tr>";
    }
    echo '</table>';
    echo '</div>';

    // 실행 버튼
    echo '<div class="info-box">';
    echo '<h3>🚀 실행</h3>';

    if (!$stats['new_tables_exist']) {
        echo '<p class="warning">⚠️ 먼저 스키마를 생성해야 합니다.</p>';
        echo '<pre>mysql -u root -p dsp1830 < /var/www/html/database/migrations/grand_design/01_schema.sql</pre>';
    } else {
        echo '<a href="?mode=dry_run&batch_size=' . $batchSize . '" class="btn btn-warning">🔍 Dry-Run (테스트)</a>';
        echo '<a href="?mode=execute&batch_size=' . $batchSize . '" class="btn btn-primary" onclick="return confirm(\'정말 마이그레이션을 실행하시겠습니까?\')">▶️ 실행</a>';
        echo '<a href="?mode=verify" class="btn btn-primary">✅ 검증</a>';
    }
    echo '</div>';
}

/**
 * 마이그레이션 실행
 */
function runMigration($db, $stats, $batchSize, $startNo, $endNo, $dryRun) {
    $mode = $dryRun ? 'Dry-Run' : 'Execute';
    echo "<h2>🔄 마이그레이션 {$mode}</h2>";

    // 범위 설정
    $minNo = $startNo ?? $stats['legacy_min'];
    $maxNo = $endNo ?? $stats['legacy_max'];

    echo '<div class="info-box">';
    echo "<p>범위: {$minNo} ~ {$maxNo} (배치 크기: {$batchSize})</p>";
    echo '</div>';

    $processed = 0;
    $success = 0;
    $failed = 0;
    $skipped = 0;
    $errors = [];

    // 배치 처리
    for ($batchStart = $minNo; $batchStart <= $maxNo; $batchStart += $batchSize) {
        $batchEnd = min($batchStart + $batchSize - 1, $maxNo);

        echo "<div class='log-entry'>배치 처리: {$batchStart} ~ {$batchEnd}...</div>";
        flush();

        // 레거시 데이터 조회
        $query = "SELECT * FROM mlangorder_printauto WHERE no >= {$batchStart} AND no <= {$batchEnd}";
        $result = mysqli_query($db, $query);

        while ($legacy = mysqli_fetch_assoc($result)) {
            $processed++;
            $legacyNo = $legacy['no'];

            try {
                // 이미 마이그레이션된 경우 스킵
                $checkQuery = "SELECT item_id FROM order_items WHERE legacy_no = {$legacyNo}";
                $checkResult = mysqli_query($db, $checkQuery);
                if (mysqli_num_rows($checkResult) > 0) {
                    $skipped++;
                    continue;
                }

                // 제품 타입 확인
                $productType = $legacy['product_type'] ?? '';
                if (empty($productType)) {
                    // 스티커 자동 감지
                    if (!empty($legacy['jong']) && !empty($legacy['garo']) && !empty($legacy['sero'])) {
                        $productType = 'sticker';
                    } else {
                        throw new Exception("제품 타입 없음");
                    }
                }

                // 정규화된 데이터 생성
                $normalized = DataAdapter::legacyToNormalized($legacy, $productType);

                if (!$dryRun) {
                    // 트랜잭션 시작
                    mysqli_begin_transaction($db);

                    try {
                        // order_items 삽입
                        $itemId = insertOrderItem($db, $normalized, $legacyNo);

                        // order_options 삽입
                        if (!empty($normalized['additional_options'])) {
                            insertOrderOptions($db, $itemId, $normalized['additional_options']);
                        }

                        // 마이그레이션 로그
                        logMigration($db, $legacyNo, null, $itemId, 'success');

                        mysqli_commit($db);
                        $success++;
                    } catch (Exception $e) {
                        mysqli_rollback($db);
                        throw $e;
                    }
                } else {
                    // Dry-Run: 검증만
                    if (empty($normalized['qty_value']) || empty($normalized['qty_unit_code'])) {
                        throw new Exception("수량 정보 누락");
                    }
                    $success++;
                }

            } catch (Exception $e) {
                $failed++;
                $errors[] = "No {$legacyNo}: " . $e->getMessage();
                if (!$dryRun) {
                    logMigration($db, $legacyNo, null, null, 'failed', $e->getMessage());
                }
            }
        }

        // 진행률 표시
        $progress = round(($batchEnd - $minNo) / ($maxNo - $minNo) * 100, 1);
        echo "<div class='progress'><div class='progress-bar' style='width:{$progress}%'>{$progress}%</div></div>";
        flush();
    }

    // 결과 요약
    echo '<div class="info-box">';
    echo '<h3>📋 결과 요약</h3>';
    echo '<table>';
    echo "<tr><td>처리</td><td class='success'>" . number_format($processed) . "건</td></tr>";
    echo "<tr><td>성공</td><td class='success'>" . number_format($success) . "건</td></tr>";
    echo "<tr><td>스킵 (이미 마이그레이션됨)</td><td>" . number_format($skipped) . "건</td></tr>";
    echo "<tr><td>실패</td><td class='error'>" . number_format($failed) . "건</td></tr>";
    echo '</table>';
    echo '</div>';

    // 오류 목록
    if (!empty($errors)) {
        echo '<div class="info-box">';
        echo '<h3>❌ 오류 목록 (최대 100건)</h3>';
        echo '<pre>';
        foreach (array_slice($errors, 0, 100) as $error) {
            echo htmlspecialchars($error) . "\n";
        }
        echo '</pre>';
        echo '</div>';
    }
}

/**
 * order_items 삽입
 */
function insertOrderItem($db, $normalized, $legacyNo) {
    $sql = "INSERT INTO order_items (
        legacy_no, product_type, product_type_display,
        spec_type, spec_material, spec_size, spec_sides, spec_design,
        qty_value, qty_unit_code, qty_sheets,
        price_supply, price_vat, price_unit,
        img_folder, thing_cate, ordertype, work_memo, legacy_data
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($db));
    }

    // 타입: i(legacyNo) + s×7(specs) + d(qty_value) + s(unit_code) + i×4(qty_sheets,prices) + s×5(files,etc)
    mysqli_stmt_bind_param($stmt, "isssssssdsiiiiissss",
        $legacyNo,
        $normalized['product_type'],
        $normalized['product_type_display'],
        $normalized['spec_type'],
        $normalized['spec_material'],
        $normalized['spec_size'],
        $normalized['spec_sides'],
        $normalized['spec_design'],
        $normalized['qty_value'],
        $normalized['qty_unit_code'],
        $normalized['qty_sheets'],
        $normalized['price_supply'],
        $normalized['price_vat'],
        $normalized['price_unit'],
        $normalized['img_folder'],
        $normalized['thing_cate'],
        $normalized['ordertype'],
        $normalized['work_memo'],
        $normalized['legacy_data']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }

    $itemId = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    return $itemId;
}

/**
 * order_options 삽입
 */
function insertOrderOptions($db, $itemId, $options) {
    if (!is_array($options)) return;

    $sql = "INSERT INTO order_options (item_id, option_category, option_type, option_value, option_price)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $sql);

    foreach ($options as $opt) {
        $category = $opt['category'];
        $type = $opt['type'] ?? null;
        $value = $opt['value'] ?? null;
        $price = $opt['price'] ?? 0;

        mysqli_stmt_bind_param($stmt, "isssi",
            $itemId,
            $category,
            $type,
            $value,
            $price
        );
        mysqli_stmt_execute($stmt);
    }

    mysqli_stmt_close($stmt);
}

/**
 * 마이그레이션 로그 기록
 */
function logMigration($db, $legacyNo, $orderId, $itemId, $status, $errorMsg = null) {
    $sql = "INSERT INTO migration_log (legacy_no, new_order_id, new_item_id, status, error_message)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iiiss", $legacyNo, $orderId, $itemId, $status, $errorMsg);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/**
 * 마이그레이션 검증
 */
function verifyMigration($db) {
    echo '<h2>✅ 마이그레이션 검증</h2>';

    // 1. 건수 비교
    echo '<div class="info-box">';
    echo '<h3>1. 건수 비교</h3>';

    $legacyCount = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto"))['cnt'];
    $itemCount = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM order_items"))['cnt'];
    $logSuccess = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as cnt FROM migration_log WHERE status='success'"))['cnt'];

    echo '<table>';
    echo "<tr><td>레거시 주문</td><td>" . number_format($legacyCount) . "건</td></tr>";
    echo "<tr><td>새 품목</td><td>" . number_format($itemCount) . "건</td></tr>";
    echo "<tr><td>마이그레이션 성공 로그</td><td>" . number_format($logSuccess) . "건</td></tr>";
    echo '</table>';
    echo '</div>';

    // 2. 샘플 데이터 비교
    echo '<div class="info-box">';
    echo '<h3>2. 샘플 데이터 비교 (최근 10건)</h3>';

    $query = "SELECT oi.*, ml.no as legacy_no, ml.product_type as legacy_product_type,
                     ml.MY_amount as legacy_amount, ml.quantity_display as legacy_qty_display
              FROM order_items oi
              JOIN mlangorder_printauto ml ON oi.legacy_no = ml.no
              ORDER BY oi.item_id DESC LIMIT 10";

    $result = mysqli_query($db, $query);

    echo '<table>';
    echo '<tr><th>Legacy No</th><th>제품</th><th>qty_value</th><th>qty_unit</th><th>표시(SSOT)</th><th>레거시 표시</th><th>일치</th></tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        $ssotDisplay = QuantityFormatter::format(
            floatval($row['qty_value']),
            $row['qty_unit_code'],
            $row['qty_sheets']
        );
        $legacyDisplay = $row['legacy_qty_display'] ?: '-';
        $match = ($ssotDisplay === $legacyDisplay) ? '✅' : '⚠️';

        echo "<tr>";
        echo "<td>{$row['legacy_no']}</td>";
        echo "<td>{$row['product_type']}</td>";
        echo "<td>{$row['qty_value']}</td>";
        echo "<td>{$row['qty_unit_code']}</td>";
        echo "<td>{$ssotDisplay}</td>";
        echo "<td>{$legacyDisplay}</td>";
        echo "<td>{$match}</td>";
        echo "</tr>";
    }

    echo '</table>';
    echo '</div>';

    // 3. 수량 합계 비교
    echo '<div class="info-box">';
    echo '<h3>3. 가격 합계 비교</h3>';

    $legacySum = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT SUM(CAST(Total_PriceForm AS SIGNED)) as total FROM mlangorder_printauto WHERE Total_PriceForm REGEXP '^[0-9]+$'"))['total'];
    $newSum = mysqli_fetch_assoc(mysqli_query($db, "SELECT SUM(price_vat) as total FROM order_items"))['total'];

    echo '<table>';
    echo "<tr><td>레거시 가격 합계</td><td>₩" . number_format($legacySum) . "</td></tr>";
    echo "<tr><td>새 테이블 가격 합계</td><td>₩" . number_format($newSum) . "</td></tr>";
    echo '</table>';
    echo '</div>';
}

/**
 * 롤백
 */
function rollbackMigration($db, $orderId) {
    echo '<h2>⏪ 롤백</h2>';

    if (!$orderId) {
        echo '<p class="warning">order_id를 지정하세요: ?mode=rollback&order_id=123</p>';
        return;
    }

    // 해당 주문의 품목 삭제 (CASCADE로 옵션도 함께 삭제)
    $result = mysqli_query($db, "DELETE FROM order_items WHERE order_id = " . intval($orderId));

    if ($result) {
        echo "<p class='success'>주문 ID {$orderId}의 데이터가 롤백되었습니다.</p>";
        // 로그 업데이트
        mysqli_query($db, "UPDATE migration_log SET status='rolled_back' WHERE new_order_id = " . intval($orderId));
    } else {
        echo "<p class='error'>롤백 실패: " . mysqli_error($db) . "</p>";
    }
}
?>

</div>
</body>
</html>
