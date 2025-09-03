<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 데이터베이스 연결 - db.php 사용
include "../../db.php";
$connect = $db;

if (!$connect) {
    die(json_encode(['success' => false, 'error' => ['message' => '데이터베이스 연결에 실패했습니다: ' . mysqli_connect_error()]]));
}

mysqli_set_charset($connect, "utf8");

// GET 방식으로 데이터 가져오기
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_Fsd = $_GET['MY_Fsd'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';
$POtype = $_GET['POtype'] ?? '';
$ordertype = $_GET['ordertype'] ?? '';

// 입력값 검증
if (empty($MY_type) || empty($PN_type) || empty($MY_Fsd) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    echo json_encode(['success' => false, 'error' => ['message' => '필수 입력값이 누락되었습니다.']]);
    exit;
}

// 테이블명 설정
$TABLE = "mlangprintauto_inserted";

// 가격 정보 검색
$query = "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND quantity='$MY_amount' AND TreeSelect='$MY_Fsd' AND POtype='$POtype'";
$result = mysqli_query($connect, $query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => ['message' => '데이터베이스 쿼리 오류: ' . mysqli_error($connect)]]);
    exit;
}

$row = mysqli_fetch_array($result);

if ($row) {
    // 가격 계산
    if ($ordertype == "print") {
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = 0;  // 디자인편집비
    } elseif ($ordertype == "design") {
        $Price = 0;  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'];  // 디자인편집비
    } else { // total
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'];  // 디자인편집비
    }
    
    $Order_PricOk = $Price + $DesignMoneyOk; // 합계
    $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
    $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
    $ViewquantityTwo = $row['quantityTwo'] ?? '';  // 전단지 연수 옆에 장수
    
    // 성공 응답
    $response = [
        'success' => true,
        'data' => [
            'Price' => number_format($Price),
            'DS_Price' => number_format($DesignMoneyOk),
            'Order_Price' => number_format($Order_PricOk),
            'PriceForm' => $Price,
            'DS_PriceForm' => $DesignMoneyOk,
            'Order_PriceForm' => $Order_PricOk,
            'VAT_PriceForm' => $VAT_PriceOk,
            'Total_PriceForm' => $Total_PriceOk,
            'StyleForm' => $MY_type,
            'SectionForm' => $PN_type,
            'QuantityForm' => $MY_amount,
            'DesignForm' => $ordertype,
            'MY_amountRight' => $ViewquantityTwo . '장'
        ]
    ];
    
    echo json_encode($response);
} else {
    // 가격 정보가 없는 경우
    echo json_encode([
        'success' => false, 
        'error' => [
            'message' => '견적을 수행할 관련 정보가 없습니다.\n\n다른 항목으로 견적을 해주시기 바랍니다.'
        ]
    ]);
}

mysqli_close($connect);
?>