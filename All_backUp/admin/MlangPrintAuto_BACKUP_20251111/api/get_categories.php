<?php
/**
 * TransactionCate 카테고리 조회 API
 * 품목별 드롭다운 옵션 제공
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../db.php';
require_once '../includes/ProductConfig.php';

// 입력 받기
$product = $_GET['product'] ?? '';
$level = intval($_GET['level'] ?? 1);
$parent_id = intval($_GET['parent_id'] ?? 0);

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

$trans_table = $config['trans_table'];

// 쿼리 타입 결정 (BigNo 또는 TreeNo)
$query_type = 'BigNo'; // 기본값
if (isset($config['selector_query_type']) && isset($config['selector_query_type'][$level - 1])) {
    $query_type = $config['selector_query_type'][$level - 1];
}

// TransactionCate에서 카테고리 조회
if ($query_type === 'TreeNo') {
    // Level 2 for products like 전단지, 카다록: WHERE TreeNo = parent_id
    $query = "SELECT no, title FROM mlangprintauto_transactioncate
              WHERE Ttable = ? AND TreeNo = ?
              ORDER BY no ASC";
    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'DB 오류: ' . mysqli_error($db)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "si", $trans_table, $parent_id);
} else {
    // Level 1 and Level 3 (default): WHERE BigNo = parent_id
    if ($parent_id === 0) {
        // Level 1: BigNo = 0 AND TreeNo IS NULL or TreeNo = ''
        $query = "SELECT no, title FROM mlangprintauto_transactioncate
                  WHERE Ttable = ? AND BigNo = ? AND (TreeNo IS NULL OR TreeNo = '' OR TreeNo = 0)
                  ORDER BY no ASC";
    } else {
        // Level 3: BigNo = parent_id AND TreeNo IS NULL or TreeNo = ''
        $query = "SELECT no, title FROM mlangprintauto_transactioncate
                  WHERE Ttable = ? AND BigNo = ? AND (TreeNo IS NULL OR TreeNo = '' OR TreeNo = 0)
                  ORDER BY no ASC";
    }

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'DB 오류: ' . mysqli_error($db)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "si", $trans_table, $parent_id);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = [
        'no' => $row['no'],
        'title' => $row['title']
    ];
}

mysqli_stmt_close($stmt);
mysqli_close($db);

echo json_encode([
    'success' => true,
    'categories' => $categories,
    'level' => $level
], JSON_UNESCAPED_UNICODE);
?>
