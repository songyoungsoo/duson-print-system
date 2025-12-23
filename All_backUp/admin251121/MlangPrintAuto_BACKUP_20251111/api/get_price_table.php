<?php
/**
 * 가격표 조회 API
 * 필터링된 가격 데이터 반환
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../db.php';
require_once '../includes/ProductConfig.php';

// 입력 받기
$product = $_GET['product'] ?? '';
$selector1 = $_GET['selector1'] ?? '';
$selector2 = $_GET['selector2'] ?? '';
$selector3 = $_GET['selector3'] ?? '';

// 품목 검증
if (!$product) {
    echo json_encode([
        'success' => false,
        'message' => '품목을 선택해주세요'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$config = ProductConfig::getConfig($product);
if (!$config) {
    echo json_encode([
        'success' => false,
        'message' => '잘못된 품목입니다'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$table = $config['table'];
$cols = $config['columns'];

// WHERE 절 구성
$where_conditions = [];
$params = [];
$types = '';

if ($selector1) {
    $where_conditions[] = "{$cols['selector1']} = ?";
    $params[] = $selector1;
    $types .= 'i';
}
if ($selector2) {
    $where_conditions[] = "{$cols['selector2']} = ?";
    $params[] = $selector2;
    $types .= 'i';
}
if ($selector3 && isset($cols['selector3'])) {
    $where_conditions[] = "{$cols['selector3']} = ?";
    $params[] = $selector3;
    $types .= 'i';
}

$where_sql = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 가격표 조회
$select_cols = [
    "{$cols['id']} as id",
    "{$cols['selector1']} as selector1",
    "{$cols['selector2']} as selector2"
];

if (isset($cols['selector3'])) {
    $select_cols[] = "{$cols['selector3']} as selector3";
}

$select_cols[] = "{$cols['quantity']} as quantity";
$select_cols[] = "{$cols['price_single']} as price_single";
$select_cols[] = "{$cols['price_double']} as price_double";

$query = "SELECT " . implode(', ', $select_cols) . "
          FROM {$table}
          {$where_sql}
          ORDER BY
            {$cols['selector1']} ASC,
            {$cols['selector2']} ASC,
            " . (isset($cols['selector3']) ? "{$cols['selector3']} ASC," : "") . "
            CAST({$cols['quantity']} AS UNSIGNED) ASC";

$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'DB 오류: ' . mysqli_error($db),
        'debug' => [
            'product' => $product,
            'table' => $table,
            'query' => $query
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // TransCate에서 한글 이름 조회
    $row['selector1_name'] = getCategoryName($db, $config['trans_table'], $row['selector1']);
    $row['selector2_name'] = getCategoryName($db, $config['trans_table'], $row['selector2']);

    if (isset($row['selector3'])) {
        $row['selector3_name'] = getCategoryName($db, $config['trans_table'], $row['selector3']);
    }

    $data[] = $row;
}

mysqli_stmt_close($stmt);

// 컬럼 헤더
$columns = $config['selector_labels'];
$columns[] = '수량';
$columns[] = '금액';
$columns[] = '편집비';

echo json_encode([
    'success' => true,
    'data' => $data,
    'columns' => $columns,
    'total' => count($data)
], JSON_UNESCAPED_UNICODE);

mysqli_close($db);

/**
 * 카테고리 한글 이름 조회 헬퍼
 */
function getCategoryName($db, $trans_table, $no) {
    $query = "SELECT title FROM mlangprintauto_transactioncate
              WHERE Ttable = ? AND no = ?";
    $stmt = mysqli_prepare($db, $query);

    if (!$stmt) {
        return '알 수 없음';
    }

    mysqli_stmt_bind_param($stmt, "si", $trans_table, $no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row['title'] ?? '알 수 없음';
}
?>
