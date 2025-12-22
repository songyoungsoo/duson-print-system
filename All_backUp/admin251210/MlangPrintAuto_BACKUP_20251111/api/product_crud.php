<?php
/**
 * 품목 가격 CRUD API
 * Create, Read, Update, Delete 작업 처리
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../../db.php';
require_once '../includes/ProductConfig.php';

// JSON 입력 받기
$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? '';
$product = $input['product'] ?? '';
$data = $input['data'] ?? [];
$id = intval($input['id'] ?? 0);

// 품목 검증
if (!$product) {
    errorResponse('품목을 선택해주세요');
}

$config = ProductConfig::getConfig($product);
if (!$config) {
    errorResponse('잘못된 품목입니다');
}

$table = $config['table'];
$cols = $config['columns'];

// 액션별 처리
switch ($action) {
    case 'create':
        handleCreate($db, $table, $cols, $data);
        break;

    case 'update':
        handleUpdate($db, $table, $cols, $data, $id);
        break;

    case 'delete':
        handleDelete($db, $table, $cols, $id);
        break;

    case 'get':
        handleGet($db, $table, $cols, $id);
        break;

    default:
        errorResponse('잘못된 작업입니다');
}

mysqli_close($db);

/**
 * CREATE 처리
 */
function handleCreate($db, $table, $cols, $data) {
    // 필드 구성
    $fields = [
        $cols['selector1'],
        $cols['selector2'],
        $cols['quantity'],
        $cols['price_single'],
        $cols['price_double']
    ];

    if (isset($cols['selector3'])) {
        $fields[] = $cols['selector3'];
    }

    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $query = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        errorResponse('DB 오류: ' . mysqli_error($db));
    }

    // 파라미터 바인딩
    $params = [
        $data['selector1'] ?? '',
        $data['selector2'] ?? '',
        $data['quantity'] ?? '',
        $data['price_single'] ?? 0,
        $data['price_double'] ?? 0
    ];

    if (isset($cols['selector3'])) {
        $params[] = $data['selector3'] ?? '';
    }

    $types = isset($cols['selector3']) ? 'iissii' : 'iisii';
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode([
            'success' => true,
            'message' => '가격이 추가되었습니다',
            'id' => mysqli_insert_id($db)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        mysqli_stmt_close($stmt);
        errorResponse('추가 실패: ' . mysqli_error($db));
    }
}

/**
 * UPDATE 처리
 */
function handleUpdate($db, $table, $cols, $data, $id) {
    if (!$id) {
        errorResponse('ID가 필요합니다');
    }

    // SET 절 구성
    $set_fields = [
        "{$cols['selector1']} = ?",
        "{$cols['selector2']} = ?",
        "{$cols['quantity']} = ?",
        "{$cols['price_single']} = ?",
        "{$cols['price_double']} = ?"
    ];

    if (isset($cols['selector3'])) {
        $set_fields[] = "{$cols['selector3']} = ?";
    }

    $query = "UPDATE {$table} SET " . implode(', ', $set_fields) . " WHERE {$cols['id']} = ?";

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        errorResponse('DB 오류: ' . mysqli_error($db));
    }

    // 파라미터 바인딩
    $params = [
        $data['selector1'] ?? '',
        $data['selector2'] ?? '',
        $data['quantity'] ?? '',
        $data['price_single'] ?? 0,
        $data['price_double'] ?? 0
    ];

    if (isset($cols['selector3'])) {
        $params[] = $data['selector3'] ?? '';
        $types = 'iissiii';
    } else {
        $types = 'iisiii';
    }

    $params[] = $id;

    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode([
            'success' => true,
            'message' => '수정되었습니다'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        mysqli_stmt_close($stmt);
        errorResponse('수정 실패: ' . mysqli_error($db));
    }
}

/**
 * DELETE 처리
 */
function handleDelete($db, $table, $cols, $id) {
    if (!$id) {
        errorResponse('ID가 필요합니다');
    }

    $query = "DELETE FROM {$table} WHERE {$cols['id']} = ?";
    $stmt = mysqli_prepare($db, $query);

    if (!$stmt) {
        errorResponse('DB 오류: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode([
            'success' => true,
            'message' => '삭제되었습니다'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        mysqli_stmt_close($stmt);
        errorResponse('삭제 실패: ' . mysqli_error($db));
    }
}

/**
 * GET (단일 조회) 처리
 */
function handleGet($db, $table, $cols, $id) {
    if (!$id) {
        errorResponse('ID가 필요합니다');
    }

    // 컬럼명에 별칭(alias) 사용하여 일관된 키로 반환
    $select_cols = [
        $cols['id'] . ' as id',
        $cols['selector1'] . ' as selector1',
        $cols['selector2'] . ' as selector2',
        $cols['quantity'] . ' as quantity',
        $cols['price_single'] . ' as price_single',
        $cols['price_double'] . ' as price_double'
    ];

    if (isset($cols['selector3'])) {
        $select_cols[] = $cols['selector3'] . ' as selector3';
    }

    $query = "SELECT " . implode(', ', $select_cols) . " FROM {$table} WHERE {$cols['id']} = ?";

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        errorResponse('DB 오류: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ($row) {
        echo json_encode([
            'success' => true,
            'data' => $row
        ], JSON_UNESCAPED_UNICODE);
    } else {
        errorResponse('데이터를 찾을 수 없습니다');
    }
}

/**
 * 에러 응답 헬퍼
 */
function errorResponse($message) {
    echo json_encode([
        'success' => false,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
