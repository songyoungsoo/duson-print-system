<?php
/**
 * 📊 엑셀 스타일 주문서 출력 페이지
 * 스프레드시트 형태의 주문서 양식
 * 파일: mlangorder_printauto/OrderFormPrintExcel.php
 */

// 세션 시작 및 데이터베이스 연결
session_start();
include "../db.php";

// URL 파라미터에서 주문 정보 받기
$orders = $_GET['orders'] ?? '';
$email = $_GET['email'] ?? '';
$name = $_GET['name'] ?? '';

// 주문번호 파싱
$order_numbers = array_filter(explode(',', $orders));
$order_list = [];

if (!empty($order_numbers)) {
    $order_numbers_str = implode(',', array_map('intval', $order_numbers));
    $query = "SELECT * FROM mlangorder_printauto WHERE no IN ($order_numbers_str) ORDER BY no ASC";
    $result = mysqli_query($db, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $order_list[] = $row;
        }
    }
}

$first_order = $order_list[0] ?? [];

// 총계 계산
$total_supply = 0;
$total_vat = 0;
$total_amount = 0;

foreach ($order_list as $order) {
    $supply = floatval($order['money_4'] ?? 0);
    $amount = floatval($order['money_5'] ?? 0);
    $vat = $amount - $supply;

    $total_supply += $supply;
    $total_vat += $vat;
    $total_amount += $amount;
}

// 상품 상세 정보 표시 함수
function getProductDetails($order) {
    $details = [];

    if (!empty($order['Type_1'])) {
        $json_data = json_decode($order['Type_1'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
            if (!empty($json_data['formatted_display'])) {
                $formatted_lines = explode('\\n', $json_data['formatted_display']);
                foreach ($formatted_lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $details[] = $line;
                    }
                }
            }
        }
    }

    if (empty($details)) {
        $details[] = $order['Type'] ?? '주문 상품';
    }

    return $details;
}

// 수량 추출 함수 - DB unit 필드 사용 (2025-12-10 수정)
function extractQuantity($order) {
    // DB에서 unit 필드 가져오기 (없으면 '매' 기본값)
    $unit = $order['unit'] ?? '매';

    $json_data = json_decode($order['Type_1'] ?? '', true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
        $order_data = $json_data['order_details'] ?? $json_data;

        $quantity = $order_data['quantityTwo'] ?? $order_data['mesu'] ?? $order_data['quantity'] ?? 0;
        if ($quantity > 0) {
            return number_format($quantity) . $unit;
        }

        if (!empty($order_data['MY_amount'])) {
            $yeonsu = floatval($order_data['MY_amount']);
            if ($yeonsu > 0) {
                if (floor($yeonsu) == $yeonsu) {
                    return number_format($yeonsu) . $unit;
                } else {
                    return number_format($yeonsu, 1) . $unit;
                }
            }
        }
    }

    return '1' . $unit;
}

// 추가 옵션 추출 함수
function getAdditionalOptions($order) {
    $options = [];

    // 코팅 옵션
    if (!empty($order['coating_enabled']) && $order['coating_enabled'] == 1) {
        $coating_names = [
            'single' => '단면유광',
            'double' => '양면유광',
            'single_matte' => '단면무광',
            'double_matte' => '양면무광'
        ];
        $coating_type = $order['coating_type'] ?? 'single';
        $coating_price = intval($order['coating_price'] ?? 0);
        $options[] = [
            'name' => '코팅',
            'detail' => ($coating_names[$coating_type] ?? $coating_type),
            'price' => $coating_price
        ];
    }

    // 접지 옵션
    if (!empty($order['folding_enabled']) && $order['folding_enabled'] == 1) {
        $folding_names = [
            '2fold' => '2단접지',
            '3fold' => '3단접지',
            'accordion' => '병풍접지',
            'gate' => '대문접지'
        ];
        $folding_type = $order['folding_type'] ?? '2fold';
        $folding_price = intval($order['folding_price'] ?? 0);
        $options[] = [
            'name' => '접지',
            'detail' => ($folding_names[$folding_type] ?? $folding_type),
            'price' => $folding_price
        ];
    }

    // 오시 옵션
    if (!empty($order['creasing_enabled']) && $order['creasing_enabled'] == 1) {
        $creasing_lines = intval($order['creasing_lines'] ?? 1);
        $creasing_price = intval($order['creasing_price'] ?? 0);
        $options[] = [
            'name' => '오시',
            'detail' => $creasing_lines . '줄',
            'price' => $creasing_price
        ];
    }

    return $options;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문서 - 두손기획인쇄</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* 📊 엑셀 스타일 주문서 CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', 'Malgun Gothic', sans-serif;
            background: #f0f0f0;
            padding: 20px;
            font-size: 11pt;
        }

        /* 메인 컨테이너 - A4 크기 */
        .excel-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            border: 1px solid #bfbfbf;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* 엑셀 그리드 테이블 스타일 */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #bfbfbf;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
            font-size: 10pt;
        }

        /* 엑셀 행 번호 스타일 */
        .row-num {
            width: 30px;
            background: #f0f0f0;
            text-align: center;
            color: #333;
            font-weight: 500;
            font-size: 9pt;
        }

        /* 엑셀 열 헤더 스타일 */
        .col-header {
            background: #E8F4E8;
            color: #4A6741;
            font-weight: 600;
            text-align: center;
            font-size: 10pt;
        }

        /* 제목 행 스타일 */
        .title-row td {
            background: #4A6741;
            color: white;
            font-weight: 700;
            font-size: 16pt;
            text-align: center;
            padding: 15px;
            letter-spacing: 3px;
        }

        /* 부제목 행 */
        .subtitle-row td {
            background: #5C8254;
            color: white;
            font-size: 9pt;
            text-align: center;
            padding: 8px;
        }

        /* 섹션 헤더 */
        .section-header td {
            background: #E8F4E8;
            color: #4A6741;
            font-weight: 600;
            padding: 8px;
            font-size: 11pt;
        }

        /* 데이터 셀 */
        .data-cell {
            background: white;
        }

        .data-cell.alt {
            background: #FAFAFA;
        }

        /* 라벨 셀 */
        .label-cell {
            background: #F5F5F5;
            font-weight: 500;
            width: 80px;
            text-align: center;
        }

        /* 값 셀 */
        .value-cell {
            background: white;
        }

        /* 숫자 정렬 */
        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* 금액 스타일 */
        .amount {
            font-family: 'Consolas', 'Courier New', monospace;
            font-weight: 600;
        }

        .amount-large {
            font-size: 12pt;
            color: #1a5f1a;
        }

        /* 합계 행 */
        .total-row td {
            background: #FFF9E6;
            font-weight: 600;
        }

        .grand-total-row td {
            background: #E8F4E8;
            font-weight: 700;
            font-size: 11pt;
        }

        /* 입금 정보 섹션 */
        .bank-section td {
            background: #FFF8DC;
        }

        /* 품목 상세 */
        .item-detail {
            font-size: 9pt;
            color: #555;
            line-height: 1.4;
        }

        /* 상태 배지 */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #FFE4B5;
            color: #8B4513;
            font-size: 9pt;
            font-weight: 500;
        }

        /* 연락처 아이콘 */
        .contact-icon {
            display: inline-block;
            width: 16px;
            text-align: center;
        }

        /* 푸터 */
        .footer-row td {
            background: #f5f5f5;
            font-size: 8pt;
            color: #666;
            text-align: center;
            padding: 10px;
        }

        /* 옵션 태그 */
        .option-tag {
            display: inline-block;
            background: #E3F2FD;
            color: #1565C0;
            padding: 2px 6px;
            margin: 1px;
            font-size: 8pt;
            border: 1px solid #90CAF9;
        }

        /* 인쇄 스타일 */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .excel-container {
                box-shadow: none;
                border: none;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            @page {
                margin: 10mm;
                size: A4;
            }
        }

        /* 인쇄 버튼 */
        .print-buttons {
            text-align: center;
            margin: 20px 0;
        }

        .print-btn {
            display: inline-block;
            padding: 10px 30px;
            margin: 0 10px;
            background: #4A6741;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .print-btn:hover {
            background: #3A5231;
        }

        .print-btn.secondary {
            background: #666;
        }

        .print-btn.secondary:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <!-- 인쇄 버튼 -->
    <div class="print-buttons no-print">
        <button class="print-btn" onclick="window.print()">🖨️ 인쇄하기</button>
        <button class="print-btn secondary" onclick="window.close()">✕ 닫기</button>
    </div>

    <div class="excel-container">
        <table class="excel-table">
            <!-- 열 너비 설정 -->
            <colgroup>
                <col style="width: 30px;">  <!-- A: 행번호 -->
                <col style="width: 80px;">  <!-- B: 라벨 -->
                <col style="width: 120px;"> <!-- C: 값1 -->
                <col style="width: 80px;">  <!-- D: 라벨 -->
                <col style="width: 120px;"> <!-- E: 값2 -->
                <col style="width: 80px;">  <!-- F: 라벨 -->
                <col style="width: auto;">  <!-- G: 값3 -->
            </colgroup>

            <!-- Row 1: 회사명 -->
            <tr class="title-row">
                <td class="row-num">1</td>
                <td colspan="6">두손기획인쇄</td>
            </tr>

            <!-- Row 2: 부제목 -->
            <tr class="subtitle-row">
                <td class="row-num">2</td>
                <td colspan="6">ORDER FORM | 주문서 | <?php echo date('Y-m-d'); ?></td>
            </tr>

            <!-- Row 3: 빈 행 -->
            <tr>
                <td class="row-num">3</td>
                <td colspan="6" style="height: 5px; background: #f0f0f0;"></td>
            </tr>

            <!-- Row 4: 회사 정보 헤더 -->
            <tr class="section-header">
                <td class="row-num">4</td>
                <td colspan="6">📍 회사 정보</td>
            </tr>

            <!-- Row 5: 주소 & 전화 -->
            <tr>
                <td class="row-num">5</td>
                <td class="label-cell">주소</td>
                <td colspan="3" class="value-cell">서울 영등포구 영등포로36길 9, 송호빌딩 1층</td>
                <td class="label-cell">전화</td>
                <td class="value-cell">02-2632-1830</td>
            </tr>

            <!-- Row 6: 팩스 & 웹사이트 -->
            <tr>
                <td class="row-num">6</td>
                <td class="label-cell">팩스</td>
                <td class="value-cell">02-2632-1831</td>
                <td class="label-cell">웹사이트</td>
                <td colspan="3" class="value-cell">www.dsp114.com</td>
            </tr>

            <!-- Row 7: 빈 행 -->
            <tr>
                <td class="row-num">7</td>
                <td colspan="6" style="height: 5px; background: #f0f0f0;"></td>
            </tr>

            <!-- Row 8: 고객 정보 헤더 -->
            <tr class="section-header">
                <td class="row-num">8</td>
                <td colspan="6">👤 고객 정보</td>
            </tr>

            <!-- Row 9: 고객명 & 연락처 -->
            <tr>
                <td class="row-num">9</td>
                <td class="label-cell">고객명</td>
                <td class="value-cell"><?php echo htmlspecialchars($name ?: $first_order['name'] ?: '-'); ?></td>
                <td class="label-cell">연락처</td>
                <td colspan="3" class="value-cell"><?php
                    $phone = $first_order['Hendphone'] ?: $first_order['phone'] ?: '-';
                    echo htmlspecialchars($phone);
                ?></td>
            </tr>

            <!-- Row 10: 이메일 & 주문일 -->
            <tr>
                <td class="row-num">10</td>
                <td class="label-cell">이메일</td>
                <td colspan="2" class="value-cell"><?php echo htmlspecialchars($email ?: $first_order['email'] ?: '-'); ?></td>
                <td class="label-cell">주문일</td>
                <td colspan="2" class="value-cell"><?php echo htmlspecialchars($first_order['date'] ?? date('Y-m-d H:i:s')); ?></td>
            </tr>

            <!-- Row 11: 배송지 -->
            <tr>
                <td class="row-num">11</td>
                <td class="label-cell">배송지</td>
                <td colspan="5" class="value-cell">
                    <?php
                    $address = '';
                    if (!empty($first_order['zip'])) $address .= '[' . $first_order['zip'] . '] ';
                    if (!empty($first_order['zip1'])) $address .= $first_order['zip1'] . ' ';
                    if (!empty($first_order['zip2'])) $address .= $first_order['zip2'];
                    echo htmlspecialchars($address ?: '-');
                    ?>
                </td>
            </tr>

            <!-- Row 12: 빈 행 -->
            <tr>
                <td class="row-num">12</td>
                <td colspan="6" style="height: 5px; background: #f0f0f0;"></td>
            </tr>

            <!-- Row 13: 주문 내역 헤더 -->
            <tr class="section-header">
                <td class="row-num">13</td>
                <td colspan="6">📋 주문 내역</td>
            </tr>

            <!-- Row 14: 테이블 헤더 -->
            <tr>
                <td class="row-num">14</td>
                <td class="col-header">주문번호</td>
                <td class="col-header">품목</td>
                <td colspan="2" class="col-header">상세 규격</td>
                <td class="col-header">수량</td>
                <td class="col-header text-right">금액(VAT별도)</td>
            </tr>

            <!-- 주문 품목 반복 -->
            <?php
            $row_num = 15;
            foreach ($order_list as $index => $order):
                $details = getProductDetails($order);
                $quantity = extractQuantity($order);
                $supply_price = floatval($order['money_4'] ?? 0);
                $total_price = floatval($order['money_5'] ?? 0);
                $vat = $total_price - $supply_price;
                $options = getAdditionalOptions($order);
            ?>
            <tr class="<?php echo ($index % 2 == 1) ? 'data-cell alt' : 'data-cell'; ?>">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td class="text-center" style="font-weight: 600; color: #1565C0;">#<?php echo htmlspecialchars($order['no']); ?></td>
                <td style="font-weight: 500;"><?php echo htmlspecialchars($order['Type']); ?></td>
                <td colspan="2" class="item-detail">
                    <?php
                    foreach ($details as $detail) {
                        echo htmlspecialchars($detail) . '<br>';
                    }
                    if (!empty($options)) {
                        echo '<div style="margin-top: 3px;">';
                        foreach ($options as $opt) {
                            echo '<span class="option-tag">' . htmlspecialchars($opt['name']) . ': ' . htmlspecialchars($opt['detail']) . '</span> ';
                        }
                        echo '</div>';
                    }
                    ?>
                </td>
                <td class="text-center"><?php echo $quantity; ?></td>
                <td class="text-right amount">₩<?php echo number_format($supply_price); ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- 빈 행 (5줄 고정) -->
            <?php
            $items_count = count($order_list);
            $empty_rows = max(0, 5 - $items_count);
            for ($i = 0; $i < $empty_rows; $i++):
            ?>
            <tr>
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td class="data-cell"></td>
                <td class="data-cell"></td>
                <td colspan="2" class="data-cell"></td>
                <td class="data-cell"></td>
                <td class="data-cell"></td>
            </tr>
            <?php endfor; ?>

            <!-- 합계 행 -->
            <tr>
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6" style="height: 3px; background: #bfbfbf;"></td>
            </tr>

            <tr class="total-row">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="4"></td>
                <td class="label-cell">공급가액</td>
                <td class="text-right amount">₩<?php echo number_format($total_supply); ?></td>
            </tr>

            <tr class="total-row">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="4"></td>
                <td class="label-cell">부가세(10%)</td>
                <td class="text-right amount">₩<?php echo number_format($total_vat); ?></td>
            </tr>

            <tr class="grand-total-row">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="4" style="text-align: right; padding-right: 20px;">총 <?php echo count($order_list); ?>건</td>
                <td class="label-cell" style="background: #4A6741; color: white;">합계금액</td>
                <td class="text-right amount amount-large" style="background: #E8F4E8;">₩<?php echo number_format($total_amount); ?></td>
            </tr>

            <!-- 빈 행 -->
            <tr>
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6" style="height: 5px; background: #f0f0f0;"></td>
            </tr>

            <!-- 입금 정보 헤더 -->
            <tr class="section-header">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6">💳 입금 계좌 안내</td>
            </tr>

            <!-- 입금 정보 -->
            <tr class="bank-section">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td class="label-cell">국민은행</td>
                <td class="value-cell" style="font-weight: 600;">999-1688-2384</td>
                <td class="label-cell">신한은행</td>
                <td class="value-cell" style="font-weight: 600;">110-342-543507</td>
                <td class="label-cell">농협</td>
                <td class="value-cell" style="font-weight: 600;">301-2632-1830-11</td>
            </tr>

            <tr class="bank-section">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td class="label-cell">예금주</td>
                <td colspan="5" class="value-cell" style="font-weight: 600;">두손기획인쇄 차경선</td>
            </tr>

            <tr>
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6" style="background: #FFF3CD; color: #856404; text-align: center; padding: 10px; font-size: 10pt;">
                    ⚠️ 입금자명을 주문자명(<strong><?php echo htmlspecialchars($name ?: $first_order['name'] ?: '고객명'); ?></strong>)과 동일하게 해주세요
                </td>
            </tr>

            <!-- 빈 행 -->
            <tr>
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6" style="height: 5px; background: #f0f0f0;"></td>
            </tr>

            <!-- 요청 사항 -->
            <tr class="section-header">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6">📝 요청 사항</td>
            </tr>

            <tr>
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6" class="value-cell" style="min-height: 60px; padding: 10px;">
                    <?php echo nl2br(htmlspecialchars($first_order['cont'] ?? '-')); ?>
                </td>
            </tr>

            <!-- 푸터 -->
            <tr>
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6" style="height: 3px; background: #bfbfbf;"></td>
            </tr>

            <tr class="footer-row">
                <td class="row-num"><?php echo $row_num++; ?></td>
                <td colspan="6">
                    📞 문의: 02-2632-1830 | 1688-2384 | 📧 dsp1830@naver.com |
                    입금 확인 후 제작이 시작됩니다. 감사합니다.
                </td>
            </tr>
        </table>
    </div>

    <script>
        // ESC 키로 창 닫기
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
