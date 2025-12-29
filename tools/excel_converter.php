<?php
$previewData = null;
$error = null;

// 파일 업로드 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    if (!isset($_FILES['excel_file']) || empty($_FILES['excel_file']['name'])) {
        $error = '파일이 선택되지 않았습니다.';
    } else {
        $uploadedFile = $_FILES['excel_file'];

        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $error = '파일 업로드 실패';
        } else {
            $ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            if ($ext !== 'xlsx') {
                $error = 'xlsx 파일만 지원합니다';
            } else {
                // 엑셀 파일 파싱
                $previewData = parseExcelFile($uploadedFile['tmp_name']);
                if (!$previewData) {
                    $error = '엑셀 파일 파싱 실패';
                }
            }
        }
    }
}

// 엑셀 파일에서 DEFGH 컬럼 추출
function parseExcelFile($filepath) {
    $rows = [];

    $zip = new ZipArchive();
    if ($zip->open($filepath) !== true) {
        return false;
    }

    // shared strings 읽기 (네임스페이스 제거 방식)
    $sharedStrings = [];
    $ssContent = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssContent) {
        // 네임스페이스 제거하여 파싱 간소화
        $ssContent = preg_replace('/xmlns[^=]*="[^"]*"/', '', $ssContent);
        $ssContent = preg_replace('/<(\/?)(\w+):/', '<$1', $ssContent);

        $xml = @simplexml_load_string($ssContent);
        if ($xml !== false) {
            foreach ($xml->si as $si) {
                // t 태그 직접 접근 또는 r/t 구조 처리
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    // 리치 텍스트 형식: r 태그 안에 t 태그들
                    $text = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) {
                            $text .= (string)$r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }
    }

    // sheet1 읽기
    $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
    if (!$sheetContent) {
        $zip->close();
        return false;
    }

    // 네임스페이스 제거
    $sheetContent = preg_replace('/xmlns[^=]*="[^"]*"/', '', $sheetContent);
    $sheetContent = preg_replace('/<(\/?)(\w+):/', '<$1', $sheetContent);

    $xml = @simplexml_load_string($sheetContent);
    if ($xml === false) {
        $zip->close();
        return false;
    }

    // sheetData에서 row 추출
    if (isset($xml->sheetData)) {
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                $col = preg_replace('/[0-9]/', '', $ref);

                $value = '';
                if (isset($cell->v)) {
                    if ((string)$cell['t'] === 's') {
                        $idx = (int)(string)$cell->v;
                        $value = $sharedStrings[$idx] ?? '';
                    } else {
                        $value = (string)$cell->v;
                    }
                }
                $rowData[$col] = $value;
            }
            $rows[] = $rowData;
        }
    }

    $zip->close();

    // DEFGH 컬럼만 추출
    $result = [];
    foreach ($rows as $row) {
        $result[] = [
            'D' => $row['D'] ?? '',
            'E' => $row['E'] ?? '',
            'F' => $row['F'] ?? '',
            'G' => $row['G'] ?? '',
            'H' => $row['H'] ?? '',
        ];
    }

    return $result;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>엑셀 변환기 - DEFGH 추출</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        /* 업로드 폼 스타일 */
        .upload-container {
            max-width: 800px;
            margin: 0 auto 30px;
        }
        h1 { color: #333; text-align: center; margin-bottom: 20px; }
        .upload-box {
            background: white;
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
        }
        .upload-box:hover { border-color: #1a73e8; }
        input[type="file"] { margin: 15px 0; }
        button, .btn {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        button:hover, .btn:hover { background: #1557b0; }
        .btn-print { background: #28a745; }
        .btn-print:hover { background: #1e7e34; }
        .error {
            background: #ffebee;
            border: 1px solid #f44336;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            color: #c62828;
        }

        /* 미리보기 테이블 스타일 */
        .preview-container {
            max-width: 210mm; /* A4 가로 */
            margin: 0 auto;
            background: white;
            padding: 10mm;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .preview-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .preview-table th, .preview-table td {
            border: 1px solid #333;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .preview-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .preview-table td { text-align: left; }
        .preview-table td.num { text-align: right; }

        /* 컬럼별 너비 (4컬럼) */
        .col-title { width: 25%; }
        .col-type { width: 18%; font-size: 11px; }
        .col-spec { width: 42%; font-size: 11px; }
        .col-price { width: 15%; }

        /* 버튼 영역 */
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }

        /* 인쇄 스타일 */
        @media print {
            body { background: white; padding: 0; }
            .upload-container, .action-buttons, .no-print { display: none !important; }
            .preview-container {
                box-shadow: none;
                max-width: 100%;
                padding: 5mm;
            }
            .preview-table { font-size: 12px; }
            .preview-table th, .preview-table td { padding: 3px 5px; }

            /* 각 페이지마다 헤더 반복 */
            thead { display: table-header-group; }
            tbody { display: table-row-group; }
            tr { page-break-inside: avoid; }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

<?php if (!$previewData): ?>
<!-- 업로드 폼 -->
<div class="upload-container">
    <h1>📊 거래내역 변환기</h1>

    <?php if ($error): ?>
    <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="upload-box">
        <form method="post" enctype="multipart/form-data">
            <p>📁 엑셀 파일(.xlsx)을 선택하세요</p>
            <input type="file" name="excel_file" accept=".xlsx" required>
            <br>
            <button type="submit">미리보기</button>
        </form>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #fff3e0; border-radius: 6px; font-size: 13px;">
        <strong>추출되는 컬럼:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <li>D: 인쇄물제목</li>
            <li>E: 제품종류</li>
            <li>F: 제품사양</li>
            <li>G: 매출액</li>
        </ul>
    </div>
</div>

<?php else: ?>
<!-- 미리보기 및 인쇄 -->
<div class="action-buttons no-print">
    <button class="btn-print" onclick="window.print()">🖨️ 인쇄하기</button>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">↩️ 다시 업로드</a>
</div>

<div class="preview-container">
    <div class="preview-title">거래내역</div>
    <table class="preview-table">
        <thead>
            <tr>
                <th class="col-title">인쇄물제목</th>
                <th class="col-type">제품종류</th>
                <th class="col-spec">제품사양</th>
                <th class="col-price">매출액</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $isFirst = true;
            foreach ($previewData as $row):
                if ($isFirst) { $isFirst = false; continue; } // 헤더 행 스킵
                if (empty($row['D']) && empty($row['E']) && empty($row['G'])) continue; // 빈 행 스킵
            ?>
            <tr>
                <td class="col-title"><?php echo htmlspecialchars($row['D']); ?></td>
                <td class="col-type"><?php echo htmlspecialchars($row['E']); ?></td>
                <td class="col-spec"><?php echo htmlspecialchars($row['F']); ?></td>
                <td class="col-price num"><?php echo htmlspecialchars($row['G']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="action-buttons no-print" style="margin-top: 30px;">
    <button class="btn-print" onclick="window.print()">🖨️ 인쇄하기</button>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">↩️ 다시 업로드</a>
</div>
<?php endif; ?>

</body>
</html>
