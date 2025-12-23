<?php
/**
 * 🛒 통합 장바구니 시스템 v2.0
 * 경로: /mlangprintauto/shop/cart_01.php
 *
 * 특징:
 * - 모든 제품 타입 통합 지원 (전단지, 봉투, 명함, 스티커, 카다록, 상품권, NCR, 포스터)
 * - AdditionalOptionsDisplay 클래스 완벽 지원
 * - 추가 옵션 시스템 (전단지: 코팅/접기/오시, 봉투: 양면테이프)
 * - 프리미엄 옵션 시스템 (명함: 박/넘버링/미싱/귀돌이/오시)
 * - 다중 테이블 지원 (shop_temp, shop_temp_cadarok)
 * - 현대적 UI/UX 디자인
 */

session_start();
$session_id = session_id();

// 데이터베이스 연결 및 필수 클래스 로드
include "../../db.php";
include "../../includes/AdditionalOptionsDisplay.php";
$connect = $db;

error_log("=== 통합 장바구니 시스템 시작 ===");
error_log("Session ID: " . $session_id);

// UTF-8 설정과 연결 확인
if ($connect) {
    error_log("Database connection successful");
    if (!mysqli_set_charset($connect, 'utf8')) {
        error_log("Error setting UTF-8 charset: " . mysqli_error($connect));
    }
} else {
    error_log("Database connection failed");
}

/**
 * ID로 한글명 가져오기 함수 (개선된 버전)
 */
function getKoreanName($connect, $id) {
    if (!$connect || !$id) {
        return $id;
    }

    // ID가 이미 한글이면 그대로 반환
    if (preg_match('/[가-힣]/u', $id)) {
        return $id;
    }

    // 숫자와 문자열 모두 처리
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? OR title = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
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
    return $id;
}

/**
 * 통합 장바구니 내용 가져오기 함수
 * shop_temp와 shop_temp_cadarok 테이블을 통합 조회
 */
function getCartItems($connect, $session_id) {
    if (!$connect) {
        error_log("Database connection failed in getCartItems");
        return false;
    }

    $items = [];

    // 1. shop_temp 테이블에서 모든 제품 데이터 가져오기 (추가 옵션 포함)
    $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp'");
    if ($table_check && mysqli_num_rows($table_check) > 0) {
        // 모든 추가 옵션 필드를 포함한 쿼리
        $query = "SELECT *,
                  COALESCE(product_type, 'namecard') as product_type,
                  MY_type as category_no,
                  MY_Fsd as style,
                  PN_type as section,
                  ordertype as tree_select,
                  st_price as price,
                  st_price_vat as price_vat,
                  -- 전단지 추가 옵션
                  coating_enabled, coating_type, coating_price,
                  folding_enabled, folding_type, folding_price,
                  creasing_enabled, creasing_lines, creasing_price,
                  additional_options_total,
                  -- 명함 프리미엄 옵션
                  premium_options, premium_options_total,
                  -- 봉투 추가 옵션
                  envelope_tape_enabled, envelope_tape_quantity,
                  envelope_tape_price, envelope_additional_options_total
                  FROM shop_temp
                  WHERE session_id = ? ORDER BY no DESC";

        $stmt = mysqli_prepare($connect, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $session_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = $row;
            }
            mysqli_stmt_close($stmt);

            error_log("Found " . count($items) . " items in shop_temp");
        } else {
            error_log("Failed to prepare shop_temp query: " . mysqli_error($connect));
        }
    }

    // 2. shop_temp_cadarok 테이블에서 카다록 데이터 가져오기
    $cadarok_table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp_cadarok'");
    if ($cadarok_table_check && mysqli_num_rows($cadarok_table_check) > 0) {
        $cadarok_query = "SELECT no, 'cadarok' as product_type,
                         type_name as MY_type,
                         paper_type as MY_Fsd,
                         size_name as PN_type,
                         amount as MY_amount,
                         order_type as ordertype,
                         st_price, st_price_vat,
                         '1' as POtype,
                         '' as MY_comment,
                         session_id
                         FROM shop_temp_cadarok
                         WHERE session_id = ? ORDER BY no DESC";

        $stmt = mysqli_prepare($connect, $cadarok_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $session_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                // 카다록 데이터를 통합 형식으로 변환
                $row['no'] = 'cadarok_' . $row['no']; // 구분을 위해 접두사 추가
                $items[] = $row;
            }
            mysqli_stmt_close($stmt);

            error_log("Found " . mysqli_num_rows(mysqli_query($connect, $cadarok_query)) . " cadarok items");
        }
    }

    return $items;
}

/**
 * 장바구니 아이템 삭제 (통합 버전)
 */
function deleteCartItem($connect, $session_id, $item_no) {
    // 카다록 아이템인지 확인
    if (strpos($item_no, 'cadarok_') === 0) {
        // 카다록 아이템 삭제
        $real_no = str_replace('cadarok_', '', $item_no);
        if (is_numeric($real_no)) {
            $delete_query = "DELETE FROM shop_temp_cadarok WHERE no = ? AND session_id = ?";
            $stmt = mysqli_prepare($connect, $delete_query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'is', $real_no, $session_id);
                $result = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return $result;
            }
        }
    } else if (is_numeric($item_no)) {
        // 일반 아이템 삭제
        $delete_query = "DELETE FROM shop_temp WHERE no = ? AND session_id = ?";
        $stmt = mysqli_prepare($connect, $delete_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'is', $item_no, $session_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
    }
    return false;
}

/**
 * 장바구니 전체 비우기 (통합 버전)
 */
function clearAllCart($connect, $session_id) {
    $success = true;

    // shop_temp 테이블 비우기
    $clear_query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $clear_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        $success = mysqli_stmt_execute($stmt) && $success;
        mysqli_stmt_close($stmt);
    }

    // shop_temp_cadarok 테이블 비우기
    $cadarok_table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp_cadarok'");
    if ($cadarok_table_check && mysqli_num_rows($cadarok_table_check) > 0) {
        $clear_cadarok_query = "DELETE FROM shop_temp_cadarok WHERE session_id = ?";
        $stmt = mysqli_prepare($connect, $clear_cadarok_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $session_id);
            $success = mysqli_stmt_execute($stmt) && $success;
            mysqli_stmt_close($stmt);
        }
    }

    return $success;
}

// 장바구니 아이템 삭제 처리
if (isset($_GET['delete'])) {
    $item_no = $_GET['delete'];
    if (deleteCartItem($connect, $session_id, $item_no)) {
        error_log("Successfully deleted item: " . $item_no);
    } else {
        error_log("Failed to delete item: " . $item_no);
    }
    header('Location: cart_01.php');
    exit;
}

// 장바구니 비우기 처리
if (isset($_GET['clear'])) {
    if (clearAllCart($connect, $session_id)) {
        error_log("Successfully cleared cart for session: " . $session_id);
    } else {
        error_log("Failed to clear cart for session: " . $session_id);
    }
    header('Location: cart_01.php');
    exit;
}

// 장바구니 아이템 조회
$cart_items = getCartItems($connect, $session_id);
$optionsDisplay = getAdditionalOptionsDisplay($connect);

if ($cart_items === false) {
    error_log("Failed to get cart items");
    $cart_items = [];
} else {
    error_log("Retrieved " . count($cart_items) . " cart items");
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 통합 장바구니 - 두손기획인쇄</title>
    <link rel="stylesheet" href="../../css/style250801.css">
    <link rel="stylesheet" href="../../css/common-styles.css">
    <style>
        /* 통합 장바구니 전용 스타일 */
        .unified-cart-container {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
        }

        .cart-hero {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.2);
        }

        .cart-hero h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .cart-hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .cart-table-container {
            background: linear-gradient(135deg, #fafbff 0%, #fff9f9 100%);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e8eaed;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .cart-table thead tr {
            background: linear-gradient(135deg, #f8f4ff 0%, #fff0f5 100%);
            border-bottom: 2px solid #e1d5e7;
        }

        .cart-table th {
            padding: 12px 15px;
            font-weight: 600;
            color: #4a5568;
            border-right: 1px solid #e8eaed;
            font-size: 13px;
        }

        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f3f4;
            border-right: 1px solid #f1f3f4;
            vertical-align: top;
        }

        .cart-table tbody tr:hover {
            background: rgba(99, 102, 241, 0.02);
        }

        .product-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .product-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 15px;
        }

        .product-details {
            font-size: 12px;
            color: #718096;
        }

        .options-display {
            background: linear-gradient(135deg, #f0f9ff 0%, #fef7ff 100%);
            border: 1px solid #e0e7ff;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 12px;
            color: #5b21b6;
            margin-top: 5px;
        }

        .premium-options {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4a 100%);
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 12px;
            color: #8b7500;
            margin-top: 5px;
        }

        .quantity-badge {
            background: #4f46e5;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
        }

        .price-display {
            text-align: right;
            font-weight: 600;
            color: #dc2626;
            font-size: 15px;
        }

        .delete-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .delete-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        .order-summary {
            background: linear-gradient(135deg, #f0fdf4 0%, #fef3f2 100%);
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 1.5rem;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .total-label {
            font-size: 1.3rem;
            font-weight: 700;
            color: #166534;
        }

        .total-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #dc2626;
        }

        .order-btn {
            width: 100%;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .empty-cart-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.6;
        }

        .empty-cart h3 {
            font-size: 1.5rem;
            color: #4a5568;
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: #718096;
            margin-bottom: 2rem;
        }

        .shop-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .shop-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        .clear-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clear-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        @media (max-width: 768px) {
            .cart-table {
                font-size: 12px;
            }

            .cart-table th,
            .cart-table td {
                padding: 8px;
            }

            .cart-hero h1 {
                font-size: 1.8rem;
            }

            .order-summary {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="unified-cart-container">
        <!-- 헤더 섹션 -->
        <div class="cart-hero">
            <h1>🛒 통합 장바구니</h1>
            <p>모든 인쇄 상품을 한 번에 주문하세요</p>
        </div>

        <!-- 네비게이션 -->
        <?php if (!empty($cart_items)): ?>
            <div style="margin-bottom: 1rem; padding: 10px; background: white; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <?php include "../../includes/nav.php"; ?>
            </div>
        <?php else: ?>
            <?php include '../../includes/nav.php'; ?>
        <?php endif; ?>

        <!-- 장바구니 메인 콘텐츠 -->
        <?php if (!empty($cart_items)): ?>
            <!-- 장바구니 액션 버튼 -->
            <div class="cart-actions">
                <div>
                    <span style="font-weight: 600; color: #4a5568;">총 <?php echo count($cart_items); ?>개 상품</span>
                </div>
                <div>
                    <button onclick="clearCart()" class="clear-btn">🗑️ 전체 삭제</button>
                </div>
            </div>

            <form method="post" action="../../mlangorder_printauto/OnlineOrder_unified.php" id="orderForm">
                <input type="hidden" name="SubmitMode" value="OrderOne">

                <!-- 장바구니 테이블 -->
                <div class="cart-table-container">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th style="text-align: left; min-width: 200px;">상품정보</th>
                                <th style="text-align: center; min-width: 150px;">규격/옵션</th>
                                <th style="text-align: center; min-width: 80px;">수량</th>
                                <th style="text-align: right; min-width: 100px;">단가</th>
                                <th style="text-align: right; min-width: 120px;">총액</th>
                                <th style="text-align: center; min-width: 60px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_price = 0;
                            $total_vat = 0;
                            $items_data = array();

                            foreach ($cart_items as $index => $item):
                                // 기본 가격 설정
                                if (!isset($item['st_price'])) {
                                    $item['st_price'] = isset($item['MY_price']) ? $item['MY_price'] : 0;
                                }
                                if (!isset($item['st_price_vat'])) {
                                    $item['st_price_vat'] = isset($item['MY_price_vat']) ? $item['MY_price_vat'] : round($item['st_price'] * 1.1);
                                }

                                // 추가 옵션 가격 계산
                                $base_price = intval($item['st_price']);
                                $base_price_vat = intval($item['st_price_vat']);

                                // AdditionalOptionsDisplay 클래스를 사용하여 최종 가격 계산
                                $has_additional_options = false;
                                $final_price = $base_price;
                                $final_price_vat = $base_price_vat;

                                if ($optionsDisplay) {
                                    // 전단지 추가 옵션 확인
                                    if (isset($item['coating_price']) || isset($item['folding_price']) || isset($item['creasing_price'])) {
                                        $has_additional_options = true;
                                        $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $item);
                                        $final_price = $price_with_options['total_price'];
                                        $final_price_vat = $price_with_options['total_vat'];
                                    }

                                    // 봉투 추가 옵션 확인
                                    if (isset($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
                                        $has_additional_options = true;
                                        $envelope_options_total = intval($item['envelope_additional_options_total'] ?? 0);
                                        $final_price = $base_price + $envelope_options_total;
                                        $final_price_vat = intval($final_price * 1.1);
                                    }

                                    // 명함 프리미엄 옵션 확인
                                    if (!empty($item['premium_options_total'])) {
                                        $has_additional_options = true;
                                        $premium_total = intval($item['premium_options_total']);
                                        $final_price = $base_price + $premium_total;
                                        $final_price_vat = intval($final_price * 1.1);
                                    }
                                }

                                $total_price += $final_price;
                                $total_vat += $final_price_vat;

                                // 제품 타입 결정
                                $product_name = '전단지';
                                if (isset($item['product_type'])) {
                                    switch($item['product_type']) {
                                        case 'cadarok': $product_name = '카달로그'; break;
                                        case 'sticker': $product_name = '스티커'; break;
                                        case 'namecard': $product_name = '명함'; break;
                                        case 'envelope': $product_name = '봉투'; break;
                                        case 'merchandisebond': $product_name = '상품권'; break;
                                        case 'ncrflambeau': $product_name = 'NCR양식'; break;
                                        case 'littleprint': $product_name = '포스터'; break;
                                        case 'msticker': $product_name = '자석스티커'; break;
                                    }
                                }

                                // hidden 필드용 데이터 저장
                                $items_data[] = array_merge($item, [
                                    'final_price' => $final_price,
                                    'final_price_vat' => $final_price_vat
                                ]);
                            ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-name"><?php echo htmlspecialchars($product_name); ?></div>
                                            <?php if (!empty($item['MY_Fsd'])): ?>
                                                <div class="product-details">용지: <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_Fsd'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['MY_type'])): ?>
                                                <div class="product-details">종류: <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($item['PN_type'])): ?>
                                            <div style="font-weight: 600; margin-bottom: 5px;">
                                                <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- 추가 옵션 표시 -->
                                        <?php if ($has_additional_options && $optionsDisplay): ?>
                                            <?php
                                            // 전단지 추가 옵션
                                            if (isset($item['coating_price']) || isset($item['folding_price']) || isset($item['creasing_price'])) {
                                                echo '<div class="options-display">';
                                                echo $optionsDisplay->getCartColumnHtml($item);
                                                echo '</div>';
                                            }

                                            // 봉투 추가 옵션
                                            if (isset($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
                                                echo '<div class="options-display">';
                                                echo '+ 양면테이프 ' . number_format($item['envelope_tape_quantity']) . '매';
                                                echo '</div>';
                                            }

                                            // 명함 프리미엄 옵션
                                            if (!empty($item['premium_options'])) {
                                                $premium_options = json_decode($item['premium_options'], true);
                                                if ($premium_options && $premium_options['premium_options_total'] > 0) {
                                                    echo '<div class="premium-options">';
                                                    $selected_options = [];
                                                    if ($premium_options['foil_enabled']) $selected_options[] = '박';
                                                    if ($premium_options['numbering_enabled']) $selected_options[] = '넘버링';
                                                    if ($premium_options['perforation_enabled']) $selected_options[] = '미싱';
                                                    if ($premium_options['rounding_enabled']) $selected_options[] = '귀돌이';
                                                    if ($premium_options['creasing_enabled']) $selected_options[] = '오시';
                                                    echo '💎 ' . implode(', ', $selected_options);
                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                        <?php endif; ?>

                                        <?php if (!empty($item['ordertype'])): ?>
                                            <div style="font-size: 12px; color: #718096; margin-top: 5px;">
                                                <?php echo htmlspecialchars(getKoreanName($connect, $item['ordertype'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="quantity-badge">
                                            <?php echo htmlspecialchars($item['MY_amount'] ?? '1'); ?>
                                        </span>
                                    </td>
                                    <td class="price-display">
                                        <?php echo number_format($final_price); ?>원
                                    </td>
                                    <td class="price-display">
                                        <?php echo number_format($final_price_vat); ?>원
                                        <div style="font-size: 11px; color: #718096; margin-top: 2px;">VAT 포함</div>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" onclick="deleteItem('<?php echo $item['no']; ?>')" class="delete-btn">
                                            ❌
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 주문 요약 -->
                <div class="order-summary">
                    <div class="order-total">
                        <span class="total-label">총 결제금액</span>
                        <span class="total-amount"><?php echo number_format($total_vat); ?>원</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 14px; color: #718096;">
                        <span>상품금액: <?php echo number_format($total_price); ?>원</span>
                        <span>VAT: <?php echo number_format($total_vat - $total_price); ?>원</span>
                    </div>

                    <!-- Hidden 필드들 -->
                    <?php foreach ($items_data as $index => $item): ?>
                        <input type="hidden" name="product_type[]" value="<?php echo htmlspecialchars($item['product_type'] ?? ''); ?>">
                        <input type="hidden" name="style[]" value="<?php echo htmlspecialchars($item['MY_Fsd'] ?? ''); ?>">
                        <input type="hidden" name="category_no[]" value="<?php echo htmlspecialchars($item['MY_type'] ?? ''); ?>">
                        <input type="hidden" name="section[]" value="<?php echo htmlspecialchars($item['PN_type'] ?? ''); ?>">
                        <input type="hidden" name="tree_select[]" value="<?php echo htmlspecialchars($item['ordertype'] ?? ''); ?>">
                        <input type="hidden" name="quantity[]" value="<?php echo htmlspecialchars($item['MY_amount'] ?? '1'); ?>">
                        <input type="hidden" name="print_side[]" value="<?php echo htmlspecialchars($item['POtype'] ?? '1'); ?>">
                        <input type="hidden" name="price[]" value="<?php echo htmlspecialchars($item['final_price']); ?>">
                        <input type="hidden" name="price_vat[]" value="<?php echo htmlspecialchars($item['final_price_vat']); ?>">
                        <input type="hidden" name="vat_amount[]" value="<?php echo htmlspecialchars($item['final_price_vat'] - $item['final_price']); ?>">

                        <!-- 추가 옵션 데이터 -->
                        <?php if (!empty($item['MY_comment'])): ?>
                            <input type="hidden" name="items[<?php echo $index; ?>][MY_comment]" value="<?php echo htmlspecialchars($item['MY_comment']); ?>">
                        <?php endif; ?>

                        <!-- 전단지 추가 옵션 -->
                        <?php if (isset($item['coating_price']) || isset($item['folding_price']) || isset($item['creasing_price'])): ?>
                            <input type="hidden" name="items[<?php echo $index; ?>][coating_enabled]" value="<?php echo htmlspecialchars($item['coating_enabled'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][coating_type]" value="<?php echo htmlspecialchars($item['coating_type'] ?? ''); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][coating_price]" value="<?php echo htmlspecialchars($item['coating_price'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][folding_enabled]" value="<?php echo htmlspecialchars($item['folding_enabled'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][folding_type]" value="<?php echo htmlspecialchars($item['folding_type'] ?? ''); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][folding_price]" value="<?php echo htmlspecialchars($item['folding_price'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][creasing_enabled]" value="<?php echo htmlspecialchars($item['creasing_enabled'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][creasing_lines]" value="<?php echo htmlspecialchars($item['creasing_lines'] ?? ''); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][creasing_price]" value="<?php echo htmlspecialchars($item['creasing_price'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][additional_options_total]" value="<?php echo htmlspecialchars($item['additional_options_total'] ?? '0'); ?>">
                        <?php endif; ?>

                        <!-- 봉투 추가 옵션 -->
                        <?php if (isset($item['envelope_tape_enabled'])): ?>
                            <input type="hidden" name="items[<?php echo $index; ?>][envelope_tape_enabled]" value="<?php echo htmlspecialchars($item['envelope_tape_enabled'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][envelope_tape_quantity]" value="<?php echo htmlspecialchars($item['envelope_tape_quantity'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][envelope_tape_price]" value="<?php echo htmlspecialchars($item['envelope_tape_price'] ?? '0'); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][envelope_additional_options_total]" value="<?php echo htmlspecialchars($item['envelope_additional_options_total'] ?? '0'); ?>">
                        <?php endif; ?>

                        <!-- 명함 프리미엄 옵션 -->
                        <?php if (!empty($item['premium_options'])): ?>
                            <input type="hidden" name="items[<?php echo $index; ?>][premium_options]" value="<?php echo htmlspecialchars($item['premium_options']); ?>">
                            <input type="hidden" name="items[<?php echo $index; ?>][premium_options_total]" value="<?php echo htmlspecialchars($item['premium_options_total'] ?? '0'); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                    <input type="hidden" name="total_price_vat" value="<?php echo $total_vat; ?>">
                    <input type="hidden" name="items_count" value="<?php echo count($items_data); ?>">

                    <button type="submit" class="order-btn">
                        🚀 주문하기 (<?php echo count($cart_items); ?>개 상품)
                    </button>
                </div>
            </form>

        <?php else: ?>
            <!-- 빈 장바구니 -->
            <div class="empty-cart">
                <div class="empty-cart-icon">📭</div>
                <h3>장바구니가 비어있습니다</h3>
                <p>원하시는 인쇄 상품을 장바구니에 담아보세요!</p>
                <a href="../../" class="shop-btn">🛍️ 쇼핑 시작하기</a>
            </div>
        <?php endif; ?>

        <!-- 주문 안내 -->
        <div style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center; margin-top: 2rem;">
            <h3 style="margin-bottom: 1rem;">📋 주문 안내</h3>
            <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 1rem; font-size: 14px;">
                <div>💰 모든 작업은 입금 후 진행</div>
                <div>📦 3만원 이상 무료배송 (미만시 택배비 별도)</div>
                <div>📁 주문 후 파일 업로드</div>
            </div>
        </div>
    </div>

    <script>
        // 아이템 삭제 확인
        function deleteItem(itemNo) {
            if (confirm('이 상품을 장바구니에서 삭제하시겠습니까?')) {
                window.location.href = '?delete=' + encodeURIComponent(itemNo);
            }
        }

        // 장바구니 비우기 확인
        function clearCart() {
            if (confirm('장바구니를 모두 비우시겠습니까?')) {
                window.location.href = '?clear=1';
            }
        }

        // 주문 전 확인
        document.getElementById('orderForm')?.addEventListener('submit', function(e) {
            if (!confirm('주문을 진행하시겠습니까?')) {
                e.preventDefault();
            }
        });

        console.log('통합 장바구니 시스템 v2.0 로드 완료');
        console.log('장바구니 아이템 수:', <?php echo count($cart_items); ?>);
    </script>
</body>
</html>
<?php
if ($connect) {
    mysqli_close($connect);
}
?>