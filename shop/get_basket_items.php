<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=UTF-8');

include "../lib/func.php";
$connect = dbconn();

// UTF-8 문자셋 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

// 전단지 관련 헬퍼 함수들
function getLeafletPaperSize($connect, $pn_type, $dimension) {
    try {
        $query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no='$pn_type'";
        $result = mysqli_query($connect, $query);
        if ($result && $row = mysqli_fetch_array($result)) {
            $title = $row['title'];
            if (preg_match('/(\d+)x(\d+)/', $title, $matches)) {
                return $dimension === 'width' ? $matches[1] : $matches[2];
            }
        }
    } catch (Exception $e) {
        // 오류 시 기본값 반환
    }
    return $dimension === 'width' ? '210' : '297';
}

function getLeafletQuantity($connect, $my_amount) {
    try {
        $query = "SELECT quantityTwo FROM MlangPrintAuto_inserted WHERE quantity='$my_amount' LIMIT 1";
        $result = mysqli_query($connect, $query);
        if ($result && $row = mysqli_fetch_array($result)) {
            return $row['quantityTwo'];
        }
    } catch (Exception $e) {
        // 오류 시 기본값 반환
    }
    return intval($my_amount * 1000);
}

function getLeafletOptions($connect, $data) {
    try {
        $color_title = '';
        $paper_title = '';
        
        // 인쇄색상 정보
        $color_query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no='{$data['MY_type']}'";
        $color_result = mysqli_query($connect, $color_query);
        if ($color_result && $color_row = mysqli_fetch_array($color_result)) {
            $color_title = $color_row['title'];
        }
        
        // 종이종류 정보
        $paper_query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no='{$data['MY_Fsd']}'";
        $paper_result = mysqli_query($connect, $paper_query);
        if ($paper_result && $paper_row = mysqli_fetch_array($paper_result)) {
            $paper_title = $paper_row['title'];
        }
        
        $sides = $data['POtype'] == '1' ? '단면' : '양면';
        return $color_title . ' / ' . $paper_title . ' / ' . $sides;
    } catch (Exception $e) {
        return '전단지 옵션';
    }
}

function getLeafletOptionsShort($data) {
    $sides = $data['POtype'] == '1' ? '단면' : '양면';
    $order_type = $data['ordertype'] === 'print' ? '인쇄만' : '디자인+인쇄';
    return $sides . ' / ' . $order_type;
}

try {
    $session_id = session_id();
    
    // 기존 장바구니 아이템 조회 (스티커, 전단지)
    $query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC";
    $result = mysqli_query($connect, $query);
    
    $items = [];
    $total = 0;
    $total_vat = 0;
    
    while ($data = mysqli_fetch_array($result)) {
        // 제품 타입 확인 (전단지 vs 스티커)
        $product_type = $data['product_type'] ?? 'sticker';
        
        if ($product_type === 'leaflet') {
            // 전단지 데이터 처리
            $items[] = [
                'no' => $data['no'],
                'product_type' => 'leaflet',
                'jong' => '전단지',
                'jong_short' => '전단지',
                'garo' => getLeafletPaperSize($connect, $data['PN_type'], 'width'),
                'sero' => getLeafletPaperSize($connect, $data['PN_type'], 'height'),
                'mesu' => getLeafletQuantity($connect, $data['MY_amount']),
                'domusong' => getLeafletOptions($connect, $data),
                'domusong_short' => getLeafletOptionsShort($data),
                'uhyung' => ($data['ordertype'] === 'print') ? 0 : 1,
                'st_price' => number_format($data['st_price']),
                'st_price_vat' => number_format($data['st_price_vat']),
                'st_price_raw' => $data['st_price'],
                'st_price_vat_raw' => $data['st_price_vat']
            ];
        } else {
            // 스티커 데이터 처리 (기존 방식)
            $items[] = [
                'no' => $data['no'],
                'product_type' => 'sticker',
                'jong' => $data['jong'] ?? '스티커',
                'jong_short' => isset($data['jong']) ? substr($data['jong'], 4, 12) : '스티커',
                'garo' => $data['garo'] ?? '0',
                'sero' => $data['sero'] ?? '0',
                'mesu' => $data['mesu'] ?? '0',
                'domusong' => $data['domusong'] ?? '',
                'domusong_short' => isset($data['domusong']) ? (function($domusong) {
                    $parts = explode(' ', $domusong, 2);
                    return isset($parts[1]) ? $parts[1] : $domusong;
                })($data['domusong']) : '',
                'uhyung' => $data['uhyung'] ?? 0,
                'st_price' => number_format($data['st_price']),
                'st_price_vat' => number_format($data['st_price_vat']),
                'st_price_raw' => $data['st_price'],
                'st_price_vat_raw' => $data['st_price_vat']
            ];
        }
        
        $total += $data['st_price'];
        $total_vat += $data['st_price_vat'];
    }
    
    // 카다록 장바구니 아이템 조회
    $cadarok_table_check = mysqli_query($connect, "SHOW TABLES LIKE 'shop_temp_cadarok'");
    if (mysqli_num_rows($cadarok_table_check) > 0) {
        $cadarok_query = "SELECT * FROM shop_temp_cadarok WHERE session_id='$session_id' ORDER BY no DESC";
        $cadarok_result = mysqli_query($connect, $cadarok_query);
        
        while ($cadarok_data = mysqli_fetch_array($cadarok_result)) {
            $items[] = [
                'no' => 'cadarok_' . $cadarok_data['no'], // 구분을 위해 접두사 추가
                'product_type' => 'cadarok',
                'jong' => '카다록',
                'jong_short' => '카다록',
                'garo' => $cadarok_data['size_name'] ?? '사용자 지정',
                'sero' => $cadarok_data['paper_type'] ?? '',
                'mesu' => $cadarok_data['amount'] ?? '',
                'domusong' => $cadarok_data['type_name'] . ' / ' . $cadarok_data['order_type'],
                'domusong_short' => $cadarok_data['order_type'] ?? '인쇄만',
                'uhyung' => (strpos($cadarok_data['order_type'] ?? '', '디자인') !== false) ? 1 : 0,
                'st_price' => number_format($cadarok_data['st_price']),
                'st_price_vat' => number_format($cadarok_data['st_price_vat']),
                'st_price_raw' => $cadarok_data['st_price'],
                'st_price_vat_raw' => $cadarok_data['st_price_vat']
            ];
            
            $total += $cadarok_data['st_price'];
            $total_vat += $cadarok_data['st_price_vat'];
        }
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'items' => $items,
        'total' => number_format($total),
        'total_vat' => number_format($total_vat),
        'total_raw' => $total,
        'total_vat_raw' => $total_vat
    ]);
    
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