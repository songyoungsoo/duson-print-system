<?php
/**
 * 통합 장바구니 페이지
 * 경로: MlangPrintAuto/cart.php
 * 모든 상품 유형을 지원하는 장바구니
 *
 * ✅ 2026-01-16: SpecDisplayService SSOT 적용
 */

session_start();
$session_id = session_id();

include "../db.php";  // ✅ 표준 DB 연결 사용
include "../lib/func.php";
include "shop_temp_helper.php";
include "../includes/SpecDisplayService.php";  // ✅ SSOT 연동

$connect = $db;  // ✅ db.php의 $db 변수 사용
$specDisplayService = new SpecDisplayService($connect);  // ✅ SSOT 서비스

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

// 장바구니 아이템 삭제
if (isset($_GET['delete'])) {
    $item_no = $_GET['delete'];
    if (removeCartItem($connect, $session_id, $item_no)) {
        header('Location: cart.php');
        exit;
    }
}

// 그룹 장바구니 아이템 일괄 삭제 (건수 곱하기 그룹)
if (isset($_GET['delete_group'])) {
    $group_id = $_GET['delete_group'];
    if (removeCartGroup($connect, $session_id, $group_id)) {
        header('Location: cart.php');
        exit;
    }
}

// 장바구니 비우기
if (isset($_GET['clear'])) {
    if (clearCart($connect, $session_id)) {
        header('Location: cart.php');
        exit;
    }
}

// 장바구니 아이템 조회
$cart_result = getCartItems($connect, $session_id);
$cart_items = [];
while ($item = mysqli_fetch_assoc($cart_result)) {
    // 기존 포맷 (hidden fields용)
    $formatted = formatCartItemForDisplay($connect, $item);

    // ✅ 2026-01-16: SpecDisplayService SSOT로 규격/수량 표준화
    $displayData = $specDisplayService->getDisplayData($item);
    $formatted['spec_line1'] = $displayData['line1'];
    $formatted['spec_line2'] = $displayData['line2'];
    $formatted['quantity_display'] = $displayData['quantity_display'];
    $formatted['unit'] = $displayData['unit'];
    $formatted['additional'] = $displayData['additional'];

    $cart_items[] = $formatted;
}

// 총액 계산
$total_info = calculateCartTotal($connect, $session_id);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 통합 장바구니</title>
    <link rel="stylesheet" href="../css/style250801.css">
    <style>
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        .cart-header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 2rem; text-align: center; border-radius: 15px; margin-bottom: 2rem; }
        .cart-item { border: 1px solid #e9ecef; padding: 1.5rem; margin-bottom: 1rem; border-radius: 10px; background: white; }
        .product-name { font-size: 1.2rem; font-weight: bold; color: #2c3e50; margin-bottom: 1rem; }
        .product-details { margin-bottom: 1rem; }
        .product-details p { margin: 0.5rem 0; }
        .price-info { text-align: right; font-size: 1.1rem; font-weight: bold; color: #e74c3c; }
        .btn { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .total-summary { background: #f8f9fa; padding: 2rem; border-radius: 10px; margin-top: 2rem; }
        .empty-cart { text-align: center; padding: 4rem 2rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <!-- 헤더 -->
        <div class="cart-header">
            <h1>🛒 통합 장바구니</h1>
            <p>모든 인쇄 상품을 한 번에 주문하세요</p>
        </div>

        <!-- 네비게이션 -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
            <div>
                <a href="cadarok/index.php" class="btn btn-primary">📖 카다록</a>
                <a href="NameCard/index.php" class="btn btn-primary">📇 명함</a>
                <a href="../shop/view_modern.php" class="btn btn-primary">🏷️ 스티커</a>
            </div>
            <div>
                <a href="?clear=1" onclick="return confirm('장바구니를 비우시겠습니까?')" class="btn btn-danger">🗑️ 전체 삭제</a>
            </div>
        </div>

        <!-- 장바구니 내용 -->
        <?php if (!empty($cart_items)): ?>
            <form method="post" action="../mlangorder_printauto/OnlineOrder.php">
                <input type="hidden" name="SubmitMode" value="OrderOne">
                
                <?php
                // 그룹 정보 전처리: item_group_id별로 아이템 그룹화
                $groups = [];
                $ungrouped = [];
                foreach ($cart_items as $item) {
                    $gid = $item['item_group_id'] ?? null;
                    if (!empty($gid)) {
                        $groups[$gid][] = $item;
                    } else {
                        $ungrouped[] = $item;
                    }
                }

                // 그룹 아이템 표시
                foreach ($groups as $gid => $group_items):
                    $group_count = count($group_items);
                    $first = $group_items[0];
                    $group_total_vat = array_sum(array_column($group_items, 'st_price_vat'));
                ?>
                    <div style="border: 2px solid #1E4E79; border-radius: 12px; margin-bottom: 1.5rem; overflow: hidden;">
                        <!-- 그룹 헤더 -->
                        <div style="background: #1E4E79; color: white; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>📋 <?php echo htmlspecialchars($first['name']); ?> × <?php echo $group_count; ?>건</strong>
                                <span style="margin-left: 12px; font-size: 0.9rem; opacity: 0.9;">
                                    총 ₩<?php echo number_format($group_total_vat); ?>
                                </span>
                            </div>
                            <a href="?delete_group=<?php echo urlencode($gid); ?>"
                               onclick="return confirm('이 그룹(<?php echo $group_count; ?>건)을 모두 삭제하시겠습니까?')"
                               style="color: white; text-decoration: none; font-size: 0.85rem; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 4px;">
                                🗑️ 그룹 전체 삭제
                            </a>
                        </div>
                        <!-- 그룹 내 개별 아이템 -->
                        <?php foreach ($group_items as $idx => $item): ?>
                        <div class="cart-item" style="margin: 0; border-radius: 0; border-bottom: 1px solid #e9ecef;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <div class="product-name" style="font-size: 1rem;">
                                        <span style="background: #1E4E79; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; margin-right: 8px;">
                                            건 <?php echo ($item['item_group_seq'] ?? ($idx + 1)); ?>/<?php echo $group_count; ?>
                                        </span>
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        <?php if (!empty($item['uploaded_files']) && $item['uploaded_files'] !== '[]'): ?>
                                            <span style="color: #28a745;">✅ 파일있음</span>
                                        <?php else: ?>
                                            <span style="color: #dc3545; font-size: 0.8rem;">❌ 파일 미등록</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-details">
                                        <?php if (!empty($item['spec_line1'])): ?>
                                            <p><strong>규격:</strong> <?php echo htmlspecialchars($item['spec_line1']); ?></p>
                                        <?php endif; ?>
                                        <p><strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_display']); ?></p>
                                    </div>
                                    <?php if (!empty($item['work_memo'])): ?>
                                        <div style="background: #f8f9fa; padding: 6px 10px; border-radius: 4px; margin-top: 0.5rem; font-size: 0.85rem;">
                                            <strong>메모:</strong> <?php echo htmlspecialchars($item['work_memo']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <div class="price-info">
                                        <?php echo number_format($item['st_price_vat']); ?>원
                                    </div>
                                    <a href="?delete=<?php echo $item['no']; ?>"
                                       onclick="return confirm('이 건만 삭제하시겠습니까?')"
                                       class="btn btn-danger" style="margin-top: 6px; font-size: 0.8rem; padding: 4px 10px;">
                                        ❌
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- hidden 필드 -->
                        <input type="hidden" name="product_type[]" value="<?php echo htmlspecialchars($item['product_type']); ?>">
                        <input type="hidden" name="price[]" value="<?php echo htmlspecialchars($item['st_price']); ?>">
                        <input type="hidden" name="price_vat[]" value="<?php echo htmlspecialchars($item['st_price_vat']); ?>">
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <!-- 비그룹 아이템 (기존 단건 주문) -->
                <?php foreach ($ungrouped as $item): ?>
                    <div class="cart-item">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <div class="product-name">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>
                                <div class="product-details">
                                    <?php if (!empty($item['spec_line1'])): ?>
                                        <p><strong>규격:</strong> <?php echo htmlspecialchars($item['spec_line1']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['spec_line2'])): ?>
                                        <p><strong>옵션:</strong> <?php echo htmlspecialchars($item['spec_line2']); ?></p>
                                    <?php endif; ?>
                                    <p><strong>수량:</strong> <?php echo htmlspecialchars($item['quantity_display']); ?></p>
                                    <?php if (!empty($item['additional'])): ?>
                                        <p><strong>후가공:</strong> <?php echo htmlspecialchars($item['additional']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['MY_comment']): ?>
                                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 1rem;">
                                        <strong>요청사항:</strong> <?php echo htmlspecialchars($item['MY_comment']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($item['additional_options_summary'])): ?>
                                    <div style="background: #e7f3ff; padding: 12px; border-radius: 5px; margin-top: 1rem; border-left: 4px solid #0066cc;">
                                        <strong style="color: #0066cc;">✨ 추가옵션:</strong>
                                        <span style="color: #333;"><?php echo htmlspecialchars($item['additional_options_summary']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right;">
                                <div class="price-info">
                                    <?php echo number_format($item['st_price_vat']); ?>원
                                    <div style="font-size: 0.9rem; color: #6c757d;">VAT 포함</div>
                                </div>
                                <a href="?delete=<?php echo $item['no']; ?>"
                                   onclick="return confirm('이 상품을 삭제하시겠습니까?')"
                                   class="btn btn-danger" style="margin-top: 10px;">
                                    ❌ 삭제
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- hidden 필드 -->
                    <input type="hidden" name="product_type[]" value="<?php echo htmlspecialchars($item['product_type']); ?>">
                    <input type="hidden" name="price[]" value="<?php echo htmlspecialchars($item['st_price']); ?>">
                    <input type="hidden" name="price_vat[]" value="<?php echo htmlspecialchars($item['st_price_vat']); ?>">
                <?php endforeach; ?>

                <!-- 주문 요약 -->
                <div class="total-summary">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <h3>주문 요약</h3>
                        <div style="text-align: right;">
                            <p>상품 개수: <?php echo $total_info['count']; ?>개</p>
                            <p>상품 금액: <?php echo number_format($total_info['total']); ?>원</p>
                            <p style="font-size: 1.2rem; font-weight: bold; color: #e74c3c;">
                                총 결제금액: <?php echo number_format($total_info['total_vat']); ?>원 (VAT 포함)
                            </p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="total_price" value="<?php echo $total_info['total']; ?>">
                    <input type="hidden" name="total_price_vat" value="<?php echo $total_info['total_vat']; ?>">
                    <input type="hidden" name="items_count" value="<?php echo $total_info['count']; ?>">
                    
                    <button type="submit" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.2rem;">
                        🚀 주문하기
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="empty-cart">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                <h3>장바구니가 비어있습니다</h3>
                <p>상품을 담아보세요!</p>
                <div style="margin-top: 2rem;">
                    <a href="cadarok/index.php" class="btn btn-primary">📖 카다록 보기</a>
                    <a href="NameCard/index.php" class="btn btn-primary">📇 명함 주문</a>
                    <a href="../shop/view_modern.php" class="btn btn-primary">🏷️ 스티커 보기</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
if ($connect) {
    mysqli_close($connect);
}
?>