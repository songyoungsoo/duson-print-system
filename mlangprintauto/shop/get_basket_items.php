<?php
/**
 * 통합 장바구니 아이템 조회
 * 경로: MlangPrintAuto/shop/get_basket_items.php
 */

ob_start();
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Fatal Error handler
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Server error', 'items' => [], 'count' => 0]);
    }
});

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
        
        // 상품별 추가 정보 — mb_substr for multi-byte safe truncation
        switch ($item['product_type']) {
            case 'sticker':
                $api_item['jong'] = $item['jong'];
                $api_item['jong_short'] = mb_substr($item['jong'] ?? '', 0, 20, 'UTF-8');
                $api_item['garo'] = $item['garo'];
                $api_item['sero'] = $item['sero'];
                $api_item['mesu'] = $item['mesu'];
                $api_item['domusong'] = $item['domusong'];
                $api_item['domusong_short'] = mb_substr($item['domusong'] ?? '', 0, 15, 'UTF-8');
                break;
                
            case 'cadarok':
            case 'leaflet':
            case 'inserted':
            case 'namecard':
            case 'envelope':
            case 'littleprint':
            case 'merchandisebond':
            case 'msticker':
            case 'ncrflambeau':
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
    $json = json_encode([
        'success' => true,
        'items' => $items,
        'total' => number_format($total),
        'total_vat' => number_format($total_vat),
        'total_raw' => $total,
        'total_vat_raw' => $total_vat,
        'count' => count($items)
    ], JSON_UNESCAPED_UNICODE);
    
    // Fallback: clean invalid UTF-8 and retry
    if ($json === false) {
        $clean_items = json_decode(json_encode($items, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE), true);
        $json = json_encode([
            'success' => true,
            'items' => $clean_items ?? [],
            'total' => number_format($total),
            'total_vat' => number_format($total_vat),
            'total_raw' => $total,
            'total_vat_raw' => $total_vat,
            'count' => count($items)
        ], JSON_UNESCAPED_UNICODE);
    }
    
    echo $json;
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if ($connect) {
    mysqli_close($connect);
}
ob_end_flush();
?>