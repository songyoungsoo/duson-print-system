<?php
// 가격 계산 테스트
$test_url = "http://localhost/MlangPrintAuto/envelope/price_cal_ajax.php?MY_type=282&PN_type=283&MY_amount=1000&POtype=1&ordertype=total";

echo "테스트 URL: " . $test_url . "\n\n";

$response = file_get_contents($test_url);

echo "응답:\n";
echo $response . "\n\n";

$json = json_decode($response, true);
if ($json) {
    echo "JSON 파싱 성공:\n";
    print_r($json);
} else {
    echo "JSON 파싱 실패\n";
    echo "JSON 오류: " . json_last_error_msg() . "\n";
}
?>