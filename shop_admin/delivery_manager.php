<?php
/**
 * 배송 관리 시스템 - 로젠택배 연동
 * - 로젠 엑셀 양식 내보내기
 * - 운송장 번호 일괄 등록
 */

// Basic Auth 인증 (lib.php 방식)
$admin_id = "duson1830";
$admin_pw = "du1830";

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $admin_id || $_SERVER['PHP_AUTH_PW'] !== $admin_pw) {
    header('WWW-Authenticate: Basic realm="관리자모드"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<script>alert("관리자만 접근 가능합니다."); history.back();</script>';
    exit;
}

// 메인 DB 연결 (주문 데이터가 있는 dsp1830)
require_once __DIR__ . '/../db.php';
$connect = $db;

// 발송인 정보 (두손기획인쇄)
$sender = [
    'name' => '두손기획인쇄',
    'phone' => '02-2272-1830',
    'mobile' => '010-3305-1830',
    'zipcode' => '04563',
    'address' => '서울특별시 중구 을지로33길 33 두손빌딩'
];

// 제품별 박스수/택배비 계산
function getDeliveryInfo($type, $type1) {
    if (preg_match("/16절/", $type1)) return ['boxes' => 2, 'fee' => 3000];
    if (preg_match("/a4|A4/i", $type1)) return ['boxes' => 1, 'fee' => 4000];
    if (preg_match("/a5|A5/i", $type1)) return ['boxes' => 1, 'fee' => 4000];
    if (preg_match("/NameCard|명함/i", $type)) return ['boxes' => 1, 'fee' => 2500];
    if (preg_match("/MerchandiseBond|상품권/i", $type)) return ['boxes' => 1, 'fee' => 2500];
    if (preg_match("/sticker|스티커|스티카/i", $type)) return ['boxes' => 1, 'fee' => 2500];
    if (preg_match("/envelope|봉투/i", $type)) return ['boxes' => 1, 'fee' => 3000];
    if (preg_match("/전단지|inserted|leaflet/i", $type)) return ['boxes' => 1, 'fee' => 3500];
    return ['boxes' => 1, 'fee' => 3000]; // 기본값
}

// 액션 처리
$action = $_REQUEST['action'] ?? '';
$message = '';
$error = '';

// 로젠 엑셀 내보내기
if ($action === 'export_logen') {
    $date_from = $_POST['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
    $date_to = $_POST['date_to'] ?? date('Y-m-d');
    $status = $_POST['export_status'] ?? 'pending'; // pending = 운송장 없는 것만

    $query = "SELECT no, Type, Type_1, name, email, zip, zip1, zip2, phone, Hendphone,
                     cont, date, OrderStyle, waybill_no
              FROM mlangorder_printauto
              WHERE date >= ? AND date <= DATE_ADD(?, INTERVAL 1 DAY)
              AND (zip1 IS NOT NULL AND zip1 != '' AND zip1 != '0')";

    if ($status === 'pending') {
        $query .= " AND (waybill_no IS NULL OR waybill_no = '')";
    }
    $query .= " ORDER BY no DESC";

    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ss", $date_from, $date_to);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 엑셀 헤더
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="logen_upload_' . date('Ymd_His') . '.xls"');
    header('Cache-Control: max-age=0');

    // BOM for UTF-8
    echo "\xEF\xBB\xBF";

    // 로젠 엑셀 양식 헤더 (표준 양식)
    echo "주문번호\t수하인명\t수하인전화\t수하인휴대폰\t우편번호\t수하인주소\t물품명\t박스수량\t운임구분\t택배비\t배송메세지\n";

    while ($row = mysqli_fetch_assoc($result)) {
        $info = getDeliveryInfo($row['Type'], $row['Type_1']);

        // 주소 조합
        $address = trim($row['zip1'] . ' ' . $row['zip2']);

        // 물품명 (Type_1 사용, 없으면 Type)
        $product_name = !empty($row['Type_1']) ? $row['Type_1'] : $row['Type'];
        $product_name = mb_substr($product_name, 0, 50); // 50자 제한

        // 배송메세지
        $delivery_msg = !empty($row['cont']) ? mb_substr($row['cont'], 0, 100) : '';

        // 탭 구분 출력
        echo implode("\t", [
            $row['no'],                          // 주문번호
            $row['name'] ?: '고객',              // 수하인명
            $row['phone'] ?: '',                 // 수하인전화
            $row['Hendphone'] ?: '',             // 수하인휴대폰
            $row['zip'] ?: '',                   // 우편번호
            $address,                            // 수하인주소
            $product_name,                       // 물품명
            $info['boxes'],                      // 박스수량
            '착불',                              // 운임구분
            $info['fee'],                        // 택배비
            $delivery_msg                        // 배송메세지
        ]) . "\n";
    }
    exit;
}

// 운송장 번호 일괄 등록
if ($action === 'import_waybill' && isset($_FILES['waybill_file'])) {
    $file = $_FILES['waybill_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        // 파일 확장자 확인
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // .xlsx 파일은 지원하지 않음
        if ($file_ext === 'xlsx' || $file_ext === 'xls') {
            $error = "<b>엑셀 파일(.xlsx, .xls)은 직접 업로드할 수 없습니다.</b><br><br>" .
                     "아래 방법으로 변환 후 업로드해주세요:<br>" .
                     "1. 엑셀 파일 열기<br>" .
                     "2. <b>'다른 이름으로 저장'</b> 클릭<br>" .
                     "3. 파일 형식: <b>'텍스트(탭으로 분리)(*.txt)'</b> 선택<br>" .
                     "4. 저장된 .txt 파일 업로드";
        } else {
        $content = file_get_contents($file['tmp_name']);
        // UTF-8 변환 (EUC-KR일 경우)
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'EUC-KR');
        }

        $lines = explode("\n", $content);
        $updated = 0;
        $failed = 0;
        $errors = [];

        // 첫 줄 헤더 분석
        $header = str_getcsv(array_shift($lines), "\t");
        $order_col = -1;
        $waybill_col = -1;

        foreach ($header as $idx => $col) {
            $col = trim($col);
            if (preg_match('/주문번호|주문NO|OrderNo/i', $col)) $order_col = $idx;
            if (preg_match('/운송장|송장번호|waybill/i', $col)) $waybill_col = $idx;
        }

        if ($order_col === -1 || $waybill_col === -1) {
            $error = "엑셀 파일에서 '주문번호'와 '운송장번호' 컬럼을 찾을 수 없습니다.";
        } else {
            $stmt = mysqli_prepare($connect,
                "UPDATE mlangorder_printauto
                 SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
                 WHERE no = ?");

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $cols = str_getcsv($line, "\t");
                $order_no = isset($cols[$order_col]) ? trim($cols[$order_col]) : '';
                $waybill_no = isset($cols[$waybill_col]) ? trim($cols[$waybill_col]) : '';

                if (!empty($order_no) && !empty($waybill_no) && is_numeric($order_no)) {
                    mysqli_stmt_bind_param($stmt, "si", $waybill_no, $order_no);
                    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                        $updated++;
                    } else {
                        $failed++;
                        $errors[] = "주문번호 {$order_no}: 업데이트 실패";
                    }
                }
            }

            $message = "운송장 등록 완료: {$updated}건 성공, {$failed}건 실패";
            if (count($errors) > 0 && count($errors) <= 5) {
                $message .= "<br><small>" . implode("<br>", $errors) . "</small>";
            }
        }
        } // .xlsx 체크 else 블록 종료
    } else {
        $error = "파일 업로드 오류가 발생했습니다.";
    }
}

// 통계 조회
$stats_query = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN waybill_no IS NOT NULL AND waybill_no != '' THEN 1 ELSE 0 END) as shipped,
    SUM(CASE WHEN (waybill_no IS NULL OR waybill_no = '') AND zip1 IS NOT NULL AND zip1 != '' THEN 1 ELSE 0 END) as pending
FROM mlangorder_printauto
WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$stats = mysqli_fetch_assoc(mysqli_query($connect, $stats_query));
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>배송 관리 - 로젠택배 연동</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Malgun Gothic', sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        h1 img { height: 30px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .card h2 { color: #1a73e8; margin-bottom: 15px; font-size: 18px; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-box { flex: 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-box.pending { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-box.shipped { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-box .number { font-size: 32px; font-weight: bold; }
        .stat-box .label { font-size: 14px; opacity: 0.9; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 100%; max-width: 200px; }
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; transition: all 0.3s; }
        .btn-primary { background: #1a73e8; color: #fff; }
        .btn-primary:hover { background: #1557b0; }
        .btn-success { background: #34a853; color: #fff; }
        .btn-success:hover { background: #2d8e47; }
        .btn-logen { background: #e31837; color: #fff; }
        .btn-logen:hover { background: #c41530; }
        .message { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .file-upload { border: 2px dashed #ddd; padding: 30px; text-align: center; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .file-upload:hover { border-color: #1a73e8; background: #f8f9fa; }
        .file-upload input[type="file"] { display: none; }
        .file-upload .icon { font-size: 40px; color: #999; margin-bottom: 10px; }
        .file-upload p { color: #666; }
        .links { margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; }
        .links a { color: #1a73e8; text-decoration: none; margin-right: 20px; }
        .links a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; }
        .waybill-link { color: #1a73e8; text-decoration: none; }
        .waybill-link:hover { text-decoration: underline; }
        .status-badge { padding: 3px 8px; border-radius: 12px; font-size: 12px; }
        .status-badge.pending { background: #fff3cd; color: #856404; }
        .status-badge.shipped { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="container">
    <h1>
        📦 배송 관리
        <a href="https://logis.ilogen.com/common/html/main.html" target="_blank" class="btn btn-logen" style="margin-left: auto; font-size: 12px;">
            🚚 로젠택배 시스템 바로가기
        </a>
    </h1>

    <?php if ($message): ?>
    <div class="message success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- 통계 -->
    <div class="stats">
        <div class="stat-box">
            <div class="number"><?php echo number_format($stats['total']); ?></div>
            <div class="label">최근 30일 전체 주문</div>
        </div>
        <div class="stat-box pending">
            <div class="number"><?php echo number_format($stats['pending']); ?></div>
            <div class="label">발송 대기</div>
        </div>
        <div class="stat-box shipped">
            <div class="number"><?php echo number_format($stats['shipped']); ?></div>
            <div class="label">발송 완료</div>
        </div>
    </div>

    <!-- 로젠 엑셀 내보내기 -->
    <div class="card">
        <h2>📤 로젠 엑셀 양식 내보내기</h2>
        <p style="color: #666; margin-bottom: 15px;">
            주문 데이터를 로젠택배 시스템에 업로드할 수 있는 엑셀 형식으로 다운로드합니다.
        </p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="export_logen">
            <div class="form-row">
                <div class="form-group">
                    <label>시작일</label>
                    <input type="date" name="date_from" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                </div>
                <div class="form-group">
                    <label>종료일</label>
                    <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>상태</label>
                    <select name="export_status">
                        <option value="pending">발송 대기 (운송장 미등록)</option>
                        <option value="all">전체</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">📥 엑셀 다운로드</button>
                </div>
            </div>
        </form>
        <div class="links">
            <a href="https://www.ilogen.com/web/enterprise/system" target="_blank">📋 로젠택배 기업시스템 매뉴얼</a>
            <a href="https://logis.ilogen.com/common/html/main.html" target="_blank">🔑 로젠택배 로그인</a>
        </div>
    </div>

    <!-- 운송장 번호 일괄 등록 -->
    <div class="card">
        <h2>📥 운송장 번호 일괄 등록</h2>
        <p style="color: #666; margin-bottom: 15px;">
            로젠택배 시스템에서 다운로드한 운송장 엑셀 파일을 업로드하여 주문에 운송장 번호를 일괄 등록합니다.<br>
            <strong>필수 컬럼:</strong> 주문번호, 운송장번호
        </p>
        <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="action" value="import_waybill">
            <div class="file-upload" onclick="document.getElementById('waybill_file').click();">
                <div class="icon">📄</div>
                <p id="file-name">클릭하여 엑셀 파일 선택 (.xls, .xlsx, .csv)</p>
                <input type="file" name="waybill_file" id="waybill_file" accept=".xls,.xlsx,.csv,.txt" onchange="updateFileName(this)">
            </div>
            <div style="margin-top: 15px; text-align: center;">
                <button type="submit" class="btn btn-success" id="uploadBtn" disabled>📤 운송장 등록</button>
            </div>
        </form>
    </div>

    <!-- 최근 발송 목록 -->
    <div class="card">
        <h2>📋 최근 발송 현황 (최근 20건)</h2>
        <table>
            <thead>
                <tr>
                    <th>주문번호</th>
                    <th>주문일</th>
                    <th>수하인</th>
                    <th>제품</th>
                    <th>주소</th>
                    <th>운송장번호</th>
                    <th>상태</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $recent_query = "SELECT no, date, name, Type, Type_1, zip1, waybill_no, waybill_date
                            FROM mlangorder_printauto
                            WHERE zip1 IS NOT NULL AND zip1 != '' AND zip1 != '0'
                            ORDER BY no DESC LIMIT 20";
            $recent_result = mysqli_query($connect, $recent_query);
            while ($row = mysqli_fetch_assoc($recent_result)):
            ?>
                <tr>
                    <td><strong><?php echo $row['no']; ?></strong></td>
                    <td><?php echo date('m/d H:i', strtotime($row['date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['name'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars(mb_substr($row['Type_1'] ?: $row['Type'], 0, 20)); ?></td>
                    <td><?php echo htmlspecialchars(mb_substr($row['zip1'], 0, 30)); ?></td>
                    <td>
                        <?php if (!empty($row['waybill_no'])): ?>
                        <a href="https://www.ilogen.com/web/personal/trace/<?php echo $row['waybill_no']; ?>"
                           target="_blank" class="waybill-link">
                            <?php echo $row['waybill_no']; ?>
                        </a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row['waybill_no'])): ?>
                        <span class="status-badge shipped">발송완료</span>
                        <?php else: ?>
                        <span class="status-badge pending">대기중</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : '클릭하여 엑셀 파일 선택';
    document.getElementById('file-name').textContent = fileName;
    document.getElementById('uploadBtn').disabled = !input.files[0];
}
</script>
</body>
</html>
