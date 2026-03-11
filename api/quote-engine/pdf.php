<?php
/**
 * 견적엔진 API — PDF 출력
 * GET /api/quote-engine/pdf.php?id=123         → PDF 다운로드
 * GET /api/quote-engine/pdf.php?id=123&inline=1 → 브라우저에서 보기
 */
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die('Unauthorized');
}

require_once __DIR__ . '/../../db.php';
mysqli_set_charset($db, 'utf8mb4');
require_once __DIR__ . '/../../includes/quote-engine/QuoteEngine.php';

$quoteId = intval($_GET['id'] ?? 0);
$inline  = isset($_GET['inline']);

if (!$quoteId) {
    http_response_code(400);
    die('Missing quote ID');
}

$engine = new QE_QuoteEngine($db);
$quote  = $engine->getQuote($quoteId);
if (!$quote) {
    http_response_code(404);
    die('Quote not found');
}

$items = $quote['items'] ?? [];

// ── Convert QE items to standard layout format ──
$layoutItems = [];
foreach ($items as $item) {
    $layoutItems[] = [
        'product_name'    => $item['product_name'],
        'specification'   => $item['specification'] ?? '',
        'quantity_display' => number_format($item['quantity']),
        'unit'            => $item['unit'] ?? '개',
        'unit_price'      => $item['unit_price'],
        'supply_price'    => $item['supply_price'],
        'notes'           => $item['note'] ?? '',
    ];
}

// ── Build layout-compatible quote data ──
$layoutQuote = [
    'quote_no'         => $quote['quote_no'],
    'quote_date'       => $quote['created_at'],
    'customer_company' => $quote['customer_company'] ?? '',
    'customer_name'    => $quote['customer_name'] ?? '',
    'customer_email'   => $quote['customer_email'] ?? '',
    'validity_days'    => $quote['valid_days'] ?? 7,
];

// ── Supplier info (same as standard system) ──
$supplier = [
    'company_name'   => '두손기획인쇄',
    'business_no'    => '107-06-45106',
    'ceo_name'       => '차경선',
    'address'        => '서울시 영등포구 영등포로 36길9 송호빌딩 1층',
    'phone'          => '02-2632-1830',
    'fax'            => '02-2632-1829',
    'email'          => 'dsp1830@naver.com',
    'account_holder' => '두손기획인쇄 차경선',
    'bank_accounts'  => [
        ['bank_name' => '국민은행', 'account_no' => '999-1688-2384'],
        ['bank_name' => '신한은행', 'account_no' => '110-342-543507'],
        ['bank_name' => '농협',     'account_no' => '301-2632-1830-11'],
    ],
];

// ── Render standard layout HTML ──
require_once __DIR__ . '/../../mlangprintauto/quote/standard/layout.php';

$baseUrl   = 'https://dsp114.com';
$quoteHtml = renderQuoteLayout($layoutQuote, $layoutItems, $supplier, $baseUrl);

// ── Build full HTML document for mPDF ──
$fullHtml = <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<style>
* { font-family: nanumgothic, sans-serif; }
body { font-size: 13px; line-height: 1.4; color: #000; }
</style>
</head>
<body>{$quoteHtml}</body>
</html>
HTML;

// ── Generate filename ──
$docLabel = ($quote['doc_type'] === 'transaction') ? 'Transaction' : 'Quote';
$filename = $docLabel . '_' . $quote['quote_no'] . '.pdf';

// ── Composer autoload ──
$autoloadPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    '/var/www/html/vendor/autoload.php',
];
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

// ── mPDF generation (copied from standard/pdf.php) ──
if (class_exists('Mpdf\Mpdf')) {
    try {
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'margin_left'    => 15,
            'margin_right'   => 15,
            'margin_top'     => 15,
            'margin_bottom'  => 15,
            'default_font'   => 'nanumgothic',
            'tempDir'        => '/tmp/mpdf',
            'fontDir'        => array_merge($fontDirs, ['/usr/share/fonts/truetype/nanum']),
            'fontdata'       => $fontData + [
                'nanumgothic' => [
                    'R' => 'NanumGothic.ttf',
                    'B' => 'NanumGothicBold.ttf',
                ],
            ],
        ]);

        $mpdf->WriteHTML($fullHtml);

        if ($inline) {
            $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        } else {
            $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
        }
        exit;

    } catch (\Exception $e) {
        error_log('QE PDF mPDF Error: ' . $e->getMessage());
    }
}

// ── Dompdf fallback ──
if (class_exists('Dompdf\Dompdf')) {
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($fullHtml);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    if ($inline) {
        $dompdf->stream($filename, ['Attachment' => false]);
    } else {
        $dompdf->stream($filename, ['Attachment' => true]);
    }
    exit;
}

// ── No PDF library: HTML fallback ──
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>PDF 견적서 - <?php echo htmlspecialchars($quote['quote_no'] ?? ''); ?></title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; background: #fff; padding: 20px; max-width: 800px; margin: 0 auto; }
        .notice { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin-bottom: 20px; font-size: 13px; color: #856404; }
        .notice code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        @media print { .notice { display: none; } }
    </style>
</head>
<body>
    <div class="notice">
        <strong>PDF 라이브러리가 설치되지 않았습니다.</strong><br>
        mPDF 설치: <code>composer require mpdf/mpdf</code><br>
        브라우저 인쇄(Ctrl+P)로 PDF 저장 가능합니다.
    </div>
    <?php echo $quoteHtml; ?>
</body>
</html>
