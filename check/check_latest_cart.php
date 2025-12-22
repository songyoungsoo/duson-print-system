<?php
header('Content-Type: application/json; charset=utf-8');

include "db.php";

if (!$db) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($db, "utf8");

// 최근 장바구니 항목 조회 (추가 옵션 JSON 방식)
$query = "SELECT no, product_type, st_price, st_price_vat,
          additional_options, additional_options_total
          FROM shop_temp
          ORDER BY no DESC
          LIMIT 1";

$result = mysqli_query($db, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => '쿼리 오류: ' . mysqli_error($db)]);
    exit;
}

$cart = mysqli_fetch_assoc($result);
mysqli_close($db);

if ($cart) {
    // additional_options JSON 파싱
    if (!empty($cart['additional_options'])) {
        $options = json_decode($cart['additional_options'], true);
        if ($options) {
            $cart['coating_enabled'] = $options['coating_enabled'] ?? 0;
            $cart['coating_type'] = $options['coating_type'] ?? '';
            $cart['coating_price'] = $options['coating_price'] ?? 0;
            $cart['folding_enabled'] = $options['folding_enabled'] ?? 0;
            $cart['folding_type'] = $options['folding_type'] ?? '';
            $cart['folding_price'] = $options['folding_price'] ?? 0;
            $cart['creasing_enabled'] = $options['creasing_enabled'] ?? 0;
            $cart['creasing_lines'] = $options['creasing_lines'] ?? 0;
            $cart['creasing_price'] = $options['creasing_price'] ?? 0;
        }
    }

    echo json_encode([
        'success' => true,
        'cart' => $cart
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => '장바구니가 비어있습니다. 먼저 전단지 페이지에서 장바구니에 추가해주세요.'
    ]);
}
?>
