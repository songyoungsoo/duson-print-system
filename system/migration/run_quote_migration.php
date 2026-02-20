<?php
/**
 * 프로덕션용 견적서 마이그레이션 실행 스크립트
 * FTP로 업로드 후 브라우저에서 ?key=migrate2026 으로 실행
 */
if (($_GET['key'] ?? '') !== 'migrate2026') {
    die('Unauthorized. Use ?key=migrate2026');
}

require_once __DIR__ . '/../../db.php';

header('Content-Type: text/plain; charset=utf-8');

$sqlFile = __DIR__ . '/migrate_quotes_data.sql';
if (!file_exists($sqlFile)) {
    die("ERROR: SQL file not found: {$sqlFile}");
}

$action = $_GET['action'] ?? 'run';

if ($action === 'check') {
    $r = mysqli_query($db, "SELECT id, quote_no, customer_name, customer_company, grand_total, status, (SELECT COUNT(*) FROM quote_items WHERE quote_id = quotes.id) as items FROM quotes WHERE quote_no LIKE 'QT-202511%' OR quote_no LIKE 'QT-202512%' OR quote_no LIKE 'QT-202601%' ORDER BY created_at DESC");
    echo "Existing QT- quotes (2025-11 ~ 2026-01): " . mysqli_num_rows($r) . "\n\n";
    while ($row = mysqli_fetch_assoc($r)) {
        printf("id=%d | %s | %s | %s | %s원 | %s | %d items\n",
            $row['id'], $row['quote_no'], $row['customer_name'], $row['customer_company'],
            number_format($row['grand_total']), $row['status'], $row['items']);
    }
    $total = mysqli_query($db, "SELECT COUNT(*) as cnt FROM quotes");
    echo "\nTotal quotes: " . mysqli_fetch_assoc($total)['cnt'] . "\n";
    $totalItems = mysqli_query($db, "SELECT COUNT(*) as cnt FROM quote_items");
    echo "Total quote_items: " . mysqli_fetch_assoc($totalItems)['cnt'] . "\n";
    mysqli_close($db);
    exit;
}

$existingCheck = mysqli_query($db, "SELECT COUNT(*) as cnt FROM quotes WHERE quote_no LIKE 'QT-202511%' OR quote_no LIKE 'QT-202512%' OR quote_no LIKE 'QT-202601%'");
$existing = mysqli_fetch_assoc($existingCheck);
if ($existing['cnt'] > 0 && $action !== 'force') {
    die("ABORT: {$existing['cnt']} QT- quotes from 2025-11~2026-01 already exist. Use ?key=migrate2026&action=check to inspect, or &action=force to overwrite.");
}

$sql = file_get_contents($sqlFile);
$statements = array_filter(array_map('trim', explode(";\n", $sql)), function($s) {
    return !empty($s) && strpos($s, '--') !== 0 && strtoupper($s) !== 'SET NAMES UTF8MB4';
});

mysqli_query($db, "SET NAMES utf8mb4");

$success = 0;
$errors = 0;

foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    
    $result = mysqli_query($db, $stmt);
    if ($result) {
        $success++;
        if (stripos($stmt, 'INSERT INTO quotes ') !== false) {
            $insertId = mysqli_insert_id($db);
            echo "OK: quotes INSERT id={$insertId}\n";
        }
    } else {
        $errors++;
        echo "ERROR: " . mysqli_error($db) . "\n";
        echo "  SQL: " . substr($stmt, 0, 120) . "...\n";
    }
}

echo "\n=== DONE ===\n";
echo "Success: {$success}\n";
echo "Errors: {$errors}\n";

$finalCheck = mysqli_query($db, "SELECT COUNT(*) as cnt FROM quotes WHERE quote_no LIKE 'QT-202511%' OR quote_no LIKE 'QT-202512%' OR quote_no LIKE 'QT-202601%'");
$final = mysqli_fetch_assoc($finalCheck);
echo "Migrated QT- quotes: {$final['cnt']}\n";

mysqli_close($db);
