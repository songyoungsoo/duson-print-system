<?php
// 간단한 가격 계산 테스트

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 간단한 가격 계산 테스트</h2>";

// POST 데이터 시뮬레이션
$_POST = [
    'action' => 'calculate',
    'jong' => 'jil 아트유광코팅',
    'garo' => '100',
    'sero' => '150',
    'mesu' => '1000',
    'uhyung' => '0',
    'domusong' => '08000 원형'
];

echo "<h3>입력 데이터:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// calculate_price.php 내용을 직접 실행
session_start();
header('Content-Type: text/html; charset=UTF-8'); // JSON 대신 HTML로 변경

$HomeDir = "../../";
include "../lib/func.php";
$connect = dbconn();

try {
    if ($_POST['action'] !== 'calculate') {
        throw new Exception('잘못된 요청입니다.');
    }
    
    $jong = $_POST['jong'] ?? '';
    $garo = (int)($_POST['garo'] ?? 0);
    $sero = (int)($_POST['sero'] ?? 0);
    $mesu = (int)($_POST['mesu'] ?? 0);
    $uhyung = (int)($_POST['uhyung'] ?? 0);
    $domusong = $_POST['domusong'] ?? '';
    
    echo "<h3>파싱된 데이터:</h3>";
    echo "jong: $jong<br>";
    echo "garo: $garo<br>";
    echo "sero: $sero<br>";
    echo "mesu: $mesu<br>";
    echo "uhyung: $uhyung<br>";
    echo "domusong: $domusong<br><br>";
    
    // 입력값 검증
    if (!$garo) throw new Exception('가로사이즈를 입력하세요');
    if (!$sero) throw new Exception('세로사이즈를 입력하세요');
    if ($garo > 590) throw new Exception('가로사이즈를 590mm이하만 입력할 수 있습니다');
    if ($sero > 590) throw new Exception('세로사이즈를 590mm이하만 입력할 수 있습니다');
    
    echo "<h3>✅ 입력값 검증 통과</h3>";
    
    // 재질별 제한 검증
    $j = substr($jong, 4, 10);
    $j1 = substr($jong, 0, 3);
    
    echo "재질 코드: j1='$j1', j='$j'<br>";
    
    if ($j == '금지스티커') throw new Exception('금지스티커는 전화 또는 메일로 견적 문의하세요');
    if ($j == '금박스티커') throw new Exception('금박스티커는 전화 또는 메일로 견적 문의하세요');
    if ($j == '롤형스티커') throw new Exception('롤스티커는 전화 또는 메일로 견적 문의하세요');
    
    echo "<h3>✅ 재질 제한 검증 통과</h3>";
    
    // 간단한 가격 계산 (복잡한 로직 대신)
    $base_price = ($garo + 4) * ($sero + 4) * $mesu * 0.15; // 기본 요율 0.15
    $domusong_price = (int)substr($domusong, 0, 5); // 도무송 가격 추출
    $total_price = $base_price + $uhyung + $domusong_price + 7000; // 기본비용 7000
    $total_price_vat = $total_price * 1.1;
    
    echo "<h3>💰 가격 계산 결과:</h3>";
    echo "기본 가격: " . number_format($base_price) . "원<br>";
    echo "도무송 가격: " . number_format($domusong_price) . "원<br>";
    echo "유형 가격: " . number_format($uhyung) . "원<br>";
    echo "최종 가격: " . number_format($total_price) . "원<br>";
    echo "VAT 포함: " . number_format($total_price_vat) . "원<br>";
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>✅ 성공!</h3>";
    echo "<strong>가격: " . number_format($total_price) . "원</strong><br>";
    echo "<strong>VAT 포함: " . number_format($total_price_vat) . "원</strong>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>❌ 오류 발생</h3>";
    echo "오류 메시지: " . $e->getMessage() . "<br>";
    echo "오류 위치: " . $e->getFile() . ":" . $e->getLine();
    echo "</div>";
}

if ($connect) {
    mysqli_close($connect);
}
?>