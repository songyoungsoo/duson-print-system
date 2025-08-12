<?php
/**
 * 주문 데이터 직접 확인
 */

include "../../db.php";

$no = $_GET['no'] ?? 83223;

echo "<h2>주문 $no 데이터 직접 확인</h2>";

// 주문 정보 조회
$query = "SELECT * FROM MlangOrder_PrintAuto WHERE no = $no";
$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    echo "<h3>Type_1 필드 내용:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($row['Type_1'] ?? '[비어있음]');
    echo "</pre>";
    
    echo "<h3>모든 필드 내용:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>필드명</th><th>값</th><th>길이</th></tr>";
    
    foreach ($row as $key => $value) {
        $display_value = htmlspecialchars($value ?? '');
        $length = strlen($value ?? '');
        
        if (empty($value)) {
            $display_value = '<span style="color: red;">[비어있음]</span>';
        } elseif (strlen($value) > 100) {
            $display_value = htmlspecialchars(substr($value, 0, 100)) . '... <span style="color: blue;">[더보기]</span>';
        }
        
        echo "<tr>";
        echo "<td><strong>$key</strong></td>";
        echo "<td>$display_value</td>";
        echo "<td>$length</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 주문이 언제 생성되었는지 확인
    echo "<h3>주문 생성 정보:</h3>";
    echo "<p><strong>주문 날짜:</strong> " . ($row['date'] ?? '없음') . "</p>";
    echo "<p><strong>주문 상태:</strong> " . ($row['OrderStyle'] ?? '없음') . "</p>";
    echo "<p><strong>상품 유형:</strong> " . ($row['Type'] ?? '없음') . "</p>";
    
} else {
    echo "<p style='color: red;'>주문 $no 을 찾을 수 없습니다.</p>";
    
    // 최근 주문들 확인
    echo "<h3>최근 주문 10개:</h3>";
    $recent_query = "SELECT no, Type, name, date, OrderStyle FROM MlangOrder_PrintAuto ORDER BY no DESC LIMIT 10";
    $recent_result = mysqli_query($db, $recent_query);
    
    if ($recent_result) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>주문번호</th><th>상품유형</th><th>주문자</th><th>날짜</th><th>상태</th></tr>";
        
        while ($recent_row = mysqli_fetch_assoc($recent_result)) {
            echo "<tr>";
            echo "<td><a href='?no=" . $recent_row['no'] . "'>" . $recent_row['no'] . "</a></td>";
            echo "<td>" . htmlspecialchars($recent_row['Type'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($recent_row['name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($recent_row['date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($recent_row['OrderStyle'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

mysqli_close($db);
?>