<?php
/**
 * 표준 견적서 - 웹 화면 출력
 *
 * 사용법:
 *   view.php?id=123        - DB에서 견적서 로드
 *   view.php               - 샘플 데이터로 미리보기
 *   view.php?sample=1      - 샘플 데이터 강제 사용
 */

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/layout.php';

// === 파라미터 처리 ===
$quoteId = intval($_GET['id'] ?? 0);
$useSample = isset($_GET['sample']);

// === 데이터 로드 ===
if ($useSample || $quoteId <= 0) {
    // 샘플 데이터 사용
    $data = loadQuoteDataPackage(null, 0);
} else {
    // DB 연결 확인
    global $db;
    if (!$db) {
        require_once __DIR__ . '/../../../db.php';
    }
    $data = loadQuoteDataPackage($db, $quoteId);
}

$quote    = $data['quote'];
$items    = $data['items'];
$supplier = $data['supplier'];

// === 기본 URL (이미지용) ===
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

// === 견적서 HTML 렌더링 ===
$quoteHtml = renderQuoteLayout($quote, $items, $supplier, $baseUrl);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 - <?php echo htmlspecialchars($quote['quote_no'] ?? '미리보기'); ?></title>
    <style>
        /* 기본 스타일 */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Malgun Gothic', '맑은 고딕', Dotum, '돋움', sans-serif;
            background: #e8e8e8;
            padding: 20px;
        }

        /* 컨테이너 */
        .page-container {
            max-width: 850px;
            margin: 0 auto;
        }

        /* 툴바 (웹 전용) */
        .toolbar {
            background: #fff;
            border: 1px solid #ccc;
            padding: 10px 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .toolbar-title {
            font-size: 14px;
            color: #333;
        }
        .toolbar-title span {
            color: #217346;
            font-weight: bold;
        }
        .toolbar-actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 14px;
            border: 1px solid #ababab;
            background: linear-gradient(to bottom, #f5f5f5 0%, #e0e0e0 100%);
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            display: inline-block;
        }
        .btn:hover {
            background: linear-gradient(to bottom, #e8e8e8 0%, #d0d0d0 100%);
        }
        .btn-primary {
            background: linear-gradient(to bottom, #217346 0%, #1a5c38 100%);
            border-color: #145a32;
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(to bottom, #1a5c38 0%, #145a32 100%);
        }

        /* 견적서 래퍼 (흰 배경) */
        .quote-wrapper {
            background: #fff;
            border: 1px solid #ccc;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* 인쇄 스타일 */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .quote-wrapper {
                border: none;
                padding: 0;
                box-shadow: none;
            }
            .page-container {
                max-width: 100%;
            }
        }

        <?php echo getQuoteStyles(); ?>
    </style>
</head>
<body>
    <div class="page-container">

        <!-- 툴바 (웹 전용, 인쇄 시 숨김) -->
        <div class="toolbar no-print">
            <div class="toolbar-title">
                견적서 <span><?php echo htmlspecialchars($quote['quote_no'] ?? ''); ?></span>
                <?php if ($useSample || $quoteId <= 0): ?>
                <em style="color:#d9534f;font-size:11px;margin-left:10px;">(샘플 데이터)</em>
                <?php endif; ?>
            </div>
            <div class="toolbar-actions">
                <button onclick="window.print()" class="btn">인쇄</button>
                <a href="pdf.php?id=<?php echo $quoteId; ?><?php echo $useSample ? '&sample=1' : ''; ?>" class="btn btn-primary" target="_blank">PDF 다운로드</a>
                <?php if ($quoteId > 0): ?>
                <a href="mail.php?id=<?php echo $quoteId; ?>&preview=1" class="btn">이메일 미리보기</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 견적서 본문 -->
        <div class="quote-wrapper">
            <?php echo $quoteHtml; ?>
        </div>

    </div>
</body>
</html>
