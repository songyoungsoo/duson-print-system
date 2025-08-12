<?php
echo "<h3>수정된 수량 옵션 테스트</h3>";

// 일반명함 수량 테스트 (100, 200매 제외되어야 함)
echo "<h4>일반명함(275) 수량 옵션 (100, 200매 제외):</h4>";
$_GET['NC_type'] = '275';
ob_start();
include "get_namecard_quantities.php";
$output1 = ob_get_clean();
echo "JSON 출력: " . $output1 . "<br><br>";

// 고급수입지 수량 테스트 (그대로 유지)
echo "<h4>고급수입지(278) 수량 옵션 (변경 없음):</h4>";
$_GET['NC_type'] = '278';
ob_start();
include "get_namecard_quantities.php";
$output2 = ob_get_clean();
echo "JSON 출력: " . $output2 . "<br><br>";

// JSON 파싱해서 확인
echo "<h4>일반명함 수량 목록 확인:</h4>";
$general_quantities = json_decode($output1, true);
if ($general_quantities) {
    foreach ($general_quantities as $qty) {
        echo "• " . $qty['text'] . " (값: " . $qty['value'] . ")<br>";
    }
    
    // 100매, 200매가 있는지 확인
    $has_100 = false;
    $has_200 = false;
    foreach ($general_quantities as $qty) {
        if ($qty['value'] == '100') $has_100 = true;
        if ($qty['value'] == '200') $has_200 = true;
    }
    
    echo "<br>검증 결과:<br>";
    echo "100매 포함: " . ($has_100 ? "❌ 있음 (문제)" : "✅ 없음 (정상)") . "<br>";
    echo "200매 포함: " . ($has_200 ? "❌ 있음 (문제)" : "✅ 없음 (정상)") . "<br>";
}
?>