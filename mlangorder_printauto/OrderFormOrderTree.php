<?php
ini_set('display_errors', '0');

// 절대 경로로 설정 (admin.php에서 include할 때도 정상 작동)
$HomeDir = $_SERVER['DOCUMENT_ROOT'];
$PageCode = "PrintAuto";
require_once $HomeDir . '/includes/PremiumOptionsConfig.php';

// 이미 db.php가 include되어 $db가 설정되어 있으면 건너뛰기
if (!isset($db) || !$db) {
    include "$HomeDir/db.php";
}

// ProductSpecFormatter도 한 번만 include
if (!class_exists('ProductSpecFormatter')) {
    include "$HomeDir/includes/ProductSpecFormatter.php";
}
// QuantityFormatter도 한 번만 include (SSOT)
if (!class_exists('QuantityFormatter')) {
    include "$HomeDir/includes/QuantityFormatter.php";
}
// ShippingCalculator (배송 추정)
if (!class_exists('ShippingCalculator')) {
    include "$HomeDir/includes/ShippingCalculator.php";
}

/**
 * ✅ 2026-01-13: 전단지 매수를 mlangprintauto_inserted 테이블에서 조회
 * (절대 계산하지 않음, DB값만 사용 - 샛밥 방식)
 *
 * @param mysqli $db DB 연결
 * @param float $reams 연수 (0.5, 1, 2, ...)
 * @return int 매수 (2000, 4000, 8000, ...)
 */
function lookupInsertedSheets($db, float $reams): int {
    if (!$db || $reams <= 0) {
        return 0;
    }

    $stmt = mysqli_prepare($db,
        "SELECT quantityTwo FROM mlangprintauto_inserted WHERE quantity = ? LIMIT 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "d", $reams);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $sheets = intval($row['quantityTwo']);
            mysqli_stmt_close($stmt);
            return $sheets;
        }
        mysqli_stmt_close($stmt);
    }

    return 0;  // 조회 실패 시 0 반환 (계산하지 않음)
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
    // $original_no가 있으면 해당 주문을 기준으로 사용
    $row = $order_rows[0];
    if (isset($original_no) && $original_no > 0) {
        foreach ($order_rows as $candidate) {
            if (intval($candidate['no']) === intval($original_no)) {
                $row = $candidate;
                break;
            }
        }
    }
    $is_group_order = count($order_rows) > 1;
    $item_count = count($order_rows);
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
            $item_count = 1;
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
// 주문자명 ≠ 입금자명 비교 (적색 경고 표시용)
$bankname_mismatch = (!empty($row['bankname']) && trim($row['bankname']) !== '' && trim($row['name']) !== trim($row['bankname']));
$View_cont = htmlspecialchars($row['cont']);
// 비고 축약본 (인쇄용): 사업자정보는 상호+번호 한줄, 메모 없으면 '내용없음'
$_raw_cont = $row['cont'] ?? '';
$_biz_name = ''; $_biz_num = ''; $_memo = [];
foreach (explode("\n", $_raw_cont) as $_cl) {
    $_cl = trim($_cl);
    if ($_cl === '' || $_cl === '=== 사업자 정보 ===' || preg_match('/^\[같은 스펙/', $_cl)) continue;
    if (preg_match('/^상호\(회사명\):\s*(.+)/', $_cl, $_m)) { $_biz_name = $_m[1]; continue; }
    if (preg_match('/^사업자등록번호:\s*(.+)/', $_cl, $_m)) { $_biz_num = $_m[1]; continue; }
    if (preg_match('/^(대표자명|사업장주소|세금계산서|업태|종목)/', $_cl)) continue;
    $_memo[] = $_cl;
}
$_parts = [];
if (!empty($_memo)) $_parts[] = implode(' / ', $_memo);
if (!empty($_biz_name)) $_parts[] = '사업자: ' . $_biz_name . (!empty($_biz_num) ? ' (' . $_biz_num . ')' : '');
$View_cont_compact = !empty($_parts) ? htmlspecialchars(implode(' | ', $_parts)) : '내용없음';
$View_date = htmlspecialchars($row['date']);
$View_OrderStyle = htmlspecialchars($row['OrderStyle']);
$View_ThingCate = htmlspecialchars($row['ThingCate']);
$View_Gensu = htmlspecialchars($row['Gensu']);

// ✅ 택배 확정 정보 (logen_* 컬럼)
$View_logen_box_qty = intval($row['logen_box_qty'] ?? 0);
$View_logen_delivery_fee = intval($row['logen_delivery_fee'] ?? 0);
$View_logen_fee_type = htmlspecialchars($row['logen_fee_type'] ?? '');
$View_shipping_bundle_type = htmlspecialchars($row['shipping_bundle_type'] ?? '');
$View_logen_tracking_no = htmlspecialchars($row['logen_tracking_no'] ?? '');
$has_logen_confirmed = ($View_logen_delivery_fee > 0 || !empty($View_logen_tracking_no));

// ✅ 같은 그룹 주문 조회 (order_group_id 기반)
$group_orders = [];
$View_order_group_id = $row['order_group_id'] ?? '';
if (!empty($View_order_group_id)) {
    $gq = "SELECT no, Type, product_type, money_5, order_group_seq FROM mlangorder_printauto WHERE order_group_id = ? AND no != ? ORDER BY order_group_seq";
    $gs = mysqli_prepare($db, $gq);
    if ($gs) {
        mysqli_stmt_bind_param($gs, "si", $View_order_group_id, $no);
        mysqli_stmt_execute($gs);
        $gr = mysqli_stmt_get_result($gs);
        while ($grow = mysqli_fetch_assoc($gr)) { $group_orders[] = $grow; }
        mysqli_stmt_close($gs);
    }
}

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
        // ✅ 2026-01-18: money_4는 이미 공급가액 (money_1+money_2 포함), money_2 중복 추가 버그 수정
        $supply_price = intval($order_item['money_4'] ?? 0);
        $item_vat = intval($order_item['money_5']) - $supply_price;
    }
    $View_money_3 += $item_vat;

    $View_money_4 += intval($order_item['money_4'] ?? 0);
    $View_money_5 += intval($order_item['money_5'] ?? 0);
}

// ✅ ProductSpecFormatter 초기화
$specFormatter = new ProductSpecFormatter($db);

// 같은 스펙(Type+money_5) 건수 카운트 — 테이블 축약용
$_spec_counts = [];
foreach ($order_rows as $_o) {
    $_sk = ($_o['Type'] ?? '') . '|' . ($_o['money_5'] ?? '');
    $_spec_counts[$_sk] = ($_spec_counts[$_sk] ?? 0) + 1;
}
$_spec_seen = [];

/**
 * 수량 숫자 포맷팅 (불필요한 소수점 제거)
 * 500.00 → 500, 0.50 → 0.5
 * @param mixed $num 수량 값
 * @return string 포맷된 수량
 */
function formatQuantityNum($num) {
    if (empty($num) || !is_numeric($num)) {
        return '-';
    }
    $float_val = floatval($num);
    // 정수면 소수점 없이
    if (floor($float_val) == $float_val) {
        return number_format($float_val);
    }
    // 0.50 → 0.5 (불필요한 0 제거)
    return rtrim(rtrim(number_format($float_val, 2), '0'), '.');
}

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

    // ✅ Phase 3: 표준 필드 우선 사용 (cart.php, OnlineOrder, OrderComplete와 동일)
    $has_phase3 = isset($summary_item['data_version']) && $summary_item['data_version'] == 2;
    $has_phase3_fields = !empty($summary_item['spec_type']) || !empty($summary_item['quantity_display']);

    if ($has_phase3 || $has_phase3_fields) {
        // ✅ Phase 3 방식: DB 표준 필드 직접 사용
        $product_type = $summary_item['product_type'] ?? '';

        if ($product_type) {
            $item_type_display = $specFormatter->getProductTypeName($product_type);
        }

        // ProductSpecFormatter에 전달 (DB 필드 우선)
        $full_spec = $specFormatter->formatSingleLine($summary_item);

        // quantity_display에서 수량 정보 추출
        if (!empty($summary_item['quantity_display'])) {
            $qty_str = $summary_item['quantity_display'];
            // 예: "1,000부" → quantity_num=1000, unit="부"
            if (preg_match('/^([\d,.]+)\s*([가-힣a-zA-Z]+)?/', $qty_str, $matches)) {
                $quantity_num = floatval(str_replace(',', '', $matches[1]));
                $unit = $matches[2] ?? $summary_item['quantity_unit'] ?? '';
            }
        } else {
            $quantity_num = $summary_item['quantity_value'] ?? 0;
            $unit = $summary_item['quantity_unit'] ?? '';
        }

        // ✅ 2026-01-12: 수량이 없으면 ProductSpecFormatter에서 추출 (카다록/NCR 지원)
        if (empty($quantity_num) && !empty($summary_item['MY_amount'])) {
            $quantity_num = floatval($summary_item['MY_amount']);
            // 제품 타입별 단위 설정
            if ($product_type === 'cadarok') {
                $unit = '부';
            } elseif ($product_type === 'ncrflambeau') {
                $unit = '권';
            } elseif (empty($unit)) {
                $unit = ProductSpecFormatter::getUnit($summary_item);
            }
        }

        // 전단지 판별
        $is_flyer = ($product_type === 'inserted' || $product_type === 'leaflet');
        if ($is_flyer) {
            $mesu_for_display = intval($summary_item['quantity_sheets'] ?? 0);
        }

        // ✅ 2026-01-16: NCR양식지 매수 계산 (권 × 50 × multiplier)
        $is_ncr = ($product_type === 'ncrflambeau');
        if ($is_ncr && $quantity_num > 0) {
            $ncr_sheets = intval($summary_item['quantity_sheets'] ?? 0);
            // 잘못 저장된 레거시 데이터 보정 (sheets <= qty면 재계산)
            if ($ncr_sheets <= $quantity_num) {
                $multiplier = QuantityFormatter::extractNcrMultiplier($summary_item);
                $ncr_sheets = QuantityFormatter::calculateNcrSheets(intval($quantity_num), $multiplier);
            }
            $mesu_for_display = $ncr_sheets;
        }

    } elseif (!empty(trim($summary_item['Type_1'] ?? ''))) {
        // ✅ Fallback: Type_1 JSON 사용 (레거시 주문)
        $type_1_data = trim($summary_item['Type_1']);
        $json_data = json_decode($type_1_data, true);

        if ($json_data && is_array($json_data)) {
            // ✅ 2026-01-13 FIX: order_details 중첩 구조 처리 (레거시 데이터 호환)
            if (isset($json_data['order_details']) && is_array($json_data['order_details'])) {
                $json_data = array_merge($json_data, $json_data['order_details']);
                unset($json_data['order_details']);
            }

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
            }
            // ✅ 2026-01-13 FIX: 스티커 mesu 필드 처리
            elseif ($product_type === 'sticker' && isset($json_data['mesu']) && intval($json_data['mesu']) > 0) {
                $quantity_num = intval($json_data['mesu']);
                $unit = '매';
            }
            elseif (isset($json_data['quantityTwo']) && $json_data['quantityTwo'] > 0) {
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
                // ✅ 2026-01-13: 매수가 없으면 mlangprintauto_inserted에서 조회 (샛밥 방식)
                if ($mesu_for_display == 0 && $quantity_num > 0) {
                    $mesu_for_display = lookupInsertedSheets($db, floatval($quantity_num));
                }
            }

            // ✅ 2026-01-16: NCR양식지 매수 계산 (권 × 50 × multiplier)
            $is_ncr = ($product_type === 'ncrflambeau');
            if ($is_ncr && $quantity_num > 0) {
                $ncr_sheets = intval($summary_item['quantity_sheets'] ?? 0);
                // 잘못 저장된 레거시 데이터 보정
                if ($ncr_sheets <= $quantity_num) {
                    $itemData_for_ncr = array_merge($summary_item, $json_data);
                    $multiplier = QuantityFormatter::extractNcrMultiplier($itemData_for_ncr);
                    $ncr_sheets = QuantityFormatter::calculateNcrSheets(intval($quantity_num), $multiplier);
                }
                $mesu_for_display = $ncr_sheets;
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

                // ✅ 2026-01-12: 숫자 + 단위 형식일 경우 포맷팅 (소수점 포함)
                // 10000 매 → 10,000매, 10.00권 → 10권, 500.00매 → 500매
                if (preg_match('/^([\d,\.]+)\s*(매|개|장|부|연|권|EA)$/u', $part, $matches)) {
                    $num = floatval(str_replace(',', '', $matches[1]));
                    $unit = $matches[2];
                    $quantity_num = $num;
                    // 정수면 소수점 없이, 소수면 불필요한 0 제거
                    if (floor($num) == $num) {
                        $part = number_format($num) . $unit;
                    } else {
                        $part = rtrim(rtrim(number_format($num, 2), '0'), '.') . $unit;
                    }
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

            const printContainer = document.querySelector('.print-container');

            if (divider && employeeOrder) {
                divider.classList.remove('hidden');
                employeeOrder.classList.remove('new-page');
            }

            // overflow 감지: 관리자 영역이 130mm(≈492px @96dpi) 초과 시 2페이지 모드
            if (printContainer && adminOrder) {
                printContainer.classList.remove('two-page-mode');
                // 임시로 보이게 해서 실제 높이 측정
                const printOnly = document.querySelector('.print-only');
                printOnly.style.display = 'block';
                printOnly.style.position = 'absolute';
                printOnly.style.left = '-9999px';
                printOnly.style.width = '194mm'; // A4 - margins

                const adminHeight = adminOrder.scrollHeight;
                const mmToPx = 3.7795; // 1mm ≈ 3.78px at 96dpi
                const threshold = 130 * mmToPx; // 130mm → ~491px

                if (adminHeight > threshold) {
                    printContainer.classList.add('two-page-mode');
                }

                printOnly.style.display = '';
                printOnly.style.position = '';
                printOnly.style.left = '';
                printOnly.style.width = '';
            }

            window.print();

            // 제목 복원
            setTimeout(() => {
                document.title = originalTitle;
            }, 1000);
        }

        // 재주문 함수
        function reOrder(orderNo) {
            if (confirm('이 주문을 재주문하시겠습니까?\n동일한 내용으로 새 주문이 생성됩니다.')) {
                window.location.href = '/admin/mlangprintauto/admin.php?mode=ReOrder&source_no=' + orderNo;
            }
        }

        // 택배비 확정 / 송장번호 저장
        function saveLogenInfo() {
            var orderNo = <?= intval($no) ?>;
            var boxQty = document.getElementById('logen_box_qty') ? document.getElementById('logen_box_qty').value : '';
            var deliveryFee = document.getElementById('logen_delivery_fee') ? document.getElementById('logen_delivery_fee').value : '';
            var feeType = document.getElementById('logen_fee_type') ? document.getElementById('logen_fee_type').value : '';
            var trackingNo = document.getElementById('logen_tracking_no') ? document.getElementById('logen_tracking_no').value : '';

            var btn = document.getElementById('btn-logen-save');
            var resultSpan = document.getElementById('logen-save-result');
            btn.disabled = true;
            btn.textContent = '저장 중...';
            resultSpan.style.display = 'none';

            var formData = new FormData();
            formData.append('action', 'logen_save');
            formData.append('no', orderNo);
            formData.append('logen_box_qty', boxQty);
            formData.append('logen_delivery_fee', deliveryFee);
            formData.append('logen_fee_type', feeType);
            formData.append('logen_tracking_no', trackingNo);

            fetch('/includes/shipping_api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = '💾 저장';
                if (data.success) {
                    var msg = '✅ 저장되었습니다.';
                    if (data.group_count && data.group_count > 0) {
                        msg = '✅ 그룹 ' + (data.group_count) + '개 품목에 일괄 저장되었습니다.';
                    }
                    resultSpan.textContent = msg;
                    resultSpan.style.color = '#28a745';
                    resultSpan.style.display = 'inline';
                    // 확정 스타일로 변경
                    var section = document.getElementById('logen-confirm-section');
                    if (section) {
                        section.style.background = '#f0faf0';
                        section.style.borderColor = '#a8d5a8';
                    }
                } else {
                    resultSpan.textContent = '❌ 저장 실패: ' + (data.error || '알 수 없는 오류');
                    resultSpan.style.color = '#dc3545';
                    resultSpan.style.display = 'inline';
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.textContent = '💾 저장';
                resultSpan.textContent = '❌ 네트워크 오류';
                resultSpan.style.color = '#dc3545';
                resultSpan.style.display = 'inline';
            });
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
            margin: 0 0 2mm 0;  /* 상단 마진 제거하여 정확히 130mm 지점에 위치 */
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

        /* 프린트 시에만 표시 - A4 한장에 관리자용(130mm) + 절취선 + 직원용 */
        @media print {
            @page {
                margin: 6mm 8mm;
                size: A4;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-only {
                display: block !important;
            }

            .screen-only {
                display: none !important;
            }

            .admin-container,
            .file-section,
            input,
            button,
            textarea {
                display: none !important;
            }

            .print-container {
                width: 100%;
                height: 285mm;
                overflow: hidden;
            }

            .print-order {
                page-break-inside: avoid;
                overflow: hidden;
                box-sizing: border-box;
            }

            .print-order:first-child {
                height: 120mm;
            }

            .print-order.employee-copy {
                height: calc(285mm - 120mm - 6mm);
            }

            .print-order.employee-copy {
                height: calc(285mm - 124mm - 6mm); /* 나머지 공간 (직원용) */
            }

            .print-divider.hidden {
                display: none !important;
            }

            /* === compact-level-1: 3~4개 품목 — 폰트/여백 축소 === */
            .compact-level-1 .print-order table {
                font-size: 9pt;
            }
            .compact-level-1 .print-order table td,
            .compact-level-1 .print-order table th {
                padding: 1mm;
            }
            .compact-level-1 .print-order .print-info-section {
                margin-bottom: 1mm;
            }
            .compact-level-1 .print-title {
                font-size: 11pt;
                margin-bottom: 1mm;
            }

            /* === compact-level-2: 5개+ 품목 — 최대 압축 === */
            .compact-level-2 .print-order table {
                font-size: 8pt;
            }
            .compact-level-2 .print-order table td,
            .compact-level-2 .print-order table th {
                padding: 0.7mm;
            }
            .compact-level-2 .print-order .print-info-section {
                margin-bottom: 0.5mm;
            }
            .compact-level-2 .print-title {
                font-size: 10pt;
                margin-bottom: 0.5mm;
            }
            .compact-level-2 .print-footer {
                font-size: 7pt;
                margin-top: 0;
            }

            /* === two-page-mode: JS가 overflow 감지 시 전환 === */
            .two-page-mode {
                height: auto;
                overflow: visible;
            }
            .two-page-mode .print-order:first-child {
                height: auto;
                page-break-after: always;
            }
            .two-page-mode .print-order.employee-copy {
                height: auto;
                page-break-before: always;
            }
            .two-page-mode .print-divider {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- 프린트 전용 내용 -->
    <?php
    // 단계적 압축 레벨 결정: 0=기본, 1=압축, 2=최대압축
    $compact_level = 0;
    if ($item_count >= 5) $compact_level = 2;
    elseif ($item_count >= 3) $compact_level = 1;
    ?>
    <div class="print-only">
        <div class="print-container compact-level-<?= $compact_level ?>" data-item-count="<?= $item_count ?>">
            <!-- 첫 번째 주문서 (관리자용) -->
            <div class="print-order">
                <div class="print-title">주문서 (관리자용)</div>

                <!-- 주요 정보 (compact) -->
                <div style="margin-bottom: 2mm; padding: 1.5mm; border: 0.3pt solid #666;">
                    <div style="display: flex; gap: 2mm; align-items: center; font-size: 11pt; font-weight: bold; line-height: 1.2;">
                        <div style="width: 14ch; flex-shrink: 0;">
                            <span style="color: #000;">주문번호: <?= $View_No ?></span>
                        </div>
                        <div style="width: 18ch; flex-shrink: 0;">
                            <span style="color: #000;">일시: <?= htmlspecialchars($View_date) ?></span>
                        </div>
                        <div style="width: 16ch; flex-shrink: 0;">
                            <span style="color: #000;">T.<?= htmlspecialchars($View_phone) ?></span>
                        </div>
                        <div style="width: 16ch; flex-shrink: 0;">
                            <span style="color: #000;">H.<?= htmlspecialchars($View_Hendphone) ?></span>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <span style="color: #000;">주문자: <?= htmlspecialchars($View_name) ?></span>
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
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 6%;">NO</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 17%;">품 목</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 44%;">규격/옵션</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 11%;">수량</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 9%;">단위</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; width: 13%;">공급가액</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row_num = 1;
                            $_spec_seen = []; // 테이블마다 리셋
                            foreach ($order_rows as $summary_item):
                                // 같은 스펙 중복 스킵 (철 항목만 표시, 건수는 가격에 반영)
                                $_sk = ($summary_item['Type'] ?? '') . '|' . ($summary_item['money_5'] ?? '');
                                if (isset($_spec_seen[$_sk])) continue;
                                $_spec_seen[$_sk] = true;
                                $_n = $_spec_counts[$_sk] ?? 1;
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
                                            $pt = $summary_item['product_type'] ?? 'namecard';
                                            $parsed_premium = PremiumOptionsConfig::parseSelectedOptions($summary_item['premium_options'], $pt);
                                            foreach ($parsed_premium as $popt) {
                                                $item_options[] = $popt['display'] . ' ' . number_format($popt['price']) . '원';
                                            }
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;"><?= $row_num++ ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;"><?= $item_type_display ?><?php if ($_n > 1) echo ' <span style="color:#e74c3c;font-weight:bold;">&times;' . $_n . '건</span>'; ?></td>
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
                                    // 🔧 2026-01-14: 수량/단위 분리 - 수량 칼럼에 숫자+매수, 단위는 별도 칼럼
                                    echo formatQuantityNum($quantity_num);
                                    // ✅ 2026-01-16: 연/권 단위 모두 매수 표시 (전단지, NCR양식지)
                                    if ($mesu_for_display > 0 && in_array($unit, ['연', '권'])) {
                                        echo '<br><span style="font-size: 8pt; color: #1e88ff;">(' . number_format($mesu_for_display) . '매)</span>';
                                    }
                                    ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">
                                    <?php
                                    // 🔧 2026-01-14: 단위 칼럼 항상 표시
                                    echo htmlspecialchars($unit);
                                    ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; font-weight: bold;">
                                    <?php
                                    // Phase 3 표준 필드 우선 사용
                                    $supply = !empty($summary_item['price_supply']) ? $summary_item['price_supply'] : $summary_item['money_4'];
                                    echo number_format(intval($supply) * $_n);
                                    if ($_n > 1) echo ' <span style="font-size:8pt;color:#888;">(' . number_format(intval($supply)) . '&times;' . $_n . ')</span>';
                                    ?>
                            </tr>
                            <?php endforeach; ?>
                            <!-- 합계 행 -->
                            <tr style="background-color: #f9f9f9; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">공급가액</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;"><?= number_format(round($View_money_4, -1)) ?></td>
                            </tr>
                            <!-- 부가세포함금액 행 추가 (10원 단위 반올림) -->
                            <tr style="background-color: #e9ecef; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; color: #000;">💰 부가세포함</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; color: #000; font-size: 9pt;"><?= number_format(round($View_money_5, -1)) ?> 원</td>
                            </tr>
                            <?php if ($View_logen_delivery_fee > 0 && $View_logen_fee_type === '선불'):
                                $p_shipping_supply = $View_logen_delivery_fee;
                                $p_shipping_vat = round($p_shipping_supply * 0.1);
                                $p_shipping_total = $p_shipping_supply + $p_shipping_vat;
                            ?>
                            <tr style="font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; font-size: 7.5pt;">🚚 택배비 (선불)</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; font-size: 8pt;"><?= number_format($p_shipping_supply) ?>+VAT <?= number_format($p_shipping_vat) ?> = <?= number_format($p_shipping_total) ?></td>
                            </tr>
                            <tr style="background-color: #d6e4f0; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; color: #000; font-size: 8pt;">📦 총 결제금액</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; color: #000; font-size: 9pt;"><?= number_format(round($View_money_5, -1) + $p_shipping_total) ?> 원</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <!-- 🔧 가격 정보 표시 제거됨 - 테이블의 "총 합계" 행에서 이미 표시됨 -->
                </div>

                <!-- 고객정보 + 기타사항 (compact 1줄 레이아웃) -->
                <div style="margin-bottom: 1mm;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; width: 8%; text-align: center;">주소</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm;" colspan="5">[<?= $View_zip ?>] <?= htmlspecialchars($View_zip1) ?> <?= htmlspecialchars($View_zip2) ?></td>
                        </tr>
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; text-align: center;">배송</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm; width: 15%;"><?= htmlspecialchars($View_delivery) ?><?php if ($View_logen_fee_type === '선불'): ?> (<b>선불</b>)<?php else: ?> (착불)<?php endif; ?></td>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; width: 8%; text-align: center;">결제</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm; width: 15%;"><?= htmlspecialchars($View_bank) ?></td>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; width: 8%; text-align: center;">입금자</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm;<?php if ($bankname_mismatch): ?> background: #c0392b; color: #fff; font-weight: bold;<?php endif; ?>"><?= htmlspecialchars($View_bankname) ?><?php if ($bankname_mismatch): ?> ⚠<?php endif; ?></td>
                        </tr>
                        <?php if (!empty($View_bizname)) { ?>
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; text-align: center;">업체</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm;" colspan="5"><?= htmlspecialchars($View_bizname) ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; text-align: center;">비고</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm; font-size: 7pt; line-height: 1.2;" colspan="5"><?= $View_cont_compact ?></td>
                        </tr>
                    </table>
                </div>

                <div class="print-footer">두손기획인쇄 02-2632-1830</div>
            </div>

            <!-- 절취선 -->
            <div class="print-divider"></div>

            <!-- 두 번째 주문서 (직원용) -->
            <div class="print-order employee-copy">
                <div class="print-title">주문서 (직원용)</div>

                <!-- 주요 정보 (compact) -->
                <div style="margin-bottom: 2mm; padding: 1.5mm; border: 0.3pt solid #666;">
                    <div style="display: flex; gap: 2mm; align-items: center; font-size: 11pt; font-weight: bold; line-height: 1.2;">
                        <div style="width: 14ch; flex-shrink: 0;">
                            <span style="color: #000;">주문번호: <?= $View_No ?></span>
                        </div>
                        <div style="width: 18ch; flex-shrink: 0;">
                            <span style="color: #000;">일시: <?= htmlspecialchars($View_date) ?></span>
                        </div>
                        <div style="width: 16ch; flex-shrink: 0;">
                            <span style="color: #000;">T.<?= htmlspecialchars($View_phone) ?></span>
                        </div>
                        <div style="width: 16ch; flex-shrink: 0;">
                            <span style="color: #000;">H.<?= htmlspecialchars($View_Hendphone) ?></span>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <span style="color: #000;">주문자: <?= htmlspecialchars($View_name) ?></span>
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
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 6%;">NO</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 17%;">품 목</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 44%;">규격/옵션</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 11%;">수량</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; width: 9%;">단위</th>
                                <th style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; width: 13%;">공급가액</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row_num = 1;
                            $_spec_seen = [];
                            foreach ($order_rows as $summary_item):
                                $_sk = ($summary_item['Type'] ?? '') . '|' . ($summary_item['money_5'] ?? '');
                                if (isset($_spec_seen[$_sk])) continue;
                                $_spec_seen[$_sk] = true;
                                $_n = $_spec_counts[$_sk] ?? 1;
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
                                            $pt = $summary_item['product_type'] ?? 'namecard';
                                            $parsed_premium = PremiumOptionsConfig::parseSelectedOptions($summary_item['premium_options'], $pt);
                                            foreach ($parsed_premium as $popt) {
                                                $item_options[] = $popt['display'] . ' ' . number_format($popt['price']) . '원';
                                            }
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;"><?= $row_num++ ?></td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;"><?= $item_type_display ?><?php if ($_n > 1) echo ' <span style="color:#e74c3c;font-weight:bold;">&times;' . $_n . '건</span>'; ?></td>
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
                                    // 🔧 2026-01-14: 수량/단위 분리 - 수량 칼럼에 숫자+매수, 단위는 별도 칼럼 (인쇄용)
                                    echo formatQuantityNum($quantity_num);
                                    // ✅ 2026-01-16: 연/권 단위 모두 매수 표시 (전단지, NCR양식지)
                                    if ($mesu_for_display > 0 && in_array($unit, ['연', '권'])) {
                                        echo '<br><span style="font-size: 8pt; color: #1e88ff;">(' . number_format($mesu_for_display) . '매)</span>';
                                    }
                                    ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">
                                    <?= htmlspecialchars($unit) ?>
                                </td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; font-weight: bold;">
                                    <?php
                                    // Phase 3 표준 필드 우선 사용
                                    $supply = !empty($summary_item['price_supply']) ? $summary_item['price_supply'] : $summary_item['money_4'];
                                    echo number_format(intval($supply) * $_n);
                                    if ($_n > 1) echo ' <span style="font-size:8pt;color:#888;">(' . number_format(intval($supply)) . '&times;' . $_n . ')</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <!-- 합계 행 -->
                            <tr style="background-color: #f9f9f9; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center;">공급가액</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right;"><?= number_format(round($View_money_4, -1)) ?></td>
                            </tr>
                            <!-- 부가세포함금액 행 추가 (10원 단위 반올림) -->
                            <tr style="background-color: #e9ecef; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; color: #000;">💰 부가세포함</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; color: #000; font-size: 9pt;"><?= number_format(round($View_money_5, -1)) ?> 원</td>
                            </tr>
                            <?php if ($View_logen_delivery_fee > 0 && $View_logen_fee_type === '선불'):
                                $p_shipping_supply = $View_logen_delivery_fee;
                                $p_shipping_vat = round($p_shipping_supply * 0.1);
                                $p_shipping_total = $p_shipping_supply + $p_shipping_vat;
                            ?>
                            <tr style="font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; font-size: 7.5pt;">🚚 택배비 (선불)</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; font-size: 8pt;"><?= number_format($p_shipping_supply) ?>+VAT <?= number_format($p_shipping_vat) ?> = <?= number_format($p_shipping_total) ?></td>
                            </tr>
                            <tr style="background-color: #d6e4f0; font-weight: bold;">
                                <td colspan="5" style="border: 0.3pt solid #000; padding: 1.5mm; text-align: center; color: #000; font-size: 8pt;">📦 총 결제금액</td>
                                <td style="border: 0.3pt solid #000; padding: 1.5mm; text-align: right; color: #000; font-size: 9pt;"><?= number_format(round($View_money_5, -1) + $p_shipping_total) ?> 원</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <!-- 🔧 가격 정보 표시 제거됨 - 테이블의 "총 합계" 행에서 이미 표시됨 -->
                </div>

                <!-- 고객정보 + 기타사항 (compact 1줄 레이아웃) -->
                <div style="margin-bottom: 1mm;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; width: 8%; text-align: center;">주소</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm;" colspan="5">[<?= $View_zip ?>] <?= htmlspecialchars($View_zip1) ?> <?= htmlspecialchars($View_zip2) ?></td>
                        </tr>
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; text-align: center;">배송</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm; width: 15%;"><?= htmlspecialchars($View_delivery) ?><?php if ($View_logen_fee_type === '선불'): ?> (<b>선불</b>)<?php else: ?> (착불)<?php endif; ?></td>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; width: 8%; text-align: center;">결제</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm; width: 15%;"><?= htmlspecialchars($View_bank) ?></td>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; width: 8%; text-align: center;">입금자</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm;<?php if ($bankname_mismatch): ?> background: #c0392b; color: #fff; font-weight: bold;<?php endif; ?>"><?= htmlspecialchars($View_bankname) ?><?php if ($bankname_mismatch): ?> ⚠<?php endif; ?></td>
                        </tr>
                        <?php if (!empty($View_bizname)) { ?>
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; text-align: center;">업체</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm;" colspan="5"><?= htmlspecialchars($View_bizname) ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th style="border: 0.3pt solid #000; background: #f0f0f0; padding: 1mm 2mm; text-align: center;">비고</th>
                            <td style="border: 0.3pt solid #000; padding: 1mm 2mm; font-size: 7pt; line-height: 1.2;" colspan="5"><?= $View_cont_compact ?></td>
                        </tr>
                    </table>
                </div>

                <div class="print-footer">두손기획인쇄 02-2632-1830</div>
            </div>
        </div>
    </div>

    <!-- 화면 표시용 내용 (엑셀 스타일 리디자인 2026-01-03) -->
    <div class="screen-only">
        <div class="admin-container" style="width: 700px; max-width: 100%; margin: 0 auto; padding: 15px; background: #fff; box-sizing: border-box;">

            <!-- ===== 주문 기본 정보 테이블 ===== -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                <tr style="background: linear-gradient(135deg, #2c3e50, #34495e);">
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
                            case '11': echo '<span style="color: #dc3545; font-weight: bold;">카드결제</span>'; break;
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
                <table id="order-products-table" style="width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                    <!-- 🎯 colgroup으로 컬럼 폭 재조정 (규격/옵션 확대, 공급가액 축소) -->
                    <colgroup>
                        <col style="width: 6%;">
                        <col style="width: 17%;">
                        <col style="width: 44%;">
                        <col style="width: 11%;">
                        <col style="width: 9%;">
                        <col style="width: 13%;">
                    </colgroup>
                    <tr style="background: linear-gradient(135deg, #2c3e50, #34495e);">
                        <td colspan="6" style="padding: 10px 15px; color: #fff; font-size: 14px; font-weight: bold;">
                            주문 상품 정보
                        </td>
                    </tr>
                    <?php
                    if (empty($order_rows) || !is_array($order_rows)) {
                        echo "<tr><td colspan='6' style='padding: 15px; color: #dc3545; background: #fff3cd;'>";
                        echo "주문 데이터를 불러올 수 없습니다. (주문번호: " . htmlspecialchars($View_No ?? 'N/A') . ")";
                        echo "</td></tr>";
                    } else {
                    ?>
                    <tr style="background: #E0E0E0;">
                        <th style="border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center; width: 6%;">NO</th>
                        <th style="border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center; width: 17%;">품목</th>
                        <th style="border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center; width: 44%;">규격/옵션</th>
                        <th style="border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center; width: 11%;">수량</th>
                        <th style="border: 1px solid #999; padding: 8px; font-size: 11px; text-align: center; width: 9%;">단위</th>
                        <th style="border: 1px solid #999; padding: 8px; font-size: 11px; text-align: right; width: 13%;">공급가액</th>
                    </tr>
                    <?php
                                        // 각 주문 아이템을 표의 행으로 표시
                                        $row_num = 1;
                                        $_spec_seen = [];
                                        foreach ($order_rows as $summary_item):
                                            $_sk = ($summary_item['Type'] ?? '') . '|' . ($summary_item['money_5'] ?? '');
                                            if (isset($_spec_seen[$_sk])) continue;
                                            $_spec_seen[$_sk] = true;
                                            $_n = $_spec_counts[$_sk] ?? 1;
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

                                            // 5. Premium options (Config 기반 — 옵션 추가 시 자동 반영)
                                            if (!empty($summary_item['premium_options'])) {
                                                $pt = $summary_item['product_type'] ?? 'namecard';
                                                $parsed_premium_opts = PremiumOptionsConfig::parseSelectedOptions($summary_item['premium_options'], $pt);
                                                foreach ($parsed_premium_opts as $popt) {
                                                    $item_options[] = htmlspecialchars($popt['display']) . ' ' . number_format($popt['price']) . '원';
                                                }
                                            }

                                            // 금액 (인쇄비, 공급가액)
                                            $printing_cost = intval($summary_item['money_4'] ?? 0);
                                            $supply_price = $printing_cost; // 공급가액 = 인쇄비

                                            // 수량 표시 포맷 (formatQuantityNum 사용)
                                            $quantity_display = formatQuantityNum($quantity_num);

                                            // 🔧 전단지인 경우 매수 정보 2줄 표시: "0.5" + "(2,000매)", 단위는 별도 칼럼
                                            if ($is_flyer && !empty($mesu_for_display) && $mesu_for_display > 0) {
                                                if ($quantity_display === '-') $quantity_display = '0';
                                                $quantity_display .= '<br><span style="font-size: 10px; color: #1e88ff;">(' . number_format($mesu_for_display) . '매)</span>';
                                                // 단위 칼럼 유지 (비우지 않음)
                                            }
                                            $unit_display = !empty($unit) ? htmlspecialchars($unit) : '';

                                            ?>
                    <tr>
                        <td style="border: 1px solid #999; padding: 6px; text-align: center; font-size: 11px;"><?= $row_num++ ?></td>
                        <td style="border: 1px solid #999; padding: 6px; text-align: center; font-size: 12px; font-weight: bold; color: #2F5496;"><?= htmlspecialchars($product_type_kr) ?><?php if ($_n > 1) echo ' <span style="color:#e74c3c;font-weight:bold;">&times;' . $_n . '건</span>'; ?></td>
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
                        <td style="border: 1px solid #999; padding: 6px; text-align: right; font-size: 11px; font-weight: bold;"><?= number_format($supply_price * $_n) ?><?php if ($_n > 1) echo ' <span style="font-size:10px;color:#888;">(' . number_format($supply_price) . '&times;' . $_n . ')</span>'; ?></td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                </table>
                <?php } // end if (!empty($order_rows)) ?>

                <!-- ===== 가격 정보 테이블 ===== -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                    <tr style="background: linear-gradient(135deg, #2c3e50, #34495e);">
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
                                            $total_money_2 += intval($row['money_2']); // 디자인비 (참고용, money_4에 이미 포함)
                                            $total_money_4 += intval($row['money_4']); // 공급가액 (money_1+money_2 포함)

                                            // ✅ 부가세 계산: money_3가 0이면 money_5에서 역산 (레거시 데이터 처리)
                                            $item_vat = intval($row['money_3']);
                                            if ($item_vat == 0 && $row['money_5'] > 0) {
                                                // money_3가 저장되지 않은 경우, money_5에서 VAT 추출
                                                // ✅ 2026-01-18: money_4는 이미 공급가액 (money_1+money_2 포함), money_2 중복 추가 버그 수정
                                                $supply_price = intval($row['money_4']) + $additionalOptionsTotal;
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
                        <!-- ✅ 2026-01-18: money_4는 이미 공급가액 (money_1+money_2 포함), money_2 중복 추가 버그 수정 -->
                        <td style="width: 70%; border: 1px solid #999; padding: 8px 10px; font-size: 13px; text-align: right; font-weight: bold;"><?= number_format(round($total_money_4 + $grand_additional_options_total, -1)) ?> 원</td>
                    </tr>
                    <tr style="background: #FFF2CC;">
                        <th style="width: 30%; background: #2c3e50; border: 1px solid #999; padding: 10px; font-size: 13px; text-align: center; color: #fff;">부가세포함금액</th>
                        <td style="width: 70%; border: 1px solid #999; padding: 10px; font-size: 15px; text-align: right; font-weight: bold; color: #C00000;"><?= number_format(round($total_money_5, -1)) ?> 원</td>
                    </tr>
                    <?php if ($View_logen_delivery_fee > 0 && $View_logen_fee_type === '선불'):
                        $shipping_supply = $View_logen_delivery_fee; // 공급가액
                        $shipping_vat = round($shipping_supply * 0.1); // 부가세
                        $shipping_total = $shipping_supply + $shipping_vat; // 부가세포함
                    ?>
                    <tr>
                        <th style="width: 30%; background: #E8F0FE; border: 1px solid #999; padding: 8px 10px; font-size: 12px; text-align: center;">🚚 택배비 (선불)</th>
                        <td style="width: 70%; border: 1px solid #999; padding: 8px 10px; font-size: 13px; text-align: right; font-weight: bold;"><?= number_format($shipping_supply) ?> + VAT <?= number_format($shipping_vat) ?> = <?= number_format($shipping_total) ?> 원</td>
                    </tr>
                    <tr style="background: #DAEAF6;">
                        <th style="width: 30%; background: #1a3a5c; border: 1px solid #999; padding: 10px; font-size: 13px; text-align: center; color: #fff;">총 결제금액</th>
                        <td style="width: 70%; border: 1px solid #999; padding: 10px; font-size: 16px; text-align: right; font-weight: bold; color: #1a3a5c;"><?= number_format(round($total_money_5, -1) + $shipping_total) ?> 원</td>
                    </tr>
                    <?php endif; ?>
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
                                case '11': echo '<span style="color: #dc3545; font-weight: bold;">카드결제</span>'; break;
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
                            echo "<tr style='background: linear-gradient(135deg, #2c3e50, #34495e);'>";
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
                                echo "<a href='/" . htmlspecialchars($filePath) . "' download='" . htmlspecialchars($file) . "' style='padding: 4px 10px; background: #007bff; color: white; text-decoration: none; font-size: 10px; font-weight: bold; border-radius: 4px;'>다운로드</a>";
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
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333;">
                    <colgroup>
                        <col style="width: 10%;">
                        <col style="width: 40%;">
                        <col style="width: 10%;">
                        <col style="width: 40%;">
                    </colgroup>
                    <tr style="background: linear-gradient(135deg, #2c3e50, #34495e);">
                        <td colspan="4" style="padding: 10px 15px; color: #fff; font-size: 14px; font-weight: bold;">
                            신청자 정보
                        </td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 6px; font-size: 11px; text-align: center; white-space: nowrap;">성명/상호</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="name" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_name ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 6px; font-size: 11px; text-align: center; white-space: nowrap;">E-MAIL</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="email" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_email ?>'></td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">우편번호</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input type="text" name="zip" style="width: 80px; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_zip ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">전화번호</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="phone" type="tel" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_phone ?>'></td>
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
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="Hendphone" type="tel" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_Hendphone ?>'></td>
                    </tr>
                    <tr>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">사업자명</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input type="text" name="bizname" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_bizname ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">입금은행</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><input name="bank" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px;" value='<?= $View_bank ?>'></td>
                    </tr>
                    <tr>
                        <th style="background: <?= $bankname_mismatch ? '#c0392b' : '#E0E0E0' ?>; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;<?= $bankname_mismatch ? ' color: #fff; font-weight: bold;' : '' ?>">입금자명<?= $bankname_mismatch ? ' ⚠' : '' ?></th>
                        <td style="border: 1px solid #999; padding: 4px 8px;<?= $bankname_mismatch ? ' background: #fff0f0;' : '' ?>"><input name="bankname" type="text" style="width: 100%; box-sizing: border-box; border: 1px solid <?= $bankname_mismatch ? '#c0392b' : '#ccc' ?>; padding: 4px 6px; font-size: 12px;<?= $bankname_mismatch ? ' color: #c0392b; font-weight: bold;' : '' ?>" value='<?= $View_bankname ?>'></td>
                        <th style="background: #E0E0E0; border: 1px solid #999; padding: 6px 10px; font-size: 11px; text-align: center;">비고</th>
                        <td style="border: 1px solid #999; padding: 4px 8px;"><textarea name="cont" rows="2" style="width: 100%; box-sizing: border-box; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; resize: vertical;"><?= $View_cont ?></textarea></td>
                    </tr>
                </table>

                <?php
                // ===== 📦 택배비 추정 섹션 (배송지가 "택배"인 경우만) =====
                $is_delivery_parcel = (mb_strpos($View_delivery, '택배') !== false);
                if ($is_delivery_parcel && !empty($order_rows)):
                    // ShippingCalculator로 각 주문 아이템의 배송 추정
                    $shipping_items = [];
                    foreach ($order_rows as $ship_item) {
                        $shipping_items[] = ShippingCalculator::estimateFromOrder($ship_item);
                    }
                    // 합산
                    $ship_total_boxes = 0;
                    $ship_total_weight_kg = 0;
                    $ship_total_fee = 0;
                    foreach ($shipping_items as $si) {
                        $ship_total_boxes += $si['boxes'];
                        $ship_total_weight_kg += $si['weight_kg'];
                        $ship_total_fee += $si['fee'];
                    }
                ?>
                <div style="background: #f0f7ff; border: 1px solid #b8d4f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                        <span style="font-size: 1.1rem;">📦</span>
                        <span style="font-weight: 700; color: #1E4E79; font-size: 14px;">배송 정보</span>
                        <span style="background: #e0a800; color: #fff; font-size: 11px; padding: 1px 8px; border-radius: 3px; font-weight: 600;">추정</span>
                    </div>
                    <div style="font-size: 13px; color: #333; line-height: 1.8;">
                        <span style="font-weight: 600;">예상 무게:</span>
                        <span style="font-weight: 700; font-size: 14px; color: #1E4E79;"><?php if ($ship_total_weight_kg <= 3): ?><?= number_format($ship_total_weight_kg, 1) ?>kg 이하<?php else: ?>약 <?= number_format($ship_total_weight_kg, 1) ?>kg<?php endif; ?></span>
                        <span style="font-size: 11px; color: #888;">(부자재 포함)</span>
                        <?php if (count($shipping_items) > 1): ?>
                        <div style="margin-top: 4px; font-size: 11px; color: #666;">
                            <?php foreach ($shipping_items as $idx => $si): ?>
                            <span style="margin-right: 12px;"><?= $idx + 1 ?>번 품목: <?php if ($si['weight_kg'] <= 3): ?><?= number_format($si['weight_kg'], 1) ?>kg 이하<?php else: ?>약 <?= number_format($si['weight_kg'], 1) ?>kg<?php endif; ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 6px; font-size: 11px; color: #888;">
                        ※ 추정치이며 실제와 다를 수 있습니다.
                    </div>
                </div>

                <?php if (!empty($group_orders)): ?>
                <!-- ===== 그룹 주문 안내 ===== -->
                <div style="background: #fff5eb; border: 1px solid #f0c878; border-radius: 8px; padding: 14px 16px; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                        <span style="font-size: 1.1rem;">📦</span>
                        <span style="font-weight: 700; color: #b45309; font-size: 14px;">그룹 주문 (<?= count($group_orders) + 1 ?>개 품목)</span>
                    </div>
                    <div style="font-size: 12px; color: #333; line-height: 1.8;">
                        <?php
                        $PRODUCT_NAME_MAP = [
                            'sticker' => '스티커', 'inserted' => '전단지', 'namecard' => '명함',
                            'envelope' => '봉투', 'littleprint' => '포스터', 'merchandisebond' => '상품권',
                            'msticker' => '자석스티커', 'cadarok' => '카다록', 'ncrflambeau' => 'NCR양식지'
                        ];
                        // 현재 주문 표시
                        $current_label = $PRODUCT_NAME_MAP[$row['product_type'] ?? ''] ?? htmlspecialchars($row['Type']);
                        ?>
                        <div style="margin-bottom: 4px;">
                            <span style="font-weight: 600; color: #1E4E79;">#<?= $no ?></span>
                            <span style="font-weight: 600;"><?= $current_label ?></span>
                            <span style="color: #888;"><?= number_format(intval($row['money_5'] ?? 0)) ?>원</span>
                            <span style="background: #1E4E79; color: #fff; font-size: 10px; padding: 1px 6px; border-radius: 3px; margin-left: 4px;">현재</span>
                        </div>
                        <?php foreach ($group_orders as $go):
                            $go_label = $PRODUCT_NAME_MAP[$go['product_type'] ?? ''] ?? htmlspecialchars($go['Type']);
                        ?>
                        <div>
                            <span style="font-weight: 600; color: #1E4E79;">#<?= $go['no'] ?></span>
                            <span><?= $go_label ?></span>
                            <span style="color: #888;"><?= number_format(intval($go['money_5'] ?? 0)) ?>원</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 8px; padding: 6px 10px; background: #fef3cd; border-radius: 4px; font-size: 11px; color: #856404;">
                        ⚠️ 택배비 저장 시 그룹 전체 품목에 일괄 적용됩니다.
                    </div>
                </div>
                <?php endif; ?>
                <!-- ===== 📦 택배비 확정 / 송장번호 입력 ===== -->
                <div id="logen-confirm-section" style="background: <?= $has_logen_confirmed ? '#f0faf0' : '#fff8e8' ?>; border: 1px solid <?= $has_logen_confirmed ? '#a8d5a8' : '#e0c880' ?>; border-radius: 8px; padding: 14px 16px; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                        <span style="font-size: 1.1rem;"><?= $has_logen_confirmed ? '✅' : '📝' ?></span>
                        <span style="font-weight: 700; color: #1E4E79; font-size: 14px;">택배비 확정 / 송장번호</span>
                        <?php if ($has_logen_confirmed): ?>
                            <span style="background: #28a745; color: #fff; font-size: 11px; padding: 1px 8px; border-radius: 3px; font-weight: 600;">확정</span>
                        <?php else: ?>
                            <span style="background: #e0a800; color: #fff; font-size: 11px; padding: 1px 8px; border-radius: 3px; font-weight: 600;">미확정</span>
                        <?php endif; ?>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 6px 10px; background: #f5f7fa; font-weight: 600; color: #333; width: 22%; text-align: center;">확정 박스수</td>
                            <td style="border: 1px solid #ccc; padding: 6px 10px; width: 28%;">
                                <input type="number" id="logen_box_qty" value="<?= $View_logen_box_qty ?: '' ?>" min="0" placeholder="-" style="width: 80px; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; text-align: center; border-radius: 3px;">
                                <span style="font-size: 11px; color: #888; margin-left: 4px;">박스</span>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 6px 10px; background: #f5f7fa; font-weight: 600; color: #333; width: 22%; text-align: center;">확정 택배비</td>
                            <td style="border: 1px solid #ccc; padding: 6px 10px; width: 28%;">
                                <input type="number" id="logen_delivery_fee" value="<?= $View_logen_delivery_fee ?: '' ?>" min="0" step="500" placeholder="-" style="width: 100px; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; text-align: right; border-radius: 3px;">
                                <span style="font-size: 11px; color: #888; margin-left: 4px;">원</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 6px 10px; background: #f5f7fa; font-weight: 600; color: #333; text-align: center;">요금 구분</td>
                            <td style="border: 1px solid #ccc; padding: 6px 10px;">
                                <select id="logen_fee_type" style="border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; border-radius: 3px;">
                                    <option value="선불" <?= $View_logen_fee_type === '선불' ? 'selected' : '' ?>>선불</option>
                                    <option value="착불" <?= ($View_logen_fee_type === '착불' || empty($View_logen_fee_type)) ? 'selected' : '' ?>>착불</option>
                                </select>
                            </td>
                            <td style="border: 1px solid #ccc; padding: 6px 10px; background: #f5f7fa; font-weight: 600; color: #333; text-align: center;">송장번호</td>
                            <td style="border: 1px solid #ccc; padding: 6px 10px;">
                                <input type="text" id="logen_tracking_no" value="<?= $View_logen_tracking_no ?>" placeholder="송장번호 입력" style="width: 160px; border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; border-radius: 3px;">
                            </td>
                        </tr>
                        <?php if (!empty($View_shipping_bundle_type)): ?>
                        <tr>
                            <td style="border: 1px solid #ccc; padding: 6px 10px; background: #f5f7fa; font-weight: 600; color: #333; text-align: center;">배송방식</td>
                            <td colspan="3" style="border: 1px solid #ccc; padding: 6px 10px;">
                                <?php if ($View_shipping_bundle_type === 'bundle'): ?>
                                    <span style="background: #1E4E79; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 3px; font-weight: 600;">묶음배송</span>
                                    <span style="font-size: 11px; color: #666; margin-left: 6px;">전체 무게 합산 기준 박스 산정</span>
                                <?php else: ?>
                                    <span style="background: #6c757d; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 3px; font-weight: 600;">개별포장</span>
                                    <span style="font-size: 11px; color: #666; margin-left: 6px;">품목별 각각 포장</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                        <button type="button" id="btn-logen-save" onclick="saveLogenInfo()" style="padding: 6px 20px; font-size: 12px; background: linear-gradient(135deg, #1E4E79, #2a6496); color: #fff; border: none; cursor: pointer; font-weight: 600; border-radius: 5px;">💾 저장</button>
                        <span id="logen-save-result" style="font-size: 12px; color: #28a745; display: none;">✅ 저장되었습니다.</span>
                    </div>
                </div>
                <?php endif; // end 택배비 추정 ?>

                <!-- ✅ 첨부 파일 섹션 (admin.php에서 전달) -->
                <?php if (isset($GLOBALS['file_section_html']) && !empty($GLOBALS['file_section_html'])): ?>
                    <?php echo $GLOBALS['file_section_html']; ?>
                <?php endif; ?>

                <!-- ===== 관리자 버튼 ===== -->
                <div style="margin-top: 15px; text-align: center; padding: 15px; background: #f5f5f5; border: 1px solid #ddd;">
                    <?php if ($no) { ?>
                        <button type="submit" style="padding: 10px 25px; font-size: 13px; margin-right: 10px; background: linear-gradient(135deg, #007bff, #0056b3); color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 6px;">정보 수정</button>
                        <button type="button" onclick="printOrder();" style="padding: 10px 25px; font-size: 13px; margin-right: 10px; background: linear-gradient(135deg, #28a745, #1e7e34); color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 6px;">주문서 출력</button>
                        <button type="button" onclick="reOrder(<?php echo $no; ?>);" style="padding: 10px 25px; font-size: 13px; margin-right: 10px; background: linear-gradient(135deg, #ff9800, #e68900); color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 6px;">재주문</button>
                    <?php } ?>
                    <button type="button" onclick="window.close();" style="padding: 10px 25px; font-size: 13px; background: linear-gradient(135deg, #6c757d, #495057); color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 6px;">창 닫기</button>
                </div>

                <?php } // end if ($no) - line 1429에서 열린 블록 종료 ?>

                </form>
                </table>
            </div> <!-- admin-content 종료 -->
        </div> <!-- admin-container 종료 -->
    </div> <!-- screen-only 종료 -->

    <!-- 이미지 라이트박스 (클릭하면 닫힘) -->
    <div id="imgLightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; cursor:pointer; justify-content:center; align-items:center;" onclick="closeLightbox()">
        <img id="lightboxImg" src="" style="max-width:90%; max-height:90%; object-fit:contain; border-radius:4px; box-shadow:0 4px 30px rgba(0,0,0,0.5);">
        <div style="position:absolute; top:15px; right:20px; color:#fff; font-size:14px; opacity:0.7;">클릭하면 닫힙니다 ✕</div>
    </div>
    <script>
    function openLightbox(src) {
        var lb = document.getElementById('imgLightbox');
        document.getElementById('lightboxImg').src = decodeURIComponent(src) + '?raw';
        lb.style.display = 'flex';
    }
    function closeLightbox() {
        var lb = document.getElementById('imgLightbox');
        lb.style.display = 'none';
        document.getElementById('lightboxImg').src = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
    </script>

    <!-- 전화번호 자동 포맷팅 (010-1234-5678) -->
    <script>
    (function() {
        function formatKoreanPhone(v) {
            var d = v.replace(/\D/g, '');
            if (d.length === 0) return '';
            if (d.substring(0, 2) === '02') {
                if (d.length <= 2) return d;
                if (d.length <= 5) return d.substring(0,2) + '-' + d.substring(2);
                if (d.length <= 9) return d.substring(0,2) + '-' + d.substring(2, d.length-4) + '-' + d.substring(d.length-4);
                return d.substring(0,2) + '-' + d.substring(2,6) + '-' + d.substring(6,10);
            }
            if (d.length <= 3) return d;
            if (d.length <= 7) return d.substring(0,3) + '-' + d.substring(3);
            if (d.length <= 11) return d.substring(0,3) + '-' + d.substring(3, d.length-4) + '-' + d.substring(d.length-4);
            return d.substring(0,3) + '-' + d.substring(3,7) + '-' + d.substring(7,11);
        }
        function applyPhoneFormat(input) {
            input.addEventListener('input', function() {
                var pos = this.selectionStart;
                var before = this.value;
                var formatted = formatKoreanPhone(before);
                if (formatted !== before) {
                    this.value = formatted;
                    var diff = formatted.length - before.length;
                    this.setSelectionRange(pos + diff, pos + diff);
                }
            });
            if (input.value && /^\d{9,11}$/.test(input.value.replace(/\D/g, ''))) {
                input.value = formatKoreanPhone(input.value);
            }
        }
        document.querySelectorAll('input[type="tel"], input[name="phone"], input[name="Hendphone"]').forEach(applyPhoneFormat);
    })();
    </script>

</body>
</html>
