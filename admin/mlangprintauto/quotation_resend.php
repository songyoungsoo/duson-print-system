<?php
/**
 * 견적서 이메일 재발송 API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

include "../../db.php";
mysqli_set_charset($db, 'utf8mb4');

// 단가 계산 헬퍼 함수 로드
require_once __DIR__ . '/../../mlangprintauto/quote/includes/PriceHelper.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$id = intval($data['id'] ?? 0);
$email = trim($data['email'] ?? '');

if ($id <= 0 || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 견적서 조회
$stmt = mysqli_prepare($db, "SELECT * FROM quotations WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$quotation) {
    echo json_encode(['success' => false, 'message' => '견적서를 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// JSON 파싱
$cart_items = json_decode($quotation['cart_items_json'], true) ?? [];
$custom_items = json_decode($quotation['custom_items_json'], true) ?? [];

// 숫자를 한글 금액으로 변환
function numberToKorean($number) {
    $number = intval($number);
    if ($number == 0) return '영원';

    $units = ['', '만', '억', '조'];
    $digits = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
    $subUnits = ['', '십', '백', '천'];

    $result = '';
    $unitIndex = 0;

    while ($number > 0) {
        $chunk = $number % 10000;
        $number = intval($number / 10000);

        if ($chunk > 0) {
            $chunkStr = '';
            $subIndex = 0;
            while ($chunk > 0) {
                $digit = $chunk % 10;
                $chunk = intval($chunk / 10);
                if ($digit > 0) {
                    $digitStr = ($digit == 1 && $subIndex > 0) ? '' : $digits[$digit];
                    $chunkStr = $digitStr . $subUnits[$subIndex] . $chunkStr;
                }
                $subIndex++;
            }
            $result = $chunkStr . $units[$unitIndex] . $result;
        }
        $unitIndex++;
    }

    return $result . '원';
}

// 이메일 HTML 생성
$email_date = date('Y년 m월 d일', strtotime($quotation['created_at']));
$koreanTotal = numberToKorean($quotation['total_price']);
$quotation_no = $quotation['quotation_no'];
$customerName = $quotation['customer_name'];
$totalSupply = $quotation['total_supply'];
$totalVat = $quotation['total_vat'];
$totalPrice = $quotation['total_price'];
$deliveryType = $quotation['delivery_type'];
$deliveryPrice = $quotation['delivery_price'];

$itemRows = '';
$itemNo = 1;
foreach ($cart_items as $item) {
    $productType = $item['product_type'] ?? '상품';
    $spec = ($item['MY_type'] ?? '') . ' / ' . ($item['Section'] ?? '') . ' / ' . ($item['ordertype'] ?? '');
    $qty = $item['MY_amount'] ?? 1;
    $supply = intval($item['st_price'] ?? 0);

    // 단가 계산: 역계산 검증으로 무한소수는 생략
    $unitPriceDisplay = formatUnitPrice($supply, $qty, '-');

    $itemRows .= "<tr>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>{$itemNo}</td>
        <td style='border: 1px solid #000; padding: 6px;'>{$productType}</td>
        <td style='border: 1px solid #000; padding: 6px;'>{$spec}</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>{$qty}</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>부</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>{$unitPriceDisplay}</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($supply) . " 원</td>
        <td style='border: 1px solid #000; padding: 6px;'></td>
    </tr>";
    $itemNo++;
}

if (!empty($deliveryType) && $deliveryPrice > 0) {
    $itemRows .= "<tr>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>-</td>
        <td style='border: 1px solid #000; padding: 6px;'>택배선불</td>
        <td style='border: 1px solid #000; padding: 6px;'>{$deliveryType}</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>1</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>식</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($deliveryPrice) . "</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($deliveryPrice) . " 원</td>
        <td style='border: 1px solid #000; padding: 6px;'></td>
    </tr>";
}

foreach ($custom_items as $customItem) {
    if (!empty($customItem['item']) && intval($customItem['price']) > 0) {
        $customSupply = intval($customItem['qty']) * intval($customItem['price']);
        $itemRows .= "<tr>
            <td style='border: 1px solid #000; padding: 6px; text-align: center;'>+</td>
            <td style='border: 1px solid #000; padding: 6px;'>" . htmlspecialchars($customItem['item']) . "</td>
            <td style='border: 1px solid #000; padding: 6px;'>" . htmlspecialchars($customItem['spec'] ?? '') . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: center;'>" . intval($customItem['qty']) . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: center;'>" . htmlspecialchars($customItem['unit'] ?? '개') . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($customItem['price']) . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($customSupply) . " 원</td>
            <td style='border: 1px solid #000; padding: 6px;'></td>
        </tr>";
    }
}

$emailBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>견적서 - {$quotation_no}</title></head>
<body style="font-family: '맑은 고딕', sans-serif; padding: 20px; max-width: 800px; margin: 0 auto;">
    <h1 style="text-align: center; font-size: 28px; margin-bottom: 20px;">견 적 서</h1>
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table style="width: 100%;">
                    <tr><td style="border: 1px solid #000; padding: 6px; background: #f5f5f5; width: 80px;">견적번호</td><td style="border: 1px solid #000; padding: 6px;">{$quotation_no}</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 6px; background: #f5f5f5;">견적일자</td><td style="border: 1px solid #000; padding: 6px;">{$email_date}</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 6px; background: #f5f5f5;">유효기간</td><td style="border: 1px solid #000; padding: 6px;">발행일로부터 7일</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 6px; background: #f5f5f5;">담당자</td><td style="border: 1px solid #000; padding: 6px;">{$customerName} 귀하</td></tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <table style="width: 100%;">
                    <tr><td colspan="2" style="border: 1px solid #000; padding: 6px; background: #f5f5f5; text-align: center;">공급자</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 4px; width: 80px;">등록번호</td><td style="border: 1px solid #000; padding: 4px;">607-26-76968</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 4px;">상호</td><td style="border: 1px solid #000; padding: 4px;">두손기획인쇄</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 4px;">대표자</td><td style="border: 1px solid #000; padding: 4px;">이두선</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 4px;">주소</td><td style="border: 1px solid #000; padding: 4px;">부산시 북구 금곡동 144-30 102호</td></tr>
                    <tr><td style="border: 1px solid #000; padding: 4px;">전화</td><td style="border: 1px solid #000; padding: 4px;">051-341-1830</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="width: 100%; text-align: center; margin-bottom: 20px; border: 2px solid #000;">
        <tr><td style="padding: 15px; font-size: 18px;"><strong>합계금액(VAT포함):</strong> <span style="font-size: 22px; color: #c00;">{$koreanTotal}</span> (₩ TOTAL_FORMATTED)</td></tr>
    </table>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="border: 1px solid #000; padding: 8px; width: 40px;">NO</th>
                <th style="border: 1px solid #000; padding: 8px;">품목</th>
                <th style="border: 1px solid #000; padding: 8px;">규격 및 사양</th>
                <th style="border: 1px solid #000; padding: 8px; width: 50px;">수량</th>
                <th style="border: 1px solid #000; padding: 8px; width: 40px;">단위</th>
                <th style="border: 1px solid #000; padding: 8px; width: 80px;">단가</th>
                <th style="border: 1px solid #000; padding: 8px; width: 100px;">공급가액</th>
                <th style="border: 1px solid #000; padding: 8px; width: 60px;">비고</th>
            </tr>
        </thead>
        <tbody>{$itemRows}</tbody>
        <tfoot>
            <tr style="background: #fffde7; font-weight: bold;">
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">합계</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">SUPPLY_FORMATTED 원</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">부가세 (10%)</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">VAT_FORMATTED 원</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr style="background: #e8f5e9; font-weight: bold;">
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">총 합계 (VAT 포함)</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; color: #c00;">TOTAL_FORMATTED2 원</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
        </tfoot>
    </table>
    <table style="width: 100%; margin-top: 20px; border: 1px solid #000;">
        <tr><td style="padding: 10px; background: #f5f5f5; width: 80px; border-right: 1px solid #000;">입금계좌</td><td style="padding: 10px;"><strong>농협은행 301-0185-6461-71</strong> 예금주: 이두선 (두손기획인쇄)</td></tr>
    </table>
    <p style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">본 견적서는 발행일로부터 7일간 유효합니다.<br>문의사항은 051-341-1830으로 연락 부탁드립니다.</p>
</body>
</html>
HTML;

$emailBody = str_replace('TOTAL_FORMATTED', number_format($totalPrice), $emailBody);
$emailBody = str_replace('SUPPLY_FORMATTED', number_format($totalSupply), $emailBody);
$emailBody = str_replace('VAT_FORMATTED', number_format($totalVat), $emailBody);
$emailBody = str_replace('TOTAL_FORMATTED2', number_format($totalPrice), $emailBody);

// PHPMailer
require_once __DIR__ . '/../../shop/mail/side/PHPMailer.php';
require_once __DIR__ . '/../../shop/mail/side/SMTP.php';

try {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->Host = 'smtp.naver.com';
    $mail->Port = 465;
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPAuth = true;
    $mail->Username = 'dsp1830';
    $mail->Password = 'du928128';
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('dsp1830@naver.com', '두손기획인쇄');
    $mail->addReplyTo('dsp1830@naver.com', '두손기획인쇄');
    $mail->addAddress($email, $customerName);

    $mail->Subject = "[두손기획인쇄] 견적서 ({$quotation_no})";
    $mail->isHTML(true);
    $mail->msgHTML($emailBody);

    $emailSent = $mail->send();
    $emailError = $mail->ErrorInfo;
} catch (Exception $e) {
    $emailSent = false;
    $emailError = $e->getMessage();
}

// 로그 저장
$email_status = $emailSent ? 'sent' : 'failed';
$email_subject = "[두손기획인쇄] 견적서 ({$quotation_no})";
$created_by = intval($_SESSION['user_id']);

$log_stmt = mysqli_prepare($db, "INSERT INTO quotation_emails (quotation_id, quotation_no, recipient_email, recipient_name, subject, status, error_message, sent_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($log_stmt, "issssssi", $id, $quotation_no, $email, $customerName, $email_subject, $email_status, $emailError, $created_by);
mysqli_stmt_execute($log_stmt);
mysqli_stmt_close($log_stmt);

if ($emailSent) {
    // 상태 업데이트
    $update_stmt = mysqli_prepare($db, "UPDATE quotations SET status = 'sent', customer_email = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "si", $email, $id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);

    echo json_encode(['success' => true, 'message' => '이메일이 발송되었습니다.'], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => '이메일 발송 실패: ' . $emailError], JSON_UNESCAPED_UNICODE);
}
?>
