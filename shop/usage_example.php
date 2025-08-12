<?php
/**
 * 통합 shop_temp 테이블 사용 예시
 */

session_start();
include "../lib/func.php";
include "shop_temp_helper.php";

$connect = dbconn();
$session_id = session_id();

// 1. 스티커 장바구니 추가 예시
$sticker_data = [
    'jong' => 'jsp 투명스티커',
    'garo' => '75',
    'sero' => '30', 
    'mesu' => '8283',
    'domusong' => '사각',
    'uhyung' => 0,
    'st_price' => 174355,
    'st_price_vat' => 191791
];

if (addStickerToCart($connect, $session_id, $sticker_data)) {
    echo "스티커가 장바구니에 추가되었습니다.<br>";
}

// 2. 카다록 장바구니 추가 예시
$cadarok_data = [
    'MY_type' => '691',      // 카다록,리플렛
    'MY_Fsd' => '697',       // 12페이지 중철(A4)
    'PN_type' => '699',      // 인쇄만
    'MY_amount' => '1000',
    'ordertype' => 'print',
    'st_price' => 268000,
    'st_price_vat' => 294800,
    'MY_comment' => '급하게 부탁드립니다'
];

if (addCadarokToCart($connect, $session_id, $cadarok_data)) {
    echo "카다록이 장바구니에 추가되었습니다.<br>";
}

// 3. 전단지 장바구니 추가 예시
$leaflet_data = [
    'MY_type' => '802',      // 칼라인쇄(CMYK)
    'MY_Fsd' => '604',       // 120아트/스노우
    'PN_type' => '823',      // B4(8절) 257x367
    'MY_amount' => '7',      // 수량 코드
    'POtype' => '1',         // 단면
    'ordertype' => 'design', // 디자인+인쇄
    'st_price' => 640000,
    'st_price_vat' => 704000,
    'MY_comment' => '컬러 선명하게 부탁드립니다'
];

if (addLeafletToCart($connect, $session_id, $leaflet_data)) {
    echo "전단지가 장바구니에 추가되었습니다.<br>";
}

// 4. 명함 장바구니 추가 예시
$namecard_data = [
    'MY_type' => '275',      // 일반명함(쿠폰)
    'MY_Fsd' => '993',       // 몽블랑240g
    'MY_amount' => '500',
    'POtype' => '1',         // 단면
    'ordertype' => 'design', // 디자인+인쇄
    'st_price' => 9000,
    'st_price_vat' => 14000,
    'MY_comment' => '로고 포함해서 디자인 부탁드립니다'
];

if (addNamecardToCart($connect, $session_id, $namecard_data)) {
    echo "명함이 장바구니에 추가되었습니다.<br>";
}

echo "<hr>";

// 5. 장바구니 내용 조회 및 표시
$cart_result = getCartItems($connect, $session_id);
echo "<h3>장바구니 내용:</h3>";

while ($item = mysqli_fetch_assoc($cart_result)) {
    $formatted = formatCartItemForDisplay($connect, $item);
    
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<h4>{$formatted['name']} (번호: {$formatted['no']})</h4>";
    
    foreach ($formatted['details'] as $key => $value) {
        echo "<p><strong>{$key}:</strong> {$value}</p>";
    }
    
    echo "<p><strong>가격:</strong> " . number_format($formatted['st_price']) . "원</p>";
    echo "<p><strong>VAT포함:</strong> " . number_format($formatted['st_price_vat']) . "원</p>";
    
    if ($formatted['MY_comment']) {
        echo "<p><strong>요청사항:</strong> {$formatted['MY_comment']}</p>";
    }
    
    echo "<p><strong>디자인:</strong> " . ($formatted['uhyung'] ? '디자인+인쇄' : '인쇄만') . "</p>";
    echo "</div>";
}

// 6. 장바구니 총액 계산
$total = calculateCartTotal($connect, $session_id);
echo "<hr>";
echo "<h3>장바구니 총계:</h3>";
echo "<p>상품 개수: {$total['count']}개</p>";
echo "<p>총 금액: " . number_format($total['total']) . "원</p>";
echo "<p>VAT 포함: " . number_format($total['total_vat']) . "원</p>";

mysqli_close($connect);
?>