<?php
/**
 * Sticker Price API
 * 스티커 가격 수정 API
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

// POST 요청만 처리
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방식입니다.']);
    exit;
}

// 스티커 타입별 테이블 및 필드 매핑
$stickerTypes = [
    'd1' => ['table' => 'shop_d1', 'prefix' => 'il'],
    'd2' => ['table' => 'shop_d2', 'prefix' => 'ka'],
    'd3' => ['table' => 'shop_d3', 'prefix' => 'sp'],
    'd4' => ['table' => 'shop_d4', 'prefix' => 'ck'],
];

// 업데이트할 데이터 분류
$updateData = [
    'd1' => [],
    'd2' => [],
    'd3' => [],
    'd4' => [],
];

foreach ($_POST as $key => $value) {
    // 각 타입의 필드 확인
    foreach ($stickerTypes as $typeKey => $typeInfo) {
        $prefix = $typeInfo['prefix'];
        // il0, il1, ... 형식의 필드명 확인
        if (strpos($key, $prefix) === 0) {
            $updateData[$typeKey][$key] = intval($value);
            break;
        }
    }
}

// 트랜잭션 시작
mysqli_begin_transaction($db);

try {
    $successCount = 0;

    foreach ($updateData as $typeKey => $fields) {
        if (empty($fields)) continue;

        $table = $stickerTypes[$typeKey]['table'];
        $setParts = [];

        foreach ($fields as $fieldName => $value) {
            $setParts[] = "{$fieldName} = {$value}";
        }

        if (!empty($setParts)) {
            $setQuery = implode(', ', $setParts);
            $query = "UPDATE {$table} SET {$setQuery}";
            $result = mysqli_query($db, $query);

            if (!$result) {
                throw new Exception("{$table} 업데이트 실패: " . mysqli_error($db));
            }
            $successCount++;
        }
    }

    mysqli_commit($db);

    echo json_encode([
        'success' => true,
        'message' => "{$successCount}개 테이블이 성공적으로 업데이트되었습니다."
    ]);

} catch (Exception $e) {
    mysqli_rollback($db);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
