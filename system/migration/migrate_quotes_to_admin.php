<?php
/**
 * QT- (quotes) → AQ- (admin_quotes) 데이터 변환 v2
 *
 * quotes/quote_items → admin_quotes/admin_quote_items 완전 변환
 * - 견적번호: 원본 created_at 날짜 기반 AQ-YYYYMMDD-NNNN 생성
 * - 품목: quote_items → admin_quote_items (quote_id 매핑 포함)
 * - 기존 AQ- 레코드 보존 (프로덕션 안전)
 * - 잘못된 마이그레이션 데이터 자동 정리
 *
 * 사용법:
 *   php migrate_quotes_to_admin.php --dry-run     미리보기 (DB 변경 없음)
 *   php migrate_quotes_to_admin.php --execute      실제 실행
 *   php migrate_quotes_to_admin.php --sql-file     SQL 파일 저장
 */

if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

$dryRun  = in_array('--dry-run', $argv);
$execute = in_array('--execute', $argv);
$sqlFile = in_array('--sql-file', $argv);

if (!$dryRun && !$execute && !$sqlFile) {
    echo "Usage:\n";
    echo "  php migrate_quotes_to_admin.php --dry-run     미리보기\n";
    echo "  php migrate_quotes_to_admin.php --execute      실제 실행\n";
    echo "  php migrate_quotes_to_admin.php --sql-file     SQL 파일 저장\n";
    exit(1);
}

require_once __DIR__ . '/../../db.php';

echo "=== 견적 데이터 변환 v2 (QT- → AQ-) ===\n";
echo "Mode: " . ($dryRun ? "DRY RUN" : ($sqlFile ? "SQL FILE" : "EXECUTE")) . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// ============================================================
// Phase 0: Clean up broken migration data (YYYYMMDD-NNNN without AQ- prefix)
// ============================================================
echo "--- Phase 0: 잘못된 마이그레이션 데이터 정리 ---\n";

$brokenCount = 0;
$brokenCheck = mysqli_query($db, "SELECT COUNT(*) as cnt FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%'");
if ($brokenCheck) {
    $row = mysqli_fetch_assoc($brokenCheck);
    $brokenCount = (int)$row['cnt'];
}

if ($brokenCount > 0) {
    echo "  ⚠️  AQ- 접두어 없는 레코드 {$brokenCount}건 발견\n";
    if ($execute) {
        mysqli_query($db, "DELETE FROM admin_quote_items WHERE quote_id IN (SELECT id FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%')");
        $delItems = mysqli_affected_rows($db);
        mysqli_query($db, "DELETE FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%'");
        $delQuotes = mysqli_affected_rows($db);
        echo "  ✅ 삭제: admin_quotes {$delQuotes}건, admin_quote_items {$delItems}건\n";
    } else {
        echo "  [DRY RUN] 삭제 예정: {$brokenCount}건\n";
    }
} else {
    echo "  ✅ 정리할 데이터 없음\n";
}

// ============================================================
// Phase 1: Read source data
// ============================================================
echo "\n--- Phase 1: 소스 데이터 조회 ---\n";

// Get existing AQ- max sequence per date (to avoid conflicts on production)
$existingAQ = [];
$aqResult = mysqli_query($db, "SELECT quote_no FROM admin_quotes WHERE quote_no LIKE 'AQ-%'");
if ($aqResult) {
    while ($row = mysqli_fetch_assoc($aqResult)) {
        if (preg_match('/^AQ-(\d{8})-(\d{4})$/', $row['quote_no'], $m)) {
            $dateKey = $m[1];
            $seq = (int)$m[2];
            if (!isset($existingAQ[$dateKey]) || $seq > $existingAQ[$dateKey]) {
                $existingAQ[$dateKey] = $seq;
            }
        }
    }
}
$existingCount = 0;
$aqCountResult = mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quotes WHERE quote_no LIKE 'AQ-%'");
if ($aqCountResult) {
    $existingCount = (int)mysqli_fetch_assoc($aqCountResult)['c'];
}
echo "  기존 AQ- 레코드: {$existingCount}건\n";
if (!empty($existingAQ)) {
    echo "  날짜별 최대 SEQ: " . json_encode($existingAQ) . "\n";
}

// Read all quotes
$quotes = [];
$result = mysqli_query($db, "SELECT * FROM quotes ORDER BY created_at ASC, id ASC");
if (!$result) {
    die("❌ quotes 조회 실패: " . mysqli_error($db) . "\n");
}
while ($row = mysqli_fetch_assoc($result)) {
    $quotes[] = $row;
}
echo "  quotes 테이블: " . count($quotes) . "건\n";

// Read all quote_items grouped by quote_id
$allItems = [];
$itemResult = mysqli_query($db, "SELECT * FROM quote_items ORDER BY quote_id, item_no");
if ($itemResult) {
    while ($row = mysqli_fetch_assoc($itemResult)) {
        $qid = $row['quote_id'];
        if (!isset($allItems[$qid])) {
            $allItems[$qid] = [];
        }
        $allItems[$qid][] = $row;
    }
}
$totalItems = array_sum(array_map('count', $allItems));
echo "  quote_items 테이블: {$totalItems}건\n";

// ============================================================
// Phase 2: Convert quotes → admin_quotes
// ============================================================
$totalQuotes = count($quotes);
echo "\n--- Phase 2: admin_quotes 변환 ({$totalQuotes}건) ---\n";

$dateSeqs = $existingAQ; // Start from existing max per date
$idMapping = [];         // old quotes.id → new admin_quotes.id
$sqlStatements = [];
$quotesOk = 0;
$itemsOk = 0;
$errors = 0;

if ($execute) {
    mysqli_begin_transaction($db);
}

foreach ($quotes as $quote) {
    $oldId = $quote['id'];
    $oldNo = $quote['quote_no'];

    // AQ- number: use original created_at date
    $createdDate = date('Ymd', strtotime($quote['created_at']));
    if (!isset($dateSeqs[$createdDate])) {
        $dateSeqs[$createdDate] = 0;
    }
    $dateSeqs[$createdDate]++;
    $newQuoteNo = sprintf('AQ-%s-%04d', $createdDate, $dateSeqs[$createdDate]);

    // Status (same ENUM values)
    $status = $quote['status'] ?? 'draft';
    $validStatuses = ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted'];
    if (!in_array($status, $validStatuses)) {
        $status = 'draft';
    }

    // valid_until
    $validUntil = $quote['valid_until'] ?? null;
    if (empty($validUntil) && !empty($quote['valid_days'])) {
        $validUntil = date('Y-m-d', strtotime("+{$quote['valid_days']} days", strtotime($quote['created_at'])));
    }

    // Memo: append original QT- number for traceability
    $adminMemo = trim(($quote['notes'] ?? '') . "\n[원본: {$oldNo}]");
    $customerMemo = $quote['customer_notes'] ?? ($quote['response_notes'] ?? '');
    $customerAddress = $quote['delivery_address'] ?? '';

    $sql = sprintf(
        "INSERT INTO admin_quotes (
            quote_no, customer_company, customer_name, customer_phone, customer_email,
            customer_address, supply_total, vat_total, grand_total, status, valid_until,
            converted_order_no, admin_memo, customer_memo, created_at, updated_at
        ) VALUES (
            %s, %s, %s, %s, %s,
            %s, %d, %d, %d, %s, %s,
            %s, %s, %s, %s, %s
        )",
        qs($db, $newQuoteNo),
        qs($db, $quote['customer_company'] ?? ''),
        qs($db, $quote['customer_name'] ?? ''),
        qs($db, $quote['customer_phone'] ?? ''),
        qs($db, $quote['customer_email'] ?? ''),
        qs($db, $customerAddress),
        (int)($quote['supply_total'] ?? 0),
        (int)($quote['vat_total'] ?? 0),
        (int)($quote['grand_total'] ?? 0),
        qs($db, $status),
        $validUntil ? qs($db, $validUntil) : 'NULL',
        qs($db, $quote['converted_order_no'] ?? ''),
        qs($db, $adminMemo),
        qs($db, $customerMemo),
        qs($db, $quote['created_at']),
        qs($db, $quote['updated_at'] ?? $quote['created_at'])
    );

    $sqlStatements[] = $sql;

    if ($execute) {
        $ok = mysqli_query($db, $sql);
        if ($ok) {
            $newId = mysqli_insert_id($db);
            $idMapping[$oldId] = $newId;
            $quotesOk++;
            echo "  [{$oldNo}] → {$newQuoteNo} (id={$newId}) ✅\n";
        } else {
            echo "  [{$oldNo}] → {$newQuoteNo} ❌ " . mysqli_error($db) . "\n";
            $errors++;
        }
    } else {
        $idMapping[$oldId] = 'NEW_' . $quotesOk;
        $quotesOk++;
        echo "  [{$oldNo}] → {$newQuoteNo} [DRY RUN]\n";
    }
}

echo "\n  admin_quotes 변환: {$quotesOk}/{$totalQuotes}건\n";

// ============================================================
// Phase 3: Convert quote_items → admin_quote_items
// ============================================================
echo "\n--- Phase 3: admin_quote_items 변환 ({$totalItems}건) ---\n";

foreach ($allItems as $oldQuoteId => $items) {
    $newQuoteId = $idMapping[$oldQuoteId] ?? null;
    if ($newQuoteId === null) {
        echo "  ⚠️  quote_id={$oldQuoteId} 매핑 없음 → " . count($items) . "건 건너뜀\n";
        continue;
    }

    foreach ($items as $item) {
        // source_data: ensure valid JSON or NULL
        $sourceData = $item['source_data'] ?? null;
        if (empty($sourceData) || $sourceData === 'null') {
            $sourceData = null;
        }

        // quantity_display: generate from quantity + unit
        $qty = $item['quantity'] ?? 1;
        $unit = $item['unit'] ?? '개';
        if ($unit === '연') {
            $sheets = (int)((float)$qty * 4000);
            $qtyDisplay = "{$qty}{$unit} ({$sheets}매)";
        } else {
            $qtyDisplay = number_format((float)$qty, 0) . $unit;
        }

        $itemSql = sprintf(
            "INSERT INTO admin_quote_items (
                quote_id, item_no, source_type, product_type, product_name, specification,
                quantity, unit, quantity_display, unit_price, supply_price, source_data, notes, created_at
            ) VALUES (
                %s, %d, %s, %s, %s, %s,
                %s, %s, %s, %s, %d, %s, %s, %s
            )",
            $execute ? (int)$newQuoteId : "'{$newQuoteId}'",
            (int)($item['item_no'] ?? 1),
            qs($db, $item['source_type'] ?? 'manual'),
            qs($db, $item['product_type'] ?? ''),
            qs($db, $item['product_name'] ?? ''),
            qs($db, $item['specification'] ?? ''),
            qs($db, $qty),
            qs($db, $unit),
            qs($db, $qtyDisplay),
            qs($db, $item['unit_price'] ?? 0),
            (int)($item['supply_price'] ?? 0),
            $sourceData ? qs($db, $sourceData) : 'NULL',
            qs($db, $item['notes'] ?? ''),
            qs($db, $item['created_at'])
        );

        $sqlStatements[] = $itemSql;

        if ($execute) {
            $ok = mysqli_query($db, $itemSql);
            if ($ok) {
                $itemsOk++;
            } else {
                echo "  ❌ item(quote_id={$oldQuoteId}, no={$item['item_no']}): " . mysqli_error($db) . "\n";
                $errors++;
            }
        } else {
            $itemsOk++;
        }
    }
}

echo "  admin_quote_items 변환: {$itemsOk}/{$totalItems}건\n";

// ============================================================
// Phase 4: Commit / Save
// ============================================================
if ($execute) {
    if ($errors === 0) {
        mysqli_commit($db);
        echo "\n✅ 트랜잭션 커밋 완료\n";
    } else {
        mysqli_rollback($db);
        echo "\n❌ 오류 {$errors}건 → 롤백\n";
    }
}

if ($sqlFile) {
    $sqlOutput = "-- 견적 데이터 변환 SQL v2 (QT- → AQ-)\n";
    $sqlOutput .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sqlOutput .= "-- Quotes: {$totalQuotes}, Items: {$totalItems}\n\n";
    $sqlOutput .= "SET NAMES utf8mb4;\n\n";
    $sqlOutput .= "-- Phase 0: 잘못된 마이그레이션 데이터 정리\n";
    $sqlOutput .= "DELETE FROM admin_quote_items WHERE quote_id IN (SELECT id FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%');\n";
    $sqlOutput .= "DELETE FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%';\n\n";
    $sqlOutput .= "-- Phase 2+3: 변환 INSERT\n";
    foreach ($sqlStatements as $s) {
        $sqlOutput .= $s . ";\n";
    }
    $outFile = __DIR__ . '/migrate_qt_to_aq_' . date('Ymd_His') . '.sql';
    file_put_contents($outFile, $sqlOutput);
    echo "\nSQL 파일 저장: {$outFile}\n";
}

// ============================================================
// Summary + Verification
// ============================================================
echo "\n=== 변환 결과 ===\n";
echo "admin_quotes:      {$quotesOk}/{$totalQuotes}건\n";
echo "admin_quote_items: {$itemsOk}/{$totalItems}건\n";
echo "오류:              {$errors}건\n";

if ($execute) {
    $aqCnt = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quotes"))['c'];
    $aiCnt = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quote_items"))['c'];
    echo "\n최종 DB 레코드:\n";
    echo "  admin_quotes:      {$aqCnt}건 (기존 {$existingCount} + 신규 {$quotesOk})\n";
    echo "  admin_quote_items: {$aiCnt}건\n";
}

mysqli_close($db);

// Helper
function qs($db, $value) {
    if ($value === null || $value === '') {
        return "''";
    }
    return "'" . mysqli_real_escape_string($db, $value) . "'";
}
