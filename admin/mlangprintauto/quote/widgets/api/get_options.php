<?php
/**
 * Shared AJAX endpoint for quote widget cascading dropdowns.
 * Returns child options from mlangprintauto_transactioncate.
 *
 * GET ?table=namecard&parent=0           → root-level options (BigNo='0')
 * GET ?table=namecard&parent=275         → children by BigNo (default)
 * GET ?table=inserted&parent=802&lookup=TreeNo → children by TreeNo
 *     (for L2 items where BigNo is empty and TreeNo links to root)
 *
 * GET ?table=littleprint&source=price&parent=590&field=Section
 *     → DISTINCT values from price table (for products that don't use transactioncate for all levels)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
mysqli_set_charset($db, 'utf8');

$tableMap = [
    'inserted'        => 'inserted',
    'namecard'        => 'NameCard',
    'envelope'        => 'Envelope',
    'littleprint'     => 'LittlePrint',
    'merchandisebond' => 'MerchandiseBond',
    'cadarok'         => 'Cadarok',
    'ncrflambeau'     => 'NcrFlambeau',
    'msticker'        => 'Msticker',
];

// Price table mapping (for DISTINCT queries from price tables)
$priceTableMap = [
    'inserted'        => 'mlangprintauto_inserted',
    'namecard'        => 'mlangprintauto_namecard',
    'envelope'        => 'mlangprintauto_envelope',
    'littleprint'     => 'mlangprintauto_littleprint',
    'merchandisebond' => 'mlangprintauto_merchandisebond',
    'cadarok'         => 'mlangprintauto_cadarok',
    'ncrflambeau'     => 'mlangprintauto_ncrflambeau',
    'msticker'        => 'mlangprintauto_msticker',
];

$table  = $_GET['table'] ?? '';
$parent = intval($_GET['parent'] ?? 0);
$lookup = $_GET['lookup'] ?? 'BigNo';  // BigNo (default) or TreeNo
$source = $_GET['source'] ?? 'cate';   // cate (default) or price

if (!isset($tableMap[$table])) {
    echo json_encode(['error' => 'Invalid table']);
    exit;
}

// Validate lookup column (whitelist)
if (!in_array($lookup, ['BigNo', 'TreeNo'], true)) {
    $lookup = 'BigNo';
}

$options = [];

if ($source === 'price') {
    // Query DISTINCT values from price table
    // Used for: littleprint L1/L2, and quantity loading for all products
    $field = $_GET['field'] ?? '';
    $allowedFields = ['style', 'Section', 'TreeSelect', 'POtype', 'quantity', 'quantityTwo'];
    
    if (!in_array($field, $allowedFields, true)) {
        echo json_encode(['error' => 'Invalid field']);
        exit;
    }
    
    $priceTable = mysqli_real_escape_string($db, $priceTableMap[$table]);
    $field = mysqli_real_escape_string($db, $field);
    
    // Build WHERE clause from filter params
    $where = [];
    $filterFields = ['style', 'Section', 'TreeSelect', 'POtype'];
    foreach ($filterFields as $f) {
        if (isset($_GET['filter_' . $f]) && $_GET['filter_' . $f] !== '') {
            $val = mysqli_real_escape_string($db, $_GET['filter_' . $f]);
            $where[] = "`{$f}` = '{$val}'";
        }
    }
    
    $whereClause = count($where) > 0 ? ' WHERE ' . implode(' AND ', $where) : '';
    $query = "SELECT DISTINCT `{$field}` FROM `{$priceTable}`{$whereClause} ORDER BY CAST(`{$field}` AS UNSIGNED), `{$field}`";
    $result = mysqli_query($db, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $val = $row[$field];
            if ($val !== '' && $val !== null) {
                $options[] = ['no' => $val, 'title' => $val];
            }
        }
    }
} else {
    // Query from transactioncate table (default)
    $ttable = mysqli_real_escape_string($db, $tableMap[$table]);
    
    if ($lookup === 'TreeNo') {
        // TreeNo lookup: find items where TreeNo matches parent AND BigNo is empty
        // Used for L2 items in inserted, ncrflambeau, littleprint
        $query = "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='{$ttable}' AND TreeNo='{$parent}' AND (BigNo='' OR BigNo IS NULL) ORDER BY TreeNo, no";
    } else {
        // BigNo lookup (default): standard parent-child relationship
        $query = "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='{$ttable}' AND BigNo='{$parent}' ORDER BY TreeNo, no";
    }
    
    $result = mysqli_query($db, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = ['no' => intval($row['no']), 'title' => $row['title']];
        }
    }
}

echo json_encode($options, JSON_UNESCAPED_UNICODE);
