<?php
/**
 * 배송 관리 시스템 - 로젠택배 연동
 * - 로젠 엑셀 양식 내보내기
 * - 운송장 번호 일괄 등록
 */

// 관리자 인증 (embed 토큰 또는 세션)
$is_authed = false;

// 1. 대시보드 embed 토큰 검증
$eauth = $_GET['_eauth'] ?? '';
if (!empty($eauth)) {
    $self_path = '/shop_admin/delivery_manager.php';
    $expected = hash_hmac('sha256', $self_path . date('Y-m-d'), 'duson_embed_2026_secret');
    if (hash_equals($expected, $eauth)) {
        $is_authed = true;
    }
}

// 2. 세션 인증
if (!$is_authed) {
    require_once __DIR__ . '/../admin/includes/admin_auth.php';
    $is_authed = isAdminLoggedIn();
}

if (!$is_authed) {
    header('Location: /admin/mlangprintauto/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
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

// 품목 한글 레이블
$type_labels = [
    'NameCard' => '명함', 'Inserted' => '전단지', 'inserted' => '전단지',
    'NcrFlambeau' => '양식지', 'ncrflambeau' => '양식지',
    'Sticker' => '스티커', 'sticker' => '스티커', 'sticker_new' => '스티커',
    'Msticker' => '자석스티커', 'msticker' => '자석스티커',
    'Envelope' => '봉투', 'envelope' => '봉투',
    'LittlePrint' => '포스터', 'littleprint' => '포스터',
    'MerchandiseBond' => '상품권', 'merchandisebond' => '상품권',
    'Cadarok' => '카다록', 'cadarok' => '카다록',
];

// 규격별 택배비 룩업 (post_list74.php와 동일)
$shipping_rules = [
    'A6'  => ['boxes' => 1, 'cost' => 4000],
    'B6'  => ['boxes' => 1, 'cost' => 4000],
    'A5'  => ['boxes' => 1, 'cost' => 6000],
    'B5'  => ['boxes' => 2, 'cost' => 7000],
    'A4'  => ['boxes' => 1, 'cost' => 6000],
    'B4'  => ['boxes' => 2, 'cost' => 12000],
    'A3'  => ['boxes' => 2, 'cost' => 12000],
];

// 택배비 자동 계산 (규격+연수 기반)
function calcShipping($data, $shipping_rules) {
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';
    $detected_size = '';
    if (preg_match('/16절|B5/i', $type1_raw)) $detected_size = 'B5';
    elseif (preg_match('/32절|B6/i', $type1_raw)) $detected_size = 'B6';
    elseif (preg_match('/8절|B4/i', $type1_raw)) $detected_size = 'B4';
    elseif (preg_match('/A3/i', $type1_raw)) $detected_size = 'A3';
    elseif (preg_match('/A4/i', $type1_raw)) $detected_size = 'A4';
    elseif (preg_match('/A5/i', $type1_raw)) $detected_size = 'A5';
    elseif (preg_match('/A6/i', $type1_raw)) $detected_size = 'A6';

    $yeon = 1;
    if (!empty($data['quantity_value']) && floatval($data['quantity_value']) > 0) {
        $yeon = floatval($data['quantity_value']);
    }

    $r = 1; $w = 3000;
    if (!empty($detected_size) && isset($shipping_rules[$detected_size])) {
        $rule = $shipping_rules[$detected_size];
        $r = (int)ceil($yeon) * $rule['boxes'];
        $w = (int)ceil($yeon) * $rule['cost'];
    } elseif (preg_match("/NameCard/i", $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match("/MerchandiseBond/i", $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match("/sticker/i", $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match("/envelop/i", $data['Type'])) { $r = 1; $w = 3000; }

    return ['boxes' => $r, 'fee' => $w];
}

// Type_1 JSON → 읽기 좋은 텍스트
function parseType1Display($type1_raw) {
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data) {
            if (isset($json_data['formatted_display'])) {
                return str_replace(["\r\n", "\r", "\n"], ' ', $json_data['formatted_display']);
            }
            $parts = [];
            if (!empty($json_data['spec_material'])) $parts[] = $json_data['spec_material'];
            if (!empty($json_data['spec_size'])) $parts[] = $json_data['spec_size'];
            if (!empty($json_data['spec_sides'])) $parts[] = $json_data['spec_sides'];
            if (!empty($json_data['quantity_display'])) $parts[] = $json_data['quantity_display'];
            if (!empty($json_data['spec_design'])) $parts[] = $json_data['spec_design'];
            if (!empty($parts)) return implode(' / ', $parts);
        }
    }
    return $type1_raw;
}

// Type → 한글 품목명
function getTypeLabel($type_raw, $type_labels) {
    $display = trim($type_raw);
    if (!empty($display) && $display[0] === '{') {
        $jt = json_decode($display, true);
        if ($jt && isset($jt['product_type'])) $display = $jt['product_type'];
    }
    return isset($type_labels[$display]) ? $type_labels[$display] : $display;
}

// 액션 처리
$action = $_REQUEST['action'] ?? '';
$message = '';
$error = '';

// 로젠 엑셀 내보내기 (HTML 테이블 형식 - export_logen_excel74.php와 동일)
if ($action === 'export_logen') {
    // 선택 항목 모드 vs 전체 모드
    $selected_nos = $_POST['selected_nos'] ?? '';
    $custom_box_qty = [];
    $custom_delivery_fee = [];
    $custom_fee_type = [];

    if (!empty($_POST['box_qty_json'])) {
        $decoded = json_decode($_POST['box_qty_json'], true);
        if (is_array($decoded)) $custom_box_qty = $decoded;
    }
    if (!empty($_POST['delivery_fee_json'])) {
        $decoded = json_decode($_POST['delivery_fee_json'], true);
        if (is_array($decoded)) $custom_delivery_fee = $decoded;
    }
    if (!empty($_POST['fee_type_json'])) {
        $decoded = json_decode($_POST['fee_type_json'], true);
        if (is_array($decoded)) $custom_fee_type = $decoded;
    }

    $where_parts = [];
    if (!empty($selected_nos)) {
        $nos_array = array_map('intval', explode(',', $selected_nos));
        $where_parts[] = "no IN (" . implode(',', $nos_array) . ")";
    } else {
        $date_from = $_POST['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $date_to = $_POST['date_to'] ?? date('Y-m-d');
        $export_status = $_POST['export_status'] ?? 'all';

        $where_parts[] = "date >= '" . mysqli_real_escape_string($connect, $date_from) . "'";
        $where_parts[] = "date < DATE_ADD('" . mysqli_real_escape_string($connect, $date_to) . "', INTERVAL 1 DAY)";
        $where_parts[] = "(zip1 IS NOT NULL AND zip1 != '' AND zip1 != '0')";
        if ($export_status === 'pending') {
            $where_parts[] = "(waybill_no IS NULL OR waybill_no = '')";
        }
    }

    $query = "SELECT * FROM mlangorder_printauto WHERE " . implode(' AND ', $where_parts) . " ORDER BY no DESC";
    $result = safe_mysqli_query($connect, $query);

    $rows = [];
    while ($data = mysqli_fetch_assoc($result)) {
        $no = $data['no'];
        $ship = calcShipping($data, $shipping_rules);
        $r = isset($custom_box_qty[$no]) && $custom_box_qty[$no] !== '' ? intval($custom_box_qty[$no]) : $ship['boxes'];
        $w = isset($custom_delivery_fee[$no]) && $custom_delivery_fee[$no] !== '' ? intval($custom_delivery_fee[$no]) : $ship['fee'];
        $ft = isset($custom_fee_type[$no]) && $custom_fee_type[$no] !== '' ? $custom_fee_type[$no] : '착불';

        $rows[] = [
            trim($data['name'] ?? ''),
            trim($data['zip'] ?? ''),
            trim(($data['zip1'] ?? '') . ' ' . ($data['zip2'] ?? '')),
            trim($data['phone'] ?? ''),
            trim($data['Hendphone'] ?? ''),
            $r, $w, $ft,
            getTypeLabel($data['Type'] ?? '', $type_labels),
            $no,
            parseType1Display($data['Type_1'] ?? ''),
        ];
    }

    $filename = "logen_" . date('Y-m-d_His') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header("Pragma: no-cache");
    header("Cache-Control: no-cache");
    header("Expires: 0");
    echo "\xEF\xBB\xBF";
    ?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=utf-8">
<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name>Sheet1</x:Name><x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
</head>
<body>
<table border="1">
<tr style="background-color:#CCCCCC; font-weight:bold;">
<td>수하인명</td><td>우편번호</td><td>주소</td><td>전화</td><td>핸드폰</td>
<td>박스수량</td><td>택배비</td><td>운임구분</td><td>Type</td><td>기타</td><td>품목</td>
</tr>
<?php foreach ($rows as $row): ?>
<tr>
<td><?php echo htmlspecialchars($row[0]); ?></td>
<td style="mso-number-format:'\@'"><?php echo htmlspecialchars($row[1]); ?></td>
<td><?php echo htmlspecialchars($row[2]); ?></td>
<td style="mso-number-format:'\@'"><?php echo htmlspecialchars($row[3]); ?></td>
<td style="mso-number-format:'\@'"><?php echo htmlspecialchars($row[4]); ?></td>
<td><?php echo $row[5]; ?></td>
<td><?php echo $row[6]; ?></td>
<td><?php echo htmlspecialchars($row[7]); ?></td>
<td><?php echo htmlspecialchars($row[8]); ?></td>
<td><?php echo htmlspecialchars($row[9]); ?></td>
<td><?php echo htmlspecialchars($row[10]); ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
    <?php
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
        .card h2 { color: #333; margin-bottom: 8px; font-size: 14px; font-weight: bold; border-bottom: 2px solid #1E4E79; padding-bottom: 5px; }

        /* 통계 박스 - 컴팩트하게 */
        .stats { display: flex; gap: 10px; margin-bottom: 10px; }
        .stat-box { flex: 1; background: #1E4E79; color: #fff; padding: 12px; text-align: center; border: 1px solid #173d5e; }
        .stat-box.pending { background: #c5504b; border-color: #9c3f3b; }
        .stat-box.shipped { background: #2d6ea7; border-color: #245a8a; }
        .stat-box .number { font-size: 22px; font-weight: bold; }
        .stat-box .label { font-size: 11px; opacity: 0.95; }

        /* 2열 레이아웃 */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }

        .form-group { margin-bottom: 8px; }
        .form-group label { display: block; margin-bottom: 3px; font-weight: bold; color: #333; font-size: 12px; }
        .form-group input, .form-group select { padding: 6px 8px; border: 1px solid #a6a6a6; width: 100%; max-width: 180px; font-size: 12px; }
        .form-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
        .btn { padding: 6px 14px; border: 1px solid #1E4E79; cursor: pointer; font-size: 12px; transition: all 0.2s; font-weight: bold; }
        .btn-primary { background: #1E4E79; color: #fff; }
        .btn-primary:hover { background: #173d5e; }
        .btn-success { background: #2d6ea7; color: #fff; border-color: #245a8a; }
        .btn-success:hover { background: #245a8a; }
        .btn-logen { background: #c5504b; color: #fff; border-color: #9c3f3b; font-size: 11px; }
        .btn-logen:hover { background: #9c3f3b; }

        .message { padding: 10px; margin-bottom: 10px; font-size: 12px; border: 1px solid; }
        .message.success { background: #e8f0f7; color: #1E4E79; border-color: #b8d4ed; }
        .message.error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        .file-upload { border: 2px dashed #a6a6a6; padding: 15px; text-align: center; cursor: pointer; transition: all 0.2s; background: #fafafa; }
        .file-upload:hover { border-color: #1E4E79; background: #f0f0f0; }
        .file-upload input[type="file"] { display: none; }
        .file-upload .icon { font-size: 24px; color: #666; margin-bottom: 5px; }
        .file-upload p { color: #666; font-size: 12px; }

        .links { margin-top: 10px; padding-top: 8px; border-top: 1px solid #d0d0d0; }
        .links a { color: #1E4E79; text-decoration: none; margin-right: 15px; font-size: 12px; }
        .links a:hover { text-decoration: underline; }

        /* 엑셀 스타일 테이블 */
        table { width: 100%; border-collapse: collapse; border: 1px solid #a6a6a6; margin-top: 8px; table-layout: fixed; }
        th, td { padding: 6px 8px; text-align: left; border: 1px solid #d0d0d0; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        th { background: #1E4E79; color: #fff; font-weight: bold; text-align: center; }
        td { background: #fff; }
        tr:nth-child(even) td { background: #f9f9f9; }
        tr:hover td { background: #e8f0f7; }

        .waybill-link { color: #1E4E79; text-decoration: none; font-weight: bold; }
        .waybill-link:hover { text-decoration: underline; }
        .status-badge { padding: 2px 6px; font-size: 11px; font-weight: bold; border: 1px solid; }
        .status-badge.pending { background: #fff3cd; color: #856404; border-color: #ffc107; }
        .status-badge.shipped { background: #e8f0f7; color: #1E4E79; border-color: #2d6ea7; }

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
            border-color: #1E4E79;
            color: #1E4E79;
        }
        .page-btn.active {
            background: #1E4E79;
            color: #fff;
            border-color: #1E4E79;
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
            <form method="POST" action="" id="exportForm">
                <input type="hidden" name="action" value="export_logen">
                <div class="form-row" style="margin-bottom: 8px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>시작일</label>
                        <input type="date" name="date_from" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>종료일</label>
                        <input type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>상태</label>
                        <select name="export_status">
                            <option value="all">전체</option>
                            <option value="pending">발송 대기</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">📥 기간별 다운로드</button>
                    <button type="button" class="btn btn-logen" style="flex:1;" onclick="exportSelectedToLogenExcel()">📥 선택 항목 다운로드</button>
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
                <span style="color: #1E4E79; font-weight: 500;">자동으로 주문번호(dsno)와 운송장번호를 찾아서 처리합니다.</span>
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

    <!-- 최근 발송 목록 (post_list74.php와 동일 구조) -->
    <div class="card">
        <h2>📋 최근 발송 현황</h2>
        <?php
        // 주소 필터 (post_list74.php와 동일)
        $base_condition = "(delivery != '방문' AND delivery != '방문수령' OR delivery IS NULL)
            AND (
              (zip1 LIKE '%구 %' OR zip1 LIKE '%구%동%')
              OR (zip1 LIKE '%로 %' OR zip1 LIKE '%로%번길%')
              OR (zip1 LIKE '%길 %')
              OR (zip1 LIKE '%대로 %' OR zip1 LIKE '%대로%번길%')
              OR (zip2 LIKE '%-%')
              OR (zip REGEXP '^[0-9]{5}$')
            )";

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE $base_condition";
        $count_result = mysqli_query($connect, $count_query);
        $total_records = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_records / $per_page);
        ?>

        <div style="margin-bottom: 8px; color: #666; font-size: 12px;">
            전체 <?php echo number_format($total_records); ?>건 |
            <?php echo $page; ?> / <?php echo number_format($total_pages); ?> 페이지
        </div>

        <form id="listForm">
        <table>
            <colgroup>
                <col style="width:28px">
                <col style="width:62px">
                <col style="width:95px">
                <col style="width:85px">
                <col style="width:52px">
                <col style="width:22%">
                <col style="width:95px">
                <col style="width:80px">
                <col style="width:36px">
                <col style="width:58px">
                <col style="width:48px">
                <col style="width:50px">
                <col style="width:55px">
                <col style="width:100px">
            </colgroup>
            <tr style="background: #1E4E79; color: #fff;">
                <th><input type="checkbox" onclick="toggleAll(this)"></th>
                <th>주문번호</th>
                <th>날짜</th>
                <th>수하인명</th>
                <th>우편번호</th>
                <th>주소</th>
                <th>전화</th>
                <th>핸드폰</th>
                <th>박스</th>
                <th>택배비</th>
                <th>운임</th>
                <th>Type</th>
                <th>기타</th>
                <th>품목</th>
            </tr>
            <?php
            $recent_query = "SELECT * FROM mlangorder_printauto WHERE $base_condition ORDER BY no DESC LIMIT $per_page OFFSET $offset";
            $recent_result = safe_mysqli_query($connect, $recent_query);

            if ($recent_result && mysqli_num_rows($recent_result) > 0):
                while ($data = mysqli_fetch_assoc($recent_result)):
                    $no = $data['no'];
                    $ship = calcShipping($data, $shipping_rules);
                    $type1_display = htmlspecialchars(parseType1Display($data['Type_1'] ?? ''));
            ?>
            <tr>
                <td><input type="checkbox" name="selected_no[]" value="<?php echo $no; ?>"></td>
                <td><?php echo htmlspecialchars($no); ?></td>
                <td><?php echo htmlspecialchars($data['date'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($data['name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($data['zip'] ?? ''); ?></td>
                <td title="<?php echo htmlspecialchars(($data['zip1'] ?? '') . ' ' . ($data['zip2'] ?? '')); ?>"><?php echo htmlspecialchars(($data['zip1'] ?? '') . ' ' . ($data['zip2'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars($data['phone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($data['Hendphone'] ?? ''); ?></td>
                <td style="text-align:center"><input type="text" id="box_qty_<?php echo $no; ?>" value="<?php echo $ship['boxes']; ?>" size="2" style="text-align:center; font-size:11px; width:100%; box-sizing:border-box; padding:1px;"></td>
                <td><input type="text" id="delivery_fee_<?php echo $no; ?>" value="<?php echo $ship['fee']; ?>" size="4" style="font-size:11px; width:100%; box-sizing:border-box; padding:1px;"></td>
                <td><select id="fee_type_<?php echo $no; ?>" style="font-size:10px; width:100%; padding:0;"><option value="착불" selected>착불</option><option value="선불">선불</option></select></td>
                <td><?php echo htmlspecialchars(getTypeLabel($data['Type'] ?? '', $type_labels)); ?></td>
                <td><?php echo $no; ?></td>
                <td title="<?php echo $type1_display; ?>"><?php echo $type1_display; ?></td>
            </tr>
            <?php
                endwhile;
            else:
            ?>
            <tr><td colspan="14" style="text-align:center; padding:30px; color:#999;">발송 데이터가 없습니다.</td></tr>
            <?php endif; ?>
        </table>
        </form>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=1" class="page-btn">&laquo; 처음</a>
            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">&lsaquo; 이전</a>
            <?php endif; ?>
            <?php
            $start_page = max(1, $page - 5);
            $end_page = min($total_pages, $page + 5);
            for ($i = $start_page; $i <= $end_page; $i++):
                $active_class = ($i == $page) ? ' active' : '';
            ?>
            <a href="?page=<?php echo $i; ?>" class="page-btn<?php echo $active_class; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">다음 &rsaquo;</a>
            <a href="?page=<?php echo $total_pages; ?>" class="page-btn">마지막 &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateFileName(input) {
    var fileName = input.files[0] ? input.files[0].name : '클릭하여 엑셀 파일 선택';
    document.getElementById('file-name').textContent = fileName;
    document.getElementById('uploadBtn').disabled = !input.files[0];
}

function toggleAll(source) {
    var checkboxes = document.getElementsByName('selected_no[]');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}

function exportSelectedToLogenExcel() {
    var checkboxes = document.getElementsByName('selected_no[]');
    var selected = [];
    var boxQty = {};
    var deliveryFee = {};
    var feeType = {};

    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            var no = checkboxes[i].value;
            selected.push(no);
            var qtyInput = document.getElementById('box_qty_' + no);
            var feeInput = document.getElementById('delivery_fee_' + no);
            var typeSelect = document.getElementById('fee_type_' + no);
            if (qtyInput) boxQty[no] = qtyInput.value;
            if (feeInput) deliveryFee[no] = feeInput.value;
            if (typeSelect) feeType[no] = typeSelect.value;
        }
    }

    if (selected.length === 0) {
        alert('다운로드할 항목을 선택해주세요.');
        return;
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    form.target = '_blank';

    var fields = {
        'action': 'export_logen',
        'selected_nos': selected.join(','),
        'box_qty_json': JSON.stringify(boxQty),
        'delivery_fee_json': JSON.stringify(deliveryFee),
        'fee_type_json': JSON.stringify(feeType)
    };

    for (var key in fields) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
</body>
</html>
