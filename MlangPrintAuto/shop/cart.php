<?php
session_start();
$session_id = session_id();

// 경로 수정: MlangPrintAuto/shop/에서 루트의 db.php 접근
include "../../db.php";
$connect = $db; // db.php에서 $db 변수 사용

error_log("Attempting to connect to database");

// UTF-8 설정과 연결 확인
if ($connect) {
    error_log("Database connection successful");
    if (!mysqli_set_charset($connect, 'utf8')) {
        error_log("Error setting UTF-8 charset: " . mysqli_error($connect));
    }
}

// ID로 한글명 가져오기 함수
function getKoreanName($connect, $id) {
    if (!$connect || !$id) {
        return $id; // 연결이 없거나 ID가 없으면 원본 반환
    }
    
    $query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return $id;
    }
    
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }
    
    mysqli_stmt_close($stmt);
    return $id; // 찾지 못하면 원본 ID 반환
}

// 장바구니 내용 가져오기 (통합 버전)
function getCartItems($connect, $session_id) {
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
            $items[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    
    // 배열을 결과셋처럼 사용할 수 있도록 변환
    if (empty($items)) {
        return false;
    }
    
    // 임시로 배열을 전역 변수에 저장하여 mysqli_fetch_assoc처럼 사용
    global $cart_items_array;
    $cart_items_array = $items;
    
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
    <title>🛒 통합 장바구니</title>
    <link rel="stylesheet" href="../../css/style250801.css">
</head>
<body>
    <div class="container">
        <!-- 헤더 섹션 -->
        <div class="hero-section" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 2rem 0; text-align: center; margin-bottom: 2rem; border-radius: 15px;">
            <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">🛒 통합 장바구니</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">모든 인쇄 상품을 한 번에 주문하세요</p>
        </div>
        
        <!-- 네비게이션 바 -->
        <div style="margin-bottom: 2rem; padding: 1.5rem; background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <!-- 품목 버튼들 -->
            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1rem; justify-content: center;">
                <a href="../inserted/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">📄 전단지</a>
                
                <a href="../cadarok/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">📖 카다록</a>
                
                <a href="../NameCard/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">📇 명함</a>
                
                <a href="view_modern.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">🏷️ 스티커</a>
                
                <a href="../msticker/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">🧲 자석스티커</a>
                
                <a href="../envelope/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">✉️ 봉투</a>
                
                <a href="../LittlePrint/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">🎨 포스터</a>
                
                <a href="../MerchandiseBond/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">🎫 상품권</a>
                
                <a href="../NcrFlambeau/index.php" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'">📋 양식지</a>
            </div>
            
            <!-- 액션 버튼들 -->
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 1.5rem;">
                <button onclick="continueShopping()" style="padding: 12px 25px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 25px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 10px rgba(0,0,0,0.2)'">🛍️ 계속 쇼핑</button>
                
                <button onclick="clearCart()" style="padding: 12px 25px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 25px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 10px rgba(0,0,0,0.2)'">🗑️ 전체 삭제</button>
            </div>
        </div>

        <!-- 장바구니 메인 콘텐츠 -->
        <div id="cartContent" style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <?php if (!empty($cart_items)): ?>
                <form method="post" action="../../MlangOrder_PrintAuto/OnlineOrder_unified.php" id="orderForm">
                    <input type="hidden" name="SubmitMode" value="OrderOne">
                    <?php 
                    $total_price = 0;
                    $total_vat = 0;
                    $items_data = array();
                    
                    foreach ($cart_items as $item):
                        $total_price += $item['st_price'];
                        $total_vat += $item['st_price_vat'];
                        
                        // 각 아이템의 데이터를 hidden 필드로 저장
                        $items_data[] = $item;
                    ?>
                        <div class="cart-item" style="border: 1px solid #e9ecef; padding: 1.5rem; margin-bottom: 1rem; border-radius: 10px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <h3 style="color: #2c3e50; margin: 0;">
                                    <?php 
                                    $product_name = '상품';
                                    switch($item['product_type']) {
                                        case 'cadarok':
                                            $product_name = '카달로그';
                                            break;
                                        case 'sticker':
                                            $product_name = '스티커';
                                            break;
                                        case 'leaflet':
                                            $product_name = '전단지';
                                            break;
                                        case 'namecard':
                                            $product_name = '명함';
                                            break;
                                        case 'envelope':
                                            $product_name = '봉투';
                                            break;
                                        case 'merchandisebond':
                                            $product_name = '상품권';
                                            break;
                                        case 'littleprint':
                                            $product_name = '포스터';
                                            break;
                                    }
                                    echo htmlspecialchars($product_name);
                                    ?>
                                </h3>
                                <a href="?delete=<?php echo $item['no']; ?>" 
                                   onclick="return confirm('이 상품을 삭제하시겠습니까?')"
                                   class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">
                                    ❌ 삭제
                                </a>
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <?php if ($item['product_type'] == 'sticker'): ?>
                                    <!-- 스티커 정보 표시 -->
                                    <?php if (!empty($item['jong'])): ?>
                                        <p><strong>종류:</strong> <?php echo htmlspecialchars($item['jong']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['garo']) && !empty($item['sero'])): ?>
                                        <p><strong>크기:</strong> <?php echo htmlspecialchars($item['garo']); ?> × <?php echo htmlspecialchars($item['sero']); ?>mm</p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['mesu'])): ?>
                                        <p><strong>수량:</strong> <?php echo htmlspecialchars($item['mesu']); ?>매</p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['domusong'])): ?>
                                        <p><strong>옵션:</strong> <?php echo htmlspecialchars($item['domusong']); ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- 기타 상품 정보 표시 -->
                                    <?php if (!empty($item['MY_type'])): ?>
                                        <p><strong>타입:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['MY_Fsd'])): ?>
                                        <p><strong>용지/스타일:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_Fsd'])); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['PN_type'])): ?>
                                        <p><strong>규격/섹션:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['MY_amount'])): ?>
                                        <p><strong>수량:</strong> <?php echo htmlspecialchars($item['MY_amount']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['POtype'])): ?>
                                        <p><strong>인쇄면:</strong> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['ordertype'])): ?>
                                        <p><strong>주문타입:</strong> <?php echo $item['ordertype'] == 'design' ? '디자인+인쇄' : '인쇄만'; ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div style="text-align: right;">
                                <p style="color: #e74c3c; font-weight: bold; font-size: 1.1rem;">
                                    총 가격: <?php echo number_format($item['st_price_vat']); ?>원 (VAT 포함)
                                </p>
                            </div>
                            
                            <?php if (!empty($item['MY_comment'])): ?>
                                <div style="margin-top: 1rem; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">
                                    <p style="margin: 0;"><strong>요청사항:</strong> 
                                        <?php echo htmlspecialchars($item['MY_comment']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- 주문 요약 -->
                    <div class="order-summary" style="background: #f8f9fa; padding: 2rem; border-radius: 10px; margin-top: 2rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <h3 style="margin: 0;">주문 합계</h3>
                            <div style="text-align: right;">
                                <p style="margin: 0;">상품금액: <?php echo number_format($total_price); ?>원</p>
                                <p style="margin: 0.5rem 0;">VAT: <?php echo number_format($total_vat - $total_price); ?>원</p>
                                <p style="color: #e74c3c; font-weight: bold; font-size: 1.2rem;">
                                    총 결제금액: <?php echo number_format($total_vat); ?>원
                                </p>
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
                        
                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button type="button" onclick="continueShopping()" class="btn-continue" style="flex: 1; padding: 1rem; background: #28a745; color: white; border: none; border-radius: 10px; font-size: 1.1rem; cursor: pointer;">
                                🛍️ 계속 쇼핑하기
                            </button>
                            <button type="button" onclick="generateQuotePDF()" class="btn-quote" style="flex: 1; padding: 1rem; background: #17a2b8; color: white; border: none; border-radius: 10px; font-size: 1.1rem; cursor: pointer;">
                                📄 견적서 PDF
                            </button>
                            <button type="submit" class="btn-order" style="flex: 1; padding: 1rem; background: #e74c3c; color: white; border: none; border-radius: 10px; font-size: 1.1rem; cursor: pointer;">
                                📋 주문하기
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: #495057;">장바구니가 비어있습니다</h3>
                    <p style="margin-bottom: 2rem;">상품을 담아보세요!</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 2rem;">
                        <a href="../inserted/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">📄 전단지</a>
                        
                        <a href="../cadarok/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">📖 카다록</a>
                        
                        <a href="../NameCard/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">📇 명함</a>
                        
                        <a href="view_modern.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">🏷️ 스티커</a>
                        
                        <a href="../msticker/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">🧲 자석스티커</a>
                        
                        <a href="../envelope/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">✉️ 봉투</a>
                        
                        <a href="../LittlePrint/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">🎨 포스터</a>
                        
                        <a href="../MerchandiseBond/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">🎫 상품권</a>
                        
                        <a href="../NcrFlambeau/index.php" style="display: inline-block; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(0,0,0,0.15);" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 18px rgba(0,0,0,0.2)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.15)'">📋 양식지</a>
                    </div>
                    <div style="text-align: center;">
                        <button onclick="continueShopping()" class="btn btn-success" style="padding: 15px 30px; font-size: 1.1rem;">
                            🛍️ 쇼핑 계속하기
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
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
        
        switch(lastProductType) {
            case 'leaflet':
                window.location.href = '../inserted/index.php';
                break;
            case 'cadarok':
                window.location.href = '../cadarok/index.php';
                break;
            case 'namecard':
                window.location.href = '../NameCard/index.php';
                break;
            case 'sticker':
                window.location.href = 'view_modern.php';
                break;
            case 'envelope':
                window.location.href = '../envelope/index.php';
                break;
            case 'merchandisebond':
                window.location.href = '../MerchandiseBond/index.php';
                break;
            case 'littleprint':
                window.location.href = '../LittlePrint/index.php';
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