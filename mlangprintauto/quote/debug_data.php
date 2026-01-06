<?php
/**
 * quotation_temp 데이터 직접 확인 스크립트
 */
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/ProductSpecFormatter.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>스티커 수량 디버깅</title>
    <style>
        body { font-family: 'Courier New', monospace; margin: 20px; background: #f5f5f5; }
        .box { background: white; border: 2px solid #333; padding: 20px; margin: 20px 0; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; }
    </style>
</head>
<body>

<div class="box">
    <h1>🔍 스티커 수량 "1" 문제 디버깅</h1>
    <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
    <p><strong>실행 시간:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    <p><strong>서버:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
</div>

<?php
// quotation_temp에서 스티커 데이터 조회
$query = "SELECT * FROM quotation_temp WHERE product_type = 'sticker' ORDER BY regdate DESC LIMIT 5";
$result = mysqli_query($db, $query);

if (!$result) {
    echo "<div class='box'><p class='error'>❌ 쿼리 실패: " . mysqli_error($db) . "</p></div>";
    exit;
}

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
?>

<div class="box">
    <h2>📊 quotation_temp 스티커 데이터 (최근 5개)</h2>
    <p>총 <strong><?php echo count($items); ?></strong>개 품목</p>
</div>

<?php
if (count($items) == 0) {
    echo "<div class='box'><p class='warning'>⚠️ quotation_temp에 스티커 데이터가 없습니다.</p></div>";
} else {
    foreach ($items as $index => $item) {
?>
<div class="box">
    <h2>스티커 품목 #<?php echo ($index + 1); ?> (no=<?php echo $item['no']; ?>)</h2>

    <h3>🔹 기본 정보</h3>
    <pre>
product_type    : <?php echo $item['product_type'] ?? 'NULL'; ?>

mesu            : <?php echo $item['mesu'] ?? 'NULL'; ?>

quantity_display: <?php echo $item['quantity_display'] ?? 'NULL'; ?>

data_version    : <?php echo $item['data_version'] ?? 'NULL'; ?>

st_price        : <?php echo $item['st_price'] ?? 'NULL'; ?>

st_price_vat    : <?php echo $item['st_price_vat'] ?? 'NULL'; ?>
</pre>

    <h3>🔹 quantity_display 상태 체크</h3>
    <pre>
isset(quantity_display) : <?php echo isset($item['quantity_display']) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>'; ?>

empty(quantity_display) : <?php echo empty($item['quantity_display']) ? '<span class="error">⚠️ YES (비어있음)</span>' : '<span class="success">✅ NO (값 있음)</span>'; ?>

값의 타입             : <?php echo gettype($item['quantity_display']); ?>

값의 길이             : <?php echo isset($item['quantity_display']) ? strlen($item['quantity_display']) : 'N/A'; ?>

실제 값 (따옴표 포함)  : '<?php echo $item['quantity_display'] ?? 'NULL'; ?>'
</pre>

    <h3>🔹 CART-STYLE 로직 테스트</h3>
    <?php
    $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';
    if (!empty($item['mesu'])) {
        $qtyDisplay = number_format($item['mesu']) . $unit;
        $method = 'mesu';
    } elseif (!empty($item['MY_amount'])) {
        $qtyDisplay = htmlspecialchars($item['MY_amount']) . $unit;
        $method = 'MY_amount';
    } else {
        $qtyDisplay = '1' . $unit;
        $method = 'default';
    }
    ?>
    <pre>
사용된 필드      : <?php echo $method; ?>

반환값          : '<?php echo $qtyDisplay; ?>'

결과 분석       : <?php
if ($qtyDisplay == '1매') {
    echo '<span class="error">❌❌❌ 문제 발생! "1매"가 반환됨</span>';
} elseif (strpos($qtyDisplay, '1,000') !== false) {
    echo '<span class="success">✅✅✅ 정상! "1,000매"가 반환됨</span>';
} else {
    echo '<span class="warning">⚠️ 예상치 못한 값</span>';
}
?>
</pre>

    <h3>🔹 mesu 값 상세 분석</h3>
    <pre>
mesu isset      : <?php echo isset($item['mesu']) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>'; ?>

mesu empty      : <?php echo empty($item['mesu']) ? '<span class="error">⚠️ YES (비어있음)</span>' : '<span class="success">✅ NO (값 있음)</span>'; ?>

mesu 값         : '<?php echo $item['mesu'] ?? 'NULL'; ?>'

mesu 타입       : <?php echo gettype($item['mesu']); ?>

number_format   : <?php echo !empty($item['mesu']) ? number_format($item['mesu']) : 'N/A'; ?>
</pre>

</div>
<?php
    }
}
?>

<div class="box">
    <h2>🎯 결론 및 다음 단계</h2>
    <?php
    $hasIssue = false;
    foreach ($items as $item) {
        $unit = ($item['product_type'] == 'ncrflambeau') ? '권' : '매';
        if (!empty($item['mesu'])) {
            $result = number_format($item['mesu']) . $unit;
        } else {
            $result = '1' . $unit;
            $hasIssue = true;
        }
    }

    if ($hasIssue) {
        echo "<p class='error'>❌ mesu 필드가 비어있습니다!</p>";
        echo "<p>가능한 원인:</p>";
        echo "<ul>";
        echo "<li>add_to_quotation_temp.php에서 mesu 저장 안 함</li>";
        echo "<li>계산기 모달에서 mesu 전송 안 함</li>";
        echo "<li>스티커가 아닌 다른 제품일 수 있음</li>";
        echo "</ul>";
    } else {
        echo "<p class='success'>✅ mesu 필드에 정상 데이터가 있습니다!</p>";
        echo "<p>그렇다면 create.php 파일이 제대로 배포되지 않았을 가능성이 높습니다.</p>";
    }
    ?>
</div>

</body>
</html>
<?php
mysqli_close($db);
?>
