<?php
/**
 * 표준 견적서 - PDF 출력
 *
 * 동일한 layout.php를 사용하여 PDF 생성
 * mPDF 라이브러리 사용 (한글 지원 우수)
 *
 * 사용법:
 *   pdf.php?id=123         - DB에서 견적서 로드 후 PDF 다운로드
 *   pdf.php?id=123&inline=1 - 브라우저에서 직접 보기
 *   pdf.php?sample=1       - 샘플 데이터로 PDF 생성
 */

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/layout.php';

// === Composer autoload ===
$autoloadPaths = [
    __DIR__ . '/../../../vendor/autoload.php',
    '/var/www/html/vendor/autoload.php',
];

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

// === 파라미터 처리 ===
$quoteId = intval($_GET['id'] ?? 0);
$useSample = isset($_GET['sample']);
$inline = isset($_GET['inline']);

// === 데이터 로드 ===
if ($useSample || $quoteId <= 0) {
    $data = loadQuoteDataPackage(null, 0);
} else {
    global $db;
    if (!$db) {
        require_once __DIR__ . '/../../../db.php';
    }
    $data = loadQuoteDataPackage($db, $quoteId);
}

$quote    = $data['quote'];
$items    = $data['items'];
$supplier = $data['supplier'];

// === 기본 URL (이미지 절대경로) ===
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

// === 견적서 HTML 렌더링 ===
$quoteHtml = renderQuoteLayout($quote, $items, $supplier, $baseUrl);

// === 전체 HTML 문서 구성 ===
$fullHtml = <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            font-family: nanumgothic, sans-serif !important;
        }
        body {
            font-size: 13px;
            line-height: 1.4;
            color: #000;
        }
    </style>
</head>
<body>
{$quoteHtml}
</body>
</html>
HTML;

// === 파일명 ===
$filename = 'Quote_' . ($quote['quote_no'] ?? date('Ymd')) . '.pdf';

// === mPDF 사용 (우선) ===
if (class_exists('Mpdf\Mpdf')) {
    try {
        // Noto Sans CJK 폰트 설정
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'nanumgothic',
            'tempDir' => '/tmp/mpdf',
            'fontDir' => array_merge($fontDirs, ['/usr/share/fonts/truetype/nanum']),
            'fontdata' => $fontData + [
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
        // mPDF 실패 시 Dompdf로 폴백
        error_log('mPDF Error: ' . $e->getMessage());
    }
}

// === Dompdf 폴백 ===
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

// === PDF 라이브러리 없을 때: HTML 출력으로 대체 ===
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
