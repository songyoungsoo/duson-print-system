<?php
echo "🔍 E2E 테스트: 옵션 포함 주문 + 이메일 발송\n\n";

$db = mysqli_connect('localhost', 'dsp1830', 'ds701018', 'dsp1830');
if (!$db) die("❌ DB 연결 실패\n");
mysqli_set_charset($db, 'utf8mb4');

// 주문 번호 생성
$max_result = mysqli_query($db, "SELECT MAX(no) as max_no FROM mlangorder_printauto");
$new_no = (mysqli_fetch_assoc($max_result)['max_no'] ?? 0) + 1;

echo "📝 1단계: 주문 생성 (옵션 포함)\n";
echo "   주문번호: #$new_no\n";

// 프리미엄 옵션 JSON
$premium_options = json_encode([
    'foil_enabled' => 1,
    'foil_type' => '박(금박무광)',
    'foil_price' => 30000,
    'premium_options_total' => 30000
], JSON_UNESCAPED_UNICODE);

// 주문 데이터
$product_type = '명함';
$product_info = "명함종류: 일반명함\n명함재질: 스노우지 250g\n인쇄면: 양면\n수량: 1000매";
$base_price = 9000;
$total_price = 39000;  // 9,000 + 30,000
$premium_total = 30000;
$username = '테스트고객';
$email = 'ysungx@naver.com';
$phone = '010-1234-5678';
$postcode = '12345';
$address = '서울시 영등포구';
$detail = '테스트동 123';
$cont = 'E2E 테스트: 옵션 가격 합산 및 이메일 발송 확인';
$date = date("Y-m-d H:i:s");

// INSERT 쿼리
$query = "INSERT INTO mlangorder_printauto (
    no, Type, Type_1, money_4, money_5, 
    name, email, zip, zip1, zip2, 
    phone, Hendphone, cont, date, OrderStyle,
    premium_options, premium_options_total,
    ImgFolder, ThingCate
) VALUES (
    $new_no, '$product_type', '$product_info', $base_price, $total_price,
    '$username', '$email', '$postcode', '$address', '$detail',
    '$phone', '$phone', '$cont', '$date', '2',
    '$premium_options', $premium_total,
    'uploads/orders/$new_no/', 'test_$new_no.jpg'
)";

if (mysqli_query($db, $query)) {
    echo "   ✅ 주문 저장 성공\n";
    echo "   - 기본가: " . number_format($base_price) . "원\n";
    echo "   - 옵션: 박(금박무광) " . number_format($premium_total) . "원\n";
    echo "   - 총액: " . number_format($total_price) . "원\n\n";
} else {
    die("   ❌ 주문 저장 실패: " . mysqli_error($db) . "\n");
}

// 이메일 발송
echo "📧 2단계: 이메일 발송\n";
include "mlangorder_printauto/mailer.lib.php";

$mail_html = "
<div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd;'>
    <h2 style='color: #333; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>주문 확인서</h2>
    <div style='padding: 20px; background: #f8f9fa; margin: 20px 0;'>
        <p><strong>주문번호:</strong> #$new_no</p>
        <p><strong>고객명:</strong> $username</p>
        <p><strong>이메일:</strong> $email</p>
        <p><strong>제품:</strong> 명함</p>
    </div>
    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
        <tr style='background: #e8f4f8;'>
            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>항목</th>
            <th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>금액</th>
        </tr>
        <tr>
            <td style='padding: 10px; border: 1px solid #ddd;'>기본 인쇄비</td>
            <td style='padding: 10px; text-align: right; border: 1px solid #ddd;'>" . number_format($base_price) . "원</td>
        </tr>
        <tr>
            <td style='padding: 10px; border: 1px solid #ddd;'>옵션: 박(금박무광)</td>
            <td style='padding: 10px; text-align: right; border: 1px solid #ddd;'>" . number_format($premium_total) . "원</td>
        </tr>
        <tr style='background: #fff3cd; font-weight: bold;'>
            <td style='padding: 10px; border: 1px solid #ddd;'>총액 (VAT 포함)</td>
            <td style='padding: 10px; text-align: right; border: 1px solid #ddd; color: #d63031;'>" . number_format($total_price) . "원</td>
        </tr>
    </table>
    <div style='padding: 15px; background: #e8f8f5; border-left: 4px solid #27ae60; margin: 20px 0;'>
        <p style='margin: 0; color: #27ae60; font-weight: bold;'>✅ E2E 테스트 주문입니다</p>
        <p style='margin: 5px 0 0 0; font-size: 14px;'>옵션 가격 합산 및 이메일 발송이 정상적으로 작동하는지 확인하는 테스트입니다.</p>
    </div>
    <div style='text-align: center; padding: 20px; background: #2c3e50; color: white; margin-top: 20px;'>
        <p style='margin: 0;'><strong>두손기획인쇄</strong></p>
        <p style='margin: 5px 0 0 0; font-size: 14px;'>02-2632-1830 | www.dsp1830.shop</p>
    </div>
</div>
";

$subject = "[두손기획인쇄] E2E 테스트 주문 완료 #$new_no";
$from_name = "두손기획인쇄";
$from_email = "dsp1830@naver.com";

$email_result = mailer($from_name, $from_email, $email, $subject, $mail_html, 1, "");

if ($email_result) {
    echo "   ✅ 이메일 발송 성공!\n";
    echo "   - 수신자: $email\n";
    echo "   - 제목: $subject\n\n";
} else {
    echo "   ❌ 이메일 발송 실패\n\n";
}

// 검증
echo "🔍 3단계: 결과 검증\n";
$check = mysqli_query($db, "SELECT no, Type, money_4, money_5, premium_options_total FROM mlangorder_printauto WHERE no = $new_no");
$order = mysqli_fetch_assoc($check);

echo "   주문번호: #{$order['no']}\n";
echo "   제품: {$order['Type']}\n";
echo "   기본가: " . number_format($order['money_4']) . "원\n";
echo "   총액(VAT): " . number_format($order['money_5']) . "원\n";
echo "   옵션총액: " . number_format($order['premium_options_total']) . "원\n";

$price_ok = ($order['money_5'] == 39000 && $order['premium_options_total'] == 30000);
echo "   " . ($price_ok ? "✅" : "❌") . " 가격 합산 " . ($price_ok ? "정상" : "오류") . "\n";

mysqli_close($db);
echo "\n" . str_repeat("=", 60) . "\n";
echo "테스트 완료!\n";
echo str_repeat("=", 60) . "\n";
echo "📧 ysungx@naver.com 메일함을 확인해주세요.\n";
?>
