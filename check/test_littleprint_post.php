<?php
/**
 * 포스터 add_to_basket.php 직접 테스트
 */

session_start();

// 테스트 데이터
$_POST = [
    'MY_type' => '4도',
    'Section' => '아트지150g',
    'PN_type' => 'A2',
    'MY_amount' => '100',
    'POtype' => '1',
    'ordertype' => 'print',
    'calculated_price' => '85000',
    'calculated_vat_price' => '93500',
    'additional_options_total' => '0',
    'work_memo' => '테스트 주문',
    'upload_method' => 'upload'
];

echo "<h2>포스터 add_to_basket.php 테스트</h2>";
echo "<h3>전송 데이터:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>응답:</h3>";
echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd;'>";

// add_to_basket.php 실행
ob_start();
include "mlangprintauto/littleprint/add_to_basket.php";
$response = ob_get_clean();

echo $response;
echo "</div>";

// JSON 파싱 시도
echo "<h3>파싱된 응답:</h3>";
$json = json_decode($response, true);
if ($json) {
    echo "<pre>";
    print_r($json);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>JSON 파싱 실패</p>";
}
?>
