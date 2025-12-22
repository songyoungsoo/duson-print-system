<?php
/**
 * 견적서 데이터 확인 도구
 * 사용법: check_quote.php?id=63
 */

session_start();
require_once __DIR__ . '/../db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die('❌ 사용법: check_quote.php?id=63');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>견적서 데이터 확인</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
        .null { color: #dc3545; font-weight: bold; }
        .empty { color: #ffc107; font-style: italic; }
        .ok { color: #28a745; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 견적서 데이터 확인 (ID: <?php echo $id; ?>)</h1>

        <?php
        // quotes 테이블 조회
        $query = "SELECT * FROM quotes WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $quote = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$quote) {
            echo "<p style='color: red; font-weight: bold;'>❌ 견적서를 찾을 수 없습니다. (ID: $id)</p>";
            exit;
        }

        echo "<h2>📄 Quotes 테이블 데이터</h2>";
        echo "<table>";
        echo "<tr><th>컬럼명</th><th>값</th><th>상태</th></tr>";

        $important_fields = [
            'id' => '견적서 ID',
            'quote_no' => '견적번호',
            'customer_name' => '고객명',
            'customer_company' => '회사명',
            'customer_phone' => '전화번호',
            'customer_email' => '이메일',
            'recipient_email' => '수신자 이메일',
            'status' => '상태',
            'created_at' => '작성일',
            'original_quote_id' => '원본 견적 ID',
            'version' => '버전',
            'is_latest' => '최신 버전'
        ];

        foreach ($important_fields as $field => $label) {
            $value = $quote[$field] ?? null;

            if ($value === null) {
                $status = "<span class='null'>NULL</span>";
                $display = '-';
            } elseif ($value === '') {
                $status = "<span class='empty'>빈 문자열</span>";
                $display = '(empty)';
            } else {
                $status = "<span class='ok'>✅ OK</span>";
                $display = htmlspecialchars($value);
            }

            echo "<tr>";
            echo "<td><strong>{$label}</strong><br><code>{$field}</code></td>";
            echo "<td>{$display}</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";

        // 전체 필드 (참고용)
        echo "<h2>📋 전체 필드 (Raw Data)</h2>";
        echo "<table>";
        echo "<tr><th>필드명</th><th>값</th></tr>";
        foreach ($quote as $key => $value) {
            if (in_array($key, array_keys($important_fields))) continue; // 중복 제외

            $display = $value === null ? '<span class="null">NULL</span>' :
                       ($value === '' ? '<span class="empty">(empty)</span>' :
                       htmlspecialchars($value));

            echo "<tr>";
            echo "<td><code>{$key}</code></td>";
            echo "<td>{$display}</td>";
            echo "</tr>";
        }
        echo "</table>";

        // quote_items 확인
        $itemsQuery = "SELECT * FROM quote_items WHERE quote_id = ? ORDER BY item_no ASC";
        $itemsStmt = mysqli_prepare($db, $itemsQuery);
        mysqli_stmt_bind_param($itemsStmt, "i", $id);
        mysqli_stmt_execute($itemsStmt);
        $itemsResult = mysqli_stmt_get_result($itemsStmt);
        $items = [];
        while ($row = mysqli_fetch_assoc($itemsResult)) {
            $items[] = $row;
        }
        mysqli_stmt_close($itemsStmt);

        echo "<h2>📦 품목 데이터 (" . count($items) . "개)</h2>";
        if (empty($items)) {
            echo "<p style='color: #ffc107;'>⚠️ 품목이 없습니다.</p>";
        } else {
            echo "<table>";
            echo "<tr><th>NO</th><th>품명</th><th>규격</th><th>수량</th><th>단가</th><th>공급가</th><th>비고</th></tr>";
            foreach ($items as $item) {
                echo "<tr>";
                echo "<td>{$item['item_no']}</td>";
                echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                echo "<td>" . htmlspecialchars($item['specification']) . "</td>";
                echo "<td>" . number_format($item['quantity']) . "</td>";
                echo "<td>" . number_format($item['unit_price']) . "</td>";
                echo "<td>" . number_format($item['supply_price']) . "</td>";
                echo "<td>" . htmlspecialchars($item['notes'] ?? '-') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        mysqli_close($db);
        ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee; text-align: center;">
            <p><a href="detail.php?id=<?php echo $id; ?>" style="color: #007bff;">← 견적서 상세 페이지로 돌아가기</a></p>
        </div>
    </div>
</body>
</html>
