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
        // A4 특약: 0.5연(2000매) 이하 = 1박스 3,500원 (로젠 계약)
        if ($detected_size === 'A4' && $yeon <= 0.5) {
            $r = 1;
            $w = 3500;
        }
    } elseif (preg_match("/NameCard/i", $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match("/MerchandiseBond/i", $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match("/sticker/i", $data['Type'])) { $r = 1; $w = 3000; }
    elseif (preg_match('/envelop/i', $data['Type'])) {
        // 봉투 종류 감지 (Type_1에서 대봉투/소봉투/자켓 구분)
        $is_big = (mb_strpos($type1_raw, '대봉투') !== false);
        $is_jacket = (preg_match('/쟈켓|자켓/u', $type1_raw));

        // 수량 파싱 (Type_1에서 숫자만 있는 줄)
        $qty = 500;
        $env_lines = preg_split('/\r?\n/', trim($type1_raw));
        foreach ($env_lines as $el) {
            $el = trim($el);
            if (preg_match('/^[\d,]+$/', $el) && intval(str_replace(',', '', $el)) >= 100) {
                $qty = intval(str_replace(',', '', $el));
                break;
            }
        }

        // 펼침면 크기 기반 무게 계산 (대봉투/소봉투/자켓 공통)
        if ($is_big) {
            $env_w = 510; $env_h = 387; $env_gsm = 120; // 대봉투 120g
        } elseif ($is_jacket) {
            $env_w = 262; $env_h = 238; $env_gsm = 100;
        } else {
            $env_w = 238; $env_h = 262; $env_gsm = 100; // 소봉투
        }
        $weight_per_piece = $env_gsm * ($env_w / 1000) * ($env_h / 1000); // g
        $total_kg = round(($weight_per_piece * $qty) / 1000, 1);
        // 박스 분리: 20kg 초과 시 분리
        $r = max(1, (int)ceil($total_kg / 20));
        $kg_per_box = ($r > 0) ? $total_kg / $r : $total_kg;
        if ($is_big) {
            // 대봉투 특약: 3,500원/box (로젠 계약)
            $w = $r * 3500;
        } else {
            // 소봉투/자켓: 무게별 택배비 (로젠 요금표)
            if ($kg_per_box <= 3) $fee_per_box = 3000;
            elseif ($kg_per_box <= 10) $fee_per_box = 3500;
            elseif ($kg_per_box <= 15) $fee_per_box = 4000;
            elseif ($kg_per_box <= 20) $fee_per_box = 5000;
            else $fee_per_box = 6000;
            $w = $r * $fee_per_box;
        }
    }

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

                    // 컬럼 인덱스 찾기 (주문번호/이름/전화/주소/운송장 자동 감지)
                    $waybill_col = -1;
                    $name_col = -1;
                    $phone_col = -1;
                    $phone2_col = -1;
                    $addr_col = -1;
                    $ordno_col = -1;

                    // 헤더 행에서 "주문번호" 또는 "기타" 컬럼 검색
                    for ($hi = 0; $hi < $data_row_idx && $hi < count($rows); $hi++) {
                        foreach ($rows[$hi] as $ci => $cv) {
                            $cv = trim($cv);
                            if ($ordno_col === -1 && preg_match('/^(주문번호|기타)$/u', $cv)) {
                                $ordno_col = $ci;
                            }
                        }
                    }

                    $scan_limit = min(20, count($data_rows));
                    for ($scan_idx = 0; $scan_idx < $scan_limit; $scan_idx++) {
                        $row = $data_rows[$scan_idx];

                        foreach ($row as $idx => $value) {
                            $value = trim($value);

                            // 운송장번호: 4로 시작하는 11~12자리 숫자
                            if ($waybill_col === -1 && preg_match('/^4[0-9]{10,11}$/', $value)) {
                                $waybill_col = $idx;
                            }

                            // 주문번호: 5자리 숫자 (헤더에서 못 찾았을 때 데이터 패턴으로 감지)
                            if ($ordno_col === -1 && preg_match('/^[0-9]{5,6}$/', $value) && $idx !== ($waybill_col ?? -1)) {
                                $ordno_col = $idx;
                            }

                            // 수하인명: 한글 2~10자
                            if ($name_col === -1 && preg_match('/^[가-힣]{2,10}$/u', $value)) {
                                $name_col = $idx;
                            }

                            // 전화번호: 0으로 시작, 하이픈 포함 가능
                            if (preg_match('/^0[0-9]{1,2}-?[0-9]{3,4}-?[0-9]{4}$/', $value)) {
                                if ($phone_col === -1) {
                                    $phone_col = $idx;
                                } elseif ($phone2_col === -1 && $idx !== $phone_col) {
                                    $phone2_col = $idx;
                                }
                            }

                            // 주소: 시/도로 시작하는 10자 이상 문자열
                            if ($addr_col === -1 && mb_strlen($value) >= 10 &&
                                preg_match('/^(서울|부산|대구|인천|광주|대전|울산|세종|경기|강원|충북|충남|전북|전남|경북|경남|제주)/u', $value)) {
                                $addr_col = $idx;
                            }
                        }

                        if ($waybill_col !== -1 && $name_col !== -1 && $phone_col !== -1) {
                            break;
                        }
                    }

                    if ($waybill_col === -1) {
                        $debug_cols = [];
                        if (count($data_rows) > 0) {
                            foreach ($data_rows[0] as $di => $dv) {
                                $debug_cols[] = "[" . ($di+1) . "] " . mb_substr(trim($dv), 0, 30);
                            }
                        }
                        $error = "Excel 파일에서 운송장번호를 감지할 수 없습니다.<br><br>" .
                                 "첫 행 데이터: " . implode(' | ', $debug_cols);
                    } elseif ($name_col === -1 && $phone_col === -1 && $addr_col === -1) {
                        $debug_cols = [];
                        if (count($data_rows) > 0) {
                            foreach ($data_rows[0] as $di => $dv) {
                                $debug_cols[] = "[" . ($di+1) . "] " . mb_substr(trim($dv), 0, 30);
                            }
                        }
                        $error = "Excel 파일에서 매칭에 필요한 정보(이름/전화/주소)를 감지할 수 없습니다.<br><br>" .
                                 "감지 결과: 운송장=컬럼" . ($waybill_col+1) .
                                 ", 이름=없음, 전화=없음, 주소=없음" .
                                 "<br><br>첫 행 데이터: " . implode(' | ', $debug_cols);
                    } else {
                        // 매칭 쿼리 준비: 주문번호 → 이름 → 전화앞5자리 → 주소(동/로/길)
                        $match_by_ordno_stmt = mysqli_prepare($connect,
                            "SELECT no, name FROM mlangorder_printauto
                             WHERE no = ?
                             AND (waybill_no IS NULL OR waybill_no = '')
                             ORDER BY no ASC LIMIT 1");

                        $match_by_name_stmt = mysqli_prepare($connect,
                            "SELECT no, name FROM mlangorder_printauto
                             WHERE name = ?
                             AND (waybill_no IS NULL OR waybill_no = '')
                             AND date >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                             ORDER BY no ASC LIMIT 1");

                        $match_by_phone_stmt = mysqli_prepare($connect,
                            "SELECT no, name FROM mlangorder_printauto
                             WHERE (LEFT(REPLACE(phone,'-',''), 5) = ? OR LEFT(REPLACE(Hendphone,'-',''), 5) = ?)
                             AND (waybill_no IS NULL OR waybill_no = '')
                             AND date >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                             ORDER BY no ASC LIMIT 1");

                        $match_by_addr_stmt = mysqli_prepare($connect,
                            "SELECT no, name FROM mlangorder_printauto
                             WHERE zip1 LIKE CONCAT('%', ?, '%')
                             AND (waybill_no IS NULL OR waybill_no = '')
                             AND date >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                             ORDER BY no ASC LIMIT 1");

                        $update_stmt = mysqli_prepare($connect,
                            "UPDATE mlangorder_printauto
                             SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
                             WHERE no = ?");

                        $skipped = 0;
                        $match_methods = []; // 매칭 방법별 카운트
                        foreach ($data_rows as $row) {
                            $waybill_no = isset($row[$waybill_col]) ? trim($row[$waybill_col]) : '';
                            $recv_name = ($name_col !== -1 && isset($row[$name_col])) ? trim($row[$name_col]) : '';
                            $recv_phone = ($phone_col !== -1 && isset($row[$phone_col])) ? trim($row[$phone_col]) : '';
                            $recv_addr = ($addr_col !== -1 && isset($row[$addr_col])) ? trim($row[$addr_col]) : '';
                            $recv_ordno = ($ordno_col !== -1 && isset($row[$ordno_col])) ? trim($row[$ordno_col]) : '';

                            if (empty($waybill_no) || !preg_match('/^4[0-9]{10,11}$/', $waybill_no)) {
                                continue;
                            }

                            $has_ordno = preg_match('/^[0-9]{5,6}$/', $recv_ordno);
                            $has_name = (mb_strlen($recv_name) >= 2);
                            $phone_clean = preg_replace('/[^0-9]/', '', $recv_phone);
                            $phone5 = substr($phone_clean, 0, 5);
                            $has_phone = (strlen($phone5) >= 5);
                            // 주소 키워드: 동/로/길 추출
                            $addr_keyword = '';
                            if (!empty($recv_addr)) {
                                if (preg_match('/([가-힣]+(?:동|로|길))\b/u', $recv_addr, $addr_m)) {
                                    $addr_keyword = $addr_m[1];
                                }
                            }
                            $has_addr = (mb_strlen($addr_keyword) >= 2);

                            if (!$has_ordno && !$has_name && !$has_phone && !$has_addr) { $skipped++; continue; }

                            // 1순위: 주문번호
                            $matched = null;
                            $method = '';
                            if ($has_ordno) {
                                $ordno_int = intval($recv_ordno);
                                mysqli_stmt_bind_param($match_by_ordno_stmt, "i", $ordno_int);
                                mysqli_stmt_execute($match_by_ordno_stmt);
                                $match_result = mysqli_stmt_get_result($match_by_ordno_stmt);
                                $matched = mysqli_fetch_assoc($match_result);
                                if ($matched) $method = '주문번호';
                            }

                            // 2순위: 이름
                            if (!$matched && $has_name) {
                                mysqli_stmt_bind_param($match_by_name_stmt, "s", $recv_name);
                                mysqli_stmt_execute($match_by_name_stmt);
                                $match_result = mysqli_stmt_get_result($match_by_name_stmt);
                                $matched = mysqli_fetch_assoc($match_result);
                                if ($matched) $method = '이름';
                            }

                            // 3순위: 전화번호 앞5자리
                            if (!$matched && $has_phone) {
                                mysqli_stmt_bind_param($match_by_phone_stmt, "ss", $phone5, $phone5);
                                mysqli_stmt_execute($match_by_phone_stmt);
                                $match_result = mysqli_stmt_get_result($match_by_phone_stmt);
                                $matched = mysqli_fetch_assoc($match_result);
                                if ($matched) $method = '전화';
                            }

                            // 4순위: 주소 (동/로/길)
                            if (!$matched && $has_addr) {
                                mysqli_stmt_bind_param($match_by_addr_stmt, "s", $addr_keyword);
                                mysqli_stmt_execute($match_by_addr_stmt);
                                $match_result = mysqli_stmt_get_result($match_by_addr_stmt);
                                $matched = mysqli_fetch_assoc($match_result);
                                if ($matched) $method = '주소';
                            }

                            if ($matched) {
                                $order_no = $matched['no'];
                                mysqli_stmt_bind_param($update_stmt, "si", $waybill_no, $order_no);
                                if (mysqli_stmt_execute($update_stmt) && mysqli_stmt_affected_rows($update_stmt) > 0) {
                                    $updated++;
                                    $match_methods[$method] = ($match_methods[$method] ?? 0) + 1;
                                } else {
                                    $failed++;
                                    if (count($errors) < 10) {
                                        $errors[] = "{$recv_name}({$recv_phone}) → 주문#{$order_no}: 업데이트 실패";
                                    }
                                }
                            } else {
                                $failed++;
                                if (count($errors) < 10) {
                                    $errors[] = "{$recv_name} / {$recv_phone} / " . mb_substr($recv_addr, 0, 20) . ": 매칭 없음";
                                }
                            }
                        }

                        mysqli_stmt_close($match_by_ordno_stmt);
                        mysqli_stmt_close($match_by_name_stmt);
                        mysqli_stmt_close($match_by_phone_stmt);
                        mysqli_stmt_close($match_by_addr_stmt);
                        mysqli_stmt_close($update_stmt);

                        // 결과 메시지
                        $detect_info = "감지: 운송장=컬럼" . ($waybill_col+1);
                        if ($ordno_col !== -1) $detect_info .= " / 주문번호=컬럼" . ($ordno_col+1);
                        if ($name_col !== -1) $detect_info .= " / 이름=컬럼" . ($name_col+1);
                        if ($phone_col !== -1) $detect_info .= " / 전화=컬럼" . ($phone_col+1);
                        if ($addr_col !== -1) $detect_info .= " / 주소=컬럼" . ($addr_col+1);

                        $method_info = [];
                        foreach ($match_methods as $m => $cnt) { $method_info[] = "{$m}:{$cnt}건"; }

                        $message = "✅ <b>Excel 운송장 등록 완료</b><br>" .
                                   "성공: {$updated}건 / 실패: {$failed}건" .
                                   ($skipped > 0 ? " / 스킵: {$skipped}건" : "") .
                                   (!empty($method_info) ? "<br>매칭방법: " . implode(", ", $method_info) : "") .
                                   "<br><small style='color:#666;'>{$detect_info}</small>";
                        if (count($errors) > 0) {
                            $message .= "<br><br><small style='color:#d97706;'>상세:<br>" .
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
            // 헤더 행에서 "주문번호" 또는 "기타" 컬럼 검색
            $ordno_col = -1;
            $all_lines = $lines; // 헤더 포함 원본 보존
            // 데이터 줄부터 시작
            $lines = array_slice($lines, $data_line_idx);

            for ($hi = 0; $hi < $data_line_idx; $hi++) {
                $hrow = str_getcsv($all_lines[$hi], "\t");
                foreach ($hrow as $ci => $cv) {
                    $cv = trim($cv);
                    if ($ordno_col === -1 && preg_match('/^(주문번호|기타)$/u', $cv)) {
                        $ordno_col = $ci;
                    }
                }
            }

            // 컬럼 인덱스 찾기 (주문번호/이름/전화/주소/운송장 자동 감지)
            $waybill_col = -1;
            $name_col = -1;
            $phone_col = -1;
            $phone2_col = -1;
            $addr_col = -1;

            $scan_limit = min(20, count($lines));
            for ($scan_idx = 0; $scan_idx < $scan_limit; $scan_idx++) {
                $row = str_getcsv($lines[$scan_idx], "\t");

                foreach ($row as $idx => $value) {
                    $value = trim($value);

                    if ($waybill_col === -1 && preg_match('/^4[0-9]{10,11}$/', $value)) {
                        $waybill_col = $idx;
                    }

                    // 주문번호: 5자리 숫자 (헤더에서 못 찾았을 때 데이터 패턴으로 감지)
                    if ($ordno_col === -1 && preg_match('/^[0-9]{5,6}$/', $value) && $idx !== ($waybill_col ?? -1)) {
                        $ordno_col = $idx;
                    }

                    if ($name_col === -1 && preg_match('/^[가-힣]{2,10}$/u', $value)) {
                        $name_col = $idx;
                    }

                    if (preg_match('/^0[0-9]{1,2}-?[0-9]{3,4}-?[0-9]{4}$/', $value)) {
                        if ($phone_col === -1) {
                            $phone_col = $idx;
                        } elseif ($phone2_col === -1 && $idx !== $phone_col) {
                            $phone2_col = $idx;
                        }
                    }

                    if ($addr_col === -1 && mb_strlen($value) >= 10 &&
                        preg_match('/^(서울|부산|대구|인천|광주|대전|울산|세종|경기|강원|충북|충남|전북|전남|경북|경남|제주)/u', $value)) {
                        $addr_col = $idx;
                    }
                }

                if ($waybill_col !== -1 && $name_col !== -1 && $phone_col !== -1) {
                    break;
                }
            }

            if ($waybill_col === -1) {
                $first_row_display = count($lines) > 0 ? str_getcsv($lines[0], "\t") : array();
                $debug_cols = [];
                foreach ($first_row_display as $di => $dv) {
                    $debug_cols[] = "[" . ($di+1) . "] " . mb_substr(trim($dv), 0, 30);
                }
                $error = "파일에서 운송장번호를 감지할 수 없습니다.<br><br>" .
                         "첫 행 데이터: " . implode(' | ', $debug_cols);
            } elseif ($name_col === -1 && $phone_col === -1 && $addr_col === -1) {
                $first_row_display = count($lines) > 0 ? str_getcsv($lines[0], "\t") : array();
                $debug_cols = [];
                foreach ($first_row_display as $di => $dv) {
                    $debug_cols[] = "[" . ($di+1) . "] " . mb_substr(trim($dv), 0, 30);
                }
                $error = "파일에서 매칭에 필요한 정보(이름/전화/주소)를 감지할 수 없습니다.<br><br>" .
                         "감지 결과: 운송장=컬럼" . ($waybill_col+1) .
                         ", 이름=없음, 전화=없음, 주소=없음" .
                         "<br><br>첫 행 데이터: " . implode(' | ', $debug_cols);
            } else {
            // 매칭 쿼리 준비: 주문번호 → 이름 → 전화앞5자리 → 주소(동/로/길)
            $match_by_ordno_stmt = mysqli_prepare($connect,
                "SELECT no, name FROM mlangorder_printauto
                 WHERE no = ?
                 AND (waybill_no IS NULL OR waybill_no = '')
                 ORDER BY no ASC LIMIT 1");

            $match_by_name_stmt = mysqli_prepare($connect,
                "SELECT no, name FROM mlangorder_printauto
                 WHERE name = ?
                 AND (waybill_no IS NULL OR waybill_no = '')
                 AND date >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                 ORDER BY no ASC LIMIT 1");

            $match_by_phone_stmt = mysqli_prepare($connect,
                "SELECT no, name FROM mlangorder_printauto
                 WHERE (LEFT(REPLACE(phone,'-',''), 5) = ? OR LEFT(REPLACE(Hendphone,'-',''), 5) = ?)
                 AND (waybill_no IS NULL OR waybill_no = '')
                 AND date >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                 ORDER BY no ASC LIMIT 1");

            $match_by_addr_stmt = mysqli_prepare($connect,
                "SELECT no, name FROM mlangorder_printauto
                 WHERE zip1 LIKE CONCAT('%', ?, '%')
                 AND (waybill_no IS NULL OR waybill_no = '')
                 AND date >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                 ORDER BY no ASC LIMIT 1");

            $update_stmt = mysqli_prepare($connect,
                "UPDATE mlangorder_printauto
                 SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
                 WHERE no = ?");

            $skipped = 0;
            $match_methods = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $cols = str_getcsv($line, "\t");
                $waybill_no = isset($cols[$waybill_col]) ? trim($cols[$waybill_col]) : '';
                $recv_name = ($name_col !== -1 && isset($cols[$name_col])) ? trim($cols[$name_col]) : '';
                $recv_phone = ($phone_col !== -1 && isset($cols[$phone_col])) ? trim($cols[$phone_col]) : '';
                $recv_addr = ($addr_col !== -1 && isset($cols[$addr_col])) ? trim($cols[$addr_col]) : '';
                $recv_ordno = ($ordno_col !== -1 && isset($cols[$ordno_col])) ? trim($cols[$ordno_col]) : '';

                if (empty($waybill_no) || !preg_match('/^4[0-9]{10,11}$/', $waybill_no)) continue;

                $has_ordno = preg_match('/^[0-9]{5,6}$/', $recv_ordno);
                $has_name = (mb_strlen($recv_name) >= 2);
                $phone_clean = preg_replace('/[^0-9]/', '', $recv_phone);
                $phone5 = substr($phone_clean, 0, 5);
                $has_phone = (strlen($phone5) >= 5);
                // 주소 키워드: 동/로/길 추출
                $addr_keyword = '';
                if (!empty($recv_addr)) {
                    if (preg_match('/([가-힣]+(?:동|로|길))\b/u', $recv_addr, $addr_m)) {
                        $addr_keyword = $addr_m[1];
                    }
                }
                $has_addr = (mb_strlen($addr_keyword) >= 2);

                if (!$has_ordno && !$has_name && !$has_phone && !$has_addr) { $skipped++; continue; }

                // 1순위: 주문번호
                $matched = null;
                $method = '';
                if ($has_ordno) {
                    $ordno_int = intval($recv_ordno);
                    mysqli_stmt_bind_param($match_by_ordno_stmt, "i", $ordno_int);
                    mysqli_stmt_execute($match_by_ordno_stmt);
                    $match_result = mysqli_stmt_get_result($match_by_ordno_stmt);
                    $matched = mysqli_fetch_assoc($match_result);
                    if ($matched) $method = '주문번호';
                }

                // 2순위: 이름
                if (!$matched && $has_name) {
                    mysqli_stmt_bind_param($match_by_name_stmt, "s", $recv_name);
                    mysqli_stmt_execute($match_by_name_stmt);
                    $match_result = mysqli_stmt_get_result($match_by_name_stmt);
                    $matched = mysqli_fetch_assoc($match_result);
                    if ($matched) $method = '이름';
                }

                // 3순위: 전화번호 앞5자리
                if (!$matched && $has_phone) {
                    mysqli_stmt_bind_param($match_by_phone_stmt, "ss", $phone5, $phone5);
                    mysqli_stmt_execute($match_by_phone_stmt);
                    $match_result = mysqli_stmt_get_result($match_by_phone_stmt);
                    $matched = mysqli_fetch_assoc($match_result);
                    if ($matched) $method = '전화';
                }

                // 4순위: 주소 (동/로/길)
                if (!$matched && $has_addr) {
                    mysqli_stmt_bind_param($match_by_addr_stmt, "s", $addr_keyword);
                    mysqli_stmt_execute($match_by_addr_stmt);
                    $match_result = mysqli_stmt_get_result($match_by_addr_stmt);
                    $matched = mysqli_fetch_assoc($match_result);
                    if ($matched) $method = '주소';
                }

                if ($matched) {
                    $order_no = $matched['no'];
                    mysqli_stmt_bind_param($update_stmt, "si", $waybill_no, $order_no);
                    if (mysqli_stmt_execute($update_stmt) && mysqli_stmt_affected_rows($update_stmt) > 0) {
                        $updated++;
                        $match_methods[$method] = ($match_methods[$method] ?? 0) + 1;
                    } else {
                        $failed++;
                        if (count($errors) < 10) $errors[] = "{$recv_name}({$recv_phone}) → 주문#{$order_no}: 업데이트 실패";
                    }
                } else {
                    $failed++;
                    if (count($errors) < 10) $errors[] = "{$recv_name} / {$recv_phone} / " . mb_substr($recv_addr, 0, 20) . ": 매칭 없음";
                }
            }

            mysqli_stmt_close($match_by_ordno_stmt);
            mysqli_stmt_close($match_by_name_stmt);
            mysqli_stmt_close($match_by_phone_stmt);
            mysqli_stmt_close($match_by_addr_stmt);
            mysqli_stmt_close($update_stmt);

            $method_info = [];
            foreach ($match_methods as $m => $cnt) { $method_info[] = "{$m}:{$cnt}건"; }

            $detect_info = "감지: 운송장=컬럼" . ($waybill_col+1);
            if ($ordno_col !== -1) $detect_info .= " / 주문번호=컬럼" . ($ordno_col+1);
            if ($name_col !== -1) $detect_info .= " / 이름=컬럼" . ($name_col+1);
            if ($phone_col !== -1) $detect_info .= " / 전화=컬럼" . ($phone_col+1);
            if ($addr_col !== -1) $detect_info .= " / 주소=컬럼" . ($addr_col+1);

            $message = "✅ 운송장 등록 완료: {$updated}건 성공, {$failed}건 실패";
            if ($skipped > 0) $message .= ", {$skipped}건 스킵";
            if (!empty($method_info)) $message .= "<br>매칭방법: " . implode(", ", $method_info);
            $message .= "<br><small style='color:#666;'>{$detect_info}</small>";
            if (count($errors) > 0) {
                $message .= "<br><small style='color:#d97706;'>" . implode("<br>", $errors) . "</small>";
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
WHERE date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
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

        /* <?php echo in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) ? '3' : '2'; ?>열 레이아웃 */
        .form-grid { display: grid; grid-template-columns: <?php echo in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) ? '1fr 1fr 1fr' : '1fr 1fr'; ?>; gap: 10px; margin-bottom: 10px; }

        /* 로젠 자동등록 터미널 */
        .logen-terminal {
            background: #1a1a2e; color: #0f0; font-family: 'Consolas', 'Courier New', monospace;
            font-size: 11px; padding: 8px; height: 180px; overflow-y: auto; border: 1px solid #333;
            white-space: pre-wrap; word-break: break-all; line-height: 1.4;
        }
        .logen-terminal::-webkit-scrollbar { width: 6px; }
        .logen-terminal::-webkit-scrollbar-thumb { background: #444; }
        .logen-status { display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: bold; border: 1px solid; }
        .logen-status.idle { background: #f0f0f0; color: #666; border-color: #ccc; }
        .logen-status.running { background: #fff3cd; color: #856404; border-color: #ffc107; animation: pulse 1.5s infinite; }
        .logen-status.done { background: #d4edda; color: #155724; border-color: #28a745; }
        .logen-status.error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }

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
        <a href="https://logis.ilogen.com/" target="_blank" class="btn btn-logen" style="margin-left: auto; font-size: 12px;">
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
                <a href="https://logis.ilogen.com/" target="_blank">🔑 로그인</a>
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

        <?php if (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])): ?>
        <!-- 로젠 자동등록 (로컬 전용) -->
        <div class="card">
            <h2>🤖 로젠 자동등록 <span id="logenStatus" class="logen-status idle">대기</span></h2>
            <p style="color: #666; margin-bottom: 8px; font-size: 11px;">
                엑셀 내보내기 → 로젠 업로드 → 운송장 발행 → DB 등록을 <strong>자동으로</strong> 처리합니다.
            </p>
            <div class="form-row" style="margin-bottom: 8px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>시작일</label>
                    <input type="date" id="logenDateFrom" value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>종료일</label>
                    <input type="date" id="logenDateTo" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <button type="button" class="btn btn-primary" style="flex:1;" id="btnLogenRun" onclick="runLogenAuto()">▶ 실행</button>
                <button type="button" class="btn btn-logen" style="flex:1;" id="btnLogenKill" onclick="killLogenAuto()" disabled>■ 중지</button>
            </div>
            <div id="logenTerminal" class="logen-terminal">로젠 자동등록 대기 중...\n버튼을 눌러 시작하세요.</div>
        </div>
        <?php endif; ?>
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

<?php if (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])): ?>
// ── 로젠 자동등록 (로컬 전용) ──────────────────────────────────
var logenPollingTimer = null;

function setLogenState(state) {
    var statusEl = document.getElementById('logenStatus');
    var btnRun = document.getElementById('btnLogenRun');
    var btnKill = document.getElementById('btnLogenKill');

    statusEl.className = 'logen-status ' + state;
    if (state === 'running') {
        statusEl.textContent = '실행 중...';
        btnRun.disabled = true;
        btnKill.disabled = false;
    } else if (state === 'done') {
        statusEl.textContent = '완료';
        btnRun.disabled = false;
        btnKill.disabled = true;
    } else if (state === 'error') {
        statusEl.textContent = '오류';
        btnRun.disabled = false;
        btnKill.disabled = true;
    } else {
        statusEl.textContent = '대기';
        btnRun.disabled = false;
        btnKill.disabled = true;
    }
}

function appendLog(text) {
    var term = document.getElementById('logenTerminal');
    term.textContent = text;
    term.scrollTop = term.scrollHeight;
}

function runLogenAuto() {
    var dateFrom = document.getElementById('logenDateFrom').value;
    var dateTo = document.getElementById('logenDateTo').value;

    if (!dateFrom || !dateTo) {
        alert('날짜를 입력해주세요.');
        return;
    }

    setLogenState('running');
    appendLog('로젠 자동등록 시작 중...\n날짜: ' + dateFrom + ' ~ ' + dateTo + '\n');

    var formData = new FormData();
    formData.append('action', 'run');
    formData.append('date_from', dateFrom);
    formData.append('date_to', dateTo);

    fetch('/tools/logen/logen_runner.php?action=run', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            setLogenState('error');
            appendLog('오류: ' + data.error);
            return;
        }
        appendLog('프로세스 시작됨 (PID: ' + data.pid + ')\n로그 수집 중...\n');
        startPolling();
    })
    .catch(function(err) {
        setLogenState('error');
        appendLog('요청 실패: ' + err.message);
    });
}

function startPolling() {
    if (logenPollingTimer) clearInterval(logenPollingTimer);
    logenPollingTimer = setInterval(pollStatus, 2000);
    // 즉시 1회 실행
    pollStatus();
}

function pollStatus() {
    fetch('/tools/logen/logen_runner.php?action=status')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.log) {
            appendLog(data.log);
        }
        if (!data.running) {
            clearInterval(logenPollingTimer);
            logenPollingTimer = null;
            // 로그에서 완료/오류 판단
            var log = data.log || '';
            if (log.indexOf('완료') !== -1 || log.indexOf('SUCCESS') !== -1 || log.indexOf('import_waybill') !== -1) {
                setLogenState('done');
            } else if (log.indexOf('ERROR') !== -1 || log.indexOf('에러') !== -1 || log.indexOf('실패') !== -1) {
                setLogenState('error');
            } else if (data.totalLines > 3) {
                setLogenState('done');
            } else {
                setLogenState('idle');
            }
        }
    })
    .catch(function() {
        // 네트워크 오류 시 폴링 계속
    });
}

function killLogenAuto() {
    if (!confirm('실행 중인 프로세스를 중지하시겠습니까?')) return;

    var formData = new FormData();
    formData.append('action', 'kill');

    fetch('/tools/logen/logen_runner.php?action=kill', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (logenPollingTimer) {
            clearInterval(logenPollingTimer);
            logenPollingTimer = null;
        }
        setLogenState('idle');
        appendLog((document.getElementById('logenTerminal').textContent || '') + '\n\n── 사용자에 의해 중지됨 ──');
    })
    .catch(function(err) {
        alert('중지 요청 실패: ' + err.message);
    });
}

// 페이지 로드 시 이미 실행 중인지 확인
(function() {
    fetch('/tools/logen/logen_runner.php?action=status')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.running) {
            setLogenState('running');
            if (data.log) appendLog(data.log);
            startPolling();
        }
    })
    .catch(function() {});
})();
<?php endif; ?>
</script>
</body>
</html>
