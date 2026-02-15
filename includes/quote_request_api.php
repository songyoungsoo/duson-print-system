<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../mlangorder_printauto/mailer.lib.php';

function jsonOut($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(false, 'POST 요청만 허용됩니다.');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonOut(false, '잘못된 요청 데이터입니다.');
}

$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

if (empty($name) || mb_strlen($name) < 1) jsonOut(false, '이름/상호를 입력해주세요.');
if (empty($phone) || !preg_match('/^[\d\-]{8,15}$/', $phone)) jsonOut(false, '올바른 전화번호를 입력해주세요.');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) jsonOut(false, '올바른 이메일을 입력해주세요.');

$productType = trim($input['product_type'] ?? '');
$productName = trim($input['product_name'] ?? '');
$specPaper = trim($input['spec_paper'] ?? '-');
$specColor = trim($input['spec_color'] ?? '-');
$specSize = trim($input['spec_size'] ?? '-');
$specQty = trim($input['spec_quantity'] ?? '-');
$pricePrint = intval($input['price_print'] ?? 0);
$priceDesign = intval($input['price_design'] ?? 0);
$priceOption = intval($input['price_option'] ?? 0);
$priceSubtotal = intval($input['price_subtotal'] ?? 0);
$priceVat = intval($input['price_vat'] ?? 0);
$priceTotal = intval($input['price_total'] ?? 0);
$optionsDetail = trim($input['options_detail'] ?? '');

// bind_param 3단계 검증
$query = "INSERT INTO quote_requests (customer_name, customer_phone, customer_email, user_id, product_type, product_name, spec_paper, spec_color, spec_size, spec_quantity, price_print, price_design, price_option, price_subtotal, price_vat, price_total, options_detail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$placeholderCount = substr_count($query, '?'); // 17
$typeString = "sssissssssiiiiiis";
$typeCount = strlen($typeString); // 17
$varCount = 17;
if ($placeholderCount !== $typeCount || $typeCount !== $varCount) {
    jsonOut(false, 'bind_param 검증 실패');
}

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, $typeString,
    $name, $phone, $email, $userId,
    $productType, $productName,
    $specPaper, $specColor, $specSize, $specQty,
    $pricePrint, $priceDesign, $priceOption, $priceSubtotal, $priceVat, $priceTotal,
    $optionsDetail
);
$insertOk = mysqli_stmt_execute($stmt);
$quoteId = mysqli_insert_id($db);

if (!$insertOk) {
    jsonOut(false, 'DB 저장 실패: ' . mysqli_error($db));
}

$emailSent = false;
$adminNotified = false;

$priceRows = '';
$priceRows .= '<tr><td style="padding:6px 12px;color:#555;">인쇄비</td><td style="padding:6px 12px;text-align:right;font-weight:600;">' . number_format($pricePrint) . '원</td></tr>';
if ($priceDesign > 0) {
    $priceRows .= '<tr><td style="padding:6px 12px;color:#555;">디자인</td><td style="padding:6px 12px;text-align:right;font-weight:600;">' . number_format($priceDesign) . '원</td></tr>';
}
if ($priceOption > 0) {
    $priceRows .= '<tr><td style="padding:6px 12px;color:#555;">인쇄외옵션</td><td style="padding:6px 12px;text-align:right;font-weight:600;">' . number_format($priceOption) . '원</td></tr>';
}

$optionLines = '';
if (!empty($optionsDetail)) {
    $opts = explode(',', $optionsDetail);
    foreach ($opts as $opt) {
        $opt = trim($opt);
        if ($opt) $optionLines .= '<span style="display:inline-block;background:#f0f0ff;color:#6366f1;padding:2px 8px;border-radius:3px;font-size:12px;margin:2px 3px;">' . htmlspecialchars($opt) . '</span>';
    }
}

$customerBody = '
<div style="max-width:560px;margin:0 auto;font-family:\'Pretendard\',\'Noto Sans KR\',sans-serif;">
  <div style="background:linear-gradient(135deg,#3b82f6,#2563eb);padding:24px;border-radius:12px 12px 0 0;text-align:center;">
    <h1 style="color:#fff;font-size:20px;margin:0;">두손기획인쇄 견적서</h1>
    <p style="color:rgba(255,255,255,0.8);font-size:13px;margin:6px 0 0;">견적번호 #' . $quoteId . '</p>
  </div>
  <div style="background:#fff;border:1px solid #e2e8f0;border-top:0;padding:24px;">
    <p style="font-size:14px;color:#333;margin:0 0 16px;">' . htmlspecialchars($name) . '님, 요청하신 견적입니다.</p>
    <div style="background:#f8fafc;border-radius:8px;padding:16px;margin-bottom:16px;">
      <div style="font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:1px;margin-bottom:8px;">품목</div>
      <div style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:12px;">' . htmlspecialchars($productName) . '</div>
      <div style="font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:1px;margin-bottom:8px;">사양</div>
      <table style="width:100%;font-size:13px;color:#334155;border-collapse:collapse;">
        <tr><td style="padding:3px 0;color:#64748b;width:60px;">용지</td><td>' . htmlspecialchars($specPaper) . '</td></tr>
        <tr><td style="padding:3px 0;color:#64748b;">인쇄</td><td>' . htmlspecialchars($specColor) . '</td></tr>
        <tr><td style="padding:3px 0;color:#64748b;">사이즈</td><td>' . htmlspecialchars($specSize) . '</td></tr>
        <tr><td style="padding:3px 0;color:#64748b;">수량</td><td>' . htmlspecialchars($specQty) . '</td></tr>
      </table>'
      . ($optionLines ? '<div style="margin-top:8px;"><span style="font-size:11px;color:#94a3b8;">인쇄외옵션:</span><br>' . $optionLines . '</div>' : '') .
    '</div>
    <table style="width:100%;font-size:14px;border-collapse:collapse;">'
      . $priceRows .
      '<tr style="border-top:1px solid #e2e8f0;">
        <td style="padding:8px 12px;font-weight:600;">합계</td>
        <td style="padding:8px 12px;text-align:right;font-weight:600;">' . number_format($priceSubtotal) . '원</td>
      </tr>
      <tr>
        <td style="padding:4px 12px;font-size:12px;color:#94a3b8;">부가세(10%)</td>
        <td style="padding:4px 12px;text-align:right;font-size:12px;color:#94a3b8;">' . number_format($priceVat) . '원</td>
      </tr>
      <tr style="border-top:2px solid #1e293b;">
        <td style="padding:12px;font-size:18px;font-weight:700;color:#1e293b;">총액 (VAT포함)</td>
        <td style="padding:12px;text-align:right;font-size:18px;font-weight:700;color:#2563eb;">' . number_format($priceTotal) . '원</td>
      </tr>
    </table>
    <div style="margin-top:20px;text-align:center;">
      <a href="https://dsp114.co.kr/mlangprintauto/' . htmlspecialchars($productType) . '/" style="display:inline-block;background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">주문하러 가기</a>
    </div>
  </div>
  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-top:0;border-radius:0 0 12px 12px;padding:16px;text-align:center;font-size:12px;color:#94a3b8;">
    <p style="margin:0;">두손기획인쇄 | 서울특별시 영등포구 영등포로 36길9 송호빌딩 1층</p>
    <p style="margin:4px 0 0;">Tel. 02-2632-1830 | Fax. 02-2632-1829</p>
    <p style="margin:4px 0 0;">본 견적은 요청 시점 기준이며, 사양 변경 시 금액이 달라질 수 있습니다.</p>
  </div>
</div>';

$customerSubject = '[두손기획인쇄] ' . $productName . ' 견적서 (#' . $quoteId . ')';
$mailResult = @mailer('두손기획인쇄', 'dsp1830@naver.com', $email, $customerSubject, $customerBody, 1, "");
$emailSent = ($mailResult === true || $mailResult == 1);

$now = date('Y-m-d H:i:s');
$adminBody = '
<div style="max-width:500px;font-family:sans-serif;font-size:14px;color:#333;">
  <h2 style="color:#2563eb;margin:0 0 12px;">견적 요청 알림 (#' . $quoteId . ')</h2>
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;width:80px;">이름/상호</td><td style="padding:8px;">' . htmlspecialchars($name) . '</td></tr>
    <tr><td style="padding:8px;font-weight:600;">전화</td><td style="padding:8px;">' . htmlspecialchars($phone) . '</td></tr>
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;">이메일</td><td style="padding:8px;">' . htmlspecialchars($email) . '</td></tr>
    <tr><td style="padding:8px;font-weight:600;">품목</td><td style="padding:8px;">' . htmlspecialchars($productName) . '</td></tr>
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;">사양</td><td style="padding:8px;">' . htmlspecialchars($specPaper) . ' / ' . htmlspecialchars($specColor) . ' / ' . htmlspecialchars($specSize) . ' / ' . htmlspecialchars($specQty) . '</td></tr>
    <tr><td style="padding:8px;font-weight:600;">총액</td><td style="padding:8px;font-size:16px;font-weight:700;color:#2563eb;">' . number_format($priceTotal) . '원</td></tr>
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;">요청시각</td><td style="padding:8px;">' . $now . '</td></tr>
    <tr><td style="padding:8px;font-weight:600;">회원여부</td><td style="padding:8px;">' . ($userId ? '회원 (ID: ' . $userId . ')' : '비회원') . '</td></tr>
  </table>
</div>';

$adminSubject = '[견적요청] ' . $productName . ' / ' . $name . ' / ' . number_format($priceTotal) . '원';
$adminResult = @mailer('두손기획인쇄', 'dsp1830@naver.com', 'dsp1830@naver.com', $adminSubject, $adminBody, 1, "");
$adminNotified = ($adminResult === true || $adminResult == 1);

$updateQuery = "UPDATE quote_requests SET email_sent = ?, admin_notified = ? WHERE id = ?";
$es = $emailSent ? 1 : 0;
$an = $adminNotified ? 1 : 0;
$updateStmt = mysqli_prepare($db, $updateQuery);
mysqli_stmt_bind_param($updateStmt, "iii", $es, $an, $quoteId);
mysqli_stmt_execute($updateStmt);

jsonOut(true, '견적서가 발송되었습니다.', [
    'quote_id' => $quoteId,
    'email_sent' => $emailSent,
    'admin_notified' => $adminNotified
]);
