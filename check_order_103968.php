<?php
$db = mysqli_connect('localhost', 'dsp1830', 'ds701018', 'dsp1830');
mysqli_set_charset($db, 'utf8mb4');

echo "주문 #103968 상세 정보\n\n";

$query = "SELECT no, Type, Type_1, money_4, money_5, premium_options, premium_options_total, name, email, date 
          FROM mlangorder_printauto WHERE no = 103968";
$result = mysqli_query($db, $query);
$order = mysqli_fetch_assoc($result);

if ($order) {
    echo "✅ 주문 조회 성공\n\n";
    echo "주문번호: #{$order['no']}\n";
    echo "고객명: {$order['name']}\n";
    echo "이메일: {$order['email']}\n";
    echo "제품: {$order['Type']}\n";
    echo "주문일시: {$order['date']}\n\n";
    echo "--- 가격 정보 ---\n";
    echo "기본가 (money_4): " . number_format($order['money_4']) . "원\n";
    echo "총액/VAT포함 (money_5): " . number_format($order['money_5']) . "원\n";
    echo "프리미엄 옵션 총액: " . number_format($order['premium_options_total']) . "원\n\n";
    echo "--- 옵션 상세 ---\n";
    $options = json_decode($order['premium_options'], true);
    if ($options) {
        foreach ($options as $key => $value) {
            if ($value) echo "$key: $value\n";
        }
    }
    echo "\n--- 검증 ---\n";
    echo "옵션 가격 합산: " . ($order['premium_options_total'] == 30000 ? "✅ 정상 (30,000원)" : "❌ 오류") . "\n";
    echo "총액 계산: " . ($order['money_5'] == 39000 ? "✅ 정상 (39,000원)" : "❌ 오류") . "\n";
} else {
    echo "❌ 주문을 찾을 수 없습니다\n";
}

mysqli_close($db);
?>
