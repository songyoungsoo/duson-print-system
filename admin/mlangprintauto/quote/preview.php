<?php
/**
 * 견적서 미리보기 (HTML)
 * 인쇄/PDF용 페이지 - 독립 페이지 (완전 격리)
 */

// 1. 기존 출력 버퍼 모두 클리어
while (ob_get_level()) {
    ob_end_clean();
}

// 2. 새 버퍼 시작 (include 파일들의 출력 캡처용)
ob_start();

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once __DIR__ . '/includes/AdminQuoteManager.php';
require_once __DIR__ . '/includes/QuoteRenderer.php';

// 3. include에서 발생한 모든 출력 버리기
ob_end_clean();

// 권한 체크
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/mlangprintauto/login.php');
    exit;
}

$quoteId = intval($_GET['id'] ?? 0);

if ($quoteId <= 0) {
    die('잘못된 요청입니다.');
}

try {
    $manager = new AdminQuoteManager($db);
    $quote = $manager->getQuote($quoteId);

    if (!$quote) {
        die('견적서를 찾을 수 없습니다.');
    }

    $items = $manager->getQuoteItems($quoteId);

    $renderer = new QuoteRenderer($quote, $items);

    // 순수한 견적서 HTML만 출력
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $renderer->renderHTML();
    exit;

} catch (Exception $e) {
    die('오류: ' . $e->getMessage());
}
