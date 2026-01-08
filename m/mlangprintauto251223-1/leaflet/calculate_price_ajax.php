<?php
header("Content-Type: application/json");
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
$MY_type = $_GET['MY_type'] ?? '';        // 규격 (A4, A5 등)
$PN_type = $_GET['PN_type'] ?? '';        // 용지
$MY_Fsd = $_GET['MY_Fsd'] ?? '';          // 재단 옵션
$MY_amount = $_GET['MY_amount'] ?? '';    // 수량
$POtype = $_GET['POtype'] ?? '';          // 인쇄 타입
$ordertype = $_GET['ordertype'] ?? '';    // 주문 유형 (인쇄/디자인/전체)
$fold_type = $_GET['fold_type'] ?? '';    // 접지방식 (리플렛 전용)
$coating_type = $_GET['coating_type'] ?? '';    // 코팅 옵션
$creasing_type = $_GET['creasing_type'] ?? '';  // 오시 옵션

// 추가 옵션 가격 받기
$additional_options_total = intval($_GET['premium_options_total'] ?? $_GET['additional_options_total'] ?? 0);

// 입력값 검증
if (empty($MY_type) || empty($PN_type) || empty($MY_Fsd) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    echo json_encode(['success' => false, 'error' => ['message' => '필수 입력값이 누락되었습니다.']]);
    exit;
}

// Step 1: inserted 테이블에서 기본 가격 조회 (전단지 가격 활용)
$TABLE = "mlangprintauto_inserted";
$query = "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND quantity='$MY_amount' AND TreeSelect='$MY_Fsd' AND POtype='$POtype'";
$result = mysqli_query($connect, $query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => ['message' => '데이터베이스 쿼리 오류: ' . mysqli_error($connect)]]);
    exit;
}

$row = mysqli_fetch_array($result);

if ($row) {
    // 기본 가격 계산 (전단지 가격)
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

    // Step 2: 접지방식 추가 금액 조회
    $fold_additional_price = 0;
    if (!empty($fold_type)) {
        $fold_query = "SELECT additional_price FROM mlangprintauto_leaflet_fold WHERE fold_type='$fold_type' AND is_active=1";
        $fold_result = mysqli_query($connect, $fold_query);
        if ($fold_result && $fold_row = mysqli_fetch_array($fold_result)) {
            $fold_additional_price = intval($fold_row['additional_price']);
        }
    }

    // Step 3: 코팅 추가 금액 조회 (전단지와 동일)
    $coating_additional_price = 0;
    if (!empty($coating_type)) {
        $coating_query = "SELECT base_price FROM additional_options_config WHERE option_type='$coating_type' AND option_category='coating' AND is_active=1";
        $coating_result = mysqli_query($connect, $coating_query);
        if ($coating_result && $coating_row = mysqli_fetch_array($coating_result)) {
            $coating_additional_price = intval($coating_row['base_price']);
        }
    }

    // Step 4: 오시 추가 금액 조회 (전단지와 동일)
    $creasing_additional_price = 0;
    if (!empty($creasing_type)) {
        $creasing_query = "SELECT base_price FROM additional_options_config WHERE option_type='$creasing_type' AND option_category='creasing' AND is_active=1";
        $creasing_result = mysqli_query($connect, $creasing_query);
        if ($creasing_result && $creasing_row = mysqli_fetch_array($creasing_result)) {
            $creasing_additional_price = intval($creasing_row['base_price']);
        }
    }

    // Step 5: 최종 가격 계산
    $Order_PricOk = $Price + $DesignMoneyOk + $fold_additional_price + $coating_additional_price + $creasing_additional_price; // 기본 합계 + 접지 + 코팅 + 오시
    $Order_PricOk_With_Options = $Order_PricOk + $additional_options_total; // 추가 옵션 포함 합계
    $VAT_PriceOk = $Order_PricOk_With_Options / 10;  // 부가세 10%
    $Total_PriceOk = $Order_PricOk_With_Options + $VAT_PriceOk;  // 최종 총액
    $ViewquantityTwo = $row['quantityTwo'] ?? '';  // 전단지 연수 옆에 장수

    // 성공 응답
    $response = [
        'success' => true,
        'data' => [
            'Price' => number_format($Price),
            'DS_Price' => number_format($DesignMoneyOk),
            'Fold_Price' => number_format($fold_additional_price), // 접지 추가금 표시
            'Coating_Price' => number_format($coating_additional_price), // 코팅 추가금 표시
            'Creasing_Price' => number_format($creasing_additional_price), // 오시 추가금 표시
            'Order_Price' => number_format($Order_PricOk_With_Options), // 추가 옵션 포함된 가격
            'Additional_Options' => number_format($additional_options_total), // 추가 옵션 가격
            'PriceForm' => $Price,
            'DS_PriceForm' => $DesignMoneyOk,
            'Fold_PriceForm' => $fold_additional_price, // 접지 추가금 숫자
            'Coating_PriceForm' => $coating_additional_price, // 코팅 추가금 숫자
            'Creasing_PriceForm' => $creasing_additional_price, // 오시 추가금 숫자
            'Order_PriceForm' => $Order_PricOk_With_Options, // 추가 옵션 포함된 가격
            'Additional_Options_Form' => $additional_options_total, // 추가 옵션 가격
            'VAT_PriceForm' => $VAT_PriceOk,
            'Total_PriceForm' => $Total_PriceOk,
            'StyleForm' => $MY_type,
            'SectionForm' => $PN_type,
            'QuantityForm' => $MY_amount,
            'FoldTypeForm' => $fold_type, // 접지방식
            'CoatingTypeForm' => $coating_type, // 코팅 옵션
            'CreasingTypeForm' => $creasing_type, // 오시 옵션
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
