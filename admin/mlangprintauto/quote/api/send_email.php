<?php
/**
 * 견적서 이메일 발송 API
 */
// 출력 버퍼링 시작 (Deprecated 경고 등 모든 출력 캡처)
ob_start();

// 모든 에러/경고 출력 완전히 억제 (JSON 응답 보호)
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// 응답 함수: 버퍼 비우고 JSON 출력
function sendJsonResponse($data) {
    ob_end_clean(); // 버퍼 내용 버리기
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 세션 시작 및 인증 확인
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    sendJsonResponse(['success' => false, 'message' => '로그인이 필요합니다.']);
}

// DB 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once dirname(__DIR__) . '/includes/AdminQuoteManager.php';
require_once dirname(__DIR__) . '/includes/QuoteRenderer.php';

// PHPMailer 로드 (경고 억제)
$phpmailerPath = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/PHPMailer/';
if (file_exists($phpmailerPath . 'PHPMailer.php')) {
    @require_once $phpmailerPath . 'PHPMailer.php';
    @require_once $phpmailerPath . 'SMTP.php';
    @require_once $phpmailerPath . 'Exception.php';
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php')) {
    @require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
} else {
    sendJsonResponse(['success' => false, 'message' => 'PHPMailer를 찾을 수 없습니다.']);
}

// JSON 입력 파싱
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    sendJsonResponse(['success' => false, 'message' => '잘못된 요청입니다.']);
}

// 필수 필드 검증
$quoteId = intval($input['quote_id'] ?? 0);
if ($quoteId <= 0) {
    sendJsonResponse(['success' => false, 'message' => '견적 ID가 필요합니다.']);
}

$recipientEmail = trim($input['recipient_email'] ?? '');
if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['success' => false, 'message' => '올바른 이메일 주소를 입력해주세요.']);
}

$ccEmail = trim($input['cc_email'] ?? '');
$customMessage = trim($input['message'] ?? '');

try {
    $manager = new AdminQuoteManager($db);

    // 견적 조회
    $quote = $manager->getQuote($quoteId);
    if (!$quote) {
        sendJsonResponse(['success' => false, 'message' => '견적을 찾을 수 없습니다.']);
    }

    // 품목 조회
    $items = $manager->getQuoteItems($quoteId);
    if (empty($items)) {
        sendJsonResponse(['success' => false, 'message' => '견적 품목이 없습니다.']);
    }

    // 이메일 본문 생성
    $renderer = new QuoteRenderer($quote, $items);
    $emailBody = $renderer->renderEmailBody();

    // 커스텀 메시지가 있으면 본문에 추가
    if (!empty($customMessage)) {
        $customHtml = '<div style="background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #ffc107;">';
        $customHtml .= '<strong>담당자 메시지:</strong><br>';
        $customHtml .= nl2br(htmlspecialchars($customMessage));
        $customHtml .= '</div>';

        // 본문 시작 부분에 삽입
        $emailBody = str_replace('<div style="padding: 20px; background: #f8f9fa;">',
            '<div style="padding: 20px; background: #f8f9fa;">' . $customHtml, $emailBody);
    }

    // 이메일 발송
    $mail = new PHPMailer(true);

    try {
        // SMTP 설정
        $mail->isSMTP();
        $mail->SMTPDebug = 0;  // SMTP::DEBUG_OFF 대신 숫자 사용 (구버전 호환)
        $mail->Host = 'smtp.naver.com';
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';  // PHPMailer::ENCRYPTION_SMTPS 대신 문자열 사용 (구버전 호환)
        $mail->SMTPAuth = true;
        $mail->Username = 'dsp1830';
        $mail->Password = '2CP3P5BTS83Y';
        $mail->CharSet = 'UTF-8';

        // 발신자/수신자 설정
        $mail->setFrom('dsp1830@naver.com', '두손기획인쇄');
        $mail->addReplyTo('dsp1830@naver.com', '두손기획인쇄');
        $mail->addAddress($recipientEmail, $quote['customer_name']);

        // CC가 있으면 추가
        if (!empty($ccEmail) && filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addCC($ccEmail);
        }

        // 이메일 내용
        $mail->Subject = '[두손기획인쇄] 견적서 (' . $quote['quote_no'] . ')';
        $mail->isHTML(true);
        $mail->msgHTML($emailBody);
        $mail->AltBody = '두손기획인쇄 견적서 - 견적번호: ' . $quote['quote_no']
            . ' / 총 견적금액: ' . number_format($quote['grand_total']) . '원 (VAT포함)';

        // PDF 첨부 (\Throwable로 PHP 기본 Exception도 포착)
        try {
            $pdfContent = $renderer->renderPDF(null, 'S');
            if ($pdfContent) {
                $mail->addStringAttachment($pdfContent, '견적서_' . $quote['quote_no'] . '.pdf', 'base64', 'application/pdf');
            }
        } catch (\Throwable $pdfError) {
            // PDF 첨부 실패해도 이메일은 발송
        }

        $mail->send();

        // 상태 업데이트: 발송됨
        $manager->updateStatus($quoteId, 'sent');

        sendJsonResponse([
            'success' => true,
            'message' => '견적서가 이메일로 발송되었습니다.',
            'recipient' => $recipientEmail
        ]);

    } catch (\Throwable $e) {
        sendJsonResponse([
            'success' => false,
            'message' => '이메일 발송 실패: ' . ($mail->ErrorInfo ?? $e->getMessage())
        ]);
    }

} catch (\Throwable $e) {
    sendJsonResponse([
        'success' => false,
        'message' => '오류: ' . $e->getMessage()
    ]);
}
