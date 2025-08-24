<?php
/**
 * ThingCate 필드와 파일 경로 디버깅
 */
session_start();
require_once dirname(__DIR__) . "/db.php";

if (!isset($connect) && isset($db)) {
    $connect = $db;
}

if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>ThingCate 디버깅</title></head>";
echo "<body style='font-family: Noto Sans KR, sans-serif; margin: 20px;'>";

echo "<h1>🔍 ThingCate 필드 및 파일 경로 디버깅</h1>";

// 교정 및 완성된 주문 중 ThingCate가 있는 것들 조회
$sql = "SELECT No, Type, ThingCate, name, OrderStyle, date 
        FROM MlangOrder_PrintAuto 
        WHERE OrderStyle IN ('6', '7', '8') 
        AND ThingCate IS NOT NULL 
        AND ThingCate != ''
        ORDER BY No DESC 
        LIMIT 20";

$result = mysqli_query($connect, $sql);

if ($result) {
    echo "<h2>📋 최신 20개 주문 (ThingCate 있음)</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>
            <th>주문번호</th>
            <th>타입</th>
            <th>ThingCate</th>
            <th>고객명</th>
            <th>상태</th>
            <th>파일 경로</th>
            <th>파일 존재</th>
            <th>이미지 테스트</th>
          </tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $order_no = $row['No'];
        $thing_cate = $row['ThingCate'];
        $order_style = $row['OrderStyle'];
        
        // 상태 이름 매핑
        $status_names = [
            '6' => '시안완료',
            '7' => '교정중',
            '8' => '작업완료'
        ];
        $status_name = $status_names[$order_style] ?? $order_style;
        
        // 이미지 파일 경로 생성 (WindowSian.php 방식)
        $image_path = "/MlangOrder_PrintAuto/upload/{$order_no}/{$thing_cate}";
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
        
        $file_exists = file_exists($file_path);
        $exists_text = $file_exists ? "✅ 존재" : "❌ 없음";
        $exists_color = $file_exists ? "green" : "red";
        
        echo "<tr>";
        echo "<td>{$order_no}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td style='font-size: 0.8rem;'>{$thing_cate}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$status_name}</td>";
        echo "<td style='font-size: 0.8rem;'>{$image_path}</td>";
        echo "<td style='color: {$exists_color}; font-weight: bold;'>{$exists_text}</td>";
        
        if ($file_exists) {
            echo "<td><img src='{$image_path}' style='max-width: 100px; max-height: 80px; border: 1px solid #ccc;' alt='테스트'></td>";
        } else {
            echo "<td>-</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // 통계
    $stats_sql = "SELECT COUNT(*) as total,
                         SUM(CASE WHEN ThingCate IS NOT NULL AND ThingCate != '' THEN 1 ELSE 0 END) as has_thingcate
                  FROM MlangOrder_PrintAuto 
                  WHERE OrderStyle IN ('6', '7', '8')";
    $stats_result = mysqli_query($connect, $stats_sql);
    $stats = mysqli_fetch_assoc($stats_result);
    
    echo "<h2>📊 통계</h2>";
    echo "<ul>";
    echo "<li><strong>전체 교정/완성 주문:</strong> " . number_format($stats['total']) . "건</li>";
    echo "<li><strong>ThingCate 있는 주문:</strong> " . number_format($stats['has_thingcate']) . "건</li>";
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>SQL 실행 실패: " . mysqli_error($connect) . "</p>";
}

echo "</body></html>";
?>