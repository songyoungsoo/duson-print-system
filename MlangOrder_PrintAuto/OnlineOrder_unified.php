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

// 통합 인증 시스템 로드
include "../includes/auth.php";

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

// 로그인 상태는 이미 auth.php에서 처리됨
// 회원 정보 가져오기 (로그인되어 있을 때만)
$user_info = null;
$debug_info = [];

if ($is_logged_in && isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $debug_info[] = "Loading user info for user_id: " . $user_id;
    
    if (!$connect) {
        $debug_info[] = "ERROR: No database connection";
    } else {
        // 테이블명 매핑 적용된 쿼리 사용
        $user_query = "SELECT * FROM users WHERE id = ?";
        $stmt = safe_mysqli_prepare($connect, $user_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $user_info = mysqli_fetch_assoc($result);
                $debug_info[] = "User info loaded successfully";
                $debug_info[] = "Available fields: " . implode(', ', array_keys($user_info));
                $debug_info[] = "Name: " . ($user_info['name'] ?? 'none');
                $debug_info[] = "Address fields: zip=" . ($user_info['zip'] ?? 'none') . 
                               ", zip1=" . ($user_info['zip1'] ?? 'none') . 
                               ", zip2=" . ($user_info['zip2'] ?? 'none') .
                               ", address=" . ($user_info['address'] ?? 'none') . 
                               ", postcode=" . ($user_info['postcode'] ?? 'none');
            } else {
                $debug_info[] = "ERROR: No user found with id: " . $user_id;
                // 테이블 존재 여부 확인
                $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
                if ($table_check && mysqli_num_rows($table_check) > 0) {
                    $debug_info[] = "Table 'users' exists";
                    // 전체 사용자 수 확인
                    $count_result = mysqli_query($connect, "SELECT COUNT(*) as total FROM users");
                    if ($count_result) {
                        $count_row = mysqli_fetch_assoc($count_result);
                        $debug_info[] = "Total users in table: " . $count_row['total'];
                    }
                } else {
                    $debug_info[] = "ERROR: Table 'users' does not exist";
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $debug_info[] = "ERROR: Failed to prepare user query: " . mysqli_error($connect);
        }
    }
} else {
    $debug_info[] = "Not logged in or missing session data";
    $debug_info[] = "is_logged_in: " . ($is_logged_in ? 'true' : 'false');
    $debug_info[] = "SESSION user_id: " . ($_SESSION['user_id'] ?? 'not set');
}

// 디버깅을 위해 로그 출력
foreach ($debug_info as $info) {
    error_log("UserInfo Debug: " . $info);
}

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";

// 디버깅 정보 임시 표시 (개발용)
if (!empty($debug_info) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], 'dsp1830.shop') !== false)) {
    echo "<div style='position: fixed; top: 10px; right: 10px; background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; font-size: 11px; z-index: 9999; max-width: 300px;'>";
    echo "<strong>🔍 회원정보 디버깅:</strong><br>";
    foreach ($debug_info as $info) {
        echo "• " . htmlspecialchars($info) . "<br>";
    }
    echo "</div>";
}
?>

<div class="container" style="padding: 0.5rem 1rem; margin-top: -1rem;">
    <!-- 주문 정보 입력 폼 -->
    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; text-align: center; padding: 0.5rem;">
            <h2 style="margin: 0; font-size: 1rem;">📋 주문 정보 입력</h2>
            <p style="margin: 0.2rem 0 0 0; opacity: 0.9; font-size: 0.75rem;">정확한 정보를 입력해 주세요</p>
        </div>
        
        <div class="centered-form" style="padding: 0.8rem;">
            <!-- 주문 요약 (장바구니 스타일) -->
            <div style="background: linear-gradient(135deg, #f7faff 0%, #fdf2f8 100%); border-radius: 8px; padding: 20px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div style="color: #4a5568; font-weight: 600; font-size: 16px;">📋 주문 요약</div>
                    <div style="color: #718096; font-size: 13px;">총 <?php echo $total_info['count']; ?>개 상품</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div style="text-align: center; padding: 12px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="color: #718096; font-size: 12px; margin-bottom: 4px;">상품금액</div>
                        <div style="color: #2d3748; font-weight: 600; font-size: 15px;"><?php echo number_format($total_info['total']); ?>원</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div style="color: #718096; font-size: 12px; margin-bottom: 4px;">부가세</div>
                        <div style="color: #2d3748; font-weight: 600; font-size: 15px;"><?php echo number_format($total_info['total_vat'] - $total_info['total']); ?>원</div>
                    </div>
                    <div style="text-align: center; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 6px; color: white;">
                        <div style="opacity: 0.9; font-size: 12px; margin-bottom: 4px;">총 결제금액</div>
                        <div style="font-weight: 700; font-size: 18px;"><?php echo number_format($total_info['total_vat']); ?>원</div>
                    </div>
                </div>
            </div>
            
            <!-- 주문 상품 목록 (장바구니 테이블 스타일) -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #4a5568; font-weight: 600; font-size: 16px; margin-bottom: 1rem;">🛍️ 주문 상품 목록</h3>
                <div style="background: linear-gradient(135deg, #fafbff 0%, #fff9f9 100%); border-radius: 8px; overflow: hidden; border: 1px solid #e8eaed;">
                    <?php foreach ($cart_items as $index => $item): 
                        $row_bg = $index % 2 == 0 ? '#fdfdfd' : '#f9f9fb';
                    ?>
                    <div style="padding: 16px; background: <?php echo $row_bg; ?>; border-bottom: 1px solid #e8eaed; transition: background-color 0.2s ease;" onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background='<?php echo $row_bg; ?>'">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="flex: 1;">
                                <?php if ($is_direct_order): ?>
                                    <?php if ($item['product_type'] == 'envelope'): ?>
                                        <strong style="color: #2c3e50; font-size: 0.95rem;">✉️ 봉투</strong>
                                        <div style="margin-top: 0.3rem;">
                                            <span style="display: inline-block; margin-right: 0.8rem; color: #666; font-size: 0.8rem;">
                                                <strong>종류:</strong> <?php echo htmlspecialchars($item['type_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 0.8rem; color: #666; font-size: 0.8rem;">
                                                <strong>규격:</strong> <?php echo htmlspecialchars($item['size_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 0.8rem; color: #666; font-size: 0.8rem;">
                                                <strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_text']); ?>
                                            </span>
                                            <span style="display: inline-block; margin-right: 0.8rem; color: #666; font-size: 0.8rem;">
                                                <strong>디자인:</strong> <?php echo htmlspecialchars($item['design_text']); ?>
                                            </span>
                                            <?php if (!empty($item['MY_comment'])): ?>
                                                <div style="margin-top: 0.3rem; padding: 0.4rem; background: #fff3cd; border-radius: 3px; font-size: 0.8rem;">
                                                    <strong>요청사항:</strong> <?php echo htmlspecialchars($item['MY_comment']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($item['product_type'] == 'merchandisebond'): ?>
                                        <strong style="color: #2c3e50; font-size: 0.95rem;">🎫 상품권</strong>
                                        <div style="margin-top: 0.3rem;">
                                            <span style="display: inline-block; margin-right: 0.8rem; color: #666; font-size: 0.8rem;">
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
                            <div style="text-align: right; min-width: 120px;">
                                <div style="color: #4a5568; font-size: 13px; margin-bottom: 2px;">부가세포함</div>
                                <div style="font-weight: 700; color: #e53e3e; font-size: 16px;">
                                    <?php echo number_format($is_direct_order ? $item['vat_price'] : $item['st_price_vat']); ?>원
                                </div>
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
                
                <h3 style="color: #2c3e50; margin-bottom: 0.8rem;">👤 신청자 정보</h3>
                <?php if ($is_logged_in): ?>
                    <div style="background: #e8f5e8; padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem; border-left: 3px solid #27ae60;">
                        <p style="margin: 0; color: #27ae60; font-weight: bold; font-size: 0.9rem;">✅ 로그인된 회원 정보가 자동으로 입력됩니다</p>
                        <p style="margin: 0.3rem 0 0 0; color: #666; font-size: 0.85rem;">정보가 변경된 경우 직접 수정해주세요</p>
                    </div>
                <?php else: ?>
                    <div style="background: #e3f2fd; padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem; border-left: 3px solid #2196f3;">
                        <p style="margin: 0; color: #1976d2; font-weight: bold; font-size: 0.9rem;">
                            👋 회원이신가요? 
                            <button onclick="showLoginModal()" style="background: #2196f3; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.8rem; margin-left: 0.5rem; cursor: pointer;">
                                로그인하기
                            </button>
                        </p>
                        <p style="margin: 0.3rem 0 0 0; color: #666; font-size: 0.8rem;">로그인하시면 회원 정보가 자동으로 입력됩니다</p>
                    </div>
                    <p style="color: #666; margin-bottom: 0.8rem; font-size: 0.9rem;">* 신청자 정보를 정확히 입력 바랍니다.</p>
                <?php endif; ?>
                
                <!-- 컴팩트 신청자 정보 입력 (1행 4칸) -->
                <div class="single-row-grid">
                    <!-- 성명/상호 -->
                    <div>
                        <label style="display: block; margin-bottom: 0.2rem; font-weight: bold; color: #2c3e50; font-size: 0.85rem;">
                            👤 성명/상호 *
                        </label>
                        <input type="text" name="username" required 
                               value="<?php echo $is_logged_in ? htmlspecialchars($user_info['name'] ?? '') : ''; ?>"
                               style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;"
                               placeholder="성명 또는 상호명">
                    </div>
                    <!-- 이메일 -->
                    <div>
                        <label style="display: block; margin-bottom: 0.2rem; font-weight: bold; color: #2c3e50; font-size: 0.85rem;">
                            📧 이메일 *
                        </label>
                        <input type="email" name="email" required 
                               value="<?php echo $is_logged_in ? htmlspecialchars($user_info['email'] ?? '') : ''; ?>"
                               style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;"
                               placeholder="이메일 주소">
                    </div>
                    <!-- 전화번호 -->
                    <div>
                        <label style="display: block; margin-bottom: 0.2rem; font-weight: bold; color: #2c3e50; font-size: 0.85rem;">
                            📞 전화번호 *
                        </label>
                        <input type="tel" name="phone" required 
                               value="<?php echo $is_logged_in ? htmlspecialchars($user_info['phone'] ?? '') : ''; ?>"
                               style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;"
                               placeholder="전화번호">
                    </div>
                    <!-- 핸드폰 -->
                    <div>
                        <label style="display: block; margin-bottom: 0.2rem; font-weight: bold; color: #2c3e50; font-size: 0.85rem;">
                            📱 핸드폰
                        </label>
                        <input type="tel" name="Hendphone" 
                               style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;"
                               placeholder="핸드폰 번호">
                    </div>
                </div>
                
                <!-- 수령지 정보 섹션 -->
                <h3 style="color: #2c3e50; margin-bottom: 0.8rem;">🏠 우편물 수령지</h3>
                
                <?php if ($is_logged_in): ?>
                    <div style="margin-bottom: 0.8rem;">
                        <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <input type="radio" id="use_member_address" name="address_option" value="member" checked onchange="toggleAddressInput()" 
                                   style="margin-right: 0.5rem; transform: scale(1.1);">
                            <label for="use_member_address" style="font-weight: bold; color: #2c3e50; cursor: pointer; font-size: 0.9rem;">
                                회원 정보 주소 사용
                            </label>
                        </div>
                        <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <input type="radio" id="use_different_address" name="address_option" value="different" onchange="toggleAddressInput()" 
                                   style="margin-right: 0.5rem; transform: scale(1.1);">
                            <label for="use_different_address" style="font-weight: bold; color: #2c3e50; cursor: pointer; font-size: 0.9rem;">
                                다른 수령지 사용
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div id="address_section" style="margin-bottom: 1rem;">
                    <div style="display: flex; gap: 0.8rem; margin-bottom: 0.6rem;">
                        <input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호" readonly
                               style="width: 140px; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;">
                        <button type="button" onclick="sample6_execDaumPostcode()" 
                                style="padding: 8px 16px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                            우편번호 찾기
                        </button>
                    </div>
                    <input type="text" id="sample6_address" name="sample6_address" placeholder="주소" readonly required
                           style="width: 100%; padding: 5px 8px; border: 1px solid #ddd; border-radius: 3px; margin-bottom: 0.3rem; font-size: 0.8rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem;">
                        <input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소"
                               style="padding: 5px 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.8rem;">
                        <input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목"
                               style="padding: 5px 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.8rem;">
                    </div>
                </div>
                
                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.2rem; font-weight: bold; color: #2c3e50; font-size: 0.8rem;">
                        💳 입금 정보
                    </label>
                    <div style="background: #e8f4fd; padding: 0.4rem 0.5rem; border-radius: 3px; margin-bottom: 0.3rem;">
                        <p style="margin: 0; color: #2c3e50; font-size: 0.75rem;"><strong>계좌번호:</strong> 국민 999-1688-2384(두손기획인쇄 차경선)</p>
                        <p style="margin: 0.2rem 0 0 0; color: #666; font-size: 0.7rem;">주문 확인 후 입금해주세요. 입금 확인 후 작업이 시작됩니다.</p>
                    </div>
                </div>
                
                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.2rem; font-weight: bold; color: #2c3e50; font-size: 0.8rem;">
                        💬 요청사항
                    </label>
                    <div style="background: #ffebee; border: 1px solid #f8bbd9; border-radius: 3px; padding: 0.3rem 0.4rem; margin-bottom: 0.3rem;">
                        <p style="margin: 0; color: #d32f2f; font-size: 0.75rem; font-weight: bold; line-height: 1.2;">
                            🚚 퀵이나 다마스로 받거나 방문수령 시 아래 요청사항에 적어주세요
                        </p>
                    </div>
                    <textarea name="cont" rows="2" 
                              style="width: 100%; padding: 5px 8px; border: 1px solid #ddd; border-radius: 3px; resize: vertical; font-size: 0.8rem;"
                              placeholder="추가 요청사항이 있으시면 입력해주세요 (퀵/다마스 배송, 방문수령 희망 시 반드시 기재해 주세요)"></textarea>
                </div>
                
                <!-- 사업자 정보 섹션 -->
                <div style="margin-bottom: 0.5rem; border: 1px solid #e0e0e0; border-radius: 3px; padding: 0.5rem; background: #f8f9fa;">
                    <div style="display: flex; align-items: center; margin-bottom: 0.3rem;">
                        <input type="checkbox" id="is_business" name="is_business" value="1" onchange="toggleBusinessInfo()" 
                               style="margin-right: 0.4rem; transform: scale(1);">
                        <label for="is_business" style="font-weight: bold; color: #2c3e50; cursor: pointer; font-size: 0.8rem;">
                            🏢 사업자 주문 (세금계산서 발행 필요시 체크)
                        </label>
                    </div>
                    
                    <div id="business_info" style="display: none;">
                        <!-- 6열 그리드 사업자 정보 입력 -->
                        <div class="compact-info-grid business-grid">
                            <div>
                                <label style="display: block; margin-bottom: 0.1rem; font-weight: bold; color: #2c3e50; font-size: 0.7rem;">
                                    🏢 사업자등록번호
                                </label>
                                <input type="text" name="business_number" 
                                       style="width: 100%; padding: 4px 6px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.75rem;"
                                       placeholder="000-00-00000" maxlength="12">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.1rem; font-weight: bold; color: #2c3e50; font-size: 0.7rem;">
                                    👤 대표자명
                                </label>
                                <input type="text" name="business_owner" 
                                       style="width: 100%; padding: 4px 6px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.75rem;"
                                       placeholder="대표자 성명">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.1rem; font-weight: bold; color: #2c3e50; font-size: 0.7rem;">
                                    🏭 업태
                                </label>
                                <input type="text" name="business_type" 
                                       style="width: 100%; padding: 4px 6px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.75rem;"
                                       placeholder="제조업, 서비스업">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.1rem; font-weight: bold; color: #2c3e50; font-size: 0.7rem;">
                                    📋 종목
                                </label>
                                <input type="text" name="business_item" 
                                       style="width: 100%; padding: 4px 6px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.75rem;"
                                       placeholder="인쇄업, 광고업">
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 0.3rem;">
                            <label style="display: block; margin-bottom: 0.1rem; font-weight: bold; color: #2c3e50; font-size: 0.7rem;">
                                🏢 사업장 주소
                            </label>
                            <textarea name="business_address" rows="1" 
                                      style="width: 100%; padding: 4px 6px; border: 1px solid #ddd; border-radius: 3px; resize: vertical; font-size: 0.75rem;"
                                      placeholder="사업자등록증상의 사업장 주소를 입력하세요"></textarea>
                        </div>
                        
                        <div style="margin-bottom: 0.3rem;">
                            <label style="display: block; margin-bottom: 0.1rem; font-weight: bold; color: #2c3e50; font-size: 0.7rem;">
                                📧 세금계산서 발행용 이메일 *
                            </label>
                            <input type="email" name="tax_invoice_email" 
                                   style="width: 100%; padding: 4px 6px; border: 1px solid #ddd; border-radius: 3px; font-size: 0.75rem;"
                                   placeholder="세금계산서를 받을 이메일 주소를 입력하세요">
                            <div style="font-size: 0.65rem; color: #666; margin-top: 0.1rem;">
                                * 일반 연락용 이메일과 다른 경우 별도로 입력해주세요
                            </div>
                        </div>
                        
                        <div style="background: #e8f4fd; padding: 0.3rem 0.4rem; border-radius: 3px; font-size: 0.65rem; color: #2c3e50;">
                            <p style="margin: 0;"><strong>📌 안내:</strong></p>
                            <p style="margin: 0.1rem 0 0 0;">• 세금계산서 발행을 원하시면 정확한 사업자 정보를 입력해주세요</p>
                            <p style="margin: 0.1rem 0 0 0;">• 사업자등록번호는 하이픈(-) 포함하여 입력해주세요</p>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 0.8rem;">
                    <button type="submit" 
                            style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; padding: 10px 30px; border-radius: 20px; font-size: 0.95rem; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.25);">
                        🚀 주문 완료하기
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 초컴팩트 레이아웃을 위한 반응형 스타일 -->
<style>
/* 전체 페이지 높이 최적화 */
body {
    margin: 0;
    padding: 0;
    line-height: 1.2 !important;
}

.container {
    max-width: 1200px;
    margin: 0 auto !important;
    padding: 0.3rem 0.8rem !important;
}

.card {
    margin-bottom: 0.5rem !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
}

.card-header {
    padding: 0.4rem !important;
}

.card-header h2 {
    font-size: 0.9rem !important;
    margin: 0 !important;
}

.card-header p {
    font-size: 0.7rem !important;
    margin: 0.1rem 0 0 0 !important;
}

/* 컴팩트한 섹션 간격 */
h3 {
    margin: 0.3rem 0 0.2rem 0 !important;
    font-size: 0.85rem !important;
    line-height: 1.1 !important;
}

/* 입력 요소들 컴팩트화 */
input, textarea, select {
    line-height: 1.1 !important;
    border-radius: 2px !important;
}

/* 버튼 컴팩트화 */
button {
    line-height: 1.2 !important;
    border-radius: 3px !important;
}

/* 6열 그리드 시스템 */
.flex-grid-6 {
    display: grid !important;
    grid-template-columns: repeat(6, 1fr) !important;
    gap: 0.4rem !important;
    align-items: end !important;
    margin-bottom: 0.5rem !important;
}

.flex-grid-6 .col-1 { grid-column: span 1; }
.flex-grid-6 .col-2 { grid-column: span 2; }
.flex-grid-6 .col-3 { grid-column: span 3; }
.flex-grid-6 .col-4 { grid-column: span 4; }
.flex-grid-6 .col-5 { grid-column: span 5; }
.flex-grid-6 .col-6 { grid-column: span 6; }

/* 중앙 집중형 레이아웃 */
.centered-form {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* 1행 4칸 그리드 레이아웃 */
.single-row-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 0.5rem !important;
    align-items: end !important;
    margin-bottom: 0.8rem !important;
}

.single-row-grid > div {
    min-width: 0; /* 그리드 오버플로우 방지 */
}

/* 컴팩트 그리드를 6열로 강제 변경 (기존 사업자 정보용) */
.compact-info-grid {
    display: grid !important;
    grid-template-columns: repeat(6, 1fr) !important;
    gap: 0.4rem !important;
    align-items: end !important;
    margin-bottom: 0.5rem !important;
    justify-content: center !important;
}

/* 기본 span 설정 - 자동으로 2칸씩 차지 */
.compact-info-grid > div {
    grid-column: span 2;
}

/* 이메일 필드는 더 넓게 (3칸) */
.compact-info-grid > div:has(input[type="email"]) {
    grid-column: span 3 !important;
}

/* 빈 공간 생성 */
.grid-spacer {
    grid-column: span 1;
}

/* 1행 4칸 그리드 반응형 처리 */
@media (max-width: 1024px) {
    .single-row-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.4rem !important;
    }
}

@media (max-width: 480px) {
    .single-row-grid {
        grid-template-columns: 1fr !important;
        gap: 0.3rem !important;
    }
    
    .single-row-grid label {
        font-size: 0.8rem !important;
        margin-bottom: 0.1rem !important;
    }
    
    .single-row-grid input {
        padding: 6px 8px !important;
        font-size: 0.85rem !important;
    }
}

/* 초컴팩트 레이아웃을 위한 반응형 처리 (기존 사업자 정보용) */
@media (max-width: 1024px) {
    .compact-info-grid {
        grid-template-columns: repeat(4, 1fr) !important;
    }
    .compact-info-grid > div {
        grid-column: span 2 !important;
    }
}

@media (max-width: 768px) {
    .compact-info-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .compact-info-grid > div {
        grid-column: span 1 !important;
    }
    
    .compact-info-grid label {
        font-size: 0.7rem !important;
        margin-bottom: 0.1rem !important;
    }
    
    .compact-info-grid input {
        padding: 4px 6px !important;
        font-size: 0.75rem !important;
    }
    
    /* 모바일에서 전체 마진 더 줄이기 */
    h3 {
        margin-bottom: 0.3rem !important;
        font-size: 0.85rem !important;
    }
    
    .container > div {
        padding: 1rem !important;
    }
}

@media (max-width: 480px) {
    .compact-info-grid {
        grid-template-columns: 1fr !important;
        gap: 0.4rem !important;
    }
    
    /* 매우 작은 화면에서 더 컴팩트하게 */
    .compact-info-grid label {
        font-size: 0.75rem !important;
        margin-bottom: 0.1rem !important;
    }
    
    .compact-info-grid input {
        padding: 5px 6px !important;
        font-size: 0.8rem !important;
    }
}
</style>

<!-- 로그인 모달 포함 -->
<div id="loginModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0; color: #2c3e50;">🔐 로그인</h3>
            <span class="close" onclick="hideLoginModal()">&times;</span>
        </div>
        
        <?php if (!empty($login_message)): ?>
            <div class="login-message <?php echo strpos($login_message, '성공') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($login_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="modal-tabs">
            <button class="tab-btn active" onclick="switchTab('login')" id="loginTab">로그인</button>
            <button class="tab-btn" onclick="switchTab('register')" id="registerTab">회원가입</button>
        </div>
        
        <!-- 로그인 폼 -->
        <div id="loginForm" class="tab-content active">
            <form method="POST" action="">
                <input type="hidden" name="login_action" value="1">
                <div class="form-group">
                    <label>아이디</label>
                    <input type="text" name="username" required placeholder="아이디를 입력하세요">
                </div>
                <div class="form-group">
                    <label>비밀번호</label>
                    <input type="password" name="password" required placeholder="비밀번호를 입력하세요">
                </div>
                <button type="submit" class="btn-primary">로그인</button>
            </form>
        </div>
        
        <!-- 회원가입 폼 -->
        <div id="registerForm" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="register_action" value="1">
                <div class="form-group">
                    <label>아이디 *</label>
                    <input type="text" name="reg_username" required placeholder="아이디를 입력하세요">
                </div>
                <div class="form-group">
                    <label>비밀번호 * (6자 이상)</label>
                    <input type="password" name="reg_password" required placeholder="비밀번호를 입력하세요">
                </div>
                <div class="form-group">
                    <label>비밀번호 확인 *</label>
                    <input type="password" name="reg_confirm_password" required placeholder="비밀번호를 다시 입력하세요">
                </div>
                <div class="form-group">
                    <label>이름 *</label>
                    <input type="text" name="reg_name" required placeholder="이름을 입력하세요">
                </div>
                <div class="form-group">
                    <label>이메일</label>
                    <input type="email" name="reg_email" placeholder="이메일을 입력하세요">
                </div>
                <div class="form-group">
                    <label>전화번호</label>
                    <input type="tel" name="reg_phone" placeholder="전화번호를 입력하세요">
                </div>
                <button type="submit" class="btn-primary">회원가입</button>
            </form>
        </div>
    </div>
</div>

<!-- 로그인 모달 스타일 -->
<style>
.modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.close {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    color: white;
}

.close:hover {
    opacity: 0.7;
}

.modal-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.tab-btn {
    flex: 1;
    padding: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.tab-btn.active {
    background: white;
    border-bottom: 2px solid #3498db;
    color: #3498db;
}

.tab-content {
    display: none;
    padding: 1.5rem;
}

.tab-content.active {
    display: block;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    box-sizing: border-box;
}

.form-group input:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.btn-primary {
    width: 100%;
    padding: 0.75rem;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.login-message {
    padding: 0.75rem;
    margin: 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
}

.login-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.login-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

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

// 회원 주소 정보 로드 함수
function loadMemberAddress() {
    console.log('loadMemberAddress() called');
    
    if (!memberInfo) {
        console.log('No member info available');
        return;
    }
    
    console.log('Loading member address...', memberInfo);
    
    // 주소 필드에 회원 정보 입력
    if (memberInfo.postcode) {
        const postcodeField = document.getElementById('sample6_postcode');
        if (postcodeField) postcodeField.value = memberInfo.postcode;
    }
    
    if (memberInfo.address) {
        const addressField = document.getElementById('sample6_address');
        if (addressField) addressField.value = memberInfo.address;
    }
    
    if (memberInfo.detailAddress) {
        const detailField = document.getElementById('sample6_detailAddress');
        if (detailField) detailField.value = memberInfo.detailAddress;
    }
    
    if (memberInfo.extraAddress) {
        const extraField = document.getElementById('sample6_extraAddress');
        if (extraField) extraField.value = memberInfo.extraAddress;
    }
    
    console.log('Member address loaded successfully');
}

// 주소 입력 방식 토글 함수
function toggleAddressInput() {
    const memberAddressRadio = document.getElementById('use_member_address');
    const addressSection = document.getElementById('address_section');
    const addressFields = ['sample6_postcode', 'sample6_address', 'sample6_detailAddress', 'sample6_extraAddress'];
    
    if (memberAddressRadio && memberAddressRadio.checked) {
        // 회원 주소 사용 - 필드 비활성화 및 회원 정보로 채우기
        console.log('Using member address - loading member info...');
        addressSection.style.opacity = '0.6';
        addressSection.style.pointerEvents = 'none';
        
        // 회원 정보로 주소 필드 채우기
        loadMemberAddress();
        
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

// 로그인 모달 관련 함수들
function showLoginModal() {
    document.getElementById('loginModal').style.display = 'flex';
    document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
}

function hideLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // 스크롤 복원
}

function switchTab(tab) {
    // 모든 탭 버튼과 콘텐츠 비활성화
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // 선택된 탭 활성화
    if (tab === 'login') {
        document.getElementById('loginTab').classList.add('active');
        document.getElementById('loginForm').classList.add('active');
    } else if (tab === 'register') {
        document.getElementById('registerTab').classList.add('active');
        document.getElementById('registerForm').classList.add('active');
    }
}

// 모달 외부 클릭 시 닫기
document.addEventListener('click', function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target === modal) {
        hideLoginModal();
    }
});

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideLoginModal();
    }
});

<?php if (!empty($login_message) && (strpos($login_message, '성공') !== false)): ?>
    // 로그인 성공 시 페이지 새로고침
    setTimeout(function() {
        location.reload();
    }, 1500);
<?php elseif (!empty($login_message)): ?>
    // 로그인 시도 후 메시지가 있으면 모달 표시
    document.addEventListener('DOMContentLoaded', function() {
        showLoginModal();
    });
<?php endif; ?>

// 회원 정보를 JavaScript 변수로 전달
<?php if ($is_logged_in && $user_info): ?>
var memberInfo = {
    postcode: '<?php echo htmlspecialchars($user_info['postcode'] ?? $user_info['zip'] ?? ''); ?>',
    address: '<?php echo htmlspecialchars($user_info['address'] ?? $user_info['zip1'] ?? ''); ?>',
    detailAddress: '<?php echo htmlspecialchars($user_info['detail_address'] ?? $user_info['zip2'] ?? ''); ?>',
    extraAddress: '<?php echo htmlspecialchars($user_info['extra_address'] ?? ''); ?>',
    name: '<?php echo htmlspecialchars($user_info['name'] ?? ''); ?>',
    email: '<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>',
    phone: '<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>'
};
console.log('Member info loaded:', memberInfo);
<?php else: ?>
var memberInfo = null;
console.log('No member info available');
<?php endif; ?>

// 페이지 로드 시 실행
document.addEventListener('DOMContentLoaded', function() {
    // 회원 정보 자동 입력 먼저 실행
    <?php if ($is_logged_in && $user_info): ?>
        console.log('Loading member address on page load...');
        loadMemberAddress();
    <?php endif; ?>
    
    // 페이지 로드 시 주소 입력 방식 초기화
    <?php if ($is_logged_in): ?>
        setTimeout(() => toggleAddressInput(), 100); // 약간의 지연 후 실행
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