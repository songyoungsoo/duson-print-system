<?php
session_start();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8");

// POST 데이터 받기
$input = json_decode(file_get_contents('php://input'), true);

$MY_type = $input['MY_type'] ?? '';
$PN_type = $input['PN_type'] ?? '';
$MY_amount = $input['MY_amount'] ?? '';
$POtype = $input['POtype'] ?? '';
$ordertype = $input['ordertype'] ?? '';

// 입력값 검증
if (empty($MY_type) || empty($PN_type) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    echo json_encode(['success' => false, 'message' => '필수 입력값이 누락되었습니다.']);
    exit;
}

try {
    // 봉투 가격 테이블에서 가격 조회
    $query = "SELECT * FROM mlangprintauto_envelope 
              WHERE MY_type = ? AND PN_type = ? AND MY_amount = ? AND POtype = ?";
    
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($stmt, 'ssss', $MY_type, $PN_type, $MY_amount, $POtype);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $print_price = intval($row['price'] ?? 0);
        $design_price = 0;
        
        // 디자인비 계산
        if ($ordertype == 'total') {
            $design_price = intval($row['design_price'] ?? 30000); // 기본 디자인비
        }
        
        $subtotal = $print_price + $design_price;
        $vat = round($subtotal * 0.1);
        $total = $subtotal + $vat;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'print_price' => $print_price,
                'design_price' => $design_price,
                'subtotal' => $subtotal,
                'vat' => $vat,
                'total' => $total,
                'formatted' => [
                    'print_price' => number_format($print_price),
                    'design_price' => number_format($design_price),
                    'subtotal' => number_format($subtotal),
                    'vat' => number_format($vat),
                    'total' => number_format($total)
                ]
            ]
        ]);
    } else {
        // 기본 가격 설정 (테이블에 데이터가 없는 경우)
        $print_price = 50000; // 기본 인쇄비
        $design_price = ($ordertype == 'total') ? 30000 : 0;
        $subtotal = $print_price + $design_price;
        $vat = round($subtotal * 0.1);
        $total = $subtotal + $vat;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'print_price' => $print_price,
                'design_price' => $design_price,
                'subtotal' => $subtotal,
                'vat' => $vat,
                'total' => $total,
                'formatted' => [
                    'print_price' => number_format($print_price),
                    'design_price' => number_format($design_price),
                    'subtotal' => number_format($subtotal),
                    'vat' => number_format($vat),
                    'total' => number_format($total)
                ]
            ]
        ]);
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '가격 계산 오류: ' . $e->getMessage()]);
}

mysqli_close($connect);
?>