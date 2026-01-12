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

// 권한 체크 (admin_logged_in 또는 admin_id)
if (empty($_SESSION['admin_logged_in']) && empty($_SESSION['admin_id'])) {
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

    // 견적서 HTML 가져오기
    $quoteHtml = $renderer->renderHTML();

    // 기존 print-controls 제거하고 새 툴바 추가
    $quoteHtml = preg_replace('/<div class="(print-controls|aq-print-controls)".*?<\/div>/s', '', $quoteHtml);

    // 새 툴바 HTML
    $toolbarHtml = <<<HTML
    <div class="preview-toolbar" style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 9999;
    ">
        <div style="color: white; font-size: 16px; font-weight: 600;">
            📄 견적서 미리보기 - {$quote['quote_no']}
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print();" style="
                padding: 10px 24px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                border: none;
                border-radius: 6px;
                background: #48bb78;
                color: white;
                display: flex;
                align-items: center;
                gap: 6px;
            ">🖨️ 인쇄하기</button>
            <a href="api/generate_pdf.php?id={$quoteId}" style="
                padding: 10px 24px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                border: none;
                border-radius: 6px;
                background: #e53e3e;
                color: white;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 6px;
            ">📥 PDF 저장</a>
            <button onclick="window.close();" style="
                padding: 10px 24px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                border: none;
                border-radius: 6px;
                background: #718096;
                color: white;
            ">✕ 닫기</button>
        </div>
    </div>
    <style>
        @media print {
            .preview-toolbar { display: none !important; }
            body { padding-top: 0 !important; padding-right: 0 !important; padding-bottom: 0 !important; padding-left: 0 !important; }
        }
        body { padding-top: 0 !important; padding-right: 0 !important; padding-bottom: 0 !important; padding-left: 0 !important; }
    </style>
HTML;

    // </body> 태그 앞에 툴바 삽입
    $quoteHtml = str_replace('</body>', $toolbarHtml . '</body>', $quoteHtml);

    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $quoteHtml;
    exit;

} catch (Exception $e) {
    die('오류: ' . $e->getMessage());
}
