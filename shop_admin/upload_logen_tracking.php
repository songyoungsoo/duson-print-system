<?php
/**
 * 로젠택배 운송장번호 업로드 및 매칭
 * 로젠에서 다운받은 엑셀 파일을 업로드하면 주문번호(기타 컬럼)와 매칭하여 DB 업데이트
 */
require_once __DIR__ . '/../db.php';

$message = '';
$results = [];

// 엑셀 업로드 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logen_excel'])) {
    $file = $_FILES['logen_excel'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            $message = '<div class="alert error">엑셀 파일(.xlsx, .xls, .csv)만 업로드 가능합니다.</div>';
        } else {
            // 임시 파일 저장
            $tmp_path = '/tmp/logen_upload_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $tmp_path);

            // CSV로 변환하여 처리 (xlsx는 Python이나 별도 라이브러리 필요)
            // 여기서는 JavaScript에서 파싱 후 JSON으로 전송하는 방식 사용
            $message = '<div class="alert info">파일이 업로드되었습니다. 아래에서 데이터를 확인하세요.</div>';
        }
    } else {
        $message = '<div class="alert error">파일 업로드에 실패했습니다.</div>';
    }
}

// JSON 데이터로 매칭 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tracking_data'])) {
    $tracking_data = json_decode($_POST['tracking_data'], true);

    if (is_array($tracking_data) && count($tracking_data) > 0) {
        $updated = 0;
        $not_found = 0;
        $errors = [];

        foreach ($tracking_data as $item) {
            $order_no = intval($item['order_no'] ?? 0);
            $tracking_no = trim($item['tracking_no'] ?? '');

            if ($order_no > 0 && !empty($tracking_no)) {
                // 주문번호로 DB 업데이트
                $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET logen_tracking_no = ? WHERE no = ?");
                mysqli_stmt_bind_param($stmt, "si", $tracking_no, $order_no);

                if (mysqli_stmt_execute($stmt)) {
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        $updated++;
                        $results[] = ['order_no' => $order_no, 'tracking_no' => $tracking_no, 'status' => 'success'];
                    } else {
                        $not_found++;
                        $results[] = ['order_no' => $order_no, 'tracking_no' => $tracking_no, 'status' => 'not_found'];
                    }
                } else {
                    $errors[] = $order_no;
                    $results[] = ['order_no' => $order_no, 'tracking_no' => $tracking_no, 'status' => 'error'];
                }
                mysqli_stmt_close($stmt);
            }
        }

        $message = '<div class="alert success">';
        $message .= "✅ 업데이트 완료: {$updated}건<br>";
        if ($not_found > 0) {
            $message .= "⚠️ 주문번호 없음: {$not_found}건<br>";
        }
        if (count($errors) > 0) {
            $message .= "❌ 오류: " . count($errors) . "건";
        }
        $message .= '</div>';
    }
}

// 최근 운송장번호 등록 현황 조회
$recent_query = "SELECT no, name, logen_tracking_no, date
                 FROM mlangorder_printauto
                 WHERE logen_tracking_no IS NOT NULL AND logen_tracking_no != ''
                 ORDER BY no DESC LIMIT 20";
$recent_result = mysqli_query($db, $recent_query);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>로젠 운송장번호 업로드</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
body { font-family: 'Malgun Gothic', sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; }
h1 { color: #03C75A; border-bottom: 3px solid #03C75A; padding-bottom: 10px; }
.card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.card h2 { margin-top: 0; color: #333; font-size: 18px; }
.upload-area { border: 2px dashed #ccc; padding: 40px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
.upload-area:hover { border-color: #03C75A; background: #f9fff9; }
.upload-area input[type="file"] { display: none; }
.upload-area label { cursor: pointer; color: #666; }
.upload-area label:hover { color: #03C75A; }
.btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 5px; }
.btn-primary { background: #03C75A; color: white; }
.btn-primary:hover { background: #02a849; }
.btn-secondary { background: #6c757d; color: white; }
.btn-secondary:hover { background: #5a6268; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #f8f9fa; }
tr:hover { background: #f5f5f5; }
.alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
.alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.alert.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
#preview-table { max-height: 400px; overflow-y: auto; }
.status-success { color: #28a745; }
.status-not_found { color: #ffc107; }
.status-error { color: #dc3545; }
.tracking-link { color: #03C75A; text-decoration: none; }
.tracking-link:hover { text-decoration: underline; }
.help-text { color: #666; font-size: 12px; margin-top: 10px; }
</style>
</head>
<body>
<div class="container">
    <h1>🚚 로젠 운송장번호 업로드</h1>

    <?php echo $message; ?>

    <div class="card">
        <h2>📤 로젠 엑셀 파일 업로드</h2>
        <p>로젠 iLOGEN에서 다운받은 엑셀 파일을 업로드하세요.<br>
        <strong>"기타" 컬럼의 주문번호</strong>와 <strong>"운송장번호" 컬럼</strong>을 자동으로 매칭합니다.</p>

        <div class="upload-area" id="dropZone">
            <label for="excelFile">
                📁 클릭하여 파일 선택 또는 여기에 드래그&드롭<br>
                <small>(.xlsx, .xls 파일 지원)</small>
            </label>
            <input type="file" id="excelFile" accept=".xlsx,.xls,.csv">
        </div>

        <div class="help-text">
            <strong>💡 로젠 엑셀 파일 구조:</strong><br>
            운송장번호 | 수하인명 | 주소 | ... | 기타(주문번호) | ...<br>
            "운송장번호"와 "기타" 컬럼이 있어야 합니다.
        </div>
    </div>

    <div class="card" id="previewCard" style="display:none;">
        <h2>📋 데이터 미리보기</h2>
        <p>매칭될 데이터를 확인하세요. 확인 후 "운송장번호 등록" 버튼을 클릭하세요.</p>
        <div id="preview-table"></div>
        <br>
        <button type="button" class="btn btn-primary" onclick="submitTrackingData()">✅ 운송장번호 등록</button>
        <button type="button" class="btn btn-secondary" onclick="cancelUpload()">취소</button>
    </div>

    <div class="card">
        <h2>📜 최근 등록된 운송장번호</h2>
        <table>
            <thead>
                <tr>
                    <th>주문번호</th>
                    <th>주문자</th>
                    <th>운송장번호</th>
                    <th>주문일</th>
                    <th>배송추적</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                <tr>
                    <td><?php echo $row['no']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['logen_tracking_no']); ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td>
                        <a href="https://www.ilogen.com/web/personal/trace/<?php echo $row['logen_tracking_no']; ?>"
                           target="_blank" class="tracking-link">🔍 조회</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($recent_result) === 0): ?>
                <tr><td colspan="5" style="text-align:center; color:#999;">등록된 운송장번호가 없습니다.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>🔧 수동 입력</h2>
        <form method="post" id="manualForm">
            <table>
                <tr>
                    <td width="150">주문번호:</td>
                    <td><input type="number" id="manual_order_no" style="width:150px;" placeholder="예: 103834"></td>
                </tr>
                <tr>
                    <td>운송장번호:</td>
                    <td><input type="text" id="manual_tracking_no" style="width:200px;" placeholder="예: 123456789012"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="submitManual()">등록</button>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="tracking_data" id="manual_tracking_data">
        </form>
    </div>
</div>

<form method="post" id="trackingForm" style="display:none;">
    <input type="hidden" name="tracking_data" id="tracking_data_input">
</form>

<script>
var parsedData = [];

// 드래그 앤 드롭
var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    dropZone.style.borderColor = '#03C75A';
    dropZone.style.background = '#f9fff9';
});
dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dropZone.style.borderColor = '#ccc';
    dropZone.style.background = '';
});
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.style.borderColor = '#ccc';
    dropZone.style.background = '';
    if (e.dataTransfer.files.length > 0) {
        handleFile(e.dataTransfer.files[0]);
    }
});

// 파일 선택
document.getElementById('excelFile').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        handleFile(e.target.files[0]);
    }
});

// 엑셀 파일 처리
function handleFile(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        var data = new Uint8Array(e.target.result);
        var workbook = XLSX.read(data, {type: 'array'});

        // 첫 번째 시트 가져오기
        var sheetName = workbook.SheetNames[0];
        var worksheet = workbook.Sheets[sheetName];

        // JSON으로 변환
        var jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1});

        if (jsonData.length < 2) {
            alert('데이터가 없습니다.');
            return;
        }

        // 헤더 찾기
        var headers = jsonData[0];
        var trackingCol = -1;
        var orderCol = -1;
        var nameCol = -1;

        for (var i = 0; i < headers.length; i++) {
            var h = String(headers[i]).trim();
            if (h.indexOf('운송장') !== -1) trackingCol = i;
            if (h === '기타' || h.indexOf('주문번호') !== -1) orderCol = i;
            if (h.indexOf('수하인') !== -1 || h === '이름') nameCol = i;
        }

        if (trackingCol === -1) {
            alert('운송장번호 컬럼을 찾을 수 없습니다.');
            return;
        }
        if (orderCol === -1) {
            alert('기타(주문번호) 컬럼을 찾을 수 없습니다.');
            return;
        }

        // 데이터 파싱
        parsedData = [];
        for (var i = 1; i < jsonData.length; i++) {
            var row = jsonData[i];
            var trackingNo = String(row[trackingCol] || '').trim();
            var orderNo = String(row[orderCol] || '').trim();
            var name = nameCol !== -1 ? String(row[nameCol] || '').trim() : '';

            // 숫자만 추출 (주문번호)
            orderNo = orderNo.replace(/[^0-9]/g, '');

            if (trackingNo && orderNo) {
                parsedData.push({
                    tracking_no: trackingNo,
                    order_no: orderNo,
                    name: name
                });
            }
        }

        if (parsedData.length === 0) {
            alert('매칭할 데이터가 없습니다. 운송장번호와 기타(주문번호) 컬럼을 확인하세요.');
            return;
        }

        // 미리보기 표시
        showPreview();
    };
    reader.readAsArrayBuffer(file);
}

// 미리보기 표시
function showPreview() {
    var html = '<table><thead><tr><th>No</th><th>주문번호</th><th>운송장번호</th><th>수하인명</th></tr></thead><tbody>';

    for (var i = 0; i < parsedData.length; i++) {
        var item = parsedData[i];
        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td>' + item.order_no + '</td>';
        html += '<td>' + item.tracking_no + '</td>';
        html += '<td>' + item.name + '</td>';
        html += '</tr>';
    }

    html += '</tbody></table>';
    html += '<p><strong>총 ' + parsedData.length + '건</strong>의 데이터가 매칭됩니다.</p>';

    document.getElementById('preview-table').innerHTML = html;
    document.getElementById('previewCard').style.display = 'block';
}

// 운송장번호 등록
function submitTrackingData() {
    if (parsedData.length === 0) {
        alert('등록할 데이터가 없습니다.');
        return;
    }

    if (!confirm(parsedData.length + '건의 운송장번호를 등록하시겠습니까?')) {
        return;
    }

    document.getElementById('tracking_data_input').value = JSON.stringify(parsedData);
    document.getElementById('trackingForm').submit();
}

// 취소
function cancelUpload() {
    parsedData = [];
    document.getElementById('previewCard').style.display = 'none';
    document.getElementById('excelFile').value = '';
}

// 수동 입력
function submitManual() {
    var orderNo = document.getElementById('manual_order_no').value.trim();
    var trackingNo = document.getElementById('manual_tracking_no').value.trim();

    if (!orderNo || !trackingNo) {
        alert('주문번호와 운송장번호를 모두 입력하세요.');
        return;
    }

    var data = [{order_no: orderNo, tracking_no: trackingNo}];
    document.getElementById('manual_tracking_data').value = JSON.stringify(data);
    document.getElementById('manualForm').submit();
}
</script>
</body>
</html>
