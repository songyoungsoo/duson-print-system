<?php
session_start();
$session_id = session_id();

// 경로 수정: MlangPrintAuto/shop/에서 루트의 db.php 접근
include "../../db.php";
include "../../includes/AdditionalOptionsDisplay.php";
include "../../includes/quantity_formatter.php";
$connect = $db; // db.php에서 $db 변수 사용

// --- REFACTOR ---
// Guideline: Admin Configuration for Leaflet Display
// TODO: Load this setting from a site_config DB table.
$LEAFLET_DISPLAY_STYLE = 'Y'; // 'Y' = show sheet count, 'N' = hide.
// --- END REFACTOR ---

error_log("Attempting to connect to database");

// UTF-8 설정과 연결 확인
if ($connect) {
    error_log("Database connection successful");
    if (!mysqli_set_charset($connect, 'utf8')) {
        error_log("Error setting UTF-8 charset: " . mysqli_error($connect));
    }
}

// ID로 한글명 가져오기 함수
function getKoreanName($connect, $id)
{
    if (!$connect || !$id) {
        return $id; // 연결이 없거나 ID가 없으면 원본 반환
    }

    // ID가 이미 한글이면 그대로 반환
    if (preg_match('/[가-힣]/u', $id)) {
        return $id;
    }

    // 숫자와 문자열 모두 처리
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? OR title = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        // 쿼리 실패시 로그
        error_log("getKoreanName prepare failed: " . mysqli_error($connect));
        return $id;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $id, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }

    mysqli_stmt_close($stmt);

    // 못 찾으면 로그 남기고 원본 반환
    error_log("getKoreanName: No match found for ID: " . $id);
    return $id; // 찾지 못하면 원본 ID 반환
}

/**
 * Get formatted sticker specifications for cart display
 * @param array $item Cart item data
 * @return array Array of specification strings ready for display
 */
function getStickerSpecs($item) {
    $specs = [];

    // Material (재질)
    if (!empty($item['jong'])) {
        $material = preg_replace('/^(jil|jsp|jka|cka)\s+/', '', $item['jong']);
        $specs[] = '재질: ' . htmlspecialchars($material);
    }

    // Size (크기)
    if (!empty($item['garo']) && !empty($item['sero'])) {
        $specs[] = '크기: ' . htmlspecialchars($item['garo']) . ' × ' . htmlspecialchars($item['sero']) . 'mm';
    }

    // Shape (모양)
    if (!empty($item['domusong'])) {
        $shape_parts = explode(' ', $item['domusong'], 2);
        $shape_name = isset($shape_parts[1]) ? $shape_parts[1] : $item['domusong'];
        $specs[] = '모양: ' . htmlspecialchars($shape_name);
    }

    // Edit type (편집) - Only if not 0
    if (!empty($item['uhyung']) && $item['uhyung'] != '0') {
        $edit_types = ['10000' => '기본편집', '30000' => '고급편집'];
        $edit_label = $edit_types[$item['uhyung']] ?? htmlspecialchars($item['uhyung']) . '원';
        $specs[] = '편집: ' . $edit_label;
    }

    return $specs;
}

/**
 * Get msticker (자석스티커) specifications for display
 * Msticker uses different field names than regular sticker
 * @param array $item Cart item data
 * @return array Array of specification strings ready for display
 */
function getMstickerSpecs($item) {
    global $connect; // DB 연결 사용
    $specs = [];

    // Type (종류) - MY_type field
    if (!empty($item['MY_type'])) {
        $type_name = getKoreanName($connect, $item['MY_type']);
        $specs[] = '종류: ' . htmlspecialchars($type_name);
    }

    // Specification/Size (규격) - Section field
    if (!empty($item['Section'])) {
        $section_name = getKoreanName($connect, $item['Section']);
        $specs[] = '규격: ' . htmlspecialchars($section_name);
    }

    // Print type (인쇄) - POtype field
    if (!empty($item['POtype'])) {
        $print_types = ['1' => '단면', '2' => '양면'];
        $print_label = $print_types[$item['POtype']] ?? htmlspecialchars($item['POtype']);
        $specs[] = '인쇄: ' . $print_label;
    }

    // Quantity (수량) - MY_amount field
    if (!empty($item['MY_amount'])) {
        $specs[] = '수량: ' . number_format(intval($item['MY_amount'])) . '매';
    }

    return $specs;
}

// 장바구니 내용 가져오기 (통합 버전)
function getCartItems($connect, $session_id)
{
    if (!$connect) {
        error_log("Database connection failed");
        return false;
    }

    $items = [];

    // shop_temp 테이블에서 모든 상품 데이터 가져오기
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            // 🆕 JSON 방식 추가 옵션 파싱 (전단지/카다록/포스터)
            if (!empty($row['additional_options'])) {
                $additional_options = json_decode($row['additional_options'], true);
                if ($additional_options && is_array($additional_options)) {
                    // JSON 데이터를 개별 필드로 변환하여 기존 코드와 호환
                    $row['coating_enabled'] = $additional_options['coating_enabled'] ?? 0;
                    $row['coating_type'] = $additional_options['coating_type'] ?? '';
                    $row['coating_price'] = $additional_options['coating_price'] ?? 0;
                    $row['folding_enabled'] = $additional_options['folding_enabled'] ?? 0;
                    $row['folding_type'] = $additional_options['folding_type'] ?? '';
                    $row['folding_price'] = $additional_options['folding_price'] ?? 0;
                    $row['creasing_enabled'] = $additional_options['creasing_enabled'] ?? 0;
                    $row['creasing_lines'] = $additional_options['creasing_lines'] ?? 0;
                    $row['creasing_price'] = $additional_options['creasing_price'] ?? 0;
                }
            }

            $items[] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    // 임시로 배열을 전역 변수에 저장하여 mysqli_fetch_assoc처럼 사용
    global $cart_items_array;
    $cart_items_array = $items;

    // 빈 장바구니도 정상으로 처리
    return true; // 성공 표시
}

// 장바구니 아이템 삭제 (통합 버전)
if (isset($_GET['delete'])) {
    $item_no = $_GET['delete'];

    if (is_numeric($item_no)) {
        $delete_query = "DELETE FROM shop_temp WHERE no = ? AND session_id = ?";
        $stmt = mysqli_prepare($connect, $delete_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'is', $item_no, $session_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: cart.php');
    exit;
}

// 장바구니 비우기 (통합 버전)
if (isset($_GET['clear'])) {
    $clear_query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $clear_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: cart.php');
    exit;
}

error_log("Starting to get cart items for session: " . $session_id);

$cart_result = getCartItems($connect, $session_id);
$cart_items = [];
$optionsDisplay = getAdditionalOptionsDisplay($connect);

if ($cart_result === false) {
    $error_message = "장바구니 정보를 불러오는데 실패했습니다. ";
    if ($connect) {
        $error_message .= "DB 오류: " . mysqli_error($connect);
    } else {
        $error_message .= "데이터베이스 연결 실패";
    }
    error_log($error_message);
    echo "<script>alert('" . addslashes($error_message) . "');</script>";
} else {
    // 전역 변수에서 아이템 가져오기
    global $cart_items_array;
    $cart_items = $cart_items_array ?? [];
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>통합 장바구니 - 두손기획인쇄</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- 헤더 스타일 (header-ui.php용) -->
    <link rel="stylesheet" href="../../css/common-styles.css">

    <link rel="stylesheet" href="../../css/excel-unified-style.css">
</head>

<body>
    <?php include "../../includes/header-ui.php"; ?>

    <div class="cart-container">
        <!-- 헤더 섹션 -->
        <div class="cart-hero">
            <h1>통합 장바구니</h1>
            <p>모든 인쇄 상품을 한 번에 주문하세요</p>
        </div>

        <!-- 통합 네비게이션 사용 -->
        <?php if (!empty($cart_items)): ?>
            <!-- 장바구니에 상품이 있을 때 -->
            <div class="cart-nav-wrapper">
                <?php include "../../includes/nav.php"; ?>
            </div>
    </div>
<?php else: ?>
    <!-- 빈 장바구니일 때 - 더 유용한 정보와 기능 제공 -->
    <!-- 품목 네비게이션 -->
    <?php include '../../includes/nav.php'; ?>

    <div style="margin-bottom: 5px;"></div>
<?php endif; ?>

<!-- 장바구니 메인 콘텐츠 -->
<div id="cartContent" style="font-family: 'Noto Sans KR', sans-serif; font-size: 13px; color: #222; line-height: 1.6; box-sizing: border-box; max-width: 1145px; margin: 0 auto; background: #fff; padding: 20px; margin-bottom: 1rem;">
    <?php if (!empty($cart_items)): ?>
        <form method="post" action="../../mlangorder_printauto/OnlineOrder_unified.php" id="orderForm">
            <input type="hidden" name="SubmitMode" value="OrderOne">
            <?php
            $total_price = 0;
            $total_vat = 0;
            $items_data = array();
            ?>

            <!-- Excel 스타일 표 형식 장바구니 (5컬럼 + 관리) -->
            <div class="excel-cart-table-wrapper">
                <table class="excel-cart-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 15%;"><!-- 품목 -->
                        <col style="width: 42%;"><!-- 규격/옵션 -->
                        <col style="width: 10%;"><!-- 수량 -->
                        <col style="width: 8%;"><!-- 단위 -->
                        <col style="width: 15%;"><!-- 총액 -->
                        <col style="width: 10%;"><!-- 관리 -->
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">품목</th>
                            <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">규격/옵션</th>
                            <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">수량</th>
                            <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">단위</th>
                            <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">공급가액</th>
                            <th style="border: 1px solid #ccc; padding: 10px; background: #f3f3f3; text-align: center; font-weight: bold;">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $index => $item):
                            // 추가 옵션 가격 계산
                            $base_price = intval($item['st_price']);
                            $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $item);
                            $final_price = $price_with_options['total_price'];
                            $final_price_vat = $price_with_options['total_vat'];

                            $total_price += $final_price;
                            $total_vat += $final_price_vat;
                            $items_data[] = $item;

                            // 상품명 매핑
                            $product_info = [
                                'cadarok' => ['name' => '카달로그', 'icon' => ''],
                                'sticker' => ['name' => '스티커', 'icon' => ''],
                                'msticker' => ['name' => '자석스티커', 'icon' => ''],
                                'leaflet' => ['name' => '전단지', 'icon' => ''],
                                'inserted' => ['name' => '전단지', 'icon' => ''],
                                'namecard' => ['name' => '명함', 'icon' => ''],
                                'envelope' => ['name' => '봉투', 'icon' => ''],
                                'merchandisebond' => ['name' => '상품권', 'icon' => ''],
                                'littleprint' => ['name' => '포스터', 'icon' => ''],
                                'poster' => ['name' => '포스터', 'icon' => '']
                            ];

                            $product = $product_info[$item['product_type']] ?? ['name' => '상품', 'icon' => ''];

                            // --- REFACTOR: Prepare variables for new amount display ---
                            $is_flyer = in_array($item['product_type'], ['inserted', 'leaflet']);
                            // FIX: 전단지는 flyer_mesu 컬럼 사용 (mesu는 스티커용)
                            $show_sheet_count = ($is_flyer && $LEAFLET_DISPLAY_STYLE === 'Y' && !empty($item['flyer_mesu']));
                            
                            $main_amount_val = 1;
                            $main_amount_display = '1';
                            $unit = '매'; // Default unit
                            $sub_amount = null;

                            if ($is_flyer) {
                                $unit = '연';
                                $main_amount_val = !empty($item['MY_amount']) ? floatval($item['MY_amount']) : 1;
                                // 0.5만 소수점, 나머지 정수 (formatQuantityValue 사용)
                                $main_amount_display = formatQuantityValue($main_amount_val, 'inserted');
                                // FIX: 전단지는 flyer_mesu 컬럼에서 매수 읽기
                                $sub_amount = $item['flyer_mesu'] ?? null;
                            } else {
                                // Other products - 정수로만 표시
                                $main_amount_val = !empty($item['mesu']) ? intval($item['mesu']) : (!empty($item['MY_amount']) ? intval($item['MY_amount']) : 1);
                                $main_amount_display = formatQuantityValue($main_amount_val, $item['product_type']);

                                if ($item['product_type'] == 'ncrflambeau') $unit = '권';
                                elseif ($item['product_type'] == 'cadarok') $unit = '부';
                            }
                            // --- END REFACTOR ---
                        ?>
                            <tr>
                                <!-- 상품정보 -->
                                <td>
                                    <div class="product-info-cell">
                                        <div class="product-icon <?php echo $item['product_type']; ?>">
                                            <?php echo $product['icon']; ?>
                                        </div>
                                        <div>
                                            <div class="product-name"><?php echo $product['name']; ?></div>
                                            <div class="product-number">#<?php echo $item['no']; ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 규격/옵션 (2줄 슬래시 방식) -->
                                <td>
                                    <div class="specs-cell">
                                        <?php
                                        // 2줄 슬래시 형식: 1줄(규격) + 2줄(옵션)
                                        $line1_parts = [];
                                        $line2_parts = [];

                                        switch ($item['product_type']) {
                                            case 'sticker':
                                                // 규격: 종류 / 용지 / 규격
                                                if (!empty($item['jong'])) {
                                                    $material = preg_replace('/^(jil|jsp|jka|cka)\s+/', '', $item['jong']);
                                                    $line1_parts[] = htmlspecialchars($material);
                                                }
                                                if (!empty($item['garo']) && !empty($item['sero'])) {
                                                    $line1_parts[] = htmlspecialchars($item['garo']) . '×' . htmlspecialchars($item['sero']) . 'mm';
                                                }
                                                if (!empty($item['domusong'])) {
                                                    $shape_parts = explode(' ', $item['domusong'], 2);
                                                    $line1_parts[] = htmlspecialchars($shape_parts[1] ?? $item['domusong']);
                                                }
                                                // 옵션: 인쇄면 / 수량 / 디자인
                                                if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '1' ? '단면' : '양면');
                                                if (!empty($item['mesu'])) $line2_parts[] = number_format(intval($item['mesu'])) . '매';
                                                if (!empty($item['uhyung']) && $item['uhyung'] != '0') {
                                                    $edit_types = ['10000' => '기본편집', '30000' => '고급편집'];
                                                    $line2_parts[] = $edit_types[$item['uhyung']] ?? htmlspecialchars($item['uhyung']) . '원';
                                                }
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            case 'msticker':
                                                // 규격: 종류 / 규격
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['Section'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['Section']));
                                                // 옵션: 인쇄면 / 수량 / 디자인
                                                if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '2' ? '양면인쇄' : '단면인쇄');
                                                if (!empty($item['MY_amount'])) $line2_parts[] = number_format(intval($item['MY_amount'])) . '매';
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            case 'envelope':
                                                // 규격: 종류 / 규격
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['Section'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['Section']));
                                                // 옵션: 수량 / 디자인
                                                if (!empty($item['mesu'])) $line2_parts[] = number_format(intval($item['mesu'])) . '매';
                                                elseif (!empty($item['MY_amount'])) $line2_parts[] = number_format(intval($item['MY_amount'])) . '매';
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            case 'namecard':
                                                // 규격: 종류 / 용지 / 규격
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['PN_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['PN_type']));
                                                if (!empty($item['Section'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['Section']));
                                                // 옵션: 인쇄면 / 수량 / 디자인
                                                if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '1' ? '단면' : '양면');
                                                if (!empty($item['mesu'])) $line2_parts[] = number_format(intval($item['mesu'])) . '매';
                                                elseif (!empty($item['MY_amount'])) $line2_parts[] = number_format(intval($item['MY_amount'])) . '매';
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            case 'merchandisebond':
                                                // 규격: 종류 / 용지
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['PN_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['PN_type']));
                                                // 옵션: 수량 / 인쇄면 / 디자인
                                                if (!empty($item['mesu'])) $line2_parts[] = number_format(intval($item['mesu'])) . '매';
                                                elseif (!empty($item['MY_amount'])) $line2_parts[] = number_format(intval($item['MY_amount'])) . '매';
                                                if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '1' ? '단면' : '양면');
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            case 'cadarok':
                                                // 규격: 종류 / 용지 / 규격
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['PN_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['PN_type']));
                                                if (!empty($item['Section'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['Section']));
                                                // 옵션: 인쇄면 / 수량 / 디자인
                                                if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '1' ? '단면' : '양면');
                                                if (!empty($item['mesu'])) $line2_parts[] = number_format(intval($item['mesu'])) . '부';
                                                elseif (!empty($item['MY_amount'])) $line2_parts[] = number_format(intval($item['MY_amount'])) . '부';
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            case 'littleprint':
                                                // 포스터: 종류 / 용지 / 규격
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['PN_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['PN_type']));
                                                if (!empty($item['Section'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['Section']));
                                                // 옵션: 인쇄면 / 수량 / 디자인
                                                if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '1' ? '단면' : '양면');
                                                if (!empty($item['mesu'])) $line2_parts[] = number_format(intval($item['mesu'])) . '매';
                                                elseif (!empty($item['MY_amount'])) $line2_parts[] = number_format(intval($item['MY_amount'])) . '매';
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            case 'ncrflambeau':
                                                // 양식지: 종류 / 용지 / 규격
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['PN_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['PN_type']));
                                                if (!empty($item['Section'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['Section']));
                                                // 옵션: 수량 / 인쇄도수 / 디자인
                                                if (!empty($item['mesu'])) $line2_parts[] = number_format(intval($item['mesu'])) . '권';
                                                elseif (!empty($item['MY_amount'])) $line2_parts[] = number_format(intval($item['MY_amount'])) . '권';
                                                if (!empty($item['MY_Fsd'])) $line2_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_Fsd']));
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;

                                            default:
                                                // 전단지/리플렛: 종류 / 용지 / 규격 (OrderComplete_universal.php와 동일)
                                                if (!empty($item['MY_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_type']));
                                                if (!empty($item['MY_Fsd'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['MY_Fsd']));
                                                if (!empty($item['PN_type'])) $line1_parts[] = htmlspecialchars(getKoreanName($connect, $item['PN_type']));
                                                // 옵션: 인쇄면 / 수량 / 디자인
                                                if (!empty($item['POtype'])) $line2_parts[] = ($item['POtype'] == '1' ? '단면컬러인쇄' : '양면컬러인쇄');
                                                // 전단지는 연/매수 표시
                                                $yeon = !empty($item['MY_amount']) ? floatval($item['MY_amount']) : 0;
                                                // flyer_mesu 우선, 없으면 mesu 필드 확인
                                                $mesu = !empty($item['flyer_mesu']) ? intval($item['flyer_mesu']) : (!empty($item['mesu']) ? intval($item['mesu']) : 0);
                                                if ($yeon > 0) {
                                                    $yeon_display = ($yeon == 0.5) ? '0.5' : number_format(intval($yeon));
                                                    $qty_text = $yeon_display . '연';
                                                    if ($mesu > 0) $qty_text .= '(' . number_format($mesu) . '매)';
                                                    $line2_parts[] = $qty_text;
                                                }
                                                if (!empty($item['ordertype'])) $line2_parts[] = ($item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])));
                                                break;
                                        }
                                        ?>
                                        <?php if (!empty($line1_parts)): ?>
                                            <div class="spec-line" style="color: #2d3748; margin-bottom: 2px;"><?php echo implode(' / ', $line1_parts); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($line2_parts)): ?>
                                            <div class="spec-line" style="color: #4a5568;"><?php echo implode(' / ', $line2_parts); ?></div>
                                        <?php endif; ?>

                                        <!-- 추가 옵션 정보 표시 -->
                                        <?php
                                        $options_details = $optionsDisplay->getOrderDetails($item);
                                        if (!empty($options_details['options'])):
                                        ?>
                                            <div class="options-section">
                                                <div class="options-title">추가옵션</div>
                                                <?php foreach ($options_details['options'] as $option): ?>
                                                    <div class="option-item">
                                                        <span class="option-category"><?php echo $option['category']; ?>:</span>
                                                        <?php echo $option['name']; ?>
                                                        <span class="option-price">(+<?php echo $option['formatted_price']; ?>)</span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- 수량 (Refactored) - 전단지: 값+단위+매수 통합 표시 -->
                                <td class="amount-cell <?php echo $is_flyer ? 'leaflet' : ''; ?>">
                                    <span class="amount-value"><?php echo $main_amount_display; ?></span>
                                    <?php if ($is_flyer): ?>
                                        <span class="amount-unit"><?php echo $unit; ?></span>
                                        <?php if ($show_sheet_count): ?>
                                            <span class="amount-sub">(<?php echo number_format($sub_amount); ?>매)</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <!-- 단위 (Refactored) - 전단지는 '-' 표시 -->
                                <td class="unit-cell">
                                    <?php if ($is_flyer): ?>
                                        <span class="amount-unit">-</span>
                                    <?php else: ?>
                                        <span class="amount-unit"><?php echo $unit; ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- 공급가액 -->
                                <td class="td-right">
                                    <div class="price-label">부가세 별도</div>
                                    <div class="price-total"><?php echo number_format($final_price); ?>원</div>
                                </td>

                                <!-- 관리 -->
                                <td class="td-center">
                                    <a href="?delete=<?php echo $item['no']; ?>"
                                        onclick="return confirm('이 상품을 삭제하시겠습니까?')"
                                        class="delete-btn">
                                        ✕
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 요약 정보 -->
            <div class="cart-summary">
                <div class="summary-header">
                    <div class="summary-title">주문 요약</div>
                    <div class="summary-count">총 <?php echo count($cart_items); ?>개 상품</div>
                </div>

                <div class="summary-grid">
                    <div class="summary-box">
                        <div class="summary-box-label">상품금액</div>
                        <div class="summary-box-value"><?php echo number_format($total_price); ?>원</div>
                    </div>
                    <div class="summary-box">
                        <div class="summary-box-label">부가세</div>
                        <div class="summary-box-value"><?php echo number_format($total_vat - $total_price); ?>원</div>
                    </div>
                    <div class="summary-box total">
                        <div class="summary-box-label">총 결제금액</div>
                        <div class="summary-box-value"><?php echo number_format($total_vat); ?>원</div>
                    </div>
                </div>

                <!-- Hidden 필드들 -->
                <?php foreach ($items_data as $index => $item): ?>
                    <input type="hidden" name="product_type[]" value="<?php echo htmlspecialchars($item['product_type']); ?>">
                    <input type="hidden" name="price[]" value="<?php echo htmlspecialchars($item['st_price']); ?>">
                    <input type="hidden" name="price_vat[]" value="<?php echo htmlspecialchars($item['st_price_vat']); ?>">
                <?php endforeach; ?>

                <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                <input type="hidden" name="total_price_vat" value="<?php echo $total_vat; ?>">
                <input type="hidden" name="items_count" value="<?php echo count($items_data); ?>">

                <!-- 컴팩트 버튼 그룹 -->
                <div class="button-group">
                    <button type="button" onclick="continueShopping()" class="btn-continue">
                        계속 쇼핑
                    </button>
                    <button type="button" onclick="showQuotation()" class="btn-quote">
                        견적서 받기
                    </button>
                    <button type="submit" class="btn-order">
                        주문하기
                    </button>
                </div>
            </div>
        </form>

        <!-- 도움말 및 정보 섹션 -->
        <div style="background: #f3f3f3; padding: 15px; border-radius: 4px; margin: 20px 0 15px 0; border: 1px solid #ccc;">
            <h4 style="margin: 0 0 10px 0; font-size: 15px; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                두손기획인쇄 이용 안내
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; text-align: left;">
                <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                    <h5 style="color: #1976d2; margin: 0 0 6px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center;">
                        품질 보장
                    </h5>
                    <ul style="margin: 0; padding-left: 18px; color: #555; font-size: 13px; line-height: 1.4;">
                        <li>20년 이상의 인쇄 경험</li>
                        <li>고품질 인쇄 장비 사용</li>
                        <li>전문 디자이너 상주</li>
                    </ul>
                </div>

                <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                    <h5 style="color: #388e3c; margin: 0 0 6px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center;">
                        빠른 배송
                    </h5>
                    <ul style="margin: 0; padding-left: 18px; color: #555; font-size: 13px; line-height: 1.4;">
                        <li>당일 출고 (오전 11시 이전 주문)</li>
                        <li>전국 택배 배송</li>
                        <li>방문 수령 가능</li>
                    </ul>
                </div>

                <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                    <h5 style="color: #f57c00; margin: 0 0 6px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center;">
                        합리적 가격
                    </h5>
                    <ul style="margin: 0; padding-left: 18px; color: #555; font-size: 13px; line-height: 1.4;">
                        <li>실시간 가격 계산</li>
                        <li>대량 주문 할인</li>
                        <li>투명한 가격 정책</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 연락처 정보 -->
        <div style="background: #f3f3f3; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ccc;">
            <h4 style="color: #0066cc; margin: 0 0 8px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                문의사항이 있으시면 언제든 연락하세요
            </h4>
            <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center; font-size: 14px;">
                <div style="display: flex; align-items: center; color: #222;">
                    <span style="font-weight: bold;">02-2632-1830</span>
                </div>
                <div style="display: flex; align-items: center; color: #222;">
                    <span style="font-weight: bold;">1688-2384</span>
                </div>
                <div style="display: flex; align-items: center; color: #222;">
                    <span>평일 09:00~18:00</span>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- 빈 장바구니 상태 - 개선된 UI -->
        <div class="empty-cart">
            <h3>장바구니가 비어있습니다</h3>
            <p>원하시는 인쇄물을 선택해서 주문을 시작해보세요!</p>

            <!-- 도움말 및 정보 섹션 -->
            <div style="background: #f3f3f3; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ccc;">
                <h4 style="margin: 0 0 10px 0; font-size: 15px; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                    두손기획인쇄 이용 안내
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; text-align: left;">
                    <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                        <h5 style="color: #1976d2; margin: 0 0 6px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center;">
                            품질 보장
                        </h5>
                        <ul style="margin: 0; padding-left: 18px; color: #555; font-size: 13px; line-height: 1.4;">
                            <li>20년 이상의 인쇄 경험</li>
                            <li>고품질 인쇄 장비 사용</li>
                            <li>전문 디자이너 상주</li>
                        </ul>
                    </div>

                    <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                        <h5 style="color: #388e3c; margin: 0 0 6px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center;">
                            빠른 배송
                        </h5>
                        <ul style="margin: 0; padding-left: 18px; color: #555; font-size: 13px; line-height: 1.4;">
                            <li>당일 출고 (오전 11시 이전 주문)</li>
                            <li>전국 택배 배송</li>
                            <li>방문 수령 가능</li>
                        </ul>
                    </div>

                    <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                        <h5 style="color: #f57c00; margin: 0 0 6px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center;">
                            합리적 가격
                        </h5>
                        <ul style="margin: 0; padding-left: 18px; color: #555; font-size: 13px; line-height: 1.4;">
                            <li>실시간 가격 계산</li>
                            <li>대량 주문 할인</li>
                            <li>투명한 가격 정책</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 연락처 정보 -->
            <div style="background: #f3f3f3; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ccc;">
                <h4 style="color: #0066cc; margin: 0 0 8px 0; font-size: 14px; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                    문의사항이 있으시면 언제든 연락하세요
                </h4>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center; font-size: 14px;">
                    <div style="display: flex; align-items: center; color: #222;">
                        <span style="font-weight: bold;">02-2632-1830</span>
                    </div>
                    <div style="display: flex; align-items: center; color: #222;">
                        <span style="font-weight: bold;">1688-2384</span>
                    </div>
                    <div style="display: flex; align-items: center; color: #222;">
                        <span>평일 09:00~18:00</span>
                    </div>
                </div>
            </div>

            <!-- 쇼핑 시작 버튼 -->
            <div style="text-align: center; margin-top: 15px;">
                <button onclick="continueShopping()" class="btn-continue" style="padding: 12px 30px; border-radius: 4px; font-size: 14px; min-width: 180px;">
                    인쇄 주문 시작하기
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 견적서 섹션 (기본적으로 숨겨짐) -->
<div id="quotationSection" class="quotation-section" style="display: none;">

    <!-- 견적서 헤더 -->
    <div class="quotation-header">
        <h1>견 적 서</h1>
        <div>
            두손기획인쇄 | 사업자등록번호: 201-10-69847<br>
            TEL: 02-2632-1830 | FAX: 02-2632-1831
        </div>
    </div>

    <!-- 견적일자 -->
    <div style="text-align: right; margin-bottom: 20px; font-size: 14px;">
        <strong>견적일자:</strong> <?php echo date('Y년 m월 d일'); ?>
    </div>

    <!-- 고객 인사말 -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; color: #2c3e50;">고객님께</h3>
        <p>아래와 같이 견적을 제출합니다.</p>
    </div>

    <?php if (!empty($cart_items)): ?>
        <!-- 견적서 테이블 -->
        <table class="quote-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>상품명</th>
                    <th>규격/옵션</th>
                    <th>수량</th>
                    <th>단가</th>
                    <th>부가세포함</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $quote_total = 0;
                $quote_total_vat = 0;
                foreach ($cart_items as $index => $item):
                    // 가격 계산 (장바구니와 동일한 로직)
                    $base_price = intval($item['st_price']);
                    $has_additional_options = isset($item['coating_price']) || isset($item['folding_price']) || isset($item['creasing_price']);

                    if ($has_additional_options) {
                        $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $item);
                        $final_price = $price_with_options['total_price'];
                        $final_price_vat = $price_with_options['total_vat'];
                    } else {
                        $final_price = $base_price;
                        $final_price_vat = intval($item['st_price_vat']);
                    }

                    $quote_total += $final_price;
                    $quote_total_vat += $final_price_vat;

                    $product_info = [
                        'cadarok' => '카달로그',
                        'sticker' => '스티커',
                        'msticker' => '자석스티커',
                        'leaflet' => '전단지',
                        'namecard' => '명함',
                        'envelope' => '봉투',
                        'merchandisebond' => '상품권',
                        'littleprint' => '포스터',
                        'poster' => '포스터',
                        'ncrflambeau' => '양식지',
                        'inserted' => '전단지'
                    ];
                    $product_name = $product_info[$item['product_type']] ?? '인쇄상품';
                ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $product_name; ?></td>
                        <td class="text-left small-text">
                            <?php if ($item['product_type'] == 'envelope'): ?>
                                <?php if (!empty($item['MY_type_name']) || !empty($item['MY_type'])): ?>
                                    <div><strong>종류:</strong> <?php echo htmlspecialchars($item['MY_type_name'] ?: getKoreanName($connect, $item['MY_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['Section_name']) || !empty($item['Section'])): ?>
                                    <div><strong>재질:</strong> <?php echo htmlspecialchars($item['Section_name'] ?: getKoreanName($connect, $item['Section'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['POtype_name']) || !empty($item['POtype'])): ?>
                                    <div><strong>인쇄:</strong> <?php echo htmlspecialchars($item['POtype_name'] ?: ($item['POtype'] == '1' ? '단면' : '양면')); ?></div>
                                <?php endif; ?>
                            <?php elseif ($item['product_type'] == 'ncrflambeau'): ?>
                                <?php if (!empty($item['MY_type'])): ?>
                                    <div><strong>색상:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['MY_Fsd'])): ?>
                                    <div><strong>종류:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_Fsd'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['PN_type'])): ?>
                                    <div><strong>규격:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['ordertype'])): ?>
                                    <div><strong>타입:</strong> <?php echo $item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : htmlspecialchars($item['ordertype'])); ?></div>
                                <?php endif; ?>

                                <!-- 🆕 양식지 추가옵션 (견적서용) -->
                                <?php if (!empty($item['premium_options'])): ?>
                                    <?php
                                    $premium_options = json_decode($item['premium_options'], true);
                                    if ($premium_options && isset($premium_options['additional_options_total']) && $premium_options['additional_options_total'] > 0):
                                        $selected_options = [];

                                        // 넘버링 (folding_enabled로 저장됨)
                                        if (isset($premium_options['folding_enabled']) && $premium_options['folding_enabled']) {
                                            $folding_type = $premium_options['folding_type'] ?? '';
                                            $folding_price = intval($premium_options['folding_price'] ?? 0);
                                            if (!empty($folding_type)) {
                                                if ($folding_type === 'numbering') {
                                                    $selected_options[] = '넘버링 (전화문의 1688-2384)';
                                                } else {
                                                    $folding_types = [
                                                        '1' => '넘버링 1줄',
                                                        '2' => '넘버링 2줄',
                                                        '3' => '넘버링 3줄'
                                                    ];
                                                    $folding_label = $folding_types[$folding_type] ?? getKoreanName($connect, $folding_type);
                                                    if ($folding_price > 0) {
                                                        $selected_options[] = $folding_label . ' (+' . number_format($folding_price) . '원)';
                                                    } else {
                                                        $selected_options[] = $folding_label;
                                                    }
                                                }
                                            }
                                        }

                                        // 미싱 (creasing_enabled로 저장됨)
                                        if (isset($premium_options['creasing_enabled']) && $premium_options['creasing_enabled']) {
                                            $creasing_lines = $premium_options['creasing_lines'] ?? '';
                                            $creasing_price = intval($premium_options['creasing_price'] ?? 0);
                                            if (!empty($creasing_lines)) {
                                                // 미싱 줄수 직접 표시 (1, 2, 3)
                                                $selected_options[] = '미싱 ' . $creasing_lines . '줄 (+' . number_format($creasing_price) . '원)';
                                            }
                                        }

                                        if (!empty($selected_options)):
                                    ?>
                                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                                <strong>추가옵션:</strong><br>
                                                <?php echo implode(', ', $selected_options); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (!empty($item['MY_type'])): ?>
                                    <div><strong>종류:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['PN_type'])): ?>
                                    <div><strong>규격:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['Section'])): ?>
                                    <div><strong>재질:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['Section'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['POtype'])): ?>
                                    <div><strong>인쇄:</strong> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- 추가 옵션 정보 표시 (일반 제품용) -->
                            <?php if ($has_additional_options && $item['product_type'] != 'ncrflambeau'): ?>
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                    <strong style="color: #e53e3e;">추가옵션:</strong><br>
                                    <?php echo $optionsDisplay->getCartColumnHtml($item); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            // 양식지(ncrflambeau)는 "권" 단위 사용
                            $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';

                            if (!empty($item['mesu'])) {
                                echo number_format($item['mesu']) . $unit;
                            } elseif (!empty($item['MY_amount'])) {
                                echo htmlspecialchars($item['MY_amount']) . $unit;
                            } else {
                                echo '1' . $unit;
                            }
                            ?>
                        </td>
                        <td class="text-right">
                            <strong><?php echo number_format($final_price); ?>원</strong>
                        </td>
                        <td class="text-right">
                            <strong><?php echo number_format($final_price_vat); ?>원</strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 합계 정보 -->
        <div style="background: #ecf0f1; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>공급가액:</span>
                <span><?php echo number_format($quote_total); ?>원</span>
            </div>
            <div style="text-align: right; color: #666; font-size: 13px; margin-top: 5px;">
                부가세 별도
            </div>
        </div>
    <?php endif; ?>

    <!-- 회사 정보 -->
    <div style="border-top: 2px solid #34495e; padding-top: 20px; color: #666; font-size: 14px;">
        <div>
            <strong>두손기획인쇄</strong><br>
            서울특별시 영등포구 영등포로 36길 9 송호빌딩 1층<br>
            전화: 02-2632-1830 | 팩스: 02-2632-1831<br>
            이메일: dsp1830@naver.com
        </div>

        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 15px;">
            <strong>입금계좌 안내</strong><br>
            국민은행: 123-456-789012 (예금주: 두손기획인쇄)<br>
            신한은행: 987-654-321098 (예금주: 두손기획인쇄)
        </div>

        <p style="margin-top: 20px; font-size: 12px; color: #999;">
            ※ 본 견적서의 유효기간은 발행일로부터 30일입니다.<br>
            ※ 상기 금액은 부가세가 포함된 금액입니다.<br>
            ※ 디자인 수정 및 추가 작업 시 별도 비용이 발생할 수 있습니다.
        </p>
    </div>

    <!-- 견적서 전용 버튼 -->
    <div style="text-align: center; margin-top: 30px; display: flex; gap: 15px; justify-content: center;">
        <button onclick="printQuotation()" class="btn-quote">
            견적서 인쇄
        </button>
        <button onclick="hideQuotation()" style="padding: 12px 30px; background-color: #6c757d; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer;">
            장바구니로 돌아가기
        </button>
    </div>
</div>
</div>

<script>
    // 견적서 표시 함수
    function showQuotation() {
        // 장바구니 내용 숨기기
        document.getElementById('cartContent').style.display = 'none';

        // 견적서 표시
        document.getElementById('quotationSection').style.display = 'block';

        // 부드러운 스크롤 효과로 견적서 위치로 이동
        document.getElementById('quotationSection').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // 페이지 제목 변경
        document.title = '견적서 - 두손기획인쇄';
    }

    // 장바구니로 돌아가기 함수
    function hideQuotation() {
        // 견적서 숨기기
        document.getElementById('quotationSection').style.display = 'none';

        // 장바구니 내용 표시
        document.getElementById('cartContent').style.display = 'block';

        // 부드러운 스크롤 효과로 장바구니 위치로 이동
        document.getElementById('cartContent').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // 페이지 제목 복원
        document.title = '통합 장바구니 - 두손기획인쇄';
    }

    // 견적서 인쇄 함수
    function printQuotation() {
        // 견적서만 인쇄하기 위한 새 창 열기
        const quotationContent = document.getElementById('quotationSection').innerHTML;
        const printWindow = window.open('', '_blank');

        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="ko">
            <head>
                <meta charset="UTF-8">
                <title>견적서 - 두손기획인쇄</title>
                <style>
                    @media print {
                        body { margin: 0; font-family: 'Malgun Gothic', Arial, sans-serif; }
                        .no-print { display: none !important; }
                    }
                    body {
                        font-family: 'Malgun Gothic', Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                    th { background: #f8f9fa; font-weight: bold; }
                    .no-print { display: none; }
                </style>
            </head>
            <body>
                ${quotationContent.replace(/onclick="[^"]*"/g, '').replace(/onmouseover="[^"]*"/g, '').replace(/onmouseout="[^"]*"/g, '')}
                <style>.no-print { display: none; }</style>
                <script>
                    // 버튼들 숨기기
                    const buttons = document.querySelectorAll('button');
                    buttons.forEach(btn => btn.style.display = 'none');

                    // 자동 인쇄 실행
                    window.onload = function() {
                        setTimeout(() => {
                            window.print();
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();
    }

    // 장바구니 비우기
    function clearCart() {
        if (confirm('장바구니를 비우시겠습니까?')) {
            window.location.href = '?clear=1';
        }
    }

    // 계속 쇼핑하기 함수
    function continueShopping() {
        // 이전 페이지가 있고, 같은 도메인이면 이전 페이지로
        if (document.referrer && document.referrer.includes(window.location.hostname)) {
            // 장바구니 페이지가 아닌 경우에만 이전 페이지로
            if (!document.referrer.includes('cart.php')) {
                window.location.href = document.referrer;
                return;
            }
        }

        // 기본적으로 메인 쇼핑 페이지들 중 하나로 이동
        // 최근에 장바구니에 추가된 상품 타입에 따라 결정
        const lastProductType = getLastProductType();

        switch (lastProductType) {
            case 'leaflet':
                window.location.href = '../inserted/index.php';
                break;
            case 'cadarok':
                window.location.href = '../cadarok/index.php';
                break;
            case 'namecard':
                window.location.href = '../namecard/index.php';
                break;
            case 'sticker':
                window.location.href = 'view_modern.php';
                break;
            case 'envelope':
                window.location.href = '../envelope/index.php';
                break;
            case 'merchandisebond':
                window.location.href = '../merchandisebond/index.php';
                break;
            case 'littleprint':
                window.location.href = '../littleprint/index.php';
                break;
            default:
                // 기본값: 전단지 페이지
                window.location.href = '../inserted/index.php';
        }
    }

    // 마지막 상품 타입 가져오기 (장바구니에서 첫 번째 아이템)
    function getLastProductType() {
        <?php if (!empty($cart_items)): ?>
            return '<?php echo $cart_items[0]['product_type'] ?? 'leaflet'; ?>';
        <?php else: ?>
            return 'leaflet';
        <?php endif; ?>
    }

    // PDF 견적서 생성 함수 (고객 정보 모달 열기)
    function generateQuotePDF() {
        <?php if (empty($cart_items)): ?>
            alert('장바구니가 비어있습니다. 상품을 추가한 후 견적서를 생성해주세요.');
            return;
        <?php endif; ?>

        // 고객 정보 입력 모달 열기
        openCustomerModal();
    }

    // 버튼 호버 효과
    document.addEventListener('DOMContentLoaded', function() {
        const continueBtn = document.querySelector('.btn-continue');
        if (continueBtn) {
            continueBtn.addEventListener('mouseenter', function() {
                this.style.background = '#218838';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 15px rgba(40, 167, 69, 0.3)';
            });

            continueBtn.addEventListener('mouseleave', function() {
                this.style.background = '#28a745';
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }

        const quoteBtn = document.querySelector('.btn-quote');
        if (quoteBtn) {
            quoteBtn.addEventListener('mouseenter', function() {
                this.style.background = '#138496';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 15px rgba(23, 162, 184, 0.3)';
            });

            quoteBtn.addEventListener('mouseleave', function() {
                this.style.background = '#17a2b8';
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }

        const orderBtn = document.querySelector('.btn-order');
        if (orderBtn) {
            orderBtn.addEventListener('mouseenter', function() {
                this.style.background = '#c82333';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 15px rgba(231, 76, 60, 0.3)';
            });

            orderBtn.addEventListener('mouseleave', function() {
                this.style.background = '#e74c3c';
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }
    });
</script>

<?php
// 고객 정보 입력 모달 포함
include 'customer_info_modal.php';
?>
</body>

</html>

<?php
if ($connect) {
    mysqli_close($connect);
}
?>