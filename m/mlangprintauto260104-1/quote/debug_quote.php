<?php
/**
 * 견적서 디버깅 스크립트
 * 견적서 번호로 DB 저장 상태 확인
 */

require_once __DIR__ . '/../../db.php';

$quote_no = $_GET['no'] ?? 'QT-20251126-025';

echo "<h2>견적서 진단: {$quote_no}</h2>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #f5f5f5; }
h2 { color: #333; border-bottom: 2px solid #03C75A; padding-bottom: 10px; }
h3 { color: #666; margin-top: 30px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
pre { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 4px; overflow-x: auto; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.warning { color: #ffc107; font-weight: bold; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; background: #fff; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #03C75A; color: white; }
tr:nth-child(even) { background: #f9f9f9; }
</style>";

// 1. 견적서 기본 정보 조회
$sql = "SELECT * FROM quotes WHERE quote_no = ? LIMIT 1";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "s", $quote_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quote = mysqli_fetch_assoc($result);

if (!$quote) {
    echo "<p class='error'>❌ 견적서를 찾을 수 없습니다: {$quote_no}</p>";
    exit;
}

echo "<h3>✅ 견적서 기본 정보</h3>";
echo "<table>";
echo "<tr><th>항목</th><th>값</th></tr>";
echo "<tr><td>견적서 ID</td><td>{$quote['id']}</td></tr>";
echo "<tr><td>견적서 번호</td><td>{$quote['quote_no']}</td></tr>";
echo "<tr><td>고객명</td><td>{$quote['customer_name']}</td></tr>";
echo "<tr><td>고객 이메일</td><td>{$quote['customer_email']}</td></tr>";
echo "<tr><td>공급가 합계</td><td>" . number_format($quote['supply_total']) . "원</td></tr>";
echo "<tr><td>VAT 합계</td><td>" . number_format($quote['vat_total']) . "원</td></tr>";
echo "<tr><td>총 금액</td><td>" . number_format($quote['grand_total']) . "원</td></tr>";
echo "<tr><td>생성일</td><td>{$quote['created_at']}</td></tr>";
echo "</table>";

// 2. 품목 정보 조회
$quote_id = $quote['id'];
$items_sql = "SELECT * FROM quote_items WHERE quote_id = ? ORDER BY item_no";
$items_stmt = mysqli_prepare($db, $items_sql);
mysqli_stmt_bind_param($items_stmt, "i", $quote_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);
$items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

echo "<h3>📦 저장된 품목 정보</h3>";
echo "<p><strong>총 품목 수:</strong> <span class='" . (count($items) >= 4 ? 'success' : 'error') . "'>" . count($items) . "개</span></p>";

if (count($items) > 0) {
    echo "<table>";
    echo "<tr><th>No</th><th>품명</th><th>규격</th><th>수량</th><th>단위</th><th>단가</th><th>공급가</th><th>Source Type</th><th>Source ID</th><th>Source Data</th></tr>";

    foreach ($items as $item) {
        $source_type_class = '';
        if ($item['source_type'] === 'cart') {
            $source_type_class = 'style="background-color: #e7f3ff;"';
        } elseif ($item['source_type'] === 'manual') {
            $source_type_class = 'style="background-color: #fff3cd;"';
        }

        echo "<tr {$source_type_class}>";
        echo "<td>{$item['item_no']}</td>";
        echo "<td>{$item['product_name']}</td>";
        echo "<td>" . ($item['specification'] ?: '-') . "</td>";
        echo "<td>" . number_format($item['quantity'], 2) . "</td>";
        echo "<td>{$item['unit']}</td>";
        echo "<td>" . number_format($item['unit_price']) . "</td>";
        echo "<td>" . number_format($item['supply_price']) . "</td>";
        echo "<td><strong>{$item['source_type']}</strong></td>";
        echo "<td>" . ($item['source_id'] ?: '-') . "</td>";
        echo "<td><small>" . (strlen($item['source_data'] ?? '') > 50 ? substr($item['source_data'], 0, 50) . '...' : ($item['source_data'] ?: '-')) . "</small></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ 저장된 품목이 없습니다!</p>";
}

// 3. 기대값 vs 실제값 비교
echo "<h3>🔍 진단 결과</h3>";
echo "<table>";
echo "<tr><th>항목</th><th>기대값</th><th>실제값</th><th>상태</th></tr>";

$expected_items = 4;
$actual_items = count($items);
$items_status = ($actual_items === $expected_items) ? '<span class="success">✅</span>' : '<span class="error">❌</span>';
echo "<tr><td>총 품목 수</td><td>{$expected_items}개</td><td>{$actual_items}개</td><td>{$items_status}</td></tr>";

$cart_items = array_filter($items, fn($i) => $i['source_type'] === 'cart');
$manual_items = array_filter($items, fn($i) => $i['source_type'] === 'manual');

$cart_status = (count($cart_items) === 2) ? '<span class="success">✅</span>' : '<span class="error">❌</span>';
echo "<tr><td>장바구니 품목</td><td>2개</td><td>" . count($cart_items) . "개</td><td>{$cart_status}</td></tr>";

$manual_status = (count($manual_items) === 2) ? '<span class="success">✅</span>' : '<span class="error">❌</span>';
echo "<tr><td>수동 추가 품목</td><td>2개</td><td>" . count($manual_items) . "개</td><td>{$manual_status}</td></tr>";

echo "</table>";

// 4. 문제 분석
echo "<h3>💡 문제 분석</h3>";

if (count($items) < 4) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
    echo "<strong>⚠️ 품목 누락 발견:</strong><br>";
    echo "- 기대: 4개 품목 (전단지, 명함, 배너, 모자)<br>";
    echo "- 실제: {$actual_items}개 품목<br>";
    echo "- 누락: " . (4 - $actual_items) . "개 품목<br><br>";

    if (count($manual_items) === 0) {
        echo "<strong>🔴 수동 추가 품목이 전혀 저장되지 않았습니다!</strong><br>";
        echo "원인 가능성:<br>";
        echo "1. QuoteManager::createFromCart()에서 manual 품목 필터링 오류<br>";
        echo "2. addManualItem() 메서드의 bind_param 오류<br>";
        echo "3. save.php에서 items 배열 처리 오류<br>";
    }
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
    echo "<strong>✅ 모든 품목이 정상적으로 저장되었습니다!</strong><br>";
    echo "- 장바구니 품목: " . count($cart_items) . "개<br>";
    echo "- 수동 추가 품목: " . count($manual_items) . "개<br>";
    echo "</div>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<p><a href='detail.php?id={$quote_id}' style='background: #03C75A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>📄 견적서 상세 보기</a></p>";
?>
