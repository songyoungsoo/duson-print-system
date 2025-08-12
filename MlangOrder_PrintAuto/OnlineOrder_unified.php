<?php
/**
 * 통합 장바구니 주문 처리
 * 경로: MlangOrder_PrintAuto/OnlineOrder_unified.php
 */

session_start();
$session_id = session_id();

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// 헬퍼 함수 포함
include "../MlangPrintAuto/shop_temp_helper.php";

// 페이지 설정
$page_title = '📋 주문 정보 입력';
$current_page = 'order';

// 주문 타입 확인
$is_direct_order = isset($_GET['direct_order']) && $_GET['direct_order'] == '1';
$is_post_order = !empty($_POST['product_type']) && !is_array($_POST['product_type']); // 단일 상품 직접 주문
$is_cart_post_order = !empty($_POST['product_type']) && is_array($_POST['product_type']); // 장바구니에서 온 주문
$cart_items = [];
$total_info = ['total' => 0, 'total_vat' => 0, 'count' => 0];

if ($is_post_order) {
    // POST로 온 직접 주문 처리 (카다록 등)
    $product_type = $_POST['product_type'] ?? 'cadarok';
    
    if ($product_type == 'cadarok') {
        // 카다록 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'cadarok',
            'type_text' => $_POST['selected_category'] ?? '',
            'size_text' => $_POST['selected_size'] ?? '',
            'paper_text' => $_POST['selected_paper'] ?? '',
            'quantity_text' => $_POST['selected_quantity'] ?? '',
            'design_text' => $_POST['selected_order'] ?? '',
            'price' => intval($_POST['Price'] ?? 0),
            'vat_price' => intval($_POST['Total_Price'] ?? 0),
            'MY_type' => $_POST['MY_type'] ?? '',
            'MY_Fsd' => $_POST['MY_Fsd'] ?? '',
            'PN_type' => $_POST['PN_type'] ?? '',
            'MY_amount' => $_POST['MY_amount'] ?? '',
            'ordertype' => $_POST['ordertype'] ?? '',
            'MY_comment' => '카다록/리플렛 주문'
        ];
        
        $cart_items = [$direct_item];
        $total_info = [
            'total' => $direct_item['price'],
            'total_vat' => $direct_item['vat_price'],
            'count' => 1
        ];
        $is_direct_order = true;
    }
} elseif ($is_direct_order) {
    // GET으로 온 직접 주문 처리 (기존)
    $product_type = $_GET['product_type'] ?? 'leaflet';
    
    if ($product_type == 'envelope') {
        // 봉투 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'envelope',
            'type_text' => $_GET['type_text'] ?? '',
            'size_text' => $_GET['size_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'MY_type' => $_GET['MY_type'] ?? '',
            'PN_type' => $_GET['PN_type'] ?? '',
            'MY_amount' => $_GET['MY_amount'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? '',
            'MY_comment' => $_GET['MY_comment'] ?? ''
        ];
    } elseif ($product_type == 'merchandisebond') {
        // 상품권 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'merchandisebond',
            'type_text' => $_GET['type_text'] ?? '',
            'size_text' => $_GET['size_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'po_text' => $_GET['po_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'MY_type' => $_GET['MY_type'] ?? '',
            'PN_type' => $_GET['PN_type'] ?? '',
            'MY_amount' => $_GET['MY_amount'] ?? '',
            'POtype' => $_GET['POtype'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? '',
            'MY_comment' => $_GET['MY_comment'] ?? ''
        ];
    } elseif ($product_type == 'namecard') {
        // 명함 직접 주문
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => 'namecard',
            'type_text' => $_GET['type_text'] ?? '',
            'paper_text' => $_GET['paper_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'sides_text' => $_GET['sides_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'NC_type' => $_GET['NC_type'] ?? '',
            'NC_paper' => $_GET['NC_paper'] ?? '',
            'NC_amount' => $_GET['NC_amount'] ?? '',
            'NC_sides' => $_GET['NC_sides'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? '',
            'NC_comment' => $_GET['NC_comment'] ?? ''
        ];
    } else {
        // 전단지 직접 주문 (기존)
        $direct_item = [
            'id' => 'direct_order',
            'product_type' => $_GET['product_type'] ?? 'leaflet',
            'color_text' => $_GET['color_text'] ?? '',
            'paper_type_text' => $_GET['paper_type_text'] ?? '',
            'paper_size_text' => $_GET['paper_size_text'] ?? '',
            'sides_text' => $_GET['sides_text'] ?? '',
            'quantity_text' => $_GET['quantity_text'] ?? '',
            'design_text' => $_GET['design_text'] ?? '',
            'price' => intval($_GET['price'] ?? 0),
            'vat_price' => intval($_GET['vat_price'] ?? 0),
            'MY_type' => $_GET['MY_type'] ?? '',
            'MY_Fsd' => $_GET['MY_Fsd'] ?? '',
            'PN_type' => $_GET['PN_type'] ?? '',
            'POtype' => $_GET['POtype'] ?? '',
            'MY_amount' => $_GET['MY_amount'] ?? '',
            'ordertype' => $_GET['ordertype'] ?? ''
        ];
    }
    
    $cart_items[] = $direct_item;
    $total_info = [
        'total' => $direct_item['price'],
        'total_vat' => $direct_item['vat_price'],
        'count' => 1
    ];
} elseif ($is_cart_post_order) {
    // 장바구니에서 온 POST 데이터 처리 - 실제 세션 데이터 사용
    error_log("Debug: Processing cart POST data");
    
    // 실제 장바구니 데이터를 세션에서 가져와서 자세한 정보 표시
    $cart_result = getCartItems($connect, $session_id);
    
    if ($cart_result) {
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $formatted_item = formatCartItemForDisplay($connect, $item);
            $cart_items[] = $formatted_item;
            error_log("Debug: Cart POST item: " . $item['product_type'] . " - " . $item['st_price_vat']);
        }
        $total_info = calculateCartTotal($connect, $session_id);
    } else {
        // 세션 데이터가 없으면 POST 데이터로 기본 구성
        error_log("Debug: No session data, using POST fallback");
        $product_types = $_POST['product_type'] ?? [];
        $prices = $_POST['price'] ?? [];
        $prices_vat = $_POST['price_vat'] ?? [];
        
        for ($i = 0; $i < count($product_types); $i++) {
            $cart_items[] = [
                'no' => 'cart_' . $i,
                'product_type' => $product_types[$i] ?? '',
                'name' => ucfirst($product_types[$i] ?? '상품'),
                'st_price' => floatval($prices[$i] ?? 0),
                'st_price_vat' => floatval($prices_vat[$i] ?? 0),
                'details' => ['정보' => '장바구니 상품']
            ];
        }
        
        $total_info = [
            'total' => intval($_POST['total_price'] ?? 0),
            'total_vat' => intval($_POST['total_price_vat'] ?? 0),
            'count' => intval($_POST['items_count'] ?? 0)
        ];
    }
    
    error_log("Debug: Cart POST items loaded: " . count($cart_items));
} else {
    // 세션 장바구니 데이터 조회 - 디버깅 추가
    error_log("Debug: Getting cart items for session_id: " . $session_id);
    $cart_result = getCartItems($connect, $session_id);
    error_log("Debug: Cart result: " . ($cart_result ? 'found' : 'not found'));
    
    if ($cart_result) {
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $formatted_item = formatCartItemForDisplay($connect, $item);
            $cart_items[] = $formatted_item;
            error_log("Debug: Added cart item: " . $item['product_type'] . " - " . $item['st_price_vat']);
        }
        $total_info = calculateCartTotal($connect, $session_id);
        error_log("Debug: Total cart items: " . count($cart_items));
    } else {
        error_log("Debug: No cart result found");
    }
    
    // 장바구니가 비어있으면 리다이렉트
    if (empty($cart_items)) {
        error_log("Debug: Cart is empty, redirecting");
        echo "<script>alert('장바구니가 비어있습니다.'); location.href='../MlangPrintAuto/shop/cart.php';</script>";
        exit;
    }
}

// 로그인 상태 확인 및 회원 정보 가져오기
$user_info = null;
$is_logged_in = false;

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($connect, $user_query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user_info = mysqli_fetch_assoc($result);
            $is_logged_in = true;
        }
        mysqli_stmt_close($stmt);
    }
}

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";
?>

<div class="container">
    <!-- 주문 정보 입력 폼 -->
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; text-align: center; padding: 2rem;">
            <h2 style="margin: 0; font-size: 2rem;">📋 주문 정보 입력</h2>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">정확한 정보를 입력해 주세요</p>
        </div>
        
        <div style="padding: 2rem;">
            <!-- 주문 요약 -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">📦 주문 요약</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="text-align: center; padding: 1rem; background: white; border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #3498db;"><?php echo $total_info['count']; ?>개</div>
                        <div style="color: #666;">총 상품수</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: white; border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #27ae60;"><?php echo number_format($total_info['total']); ?>원</div>
                        <div style="color: #666;">총 인쇄비</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: white; border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #e74c3c;"><?php echo number_format($total_info['total_vat'] - $total_info['total']); ?>원</div>
                        <div style="color: #666;">부가세</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: white; border-radius: 8px;">
                        <div style="font-size: 1.8rem; font-weight: bold; color: #e74c3c;"><?php echo number_format($total_info['total_vat']); ?>원</div>
                        <div style="color: #666;">총 결제금액</div>
                    </div>
                </div>
            </div>
            
            <!-- 주문 상품 목록 -->
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">🛍️ 주문 상품 목록</h3>
                <div style="background: white; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
                    <?php foreach ($cart_items as $index => $item): ?>
                    <div style="padding: 1rem; border-bottom: 1px solid #eee; <?php echo $index % 2 == 0 ? 'background: #f9f9f9;' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <?php if ($is_direct_order): ?>
                                    <?php if ($item['product_type'] == 'envelope'): ?>
                                        <strong style="color: #2c3e50; font-size: 1.1rem;">✉️ 봉투</strong>
                                        <div style="margin-top: 0.5rem;">
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>종류:</strong> <?php echo htmlspecialchars($item['type_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>규격:</strong> <?php echo htmlspecialchars($item['size_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>디자인:</strong> <?php echo htmlspecialchars($item['design_text']); ?>
                                            </span>
                                            <?php if (!empty($item['MY_comment'])): ?>
                                                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                                                    <strong>요청사항:</strong> <?php echo htmlspecialchars($item['MY_comment']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($item['product_type'] == 'merchandisebond'): ?>
                                        <strong style="color: #2c3e50; font-size: 1.1rem;">🎫 상품권</strong>
                                        <div style="margin-top: 0.5rem;">
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>종류:</strong> <?php echo htmlspecialchars($item['type_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>후가공:</strong> <?php echo htmlspecialchars($item['size_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>인쇄면:</strong> <?php echo htmlspecialchars($item['po_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>주문방법:</strong> <?php echo htmlspecialchars($item['design_text']); ?>
                                            </span>
                                            <?php if (!empty($item['MY_comment'])): ?>
                                                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                                                    <strong>요청사항:</strong> <?php echo htmlspecialchars($item['MY_comment']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($item['product_type'] == 'namecard'): ?>
                                        <strong style="color: #2c3e50; font-size: 1.1rem;">📇 명함</strong>
                                        <div style="margin-top: 0.5rem;">
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>명함종류:</strong> <?php echo htmlspecialchars($item['type_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>용지종류:</strong> <?php echo htmlspecialchars($item['paper_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>인쇄면:</strong> <?php echo htmlspecialchars($item['sides_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>디자인:</strong> <?php echo htmlspecialchars($item['design_text'] ?? ''); ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($item['NC_comment'])): ?>
                                            <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                                                <strong>요청사항:</strong> <?php echo htmlspecialchars($item['NC_comment']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($item['product_type'] == 'cadarok'): ?>
                                        <strong style="color: #2c3e50; font-size: 1.1rem;">📚 카다록/리플렛</strong>
                                        <div style="margin-top: 0.5rem;">
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>구분:</strong> <?php echo htmlspecialchars($item['type_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>규격:</strong> <?php echo htmlspecialchars($item['size_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>종이종류:</strong> <?php echo htmlspecialchars($item['paper_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>주문방법:</strong> <?php echo htmlspecialchars($item['design_text'] ?? ''); ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($item['MY_comment'])): ?>
                                            <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                                                <strong>요청사항:</strong> <?php echo htmlspecialchars($item['MY_comment']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <strong style="color: #2c3e50; font-size: 1.1rem;">📄 전단지</strong>
                                        <div style="margin-top: 0.5rem;">
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>인쇄색상:</strong> <?php echo htmlspecialchars($item['color_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>종이종류:</strong> <?php echo htmlspecialchars($item['paper_type_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>종이규격:</strong> <?php echo htmlspecialchars($item['paper_size_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>인쇄면:</strong> <?php echo htmlspecialchars($item['sides_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_text'] ?? ''); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong>디자인:</strong> <?php echo htmlspecialchars($item['design_text'] ?? ''); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <strong style="color: #2c3e50; font-size: 1.1rem;"><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <div style="margin-top: 0.5rem;">
                                        <?php foreach ($item['details'] as $key => $value): ?>
                                            <span style="display: inline-block; margin-right: 1rem; color: #666; font-size: 0.9rem;">
                                                <strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if ($item['MY_comment']): ?>
                                        <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                                            <strong>요청사항:</strong> <?php echo htmlspecialchars($item['MY_comment']); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.2rem; font-weight: bold; color: #e74c3c;">
                                    <?php echo number_format($is_direct_order ? $item['vat_price'] : $item['st_price_vat']); ?>원
                                </div>
                                <div style="font-size: 0.9rem; color: #666;">VAT 포함</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- 주문자 정보 입력 폼 -->
            <form method="post" action="ProcessOrder_unified.php" id="orderForm">
                <!-- 주문 데이터를 hidden으로 전달 -->
                <input type="hidden" name="total_price" value="<?php echo $total_info['total']; ?>">
                <input type="hidden" name="total_price_vat" value="<?php echo $total_info['total_vat']; ?>">
                <input type="hidden" name="items_count" value="<?php echo $total_info['count']; ?>">
                <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                <input type="hidden" name="is_direct_order" value="<?php echo $is_direct_order ? '1' : '0'; ?>">
                
                <?php if ($is_direct_order): ?>
                    <!-- 직접 주문 데이터 전달 -->
                    <input type="hidden" name="direct_product_type" value="<?php echo htmlspecialchars($cart_items[0]['product_type']); ?>">
                    <input type="hidden" name="direct_MY_type" value="<?php echo htmlspecialchars($cart_items[0]['MY_type']); ?>">
                    <input type="hidden" name="direct_MY_Fsd" value="<?php echo htmlspecialchars($cart_items[0]['MY_Fsd']); ?>">
                    <input type="hidden" name="direct_PN_type" value="<?php echo htmlspecialchars($cart_items[0]['PN_type']); ?>">
                    <input type="hidden" name="direct_POtype" value="<?php echo htmlspecialchars($cart_items[0]['POtype']); ?>">
                    <input type="hidden" name="direct_MY_amount" value="<?php echo htmlspecialchars($cart_items[0]['MY_amount']); ?>">
                    <input type="hidden" name="direct_ordertype" value="<?php echo htmlspecialchars($cart_items[0]['ordertype']); ?>">
                    <input type="hidden" name="direct_color_text" value="<?php echo htmlspecialchars($cart_items[0]['color_text']); ?>">
                    <input type="hidden" name="direct_paper_type_text" value="<?php echo htmlspecialchars($cart_items[0]['paper_type_text']); ?>">
                    <input type="hidden" name="direct_paper_size_text" value="<?php echo htmlspecialchars($cart_items[0]['paper_size_text']); ?>">
                    <input type="hidden" name="direct_sides_text" value="<?php echo htmlspecialchars($cart_items[0]['sides_text']); ?>">
                    <input type="hidden" name="direct_quantity_text" value="<?php echo htmlspecialchars($cart_items[0]['quantity_text']); ?>">
                    <input type="hidden" name="direct_design_text" value="<?php echo htmlspecialchars($cart_items[0]['design_text']); ?>">
                    <input type="hidden" name="direct_price" value="<?php echo $cart_items[0]['price']; ?>">
                    <input type="hidden" name="direct_vat_price" value="<?php echo $cart_items[0]['vat_price']; ?>">
                <?php endif; ?>
                
                <?php if (!$is_logged_in): ?>
                    <!-- 비회원인 경우 기본값으로 different 설정 -->
                    <input type="hidden" name="address_option" value="different">
                <?php endif; ?>
                
                <h3 style="color: #2c3e50; margin-bottom: 1.5rem;">👤 신청자 정보</h3>
                <?php if ($is_logged_in): ?>
                    <div style="background: #e8f5e8; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #27ae60;">
                        <p style="margin: 0; color: #27ae60; font-weight: bold;">✅ 로그인된 회원 정보가 자동으로 입력됩니다</p>
                        <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">정보가 변경된 경우 직접 수정해주세요</p>
                    </div>
                <?php else: ?>
                    <p style="color: #666; margin-bottom: 2rem;">* 신청자 정보를 정확히 입력 바랍니다.</p>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                            👤 성명/상호 *
                        </label>
                        <input type="text" name="username" required 
                               value="<?php echo $is_logged_in ? htmlspecialchars($user_info['name'] ?? '') : ''; ?>"
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                               placeholder="성명 또는 상호명을 입력하세요">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                            📧 이메일 *
                        </label>
                        <input type="email" name="email" required 
                               value="<?php echo $is_logged_in ? htmlspecialchars($user_info['email'] ?? '') : ''; ?>"
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;"
                               placeholder="이메일 주소를 입력하세요">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                            📞 전화번호 *
                        </label>
                        <input type="tel" name="phone" required 
                               value="<?php echo $is_logged_in ? htmlspecialchars($user_info['phone'] ?? '') : ''; ?>"
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;"
                               placeholder="전화번호를 입력하세요">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                            📱 핸드폰
                        </label>
                        <input type="tel" name="Hendphone" 
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;"
                               placeholder="핸드폰 번호를 입력하세요">
                    </div>
                </div>
                
                <!-- 수령지 정보 섹션 -->
                <h3 style="color: #2c3e50; margin-bottom: 1.5rem;">🏠 우편물 수령지</h3>
                
                <?php if ($is_logged_in): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <input type="radio" id="use_member_address" name="address_option" value="member" checked onchange="toggleAddressInput()" 
                                   style="margin-right: 0.5rem; transform: scale(1.2);">
                            <label for="use_member_address" style="font-weight: bold; color: #2c3e50; cursor: pointer;">
                                회원 정보 주소 사용
                            </label>
                        </div>
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <input type="radio" id="use_different_address" name="address_option" value="different" onchange="toggleAddressInput()" 
                                   style="margin-right: 0.5rem; transform: scale(1.2);">
                            <label for="use_different_address" style="font-weight: bold; color: #2c3e50; cursor: pointer;">
                                다른 수령지 사용
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div id="address_section" style="margin-bottom: 2rem;">
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호" readonly
                               style="width: 150px; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                        <button type="button" onclick="sample6_execDaumPostcode()" 
                                style="padding: 12px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            우편번호 찾기
                        </button>
                    </div>
                    <input type="text" id="sample6_address" name="sample6_address" placeholder="주소" readonly required
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 1rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소"
                               style="padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                        <input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목"
                               style="padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                        💳 입금 정보
                    </label>
                    <div style="background: #e8f4fd; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                        <p style="margin: 0; color: #2c3e50;"><strong>계좌번호:</strong> 국민 999-1688-2384(두손기획인쇄 차경선)</p>
                        <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">주문 확인 후 입금해주세요. 입금 확인 후 작업이 시작됩니다.</p>
                    </div>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                        💬 요청사항
                    </label>
                    <div style="background: #ffebee; border: 1px solid #f8bbd9; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <p style="margin: 0; color: #d32f2f; font-size: 1.1rem; font-weight: bold; line-height: 1.4;">
                            🚚 퀵이나 다마스로 받거나 방문수령 시 아래 요청사항에 적어주세요
                        </p>
                    </div>
                    <textarea name="cont" rows="4" 
                              style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; resize: vertical;"
                              placeholder="추가 요청사항이 있으시면 입력해주세요 (퀵/다마스 배송, 방문수령 희망 시 반드시 기재해 주세요)"></textarea>
                </div>
                
                <!-- 사업자 정보 섹션 -->
                <div style="margin-bottom: 2rem; border: 1px solid #e0e0e0; border-radius: 10px; padding: 1.5rem; background: #f8f9fa;">
                    <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                        <input type="checkbox" id="is_business" name="is_business" value="1" onchange="toggleBusinessInfo()" 
                               style="margin-right: 0.5rem; transform: scale(1.2);">
                        <label for="is_business" style="font-weight: bold; color: #2c3e50; cursor: pointer;">
                            🏢 사업자 주문 (세금계산서 발행 필요시 체크)
                        </label>
                    </div>
                    
                    <div id="business_info" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                                    🏢 사업자등록번호
                                </label>
                                <input type="text" name="business_number" 
                                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="000-00-00000" maxlength="12">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                                    👤 대표자명
                                </label>
                                <input type="text" name="business_owner" 
                                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="대표자 성명을 입력하세요">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                                    🏭 업태
                                </label>
                                <input type="text" name="business_type" 
                                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="예: 제조업, 서비스업">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                                    📋 종목
                                </label>
                                <input type="text" name="business_item" 
                                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="예: 인쇄업, 광고업">
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                                🏢 사업장 주소
                            </label>
                            <textarea name="business_address" rows="2" 
                                      style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; resize: vertical;"
                                      placeholder="사업자등록증상의 사업장 주소를 입력하세요"></textarea>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #2c3e50;">
                                📧 세금계산서 발행용 이메일 *
                            </label>
                            <input type="email" name="tax_invoice_email" 
                                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;"
                                   placeholder="세금계산서를 받을 이메일 주소를 입력하세요">
                            <div style="font-size: 0.85rem; color: #666; margin-top: 0.3rem;">
                                * 일반 연락용 이메일과 다른 경우 별도로 입력해주세요
                            </div>
                        </div>
                        
                        <div style="background: #e8f4fd; padding: 1rem; border-radius: 5px; font-size: 0.9rem; color: #2c3e50;">
                            <p style="margin: 0;"><strong>📌 안내사항:</strong></p>
                            <p style="margin: 0.5rem 0 0 0;">• 세금계산서 발행을 원하시면 정확한 사업자 정보를 입력해주세요</p>
                            <p style="margin: 0.3rem 0 0 0;">• 사업자등록번호는 하이픈(-) 포함하여 입력해주세요</p>
                            <p style="margin: 0.3rem 0 0 0;">• 세금계산서 발행용 이메일은 필수 입력 항목입니다</p>
                            <p style="margin: 0.3rem 0 0 0;">• 입력하신 정보는 세금계산서 발행 시에만 사용됩니다</p>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 3rem;">
                    <button type="submit" 
                            style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; padding: 20px 50px; border-radius: 50px; font-size: 1.2rem; font-weight: bold; cursor: pointer; box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);">
                        🚀 주문 완료하기
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 다음 우편번호 서비스 -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
function sample6_execDaumPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            var addr = '';
            var extraAddr = '';

            if (data.userSelectedType === 'R') {
                addr = data.roadAddress;
            } else {
                addr = data.jibunAddress;
            }

            if(data.userSelectedType === 'R'){
                if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                    extraAddr += data.bname;
                }
                if(data.buildingName !== '' && data.apartment === 'Y'){
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                if(extraAddr !== ''){
                    extraAddr = ' (' + extraAddr + ')';
                }
                document.getElementById("sample6_extraAddress").value = extraAddr;
            } else {
                document.getElementById("sample6_extraAddress").value = '';
            }

            document.getElementById('sample6_postcode').value = data.zonecode;
            document.getElementById('sample6_address').value = addr;
            document.getElementById("sample6_detailAddress").focus();
        }
    }).open();
}

// 사업자 정보 토글 함수
function toggleBusinessInfo() {
    const checkbox = document.getElementById('is_business');
    const businessInfo = document.getElementById('business_info');
    
    if (checkbox.checked) {
        businessInfo.style.display = 'block';
        // 사업자 정보 필드들을 필수로 만들기
        const businessFields = businessInfo.querySelectorAll('input[name^="business_"], textarea[name^="business_"], input[name="tax_invoice_email"]');
        businessFields.forEach(field => {
            if (field.name === 'business_number' || field.name === 'business_owner' || field.name === 'tax_invoice_email') {
                field.required = true;
            }
        });
    } else {
        businessInfo.style.display = 'none';
        // 사업자 정보 필드들의 필수 속성 제거 및 값 초기화
        const businessFields = businessInfo.querySelectorAll('input[name^="business_"], textarea[name^="business_"], input[name="tax_invoice_email"]');
        businessFields.forEach(field => {
            field.required = false;
            field.value = '';
        });
    }
}

// 주소 입력 방식 토글 함수
function toggleAddressInput() {
    const memberAddressRadio = document.getElementById('use_member_address');
    const addressSection = document.getElementById('address_section');
    const addressFields = ['sample6_postcode', 'sample6_address', 'sample6_detailAddress', 'sample6_extraAddress'];
    
    if (memberAddressRadio && memberAddressRadio.checked) {
        // 회원 주소 사용 - 필드 비활성화 및 회원 정보로 채우기
        addressSection.style.opacity = '0.6';
        addressSection.style.pointerEvents = 'none';
        
        // 회원 주소 정보가 있다면 자동 입력
        <?php if ($is_logged_in && isset($user_info)): ?>
            <?php if (!empty($user_info['address'])): ?>
                document.getElementById('sample6_postcode').value = '<?php echo htmlspecialchars($user_info['postcode'] ?? ''); ?>';
                document.getElementById('sample6_address').value = '<?php echo htmlspecialchars($user_info['address'] ?? ''); ?>';
                document.getElementById('sample6_detailAddress').value = '<?php echo htmlspecialchars($user_info['detail_address'] ?? ''); ?>';
                document.getElementById('sample6_extraAddress').value = '<?php echo htmlspecialchars($user_info['extra_address'] ?? ''); ?>';
            <?php endif; ?>
        <?php endif; ?>
        
        addressFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.required = false;
        });
    } else {
        // 다른 주소 사용 - 필드 활성화
        addressSection.style.opacity = '1';
        addressSection.style.pointerEvents = 'auto';
        
        // 주소 필드를 필수로 설정
        const addressField = document.getElementById('sample6_address');
        if (addressField) addressField.required = true;
        
        // 필드 초기화
        addressFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.value = '';
        });
    }
}

// 사업자등록번호 자동 하이픈 추가
document.addEventListener('DOMContentLoaded', function() {
    // 페이지 로드 시 주소 입력 방식 초기화
    <?php if ($is_logged_in): ?>
        toggleAddressInput();
    <?php endif; ?>
    
    const businessNumberInput = document.querySelector('input[name="business_number"]');
    if (businessNumberInput) {
        businessNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length >= 3 && value.length <= 5) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            } else if (value.length > 5) {
                value = value.substring(0, 3) + '-' + value.substring(3, 5) + '-' + value.substring(5, 10);
            }
            e.target.value = value;
        });
    }
});
</script>

<?php
// 공통 푸터 포함
include "../includes/footer.php";
?>