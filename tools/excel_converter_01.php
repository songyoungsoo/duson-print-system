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
    <title>엑셀 변환기 v1.01 - DEFGH 추출 (체크박스)</title>
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
        .version { color: #666; font-size: 14px; font-weight: normal; }
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
        .btn-clear { background: #dc3545; }
        .btn-clear:hover { background: #c82333; }
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
        .preview-table td.center { text-align: center; }

        /* 컬럼별 너비 (5컬럼: 체크 + 4개 데이터) */
        .col-check { width: 5%; }
        .col-title { width: 23%; }
        .col-type { width: 17%; font-size: 11px; }
        .col-spec { width: 40%; font-size: 11px; }
        .col-price { width: 15%; }

        /* 체크된 행 스타일 - 엷은 청색 */
        .row-checked {
            background-color: #e3f2fd !important;
        }
        .row-checked td {
            background-color: #e3f2fd !important;
        }

        /* 체크박스 스타일 */
        .row-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* 버튼 영역 */
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        .check-info {
            text-align: center;
            margin: 10px 0;
            color: #666;
            font-size: 13px;
        }
        .check-count {
            color: #1a73e8;
            font-weight: bold;
        }

        /* 인쇄 스타일 */
        @media print {
            body { background: white; padding: 0; }
            .upload-container, .action-buttons, .no-print, .check-info { display: none !important; }
            .preview-container {
                box-shadow: none;
                max-width: 100%;
                padding: 5mm;
            }
            .preview-table { font-size: 12px; }
            .preview-table th, .preview-table td { padding: 3px 5px; }

            /* 인쇄 시 체크박스 컬럼 숨김 */
            .col-check, td.center { display: none; }
            th.col-check { display: none; }

            /* 인쇄 시 체크된 행 배경색 유지 */
            .row-checked, .row-checked td {
                background-color: #e3f2fd !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

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
    <h1>📊 거래내역 변환기 <span class="version">v1.01</span></h1>

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
        <strong>📌 v1.01 업데이트:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <li>✅ 체크박스 추가 - 클릭하면 해당 행이 <span style="background:#e3f2fd;padding:2px 5px;">엷은 청색</span>으로 표시</li>
            <li>✅ 전체 선택/해제 기능</li>
            <li>✅ 인쇄 시 체크된 행 배경색 유지</li>
        </ul>
        <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
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
    <button class="btn-clear" onclick="clearAllChecks()">✖️ 전체 해제</button>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">↩️ 다시 업로드</a>
</div>

<div class="check-info no-print">
    체크된 항목: <span class="check-count" id="checkCount">0</span>개
</div>

<div class="preview-container">
    <div class="preview-title">거래내역</div>
    <table class="preview-table">
        <thead>
            <tr>
                <th class="col-check">
                    <input type="checkbox" id="checkAll" class="row-checkbox" onclick="toggleAllChecks(this)" title="전체 선택/해제">
                </th>
                <th class="col-title">인쇄물제목</th>
                <th class="col-type">제품종류</th>
                <th class="col-spec">제품사양</th>
                <th class="col-price">매출액</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $isFirst = true;
            $rowIndex = 0;
            foreach ($previewData as $row):
                if ($isFirst) { $isFirst = false; continue; } // 헤더 행 스킵
                if (empty($row['D']) && empty($row['E']) && empty($row['G'])) continue; // 빈 행 스킵
                $rowIndex++;
            ?>
            <tr id="row-<?php echo $rowIndex; ?>">
                <td class="center">
                    <input type="checkbox" class="row-checkbox" data-row="<?php echo $rowIndex; ?>" onclick="toggleRowHighlight(this)">
                </td>
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
    <button class="btn-clear" onclick="clearAllChecks()">✖️ 전체 해제</button>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">↩️ 다시 업로드</a>
</div>

<script>
// 개별 행 체크박스 클릭 시 배경색 토글
function toggleRowHighlight(checkbox) {
    const rowId = checkbox.getAttribute('data-row');
    const row = document.getElementById('row-' + rowId);

    if (checkbox.checked) {
        row.classList.add('row-checked');
    } else {
        row.classList.remove('row-checked');
    }

    updateCheckCount();
    updateCheckAllState();
}

// 전체 선택/해제
function toggleAllChecks(masterCheckbox) {
    const checkboxes = document.querySelectorAll('tbody .row-checkbox');

    checkboxes.forEach(function(checkbox) {
        checkbox.checked = masterCheckbox.checked;
        const rowId = checkbox.getAttribute('data-row');
        const row = document.getElementById('row-' + rowId);

        if (masterCheckbox.checked) {
            row.classList.add('row-checked');
        } else {
            row.classList.remove('row-checked');
        }
    });

    updateCheckCount();
}

// 전체 해제 버튼
function clearAllChecks() {
    const masterCheckbox = document.getElementById('checkAll');
    masterCheckbox.checked = false;

    const checkboxes = document.querySelectorAll('tbody .row-checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;
        const rowId = checkbox.getAttribute('data-row');
        const row = document.getElementById('row-' + rowId);
        row.classList.remove('row-checked');
    });

    updateCheckCount();
}

// 체크된 항목 수 업데이트
function updateCheckCount() {
    const checked = document.querySelectorAll('tbody .row-checkbox:checked').length;
    document.getElementById('checkCount').textContent = checked;
}

// 전체 선택 체크박스 상태 업데이트
function updateCheckAllState() {
    const total = document.querySelectorAll('tbody .row-checkbox').length;
    const checked = document.querySelectorAll('tbody .row-checkbox:checked').length;
    const masterCheckbox = document.getElementById('checkAll');

    if (checked === 0) {
        masterCheckbox.checked = false;
        masterCheckbox.indeterminate = false;
    } else if (checked === total) {
        masterCheckbox.checked = true;
        masterCheckbox.indeterminate = false;
    } else {
        masterCheckbox.checked = false;
        masterCheckbox.indeterminate = true;
    }
}
</script>
<?php endif; ?>

</body>
</html>
