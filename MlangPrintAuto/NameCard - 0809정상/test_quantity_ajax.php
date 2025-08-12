<?php
echo "<h3>수량 AJAX 테스트</h3>";

// 일반명함 수량 테스트
echo "<h4>일반명함(275) 수량 옵션:</h4>";
$_GET['NC_type'] = '275';
ob_start();
include "get_namecard_quantities.php";
$output1 = ob_get_clean();
echo "JSON 출력: " . $output1 . "<br><br>";

// 고급수입지 수량 테스트  
echo "<h4>고급수입지(278) 수량 옵션:</h4>";
$_GET['NC_type'] = '278';
ob_start();
include "get_namecard_quantities.php";
$output2 = ob_get_clean();
echo "JSON 출력: " . $output2 . "<br><br>";

// 카드명함 수량 테스트
echo "<h4>카드명함(704) 수량 옵션:</h4>";
$_GET['NC_type'] = '704';
ob_start();
include "get_namecard_quantities.php";
$output3 = ob_get_clean();
echo "JSON 출력: " . $output3 . "<br>";
?>