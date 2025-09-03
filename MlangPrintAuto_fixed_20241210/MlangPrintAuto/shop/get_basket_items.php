<?php
/**
 * 통합 장바구니 아이템 조회
 * 경로: MlangPrintAuto/shop/get_basket_items.php
 */

ob_start();
session_start();
header('Content-Type: application/json; charset=UTF-8');

// 경로 수정: MlangPrintAuto/shop/에서 루트의 db.php 접근
include "../../db.php";
$connect = $db;

// 헬퍼 함수 포함
include "../shop_temp_helper.php";

// UTF-8 문자셋 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

try {
    $session_id = session_id();
    
    // 장바구니 아이템 조회
    $cart_result = getCartItems($connect, $session_id);
    $items = [];
    $total = 0;
    $total_vat = 0;
    
    while ($item = mysqli_fetch_assoc($cart_result)) {
        // 표시용 데이터로 변환
        $formatted_item = formatCartItemForDisplay($connect, $item);
        
        // API 응답용 형식으로 변환
        $api_item = [
            'no' => $item['no'],
            'product_type' => $item['product_type'],
            'name' => $formatted_item['name'],
            'details' => $formatted_item['details'],
            'st_price' => number_format($item['st_price']),
            'st_price_vat' => number_format($item['st_price_vat']),
            'st_price_raw' => $item['st_price'],
            'st_price_vat_raw' => $item['st_price_vat'],
            'uhyung' => $item['uhyung'],
            'MY_comment' => $item['MY_comment']
        ];
        
        // 상품별 추가 정보
        switch ($item['product_type']) {
            case 'sticker':
                $api_item['jong'] = $item['jong'];
                $api_item['jong_short'] = substr($item['jong'], 0, 20);
                $api_item['garo'] = $item['garo'];
                $api_item['sero'] = $item['sero'];
                $api_item['mesu'] = $item['mesu'];
                $api_item['domusong'] = $item['domusong'];
                $api_item['domusong_short'] = substr($item['domusong'], 0, 15);
                break;
                
            case 'cadarok':
            case 'leaflet':
            case 'namecard':
            case 'envelope':
                $api_item['MY_type'] = $item['MY_type'];
                $api_item['MY_Fsd'] = $item['MY_Fsd'];
                $api_item['PN_type'] = $item['PN_type'];
                $api_item['MY_amount'] = $item['MY_amount'];
                $api_item['POtype'] = $item['POtype'];
                $api_item['ordertype'] = $item['ordertype'];
                break;
        }
        
        $items[] = $api_item;
        $total += $item['st_price'];
        $total_vat += $item['st_price_vat'];
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'items' => $items,
        'total' => number_format($total),
        'total_vat' => number_format($total_vat),
        'total_raw' => $total,
        'total_vat_raw' => $total_vat,
        'count' => count($items)
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'session_id' => session_id(),
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile()
        ]
    ]);
}

if ($connect) {
    mysqli_close($connect);
}
ob_end_flush();
?>