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
    $status = $_POST['export_status'] ?? 'all'; // all = 전체, pending = 운송장 없는 것만

    // ✅ FIX: logen_tracking_no 컬럼이 존재하지 않음 → waybill_no 사용
    $query = "SELECT no, Type, Type_1, name, email, zip, zip1, zip2, phone, Hendphone,
                     cont, date, OrderStyle, waybill_no
              FROM mlangorder_printauto
              WHERE date >= ? AND date < DATE_ADD(?, INTERVAL 1 DAY)
              AND (zip1 IS NOT NULL AND zip1 != '' AND zip1 != '0')";

    if ($status === 'pending') {
        $query .= " AND (waybill_no IS NULL OR waybill_no = '')";
    }
    $query .= " ORDER BY no DESC";

    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ss", $date_from, $date_to);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 결과 수집
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    // 엑셀 헤더
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="logen_upload_' . date('Ymd_His') . '.xls"');
    header('Cache-Control: max-age=0');

    // BOM for UTF-8
    echo "\xEF\xBB\xBF";

    // 로젠 엑셀 양식 헤더 (표준 양식)
    echo "주문번호\t수하인명\t수하인전화\t수하인휴대폰\t우편번호\t수하인주소\t물품명\t박스수량\t운임구분\t택배비\t배송메세지\n";

    foreach ($rows as $row) {
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
            'dsno' . $row['no'],                 // 주문번호 (dsno 접두사 추가)
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

        // Excel 파일 처리
        if ($file_ext === 'xlsx' || $file_ext === 'xls') {
            try {
                // SimpleXLSX 라이브러리 로드
                $xlsx_path = __DIR__ . '/../includes/SimpleXLSX.php';
                if (!file_exists($xlsx_path)) {
                    $error = "SimpleXLSX 라이브러리를 찾을 수 없습니다. 경로: {$xlsx_path}";
                } else {
                    require_once $xlsx_path;

                    if (!class_exists('Shuchkin\\SimpleXLSX')) {
                        $error = "SimpleXLSX 클래스를 로드할 수 없습니다.";
                    } else {
                        $xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name']);

                        if ($xlsx === false) {
                            $error = "Excel 파일 파싱 실패: " . \Shuchkin\SimpleXLSX::parseError();
                        } else {
                $rows = $xlsx->rows();
                $updated = 0;
                $failed = 0;
                $errors = [];

                // 데이터 행 찾기 (숫자로 시작하는 첫 번째 행)
                $data_row_idx = -1;
                foreach ($rows as $idx => $row) {
                    $first_col = isset($row[0]) ? trim($row[0]) : '';
                    if (preg_match('/^[0-9]+$/', $first_col)) {
                        $data_row_idx = $idx;
                        break;
                    }
                }

                if ($data_row_idx === -1) {
                    $error = "데이터 행을 찾을 수 없습니다. 파일 형식을 확인해주세요.";
                } else {
                    // 데이터 행부터 처리
                    $data_rows = array_slice($rows, $data_row_idx);

                    // 컬럼 인덱스 찾기 (첫 20행 스캔)
                    $order_col = -1;
                    $waybill_col = -1;

                    $scan_limit = min(20, count($data_rows));
                    for ($scan_idx = 0; $scan_idx < $scan_limit; $scan_idx++) {
                        $row = $data_rows[$scan_idx];

                        foreach ($row as $idx => $value) {
                            $value = trim($value);

                            // 운송장번호: 4로 시작하는 11자리 숫자
                            if ($waybill_col === -1 && preg_match('/^4[0-9]{10}$/', $value)) {
                                $waybill_col = $idx;
                            }

                            // 주문번호: dsno 포함
                            if ($order_col === -1 && preg_match('/dsno[0-9]+/i', $value)) {
                                $order_col = $idx;
                            }
                        }

                        if ($order_col !== -1 && $waybill_col !== -1) {
                            break;
                        }
                    }

                    if ($order_col === -1 || $waybill_col === -1) {
                        $error = "Excel 파일에서 '주문번호(dsno형식)'와 '운송장번호(4로 시작하는 11자리)'를 찾을 수 없습니다.<br><br>" .
                                 "찾은 위치: 주문번호=" . ($order_col === -1 ? '없음' : "컬럼 " . ($order_col+1)) .
                                 ", 운송장=" . ($waybill_col === -1 ? '없음' : "컬럼 " . ($waybill_col+1));
                    } else {
                        // DB 업데이트
                        $stmt = mysqli_prepare($connect,
                            "UPDATE mlangorder_printauto
                             SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
                             WHERE no = ?");

                        foreach ($data_rows as $row) {
                            $order_no_raw = isset($row[$order_col]) ? trim($row[$order_col]) : '';
                            $waybill_no = isset($row[$waybill_col]) ? trim($row[$waybill_col]) : '';

                            // "dsno" 접두사 제거
                            $order_no = preg_replace('/^dsno/i', '', $order_no_raw);

                            if (!empty($order_no) && !empty($waybill_no) && is_numeric($order_no)) {
                                mysqli_stmt_bind_param($stmt, "si", $waybill_no, $order_no);
                                if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                                    $updated++;
                                } else {
                                    $failed++;
                                    if (count($errors) < 10) {
                                        $errors[] = "주문번호 {$order_no}: 업데이트 실패";
                                    }
                                }
                            }
                        }

                        $message = "✅ <b>Excel 자동 처리 완료</b><br>" .
                                   "운송장 등록: {$updated}건 성공, {$failed}건 실패";
                        if (count($errors) > 0) {
                            $message .= "<br><br><small style='color:#d97706;'>오류 내역:<br>" .
                                       implode("<br>", $errors) . "</small>";
                        }
                    }
                }
            }
        }
    }
} catch (Exception $e) {
                $error = "Excel 처리 중 오류 발생: " . $e->getMessage() . "<br><br>" .
                         "파일: " . $e->getFile() . "<br>" .
                         "라인: " . $e->getLine();
                error_log("Excel upload error: " . $e->getMessage());
            }
        } else {
            // 기존 TXT 파일 처리 로직 (하위 호환성)
        $content = file_get_contents($file['tmp_name']);
        // UTF-8 변환 (EUC-KR일 경우)
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'EUC-KR');
        }

        // Windows 줄바꿈(\r\n)을 Unix 줄바꿈(\n)으로 통일
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        $lines = explode("\n", $content);
        $updated = 0;
        $failed = 0;
        $errors = [];

        // 디버그: 파일 기본 정보
        error_log("=== 파일 디버그 ===");
        error_log("총 라인 수: " . count($lines));
        error_log("첫 3줄 (raw): " . print_r(array_slice($lines, 0, 3), true));

        // 실제 데이터 줄 찾기 (숫자로 시작하는 첫 번째 줄)
        $data_line_idx = -1;
        for ($i = 0; $i < count($lines) && $i < 10; $i++) {
            $test_row = str_getcsv($lines[$i], "\t");
            $first_col = isset($test_row[0]) ? trim($test_row[0]) : '';

            // 첫 번째 컬럼이 숫자로 시작하면 데이터 줄
            if (preg_match('/^[0-9]+$/', $first_col)) {
                $data_line_idx = $i;
                break;
            }
        }

        if ($data_line_idx === -1) {
            $error = "데이터 줄을 찾을 수 없습니다. 파일 형식을 확인해주세요.";
        } else {
            // 데이터 줄부터 시작
            $lines = array_slice($lines, $data_line_idx);

            $order_col = -1;
            $waybill_col = -1;

            // 패턴 기반 컬럼 인식: 여러 데이터 행을 스캔해서 패턴으로 컬럼 찾기
            // (첫 번째 행에 패턴이 없을 수 있으므로 최대 20개 행 스캔)
            $scan_limit = min(20, count($lines));
            for ($scan_idx = 0; $scan_idx < $scan_limit; $scan_idx++) {
                $row = str_getcsv($lines[$scan_idx], "\t");

                foreach ($row as $idx => $value) {
                    $value = trim($value);

                    // 운송장번호: 4로 시작하는 11자리 숫자 (예: 43366261260)
                    if ($waybill_col === -1 && preg_match('/^4[0-9]{10}$/', $value)) {
                        $waybill_col = $idx;
                    }

                    // 주문번호: dsno 포함 (예: dsno84285, 또는 더 긴 문자열에 dsno84285 포함)
                    if ($order_col === -1 && preg_match('/dsno[0-9]+/i', $value)) {
                        $order_col = $idx;
                    }
                }

                // 둘 다 찾았으면 더 이상 스캔 안 함
                if ($order_col !== -1 && $waybill_col !== -1) {
                    break;
                }
            }

            if ($order_col === -1 || $waybill_col === -1) {
                // 디버그 정보 - 더 상세하게
                $total_lines = count($lines);
                $first_row_display = $total_lines > 0 ? str_getcsv($lines[0], "\t") : array();

                // 첫 5줄의 raw 데이터 표시
                $raw_lines_preview = array_slice($lines, 0, 5);

                // 전체 컬럼을 스캔해서 패턴 매칭 여부 확인
                $waybill_matches = [];
                $order_matches = [];
                foreach ($first_row_display as $idx => $value) {
                    $value = trim($value);
                    if (preg_match('/^4[0-9]{10}$/', $value)) {
                        $waybill_matches[] = "[$idx] " . $value;
                    }
                    if (preg_match('/dsno[0-9]+/i', $value)) {
                        $order_matches[] = "[$idx] " . $value;
                    }
                }

                $error = "엑셀 파일에서 '주문번호'와 '운송장번호' 컬럼을 찾을 수 없습니다.<br><br>" .
                         "<b>패턴 인식 정보:</b><br>" .
                         "운송장번호 패턴: 4로 시작하는 11자리 숫자 (예: 43366261260)<br>" .
                         "주문번호 패턴: dsno + 숫자 (예: dsno84285)<br><br>" .
                         "주문번호 위치: " . ($order_col === -1 ? '찾을 수 없음' : "컬럼 " . $order_col) . "<br>" .
                         "운송장 위치: " . ($waybill_col === -1 ? '찾을 수 없음' : "컬럼 " . $waybill_col) . "<br><br>" .
                         "<b>전체 컬럼에서 발견된 패턴:</b><br>" .
                         "운송장 패턴 매칭: " . (count($waybill_matches) > 0 ? implode(", ", $waybill_matches) : "없음") . "<br>" .
                         "주문번호 패턴 매칭: " . (count($order_matches) > 0 ? implode(", ", $order_matches) : "없음") . "<br><br>" .
                         "<b>파일 정보:</b><br>" .
                         "데이터 시작 줄: " . ($data_line_idx + 1) . "번째 줄 (0부터 시작: " . $data_line_idx . ")<br>" .
                         "총 데이터 라인 수: " . $total_lines . "<br>" .
                         "첫 번째 데이터 행 컬럼 수: " . count($first_row_display) . "<br><br>" .
                         "<b>첫 번째 데이터 행 (전체 컬럼):</b><br>" .
                         "<small>" . implode("<br>", array_map(function($i, $c) {
                             $len = mb_strlen($c);
                             return "[" . $i . "] (길이:" . $len . ") " . htmlspecialchars(trim($c));
                         }, array_keys($first_row_display), $first_row_display)) . "</small>";
            } else {
            $stmt = mysqli_prepare($connect,
                "UPDATE mlangorder_printauto
                 SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
                 WHERE no = ?");

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $cols = str_getcsv($line, "\t");
                $order_no_raw = isset($cols[$order_col]) ? trim($cols[$order_col]) : '';
                $waybill_no = isset($cols[$waybill_col]) ? trim($cols[$waybill_col]) : '';

                // "dsno" 접두사 제거 (예: dsno84285 → 84285)
                $order_no = preg_replace('/^dsno/i', '', $order_no_raw);

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
        } // if ($header === null) else 블록 종료
        } // if ($file_ext === 'xlsx') else 블록 종료
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
$stats_result = @mysqli_query($connect, $stats_query);
if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
} else {
    $stats = ['total' => 0, 'shipped' => 0, 'pending' => 0];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>배송 관리 - 로젠택배 연동</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Malgun Gothic', 'Arial', sans-serif; background: #f5f5f5; padding: 10px; font-size: 13px; }
        .container { max-width: 1600px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; font-size: 20px; }
        h1 img { height: 24px; }
        .card { background: #fff; border: 1px solid #d0d0d0; padding: 12px; margin-bottom: 10px; }
        .card h2 { color: #333; margin-bottom: 8px; font-size: 14px; font-weight: bold; border-bottom: 2px solid #217346; padding-bottom: 5px; }

        /* 통계 박스 - 컴팩트하게 */
        .stats { display: flex; gap: 10px; margin-bottom: 10px; }
        .stat-box { flex: 1; background: #217346; color: #fff; padding: 12px; text-align: center; border: 1px solid #1a5c38; }
        .stat-box.pending { background: #c5504b; border-color: #9c3f3b; }
        .stat-box.shipped { background: #4472c4; border-color: #365a99; }
        .stat-box .number { font-size: 22px; font-weight: bold; }
        .stat-box .label { font-size: 11px; opacity: 0.95; }

        /* 2열 레이아웃 */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }

        .form-group { margin-bottom: 8px; }
        .form-group label { display: block; margin-bottom: 3px; font-weight: bold; color: #333; font-size: 12px; }
        .form-group input, .form-group select { padding: 6px 8px; border: 1px solid #a6a6a6; width: 100%; max-width: 180px; font-size: 12px; }
        .form-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
        .btn { padding: 6px 14px; border: 1px solid #217346; cursor: pointer; font-size: 12px; transition: all 0.2s; font-weight: bold; }
        .btn-primary { background: #217346; color: #fff; }
        .btn-primary:hover { background: #1a5c38; }
        .btn-success { background: #4472c4; color: #fff; border-color: #365a99; }
        .btn-success:hover { background: #365a99; }
        .btn-logen { background: #c5504b; color: #fff; border-color: #9c3f3b; font-size: 11px; }
        .btn-logen:hover { background: #9c3f3b; }

        .message { padding: 10px; margin-bottom: 10px; font-size: 12px; border: 1px solid; }
        .message.success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        .file-upload { border: 2px dashed #a6a6a6; padding: 15px; text-align: center; cursor: pointer; transition: all 0.2s; background: #fafafa; }
        .file-upload:hover { border-color: #217346; background: #f0f0f0; }
        .file-upload input[type="file"] { display: none; }
        .file-upload .icon { font-size: 24px; color: #666; margin-bottom: 5px; }
        .file-upload p { color: #666; font-size: 12px; }

        .links { margin-top: 10px; padding-top: 8px; border-top: 1px solid #d0d0d0; }
        .links a { color: #217346; text-decoration: none; margin-right: 15px; font-size: 12px; }
        .links a:hover { text-decoration: underline; }

        /* 엑셀 스타일 테이블 */
        table { width: 100%; border-collapse: collapse; border: 1px solid #a6a6a6; margin-top: 8px; }
        th, td { padding: 6px 8px; text-align: left; border: 1px solid #d0d0d0; font-size: 12px; }
        th { background: #217346; color: #fff; font-weight: bold; text-align: center; }
        td { background: #fff; }
        tr:nth-child(even) td { background: #f9f9f9; }
        tr:hover td { background: #e8f5e9; }

        .waybill-link { color: #217346; text-decoration: none; font-weight: bold; }
        .waybill-link:hover { text-decoration: underline; }
        .status-badge { padding: 2px 6px; font-size: 11px; font-weight: bold; border: 1px solid; }
        .status-badge.pending { background: #fff3cd; color: #856404; border-color: #ffc107; }
        .status-badge.shipped { background: #d4edda; color: #155724; border-color: #28a745; }

        /* 페이지네이션 스타일 */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            background: #fff;
            transition: all 0.3s;
            font-size: 14px;
        }
        .page-btn:hover {
            background: #f8f9fa;
            border-color: #1a73e8;
            color: #1a73e8;
        }
        .page-btn.active {
            background: #1a73e8;
            color: #fff;
            border-color: #1a73e8;
            font-weight: bold;
        }
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

    <!-- 2열 그리드: 엑셀 내보내기 + 운송장 등록 -->
    <div class="form-grid">
        <!-- 로젠 엑셀 내보내기 -->
        <div class="card">
            <h2>📤 로젠 엑셀 양식 내보내기</h2>
            <p style="color: #666; margin-bottom: 10px; font-size: 11px;">
                주문 데이터를 로젠택배 시스템에 업로드할 수 있는 엑셀 형식으로 다운로드합니다.
            </p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="export_logen">
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
                        <option value="all">전체</option>
                        <option value="pending">발송 대기 (운송장 미등록)</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">📥 엑셀 다운로드</button>
                </div>
            </form>
            <div class="links" style="font-size: 11px;">
                <a href="https://www.ilogen.com/web/enterprise/system" target="_blank">📋 매뉴얼</a>
                <a href="https://logis.ilogen.com/common/html/main.html" target="_blank">🔑 로그인</a>
            </div>
        </div>

        <!-- 운송장 번호 일괄 등록 -->
        <div class="card">
            <h2>📥 운송장 번호 일괄 등록</h2>
            <p style="color: #666; margin-bottom: 10px; font-size: 11px;">
                <strong>✅ Excel 직접 업로드 지원!</strong> 로젠택배에서 다운로드한 .xlsx/.xls 파일을 <strong>변환 없이 바로 업로드</strong>하세요.<br>
                <span style="color: #217346; font-weight: 500;">자동으로 주문번호(dsno)와 운송장번호를 찾아서 처리합니다.</span>
            </p>
            <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="action" value="import_waybill">
                <div class="file-upload" onclick="document.getElementById('waybill_file').click();" style="padding: 10px;">
                    <div class="icon" style="font-size: 20px;">📄</div>
                    <p id="file-name" style="font-size: 11px;">클릭하여 Excel 파일 선택 (.xlsx, .xls 직접 업로드 가능)</p>
                    <input type="file" name="waybill_file" id="waybill_file" accept=".xls,.xlsx,.csv,.txt" onchange="updateFileName(this)">
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-success" id="uploadBtn" disabled style="width: 100%;">📤 운송장 등록</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 최근 발송 목록 -->
    <div class="card">
        <h2>📋 최근 발송 현황</h2>
        <?php
        // 페이지네이션 설정
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        // 전체 레코드 수 조회
        $count_query = "SELECT COUNT(*) as total
                       FROM mlangorder_printauto
                       WHERE zip1 IS NOT NULL AND zip1 != '' AND zip1 != '0'";
        $count_result = mysqli_query($connect, $count_query);
        $total_records = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_records / $per_page);
        ?>

        <div style="margin-bottom: 10px; color: #666; font-size: 14px;">
            전체 <?php echo number_format($total_records); ?>건 |
            <?php echo $page; ?> / <?php echo number_format($total_pages); ?> 페이지
        </div>

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
            $recent_query = "SELECT no, date, name, Type, Type_1, zip1, logen_tracking_no, waybill_date
                            FROM mlangorder_printauto
                            WHERE zip1 IS NOT NULL AND zip1 != '' AND zip1 != '0'
                            ORDER BY no DESC
                            LIMIT $per_page OFFSET $offset";
            $recent_result = mysqli_query($connect, $recent_query);

            if (mysqli_num_rows($recent_result) > 0):
                while ($row = mysqli_fetch_assoc($recent_result)):
            ?>
                <tr>
                    <td><strong><?php echo $row['no']; ?></strong></td>
                    <td><?php echo date('m/d H:i', strtotime($row['date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['name'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars(mb_substr($row['Type_1'] ?: $row['Type'], 0, 20)); ?></td>
                    <td><?php echo htmlspecialchars(mb_substr($row['zip1'], 0, 30)); ?></td>
                    <td>
                        <?php if (!empty($row['logen_tracking_no'])): ?>
                        <a href="https://www.ilogen.com/web/personal/trace/<?php echo $row['logen_tracking_no']; ?>"
                           target="_blank" class="waybill-link">
                            <?php echo $row['logen_tracking_no']; ?>
                        </a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row['logen_tracking_no'])): ?>
                        <span class="status-badge shipped">발송완료</span>
                        <?php else: ?>
                        <span class="status-badge pending">대기중</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                        발송 데이터가 없습니다.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            // 이전 페이지
            if ($page > 1):
            ?>
            <a href="?page=1" class="page-btn">&laquo; 처음</a>
            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">&lsaquo; 이전</a>
            <?php endif; ?>

            <?php
            // 페이지 번호 (현재 페이지 기준 앞뒤 5개씩)
            $start_page = max(1, $page - 5);
            $end_page = min($total_pages, $page + 5);

            for ($i = $start_page; $i <= $end_page; $i++):
                $active_class = ($i == $page) ? ' active' : '';
            ?>
            <a href="?page=<?php echo $i; ?>" class="page-btn<?php echo $active_class; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>

            <?php
            // 다음 페이지
            if ($page < $total_pages):
            ?>
            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">다음 &rsaquo;</a>
            <a href="?page=<?php echo $total_pages; ?>" class="page-btn">마지막 &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
