<?php
/**
 * QT→AQ 견적 데이터 변환 (프로덕션 웹 실행기)
 * ?key=migrate2026&action=check  — 현재 상태 확인
 * ?key=migrate2026&action=run    — 변환 실행
 */
if (($_GET['key'] ?? '') !== 'migrate2026') {
    die('Unauthorized');
}

require_once __DIR__ . '/../../db.php';
header('Content-Type: text/plain; charset=utf-8');
echo "=== QT- → AQ- 견적 변환 ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$action = $_GET['action'] ?? 'check';

$aqCount = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quotes"))['c'];
$aqAQCount = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quotes WHERE quote_no LIKE 'AQ-%'"))['c'];
$aqBroken = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%'"))['c'];
$aiCount = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quote_items"))['c'];
$qtCount = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM quotes"))['c'];
$qiCount = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM quote_items"))['c'];

echo "[현재 상태]\n";
echo "  admin_quotes:      {$aqCount}건 (AQ-: {$aqAQCount}, 잘못된 형식: {$aqBroken})\n";
echo "  admin_quote_items: {$aiCount}건\n";
echo "  quotes (QT-):      {$qtCount}건\n";
echo "  quote_items:       {$qiCount}건\n\n";

if ($action === 'check') {
    echo "[AQ- 레코드 목록]\n";
    $r = mysqli_query($db, "SELECT id, quote_no, customer_name, grand_total, status FROM admin_quotes WHERE quote_no LIKE 'AQ-%' ORDER BY id LIMIT 20");
    while ($row = mysqli_fetch_assoc($r)) {
        printf("  id=%d %s %s %s원 %s\n", $row['id'], $row['quote_no'], $row['customer_name'], number_format($row['grand_total']), $row['status']);
    }
    echo "\n[QT- 레코드 목록]\n";
    $r = mysqli_query($db, "SELECT id, quote_no, customer_name, grand_total, status, (SELECT COUNT(*) FROM quote_items WHERE quote_id=quotes.id) as items FROM quotes ORDER BY created_at ASC");
    while ($row = mysqli_fetch_assoc($r)) {
        printf("  id=%d %s %s %s원 %s (%d items)\n", $row['id'], $row['quote_no'], $row['customer_name'], number_format($row['grand_total']), $row['status'], $row['items']);
    }
    echo "\n→ 변환 실행: ?key=migrate2026&action=run\n";
    mysqli_close($db);
    exit;
}

if ($action !== 'run') {
    die("Unknown action: {$action}");
}

// Phase 0: Clean broken data
if ($aqBroken > 0) {
    echo "[Phase 0] 잘못된 형식 {$aqBroken}건 삭제...\n";
    mysqli_query($db, "DELETE FROM admin_quote_items WHERE quote_id IN (SELECT id FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%')");
    mysqli_query($db, "DELETE FROM admin_quotes WHERE quote_no NOT LIKE 'AQ-%'");
    echo "  ✅ 삭제 완료\n\n";
}

// Phase 1: Get existing AQ- max sequences
$existingAQ = [];
$aqResult = mysqli_query($db, "SELECT quote_no FROM admin_quotes WHERE quote_no LIKE 'AQ-%'");
while ($aqResult && $row = mysqli_fetch_assoc($aqResult)) {
    if (preg_match('/^AQ-(\d{8})-(\d{4})$/', $row['quote_no'], $m)) {
        $dateKey = $m[1];
        $seq = (int)$m[2];
        if (!isset($existingAQ[$dateKey]) || $seq > $existingAQ[$dateKey]) {
            $existingAQ[$dateKey] = $seq;
        }
    }
}

// Phase 2: Read quotes + items
$quotes = [];
$r = mysqli_query($db, "SELECT * FROM quotes ORDER BY created_at ASC, id ASC");
while ($r && $row = mysqli_fetch_assoc($r)) { $quotes[] = $row; }

$allItems = [];
$r = mysqli_query($db, "SELECT * FROM quote_items ORDER BY quote_id, item_no");
while ($r && $row = mysqli_fetch_assoc($r)) {
    $allItems[$row['quote_id']][] = $row;
}
$totalItems = array_sum(array_map('count', $allItems));

$cnt = count($quotes);
echo "[Phase 2] quotes {$cnt}건, items {$totalItems}건\n\n";

// Phase 3: Convert
$dateSeqs = $existingAQ;
$idMapping = [];
$quotesOk = 0;
$itemsOk = 0;
$errors = 0;

mysqli_begin_transaction($db);

echo "[Phase 3] admin_quotes 변환...\n";
foreach ($quotes as $q) {
    $oldId = $q['id'];
    $oldNo = $q['quote_no'];
    $createdDate = date('Ymd', strtotime($q['created_at']));
    if (!isset($dateSeqs[$createdDate])) { $dateSeqs[$createdDate] = 0; }
    $dateSeqs[$createdDate]++;
    $newNo = sprintf('AQ-%s-%04d', $createdDate, $dateSeqs[$createdDate]);

    $status = $q['status'] ?? 'draft';
    $validStatuses = ['draft','sent','viewed','accepted','rejected','expired','converted'];
    if (!in_array($status, $validStatuses)) { $status = 'draft'; }

    $validUntil = $q['valid_until'] ?? null;
    if (empty($validUntil) && !empty($q['valid_days'])) {
        $validUntil = date('Y-m-d', strtotime("+{$q['valid_days']} days", strtotime($q['created_at'])));
    }

    $adminMemo = trim(($q['notes'] ?? '') . "\n[원본: {$oldNo}]");
    $customerMemo = $q['customer_notes'] ?? ($q['response_notes'] ?? '');

    $sql = sprintf(
        "INSERT INTO admin_quotes (quote_no, customer_company, customer_name, customer_phone, customer_email, customer_address, supply_total, vat_total, grand_total, status, valid_until, converted_order_no, admin_memo, customer_memo, created_at, updated_at) VALUES (%s,%s,%s,%s,%s,%s,%d,%d,%d,%s,%s,%s,%s,%s,%s,%s)",
        qs($db, $newNo), qs($db, $q['customer_company'] ?? ''), qs($db, $q['customer_name'] ?? ''),
        qs($db, $q['customer_phone'] ?? ''), qs($db, $q['customer_email'] ?? ''),
        qs($db, $q['delivery_address'] ?? ''),
        (int)($q['supply_total'] ?? 0), (int)($q['vat_total'] ?? 0), (int)($q['grand_total'] ?? 0),
        qs($db, $status), $validUntil ? qs($db, $validUntil) : 'NULL',
        qs($db, $q['converted_order_no'] ?? ''), qs($db, $adminMemo), qs($db, $customerMemo),
        qs($db, $q['created_at']), qs($db, $q['updated_at'] ?? $q['created_at'])
    );

    if (mysqli_query($db, $sql)) {
        $idMapping[$oldId] = mysqli_insert_id($db);
        $quotesOk++;
        echo "  {$oldNo} → {$newNo} (id={$idMapping[$oldId]}) ✅\n";
    } else {
        echo "  {$oldNo} → {$newNo} ❌ " . mysqli_error($db) . "\n";
        $errors++;
    }
}

echo "\n[Phase 4] admin_quote_items 변환...\n";
foreach ($allItems as $oldQid => $items) {
    $newQid = $idMapping[$oldQid] ?? null;
    if (!$newQid) { echo "  ⚠️ quote_id={$oldQid} 매핑 없음\n"; continue; }

    foreach ($items as $item) {
        $sourceData = $item['source_data'] ?? null;
        if (empty($sourceData) || $sourceData === 'null') { $sourceData = null; }

        $qty = $item['quantity'] ?? 1;
        $unit = $item['unit'] ?? '개';
        $qtyDisplay = ($unit === '연') ? "{$qty}{$unit} (" . (int)((float)$qty * 4000) . "매)" : number_format((float)$qty, 0) . $unit;

        $sql = sprintf(
            "INSERT INTO admin_quote_items (quote_id, item_no, source_type, product_type, product_name, specification, quantity, unit, quantity_display, unit_price, supply_price, source_data, notes, created_at) VALUES (%d,%d,%s,%s,%s,%s,%s,%s,%s,%s,%d,%s,%s,%s)",
            (int)$newQid, (int)($item['item_no'] ?? 1),
            qs($db, $item['source_type'] ?? 'manual'), qs($db, $item['product_type'] ?? ''),
            qs($db, $item['product_name'] ?? ''), qs($db, $item['specification'] ?? ''),
            qs($db, $qty), qs($db, $unit), qs($db, $qtyDisplay),
            qs($db, $item['unit_price'] ?? 0), (int)($item['supply_price'] ?? 0),
            $sourceData ? qs($db, $sourceData) : 'NULL',
            qs($db, $item['notes'] ?? ''), qs($db, $item['created_at'])
        );

        if (mysqli_query($db, $sql)) { $itemsOk++; }
        else { echo "  ❌ item: " . mysqli_error($db) . "\n"; $errors++; }
    }
}

if ($errors === 0) {
    mysqli_commit($db);
    echo "\n✅ 커밋 완료\n";
} else {
    mysqli_rollback($db);
    echo "\n❌ 오류 {$errors}건 → 롤백\n";
}

$finalAQ = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quotes"))['c'];
$finalAI = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM admin_quote_items"))['c'];
echo "\n=== 결과 ===\n";
echo "admin_quotes: {$quotesOk}/" . count($quotes) . "건, admin_quote_items: {$itemsOk}/{$totalItems}건, 오류: {$errors}\n";
echo "최종: admin_quotes {$finalAQ}건, admin_quote_items {$finalAI}건\n";
echo "\n⚠️ 실행 후 이 파일을 프로덕션에서 삭제하세요!\n";

mysqli_close($db);

function qs($db, $v) {
    if ($v === null || $v === '') return "''";
    return "'" . mysqli_real_escape_string($db, $v) . "'";
}
