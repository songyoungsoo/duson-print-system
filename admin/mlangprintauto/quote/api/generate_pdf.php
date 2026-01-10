<?php
/**
 * 견적서 PDF 생성 API
 * GET: ?id=quote_id&mode=D|I
 * Response: PDF 파일 다운로드 또는 브라우저 표시
 */

session_start();

// Admin authentication check
if (empty($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 401 Unauthorized');
    die('로그인이 필요합니다.');
}

// DB 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once dirname(__DIR__) . '/includes/AdminQuoteManager.php';
require_once dirname(__DIR__) . '/includes/QuoteRenderer.php';

// 견적 ID 확인
$quoteId = intval($_GET['id'] ?? 0);
if ($quoteId <= 0) {
    header('HTTP/1.1 400 Bad Request');
    die('견적 ID가 필요합니다.');
}

// 출력 모드: D=다운로드, I=브라우저
$mode = $_GET['mode'] ?? 'D';
if (!in_array($mode, ['D', 'I'])) {
    $mode = 'D';
}

try {
    $manager = new AdminQuoteManager($db);

    // 견적 조회
    $quote = $manager->getQuote($quoteId);
    if (!$quote) {
        header('HTTP/1.1 404 Not Found');
        die('견적을 찾을 수 없습니다.');
    }

    // 품목 조회
    $items = $manager->getQuoteItems($quoteId);
    if (empty($items)) {
        header('HTTP/1.1 400 Bad Request');
        die('견적 품목이 없습니다.');
    }

    // PDF 렌더링
    $renderer = new QuoteRenderer($quote, $items);
    $renderer->renderPDF(null, $mode);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('PDF 생성 오류: ' . $e->getMessage());
}
