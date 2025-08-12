<?php
session_start();
$session_id = session_id();

$HomeDir = "../../";
include "../lib/func.php";

error_log("Attempting to connect to database");
$connect = dbconn();

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
    
    // 1. shop_temp 테이블에서 스티커/전단지 데이터 가져오기
    $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp'");
    if ($table_check && mysqli_num_rows($table_check) > 0) {
        $query = "SELECT *, 
                  COALESCE(product_type, 'sticker') as product_type,
                  MY_type as category_no,
                  MY_Fsd as style,
                  PN_type as section,
                  ordertype as tree_select,
                  st_price as price,
                  st_price_vat as price_vat
                  FROM shop_temp 
                  WHERE session_id = ?";
                  
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
                         '' as MY_comment
                         FROM shop_temp_cadarok 
                         WHERE session_id = ?";
                         
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
        }
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
    
    // 카다록 아이템인지 확인
    if (strpos($item_no, 'cadarok_') === 0) {
        // 카다록 아이템 삭제
        $real_no = str_replace('cadarok_', '', $item_no);
        if (is_numeric($real_no)) {
            $delete_query = "DELETE FROM shop_temp_cadarok WHERE no = ? AND session_id = ?";
            $stmt = mysqli_prepare($connect, $delete_query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'is', $real_no, $session_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    } else if (is_numeric($item_no)) {
        // 일반 아이템 삭제
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
    // shop_temp 테이블 비우기
    $clear_query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $clear_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // shop_temp_cadarok 테이블 비우기
    $cadarok_table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp_cadarok'");
    if ($cadarok_table_check && mysqli_num_rows($cadarok_table_check) > 0) {
        $clear_cadarok_query = "DELETE FROM shop_temp_cadarok WHERE session_id = ?";
        $stmt = mysqli_prepare($connect, $clear_cadarok_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $session_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
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
    <title>🛒 장바구니</title>
    <link rel="stylesheet" href="../css/modern-style.css">
</head>
<body>
    <div class="container">
        <!-- 헤더 섹션 -->
        <div class="hero-section" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 2rem 0; text-align: center; margin-bottom: 2rem; border-radius: 15px;">
            <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">🛒 장바구니</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">주문하실 상품을 확인하고 수정하세요</p>
        </div>
        
        <!-- 네비게이션 바 - 상단에 작게 -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="display: flex; gap: 1rem;">
                <a href="view_modern.php" class="btn btn-info" style="padding: 8px 16px; font-size: 14px;">🛍️ 계속 쇼핑</a>
                <a href="../MlangPrintAuto/NameCard/index_modern.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 14px;">📇 명함 주문</a>
            </div>
            <div>
                <button onclick="clearCart()" class="btn btn-danger" style="padding: 8px 16px; font-size: 14px;">🗑️ 전체 삭제</button>
            </div>
        </div>

        <!-- 장바구니 메인 콘텐츠 -->
        <div id="cartContent" style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <?php if (!empty($cart_items)): ?>
                <form method="post" action="../MlangOrder_PrintAuto/OnlineOrder.php" id="orderForm">
                    <input type="hidden" name="SubmitMode" value="OrderOne">
                    <?php 
                    $total_price = 0;
                    $total_vat = 0;
                    $items_data = array();
                    
                    foreach ($cart_items as $item):
                        if (!isset($item['st_price'])) {
                            $item['st_price'] = isset($item['MY_price']) ? $item['MY_price'] : 0;
                        }
                        if (!isset($item['st_price_vat'])) {
                            $item['st_price_vat'] = isset($item['MY_price_vat']) ? $item['MY_price_vat'] : round($item['st_price'] * 1.1);
                        }
                        $total_price += $item['st_price'];
                        $total_vat += $item['st_price_vat'];
                        
                        // 각 아이템의 데이터를 hidden 필드로 저장
                        $items_data[] = $item;
                    ?>
                        <div class="cart-item" style="border: 1px solid #e9ecef; padding: 1.5rem; margin-bottom: 1rem; border-radius: 10px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <h3 style="color: #2c3e50; margin: 0;">
                                    <?php 
                                    $product_name = '전단지';
                                    if (isset($item['product_type'])) {
                                        switch($item['product_type']) {
                                            case 'cadarok':
                                                $product_name = '카달로그';
                                                break;
                                            case 'sticker':
                                                $product_name = '스티커';
                                                break;
                                        }
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
                                <?php if (!empty($item['MY_Fsd'])): ?>
                                    <p><strong>용지:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_Fsd'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['MY_type'])): ?>
                                    <p><strong>인쇄색상:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['MY_type'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['PN_type'])): ?>
                                    <p><strong>규격:</strong> <?php echo htmlspecialchars(getKoreanName($connect, $item['PN_type'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['MY_amount'])): ?>
                                    <p><strong>수량:</strong> <?php echo htmlspecialchars($item['MY_amount']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (isset($item['POtype'])): ?>
                                    <p><strong>인쇄면:</strong> <?php echo $item['POtype'] == '1' ? '단면' : '양면'; ?></p>
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
                        <?php foreach ($items_data as $index => $item): ?>
                            <input type="hidden" name="product_type[]" value="<?php echo htmlspecialchars($item['product_type']); ?>">
                            <input type="hidden" name="style[]" value="<?php echo htmlspecialchars($item['MY_Fsd']); ?>">
                            <input type="hidden" name="category_no[]" value="<?php echo htmlspecialchars($item['MY_type']); ?>">
                            <input type="hidden" name="section[]" value="<?php echo htmlspecialchars($item['PN_type']); ?>">
                            <input type="hidden" name="tree_select[]" value="<?php echo htmlspecialchars($item['ordertype']); ?>">
                            <input type="hidden" name="quantity[]" value="<?php echo htmlspecialchars($item['MY_amount']); ?>">
                            <input type="hidden" name="print_side[]" value="<?php echo htmlspecialchars($item['POtype']); ?>">
                            <input type="hidden" name="price[]" value="<?php echo htmlspecialchars($item['st_price']); ?>">
                            <input type="hidden" name="price_vat[]" value="<?php echo htmlspecialchars($item['st_price_vat']); ?>">
                            <?php $vat_difference = $item['st_price_vat'] - $item['st_price']; ?>
                            <input type="hidden" name="vat_amount[]" value="<?php echo htmlspecialchars($vat_difference); ?>">
                            <?php if (!empty($item['MY_comment'])): ?>
                                <input type="hidden" name="items[<?php echo $index; ?>][MY_comment]" value="<?php echo htmlspecialchars($item['MY_comment']); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                        <input type="hidden" name="total_price_vat" value="<?php echo $total_vat; ?>">
                        <input type="hidden" name="items_count" value="<?php echo count($items_data); ?>">
                        <button type="submit" class="btn-order" style="width: 100%; padding: 1rem; margin-top: 1rem; background: #e74c3c; color: white; border: none; border-radius: 10px; font-size: 1.2rem; cursor: pointer;">
                            주문하기 🚀
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: #495057;">장바구니가 비어있습니다</h3>
                    <p style="margin-bottom: 2rem;">상품을 담아보세요!</p>
                    <a href="view_modern.php" class="btn btn-primary" style="padding: 12px 24px;">🛍️ 쇼핑 시작하기</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- 주문 안내 -->
        <div class="notice" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 1.5rem; border-radius: 15px; text-align: center;">
            <h3 style="margin-bottom: 1rem;">📋 주문 안내</h3>
            <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 1rem;">
                <div>💰 모든 작업은 입금 후 진행</div>
                <div>📦 택배비는 착불</div>
                <div>📁 주문 후 파일 업로드</div>
            </div>
        </div>
    </div>

    <script>
    // 장바구니 비우기
    function clearCart() {
        if (confirm('장바구니를 비우시겠습니까?')) {
            window.location.href = '?clear=1';
        }
    }
    
        if (items.length === 0) {
            document.getElementById('cartContent').innerHTML = 
                '<div style="text-align: center; padding: 4rem 2rem; color: #6c757d;">' +
                '<div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>' +
                '<h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: #495057;">장바구니가 비어있습니다</h3>' +
                '<p style="margin-bottom: 2rem;">상품을 담아보세요!</p>' +
                '<a href="view_modern.php" class="btn btn-primary" style="padding: 12px 24px;">🛍️ 쇼핑 시작하기</a>' +
                '</div>';
            return;
        }

        let html = '<div style="margin-bottom: 2rem;">';
        html += '<h2 style="color: #2c3e50; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">';
        html += '<span>📋</span> 주문 상품 목록 (' + items.length + '개)';
        html += '</h2>';
        html += '</div>';

        // 반응형 테이블
        html += '<div style="overflow-x: auto; margin-bottom: 2rem;">';
        html += '<table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">';
        html += '<thead style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white;">';
        html += '<tr>';
        html += '<th style="padding: 18px 12px; text-align: center; font-weight: 700; font-size: 16px;">NO</th>';
        html += '<th style="padding: 18px 12px; text-align: left; font-weight: 700; font-size: 16px;">상품정보</th>';
        html += '<th style="padding: 18px 12px; text-align: center; font-weight: 700; font-size: 16px;">사이즈</th>';
        html += '<th style="padding: 18px 12px; text-align: center; font-weight: 700; font-size: 16px;">수량</th>';
        html += '<th style="padding: 18px 12px; text-align: center; font-weight: 700; font-size: 16px;">옵션</th>';
        html += '<th style="padding: 18px 12px; text-align: right; font-weight: 700; font-size: 16px;">금액</th>';
        html += '<th style="padding: 18px 12px; text-align: center; font-weight: 700; font-size: 16px;">관리</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        items.forEach((item, index) => {
            const bgColor = index % 2 === 0 ? '#f8f9fa' : 'white';
            html += '<tr style="background: ' + bgColor + '; border-bottom: 1px solid #dee2e6;">';
            html += '<td style="padding: 18px 12px; text-align: center; font-weight: 700; color: #495057; font-size: 18px;">' + item.no + '</td>';
            
            // 상품정보 (재질)
            html += '<td style="padding: 18px 12px;">';
            html += '<div style="font-weight: 700; color: #2c3e50; margin-bottom: 5px; font-size: 18px;">' + item.jong_short + '</div>';
            html += '<div style="font-size: 14px; color: #6c757d; font-weight: 500;">' + (item.uhyung > 0 ? '🎨 디자인+인쇄' : '🖨️ 인쇄만') + '</div>';
            html += '</td>';
            
            // 사이즈
            html += '<td style="padding: 18px 12px; text-align: center;">';
            html += '<div style="font-weight: 700; color: #495057; font-size: 18px;">' + item.garo + ' × ' + item.sero + '</div>';
            html += '<div style="font-size: 14px; color: #6c757d; font-weight: 500;">mm</div>';
            html += '</td>';
            
            // 수량
            html += '<td style="padding: 18px 12px; text-align: center;">';
            html += '<div style="font-weight: 700; color: #495057; font-size: 20px;">' + item.mesu + '</div>';
            html += '<div style="font-size: 14px; color: #6c757d; font-weight: 500;">매</div>';
            html += '</td>';
            
            // 옵션 (도무송)
            html += '<td style="padding: 18px 12px; text-align: center;">';
            html += '<div style="font-size: 14px; color: #6c757d; background: #e9ecef; padding: 6px 12px; border-radius: 15px; display: inline-block; font-weight: 500;">' + item.domusong_short + '</div>';
            html += '</td>';
            
            // 금액
            html += '<td style="padding: 18px 12px; text-align: right;">';
            html += '<div style="font-weight: 700; color: #e74c3c; font-size: 20px;">' + item.st_price_vat + '원</div>';
            html += '<div style="font-size: 14px; color: #6c757d; font-weight: 500;">VAT 포함</div>';
            html += '</td>';
            
            // 삭제 버튼
            html += '<td style="padding: 18px 12px; text-align: center;">';
            html += '<button onclick="removeItem(' + item.no + ')" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; padding: 10px 16px; border-radius: 25px; cursor: pointer; font-size: 14px; font-weight: 700; transition: all 0.3s ease;" onmouseover="this.style.transform=\'translateY(-2px)\'" onmouseout="this.style.transform=\'translateY(0)\'">🗑️ 삭제</button>';
            html += '</td>';
            
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';

        // 주문 요약 카드
        html += '<div style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; text-align: center;">';
        html += '<h3 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-weight: 700;">💰 주문 요약</h3>';
        html += '<div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">';
        html += '<div style="text-align: center;">';
        html += '<div style="font-size: 2rem; font-weight: 700;">' + items.length + '</div>';
        html += '<div style="opacity: 0.9;">상품 개수</div>';
        html += '</div>';
        html += '<div style="text-align: center;">';
        html += '<div style="font-size: 2rem; font-weight: 700;">' + total.toLocaleString() + '원</div>';
        html += '<div style="opacity: 0.9;">세전 금액</div>';
        html += '</div>';
        html += '<div style="text-align: center;">';
        html += '<div style="font-size: 2.5rem; font-weight: 700;">' + totalVat.toLocaleString() + '원</div>';
        html += '<div style="opacity: 0.9;">최종 결제금액</div>';
        html += '</div>';
        html += '</div>';
        
        // 주문하기 버튼 - 크고 눈에 띄게
        html += '<button onclick="proceedToOrder()" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none; padding: 20px 50px; border-radius: 50px; font-size: 18px; font-weight: 700; cursor: pointer; box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform=\'translateY(-3px)\'; this.style.boxShadow=\'0 12px 35px rgba(52, 152, 219, 0.4)\'" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 8px 25px rgba(52, 152, 219, 0.3)\'">';
        html += '🚀 주문하기';
        html += '</button>';
        html += '</div>';

        document.getElementById('cartContent').innerHTML = html;
    }

    // 아이템 삭제
    function removeItem(itemNo) {
        console.log('삭제할 아이템 번호:', itemNo);
        if (confirm('이 상품을 장바구니에서 삭제하시겠습니까?')) {
            fetch('remove_from_basket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'no=' + encodeURIComponent(itemNo)
            })
            .then(response => {
                console.log('개별 삭제 응답 상태:', response.status);
                console.log('개별 삭제 응답 헤더:', response.headers.get('content-type'));
                return response.text();
            })
            .then(text => {
                console.log('개별 삭제 응답 내용:', text);
                
                if (!text || text.trim() === '') {
                    alert('서버에서 빈 응답을 받았습니다.');
                    return;
                }
                
                try {
                    const data = JSON.parse(text);
                    console.log('파싱된 JSON:', data);
                    
                    if (data.success) {
                        alert(data.message || '상품이 삭제되었습니다.');
                        loadCartItems(); // 목록 새로고침
                    } else {
                        alert('오류: ' + (data.message || '알 수 없는 오류가 발생했습니다.'));
                    }
                } catch (e) {
                    console.error('JSON 파싱 오류:', e);
                    console.error('파싱 시도한 텍스트:', text);
                    
                    if (text.includes('<html>') || text.includes('<!DOCTYPE')) {
                        alert('서버에서 HTML 오류 페이지를 반환했습니다. 콘솔을 확인해주세요.');
                    } else {
                        alert('서버 응답을 처리할 수 없습니다: ' + e.message);
                    }
                }
            })
            .catch(error => {
                console.error('네트워크 오류:', error);
                alert('네트워크 오류가 발생했습니다: ' + error.message);
            });
        }
    }

    // 장바구니 비우기
    function clearCart() {
        if (confirm('장바구니를 모두 비우시겠습니까?')) {
            console.log('전체 삭제 요청 시작');
            
            fetch('clear_basket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => {
                console.log('전체 삭제 응답 상태:', response.status);
                console.log('전체 삭제 응답 헤더:', response.headers.get('content-type'));
                return response.text();
            })
            .then(text => {
                console.log('전체 삭제 응답 내용 (원본):', text);
                console.log('응답 길이:', text.length);
                
                // 응답이 비어있는지 확인
                if (!text || text.trim() === '') {
                    alert('서버에서 빈 응답을 받았습니다.');
                    return;
                }
                
                try {
                    const data = JSON.parse(text);
                    console.log('파싱된 JSON:', data);
                    
                    if (data.success) {
                        alert(data.message || '장바구니가 비워졌습니다.');
                        loadCartItems();
                    } else {
                        alert('오류: ' + (data.message || '알 수 없는 오류가 발생했습니다.'));
                    }
                } catch (e) {
                    console.error('JSON 파싱 오류:', e);
                    console.error('파싱 시도한 텍스트:', text);
                    
                    // HTML 오류 페이지인지 확인
                    if (text.includes('<html>') || text.includes('<!DOCTYPE')) {
                        alert('서버에서 HTML 오류 페이지를 반환했습니다. 콘솔을 확인해주세요.');
                    } else {
                        alert('서버 응답을 처리할 수 없습니다: ' + e.message);
                    }
                }
            })
            .catch(error => {
                console.error('네트워크 오류:', error);
                alert('네트워크 오류가 발생했습니다: ' + error.message);
            });
        }
    }

    // 주문하기
    function proceedToOrder() {
        if (confirm('주문을 진행하시겠습니까?')) {
            document.getElementById('orderForm').submit();
        }
    }
    </script>
</body>
</html>

<?php
if ($connect) {
    mysqli_close($connect);
}
?>