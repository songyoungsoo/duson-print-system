<?php
/**
 * 카다록 가격 계산 API (통합 장바구니 호환 버전)
 * 경로: mlangprintauto/cadarok/price_cal_new.php
 */

header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 입력값 받기
$MY_type    = $_POST['MY_type']    ?? '';
$MY_Fsd     = $_POST['MY_Fsd']     ?? '';
$PN_type    = $_POST['PN_type']    ?? '';
$MY_amount  = $_POST['MY_amount']  ?? '';
$ordertype  = $_POST['ordertype']  ?? 'print';

try {
    if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount)) {
        throw new Exception('모든 옵션을 선택해주세요.');
    }

    // 기본 가격 조회
    $print_price = 0;
    $design_price = 0;
    
    // mlangprintauto_cadarok 테이블에서 가격 조회
    $table_check = mysqli_query($connect, "SHOW TABLES LIKE "mlangprintauto_cadarok"");
    
    if (mysqli_num_rows($table_check) > 0) {
        $stmt = mysqli_prepare($connect, "SELECT money, DesignMoney FROM mlangprintauto_cadarok WHERE style = ? AND Section = ? AND TreeSelect = ? AND quantity = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "sssi", $MY_type, $MY_Fsd, $PN_type, $MY_amount);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $print_price = (int)$row['money'];
            $design_price = (int)($row['DesignMoney'] ?? 0);
        } else {
            // 데이터가 없는 경우 수량에 따른 기본 가격 계산
            $amount = (int)$MY_amount;
            if ($amount <= 100) {
                $print_price = 50000;
            } elseif ($amount <= 500) {
                $print_price = 80000;
            } elseif ($amount <= 1000) {
                $print_price = 120000;
            } else {
                $print_price = 150000;
            }
            $design_price = 30000; // 기본 디자인비
        }
        mysqli_stmt_close($stmt);
    } else {
        // 테이블이 없는 경우 기본 가격
        $amount = (int)$MY_amount;
        if ($amount <= 100) {
            $print_price = 50000;
        } elseif ($amount <= 500) {
            $print_price = 80000;
        } elseif ($amount <= 1000) {
            $print_price = 120000;
        } else {
            $print_price = 150000;
        }
        $design_price = 30000;
    }

    // 디자인 포함 여부에 따른 최종 가격 계산
    $total_price = $print_price;
    if ($ordertype === 'design') {
        $total_price += $design_price;
    }

    // VAT 계산
    $vat_amount = (int)round($total_price * 0.1);
    $total_price_vat = $total_price + $vat_amount;

    // 통합 장바구니 호환 응답 형식
    echo json_encode([
        'success' => true,
        'price' => number_format($total_price),
        'price_vat' => number_format($total_price_vat),
        'price_raw' => $total_price,
        'price_vat_raw' => $total_price_vat,
        'print_price' => $print_price,
        'design_price' => ($ordertype === 'design') ? $design_price : 0,
        'vat_amount' => $vat_amount,
        'details' => [
            'MY_type' => $MY_type,
            'MY_Fsd' => $MY_Fsd,
            'PN_type' => $PN_type,
            'MY_amount' => $MY_amount,
            'ordertype' => $ordertype
        ],
        // 기존 호환성을 위한 필드들
        'PriceForm' => $print_price,
        'DS_PriceForm' => ($ordertype === 'design') ? $design_price : 0,
        'Order_PriceForm' => $total_price,
        'VAT_PriceForm' => $vat_amount,
        'Total_PriceForm' => $total_price_vat
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'price' => '0',
        'price_vat' => '0'
    ], JSON_UNESCAPED_UNICODE);
}

if ($connect) {
    mysqli_close($connect);
}
?>