<?php
/**
 * 추가옵션 총액 디버깅 스크립트
 */

include __DIR__ . "/../db.php";

$no = $_GET['no'] ?? 90057;

// 주문 조회
$stmt = $db->prepare("SELECT * FROM mlangorder_printauto WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("주문을 찾을 수 없습니다.");
}

echo "<h2>🐛 추가옵션 총액 디버깅 (주문번호: $no)</h2>";

// View_ 변수들 설정 (OrderFormOrderTree.php와 동일하게)
$View_money_4 = $row['money_4']; // 인쇄비
$View_money_2 = $row['money_2']; // 디자인비
$View_money_3 = $row['money_3']; // 부가세
$View_money_5 = $row['money_5']; // 총 합계
$View_Type_1 = $row['Type_1'];
$View_Type = $row['Type'];

echo "<h3>📋 기본 정보</h3>";
echo "<pre>";
echo "View_money_4 (인쇄비): " . number_format($View_money_4) . "원\n";
echo "View_money_2 (디자인비): " . number_format($View_money_2) . "원\n";
echo "View_money_3 (부가세): " . number_format($View_money_3) . "원\n";
echo "View_money_5 (총 합계): " . number_format($View_money_5) . "원\n";
echo "</pre>";

// OrderFormOrderTree.php와 동일한 로직으로 처리
$additionalOptionsTotal = 0;
$additionalOptionsHTML = '';

echo "<h3>🔍 추가옵션 처리 과정</h3>";

if (!empty($View_Type_1)) {
    echo "<p>✅ View_Type_1이 비어있지 않음</p>";

    $typeData = json_decode($View_Type_1, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($typeData)) {
        echo "<p>✅ View_Type_1이 유효한 JSON</p>";
        echo "<pre>" . json_encode($typeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "<p>⚠️ View_Type_1이 JSON이 아님</p>";
    }
} else {
    echo "<p>❌ View_Type_1이 비어있음</p>";
}

// 프리미엄 옵션 처리
echo "<h4>명함 프리미엄 옵션 확인:</h4>";
if (!empty($row['premium_options'])) {
    echo "<p style='color: green;'>✅ premium_options 존재</p>";

    $premium_options = json_decode($row['premium_options'], true);
    echo "<pre>" . json_encode($premium_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

    if ($premium_options && is_array($premium_options)) {
        $premium_option_names = [
            'foil' => ['name' => '박'],
            'numbering' => ['name' => '넘버링'],
            'perforation' => ['name' => '미싱'],
            'rounding' => ['name' => '모서리라운딩'],
            'creasing' => ['name' => '오시']
        ];

        foreach ($premium_option_names as $option_key => $option_info) {
            $enabled = $premium_options[$option_key . '_enabled'] ?? 0;
            $price = intval($premium_options[$option_key . '_price'] ?? 0);

            echo "<p>{$option_info['name']}: enabled={$enabled}, price=" . number_format($price) . "원</p>";

            if (!empty($premium_options[$option_key . '_enabled']) && $premium_options[$option_key . '_enabled'] == 1) {
                if ($price > 0) {
                    echo "<p style='color: green;'>  → ✅ additionalOptionsTotal에 추가: " . number_format($price) . "원</p>";
                    $additionalOptionsTotal += $price;
                }
            }
        }
    }
} else {
    echo "<p style='color: red;'>❌ premium_options 없음</p>";
}

echo "<h3>📊 최종 계산</h3>";
echo "<pre>";
echo "additionalOptionsTotal: " . number_format($additionalOptionsTotal) . "원\n";
echo "\n";
echo "소계 계산:\n";
echo "  인쇄비: " . number_format($View_money_4) . "원\n";
echo "  디자인비: " . number_format($View_money_2) . "원\n";
echo "  추가옵션: " . number_format($additionalOptionsTotal) . "원\n";
echo "  ─────────────────\n";
echo "  소계: " . number_format($View_money_4 + $View_money_2 + $additionalOptionsTotal) . "원\n";
echo "\n";
echo "부가세 (10%): " . number_format($View_money_3) . "원\n";
echo "총 합계: " . number_format($View_money_5) . "원\n";
echo "\n";
echo "기대값:\n";
echo "  소계 = 69,000 + 0 + 60,000 = 129,000원\n";
echo "  부가세 = 129,000 * 0.1 = 12,900원\n";
echo "  총 합계 = 129,000 + 12,900 = 141,900원\n";
echo "</pre>";

if ($additionalOptionsTotal == 60000) {
    echo "<p style='color: green; font-size: 1.2em;'>✅ additionalOptionsTotal이 정확합니다!</p>";
    echo "<p style='color: orange;'>⚠️ 그러나 실제 화면에서 소계가 69,000원으로 표시된다면, OrderFormOrderTree.php에서 변수가 제대로 전달되지 않는 것입니다.</p>";
} else {
    echo "<p style='color: red; font-size: 1.2em;'>❌ additionalOptionsTotal이 0원입니다. 프리미엄 옵션이 처리되지 않았습니다.</p>";
}
?>
