<?php
ini_set('display_errors', '0');

// 절대 경로로 설정 (admin.php에서 include할 때도 정상 작동)
$HomeDir = $_SERVER['DOCUMENT_ROOT'];
$PageCode = "PrintAuto";

// 이미 db.php가 include되어 $db가 설정되어 있으면 건너뛰기
if (!isset($db) || !$db) {
    include "$HomeDir/db.php";
}

// ProductSpecFormatter도 한 번만 include
if (!class_exists('ProductSpecFormatter')) {
    include "$HomeDir/includes/ProductSpecFormatter.php";
}
// include $_SERVER['DOCUMENT_ROOT'] . "/mlangprintauto/mlangprintautotop.php";

// 데이터베이스 연결은 이미 db.php에서 완료됨
// $db 변수가 이미 설정되어 있음
if (!$db) {
    die("Connection failed: Database connection not established");
}
$db->set_charset("utf8");

// ✅ admin.php에서 $order_rows 배열이 전달되었는지 확인
if (isset($order_rows) && is_array($order_rows) && count($order_rows) > 0) {
    // 다중 주문 처리 (장바구니 그룹)
    $row = $order_rows[0]; // 첫 번째 주문에서 고객 정보 사용
    $is_group_order = count($order_rows) > 1; // 2개 이상이면 그룹 주문
} else {
    // 단일 주문 처리 (기존 방식 유지)
    $no = isset($_REQUEST['no']) ? intval($_REQUEST['no']) : 0;

    if ($no > 0) {
        $stmt = $db->prepare("SELECT * FROM mlangorder_printauto WHERE no = ?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $order_rows = [$row]; // 배열로 변환
            $is_group_order = false;
        } else {
            echo ("<script>
                alert('Database error.');
                window.self.close();
            </script>");
            exit;
        }
        $stmt->close();
    } else {
        echo ("<script>
            alert('No order number provided.');
            window.self.close();
        </script>");
        exit;
    }
}

// ✅ 공통 고객 정보 설정 (첫 번째 주문 기준)
$no = $row['no']; // 화면 표시 조건용
$View_No = htmlspecialchars($row['no']);
$View_Type = htmlspecialchars($row['Type']);
$View_ImgFolder = htmlspecialchars($row['ImgFolder']);
$View_Type_1 = $row['Type_1']; // JSON 데이터는 htmlspecialchars 적용하지 않음

// name이 '0' 또는 비어있으면 email 앞부분 사용
$View_name = $row['name'];
if (empty($View_name) || $View_name === '0') {
    if (!empty($row['email'])) {
        $View_name = explode('@', $row['email'])[0];
    } else {
        $View_name = '미입력';
    }
}
$View_name = htmlspecialchars($View_name);
$View_email = htmlspecialchars($row['email']);
$View_zip = htmlspecialchars($row['zip']);
$View_zip1 = htmlspecialchars($row['zip1']);
$View_zip2 = htmlspecialchars($row['zip2']);
$View_phone = htmlspecialchars($row['phone']);
$View_Hendphone = htmlspecialchars($row['Hendphone']);
$View_delivery = htmlspecialchars($row['delivery']);
$View_bizname = htmlspecialchars($row['bizname']);
$View_bank = htmlspecialchars($row['bank']);
$View_bankname = htmlspecialchars($row['bankname']);
$View_cont = htmlspecialchars($row['cont']);
$View_date = htmlspecialchars($row['date']);
$View_OrderStyle = htmlspecialchars($row['OrderStyle']);
$View_ThingCate = htmlspecialchars($row['ThingCate']);
$View_Gensu = htmlspecialchars($row['Gensu']);

// ✅ 가격 정보 계산 (그룹 주문 시 합산)
$View_money_1 = 0;
$View_money_2 = 0;
$View_money_3 = 0;
$View_money_4 = 0;
$View_money_5 = 0;

// 모든 주문의 가격을 합산
foreach ($order_rows as $order_item) {
    $View_money_1 += intval($order_item['money_1'] ?? 0);
    $View_money_2 += intval($order_item['money_2'] ?? 0);

    // ✅ 부가세 계산: money_3가 0이면 money_5에서 역산 (레거시 데이터 처리)
    $item_vat = intval($order_item['money_3'] ?? 0);
    if ($item_vat == 0 && $order_item['money_5'] > 0) {
        // money_3가 저장되지 않은 경우, money_5에서 VAT 추출
        $supply_price = intval($order_item['money_4'] ?? 0) + intval($order_item['money_2'] ?? 0);
        $item_vat = intval($order_item['money_5']) - $supply_price;
    }
    $View_money_3 += $item_vat;

    $View_money_4 += intval($order_item['money_4'] ?? 0);
    $View_money_5 += intval($order_item['money_5'] ?? 0);
}

// ✅ ProductSpecFormatter 초기화
$specFormatter = new ProductSpecFormatter($db);

/**
 * 주문 항목에서 규격, 수량, 단위 정보 추출
 * ProductSpecFormatter 사용으로 중복 코드 제거
 */
function getOrderItemInfo($summary_item, $specFormatter) {
    $full_spec = '';
    $quantity_num = '';
    $unit = '';
    $item_type_display = htmlspecialchars($summary_item['Type']); // 기본값
    $is_flyer = false;
    $mesu_for_display = 0;
    $json_data = null;

    // 🆕 DB의 unit 필드 우선 사용
    $db_unit = $summary_item['unit'] ?? '';
    if (!empty($db_unit) && $db_unit !== '개') {
        $unit = $db_unit;
    }

    if (!empty($summary_item['Type_1'])) {
        $type_1_data = trim($summary_item['Type_1']);
        $json_data = json_decode($type_1_data, true);

        if ($json_data && is_array($json_data)) {
            // ✅ product_type으로 품목명 변환
            $product_type = $json_data['product_type'] ?? '';
            if ($product_type) {
                $item_type_display = $specFormatter->getProductTypeName($product_type);
            }

            // ✅ ProductSpecFormatter로 규격 문자열 생성 (한 줄 형식)
            $itemData = array_merge($summary_item, $json_data);
            $itemData['product_type'] = $product_type;
            $full_spec = $specFormatter->formatSingleLine($itemData);

            // 🔧 수량/단위 추출 로직
            $item_type_str = $summary_item['Type'] ?? '';
            $is_flyer = ($product_type === 'inserted' || $product_type === 'leaflet' ||
                         strpos($item_type_str, '전단지') !== false ||
                         strpos($item_type_str, '리플렛') !== false);

            // 전단지/리플렛: 연 단위
            $flyer_quantity = $json_data['quantity'] ?? $json_data['MY_amount'] ?? null;
            if ($is_flyer && $flyer_quantity !== null && floatval($flyer_quantity) > 0) {
                $quantity_num = floatval($flyer_quantity);
                $unit = '연';
            } elseif ($is_flyer) {
                $quantity_num = floatval($json_data['quantityTwo'] ?? $json_data['quantity'] ?? $json_data['MY_amount'] ?? 1);
                $unit = '연';
            } elseif (isset($json_data['quantityTwo']) && $json_data['quantityTwo'] > 0) {
                $quantity_num = intval($json_data['quantityTwo']);
                $unit = '매';
            } elseif ((isset($json_data['MY_amount']) && is_numeric($json_data['MY_amount']) && floatval($json_data['MY_amount']) > 0)) {
                $quantity_num = floatval($json_data['MY_amount']);
                // 제품 타입별 기본 단위
                if ($product_type === 'cadarok') {
                    $unit = '부';
                } elseif ($product_type === 'ncrflambeau') {
                    $unit = '권';
                } else {
                    $unit = '매';
                }
            }

            // 전단지 매수 정보
            if ($is_flyer) {
                $mesu_for_display = intval($json_data['quantityTwo'] ?? $json_data['mesu'] ?? 0);
                if ($mesu_for_display == 0 && isset($summary_item['mesu']) && $summary_item['mesu'] > 0) {
                    $mesu_for_display = intval($summary_item['mesu']);
                }
            }
        } else {
            // 레거시 텍스트 처리 (2줄 슬래시 형식 적용 - duson-print-rules 준수)
            $raw_spec = strip_tags($type_1_data);
            $raw_spec = str_replace(["\r\n", "\n", "\r"], '|', $raw_spec);
            $raw_spec = preg_replace('/\s+/', ' ', $raw_spec);
            $raw_spec = trim($raw_spec, ' |');

            // 파이프로 분리
            $parts = explode('|', $raw_spec);
            $clean_parts = [];

            foreach ($parts as $part) {
                $part = trim($part);
                if (empty($part)) continue;

                // 라벨 제거 (크기:, 매수:, 규격:, 용지:, 인쇄면:, 디자인: 등)
                $part = preg_replace('/^(크기|매수|규격|용지|인쇄면|인쇄|디자인|종류|수량|모양|재질|도무송)\s*[:：]\s*/u', '', $part);

                // 숫자 + 단위 형식일 경우 포맷팅 (10000 매 → 10,000매)
                if (preg_match('/^(\d+)\s*(매|개|장|부|연|권|EA)$/u', $part, $matches)) {
                    $quantity_num = intval($matches[1]);
                    $unit = $matches[2];
                    $part = number_format($quantity_num) . $unit;
                }

                if (!empty($part)) {
                    $clean_parts[] = $part;
                }
            }

            // 2줄 슬래시 형식으로 조합
            // Line 1: 첫 2개 항목 (규격)
            // Line 2: 나머지 항목 (옵션)
            $line1_items = array_slice($clean_parts, 0, 2);
            $line2_items = array_slice($clean_parts, 2);

            $line1 = implode(' / ', $line1_items);
            $line2 = implode(' / ', $line2_items);

            $full_spec = $line1;
            if (!empty($line2)) {
                $full_spec .= ' | ' . $line2;  // 표시 시 |로 분리하여 2줄로 표시
            }
        }
    }

    // 사양이 없으면 기본값
    if (empty($full_spec)) {
        $full_spec = '-';
    }

    return [
        'full_spec' => $full_spec,
        'quantity_num' => $quantity_num,
        'unit' => $unit,
        'item_type_display' => $item_type_display,
        'is_flyer' => $is_flyer,
        'mesu_for_display' => $mesu_for_display,
        'json_data' => $json_data
    ];
}

// $db->close(); // 연결 유지 - admin.php에서 계속 사용
?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 상세 정보 - 두손기획인쇄</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        var NUM = "0123456789";
        var SALPHA = "abcdefghijklmnopqrstuvwxyz";
        var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

        function TypeCheck(s, spc) {
            for (var i = 0; i < s.length; i++) {
                if (spc.indexOf(s.substring(i, i + 1)) < 0) {
                    return false;
                }
            }
            return true;
        }

        function zipcheck() {
            window.open("/mlangprintauto/zip.php?mode=search", "zip", "scrollbars=yes,resizable=yes,width=550,height=510,top=10,left=50");
        }

        function JoinCheckField() {
            var f = document.JoinInfo;

            if (f.name.value.trim() == "") {
                alert("성명/상호를 입력해 주세요.");
                f.name.focus();
                return false;
            }

            if (f.email.value.trim() == "" || f.email.value.indexOf("@") == -1) {
                alert("올바른 이메일을 입력해 주세요.");
                f.email.focus();
                return false;
            }

            if (f.phone.value.trim() == "" && f.Hendphone.value.trim() == "") {
                alert("전화번호 또는 휴대폰 중 하나는 입력해 주세요.");
                f.phone.focus();
                return false;
            }

            return true;
        }

        function printOrder() {
            // PDF 파일명을 주문자명_주문번호 형식으로 설정
            const customerName = "<?= htmlspecialchars($View_name) ?>";
            const orderNumber = "<?= $View_No ?>";

            // 파일명에 사용할 수 없는 문자 제거
            const sanitizeName = (name) => {
                return name.replace(/[^\w가-힣]/g, '_');
            };

            const fileName = sanitizeName(customerName) + '_' + orderNumber + '.pdf';

            // 페이지 제목을 임시로 변경 (PDF 저장 시 파일명으로 사용됨)
            const originalTitle = document.title;
            document.title = fileName.replace('.pdf', '');

            // ✅ 관리자용 내용 높이 체크하여 레이아웃 결정
            const printOnly = document.querySelector('.print-only');
            const adminOrder = document.querySelector('.print-order:not(.employee-copy)');
            const divider = document.querySelector('.print-divider');
            const employeeOrder = document.querySelector('.print-order.employee-copy');

            if (adminOrder && divider && employeeOrder) {
                // 임시로 print-only 표시하여 높이 측정
                printOnly.style.display = 'block';
                const adminHeight = adminOrder.offsetHeight;

                // A4 용지 세로 길이의 약 45% (여백 고려) = 약 450px
                const halfPageHeight = 450;

                if (adminHeight > halfPageHeight) {
                    // 관리자 내용이 절반을 넘으면: 절취선 숨기고 2페이지 모드
                    divider.classList.add('hidden');
                    employeeOrder.classList.add('new-page');
                } else {
                    // 관리자 내용이 절반 이하면: 절취선 표시, 같은 페이지
                    divider.classList.remove('hidden');
                    employeeOrder.classList.remove('new-page');
                }

                // 다시 숨기기 (프린트 CSS에서 표시됨)
                printOnly.style.display = '';
            }

            window.print();

            // 제목 복원
            setTimeout(() => {
                document.title = originalTitle;
            }, 1000);
        }
    </script>
    <link href="/mlangprintauto/css/board.css" rel="stylesheet" type="text/css">
<!-- Order Complete Style -->
    <link rel="stylesheet" href="/css/order-complete-style.css">
    <style>
        /* 화면에서는 프린트 전용 내용 숨기기 */
        .print-only {
            display: none;
        }

        /* 절취선 스타일 */
        .print-divider {
            position: relative;
            margin: 8mm 0;
            border: none;
            border-top: 2px dashed #666;
            height: 0;
        }

        .print-divider::before {
            content: "✂ 절 취 선";
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 15px;
            font-size: 11px;
            color: #666;
            letter-spacing: 3px;
        }

        /* 절취선 숨김 (JS에서 제어) */
        .print-divider.hidden {
            display: none !important;
        }

        /* 프린트 시에만 표시 */
        @media print {
            /* 프린트 전용 내용만 표시 */
            .print-only {
                display: block !important;
            }

            .screen-only {
                display: none !important;
            }

            /* 화면 전용 요소 숨기기 */
            .admin-container,
            .file-section,
            input,
            button,
            textarea {
                display: none !important;
            }

            /* 주문서 컨테이너 */
            .print-container {
                width: 100%;
            }

            /* 각 주문서가 페이지에 맞게 자동 분리 */
            .print-order {
                page-break-inside: auto;
            }

            /* 절취선 숨김 시에도 적용 */
            .print-divider.hidden {
                display: none !important;
            }

            /* 2페이지 모드: 직원용 주문서 새 페이지에서 시작 */
            .print-order.employee-copy.new-page {
                page-break-before: always;
            }
        }
    </style>
</head>

<body>

    <!-- 프린트 전용 내용 -->
    <div class="print-only">
        <div class="print-container">
            <!-- 첫 번째 주문서 (관리자용) -->
            <div class="print-order">
                <div class="print-title">주문서 (관리자용)</div>

                <!-- 주요 정보를 크게 표시 (노인 친화적) -->
                <div style="margin-bottom: 3mm; padding: 2mm; border: 0.3pt solid #666;">
                    <div style="display: flex; gap: 3mm; align-items: center; font-size: 14pt; font-weight: bold; line-height: 1.2;">
                        <div style="flex: 1;">
                            <span style="color: #000;">주문번호: <?= $View_No ?></span>
                        </div>
                        <div style="flex: 1;">
                            <span style="color: #000;">일시: <?= htmlspecialchars($View_date) ?></span>
                        </div>
                        <div style="flex: 1;">
                            <span style="color: #000;">주문자: <?= htmlspecialchars($View_name) ?></span>
                        </div>
                        <div style="flex: 1;">
                            <span style="color: #000;">전화: <?= htmlspecialchars($View_phone) ?></span>
                        </div>
                    </div>
                </div>

                <!-- 주문 상세 -->
                <div class="print-info-section">
                    <div class="print-info-title">주문상세</div>

                    <?php if (!empty($order_rows)): ?>
                    <!-- 주문 상세 표 (단일/그룹 모두 표시) -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 3mm; font-size: 10pt;">
                        <thead>
                            <tr style="background-color: #f5f5f5; border: 0.3pt solid #000;">
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 5%;">NO</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 12%;">품 목</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 47%;">규격/옵션</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 8%;">수량</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 5%;">단위</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; width: 9%;">인쇄비</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; width: 10%;">공급가액</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row_num = 1;
                            foreach ($order_rows as $summary_item):
                                // ✅ ProductSpecFormatter로 규격/수량/단위 정보 추출 (중복 코드 제거)
                                $info = getOrderItemInfo($summary_item, $specFormatter);
                                $full_spec = $info['full_spec'];
                                $quantity_num = $info['quantity_num'];
                                $unit = $info['unit'];
                                $item_type_display = $info['item_type_display'];
                                $is_flyer = $info['is_flyer'];
                                $mesu_for_display = $info['mesu_for_display'];
                                $json_data = $info['json_data'];

                                // 🔧 Extract options for this item
                                $item_options = [];

                                // 1. Coating option
                                if (!empty($summary_item['coating_enabled']) && $summary_item['coating_enabled'] == 1) {
                                    $coating_type_kr = $summary_item['coating_type'] ?? '';
                                    if ($coating_type_kr == 'single') $coating_type_kr = '단면유광코팅';
                                    elseif ($coating_type_kr == 'double') $coating_type_kr = '양면유광코팅';
                                    elseif ($coating_type_kr == 'single_matte') $coating_type_kr = '단면무광코팅';
                                    elseif ($coating_type_kr == 'double_matte') $coating_type_kr = '양면무광코팅';
                                    $coating_price = intval($summary_item['coating_price'] ?? 0);
                                    if ($coating_price > 0) {
                                        $item_options[] = '코팅(' . $coating_type_kr . ') ' . number_format($coating_price) . '원';
                                    }
                                }

                                // 2. Folding option
                                if (!empty($summary_item['folding_enabled']) && $summary_item['folding_enabled'] == 1) {
                                    $folding_type_kr = $summary_item['folding_type'] ?? '';
                                    if ($folding_type_kr == '2fold') $folding_type_kr = '2단접지';
                                    elseif ($folding_type_kr == '3fold') $folding_type_kr = '3단접지';
                                    elseif ($folding_type_kr == 'accordion') $folding_type_kr = '아코디언접지';
                                    elseif ($folding_type_kr == 'gate') $folding_type_kr = '게이트접지';
                                    $folding_price = intval($summary_item['folding_price'] ?? 0);
                                    if ($folding_price > 0) {
                                        $item_options[] = '접지(' . $folding_type_kr . ') ' . number_format($folding_price) . '원';
                                    }
                                }

                                // 3. Creasing option
                                if (!empty($summary_item['creasing_enabled']) && $summary_item['creasing_enabled'] == 1) {
                                    $creasing_lines = intval($summary_item['creasing_lines'] ?? 0);
                                    $creasing_price = intval($summary_item['creasing_price'] ?? 0);
                                    if ($creasing_price > 0) {
                                        $item_options[] = '오시(' . $creasing_lines . '줄) ' . number_format($creasing_price) . '원';
                                    }
                                }

                                // 4. Envelope tape option
                                if (!empty($summary_item['envelope_tape_enabled']) && $summary_item['envelope_tape_enabled'] == 1) {
                                    $tape_quantity = intval($summary_item['envelope_tape_quantity'] ?? 0);
                                    $tape_price = intval($summary_item['envelope_tape_price'] ?? 0);
                                    if ($tape_price > 0) {
                                        $item_options[] = '양면테이프(' . number_format($tape_quantity) . '개) ' . number_format($tape_price) . '원';
                                    }
                                }

                                // 5. Premium options (business cards, NCR forms, merchandise bonds)
                                if (!empty($summary_item['premium_options'])) {
                                    $premium_opts = json_decode($summary_item['premium_options'], true);
                                    if ($premium_opts && is_array($premium_opts)) {
                                        // NCRFlambeau (양식지) processing
                                        if (isset($premium_opts['creasing_lines'])) {
                                            if (!empty($premium_opts['creasing_enabled'])) {
                                                $creasing_lines = $premium_opts['creasing_lines'] ?? '';
                                                $creasing_price = intval($premium_opts['creasing_price'] ?? 0);
                                                if (!empty($creasing_lines) && $creasing_price > 0) {
                                                    $item_options[] = '미싱 ' . $creasing_lines . '줄 ' . number_format($creasing_price) . '원';
                                                }
                                            }

                                            if (!empty($premium_opts['folding_enabled'])) {
                                                $folding_type = $premium_opts['folding_type'] ?? '';
                                                $folding_price = intval($premium_opts['folding_price'] ?? 0);
                                                if ($folding_type === 'numbering' && $folding_price > 0) {
                                                    $item_options[] = '넘버링 ' . number_format($folding_price) . '원';
                                                }
                                            }
                                        } else {
                                            // Business cards/merchandise bond premium options
                                            $opt_config = [
                                                'foil' => ['name' => '박', 'types' => [
                                                    'gold_matte' => '금박무광',
                                                    'gold_gloss' => '금박유광',
                                                    'silver_matte' => '은박무광',
                                                    'silver_gloss' => '은박유광',
                                                    'blue_gloss' => '청박유광',
                                                    'red_gloss' => '적박유광',
                                                    'green_gloss' => '녹박유광',
                                                    'black_gloss' => '먹박유광'
                                                ]],
                                                'numbering' => ['name' => '넘버링', 'types' => ['single' => '1개', 'double' => '2개']],
                                                'perforation' => ['name' => '미싱', 'types' => ['horizontal' => '가로미싱', 'vertical' => '세로미싱', 'cross' => '십자미싱']],
                                                'rounding' => ['name' => '귀돌이', 'types' => ['4corners' => '네귀돌이', '2corners' => '두귀돌이']],
                                                'creasing' => ['name' => '오시', 'types' => ['single_crease' => '1줄오시', 'double_crease' => '2줄오시']]
                                            ];

                                            foreach ($opt_config as $key => $config) {
                                                if (!empty($premium_opts[$key . '_enabled']) && $premium_opts[$key . '_enabled'] == 1) {
                                                    $price = intval($premium_opts[$key . '_price'] ?? 0);
                                                    if ($price > 0) {
                                                        $opt_type = $premium_opts[$key . '_type'] ?? '';
                                                        $type_name = '';
                                                        if (!empty($opt_type) && isset($config['types'][$opt_type])) {
                                                            $type_name = '(' . $config['types'][$opt_type] . ')';
                                                        }
                                                        $item_options[] = $config['name'] . $type_name . ' ' . number_format($price) . '원';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;"><?= $row_num++ ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm;"><?= $item_type_display ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; font-size: 10pt; line-height: 1.4; vertical-align: top;">
                                    <?php
                                    // 🔧 규격/옵션 2줄+2줄 형식으로 표시 (duson-print-rules 준수)
                                    $spec_parts = array_map('trim', explode('|', $full_spec));
                                    $spec_parts = array_filter($spec_parts, function($p) { return !empty($p); });
                                    $spec_parts = array_values($spec_parts);

                                    // 규격 (최대 2줄)
                                    for ($i = 0; $i < min(2, count($spec_parts)); $i++):
                                    ?>
                                        <div style="color: #4a5568; margin-bottom: 1px;"><?= htmlspecialchars($spec_parts[$i]) ?></div>
                                    <?php endfor; ?>

                                    <?php
                                    // 옵션 (나머지 최대 2줄)
                                    for ($i = 2; $i < min(4, count($spec_parts)); $i++):
                                    ?>
                                        <div style="color: #667eea; margin-bottom: 1px;"><?= htmlspecialchars($spec_parts[$i]) ?></div>
                                    <?php endfor; ?>

                                    <?php if (!empty($item_options)): ?>
                                        <div style="color: #e65100; font-size: 9pt; margin-top: 2px;"><?= implode(' / ', $item_options) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">
                                    <?php
                                    // 🔧 전단지/리플렛: "X연 (Y매)" 형식으로 표시
                                    if (isset($is_flyer) && $is_flyer && $mesu_for_display > 0) {
                                        $yeon_display = $quantity_num ? (floor($quantity_num) == $quantity_num ? number_format($quantity_num) : number_format($quantity_num, 1)) : '0';
                                        echo $yeon_display . '연 (' . number_format($mesu_for_display) . '매)';
                                    } else {
                                        echo $quantity_num ? (floor($quantity_num) == $quantity_num ? number_format($quantity_num) : number_format($quantity_num, 1)) : '-';
                                    }
                                    ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">
                                    <?php
                                    // 🔧 전단지/리플렛: 단위 칼럼 비우기
                                    if (isset($is_flyer) && $is_flyer && $mesu_for_display > 0) {
                                        echo '-';
                                    } else {
                                        echo htmlspecialchars($unit);
                                    }
                                    ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;">
                                    <?= number_format(intval($summary_item['money_4'])) ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; font-weight: bold;">
                                    <?= number_format(intval($summary_item['money_4'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <!-- 합계 행 -->
                            <tr style="background-color: #f9f9f9; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">공급가액</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;"><?= number_format(round($View_money_4, -1)) ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;"><?= number_format(round($View_money_4, -1)) ?></td>
                            </tr>
                            <!-- 부가세포함금액 행 추가 (10원 단위 반올림) -->
                            <tr style="background-color: #e9ecef; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; color: #000;">💰 부가세포함</td>
                                <td colspan="2" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; color: #000; font-size: 12pt;"><?= number_format(round($View_money_5, -1)) ?> 원</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <!-- 🔧 가격 정보 표시 제거됨 - 테이블의 "총 합계" 행에서 이미 표시됨 -->
                </div>

                <!-- 고객 정보 -->
                <div class="print-info-section">
                    <div class="print-info-title">고객정보</div>
                    <table class="print-table">
                        <tr>
                            <th>성명</th>
                            <td><?= htmlspecialchars($View_name) ?></td>
                            <th>전화</th>
                            <td><?= htmlspecialchars($View_phone) ?></td>
                        </tr>
                        <tr>
                            <th>주소</th>
                            <td colspan="3">[<?= $View_zip ?>] <?= htmlspecialchars($View_zip1) ?> <?= htmlspecialchars($View_zip2) ?></td>
                        </tr>
                        <?php if (!empty($View_bizname)) { ?>
                            <tr>
                                <th>업체명</th>
                                <td><?= htmlspecialchars($View_bizname) ?></td>
                                <th>입금</th>
                                <td><?= htmlspecialchars($View_bank) ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>

                <!-- 기타 사항 및 사업자 정보 -->
                <?php if (!empty($View_cont) && trim($View_cont) != '') { ?>
                    <div class="print-info-section">
                        <div class="print-info-title">기타사항</div>
                        <div style="padding: 2mm; border: 0.3pt solid #666; min-height: 10mm; font-size: 8pt; line-height: 1.2;">
                            <?php echo nl2br(htmlspecialchars($View_cont)); ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="print-footer">두손기획인쇄 02-2632-1830</div>
            </div>

            <!-- 절취선 -->
            <div class="print-divider"></div>

            <!-- 두 번째 주문서 (직원용) -->
            <div class="print-order employee-copy">
                <div class="print-title">주문서 (직원용)</div>

                <!-- 주요 정보를 크게 표시 -->
                <div style="margin-bottom: 3mm; padding: 2mm; border: 0.3pt solid #666;">
                    <div style="display: flex; gap: 3mm; align-items: center; font-size: 12pt; font-weight: bold; line-height: 1.2;">
                        <div style="flex: 1;">
                            <span style="color: #000;">주문번호: <?= $View_No ?></span>
                        </div>
                        <div style="flex: 1;">
                            <span style="color: #000;">일시: <?= htmlspecialchars($View_date) ?></span>
                        </div>
                        <div style="flex: 1;">
                            <span style="color: #000;">주문자: <?= htmlspecialchars($View_name) ?></span>
                        </div>
                        <div style="flex: 1;">
                            <span style="color: #000;">전화: <?= htmlspecialchars($View_phone) ?></span>
                        </div>
                    </div>
                </div>

                <!-- 주문 상세 -->
                <div class="print-info-section">
                    <div class="print-info-title">주문상세</div>

                    <?php if (!empty($order_rows)): ?>
                    <!-- 주문 상세 표 (단일/그룹 모두 표시) -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 3mm; font-size: 10pt;">
                        <thead>
                            <tr style="background-color: #f5f5f5; border: 0.3pt solid #000;">
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 5%;">NO</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 12%;">품 목</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 47%;">규격/옵션</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 8%;">수량</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 5%;">단위</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; width: 9%;">인쇄비</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; width: 10%;">공급가액</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row_num = 1;
                            foreach ($order_rows as $summary_item):
                                // Type_1에서 전체 사양, 수량, 단위 정보 추출
                                $full_spec = '';
                                $quantity_num = '';
                                $unit = '';
                                $item_type_display = htmlspecialchars($summary_item['Type']); // 기본값

                                // 🆕 DB의 unit 필드 우선 사용 (shop_temp에서 복사된 값)
                                $db_unit = $summary_item['unit'] ?? '';
                                if (!empty($db_unit) && $db_unit !== '개') {
                                    $unit = $db_unit;
                                }

                                if (!empty($summary_item['Type_1'])) {
                                    $type_1_data = trim($summary_item['Type_1']);

                                    // 🔧 JSON 파싱 시도
                                    $json_data = json_decode($type_1_data, true);

                                    // ✅ product_type으로 품목명 변환
                                    if ($json_data && isset($json_data['product_type'])) {
                                        $product_type = $json_data['product_type'];
                                        if ($product_type === 'littleprint' || $product_type === 'poster') {
                                            $item_type_display = '포스터';
                                        } elseif ($product_type === 'namecard') {
                                            $item_type_display = '명함';
                                        } elseif ($product_type === 'inserted') {
                                            $item_type_display = '전단지';
                                        } elseif ($product_type === 'envelope') {
                                            $item_type_display = '봉투';
                                        } elseif ($product_type === 'sticker') {
                                            $item_type_display = '스티커';
                                        } elseif ($product_type === 'msticker') {
                                            $item_type_display = '자석스티커';
                                        } elseif ($product_type === 'cadarok') {
                                            $item_type_display = '카다록';
                                        } elseif ($product_type === 'leaflet') {
                                            $item_type_display = '리플렛';
                                        } elseif ($product_type === 'ncrflambeau') {
                                            $item_type_display = 'NCR양식';
                                        } elseif ($product_type === 'merchandisebond') {
                                            $item_type_display = '상품권';
                                        }
                                    }
                                    if ($json_data && isset($json_data['formatted_display'])) {
                                        // JSON의 formatted_display 사용
                                        $full_spec = $json_data['formatted_display'];
                                        // 줄바꿈을 | 구분자로 변경하여 한 줄로 표시
                                        $full_spec = str_replace(["\r\n", "\n", "\r"], ' | ', $full_spec);
                                        $full_spec = trim($full_spec);

                                        // 🔧 JSON에서 수량/단위 직접 추출 (우선순위)
                                        // 🔧 전단지(inserted/leaflet)는 무조건 연 단위로 표시
                                        $product_type = $json_data['product_type'] ?? '';
                                        $item_type_str = $summary_item['Type'] ?? '';
                                        // JSON의 product_type 또는 DB의 Type 필드에서 전단지/리플렛 감지
                                        $is_flyer = ($product_type === 'inserted' || $product_type === 'leaflet' ||
                                                     strpos($item_type_str, '전단지') !== false ||
                                                     strpos($item_type_str, '리플렛') !== false);

                                        // 전단지/리플렛: quantity 또는 MY_amount 필드에서 연수 추출
                                        $flyer_quantity = $json_data['quantity'] ?? $json_data['MY_amount'] ?? null;
                                        if ($is_flyer && $flyer_quantity !== null && floatval($flyer_quantity) > 0) {
                                            // 전단지: quantity 또는 MY_amount는 연수, 단위는 무조건 "연"
                                            $quantity_num = floatval($flyer_quantity);
                                            $unit = '연';
                                        } elseif ($is_flyer) {
                                            // 전단지인데 quantity/MY_amount가 없는 경우에도 연 단위 강제
                                            $quantity_num = floatval($json_data['quantityTwo'] ?? $json_data['quantity'] ?? $json_data['MY_amount'] ?? 1);
                                            $unit = '연';
                                        } elseif (isset($json_data['quantityTwo']) && $json_data['quantityTwo'] > 0) {
                                            // 다른 제품: 매수(quantityTwo)가 있으면 사용
                                            $quantity_num = intval($json_data['quantityTwo']);
                                            $unit = '매';
                                        } elseif ((isset($json_data['quantity']) && is_numeric($json_data['quantity']) && floatval($json_data['quantity']) > 0) ||
                                                  (isset($json_data['MY_amount']) && is_numeric($json_data['MY_amount']) && floatval($json_data['MY_amount']) > 0)) {
                                            // quantity 또는 MY_amount만 있으면 formatted_display에서 단위 추출 시도
                                            $quantity_num = floatval($json_data['quantity'] ?? $json_data['MY_amount']);
                                            // formatted_display에서 단위 추출: "수량: 500개" 또는 "수량: 1,000매" (소수점 포함)
                                            if (preg_match('/수량[:\s]*([\d,.]+)\s*([가-힣a-zA-Z]+)/u', $full_spec, $unit_matches)) {
                                                $unit = trim($unit_matches[2]);
                                            } else {
                                                // 🔧 제품 타입별 기본 단위 설정 (과거 주문 호환)
                                                if ($product_type === 'cadarok') {
                                                    $unit = '부';
                                                } elseif (strpos($item_type_str, '카다록') !== false || strpos($item_type_str, '카탈로그') !== false) {
                                                    $unit = '부';
                                                } else {
                                                    // 대부분의 제품: 명함/봉투/스티커/포스터/상품권/양식지 = '매'
                                                    // 전단지/리플렛은 위에서 '연'으로 이미 처리됨
                                                    $unit = '매';
                                                }
                                            }
                                        }
                                    } elseif ($json_data && isset($json_data['product_type']) &&
                                              ($json_data['product_type'] === 'poster' || $json_data['product_type'] === 'littleprint')) {
                                        // ✅ raw JSON 포스터 처리
                                        $spec_parts = [];

                                        // 구분
                                        if (!empty($json_data['MY_type'])) {
                                            $spec_parts[] = '구분: ' . htmlspecialchars($json_data['MY_type']);
                                        }

                                        // 용지
                                        if (!empty($json_data['Section'])) {
                                            $spec_parts[] = '용지: ' . htmlspecialchars($json_data['Section']);
                                        }

                                        // 규격
                                        if (!empty($json_data['PN_type'])) {
                                            $spec_parts[] = '규격: ' . htmlspecialchars($json_data['PN_type']);
                                        }

                                        // 인쇄면
                                        if (!empty($json_data['POtype'])) {
                                            $sides = ($json_data['POtype'] == '1') ? '단면' : '양면';
                                            $spec_parts[] = '인쇄면: ' . $sides;
                                        }

                                        // 디자인
                                        if (!empty($json_data['ordertype'])) {
                                            $design = ($json_data['ordertype'] == 'total') ? '디자인+인쇄' : '인쇄만';
                                            $spec_parts[] = '디자인: ' . $design;
                                        }

                                        $full_spec = implode(' | ', $spec_parts);

                                        // 수량
                                        if (!empty($json_data['MY_amount'])) {
                                            $quantity_num = floatval($json_data['MY_amount']);
                                            $unit = '매';
                                        }
                                    } elseif ($json_data && isset($json_data['product_type']) && $json_data['product_type'] === 'ncrflambeau') {
                                        // ✅ NCR양식지 raw JSON 처리
                                        $spec_parts = [];

                                        // 규격
                                        if (!empty($json_data['MY_type_name'])) {
                                            $spec_parts[] = '규격: ' . htmlspecialchars($json_data['MY_type_name']);
                                        }

                                        // 용도/서식명
                                        if (!empty($json_data['Section_name'])) {
                                            $spec_parts[] = '용도: ' . htmlspecialchars($json_data['Section_name']);
                                        }

                                        // 인쇄도수
                                        if (!empty($json_data['PN_type_name'])) {
                                            $spec_parts[] = '인쇄도수: ' . htmlspecialchars($json_data['PN_type_name']);
                                        }

                                        // 디자인
                                        if (!empty($json_data['ordertype'])) {
                                            $design = ($json_data['ordertype'] == 'total') ? '디자인+인쇄' : '인쇄만';
                                            $spec_parts[] = '디자인: ' . $design;
                                        }

                                        $full_spec = implode(' | ', $spec_parts);

                                        // 수량
                                        if (!empty($json_data['MY_amount'])) {
                                            $quantity_num = floatval($json_data['MY_amount']);
                                            $unit = '권';
                                        }
                                    } elseif ($json_data && isset($json_data['product_type']) &&
                                              ($json_data['product_type'] === 'sticker' || $json_data['product_type'] === 'msticker')) {
                                        // ✅ 스티커/자석스티커 raw JSON 처리 (관리자용 ProductSpecFormatter와 동일 포맷)
                                        $line1Parts = [];
                                        $line2Parts = [];

                                        // 1줄: 종류 / 규격 (신규: MY_type_name, Section_name / 레거시: jong, garo/sero)
                                        if (!empty($json_data['MY_type_name'])) {
                                            $line1Parts[] = htmlspecialchars($json_data['MY_type_name']);
                                        } elseif (!empty($json_data['jong'])) {
                                            $material = preg_replace('/^(jil|jsp|jka|cka)\s+/', '', $json_data['jong']);
                                            $line1Parts[] = htmlspecialchars($material);
                                        }

                                        if (!empty($json_data['Section_name'])) {
                                            $line1Parts[] = htmlspecialchars($json_data['Section_name']);
                                        } elseif (!empty($json_data['garo']) && !empty($json_data['sero'])) {
                                            $line1Parts[] = htmlspecialchars($json_data['garo']) . '×' . htmlspecialchars($json_data['sero']) . 'mm';
                                        }

                                        // 모양 (레거시: domusong)
                                        if (!empty($json_data['domusong'])) {
                                            $shape_parts = explode(' ', $json_data['domusong'], 2);
                                            $line1Parts[] = htmlspecialchars($shape_parts[1] ?? $json_data['domusong']);
                                        }

                                        // 2줄: 수량 / 디자인
                                        $qty = '';
                                        if (!empty($json_data['mesu'])) {
                                            $qty = number_format(intval($json_data['mesu'])) . '매';
                                            $quantity_num = intval($json_data['mesu']);
                                            $unit = '매';
                                        } elseif (!empty($json_data['MY_amount'])) {
                                            $qty = number_format(floatval($json_data['MY_amount'])) . '매';
                                            $quantity_num = floatval($json_data['MY_amount']);
                                            $unit = '매';
                                        }
                                        if ($qty) $line2Parts[] = $qty;

                                        if (!empty($json_data['ordertype'])) {
                                            $design = ($json_data['ordertype'] == 'total') ? '디자인+인쇄' : '인쇄만';
                                            $line2Parts[] = $design;
                                        }

                                        // 관리자용과 동일 포맷: "종류 / 규격 | 수량 / 디자인"
                                        $line1 = implode(' / ', array_filter($line1Parts));
                                        $line2 = implode(' / ', array_filter($line2Parts));
                                        $full_spec = $line1 . ($line2 ? ' | ' . $line2 : '');
                                    } elseif ($json_data && isset($json_data['product_type']) && $json_data['product_type'] === 'merchandisebond') {
                                        // ✅ 상품권 raw JSON 처리
                                        $spec_parts = [];

                                        // 용지
                                        if (!empty($json_data['Section'])) {
                                            $spec_parts[] = '용지: ' . htmlspecialchars($json_data['Section']);
                                        }

                                        // 규격
                                        if (!empty($json_data['PN_type'])) {
                                            $spec_parts[] = '규격: ' . htmlspecialchars($json_data['PN_type']);
                                        }

                                        // 인쇄면
                                        if (!empty($json_data['POtype'])) {
                                            $sides = ($json_data['POtype'] == '1') ? '단면' : '양면';
                                            $spec_parts[] = '인쇄면: ' . $sides;
                                        }

                                        // 디자인
                                        if (!empty($json_data['ordertype'])) {
                                            $design = ($json_data['ordertype'] == 'total') ? '디자인+인쇄' : '인쇄만';
                                            $spec_parts[] = '디자인: ' . $design;
                                        }

                                        $full_spec = implode(' | ', $spec_parts);

                                        // 수량
                                        if (!empty($json_data['MY_amount'])) {
                                            $quantity_num = floatval($json_data['MY_amount']);
                                            $unit = '매';
                                        }
                                    } elseif ($json_data && isset($json_data['product_type']) && $json_data['product_type'] === 'cadarok') {
                                        // ✅ 카다록 raw JSON 처리
                                        $spec_parts = [];

                                        // 종류
                                        if (!empty($json_data['MY_type_name'])) {
                                            $spec_parts[] = '종류: ' . htmlspecialchars($json_data['MY_type_name']);
                                        }

                                        // 규격
                                        if (!empty($json_data['Section_name'])) {
                                            $spec_parts[] = '규격: ' . htmlspecialchars($json_data['Section_name']);
                                        }

                                        // 인쇄면
                                        if (!empty($json_data['POtype'])) {
                                            $sides = ($json_data['POtype'] == '1') ? '단면' : '양면';
                                            $spec_parts[] = '인쇄면: ' . $sides;
                                        }

                                        // 디자인
                                        if (!empty($json_data['ordertype'])) {
                                            $design = ($json_data['ordertype'] == 'total') ? '디자인+인쇄' : '인쇄만';
                                            $spec_parts[] = '디자인: ' . $design;
                                        }

                                        $full_spec = implode(' | ', $spec_parts);

                                        // 수량
                                        if (!empty($json_data['MY_amount'])) {
                                            $quantity_num = floatval($json_data['MY_amount']);
                                            $unit = '부';
                                        }
                                    } elseif ($json_data && isset($json_data['product_type']) && $json_data['product_type'] === 'namecard') {
                                        // ✅ 명함 raw JSON 처리
                                        $spec_parts = [];

                                        // 종류
                                        if (!empty($json_data['MY_type_name'])) {
                                            $spec_parts[] = '종류: ' . htmlspecialchars($json_data['MY_type_name']);
                                        }

                                        // 용지
                                        if (!empty($json_data['Section_name'])) {
                                            $spec_parts[] = '용지: ' . htmlspecialchars($json_data['Section_name']);
                                        }

                                        // 인쇄면
                                        if (!empty($json_data['POtype'])) {
                                            $sides = ($json_data['POtype'] == '1') ? '단면' : '양면';
                                            $spec_parts[] = '인쇄면: ' . $sides;
                                        } elseif (!empty($json_data['POtype_name'])) {
                                            $spec_parts[] = '인쇄면: ' . htmlspecialchars($json_data['POtype_name']);
                                        }

                                        // 디자인
                                        if (!empty($json_data['ordertype'])) {
                                            $design = ($json_data['ordertype'] == 'total') ? '디자인+인쇄' : '인쇄만';
                                            $spec_parts[] = '디자인: ' . $design;
                                        }

                                        $full_spec = implode(' | ', $spec_parts);

                                        // 수량
                                        if (!empty($json_data['MY_amount'])) {
                                            $quantity_num = floatval($json_data['MY_amount']);
                                            $unit = '매';
                                        }
                                    } elseif ($json_data && isset($json_data['product_type']) && $json_data['product_type'] === 'envelope') {
                                        // ✅ 봉투 raw JSON 처리
                                        $spec_parts = [];

                                        // 타입
                                        if (!empty($json_data['MY_type_name'])) {
                                            $spec_parts[] = '타입: ' . htmlspecialchars($json_data['MY_type_name']);
                                        }

                                        // 용지
                                        if (!empty($json_data['Section_name'])) {
                                            $spec_parts[] = '용지: ' . htmlspecialchars($json_data['Section_name']);
                                        }

                                        // 인쇄도수
                                        if (!empty($json_data['PN_type_name'])) {
                                            $spec_parts[] = '인쇄: ' . htmlspecialchars($json_data['PN_type_name']);
                                        }

                                        // 디자인
                                        if (!empty($json_data['ordertype'])) {
                                            $design = ($json_data['ordertype'] == 'total') ? '디자인+인쇄' : '인쇄만';
                                            $spec_parts[] = '디자인: ' . $design;
                                        }

                                        $full_spec = implode(' | ', $spec_parts);

                                        // 수량
                                        if (!empty($json_data['MY_amount'])) {
                                            $quantity_num = floatval($json_data['MY_amount']);
                                            $unit = '매';
                                        }
                                    } else {
                                        // 레거시 텍스트 처리 (2줄 슬래시 형식 적용 - duson-print-rules 준수)
                                        $raw_spec = strip_tags($type_1_data);
                                        $raw_spec = str_replace(["\r\n", "\n", "\r"], '|', $raw_spec);
                                        $raw_spec = preg_replace('/\s+/', ' ', $raw_spec);
                                        $raw_spec = trim($raw_spec, ' |');

                                        // 파이프로 분리
                                        $parts = explode('|', $raw_spec);
                                        $clean_parts = [];

                                        foreach ($parts as $part) {
                                            $part = trim($part);
                                            if (empty($part)) continue;

                                            // 라벨 제거 (크기:, 매수:, 규격:, 용지:, 인쇄면:, 디자인: 등)
                                            $part = preg_replace('/^(크기|매수|규격|용지|인쇄면|인쇄|디자인|종류|수량|모양|재질|도무송)\s*[:：]\s*/u', '', $part);

                                            // 숫자 + 단위 형식일 경우 포맷팅 (10000 매 → 10,000매)
                                            if (preg_match('/^(\d+)\s*(매|개|장|부|연|권|EA)$/u', $part, $matches)) {
                                                $qty_num = intval($matches[1]);
                                                $qty_unit = $matches[2];
                                                $part = number_format($qty_num) . $qty_unit;
                                                // 수량/단위도 저장
                                                $quantity_num = $qty_num;
                                                $unit = $qty_unit;
                                            }

                                            // 순수 숫자 또는 소수점 숫자인 경우 수량으로 간주
                                            if (preg_match('/^[\d.]+$/', $part) && floatval($part) > 0) {
                                                $quantity_num = floatval($part);
                                                $unit = '연'; // 레거시 전단지는 연 단위
                                            }

                                            if (!empty($part)) {
                                                $clean_parts[] = $part;
                                            }
                                        }

                                        // 2줄 슬래시 형식으로 조합
                                        // Line 1: 첫 2개 항목 (규격)
                                        // Line 2: 나머지 항목 (옵션)
                                        $line1_items = array_slice($clean_parts, 0, 2);
                                        $line2_items = array_slice($clean_parts, 2);

                                        $line1 = implode(' / ', $line1_items);
                                        $line2 = implode(' / ', $line2_items);

                                        $full_spec = $line1;
                                        if (!empty($line2)) {
                                            $full_spec .= ' | ' . $line2;  // 표시 시 |로 분리하여 2줄로 표시
                                        }
                                    }

                                    // 🔧 formatted_display에서 수량 추출 (위에서 못 찾은 경우)
                                    if (empty($quantity_num)) {
                                        // ★ 전단지 형식: "수량: 0.5연 (2,000매)" → 매수(2000)와 단위(매) 추출
                                        if (preg_match('/수량[:\s]*[\d.]+연\s*\(([\d,]+)매\)/u', $full_spec, $matches)) {
                                            // 전단지: 괄호 안의 매수를 사용
                                            $quantity_num = str_replace(',', '', $matches[1]);
                                            $unit = '매';
                                        } elseif (preg_match('/수량[:\s]*(\d+[\d,]*)\s*([가-힣a-zA-Z]+)?/u', $full_spec, $matches)) {
                                            // 기존 형식: "수량: 500매" 등
                                            $quantity_num = str_replace(',', '', $matches[1]);
                                            $unit = isset($matches[2]) ? trim($matches[2]) : '';
                                        }
                                    }
                                }

                                // 사양이 없으면 기본값
                                if (empty($full_spec)) {
                                    $full_spec = '-';
                                }

                                // 🔧 Extract options for this item
                                $item_options = [];

                                // 1. Coating option
                                if (!empty($summary_item['coating_enabled']) && $summary_item['coating_enabled'] == 1) {
                                    $coating_type_kr = $summary_item['coating_type'] ?? '';
                                    if ($coating_type_kr == 'single') $coating_type_kr = '단면유광코팅';
                                    elseif ($coating_type_kr == 'double') $coating_type_kr = '양면유광코팅';
                                    elseif ($coating_type_kr == 'single_matte') $coating_type_kr = '단면무광코팅';
                                    elseif ($coating_type_kr == 'double_matte') $coating_type_kr = '양면무광코팅';
                                    $coating_price = intval($summary_item['coating_price'] ?? 0);
                                    if ($coating_price > 0) {
                                        $item_options[] = '코팅(' . $coating_type_kr . ') ' . number_format($coating_price) . '원';
                                    }
                                }

                                // 2. Folding option
                                if (!empty($summary_item['folding_enabled']) && $summary_item['folding_enabled'] == 1) {
                                    $folding_type_kr = $summary_item['folding_type'] ?? '';
                                    if ($folding_type_kr == '2fold') $folding_type_kr = '2단접지';
                                    elseif ($folding_type_kr == '3fold') $folding_type_kr = '3단접지';
                                    elseif ($folding_type_kr == 'accordion') $folding_type_kr = '아코디언접지';
                                    elseif ($folding_type_kr == 'gate') $folding_type_kr = '게이트접지';
                                    $folding_price = intval($summary_item['folding_price'] ?? 0);
                                    if ($folding_price > 0) {
                                        $item_options[] = '접지(' . $folding_type_kr . ') ' . number_format($folding_price) . '원';
                                    }
                                }

                                // 3. Creasing option
                                if (!empty($summary_item['creasing_enabled']) && $summary_item['creasing_enabled'] == 1) {
                                    $creasing_lines = intval($summary_item['creasing_lines'] ?? 0);
                                    $creasing_price = intval($summary_item['creasing_price'] ?? 0);
                                    if ($creasing_price > 0) {
                                        $item_options[] = '오시(' . $creasing_lines . '줄) ' . number_format($creasing_price) . '원';
                                    }
                                }

                                // 4. Envelope tape option
                                if (!empty($summary_item['envelope_tape_enabled']) && $summary_item['envelope_tape_enabled'] == 1) {
                                    $tape_quantity = intval($summary_item['envelope_tape_quantity'] ?? 0);
                                    $tape_price = intval($summary_item['envelope_tape_price'] ?? 0);
                                    if ($tape_price > 0) {
                                        $item_options[] = '양면테이프(' . number_format($tape_quantity) . '개) ' . number_format($tape_price) . '원';
                                    }
                                }

                                // 5. Premium options (business cards, NCR forms, merchandise bonds)
                                if (!empty($summary_item['premium_options'])) {
                                    $premium_opts = json_decode($summary_item['premium_options'], true);
                                    if ($premium_opts && is_array($premium_opts)) {
                                        // NCRFlambeau (양식지) processing
                                        if (isset($premium_opts['creasing_lines'])) {
                                            if (!empty($premium_opts['creasing_enabled'])) {
                                                $creasing_lines = $premium_opts['creasing_lines'] ?? '';
                                                $creasing_price = intval($premium_opts['creasing_price'] ?? 0);
                                                if (!empty($creasing_lines) && $creasing_price > 0) {
                                                    $item_options[] = '미싱 ' . $creasing_lines . '줄 ' . number_format($creasing_price) . '원';
                                                }
                                            }

                                            if (!empty($premium_opts['folding_enabled'])) {
                                                $folding_type = $premium_opts['folding_type'] ?? '';
                                                $folding_price = intval($premium_opts['folding_price'] ?? 0);
                                                if ($folding_type === 'numbering' && $folding_price > 0) {
                                                    $item_options[] = '넘버링 ' . number_format($folding_price) . '원';
                                                }
                                            }
                                        } else {
                                            // Business cards/merchandise bond premium options
                                            $opt_config = [
                                                'foil' => ['name' => '박', 'types' => [
                                                    'gold_matte' => '금박무광',
                                                    'gold_gloss' => '금박유광',
                                                    'silver_matte' => '은박무광',
                                                    'silver_gloss' => '은박유광',
                                                    'blue_gloss' => '청박유광',
                                                    'red_gloss' => '적박유광',
                                                    'green_gloss' => '녹박유광',
                                                    'black_gloss' => '먹박유광'
                                                ]],
                                                'numbering' => ['name' => '넘버링', 'types' => ['single' => '1개', 'double' => '2개']],
                                                'perforation' => ['name' => '미싱', 'types' => ['horizontal' => '가로미싱', 'vertical' => '세로미싱', 'cross' => '십자미싱']],
                                                'rounding' => ['name' => '귀돌이', 'types' => ['4corners' => '네귀돌이', '2corners' => '두귀돌이']],
                                                'creasing' => ['name' => '오시', 'types' => ['single_crease' => '1줄오시', 'double_crease' => '2줄오시']]
                                            ];

                                            foreach ($opt_config as $key => $config) {
                                                if (!empty($premium_opts[$key . '_enabled']) && $premium_opts[$key . '_enabled'] == 1) {
                                                    $price = intval($premium_opts[$key . '_price'] ?? 0);
                                                    if ($price > 0) {
                                                        $opt_type = $premium_opts[$key . '_type'] ?? '';
                                                        $type_name = '';
                                                        if (!empty($opt_type) && isset($config['types'][$opt_type])) {
                                                            $type_name = '(' . $config['types'][$opt_type] . ')';
                                                        }
                                                        $item_options[] = $config['name'] . $type_name . ' ' . number_format($price) . '원';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;"><?= $row_num++ ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm;"><?= $item_type_display ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; font-size: 10pt; line-height: 1.4; vertical-align: top;">
                                    <?php
                                    // 🔧 규격/옵션 2줄+2줄 형식으로 표시 (duson-print-rules 준수)
                                    $spec_parts = array_map('trim', explode('|', $full_spec));
                                    $spec_parts = array_filter($spec_parts, function($p) { return !empty($p); });
                                    $spec_parts = array_values($spec_parts);

                                    // 규격 (최대 2줄)
                                    for ($i = 0; $i < min(2, count($spec_parts)); $i++):
                                    ?>
                                        <div style="color: #4a5568; margin-bottom: 1px;"><?= htmlspecialchars($spec_parts[$i]) ?></div>
                                    <?php endfor; ?>

                                    <?php
                                    // 옵션 (나머지 최대 2줄)
                                    for ($i = 2; $i < min(4, count($spec_parts)); $i++):
                                    ?>
                                        <div style="color: #667eea; margin-bottom: 1px;"><?= htmlspecialchars($spec_parts[$i]) ?></div>
                                    <?php endfor; ?>

                                    <?php if (!empty($item_options)): ?>
                                        <div style="color: #e65100; font-size: 9pt; margin-top: 2px;"><?= implode(' / ', $item_options) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">
                                    <?= $quantity_num ? (floor($quantity_num) == $quantity_num ? number_format($quantity_num) : number_format($quantity_num, 1)) : '-' ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">
                                    <?= htmlspecialchars($unit) ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;">
                                    <?= number_format(intval($summary_item['money_4'])) ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; font-weight: bold;">
                                    <?= number_format(intval($summary_item['money_4'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <!-- 합계 행 -->
                            <tr style="background-color: #f9f9f9; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">공급가액</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;"><?= number_format(round($View_money_4, -1)) ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;"><?= number_format(round($View_money_4, -1)) ?></td>
                            </tr>
                            <!-- 부가세포함금액 행 추가 (10원 단위 반올림) -->
                            <tr style="background-color: #e9ecef; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; color: #000;">💰 부가세포함</td>
                                <td colspan="2" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; color: #000; font-size: 12pt;"><?= number_format(round($View_money_5, -1)) ?> 원</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <!-- 🔧 가격 정보 표시 제거됨 - 테이블의 "총 합계" 행에서 이미 표시됨 -->
                </div>

                <!-- 고객 정보 -->
                <div class="print-info-section">
                    <div class="print-info-title">고객정보</div>
                    <table class="print-table">
                        <tr>
                            <th>성명</th>
                            <td><?= htmlspecialchars($View_name) ?></td>
                            <th>전화</th>
                            <td><?= htmlspecialchars($View_phone) ?></td>
                        </tr>
                        <tr>
                            <th>주소</th>
                            <td colspan="3">[<?= $View_zip ?>] <?= htmlspecialchars($View_zip1) ?> <?= htmlspecialchars($View_zip2) ?></td>
                        </tr>
                        <?php if (!empty($View_bizname)) { ?>
                            <tr>
                                <th>업체명</th>
                                <td><?= htmlspecialchars($View_bizname) ?></td>
                                <th>입금</th>
                                <td><?= htmlspecialchars($View_bank) ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>

                <!-- 기타 사항 및 사업자 정보 -->
                <?php if (!empty($View_cont) && trim($View_cont) != '') { ?>
                    <div class="print-info-section">
                        <div class="print-info-title">기타사항</div>
                        <div style="padding: 2mm; border: 0.3pt solid #666; min-height: 10mm; font-size: 8pt; line-height: 1.2;">
                            <?php echo nl2br(htmlspecialchars($View_cont)); ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="print-footer">두손기획인쇄 02-2632-1830</div>
            </div>
        </div>
    </div>

    <!-- 화면 표시용 내용 (엑셀 스타일 리디자인 2026-01-03) -->
    <div class="screen-only">
        <div class="admin-container" style="width: 700px; max-width: 100%; margin: 0 auto; padding: 15px; background: #fff; box-sizing: border-box;">

            <!-- ===== 주문 기본 정보 테이블 ===== -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                <tr style="background: #4472C4;">
                    <td colspan="4" style="padding: 12px 15px; color: #fff; font-size: 16px; font-weight: bold; text-align: center;">
                        주문 상세 정보
                    </td>
                </tr>
                <tr>
                    <th style="width: 15%; background: #E0E0E0; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">주문번호</th>
                    <td style="width: 35%; border: 1px solid #999; padding: 8px 10px; font-size: 13px; font-weight: bold; color: #C00000;"><?= $View_No ?></td>
                    <th style="width: 15%; background: #E0E0E0; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">주문일시</th>
                    <td style="width: 35%; border: 1px solid #999; padding: 8px 10px; font-size: 12px;"><?= $View_date ?></td>
                </tr>
                <tr>
                    <th style="background: #E0E0E0; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">주문자</th>
                    <td style="border: 1px solid #999; padding: 8px 10px; font-size: 13px; font-weight: bold;"><?= $View_name ?></td>
                    <th style="background: #E0E0E0; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">주문상태</th>
                    <td style="border: 1px solid #999; padding: 8px 10px; font-size: 12px;">
                        <?php
                        switch ($View_OrderStyle) {
                            case '1': echo '<span style="color: #856404; font-weight: bold;">주문접수</span>'; break;
                            case '2': echo '<span style="color: #155724; font-weight: bold;">신규주문</span>'; break;
                            case '3': echo '<span style="color: #004085; font-weight: bold;">확인완료</span>'; break;
                            case '6': echo '<span style="color: #721c24; font-weight: bold;">시안</span>'; break;
                            case '7': echo '<span style="color: #383d41; font-weight: bold;">교정</span>'; break;
                            default: echo '<span style="color: #6c757d;">상태미정</span>';
                        }
                        ?>
                    </td>
                </tr>
            </table>

            <form name='JoinInfo' method='post' enctype='multipart/form-data' onsubmit='return JoinCheckField()' action='/admin/mlangprintauto/admin.php' style="width: 100%; margin: 0; padding: 0;">
                <?php if ($no) { ?>
                    <input type="hidden" name="no" value="<?= $no ?>">
                    <input type="hidden" name="mode" value="ModifyOk">
                <?php } else { ?>
                    <input type="hidden" name="mode" value="SubmitOk">
                <?php } ?>

                <?php if ($no) { ?>

                <!-- ===== 주문 상품 테이블 ===== -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                    <tr style="background: #4472C4;">
                        <td colspan="7" style="padding: 10px 15px; color: #fff; font-size: 14px; font-weight: bold;">
                            주문 상품 정보
                        </td>
                    </tr>
                    <?php
                    if (empty($order_rows) || !is_array($order_rows)) {
                        echo "<tr><td colspan='7' style='padding: 15px; color: #dc3545; background: #fff3cd;'>";
                        echo "주문 데이터를 불러올 수 없습니다. (주문번호: " . htmlspecialchars($View_No ?? 'N/A') . ")";
                        echo "</td></tr>";
                    } else {
                    ?>
                    <tr style="background: #E0E0E0;">
                        <th style="width: 4%; border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center;">NO</th>
                        <th style="width: 8%; border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center;">품목</th>
                        <th style="width: 48%; border: 1px solid #999; padding: 8px; font-size: 11px; text-align: left;">규격/옵션</th>
                        <th style="width: 12%; border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center;">수량</th>
                        <th style="width: 5%; border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center;">단위</th>
                        <th style="width: 11%; border: 1px solid #999; padding: 8px; font-size: 11px; text-align: right;">인쇄비</th>
                        <th style="width: 12%; border: 1px solid #999; padding: 8px; font-size: 11px; text-align: right;">공급가액</th>
                    </tr>
                    <?php
                                        // 각 주문 아이템을 표의 행으로 표시
                                        $row_num = 1;
                                        foreach ($order_rows as $summary_item):
                                            // ✅ ProductSpecFormatter로 규격/수량/단위 정보 추출 (중복 코드 제거)
                                            $info = getOrderItemInfo($summary_item, $specFormatter);
                                            $full_spec = $info['full_spec'];
                                            $quantity_num = $info['quantity_num'];
                                            $unit = $info['unit'];
                                            $product_type_kr = $info['item_type_display'];  // Excel 섹션용 변수명
                                            $is_flyer = $info['is_flyer'];
                                            $mesu_for_display = $info['mesu_for_display'];
                                            $type1_data = $info['json_data'];  // Excel 섹션용 변수명

                                            // 🔧 Extract options for this item (옵션 추출)
                                            $item_options = [];

                                            // 1. Coating option (코팅)
                                            if (!empty($summary_item['coating_enabled']) && $summary_item['coating_enabled'] == 1) {
                                                $coating_type_kr = $summary_item['coating_type'] ?? '';
                                                if ($coating_type_kr == 'single') $coating_type_kr = '단면유광코팅';
                                                elseif ($coating_type_kr == 'double') $coating_type_kr = '양면유광코팅';
                                                elseif ($coating_type_kr == 'single_matte') $coating_type_kr = '단면무광코팅';
                                                elseif ($coating_type_kr == 'double_matte') $coating_type_kr = '양면무광코팅';
                                                $coating_price = intval($summary_item['coating_price'] ?? 0);
                                                if ($coating_price > 0) {
                                                    $item_options[] = '코팅(' . $coating_type_kr . ') ' . number_format($coating_price) . '원';
                                                }
                                            }

                                            // 2. Folding option (접지)
                                            if (!empty($summary_item['folding_enabled']) && $summary_item['folding_enabled'] == 1) {
                                                $folding_type_kr = $summary_item['folding_type'] ?? '';
                                                if ($folding_type_kr == '2fold') $folding_type_kr = '2단접지';
                                                elseif ($folding_type_kr == '3fold') $folding_type_kr = '3단접지';
                                                elseif ($folding_type_kr == 'accordion') $folding_type_kr = '아코디언접지';
                                                elseif ($folding_type_kr == 'gate') $folding_type_kr = '게이트접지';
                                                $folding_price = intval($summary_item['folding_price'] ?? 0);
                                                if ($folding_price > 0) {
                                                    $item_options[] = '접지(' . $folding_type_kr . ') ' . number_format($folding_price) . '원';
                                                }
                                            }

                                            // 3. Creasing option (오시)
                                            if (!empty($summary_item['creasing_enabled']) && $summary_item['creasing_enabled'] == 1) {
                                                $creasing_lines = intval($summary_item['creasing_lines'] ?? 0);
                                                $creasing_price = intval($summary_item['creasing_price'] ?? 0);
                                                if ($creasing_price > 0) {
                                                    $item_options[] = '오시(' . $creasing_lines . '줄) ' . number_format($creasing_price) . '원';
                                                }
                                            }

                                            // 4. Envelope tape option (양면테이프)
                                            if (!empty($summary_item['envelope_tape_enabled']) && $summary_item['envelope_tape_enabled'] == 1) {
                                                $tape_quantity = intval($summary_item['envelope_tape_quantity'] ?? 0);
                                                $tape_price = intval($summary_item['envelope_tape_price'] ?? 0);
                                                if ($tape_price > 0) {
                                                    $item_options[] = '양면테이프(' . number_format($tape_quantity) . '개) ' . number_format($tape_price) . '원';
                                                }
                                            }

                                            // 5. Premium options (명함 박, 넘버링, 미싱, 귀돌이 등)
                                            if (!empty($summary_item['premium_options'])) {
                                                $premium_options = json_decode($summary_item['premium_options'], true);
                                                if ($premium_options && is_array($premium_options)) {
                                                    // 박 옵션
                                                    if (!empty($premium_options['foil_enabled'])) {
                                                        $foil_type = $premium_options['foil_type'] ?? '';
                                                        $foil_types = ['gold_matte' => '금박무광', 'gold_gloss' => '금박유광', 'silver_matte' => '은박무광', 'silver_gloss' => '은박유광'];
                                                        $foil_name = $foil_types[$foil_type] ?? '박';
                                                        $foil_price = intval($premium_options['foil_price'] ?? 0);
                                                        if ($foil_price > 0) {
                                                            $item_options[] = '박(' . $foil_name . ') ' . number_format($foil_price) . '원';
                                                        }
                                                    }

                                                    // 넘버링
                                                    if (!empty($premium_options['numbering_enabled'])) {
                                                        $numbering_price = intval($premium_options['numbering_price'] ?? 0);
                                                        if ($numbering_price > 0) {
                                                            $item_options[] = '넘버링 ' . number_format($numbering_price) . '원';
                                                        }
                                                    }

                                                    // 미싱
                                                    if (!empty($premium_options['perforation_enabled'])) {
                                                        $perforation_type = $premium_options['perforation_type'] ?? '';
                                                        $perforation_types = ['horizontal' => '가로미싱', 'vertical' => '세로미싱', 'cross' => '십자미싱'];
                                                        $perforation_name = $perforation_types[$perforation_type] ?? '미싱';
                                                        $perforation_price = intval($premium_options['perforation_price'] ?? 0);
                                                        if ($perforation_price > 0) {
                                                            $item_options[] = '미싱(' . $perforation_name . ') ' . number_format($perforation_price) . '원';
                                                        }
                                                    }

                                                    // 귀돌이
                                                    if (!empty($premium_options['rounding_enabled'])) {
                                                        $rounding_type = $premium_options['rounding_type'] ?? '';
                                                        $rounding_types = ['4corners' => '네귀돌이', '2corners' => '두귀돌이'];
                                                        $rounding_name = $rounding_types[$rounding_type] ?? '귀돌이';
                                                        $rounding_price = intval($premium_options['rounding_price'] ?? 0);
                                                        if ($rounding_price > 0) {
                                                            $item_options[] = '귀돌이(' . $rounding_name . ') ' . number_format($rounding_price) . '원';
                                                        }
                                                    }
                                                }
                                            }

                                            // 금액 (인쇄비, 공급가액)
                                            $printing_cost = intval($summary_item['money_4'] ?? 0);
                                            $supply_price = $printing_cost; // 공급가액 = 인쇄비

                                            // 수량 표시 포맷 (천 단위 구분, 소수점 처리)
                                            if (!empty($quantity_num)) {
                                                $qty_float = floatval($quantity_num);
                                                // 정수면 소수점 없이, 소수면 1자리까지 표시
                                                $quantity_display = (floor($qty_float) == $qty_float)
                                                    ? number_format($qty_float)
                                                    : number_format($qty_float, 1);
                                                
                                                // 🆕 전단지인 경우 매수 정보 추가 표시: "0.5연 (2,000매)"
                                                if ($is_flyer && !empty($mesu_for_display) && $mesu_for_display > 0) {
                                                    $quantity_display .= $unit . ' (' . number_format($mesu_for_display) . '매)';
                                                    $unit = ''; // 단위 셀 비우기 (수량에 이미 포함됨)
                                                }
                                            } else {
                                                $quantity_display = '-';
                                            }
                                            $unit_display = !empty($unit) ? htmlspecialchars($unit) : '';

                                            ?>
                    <tr>
                        <td style="border: 1px solid #999; padding: 6px; text-align: center; font-size: 11px;"><?= $row_num++ ?></td>
                        <td style="border: 1px solid #999; padding: 6px; text-align: center; font-size: 12px; font-weight: bold; color: #2F5496;"><?= htmlspecialchars($product_type_kr) ?></td>
                        <td style="border: 1px solid #999; padding: 6px; font-size: 11px; line-height: 1.5;">
                            <?php
                            // 규격/옵션 표시
                            $spec_parts = array_map('trim', explode('|', $full_spec));
                            $spec_parts = array_filter($spec_parts, function($p) { return !empty($p); });
                            $spec_parts = array_values($spec_parts);
                            foreach ($spec_parts as $i => $part):
                                $color = ($i < 2) ? '#2F5496' : '#667eea';
                            ?>
                                <div style="color: <?= $color ?>; margin-bottom: 1px;"><?= htmlspecialchars($part) ?></div>
                            <?php endforeach; ?>
                            <?php if (!empty($item_options)): ?>
                                <div style="color: #C65911; font-size: 10px; margin-top: 2px;">옵션: <?= implode(', ', $item_options) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="border: 1px solid #999; padding: 6px; text-align: right; font-size: 11px;"><?= $quantity_display ?></td>
                        <td style="border: 1px solid #999; padding: 6px; text-align: center; font-size: 11px;"><?= $unit_display ?></td>
                        <td style="border: 1px solid #999; padding: 6px; text-align: right; font-size: 11px;"><?= number_format($printing_cost) ?></td>
                        <td style="border: 1px solid #999; padding: 6px; text-align: right; font-size: 11px; font-weight: bold;"><?= number_format($supply_price) ?></td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                </table>
                <?php } // end if (!empty($order_rows)) ?>

                <!-- ===== 가격 정보 테이블 ===== -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                    <tr style="background: #4472C4;">
                        <td colspan="2" style="padding: 10px 15px; color: #fff; font-size: 14px; font-weight: bold;">
                            가격 정보
                        </td>
                    </tr>
                                            <?php
                                            // ✅ 전체 합산용 변수 초기화
                                            $total_money_1 = 0; // 디자인비 합계
                                            $total_money_2 = 0; // 디자인비 합계 (View_money_2)
                                            $total_money_3 = 0; // 부가세 합계
                                            $total_money_4 = 0; // 인쇄비 합계
                                            $total_money_5 = 0; // 총합계
                                            $grand_additional_options_total = 0; // 전체 추가옵션 합계

                                            // ✅ 각 주문별로 반복 처리 (계산만 수행, 개별 표시 숨김)
                                            foreach ($order_rows as $index => $order_item) {
                                                // 현재 주문 아이템의 정보 설정
                                                $row = $order_item; // $row를 현재 아이템으로 설정 (아래 코드에서 $row 사용)

                                                // 제품 타입 한글 변환
                                                $product_type_kr = '';
                                                switch($row['Type']) {
                                                    case 'inserted': $product_type_kr = '전단지'; break;
                                                    case 'namecard': case 'NameCard': $product_type_kr = '명함'; break;
                                                    case 'envelope': $product_type_kr = '봉투'; break;
                                                    case 'sticker': $product_type_kr = '스티커'; break;
                                                    case 'msticker': $product_type_kr = '자석스티커'; break;
                                                    case 'cadarok': $product_type_kr = '카다록'; break;
                                                    case 'littleprint': case 'poster': $product_type_kr = '포스터'; break;
                                                    case 'ncrflambeau': $product_type_kr = '양식지'; break;
                                                    case 'merchandisebond': $product_type_kr = '상품권'; break;
                                                    case 'leaflet': $product_type_kr = '리플렛'; break;
                                                    default: $product_type_kr = htmlspecialchars($row['Type']); break;
                                                }

                                                // 🔧 개별 항목 표시 숨김 (2025-12-02) - 사용자 요청
                                                // 📦 제품별 헤더, 인쇄비, 디자인비 개별 표시 생략
                                                // 계산 로직은 유지하고 전체 합계만 표시

                                            // 추가옵션 금액 계산 (표시 생략, 합계 계산용)
                                            $additionalOptionsTotal = 0;

                                            // 1. 코팅 옵션
                                            if (!empty($row['coating_enabled']) && $row['coating_enabled'] == 1) {
                                                $additionalOptionsTotal += intval($row['coating_price'] ?? 0);
                                            }
                                            // 2. 접지 옵션
                                            if (!empty($row['folding_enabled']) && $row['folding_enabled'] == 1) {
                                                $additionalOptionsTotal += intval($row['folding_price'] ?? 0);
                                            }
                                            // 3. 오시 옵션
                                            if (!empty($row['creasing_enabled']) && $row['creasing_enabled'] == 1) {
                                                $additionalOptionsTotal += intval($row['creasing_price'] ?? 0);
                                            }
                                            // 4. 봉투 양면테이프 옵션
                                            if (!empty($row['envelope_tape_enabled']) && $row['envelope_tape_enabled'] == 1) {
                                                $additionalOptionsTotal += intval($row['envelope_tape_price'] ?? 0);
                                            }

                                            // Fallback: Type_1 JSON에서 추가 옵션 금액 계산 (레거시 데이터)
                                            if (!empty($View_Type_1)) {
                                                $typeData = json_decode($View_Type_1, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($typeData)) {
                                                    if (isset($typeData['additional_options'])) {
                                                        $options = $typeData['additional_options'];
                                                        // 코팅
                                                        if (empty($row['coating_enabled']) && isset($options['coating']) && $options['coating']['enabled']) {
                                                            $additionalOptionsTotal += intval($options['coating']['price'] ?? 0);
                                                        }
                                                        // 접지
                                                        if (empty($row['folding_enabled']) && isset($options['folding']) && $options['folding']['enabled']) {
                                                            $additionalOptionsTotal += intval($options['folding']['price'] ?? 0);
                                                        }
                                                        // 오시
                                                        if (empty($row['creasing_enabled']) && isset($options['creasing']) && $options['creasing']['enabled']) {
                                                            $additionalOptionsTotal += intval($options['creasing']['price'] ?? 0);
                                                        }
                                                    }
                                                }
                                            }
                                            // 프리미엄 옵션은 이미 인쇄비(money_4)에 포함되어 있으므로 별도 계산 불필요

                                            // ✅ 이 아이템의 소계를 전체 합계에 누적
                                            $total_money_2 += intval($row['money_2']); // 디자인비
                                            $total_money_4 += intval($row['money_4']); // 인쇄비

                                            // ✅ 부가세 계산: money_3가 0이면 money_5에서 역산 (레거시 데이터 처리)
                                            $item_vat = intval($row['money_3']);
                                            if ($item_vat == 0 && $row['money_5'] > 0) {
                                                // money_3가 저장되지 않은 경우, money_5에서 VAT 추출
                                                $supply_price = intval($row['money_4']) + intval($row['money_2']) + $additionalOptionsTotal;
                                                $item_vat = intval($row['money_5']) - $supply_price;
                                            }
                                            $total_money_3 += $item_vat; // 부가세

                                            $total_money_5 += intval($row['money_5']); // 총합계
                                            $grand_additional_options_total += $additionalOptionsTotal; // 추가옵션

                                            // 🔧 아이템별 소계 표시 숨김 (2025-12-02)
                                            // if ($is_group_order) { ... }

                                            } // ✅ foreach ($order_rows as $index => $order_item) 종료
                                            ?>

                    <tr>
                        <th style="width: 30%; background: #E0E0E0; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">공급가액</th>
                        <td style="width: 70%; border: 1px solid #999; padding: 8px 10px; font-size: 13px; text-align: right; font-weight: bold;"><?= number_format(round($total_money_4 + $total_money_2 + $grand_additional_options_total, -1)) ?> 원</td>
                    </tr>
                    <tr style="background: #FFF2CC;">
                        <th style="width: 30%; background: #4472C4; border: 1px solid #999; padding: 10px; font-size: 13px; text-align: center; color: #fff;">부가세포함금액</th>
                        <td style="width: 70%; border: 1px solid #999; padding: 10px; font-size: 15px; text-align: right; font-weight: bold; color: #C00000;"><?= number_format(round($total_money_5, -1)) ?> 원</td>
                    </tr>
                </table>

                <!-- ===== 상품/주문 상태 테이블 ===== -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                    <tr>
                        <th style="width: 15%; background: #E0E0E0; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">상품 유형</th>
                        <td style="width: 35%; border: 1px solid #999; padding: 8px 10px; font-size: 12px; font-weight: bold; color: #2F5496;"><?= htmlspecialchars($View_Type) ?></td>
                        <th style="width: 15%; background: #E0E0E0; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">주문 상태</th>
                        <td style="width: 35%; border: 1px solid #999; padding: 8px 10px; font-size: 12px; font-weight: bold;">
                            <?php
                            switch ($View_OrderStyle) {
                                case '1': echo '<span style="color: #856404;">주문접수</span>'; break;
                                case '2': echo '<span style="color: #155724;">신규주문</span>'; break;
                                case '3': echo '<span style="color: #004085;">확인완료</span>'; break;
                                case '6': echo '<span style="color: #721c24;">시안</span>'; break;
                                case '7': echo '<span style="color: #383d41;">교정</span>'; break;
                                default: echo '<span style="color: #6c757d;">상태미정</span>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <?php
                // 업로드된 파일 표시 섹션 (Excel 스타일)
                if (!empty($View_ImgFolder) && $View_ImgFolder != '') {
                    $imgFolder = $View_ImgFolder;
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($imgFolder, '/');

                    if (is_dir($fullPath)) {
                        $files = array_diff(scandir($fullPath), array('.', '..'));

                        if (!empty($files)) {
                            echo "<table style='width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;'>";
                            echo "<tr style='background: #4472C4;'>";
                            echo "<td colspan='3' style='padding: 10px 15px; color: #fff; font-size: 14px; font-weight: bold;'>첨부 파일 (" . count($files) . "개)</td>";
                            echo "</tr>";
                            echo "<tr style='background: #E0E0E0;'>";
                            echo "<th style='width: 50%; border: 1px solid #999; padding: 6px; font-size: 11px; text-align: center;'>파일명</th>";
                            echo "<th style='width: 20%; border: 1px solid #999; padding: 6px; font-size: 11px; text-align: center;'>크기</th>";
                            echo "<th style='width: 30%; border: 1px solid #999; padding: 6px; font-size: 11px; text-align: center;'>다운로드</th>";
                            echo "</tr>";

                            foreach ($files as $file) {
                                $filePath = $imgFolder . '/' . $file;
                                $fileSize = filesize($fullPath . '/' . $file);
                                $fileSizeFormatted = $fileSize > 1024 * 1024
                                    ? number_format($fileSize / (1024 * 1024), 2) . ' MB'
                                    : number_format($fileSize / 1024, 2) . ' KB';

                                echo "<tr>";
                                echo "<td style='border: 1px solid #999; padding: 6px; font-size: 11px; word-break: break-all;'>" . htmlspecialchars($file) . "</td>";
                                echo "<td style='border: 1px solid #999; padding: 6px; font-size: 11px; text-align: center;'>$fileSizeFormatted</td>";
                                echo "<td style='border: 1px solid #999; padding: 6px; text-align: center;'>";
                                echo "<a href='/" . htmlspecialchars($filePath) . "' download='" . htmlspecialchars($file) . "' style='padding: 4px 10px; background: #4472C4; color: white; text-decoration: none; font-size: 10px; font-weight: bold;'>다운로드</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                    }
                }
                ?>

                <!-- 주문개수 필드 숨김 (레거시 필드) -->
                <input name="Gensu" type="hidden" value='<?= $View_Gensu ?>'>

                <!-- ===== 신청자 정보 테이블 ===== -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                    <tr style="background: #4472C4;">
                        <td colspan="4" style="padding: 10px 15px; color: #fff; font-size: 14px; font-weight: bold;">
                            신청자 정보
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 15%; background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">성명/상호</th>
                        <td style="width: 35%; border: 1px solid #999; padding: 4px 8px;"><input name="name" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_name ?>'></td>
                        <th style="width: 15%; background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">E-MAIL</th>
                        <td style="width: 35%; border: 1px solid #999; padding: 4px 8px;"><input name="email" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_email ?>'></td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">우편번호</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input type="text" name="zip" style="width: 80px; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_zip ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">전화번호</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="phone" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_phone ?>'></td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">주소</th>
                        <td colspan="3" style="border: 1px solid #999; padding: 4px 8px;">
                            <input type="text" name="zip1" placeholder="기본주소" style="width: 48%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; margin-right: 2%;" value='<?= $View_zip1 ?>'>
                            <input type="text" name="zip2" placeholder="상세주소" style="width: 48%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_zip2 ?>'>
                        </td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">배송지</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input type="text" name="delivery" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_delivery ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">휴대폰</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="Hendphone" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_Hendphone ?>'></td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">사업자명</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input type="text" name="bizname" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_bizname ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">입금은행</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="bank" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_bank ?>'></td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">입금자명</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="bankname" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_bankname ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">비고</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><textarea name="cont" rows="2" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; resize: vertical;"><?= $View_cont ?></textarea></td>
                    </tr>
                </table>

                <!-- ✅ 첨부 파일 섹션 (admin.php에서 전달) -->
                <?php if (isset($GLOBALS['file_section_html']) && !empty($GLOBALS['file_section_html'])): ?>
                    <?php echo $GLOBALS['file_section_html']; ?>
                <?php endif; ?>

                <!-- ===== 관리자 버튼 ===== -->
                <div style="margin-top: 15px; text-align: center; padding: 15px; background: #f5f5f5; border: 1px solid #ddd;">
                    <?php if ($no) { ?>
                        <button type="submit" style="padding: 10px 25px; font-size: 13px; margin-right: 10px; background: #4472C4; color: white; border: none; cursor: pointer; font-weight: bold;">정보 수정</button>
                        <button type="button" onclick="printOrder();" style="padding: 10px 25px; font-size: 13px; margin-right: 10px; background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold;">주문서 출력</button>
                    <?php } ?>
                    <button type="button" onclick="window.close();" style="padding: 10px 25px; font-size: 13px; background: #6c757d; color: white; border: none; cursor: pointer; font-weight: bold;">창 닫기</button>
                </div>

                <?php } // end if ($no) - line 1429에서 열린 블록 종료 ?>

                </form>
                </table>
            </div> <!-- admin-content 종료 -->
        </div> <!-- admin-container 종료 -->
    </div> <!-- screen-only 종료 -->

</body>
</html>