<?php
/**
 * get_materials.php - 명함 재질 동적 로딩 AJAX 엔드포인트
 *
 * 선택된 명함 종류에 따라 해당 재질 목록을 JSON으로 반환
 */

header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
require_once '../../db.php';

// 입력 검증
$type_no = isset($_GET['type']) ? intval($_GET['type']) : 0;

if ($type_no <= 0) {
    echo json_encode([
        'success' => false,
        'error' => '유효하지 않은 명함 종류입니다.',
        'materials' => []
    ]);
    exit;
}

try {
    // 재질 목록 조회
    $query = "SELECT no, title
              FROM mlangprintauto_transactioncate
              WHERE Ttable = 'NameCard'
                AND BigNo = ?
              ORDER BY no ASC";

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, "i", $type_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $materials = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = [
            'no' => $row['no'],
            'title' => $row['title']
        ];
    }

    mysqli_stmt_close($stmt);

    // 성공 응답
    echo json_encode([
        'success' => true,
        'materials' => $materials,
        'count' => count($materials)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // 에러 응답
    error_log("get_materials.php Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '재질 목록을 불러오는 중 오류가 발생했습니다.',
        'materials' => []
    ], JSON_UNESCAPED_UNICODE);
}
