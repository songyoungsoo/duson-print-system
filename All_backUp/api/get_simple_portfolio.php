<?php
/**
 * 간단한 포트폴리오 API - 오류 없는 안전한 버전
 */

header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../db.php";

if (!$db) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    // 파라미터 처리
    $category = isset($_GET['category']) ? $_GET['category'] : 'inserted';
    $perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 4;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $perPage;
    
    // 안전한 쿼리
    $query = "SELECT no, ThingCate, Type, name, date 
              FROM mlangorder_printauto 
              WHERE ThingCate IS NOT NULL 
              AND ThingCate != '' 
              AND LENGTH(ThingCate) > 3
              AND (Type = '전단지' OR Type LIKE '%전단%')
              AND date >= '2020-01-01'
              ORDER BY date DESC 
              LIMIT " . intval($perPage) . " OFFSET " . intval($offset);
    
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        echo json_encode([
            'success' => false, 
            'error' => 'Query failed: ' . mysqli_error($db),
            'query' => $query
        ]);
        exit;
    }
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orderNo = $row['no'];
        $imageFile = $row['ThingCate'];
        $imagePath = "/mlangorder_printauto/upload/$orderNo/$imageFile";
        
        // 고객명 마스킹
        $customerName = $row['name'] ?? '';
        $maskedName = mb_substr($customerName, 0, 1) . str_repeat('*', max(0, mb_strlen($customerName) - 1));
        
        $images[] = [
            'id' => 'real_' . $orderNo,
            'title' => $maskedName . "님의 전단지 작품",
            'path' => $imagePath,
            'image_path' => $imagePath,
            'thumbnail' => $imagePath,
            'thumb' => $imagePath,
            'url' => $imagePath,
            'full' => $imagePath,
            'order_no' => $orderNo,
            'filename' => $imageFile,
            'date' => $row['date'],
            'type' => $row['Type']
        ];
    }
    
    // 응답
    echo json_encode([
        'success' => true,
        'data' => $images,
        'images' => $images,
        'count' => count($images),
        'category' => $category,
        'page' => $page,
        'per_page' => $perPage,
        'total_items' => count($images),
        'source' => 'simple_api'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if ($db) {
        mysqli_close($db);
    }
}
?>