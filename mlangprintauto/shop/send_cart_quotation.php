<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../mlangorder_printauto/mailer.lib.php';
require_once __DIR__ . '/../includes/company_info.php';
require_once __DIR__ . '/../../includes/AdditionalOptionsDisplay.php';

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

$name    = trim($input['name'] ?? '');
$phone   = trim($input['phone'] ?? '');
$email   = trim($input['email'] ?? '');
$company = trim($input['company'] ?? '');
$memo    = trim($input['memo'] ?? '');
$userId  = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

if (empty($name))  jsonOut(false, '고객명을 입력해주세요.');
if (empty($phone)) jsonOut(false, '연락처를 입력해주세요.');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonOut(false, '올바른 이메일 주소를 입력해주세요.');
}

$session_id = session_id();
$cartQuery = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
$cartStmt = mysqli_prepare($db, $cartQuery);
if (!$cartStmt) {
    jsonOut(false, 'DB 오류: ' . mysqli_error($db));
}
mysqli_stmt_bind_param($cartStmt, 's', $session_id);
mysqli_stmt_execute($cartStmt);
$cartResult = mysqli_stmt_get_result($cartStmt);

$cartItems = [];
while ($row = mysqli_fetch_assoc($cartResult)) {
    if (!empty($row['additional_options'])) {
        $addOpts = json_decode($row['additional_options'], true);
        if ($addOpts && is_array($addOpts)) {
            $row['coating_enabled']  = $addOpts['coating_enabled'] ?? 0;
            $row['coating_type']     = $addOpts['coating_type'] ?? '';
            $row['coating_price']    = $addOpts['coating_price'] ?? 0;
            $row['folding_enabled']  = $addOpts['folding_enabled'] ?? 0;
            $row['folding_type']     = $addOpts['folding_type'] ?? '';
            $row['folding_price']    = $addOpts['folding_price'] ?? 0;
            $row['creasing_enabled'] = $addOpts['creasing_enabled'] ?? 0;
            $row['creasing_lines']   = $addOpts['creasing_lines'] ?? 0;
            $row['creasing_price']   = $addOpts['creasing_price'] ?? 0;
        }
    }
    $cartItems[] = $row;
}
mysqli_stmt_close($cartStmt);

if (empty($cartItems)) {
    jsonOut(false, '장바구니가 비어있습니다.');
}

$productNameMap = [
    'cadarok'        => '카다록',
    'sticker'        => '스티커',
    'sticker_new'    => '스티커',
    'msticker'       => '자석스티커',
    'leaflet'        => '전단지',
    'inserted'       => '전단지',
    'namecard'       => '명함',
    'envelope'       => '봉투',
    'merchandisebond'=> '상품권',
    'littleprint'    => '포스터',
    'poster'         => '포스터',
    'ncrflambeau'    => 'NCR양식지',
];

$optionsDisplay = getAdditionalOptionsDisplay($db);
$totalPrice    = 0;
$totalPriceVat = 0;
$itemsSummary  = [];

foreach ($cartItems as $item) {
    $basePrice = intval($item['st_price'] ?? 0);
    $priceData = $optionsDisplay->calculateTotalWithOptions($basePrice, $item);
    $finalPrice    = $priceData['total_price'];
    $finalPriceVat = $priceData['total_vat'];
    $totalPrice    += $finalPrice;
    $totalPriceVat += $finalPriceVat;

    $pType = $item['product_type'] ?? 'unknown';
    $pName = $productNameMap[$pType] ?? '인쇄상품';
    $itemsSummary[] = [
        'product_type' => $pType,
        'product_name' => $pName,
        'price'        => $finalPrice,
        'price_vat'    => $finalPriceVat,
    ];
}

$totalVat        = $totalPriceVat - $totalPrice;
$firstProductType = $cartItems[0]['product_type'] ?? 'unknown';
$firstProductName = $productNameMap[$firstProductType] ?? '인쇄상품';
$itemCount        = count($cartItems);
$productSummary   = $itemCount > 1
    ? $firstProductName . ' 외 ' . ($itemCount - 1) . '건'
    : $firstProductName;

$today    = date('Ymd');
$fqPrefix = 'FQ-' . $today . '-';
$seqQuery = "SELECT quote_no FROM quote_requests WHERE quote_no LIKE ? ORDER BY quote_no DESC LIMIT 1";
$seqStmt  = mysqli_prepare($db, $seqQuery);
$seqPat   = $fqPrefix . '%';
mysqli_stmt_bind_param($seqStmt, 's', $seqPat);
mysqli_stmt_execute($seqStmt);
$seqResult = mysqli_stmt_get_result($seqStmt);
$lastRow   = mysqli_fetch_assoc($seqResult);
mysqli_stmt_close($seqStmt);

if ($lastRow && $lastRow['quote_no']) {
    $lastSeq = intval(substr($lastRow['quote_no'], -3));
    $nextSeq = $lastSeq + 1;
} else {
    $nextSeq = 1;
}
$quoteNo = $fqPrefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

$itemsJson    = json_encode($itemsSummary, JSON_UNESCAPED_UNICODE);
$specQuantity = $itemCount . '개 품목';
$optionsDetail = !empty($memo) ? $memo : '';
if (!empty($company)) {
    $optionsDetail = '회사명: ' . $company . ($optionsDetail ? ' / ' . $optionsDetail : '');
}

$insertQuery = "INSERT INTO quote_requests (quote_no, customer_name, customer_phone, customer_email, user_id, product_type, product_name, spec_paper, spec_color, spec_size, spec_quantity, price_print, price_design, price_option, price_subtotal, price_vat, price_total, options_detail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$placeholderCount = substr_count($insertQuery, '?');
$typeString       = "ssssissssssiiiiiis";
$typeCount        = strlen($typeString);
$varCount         = 18;
if ($placeholderCount !== $typeCount || $typeCount !== $varCount) {
    jsonOut(false, 'bind_param 검증 실패');
}

$specPaper  = '-';
$specColor  = '-';
$specSize   = '-';
$priceDesign = 0;
$priceOption = 0;

$insertStmt = mysqli_prepare($db, $insertQuery);
if (!$insertStmt) {
    jsonOut(false, 'DB 준비 실패: ' . mysqli_error($db));
}
mysqli_stmt_bind_param($insertStmt, $typeString,
    $quoteNo,
    $name, $phone, $email, $userId,
    $firstProductType, $productSummary,
    $specPaper, $specColor, $specSize, $specQuantity,
    $totalPrice, $priceDesign, $priceOption, $totalPrice, $totalVat, $totalPriceVat,
    $optionsDetail
);
$insertOk = mysqli_stmt_execute($insertStmt);
$quoteId  = mysqli_insert_id($db);
mysqli_stmt_close($insertStmt);

if (!$insertOk) {
    jsonOut(false, 'DB 저장 실패: ' . mysqli_error($db));
}

$ci        = getCompanyInfo();
$quoteDate = date('Y-m-d');

$itemRows = '';
$rowNum   = 1;
foreach ($cartItems as $item) {
    $pType = $item['product_type'] ?? 'unknown';
    $pName = $productNameMap[$pType] ?? '인쇄상품';

    $basePrice = intval($item['st_price'] ?? 0);
    $priceData = $optionsDisplay->calculateTotalWithOptions($basePrice, $item);
    $rowPrice  = $priceData['total_price'];

    // 수량 표시
    if (in_array($pType, ['inserted', 'leaflet'])) {
        $yeon = floatval($item['yeon'] ?? $item['MY_amount'] ?? 1);
        $qty  = ($yeon == intval($yeon))
            ? number_format($yeon) . '연'
            : rtrim(rtrim(number_format($yeon, 1), '0'), '.') . '연';
    } elseif ($pType === 'ncrflambeau') {
        $qty = number_format(intval($item['MY_amount'] ?? 1)) . '권';
    } elseif ($pType === 'cadarok') {
        $qty = number_format(intval($item['MY_amount'] ?? 1)) . '부';
    } else {
        $qty = number_format(intval($item['mesu'] ?? $item['MY_amount'] ?? 1)) . '매';
    }

    $bgColor = ($rowNum % 2 === 0) ? '#f8fafc' : '#ffffff';
    $itemRows .= '
      <tr style="background:' . $bgColor . ';">
        <td style="padding:8px 10px;border:1px solid #cbd5e1;text-align:center;font-size:13px;">' . $rowNum . '</td>
        <td style="padding:8px 10px;border:1px solid #cbd5e1;font-weight:600;color:#1E4E79;font-size:13px;">' . htmlspecialchars($pName) . '</td>
        <td style="padding:8px 10px;border:1px solid #cbd5e1;text-align:center;font-size:13px;">' . htmlspecialchars($qty) . '</td>
        <td style="padding:8px 10px;border:1px solid #cbd5e1;text-align:right;font-weight:600;font-size:13px;">' . number_format($rowPrice) . '원</td>
      </tr>';
    $rowNum++;
}

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
        <td style="text-align:right;font-size:13px;color:#64748b;">견적번호 <strong style="color:#1E4E79;">' . htmlspecialchars($quoteNo) . '</strong></td>
      </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:20px;">
      <tr valign="top">
        <td width="50%" style="padding-right:6px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:2px solid #2a6496;">
            <tr><td colspan="2" style="background:#1E4E79;color:#fff;padding:7px 10px;font-size:13px;font-weight:600;text-align:center;letter-spacing:2px;border-bottom:1px solid #3a7ab5;">공급받는자</td></tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;width:60px;text-align:center;border:1px solid #94a3b8;">견적일</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . $quoteDate . '</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">상호/성명</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($name) . ($company ? ' (' . htmlspecialchars($company) . ')' : '') . ' 귀하</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">연락처</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($phone) . '</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">이메일</td>
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
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">상호/대표</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:700;border:1px solid #94a3b8;">' . htmlspecialchars($ci['name']) . ' / ' . htmlspecialchars($ci['owner']) . '</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">업태/종목</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($ci['business_type']) . ' / ' . htmlspecialchars($ci['business_item']) . '</td>
            </tr>
            <tr>
              <td style="background:#e8eff7;padding:6px 8px;font-size:12px;text-align:center;border:1px solid #94a3b8;">연락처</td>
              <td style="background:#f8fafc;padding:6px 8px;font-size:12px;font-weight:600;border:1px solid #94a3b8;">' . htmlspecialchars($ci['phone']) . '</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- 품목 테이블 -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #2a6496;margin-bottom:20px;">
      <tr>
        <td colspan="4" style="background:#e8eff7;color:#1E4E79;padding:10px 12px;font-size:14px;font-weight:700;letter-spacing:1px;border-bottom:1px solid #94a3b8;">주문 품목</td>
      </tr>
      <tr style="background:#1E4E79;">
        <th style="padding:8px 10px;color:#fff;font-size:12px;border:1px solid #3a7ab5;text-align:center;width:40px;">번호</th>
        <th style="padding:8px 10px;color:#fff;font-size:12px;border:1px solid #3a7ab5;text-align:left;">품목</th>
        <th style="padding:8px 10px;color:#fff;font-size:12px;border:1px solid #3a7ab5;text-align:center;width:80px;">수량</th>
        <th style="padding:8px 10px;color:#fff;font-size:12px;border:1px solid #3a7ab5;text-align:right;width:100px;">공급가액</th>
      </tr>
      ' . $itemRows . '
    </table>

    <!-- 합계 -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #2a6496;margin-bottom:20px;">
      <tr>
        <td style="padding:8px 12px;background:#eef3f9;color:#334155;font-weight:600;font-size:13px;border:1px solid #cbd5e1;">공급가액</td>
        <td style="padding:8px 12px;background:#f8fafc;text-align:right;font-weight:700;font-size:13px;border:1px solid #cbd5e1;">' . number_format($totalPrice) . '원</td>
      </tr>
      <tr>
        <td style="padding:8px 12px;background:#eef3f9;color:#334155;font-weight:600;font-size:13px;border:1px solid #cbd5e1;">부가세(10%)</td>
        <td style="padding:8px 12px;background:#f8fafc;text-align:right;font-size:13px;color:#64748b;border:1px solid #cbd5e1;">' . number_format($totalVat) . '원</td>
      </tr>
      <tr>
        <td style="padding:12px;background:#e8eff7;font-size:16px;font-weight:700;color:#1E4E79;border:1px solid #2a6496;">합계 (VAT포함)</td>
        <td style="padding:12px;background:#e8eff7;text-align:right;font-size:18px;font-weight:700;color:#1E4E79;border:1px solid #2a6496;">' . number_format($totalPriceVat) . '원</td>
      </tr>
    </table>'
    . (!empty($memo) ? '
    <div style="background:#fff3cd;border:1px solid #ffeaa7;border-radius:4px;padding:12px 16px;margin-bottom:20px;">
      <strong style="color:#856404;">📝 요청사항:</strong><br>
      <span style="font-size:13px;color:#333;">' . nl2br(htmlspecialchars($memo)) . '</span>
    </div>' : '') . '
    <div style="text-align:center;margin-top:24px;">
      <a href="https://dsp114.co.kr/mlangprintauto/shop/cart.php" style="display:inline-block;background:#1E4E79;color:#fff;padding:12px 36px;text-decoration:none;font-weight:600;font-size:14px;letter-spacing:1px;">주문하러 가기 →</a>
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

$now       = date('Y-m-d H:i:s');
$adminItemList = '';
foreach ($itemsSummary as $si) {
    $adminItemList .= '<tr><td style="padding:6px 8px;border:1px solid #e2e8f0;">' . htmlspecialchars($si['product_name']) . '</td><td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($si['price_vat']) . '원</td></tr>';
}

$adminBody = '
<div style="max-width:500px;font-family:sans-serif;font-size:14px;color:#333;">
  <h2 style="color:#1E4E79;margin:0 0 12px;">장바구니 견적 요청 알림 (' . htmlspecialchars($quoteNo) . ')</h2>
  <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:16px;">
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;width:80px;border:1px solid #e2e8f0;">이름/상호</td><td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($name) . ($company ? ' (' . htmlspecialchars($company) . ')' : '') . '</td></tr>
    <tr><td style="padding:8px;font-weight:600;border:1px solid #e2e8f0;">전화</td><td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($phone) . '</td></tr>
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;border:1px solid #e2e8f0;">이메일</td><td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($email) . '</td></tr>
    <tr><td style="padding:8px;font-weight:600;border:1px solid #e2e8f0;">품목수</td><td style="padding:8px;border:1px solid #e2e8f0;">' . $itemCount . '개</td></tr>
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;border:1px solid #e2e8f0;">총액(VAT포함)</td><td style="padding:8px;font-size:16px;font-weight:700;color:#1E4E79;border:1px solid #e2e8f0;">' . number_format($totalPriceVat) . '원</td></tr>
    <tr><td style="padding:8px;font-weight:600;border:1px solid #e2e8f0;">요청시각</td><td style="padding:8px;border:1px solid #e2e8f0;">' . $now . '</td></tr>
    <tr style="background:#f8fafc;"><td style="padding:8px;font-weight:600;border:1px solid #e2e8f0;">회원여부</td><td style="padding:8px;border:1px solid #e2e8f0;">' . ($userId ? '회원 (ID: ' . $userId . ')' : '비회원') . '</td></tr>'
    . (!empty($memo) ? '<tr><td style="padding:8px;font-weight:600;border:1px solid #e2e8f0;">요청사항</td><td style="padding:8px;border:1px solid #e2e8f0;">' . nl2br(htmlspecialchars($memo)) . '</td></tr>' : '') . '
  </table>
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <tr style="background:#1E4E79;"><th style="padding:8px;color:#fff;text-align:left;border:1px solid #3a7ab5;">품목</th><th style="padding:8px;color:#fff;text-align:right;border:1px solid #3a7ab5;">금액(VAT포함)</th></tr>
    ' . $adminItemList . '
  </table>
</div>';

$customerSubject = '[두손기획인쇄] ' . $productSummary . ' 견적서 (' . $quoteNo . ')';
$mailResult      = @mailer('두손기획인쇄', 'dsp1830@naver.com', $email, $customerSubject, $customerBody, 1, "");
$emailSent       = ($mailResult === true || $mailResult == 1);

$adminSubject  = '[장바구니견적] ' . $productSummary . ' / ' . $name . ' / ' . number_format($totalPriceVat) . '원';
$adminResult   = @mailer('두손기획인쇄', 'dsp1830@naver.com', 'dsp1830@naver.com', $adminSubject, $adminBody, 1, "");
$adminNotified = ($adminResult === true || $adminResult == 1);

$updateQuery = "UPDATE quote_requests SET email_sent = ?, admin_notified = ? WHERE id = ?";
$es = $emailSent ? 1 : 0;
$an = $adminNotified ? 1 : 0;
$updateStmt = mysqli_prepare($db, $updateQuery);
mysqli_stmt_bind_param($updateStmt, "iii", $es, $an, $quoteId);
mysqli_stmt_execute($updateStmt);
mysqli_stmt_close($updateStmt);

jsonOut(true, '견적서가 발송되었습니다.', [
    'quote_id'       => $quoteId,
    'quote_no'       => $quoteNo,
    'email_sent'     => $emailSent,
    'admin_notified' => $adminNotified,
]);
