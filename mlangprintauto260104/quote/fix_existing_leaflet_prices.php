<?php
/**
 * 기존 전단지 견적 아이템의 단가를 재계산
 * INT로 저장된 단가를 DECIMAL로 재계산하여 업데이트
 */

require_once __DIR__ . '/../../db.php';

echo "<h2>기존 전단지 견적 단가 재계산</h2>";
echo "<p>전단지(inserted) 제품의 단가를 매수(mesu) 기준으로 재계산합니다.</p>";
echo "<hr>";

// Step 1: 전단지 아이템 조회 (source_data에 mesu가 있는 경우)
$query = "SELECT id, quote_id, product_name, unit_price, total_price, source_data
          FROM quote_items
          WHERE product_type = 'inserted'
          AND source_data IS NOT NULL
          AND source_data != ''
          ORDER BY id DESC";

$result = mysqli_query($db, $query);
$total = mysqli_num_rows($result);

echo "<p><strong>발견된 전단지 아이템:</strong> {$total}개</p>";

$updated = 0;
$skipped = 0;
$errors = 0;

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 20px;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th>ID</th><th>견적번호</th><th>제품명</th><th>기존 단가</th><th>새 단가</th><th>상태</th>";
echo "</tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $item_id = $row['id'];
    $quote_id = $row['quote_id'];
    $product_name = htmlspecialchars($row['product_name']);
    $old_unit_price = $row['unit_price'];
    $total_price = $row['total_price'];
    $source_data = json_decode($row['source_data'], true);

    // mesu 값 추출
    $mesu = isset($source_data['mesu']) ? intval($source_data['mesu']) : 0;

    if ($mesu > 0) {
        // 공급가액 계산 (total_price / 1.1)
        $supply_price = round($total_price / 1.1);

        // 새 단가 계산 (소수점 1자리)
        $new_unit_price = round($supply_price / $mesu, 1);

        // 단가가 변경된 경우만 업데이트
        if (abs($old_unit_price - $new_unit_price) > 0.01) {
            $update_query = "UPDATE quote_items SET unit_price = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $update_query);
            mysqli_stmt_bind_param($stmt, "di", $new_unit_price, $item_id);

            if (mysqli_stmt_execute($stmt)) {
                echo "<tr style='background: #d4edda;'>";
                echo "<td>{$item_id}</td>";
                echo "<td>{$quote_id}</td>";
                echo "<td>{$product_name}</td>";
                echo "<td>" . number_format($old_unit_price, 1) . "</td>";
                echo "<td><strong>" . number_format($new_unit_price, 1) . "</strong></td>";
                echo "<td style='color: green;'>✅ 업데이트</td>";
                echo "</tr>";
                $updated++;
            } else {
                echo "<tr style='background: #f8d7da;'>";
                echo "<td>{$item_id}</td>";
                echo "<td>{$quote_id}</td>";
                echo "<td>{$product_name}</td>";
                echo "<td>" . number_format($old_unit_price, 1) . "</td>";
                echo "<td>-</td>";
                echo "<td style='color: red;'>❌ 오류</td>";
                echo "</tr>";
                $errors++;
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<tr style='background: #fff3cd;'>";
            echo "<td>{$item_id}</td>";
            echo "<td>{$quote_id}</td>";
            echo "<td>{$product_name}</td>";
            echo "<td>" . number_format($old_unit_price, 1) . "</td>";
            echo "<td>" . number_format($new_unit_price, 1) . "</td>";
            echo "<td style='color: orange;'>⏭️ 동일함</td>";
            echo "</tr>";
            $skipped++;
        }
    } else {
        echo "<tr style='background: #e2e3e5;'>";
        echo "<td>{$item_id}</td>";
        echo "<td>{$quote_id}</td>";
        echo "<td>{$product_name}</td>";
        echo "<td>" . number_format($old_unit_price, 1) . "</td>";
        echo "<td>-</td>";
        echo "<td style='color: gray;'>⚠️ mesu 없음</td>";
        echo "</tr>";
        $skipped++;
    }
}

echo "</table>";

echo "<hr>";
echo "<h3>처리 결과</h3>";
echo "<ul>";
echo "<li><strong>전체:</strong> {$total}개</li>";
echo "<li style='color: green;'><strong>업데이트:</strong> {$updated}개</li>";
echo "<li style='color: orange;'><strong>건너뜀:</strong> {$skipped}개</li>";
echo "<li style='color: red;'><strong>오류:</strong> {$errors}개</li>";
echo "</ul>";

if ($updated > 0) {
    echo "<p style='background: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "✅ <strong>완료!</strong> {$updated}개의 전단지 견적 아이템이 업데이트되었습니다.<br>";
    echo "이제 소수점 1자리까지 정확하게 표시됩니다.";
    echo "</p>";
}

mysqli_close($db);
?>
