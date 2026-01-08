<?php
$session_id = 'test_email_' . time();
echo "🔍 E2E 테스트 시작 (Session: $session_id)\n\n";

$db = mysqli_connect('localhost', 'dsp1830', 'ds701018', 'dsp1830');
if (!$db) die("DB 연결 실패\n");
mysqli_set_charset($db, 'utf8mb4');
echo "✅ DB 연결\n\n";

echo "📦 1단계: 장바구니 추가\n";
$premium_options = json_encode(['foil_enabled' => 1, 'foil_type' => '박(금박무광)', 'foil_price' => 30000, 'premium_options_total' => 30000], JSON_UNESCAPED_UNICODE);
$stmt = mysqli_prepare($db, "INSERT INTO shop_temp (product_type, MY_type, Section, POtype, MY_amount, ordertype, premium_options, premium_options_total, st_price, st_price_vat, session_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
mysqli_stmt_bind_param($stmt, 'ssssissiiis', $p='namecard', $t='1', $s='1', $po='2', $a='1000', $o='print_only', $premium_options, $pt=30000, $pr=9000, $vat=39000, $session_id);
if (!mysqli_stmt_execute($stmt)) die("장바구니 추가 실패\n");
echo "   ✅ 추가 성공 (옵션: 박/금박무광 30,000원)\n\n";

echo "📝 2단계: 주문 생성\n";
$max_result = mysqli_query($db, "SELECT MAX(no) as max_no FROM mlangorder_printauto");
$new_no = (mysqli_fetch_assoc($max_result)['max_no'] ?? 0) + 1;
$stmt = mysqli_prepare($db, "INSERT INTO mlangorder_printauto (no, Type, Type_1, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, cont, date, OrderStyle, premium_options, premium_options_total, ImgFolder, ThingCate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'issiiisssssssssiss', $new_no, $type='명함', $info="명함 테스트", $pr, $vat, $name='테스트고객', $email='ysungx@naver.com', $zip='12345', $addr='서울시', $det='테스트', $ph='010-1234-5678', $ph, $cont='E2E 테스트', $date=date("Y-m-d H:i:s"), $os='2', $premium_options, $pt, $folder="uploads/orders/$new_no/", $file="test.jpg");
if (!mysqli_stmt_execute($stmt)) die("주문 생성 실패\n");
echo "   ✅ 주문 생성 (번호: #$new_no)\n\n";

echo "📧 3단계: 이메일 발송\n";
include "mlangorder_printauto/mailer.lib.php";
$mail = "<h2>주문 확인</h2><p>주문번호: #$new_no</p><p>제품: 명함</p><p>기본가: 9,000원</p><p>옵션: 박(금박무광) 30,000원</p><p>총액: 39,000원</p>";
$result = mailer("두손기획인쇄", "dsp1830@naver.com", "ysungx@naver.com", "[두손기획인쇄] 테스트 주문 #$new_no", $mail, 1, "");
echo $result ? "   ✅ 이메일 발송 성공\n\n" : "   ❌ 이메일 발송 실패\n\n";

echo "🔍 4단계: 결과 확인\n";
$stmt = mysqli_prepare($db, "SELECT no, Type, money_4, money_5, premium_options_total FROM mlangorder_printauto WHERE no = ?");
mysqli_stmt_bind_param($stmt, 'i', $new_no);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
echo "   주문번호: #{$order['no']}\n";
echo "   제품: {$order['Type']}\n";
echo "   기본가: " . number_format($order['money_4']) . "원\n";
echo "   총액(VAT): " . number_format($order['money_5']) . "원\n";
echo "   옵션총액: " . number_format($order['premium_options_total']) . "원\n";
echo ($order['money_5'] == 39000 && $order['premium_options_total'] == 30000) ? "   ✅ 가격 합산 정상\n" : "   ❌ 가격 오류\n";

mysqli_query($db, "DELETE FROM shop_temp WHERE session_id = '$session_id'");
mysqli_close($db);
echo "\n============================================================\n테스트 완료! ysungx@naver.com 이메일을 확인하세요.\n============================================================\n";
?>
