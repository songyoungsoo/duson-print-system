<?php
/**
 * Shared AJAX endpoint for quote widget cascading dropdowns.
 * Returns child options from mlangprintauto_transactioncate.
 *
 * GET ?table=namecard&parent=0  → root-level options
 * GET ?table=namecard&parent=275 → children of Tno=275
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

$table = $_GET['table'] ?? '';
$parent = intval($_GET['parent'] ?? 0);

if (!isset($tableMap[$table])) {
    echo json_encode(['error' => 'Invalid table']);
    exit;
}

$ttable = mysqli_real_escape_string($db, $tableMap[$table]);
$query = "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='{$ttable}' AND BigNo='{$parent}' ORDER BY TreeNo, no";
$result = mysqli_query($db, $query);

$options = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $options[] = ['no' => intval($row['no']), 'title' => $row['title']];
    }
}

echo json_encode($options, JSON_UNESCAPED_UNICODE);
