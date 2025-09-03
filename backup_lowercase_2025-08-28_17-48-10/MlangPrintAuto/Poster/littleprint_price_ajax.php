<?php
session_start();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

header('Content-Type: application/json; charset=utf-8');

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8");

// GET 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';        // 구분 (style)
$TreeSelect = $_GET['TreeSelect'] ?? '';  // 종이종류
$PN_type = $_GET['PN_type'] ?? '';        // 종이규격 (Section)
$POtype = $_GET['POtype'] ?? '';          // 인쇄면
$MY_amount = $_GET['MY_amount'] ?? '';    // 수량
$ordertype = $_GET['ordertype'] ?? 'total'; // 디자인편집

// 입력값 검증
if (empty($MY_type) || empty($PN_type) || empty($MY_amount) || empty($POtype)) {
    echo json_encode(['success' => false, 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

try {
    // 데이터베이스에서 실제 가격 조회
    $query = "SELECT money, DesignMoney FROM mlangprintauto_littleprint WHERE style = ? AND Section = ? AND quantity = ? AND POtype = ?";
    $params = [$MY_type, $PN_type, $MY_amount, $POtype];
    $types = "ssss";
    
    if (!empty($TreeSelect)) {
        $query .= " AND TreeSelect = ?";
        $params[] = $TreeSelect;
        $types .= "s";
    }
    
    $query .= " LIMIT 1";
    
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $base_price = intval($row['money']);
        $design_price = intval($row['DesignMoney']);
    } else {
        // 정확한 조건의 데이터가 없으면 비슷한 조건으로 검색
        $query2 = "SELECT money, DesignMoney FROM mlangprintauto_littleprint WHERE style = ? AND Section = ? AND POtype = ?";
        $params2 = [$MY_type, $PN_type, $POtype];
        $types2 = "sss";
        
        if (!empty($TreeSelect)) {
            $query2 .= " AND TreeSelect = ?";
            $params2[] = $TreeSelect;
            $types2 .= "s";
        }
        
        $query2 .= " ORDER BY ABS(quantity - ?) ASC LIMIT 1";
        $params2[] = $MY_amount;
        $types2 .= "s";
        
        $stmt2 = mysqli_prepare($connect, $query2);
        mysqli_stmt_bind_param($stmt2, $types2, ...$params2);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        
        if ($result2 && mysqli_num_rows($result2) > 0) {
            $row = mysqli_fetch_array($result2);
            $base_price = intval($row['money']);
            $design_price = intval($row['DesignMoney']);
            
            // 수량에 따른 비례 계산
            $quantity_ratio = floatval($MY_amount) / 100; // 기준 수량 100으로 비례 계산
            $base_price = intval($base_price * $quantity_ratio);
        } else {
            // 데이터가 전혀 없으면 기본 계산 로직 사용
            $quantity = intval($MY_amount);
            $base_price = $quantity * 1000; // 기본 단가
            
            // 인쇄면에 따른 가격 조정
            if ($POtype == '2') { // 양면인쇄
                $base_price = intval($base_price * 1.6);
            }
            
            $design_price = 100000; // 기본 디자인비
        }
    }
    
    // 주문 방법에 따른 최종 가격 계산
    switch ($ordertype) {
        case 'total': // 디자인+인쇄
            $final_base_price = $base_price;
            $final_design_price = $design_price;
            break;
        case 'design': // 디자인만
            $final_base_price = 0;
            $final_design_price = $design_price;
            break;
        case 'print': // 인쇄만
            $final_base_price = $base_price;
            $final_design_price = 0;
            break;
        default:
            $final_base_price = $base_price;
            $final_design_price = $design_price;
    }
    
    $total_price = $final_base_price + $final_design_price;
    $vat_price = $total_price * 1.1;
    
    // 응답 데이터 구성
    $response_data = [
        'LP_Price' => number_format($final_base_price),
        'LP_DS_Price' => number_format($final_design_price),
        'LP_Order_Price' => number_format($total_price),
        'PriceForm' => $final_base_price,
        'DS_PriceForm' => $final_design_price,
        'Order_PriceForm' => $total_price,
        'VAT_PriceForm' => $vat_price,
        'Total_PriceForm' => $vat_price,
        'StyleForm' => $MY_type,
        'SectionForm' => $PN_type,
        'QuantityForm' => $MY_amount,
        'DesignForm' => $ordertype
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $response_data
    ]);
    
} catch (Exception $e) {
    error_log("littleprint_price_ajax.php 오류: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '가격 계산 중 오류가 발생했습니다: ' . $e->getMessage()
    ]);
}

mysqli_close($connect);
?>