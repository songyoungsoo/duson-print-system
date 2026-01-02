<?php
/**
 * 견적서 이메일 발송 API
 * save.php에서 호출되거나 직접 호출 가능
 */

// PHPMailer 로드 (기존 mlangorder_printauto의 PHPMailer 사용)
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

        // 금액 한글 변환
        $grandTotalKr = numberToKorean($quote['grand_total']);

        // 이메일 본문 생성
        $emailDate = date('Y년 m월 d일');
        $validUntil = date('Y년 m월 d일', strtotime($quote['valid_until']));

        // 품목 HTML 생성
        $itemsHtml = '';
        $no = 1;
        foreach ($items as $item) {
            $itemsHtml .= "<tr>
                <td style='border:1px solid #000;padding:6px;text-align:center;'>{$no}</td>
                <td style='border:1px solid #000;padding:6px;'>" . htmlspecialchars($item['product_name']) . "</td>
                <td style='border:1px solid #000;padding:6px;'>" . htmlspecialchars($item['specification']) . "</td>
                <td style='border:1px solid #000;padding:6px;text-align:center;'>" . number_format($item['quantity']) . "</td>
                <td style='border:1px solid #000;padding:6px;text-align:center;'>" . htmlspecialchars($item['unit']) . "</td>
                <td style='border:1px solid #000;padding:6px;text-align:right;'>" . number_format($item['unit_price']) . "</td>
                <td style='border:1px solid #000;padding:6px;text-align:right;'>" . number_format($item['total_price']) . "</td>
            </tr>";
            $no++;
        }

        // 이메일 HTML
        $emailBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>견적서 - {$quoteNo}</title>
</head>
<body style="font-family:'Malgun Gothic',sans-serif;margin:0;padding:20px;background:#f5f5f5;">
    <div style="max-width:800px;margin:0 auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">

        <h1 style="text-align:center;font-size:28px;margin-bottom:30px;border-bottom:3px double #333;padding-bottom:15px;">견 적 서</h1>

        <table style="width:100%;margin-bottom:20px;">
            <tr>
                <td style="width:50%;vertical-align:top;">
                    <table style="width:100%;">
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">견적번호</td>
                            <td style="border:1px solid #000;padding:6px;font-weight:bold;">{$quoteNo}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">견적일자</td>
                            <td style="border:1px solid #000;padding:6px;">{$emailDate}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">유효기간</td>
                            <td style="border:1px solid #000;padding:6px;">{$validUntil}까지</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">고객명</td>
                            <td style="border:1px solid #000;padding:6px;">" . htmlspecialchars($quote['customer_name']) . " 귀하</td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:20px;">
                    <table style="width:100%;">
                        <tr>
                            <td colspan="2" style="border:1px solid #000;padding:6px;background:#f5f5f5;text-align:center;">공급자</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">등록번호</td>
                            <td style="border:1px solid #000;padding:6px;">" . htmlspecialchars($company['business_number'] ?? '107-06-45106') . "</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">상호</td>
                            <td style="border:1px solid #000;padding:6px;">" . htmlspecialchars($company['company_name'] ?? '두손기획인쇄') . "</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">대표자</td>
                            <td style="border:1px solid #000;padding:6px;">" . htmlspecialchars($company['representative'] ?? '차경선(직인생략)') . "</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;padding:6px;background:#f5f5f5;">연락처</td>
                            <td style="border:1px solid #000;padding:6px;">" . htmlspecialchars($company['phone'] ?? '02-2632-1830') . "</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="background:#0d6efd;color:#fff;padding:15px;text-align:center;margin-bottom:20px;border-radius:4px;">
            <span style="font-size:16px;">합계금액 (부가세포함)</span>
            <span style="font-size:28px;font-weight:bold;margin-left:20px;">" . number_format($quote['grand_total']) . " 원</span>
            <div style="font-size:14px;margin-top:5px;">일금 {$grandTotalKr} 원정</div>
        </div>

        <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
            <thead>
                <tr style="background:#333;color:#fff;">
                    <th style="border:1px solid #000;padding:8px;">NO</th>
                    <th style="border:1px solid #000;padding:8px;">품명</th>
                    <th style="border:1px solid #000;padding:8px;">규격/사양</th>
                    <th style="border:1px solid #000;padding:8px;">수량</th>
                    <th style="border:1px solid #000;padding:8px;">단위</th>
                    <th style="border:1px solid #000;padding:8px;">단가</th>
                    <th style="border:1px solid #000;padding:8px;">금액</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHtml}
            </tbody>
        </table>

        <table style="width:300px;margin-left:auto;border-collapse:collapse;">
            <tr>
                <td style="border:1px solid #000;padding:8px;background:#f5f5f5;">공급가액</td>
                <td style="border:1px solid #000;padding:8px;text-align:right;">" . number_format($quote['supply_total']) . " 원</td>
            </tr>
            <tr>
                <td style="border:1px solid #000;padding:8px;background:#f5f5f5;">부가세 (VAT)</td>
                <td style="border:1px solid #000;padding:8px;text-align:right;">" . number_format($quote['vat_total']) . " 원</td>
            </tr>
            " . ($quote['delivery_price'] > 0 ? "<tr>
                <td style='border:1px solid #000;padding:8px;background:#f5f5f5;'>배송비</td>
                <td style='border:1px solid #000;padding:8px;text-align:right;'>" . number_format($quote['delivery_price']) . " 원</td>
            </tr>" : "") . "
            " . ($quote['discount_amount'] > 0 ? "<tr>
                <td style='border:1px solid #000;padding:8px;background:#f5f5f5;'>할인</td>
                <td style='border:1px solid #000;padding:8px;text-align:right;'>-" . number_format($quote['discount_amount']) . " 원</td>
            </tr>" : "") . "
            <tr style="background:#0d6efd;color:#fff;">
                <td style="border:1px solid #000;padding:10px;font-weight:bold;">합계</td>
                <td style="border:1px solid #000;padding:10px;text-align:right;font-weight:bold;font-size:18px;">" . number_format($quote['grand_total']) . " 원</td>
            </tr>
        </table>

        <div style="margin-top:30px;padding:20px;background:#f8f9fa;border-radius:4px;">
            <p style="margin:0 0 10px 0;"><strong>결제조건:</strong> " . htmlspecialchars($quote['payment_terms'] ?? '발행일로부터 7일') . "</p>
            <p style="margin:0;"><strong>온라인 견적서 확인:</strong> <a href='" . $manager->getPublicUrl($quote['public_token']) . "' style='color:#0d6efd;'>여기를 클릭하세요</a></p>
        </div>

        <div style="margin-top:30px;text-align:center;color:#666;font-size:12px;border-top:1px solid #ddd;padding-top:20px;">
            <p>본 견적서는 {$validUntil}까지 유효합니다.</p>
            <p>문의: " . htmlspecialchars($company['phone'] ?? '02-2632-1830') . " | " . htmlspecialchars($company['email'] ?? 'dsp1830@naver.com') . "</p>
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
        $mail->Password = 'MC8T8Z83B149';
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('dsp1830@naver.com', '두손기획인쇄');
        $mail->addReplyTo('dsp1830@naver.com', '두손기획인쇄');
        $mail->addAddress($recipientEmail, $recipientName ?: $quote['customer_name']);

        // CC 추가
        if (!empty($ccEmail) && filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addCC($ccEmail);
        }

        $mail->Subject = "[두손기획인쇄] 견적서 ({$quoteNo})";
        $mail->isHTML(true);
        $mail->msgHTML($emailBody);
        $mail->AltBody = "두손기획인쇄 견적서\n견적번호: {$quoteNo}\n합계금액: " . number_format($quote['grand_total']) . "원 (VAT포함)";

        $mail->send();

        // 발송 이력 저장
        $logQuery = "INSERT INTO quote_emails (quote_id, quote_no, recipient_email, recipient_name, cc_email, subject, status, sent_by) VALUES (?, ?, ?, ?, ?, ?, 'sent', ?)";
        $stmt = mysqli_prepare($db, $logQuery);
        $subject = "[두손기획인쇄] 견적서 ({$quoteNo})";
        $sentBy = intval($_SESSION['user_id'] ?? 0);
        mysqli_stmt_bind_param($stmt, "isssssi", $quoteId, $quoteNo, $recipientEmail, $recipientName, $ccEmail, $subject, $sentBy);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return ['success' => true, 'message' => '이메일이 발송되었습니다.'];

    } catch (Exception $e) {
        // 실패 이력 저장
        $logQuery = "INSERT INTO quote_emails (quote_id, quote_no, recipient_email, recipient_name, subject, status, error_message, sent_by) VALUES (?, ?, ?, ?, ?, 'failed', ?, ?)";
        $stmt = mysqli_prepare($db, $logQuery);
        $subject = "[두손기획인쇄] 견적서";
        $errorMsg = $e->getMessage();
        $sentBy = intval($_SESSION['user_id'] ?? 0);
        mysqli_stmt_bind_param($stmt, "issssis", $quoteId, $quoteNo ?? '', $recipientEmail, $recipientName, $subject, $errorMsg, $sentBy);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

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
