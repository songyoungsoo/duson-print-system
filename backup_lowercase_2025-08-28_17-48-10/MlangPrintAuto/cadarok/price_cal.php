<?php
header('Content-Type: application/json; charset=utf-8');

// 디버그 모드 (개발 중에만 사용)
$debug = true;
if ($debug) {
    error_log("=== 카다록 가격 계산 시작 ===");
    error_log("POST 데이터: " . print_r($_POST, true));
}

// === 기본 설정 ===
$HomeDir = "../../";

// 데이터베이스 연결 설정 (index.php와 동일하게)
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$connect = mysqli_connect($host, $user, $password, $dataname);
if (!$connect) {
    echo json_encode(["error" => "데이터베이스 연결에 실패했습니다: " . mysqli_connect_error()]);
    exit;
}

mysqli_set_charset($connect, "utf8");

// === 입력값 받기 ===
$MY_type    = $_POST['MY_type']    ?? '';
$MY_Fsd     = $_POST['MY_Fsd']     ?? '';
$PN_type    = $_POST['PN_type']    ?? '';
$MY_amount  = $_POST['MY_amount']  ?? '';
$ordertype  = $_POST['ordertype']  ?? '';

$response = [
  "PriceForm" => 0,
  "DS_PriceForm" => 0,
  "Order_PriceForm" => 0,
  "VAT_PriceForm" => 0,
  "Total_PriceForm" => 0,
  "StyleForm" => $MY_type,
  "SectionForm" => $MY_Fsd,
  "QuantityForm" => $MY_amount,
  "DesignForm" => $ordertype
];

if ($MY_type && $MY_Fsd && $PN_type && $MY_amount) {
  // 먼저 테이블이 존재하는지 확인
  $table_check = mysqli_query($connect, "SHOW TABLES LIKE "mlangprintauto_cadarok"");
  
  if (mysqli_num_rows($table_check) > 0) {
    $stmt = mysqli_prepare($connect, "SELECT money FROM mlangprintauto_cadarok WHERE style = ? AND Section = ? AND TreeSelect = ? AND quantity = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "sssi", $MY_type, $MY_Fsd, $PN_type, $MY_amount);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
      $print_price = (int)$row['money'];
      $order_price = $print_price;
      $vat = (int)round($order_price * 0.1);
      $total_price = $order_price + $vat;

      $response["PriceForm"] = $print_price;
      $response["Order_PriceForm"] = $order_price;
      $response["VAT_PriceForm"] = $vat;
      $response["Total_PriceForm"] = $total_price;
    } else {
      // 데이터가 없는 경우 기본 가격 설정
      $print_price = 50000; // 기본 가격
      $order_price = $print_price;
      $vat = (int)round($order_price * 0.1);
      $total_price = $order_price + $vat;

      $response["PriceForm"] = $print_price;
      $response["Order_PriceForm"] = $order_price;
      $response["VAT_PriceForm"] = $vat;
      $response["Total_PriceForm"] = $total_price;
    }
    mysqli_stmt_close($stmt);
  } else {
    // 테이블이 없는 경우 기본 가격 설정
    $print_price = 50000; // 기본 가격
    $order_price = $print_price;
    $vat = (int)round($order_price * 0.1);
    $total_price = $order_price + $vat;

    $response["PriceForm"] = $print_price;
    $response["Order_PriceForm"] = $order_price;
    $response["VAT_PriceForm"] = $vat;
    $response["Total_PriceForm"] = $total_price;
  }
}

// 연결 종료
if ($connect) {
    mysqli_close($connect);
}

if ($debug) {
    error_log("최종 응답: " . print_r($response, true));
    error_log("=== 카다록 가격 계산 완료 ===");
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
