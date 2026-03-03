<?php
session_start();
$session_id = session_id();
$current_page = 'cart';

// 경로 수정: MlangPrintAuto/shop/에서 루트의 db.php 접근
include "../../db.php";
include "../../includes/AdditionalOptionsDisplay.php";
include "../../includes/quantity_formatter.php";
include "../../includes/ProductSpecFormatter.php";
include "../../includes/SpecDisplayService.php";
$connect = $db; // db.php에서 $db 변수 사용

// SpecDisplayService 인스턴스 (통합 출력 서비스)
$specDisplayService = new SpecDisplayService($connect);

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

    // Quantity (수량) - MY_amount field (formatQuantityNum으로 0.5연 등 소수점 지원)
    if (!empty($item['MY_amount'])) {
        $specs[] = '수량: ' . formatQuantityNum($item['MY_amount']) . '매';
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
    <link rel="stylesheet" href="../../css/common-styles.css?v=20260117">

    <link rel="stylesheet" href="../../css/excel-unified-style.css?v=20260117">
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
            <?php include "../../includes/nav.php"; ?>
    </div>
<?php else: ?>
    <!-- 빈 장바구니일 때 - 더 유용한 정보와 기능 제공 -->
    <!-- 품목 네비게이션 -->
    <?php include '../../includes/nav.php'; ?>

    <div style="margin-bottom: 5px;"></div>
<?php endif; ?>

<!-- 장바구니 메인 콘텐츠 -->
<div id="cartContent" style="font-family: 'Noto Sans KR', sans-serif; font-size: 13px; color: #222; line-height: 1.6; box-sizing: border-box; background: #fff; margin-bottom: 1rem;">
    <?php if (!empty($cart_items)): ?>
        <form method="post" action="../../mlangorder_printauto/OnlineOrder_unified.php" id="orderForm">
            <input type="hidden" name="SubmitMode" value="OrderOne">
            <input type="hidden" name="cart_session_id" value="<?php echo $session_id; ?>">
            <?php
            // 건수 그룹 전처리: item_group_id별로 그룹화
            $order_groups = [];
            $order_ungrouped = [];
            foreach ($cart_items as $ci) {
                $gid = $ci['item_group_id'] ?? null;
                if (!empty($gid)) {
                    $order_groups[$gid][] = $ci;
                } else {
                    $order_ungrouped[] = $ci;
                }
            }

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
                        <?php
                        // 그룹 아이템 렌더링 (축약: 1행 + ×N건 배지)
                        foreach ($order_groups as $gid => $group_items):
                            $group_count = count($group_items);
                            $first_item = $group_items[0];
                            $item = $first_item; // 대표 아이템으로 규격/수량 표시

                            // 그룹 내 모든 아이템의 가격 합산
                            $group_total_price = 0;
                            $group_total_vat = 0;
                            foreach ($group_items as $gi) {
                                $base_price = intval($gi['st_price']);
                                $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $gi);
                                $group_total_price += $price_with_options['total_price'];
                                $group_total_vat += $price_with_options['total_vat'];
                                $items_data[] = $gi;
                            }

                            $total_price += $group_total_price;
                            $total_vat += $group_total_vat;

                            // 상품명 매핑
                            $product_info = [
                                'cadarok' => ['name' => '카다록', 'icon' => ''],
                                'sticker' => ['name' => '스티커', 'icon' => ''],
                                'msticker' => ['name' => '자석스티커', 'icon' => ''],
                                'leaflet' => ['name' => '전단지', 'icon' => ''],
                                'inserted' => ['name' => '전단지', 'icon' => ''],
                                'namecard' => ['name' => '명함', 'icon' => ''],
                                'envelope' => ['name' => '봉투', 'icon' => ''],
                                'merchandisebond' => ['name' => '상품권', 'icon' => ''],
                                'littleprint' => ['name' => '포스터', 'icon' => ''],
                                'poster' => ['name' => '포스터', 'icon' => ''],
                                'ncrflambeau' => ['name' => '양식지', 'icon' => '']
                            ];

                            $product = $product_info[$item['product_type']] ?? ['name' => '상품', 'icon' => ''];

                            // SpecDisplayService 통합 출력 사용
                            $displayData = $specDisplayService->getDisplayData($item);
                            $is_flyer = in_array($item['product_type'], ['inserted', 'leaflet']);
                            $show_sheet_count = ($is_flyer && $LEAFLET_DISPLAY_STYLE === 'Y');

                            $quantity_display = $displayData['quantity_display'];
                            $unit = $displayData['unit'];
                            $main_amount_val = $displayData['quantity_value'];
                            $main_amount_display = $quantity_display;
                            $sub_amount = $item['mesu'] ?? $item['quantity_sheets'] ?? null;
                        ?>
                            <tr>
                                <!-- 상품정보 + ×N건 배지 -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <div class="product-name">
                                        <?php echo $product['name']; ?>
                                        <span style="display:inline-block;background:#e74c3c;color:white;font-size:11px;padding:1px 8px;border-radius:10px;margin-left:4px;font-weight:bold;">×<?php echo $group_count; ?>건</span>
                                    </div>
                                    <div class="product-number">#<?php echo $item['no']; ?></div>
                                </td>

                                <!-- 규격/옵션 (첫 번째 아이템 기준) -->
                                <td>
                                    <div class="specs-cell">
                                        <?php
                                        $specFormatter = new ProductSpecFormatter($connect);
                                        $specs = $specFormatter->format($item);
                                        ?>
                                        <?php if (!empty($specs['line1'])): ?>
                                            <div class="spec-line" style="color: #2d3748; margin-bottom: 2px;"><?php echo htmlspecialchars($specs['line1']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($specs['line2'])): ?>
                                            <div class="spec-line" style="color: #4a5568;"><?php echo htmlspecialchars($specs['line2']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($specs['additional'])): ?>
                                            <div class="spec-line" style="color: #666; font-size: 12px;"><?php echo htmlspecialchars($specs['additional']); ?></div>
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

                                <!-- 수량 (첫 번째 아이템 기준) -->
                                <td class="amount-cell <?php echo $is_flyer ? 'leaflet' : ''; ?>">
                                    <?php
                                    $qty_val = $displayData['quantity_value'] ?? 0;
                                    $qty_sheets = $displayData['quantity_sheets'] ?? null;
                                    $formatted_qty = function_exists('formatQuantityNum')
                                        ? formatQuantityNum($qty_val)
                                        : number_format(floatval($qty_val));
                                    ?>
                                    <span class="amount-value"><?php echo $formatted_qty; ?></span>
                                    <?php if ($is_flyer && $qty_sheets): ?>
                                        <br><span class="amount-sub">(<?php echo number_format($qty_sheets); ?>매)</span>
                                    <?php endif; ?>
                                </td>

                                <!-- 단위 -->
                                <td class="unit-cell">
                                    <span class="amount-unit"><?php echo htmlspecialchars($unit); ?></span>
                                </td>

                                <!-- 공급가액 (그룹 합계) -->
                                <td class="td-right">
                                    <?php if ($group_count > 1): ?>
                                        <div class="price-total">
                                            <?php echo number_format(intval($first_item['st_price'])); ?>원 × <?php echo $group_count; ?>건 = <?php echo number_format($group_total_price); ?>원
                                            <br><span style="font-size: 9px; font-weight: 400; color: #888;">(부가세별도)</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="price-total"><?php echo number_format($group_total_price); ?>원 <span style="font-size: 9px; font-weight: 400; color: #888;">(부가세별도)</span></div>
                                    <?php endif; ?>
                                </td>

                                <!-- 관리 (첫 번째 아이템만) -->
                                <td class="td-center">
                                    <a href="?delete=<?php echo $item['no']; ?>"
                                        onclick="return confirm('이 상품을 삭제하시겠습니까?')"
                                        class="delete-btn">
                                        ✕
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php
                        // 그룹화되지 않은 아이템 렌더링 (기존 로직)
                        foreach ($order_ungrouped as $index => $item):
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
                                'cadarok' => ['name' => '카다록', 'icon' => ''],
                                'sticker' => ['name' => '스티커', 'icon' => ''],
                                'msticker' => ['name' => '자석스티커', 'icon' => ''],
                                'leaflet' => ['name' => '전단지', 'icon' => ''],
                                'inserted' => ['name' => '전단지', 'icon' => ''],
                                'namecard' => ['name' => '명함', 'icon' => ''],
                                'envelope' => ['name' => '봉투', 'icon' => ''],
                                'merchandisebond' => ['name' => '상품권', 'icon' => ''],
                                'littleprint' => ['name' => '포스터', 'icon' => ''],
                                'poster' => ['name' => '포스터', 'icon' => ''],
                                'ncrflambeau' => ['name' => '양식지', 'icon' => '']
                            ];

                            $product = $product_info[$item['product_type']] ?? ['name' => '상품', 'icon' => ''];

                            // --- REFACTOR: SpecDisplayService 통합 출력 사용 ---
                            $displayData = $specDisplayService->getDisplayData($item);
                            $is_flyer = in_array($item['product_type'], ['inserted', 'leaflet']);
                            $show_sheet_count = ($is_flyer && $LEAFLET_DISPLAY_STYLE === 'Y');

                            // SpecDisplayService에서 통합 데이터 사용
                            $quantity_display = $displayData['quantity_display'];  // 예: "1연 (5,000매)" 또는 "1,000매"
                            $unit = $displayData['unit'];  // 연, 매, 부, 권 등
                            $main_amount_val = $displayData['quantity_value'];

                            // 전단지: quantity_display에 이미 "연 (매)" 형식 포함
                            // 다른 제품: quantity_display에 "매" 등 단위 포함
                            $main_amount_display = $quantity_display;
                            $sub_amount = $item['mesu'] ?? $item['quantity_sheets'] ?? null;
                            // --- END REFACTOR ---
                        ?>
                            <tr>
                                <!-- 상품정보 -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <div class="product-name"><?php echo $product['name']; ?></div>
                                    <div class="product-number">#<?php echo $item['no']; ?></div>
                                </td>

                                <!-- 규격/옵션 (2줄 슬래시 방식) - ProductSpecFormatter 사용 -->
                                <td>
                                    <div class="specs-cell">
                                        <?php
                                        // ProductSpecFormatter로 2줄 형식 생성
                                        $specFormatter = new ProductSpecFormatter($connect);
                                        $specs = $specFormatter->format($item);
                                        ?>
                                        <?php if (!empty($specs['line1'])): ?>
                                            <div class="spec-line" style="color: #2d3748; margin-bottom: 2px;"><?php echo htmlspecialchars($specs['line1']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($specs['line2'])): ?>
                                            <div class="spec-line" style="color: #4a5568;"><?php echo htmlspecialchars($specs['line2']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($specs['additional'])): ?>
                                            <div class="spec-line" style="color: #666; font-size: 12px;"><?php echo htmlspecialchars($specs['additional']); ?></div>
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

                                <!-- 수량 (통합) - 모든 품목 동일 구조 -->
                                <td class="amount-cell <?php echo $is_flyer ? 'leaflet' : ''; ?>">
                                    <?php
                                    $qty_val = $displayData['quantity_value'] ?? 0;
                                    $qty_sheets = $displayData['quantity_sheets'] ?? null;
                                    $formatted_qty = function_exists('formatQuantityNum')
                                        ? formatQuantityNum($qty_val)
                                        : number_format(floatval($qty_val));
                                    ?>
                                    <span class="amount-value"><?php echo $formatted_qty; ?></span>
                                    <?php if ($is_flyer && $qty_sheets): ?>
                                        <br><span class="amount-sub">(<?php echo number_format($qty_sheets); ?>매)</span>
                                    <?php endif; ?>
                                </td>

                                <!-- 단위 (통합) - 모든 품목 동일하게 단위 표시 -->
                                <td class="unit-cell">
                                    <span class="amount-unit"><?php echo htmlspecialchars($unit); ?></span>
                                </td>

                                <!-- 공급가액 -->
                                <td class="td-right">
                                    <div class="price-total"><?php echo number_format($final_price); ?>원 <span style="font-size: 9px; font-weight: 400; color: #888;">(부가세별도)</span></div>
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
        <div class="cart-info-section" style="background: #f3f3f3; padding: 15px; border-radius: 4px; margin: 20px auto 15px auto; max-width: 1100px; border: 1px solid #ccc;">
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
        <div class="cart-contact-section" style="background: #f3f3f3; padding: 12px 15px; border-radius: 4px; margin: 0 auto 15px auto; max-width: 1100px; border: 1px solid #ccc;">
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
            <div class="cart-info-section" style="background: #f3f3f3; padding: 15px; border-radius: 4px; margin: 0 auto 15px auto; max-width: 1100px; border: 1px solid #ccc;">
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
        <div class="cart-contact-section" style="background: #f3f3f3; padding: 12px 15px; border-radius: 4px; margin: 0 auto 15px auto; max-width: 1100px; border: 1px solid #ccc;">
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
            두손기획인쇄 | 사업자등록번호: 107-06-45106<br>
            TEL: 02-2632-1830 | FAX: 02-2632-1829
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
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="quote-table" style="min-width: 480px;">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>상품명</th>
                    <th>규격/옵션</th>
                    <th>수량</th>
                    <th>단가</th>
                    <th>공급가액</th>
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
                        'cadarok' => '카다록',
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
                            <?php
                                // ProductSpecFormatter로 2줄 형식 생성 (견적서용)
                                $specFormatter = new ProductSpecFormatter($connect);
                                $specs = $specFormatter->format($item);
                            ?>
                            <?php if (!empty($specs['line1'])): ?>
                                <div class="spec-line"><?php echo htmlspecialchars($specs['line1']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($specs['line2'])): ?>
                                <div class="spec-line" style="color: #555;"><?php echo htmlspecialchars($specs['line2']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($specs['additional'])): ?>
                                <div class="spec-line" style="color: #777; font-size: 11px;"><?php echo htmlspecialchars($specs['additional']); ?></div>
                            <?php endif; ?>

                            <!-- 추가 옵션 정보 표시 (모든 제품 공통) -->
                            <?php
                            $options_details_quote = $optionsDisplay->getOrderDetails($item);
                            if (!empty($options_details_quote['options'])):
                            ?>
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                    <strong style="color: #e53e3e;">추가옵션:</strong><br>
                                    <?php
                                    $option_strings = [];
                                    foreach ($options_details_quote['options'] as $option) {
                                        $option_strings[] = htmlspecialchars($option['category'] . ': ' . $option['name'] . ' (' . $option['formatted_price'] . ')');
                                    }
                                    echo implode('<br>', $option_strings);
                                    ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            // 전단지(inserted/leaflet)는 "연" 단위로 소수점 허용
                            // 양식지(ncrflambeau)는 "권" 단위
                            // 그 외는 정수 "매" 단위
                            $is_flyer = in_array($item['product_type'], ['inserted', 'leaflet']);

                            if ($is_flyer) {
                                // 전단지: 연 단위 표시 (0.5연, 1연 등)
                                $yeon = floatval($item['yeon'] ?? $item['MY_amount'] ?? 1);
                                if ($yeon == intval($yeon)) {
                                    echo number_format($yeon) . '연';
                                } else {
                                    echo rtrim(rtrim(number_format($yeon, 1), '0'), '.') . '연';
                                }
                            } elseif ($item['product_type'] == 'ncrflambeau') {
                                // 양식지: 권 단위 (정수)
                                $qty = intval($item['MY_amount'] ?? 1);
                                echo number_format($qty) . '권';
                            } else {
                                // 그 외: 매 단위 (정수)
                                $qty = intval($item['mesu'] ?? $item['MY_amount'] ?? 1);
                                echo number_format($qty) . '매';
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
        </div>

        <!-- 합계 정보 (3박스 그리드) -->
        <?php
        $quote_vat = intval($quote_total * 0.1);
        $quote_grand_total = $quote_total + $quote_vat;
        ?>
        <div class="cart-summary" style="margin-bottom: 30px;">
            <div class="summary-header">
                <div class="summary-title">주문 요약</div>
                <div class="summary-count">총 <?php echo count($cart_items); ?>개 상품</div>
            </div>
            <div class="summary-grid">
                <div class="summary-box">
                    <div class="summary-box-label">상품금액</div>
                    <div class="summary-box-value"><?php echo number_format($quote_total); ?>원</div>
                </div>
                <div class="summary-box">
                    <div class="summary-box-label">부가세</div>
                    <div class="summary-box-value"><?php echo number_format($quote_vat); ?>원</div>
                </div>
                <div class="summary-box total">
                    <div class="summary-box-label">총 결제금액</div>
                    <div class="summary-box-value"><?php echo number_format($quote_grand_total); ?>원</div>
                </div>
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
            예금주: 두손기획인쇄 차경선<br>
            국민은행: 999-1688-2384<br>
            신한은행: 110-342-543507<br>
            농협: 301-2632-1830-11<br>
            카드결제: 1688-2384<br>
            <span style="color: #d9534f; font-size: 12px;">입금자명을 주문자명(관리자)과 동일하게 해주세요</span>
        </div>

        <p style="margin-top: 20px; font-size: 12px; color: #999;">
            ※ 본 견적서의 유효기간은 발행일로부터 7일입니다.<br>
            ※ 상기 금액은 부가세가 포함된 금액입니다.<br>
            ※ 디자인 수정 및 추가 작업 시 별도 비용이 발생할 수 있습니다.
        </p>
    </div>

    <!-- 견적서 전용 버튼 -->
    <div class="button-group" style="margin-top: 30px;">
        <button onclick="printQuotation()" class="btn-quote">
            견적서 인쇄
        </button>
        <button onclick="openCustomerModal()" class="btn-quote" style="background-color: #2563eb;">
            이메일 발송
        </button>
        <button onclick="hideQuotation()" class="btn-continue" style="background-color: #6c757d;">
            돌아가기
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