<?php
/**
 * 견적서 이메일 발송 API (개선된 디자인)
 * save.php에서 호출되거나 직접 호출 가능
 */

// PHPMailer 로드
require_once __DIR__ . '/../../../mlangorder_printauto/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../../mlangorder_printauto/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../../mlangorder_printauto/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * 견적서 이메일 발송 함수
 */
function sendQuotationEmail($db, $quoteId, $recipientEmail, $recipientName = '', $ccEmail = '') {
    require_once __DIR__ . '/../includes/QuoteManager.php';

    try {
        $manager = new QuoteManager($db);
        $quote = $manager->getById($quoteId);

        if (!$quote) {
            return ['success' => false, 'message' => '견적서를 찾을 수 없습니다.'];
        }

        $company = $manager->getCompanySettings();
        $quoteNo = $quote['quote_no'];
        $items = $quote['items'];

        // 변수 준비
        $customerName = htmlspecialchars($quote['customer_name']);
        $grandTotalKr = numberToKorean($quote['grand_total']);
        $grandTotalFmt = number_format($quote['grand_total']);
        $supplyTotalFmt = number_format($quote['supply_total']);
        $vatTotalFmt = number_format($quote['vat_total']);
        $emailDate = date('Y년 m월 d일');
        $validUntil = date('Y년 m월 d일', strtotime($quote['valid_until']));
        $publicUrl = $manager->getPublicUrl($quote['public_token']);

        $businessNumber = htmlspecialchars($company['business_number'] ?? '107-06-45106');
        $companyName = htmlspecialchars($company['company_name'] ?? '두손기획인쇄');
        $representative = htmlspecialchars($company['representative'] ?? '차경선(직인생략)');
        $companyPhone = htmlspecialchars($company['phone'] ?? '02-2632-1830');
        $companyEmail = htmlspecialchars($company['email'] ?? 'dsp1830@naver.com');
        $paymentTerms = htmlspecialchars($quote['payment_terms'] ?? '발행일로부터 7일');

        // 품목 HTML 생성
        $itemsHtml = '';
        $no = 1;
        foreach ($items as $item) {
            $qty = $item['quantity'];
            $qtyDisplay = ($qty == intval($qty)) ? number_format($qty) : rtrim(rtrim(number_format($qty, 2), '0'), '.');

            // 전단지(inserted)인 경우 매수 표시 추가
            if ($item['product_type'] == 'inserted' && !empty($item['source_data'])) {
                $sourceData = json_decode($item['source_data'], true);
                if (!empty($sourceData['mesu'])) {
                    $qtyDisplay .= '<br><span style="font-size:9px;color:#888;">(' . number_format($sourceData['mesu']) . '매)</span>';
                }
            }

            $productName = htmlspecialchars($item['product_name']);
            $specification = htmlspecialchars($item['specification']);
            $unit = htmlspecialchars($item['unit']);

            // 전단지는 소수점 1자리 표시
            if ($item['product_type'] == 'inserted') {
                $unitPriceFmt = number_format($item['unit_price'], 1);
            } else {
                $unitPriceFmt = number_format($item['unit_price']);
            }

            $totalPriceFmt = number_format($item['total_price']);

            $itemsHtml .= "<tr>
                <td style='border:1px solid #ddd;padding:5px;text-align:center;font-size:12px;'>{$no}</td>
                <td style='border:1px solid #ddd;padding:5px;font-size:12px;'>{$productName}</td>
                <td style='border:1px solid #ddd;padding:5px;font-size:11px;line-height:1.4;'>{$specification}</td>
                <td style='border:1px solid #ddd;padding:5px;text-align:center;font-size:12px;'>{$qtyDisplay}</td>
                <td style='border:1px solid #ddd;padding:5px;text-align:center;font-size:12px;'>{$unit}</td>
                <td style='border:1px solid #ddd;padding:5px;text-align:right;font-size:12px;'>{$unitPriceFmt}</td>
                <td style='border:1px solid #ddd;padding:5px;text-align:right;font-size:12px;font-weight:600;'>{$totalPriceFmt}</td>
            </tr>";
            $no++;
        }

        // 배송비/할인 행
        $deliveryRow = '';
        if ($quote['delivery_price'] > 0) {
            $deliveryFmt = number_format($quote['delivery_price']);
            $deliveryRow = "<tr>
                <td style='border:1px solid #ddd;padding:6px;background:#fafafa;font-size:12px;'>배송비</td>
                <td style='border:1px solid #ddd;padding:6px;text-align:right;font-size:12px;'>{$deliveryFmt} 원</td>
            </tr>";
        }

        $discountRow = '';
        if ($quote['discount_amount'] > 0) {
            $discountFmt = number_format($quote['discount_amount']);
            $discountRow = "<tr>
                <td style='border:1px solid #ddd;padding:6px;background:#fafafa;font-size:12px;'>할인</td>
                <td style='border:1px solid #ddd;padding:6px;text-align:right;font-size:12px;color:#e74c3c;'>-{$discountFmt} 원</td>
            </tr>";
        }

        // 이메일 HTML
        $emailBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>견적서 - {$quoteNo}</title>
</head>
<body style="font-family:'Noto Sans KR','Noto Sans',sans-serif;margin:0;padding:12px;background:#f8f9fa;font-size:13px;line-height:1.5;color:#333;">
    <div style="max-width:750px;margin:0 auto;background:#fff;padding:20px;border-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">

        <div style="text-align:center;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #2c3e50;">
            <h1 style="margin:0;font-size:18px;font-weight:700;letter-spacing:-0.3px;color:#2c3e50;">견 적 서</h1>
        </div>

        <table style="width:100%;margin-bottom:15px;font-size:12px;">
            <tr>
                <td style="width:50%;vertical-align:top;padding-right:8px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;width:70px;font-size:11px;">견적번호</td>
                            <td style="border:1px solid #ddd;padding:4px;font-weight:600;font-size:12px;">{$quoteNo}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;font-size:11px;">견적일자</td>
                            <td style="border:1px solid #ddd;padding:4px;font-size:12px;">{$emailDate}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;font-size:11px;">유효기간</td>
                            <td style="border:1px solid #ddd;padding:4px;font-size:12px;">{$validUntil}까지</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;font-size:11px;">고객명</td>
                            <td style="border:1px solid #ddd;padding:4px;font-size:12px;">{$customerName} 귀하</td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:8px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td colspan="2" style="border:1px solid #ddd;padding:4px;background:#2c3e50;text-align:center;color:#fff;font-size:11px;font-weight:600;">공급자</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;width:70px;font-size:11px;">등록번호</td>
                            <td style="border:1px solid #ddd;padding:4px;font-size:12px;">{$businessNumber}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;font-size:11px;">상호</td>
                            <td style="border:1px solid #ddd;padding:4px;font-size:12px;font-weight:600;">{$companyName}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;font-size:11px;">대표자</td>
                            <td style="border:1px solid #ddd;padding:4px;font-size:12px;">{$representative}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ddd;padding:4px;background:#f8f9fa;font-size:11px;">연락처</td>
                            <td style="border:1px solid #ddd;padding:4px;font-size:12px;">{$companyPhone}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:10px;text-align:center;margin-bottom:15px;border-radius:3px;">
            <div style="font-size:11px;opacity:0.9;margin-bottom:2px;">합계금액 (부가세포함)</div>
            <div style="font-size:18px;font-weight:700;margin-bottom:2px;">{$grandTotalFmt} 원</div>
            <div style="font-size:10px;opacity:0.85;">일금 {$grandTotalKr} 원정</div>
        </div>

        <table style="width:100%;border-collapse:collapse;margin-bottom:15px;">
            <thead>
                <tr style="background:#34495e;color:#fff;">
                    <th style="border:1px solid #34495e;padding:6px;font-size:11px;font-weight:600;">NO</th>
                    <th style="border:1px solid #34495e;padding:6px;font-size:11px;font-weight:600;">품명</th>
                    <th style="border:1px solid #34495e;padding:6px;font-size:11px;font-weight:600;">규격/사양</th>
                    <th style="border:1px solid #34495e;padding:6px;font-size:11px;font-weight:600;">수량</th>
                    <th style="border:1px solid #34495e;padding:6px;font-size:11px;font-weight:600;">단위</th>
                    <th style="border:1px solid #34495e;padding:6px;font-size:11px;font-weight:600;">단가</th>
                    <th style="border:1px solid #34495e;padding:6px;font-size:11px;font-weight:600;">금액</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHtml}
            </tbody>
        </table>

        <table style="width:280px;margin-left:auto;border-collapse:collapse;margin-bottom:15px;">
            <tr>
                <td style="border:1px solid #ddd;padding:5px;background:#f8f9fa;font-size:11px;">공급가액</td>
                <td style="border:1px solid #ddd;padding:5px;text-align:right;font-size:12px;">{$supplyTotalFmt} 원</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:5px;background:#f8f9fa;font-size:11px;">부가세 (VAT)</td>
                <td style="border:1px solid #ddd;padding:5px;text-align:right;font-size:12px;">{$vatTotalFmt} 원</td>
            </tr>
            {$deliveryRow}
            {$discountRow}
            <tr style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;">
                <td style="border:1px solid #667eea;padding:7px;font-weight:600;font-size:12px;">합계</td>
                <td style="border:1px solid #667eea;padding:7px;text-align:right;font-weight:700;font-size:14px;">{$grandTotalFmt} 원</td>
            </tr>
        </table>

        <div style="margin-bottom:12px;padding:12px;background:#f8f9fa;border-radius:3px;border-left:3px solid #667eea;">
            <p style="margin:0 0 6px 0;font-size:11px;"><strong style="color:#667eea;">결제조건:</strong> <span style="font-size:12px;">{$paymentTerms}</span></p>
            <p style="margin:0;font-size:11px;"><strong style="color:#667eea;">온라인 확인:</strong> <a href="{$publicUrl}" style="color:#667eea;text-decoration:none;font-weight:600;">견적서 보기 →</a></p>
        </div>

        <div style="text-align:center;color:#95a5a6;font-size:10px;border-top:1px solid #ecf0f1;padding-top:12px;">
            <p style="margin:0 0 4px 0;">본 견적서는 {$validUntil}까지 유효합니다.</p>
            <p style="margin:0;">문의 {$companyPhone} | {$companyEmail}</p>
        </div>
    </div>
</body>
</html>
HTML;

        // PHPMailer로 발송
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.naver.com';
        $mail->Port = 465;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Username = 'dsp1830';
        $mail->Password = '2CP3P5BTS83Y';
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('dsp1830@naver.com', '두손기획인쇄');
        $mail->addReplyTo('dsp1830@naver.com', '두손기획인쇄');
        $mail->addAddress($recipientEmail, $recipientName ?: $quote['customer_name']);

        if (!empty($ccEmail) && filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addCC($ccEmail);
        }

        $mail->Subject = "[두손기획인쇄] 견적서 ({$quoteNo})";
        $mail->isHTML(true);
        $mail->msgHTML($emailBody);
        $mail->AltBody = "두손기획인쇄 견적서\n견적번호: {$quoteNo}\n합계금액: {$grandTotalFmt}원 (VAT포함)";

        $mail->send();

        // 발송 이력 저장 (테이블이 없을 수 있으므로 오류 무시)
        $logQuery = "INSERT INTO quote_emails (quote_id, quote_no, recipient_email, recipient_name, cc_email, subject, status, sent_by) VALUES (?, ?, ?, ?, ?, ?, 'sent', ?)";
        $stmt = mysqli_prepare($db, $logQuery);
        if ($stmt) {
            $subject = "[두손기획인쇄] 견적서 ({$quoteNo})";
            $sentBy = intval($_SESSION['user_id'] ?? 0);
            mysqli_stmt_bind_param($stmt, "isssssi", $quoteId, $quoteNo, $recipientEmail, $recipientName, $ccEmail, $subject, $sentBy);
            @mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        return ['success' => true, 'message' => '이메일이 발송되었습니다.'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => '이메일 발송 실패: ' . $e->getMessage()];
    }
}

/**
 * 숫자를 한글로 변환
 */
function numberToKorean($number) {
    $units = ['', '만', '억', '조'];
    $digits = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
    $subUnits = ['', '십', '백', '천'];

    if ($number == 0) return '영';

    $result = '';
    $unitIndex = 0;
    $number = intval($number);

    while ($number > 0) {
        $part = $number % 10000;
        $number = intval($number / 10000);

        if ($part > 0) {
            $partStr = '';
            $subUnitIndex = 0;

            while ($part > 0) {
                $digit = $part % 10;
                $part = intval($part / 10);

                if ($digit > 0) {
                    $digitStr = ($digit == 1 && $subUnitIndex > 0) ? '' : $digits[$digit];
                    $partStr = $digitStr . $subUnits[$subUnitIndex] . $partStr;
                }
                $subUnitIndex++;
            }

            $result = $partStr . $units[$unitIndex] . $result;
        }
        $unitIndex++;
    }

    return $result;
}

// 직접 호출 시 (POST 요청)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['SCRIPT_FILENAME']) === 'send_email.php') {
    session_start();
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../db.php';

    $quoteId = intval($_POST['quote_id'] ?? 0);
    $recipientEmail = trim($_POST['recipient_email'] ?? '');
    $recipientName = trim($_POST['recipient_name'] ?? '');
    $ccEmail = trim($_POST['cc_email'] ?? '');

    if (!$quoteId || !$recipientEmail) {
        echo json_encode(['success' => false, 'message' => '필수 정보가 누락되었습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = sendQuotationEmail($db, $quoteId, $recipientEmail, $recipientName, $ccEmail);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
?>
