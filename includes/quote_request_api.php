<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../mlangorder_printauto/mailer.lib.php';
require_once __DIR__ . '/../mlangprintauto/includes/company_info.php';
ob_start();

function jsonOut($success, $message, $data = []) {
    ob_end_clean();
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
$priceRows .= '<tr><td style="padding:8px 12px;background:#1E4E79;color:#fff;font-weight:600;font-size:13px;border:1px solid #3a7ab5;width:120px;">인쇄비</td><td style="padding:8px 12px;background:#f8fafc;text-align:right;font-weight:600;font-size:13px;border:1px solid #cbd5e1;">' . number_format($pricePrint) . '원</td></tr>';
if ($priceDesign > 0) {
    $priceRows .= '<tr><td style="padding:8px 12px;background:#1E4E79;color:#fff;font-weight:600;font-size:13px;border:1px solid #3a7ab5;">디자인</td><td style="padding:8px 12px;background:#f8fafc;text-align:right;font-weight:600;font-size:13px;border:1px solid #cbd5e1;">' . number_format($priceDesign) . '원</td></tr>';
}
if ($priceOption > 0) {
    $priceRows .= '<tr><td style="padding:8px 12px;background:#1E4E79;color:#fff;font-weight:600;font-size:13px;border:1px solid #3a7ab5;">인쇄외옵션</td><td style="padding:8px 12px;background:#f8fafc;text-align:right;font-weight:600;font-size:13px;border:1px solid #cbd5e1;">' . number_format($priceOption) . '원</td></tr>';
}

$optionLines = '';
if (!empty($optionsDetail)) {
    $opts = explode(',', $optionsDetail);
    foreach ($opts as $opt) {
        $opt = trim($opt);
        if ($opt) $optionLines .= '<span style="display:inline-block;background:#e8eff7;color:#1E4E79;padding:2px 8px;border:1px solid #cbd5e1;font-size:12px;margin:2px 3px;">' . htmlspecialchars($opt) . '</span>';
    }
}

$ci = getCompanyInfo();
$quoteDate = date('Y-m-d');

$customerBody = '
<div style="max-width:640px;margin:0 auto;font-family:\'Pretendard\',\'Noto Sans KR\',\'Malgun Gothic\',sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
    <tr><td style="background:#1E4E79;padding:28px 24px;text-align:center;">
      <h1 style="color:#fff;font-size:22px;font-weight:700;margin:0;letter-spacing:6px;">견 적 서</h1>
      <p style="color:#a3c4e0;font-size:12px;margin:8px 0 0;">QUOTATION</p>
    </td></tr>
  </table>
  <div style="background:#fff;border-left:1px solid #2a6496;border-right:1px solid #2a6496;padding:24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:16px;">
      <tr>
        <td style="font-size:14px;color:#334155;">' . htmlspecialchars($name) . '님, 요청하신 견적입니다.</td>
        <td style="text-align:right;font-size:13px;color:#64748b;">견적번호 <strong style="color:#1E4E79;">#' . $quoteId . '</strong></td>
      </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:20px;">
      <tr valign="top">
        <td width="50%" style="padding-right:6px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:2px solid #2a6496;">
            <tr><td colspan="2" style="background:#1E4E79;color:#fff;padding:7px 10px;font-size:13px;font-weight:600;text-align:center;letter-spacing:2px;border-bottom:1px solid #3a7ab5;">공급받는자</td></tr>
            <tr>
              <td style="background:#1E4E79;color:#fff;padding:6px 8px;font-size:12px;width:60px;text-align:center;border:1px solid #3a7ab5;">견적일</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . $quoteDate . '</td>
            </tr>
            <tr>
              <td style="background:#1E4E79;color:#fff;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #3a7ab5;">상호/성명</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($name) . ' 귀하</td>
            </tr>
            <tr>
              <td style="background:#1E4E79;color:#fff;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #3a7ab5;">연락처</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($phone) . '</td>
            </tr>
            <tr>
              <td style="background:#1E4E79;color:#fff;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #3a7ab5;">이메일</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($email) . '</td>
            </tr>
          </table>
        </td>
        <td width="50%" style="padding-left:6px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:2px solid #2a6496;">
            <tr><td colspan="2" style="background:#1E4E79;color:#fff;padding:7px 10px;font-size:13px;font-weight:600;text-align:center;letter-spacing:2px;border-bottom:1px solid #3a7ab5;">공 급 자</td></tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;width:60px;border:1px solid #94a3b8;">등록번호</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:700;border:1px solid #94a3b8;">' . htmlspecialchars($ci['business_number']) . '</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">상 호</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:700;border:1px solid #94a3b8;">' . htmlspecialchars($ci['name']) . '</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">대표자</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($ci['owner']) . '</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">연락처</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($ci['phone']) . '</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #2a6496;margin-bottom:20px;">
      <tr>
        <td colspan="2" style="background:#1E4E79;color:#fff;padding:10px 12px;font-size:14px;font-weight:700;letter-spacing:1px;border-bottom:1px solid #3a7ab5;">품목 · 사양</td>
      </tr>
      <tr>
        <td style="background:#1E4E79;color:#fff;padding:8px 12px;font-size:13px;font-weight:600;border:1px solid #3a7ab5;width:80px;">품목</td>
        <td style="background:#f8fafc;padding:8px 12px;font-size:14px;font-weight:700;color:#1E4E79;border:1px solid #cbd5e1;">' . htmlspecialchars($productName) . '</td>
      </tr>
      <tr>
        <td style="background:#1E4E79;color:#fff;padding:8px 12px;font-size:13px;font-weight:600;border:1px solid #3a7ab5;">용지</td>
        <td style="background:#f8fafc;padding:8px 12px;font-size:13px;color:#334155;border:1px solid #cbd5e1;">' . htmlspecialchars($specPaper) . '</td>
      </tr>
      <tr>
        <td style="background:#1E4E79;color:#fff;padding:8px 12px;font-size:13px;font-weight:600;border:1px solid #3a7ab5;">인쇄</td>
        <td style="background:#f8fafc;padding:8px 12px;font-size:13px;color:#334155;border:1px solid #cbd5e1;">' . htmlspecialchars($specColor) . '</td>
      </tr>
      <tr>
        <td style="background:#1E4E79;color:#fff;padding:8px 12px;font-size:13px;font-weight:600;border:1px solid #3a7ab5;">사이즈</td>
        <td style="background:#f8fafc;padding:8px 12px;font-size:13px;color:#334155;border:1px solid #cbd5e1;">' . htmlspecialchars($specSize) . '</td>
      </tr>
      <tr>
        <td style="background:#1E4E79;color:#fff;padding:8px 12px;font-size:13px;font-weight:600;border:1px solid #3a7ab5;">수량</td>
        <td style="background:#f8fafc;padding:8px 12px;font-size:13px;color:#334155;border:1px solid #cbd5e1;">' . htmlspecialchars($specQty) . '</td>
      </tr>'
      . ($optionLines ? '
      <tr>
        <td style="background:#1E4E79;color:#fff;padding:8px 12px;font-size:13px;font-weight:600;border:1px solid #3a7ab5;vertical-align:top;">옵션</td>
        <td style="background:#f8fafc;padding:8px 12px;border:1px solid #cbd5e1;">' . $optionLines . '</td>
      </tr>' : '') . '
    </table>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #2a6496;margin-bottom:20px;">'
      . $priceRows .
      '<tr>
        <td style="padding:8px 12px;background:#1E4E79;color:#fff;font-weight:600;font-size:13px;border:1px solid #3a7ab5;">공급가액</td>
        <td style="padding:8px 12px;background:#f8fafc;text-align:right;font-weight:700;font-size:13px;border:1px solid #cbd5e1;">' . number_format($priceSubtotal) . '원</td>
      </tr>
      <tr>
        <td style="padding:8px 12px;background:#1E4E79;color:#fff;font-weight:600;font-size:13px;border:1px solid #3a7ab5;">부가세(10%)</td>
        <td style="padding:8px 12px;background:#f8fafc;text-align:right;font-size:13px;color:#64748b;border:1px solid #cbd5e1;">' . number_format($priceVat) . '원</td>
      </tr>
      <tr>
        <td style="padding:12px;background:#e8eff7;font-size:16px;font-weight:700;color:#1E4E79;border:1px solid #2a6496;">합계 (VAT포함)</td>
        <td style="padding:12px;background:#e8eff7;text-align:right;font-size:18px;font-weight:700;color:#1E4E79;border:1px solid #2a6496;">' . number_format($priceTotal) . '원</td>
      </tr>
    </table>
    <div style="text-align:center;margin-top:24px;">
      <a href="https://dsp114.co.kr/mlangprintauto/' . htmlspecialchars($productType) . '/" style="display:inline-block;background:#1E4E79;color:#fff;padding:12px 36px;text-decoration:none;font-weight:600;font-size:14px;letter-spacing:1px;">주문하러 가기 →</a>
    </div>
  </div>
  <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
    <tr><td style="background:#1E4E79;padding:16px 24px;text-align:center;font-size:12px;color:#a3c4e0;">
      <p style="margin:0;">' . htmlspecialchars($ci['name']) . ' | ' . htmlspecialchars($ci['address']) . '</p>
      <p style="margin:4px 0 0;">Tel. ' . htmlspecialchars($ci['phone']) . ' | Fax. ' . htmlspecialchars($ci['fax']) . '</p>
      <p style="margin:6px 0 0;color:#7ba7cc;font-size:11px;">본 견적은 요청 시점 기준이며, 사양 변경 시 금액이 달라질 수 있습니다.</p>
    </td></tr>
  </table>
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
